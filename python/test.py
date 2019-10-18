import math
import urllib2
import json
from datetime import datetime
import ephem
import time
import serial
import sys
key = "BMDSE6-VEC9T4-Y6YANC-47A9"
sat_code = "25544" #ISS 25544
lat = "46.9438779"
lon = "26.3534007"
alt = "359"

degrees_per_radian = 180.0 / math.pi
home = ephem.Observer()
home.lon = lon
home.lat = lat
home.elevation = int(alt)

urlpos = "https://www.n2yo.com/rest/v1/satellite/positions/25544/"
urlpos += lat + "/"
urlpos += lon + "/"
urlpos += alt + "/2/&apiKey="
urlpos += key
#print urlpos
#https://www.n2yo.com/rest/v1/satellite/positions/25544/41.702/-76.014/0/2/&apiKey=589P8Q-SDRYX8-L842ZD-5Z9 

urltle = "https://www.n2yo.com/rest/v1/satellite/tle/25544&apiKey="
urltle += key
#print urltle

def send(toSend):
	port = "/dev/ttyUSB" + str(sys.argv[1])
	ser = serial.Serial(port, 9600, timeout=0)
	ser.write(str(toSend))
	ser.close()

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

tle = getTLE()
iss = ephem.readtle('ISS', tle[0], tle[1])

while True:
	home.date = datetime.utcnow()
	iss.compute(home)

	azimuthTLE   = iss.az  * degrees_per_radian
	elevationTLE = iss.alt * degrees_per_radian
	if elevationTLE < 0:
		elevationTLE = 0
		
	sendstr = "<" + str(int(azimuthTLE)) + " " + str(int(elevationTLE)) + ">"
	sendstr = "<-2312 -2334>"
	send(sendstr)
	print sendstr
	time.sleep(1.0)
