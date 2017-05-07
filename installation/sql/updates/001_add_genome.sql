

-- CREATE OPTION FOR EACH GENOME
DROP TABLE pa_option_wt_genome;

CREATE TABLE pa_option_wt_genome AS 
SELECT DISTINCT pa_options.*, sample.ref_genome FROM primary_analysis, pa_options, sample
WHERE sample_id = sample.id AND options_id = pa_options.id;

-- Add genome to the option of tpromay analysis to allow aligning to a different genome.
ALTER TABLE pa_options ADD COLUMN `genome` varchar(20) DEFAULT NULL;

INSERT INTO pa_options(aln_prog, aln_options,
rm_bad_reads, trimming, masking, alignment, paired,
rm_tmp_files,  rm_duplicates, stranded, genome)
SELECT DISTINCT aln_prog, aln_options, rm_bad_reads, trimming, masking, alignment, paired, rm_tmp_files,  rm_duplicates, stranded, ref_genome FROM pa_option_wt_genome;

UPDATE primary_analysis, pa_options new_options, pa_options old_options, sample
SET primary_analysis.options_id = new_options.id  
WHERE primary_analysis.options_id = old_options.id 
AND primary_analysis.sample_id = sample.id
AND old_options.aln_options = new_options.aln_options
AND old_options.aln_prog = new_options.aln_prog
AND old_options.alignment = new_options.alignment
AND old_options.rm_bad_reads = new_options.rm_bad_reads
AND old_options.trimming = new_options.trimming
AND old_options.masking = new_options.masking
AND old_options.paired = new_options.paired
AND old_options.rm_tmp_files = new_options.rm_tmp_files
AND old_options.rm_duplicates = new_options.rm_duplicates
AND old_options.stranded = new_options.stranded
AND sample.ref_genome = new_options.genome;



