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

?><div>Please enter:
	<ul><li>The ID of the input and the ID of the ChIP;
		<ul><li>if you do
		not have an input for the ChIP fill both with ChIP ID.</li>
		<li>if you have more then one ChIP click the
		ADD button to insert another ChIP.</li>
		</ul></li>
		<li>A label: <ul><li>do not use space when
		you name analysis or labels, use instead the underscore ( _ ).</li>
	<li>do not use numbers as starting character for naming the
		analysis or labels (ex. 0h_Myc, use instead Myc_0h).</li>
		</ul></li></ul>
</div>

<div id="container2"
	style="float: left; padding-left: 10px; padding-right: 10px;">
	<!--        // in this container will be generated the rows of the table-->

	<div id="add1" style="float: right; margin-left: 50px;">
		<p id="addbutton">
			<button id="AddLine" onclick="peakAddLine()">ADD</button>
			<button id="remLine" onclick="peakRemoveLine()" disabled>REMOVE</button>
		</p>
	</div>

	<div id="riquadro" style="float: left; width: 80px;">
		<p style="color: #00557F; font-weight: normal;">Input</p>
		<input type="text" id="input1" class="samples" style="width: 70px;" />
	</div>

	<div id="riquadro" style="float: left; width: 80px;">
		<p style="color: #00557F; font-weight: normal;">ChIP</p>
		<input type="text" id="base1" class="samples" style="width: 70px;" />
	</div>
	
	<div id="riquadro" style="float: left; width: 210px;">
		<p style="color: #00557F; font-weight: normal;">LABEL</p>
		<input type="text" id="label1" class="labels" style="width: 200px;"
			size="10" maxlength="30" />
	</div>
	<div style="clear: both;"></div>
</div>


<div style="clear: both;"></div>

<br />
<br />



<div style="clear: both;"></div>
<form name='PROG' id='PROGselected' action=''
	onchange='PeakCallerOptions();'>
	You want to find: <input type="radio" id='peakcaller' name="peakcaller" onclick='PeakCallerOptions()' value='MACSnarrow' checked/>NARROW PEAKS (MACS2)
		<input type="radio" id='peakcaller' name="peakcaller" onclick='PeakCallerOptions()' value='MACSbroad'/>BROAD PEAKS (MACS2)
		<input type="radio" id='peakcaller' name="peakcaller" onclick='PeakCallerOptions()' value='MACSboth'/>NARROW/BROAD PEAKS (MACS2)
</form>

<div id="OPTIONS" style="clear: both;">
	<form id='RUNform' action="" method="post">	
	</form>
</div>

<script>PeakCallerOptions();</script>

<div id="INFO"></div>
