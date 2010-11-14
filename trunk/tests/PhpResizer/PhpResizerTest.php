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
    /**
     * @var string
     */
    protected $_filesDir;

    /**
     * @var PhpResizer_PhpResizer
     */
    protected $_fixture;

    /**
     *
     */
    public function setUp()
    {
        $path = dirname(__FILE__);
        $this->_filesDir = $path . DIRECTORY_SEPARATOR . 'files';

        $cacheDir = $path . DIRECTORY_SEPARATOR . 'cache';
        $this->_cleanDir($cacheDir);

        $options = array(
            'cacheDir' => $cacheDir,
        );
        $this->_fixture = new PhpResizer_PhpResizer($options);
    }

    /**
     * Delete all files from directory
     *
     * @param string $dir
     */
    protected function _cleanDir($dir)
    {
        $command = "rm -rf {$dir}/*";
        exec($command);
    }

    /**
     * @return array
     */
    public function providerFiles()
    {
        return array('normal.bmp', 'normal.jpg', 'normalCMYK.jpg'
            , 'normal.gif');
    }

    /**
     * @dataProvider providerFiles
     * @knownException PhpResizer_Exception_ProcessBmpWithGd2
     */
    public function testResize($file)
    {
        $options = array(
            'width'  => 100,
            'height' => 150,
            'crop'   => 75,
            'aspect' => false,
            'returnOnlyPath' => true,
        );

        $ext = $this->_fixture->getExtension($file);
        $filename = $this->_filesDir . DIRECTORY_SEPARATOR . $file;
        $cache = $this->_fixture->generatePath($filename, $options);

        foreach ($this->_getAvailableEngines() as $engine) {
            $this->_fixture->useEngine($engine);
            $this->_fixture->resize($filename, $options);

            list($width, $height) = getimagesize($cache);

            $this->assertTrue(file_exists($cache)
                , 'Наличие файла в кэше (движок:'.$engine.') (файл:'.$filename.')');
            $this->assertEquals($width, $options['width']
                , 'ширина файла (движок:'.$engine.') (файл:'.$file.')');
            $this->assertEquals($height, $options['height']
                ,'высота файла (движок:'.$engine.') (файл:'.$file.')');
        }
    }

    /**
     * @return array
     */
    private function _getAvailableEngines()
    {
        $engines = array();
        $reflection = new ReflectionClass($this->_fixture);
        foreach ($reflection->getConstants() as $const) {
            if (0 === strpos($const, 'ENGINE')) {
                $engines[] = $const;
            }
        }
        return $engines;
    }
}