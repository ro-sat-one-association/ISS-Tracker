const Express = require('express');
const app = new Express();
const fs = require('fs');
const formidable = require('formidable');
const bodyParser = require('body-parser');
const { exec } = require('child_process');

var http = require('http').Server(app);
var io = require('socket.io')(http, { wsEngine: 'ws' });

var port = process.env.PORT || 3000;

var urlencodedParser = bodyParser.urlencoded({ extended: true });

const confFile = '/home/pi/n2yo/config.json';
const unrollFile = '/home/pi/n2yo/unroll.txt';


function execCommand(command){
  exec(command, (err, stdout, stderr) => {
    if (err) {
      //some err occurred
      console.error(err)
    } else {
    // the *entire* stdout and stderr (buffered)
    console.log(`stdout: ${stdout}`);
    console.log(`stderr: ${stderr}`);
    }
  });
}

function getConfig(){
  let rawdata = fs.readFileSync(confFile);
  let config = JSON.parse(rawdata);
  return config
}

fs.watchFile(confFile, (curr, prev) => { //s-a modificat configul, trimite noile constante pe interfata
  io.emit('conf', getConfig());
});

app.use(Express.static(__dirname + '/public'));


app.get('/', function(req, res){
  res.sendFile(__dirname + '/public/track.html');
});

app.get('/track', function(req, res){
  res.sendFile(__dirname + '/public/track.html');
});

app.get('/customtime', function(req, res){
  res.sendFile(__dirname + '/public/customtime.html');
});

app.get('/unghiuri', function(req, res){
  res.sendFile(__dirname + '/public/unghiuri.html');
});

app.get('/config', function(req, res){
  res.sendFile(__dirname + '/public/config.html');
});

app.get('/aprs', function(req, res){
  res.sendFile(__dirname + '/public/aprs.html');
});

app.get('/upload', function(req, res){
  res.sendFile(__dirname + '/public/upload.html');
});


const nsp = io.of('/python');
nsp.on('connection', function(socket){
  console.log('s-a conectat pythonul');
  socket.on('data', function(msg){
  //  console.log('am primit ceva de la python, trimit la interfata');
    io.volatile.emit('data', msg);
  });
  socket.on('time', function(msg){
    io.emit('time', msg);
  });
});

io.on('connection', function(socket){ //am primit ceva, redirectioneaza
  console.log('s-a conectat un client');
  socket.on('conf', function(msg){
    console.log('am trimis conf la un client');
    io.emit('conf', getConfig());
  });
  socket.on('upload', function(msg){
    io.emit('upload', msg);
  });
});

 app.post('/upload', function (req, res){
  var form = new formidable.IncomingForm();

  form.parse(req);

  form.on('fileBegin', function (name, file){
      file.path = '/home/pi/upload/' + file.name;
  });

  form.on('file', function (name, file){
      console.log('Uploaded ' + file.name);
  });

  res.sendFile(__dirname + '/public/upload.html');

  execCommand('sudo python3 /home/pi/upload_arduino.py &');

});

http.listen(port, function(){
  console.log('listening on *:' + port);
});
