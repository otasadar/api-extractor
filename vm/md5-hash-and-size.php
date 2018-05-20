<?php 
$url=$_POST['url'];
$output = exec("python md5-hash-and-size.py '$url'");

echo $output ;
?>