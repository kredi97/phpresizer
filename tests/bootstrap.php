<?php
/**
 * @version $Revision$
 * @category PhpResizerTest
 * @package PhpResizerTest
 * @author $Author$ $Date$
 * @license New BSD license
 * @copyright http://code.google.com/p/phpresizer/
 */

define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/sources'),
    get_include_path(),
)));

require 'PhpResizer/Autoloader.php';
new PhpResizer_Autoloader();
