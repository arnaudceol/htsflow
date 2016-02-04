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

<b>Analyses:</b>
<ul class="fa-ul">
<li><i class="fa fa-thumbs-up"></i> Completed analysis.</li>
<li><i class="fa fa-thumbs-down"></i> Analysis with error.</li>
<li><i class="fa fa-cog"></i> Running analysis.</li>
<li><i class="fa fa-times" style="color: grey"></i> Deleted analyses.</li>
<li><i class="fa fa-clock-o" style="color: grey"></i> Queued/Waiting to start.</li>
</ul>

<b>Table navigation:</b>
<ul class="fa-ul">
<li><i class="fa fa-refresh"></i> Click to update the table.</li>
<li><i class="fa fa-fast-backward"></i> Go to first page.</li>
<li><i class="fa fa-backward"></i> Go to previous page.</li>
<li><i class="fa fa-forward"></i> Go to next page.</li>
<li><i class="fa fa-fast-forward"></i> Go to last pages.</li>
</ul>

<b>Actions:</b>
<ul class="fa-ul">
<li><i class="fa fa-eraser"></i> Delete analysis: all data output will be removed from disk and the analysis will be marked as deleted.</li>
<li><i class="fa fa-repeat"></i> Repeat the analysis: only available for analysis which have been previously deleted.</li>
<li><i class="fa fa-info"></i> Click to show details.</li>
<li><i class="fa fa-pencil"></i> Edit information.</li> 
<li><i class="fa fa-file-text-o" style="color: green"></i> Description available (click to edit).</li>
<li><i class="fa fa-file-o" style="color: red"></i> Missing description (click to edit).</li>
</ul>

<b>Miscelanous:</b>
<ul class="fa-ul">
<li><i class="fa fa-times"></i> Close view.</li>
<li><i class="fa fa-question"></i> Show this legend.</li>
<li><img src="images/fastqc_icon.png" width="12"/> Show FastQC report.</li>
<li><i class="fa fa-folder"></i> Browse data.</li>
<li><i class="fa fa-download"></i> Download data.</li>
<li><i class="fa fa-share"></i> See follow up analysis</li>
<li><i class="fa fa-reply"></i> See source sample/analysis</li>
<li><i class="fa fa-newspaper-o"></i> See logs and output.</li>
<li><img src="images/igb.jpg" width="18"/> Import in the Integrated Genome Browser (IGB). IGB should be installed and running (<a href="http://bioviz.org/igb/index.html">http://bioviz.org/igb/index.html</a>). </li>
</ul>
