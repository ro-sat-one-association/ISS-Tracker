#!/usr/bin/python3
import math
import urllib.request, urllib.error, urllib.parse
import json
from datetime import datetime
from datetime import timedelta
import ephem
import time
import serial
import sys
import serial.tools.list_ports
import time
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler

config = ""

def refreshConfig():
	global config
	with open('/home/pi/n2yo/config.json') as json_file:
		config = json.load(json_file)

refreshConfig()

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

def csum(s):
	return str(sum(bytearray(s, encoding='ascii')) % 10)

def getFileContent(file):
	f = open(file, 'r')
	l = f.read()
	f.close()
	return l

def getRequestURL():
	urltle 	= "https://www.n2yo.com/rest/v1/satellite/tle/"
	urltle += config['sat']['NORAD']
	urltle += "&apiKey=" + config['observer']['n2yo-key']
	print (urltle)
	return urltle

def representsInt(s):
    try: 
        int(s)
        return True
    except ValueError:
        return False

def validLatitude(lat):
	try:
		lat = float(lat)
		if lat < 90.0 and lat > -90.0:
			return True
		else:
			return False
	except ValueError:
		return False

def validLongitude(lon):
	try:
		lon = float(lon)
		if lon < 180.0 and lon > -180.0:
			return True
		else:
			return False
	except ValueError:
		return False

def getTLE():
	if config['sat']['tle1'] and config['sat']['tle2']:
		tle = ["", ""]
		tle[0] = config['sat']['tle1']
		tle[1] = config['sat']['tle2']
		print("Custom TLE: ")
		customTLE = True
		for x in tle:
			print(x)
		return tle
	elif representsInt(config['sat']['NORAD']):
		response = urllib.request.urlopen(getRequestURL())
		data = json.loads(response.read())
		if data['tle']:
			tle = data['tle'].split("\r\n")
			print("TLE: ")
			for x in tle:
				print(x)
			customTLE = False
			return tle
		else:
			return None
	else:
		return config['sat']['NORAD']

def getName():
	response = urllib.request.urlopen(getRequestURL())
	data = json.loads(response.read())
	if config['sat']['tle1'] and config['sat']['tle2']:
		name = "CUSTOM TLE"
	else:
		name = data["info"]["satname"]
	return name

def getCustomTime():
	datestr = config['sat']['customtime']
	base = datetime.strptime(datestr, "%Y-%m-%d %H:%M")
	return base

def getObserver():
	home = ephem.Observer()
	lon = str(config['observer']['longitude'])
	lat = str(config['observer']['latitude'])

	if validLatitude(lat):
		home.lat = lat
	else:
		print("Latitudine nevalida")
		home.lat = "0.0"
		home.lon = "0.0"
		home.elevation = 0
		return home

	if validLongitude(lon):
		home.lon  = lon
	else:
		print("Longitudine nevalida")
		home.lat = "0.0"
		home.lon = "0.0"
		home.elevation = 0
		return home	
		
	try:
		home.elevation = float(config['observer']['altitude'])
	except ValueError:
		print("Altitudine nevalida")
		home.lat = "0.0"
		home.lon = "0.0"
		home.elevation = 0
		return home
		
	return home

def getLiveData(ser):
	linie = ""
	try:
		linie = ser.readline().decode('ascii')
	except:
		print("Am primit ceva gunoi pe serial")
	ser.reset_input_buffer()
	l = linie.split(" ")
	if(len(l) == 2):
		if('.' in l[0] and '.' in l[1]):
			a = l[0].strip()
			e = l[1].strip()
			return (a,e)
		else:
			return None
	return None

def getWriteLiveData(ser):
	data = getLiveData(ser)
	if data is not None:
		live = getFileContent('/home/pi/n2yo/liveTemplate.html')
		live = live.format(data[0], data[1])
		f = open('/var/www/html/livedata.html', 'w')
		f.write(live)
		f.close()
		print(data)

def writeError(e):
	log = getFileContent('/home/pi/n2yo/logTemplate.html')
	log = log.format(
		"-", 
		"-", 
		"-", 
		str(datetime.utcnow()), 
		str(datetime.utcnow()),
		str(e)
	)
	f = open('/var/www/html/log.html', 'w')
	f.write(log)
	f.close()

home = None
tle  = None
sat  = None
satName = None

