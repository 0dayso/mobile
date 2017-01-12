<?php
/**
 * 入口文件
 * Some rights reserved：www.thinkcmf.com
 */

//header('Location:http://g.7gu.cn/index.php?g=api&m=Mobile&a=phonemobile');

header('Content-type:text/json');
if(isset($_REQUEST['row'])){
	$row=$_REQUEST['row'];	
}else{
	$row=1;
}
echo file_get_contents("http://g.7gu.cn/index.php?g=api&m=Mobilebook&a=phonemobile&row=".$row);
exit();
?>

