<?php 

  $azi= $_POST["azi"]; 
  $ele = $_POST["ele"];

  $f = fopen("/home/pi/n2yo/unghiuri.txt", "w");
  fwrite($f, $azi."\n".$ele."\n");
  fclose($f);
  $f = fopen("/home/pi/n2yo/state.txt", "w");
  fwrite($f, "UNGHI");
  fclose($f);

?>

<html>
  <head>
   <meta http-equiv = "refresh" content = "2; url = unghiuri.php"/>
  </head>
</html>

