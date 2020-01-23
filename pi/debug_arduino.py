#!/usr/bin/python3
import os
import sys
import serial.tools.list_ports
import glob
import subprocess
import shlex
import json

sys.stdout = open('/var/www/html/arduino_debug_log.txt', 'a')

config = ""

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

def deleteFile(file):
	f = open(file, 'w')
	f.close()

def getFTDIPort():
	port = "/dev/"
	ports = list(serial.tools.list_ports.comports())
	#print("Listing all available ports...")
	#for p in ports:
	#	print(p.description)
	for p in ports:
		serialDesc = config['arduino']['serial-descriptor']
		if serialDesc in p.description:
			port = port + p.name
			return str(port)
	print ("No corresponding port was found for - " + serialDesc)
	raise Exception("No FTDI port was found")


def getLiveData(ser):
	try:
		line = ser.readline().decode('utf-8')
	except:
		print("", end='')
	#ser.reset_input_buffer()
	if line:
		print(line, end='')


def execAndPrint(command):
	proc = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
	out = proc.stdout.read().decode('utf-8')
	err = proc.stderr.read().decode('utf-8')

	output = out + err

	print(output)

ser = serial.Serial(getFTDIPort(), 9600, timeout=0)

while True:
	getLiveData(ser)
	command = getFileContent('/home/pi/debug_command.txt')
	deleteFile('/home/pi/debug_command.txt')
	if command:
		ser.write(command.encode('ascii'))
		print("[Sent] " + command)
	sys.stdout.flush()


