import os
import json
import time

while True:
	try:
		f = open("/home/pi/n2yo/config.json", 'r')
		a = json.loads(f.read())
		#print(a)
	except:
		#print("Config belit!")
		os.system("sudo cp /home/pi/config.json /home/pi/n2yo/config.json")
		os.system("sudo chown www-data /home/pi/n2yo/config.json")
		os.system("sudo chmod 775 /home/pi/n2yo/config.json")
		os.system("sudo systemctl restart track")
	time.sleep(1.0)
