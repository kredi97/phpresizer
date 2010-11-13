<?php
/**
 * Запуск всех тестов Libraries
 *
 * @package Test
 * @subpackage PhpUnit
 * @author niko
 * @filesource
 *
 */

require_once 'PHPUnit/Framework.php';
require_once 'Resizer.php';


/**
 * Запуск всех тестов Libraries
 *
 */



class AllTests
{
    public static function suite()   {
        $suite = new PHPUnit_Framework_TestSuite('ResizerSuiteTest');
        $suite->addTestSuite('Resizer_Test');
        return $suite;
    }
}
?>