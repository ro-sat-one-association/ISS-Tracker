<html>
<?php

$f = fopen("/home/pi/n2yo/unghiuri.txt", "r");
$azi = fgets($f);
$ele = fgets($f);
fclose($f);

?>


<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>

<body>

<form action="submit_unghiuri.php" method="post">

<table>
    <tbody>
        <tr>
            <td>Azimut dorit:</td>
            <td><input type="text" id = "target_azi" name="azi" value="<?php echo $azi;?>"></td>
        </tr>
        <tr>
            <td>Elevatie dorita:</td>
            <td><input type="text" id = "target_ele" name="ele" value="<?php echo $ele;?>"></td>
        </tr>
    </tbody>
</table>



<input type="submit">
</form>

<table> 
    <tbody>
	<tr>
 	  <td>
		<a href="submit_unroll.php?a=A0" class="button">O rotatie Azimut Ceasornic</a>
  	  </td>
	  <td>
		<a href="submit_unroll.php?a=A1" class="button">O rotatie Azimut Anti-Ceasornic</a>
	  </td>
	</tr>
	<tr>
          <td>
		<a href="submit_unroll.php?a=E0" class="button">O rotatie Elevatie Ceasornic</a>
          </td>
	  <td>
		<a href="submit_unroll.php?a=E1" class="button">O rotatie Elevatie Anti-Ceasornic</a>
	  </td>
	</tr>
    </tbody>
</table>

<div>
	Date actuale: 
	<div id="livedata">
		 <span id= "target_azi">-</span>
		 <span id= "target_ele">-</span>
	</div>

</div>
<div>
<canvas id="canvas1" width="250" height="250"></canvas>
<canvas id="canvas2" width="250" height="250"></canvas>
</div>

<script>

var previous = "";

function getLiveData() {
    var ajax = new XMLHttpRequest();
    ajax.onreadystatechange = function() {
        if (ajax.readyState == 4) {
            if (ajax.responseText != previous) {
		previous = ajax.responseText;
		if(this.responseText.search(".") != -1){
                	document.getElementById("livedata").innerHTML = this.responseText;
			drawAll();
		}
            }
        }
    };
    ajax.open("POST", "livedata.html", true); //Use POST to avoid caching
    ajax.send();
}

setInterval(getLiveData, 100);

var canvas1 = document.getElementById("canvas1");
var canvas2 = document.getElementById("canvas2");

var ctx1 = canvas1.getContext("2d");
var ctx2 = canvas2.getContext("2d");

var radius = canvas1.height / 2;

ctx1.translate(radius,radius);
ctx2.translate(radius,radius)

radius = radius * 0.90;


function drawAll(){
  drawBusola();
  drawElevatie();
}


function drawElevatie(){
  u = document.getElementById("target_ele").value;
  u = u - 90;
  if(u < 0) u += 360;
  u *= Math.PI/180;

  l = document.getElementById("live_ele").innerHTML;
  l = l - 90;
  if(l < 0) l += 360;
  l *= Math.PI/180;

  drawFace(ctx2, radius);
  drawCardinale(ctx2, radius, 2);
  drawBratTarget(ctx2, radius, u);
  drawBratLive(ctx2, radius, l);
}


function drawBusola(){
  u = document.getElementById("target_azi").value;
  u *=  Math.PI/180;
  l = document.getElementById("live_azi").innerHTML;
  l *=  Math.PI/180;
  drawFace(ctx1, radius);
  drawCardinale(ctx1, radius, 1);
  drawBratTarget(ctx1, radius, u);
  drawBratLive(ctx1, radius, l);
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

function drawBratLive(ctx, radius, unghi) {
  drawHand(ctx, unghi, radius*0.75, radius*0.02, 'white');
}

function drawBratTarget(ctx, radius, unghi) {
  drawHand(ctx, unghi, radius*0.6, radius*0.04, '#ff8c12');
}


function drawHand(ctx, pos, length, width, color) {
    ctx.beginPath();
    ctx.lineWidth = width;
    ctx.lineCap = "round";
    ctx.moveTo(0,0);
    ctx.rotate(pos);
    ctx.lineTo(0, -length);
    ctx.strokeStyle = color;
    ctx.stroke();
    ctx.rotate(-pos);
}

</script>

</body>

</html>

