while IFS=$'\ ' read -r -r species host version
do

igenomeFile=`echo $species`_`echo $host`_`echo $version`
mkdir $version 
cd $version

mkdir GTF/

wget ftp://igenome:G3nom3s4u@ussd-ftp.illumina.com/$species/$host/$version/$igenomeFile.tar.gz

tar xzf $igenomeFile.tar.gz
cp $species/$host/$version/Annotation/Genes/genes.gtf GTF/
cp $species/$host/$version/Sequence/Bowtie2Index/* . 
cp $species/$host/$version/Sequence/BWAIndex/* .
cp $species/$host/$version/Sequence/WholeGenomeFasta/genome.fa* .

rename genome $version *

bwa index $version.fa 
bowtie2-build $version.fa $version

done < genomes.txt

