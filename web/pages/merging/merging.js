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

function DNDmerging(){
    var sample_name = document.getElementById('merge_name').value;
    var INFO = '';
    var OK = 1; // this control the input of the data
    var selectedSamples = $('#selectedIds').val().replace(/\'/g, "").replace(/\ +/g, " ").trim().split(" ");
    var rm_dup = $('#rm_duplicates').val();
    INFO = INFO + "<form id=\"mergeRUN\" action=\"pages/merging/submit.php\" method=\"POST\">";
    INFO = INFO + "<input type='hidden' name='analysis' value='merging' />";

    var methods = new Array();
    
    $('#tableMerge .method').each(function() {
        var method = $(this).html();
        if (false == contains(methods, method)) {
      		  methods.push(method);
        }
     });
    
    var readModes = new Array();
    
    $('#tableMerge .readMode').each(function() {
        var readMode = $(this).html();
        if (false == contains(readModes, readMode)) {
        	readModes.push(readMode);
        }
     });
    
    
    for (i = 0; i < selectedSamples.length; i++) {
        INFO = INFO + "<input type='hidden' name='sample"+i+"' value='"+selectedSamples[i]+"'' />";
    }
    if ( methods.length > 1 ) {
        alert("You have selected samples with different sequencing methods, please go back and select samples with the same sequencing method.");
        OK = 0;
    } else {
        if( sample_name.replace(/^\s+|\s+$/g, '') == '' ) {
            alert("You must provide a sample name.");
            OK = 0;
        } else {
            INFO = INFO + "<input type='hidden' name='merge_name' value='"+sample_name+"' />";
            INFO = INFO + "<input type='hidden' name='rm_duplicates' value='"+rm_dup+"' />";
            INFO = INFO + "<input type='hidden' name='seq_method' value='"+methods[0]+"' />";
        }
    }
    INFO = INFO + "<input type='hidden' name='description' value='"+$('#description').val()+"' >";
    INFO = INFO + "</form>";
    if (OK){
        $("#INFO").append(INFO);
        // and then we auto-submit the form that load the data inside the DB.
        // everything is controlled by
        $("#mergeRUN").submit();
    }
}
