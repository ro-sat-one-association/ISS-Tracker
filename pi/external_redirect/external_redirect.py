import urllib2
import os

os.system("hostname -I > ip")

f = open('./ip', 'r')

l = f.readline()
l = l.strip()
l = l.split(" ")[0]
f.close()

print l 

connection = urllib2.urlopen("http://gigacloud.go.ro/taddress.php?a="+l)
connection.close()
