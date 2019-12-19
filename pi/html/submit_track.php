<?php 

  $sat = $_POST["sat"]; 
  $lat = $_POST["lat"];
  $lon = $_POST["lon"];
  $alt = $_POST["alt"];

  $f = fopen("/home/pi/n2yo/config.txt", "w");
  fwrite($f, $sat."\n".$lat."\n".$lon."\n".$alt."\n");
  fclose($f);
  $f = fopen("/home/pi/n2yo/state.txt", "w");
  fwrite($f, "TRACK");
  fclose($f);

?>

<html>
  <head>
   <meta http-equiv = "refresh" content = "2; url = track.php"/>
  </head>
</html>
