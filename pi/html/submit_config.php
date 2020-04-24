<?php 
$jsonString = file_get_contents('/home/pi/n2yo/config.json');
$data = json_decode($jsonString, true);
if (isset($_POST["desc"])) {
    $data['arduino']['serial-descriptor'] = trim($_POST["desc"]);
}
if (isset($_POST["key"])) {
    $data['observer']['n2yo-key'] = trim($_POST["key"]);
}
if (isset($_POST["fqbn"])) {
    $data['arduino']['fqbn'] = trim($_POST["fqbn"]);
}
if (isset($_POST["sat"])) {
    $data['sat']['NORAD'] = trim($_POST["sat"]);
    exec("sudo systemctl stop aprs");
    exec("sudo systemctl start track");
}
if (isset($_POST["lat"])) {
    $data['observer']['latitude'] = trim($_POST["lat"]);
}
if (isset($_POST["lon"])) {
    $data['observer']['longitude'] = trim($_POST["lon"]);
}
if (isset($_POST["alt"])) {
    $data['observer']['altitude'] = trim($_POST["alt"]);
}
if (isset($_POST["datestr"])) {
    $data['sat']['customtime'] = trim($_POST["datestr"]);
}
if (isset($_POST["azi"])) {
    $data['custom-angles']['azimuth'] = trim($_POST["azi"]);
    exec("sudo systemctl stop aprs");
    exec("sudo systemctl start track");
}
if (isset($_POST["ele"])) {
    $data['custom-angles']['elevation'] = trim($_POST["ele"]);
}
if (isset($_POST["state"])) {
    $data['general-state'] = trim($_POST["state"]);
}
if (isset($_POST["tle1"])) {
    $data['sat']['tle1'] = trim($_POST["tle1"]);
}
if (isset($_POST["tle2"])) {
    $data['sat']['tle2'] = trim($_POST["tle2"]);
}
if (isset($_POST["deltaazimuth"])) {
    $data['custom-angles']['delta-azimuth'] = trim($_POST["deltaazimuth"]);
}
if (isset($_POST["deltaelevation"])) {
    $data['custom-angles']['delta-elevation'] = trim($_POST["deltaelevation"]);
}
if (isset($_POST["callsign"])) {
    $data['target']['callsign'] = trim($_POST["callsign"]);
    exec('sudo systemctl stop track');
    exec('sudo systemctl start aprs');
}
if (isset($_POST["autostart"])) {
    if ($_POST["autostart"] == "0") {
        $data['autostart'] = false;
        exec("sudo systemctl disable track");
    }
    else {
        $data['autostart'] = true;
        exec("sudo systemctl enable track");
    }
}
$newJsonString = json_encode($data);
file_put_contents('/home/pi/n2yo/config.json', $newJsonString);

?>
