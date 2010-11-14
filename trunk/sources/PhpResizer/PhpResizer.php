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

    const DEFAULT_CACHE_TTL = 10080;

    /**
     * @var array
     */
    private $_config = array (
        'engine' => self::ENGINE_GD2,
        'cache' => true,
        'cacheBrowser' => true,
        'cacheDir' => '/tmp/resizerCache/',
        'tmpDir' => '/tmp/'
    );

    /**
     * @var bool
     */
    private $_returnOnlyPath = false;

    /**
     * @var bool
     */
    private $_checkEtag;

    /**
     * @var PhpResizer_Engine_EngineAbstract
     */
    protected $_engine;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->_config = array_merge($this->_config, $options);

        $this->_validateTmpDir($this->_config['tmpDir']);

        if ($this->_config['cache']) {
            $this->_validateCacheDir($this->_config['cacheDir']);
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
     * @param string $dir
     * @throws PhpResizer_PhpResizerException
     */
    protected function _validateTmpDir($dir)
    {
        if (!is_writable($dir)) {
            $message = sprintf(self::EXC_TMPDIR_NOT_EXISTS, $dir);
            throw new PhpResizer_PhpResizerException($message);
        }
    }

    /**
     * @param string $dir
     * @throws PhpResizer_PhpResizerException
     */
    protected function _validateCacheDir($dir)
    {
        if (!is_writable($dir) || !is_executable($dir)) {
            $message = sprintf(self::EXC_CACHEDIR_NOT_EXISTS, $dir);
            throw new PhpResizer_PhpResizerException($message);
        }
    }

    /**
     *
     * @param string $filename
     * @param array $options
     * @throws PhpResizer_PhpResizerException
     */
    public function resize($filename, array $options = array())
    {
        if (!is_readable($filename)) {
            return $this->_return404();

        } else if (false === ($size = @getimagesize($filename))) {
            $message = sprintf(self::EXC_FILE_CRASHED, $path);
            throw new PhpResizer_PhpResizerException($message);
        }

        if (!$options) {
            $this->_returnImageOrPath($filename);
        }

        if (isset($options['returnOnlyPath']) && $options['returnOnlyPath']
            && $this->_config['cache'])
        {
            unset($options['returnOnlyPath']);
            $this->_returnOnlyPath = true;

        } else if (isset($options['returnOnlyPath'])
            && $options['returnOnlyPath'])
        {
            throw new PhpResizer_PhpResizerException(self::EXC_ENABLE_CACHE);
        }

        $cacheFile = $this->_getCacheFileName($filename, $options);

        $options += array(
            'path' => $path,
            'size' => $size,
            'cacheFile' => $cacheFile,
        );

        if (!$this->_engine->resize($options)) {
            $this->_return404();
        }

        if (!$this->_config['cache']){
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
    private function _getCacheFileName ($path, $options)
    {
        $cacheFile = null;
        if ($this->_config['cache']) {
            $cacheFile = $this->generatePath($path,$options);
            if (file_exists($cacheFile) && getimagesize($cacheFile) &&
                filemtime($cacheFile)>=filemtime($path)) {
                    return $this->_returnImageOrPath($cacheFile, $options);

            } else if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }

        } else {
            $cacheFile = $this->_config['tmpDir'] . '/imageResizerTmpFile_'
                . uniqid() . $this->getExtensionFilter($path);
        }

        return $cacheFile;
    }

    /**
     * @param string $filename
     * @return string
     */
    private function getExtensionFilter($filename)
    {
        $allowedExtenstions = array('png');
        $defaultExtension = 'jpg';
        $ext = substr($path, strlen($path) - 3);

        if (in_array($ext, $allowedExtenstions)) {
            return '.' . $ext;

        } else {
            return '.' . $defaultExtension;
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
        $cacheFilePath = $this->_config['cacheDir'].'/'.substr($hash, 0,2).'/'.substr($hash,2,2).'/'.substr($hash,4).$this->getExtensionFilter($path);
        if(!is_dir(dirname(dirname($cacheFilePath)))){
            mkdir(dirname(dirname($cacheFilePath)));
        }
        if(!is_dir(dirname($cacheFilePath))){
            mkdir(dirname($cacheFilePath));
        }
        return $cacheFilePath;
    }

    /**
     * @param string $filename absolute path to image-file
     */
    private function _returnImageOrPath($filename)
    {
        if ($this->_returnOnlyPath) {
            return $filename;
        }

        if ($this->_checkEtag($filename)) {
            header("HTTP/1.1 304 Not Modified");

        } else {
            header("Content-type: image/jpeg");
            header("Content-Length: ".@filesize($filename));
            header('ETag: '.md5_file($filename));
            readfile ($filename);
            exit;
        }
    }

    /**
     * Send 404 HTTP code
     */
    private function _return404()
    {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    /**
     * @param string $filename absolute path to image-file
     * @return boolean
     */
    private function _checkEtag($filename)
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
            $this->_checkEtag = true;
            return true;

        } else {
            $this->_checkEtag = false;
            return false;
        }
    }

    /**
     * @param int $ttl
     * @return string
     */
    public function clearCache($ttl = self::DEFAULT_CACHE_TTL)
    {
        $command = "find {$this->_config['cacheDir']} \! -type d -amin +{$ttl} -exec  rm -v '{}' ';'";
        passthru($command, $result);
        return $result;
    }

    /**
     * @return bool
     */
    public function isGD2()
    {
        return $this->_engine instanceof PhpResizer_Engine_GD2;
    }

    /**
     * @return bool
     */
    public function isImageMagickEngine()
    {
        return $this->_engine instanceof PhpResizer_Engine_ImageMagic;
    }

    /**
     * @return bool
     */
    public function isGraphicsMagick()
    {
         return $this->_engine instanceof PhpResizer_Engine_GraphicsMagick;
    }
}