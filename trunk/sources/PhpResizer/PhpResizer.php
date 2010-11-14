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

    const EXC_CACHEDIR_NOT_EXISTS = 'Path "%s" is not exists or not writtable or not executable';

    /**
     * initialization configuration
     * @var array
     */
    private $_config = array (
        'engine' => self::ENGINE_GD2,
        'cache' => true,
        'cacheBrowser' => true,
        'cacheDir' => '/tmp/resizerCache/',
        'tmpDir' => '/tmp/'
    );
    private $_returnOnlyPath = false;
    private $_checkEtag;

    /**
     * @var PhpResizer_Engine_EngineAbstract
     */
    protected $_engine;


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
     * @param string $path
     * @param array $options
     */
    public function resize($path, array $options = array()) {
        if (!file_exists($path)) {
            $this->_return404($path);
        }elseif (!$size = @getimagesize($path)) {
            throw new PhpResizer_PhpResizerException('file '.$path.' is crashed');
        }

        if (empty($options)) {
            $this->_returnImageOrPath($path);
        }

        if (isset($options['returnOnlyPath']) AND $options['returnOnlyPath']=== true AND $this->_config['cache']) {
                unset ($options['returnOnlyPath']);
                $this->_returnOnlyPath = true;
        }elseif(isset($options['returnOnlyPath']) AND $options['returnOnlyPath']=== true){
                throw new PhpResizer_PhpResizerException('for returnOnlyPath turn cache TRUE');
        }

        $cacheFile = $this->_getCacheFileName($path,$options);


        if ($this->_engine->resize(
            $options+=array(
            'path'=>$path,
            'cacheFile'=>$cacheFile,
            'size'=>$size))!==true) {
            $this->_return404();
        }


        if (!$this->_config['cache']){
            $this->_returnImageOrPath($cacheFile,$options);
            unlink($cacheFile);
            return;
        }else{
            return $this->_returnImageOrPath($cacheFile,$options);
        }
    }


    /**
     *
     * return image if cacheFile is valid and exist OR return path to newcacheFile
     * @param $options
     * @param $path
     */
    private function _getCacheFileName ($path,$options) {
        $cacheFile=null;

        if ($this->_config['cache']) {
            $cacheFile= $this->generatePath($path,$options);
            if (file_exists($cacheFile) &&
                getimagesize($cacheFile) &&
                filemtime($cacheFile)>=filemtime($path)) {
                    return $this->_returnImageOrPath($cacheFile,$options);
            }elseif (file_exists($cacheFile)){
                unlink($cacheFile);
            }
        }else{
            $cacheFile=$this->_config['tmpDir'].'/imageResizerTmpFile_'.md5(microtime().mt_rand(100000,999999)).$this->getExtensionFilter($path);
        }
        return $cacheFile;
    }

    private function getExtensionFilter ($path) {
        $fileInfoExtension = strtolower(pathinfo($path,PATHINFO_EXTENSION));
        return (in_array($fileInfoExtension,array('png')))?'.'.$fileInfoExtension:'.jpg';
    }

    public function generatePath ($path,array $options){

        if (isset($options['returnOnlyPath'])) {
            unset ($options['returnOnlyPath']);
        }
//    	$cacheFilePath = $this->_config['cacheDir'].implode('_',$options).'/'.str_replace('/','-',$path).$this->getExtensionFilter($path);
//    	if(!is_dir(dirname($cacheFilePath))){
//            mkdir(dirname($cacheFilePath));
//        }
//    	return $cacheFilePath;
//
//    	$cacheFilePath = $this->_config['cacheDir'].'/'.str_replace('/','-',$path).$this->getExtensionFilter($path);
//    	return $cacheFilePath;

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
    private function _returnImageOrPath ($filename,array $options=array()) {

        if ($this->_returnOnlyPath) {
                return $filename;
        }

        if ($this->_checkEtag($filename)) {
            header("HTTP/1.1 304 Not Modified");
        }else{

            header("Content-type: image/jpeg");
            header("Content-Length: ".@filesize($filename));
            header('ETag: '.md5_file($filename));
            readfile ($filename); exit();
        }
    }


    private function _return404 ($fileName='') {
        header('HTTP/1.1 404 Not Found');
        echo 'file '.$fileName.' not found';
          exit;
    }

    /**
     *
     * @param string $filename absolute path to image-file
     * @return boolean
     */
    private function _checkEtag($filename) {
        if (!$this->_config['cacheBrowser']) {
            return false;
        }
        if (isset ($this->_checkEtag)) {
            return $this->_checkEtag;
        }
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])&& md5_file($filename)==$_SERVER['HTTP_IF_NONE_MATCH']) {
            $this->_checkEtag = true;
            return true;
        }else{
            $this->_checkEtag = false;
            return false;
        }
    }
    /**
     *
     * @param integer $timeMinuts
     */
    public function clearCache ($timeMinuts=10080) {
        $timeMinuts=(int)$timeMinuts;
        $command = "find ".$this->_config['cacheDir']." \! -type d -amin +".$timeMinuts." -exec  rm -v '{}' ';'";
        ob_start();
            passthru($command);
        $result = ob_get_clean();
        return $result;
    }
}