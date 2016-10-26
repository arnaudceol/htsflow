create database htsflowdb;

USE htsflowdb;


--
-- Table structure for table `seq_samples`
--

DROP TABLE IF EXISTS `seq_samples`;
CREATE TABLE `seq_samples` (
  `n` bigint(20) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`n`)
) ENGINE=InnoDB AUTO_INCREMENT=12972 DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'auto incrementing user_id of each user, unique index',
  `user_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'user''s name, unique',
  `is_dev` tinyint(1) NOT NULL DEFAULT '0',
  `granted_browse` tinyint(1) NOT NULL DEFAULT '0',
  `granted_primary` tinyint(1) NOT NULL DEFAULT '0',
  `granted_secondary` tinyint(1) NOT NULL DEFAULT '0',
  `granted_admin` tinyint(1) NOT NULL DEFAULT '0',
  `user_group` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user data';


DROP TABLE IF EXISTS `controlled_vocabulary`;
CREATE TABLE `controlled_vocabulary` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cv_term` varchar(30) NOT NULL,
  `display_term` varchar(30) NOT NULL,
  `cv_type` varchar(30) NOT NULL,
  `available` boolean NOT NULL DEFAULT true,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1399 DEFAULT CHARSET=latin1;
INSERT INTO `controlled_vocabulary` (cv_type, display_term, cv_term, available) VALUES ('sequencing_type', 'ChIP-Seq', 'chip-seq', true);
INSERT INTO `controlled_vocabulary` (cv_type, display_term, cv_term, available) VALUES ('sequencing_type', 'RNA-Seq', 'rna-seq', true);
INSERT INTO `controlled_vocabulary` (cv_type, display_term, cv_term, available) VALUES ('sequencing_type', 'DNase-Seq', 'dnase-seq', true);
INSERT INTO `controlled_vocabulary` (cv_type, display_term, cv_term, available) VALUES ('sequencing_type', 'DNA-Seq', 'dna-seq', false);
INSERT INTO `controlled_vocabulary` (cv_type, display_term, cv_term, available) VALUES ('sequencing_type', 'BS-Seq', 'bs-seq', true);



--
-- Table structure for table `pa_options`
--

DROP TABLE IF EXISTS `pa_options`;
CREATE TABLE `pa_options` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `rm_bad_reads` tinyint(1) NOT NULL,
  `trimming` tinyint(1) NOT NULL,
  `masking` tinyint(1) NOT NULL,
  `alignment` tinyint(1) NOT NULL,
  `aln_prog` varchar(20) DEFAULT NULL,
  `aln_options` text,
  `paired` tinyint(1) NOT NULL,
  `rm_tmp_files` tinyint(1) NOT NULL,
  `rm_duplicates` tinyint(1) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;



--
-- Table structure for table `primary_analysis`
--

DROP TABLE IF EXISTS `primary_analysis`;
CREATE TABLE `primary_analysis` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sample_id` varchar(30) DEFAULT NULL,
  `options_id` int(10) NOT NULL,
  `reads_num` bigint(20) DEFAULT NULL,
  `raw_reads_num` bigint(20) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `user_id` int(10) NOT NULL,
  `dateStart` varchar(30) DEFAULT NULL,
  `dateEnd` varchar(30) DEFAULT NULL,  
  `description` text,
  `origin` int(5) NOT NULL DEFAULT '0',
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `options_id` (`options_id`),
  CONSTRAINT `pre_analysis_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `pre_analysis_ibfk_2` FOREIGN KEY (`options_id`) REFERENCES `pa_options` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2706 DEFAULT CHARSET=latin1;


--
-- Table structure for table `other_analysis`
--

DROP TABLE IF EXISTS `other_analysis`;
CREATE TABLE `other_analysis` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` text,
  `status` varchar(50) DEFAULT NULL,
  `user_id` int(10) NOT NULL,
  `dateStart` varchar(30) DEFAULT NULL,
  `dateEnd` varchar(30) DEFAULT NULL,  
  `description` text,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `other_analysis_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2706 DEFAULT CHARSET=latin1;



--
-- Table structure for table `sample`
--

