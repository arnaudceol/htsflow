<?php
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
session_start();

require_once ("config.php");

header('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <?php include ("pages/header.php"); //header of the page ?>
    
    <style>
#toc a {
	display: block;
}

.table-of-contents {
	-moz-border-radius: 10px;
	border-radius: 10px;
	float: right;
	overflow: hidden;
	margin-top: 0px;
	margin-bottom: 0px;
	color: #fff;
	background-color: #0288AD; /* 0288AD d02552  F6F3EC*/
	border-bottom: 5px solid #0288AD;
	padding: 10px;
}

.table-of-contents ul {
	line-height: normal;
	text-align: center;
	overflow: hidden;
}

.table-of-contents li {
	display: inline;
	padding: 10px;
}

.table-of-contents a {
	letter-spacing: 1px;
	text-decoration: none;
	/* text-transform: uppercase; */
	color: #fff;
	margin-left: 5px;
}

.table-of-contents a[title=H1] {
	font-size: 100%;
}

.table-of-contents a[title=H2] {
	font-size: 100%;
}

.table-of-contents a[title=H3] {
	font-size: 80%;;
	padding-left: 10px
}

#article h2 {
	margin: 0px;
	padding-top: 30px;
	margin-bottom: 10px;
	border-bottom-style: solid;
	width: 100%;
}

