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

var previous = "";

var time = "";
var utc_time = "";

setInterval(drawBusola, 10);
setInterval(drawElevatie, 10);

actualAzimuth = 0;
liveAzimuth = 0;
targetAzimuth = 0;

actualElevatie = 0;
liveElevatie = 0;
targetElevatie = 0;

function setLiveData() {
    liveAzimuth = document.getElementById("mini_live_azi").innerHTML;
    liveElevatie = document.getElementById("mini_live_ele").innerHTML;
}

function setTargetData() {
    targetAzimuth = document.getElementById("target_azi").value;
    targetElevatie = document.getElementById("target_ele").value;
    document.getElementById("mini_target_azi").innerHTML = targetAzimuth;
    document.getElementById("mini_target_ele").innerHTML = targetElevatie;
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
    setLiveData();
    setTargetData();
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
        message: "Trimis unghiurile succes!"

    }, {
        type: 'success',
        timer: 4000,
        placement: {
            from: from,
            align: align
        }
    });
}

function showUnrollOKNotification(from, align) {

    $.notify({
        icon: "add_alert",
        message: "Am trimis comanda cu succes!"

    }, {
        type: 'success',
        timer: 2000,
        placement: {
            from: from,
            align: align
        }
    });
}

function SubFormRoll() {
    $.ajax({
        url: '/submit_unroll.php',
        type: 'post',
        data: $('#unrollform').serialize(),
        success: function() {
            showOKNotification();
        }
    });
}

function SubForm() {
    $.ajax({
        url: '/submit_config.php',
        type: 'post',
        data: $('#trackform').serialize(),
        success: function() {
            showOKNotification();
        }
    });
}

function clockAzi() {
    document.getElementById("command").value = "A0";
    SubFormRoll();
}

function anticlockAzi() {
    document.getElementById("command").value = "A1";
    SubFormRoll();
}
