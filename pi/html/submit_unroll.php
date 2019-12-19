<?php 

  $a= $_GET["a"]; 
  $f = fopen("/home/pi/n2yo/unroll.txt", "w");
  fwrite($f, $a);
  fclose($f);
  $f = fopen("/home/pi/n2yo/state.txt", "w");
  fwrite($f, "UNROLL");
  fclose($f);
?>

<html>
  <head>
   <meta http-equiv = "refresh" content = "2; url = unghiuri.php"/>
  </head>
</html>

