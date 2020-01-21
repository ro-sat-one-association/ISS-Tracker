#!/usr/bin/python3
import os
import sys
import serial.tools.list_ports
import glob
import subprocess
import shlex

sys.stdout = open('/var/www/html/arduino_upload_log.txt', 'w')

def getFileContent(file):
	f = open(file, 'r')
	l = f.read()
	f.close()
	return l

def getFTDIPort():
	port = "/dev/"
	ports = list(serial.tools.list_ports.comports())
	print("Listing all available ports...")
	for p in ports:
		print(p.description)
	for p in ports:
		serialDesc = getFileContent('/home/pi/n2yo/serial-desc.txt').strip()
		if serialDesc in p.description:
			port = port + p.name
			return str(port)
	print ("No corresponding port was found for - " + serialDesc)
	raise Exception("No FTDI port was found")

def execAndPrint(command):
	proc = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	out = proc.stdout.read().decode('utf-8')
	err = proc.stderr.read().decode('utf-8')

	output = out + err

	print(output)

port = getFTDIPort()
fqbn = "arduino:avr:pro:cpu=8MHzatmega328"
fqbn = getFileContent('/home/pi/n2yo/fqbn.txt').strip()
execAndPrint("systemctl stop track")

os.chdir("/home/pi/upload/")
execAndPrint("unzip /home/pi/upload/*.zip")
for i in glob.iglob('/home/pi/upload/*/'):
	os.chdir(i)
execAndPrint("arduino-cli compile --fqbn " + fqbn + " ./")
execAndPrint("arduino-cli upload -v -p " + port + " --fqbn " + fqbn + " ./")
os.chdir("/home/pi/")
os.system("rm -r /home/pi/upload/*")
execAndPrint("systemctl start track")


