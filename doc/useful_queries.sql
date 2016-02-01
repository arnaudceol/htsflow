-- Get all secondary ids by type 
SELECT DISTINCT id_sec, user_name, dateEnd, method FROM secondary, users WHERE  secondary.user_id = users.user_id AND status = 'completed' AND method = 'diff_gene_expression' ORDER BY dateEnd;

-- Get paths
SELECT * FROM paths;

