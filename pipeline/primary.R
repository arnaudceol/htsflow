# Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.


#######################################################################
## primary.R ##########################################################
#######################################################################
## This file contains the whole set of functions to perform      ######
## primary analysis in HTS-flow, from fastq extraction, filter   ######
## alignment and generation of big wig files for genome browser  ######
#######################################################################
#######################################################################


## pipeline.R generates all the informartion needed to call
## primary_Pipeline.
## sample contains the primary ID and the path to fastq files.
## flags contains all the information in the database associated to sample.
## paths is a general call the DB table paths using paths <- HTSFLOW_Path()
## genomePaths contains the names to reference genome created with function
## getGenomePaths( paths, flags$ref_genome )
primaryPipeline <- function( sample, flags, genomePaths ) {
    primaryId <- names(sample)
    bamFileName <- paste0 ( getHTSFlowPath("HTSFLOW_ALN"), "/", primaryId, ".bam" )

    # location of files important for the primary analysis ############
    outPath <- getPreprocessDir()
    tmpFold <- getHTSFlowPath("HTSFLOW_TMP")

	loginfo(paste("Start primary, primary id: ", primaryId, ", BAM file: ", bamFileName, ", Pre-process directory: ", outPath, ", tmp directory: ", tmpFold))
	
    ## update the DB with dateStart and status 'running'
    SQL <-
        paste(
            "UPDATE primary_analysis SET dateStart=NOW(), status='running pipeline..' WHERE id="
            ,primaryId
            ,sep=""
        )
    res <- updateInfoOnDB( SQL )

    ## IF BAM FILE DOES NOT EXIST IT WILL EXTRACT THE READS AND ALIGN THEM TO THE REFERENCE GENOME
    ## USING THE APPROPRIATE ALIGNER.
    if (!file.exists(bamFileName)){
        ## update the DB with status 'quality control'
        SQL <-
            paste(
                "UPDATE primary_analysis SET status='quality control on reads..' WHERE id="
                ,primaryId
                ,sep=""
            )
        res <- updateInfoOnDB( SQL )

        ## qualityCheckOnReads FUNCTION EXTRACTS THE READS, REMOVE THE BAD READS BASED ON QUALITY
        ## TRIMMING AND MASKING
        qualityCheckOnReads( flags, sample, outPath )

        ## WHEN THE READS ARE READY, BASED ON THE KIND OF EXPERIMENT THE APPROPRIATE ALIGNER WILL
        ## BE USED, AS WELL THE COUNT OF THE ALIGNED READS AND THE REMOVAL OF PCR DUPLICATES
        if( as.numeric( flags$alignment ) ) {
            if ( flags$aln_prog == "tophat" ) {
                doTophatAlignment( sample , outPath, genomePaths, 'genome', flags )
            }
            if ( flags$aln_prog == "bwa" ) {
                doBwaAlignment( sample , outPath, genomePaths, 'genome', flags )
            }
            if ( flags$aln_prog == "bismark" ) {
                doBismarkAlignment( sample , outPath, genomePaths, 'genome', flags )
            }
            ## if the analysis requires removal of PCR duplicates, doUniquelyAlignedBam
            ## returns a BAM file without duplicates and generates the corresponding
            ## index file.
            if ( as.numeric( flags$rm_duplicates ) ) {
                unique_reads <- doUniquelyAlignedBam( sample )
            }
        }
    }

    # update the DB with the counting aligned reads status
    SQL <- paste("UPDATE primary_analysis SET status='counting aligned reads..' WHERE id=", primaryId ,sep="")
    res <- updateInfoOnDB( SQL )
    ## countReadsNum simply runs samtools view -c on a sample and updates the DB with this number
    countReadsNum( sample, flags )

    ## if the analysis is from RNA-Seq samples we want to extract the reads per gene count using
    ## featureCounts tool (incredibly much faster than HTSeqCount)
    if ( flags$seq_method=="RNA-Seq" ) {
        ## update the DB with the "deconvolute reads.." status
        SQL <- paste("UPDATE primary_analysis SET status='deconvolute reads..' WHERE id=", primaryId ,sep="")
        res <- updateInfoOnDB( SQL )
        doFeatureCounts( sample, genomePaths, flags )
    }

	## generate the bw file. If the analysis comes from a BS experiments we need
    ## to pass the BS genome instead of the regular one. This is why exists two
    ## different functions for this task: doBW and doBW_bs
    if ( flags$seq_method == 'BS-Seq' ) {
        doBW_bs( sample, genomePaths )
    } else {
        doBW( sample, genomePaths )
    }
    ## The last tool to be launched will be FastQC. fastQCexec creates a report from FastQC in ./QC/ folder.
    ## For the future: add on the web interface a link to this output.
    SQL <- paste("UPDATE primary_analysis SET status='FastQC quality control..' WHERE id=", primaryId ,sep="")
    res <- updateInfoOnDB( SQL )
    fastQCexec( sample )

    # REMOVE THE TMP FILE if needed otherwise they will be moved to TMP folder.
    if ( as.numeric( flags$rm_tmp_files ) ) {
        loginfo ("Delete temporary files..")
		## command <- paste0 ( "rm -f ", outPath, primaryId , "*" )
		## tryOrExit(command, "delete temporary files")
		deleteFile(outPath, recursive = TRUE )
    } else {
        loginfo ("Move files to TMP/ folder..")
        command <- paste0( "mv ", outPath, primaryId, "* ", tmpFold)
		tryOrExit(command, "moving files to temporary folder")
    }
    ## Finally let's update the status with 'completed'.
    SQL <- paste("UPDATE primary_analysis SET status='completed', dateEnd=NOW() WHERE id=", primaryId ,sep="")
    res <- updateInfoOnDB( SQL )
}

