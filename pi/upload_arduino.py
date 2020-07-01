#!/usr/bin/python3
import os
import sys
import serial.tools.list_ports
import glob
import subprocess
import shlex
import json
import socketio
import time
#sys.stdout = open('/var/www/html/arduino_upload_log.txt', 'w')

config = ""

sio = socketio.Client()

web = 'http://localhost:3000'

try:
	sio.connect(web)
except:
	print("Nu m-am putut conecta la interfata")
	print(sio.sid)	

def sendSoc(soc, data):
	if sio.sid is None:
		try:
			sio.connect(web)
		except:
			pass
	try:
		sio.emit(soc, data)
	except:
		pass

def refreshConfig():
	global config
	with open('/home/pi/n2yo/config.json') as json_file:
		config = json.load(json_file)

refreshConfig()


def getFileContent(file):
	f = open(file, 'r')
	l = f.read()
	f.close()
	return l

def getFTDIPort():
	port = "/dev/"
	ports = list(serial.tools.list_ports.comports())
	print("Listing all available ports...")
	sendSoc("upload", "Listing all available ports...")
	for p in ports:
		print(p.description)
		sendSoc("upload", p.description)
	for p in ports:
		serialDesc = config['arduino']['serial-descriptor']
		if serialDesc in p.description:
			port = port + p.name
			return str(port)
	print ("No corresponding port was found for - " + serialDesc)
	sendSoc("upload", "No corresponding port was found for - " + serialDesc)
	raise Exception("No FTDI port was found")

def execAndPrint(command):
	proc = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	out = proc.stdout.read().decode('utf-8')
	err = proc.stderr.read().decode('utf-8')

	output = out + err

	print(output)
	sendSoc("upload", output)


for x in range(0, 5):
	print("Starting in " + str(5-x) + " seconds...")
	sendSoc("upload", "Starting in " + str(5-x) + " seconds...")
	time.sleep(1)

execAndPrint("sudo systemctl stop track")
execAndPrint("sudo systemctl stop aprs")

port = getFTDIPort()
fqbn = "arduino:avr:pro:cpu=8MHzatmega328"
fqbn = config['arduino']['fqbn']

os.chdir("/home/pi/upload/")
execAndPrint("unzip /home/pi/upload/*.zip")
for i in glob.iglob('/home/pi/upload/*/'):
	os.chdir(i)
execAndPrint("arduino-cli compile --fqbn " + fqbn + " ./")
execAndPrint("arduino-cli upload -v -p " + port + " --fqbn " + fqbn)
os.chdir("/home/pi/")
os.system("rm -r /home/pi/upload/*")


