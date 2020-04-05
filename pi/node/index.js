const Express = require('express');
const app = new Express();
const fs = require('fs');
const formidable = require('formidable');
const bodyParser = require('body-parser');
const { exec } = require('child_process');

var http = require('http').Server(app);
var io = require('socket.io')(http, { wsEngine: 'ws' });

var port = process.env.PORT || 80;

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

app.post('/submit_conf', urlencodedParser, function (req, res){
  c = getConfig();
  if (typeof req.body.desc !== 'undefined') {
    c['arduino']['serial-descriptor'] = req.body.desc.trim();
  } 
  if (typeof req.body.key !== 'undefined') {
    c['observer']['n2yo-key'] = req.body.key.trim();
  } 
  if (typeof req.body.fqbn !== 'undefined') {
    c['arduino']['fqbn'] = req.body.fqbn.trim();
  }
  if (typeof req.body.sat !== 'undefined') {
    c['sat']['NORAD'] = req.body.sat.trim();
  } 
  if (typeof req.body.lat !== 'undefined') {
    c['observer']['latitude'] = req.body.lat.trim();
  } 
  if (typeof req.body.lon !== 'undefined') {
    c['observer']['longitude'] = req.body.lon.trim();
  } 
  if (typeof req.body.alt !== 'undefined') {
    c['observer']['altitude'] = req.body.alt.trim();
  } 
  if (typeof req.body.datestr !== 'undefined') {
    c['sat']['customtime'] = req.body.datestr.trim();
  }
  if (typeof req.body.azi !== 'undefined') {
    c['custom-angles']['azimuth'] = req.body.azi.trim();
  } 
  if (typeof req.body.ele !== 'undefined') {
    c['custom-angles']['elevation'] = req.body.ele.trim();
  }  
  if (typeof req.body.state !== 'undefined') {
    c['general-state'] = req.body.state.trim();
  } 
  if (typeof req.body.tle1 !== 'undefined') {
    c['sat']['tle1'] = req.body.tle1.trim();
  } 
  if (typeof req.body.tle2 !== 'undefined') {
    c['sat']['tle2'] = req.body.tle2.trim();
  }
  if (typeof req.body.deltaazimuth !== 'undefined') {
    c['custom-angles']['delta-azimuth'] = req.body.deltaazimuth.trim();
  } 
  if (typeof req.body.deltaelevation !== 'undefined') {
    c['custom-angles']['delta-elevation'] = req.body.deltaelevation.trim();
  }  

  if (typeof req.body.autostart !== 'undefined') {
    if(typeof req.body.autostart[1] !== 'undefined'){ //on
      execCommand('sudo systemctl enable track');
      c['autostart'] = true;
      console.log("Autostart on");
    } else { //off
      execCommand('sudo systemctl disable track');
      c['autostart'] = false;
      console.log("Autostart off");
    }
  }  

  execCommand('sudo systemctl start track');

  fs.writeFile(confFile, JSON.stringify(c), function(err) {
    if(err) {
        return console.log(err);
    }
    console.log("Am schimbat configuratia!");
  }); 
  
  res.sendStatus(200);
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


 app.post('/submit_unroll', urlencodedParser, function (req, res){
  console.log(req.body.command);
  
  fs.writeFile(unrollFile, req.body.command, function(err) {
    if(err) {
        return console.log(err);
    }
    console.log("Comanda de unroll scrisa in fisier");
  }); 

  c = getConfig();
  c['general-state'] = "UNROLL";
  fs.writeFile(confFile, JSON.stringify(c), function(err) {
    if(err) {
        return console.log(err);
    }
    console.log("Am schimbat configuratia!");
  }); 
  
 });

http.listen(port, function(){
  console.log('listening on *:' + port);
});