qualityCheckOnReads <- function( flags, sample, outPath ){
    ## This function will check the options contained in flags and
    ## arrange the entire command line to be executed with system.
    primaryId <- names(sample)
    ## the code is divided for working separately paired end and single end data.
    if( as.numeric( flags$paired ) ) {
        tag1 <- " -not -iname  '*_R2*'"
        tag2 <- " -iname  '*_R2*'"
        fastqFileNameR1 <- paste0(
            outPath
            ,'/'
            , primaryId
            , '_R1'
            , '.fq'
        )
        fastqFileNameR2 <- paste0(
            outPath
            ,'/'
            , primaryId
            , '_R2'
            , '.fq'
        )
        ## Extract fastq contains the line for unzipping fastq files
        cmd1 <- getExtractFastqCommand( sample, tag1 )
        cmd2 <- getExtractFastqCommand( sample, tag2 )
        if ( as.numeric( flags$origin ) != 2 ) {
            if( as.numeric( flags$rm_bad_reads ) ) {
                ## line of code for removing reads flagged as bad from the sequencer and rename the reads
                cmd1 <- paste0( cmd1, ' | ', getFastqFilterBadReadsCommand() )
                cmd2 <- paste0( cmd2, ' | ', getFastqFilterBadReadsCommand() )
            } else {
                ## otherwise simply rename the reads
                cmd1 <- paste0( cmd1, ' | ', getRenameReadsCommand() )
                cmd2 <- paste0( cmd2, ' | ', getRenameReadsCommand() )
            }
        }
        if( as.numeric( flags$trimming ) ) {
            ## line of code for add trimming part in the piped command
            cmd1 <- paste0( cmd1, ' | ', getFastqTrimmingCommand(  ) )
            cmd2 <- paste0( cmd2, ' | ', getFastqTrimmingCommand(  ) )
        }
        if( as.numeric( flags$masking ) ) {
            ## line of code for add masking part in the piped command
            cmd1 <- paste0( cmd1, ' | ', getFastqMaskingCommand(  ) )
            cmd2 <- paste0( cmd2, ' | ', getFastqMaskingCommand(  ) )
        }
        ## finally we move all the ouput from RAM into two files with the
        ## fastq file names.
        cmd1 <- paste0( cmd1, ' > ', fastqFileNameR1 )
        cmd2 <- paste0( cmd2, ' > ', fastqFileNameR2 )
        tryOrExit(cmd1, "quality check on reads")
		tryOrExit(cmd2, "quality check on reads")
        if( as.numeric( flags$trimming ) ) {
            ## if the user ask for trimming the reads we have to perform the sorting of
            ## the reads to avoid problems to tophat in which reads have to be sorted.
            fastqSort( fastqFileNameR1, fastqFileNameR2 )
        }
        ## doCountFastqReads returns the number of reads after the quality filtering before the alignment
        n1 <- doCountFastqReads( fastqFileNameR1 )
        n2 <- doCountFastqReads( fastqFileNameR2 )
        totReads <- n1+n2
        ## updates the number of raw_reads in primary_analysis
        SQL <- paste0( "UPDATE primary_analysis SET raw_reads_num='",totReads,"' WHERE id=", primaryId )
        res <- updateInfoOnDB( SQL )
    } else {		
		tag1 <- " -not -iname  '*_R2*'"
        fastqFileNameR1 <- paste0(
            outPath
            ,'/'
            , primaryId
            , '_R1'
            , '.fq'
        )
        ## Extract fastq contains the line for unzipping fastq files
        ## line of code for removing reads flagged as bad from the sequencer and rename the reads
        ## otherwise simply rename the reads
        cmd1 <- getExtractFastqCommand( sample, tag1 )
        if ( as.numeric( flags$origin ) != 2 ) {
            if( as.numeric( flags$rm_bad_reads ) ) {
                cmd1 <- paste0( cmd1, ' | ', getFastqFilterBadReadsCommand() )
            } else {
                cmd1 <- paste0( cmd1, ' | ', getRenameReadsCommand() )
            }
        }
        if( as.numeric( flags$trimming ) ) {
            ## line of code for add trimming part in the piped command
            cmd1 <- paste0( cmd1, ' | ', getFastqTrimmingCommand(  ) )
        }
        if( as.numeric( flags$masking ) ) {
            ## line of code for add masking part in the piped command
            cmd1 <- paste0( cmd1, ' | ', getFastqMaskingCommand(  ) )
        }
        ## finally we move all the ouput from RAM into two files with the
        ## fastq file names.
        cmd1 <- paste0( cmd1, ' > ', fastqFileNameR1 )
		tryOrExit(cmd1, "quality check on reads")

        ## doCountFastqReads returns the number of reads after the quality filtering before the alignment
        totReads <- doCountFastqReads( fastqFileNameR1 )
        ## updates the number of raw_reads in primary_analysis
        SQL <- paste0( "UPDATE primary_analysis SET raw_reads_num='",totReads,"' WHERE id=", primaryId )
        res <- updateInfoOnDB( SQL )
    }
}

