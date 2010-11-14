<?php
if (isset($_GET['file'])) {
	$file=$_GET['file'];
}else{
	$file='';
};

$options = array (
	'prev1'=>array(
		'width'=>50,
		'height'=>50,
		'aspect'=>false,
		'crop'=>90,
	),
	'prev2'=>array(
		'width'=>200,
		'height'=>200,
		'aspect'=>true,
		'crop'=>100,
	),
	'prev3'=>array(
		'width'=>600,
		'height'=>600,
		'aspect'=>true,
		'crop'=>100,
	),
	'prev4'=>array(
		'width'=>1000,
		'height'=>1000,
		'aspect'=>true,
		'crop'=>100,
	),
);

if (isset($_GET['type']) AND isset($options[$_GET['type']])) {
	$opt =  $options[$_GET['type']];
}else{
	$opt = array();
}

require '../sources/PhpResizer/Autoloader.php';
new PhpResizer_Autoloader(); 

try {
	$resizer = new PhpResizer_PhpResizer(array (
		'engine'=>PhpResizer_PhpResizer::ENGINE_GD2,
		'cacheDir'=>dirname(__FILE__).'/cache/',
		'cache'=>true,
		'cacheBrowser'=>true,
		)
	);
	$resizer->resize(dirname(__FILE__).'/'.$file, $opt);
}catch(Exception $e) {

}

