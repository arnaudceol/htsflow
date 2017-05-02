

-- CREATE OPTION FOR EACH GENOME
CREATE TEMP TABLE pa_option_wt_genome AS 
SELECT DISTINCT pa_option.*, sample.ref_genome FROM primary_analysis, pa_option, sample
WHERE sample_id = sample.id AND primary_id = primary_analyses.id;


-- Add genome to the option of tpromay analysis to allow aligning to a different genome.
ALTER TABLE pa_option ADD COLUMN `genome` varchar(20) DEFAULT NULL,

INSERT INTO pa_options(rm_bad_reads, trimming, masking, alignment, paired, rm_tmp_files,  rm_duplicates, stranded, genome)
SELECT DISTINCT rm_bad_reads, trimming, masking, alignment, paired, rm_tmp_files,  rm_duplicates, stranded, ref_genome FROM pa_option_wt_genome;

UPDATE primary_analysis SET options_id = new_options.id FROM pa_options new_options, pa_options old_options, samples 
WHERE primary_analysis.options_id = old_options.options_id 
AND primary_id.sample_id = sample.id
AND old_options.rm_bad_reads = new_options.rm_bad_reads
AND old_options.trimming = new_options.trimming
AND old_options.masking = new_options.masking clustered
AND old_options.alignment = new_options.alignment
AND old_options.paired = new_option.paired
AND old_options.rm_tmp_files = new_options.rm_tmp_files
AND old_options.rm_duplicates = new_options.rm_duplicates
AND old_options.stranded = new_options.stranded
AND sample.ref_genome = new_options.genome;

