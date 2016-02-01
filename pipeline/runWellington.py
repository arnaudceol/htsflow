#Call footprints
import sys
import pyDNase
import pyDNase.footprinting as fp

if(sys.argv[5] == 'singleEnd'):
	regions = pyDNase.GenomicIntervalSet(sys.argv[1])
	reads = pyDNase.BAMHandler(sys.argv[2])
	f=len(regions)-1
	for x in range(f):
	  footprinter = fp.wellington1D(regions[x],reads)
	  footprints = footprinter.footprints(withCutoff=int(sys.argv[4]))
	  with open(sys.argv[3],"a") as bedout:
	    bedout.write(str(footprints))
else:
	regions = pyDNase.GenomicIntervalSet(sys.argv[1])
	reads = pyDNase.BAMHandler(sys.argv[2])
	f=len(regions)-1
	for x in range(f):
	  footprinter = fp.wellington(regions[x],reads)
	  footprints = footprinter.footprints(withCutoff=int(sys.argv[4]))
	  with open(sys.argv[3],"a") as bedout:
	    bedout.write(str(footprints))	
