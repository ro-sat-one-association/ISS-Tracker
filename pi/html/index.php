<html>
<?php

$f = fopen("/home/pi/n2yo/config.txt", "r");
$sat = fgets($f);
$lat = fgets($f);
$lon = fgets($f);
$alt = fgets($f);

fclose($f);

?>


<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>

<form action="track.php" method="post">

<table>
    <tbody>
        <tr>
            <td>Cod NORAD:</td>
            <td><input type="text" name="sat" value="<?php echo $sat;?>"></td>
        </tr>
        <tr>
            <td>Latitudinea ta:</td>
            <td><input type="text" name="lat" value="<?php echo $lat;?>"></td>
        </tr>
        <tr>
            <td>Longitudinea ta:</td>
            <td><input type="text" name="lon" value="<?php echo $lon;?>"</td>
        </tr>
        <tr>
            <td>Altitudinea ta:</td>
            <td><input type="text" name="alt" value="<?php echo $alt;?>"></td>
        </tr>
    </tbody>
</table>

<input type="submit">
</form>

<div>
<iframe src="log.html" id="iframe" onload="onLoadHandler()" width="500px"></iframe>
</div>
<div>
<canvas id="canvas1" width="250" height="250"></canvas>
<canvas id="canvas2" width="250" height="250"></canvas>
</div>

<script>

function reloadIFrame() {
    console.log('Incarc iframeul');
    document.getElementById('iframe').contentWindow.location.reload();
}

var canvas1 = document.getElementById("canvas1");
var canvas2 = document.getElementById("canvas2");

var ctx1 = canvas1.getContext("2d");
var ctx2 = canvas2.getContext("2d");

var radius = canvas1.height / 2;

ctx1.translate(radius,radius);
ctx2.translate(radius,radius)

radius = radius * 0.90;

setInterval(reloadIFrame, 3000);

function onLoadHandler(){
  drawBusola();
  drawElevatie();
}


function drawElevatie(){
  f = document.getElementById("iframe").contentWindow.document.getElementById("ele").innerHTML;
  u = f - 90;
  if(u < 0) u += 360;
  u *= Math.PI/180;
  drawFace(ctx2, radius);
  drawCardinale(ctx2, radius, 2);
  drawBrat(ctx2, radius, u);
}


function drawBusola(){
  f = document.getElementById("iframe").contentWindow.document.getElementById("azi").innerHTML;
//f = document.getElementById("iframe").innerHTML;
  u = f;
  u *=  Math.PI/180;
  drawFace(ctx1, radius);
  drawCardinale(ctx1, radius, 1);
  drawBrat(ctx1, radius, u);
}

function drawFace(ctx, radius){
  var grad;

  ctx.beginPath();
  ctx.arc(0, 0, radius, 0, 2*Math.PI);
  ctx.fillStyle = "#ffce7a";
  ctx.fill();

  grad = ctx.createRadialGradient(0, 0, radius*0.8, 0, 0, radius*1.35);
  grad.addColorStop(0, '#FFF');
  grad.addColorStop(0.5, '#FFF');
  grad.addColorStop(1, '#FFF');
  ctx.strokeStyle = grad;
  ctx.lineWidth = radius*0.2;
  ctx.stroke();

  ctx.beginPath();
  ctx.arc(0, 0, radius*0.05, 0, 2*Math.PI);
  ctx.fillStyle = "white";
  ctx.fill();
}

function drawCardinale(ctx, radius, x) {
  var ang;
  var num;
  ctx.font = radius*0.15 + "px arial";
  ctx.textBaseline="middle";
  ctx.textAlign="center";
  if (x == 1) var cardinale = "-ESVN";
  if (x == 2) var cardinale = ["-", "180", "-90", "0", "90"];
  for(num = 1; num < 5; num++){
    ang = num * Math.PI / 2;
    ctx.rotate(ang);
    ctx.translate(0, -radius*0.80);
    ctx.rotate(-ang);
    ctx.fillText(cardinale[num], 0, 0);
    ctx.rotate(ang);
    ctx.translate(0, radius*0.80);
    ctx.rotate(-ang);
  }
}

function drawBrat(ctx, radius, unghi) {
  drawHand(ctx, unghi, radius*0.75, radius*0.02);
}

function drawHand(ctx, pos, length, width) {
    ctx.beginPath();
    ctx.lineWidth = width;
    ctx.lineCap = "round";
    ctx.moveTo(0,0);
    ctx.rotate(pos);
    ctx.lineTo(0, -length);
    ctx.stroke();
    ctx.rotate(-pos);
}

</script>

</body>

</html>

