<?php 

  $azi= $_POST["azi"]; 
  $ele = $_POST["ele"];

  $f = fopen("/home/pi/n2yo/unghiuri.txt", "w");
  fwrite($f, $azi."\n".$ele."\n");
  fclose($f);
 
  exec('sudo systemctl stop track');
  exec('sudo systemctl stop unroll');
  exec('sudo systemctl restart unghi');

?>

<html>
  <head>
   <meta http-equiv = "refresh" content = "2; url = unghiuri.php"/>
  </head>
</html>

