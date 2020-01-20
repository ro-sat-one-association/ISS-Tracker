<!DOCTYPE html>
<html>
<head>
  <title>Upload Arduino</title>
</head>
<body>
  <form enctype="multipart/form-data" action="upload.php" method="POST">
    <p>Incarca sketchul ca folder intr-un *.zip</p>
    <input type="file" name="uploaded_file"></input><br />
    <input type="text" name="port" value="/dev/ttyUSB0"><br />
    <input type="text" name="fqbn" value="arduino:avr:pro:cpu=8MHzatmega328"><br />
    <input type="submit" value="Upload"></input>
  </form>
</body>
</html>
<?php
  if(!empty($_FILES['uploaded_file']))
  {
    $path = "/home/pi/upload/";
    $path = $path . "a.zip";

    if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) {
      echo "The file ".  basename( $_FILES['uploaded_file']['name']). 
      " has been uploaded";
      echo "Incep sa incarc, nu stiu daca merge sigur, dar na";
      exec("sudo sh /home/pi/upload_arduino.sh ". $_POST['fqbn'] . " " . $_POST['port']);

    } else{
        echo "There was an error uploading the file, please try again!";
    }
  }
?>