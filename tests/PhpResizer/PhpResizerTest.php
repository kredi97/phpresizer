<?php
/**
 * @version $Revision$
 * @category PhpResizerTest
 * @package PhpResizerTest
 * @author $Author$ $Date$
 * @license New BSD license
 * @copyright http://code.google.com/p/phpresizer/
 */

/**
 *
 */
class PhpResizer_PhpResizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $_filesDir;

    /**
     * @var string
     */
    protected static $_cacheDir;
    
    /**
     * @var array
     */
    protected static $testPhoto;
    
    /**
     *
     */
    public static function setUpBeforeClass()
    {
        $path = dirname(__FILE__);
        self::$_filesDir = $path . DIRECTORY_SEPARATOR . 'files';
        self::$_cacheDir = $path . DIRECTORY_SEPARATOR . 'cache';
    }

	/**
	 * 
	 */
    public static function tearDownAfterClass() 
    {
		self::_cleanDir(self::$_cacheDir);
    }
    
    /**
     * Delete all files from directory
     *
     * @param string $dir
     */
    protected static function _cleanDir($dir)
    {
        $command = "rm -rf {$dir}/*";
        exec($command);
    }

    /**
     * @return array
     */
    public function providerFiles()
    {	       
      	self::setUpBeforeClass();
      	      	
      	$directoryIterator = new DirectoryIterator (self::$_filesDir);
		$testPhoto=array();
		foreach  ($directoryIterator as $item)  {
			if ($item->isFile()) {
				$testPhoto[]=array($item->getFileName(), $item->getFileName()=== 'bad_image.jpg');
			}
		}
		
		return $testPhoto;
    }


    /**
     * @dataProvider providerFiles
     * @knownException PhpResizer_Exception_IncorrectExtension
     */
    public function testResize($file, $isBadFile)
    {

        $options = array(
            'width'  => 100,
            'height' => 150,
            'crop'   => 75,
            'aspect' => false
        );

        $filename = self::$_filesDir . DIRECTORY_SEPARATOR . $file;
        
        foreach ($this->_getAvailableEngines() as $engine) 
        {
        	$phpReszierOptions = array(
            	'cacheDir' => self::$_cacheDir,
				'engine'=>$engine
        	);
        	
            try {
            	
            	$phpResizerObj= new PhpResizer_PhpResizer($phpReszierOptions);            	
            	$cacheFile = $phpResizerObj->resize($filename, $options, true);
            	
            }catch(PhpResizer_Exception_IncorrectExtension $e){
            	echo 'engine:' . $engine . ' - ' . $e->getMessage().PHP_EOL;
            	return;
            }catch(PhpResizer_Exception_Basic $e){
            	$this->assertTrue($isBadFile
                , 'испорченный файл не вызвал исключение (движок:'.$engine.') (файл:'.$filename.')');
                return; 
            }
           
            $this->assertTrue(file_exists($cacheFile)
                , 'Наличие файла в кэше (движок:'.$engine.') (файл:'.$filename.')');
                
            list($width, $height) = getimagesize($cacheFile);
                            
            $this->assertTrue($options['width'] - 1 <= $width && $width <= $options['width'] + 1
                , 'ширина изображения (движок:'.$engine.') (файл:'.$cacheFile.')');
            
			$this->assertTrue($options['height'] - 1 <= $height && $height <= $options['height'] + 1
                ,'высота изображения (движок:'.$engine.') (файл:'.$cacheFile.')');
        }
    }

    /**
     * @return array
     */
    private function _getAvailableEngines()
    {
        $engines = array();
        $reflection = new ReflectionClass('PhpResizer_PhpResizer');
        foreach ($reflection->getConstants() as $const=>$value) {
            if (0 === strpos($const, 'ENGINE')) {
                $engines[] = $value;
            }
        }
        
        return $engines;
    }
}