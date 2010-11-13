<?php
/**
 * PHPResizer
 *
 *
 * @package    PHPResizer
 * @copyright  http://phpresizer.org/
 * @author 	   niko
 * @version    1.0 beta
 */
/**
 * @author niko
 *
 */
class ResizerException extends Exception  {} 

/**
 * 
 * @author niko
 */
class Resizer {
	
	const ENGINE_GD2='gd2';
	const ENGINE_IMAGEMAGICK='imagemagic';
	const ENGINE_GRAPHIKSMAGICK='graphicsmagick';
	private $avalibleEngine = array (self::ENGINE_GD2,self::ENGINE_IMAGEMAGICK,self::ENGINE_GRAPHIKSMAGICK);
	
	/**
	 * initialization configuration
	 * @var array
	 */
	private $_defaultConfig = array (
		'engine'=>'gd2',
		'cache'=>true,
		'cacheBrowser'=>true,
		'cacheDir'=>'/tmp/resizerCache/',
		'tmpDir'=>'/tmp/'
	);
	private $_config;
	private $_returnOnlyPath = false;
	private $_checkETag;


	public function __construct(array $cnf=array()) {
		$this->_config=array_merge($this->_defaultConfig,$cnf);

		if (!is_dir($this->_config['tmpDir']) OR !is_writable($this->_config['tmpDir'])) {
			throw new ResizerException('path '.$this->_config['tmpDir'].' is not exist or nor writible');
		}

		if ($this->_config['cache'] 
			AND (!is_dir($this->_config['cacheDir'])
			OR !is_writable($this->_config['cacheDir']) 
			OR !is_executable($this->_config['cacheDir']))) {
			throw new ResizerException('path '.$this->_config['cacheDir'].' is not exist or nor writible or no executable');
		}
		
		require_once ('engine/'.$this->_config['engine'].'.php');
       
		$this->enginer = new $this->_config['engine']();
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
        	throw new ResizerException('file '.$path.' is crashed');
        }
        
		if (empty($options)) {
			$this->_returnImageOrPath($path);
		}
		
    	if (isset($options['returnOnlyPath']) AND $options['returnOnlyPath']=== true AND $this->_config['cache']) {
        		unset ($options['returnOnlyPath']);
    			$this->_returnOnlyPath = true; 
        }elseif(isset($options['returnOnlyPath']) AND $options['returnOnlyPath']=== true){
                throw new ResizerException('for returnOnlyPath turn cache TRUE');
        }

		$cacheFile = $this->_getCacheFileName($path,$options);

        
        if ($this->enginer->resize(
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

    	if ($this->_checkETag($filename)) {
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
	private function _checkETag ($filename) {
		if (!$this->_config['cacheBrowser']) {
			return false;
		}
		if (isset ($this->checkETag)) {
			return $this->checkETag;
		}
		if (isset($_SERVER['HTTP_IF_NONE_MATCH'])&& md5_file($filename)==$_SERVER['HTTP_IF_NONE_MATCH']) {
			$this->checkETag=true;
			return true;
		}else{
			$this->checkETag=false;
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