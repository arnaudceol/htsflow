#!/bin/bash

deploydir=$1
htsflow_home=$2

echo $deploydir
echo $htsflow_home
#if [[ $# -ne 0 ]] ; then
#    echo 'Usage: sh ./software.sh deploydir htsflowhome'
#    echo "Where deploydir is the directory where the programs will be installed and htsflowhome the main HTS flow folder, which contains the installation directory"
#    exit 1
#fi


#cd /tmp

### fastq_masker, fastqQualityTrimmer: 
# part of the fastx_toolkit, http://hannonlab.cshl.edu/fastx_toolkit/fastx_toolkit_0.0.13_binaries_Linux_2.6_amd64.tar.bz2
wget http://hannonlab.cshl.edu/fastx_toolkit/fastx_toolkit_0.0.13_binaries_Linux_2.6_amd64.tar.bz2
bunzip2 fastx_toolkit_0.0.13_binaries_Linux_2.6_amd64.tar.bz2
tar xf fastx_toolkit_0.0.13_binaries_Linux_2.6_amd64.tar 
mv ./bin/fastq_masker ./bin/fastq_quality_trimmer $deploydir
rm -rf fastx/

# bedGraphToBigWig: http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedGraphToBigWig
wget http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedGraphToBigWig
chmod +x bedGraphToBigWig
mv bedGraphToBigWig $deploydir

# bedToBigBed: http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedToBigBed
wget http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64.v287/bedToBigBed
chmod +x bedToBigBed
mv bedToBigBed $deploydir


# bwa: http://sourceforge.net/projects/bio-bwa/files/bwa-0.7.12.tar.bz2/download
wget http://sourceforge.net/projects/bio-bwa/files/bwa-0.6.2.tar.bz2
bunzip2 bwa-0.6.2.tar.bz2
tar xf bwa-0.6.2.tar
cd bwa-0.6.2
make
cp bwa $deploydir
cd ..
rm -rf bwa-0.6.2

# genomeCoverageBed: bedtool https://github.com/arq5x/bedtools2/releases/download/v2.24.0/bedtools-2.24.0.tar.gz
wget https://github.com/arq5x/bedtools2/releases/download/v2.24.0/bedtools-2.24.0.tar.gz
tar xzf bedtools-2.24.0.tar.gz
cd bedtools2
make
cp bin/genomeCoverageBed $deploydir
cp bin/bedtools $deploydir
cd ..
rm -rf bedtool2

# bedClip
wget http://hgdownload.cse.ucsc.edu/admin/exe/linux.x86_64/bedClip
chmod +x bedClip
mv bedClip $deploydir


#samtools = http://sourceforge.net/projects/samtools/files/latest/download?source=files
# http://downloads.sourceforge.net/project/samtools/samtools/0.1.18/samtools-0.1.18.tar.bz2?r=&ts=1435814326&use_mirror=garr
wget http://sourceforge.net/projects/samtools/files/samtools/0.1.18/samtools-0.1.18.tar.bz2

bunzip2 samtools-0.1.18.tar.bz2
tar xf samtools-0.1.18.tar
cd samtools-0.1.18
make
cp samtools $deploydir
cd .. 
rm -rf samtools-0.1.18


# Bismark
wget http://www.bioinformatics.babraham.ac.uk/projects/bismark/bismark_v0.14.0.tar.gz
tar xzf bismark_v0.14.0.tar.gz
mv bismark_v0.14.0 $deploydir


# Bowtie
wget -O bowtie-1.1.1-src.zip http://sourceforge.net/projects/bowtie-bio/files/bowtie/1.1.1/bowtie-1.1.1-src.zip/download
unzip bowtie-1.1.1-src.zip
cd bowtie-1.1.1
make
cd ..
mv bowtie-1.1.1 $deploydir

# Bowtie 2
wget -O bowtie2-2.1.0-source.zip http://sourceforge.net/projects/bowtie-bio/files/bowtie2/2.1.0/bowtie2-2.1.0-source.zip/download
unzip bowtie2-2.1.0-source.zip
cd bowtie2-2.1.0
make
cd ..
mv bowtie2-2.1.0 $deploydir


# Fast QC
wget http://www.bioinformatics.babraham.ac.uk/projects/fastqc/fastqc_v0.9.3.zip
unzip fastqc_v0.9.3.zip
cd FastQC
chmod 755 fastqc
cd ..	
mv FastQC $deploydir

# subread/featurecount
wget -O subread-1.4.5-p1-source.tar.gz http://sourceforge.net/projects/subread/files/subread-1.4.5-p1/subread-1.4.5-p1-source.tar.gz/download
tar xzf subread-1.4.5-p1-source.tar.gz
cd subread-1.4.5-p1-source/src/
make -f Makefile.Linux
cd ../../
cp subread-1.4.5-p1-source/bin/featureCounts $deploydir
rm -rf subread*


# tophat
wget https://ccb.jhu.edu/software/tophat/downloads/tophat-2.0.8.Linux_x86_64.tar.gz
tar xzf tophat-2.0.8.Linux_x86_64.tar.gz
mv tophat-2.0.8.Linux_x86_64  $deploydir


tmpdir=`pwd`


# python
# 2.7 for MACS2
wget https://www.python.org/ftp/python/2.7.10/Python-2.7.10.tar.xz
tar xf Python-2.7.10.tar.xz
cd Python-2.7.10
./configure --prefix $deploydir/python-2.7
make altinstall prefix=$deploydir/python2.7 exec-prefix=$deploydir/python2.7

cd $deploydir/python2.7
./bin/python2.7 -m ensurepip
./bin/pip2.7   install --upgrade pip
./bin/pip2.7  install numpy
./bin/pip2.7  install configparser
./bin/pip2.7  install MySQL-python
./bin/pip2.7  install pymysql
./bin/pip2.7  install command
./bin/pip2.7  install -Iv pyDNase==0.1.6
./bin/pip2.7  install Cython
#./bin/pip2.7  install  -Iv MACS2==2.0.9

cd $tmpdir

# macs2 from source
wget   https://github.com/taoliu/MACS/archive/v2.0.9.tar.gz
tar xf  v2.0.9.tar.gz
cd MACS-2.0.9
export PYTHONPATH=$deploydir/python2.7/
$deploydir/python2.7/bin/python2.7 setup.py install --prefix $deploydir/python2.7/
cd ..
rm -rf MACS-2.0.9


# R 3.2
version=3.2.0
wget http://cran.rstudio.com/src/base/R-3/R-$version.tar.gz
tar xf R-$version.tar.gz
cd R-$version
./configure --prefix=$deploydir/R
make && make install
cd .. 
rm -rf R-$version

# install libraries
cd $deploydir/R
./bin/Rscript $htsflow_home/installation/install_packages.R




