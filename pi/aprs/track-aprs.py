import aprslib
import requests
import math
from geographiclib.geodesic import Geodesic


call = "YO8BCA-1"
key = "106305.jkH03UDkNWrZX5zf"

def getReqURL():
    reqURL = "https://api.aprs.fi/api/get?name=" + call + "&what=loc&apikey=" + key + "&format=json"
    return reqURL

def getLatLon():
    data = requests.get(getReqURL()).json()
    lat = data['entries'][0]['lat']
    lon = data['entries'][0]['lon']
    print(lat + " " + lon)
    return (lat,lon)


observer = {}
target = {}

observer['lat'] = "46.943581"
observer['lon'] = "26.352924"
observer['alt'] = "300"

target['lat'] = "46.942288"
target['lon'] = "26.497852"
target['alt'] = "250"
 
def getDist(observer, target):
    return Geodesic.WGS84.Inverse(float(observer['lat']), float(observer['lon']), float(target['lat']), float(target['lon']))['s12']

def getAzi(observer, target):
    return Geodesic.WGS84.Inverse(float(observer['lat']), float(observer['lon']), float(target['lat']), float(target['lon']))['azi1']

def getEle(observer, taget):
    d = getDist(observer, target)
    alti = float(target['alt']) - float(observer['alt'])
    ele = math.degrees(math.atan(abs(alti/d)))
    if alti >= 0:
        return ele
    else:
        return -1 * ele
    
print(str(getAzi(observer, target)) + " " + str(getEle(observer, target)) + " " + str(getDist(observer, target)))

    