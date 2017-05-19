

source activate htsflow

conda config --add channels conda-forge
conda config --add channels defaults
conda config --add channels r
conda config --add channels bioconda

#Install first python 2.7, to avoid installing version 3 indirectly
conda install -y python=2.7

#Macs 2
conda install -y -c hcc macs2=2.1.0



### fastq_masker, fastqQualityTrimmer: NO VERSIONN 0.0.13
# bedGraphToBigWig: http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedGraphToBigWig
# bedToBigBed: http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedToBigBed
# bedClip
conda install -y fastx_toolkit  ucsc-bedGraphToBigWig ucsc-bedToBigBed ucsc-bedclip

# bwa: http://sourceforge.net/projects/bio-bwa/files/bwa-0.7.12.tar.bz2/download
# genomeCoverageBed: bedtool https://github.com/arq5x/bedtools2/releases/download/v2.24.0/bedtools-2.24.0.tar.gz
#samtools = http://sourceforge.net/projects/samtools/files/latest/download?source=files
# Bismark NO VERSION 0.14.0!!
# Bowtie NO VERSION 1.1.1 2.1.0 CANNOT INSTALL bowtie 1
# Fast QC NO VERSION 0.9
conda install -y bwa=0.6.2 bedtools=2.24.0 samtools=0.1.18 bismark bowtie=1.1.2 bowtie2=2.2.5 fastqc=0.10.1


# subread/featurecount
# tophat NO VERSION 2.0.8
# SRA toolkit (to import fastq from GEO)
#  subreads , NO VERSION 1.4
conda install -y tophat=2.1.0 sra-tools=2.6.3 subread=1.5.0 

## Macs2, NO VERSION  2.0.9
##conda install -y macs2=2.1.0 

# R
conda install -y r=3.2.0



# mysql 
conda install -y mysql

# For some reason, we need this to avoid bug:
# conda R symbol lookup error: libreadline.so.6: "undefined symbol: PC"
conda remove --force readline

