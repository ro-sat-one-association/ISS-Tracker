<?php 

  $a= $_GET["a"]; 

  $f = fopen("/home/pi/n2yo/unroll.txt", "w");
  fwrite($f, $a);
  fclose($f);

  exec('sudo systemctl stop track');
  exec('sudo systemctl stop unghi');
  exec('sudo systemctl restart unroll');
?>

<html>
  <head>
   <meta http-equiv = "refresh" content = "2; url = unghiuri.php"/>
  </head>
</html>

