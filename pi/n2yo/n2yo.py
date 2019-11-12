import math
import urllib2
import json
from datetime import datetime
import ephem
import time
import serial
import sys
import serial.tools.list_ports

"""
log = open('/var/www/html/log', 'w')
log.close()
"""

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

lastE = 0
lastA = 0

while True:
    home.date = datetime.utcnow()
    sat.compute(home)

    azimuthTLE   = sat.az  * degrees_per_radian
    elevationTLE = sat.alt * degrees_per_radian
    azimuthTLE   = float(azimuthTLE)
    elevationTLE = float(elevationTLE)

    #if(elevationTLE < 0):
    #    elevationTLE += 360
    
    if((int(lastE) != int(elevationTLE)) or (int(lastA) != int(azimuthTLE))):
#    if(True):
        log = open('/var/www/html/log.html', 'w') 
        lastE = int(elevationTLE)
        lastA = int(azimuthTLE)
	strA  = "%.2f" % azimuthTLE 
	strE  = "%.2f" % elevationTLE 
        sendstr = strA + "&" + strE
        ser.write(sendstr)
    	print sendstr
    	logstr  = "<div>Azimut: <span id = \"azi\">"
        logstr += strA + "</span></div>\n"
        logstr += "<div>Elevatie: <span id=\"ele\">"
        logstr += strE
        logstr += "</span></div>\n"
        logstr += "<div>" + str(satName) + " - " + str(sat_code) + "</div>"
        log.write(logstr)
        #log.write("Azimut:" + "<div id = \"azi\">" + str(azimuthTLE) + "</div>\n" + "Elevatie: <div id=\"ele\">" + str(elevationTLE) + "</div>" + "\n\n" + sat_code)
    	log.close()
    
    time.sleep(1.0)
