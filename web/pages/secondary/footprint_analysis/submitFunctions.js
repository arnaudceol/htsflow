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

function submitSecondary() {
    var exp_name = $('#exp_name').val();
    var program = $('#program').val();
    var pvalue = $('#pvalue').val();
    var options = $('#options').val();
    var INFO = '';
    var OK = 1; // this control the input of the data
    // var OKcheck = 1; // this control that the data is really present in the database

    INFO = INFO + "<form id=\"secondaryRUN\" action=\"pages/secondary/common/submit.php\" method=\"POST\" >";
    INFO = INFO + "<input type=\"hidden\" name=\"analysis\" value=\"secondary\" />";
    INFO = INFO + "<input type=\"hidden\" name=\"method\" value=\"footprint_analysis\" />";
    INFO = INFO + "<input type=\"hidden\" name=\"exp_name\" value=\""+exp_name+"\" />";
    INFO = INFO + "<input type=\"hidden\" name=\"program\" value=\""+program+"\" />";
    INFO = INFO + "<input type=\"hidden\" name=\"pvalue\" value=\""+pvalue+"\" />";
    INFO = INFO + "<input type=\"hidden\" name=\"options\" value=\""+options+"\" />";
    INFO = INFO + "<input type='hidden' name='title' value='"+$('#title').val()+"' >";
    INFO = INFO + "<input type='hidden' name='description' value='"+$('textarea#description').val()+"' >";

    selectedSamples = $('#selectedIds').val().replace(/\'/g, "").trim().split(" ");
    
    for (i = 0; i < selectedSamples.length; i++) {
        INFO = INFO + "<input type=\"hidden\" name=\"peak"+i+"\" value=\""+selectedSamples[i]+"\" />";
    }

    INFO = INFO + "</form>";

    if ( exp_name == '' || pvalue == '' ){
        alert('You have to fill all the lines. Please provide EXP NAME, PVALUE, and OPTIONS WHETHER AVAILABLE.');
        OK = 0;
    }     

    if (OK){
        $('#INFO').html(INFO);
        // and then we auto-submit the form that load the data inside the DB.
        // everything is controlled by
        $('#secondaryRUN').submit();
    }
}
