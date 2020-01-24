<?php 

  $f = fopen("/var/www/html/debug_state.txt", "r");
  $state = fgets($f);
  fclose($f);

  echo $state;

  $f = fopen("/var/www/html/debug_state.txt", "w");

  if(strpos($state, '1') !== false) {
  	exec("sudo systemctl stop ardudebug");
  	exec("sudo systemctl start track");
        fwrite($f, "0"); 
        echo "am oprit debug";
  } else {
  	exec("sudo systemctl stop track");
  	exec("sudo systemctl start ardudebug");
        fwrite($f, "1"); 
        echo "am pornit debug";
  }
  fclose($f);

?>
