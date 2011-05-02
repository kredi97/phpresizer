<?php

if (isset($_GET['file'])) {
	$file=rawurldecode($_GET['file']);
}else{
	$file='';
};

$options = array (
	'prev1'=>array(
		'width'=>50,
		'height'=>50,
		'aspect'=>false,
		'crop'=>90,
		'quality'=>100
	),
	'prev2'=>array(
		'width'=>130,
		'height'=>120,
		'aspect'=>true,
		'crop'=>100,
		'background'=>'fF0000'
	),
	'prev3'=>array(
		'width'=>150,
		'height'=>150,
		'aspect'=>true,
		'quality'=>25,
		'crop'=>60,
	),
);

if (isset($_GET['type']) AND isset($options[$_GET['type']])) {
	$opt =  $options[$_GET['type']];
}else{
	$opt = array();
}

require '../sources/PhpResizer/Autoloader.php';
new PhpResizer_Autoloader();

$engines=array(
	'im'=>PhpResizer_PhpResizer::ENGINE_IMAGEMAGICK,
	'gm'=>PhpResizer_PhpResizer::ENGINE_GRAPHIKSMAGICK,
	'gd'=>PhpResizer_PhpResizer::ENGINE_GD2);

if (isset($_GET['engine']) AND isset($engines[$_GET['engine']])) {
	$engine = $engines[$_GET['engine']];	
}


try {
	$resizer = new PhpResizer_PhpResizer(array (
		'engine'=>$engine,
		'cacheDir'=>dirname(__FILE__).'/cache/',
		'cache'=>false,
		'cacheBrowser'=>false,
		)
	);	
	$resizer->resize(dirname(__FILE__).'/../tests/PhpResizer/files/'.$file, $opt);
}catch(Exception $e) {	
	echo $e->getMessage();
}

