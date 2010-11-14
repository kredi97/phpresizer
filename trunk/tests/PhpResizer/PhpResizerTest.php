<?php
/**
 * @version $Revision$
 * @category PhpResizerTest
 * @package PhpResizerTest
 * @author $Author$ $Date$
 * @license New BSD license
 * @copyright http://phpresizer.org/
 */

/**
 *
 */
class PhpResizer_PhpResizerTest extends PHPUnit_Framework_TestCase
{
    protected $_cacheDir;

    protected $_testFile;

    /**
     *
     */
    public function setUp()
    {
        $this->_cacheDir = dirname(__FILE__).'/cache/';
        $this->_testFile = dirname(__FILE__).'/ResizerTestFile/';
        $this->_cleanCacheDir();
    }

    protected function _cleanCacheDir()
    {
        $command = 'rm -rf '.self::$cacheDir.'*';
        exec ($command);
    }

    /**
     *
     */
    public function testResize()
    {
        $normalFiles = array ('normal.bmp','normal.jpg','normalCMYK.jpg','normal.gif');
        foreach ($normalFiles as $file) {
            foreach (array(Resizer::ENGINE_GRAPHIKSMAGICK, Resizer::ENGINE_IMAGEMAGICK, Resizer::ENGINE_GD2) as $engine) {

                $resizer = new Resizer(array(
                    'engine'=>$engine,
                    'cacheDir'=>self::$cacheDir
                    )
                );

                $opt = array(
                    'width'=>100,
                    'height'=>150,
                    'crop'=>75,
                    'aspect'=>false,
                    'returnOnlyPath'=>true
                );

                $fileInfoExtension = strtolower(pathinfo($file,PATHINFO_EXTENSION));
                $fileInfoExtensionFilter = (in_array($fileInfoExtension,array('png')))?$fileInfoExtension:'jpg';

                $cacheFile = $resizer->generatePath(self::$testFile.$file,$opt);

                $this->assertEquals($fileInfoExtensionFilter,pathinfo($cacheFile,PATHINFO_EXTENSION),'расширение исходного и ужатого файла (движок:'.$engine.') (файл:'.$file.')');

                try {
                    $resizer->resize(self::$testFile.$file,$opt);
                }catch (ResizerException $e) {
                    if ($engine == Resizer::ENGINE_GD2 and $fileInfoExtension =='bmp') {
                        continue;
                    }
                    $this->assertTrue(false, 'Не перехваченное исключение (движок:'.$engine.') (файл:'.$file.')');
                }

                $this->assertTrue(file_exists($cacheFile), 'Наличие файла в кэше (движок:'.$engine.') (файл:'.$file.')');

                $getimagesize = getimagesize($cacheFile);

                $this->assertEquals($getimagesize[0],$opt['width'],'ширина файла (движок:'.$engine.') (файл:'.$file.')');
                $this->assertTrue($opt['height']-1<=$getimagesize[1] AND $getimagesize[1]<=$opt['height']+1 ,'высота файла (движок:'.$engine.') (файл:'.$file.')');
            }
        }
    }
}