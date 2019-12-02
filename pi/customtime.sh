#!/bin/bash
sudo systemctl stop track
#sudo systemctl stop customtime
sudo systemctl stop unghi
sudo systemctl stop unroll
python2 /home/pi/n2yo/customtime.py
