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
    var INFO = '';
    var OK = 1; // this control the input of the data
    var no_overlap = $('#no_overlap').val();
    var read_context = $('#read_context').val();
    INFO = INFO + "<form id=\"secondaryRUN\" action=\"pages/secondary/common/submit.php\" method=\"POST\">";
    INFO = INFO + "<input type=\"hidden\" name=\"analysis\" value=\"secondary\" />";
    INFO = INFO + "<input type=\"hidden\" name=\"method\" value=\"methylation_calling\" />";
    selectedSamples = $('#selectedIds').val().replace(/\'/g, "").split(" ");
    for (i = 0; i < selectedSamples.length; i++) {
        INFO = INFO + "<input type=\"hidden\" name=\"sample"+i+"\" value=\""+selectedSamples[i]+"\" />";
    }
 
    INFO = INFO + "<input type=\"hidden\" name=\"no_overlap\" value=\""+no_overlap+"\" />";
    INFO = INFO + "<input type=\"hidden\" name=\"read_context\" value=\""+read_context+"\" />";
    INFO = INFO + "<input type='hidden' name='title' value='"+$('#title').val()+"' >";
    INFO = INFO + "<input type='hidden' name='description' value='"+$('#description').val()+"' >";
    INFO = INFO + "</form>";
    if (OK){
        $('#INFO').html(INFO);   
        $('#secondaryRUN').submit();
    }
}