getExtractFastqCommand <- function ( sample, tag ) {
    DirToProcess <- sample[[1]]
    execute <- paste0(
            "find "
            ,DirToProcess
            ," \\( -iname '*.fastq.gz' -o  -iname '*.fq.gz' \\) "
            ,tag
            ," | sort | xargs -I {} pigz -cd {} "
            )
    return( execute )
}

getFastqFilterBadReadsCommand <- function () {
    execute <- paste0( " grep -A 3 '^@.* [^:]*:N:[^:]*:' | grep -v -- '^--$' ", " | ", getRenameReadsCommand() )
    return( execute )
}

getRenameReadsCommand <- function () {
        execute <- " sed 's/ [0-9]:N:[0-9]*:[A-Z]*$//g' "
        return( execute )
}

getFastqTrimmingCommand <- function() {
    execute <- paste0(
        getHTSFlowPath("fastqQualityTrimmer")
        ," -Q33 -t 20 -l 20 -v "
        )
    return( execute )
}

getFastqMaskingCommand <- function() {
    execute <- paste0(
        getHTSFlowPath("fastqMasker")
        ," -Q33 -q 20 -r N -v "
        )
	
    return( execute )
}

fastqSort <- function( f1, f2 ) {
    execute <- paste(
        getHTSFlowPath("sorter")
        , f1
        , f2
        )
    return(tryOrExit( execute, 'Fastq sorter' ))
}

