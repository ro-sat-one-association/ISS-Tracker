<html>
<body>

<form action="track.php" method="post">
Cod NORAD Satelit: <input type="text" name="sat" value="25544"><br>
Latitudinea ta: <input type="text" name="lat" value="46.9438779"><br>
Longitudinea ta: <input type="text" name="lon" value="26.3534007"><br>
Altitudinea ta:  <input type="text" name="alt" value="359"><br>
<input type="submit">
</form>

<iframe src="log.html" id="iframe"></iframe>

<script>
        window.setInterval(function() {
            reloadIFrame()
        }, 3000);

        function reloadIFrame() {
            console.log('reloading..,');
            document.getElementById('iframe').contentWindow.location.reload();
        }
</script>


</body>
</html>
