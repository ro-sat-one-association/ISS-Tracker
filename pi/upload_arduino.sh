#!/bin/bash
sudo systemctl stop track
cd /home/pi/upload/
unzip /home/pi/upload/*.zip
cd /home/pi/upload/*/
arduino-cli compile --fqbn $1 ./
sudo arduino-cli upload -p $2 --fqbn $1 ./
sudo rm -r /home/pi/upload/*
sudo systemctl start track