fastQCexec <- function( sample ) {
    loginfo('FASTQC')
    primaryId <- names(sample)
    execute <- paste0(
         getHTSFlowPath("fastQC")
        ,' -o '
        ,getHTSFlowPath("HTSFLOW_QC")
        ,' -c '
        ,getHTSFlowPath("HTSFLOW_CONTAMINANTS")
        ,' '
        ,getHTSFlowPath("HTSFLOW_ALN")
		,'/'
        ,primaryId
        ,'.bam'
    )
    return(tryOrExit(execute, 'FASTQC'))
}

countReadsNum <- function( sample, flags ){
    loginfo ("Count aligned reads")
    primaryId <- names(sample)
    bamFileName <- paste( getHTSFlowPath("HTSFLOW_ALN"), "/", primaryId, ".bam", sep="" )
    cmd <- paste(  getHTSFlowPath("samtools"), " view -c  ", bamFileName, sep="" )
    nReads <- tryInternOrExit( cmd, "count number of reads")
    loginfo ( paste ( bamFileName, ": ", nReads, sep="" ) )
    # update the DB with the number of reads aligned.
    SQL <- paste("UPDATE primary_analysis SET reads_num=", nReads ," WHERE id=", primaryId ,sep="")
    res <- updateInfoOnDB( SQL )
    #########################################
}

doCountFastqReads <- function( fastqFileName ) {
    loginfo ("Count raw reads number")
    nReads <- as.numeric( unlist( strsplit( tryInternOrExit( paste0('wc -l ', fastqFileName), 'count fastq reads'), split="[ ]" )[[1]][1] ) )/4
    loginfo ( nReads )
    return( nReads )
}

doFeatureCounts <- function( sample, RefGenomes, flags ) {
    primaryId <- names(sample)
    loginfo ("Count reads per gene with featuresCounts")
    BAMdir <- getHTSFlowPath("HTSFLOW_ALN")
    COUNTdir <- getHTSFlowPath("HTSFLOW_COUNT")
    baseName <- paste( names(sample), sep="" )
    bamfile <- paste( BAMdir, baseName, ".bam", sep="" )
    countfile <- paste( COUNTdir, baseName, ".count" , sep='' )
    gtf <- RefGenomes["genes",]
    if ( as.numeric( flags$paired ) ) {
        cmd <- paste(
                    getHTSFlowPath("featuresCounts")
                    ,"-T 2 -p -P -a"
                    ,gtf
                    ,"-o"
                    ,countfile
                    ,bamfile
                    ,sep=" "
                    )        
        return(tryOrExit(cmd, "feature counts"))
    } else {
         cmd <- paste(
                    getHTSFlowPath("featuresCounts")
                    ,"-T 2 -a"
                    ,gtf
                    ,"-o"
                    ,countfile
                    ,bamfile
                    ,sep=" "
                    )
        return(tryOrExit(cmd, "feature counts"))
    }
}

doUniquelyAlignedBam <- function( sample ) {
    ###########################
    #### create file names ####
    ###########################
    primaryId <- names(sample)
    bamFileNameIn <- paste(
        getHTSFlowPath("HTSFLOW_ALN")
        ,"/"
        ,primaryId
        ,'.bam'
        ,sep=''
    )

    bamFileSorted <- paste(
		getPreprocessDir()
        ,primaryId
        ,'_sort.bam'
        ,sep=''
    )

    bamFileNoDup <- paste(
		getPreprocessDir()
        ,primaryId
        ,'_unique.bam'
        ,sep=''
    )
    ########################
    #### filter bam file ###
    ########################
    loginfo('Create a bam file with only uniquely mapped reads')
    execute <- paste(
        getHTSFlowPath("samtools")
        ,' sort '
        ,bamFileNameIn
        ," "
        ,getPreprocessDir()
        ,primaryId
        ,'_sort'
        ,sep = ""
    )
    result <- tryOrExit( execute, 'Create a bam file with only uniquely mapped reads' )
	if (result > 0) {
		return(result)
	}
    execute <- paste(
        getHTSFlowPath("samtools")
        ,' rmdup -sS '
        ,bamFileSorted
        ," "
        ,bamFileNoDup
        ,sep = ""
    )
    
    removed <- tryInternOrExit( execute, 'Create a bam file with only uniquely mapped reads')
    ## copy back the bam file to ALN folder
    execute <- paste(
                    "mv"
                    ,bamFileNoDup
                    ,bamFileNameIn
                    ,sep=" "
                )
	tryOrExit(execute, "move bam files")
				
	deleteFile(bamFileSorted)
	
	## we have to recreate the index file at this stage for the new bam file
    execute <- paste0(
                    getHTSFlowPath("samtools")
                    ," index "
                    ,bamFileNameIn
                )
    result <- tryOrExit( execute, "index bam" )
	if (result > 0) {
		return(result)
	}
    return (removed)
}

