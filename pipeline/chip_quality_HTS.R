# developer: Ottavio Croci - IIT@SEMM (ottavio.croci@iit.it) - http://genomics.iit.it/
#libraries
library(compEpiTools, quietly = TRUE)
library(TxDb.Mmusculus.UCSC.mm9.knownGene, quietly = TRUE)
library(Mus.musculus, quietly = TRUE)
library(org.Mm.eg.db, quietly = TRUE)
txdb<-TxDb.Mmusculus.UCSC.mm9.knownGene
dbsymbols<-org.Mm.egSYMBOL
db<- Mus.musculus

###############################################################################################################
#FILTERBED
###############################################################################################################

#reads MACS outputs, based on their names
readbed<-function(bedpath){
	#open "customed" bed file if end of file name is not _broad_peaks.bed || _peaks.xls
	if (!grepl("_peaks.xls", bedpath)&&!grepl("_broad_peaks.bed",bedpath)){
		bed<-read.table(bedpath,sep='\t',header=TRUE)
		l=length(bed[,1])
		print(paste("Opening customed bed file with ",l," peaks",sep=''))
		return(bed)
	}else{
		#reads sharp peaks file
		if (grepl("_peaks.xls", bedpath)){
			bed<-read.table(bedpath,sep='\t',header=TRUE)
			l=length(bed[,1])
			print(paste("Opening ",l," NARROW peaks from MACS... ",sep=''))
			bed<-bed[,1:3]
			colnames(bed)<-c("V1","V2","V3")
			return(bed)
		}
		#reads broad peaks file
		if (grepl("_broad_peaks.bed",bedpath)){
			#skip 1st line from MACS HTS flow output
			bed=read.table(bedpath,sep='\t',header=FALSE,skip=1)
			l=length(bed[,1])
			print(paste("Opening ",l," BROAD peaks from MACS... ",sep=''))
			bed=bed[c("V1","V2","V3")]
			return(bed)
		}
}
}

###############################################################################################################
#STD PLOT SINGLE CHIP - requires bai files
###############################################################################################################

