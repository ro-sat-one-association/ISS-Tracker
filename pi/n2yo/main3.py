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


def getFTDIPort():
	port = "/dev/"
	ports = list(serial.tools.list_ports.comports())
	for p in ports:
		if "UART" in p.description:
			port = port + p.name
			return str(port)
	raise Exception("No FTDI port was found")

def csum(s):
	return str(sum(bytearray(s, encoding='ascii')) % 10)

def getSatCode(configFile):
	f = open(configFile, 'r')
	l = f.readlines()
	f.close()
	sat_code = l[0].strip()
	return sat_code

def getRequestURL(configFile):
	urltle 	= "https://www.n2yo.com/rest/v1/satellite/tle/"
	urltle += getSatCode(configFile)
	urltle += "&apiKey=" + getFileContent('/home/pi/n2yo/n2yo-key.txt')
	return urltle


def getTLE(configFile):
	response = urllib.request.urlopen(getRequestURL(configFile))
	data = json.loads(response.read())
	tle = data["tle"].split("\r\n")
	return tle

def getName(configFile):
	response = urllib.request.urlopen(getRequestURL(configFile))
	data = json.loads(response.read())
	name = data["info"]["satname"]
	return str(name)


def getObserver(configFile):
	f = open(configFile, 'r')
	l = f.readlines()
	f.close()

	lat = l[1].strip()
	lon = l[2].strip()
	alt = l[3].strip()

	home = ephem.Observer()
	home.lon = lon
	home.lat = lat
	home.elevation = int(alt)

	return home


def getFileContent(file):
	f = open(file, 'r')
	l = f.read()
	f.close()
	return l

def getLiveData(ser):
	linie = ser.readline().decode('ascii')
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

def getCustomTime(customTimeFile):
	datestr = getFileContent(customTimeFile).strip()
	base = datetime.strptime(datestr, "%Y-%m-%d %H:%M")
	return base

config = "/home/pi/n2yo/config.txt"

satName = getName(config)
tle 	= getTLE(config)
home 	= getObserver(config)
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
			ser.write(sendstr.encode('ascii'))
			print(sendstr)
			timeSerialWrite = time.time()

		if(time.time() - timeSerialRead > 0.5):
			getWriteLiveData(ser)
			timeSerialRead = time.time()


class MyHandler(FileSystemEventHandler):
	def on_modified(self, event):
		if event.src_path == config:
			global sat, satName, home
			satName = getName(config)
			tle 	= getTLE(config)
			home 	= getObserver(config)
			sat 	= ephem.readtle(satName, tle[0], tle[1])
			if(state == 'CUSTOMTIME'):
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
	state = getFileContent('/home/pi/n2yo/state.txt').strip()

	if (state == "TRACK"):
		base = 0
		home.date = datetime.utcnow()
		standardRoutine()

	if (state == "CUSTOMTIME"):
		if(time.time() - timeCalc > 1.0):
			base2 = getCustomTime('/home/pi/n2yo/customtime.txt')
			if(base != base2):
				print("Am schimbat timpul simulat")
				home.date = base2

			base = base2

			timeCalc = time.time()
			home.date = home.date.datetime() + timedelta(seconds=1)
		standardRoutine()

	if (state == "UNGHI"):
		if(time.time() - timeSerialWrite > 1.0):
			conf = open('/home/pi/n2yo/unghiuri.txt', 'r')
			l = conf.readlines()
			azi  = l[0].strip()
			ele = l[1].strip()
			sendstr = "!" + azi + "&" + ele
			sendstr = sendstr + "!" + csum(sendstr)
			ser.write(sendstr.encode('ascii'))
			timeSerialWrite = time.time()
			
		if(time.time() - timeSerialRead > 0.5):
			getWriteLiveData(ser)
			timeSerialRead = time.time()

	if (state == "UNROLL"):
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

