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

f = open('/home/pi/n2yo/customtime.txt', 'r')
datestr = f.readline().strip()
f.close()

base = datetime.strptime(datestr, "%Y-%m-%d %H:%M")

f = open('/home/pi/n2yo/config.txt', 'r')
l = f.readlines()

key = "BMDSE6-VEC9T4-Y6YANC-47A9"

sat_code = l[0].strip()
lat = l[1].strip()
lon = l[2].strip()
alt = l[3].strip()

"""
key = "BMDSE6-VEC9T4-Y6YANC-47A9"
sat_code = "25544" #ISS 25544
lat = "46.9438779"
lon = "26.3534007"
alt = "359"
"""


port = "/dev/"
ports = list(serial.tools.list_ports.comports())
for p in ports:
    if "UART" in p.description:
    	port = port + p.name

degrees_per_radian = 180.0 / math.pi
home = ephem.Observer()
home.lon = lon
home.lat = lat
home.elevation = int(alt)

urltle = "https://www.n2yo.com/rest/v1/satellite/tle/" + sat_code + "&apiKey="
urltle += key
#print urltle

def getAE():
	response = urllib2.urlopen(urlpos)
	data = json.loads(response.read())
	azimuth   = data["positions"][0]["azimuth"]
	elevation = data["positions"][0]["elevation"]
	return [azimuth, elevation]

def getTLE():
	response = urllib2.urlopen(urltle)
	data = json.loads(response.read())
	tle = data["tle"].split("\r\n")
	return tle

def getName():
	response = urllib2.urlopen(urltle)
	data = json.loads(response.read())
	name = data["info"]["satname"]
	return name


satName = getName()
tle = getTLE()
sat = ephem.readtle(str(satName), tle[0], tle[1])

#port = "/dev/ttyUSB" + str(sys.argv[1])
ser = serial.Serial(port, 9600, timeout=0)

def csum(s):
    return str(sum(bytearray(s)) % 10)

timeSerialWrite = 0
timeSerialRead  = 0
timeCalc 	= 0

while True:
	if(time.time() - timeCalc > 1.0):
		timeCalc = time.time()
		base = base + timedelta(seconds=1)
		home.date = base
		sat.compute(home)

		azimuthTLE   = sat.az  * degrees_per_radian
		elevationTLE = sat.alt * degrees_per_radian
		azimuthTLE   = float(azimuthTLE)
		elevationTLE = float(elevationTLE)
		
		strA  = "%.2f" % azimuthTLE 
		strE  = "%.2f" % elevationTLE 
		sendstr = "!" + strA + "&" + strE
		sendstr = sendstr + "!" + csum(sendstr)	

		logstr  = "<div>Azimut: <span id = \"target_azi\">"
		logstr += strA + "</span></div>\n"
		logstr += "<div>Elevatie: <span id=\"target_ele\">"
		logstr += strE
		logstr += "</span></div>\n"
		logstr += "<div id=\"sat\">" + str(satName) + " - " + str(sat_code) + "</div>\n"
	        logstr += "<div id=\"time\">" + str(base) + "</div>"
		logstr += "<div id=\"time_utc_now\">" + str(datetime.utcnow()) + "</div>"
	if(time.time() - timeSerialWrite > 1.0):
		log = open('/var/www/html/log.html', 'w') 
		log.write(logstr)
		log.close()
		ser.write(sendstr)
		print sendstr
		timeSerialWrite = time.time()

	if(time.time() - timeSerialRead > 0.5):
		linie = ser.readline()
		ser.reset_input_buffer()
		l = linie.split(" ")
		if(len(l) == 2):
			if('.' in l[0] and '.' in l[1]):
				a =l[0].strip()
				e =l[1].strip()
				print a,e
				live = open('/var/www/html/livedata.html', 'w')
				livestr  = "<span id= \"live_azi\">"
				livestr += a + "</span>\n<span id=\"live_ele\">"
				livestr += e + "</span>"
				live.write(livestr)
		timeSerialRead = time.time()
