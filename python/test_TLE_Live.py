import math
import urllib2
import json
from datetime import datetime
import ephem
import time

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

counter = 4
az = ""
el = ""

while True:
	if counter == 4:
		r = getAE()
		az = r[0]
		el = r[1]
		counter = 0
		print "ACTUALIZARE"
	home.date = datetime.utcnow()
	iss.compute(home)

	azimuthTLE   = iss.az  * degrees_per_radian
	elevationTLE = iss.alt * degrees_per_radian
	d1 = 100.0 - float(el)/elevationTLE * 100.0
	d2 = 100.0 - float(az)/azimuthTLE * 100.0
	print d1,d2
	time.sleep(1.0)
	counter += 1
