import time
import serial
import sys
import serial.tools.list_ports

port = "/dev/"
ports = list(serial.tools.list_ports.comports())
for p in ports:
    if "UART" in p.description:
    	port = port + p.name

ser = serial.Serial(port, 9600, timeout=0)

A = 0
E = 0

while True:
    A += 1
    E += 1
    s = str(A) + "&" + str(E)
    ser.write(s) 
    print s
    time.sleep(2.0)
