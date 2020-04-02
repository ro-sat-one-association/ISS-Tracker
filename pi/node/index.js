const Express = require('express');
const app = new Express();
const fs = require('fs');
var bodyParser = require('body-parser');
var urlencodedParser = bodyParser.urlencoded({ extended: true });
var http = require('http').Server(app);
var io = require('socket.io')(http);
var port = process.env.PORT || 3000;

const confFile = '/home/pi/n2yo/config.json';

function getConfig(){
  let rawdata = fs.readFileSync(confFile);
  let config = JSON.parse(rawdata);
  return config
}

fs.watchFile(confFile, (curr, prev) => { //s-a modificat configul, trimite noile constante pe interfata
  io.emit('conf', getConfig());
});

app.use(Express.static(__dirname+'/public'));


app.get('/track', function(req, res){
  res.sendFile(__dirname + '/track.html');
});

app.get('/customtime', function(req, res){
  res.sendFile(__dirname + '/customtime.html');
});

app.get('/', function(req, res){
  res.sendFile(__dirname + '/track.html');
});

io.on('connection', function(socket){ //am primit ceva
  socket.on('data', function(msg){
    io.emit('data', msg);
  });
  socket.on('conf', function(msg){
    io.emit('conf', getConfig());
  });
  socket.on('time', function(msg){
    io.emit('time', msg);
  });
});

app.post('/submit_conf', urlencodedParser, function (req, res){
  c = getConfig();
  if (typeof req.body.desc !== 'undefined') {
    c['arduino']['serial-descriptor'] = req.body.desc;
  } 
  if (typeof req.body.key !== 'undefined') {
    c['observer']['n2yo-key'] = req.body.key;
  } 
  if (typeof req.body.fqbn !== 'undefined') {
    c['arduino']['fqbn'] = req.body.fqbn;
  }
  if (typeof req.body.sat !== 'undefined') {
    c['sat']['NORAD'] = req.body.sat;
  } 
  if (typeof req.body.lat !== 'undefined') {
    c['observer']['latitude'] = req.body.lat;
  } 
  if (typeof req.body.lon !== 'undefined') {
    c['observer']['longitude'] = req.body.lon;
  } 
  if (typeof req.body.alt !== 'undefined') {
    c['observer']['altitude'] = req.body.alt;
  } 
  if (typeof req.body.datestr !== 'undefined') {
    c['sat']['customtime'] = req.body.datestr;
  }
  if (typeof req.body.azi !== 'undefined') {
    c['custom-angles']['azimuth'] = req.body.azi;
  } 
  if (typeof req.body.ele !== 'undefined') {
    c['custom-angles']['elevation'] = req.body.ele;
  }  
  if (typeof req.body.state !== 'undefined') {
    c['general-state'] = req.body.state;
  } 
  if (typeof req.body.tle1 !== 'undefined') {
    c['sat']['tle1'] = req.body.tle1;
  } 
  if (typeof req.body.tle2 !== 'undefined') {
    c['sat']['tle2'] = req.body.tle2;
  }
  if (typeof req.body.deltaazimuth !== 'undefined') {
    c['custom-angles']['delta-azimuth'] = req.body.deltaazimuth;
  } 
  if (typeof req.body.deltaelevation !== 'undefined') {
    c['custom-angles']['delta-elevation'] = req.body.deltaelevation;
  }  
//TO-DO: autostart checkmark

  fs.writeFile(confFile, JSON.stringify(c), function(err) {
    if(err) {
        return console.log(err);
    }
    console.log("Am schimbat configuratia!");
  }); 
  
  res.sendStatus(200);
 });

http.listen(port, function(){
  console.log('listening on *:' + port);
});
