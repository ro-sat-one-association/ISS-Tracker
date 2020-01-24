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
                        <!-- <li class="nav-item active-pro ">
                <a class="nav-link" href="./upgrade.html">
                    <i class="material-icons">unarchive</i>
                    <p>Upgrade to PRO</p>
                </a>
            </li> -->
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
                <!-- End Navbar -->
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
                                            <input style="width:100%;"  type="file" class="btn btn-default" name="uploaded_file"></input><br />
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
                                    <p class="card-text"><div id = "log" style = "white-space:pre-wrap;overflow:scroll;overflow-x:hidden; height:400px;"></div></p>
                                    <h4 class="card-title">Log</h4>
                                  </div>

                                </div>

                            </div>

                            <div class="col-sm">
                                <div class="card ">

                                 <div class="card-body">
                                    <p class="card-text">
                                      <div class="col-sm">
                                          <div id = "serial" style = "white-space:pre-wrap;overflow:scroll;overflow-x:hidden; height:400px;"></div>
                                      </div>
                                    </p>
                                      <div class="row" style="width:100%;">
                                      <form id = "cmdform" action="#" method="post">
                                            <div class="form-group">
                                                <label for="cmd">Trimite o comanda</label>
                                                <input type="text" class="form-control" name="cmd" id="cmd" value="">
                                            </div>
                                      </form>
                                      <button onclick="SubCmdForm()" class="btn btn-info">Send</button>
                                      <button onclick="SubDebug()" class="btn btn-danger" id="debug_button"></button>
                                      </div>
                                    <h4 class="card-title">Serial Log</h4>
                                  </div>

                                </div>
                            </div>

                        </div>
                    </div>
                  </div>
            </div>
           
            <script>
                const x = new Date().getFullYear();
                let date = document.getElementById('date');
                date.innerHTML = '&copy; ' + x + date.innerHTML;
            </script>
        </div>
        </div>
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
          previous = ""
          is_debug_on = false;
          function loadDoc() {
              var ajax = new XMLHttpRequest();
              ajax.onreadystatechange = function() {
                  if (ajax.readyState == 4) {
                      if (ajax.responseText != previous) {
                          previous = ajax.responseText;
                          document.getElementById("log").innerHTML =
                                this.responseText;  
                          var elem = document.getElementById("log");
                          elem.scrollTop = elem.scrollHeight; 
                      }
                  }
              };
              ajax.open("POST", "arduino_upload_log.txt", true); //Use POST to avoid caching
              ajax.send();
          }

          function loadSerial() {
              var ajax = new XMLHttpRequest();
              ajax.onreadystatechange = function() {
                  if (ajax.readyState == 4) {
                      if (ajax.responseText != previous) {
                          previous = ajax.responseText;
                          document.getElementById("serial").innerHTML =
                                this.responseText;  
                          var elem = document.getElementById("serial");
                          elem.scrollTop = elem.scrollHeight; 
                      }
                  }
              };
              ajax.open("POST", "arduino_debug_log.txt", true); //Use POST to avoid caching
              ajax.send();
          }

          function loadDebugState() {
              var ajax = new XMLHttpRequest();
              ajax.onreadystatechange = function() {
                  if (ajax.readyState == 4) {
                      if (ajax.responseText != previous) {
                          previous = ajax.responseText;
                          if (this.responseText.includes("1")){
                              document.getElementById("debug_button").innerHTML = "Stop Debug";
                              is_debug_on = false;

                          } else {
                              document.getElementById("debug_button").innerHTML = "Start Debug";
                              is_debug_on = true;
                          }
                      }
                  }
              };
              ajax.open("POST", "debug_state.txt", true); //Use POST to avoid caching
              ajax.send();
          }

            function SubCmdForm() {
                $.ajax({
                    url: 'submit_cmd.php',
                    type: 'post',
                    data: $('#cmdform').serialize(),
                    success: function() {
                        console.log("trimis o comanda");
                    }
                });
            }

            function SubDebug() {
                $.ajax({
                    url: 'submit_debug.php',
                    type: 'post',
                    data: $('#cmdform').serialize(),
                    success: function() {
                        console.log("pornit/oprit dbg");
                    }
                });
            }

          setInterval(loadDoc, 100);
          setInterval(loadSerial, 50);
          setInterval(loadDebugState, 200);
        </script>

        <script>
            if ( window.history.replaceState ) {
                window.history.replaceState( null, null, window.location.href );
            }
        </script>

        <script>refreshMode();</script>
    </body>

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