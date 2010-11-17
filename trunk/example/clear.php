<?php
require '../sources/PhpResizer/Autoloader.php';
new PhpResizer_Autoloader(); ;

try {
	$resizer = new PhpResizer_PhpResizer(array('cacheDir'=>dirname(__FILE__).'/cache/'));
	$result = $resizer->clearCache(1);
}catch(Exception $e) {
	echo $e->getMessage();
}
?>

<html>
	<head>
	</head>
	<body>
	<h4>Was deleted <?php echo count($result); ?> file(s)</h4>
	<?php echo  implode('<br/>',$result); ?>
	</body>
</html>