DROP TABLE IF EXISTS `sample`;
CREATE TABLE `sample` (
  `id` varchar(30) NOT NULL DEFAULT '',
  `sample_name` text NOT NULL,
  `seq_method` varchar(20) NOT NULL,
  `reads_length` int(10) NOT NULL,
  `reads_mode` varchar(3) NOT NULL,
  `ref_genome` varchar(10) NOT NULL,
  `raw_data_path` text NOT NULL,
  `project` varchar(30) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `source` int(11) NOT NULL DEFAULT '0',
  `raw_data_path_date` timestamp NULL DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`source`),
  KEY `sample_user_fk` (`user_id`),
  CONSTRAINT `sample_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `sample_description`;
CREATE TABLE `sample_description` (
  `sample_id` varchar(30) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  PRIMARY KEY (`sample_id`),
  KEY `sample_id_fk` (`sample_id`),
  CONSTRAINT `sample_id_fk` FOREIGN KEY (`sample_id`) REFERENCES `sample` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Table structure for table `secondary_analysis`
--

DROP TABLE IF EXISTS `secondary_analysis`;
CREATE TABLE `secondary_analysis` (
  `id` int(10) NOT NULL,
  `method` varchar(50) NOT NULL,
  `user_id` int(10) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `dateStart` varchar(30) DEFAULT NULL,
  `dateEnd` varchar(30) DEFAULT NULL,
  `foldOut` text,
  `title` text,
  `description` text,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_fk` (`user_id`),
  CONSTRAINT `user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




--
-- Table structure for table `differential_gene_expression`
--

DROP TABLE IF EXISTS `differential_gene_expression`;
CREATE TABLE `differential_gene_expression` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `secondary_id` int(10) NOT NULL,
  `exp_name` text NOT NULL,
  `primary_id` int(10) NOT NULL,
  `cond` varchar(30) NOT NULL,
  `mix_spike` varchar(3) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `primary_id` (`primary_id`),
  KEY `secondary_id` (`secondary_id`),
  CONSTRAINT `diff_gene_expression_ibfk_1` FOREIGN KEY (`primary_id`) REFERENCES `primary_analysis` (`id`),
  CONSTRAINT `diff_gene_expression_ibfk_2` FOREIGN KEY (`secondary_id`) REFERENCES `secondary_analysis` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1997 DEFAULT CHARSET=latin1;

--
-- Table structure for table `expression_quantification`
--

DROP TABLE IF EXISTS `expression_quantification`;
CREATE TABLE `expression_quantification` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `secondary_id` int(10) NOT NULL,
  `primary_id` int(10) NOT NULL,
  `mix_spike` varchar(3) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `primary_id` (`primary_id`),
  KEY `secondary_id` (`secondary_id`),
  CONSTRAINT `expression_quantification_ibfk_1` FOREIGN KEY (`primary_id`) REFERENCES `primary_analysis` (`id`),
  CONSTRAINT `expression_quantification_ibfk_2` FOREIGN KEY (`secondary_id`) REFERENCES `secondary_analysis` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1399 DEFAULT CHARSET=latin1;


--
-- Table structure for table `merged_primary`
--

DROP TABLE IF EXISTS `merged_primary`;
CREATE TABLE `merged_primary` (
  `result_primary_id` int(10) NOT NULL DEFAULT '0',
  `source_primary_id` int(10) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`result_primary_id`,`source_primary_id`),
  KEY `source_fk` (`source_primary_id`),
  CONSTRAINT `merged_fk` FOREIGN KEY (`result_primary_id`) REFERENCES `primary_analysis` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `source_fk` FOREIGN KEY (`source_primary_id`) REFERENCES `primary_analysis` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Table structure for table `downsample_primary`
--

DROP TABLE IF EXISTS `downsample_primary`;
CREATE TABLE `downsample_primary` (
  `result_primary_id` int(10) NOT NULL DEFAULT '0',
  `source_primary_id` int(10) NOT NULL,
  `target_reads_number` int(10) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`result_primary_id`,`source_primary_id`),
  KEY `source_fk` (`source_primary_id`),
  CONSTRAINT `downsample_fk` FOREIGN KEY (`result_primary_id`) REFERENCES `primary_analysis` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `downsample_source_fk` FOREIGN KEY (`source_primary_id`) REFERENCES `primary_analysis` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



--
-- Table structure for table `methylation_calling`
--

DROP TABLE IF EXISTS `methylation_calling`;
CREATE TABLE `methylation_calling` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `secondary_id` int(10) NOT NULL,
  `exp_name` text NOT NULL,
  `primary_id` int(10) NOT NULL,
  `no_overlap` tinyint(1) NOT NULL,
  `read_context` varchar(10) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `secondary_id` (`secondary_id`),
  KEY `primary_id` (`primary_id`),
  CONSTRAINT `methylation_ibfk_1` FOREIGN KEY (`secondary_id`) REFERENCES `secondary_analysis` (`id`),
  CONSTRAINT `methylation_ibfk_2` FOREIGN KEY (`primary_id`) REFERENCES `primary_analysis` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `peak_calling`
--

DROP TABLE IF EXISTS `peak_calling`;
CREATE TABLE `peak_calling` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `secondary_id` int(10) NOT NULL,
  `exp_name` text NOT NULL,
  `program` varchar(50) NOT NULL,
  `primary_id` int(10) NOT NULL,
  `input_id` int(10) NOT NULL,
  `label` varchar(30) NOT NULL,
  `pvalue` text,
  `stats` text,
  `saturation` tinyint(1) DEFAULT '0',
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `primary_id` (`primary_id`),
  KEY `input_id` (`input_id`),
  KEY `secondary_id` (`secondary_id`),
  CONSTRAINT `peak_calling_ibfk_1` FOREIGN KEY (`primary_id`) REFERENCES `primary_analysis` (`id`),
  CONSTRAINT `peak_calling_ibfk_2` FOREIGN KEY (`input_id`) REFERENCES `primary_analysis` (`id`),
  CONSTRAINT `peak_calling_ibfk_3` FOREIGN KEY (`secondary_id`) REFERENCES `secondary_analysis` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1626 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `inspect`;
CREATE TABLE `inspect` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `secondary_id` int(10) NOT NULL,
 `type` varchar(30) NOT NULL,
  `primary_id` int(10) NOT NULL,
  `rnatotal_id` int(10) NOT NULL,
  `cond` varchar(3) DEFAULT NULL,
  `timepoint` double DEFAULT NULL,
  labeling_time int,
 deg_during_pulse  tinyint(1) NOT NULL DEFAULT '0',
  modeling_rates  tinyint(1) NOT NULL DEFAULT '0',
 counts_filtering int(10) NOT NULL DEFAULT 5,
  `created` timestamp NULL DEFAULT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `foursu_primary_id` (`primary_id`),
  KEY `rnatotal_primary_id` (`rnatotal_id`),
  KEY `secondary_id` (`secondary_id`),
  CONSTRAINT `inspect_ibfk_1` FOREIGN KEY (`primary_id`) REFERENCES `primary_analysis` (`id`),
  CONSTRAINT `inspect_ibfk_2` FOREIGN KEY (`rnatotal_id`) REFERENCES `primary_analysis` (`id`),
  CONSTRAINT `inspect_ibfk_3` FOREIGN KEY (`secondary_id`) REFERENCES `secondary_analysis` (`id`)
);


--
-- Table structure for table `footprint_analysis`
--

DROP TABLE IF EXISTS `footprint_analysis`;
CREATE TABLE `footprint_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `htsflow1_id` int(11) DEFAULT NULL,
  `secondary_id` int(11) NOT NULL,
  `exp_name` text NOT NULL,
  `peak_id` int(11) NOT NULL,
  `caller` text NOT NULL,
  `pvalue` float NOT NULL,
  `options` text,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `footprint2_ibfk_2` (`peak_id`),
  KEY `footprint2_ibfk_1` (`secondary_id`),
  CONSTRAINT `footprint2_ibfk_1` FOREIGN KEY (`secondary_id`) REFERENCES `secondary_analysis` (`id`),
  CONSTRAINT `footprint2_ibfk_2` FOREIGN KEY (`peak_id`) REFERENCES `peak_calling` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=230 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `job_list`;
