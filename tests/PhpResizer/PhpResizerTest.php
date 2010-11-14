<?php
/**
 * Test
 *
 * @package Test
 * @subpackage PhpUnit
 * @author niko
 * @filesource
 *
 */
/**
 * Родительский класс для все классов тетсирования Stickr
 */
class Resizer_TestCase extends PHPUnit_Framework_TestCase {
    static public $cacheDir;
    static public $testFile;

    /**
     * Метод выполняется перед всеми тестами в текущем классе
     */
    public static function setUpBeforeClass(){
        self::$cacheDir=dirname(__FILE__).'/cache/';
        self::$testFile=dirname(__FILE__).'/ResizerTestFile/';
    }

    /**
     * Метод выполняется после всех тестов в текущем классе
     */
    public static function tearDownAfterClass() {
    }

    /**
     * Метод выполняется перед каждым тестируемым методом
     */
    public function setUp (){
        $command = 'rm -rf '.self::$cacheDir.'*';
        exec ($command);
    }

    /**
     * Метод выполняется после каждого тестируемого метода
     */
    protected function tearDown(){
        $command = 'rm -rf '.self::$cacheDir.'*';
        //exec ($command);
    }

    /**
     *
     */
    public function testResize1() {
        $normalFiles = array ('normal.bmp','normal.jpg','normalCMYK.jpg','normal.gif');
        foreach ($normalFiles as $file) {
            foreach (array(Resizer::ENGINE_GRAPHIKSMAGICK,Resizer::ENGINE_IMAGEMAGICK,Resizer::ENGINE_GD2) as $engine) {

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

                //echo $file.' -- '.$cacheFile."\n";
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



    /**
     *
     * @param string $url
     * @param array $options
     * @return array
     */

    protected function sendGET ($url,array $options=array()) {
         $defaultOptions = array(
             'session' => false
         );
        extract (array_merge($defaultOptions,$options));

        $uagent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // возвращает веб-страницу
        curl_setopt($ch, CURLOPT_HEADER, 0);           // не возвращает заголовки
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // переходит по редиректам
        curl_setopt($ch, CURLOPT_ENCODING, "");        // обрабатывает все кодировки
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);  // таймаут соединения
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);         // таймаут ответа
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);       // останавливаться после 10-ого редиректа
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);

        if ($session) {
            $cook_file=DOCROOT.'test/data/cook/cookie_'.$session;
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cook_file);
            curl_setopt($ch, CURLOPT_COOKIEFILE,$cook_file);
        }

        $header  = curl_getinfo( $ch );
        $header['errno']   = curl_errno( $ch );
        $header['errmsg']  = curl_error( $ch );
        $header['content'] = curl_exec( $ch );
        curl_close( $ch );
        return $header;
    }

    /**
     *
     * @param string $url
     * @param array $data
     * @param array $options
     * @return array
     */
     protected function sendPOST ($url, $data, array $options=array()) {
        $defaultOptions = array(
            'session' => false,
            'file'=>false
        );
        extract (array_merge($defaultOptions,$options));

        if ($file) {
            $file = DOCROOT.'test/data/'.$file;
            $data['file']='@'.$file;
        }

        $uagent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8";

        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   // возвращает веб-страницу
        curl_setopt($ch, CURLOPT_HEADER, 0);           // не возвращает заголовки
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);   // переходит по редиректам
        curl_setopt($ch, CURLOPT_ENCODING, "");        // обрабатывает все кодировки
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);  // таймаут соединения
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);         // таймаут ответа
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);       // останавливаться после 10-ого редиректа
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($session) {
            $cook_file=DOCROOT.'test/data/cook/cookie_'.$session;
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cook_file);
            curl_setopt($ch, CURLOPT_COOKIEFILE,$cook_file);
        }

        $header  = curl_getinfo($ch);
        $header['errno']   = curl_errno($ch);
        $header['errmsg']  = curl_error($ch);
        $header['content'] = curl_exec($ch);
        curl_close( $ch );
        return $header;
    }
}
?>
