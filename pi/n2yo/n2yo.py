import math
import urllib2
import json
from datetime import datetime
import ephem
import time
import serial
import sys
import serial.tools.list_ports

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

tle = getTLE()
iss = ephem.readtle('ISS', tle[0], tle[1])


#port = "/dev/ttyUSB" + str(sys.argv[1])
ser = serial.Serial(port, 9600, timeout=0)

lastE = 0
lastA = 0

while True:
    home.date = datetime.utcnow()
    iss.compute(home)

    azimuthTLE   = iss.az  * degrees_per_radian
    elevationTLE = iss.alt * degrees_per_radian
    azimuthTLE   = int(azimuthTLE)
    elevationTLE = int(elevationTLE)

    #if(elevationTLE < 0):
    #    elevationTLE += 360
    
    if((lastE != elevationTLE) or (lastA != azimuthTLE)):
        lastE = elevationTLE
        lastA = azimuthTLE
        sendstr = str(azimuthTLE) + "&" + str(elevationTLE)
        ser.write(sendstr)
    	print sendstr
    time.sleep(1.0)
