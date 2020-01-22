<?php 

  $f = fopen("/var/www/html/debug_state.txt", "r");
  $state = fgets($f);
  if(strpos($state, "1") !== false) {
  	exec("sudo systemctl stop ardudebug");
  	exec("sudo systemctl start track");
  } else {
  	exec("sudo systemctl stop track");
  	exec("sudo systemctl start ardudebug");
  }
  fclose($f);

?>