doTophatAlignment <- function( sample , outFolder, RefGenomes, reference, flags ) {
    primaryId <- names(sample)
    # update the DB with the aligning status
    SQL <- paste("UPDATE primary_analysis SET status='tophat aligning..' WHERE id=", primaryId ,sep="")
    res <- updateInfoOnDB( SQL )

    fastqFileNameR1 <- paste(
			getPreprocessDir()
        , primaryId
        , '_R1.fq'
        , sep=''
        )
    if( as.numeric( flags$paired ) ) {
        fastqFileNameR2 <- paste(
              getPreprocessDir()
            , primaryId
            , '_R2.fq'
            , sep=''
            )
    } else {
		fastqFileNameR2 <- ''
	}
		
	if( reference == 'genome' ) {
        indexFile <- RefGenomes[ "genome", ]
        gtfSets   <- paste( '--transcriptome-index=' , RefGenomes[ "transcripts-index", ] , sep="" )
    }
    dirname <- paste(
         outFolder
        ,'/'
        ,'tophatOut_'
        , primaryId
        , '_'
        , reference
        , "/"
        , sep=''
        )
    loginfo('Start alignment with tophat')
    if( file.exists('tophatexecfailed') ) {
		deleteFile('tophatexecfailed')
	}
	
	
	createDir(dirname,  recursive =  TRUE)		

	sets <- flags$aln_options
    # run tophat and create a file 'tophatexecfailed' to control
	tophat_exe <- paste0(getHTSFlowPath("tophat_dir"), "/tophat")
    execute <- paste(
		tophat_exe    # path of the aligner
        ,sets              # settings
        ,gtfSets
        ,'-o' , dirname    # directory for the output
        ,indexFile         # index file for the reference genome
        ,fastqFileNameR1   # fastq file name where reads are stored (R1)
        ,fastqFileNameR2   # fastq file name where reads are stored (R2)
        )
	tryOrExit(execute, "Tophat Alignment")
	
    # SORT AND INDEXING BAM FILE
    execute <- paste0(
                    getHTSFlowPath("samtools")
                    ," sort "
                    ,dirname
                    ,"accepted_hits.bam "
                    ,dirname
                    ,"accepted_hits.sorted "
                )
	tryOrExit(execute, "Tophat Alignment")
	
    execute <- paste0(
                    getHTSFlowPath("samtools")
                    ," index "
                    ,dirname
                    ,"accepted_hits.sorted.bam "
                )
	tryOrExit(execute, "Tophat Alignment")
	
    # LET'S MOVE BAMs and BAIs IN ALN FOLDER
    execute <- paste0(
                    "mv "
                    ,dirname
                    ,"accepted_hits.sorted.bam "
                    ,getHTSFlowPath("HTSFLOW_ALN")
                    ,"/"
                    ,primaryId
                    ,".bam"
                )
	tryOrExit(execute, "Tophat Alignment")
	
    execute <- paste0(
                    "mv "
                    ,dirname
                    ,"accepted_hits.sorted.bam.bai "
                    ,getHTSFlowPath("HTSFLOW_ALN")
                    ,"/"
                    ,primaryId
                    ,".bam.bai"
                )
	tryOrExit(execute, "Tophat Alignment")
	
	result <- deleteFile(dirname, recursive=TRUE)
	
    loginfo('Alignment completed')
	return(result)
}

