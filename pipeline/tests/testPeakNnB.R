library(compEpiTools)

IDsec_FOLDER = "/data/BA/public_html/HTS-flow/DB/secondary/683/"
label = "test_Pol2_wt_2h"
BAMfolder <- paste0 ( "/data/BA/public_html/HTS-flow/", "DB/ALN/" )

CHIP_ID = "2051"
INPUT_ID = "2642"

#IDsec_FOLDER = "/data/BA/public_html/HTS-flow/DB/secondary/590/"
#label = "U2OS.+Dox.Pol2"
#BAMfolder <- paste0 ( "/data/BA/public_html/HTS-flow/", "DB/ALN/" )
#CHIP_ID = "2400"
#INPUT_ID = "2398"





FILEnarrow <- paste0( IDsec_FOLDER, "NARROW", "/", label, "_peaks.bed" )
FILEbroad <- paste0( IDsec_FOLDER, "BROAD", "/", label, "_broad_peaks.bed" )
BAM <- paste0( BAMfolder,CHIP_ID,".bam" )
BAMREF <- paste0(BAMfolder,INPUT_ID,".bam")

TempGRnarrow <- read.table( FILEnarrow, sep="\t")
TempGRbroad <- read.table( FILEbroad, sep="\t", skip=1)

peaksGRnarrow <- GRanges(Rle(as.character(TempGRnarrow$V1)),IRanges(TempGRnarrow$V2, TempGRnarrow$V3))
peaksGRbroad <- GRanges(Rle(as.character(TempGRbroad$V1)),IRanges(TempGRbroad$V2, TempGRbroad$V3))

peaksGR <- union( peaksGRnarrow, peaksGRbroad )


bind <- GRenrichment(peaksGR, BAM, BAMREF)

