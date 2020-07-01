def csum(s):
	return str(sum(bytearray(s, encoding='ascii')) % 10)

print(csum("!200&0"))