#article h3 {
	margin: 0px;
	padding-top: 10px;
	margin-bottom: 1px;
	border-bottom-width: thin;
	border-bottom-style: solid;
	border-bottom-color: #CCC;
	width: 100%;
}
</style>
<body>
	<div id="wrapper">
        <?php
        include ("pages/menu.php"); // import of menu
        ?><div id="content">



			<div>
				<b>HTS-flow</b> provides a framework for the management of
				NGS-samples, data retrieval, primary and secondary analyses.
			</div>
			<!-- <div>
				<h2>Enable HTS flow jobs</h2>
				<p>
					Add the folowing line to cron: <br /> <i>* * * * * python
						/data/BA/htsflow2/scripts/HTSflowSubmitter2.py
						/data/BA/htsflow2/conf/htsflow.conf</i><br /> Note: this will be
					only necessary during the testing period. Once in production, I'll
					replace the current script to make the transition as smooth as
					possible.
				</p>
			</div> -->

			<div id="toc" class="title menu table-of-contents"></div>

			<div id="article">
				<!-- 		<div>
					<h2>Data paths</h2>
					<ul>
						<li><b>Jobs scripts and output: </b> <?php echo $HTSFLOW_PATHS['HTSFLOW_OUTPUT'] . "/users/" . $_SESSION["hf_user_name"]  . "/" ; ?>. 
			For each job, a directory is created. The name is composed of a single letter (<b>P</b>
							for Primary analysis, <b>S</b> for secondary and <b>M</b> for
							merging) and the analysis ID. The directory contains the script
							itself, the script output and the job output (file .oXXXX).</li>
						<li><b>Output files:</b> all output files are available in <?php echo $HTSFLOW_PATHS['HTSFLOW_OUTPUT']; ?>.</li>
					</ul>
				</div>
				<div></div>
 -->


				<h2>Overview</h2>
				<div>

					<p>HTS-flow (High-Throughput Sequencing flow) provides a framework
						for the management and analysis of NGS data.</p>

					<p>HTS-flow is based on a combination of a MySQL database, a PHP
						web interface and several NGS analysis modules. It allows labs
						generating NGS samples to analyze sequencing data and to manage
						the increasing size of their data repository. HTS-flow facilitates
						the reproducibility and traceability of the analyses by avoiding
						manual, error-prone execution of a set of standard NGS tools.</p>

					<p>The core of HTS-flow is a MySQL database with three main
						“entities”: sample description, primary and secondary analyses..</p>

					<ul>
						<li>A primary analysis is performed on each type of raw data:
							quality controls, filtering, and alignment.
						</li>
						<li>Higher-level (secondary) analysis can be performed on a group
							of samples according to the data type and user needs: peak
							calling, differential peak calling and saturation analysis for
							ChIP-seq; absolute and differential expression quantification for
							RNA-seq; determination of absolute and relative methylation
							levels, and identification of differentially methylated regions
							for high-throughput DNA methylation data.</li>
					</ul>

					<p>The analyses rely on predefined, easily customizable modular
						scripts to invoke the most common analysis steps:</p>

					<ul>
						<li>FastX-toolkits and FastQC tools are used to filter reads and
							control the quality of all raw data;
						</li>
						<li>BWA, TopHat and Bismark are the options for the reads
							alignment of ChIP-seq, RNA-seq and DNA-methylation reads,
							respectively;</li>
						<li>MACS is used for peak calling and saturation analysis,</li>
						<li>The Bioconductor diffBind is used for differential peak
							calling of ChIP-seq data;</li>
						<li>Cufflinks and Cuffdiff, or alternatively Bioconductor tools
							including edgeR and DESeq are available for the absolute and
							differential quantification of expression;</li>
						<li>The methylPipe R library is used for the analysis of DNA
							methylation data.</li>
					</ul>

					<p>A PHP-based web interface allows the users to run and follow the
						progression of the primary and secondary analysis. The user can
						decide which steps of the analysis have to be performed, and
						directly modify their specific settings, e.g. the maximum number
						of mismatches allowed in the alignment process.</p>

				</div>


				<h2>Availability</h2>
				<div>
					HTS flow is freely available to all users, academic or commercial,
					under the terms of the <a
						href="http://www.apache.org/licenses/LICENSE-2.0.html">Apache
						License, Version 2.0</a>. The <a
						href="https://github.com/arnaudceol/htsflow/">source code</a> and
					<a href="https://github.com/arnaudceol/htsflow/wiki">instruction
						for the installation</a> are available online.
				</div>

				<h2>Legends</h2>
				<div id="legend">				
					<?php include 'pages/legendsList.php'?>				
				</div>

				<h2>Samples</h2>

				<img style="display: block; margin: 0 auto;"  src="images/samples-legends.png"/>
				<div id="samples">
					<p>The sample page provides information about all the samples
						imported from the LIMS (if configured) or imported as external
						data.</p>
						
					<div>A description is associated to each sample, and can be edited by clicking 
					on the <i class="fa fa-file-text-o" style="color: green"></i> or 
					<i class="fa fa-file-o" style="color: red"></i> icons. It is possible to load a 
					template for the sample description by clicking the corresponding link.
					It is also possible to edit <i class="fa fa-pencil"></i> the genome name and the sequencing method. 
					</div>
					<div>The <i class="fa fa-share"></i> icon is used to see the primary analysis 
					run on a sample.</div>

					<h3>Preparing the FASTQ file(s)</h3>
					<p>External data (FASTQ) should be stored in a location accessible
						to the web server. HTS-flow does not accept BAM files as external
						data. If the starting file is in BAM format, it must first be
						converted in FASTQ format (for example, using bamtools available
						here https://github.com/pezmaster31/bamtools).</p>
					<div>Each sample must be stored in HTS-flow’s external_data folder
						(defined in the configuration file), or a folder accessible from
						the web server.</div>
					<div><ul><li>You can select either files or directories. If a directory is selected, all the files it contains will be merged as 
						a single sample.</li>
						<li>The	files should be in FASTQ format and gzipped. They should have either the suffix .fastq.gz or fq.gz (either in upper case or lower case).</li>
						<li>In case of paired-end samples, the files should contain the R1 and R2 suffix (e.g. sample_R1.fastq.gz and sample_R2.fastq.gz).</li>
						<li>Each file or directory selected will be added as a different sample (the name of the sample will be the nae of the containing directory by 
						default, it can be edited later).</li></ul>								
					</div>
					<div>
						To add a new sample, got to the sample page and press the “add
						external data”:
						<ul>
							<li><b>Sequencing Method:</b> mandatory: one among RNA-Seq,
								ChIP-Seq, DNaseI-Seq, BS-Seq</li>
							<li><b>Read Length: optional:</b> length of the reads</li>
							<li><b>Read Type: mandatory:</b> either Single End or Pair End</li>
							<li><b>Reference Genome:</b> mandatory, one among mm9, mm10,
								hg18, hg19, rn5</li>
							<li><b>Path(s):</b> mandatory, path for the folder(s)
								corresponding to each sample, one per line, matching the order
								used in Sample(s) field. If the samples are stored in the
								standard HTS-flow folder for external data, it is also possible
								to add it by browsing this directory.</li>
						</ul>
					</div>
				</div>

				<h2>Primary Analysis</h2>
				<img style="display: block; margin: 0 auto;" src="images/primaries-legends.png"/>
				<h3>New primary analysis</h3>
				<div>
					Primary analyses can be submitted on a sample or on a group of
					samples. A primary analysis consists of filtering, quality control
					and alignment to reference genome of the reads from a specific
					sample. The reference genomes present in HTS-flow are the
					following:
					<ul>
						<li>mm9 - Mus musculus,</li>
						<li>mm10 - Mus musculus,</li>
						<li>hg18 - Homo sapiens,</li>
						<li>hg19 - Homo sapiens,</li>
						<li>rn5 - Rattus norvegicus,</li>
					</ul>
					The sequencing technologies implemented in HTS-flow are
					<ul>
						<li>RNA-Seq,</li>
						<li>ChIP-Seq,</li>
						<li>DNaseI-Seq,</li>
						<li>BS-Seq.</li>
					</ul>
				</div>
				<div>If the label of the reference genome or method is not listed
					here, the analysis will not be completed and return an error. The
					user can nevertheless edit this information from the sample page on
					the web-interface.</div>
				<div>
					After the selection of sample(s) the user can proceed to select the
					set of options for the primary analysis:
					<ul>
						<li><b>Remove Bad Reads:</b> default TRUE - removal of reads that
							has been labelled bad from the sequencer (grep -A 3 '^@.*
							[^:]*:N:[^:]*:' | grep -v -- '^--$' | sed 's/
							[0-9]:N:[0-9]*:[A-Z]*$//g').</li>
						<li><b>Trimming:</b> default FALSE - if TRUE trim the reads
							starting at 5' if nucleotide quality Q is below 20. Phred quality
							scores Q are defined as a property which is logarithmically
							related to the base-calling error probabilities P. To be used if
							exists the possibility of high degradation of quality at 5' ends
							of reads.</li>
						<li><b>Masking:</b> default TRUE - if TRUE will mask nucleotides
							along the whole reads with N if their Q quality score is below
							20.</li>
						<li><b>Alignment:</b> default TRUE - if TRUE will align the reads
							to the reference genome.</li>
						<li><b>Program:</b> tophat/bwa/bismarck - tophat is used for
							aligning RNA-Seq reads, bwa for ChIP-Seq and DNaseI-Seq, bismarck
							for BS-Seq.</li>
						<li><b>Alignment </b>Options: this line is intended for changing
							the options provided to the aligners.</li>
						<li><b>Paired:</b> TRUE/FALSE - if TRUE will be used for both
							tophat and bwa the set of options that treats paired-end reads.</li>
						<li><b>Remove Temp Files:</b> default TRUE - removes the temporary
							files generated during the filtering and alignment processes.</li>
						<li><b>Remove Duplicates:</b> default TRUE - if TRUE the final
							alignment file is processed for removing PCR duplicates ( reads
							that align on the same genomic location ).</li>
					</ul>
				</div>

				<h3>Merging primary analysis</h3>
				<div>
					It is possible to select a group of aligned samples and pool their
					reads to obtain a merged alignment file. To be merged, the samples
					need to be aligned to the same reference genome. This function is
					available from the primary analysis page, with the following
					parameters:
					<ul>
						<li>SAMPLE NAME: the name for the merged sample.</li>
						<li>REMOVAL OF DUPLICATES: default TRUE: removal of duplicates in
							the merged file (suspected to be PCR duplicates). For DNaseI-Seq
							at high depth ( footprint calling ), change it to FALSE.</li>
					</ul>
				</div>




				<h2>Secondary analysis</h2>
				<img style="display: block; margin: 0 auto;"  src="images/secondaries-legends.png"/>
				<div>
					Secondary Analyses are datatype-specific. HTS-flow supports:
					<ul>
						<li>Expression Quantification</li>
						<li>Differential Genes Expression (DEG calling)</li>
						<li>Peak Calling</li>
						<li>Footprint Calling</li>
						<li>Methylation calling</li>
					</ul>

					The output of a secondary analysis is saved in <b>RDS</b> format,
					which can be loaded within R with the function readRDS:
					<code>results=readRDS(‘path_to_your_result_file’)</code>
					. Refer to
					https://stat.ethz.ch/R-manual/R-devel/library/base/html/readRDS.html
					for more information about this function.
				</div>


				<h3>Expression Quantification</h3>
				<div>
					After selecting the samples (based on the results of primary
					analysis), the user must fill a table with two fields:
					<ul>
						<li><b>SAMPLE:</b> the PRIMARY ID of the sample;</li>
						<li><b>MIX:</b> the ERCC spike-in Mix (either 1 or 2) added for
							normalization. If no mix was added, leave empty. This feature has
							not yet implemented.</li>
					</ul>
				</div>
				<div>The ADD and REMOVE buttons can be used to create/delete more
					lines in the table and quantify the expression of multiple samples
					within the same secondary analysis.</div>

				<h4>Output</h4>
				<div>The Expression Quantification analysis will create two
					different output files: RPKMS.rds and eRPKMS.rds. RPKMS.rds:
					absolute quantification of gene expression in terms of Reads per
					Kilobase per Million of mapped reads (RPKM). eRPKMS.rds: absolute
					quantification of gene expression in terms of Reads per Kilobase
					per Million of mapped exonic reads (eRPKM).</div>
				<div>Both files are R data frames where each row is a gene (row
					names are Gene Symbols) and each column is a sample, and values
					correspond to gene expressions.</div>



				<h3>Differential Gene Expression (DEG calling)</h3>
				<div>Calling Differentially Expressed Genes (DEGs) is performed with
					DESeq2. The experimental design requires two conditions, typically
					a treated set of samples and a control set of samples. The presence
					of replicates in at least one condition is essential.</div>

				<div>
					After choosing the samples, the user must fill a table with three
					fields:
					<ul>
						<li><b>SAMPLE:</b> the PRIMARY ID of the sample.</li>
						<li><b>CONDITION:</b> one of the two classes of samples (e.g.:
							treated and control) in the experimental design.</li>
						<li><b>MIX:</b> the ERCC spike-in Mix (either 1 or 2) added for
							normalization. If no mix was added, leave empty. This feature has
							not yet implemented.</li>
				
				</div>
				<div>The ADD and REMOVE buttons can be used to create/delete more
					lines in the table and add replicates to the conditions.</div>

				<h4>Output</h4>
				<div>Differential Gene Expression analysis will create a single
					output file, corresponding to the EXP NAME field, in the form ‘EXP
					NAME’.rds. This file contains an R data frame where each row is a
					gene (Gene Symbol) and columns list the DESeq2 default outputs:
					baseMean: the mean gene expression over all samples in the two
					conditions log2FoldChange: log2 Fold Change, treated vs untreated
					lfcSE: standard error, treated vs untreated stat: Wald test
					statistic pvalue: Wald test p-value padj: Benjamini-Hochberg
					adjusted p-values (False Discovery Rate)</div>

				<h3>Peak Calling</h3>
				<div>Peak Calling is performed with MACS2.</div>

				<div>
					After choosing the samples, the user must fill a table with three
					fields:
					<ul>
						<li><b>SAMPLE1:</b> the PRIMARY ID of the sample used as input in
							the peak call.</li>
						<li><b>SAMPLE2:</b> the PRIMARY ID of the sample where peaks
							should be called.</li>
					</ul>
				</div>
				<div>The ADD and REMOVE buttons can be used to create/delete more
					lines in the table and performing more peak calls within the same
					secondary job.</div>

				<div>The last part of the form allows to select the parameters for
					the peak call performed by MACS2. Peak shapes must chosen
					accordingly to the specific ChIP-seq experiment: for example,
					NARROW peaks for transcription factors, BROAD peaks for histone
					marks. With the NARROW/BROAD option both calls will be performed
					and the union of peaks from the NARROW and BROAD analysis will be
					output. This option will affect the annotation analysis performed
					by HTS-flow on the identified peaks as explained below.</div>

				<h4>Output</h4>
				<div>The output of the peak calling analysis is distributed in two
					folders: the NARROW/ or BROAD/ folders (depending on the type of
					call) contain the MACS2 output, i.e. a bed file containing the
					genomic locations for each peak identified for each sample and a
					saturation table file for each sample. Saturation reports at
					different fold-enrichments, the proportion of peaks that could
					still be detected when using 80% to 20% of the sequence reads.
					Saturation file name is in the form 'LABEL'_saturation.txt.
					Besides, in the annotation/ folder the Peak Calling analysis will
					create an output file per sample submitted, whose name will be in
					the form ‘LABEL.rds’. Each file contains a GRanges object where
					each element is a genomic interval (a peak), complemented by
					information obtained with the GRannotate and GRenrichment functions
					from compEpiTools.</div>
				<div>For each peak, the following fields are available: enrichment:
					log2(ChIP/N1-input/N2), where ChIP is the number of reads falling
					in the interval in the sample, N1 is the library size of the
					sample, input is the number of reads falling in the interval in the
					input and N2 is the library size of the input. Computed with the
					GRenrichment function from compEpiTools. summit: position of
					maximum coverage of the peak. Computed with the GRcoverageSummit
					function from compEpiTools. midpoint: the midpoint of the peak.
					Computed with the GRmidpoint function from compEpiTools.</div>
				<div>Annotation is computed in two distinct ways, depending on the
					type of peak calling requested: NARROW calls computes annotation
					from the summit of the peak BROAD calls computes annotation from
					the midpoint of the peak. NARROW/BROAD calls computes annotation
					from the midpoint of the peak.</div>

				<h3>Footprint Calling</h3>
				<div>Footprint calls are performed with Wellington.</div>
				<div>
					After choosing the samples, the user must fill a table with four
					fields:
					<ul>
						<li><b>PROGRAM:</b> The tool used to call footprint. Currently,
							only Wellington is available.</li>
						<li><b>PVALUE:</b> The p-value to use as a threshold for
							statistical significance. By default this is set to 10-30.
							OPTIONS: a set of options that can be used by the selected tool.</li>
					</ul>
				</div>

				<h4>Output</h4>
				<div>Wellington outputs a bed file containing the genomic locations
					for each footprint for each sample. These file are located in the
					output folders footprints/. The bed file contains the the genomic
					locations for each genomic footprint, followed by a score
					associated by the footprint caller assessing its statistical
					significance. Besides reporting the original wellington output ( a
					txt file ), HTS-flow converts the bed files in R GRanges objects in
					the form 'EXP_NAME'.rds in the output folder footprints/.</div>


				<h2>Third party software</h2>
				<div>
					<iframe style="width: 100%; height: 1200px"
						src="https://docs.google.com/spreadsheets/d/1UwvescfvjvwFPD28E4AJb3i48l-9mU435C0-aecU-Gk/pubhtml?gid=0&amp;single=true&amp;widget=true&amp;headers=false"></iframe>
				</div>


			</div>
		</div>


		<script>
			$("h1, h2, h3").each(function(i) {
			    var current = $(this);
			    current.attr("id", "title" + i);

				
			    
			    var pos = current.position().top / $("#content").height() * $(window).height();
			    $("#toc").append("<a id='link" + i + "' href='#title" + i +
			    "' title='" + current[0].tagName + "'>" +
			    current.html() + "</a>");
			    
			});
        </script>
				
        
     </div>
     <?php include ("pages/footer.php");    ?>
</body>
</html>