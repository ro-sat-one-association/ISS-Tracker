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

port = "/dev/"
ports = list(serial.tools.list_ports.comports())
for p in ports:
	if "UART" in p.description:
    		port = port + p.name

ser = serial.Serial(port, 9600, timeout=0)
time.sleep(5)

f = open('/home/pi/n2yo/unroll.txt', 'r')

sendstr = f.read().strip()

f.close()

ser.write(sendstr)
print sendstr

initTime = time.time()
timeSerialRead = 0

while True:
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
