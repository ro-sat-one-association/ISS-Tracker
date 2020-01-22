<?php 

  $cmd = $_POST["cmd"]; 

  $f = fopen("/home/pi/debug_command.txt", "w");
  fwrite($f, $cmd);
  fclose($f);

?>
