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

function DNDdownsampling() {
	var sample_name = document.getElementById('downsample_name').value;
	var INFO = '';
	var OK = 1; // this control the input of the data
	var primaryId = document.getElementById('downsample_primaryId').value;	
	var description = document.getElementById('downsample_description').value;
	var target_num_reads = document.getElementById('downsample_target_num_reads').value;
	var original_num_reads = document.getElementById('downsample_original_num_reads').value;

	INFO = INFO
			+ "<form id=\"downsampleRUN\" action=\"pages/downsample/submit.php\" method=\"POST\">";
	INFO = INFO + "<input type='hidden' name='analysis' value='downsample' />";

	var methods = new Array();

	INFO = INFO
			+ "<input type='hidden' name='downsample_primaryId' id='downsample_primaryId' value='"
			+ primaryId + "' />";

	if (sample_name.replace(/^\s+|\s+$/g, '') == '') {
		alert("You must provide a sample name.");
		OK = 0;
	} else {
		INFO = INFO + "<input type='hidden' name='downsample_name' value='"
				+ sample_name + "' />";		
	}

	INFO = INFO + "<input type='hidden' name='downsample_description' value='"
			+ description + "' >";
	INFO = INFO + "<input type='hidden' name='downsample_target_num_reads' value='"
			+ target_num_reads + "' >";
	INFO = INFO + "</form>";
	INFO = INFO + "<script>$('#downsampleRUN').on('submit', function(){ \
    var value = $('#downsample_target_num_reads').val(); \
    if ($.isNumeric(value) && value < " + original_num_reads + ") {\
    		return true; \
    	} else { \
    	alert(\"target number of reads should be a number < "+ original_num_reads + "\");		\
    	return false; \
    		}		\
	});</script>"
	
	if (OK) {
		$("#INFO").append(INFO);
		// and then we auto-submit the form that load the data inside the DB.
		// everything is controlled by
		$("#downsampleRUN").submit();
	}
}
