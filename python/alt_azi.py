import math
import time
from datetime import datetime
import ephem
degrees_per_radian = 180.0 / math.pi
home = ephem.Observer()
home.lon = '46.9438779'   # +E
home.lat = '26.3534007'      # +N
home.elevation = 80 # meters
# Always get the latest ISS TLE data from:
# http://spaceflight.nasa.gov/realdata/sightings/SSapplications/Post/JavaSSOP/orbit/ISS/SVPOST.html
iss = ephem.readtle('ISS',
    '1 25544U 98067A   19256.51399165  .00016717  00000-0  10270-3 0  9009',
    '2 25544  51.6423 283.1789 0008430  35.3638 324.8070 15.50424556 28959'
)
while True:
    home.date = datetime.utcnow()
    iss.compute(home)
    print('iss: altitude %4.1f deg, azimuth %5.1f deg' % (iss.alt * degrees_per_radian, iss.az * degrees_per_radian))
    time.sleep(1.0)
