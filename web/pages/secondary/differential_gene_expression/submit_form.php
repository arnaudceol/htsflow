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
	For each sample, please provide:
	<ul>
		<li>Primary ID: The ID of the related primary analyses.</li>
		<li>Condition: a name for the condition, usually <b>treated</b> and <b>control</b>.
			Please bear in mind that the names are used by DESeq2 in alphabetical
			order. So if you label two conditions 'a' and 'b' the analysis will
			be performed 'b vs a'.
		</li>
		<li>MIX: the type of ERCC Mix used if available (currently under
			test);</li>
	</ul>

	<p>Do not use space when you name analysis or labels, use instead the
		underscore ( _ ).</p>
	<p>Avoid numbers as starting character for naming the analysis or
		labels (ex. 0h_Myc, use instead Myc_0h).</p>

</div>
<div id="container2">
	<div id="riquadro" style="float: left;">
		<p style="color: #00557F;">Primary ID</p>
		<p id="sample">
			<input type="text" id="sample1" size="10" maxlength="30">
		</p>
	</div>

	<div id="riquadro" style="float: left;">
		<p style="color: #00557F; font-weight: normal;">CONDITION</p>
		<p id="condition">
			<input type="text" id="condition1">
		</p>
	</div>
	<input type="hidden" id="mix1" value=""/>
<!--	<div id="riquadro" style="float: left;"> -->
<!--		<p style="color: #00557F; font-weight: normal;">MIX</p> -->
<!-- 		<p id="mix"> -->
<!-- 			<select id="mix1"> -->
<!-- 				<option value=""></option> -->
<!-- 				<option value="1">1</option> -->
<!-- 				<option value="2">2</option> -->
<!-- 			</select> -->
<!-- 		</p> -->
<!-- 	</div> -->
	<div style="clear: both;"></div>
</div>

<div id="add1">
	<p id="addbutton">
		<button id="AddLine" onclick="degAddLine()">ADD</button>
		<button id="remLine" onclick="degRemoveLine()" disabled>REMOVE</button>
	</p>
</div>

<div id="INFO"></div>
