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


function peakAddLine(){
    var boxes = $('#container2');
    var index =boxes.find( "input[id^='base']" ).length +1; // (((boxes.childNodes.length)-11)/4)+2;
 
    var new_div1 = $('<div>').attr({ id: 'riquadro', style: "width: 80px; float: left"});
    boxes.append(new_div1);
    
    var base1 = $('<input>').attr({ id: "input"+String(index), style: "width: 70px"});
    new_div1.append(base1);

    var new_div2 = $('<div>').attr({ id: 'riquadro', style : "width: 80px; float: left"});
    boxes.append(new_div2);
    
    var input1 = $('<input>').attr({ id: "base"+String(index), style: "width: 70px; float: left"});
    new_div2.append(input1);

    var new_div3 = $('<div>').attr({ id: 'riquadro', style: "width = 210px; float: left"});
    boxes.append(new_div3);
    
    var label1 = $('<input>').attr({id: "label"+String(index), style: "width: 200px"});
    new_div3.append(label1);

    var new_div4 = $('<div>').attr({id: 'riquadro', style: "clear: both"});
    boxes.append(new_div4);    

    $("#remLine").removeAttr('disabled');
}


function peakRemoveLine(){
    var boxes = $('#container2');
    $("#container2  div:last").remove();
    $("#container2  div:last").remove();
    $("#container2  div:last").remove();
    $("#container2  div:last").remove();

    if (boxes.children().length == 5) {
        document.getElementById("remLine").disabled=true;
    }
}

function PeakCallerOptions() {

    var caller = $("input[name=peakcaller]:checked").val();
    
    var TEXT = '';

    TEXT = TEXT + "\t\t<form id='RUNform' action='' method=''>\n";
	TEXT = TEXT + "\t\t<div style='clear:both;'></div>\n";
	TEXT = TEXT + "\t\t<table ><tbody>\n";

	if (caller == 'MACSnarrow' || caller == '' || caller == 'MACSboth') {
		TEXT = TEXT
				+ "\t\t<tr><th>P-value</th><td><input type='text' id='narrow_pvalue' name='narrow_pvalue' value='0.00001'/>&nbsp;&nbsp;&nbsp;<span style=\"font-size: 0.8em\">e.g.: 0.00001 is 10e-5</span></td></tr>\n";
		TEXT = TEXT
				+ "\t\t<tr><th>Options</th><td><input type='text' id='narrow_stats' name='narrow_stats' value='--mfold=7,30'/></td></tr>\n";
	}
	if (caller == 'MACSbroad' || caller == 'MACSboth') {
		TEXT = TEXT
				+ "\t\t<tr><th>P-value</th><td><input type='text' id='broad_pvalue' name='broad_pvalue' value='0.00000001'/>&nbsp;&nbsp;&nbsp;<span style=\"font-size: 0.8em\">e.g.: 0.00001 is 10e-5</span></td></tr>\n";
		TEXT = TEXT
				+ "\t\t<tr><th>Options</th><td><input type='text' id='broad_stats' name='broad_stats' value='--auto-bimodal'/></td></tr>\n";
	}
	TEXT = TEXT + "\t\t</tbody></table>\n";
	TEXT = TEXT
			+ "\t\t<u>Saturation</u>: <input type=\"checkbox\" id=\"saturation\" name=\"saturation\" value=\"1\" checked />\n";
	TEXT = TEXT + "\t\t</form>\n";

    document.getElementById("OPTIONS").innerHTML=TEXT;	
}

