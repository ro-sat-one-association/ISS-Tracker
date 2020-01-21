<?php 

  $desc = $_POST["desc"]; 
  $key = $_POST["key"];
  $fqbn = $_POST["fqbn"];

  $f = fopen("/home/pi/n2yo/serial-desc.txt", "w");
  fwrite($f, $desc."\n");
  fclose($f);

  $f = fopen("/home/pi/n2yo/n2yo-key.txt", "w");
  fwrite($f, $key."\n");
  fclose($f);

  $f = fopen("/home/pi/n2yo/fqbn.txt", "w");
  fwrite($f, $fqbn."\n");
  fclose($f);

  exec("sudo systemctl restart track");

?>

<html>
  <head>
   <meta http-equiv = "refresh" content = "2; url = config.php"/>
  </head>
</html>