doBismarkAlignment <- function( sample , outFolder, RefGenomes, reference, flags ) {
    primaryId <- names(sample)
    # update the DB with the aligning status
    SQL <- paste("UPDATE primary_analysis SET status='bismark aligning..' WHERE id=", primaryId ,sep="")
    res <- updateInfoOnDB( SQL )


    indexFile <- RefGenomes[ "genome_bs", ]

    fastqFileNameR1 <- paste(
          getPreprocessDir()
        , primaryId
        , '_R1.fq'
        , sep=''
        )
    if( as.numeric( flags$paired ) ) {
        fastqFileNameR2 <- paste(
              getPreprocessDir()
            , primaryId
            , '_R2.fq'
            , sep=''
            )
    } else {
		fastqFileNameR2 <- ''
	}
	
    dirname <- paste(
         outFolder
        ,'/'
        ,'bismarkOut_'
        , primaryId
        , '_'
        , reference
        , "/"
        , sep=''
        )

    loginfo('Start alignment with bismark ..')
    if( file.exists('bismarkexecfailed') ) {
		## system('rm bismarkexecfailed')
		deleteFile('bismarkexecfailed')
	}
	
	createDir(dirname,  recursive =  TRUE)		
	
    sets <- flags$aln_options
    # run bismark and create a file 'bismarkexecfailed' to control
    if( as.numeric( flags$paired ) ) {
        execute <- paste(
             getHTSFlowPath("bismark")   # path of the aligner
            ,sets              # settings
            ,paste0( '--path_to_bowtie ', getHTSFlowPath("bowtie_dir") )
            ,indexFile         # index file for the reference genome
            ,'-1 '
            ,fastqFileNameR1   # fastq file name where reads are stored (R1)
            ,'-2 '
            ,fastqFileNameR2   # fastq file name where reads are stored (R2)
            ,'-o' , dirname    # directory for the output
        )
    } else {
        execute <- paste(
             getHTSFlowPath("bismark")   # path of the aligner
            ,sets              # settings
            ,paste0( '--path_to_bowtie ', getHTSFlowPath("bowtie_dir") )
            ,indexFile         # index file for the reference genome
            ,fastqFileNameR1   # fastq file name where reads are stored (R1)
            ,'-o' , dirname    # directory for the output
        )
    }
	tryOrExit(execute, "Bismark Alignment")

    # SORT AND INDEXING BAM FILE
    execute <- paste0( 'mv ', dirname, '*.bam ', dirname, primaryId, '.bam' )
	tryOrExit(execute, "move bam file")

	execute <- paste0(
                    getHTSFlowPath("samtools")
                    ," sort "
                    ,dirname
                    ,primaryId
                    ,".bam "
                    ,dirname
                    ,primaryId
                    ,".sorted"
                )
	tryOrExit(execute, "Bismark Alignment")

    execute <- paste0(
                    getHTSFlowPath("samtools")
                    ," index "
                    ,dirname
                    ,primaryId
                    ,".sorted.bam "
                )
	tryOrExit(execute, "Bismark Alignment")

    # LET'S MOVE BAMs and BAIs IN ALN FOLDER
    execute <- paste0(
                    "mv "
                    ,dirname
                    ,primaryId
                    ,".sorted.bam "
                    ,getHTSFlowPath("HTSFLOW_ALN")
                    ,"/"
                    ,primaryId
                    ,".bam"
                )
	tryOrExit(execute, "Bismark Alignment")

    execute <- paste0(
                    "mv "
                    ,dirname
                    ,primaryId
                    ,".sorted.bam.bai "
                    ,getHTSFlowPath("HTSFLOW_ALN")
                    ,"/"
                    ,primaryId
                    ,".bam.bai"
                )
	tryOrExit(execute, "Bismark Alignment")
	
	result <- deleteFile(dirname, recursive=TRUE)
	
	
    loginfo('Alignment completed')
	return(result)
}

