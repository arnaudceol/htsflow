#++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
#  Create the TranscriptDb object from gtf file
#++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# load the library for creating the gencode transcript database and extracting and processing the features
library("GenomicFeatures")
library("GRanges")
# Get the chromosome info as a dataframe
# One can use the script fethChromsomeSize from UCSC to the get info, filter it to remove information from 
# non-std chromosomes
# Add the header "chrom length  is_circular" additional column to say that none of the chromosomes are circular.
#   chrom length  is_circular
#   chr1 249250621       FALSE
#   chr2 243199373       FALSE
#   chr3 198022430       FALSE
chrom.info=read.table(file="/home/kalyankpy/hg19/hg19.size", header=T)
# Create the transcriptdb from the gencode gtf file
# Download the latest gencode comprehensive gtf file from gencode website
gencode<-makeTranscriptDbFromGFF("/home/kalyankpy/Downloads/gencode.v19.annotation_2.gtf", 
		format="gtf", exonRankAttributeName="exon_number", chrominfo=chrom.info, 
		dataSource=paste("ftp://ftp.sanger.ac.uk/pub/gencode/Gencode_human/release_19/gencode.v19.annotation.gtf.gz"),
		species="Homo Sapies")
# Save the transcriptDb object as a sql database object
saveDb(gencode, file="/home/kalyankpy/reg_elements/gencode_human_v19.sqlite")
loadDb("/home/kalyankpy/reg_elements/gencode_human_v19.sqlite")
#### Create GRAnges objects for the each feature
genes.gencode<-sort(genes(gencode))
intergenic.gencode<-gaps(genes.gencode)
transcript.gencode<-sort(transcripts(gencode))
cds.gencode<-sort(cds(gencode))
exons.gencode<-sort(exons(gencode))
introns.gencode<-sort(unlist(intronsByTranscript(gencode)))
# save the combined obj for easy loading in future.
save(genes.gencode,intergenic.gencode,transcript.gencode,cds.gencode,exons.gencode,introns.gencode, file="/home/kalyankpy/reg_elements/gencode_all_features.rda")
# Load the database in future
load("/home/kalyankpy/reg_elements/gencode_all_features.rda")
# Alternatively save all the above objects into a single 'GenomicRangesList'
annotation.all.features=GenomicRangesList(genes.gencode,intergenic.gencode,transcript.gencode,cds.gencode,exons.gencode,introns.gencode)
# save this list of features
save(annotation.all.features, file="/home/kalyankpy/reg_elements/gencode_all_features_list.rda")
# Load in future
load("/home/kalyankpy/reg_elements/gencode_all_features_list.rda")