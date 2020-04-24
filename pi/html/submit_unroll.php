<?php 
  exec("sudo systemctl stop aprs");
  exec("sudo systemctl start track");
  $a = $_POST["command"]; 
  $f = fopen("/home/pi/n2yo/unroll.txt", "w");
  fwrite($f, $a."\n");

  $jsonString = file_get_contents('/home/pi/n2yo/config.json');
  $data = json_decode($jsonString, true);
  $data['general-state'] = "UNROLL";
  $newJsonString = json_encode($data);
  file_put_contents('/home/pi/n2yo/config.json', $newJsonString);
?>