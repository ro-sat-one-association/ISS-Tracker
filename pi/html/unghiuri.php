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

$f = fopen("/home/pi/n2yo/unghiuri.txt", "r");
$azi = fgets($f);
$ele = fgets($f);
fclose($f);

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
        <link rel="icon" type="image/png" href="assets/img/favicon.png">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>Dezcâlcește / Unghiuri</title>
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
        <!--     Fonts and icons     -->
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
        <!-- CSS Files -->
        <link href="https://cdn.jsdelivr.net/npm/material-dashboard@2.1.0/assets/css/material-dashboard.min.css" rel="stylesheet" >
        <link href="assets/css/dark-edition.css" rel="stylesheet" >

    </head>

    <body class="dark-edition">

        <div class="wrapper ">
            <div class="sidebar" data-color="danger" data-background-color="black" data-image="assets/img/sidebar.jpg">
                <!--
        Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

        Tip 2: you can also add an image using data-image tag
    -->
                <div class="sidebar-wrapper">
                    <ul class="nav">
                        <li class="nav-item ">
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
                        <li class="nav-item active  ">
                            <a class="nav-link" href="./unghiuri.php">
                                <i class="material-icons">cached</i>
                                <p>Dezcâlcește/Unghiuri</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="main-panel">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top " id="navigation-example">
                    <div class="container-fluid">
                        <span id="alerta_timp"></span>
                        <div class="navbar-wrapper">
                            <a class="navbar-brand" href="javascript:void(0)">Dashboard</a>
                        </div>
                        <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation" data-target="#navigation-example">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="navbar-toggler-icon icon-bar"></span>
                            <span class="navbar-toggler-icon icon-bar"></span>
                            <span class="navbar-toggler-icon icon-bar"></span>
                        </button>
                    </div>
                </nav>
                <!-- End Navbar -->
                <div class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm">
<div class="card card-chart">
    <div class="card-header card-header-success">
        <div class="text-center">
            <canvas id="canvas1" width="250" height="250"></canvas>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
        <div class="col-sm">
            <h4 class="card-title">Indicator Azimut</h4>
        </div>
        <div class="col-sm">
        <p class="card-category">Azimut actual</p>
        <h4 class="card-title">
                    <span id="mini_live_azi"></span>
                    <small>°</small>
                  </h4>
        </div>
        <div class="col-sm">
        <p class="card-category">Azimut țintă</p>
        <h4 class="card-title">
                    <span style="font-weight: bold; opacity: 0.5;" id="mini_target_azi">0</span>
                    <small>°</small>
                  </h4>
        </div>

        </div>
    </div>
    <div class="card-footer">
        <div class="stats">
            <!-- <i class="material-icons">access_time</i> updated 4 minutes ago -->
        </div>
    </div>
</div>
                            </div>

                            <div class="col-sm">
<div class="card card-chart">
    <div class="card-header card-header-warning">
        <div class="text-center">
            <canvas id="canvas2" width="250" height="250"></canvas>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
        <div class="col-sm">
            <h4 class="card-title">Indicator Elevație</h4>
        </div>
        <div class="col-sm">
        <p class="card-category">Elevație actuală</p>
        <h4 class="card-title">
                    <span id="mini_live_ele"></span>
                    <small>°</small>
                  </h4>
        </div>
        <div class="col-sm">
        <p class="card-category">Elevație țintă</p>
        <h4 class="card-title">
                    <span style="font-weight: bold; opacity: 0.5;" id="mini_target_ele">0</span>
                    <small>°</small>
                  </h4>
        </div>

        </div>
    </div>
    <div class="card-footer">
        <div class="stats">
            <!--  <i class="material-icons">access_time</i> campaign sent 2 days ago -->
        </div>
    </div>
</div>

                            </div>

                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card card-stats">
                                    <div class="card-header card-header-info card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">cached</i>
                                        </div>
                                        <p class="card-category">Unghiuri</p>
                                        <h3 class="card-title"><span>&nbsp;</span></h3>
                                        <h3 class="card-title"><span>&nbsp;</span></h3>
                                        <form id="trackform" action="" method="post">

                                            <div class="form-group">
                                                <label for="target_azi">Azimut dorit:</label>
                                                <input type="text" class="form-control" name="azi" id="target_azi" value="<?php echo $azi;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="target_ele">Elevație dorită</label>
                                                <input type="text" class="form-control" name="ele" id="target_ele" value="<?php echo $ele;?>">
                                            </div>

                                        </form>
                                        <button onclick="SubForm()" class="btn btn-info">Submit</button>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <!--<i class="material-icons text-warning">warning</i>
                                                                    <a href="#pablo" class="warning-link">Get More Space...</a>-->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm">
                                <div class="card">
                                        
                                        

        <div class = "row">
            <div class = "col-sm">
                <button onclick="clockAzi()" style="width:100%;" class="btn btn-success btn-lg">
                    Azimut ceasornic
                </button>

            </div>
            <div class="col-sm">
                <button onclick="clockEle()" style="width:100%;" class="btn btn-warning btn-lg">
                    Elevație ceasornic
                </button>

            </div>
        </div>
        &nbsp; 
        <div class = "row">
            <div class="col-sm">
                <button onclick="anticlockAzi()" style="width:100%;" class="btn btn-success btn-lg">
                    Azimut anti-ceasornic
                </button>
            </div>
            <div class="col-sm">
                <button onclick="anticlockEle()" style="width:100%;" class="btn btn-warning btn-lg">
                    Elevație anti-ceasornic
                </button>
            </div>
        </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div style = "display:none;" id="livedata">
                                    <span id="live_azi">-</span>
                                    <span id="live_ele">-</span>
                                </div>
                            </div>
                            <div class="col-sm"></div>
                            <div class="col-sm"></div>

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

        <script src="assets/js/custom/unghiuri.js"></script>

    </body>

    </html>