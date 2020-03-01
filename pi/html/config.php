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
<?php

$string = file_get_contents("/home/pi/n2yo/config.json");
$json_a = json_decode($string, true);

$key  = $json_a['observer']['n2yo-key'];
$desc = $json_a['arduino']['serial-descriptor'];
$fqbn = $json_a['arduino']['fqbn'];
$tle1 = $json_a['sat']['tle1'];
$tle2 = $json_a['sat']['tle2'];

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
        <link rel="icon" type="image/png" href="assets/img/favicon.png">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>Configurare</title>
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
                        <li class="nav-item">
                            <a class="nav-link" href="./upload.php">
                                <i class="material-icons">cloud_upload</i>
                                <p>Arduino Upload</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="./upload.php">
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
                                <div class="card card-stats">
                                    <div class="card-header card-header-danger card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">settings</i>
                                        </div>
                                        <p class="card-category">Setări</p>
                                        <h3 class="card-title"><span>&nbsp;</span></h3>
                                        <h3 class="card-title"><span>&nbsp;</span></h3>
                                        <form id="trackform" action="" method="post">

                                            <div class="form-group">
                                                <label for="key">N2YO Key</label>
                                                <input type="text" class="form-control" name="key" id="key" value="<?php echo $key;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="fqbn">FQBN</label>
                                                <input type="text" class="form-control" name="fqbn" id="fqbn" value="<?php echo $fqbn;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="desc">Descriere Serial</label>
                                                <input type="text" placeholder="UART / Arduino / CH340 / etc." class="form-control" name="desc" id="desc" value="<?php echo $desc;?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="desc">TLE Custom - 1</label>
                                                <input type="text" placeholder="Lasă liber daca nu e necesar" class="form-control" name="tle1" id="desc" value="<?php echo $tle1;?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="desc">TLE Custom - 2</label>
                                                <input type="text" placeholder="Lasă liber daca nu e necesar" class="form-control" name="tle2" id="desc" value="<?php echo $tle2;?>">
                                            </div>
                                        </form>
                                         <button onclick="SubForm()" class="btn btn-danger">Submit</button>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <!--<i class="material-icons text-warning">warning</i>
                                    <a href="#pablo" class="warning-link">Get More Space...</a>-->
                                        </div>
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

			function showOKNotification(from, align) {

			    $.notify({
			        icon: "add_alert",
			        message: "Am trimis noile constante"

			    }, {
			        type: 'success',
			        timer: 2000,
			        placement: {
			            from: from,
			            align: align
			        }
			    });
			}

			function SubForm() {
			    $.ajax({
			        url: 'submit_config.php',
			        type: 'post',
			        data: $('#trackform').serialize(),
			        success: function() {
			            showOKNotification();
			        }
			    });
			}

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