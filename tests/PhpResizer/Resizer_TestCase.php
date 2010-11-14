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
