<?php
 $x = "<html><head><meta http-equiv=\"refresh\" content=\"2;url=http://";
 $a = $_GET["a"];
 $y = "\"/></head></head>";

 $f = fopen('/var/www/html/track', 'w');
 fwrite($f, $x.$a.$y);
 echo $x.$a.$y;
?>