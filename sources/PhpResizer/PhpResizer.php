<?php
/**
 * @version $Revision$
 * @category PhpResizer
 * @package PhpResizer
 * @author $Author$ $Date$
 * @license New BSD license
 * @copyright http://phpresizer.org/
 */

/**
 *
 */
class PhpResizer_PhpResizer {

    const ENGINE_GD2 = 'GD2';
    const ENGINE_IMAGEMAGICK = 'ImageMagic';
    const ENGINE_GRAPHIKSMAGICK = 'GraphicsMagick';

    const EXC_TMPDIR_NOT_EXISTS = 'Path "%s" is not exists or not writtable';
    const EXC_CACHEDIR_NOT_EXISTS =
        'Path "%s" is not exists or not writtable or not executable';
    const EXC_FILE_CRASHED = 'File "%s" is crashed';
    const EXC_ENABLE_CACHE =
        'For "returnOnlyPath" option set "cache" options as TRUE';

    /**
     * Default cache image time to live in minutes
     *
     * @var int
     */
    const DEFAULT_CACHE_TTL = 10080;

    /**
     * @var array
     */
    protected $_config;

    /**
     * @var bool
     */
    protected $_returnOnlyPath = false;

    /**
     * @var bool
     */
    protected $_checkEtag;

    /**
     * @var PhpResizer_Engine_EngineAbstract
     */
    protected $_engine;

    /**
     * @var string
     */
    protected $_cacheDir;

    /**
     * @var string
     */
    protected $_tmpDir;

    /**
     * @var bool
     */
    protected $_useCache = false;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->_config = array_merge(array (
            'engine' => self::ENGINE_GD2,
            'cache' => true,
            'cacheBrowser' => true,
            'cacheDir' => '/tmp/resizerCache/',
            'tmpDir' => '/tmp/'
        ), $options);

        $this->_useCache = (bool)$this->_config['cache'];
        $this->_tmpDir = $this->_config['tmpDir'];
        $this->_cacheDir = $this->_config['cacheDir'];

        $this->_validateTmpDir();
        if ($this->_useCache) {
            $this->_validateCacheDir();
        }

        $this->_engine = $this->_createEngine($this->_config['engine']);
    }

    /**
     * @param string $name
     * @return PhpResizer_Engine_EngineAbstract
     */
    protected function _createEngine($name)
    {
        $class = 'PhpResizer_Engine_' . $name;
        $engine = new $class();
        return $engine;
    }

    /**
     * @throws PhpResizer_Exception_Basic
     */
    protected function _validateTmpDir()
    {
        if (!is_writable($this->_tmpDir)) {
            $message = sprintf(self::EXC_TMPDIR_NOT_EXISTS, $this->_tmpDir);
            throw new PhpResizer_Exception_Basic($message);
        }
    }

    /**
     * @throws PhpResizer_Exception_Basic
     */
    protected function _validateCacheDir()
    {
        $dir = $this->_cacheDir;
        if (!is_writable($dir) || !is_executable($dir)) {
            $message = sprintf(self::EXC_CACHEDIR_NOT_EXISTS, $dir);
            throw new PhpResizer_Exception_Basic($message);
        }
    }

    /**
     *
     * @param string $filename
     * @param array $options
     * @throws PhpResizer_Exception_Basic
     */
    public function resize($filename, array $options = array())
    {
        if (!is_readable($filename)) {
            return $this->_return404();

        } else if (false === ($size = @getimagesize($filename))) {
            $message = sprintf(self::EXC_FILE_CRASHED, $filename);
            throw new PhpResizer_Exception_Basic($message);
        }

        if (!$options) {
            $this->_returnImageOrPath($filename);
        }

        if (isset($options['returnOnlyPath']) && $options['returnOnlyPath']
            && $this->_useCache)
        {
            unset($options['returnOnlyPath']);
            $this->_returnOnlyPath = true;

        } else if (isset($options['returnOnlyPath'])
            && $options['returnOnlyPath'])
        {
            throw new PhpResizer_Exception_Basic(self::EXC_ENABLE_CACHE);
        }

        $cacheFile = $this->_getCacheFileName($filename, $options);

        $options += array(
            'path' => $filename,
            'size' => $size,
            'cacheFile' => $cacheFile,
        );

        if (!$this->_engine->resize($options)) {
            $this->_return404();
        }

        if (!$this->_useCache){
            $this->_returnImageOrPath($cacheFile, $options);
            unlink($cacheFile);

        } else {
            return $this->_returnImageOrPath($cacheFile, $options);
        }

    }

    /**
     * Return image if cacheFile is valid and exist or return path to newcacheFile
     *
     * @param $path
     * @param $options
     * @return string
     */
    protected function _getCacheFileName ($path, $options)
    {
        $cacheFile = null;
        if ($this->_useCache) {
            $cacheFile = $this->generatePath($path, $options);
            if (file_exists($cacheFile) && getimagesize($cacheFile) &&
                filemtime($cacheFile)>=filemtime($path)) {
                    return $this->_returnImageOrPath($cacheFile, $options);

            } else if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }

        } else {
            $cacheFile = $this->_tmpDir . '/imageResizerTmpFile_'
                . uniqid() . '.' . $this->getExtension($path);
        }

        return $cacheFile;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getExtension($filename)
    {
        $allowedExtenstions = array('png');
        $defaultExtension = 'jpg';
        $ext = strtolower(substr($filename,-3));

        if (in_array($ext, $allowedExtenstions)) {
            return $ext;

        } else {
            return $defaultExtension;
        }
    }

    /**
     * @param string $path
     * @param array $options
     * @return string
     */
    public function generatePath($path, array $options)
    {

        if (isset($options['returnOnlyPath'])) {
            unset ($options['returnOnlyPath']);
        }

        $hash = md5(serialize($options).$path);
        $cacheFilePath = $this->_cacheDir . '/' . substr($hash, 0,1)
            . '/' . substr($hash, 1, 1) . '/' . substr($hash, 2) . '.'
            . $this->getExtension($path);


        if (!is_dir(dirname($cacheFilePath))){
            mkdir(dirname($cacheFilePath),0777,true);
        }

        return $cacheFilePath;
    }

    /**
     * @param string $filename absolute path to image-file
     */
    protected function _returnImageOrPath($filename)
    {
        if ($this->_returnOnlyPath) {
            return $filename;
        }

        if ($this->_checkEtag($filename)) {
            header('HTTP/1.1 304 Not Modified');

        } else {
            header('Content-type: image/jpeg');
            header('Content-Length: ' . filesize($filename));
            header('ETag: ' . md5_file($filename));
            readfile($filename);
        }

        exit;
    }

    /**
     * Send 404 HTTP code
     */
    protected function _return404()
    {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    /**
     * @param string $filename absolute path to image-file
     * @return boolean
     */
    protected function _checkEtag($filename)
    {
        if (!$this->_config['cacheBrowser']) {
            return false;
        }
        if (isset($this->_checkEtag)) {
            return $this->_checkEtag;
        }

        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])
            && md5_file($filename) == $_SERVER['HTTP_IF_NONE_MATCH'])
        {
            $_checkEtag = true;
            return true;
        }

        $this->_checkEtag = false;
        return false;
    }

    /**
     * @param int $ttl
     * @return array
     */
    public function clearCache($ttl = self::DEFAULT_CACHE_TTL)
    {
        $ttl = (int) $ttl;
        $dir = escapeshellcmd($this->_config['cacheDir']);
        $command = "find {$dir} \! -type d -amin +{$ttl} -exec  rm -v '{}' ';'";
        exec($command, $stringOutput);
        return $stringOutput;
    }
}