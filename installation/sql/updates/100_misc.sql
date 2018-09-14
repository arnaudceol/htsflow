

-- Fix options for merged data: put the one of the first merged entry
UPDATE primary_analysis merged, merged_primary, primary_analysis sourcep 
SET merged.options_id = sourcep.options_id 
WHERE merged.options_id = 211 AND merged.origin = 1 AND source_primary_id = sourcep.id AND result_primary_id = merged.id;