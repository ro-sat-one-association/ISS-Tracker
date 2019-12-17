import math
import urllib2
import json
from datetime import datetime
from datetime import timedelta
import ephem
import time
import serial
import sys
import serial.tools.list_ports
import time


def getFTDIPort():
	port = "/dev/"
	ports = list(serial.tools.list_ports.comports())
	for p in ports:
		if "UART" in p.description:
			port = port + p.name
			return str(port)
	raise Exception("No FTDI port was found")

def csum(s):
	return str(sum(bytearray(s)) % 10)

def getSatCode(configFile):
	f = open(configFile, 'r')
	l = f.readlines()
	f.close()
	sat_code = l[0].strip()
	return sat_code

def getKey(configFile): #TO-DO
	return "BMDSE6-VEC9T4-Y6YANC-47A9"

def getRequestURL(configFile):
	urltle 	= "https://www.n2yo.com/rest/v1/satellite/tle/"
	urltle += getSatCode(configFile)
	urltle += "&apiKey=" + getKey(configFile)
	return urltle


def getTLE(configFile):
	response = urllib2.urlopen(getRequestURL(configFile))
	data = json.loads(response.read())
	tle = data["tle"].split("\r\n")
	return tle

def getName(configFile):
	response = urllib2.urlopen(getRequestURL(configFile))
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


def getLogTemplate(logTemplate):
	f = open(logTemplate, 'r')
	l = f.read()
	f.close()
	return l

def getLiveTemplate(liveTemplate):
	f = open(liveTemplate, 'r')
	l = f.read()
	f.close()
	return l

def getLiveData(ser):
	linie = ser.readline()
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

def getState(stateFile):
	f = open(stateFile, 'r')
	l = f.read()
	f.close()
	return l

def getCustomTime(customTimeFile):
	f = open(customTimeFile, 'r')
	datestr = f.readline().strip()
	f.close()
	base = datetime.strptime(datestr, "%Y-%m-%d %H:%M")
	return base

config = "./config.txt"

satName = getName(config)
tle 	= getTLE(config)
home 	= getObserver(config)
sat 	= ephem.readtle(satName, tle[0], tle[1])
ser 	= serial.Serial(getFTDIPort(), 9600, timeout=0)


timeSerialWrite = 0
timeSerialRead  = 0
timeCalc 	= 0


def standardRoutine():
		global timeSerialRead
		global timeSerialWrite
		azi  = "%.2f" %  (sat.az  * 180.0 / math.pi)
		ele  = "%.2f" %  (sat.alt * 180.0 / math.pi)

		log = getLogTemplate('./logTemplate.html')
		log = log.replace("AZI", azi)
		log = log.replace("ELE", ele)
		log = log.replace("SAT", satName)
		log = log.replace("DAT", str(home.date.datetime()))
		log = log.replace("TIME_NOW", str(datetime.utcnow()))

		sendstr  = "!" + azi + "&" + ele 
		sendstr += "!" + csum(sendstr)	

		if(time.time() - timeSerialWrite > 1.0):
			f = open('/var/www/html/log.html', 'w')
			f.write(log)
			f.close()
			ser.write(sendstr)
			print sendstr
			timeSerialWrite = time.time()

		if(time.time() - timeSerialRead > 0.5):
			data = getLiveData(ser)
			if data is not None:
				live = getLiveTemplate('./liveTemplate.html')
				live = live.replace("AZI", data[0])
				live = live.replace("ELE", data[1])
				f = open('/var/www/html/livedata.html', 'w')
				f.write(live)
				f.close()
				print data
				timeSerialRead = time.time()



base  = 0

while True:
	state = getState('./state.txt').strip()

	if (state == "TRACK"):
		home.date = datetime.utcnow()
		sat.compute(home)
		standardRoutine()

	if (state == "CUSTOMTIME"):
		if(time.time() - timeCalc > 1.0):
			base2 = getCustomTime('../n2yo/customtime.txt')
			if(base != base2):
				print "Am schimbat timpul simulat"
				home.date = base

			base = base2
			
			timeCalc = time.time()
			home.date = home.date.datetime() + timedelta(seconds=1)
			sat.compute(home)
		standardRoutine()

"""
	if (state == "UNGHI"):
		#...
	if (state == "UNROLL"):
		#...
"""
