import requests
import math
import time
from geographiclib.geodesic import Geodesic
from datetime import datetime
import socketio
import threading
import json
import serial
import serial.tools.list_ports
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
import maidenhead as mh

nodeData = ""
config = ""
callsign = ""
key = "106305.jkH03UDkNWrZX5zf"

web = 'http://localhost:3000'
space = '/python'

sio = socketio.Client()

reqDelay = 300
lastTime = time.time()
serialTime = 0

@sio.on('connect', namespace=space)
def on_connect():
    print("I'm connected to the " + space + " namespace!")

def connectInterface():
	sio.connect(web, namespaces = [space])

try:
	connectInterface()
except:
	print("Nu m-am putut conecta la interfata")
	print(sio.sid)	
 
def sendSoc(soc, data):
	try:
		sio.emit(soc, data, namespace = space)
	except:
		pass

def sendSocThread(soc, data):
	if sio.sid is None:
		try:
			connectInterface()
		except:
			pass
	th = threading.Thread(target=sendSoc, args=(soc,data))
	th.start()
 
def refreshConfig():
	global config
	with open('/home/pi/n2yo/config.json') as json_file:
		config = json.load(json_file)
refreshConfig()

with open('/home/pi/n2yo/nodedata.json') as json_file: #templates
	nodeData = json.load(json_file)

with open('/home/pi/n2yo/timedata.json') as json_file:
	timeData = json.load(json_file)

def csum(s):
	return str(sum(bytearray(s, encoding='ascii')) % 10)

def getFTDIPort():
	port = "/dev/"
	ports = list(serial.tools.list_ports.comports())
	for p in ports:
		print(p)
		serialDesc = config['arduino']['serial-descriptor']
		if serialDesc in p.description:
			port = port + p.name
			print("Found port " + port) 
			return str(port)
	raise Exception("No FTDI port was found")

ser  = serial.Serial(getFTDIPort(), 9600, timeout=0.5)

def getReqURL():
    reqURL = "https://api.aprs.fi/api/get?name=" + callsign + "&what=loc&apikey=" + key + "&format=json"
    print(reqURL)
    return reqURL

def getLatLonAlt():
    data = requests.get(getReqURL()).json()
    if data['found'] != 0:
        lat = data['entries'][0]['lat']
        lon = data['entries'][0]['lng']
        try:
            alt = data['entries'][0]['altitude']
        except:
            alt = "-"
        print(str(lat) + " " + str(lon))
        return (lat,lon,alt)
    else:
        return ("-", "-", "-")

obs = {}
target = {}

def redefineTarget():
    global target, lastTime, callsign
    if isGridLocator(callsign) is not False:
        target['lat'] = isGridLocator(callsign)[0]
        target['lon'] = isGridLocator(callsign)[1]
        target['alt'] = obs['alt']
    else:
        callsign = callsign.upper()
        data = getLatLonAlt()
        target['lat'] = data[0]
        target['lon'] = data[1]
        if data[2] is not "-":
            target['alt'] = data[2]
        else:
            target['alt'] = obs['alt']
    lastTime = time.time()

def redefineSettings():
    global obs, callsign
    refreshConfig()
    obs['lat'] = config['observer']['latitude']
    obs['lon'] = config['observer']['longitude']
    obs['alt'] = config['observer']['altitude']
    callsign  = config['target']['callsign']
    redefineTarget()
 
def isGridLocator(x):
    try:
        return mh.toLoc(x)
    except: 
        return False

def getDist(observer, target):
    return Geodesic.WGS84.Inverse(float(observer['lat']), float(observer['lon']), float(target['lat']), float(target['lon']))['s12']

def getAzi(observer, target):
    a = float(Geodesic.WGS84.Inverse(float(observer['lat']), float(observer['lon']), float(target['lat']), float(target['lon']))['azi1'])
    if a < 0:
        return a + 360
    if a > 360:
        return a - 360
    return a

def getEle(observer, taget):
    d = getDist(observer, target)
    alti = float(target['alt']) - float(observer['alt'])
    ele = math.degrees(math.atan(abs(alti/d)))
    if alti >= 0:
        return ele
    else:
        return -1 * ele
    
def getLiveData(ser):
	linie = ""
	try:
		linie = ser.readline().decode('ascii')
	except:
		return None

	linie = linie.strip()
	if linie:
		try:
			if linie[-1] == csum(linie[:-1]):
				l = linie.split("&")
				a = l[0]
				e = l[1][:-2]
				ser.reset_input_buffer()
				return (a,e)	
			else:
				return None
		except:
			pass
	return None

def getWriteLiveData(ser):
    global nodeData
    data = getLiveData(ser)
    if data is not None:
        nodeData['azimuth']['live']   = data[0]
        nodeData['elevation']['live'] = data[1]

class MyHandler(FileSystemEventHandler):
	def on_modified(self, event):
		if 'config.json' in event.src_path:
			redefineSettings()
			print("Am schimbat configuratia")

event_handler = MyHandler()
observer = Observer()
observer.schedule(event_handler, path='/home/pi/n2yo', recursive=False)
observer.start()

redefineSettings()



while True:
    if(time.time() - lastTime > reqDelay):
        lastTime = time.time()
        redefineSettings()

    if target['lat'] is not "-":
        azi = "%.2f" % float(getAzi(obs, target))
        ele = "%.2f" % float(getEle(obs, target))
        nodeData['err'] = "No reported error"
        nodeData['callsign'] = callsign
    else:
        azi = "0.0"
        ele = "0.0"
        nodeData['err'] = "Wrong Callsign"
        nodeData['callsign'] = "-"

    sendstr  = "!" + azi + "&" + ele
    sendstr += "!" + csum(sendstr)

    nodeData['azimuth']['target'] = azi
    nodeData['elevation']['target'] = ele
    
    
    getWriteLiveData(ser)
    sendSocThread("data", nodeData)

    if(time.time() - serialTime > 1.0):
        ser.write(sendstr.encode('ascii'))
        print(sendstr)
        timeSerialWrite = time.time()
        timeData['time'] = time.strftime('%Y-%m-%d %H:%M:%S', time.gmtime(lastTime))
        timeData['utc'] = str(datetime.utcnow())
        sendSocThread("time", timeData)
        serialTime = time.time()
    
    

