<?php

$jsonString = file_get_contents('/home/pi/n2yo/config.json');
$data = json_decode($jsonString, true);

if(isset($_POST["desc"])){
    $data['arduino']['serial-descriptor'] = $_POST["desc"];
}

if(isset($_POST["key"])){
	$data['observer']['n2yo-key'] = $_POST["key"];
}   

if(isset($_POST["fqbn"])){
	$data['arduino']['fqbn'] = $_POST["fqbn"];
}

if(isset($_POST["sat"])){
	$data['sat']['NORAD'] = $_POST["sat"];
}

if(isset($_POST["lat"])){
	$data['observer']['latitude'] = floatval($_POST["lat"]);
}

if(isset($_POST["lon"])){
	$data['observer']['longitude'] = floatval($_POST["lon"]);
}

if(isset($_POST["alt"])){
	$data['observer']['altitude'] = intval($_POST["alt"]);
}

if(isset($_POST["datestr"])){
	$data['sat']['customtime'] = $_POST["datestr"];
}

if(isset($_POST["azi"])){
	$data['custom-angles']['azimuth'] = $_POST["azi"];
}

if(isset($_POST["ele"])){
	$data['custom-angles']['elevation'] = $_POST["ele"];
}

if(isset($_POST["state"])){
	$data['general-state'] = $_POST["state"];
}


$newJsonString = json_encode($data);
file_put_contents('/home/pi/n2yo/config.json', $newJsonString);

?>
