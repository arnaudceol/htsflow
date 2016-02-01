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
    var exp_name = $('#exp_name').val();
    
    var boxes = $('#container2');
    var num = $('#container2').find( "input[id^='sample']"  ).size(); //(((boxes.childNodes.length)-11)/4)+1;
    
    //var num = (((boxes.childNodes.length)-11)/4)+1;
    var INFO = '';
    var OK = 1; // this control the input of the data
    var OKcheck = 1; // this control that the data is really present in the database
    INFO = INFO + "<form id=\"secondaryRUN\" action=\"pages/secondary/common/submit.php\" method=\"POST\">";    
    INFO = INFO + "<input type='hidden' name='analysis' value='secondary' />";
    INFO = INFO + "<input type='hidden' name='method' value='differential_gene_expression' />";
    INFO = INFO + "<input type='hidden' name='exp_name' value='"+exp_name+"' />";
    
    var myArray = new Array();
    var myConditions = new Array();

    for (var ind = 1; ind <= num; ind++){
        var SAMPLEname = 'sample'+ind;
        var CONDITIONname = 'condition'+ind;
        var MIXname = 'mix'+ind;
        if ($("#" + SAMPLEname).val() == '' || $("#" + CONDITIONname).value == '' || exp_name == ''){
            alert('You have to fill all the lines. Please provide EXP NAME, SAMPLE ID, CONDITION and MIX WHETHER AVAILABLE.');
            OK = 0;
        } else {
            // we load the available IDs in selectedSamples. If the user write an ID not available, the system raise an error and put OK = 0.
            // OK=1 otherwise.
//            selectedSamples = new Array();
//            var rows = document.getElementById('selectedSamples').rows;
//            rowsL = rows.length;
//            for (var i=0; i < rowsL; i++){
//                selectedSamples[i] = rows[i].cells[0].innerHTML;
//            }
        	selectedSamples = $('#selectedIds').val().replace(/\'/g, "").split(" ");
        	var checkSAMPLE = contains(selectedSamples, document.getElementById(SAMPLEname).value);
            if (!checkSAMPLE){
                OKcheck=0;
            }       
    
            var stringa = $("#" + SAMPLEname).val() + ' ' + $("#" + CONDITIONname).val() + ' ' + $("#" + MIXname).val();
            
            myArray.push(stringa);
            // fill an array for checking the total number of unique conditions that has to be 2
            if( !contains( myConditions, document.getElementById(CONDITIONname ).value ) ){
                myConditions.push( document.getElementById(CONDITIONname).value );
            }
        }
    }

    if( myConditions.length != 2 ) {
        OKcheck = 0;
        alert("You are using a wrong number of conditions for this experiment. Remember that you can use only two conditions. Ex: treat, control.");
    }

    uniqueElements = new Array();
    for ( var i = 0; i<myArray.length; i++) {
        if( !contains(uniqueElements, myArray[i]) ){
            uniqueElements.push(myArray[i]);
        }
    }

    for ( var indice = 0; indice<uniqueElements.length; indice++) { //for every unique element
        var res = uniqueElements[indice].split(" "); //I get the number of elements (3)
        var index = indice+1;
        var SAMPLEname = 'sample'+index;
        var CONDITIONname = 'condition'+index;
        var MIXname = 'mix'+index;
        INFO = INFO + "<input type='hidden' name='"+SAMPLEname+"' value='"+res[0]+"' />";
        INFO = INFO + "<input type='hidden' name='"+CONDITIONname+"' value='"+res[1]+"' />";
        INFO = INFO + "<input type='hidden' name='"+MIXname+"' value='"+res[2]+"' />";
    }   

    INFO = INFO + "<input type='hidden' name='title' value='"+$('#title').val()+"' >";
    INFO = INFO + "<input type='hidden' name='description' value='"+$('#description').val()+"' >";
    
    // in this part we put the form inside the div INFO.
    INFO = INFO + "</form>";
    if (!OKcheck){
        alert("There were problems in the compilation of the form. Please check it again. Check in order: PRIMARY IDs provided, number of conditions equal to 2.");
        OK = 0;
    } 
    if (OK){
        if (uniqueElements.length != myArray.length) {
            alert("You fill some samples twice in the table with the same IDs. They are automatically removed. Check carefully your analysis in the end.")
        }
        document.getElementById("INFO").innerHTML=INFO;
        // and then we auto-submit the form that load the data inside the DB.
        // everything is controlled by
        document.getElementById("secondaryRUN").submit();
    }
}




function degRemoveLine(){
    var boxes = document.getElementById('container2');    
    boxes.removeChild(boxes.lastChild);
    boxes.removeChild(boxes.lastChild);
    boxes.removeChild(boxes.lastChild);
    boxes.removeChild(boxes.lastChild);
    if (boxes.childNodes.length == 11) {
        document.getElementById("remLine").disabled=true;
    }
}


function degAddLine(){
    var boxes = document.getElementById('container2');
    var num = $('#container2').find( "input[id^='sample']"  ).size() + 1; //(((boxes.childNodes.length)-11)/4)+2; 
 
    var new_div1 = document.createElement('div');
    new_div1.setAttribute('id','riquadro');
    new_div1.style.cssFloat = "left";
    //new_div1.style.paddingLeft = "20px";
    boxes.appendChild(new_div1);
    var base1 = document.createElement('input');
    var NAME = "sample"+String(num)
    
    //base1.setAttribute("value", NAME);
    base1.setAttribute("id", NAME);
    base1.setAttribute("size", 10);
    base1.setAttribute("maxlength", 30);
    new_div1.appendChild(base1);

    var new_div2 = document.createElement('div');
    new_div2.setAttribute('id','riquadro');
    new_div2.style.cssFloat = "left";
    //new_div2.style.paddingLeft = "15px";
    boxes.appendChild(new_div2);
    var label1 = document.createElement('input');
    var NAME = "condition"+String(num) 
    //label1.setAttribute("value", NAME);
    label1.setAttribute("id", NAME);
    new_div2.appendChild(label1);

    var new_div3 = document.createElement('div');
    new_div3.setAttribute('id','riquadro');
    new_div3.style.width = "80px";
    new_div3.style.cssFloat = "left";
    new_div3.style.minHeight = "50px";
    boxes.appendChild(new_div3);
    var input1 = document.createElement('select');
    var NAME = "mix"+String(num)
    input1.setAttribute("id", NAME);
    new_div3.appendChild(input1);
    var x = document.getElementById(NAME);
    var option = document.createElement("option");
    option.text = "";
    x.add(option);
    var option = document.createElement("option");
    option.text = "1";
    x.add(option);
    var option = document.createElement("option");
    option.text = "2";
    x.add(option);

    var new_div4 = document.createElement('div');
    new_div4.style.clear = "both";
    boxes.appendChild(new_div4);    

    document.getElementById("remLine").disabled=false;

}
