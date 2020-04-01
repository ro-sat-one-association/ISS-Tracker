const Express = require('express');
const app = new Express();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var port = process.env.PORT || 3000;


app.use(Express.static(__dirname+'/public'));


app.get('/track', function(req, res){
  res.sendFile(__dirname + '/track.html');
});

app.get('/', function(req, res){
  res.sendFile(__dirname + '/index.html');
});

io.on('connection', function(socket){ //am primit ceva
  socket.on('my message', function(msg){
    io.emit('live_azi', msg);
  });
});

/*
setInterval(function() {
  io.emit('live_azi', Date.now());
}, 180);
*/

http.listen(port, function(){
  console.log('listening on *:' + port);
});
