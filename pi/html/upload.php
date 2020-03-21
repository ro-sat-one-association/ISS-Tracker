<!--
=========================================================
* Material Dashboard Dark Edition - v2.1.0
=========================================================

* Product Page: https://www.creative-tim.com/product/material-dashboard-dark
* Copyright 2019 Creative Tim (http://www.creative-tim.com)

* Coded by www.creative-tim.com

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
      <link rel="icon" type="image/png" href="assets/img/favicon.png">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>Arduino Upload</title>
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
        <!--     Fonts and icons     -->
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
          <!-- CSS Files -->
        <link href="https://cdn.jsdelivr.net/npm/material-dashboard@2.1.0/assets/css/material-dashboard.min.css" rel="stylesheet" >
        <link href="assets/css/dark-edition.css" rel="stylesheet" >
        <link href="assets/css/scroll.css" rel="stylesheet">
        <script src="assets/js/custom/options.js"></script>
    </head>
    <body id = "body" class="dark-edition">
    <script>refreshMode();</script>
              <div class="wrapper ">
                <div id = "sidebar" class="sidebar" data-color="danger" data-background-color="black" data-image="assets/img/sidebar.jpg">
                  <div class="sidebar-wrapper">
                    <ul class="nav">
                      <li class="nav-item">
                        <a class="nav-link" href="./track.php">
                          <i class="material-icons">explore</i>
                          <p>Urmărește Satelit</p>
                        </a>
                      </li>
                      <li class="nav-item ">
                        <a class="nav-link" href="./customtime.php">
                          <i class="material-icons">watch_later</i>
                          <p>Timp modificat</p>
                        </a>
                      </li>
                      <li class="nav-item ">
                        <a class="nav-link" href="./unghiuri.php">
                          <i class="material-icons">cached</i>
                          <p>Dezcâlcește/Unghiuri</p>
                        </a>
                      </li>
                      <li class="nav-item active">
                        <a class="nav-link" href="./upload.php">
                          <i class="material-icons">cloud_upload</i>
                          <p>Arduino Upload</p>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="./config.php">
                          <i class="material-icons">settings_applications</i>
                          <p>Configurare</p>
                        </a>
                      </li>
                      <li class="nav-item ">
                        <a class="nav-link" onclick="changeMode()">
                          <i id="mode-button" class="material-icons"></i>
                          <p>Schimbă modul</p>
                        </a>
                      </li>
                      <!-- <li class="nav-item active-pro "><a class="nav-link" href="./upgrade.html"><i class="material-icons">unarchive</i><p>Upgrade to PRO</p></a></li> -->
                    </ul>
                  </div>
                </div>

                <div class="main-panel">
                  <!-- Navbar -->
                  <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top " id="navigation-example">
                    <div class="container-fluid">
                      <div class="navbar-wrapper">
                        <span id="alerta_timp"></span>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation" data-target="#navigation-example">
                          <span class="sr-only">Toggle navigation</span>
                          <span class="navbar-toggler-icon icon-bar"></span>
                          <span class="navbar-toggler-icon icon-bar"></span>
                          <span class="navbar-toggler-icon icon-bar"></span>
                        </button>
                      </div>
                    </div>
                  </nav>

                  <div class="content">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-sm">
                          <div class="card ">
                            <div class="card-body">
                              <p class="card-text">
                                <form enctype="multipart/form-data" id="fileform" action = "#" method="POST">
                                  <div class="col-sm">
                                    <div class="row">
                                      <p>Incarca sketchul ca folder intr-un *.zip</p>
                                    </div>
                                    <div class="row">
                                      <input style="width:100%;"  type="file" class="btn btn-default" name="uploaded_file">
                                      <br />
                                    </div>
                                    <div class="row">
                                      <button style="width:100%;"  type = "submit" class="btn btn-warning" >Upload</button>
                                    </div>
                                  </div>
                                </form>
                              </p>
                              <h4 class="card-title"></h4>
                            </div>
                          </div>
                          <div class="card ">
                            <div class="card-body">
                              <p class="card-text">
                                <div id = "log" style = "white-space:pre-wrap;overflow:scroll;overflow-x:hidden; word-break: break-word; height:400px;"></div>
                                <div class="form-check">
                                  <label class="form-check-label">
                                    <input class="form-check-input" id = "log_scroll" type="checkbox" value="" onclick = "scrollDownLog();">
                                              Autoscroll
                                              
                                      <span class="form-check-sign">
                                        <span class="check"></span>
                                      </span>
                                    </label>
                                  </div>
                                </p>
                                <h4 class="card-title">Log</h4>
                              </div>
                            </div>
                          </div>
                          <div class="col-sm">

                          </div>
                        </div>
                      </div>
                    </div>
                  </body>
                  
                  <!--   Core JS Files   -->
                  <script src="assets/js/core/jquery.min.js"></script>
                  <script src="assets/js/core/popper.min.js"></script>
                  <script src="assets/js/core/bootstrap-material-design.min.js"></script>
                  <script src="https://unpkg.com/default-passive-events"></script>
                  <script async defer src="https://buttons.github.io/buttons.js"></script>
                  <!--  Notifications Plugin    -->
                  <script src="assets/js/plugins/bootstrap-notify.js"></script>
                  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
                  <script src="https://cdn.jsdelivr.net/npm/material-dashboard@2.1.0/assets/js/material-dashboard.js"></script>
                  <script>
          previous_log = ""
          previous_serial = ""
          previous_debug = ""

          is_debug_on = false;

          function scrollDownLog(){
            if(document.getElementById("log_scroll").checked){
              var elem = document.getElementById("log");
              elem.scrollTop = elem.scrollHeight; 
            }
          }

          function loadDoc() {
              var ajax = new XMLHttpRequest();
              ajax.onreadystatechange = function() {
                  if (ajax.readyState == 4) {
                      if (ajax.responseText != previous_log) {
                          previous = ajax.responseText;
                          document.getElementById("log").innerHTML =
                                this.responseText;  
                          scrollDownLog();
                      }
                  }
              };
              ajax.open("POST", "arduino_upload_log.txt", true); //Use POST to avoid caching
              ajax.send();
          }

          setInterval(loadDoc, 100);

        </script>
        <script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>
        <script>refreshMode();</script>
</html>

<?php
  if(!empty($_FILES['uploaded_file']))
  {
    $path = "/home/pi/upload/";
    $path = $path . basename($_FILES['uploaded_file']['name']);
    if(move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $path)) {
      exec("sudo python3 /home/pi/upload_arduino.py &");
    }
  }
?>