std_plot=function(CHIP_ID, INPUT_ID=NULL, label, outFolder, typeOfpeakCalling, BAMfolder, txdb, dbsymbols, colore="grey50", upstream=2000, downstream=1000, ROI_upstream=5000, ROI_downstream=5000, bins=150, quant=.95 ){
	#CHIP_ID=HTS flow ID of ChIP bam file
	#INPUT_ID=HTS flow ID of input bam file
	#label=label selected by the user for that peak calling(for example, Myc_condition1)
	#outFolder= output folder with the secondary ID of the peak calling job
	#typeOfpeakCalling= MACSnarrow or MACSbroad, depending of the peak calling type
	#BAMfolder (/data/BA/public_html/HTS-flow/DB/ALN) - where bam files are stored, together with .bai (bam file indexes)
	#txdb= transcript database (for example TxDb.Mmusculus.UCSC.mm9.knownGene)
	#dbsymbols= dictionary for the conversion gene symbol -> gene ID (for example org.Mm.egSYMBOL)
	#upstream/downstream= promoter boundaries (default: [-2000;+1000] from the TSSs)
	#ROI_upstream/ROI_downstream= boundaries from the TSS to consider to plot TSS peak shape (default: [-5000;+5000])
	#bins= bins to use in GRcoverageInBins function to plot distal peaks profile (default:150)
	#quant= quantile to use to plot the frequency plot of the peaks length (default: .95, means 95% of the population, leaving widest peaks out)

	#create std_plot directory in which place the pdf output
	dire=paste(outFolder, "/std_plot/",sep='')
	if (!file.exists(dire)){
		dir.create(dire)
	}

	
	#create bedpath, bampath and input path, check the existence of the files and read the bed
	if (typeOfpeakCalling=="MACSbroad"){
		bedpath=paste(outFolder,"BROAD/",label,"_broad_peaks.bed",sep='')
	}
	if(typeOfpeakCalling=="MACSnarrow"){
		bedpath=paste(outFolder,"NARROW/",label,"_narrow_peaks.xls",sep='')
	}

	if(!file.exists(bedpath)){
		stop("ERROR: bed file doesn't exist!! Exiting...")
	}
	bampath=paste(BAMfolder,CHIP_ID,".bam",sep='')
	if(!file.exists(bampath)){
		stop("ERROR: bam file doesn't exist!! Exiting...")
	}
	if (!is.null(INPUT_ID)){
		input=paste(BAMfolder,INPUT_ID,".bam",sep='')
		if(!file.exists(input)){
			stop("ERROR: input bam file doesn't exist!! Exiting...")
		}
	}
	
	bed=readbed(bedpath)

	#annotation: convert bed to GRanges, finds promoters and make piechart. Annotation is made with the midpoint of GRange
	tratti<-GRanges(Rle(as.character(bed$V1)), IRanges(bed$V2, bed$V3))
	tratti2<-resize(tratti, width=1,fix='center')
	print(paste("Annotating peaks of ",label," with promoters defined as ",upstream," bp upstream and ",downstream," bp downstream...",sep=''))
	ga<-GRannotate(Object=tratti2,txdb=txdb,EG2GS=dbsymbols,upstream=upstream,downstream=downstream)
	ranges(ga)<-ranges(tratti)

	#uncomment if you want the annotation file in *.xls format
	# gabed=data.frame(seqname=seqnames(ga),start=start(ga),end=end(ga),strand=strand(ga),nearest_tx_name=ga$nearest_tx_name,
	# 		distance_fromTSS=ga$distance_fromTSS, nearest_gene_id=ga$nearest_gene_id, nearest_gene_symbol=ga$nearest_gene_symbol,
	# 		location=ga$location, location_tx_id=ga$location_tx_id, location_gene_id=ga$location_gene_id, location_gene_symbol=ga$location_gene_symbol)
	# write.table(gabed,paste("./std_plot/Annotations_",label,".xls",sep=''),quote=FALSE,sep='\t',row.names=FALSE)

	#define promoter, intra, inter (with this priority)
	prom<-ga[grepl("promoter",ga$location),]
	ws<-ga[!grepl("promoter",ga$location),]
	intra<-ws[grepl("genebody",ws$location),]
	inter<-ws[!grepl("genebody",ws$location),]
	elementi<-c(length(start(prom)),length(start(intra)),length(start(inter)))
	perc=c(round(length(start(prom))/length(bed$V1),2)*100,round(length(start(intra))/length(bed$V1),2)*100,round(length(start(inter))/length(bed$V1),2)*100)
	LABEL<-c(paste(perc[1],"% (",elementi[1],")",sep=''),paste(perc[2],"% (",elementi[2],")",sep=''),paste(perc[3],"% (",elementi[3],")",sep=''))
	totelements=length(bed$V1)

	#plots piecharts (parameters defined if we don't have bam file path)
	print(paste("Doing piechart of location of ",label, sep=''))	
	pdf(paste("./report_",label,".pdf",sep=''),height=16,width=18)
	par(mar=c(5,5,5,5),mfrow=c(3,2),oma=c(0,0,3,0))
	x=1
	y=-1
	pie(elementi,col=c("red","orange","green"),main=paste("Location (",totelements," total peaks)",sep=''),cex.main=3,labels=LABEL,cex=2.8)
	mtext(label, outer=T, line=-1,cex=3.5)
	par(xpd=NA)
	legend(x=x,y=y,legend=c("Promoters","Genebody","Intergenic") , col=c("red","orange","green") , pch=rep(19,3),cex=2)
	cuttedga=ga[width(ga)<quantile(width(ga),quant)]
	cuttedprom=prom[width(prom)<quantile(width(prom),quant)]
	cuttedintra=intra[width(intra)<quantile(width(intra),quant)]
	cuttedinter=inter[width(inter)<quantile(width(inter),quant)]
	xlim=c(min(density(width(cuttedga))$x ,density(width(cuttedprom))$x,density(width(cuttedintra))$x,density(width(cuttedinter))$x),
		   max(density(width(cuttedga))$x,density(width(cuttedprom))$x,density(width(cuttedintra))$x,density(width(cuttedinter))$x)  )
	ylim=c(0, max(max(density(width(cuttedprom))$y),max(density(width(cuttedintra))$y),max(density(width(cuttedinter))$y) ) )
	plot(density(width(cuttedga))$x,density(width(cuttedga))$y,pch=".",cex=2,xlab="Length",cex.lab=2.4,cex.axis=2.1,ylab="Frequency",ylim=ylim,xlim=xlim,
				main=paste("Peak length distribution (0 - ",quant*100,"% of distribution)",sep=''),cex.main=2.5,lwd=2,type="l")
	lines(density(width(cuttedprom))$x,density(width(cuttedprom))$y,cex=2,col="red",ylim=ylim,xlim=xlim,lty=1,lwd=2)
	lines(density(width(cuttedintra))$x,density(width(cuttedintra))$y,cex=2,col="orange",ylim=ylim,xlim=xlim,lty=1,lwd=2)
	lines(density(width(cuttedinter))$x,density(width(cuttedinter))$y,cex=2,col="green",ylim=ylim,xlim=xlim,lty=1,lwd=2)
	legend("topright" , c("All peaks","Promoter","Genebody","Intergenic") , col=c("black","red","orange","green") , lty=rep(1,4),lwd=3,cex=1.6)

	
	#coverage , with or without input...
	#splittin GRanges for parallelization (6+ cores):
	len=length(prom)%/%2
	prom1=prom[1:len]
	prom2=prom[(len+1):length(prom)]
	len=length(intra)%/%2
	intra1=intra[1:len]
	intra2=intra[(len+1):length(intra)]
	len=length(inter)%/%2
	inter1=inter[1:len]
	inter2=inter[(len+1):length(inter)]

	#coverprom1<-mcparallel(GRcoverage(prom1,bam=bampath,Snorm=FALSE,Nnorm=TRUE))
	#coverintra1<-mcparallel(GRcoverage(intra1,bam=bampath,Snorm=FALSE,Nnorm=TRUE))
	#coverinter1<-mcparallel(GRcoverage(inter1,bam=bampath,Snorm=FALSE,Nnorm=TRUE))
	#coverprom2<-mcparallel(GRcoverage(prom2,bam=bampath,Snorm=FALSE,Nnorm=TRUE))
	#coverintra2<-mcparallel(GRcoverage(intra2,bam=bampath,Snorm=FALSE,Nnorm=TRUE))
	#coverinter2<-mcparallel(GRcoverage(inter2,bam=bampath,Snorm=FALSE,Nnorm=TRUE))

	batch_arg1 <- c(prom1, intra1, inter1, prom2, intra2, inter2)
	batch_arg2 <- rep(bampath,6)


	if(!is.null(INPUT_ID)){
		print(paste("Coverage of peaks of ",label," with input!",sep=''))
		#coverprominput1<-mcparallel(GRcoverage(prom1,bam=input,Snorm=FALSE,Nnorm=TRUE))
		#coverintrainput1<-mcparallel(GRcoverage(intra1,bam=input,Snorm=FALSE,Nnorm=TRUE))
		#coverinterinput1<-mcparallel(GRcoverage(inter1,bam=input,Snorm=FALSE,Nnorm=TRUE))
		#coverprominput2<-mcparallel(GRcoverage(prom2,bam=input,Snorm=FALSE,Nnorm=TRUE))
		#coverintrainput2<-mcparallel(GRcoverage(intra2,bam=input,Snorm=FALSE,Nnorm=TRUE))
		#coverinterinput2<-mcparallel(GRcoverage(inter2,bam=input,Snorm=FALSE,Nnorm=TRUE))
		
		batch_arg1 <- append(batch_arg1, c(prom1,intra1,inter1,prom2,intra2,inter2))
		batch_arg2 <- append(batch_arg2, rep(input, 6))
		
	}else{
		print("Coverage of peaks of ",label," WITHOUT input!",sep='')
	}
	#if(!is.null(INPUT_ID)){
	#	jobs=list(coverprom1,coverintra1,coverinter1,coverprom2,coverintra2,coverinter2,coverprominput1,coverintrainput1,coverinterinput1,coverprominput2,coverintrainput2,coverinterinput2)
	#}else{
	#	jobs=list(coverprom1,coverintra1,coverinter1,coverprom2,coverintra2,coverinter2)
	#}
	
	reg <- makeHtsflowRegistry("HF_StdPlot")
	ids <- batchMap(reg, fun=GRcoverage, batch_arg1, bam=batch_arg2, Snorm=rep(FALSE, length(batch_arg1)), Nnorm=rep(FALSE, length(batch_arg1)))
	submitJobs(reg)
	#showStatus(reg)
	#done <- submitJobs(reg)

	waitForJobs(reg)
	
	if (lenght(findErrors()) > 0) {
		
	}
	
	res <- reduceResultsMatrix(reg)

	#res=mccollect(jobs,wait=TRUE)
	coverprom1=res[[1]]
	coverintra1=res[[2]]
	coverinter1=res[[3]]
	coverprom2=res[[4]]
	coverintra2=res[[5]]
	coverinter2=res[[6]]
	if(!is.null(INPUT_ID)){
		coverprominput1=res[[7]]
		coverintrainput1=res[[8]]
		coverinterinput1=res[[9]]
		coverprominput2=res[[10]]
		coverintrainput2=res[[11]]
		coverinterinput2=res[[12]]
	}

	#now subtract chip-input
	if(!is.null(INPUT_ID)){
		coverprom=c((coverprom1-coverprominput1),(coverprom2-coverprominput2))
		coverintra=c((coverintra1-coverintrainput1),(coverintra2-coverintrainput2))
		coverinter=c((coverinter1-coverinterinput1),(coverinter2-coverinterinput2))
	}else{
		coverprom=c(coverprom1,coverprom2)
		coverintra=c(coverprom1,coverprom2)
		coverinter=c(coverprom1,coverprom2)
	}
	rm(res)
	#plot(0,type='n',axes=FALSE,ann=FALSE)

	boxplot(log2(c(coverprom,coverintra,coverinter)),log2(coverprom),log2(coverintra),log2(coverinter),outline=FALSE,col=c(colore,"red","orange","green"),xaxt='n',
							ylab="Enrichment: log2 (ChIP - input)",cex.lab=2.5,cex.axis=1.8,cex.main=3,main="Total reads",notch=TRUE,varwidth=TRUE)
	labs=seq(1,4,1)+0
	axis(1,at=seq(1,4,1)+0,labels=c("All peaks","Promoters","Genebody","Intergenic"),cex.axis=1.8)
	all_density=c(coverprom/(end(prom)-start(prom)),coverintra/(end(intra)-start(intra)),coverinter/(end(inter)-start(inter)))
	boxplot(all_density,coverprom/(end(prom)-start(prom)),coverintra/(end(intra)-start(intra)),coverinter/(end(inter)-start(inter)),outline=FALSE,	
				col=c(colore,"red","orange","green"),xaxt='n',ylab="Reads per million/base pair",cex.lab=2.5,cex.axis=1.8,cex.main=3,main="Read density",notch=TRUE,varwidth=TRUE)
	labs=seq(1,4,1)+0
	axis(1,at=seq(1,4,1)+0,labels=c("All peaks","Promoters","Genebody","Intergenic"),cex.axis=1.8)
	#...................................................

	#now TSSs profile. We consider, for the plot, all promoters (-Upstream,+Downstream) that overlap with peaks. Is strand weighted!
	#Doesn't matter how many times a peak overlap. Considered as one in any case
	ROI=promoters(txdb, upstream=ROI_upstream,downstream=ROI_downstream)
	ROImeno=ROI[strand(ROI)=="-",]
	ROIpiu=ROI[strand(ROI)=="+",]	
	idxmeno=countOverlaps(ROImeno,prom)
	idxpiu=countOverlaps(ROIpiu,prom)
	ROI_filteredmeno=ROImeno[idxmeno>0]
	ROI_filteredpiu=ROIpiu[idxpiu>0]
	
	#now the coverage for the peaks shape:
	print(paste("Coverage of ",label," for TSS profile (strand weighted)...",sep=''))
	newbins=round(((ROI_upstream+ROI_downstream)/10),0)
	#covmeno=mcparallel(GRcoverageInbins(Object=ROI_filteredmeno,bam=bampath,Nnorm=TRUE,Nbins=newbins,Snorm=TRUE))
	#covpiu=mcparallel(GRcoverageInbins(Object=ROI_filteredpiu,bam=bampath,Nnorm=TRUE,Nbins=newbins,Snorm=TRUE))

	if (!is.null(input)){
		print(paste("Coverage of input for TSS profile (strand weighted)...",sep=''))
#		covpiu_input=mcparallel(GRcoverageInbins(Object=ROI_filteredpiu,bam=input,Nnorm=TRUE,Nbins=newbins,Snorm=TRUE))
#		covmeno_input=mcparallel(GRcoverageInbins(Object=ROI_filteredmeno,bam=input,Nnorm=TRUE,Nbins=newbins,Snorm=TRUE))
		batch_args_object <- c(ROI_filteredmeno, ROI_filteredpiu,ROI_filteredpiu,ROI_filteredmeno)
	} else {
		batch_args_object <- c(ROI_filteredmeno, ROI_filteredpiu)
	}
	
	
	#if (!is.null(input)){
	#	jobs=list(covmeno,covpiu,covpiu_input,covmeno_input)
	#}else{
	#	jobs=list(covmeno,covpiu)
	#}

	reg <- makeHtsflowRegistry("HF_StdPlot2")
	ids <- batchMap(reg, fun=list, Object=batch_args_object, bam=rep(input, length(batch_args_object)), Nnorm=rep(TRUE, length(batch_args_object)), Nbins=rep(newBins, length(batch_args_object)),Snorm=rep(TRUE, length(batch_args_object)))
	submitJobs(reg)
	#showStatus(reg)
	#done <- submitJobs(reg)

	waitForJobs(reg)

	#res=mccollect(jobs,wait=TRUE)
	res <- reduceResultsMatrix(reg)
	covmeno=res[[1]]
	covpiu=res[[2]]
	if (!is.null(input)){
		covpiu_input=res[[3]]
		covmeno_input=res[[4]]
	}
	

	matrixmeno=covmeno[,ncol(covmeno):1]
	matrix_NA=rbind(covpiu,matrixmeno)
	#NAs exclusion
	matrix=matrix_NA[apply(matrix_NA,1,function(i) !any(is.na(i))),]
	meanpoints=apply(matrix, 2, mean)
	
	if (!is.null(input)){	
		matrixmeno_input=covmeno_input[,ncol(covmeno_input):1]
		matrix_input_NA=rbind(covpiu_input,matrixmeno_input)
		#NAs exclusion
		matrix_input=matrix_input_NA[apply(matrix_input_NA,1,function(i) !any(is.na(i))),]
		meanpoints_input=apply(matrix_input, 2, mean)
	}

	#now coverage of distal peak profile:
	#ws=resize(ws,width=10000,fix="center")
	print(paste("Coverage of ",label," for distal peak profile...",sep=''))
	#preparing for parallelization:
	len=length(ws)%/%2
	ws1=ws[1:len]
	ws2=ws[(len+1):length(ws)]

	#cov_ws1=mcparallel(GRcoverageInbins(Object=ws1,bam=bampath,Nnorm=TRUE,Snorm=TRUE,Nbins=bins))
	#cov_ws2=mcparallel(GRcoverageInbins(Object=ws2,bam=bampath,Nnorm=TRUE,Snorm=TRUE,Nbins=bins))


	batch_args_object <- c(ws1,ws2)
	batch_args_input <- c(bampath,bampath)

	if(!is.null(input)){
		print(paste("Coverage of input for distal peak profile...",sep=''))
	#	cov_input_ws1=mcparallel(GRcoverageInbins(Object=ws1,bam=input,Nnorm=TRUE,Snorm=TRUE,Nbins=bins))
	#	cov_input_ws2=mcparallel(GRcoverageInbins(Object=ws2,bam=input,Nnorm=TRUE,Snorm=TRUE,Nbins=bins))
		batch_args_object <- append(batch_args_object, c(ws1,ws2))
		batch_args_bam <- append(batch_args_bam, c(input,input))
	}
	
	#if(!is.null(input)){
	#	jobs=list(cov_ws1,cov_ws2,cov_input_ws1,cov_input_ws2)
	#}else{
	#	jobs=list(cov_ws1,cov_ws2)
	#}

	#res=mccollect(jobs,wait=TRUE)
	reg <- makeHtsflowRegistry("HF_StdPlot3")
	ids <- batchMap(reg, fun=GRcoverage, Object=batch_args_object, bam=batch_args_bam, Nnorm=rep(TRUE, length(batch_args_object)) ,Snorm=rep(TRUE, length(batch_args_object)),Nbinsrep(bins, length(batch_args_object)))
	submitJobs(reg)
	#showStatus(reg)
	#done <- submitJobs(reg)

	waitForJobs(reg)
	res <- reduceResultsMatrix(reg)

	cov_ws1=res[[1]]
	cov_ws2=res[[2]]
	if(!is.null(input)){
		cov_input_ws1=res[[3]]
		cov_input_ws2=res[[4]]
	}
	cov_ws=rbind(cov_ws1,cov_ws2)
	if(!is.null(input)){
		cov_input_ws=rbind(cov_input_ws1,cov_input_ws2)
	}

	#NAs exclusion
	matrix_ws=cov_ws[apply(cov_ws,1,function(i) !any(is.na(i))),]
	meanpoints_ws=apply(matrix_ws, 2, mean)
	
	if(!is.null(input)){
		#NAs exclusion
		matrix_input_ws=cov_input_ws[apply(cov_input_ws,1,function(i) !any(is.na(i))),]
		meanpoints_input_ws=apply(matrix_input_ws, 2, mean)
	}

	#plot TSSs profiles and distal peak profiles, with the same scale (library-normalized total reads (rpm))
	if (!is.null(input)){
		minimo=min(min(meanpoints),min(meanpoints_input),min(meanpoints_ws),min(meanpoints_input_ws))
		massimo=max(max(meanpoints),max(meanpoints_input),max(meanpoints_ws),max(meanpoints_input_ws))
	}else{
		minimo=min(min(meanpoints),min(meanpoints_ws))
		massimo=max(max(meanpoints),max(meanpoints_ws))
	}

	#................................
	#[seq(1,length(meanpoints),by=10)]
	plot(meanpoints,pch=".",cex=2,xaxt="n",xlab="TSS",cex.lab=2.4,cex.axis=2.1,ylab="Reads per million/base pair",ylim=c(minimo,massimo),
						main=paste(" Profile TSS (",length(start(ROI_filteredmeno))+length(start(ROI_filteredpiu))," regions)- strand weighted",sep=''),cex.main=2.5,lwd=2,type="l")
	mtext(label, outer=T, line=-1,cex=3.5)
	if (!is.null(input)){
		lines(meanpoints_input,cex=2,col="black",xaxt="n",lty=2,lwd=2)
	}
	axis(1,at=seq(1,length(meanpoints),length(meanpoints)/2-1),labels=c("-5000","0","+5000"),cex.axis=1.8)
	if (!is.null(input)){
		legend("topright" , c("ChIP","input") , col=rep("black",2) , lty=c(1,2),lwd=3,cex=1.6)
	}else{
		legend("topright" , "ChIP" , col="black" , lty=1,lwd=3,cex=1.6)
	}

	plot(meanpoints_ws,pch=".",cex=2,xaxt="n",xlab="Peak center",cex.lab=2.4,cex.axis=2.1,ylab="Reads per million/base pair",ylim=c(minimo,massimo),
						main=paste("Profile distal peaks (",length(start(ws))," regions)",sep=''),cex.main=2.5,lwd=2,type="l")
	if (!is.null(input)){
		lines(meanpoints_input_ws,cex=2,col="black",xaxt="n",lty=2,lwd=2)
	}
	axis(1,at=seq(1,length(meanpoints_ws),length(meanpoints_ws)/2-1),labels=c("Peak start","Peak center","peak end"),cex.axis=1.8)
	if (!is.null(input)){
		legend("topright" , c("ChIP","input") , col=rep("black",2) , lty=c(1,2),lwd=3,cex=1.6)
	}else{
		legend("topright" , "ChIP" , col="black" , lty=1,lwd=3,cex=1.6)
	}
	dev.off()


}




