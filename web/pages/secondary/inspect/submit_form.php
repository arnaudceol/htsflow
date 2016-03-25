<?php 
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
?>
<div>

	<p>Do not use space when you name analysis or labels, use instead the
		underscore ( _ ).</p>
	<p>Avoid numbers as starting character for naming the analysis or
		labels (ex. 0h_Myc, use instead Myc_0h).</p>

</div>

<script>

function setInspect() {

	$("#subform").css('visibility', 'visible');
	$("#selectMessage").css('visibility', 'hidden'); 	
	var inspectType = $("input[type='radio'][name='inspect_type']:checked").val();
	if (inspectType == "time_course") {
		$(".inspectCondition").val('0');
		$(".inspectCondition").prop('disabled', true);
		$(".inspectTimepoint").prop('disabled', false);
	} else if (inspectType == "steady_states") {
		$(".inspectTimepoint").val('0');
		$(".inspectTimepoint").prop('disabled', true);
		$(".inspectCondition").prop('disabled', false);
	}
}

</script>

<div style="color: orange" id="selectMessage">Choose a type of analyses to show the available options.</div>
<input type="radio" name="inspect_type"
					value="time_course" onchange="setInspect()"
					/> TIME COURSE ANALYSIS  
<input type="radio" name="inspect_type" onchange="setInspect()"
					value="steady_states"/>STEADY-STATES DIFFERENTIAL ANALYSIS
<br/>	


<div id="subform" style="visibility: hidden">		

DEG During pulse: <input type="checkbox" id="deg_during_pulse" /><br/>
Modeling rates: <input type="checkbox" id="modeling_rates" /><br/>
Count filtering: <input type="text" id="counts_filtering" size="2" maxlength="2" pattern="[0-5]" value="5"/><br/>

<div id="container2" style="padding:10px; margin: 10px">
	<div id="riquadro" style="float: left;">
		<p style="color: #00557F;">4SU</p>
		<input type="text" id="foursu_primary_id1" size="20" maxlength="30" >		
	</div>
	
	<div id="riquadro" style="float: left;">
		<p style="color: #00557F;">RNA total</p>
		<input type="text" id="rnatotal_primary_id1" size="20" maxlength="30">		
	</div>
	
	<div id="riquadro" style="float: left;">
		<p style="color: #00557F; font-weight: normal;">CONDITION</p>
		<input class="inspectCondition" type="text" id="condition1" >
	</div>
	
	<div id="riquadro" style="float: left;">
		<p style="color: #00557F; font-weight: normal;">TIME POINT</p>
		<input class="inspectTimepoint" type="text" id="timepoint1" pattern="[0-9]+(\.[0-9]+)?">
	</div>

	<div style="clear: both;"></div>
</div>

<div id="add1">
	<p id="addbutton">
		<button id="AddLine" onclick="inspectAddLine();setInspect()">ADD</button>
		<button id="remLine" onclick="inspectRemoveLine()" disabled>REMOVE</button>
	</p>
</div>

<div id="INFO"></div>
</div>


