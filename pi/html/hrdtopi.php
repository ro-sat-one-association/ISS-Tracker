<?php
 $a = $_GET["data"];


 $f = fopen('/home/pi/n2yo/hrd.txt', 'w');
 fwrite($f, $a);
 echo $a;
?>
