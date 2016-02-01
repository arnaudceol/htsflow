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

function submitSecondary(){
    var boxes = document.getElementById('container2');
    var num = $('#container2').find( "input[id^='sample']"  ).size(); //(((boxes.childNodes.length)-11)/4)+1;
//    var num = (((boxes.childNodes.length)-9)/3)+1;
    var INFO = '';
    var OK = 1; // this control the input of the data
    var OKcheck = 1; // this control that the data is really present in the database
    INFO = INFO + "<form id=\"secondaryRUN\" action=\"pages/secondary/common/submit.php\" method=\"POST\">";
    INFO = INFO + "<input type='hidden' name='analysis' value='secondary' >";
    INFO = INFO + "<input type='hidden' name='method' value='expression_quantification' >";
    for (var ind = 0; ind < num; ind++){
        var SAMPLEname = 'sample'+ind;
        var MIXname = 'mix'+ind;
        //alert(SAMPLEname);
        if ( document.getElementById(SAMPLEname).value == '' ) {
            alert('You have to fill all the lines. Please provide SAMPLE name and/or Mix type if available.');
            OK = 0;
        } else {
            // we load the available IDs in selectedSamples. If the user write an ID not available, the system raise an error and put OK = 0.
            // OK=1 otherwise.
//            selectedSamples = new Array();
//            var rows = document.getElementById('selectedSamples').rows;
//            rowsL = rows.length;
//            for (var i=0; i < rowsL; i++){
//                selectedSamples[i] = rows[i].cells[0].innerHTML.trim();
//            }
        	selectedSamples = $('#selectedIds').val().replace(/\'/g, "").split(" ");
        	
            var checkbase = contains(selectedSamples, document.getElementById(SAMPLEname).value);
            
            if (!checkbase){
                OKcheck=0;
            }          
            INFO = INFO + "<input type='hidden' name='"+SAMPLEname+"' value='"+document.getElementById(SAMPLEname).value+"' />";
            INFO = INFO + "<input type='hidden' name='"+MIXname+"' value='"+document.getElementById(MIXname).value+"' />";
        }
    }
    INFO = INFO + "<input type='hidden' name='title' value='"+$('#title').val()+"' >";
    INFO = INFO + "<input type='hidden' name='description' value='"+$('#description').val()+"' >";
    // in this part we put the form inside the div INFO.  
    INFO = INFO + "</form>";
    if (!OKcheck){
        alert("You can use only available IDs. Please check the form.");
        OK = 0;
    } 
    if (OK){
        document.getElementById("INFO").innerHTML=INFO;
        // and then we auto-submit the form that load the data inside the DB.
        // everything is controlled by
        //alert(INFO);
        document.getElementById("secondaryRUN").submit();
    }
}



//function expressionAddLine(){
//    var boxes = document.getElementById('container2');
//    var nchild = (((boxes.childNodes.length)-9)/3)+2;
// 
//    var new_div1 = document.createElement('div');
//    new_div1.setAttribute('id','riquadro');   
//    boxes.appendChild(new_div1);
//    var base1 = document.createElement('input');
//    var NAME = "sample"+String(nchild)
//    //base1.setAttribute("value", NAME);
//    base1.setAttribute("id", NAME);
//    new_div1.appendChild(document.createTextNode("Sample: "));
//    new_div1.appendChild(base1);
//    new_div1.appendChild(document.createTextNode(" MIX: "));
//    var input1 = document.createElement('select');
//    var NAME = "mix"+String(nchild)
//    input1.setAttribute("id", NAME);
//    new_div1.appendChild(input1);    
//    var x = document.getElementById(NAME);
//    var option = document.createElement("option");
//    option.text = "";
//    x.add(option);
//    var option = document.createElement("option");
//    option.text = "1";
//    x.add(option);
//    var option = document.createElement("option");
//    option.text = "2";
//    x.add(option);
//    
//    document.getElementById("remLine").disabled=false;
//
//}
//
//
//function expressionRemoveLine(){
//    var boxes = document.getElementById('container2');    
//    boxes.removeChild(boxes.lastChild);
//    if (boxes.childNodes.length == 9) {
//        document.getElementById("remLine").disabled=true;
//    }
//}