CREATE TABLE `job_list` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `analyses_type` varchar(30) DEFAULT NULL,
  `analyses_id` varchar(30) DEFAULT NULL,
  `action` varchar(30) DEFAULT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `queued` timestamp  NULL,
  `started` timestamp  NULL,
  `user_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `job_list_user_fk` (`user_id`),
  CONSTRAINT `job_list_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1399 DEFAULT CHARSET=latin1;


DELIMITER $$
CREATE FUNCTION nextSampleId()
RETURNS BIGINT
BEGIN
 DECLARE r BIGINT;
 INSERT INTO `seq_samples` (`n`) VALUES (NULL);
 SELECT MAX(`n`) INTO r FROM `seq_samples`;
 
 RETURN r;
END $$
DELIMITER ;



-- Default options  (for merging)
INSERT INTO pa_options (rm_bad_reads, trimming, masking, alignment, paired, rm_tmp_files,  rm_duplicates )
VALUES (0, 0, 0, 0, 0, 0, 0);

INSERT INTO pa_options (rm_bad_reads, trimming, masking, alignment, paired, rm_tmp_files,  rm_duplicates )
VALUES (0, 0, 0, 0, 0, 0, 1);


INSERT INTO seq_samples VALUES(0);


grant all on `htsflowdb`.* to 'htsflow'@'%' identified by 'hts';
grant select on `htsflowdb`.* to 'htsflow_view'@'%' identified by 'hts';