function submitSecondary(){
    var exp_name = $('#exp_name').val();
	var x = $('#RUNform');
    var num = $('#container2').find( "input[id^='base']"  ).size(); // (((boxes.childNodes.length)-11)/4)+1;
    var tool = $("input[name=peakcaller]:checked").val();
    var INFO = '';
    var OK = 1; // this control the input of the data
    var OKcheck = 1; // this control that the data is really present in the
						// database
    INFO = INFO + "<form id=\"secondaryRUN\" action=\"pages/secondary/common/submit.php\" method=\"POST\">";
    INFO = INFO + "<input type='hidden' name='analysis' value='secondary' />";
    INFO = INFO + "<input type='hidden' name='method' value='peak_calling' />";
    INFO = INFO + "<input type='hidden' name='exp_name' value='"+exp_name+"' />";
    INFO = INFO + "<input type='hidden' name='program' value='"+tool+"'/>";
    for (var ind = 1; ind <= num; ind++){
        var BASEname = 'base'+ind;
        var INPUTname = 'input'+ind;
        var LABELname = 'label'+ind;

        var unknowmSample = ""
        
        if ($('#' + LABELname).val() == '' || $('#' + BASEname).val() == '' || $('#' + INPUTname).val() == '' || exp_name == '') {
            alert('You have to fill all the lines. Please provide EXP NAME, BASE, INPUT and LABEL.');
            OK = 0;
        } else {
            // we load the available IDs in selectedSamples. If the user write
			// an ID not available, the system raise an error and put OK = 0.
            // OK=1 otherwise.
// selectedSamples = new Array();
// var rows = document.getElementById('selectedSamples').rows;
// rowsL = rows.length;
// for (var i=0; i < rowsL; i++){
// selectedSamples[i] = rows[i].cells[0].innerHTML.trim();
// }
            selectedSamples = $('#selectedIds').val().replace(/\'/g, "").split(" ");
             
            var checkbase = contains(selectedSamples, $('#' + BASEname).val());
            var checkinput = contains(selectedSamples, $('#' + INPUTname).val());
            
            
            if (!checkbase) {
                OKcheck=0;
                unknowmSample += " " + $('#' + BASEname).val();
            }

            if (!checkinput) {
                OKcheck=0;
                unknowmSample += " " + $('#' + INPUTname).val();
            }	
            	  
            INFO = INFO + "<input type='hidden' name='"+BASEname+"' value='"+$('#' + BASEname).val()+"' />";
            INFO = INFO + "<input type='hidden' name='"+INPUTname+"' value='"+$('#' + INPUTname).val()+"' />";
            INFO = INFO + "<input type='hidden' name='"+LABELname+"' value='"+$('#' + LABELname).val()+"' />";
        }
    }
    
    if (tool == 'MACSboth') {
    	pvalue_narrow = $('#narrow_pvalue').length ? $('#narrow_pvalue').val() : "";
    	stats_narrow = $('#narrow_stats').length ? $('#narrow_stats').val() : "";
    	pvalue_broad = $('#broad_pvalue').length ? $('#broad_pvalue').val() : "";
    	stats_broad = $('#broad_stats').length ? $('#broad_stats').val() : "";
        INFO = INFO + "<input type='hidden' name='pvalue' value='" + pvalue_narrow + ";" + pvalue_broad +"' />";
        INFO = INFO + "<input type='hidden' name='stats' value='" + stats_narrow + ";" + stats_broad + "' />";
    } else if  (tool == 'MACSnarrow') {
    	pvalue_narrow = $('#narrow_pvalue').length ? $('#narrow_pvalue').val() : "";
    	stats_narrow = $('#narrow_stats').length ? $('#narrow_stats').val() : "";
    	INFO = INFO + "<input type='hidden' name='pvalue' value='"+pvalue_narrow+"' />";
    	INFO = INFO + "<input type='hidden' name='stats' value='"+stats_narrow+"' />";
    } else {
    	pvalue_broad = $('#broad_pvalue').length ? $('#broad_pvalue').val() : "";
    	stats_broad = $('#broad_stats').length ? $('#broad_stats').val() : "";
    	INFO = INFO + "<input type='hidden' name='pvalue' value='"+pvalue_broad+"' />";
    	INFO = INFO + "<input type='hidden' name='stats'value='"+stats_broad+"' />";
    }
    
    INFO = INFO + "<input type='hidden' name='saturation' value='"+$('#saturation').is(':checked')+"' >";
    INFO = INFO + "<input type='hidden' name='title' value='"+$('#title').val()+"' >";
    INFO = INFO + "<input type='hidden' name='description' value='"+$('textarea#description').val()+"' >";
    
    INFO = INFO + "</form>";
    if (!OKcheck){
        alert("You can use only available IDs. Please check the form: " + unknowmSample + " not in " + $('#selectedIds').val().replace("'", ""));
        OK = 0;
    } 
    if (OK){
        $("#INFO").html(INFO);
        // and then we auto-submit the form that load the data inside the DB.
        // everything is controlled by
        //alert(INFO);
        $("#secondaryRUN").submit();
    }
}
