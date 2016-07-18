library( org.Mm.eg.db , quietly = TRUE)
EG2GSlist <- as.list( org.Mm.egSYMBOL )

dirout='/home/mmorelli/public_html/Alessandra/SpikeIn/'

samples=c(15693,15694,15695, 15699,15700,15701, 15702,15703,15704, 15705,15706,15707, 15696,15697,15698, 15734,
		15728,15729,15730, 15735,15736,15737, 15738,15739,15740, 15741,15742,15743, 15731,15732,15733)
snames=c('S_0h_wt_1','S_0h_wt_2','S_0h_wt_3', 'S_2h_wt_1','S_2h_wt_2','S_2h_wt_3', 
		'S_4h_wt_1','S_4h_wt_2','S_4h_wt_3', 'S_8h_wt_1','S_8h_wt_2','S_8h_wt_3', 
		'S_24h_wt_1','S_24h_wt_2','S_24h_wt_3', 'S_24h_wt_plus',
		'S_0h_flox_1','S_0h_flox_2','S_0h_flox_3', 'S_2h_flox_1','S_2h_flox_2','S_2h_flox_3', 
		'S_4h_flox_1','S_4h_flox_2','S_4h_flox_3', 'S_8h_flox_1','S_8h_flox_2','S_8h_flox_3', 
		'S_24h_flox_1','S_24h_flox_2','S_24h_flox_3')


samples=IDpreList
snames=sampleNames

countfiles=paste0('/data/BA/public_html/HTS-flow-data/COUNT/', samples, '.count')
BAMs=paste0('/data/BA/public_html/HTS-flow-data/ALN/', samples, '.bam')

#lsize=array(dim=length(samples))
#for(i in 1:length(samples))	lsize[i]=system(paste('samtools view -c', BAMs[i]), intern=TRUE)
#saveRDS(lsize, file=paste0(dirout, 'lsize.rds'))
lsize=readRDS(paste0(dirout, 'lsize.rds'))

RPKM2=readRDS('/data/BA/public_html/HTS-flow-data/secondary/995/RPKMS.rds')
RPKM2=RPKM2[,c(31:29,25:17,28:26,10,16:14,9:1,13:11)]


counts=read.table(countfiles[1], header=TRUE)[,7]
for(i in 2:length(samples))	counts=cbind(counts,read.table(countfiles[i], header=TRUE)[,7])
colnames(counts)=snames
gnames=as.character(read.table(countfiles[1], header=TRUE)[,1])
rownames(counts)=gnames
l=read.table(countfiles[1], header=TRUE)[,6]

tmp=counts/l*10^9
RPKM=tmp/as.numeric(lsize)

RPKM <- as.matrix(RPKM[names(EG2GSlist[rownames(RPKM)][!sapply(EG2GSlist[rownames(RPKM)], is.null)]),] )
rownames(RPKM) <- EG2GSlist[rownames(RPKM)]
RPKM <- as.matrix( RPKM[order(rownames(RPKM)),] )


eRPKM <- as.matrix(eRPKM[names(EG2GSlist[rownames(eRPKM)][!sapply(EG2GSlist[rownames(eRPKM)], is.null)]),] )
rownames(eRPKM) <- EG2GSlist[rownames(eRPKM)]
eRPKM <- as.matrix( eRPKM[order(rownames(eRPKM)),] )














