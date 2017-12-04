#################### 
# 
# Macs versions 2.0 and 2.1 are not compatible, 
# we should choose which version to import.
#

if (getHTSFlowPath("MACS2_VERSION") == "2.0") {
	pcPath = "pcmacs2.0"
} else if (getHTSFlowPath("MACS2_VERSION") == "2.1") {
	pcPath = "pcmacs2.1"
} else {
	stop("The version of macs 2 is not specified. Please add MACS2_VERSION, with value 2.0 or 2.1, in the configuration file.")
}

sourceSecondaries <- sapply(list.files(pattern="[.]R$", path=pcPath, full.names=TRUE), source,simplify = FALSE)