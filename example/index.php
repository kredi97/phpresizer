<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style>
		* {padding: 0; margin: 0;}
		body {padding: 20px; background-color: #f0f0f0;}
		img {padding: 0px; margin: 0}
		table {width: 50%; margin: 0 auto;}
		table td{padding: 5px; text-align: center; background-color: #777}
	</style>
	<title>PhpResizer - example</title>
</head>
<body>

<?php 
$directoryIterator = new DirectoryIterator (dirname(__FILE__).'/../tests/PhpResizer/files');
$photos=array();
foreach  ($directoryIterator as $item)  {
	if ($item->isFile())  {
		$photos[]=$item->getFileName();
	}
}
?>

<table>
<?php foreach ($photos as $photo) { ?>

<tr><td colspan=9><?php echo $photo; ?></td></td>
<tr>
<?php foreach (array('prev1','prev2','prev3') as $type) {?>
	<?php foreach (array('im','gm','gd') as $engine) {?>
		<td>
		<?php echo $engine?>
			<img src="<?php echo $photo?>?type=<?php echo $type?>&engine=<?php echo $engine?>" />
		</td>
	<?php }?>
<?php }?>	
</tr>
<?php }?>
	
</body>
</html>
