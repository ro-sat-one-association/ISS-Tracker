<?php 

  $f = fopen("/var/www/html/debug_state.txt", "w+");
  $state = fgets($f);
  if(strpos($state, "1") !== false) {
  	exec("sudo systemctl stop ardudebug");
  	exec("sudo systemctl start track");
    fwrite($f, "0"); 
  } else {
  	exec("sudo systemctl stop track");
  	exec("sudo systemctl start ardudebug");
    fwrite($f, "1"); 
  }
  fclose($f);

?>