doBwaAlignment <- function( sample , outFolder, RefGenomes, reference, flags ) {
    primaryId <- names(sample)
    # update the DB with the aligning status
    SQL <- paste("UPDATE primary_analysis SET status='bwa aligning..' WHERE id=", primaryId ,sep="")
    res <- updateInfoOnDB( SQL )
    #########################################
    indexFile <- RefGenomes[ "genome", ]
    fastaFile <- RefGenomes[ "fasta", ]

    fastqFileNameR1 <- paste(
          getPreprocessDir()
        , primaryId
        , '_R1.fq'
        , sep=''
        )
		
    if( as.numeric( flags$paired ) ) {
        fastqFileNameR2 <- paste(
              getPreprocessDir()
            , primaryId
            , '_R2.fq'
            , sep=''
            )
    } else {
		fastqFileNameR2 <- ''
	}
	
    loginfo('Start alignment with bwa ..')
    if( file.exists('bwaexecfailed') ) {
		deleteFile('bwaexecfailed')
	}

    sets <- flags$aln_options

    # run bwa and create a file 'bwaexecfailed' to control
    if ( as.numeric( flags$paired ) ) {

        part1File <- paste( HTSFLOW_PREPROCESS, '/' , primaryId, "_sa1.sai", sep="" )
        part2File <- paste( HTSFLOW_PREPROCESS, '/' , primaryId, "_sa2.sai", sep="" )

        execPart1 <- paste (
            getHTSFlowPath("bwa")   # path of the aligner
            ," aln "
            ,sets              # settings
            ," "
            ,fastaFile
            ," "
            ,fastqFileNameR1
            ," > "
            ,part1File
            ,sep=""
            )

        execPart2 <- paste (
            getHTSFlowPath("bwa")   # path of the aligner
            ," aln "
            ,sets              # settings
            ," "
            ,fastaFile
            ," "
            ,fastqFileNameR2
            ," > "
            ,part2File
            ,sep=""
            )

        execPart3 <- paste(
            getHTSFlowPath("bwa")
            ," sampe -n 1 "
            ,fastaFile
            ," "
            ,part1File
            ," "
            ,part2File
            ," "
            ,fastqFileNameR1
            ," "
            ,fastqFileNameR2
            ," | "
            ,getHTSFlowPath("samtools")
            ," view -q 1 -ut "
            ,fastaFile
            ," - | "
            ,getHTSFlowPath("samtools")
            ," sort - "
            ,getHTSFlowPath("HTSFLOW_ALN")
            ,"/"
            ,primaryId
            ,sep=""
            )

        tryOrExit(execPart1, "BWA Alignment")
		
        tryOrExit(execPart2, "BWA Alignment")

		tryOrExit(execPart3, "BWA Alignment")

    } else {

        execute <- paste(
             getHTSFlowPath("bwa")   # path of the aligner
            ," aln "
            ,sets              # settings
            ," "
            ,fastaFile
            ," "
            ,fastqFileNameR1
            ," | "
            ,getHTSFlowPath("bwa")
            ," samse -n 1 "
            ,fastaFile
            ," - "
            ,fastqFileNameR1
            ," | "
            ,getHTSFlowPath("samtools")
            ," view -q 1 -ut "
            ,fastaFile
            ," - | "
            ,getHTSFlowPath("samtools")
            ," sort - "
            ,getHTSFlowPath("HTSFLOW_ALN")
            ,"/"
            ,primaryId
            ,sep=""
            )
		tryOrExit(execute, "BWA Alignment")
    }

    execute <- paste0(
                    getHTSFlowPath("samtools")
                    ," index "
                    ,getHTSFlowPath("HTSFLOW_ALN")
                    ,"/"
                    , primaryId
                    , ".bam"
                )
	result <- tryOrExit(execute, "BWA Alignment")
    loginfo('Alignment completed')
	return(result)
}

