

source activate htsflow



### fastq_masker, fastqQualityTrimmer: NO VERSIONN 0.0.13
conda install -y fastx_toolkit 


# bedGraphToBigWig: http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedGraphToBigWig
# bedToBigBed: http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedToBigBed
# bedClip
conda install -y ucsc-bedGraphToBigWig ucsc-bedToBigBed ucsc-bedclip

# bwa: http://sourceforge.net/projects/bio-bwa/files/bwa-0.7.12.tar.bz2/download
conda install -y bwa=0.6.2

# genomeCoverageBed: bedtool https://github.com/arq5x/bedtools2/releases/download/v2.24.0/bedtools-2.24.0.tar.gz
conda install -y bedtools=2.24.0

#samtools = http://sourceforge.net/projects/samtools/files/latest/download?source=files
conda install -y samtools=0.1.18

# Bismark NO VERSION 0.14.0!!
conda install -y bismark


# Bowtie NO VERSION 1.1.1 2.1.0 CANNOT INSTALL bowtie 1
conda install -y bowtie=1.1.2 bowtie2=2.2.5


# Fast QC NO VERSION 0.9
conda install -y fastqc=0.10.1


# subread/featurecount
conda install -y subread=1.4.5

# tophat
conda install -y tophat=2.0.8

# SRA toolkit (to import fastq from GEO)
conda install -y sratoolkit=2.5.7

# Macs2, NO VERSION  2.0.9
conda install -y macs2=2.1.0 

# R
conda install r=3.2.0