def redefineSettings():
	global sat, satName, home, ser
	refreshConfig()
	tle  = getTLE()
	home = getObserver()
	ser  = serial.Serial(getFTDIPort(), 9600, timeout=0)
	if tle is not None:
		if (representsInt(config['sat']['NORAD'])):
			satName = getName()
			sat 	= ephem.readtle(satName, tle[0], tle[1])
		elif tle.lower() == "sun":
			satName = "Sun"
			sat  	= ephem.Sun()
		elif tle.lower() == "moon":
			satName = "Moon"
			sat 	= ephem.Moon()
		elif tle.lower() == "venus":
			satName = "Venus"
			sat 	= ephem.Venus()
		elif tle.lower() == "mars":
			satName = "Mars"
			sat 	= ephem.Mars()
		else:
			sat = None
			satName = ""
	else: 
		sat = None
		satName = ""

redefineSettings()

timeSerialWrite = 0
timeSerialRead  = 0
timeCalc 	= 0
base  = 0
state = ""
sentCommand = False

def standardRoutine():
		global timeSerialRead
		global timeSerialWrite
		if sat is not None:
			if not (float(home.lat) == 0.0 and float(home.lon) == 0.0 and int(home.elevation) == 0):
				sat.compute(home)
				deltaAzimuth   = float(config['custom-angles']['delta-azimuth'])
				deltaElevation = float(config['custom-angles']['delta-elevation'])
				azi  = "%.2f" %  (sat.az  * 180.0 / math.pi + deltaAzimuth)
				ele  = "%.2f" %  (sat.alt * 180.0 / math.pi + deltaElevation)

				log = getFileContent('/home/pi/n2yo/logTemplate.html')
				log = log.format(
					azi, 
					ele, 
					satName, 
					str(home.date.datetime()), 
					str(datetime.utcnow()),
					"No reported error"
				)

				sendstr  = "!" + azi + "&" + ele 
				sendstr += "!" + csum(sendstr)	

				if(time.time() - timeSerialWrite > 1.0):
					f = open('/var/www/html/log.html', 'w')
					f.write(log)
					f.close()
					print(sendstr)
					ser.write(sendstr.encode('ascii'))
					print(sendstr)
					timeSerialWrite = time.time()

				if(time.time() - timeSerialRead > 0.5):
					getWriteLiveData(ser)
					timeSerialRead = time.time()
			else:
				writeError("Wrong Coordinates/Altitude")
				getWriteLiveData(ser)
				time.sleep(1.0)
		else:
			writeError("Wrong NORAD")
			getWriteLiveData(ser)
			time.sleep(1.0)
			

class MyHandler(FileSystemEventHandler):
	def on_modified(self, event):
		if 'config.json' in event.src_path:
			redefineSettings()
			if(config['general-state'] == 'CUSTOMTIME'):
				home.date = base
			print("Am schimbat configuratia")

		if event.src_path == '/home/pi/n2yo/unroll.txt':
			global sentCommand
			print("Primit comanda de unroll")
			sentCommand = False


event_handler = MyHandler()
observer = Observer()
observer.schedule(event_handler, path='/home/pi/n2yo', recursive=False)
observer.start()


while True:
	state = config['general-state']

	if ("TRACK" in state):
		base = 0
		home.date = datetime.utcnow()
		standardRoutine()

	if ("CUSTOMTIME" in state):
		if(time.time() - timeCalc > 1.0):
			base2 = config['sat']['customtime']
			if(base != base2):
				print("Am schimbat timpul simulat")
				home.date = base2

			base = base2

			timeCalc = time.time()
			home.date = home.date.datetime() + timedelta(seconds=1)
		standardRoutine()

	if ("UNGHI" in state):
		if(time.time() - timeSerialWrite > 1.0):
			azi  = config['custom-angles']['azimuth']
			ele  = config['custom-angles']['elevation']
			sendstr = "!" + azi + "&" + ele
			sendstr = sendstr + "!" + csum(sendstr)
			ser.write(sendstr.encode('ascii'))
			print(sendstr)
			timeSerialWrite = time.time()
			
		if(time.time() - timeSerialRead > 0.5):
			getWriteLiveData(ser)
			timeSerialRead = time.time()

	if ("UNROLL" in state):
		if(sentCommand == False):
			f = open('/home/pi/n2yo/unroll.txt', 'r')
			sendstr = f.read().strip()
			f.close()
			ser.write(sendstr.encode('ascii'))
			print(sendstr)
			sentCommand = True

		if(time.time() - timeSerialRead > 0.5):
			getWriteLiveData(ser)
			timeSerialRead = time.time()

