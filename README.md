# About

This is the software for an antenna tracker developed on top of Raspberry Pi. The main software is written in Python, using the Ephem library and the TLE Orbit Data is automatically retreived from https://www.n2yo.com/. 
The sensor data (accelerometer and compass) is sent from and Arduino Phone through LAN using socket.io.

The sensor data and the azimuth and elevation target angles are then sent from the Pi to an Arduino through USB Serial that controls the motors which move the antenna to the desired angles.

All of this is monitored and configured through a web interface written in Node.js which is hosted on the Raspberry Pi.


# Screenshots
* In Romanian - sorry 

* Main tracking page
![1](https://raw.githubusercontent.com/ro-sat-one-association/ISS-Tracker/master/1.png)

* Configuration editor

![2]https://raw.githubusercontent.com/ro-sat-one-association/ISS-Tracker/master/2.png)
