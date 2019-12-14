<?php 

  $sat     = $_POST["sat"]; 
  $lat     = $_POST["lat"];
  $lon     = $_POST["lon"];
  $alt     = $_POST["alt"];
  $datestr = $_POST["datestr"];
  $f = fopen("/home/pi/n2yo/config.txt", "w");
  fwrite($f, $sat."\n".$lat."\n".$lon."\n".$alt."\n");
  fclose($f);
  $f = fopen("/home/pi/n2yo/customtime.txt", "w");
  fwrite($f, $datestr."\n");
  fclose($f);
  exec('sudo systemctl restart customtime');
?>

<html>
  <head>
   <meta http-equiv = "refresh" content = "2; url = customtime.php"/>
  </head>
</html>