doBW <- function( sample, RefGenomes ){
    loginfo ( "Generating BW file")
    chromSize <- RefGenomes[ "chromSize", ]
    primaryId <- names(sample)
    # this is a patch for having all the bw in the genome browser
    BWOUTFOLDER <-  getHTSFlowPath("HTSFLOW_BW")

	if (! file.exists(BWOUTFOLDER)){
		loginfo(paste("create directory: ", BWOUTFOLDER))
		createDir(BWOUTFOLDER,  recursive =  TRUE)		
	}
	
    bamFile <- paste0 (
                getHTSFlowPath("HTSFLOW_ALN")
                ,"/"
                ,primaryId
                ,".bam"
        )
    loginfo(bamFile)
    SQL <- paste0("SELECT reads_num FROM primary_analysis WHERE id=",primaryId,";")
    readsAln <- extractInfoFromDB( SQL )

    bedgraphF <- paste0(
                getPreprocessDir()
                ,primaryId
                ,".bedgraph"
        )

    bedgraphTmp <- paste0(
				getPreprocessDir()
                ,primaryId
                ,".tmp"
        )

    bwF <- paste0(
				getPreprocessDir()
                ,primaryId
                ,".bw"
        )

    execute <- paste0(
            getHTSFlowPath("genomeCoverageBed")
            ," -split -ibam "
            ,bamFile
            ," -g "
            ,chromSize
            ," -bg > "
            ,bedgraphF
        )

	tryOrExit(execute, "BW")
	
    execute <- paste0(
            "awk '{print $1,$2,$3,int($4*1e7/"
            ,readsAln
            ,")+1}' "
            ,bedgraphF
            ," >  "
            ,bedgraphTmp
        )
	
	tryOrExit(execute, "BW")
	
    execute <- paste0(
            getHTSFlowPath("bedGraphToBigWig")
            ," "
            ,bedgraphTmp
            ," "
            ,chromSize
            ," "
            ,bwF
        )

	tryOrExit(execute, "BW")
	
	deleteFile(bedgraphF, recursive=TRUE)
	deleteFile(bedgraphTmp, recursive=TRUE)
		
	loginfo ('Copying BW file to Genome Browser folder')
    execute <- paste0( 'mv ',bwF, ' ',BWOUTFOLDER )
	result <- tryOrExit(execute, "BW")
	
    loginfo ( "BW done.")
	return (result)
}

doBW_bs <- function( sample, RefGenomes ){
    loginfo ( "Generating BW file")
    chromSize <- RefGenomes[ "chromSize_bs", ]
    primaryId <- names(sample)
    # this is a patch for having all the bw in the genome browser
    BWOUTFOLDER <-  paste0 ( getHTSFlowPath("HTSFLOW_BW"))
	
	if (! file.exists(BWOUTFOLDER)){
		loginfo(paste("create directory: ", BWOUTFOLDER))
		createDir(BWOUTFOLDER,  recursive =  TRUE)		
	}
	
    bamFile <- paste0 (
                getHTSFlowPath("HTSFLOW_ALN")
                ,"/"
                ,primaryId
                ,".bam"
        )
    SQL <- paste0("SELECT reads_num FROM primary_analysis WHERE id=",primaryId,";")
    readsAln <- extractInfoFromDB( SQL )

    bedgraphF <- paste0(
				getPreprocessDir()
                ,primaryId
                ,".bedgraph"
        )

    bedgraphTmp <- paste0(
				getPreprocessDir()
                ,primaryId
                ,".tmp"
        )

    bwF <- paste0(
				getPreprocessDir()
                ,primaryId
                ,".bw"
        )

    execute <- paste0(
            getHTSFlowPath("genomeCoverageBed")
            ," -split -ibam "
            ,bamFile
            ," -g "
            ,chromSize
            ," -bg > "
            ,bedgraphF
        )
	tryOrExit(execute, "BW_bs")
	
    execute <- paste0(
            "awk '{print $1,$2,$3,int($4*1e7/"
            ,readsAln
            ,")+1}' "
            ,bedgraphF
            ," >  "
            ,bedgraphTmp
        )
	tryOrExit(execute, "BW_bs")
	
    execute <- paste0(
            getHTSFlowPath("bedGraphToBigWig")
            ," "
            ,bedgraphTmp
            ," "
            ,chromSize
            ," "
            ,bwF
        )
	tryOrExit(execute, "BW_bs")
	
	deleteFile(bedgraphF, recursive=TRUE)
	deleteFile(bedgraphTmp, recursive=TRUE)
	
	
    loginfo ('Copying BW file to Genome Browser folder')
    execute <- paste0( 'mv ',bwF, ' ',BWOUTFOLDER )
	result <- tryOrExit(execute, "BW_bs")
    loginfo ( "BW done.")
	
	return(result)
}
