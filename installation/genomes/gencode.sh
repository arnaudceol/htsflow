
export PATH=$PATH:/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/:/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/bowtie2-2.1.0/


# contaminant:

cp /hpcnfs/data/BA/htsflow2/data/contaminant_list.txt  /hpcnfs/techunits/bioinformatics/PublicData/FN/data//contaminant_list.txt


mkdir GRCm38
cd GRCM38
wget ftp://ftp.ebi.ac.uk/pub/databases/gencode/Gencode_mouse/release_M17/GRCm38.primary_assembly.genome.fa.gz
gunzip GRCm38.primary_assembly.genome.fa.gz

mv *.fa GRCm38.fa

/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/samtools faidx GRCm38.fa
/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/bowtie2-2.1.0/bowtie2-build GRCm38.fa GRCm38
/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/bwa index GRCm38.fa GRCm38

cut -f 1,2 GRCm38.fa.fai > GRCm38.chrom.sizes

mkdir GTF

wget ftp://ftp.ebi.ac.uk/pub/databases/gencode/Gencode_mouse/release_M17/gencode.vM17.annotation.gtf.gz -O GTF/genes.gtf.gz
gunzip GTF/genes.gtf.gz

cat   GTF/genes.gtf |perl -p -e 's/.*gene_id \"([^\"]*)\".*gene_name \"([^\"]*)\".*/$1\t$2/g' |grep -v '#' |sort -u > GTF/id2genename.txt

touch dummy.fq

/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin//tophat-2.0.8.Linux_x86_64//tophat -G GTF/genes.gtf  --transcriptome-index=transcriptome_data/transcripts GRCm38 dummy.fq

cd ..

mkdir GRCh38
cd GRCh38
wget ftp://ftp.ebi.ac.uk/pub/databases/gencode/Gencode_human/release_28/GRCh38.primary_assembly.genome.fa.gz
gunzip GRCh38.primary_assembly.genome.fa.gz

mv *.fa GRCh38.fa

/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/samtools faidx GRCh38.fa

/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/bowtie2-2.1.0/bowtie2-build GRCh38.fa GRCh38
/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin/bwa index GRCh38.fa GRCh38

cut -f 1,2 GRCh38.fa.fai > GRCh38.chrom.sizes


mkdir GTF

wget ftp://ftp.ebi.ac.uk/pub/databases/gencode/Gencode_human/release_28/gencode.v28.annotation.gtf.gz -O GTF/genes.gtf.gz
gunzip GTF/genes.gtf.gz

cat   GTF/genes.gtf |perl -p -e 's/.*gene_id \"([^\"]*)\".*gene_name \"([^\"]*)\".*/$1\t$2/g' |grep -v '#' |sort -u > GTF/id2genename.txt


touch dummy.fq

/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin//tophat-2.0.8.Linux_x86_64//tophat -G GTF/genes.gtf  --transcriptome-index=transcriptome_data/transcripts GRCh38 /hpcnfs/techunits/bioinformatics/PublicData/FN/out//preprocess//P18737/18737_R1.fq /hpcnfs/techunits/bioinformatics/PublicData/FN/out//preprocess//P18737/18737_R2.fq

R:

library(GenomicFeatures)
library(compEpiTools)

txdbLib<-'TxDb.Hsapiens.UCSC.hg38.knownGene'
metadata <- data.frame(name="Resource URL",
                       value="ftp://ftp.ebi.ac.uk/pub/databases/gencode/Gencode_human/")

txdb <- makeTxDbFromGFF(paste("GTF", "genes.gtf", sep="/"), format="gtf", dataSource="gencode", organism="Homo sapiens", metadata=metadata)
makeTxDbPackage(txdb = txdb, version = "1.0.0", author='htsflow', maintainer='htsflow <htsflow@htsflow.org>', destDir="txdb", pkgname=txdbLib )			
install.packages(paste("txdb", txdbLib, sep="/"), repos = NULL)



cd ..





/hpcnfs/techunits/bioinformatics/htsflow/v0.1/conda2/bin//tophat-2.0.8.Linux_x86_64//tophat -G GTF/genes.gtf  --transcriptome-index=transcriptome_data/transcripts TAIR10 /hpcnfs/techunits/bioinformatics/PublicData/FN/out//preprocess//P18737/18737_R1.fq /hpcnfs/techunits/bioinformatics/PublicData/FN/out//preprocess//P18737/18737_R2.fq

