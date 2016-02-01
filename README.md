# htsflow
HTS-flow (High-Throughput Sequencing flow) provides a framework for the management and analysis of NGS data.

HTS-flow is based on a combination of a MySQL database, a PHP web interface and several NGS analysis modules. It allows labs generating NGS samples to analyze sequencing data and to manage the increasing size of their data repository. HTS-flow facilitates the reproducibility and traceability of the analyses by avoiding manual, error-prone execution of a set of standard NGS tools.


The core of HTS-flow is a MySQL database with three main “entities”: sample description, primary and secondary analyses..

* A primary analysis is performed on each type of raw data: quality controls, filtering, and alignment.
* Higher-level (secondary) analysis can be performed on a group of samples according to the data type and user needs: peak calling, differential peak calling and saturation analysis for ChIP-seq; absolute and differential expression quantification for RNA-seq; determination of absolute and relative methylation levels, and identification of differentially methylated regions for high-throughput DNA methylation data.

The analyses rely on predefined, easily customizable modular scripts to invoke the most common analysis steps:

* FastX-toolkits and FastQC tools are used to filter reads and control the quality of all raw data;
* BWA, TopHat and Bismark are the options for the reads alignment of ChIP-seq, RNA-seq and DNA-methylation reads, respectively;
* MACS is used for peak calling and saturation analysis,
* The Bioconductor diffBind is used for differential peak calling of ChIP-seq data;
* Cufflinks and Cuffdiff, or alternatively Bioconductor tools including edgeR and DESeq are available for the absolute and differential quantification of expression;
* The methylPipe R library is used for the analysis of DNA methylation data.

A PHP-based web interface allows the users to run and follow the progression of the primary and secondary analysis. The user can decide which steps of the analysis have to be performed, and directly modify their specific settings, e.g. the maximum number of mismatches allowed in the alignment process.


Information on how to install HTS-flow is available from the wiki at: https://github.com/arnaudceol/htsflow/wiki

