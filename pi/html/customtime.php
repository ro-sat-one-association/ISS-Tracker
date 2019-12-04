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

$f = fopen("/home/pi/n2yo/config.txt", "r");
$sat = fgets($f);
$lat = fgets($f);
$lon = fgets($f);
$alt = fgets($f);

fclose($f);

$f = fopen("/home/pi/n2yo/customtime.txt", "r");
$datestr = fgets($f);

?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png">
        <link rel="icon" type="image/png" href="assets/img/favicon.png">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title>Timp simulat</title>
        <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
        <!--     Fonts and icons     -->
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
        <!-- CSS Files -->
        <link href="assets/css/material-dashboard.css?v=2.1.0" rel="stylesheet" />
        <!-- CSS Just for demo purpose, don't include it in your project -->
        <link href="assets/demo/demo.css" rel="stylesheet" />
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
                        <li class="nav-item">
                            <a class="nav-link" href="./track.php">
                                <i class="material-icons">explore</i>
                                <p>Urmărește Satelit</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="./customtime.php">
                                <i class="material-icons">watch_later</i>
                                <p>Timp modificat</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./tables.html">
                                <i class="material-icons">content_paste</i>
                                <p>Table List</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./typography.html">
                                <i class="material-icons">library_books</i>
                                <p>Typography</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./icons.html">
                                <i class="material-icons">bubble_chart</i>
                                <p>Icons</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./map.html">
                                <i class="material-icons">location_ons</i>
                                <p>Maps</p>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link" href="./notifications.html">
                                <i class="material-icons">notifications</i>
                                <p>Notifications</p>
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
                            <div class="col-xl-4 col-lg-12">
                                <div class="card card-chart">
                                    <div class="card-header card-header-success">
                                        <div class="text-center">
                                            <canvas id="canvas1" width="250" height="250"></canvas>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title">Indicator Azimut</h4>
                                        <p class="card-category">
                                            <!--<span class="text-success"><i class="fa fa-long-arrow-up"></i> 55% </span> increase in today sales.</p>-->
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <!-- <i class="material-icons">access_time</i> updated 4 minutes ago -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-12">
                                <div class="card card-chart">
                                    <div class="card-header card-header-warning">
                                        <div class="text-center">
                                            <canvas id="canvas2" width="250" height="250"></canvas>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title">Indicator Elevație</h4>
                                        <!--<p class="card-category">Last Campaign Performance</p>-->
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <!--  <i class="material-icons">access_time</i> campaign sent 2 days ago -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-12">
                                <div class="card card-chart">
                                    <div class="card-header card-header-info">
                                        <div>
                                            <div id="targetdata">
                                                <div>Azimut: <span id="target_azi">-</span></div>
                                                <div>Elevatie: <span id="taget_ele">-</span></div>
                                                <div>-</div>
                                            </div>

                                            <div id="livedata">
                                                <span id="target_azi">-</span>
                                                <span id="target_ele">-</span>
                                            </div>

                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title">Date brute</h4>
                                        <p class="card-category">Datele țintă și actuale</p>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="card card-stats">
                                    <div class="card-header card-header-success card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">360</i>
                                        </div>
                                        <p class="card-category">Azimut actual</p>
                                        <h3 class="card-title">
                    <span id="mini_live_azi"></span>
                    <small>°</small>
                  </h3>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <!--<i class="material-icons">date_range</i> Last 24 Hours-->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm">
                                <div class="card card-stats">
                                    <div class="card-header card-header-warning card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">shuffle</i>
                                        </div>
                                        <p class="card-category">Elevație actuală</p>
                                        <h3 class="card-title"><span id="mini_live_ele"></span>
                    <small>°</small>
                  </h3>
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
                                <div class="card card-stats">
                                    <div class="card-header card-header-info card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">info_outline</i>
                                        </div>
                                        <p class="card-category">Satelit urmărit</p>
                                        <h3 class="card-title"><span id="mini_sat"></span></h3>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <!--<i class="material-icons">local_offer</i> Tracked from Github-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-sm">
                                <div class="card card-stats">
                                    <div class="card-header card-header-danger card-header-icon">
                                        <div class="card-icon">
                                            <i class="material-icons">watch</i>
                                        </div>
                                        <p class="card-category">Timpul simulat</p>
                                        <h3 class="card-title">
                                                <span id="mini_timp"></span>
                                        </h3>
                                    </div>
                                    <div class="card-footer">
                                        <div class="stats">
                                            <!--<i class="material-icons">date_range</i> Last 24 Hours-->
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                                <label for="sat_field">Cod NORAD</label>
                                                <input type="text" class="form-control" name="sat" id="sat_field" value="<?php echo $sat;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="lat_field">Latitudinea ta</label>
                                                <input type="text" class="form-control" name="lat" id="lat_field" value="<?php echo $lat;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="lon_field">Longitudinea ta</label>
                                                <input type="text" class="form-control" name="lon" id="lon_field" value="<?php echo $lon;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="alt_field">Altitudinea ta</label>
                                                <input type="text" class="form-control" name="alt" id="alt_field" value="<?php echo $alt;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="alt_field">Altitudinea ta</label>
                                                <input type="text" class="form-control" name="alt" id="alt_field" value="<?php echo $alt;?>">
                                            </div>

                                            <div class="form-group">
                                                <label for="date_field">Moment inițial timp simulat</label>
                                                <input type="text" class="form-control" name="datestr" id="date_field" placeholder="YYYY-mm-DD HH:MM" value="<?php echo $datestr;?>">
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
        <script src="assets/js/plugins/perfect-scrollbar.jquery.min.js"></script>
        <!-- Place this tag in your head or just before your close body tag. -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>
        <!--  Google Maps Plugin    -->
        <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>
        <!-- Chartist JS -->
        <script src="assets/js/plugins/chartist.min.js"></script>
        <!--  Notifications Plugin    -->
        <script src="assets/js/plugins/bootstrap-notify.js"></script>
        <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
        <script src="assets/js/material-dashboard.js?v=2.1.0"></script>
        <!-- Material Dashboard DEMO methods, don't include it in your project! -->
        <script src="assets/demo/demo.js"></script>
        <script>
            $(document).ready(function() {
                $().ready(function() {
                    $sidebar = $('.sidebar');

                    $sidebar_img_container = $sidebar.find('.sidebar-background');

                    $full_page = $('.full-page');

                    $sidebar_responsive = $('body > .navbar-collapse');

                    window_width = $(window).width();

                    $('.fixed-plugin a').click(function(event) {
                        // Alex if we click on switch, stop propagation of the event, so the dropdown will not be hide, otherwise we set the  section active
                        if ($(this).hasClass('switch-trigger')) {
                            if (event.stopPropagation) {
                                event.stopPropagation();
                            } else if (window.event) {
                                window.event.cancelBubble = true;
                            }
                        }
                    });

                    $('.fixed-plugin .active-color span').click(function() {
                        $full_page_background = $('.full-page-background');

                        $(this).siblings().removeClass('active');
                        $(this).addClass('active');

                        var new_color = $(this).data('color');

                        if ($sidebar.length != 0) {
                            $sidebar.attr('data-color', new_color);
                        }

                        if ($full_page.length != 0) {
                            $full_page.attr('filter-color', new_color);
                        }

                        if ($sidebar_responsive.length != 0) {
                            $sidebar_responsive.attr('data-color', new_color);
                        }
                    });

                    $('.fixed-plugin .background-color .badge').click(function() {
                        $(this).siblings().removeClass('active');
                        $(this).addClass('active');

                        var new_color = $(this).data('background-color');

                        if ($sidebar.length != 0) {
                            $sidebar.attr('data-background-color', new_color);
                        }
                    });

                    $('.fixed-plugin .img-holder').click(function() {
                        $full_page_background = $('.full-page-background');

                        $(this).parent('li').siblings().removeClass('active');
                        $(this).parent('li').addClass('active');

                        var new_image = $(this).find("img").attr('src');

                        if ($sidebar_img_container.length != 0 && $('.switch-sidebar-image input:checked').length != 0) {
                            $sidebar_img_container.fadeOut('fast', function() {
                                $sidebar_img_container.css('background-image', 'url("' + new_image + '")');
                                $sidebar_img_container.fadeIn('fast');
                            });
                        }

                        if ($full_page_background.length != 0 && $('.switch-sidebar-image input:checked').length != 0) {
                            var new_image_full_page = $('.fixed-plugin li.active .img-holder').find('img').data('src');

                            $full_page_background.fadeOut('fast', function() {
                                $full_page_background.css('background-image', 'url("' + new_image_full_page + '")');
                                $full_page_background.fadeIn('fast');
                            });
                        }

                        if ($('.switch-sidebar-image input:checked').length == 0) {
                            var new_image = $('.fixed-plugin li.active .img-holder').find("img").attr('src');
                            var new_image_full_page = $('.fixed-plugin li.active .img-holder').find('img').data('src');

                            $sidebar_img_container.css('background-image', 'url("' + new_image + '")');
                            $full_page_background.css('background-image', 'url("' + new_image_full_page + '")');
                        }

                        if ($sidebar_responsive.length != 0) {
                            $sidebar_responsive.css('background-image', 'url("' + new_image + '")');
                        }
                    });

                    $('.switch-sidebar-image input').change(function() {
                        $full_page_background = $('.full-page-background');

                        $input = $(this);

                        if ($input.is(':checked')) {
                            if ($sidebar_img_container.length != 0) {
                                $sidebar_img_container.fadeIn('fast');
                                $sidebar.attr('data-image', '#');
                            }

                            if ($full_page_background.length != 0) {
                                $full_page_background.fadeIn('fast');
                                $full_page.attr('data-image', '#');
                            }

                            background_image = true;
                        } else {
                            if ($sidebar_img_container.length != 0) {
                                $sidebar.removeAttr('data-image');
                                $sidebar_img_container.fadeOut('fast');
                            }

                            if ($full_page_background.length != 0) {
                                $full_page.removeAttr('data-image', '#');
                                $full_page_background.fadeOut('fast');
                            }

                            background_image = false;
                        }
                    });

                    $('.switch-sidebar-mini input').change(function() {
                        $body = $('body');

                        $input = $(this);

                        if (md.misc.sidebar_mini_active == true) {
                            $('body').removeClass('sidebar-mini');
                            md.misc.sidebar_mini_active = false;

                            $('.sidebar .sidebar-wrapper, .main-panel').perfectScrollbar();

                        } else {

                            $('.sidebar .sidebar-wrapper, .main-panel').perfectScrollbar('destroy');

                            setTimeout(function() {
                                $('body').addClass('sidebar-mini');

                                md.misc.sidebar_mini_active = true;
                            }, 300);
                        }

                        // we simulate the window Resize so the charts will get updated in realtime.
                        var simulateWindowResize = setInterval(function() {
                            window.dispatchEvent(new Event('resize'));
                        }, 180);

                        // we stop the simulation of Window Resize after the animations are completed
                        setTimeout(function() {
                            clearInterval(simulateWindowResize);
                        }, 1000);

                    });
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                // Javascript method's body can be found in assets/js/demos.js
                md.initDashboardPageCharts();

            });
        </script>

        <script>
            var previous = "";

            var time = "";
            var utc_time = "";

            function getLiveData() {
                var ajax = new XMLHttpRequest();
                ajax.onreadystatechange = function() {
                    if (ajax.readyState == 4) {
                        if (ajax.responseText != previous) {
                            previous = ajax.responseText;
                            if (this.responseText.search(".") != -1) {
                                document.getElementById("livedata").innerHTML = this.responseText;
                                var doc = new DOMParser().parseFromString(this.responseText, "text/html")
                                document.getElementById("mini_live_azi").innerHTML = doc.getElementById("live_azi").innerHTML;
                                document.getElementById("mini_live_ele").innerHTML = doc.getElementById("live_ele").innerHTML;
                                setLiveData();
                            }
                        }
                    }
                };
                ajax.open("POST", "livedata.html", true); //Use POST to avoid caching
                ajax.send();
            }

            function getTargetData() {
                var ajax = new XMLHttpRequest();
                ajax.onreadystatechange = function() {
                    if (ajax.readyState == 4) {
                        if (ajax.responseText != previous) {
                            document.getElementById("targetdata").innerHTML = this.responseText;
                            var doc = new DOMParser().parseFromString(this.responseText, "text/html")
                            document.getElementById("mini_sat").innerHTML = doc.getElementById("sat").innerHTML;
                            document.getElementById("mini_timp").innerHTML = doc.getElementById("time").innerHTML;
                            time = doc.getElementById("time").innerHTML;
                            utc_time = doc.getElementById("time_utc_now").innerHTML;
                            previous = ajax.responseText;
                            setTargetData();
                        }
                    }
                };
                ajax.open("POST", "log.html", true); //Use POST to avoid caching
                ajax.send();
            }

            setInterval(getLiveData, 100);
            setInterval(getTargetData, 1000);
            setInterval(drawBusola, 10);
            setInterval(drawElevatie, 10);
            setInterval(verificaTimpul, 100);

            actualAzimuth = 0;
            liveAzimuth = 0;
            targetAzimuth = 0;

            actualElevatie = 0;
            liveElevatie = 0;
            targetElevatie = 0;

            function setLiveData() {
                liveAzimuth = document.getElementById("live_azi").innerHTML;
                liveElevatie = document.getElementById("live_ele").innerHTML;
            }

            function setTargetData() {
                targetAzimuth = document.getElementById("target_azi").innerHTML;
                targetElevatie = document.getElementById("target_ele").innerHTML;
            }

            var canvas1 = document.getElementById("canvas1");
            var canvas2 = document.getElementById("canvas2");

            var ctx1 = canvas1.getContext("2d");
            var ctx2 = canvas2.getContext("2d");

            var radius = canvas1.height / 2;

            ctx1.translate(radius, radius);
            ctx2.translate(radius, radius)

            radius = radius * 0.95;

            busolastyle = "rgba(255, 255, 255, 0.2)";
            elevatiestyle = "rgba(255, 255, 255, 0.2)";

            dAzimuth = 10 * 360 / 4000;
            dElevatie = 10 * 360 / 30000;

            // u = document.getElementById("target_azi").innerHTML;
            // l = document.getElementById("live_azi").innerHTML;

            function deltaAzimuth(t, h) {
                if (Math.abs(t - h) < 180)
                    return Math.abs(t - h);
                else
                    return 360 - Math.abs(t - h);
            }

            function sensAzimuth(t, h) {
                if (Math.abs(t - h) < 180) {
                    if (h > t)
                        return -1;
                    else
                        return 1;
                } else {
                    if (h > t)
                        return 1;
                    else
                        return -1;
                }
            }

            function drawBusola() {
                u = targetAzimuth;
                u *= Math.PI / 180;

                if (actualAzimuth < 0) actualAzimuth += 360;

                actualAzimuth = actualAzimuth % 360;

                if (deltaAzimuth(liveAzimuth, actualAzimuth) > 1) actualAzimuth += sensAzimuth(liveAzimuth, actualAzimuth) * dAzimuth;
                l = actualAzimuth;

                l *= Math.PI / 180;
                drawFace(ctx1, radius, busolastyle);
                drawCardinale(ctx1, radius, 1);
                drawBratTarget(ctx1, radius, u);
                drawBratLive(ctx1, radius, l);
            }

            function drawElevatie() {
                u = targetElevatie;
                u = u - 90;
                if (u < 0) u += 360;
                u *= Math.PI / 180;

                ae = parseFloat(actualElevatie);
                le = parseFloat(liveElevatie);

                if (ae < 0) ae += 360.0;
                if (le < 0) le += 360.0;

                if (deltaAzimuth(le, ae) > 1) actualElevatie += sensAzimuth(le, ae) * dElevatie;

                l = ae;
                l = l - 90;
                if (l < 0) l += 360;
                l *= Math.PI / 180;

                drawFace(ctx2, radius, elevatiestyle);
                drawCardinale(ctx2, radius, 2);
                drawBratTarget(ctx2, radius, u);
                drawBratLive(ctx2, radius, l);
            }

            function drawFace(ctx, radius, style) {
                var grad;
                ctx.beginPath();
                ctx.arc(0, 0, radius, 0, 2 * Math.PI);
                ctx.fillStyle = style;
                ctx.clearRect(-500, -500, 1000, 1000);
                ctx.fill();

                grad = ctx.createRadialGradient(0, 0, radius * 0.8, 0, 0, radius * 1.35);
                //grad.addColorStop(0, '#FFF');
                //grad.addColorStop(0.5, '#FFF');
                //grad.addColorStop(1, '#FFF');
                ctx.strokeStyle = grad;
                ctx.lineWidth = radius * 0.2;
                ctx.stroke();

                ctx.beginPath();
                ctx.arc(0, 0, radius * 0.05, 0, 2 * Math.PI);
                ctx.fillStyle = "white";
                ctx.fill();
            }

            function drawCardinale(ctx, radius, x) {
                var ang;
                var num;
                ctx.font = radius * 0.15 + "px arial";
                ctx.textBaseline = "middle";
                ctx.textAlign = "center";
                if (x == 1) var cardinale = "-ESVN";
                if (x == 2) var cardinale = ["-", "180", "-90", "0", "90"];
                for (num = 1; num < 5; num++) {
                    ang = num * Math.PI / 2;
                    ctx.rotate(ang);
                    ctx.translate(0, -radius * 0.80);
                    ctx.rotate(-ang);
                    ctx.fillText(cardinale[num], 0, 0);
                    ctx.rotate(ang);
                    ctx.translate(0, radius * 0.80);
                    ctx.rotate(-ang);
                }
            }

            function drawBratLive(ctx, radius, unghi) {
                drawHand(ctx, unghi, radius * 0.75, radius * 0.02, 'white');
            }

            function drawBratTarget(ctx, radius, unghi) {
                drawHand(ctx, unghi, radius * 0.6, radius * 0.04, 'rgba(255, 255, 255, 0.7)');
            }

            function drawHand(ctx, pos, length, width, color) {
                ctx.beginPath();
                ctx.lineWidth = width;
                ctx.lineCap = "round";
                ctx.moveTo(0, 0);
                ctx.rotate(pos);
                ctx.lineTo(0, -length);
                ctx.strokeStyle = color;
                ctx.stroke();
                ctx.rotate(-pos);
            }

      function showOKNotification(from, align) {

          $.notify({
              icon: "add_alert",
              message: "Schimbat satelitul și timpul cu succes!"

          }, {
              type: 'success',
              timer: 4000,
              placement: {
                  from: from,
                  align: align
              }
          });
      }

      function SubForm(){
          $.ajax({
              url:'submit_customtime.php',
              type:'post',
              data:$('#trackform').serialize(),
              success:function(){
                  showOKNotification();
              }
          });
      }


      function verificaTimpul(){
        if(time.length     > 19) time     = time.slice(0, -7);
        if(utc_time.length > 19) utc_time = utc_time.slice(0, -7);
        if(time != utc_time) {
            document.getElementById("alerta_timp").innerHTML = "<div class=\"alert alert-info\" role=\"alert\">Timpul modificat este setat</div>";
        } else {
            document.getElementById("alerta_timp").innerHTML = "<div class=\"alert alert-warning\" role=\"alert\">Timpul modificat nu este setat!</div>";
        }

      }

        </script>

    </body>

    </html>