<?php
/**
 * @version $Revision$
 * @category PhpResizer
 * @package PhpResizer
 * @author $Author$ $Date$
 * @license New BSD license
 * @copyright http://code.google.com/p/phpresizer/
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
        'Path "%s" is not exists or not writtable';
    const EXC_FILE_CRASHED = 'File "%s" is crashed';
    const EXC_ENABLE_CACHE =
        'For "returnOnlyPath" option set "cache" options as TRUE';

    /**
     * Default cache image time to live in minutes
     *
     * @var int
     */
    const DEFAULT_CACHE_TTL = 10080; // 10080 minutes = 1 week
    const DEFAULT_RETURN_ONLY_PATH = false;
    const DEFAULT_CACHE = true;
    const DEFAULT_CACHE_BROWSER = true;
    const DEFAULT_CACHR_DIR  = '/tmp/resizerCache/';
	const DEFAULT_TMP_DIR  = '/tmp/';
	
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
     * @var bool
     */
    protected $_cacheBrowser = false;

    /**
     * @param array $options
     */
    public function __construct(array $inputOptions = array())
    {
    	$defaultOptions = array (
            'engine' => self::ENGINE_GD2,
            'cache' => self::DEFAULT_CACHE,
            'cacheBrowser' => self::DEFAULT_CACHE_BROWSER,
            'cacheDir' => self::DEFAULT_CACHR_DIR,
            'tmpDir' => self::DEFAULT_TMP_DIR);
    	
        $config = array_merge($defaultOptions, $inputOptions);

        $this->_useCache = (bool)$config['cache'];
        $this->_tmpDir = $config['tmpDir'];
        $this->_cacheDir = $config['cacheDir'];
        $this->_cacheBrowser = (bool)$config['cacheBrowser'];

        $this->_validateTmpDir();
        $this->_validateCacheDir();

        $this->_engine = $this->_createEngine($config['engine']);
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
        if ($this->_useCache && !is_writable($this->_cacheDir)) {
            $message = sprintf(self::EXC_CACHEDIR_NOT_EXISTS, $this->_cacheDir);
            throw new PhpResizer_Exception_Basic($message);
        }
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
     *
     * @param string $filename
     * @param array $options
     * @throws PhpResizer_Exception_Basic
     */
    public function resize($filename, 
    						array $options = array(),
    						$returnOnlyPath = self::DEFAULT_RETURN_ONLY_PATH)
    {
    	$this->_options = $options;
    	$this->_returnOnlyPath = (bool) $returnOnlyPath;
    	
        if ($this->_returnOnlyPath && !$this->_useCache)
        {
            throw new PhpResizer_Exception_Basic(self::EXC_ENABLE_CACHE);
        }
                
        if (!is_readable($filename)) {
            return $this->_return404();
        } else if (false === ($size = @getimagesize($filename))) {
            $message = sprintf(self::EXC_FILE_CRASHED, $filename);
            throw new PhpResizer_Exception_Basic($message);
        }

        if (!$this->_options) {
            $this->_returnImageOrPath($filename);
        }

        $this->_options += array(
            'path' => $filename,
			'cacheFile' => $this->_getCacheFileNamePath($filename),
            'size' => $size
        );

        if (!$this->_engine->resize($this->_options)) {
            $this->_return404();
        }

		chmod($this->_options['cacheFile'], 0777);
		return $this->_returnImageOrPath($this->_options['cacheFile']);
    }

    /**
     * generete CacheFileName and if cacheFile is exist and valid - return image
     * else return path to uncreated newcacheFile
     *
     * @param $path
     * @param $options
     * @return string
     */
    protected function _getCacheFileNamePath ($path)
    {
        $cacheFile = null;
        $options = $this->_options;
        
        if ($this->_useCache) {
        	
            $cacheFile = $this->generatePath($path);
            
            if (file_exists($cacheFile) && getimagesize($cacheFile) &&
                filemtime($cacheFile)>=filemtime($path)) {
                	return $this->_returnImageOrPath($cacheFile);

            } else if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }

        } else {
            $cacheFile = $this->_tmpDir . 'imageResizerTmpFile_'
                . uniqid() . '.' . $this->getExtensionOutputFile($path);
        }

        return $cacheFile;
    }

    /**
     * @param string $filename
     * @return string
     */
    public static function getExtensionOutputFile($filename)
    {
        $allowedExtenstions = array('png');
        $defaultExtension = 'jpg';
        $ext = self::getExtension($filename);
        //$ext = strtolower(substr($filename,-3));

        if (in_array($ext, $allowedExtenstions)) {
            return $ext;

        } else {
            return $defaultExtension;
        }
    }
    
    public static function getExtension($filename) {
    	$filenameArr = explode('.',$filename);
    	$ext = array_pop($filenameArr);
    	return strtolower($ext);
    }

    /**
     * @param string $path
     * @param array $options
     * @return string
     */
    protected function generatePath($path)
    {
        $hash = md5(serialize($this->_options).$path);
        $cacheFilePath = $this->_cacheDir . '/' . substr($hash, 0,1)
            . '/' . substr($hash, 1, 1) . '/' . substr($hash, 2) . '.'
            . $this->getExtensionOutputFile($path);

        if (!is_dir(dirname($cacheFilePath))){
        	$oldUmask = umask(0);
            mkdir(dirname($cacheFilePath),0777,true);
            umask($oldUmask);            
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

    	if (!$this->_useCache 
			&& isset($this->_options['cacheFile'])
			&& $this->_options['cacheFile']==$filename)
		{
            unlink($filename);
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
        if (!$this->_cacheBrowser) {
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
     * @param int $ttl in minutes
     * @return array
     */
    public function clearCache($ttl = self::DEFAULT_CACHE_TTL)
    {
        $ttl = (int) $ttl;
        $dir = escapeshellcmd($this->_cacheDir);
        $command = "find {$dir} \! -type d -amin +{$ttl} -exec  rm -v '{}' ';'";
        exec($command, $stringOutput);
        return $stringOutput;
    }
}