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
setInterval(verificaTimpul, 100);


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
    targetAzimuth = document.getElementById("mini_target_azi").innerHTML;
    targetElevatie = document.getElementById("mini_target_ele").innerHTML;
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

elevatiestyle1 = "rgba(255, 255, 255, 0.1)";
elevatiestyle2 = "rgba(255, 255, 255, 0.2)";

dAzimuth = 10 * 360 / 4000;
dElevatie = 10 * 360 / 30000;

//constante pentru user
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return false;
}


showRedDot  = false;
showHorizon = false;


function refreshCookies(){
    showRedDot  = Boolean(getCookie("showRedDot"));
    showHorizon = Boolean(getCookie("showHorizon"));


    if(showHorizon) {
        document.getElementById("horizon_text").innerHTML = "Ascunde orizontul";
    } else {
        document.getElementById("horizon_text").innerHTML = "Arată orizontul";
    }

    if(showRedDot){
        document.getElementById("reddot_text").innerHTML = "Ascunde indicatorul roșu";
    } else {
        document.getElementById("reddot_text").innerHTML = "Arată indicatorul roșu";
    }
}

setInterval(refreshCookies, 1000);


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
    setTargetData();
    setLiveData();
    u = targetAzimuth;
    u *= Math.PI / 180;

    if (actualAzimuth < 0) actualAzimuth += 360;

    actualAzimuth = actualAzimuth % 360;

    if (deltaAzimuth(liveAzimuth, actualAzimuth) > 1) actualAzimuth += sensAzimuth(liveAzimuth, actualAzimuth) * dAzimuth;
    l = actualAzimuth;

    l *= Math.PI / 180;
    drawFace(ctx1, radius, busolastyle, busolastyle);
    drawCardinale(ctx1, radius, 1);
    drawBratTarget(ctx1, radius, u);
    drawBratLive(ctx1, radius, l);
}

function drawElevatie() {
    var dot = false;
    u = targetElevatie;
    if(u < 0) {
        dot = true;
    }
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
    if(showHorizon)
        drawFace(ctx2, radius, elevatiestyle1, elevatiestyle2);
    else
        drawFace(ctx2, radius, elevatiestyle2, elevatiestyle2);

    drawCardinale(ctx2, radius, 2);
    drawBratTarget(ctx2, radius, u);
    drawBratLive(ctx2, radius, l);
    if(dot && showRedDot) drawRedDot(ctx2, radius);
}

function drawFace(ctx, radius, style1, style2) {
    var grad;
    ctx.beginPath();
    ctx.arc(0, 0, radius, 0, Math.PI);
    ctx.fillStyle = style1;
    ctx.clearRect(-500, -500, 1000, 1000);
    ctx.fill();
    ctx.beginPath();
    ctx.arc(0, 0, radius, Math.PI, 2 * Math.PI);
    ctx.fillStyle = style2;
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

function drawRedDot(ctx, radius){
    ctx.beginPath();
    ctx.arc(110, 110, radius * 0.04, 0, 2 * Math.PI);
    ctx.fillStyle = "#e53935";
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

function showOKNotification(from, align, msg = "Am trimis noile constante", t = 'info') {

    $.notify({
        icon: "add_alert",
        message: msg

    }, {
        type: t,
        timer: 2000,
        placement: {
            from: from,
            align: align
        }
    });
}

function verificaTimpul() {
    if(document.getElementById("time") != null && document.getElementById("time_utc_now") != null){
        time = document.getElementById("time").innerHTML;
        utc_time = document.getElementById("time_utc_now").innerHTML;
        time = time.trim();
        utc_time = utc_time.trim();
        if (time.length > 19) time = time.slice(0, -7);
        if (utc_time.length > 19) utc_time = utc_time.slice(0, -7);
        a = new Date(time + ".0000000 GMT+0000");
        b = new Date(utc_time + ".0000000 GMT+0000");
        delta = Math.abs(a-b)/1000;
        if (delta > 2) {
            document.getElementById("alerta_timp").innerHTML = ("<div class=\"alert alert-info\" role=\"alert\">Timpul modificat este setat - " + a + "</div>");
        } else {
            document.getElementById("alerta_timp").innerHTML = ("<div class=\"alert alert-warning\" role=\"alert\">Timpul modificat nu este setat!</div>");
        }
    }
}


var lastSat = "";

function verificaSatelit(){
    if (lastSat != document.getElementById("mini_sat").innerHTML){
        lastSat = document.getElementById("mini_sat").innerHTML;
        if (document.getElementById("err_code").innerHTML == "Wrong NORAD"){
            showOKNotification('top', 'right', 'Nu s-a găsit niciun satelit/corp!', 'danger');
        } else {
            showOKNotification('top', 'right', 'S-a schimbat satelitul/corpul urmărit', 'success');
        }
    }
}

$(document).ready(function(){
    setTimeout(() => { //cam nasoala rezolvare, dar na, delay 3 sec pt notfiicari
        lastSat = document.getElementById("mini_sat").innerHTML;
        setInterval(verificaSatelit, 100); 
    }, 3000);
});

function SubForm() {
    $.ajax({
        url: '/submit_conf',
        type: 'post',
        data: $('#trackform').serialize(),
        success: function() {
            showOKNotification();
        }
    });
}