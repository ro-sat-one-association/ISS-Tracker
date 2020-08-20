var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var port = process.env.PORT || 3001;
var fs = require('fs');

app.get('/', function(req, res){
  res.sendFile(__dirname + '/index.html');
});

io.on('connection', function(socket){
  socket.on('sensordata', function(msg){
    io.emit('sensordata', msg);
    fs.writeFile('/home/pi/n2yo/phonedata.txt', 'SNZ ' + msg + '\n', function (err,data) {
      if (err) {
        return console.log(err);
      }
    });
  });
});

http.listen(port, function(){
  console.log('ascult pe ' + port);
});
