HTSFLOW_BIN=/data/BA/htsflow2/bin/


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

# rename genome $version *

for file in genome.*; do
 mv "$file" "${file//genome/$version}"
done


$HTSFLOW_BIN/bwa index $version.fa 
$HTSFLOW_BIN/bowtie2-2.1.0/bowtie2-build $version.fa $version

rm -rf $species $igenomeFile.tar.gz

cd ../

# BS
mkdir `echo $version`_bs
cp `echo $version`/`echo $version`.fa  `echo $version`_bs/
cd `echo $version`_bs/
$HTSFLOW_BIN/bismark_v0.14.0/bismark_genome_preparation --path_to_bowtie $HTSFLOW_BIN/bowtie-1.1.1/ .
cd ..


done < genomes.txt

