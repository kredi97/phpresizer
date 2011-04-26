<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<style>
		* {padding: 0; margin: 0;}
		body {padding: 20px; background-color: #f0f0f0;}
		img {padding: 0px; margin: 0}
		table {width: 50%; margin: 0 auto;}
		table td{padding: 5px; text-align: center; background-color: #fef}
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

<tr><td colspan=3><?php echo $photo; ?></td></td>
<tr>	
	<td><img src="<?php echo $photo?>?type=prev1" /></td>
	<td><img src="<?php echo $photo?>?type=prev2" /></td>
	<td><img src="<?php echo $photo?>?type=prev3" /></td>	
</tr>
<?php }?>
	
</body>
</html>
