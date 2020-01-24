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
		serialDesc = config['arduino']['serial-descriptor']
		if serialDesc in p.description:
			port = port + p.name
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
	return urltle

def getTLE():
	response = urllib.request.urlopen(getRequestURL())
	data = json.loads(response.read())
	tle = data["tle"].split("\r\n")
	return tle

def getName():
	response = urllib.request.urlopen(getRequestURL())
	data = json.loads(response.read())
	name = data["info"]["satname"]
	return str(name)

def getCustomTime():
	datestr = config['sat']['customtime']
	base = datetime.strptime(datestr, "%Y-%m-%d %H:%M")
	return base

def getObserver():
	home = ephem.Observer()
	home.lon = config['observer']['longitude']
	home.lat = config['observer']['latitude']
	home.elevation = config['observer']['altitude']

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

satName = getName()
tle 	= getTLE()
home 	= getObserver()
sat 	= ephem.readtle(satName, tle[0], tle[1])
ser 	= serial.Serial(getFTDIPort(), 9600, timeout=0)

timeSerialWrite = 0
timeSerialRead  = 0
timeCalc 	= 0
base  = 0
state = ""
sentCommand = False

def standardRoutine():
		global timeSerialRead
		global timeSerialWrite
		sat.compute(home)

		azi  = "%.2f" %  (sat.az  * 180.0 / math.pi)
		ele  = "%.2f" %  (sat.alt * 180.0 / math.pi)

		log = getFileContent('/home/pi/n2yo/logTemplate.html')
		log = log.format(
			azi, 
			ele, 
			satName, 
			str(home.date.datetime()), 
			str(datetime.utcnow())
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

class MyHandler(FileSystemEventHandler):
	def on_modified(self, event):
		if 'config.json' in event.src_path:
			global sat, satName, home
			refreshConfig()
			satName = getName()
			tle 	= getTLE()
			home 	= getObserver()
			sat 	= ephem.readtle(satName, tle[0], tle[1])
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

