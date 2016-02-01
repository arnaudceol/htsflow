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
		Please control that this set of options follow your needs:
		<ul><li><b>NO OVERLAP:</b> if TRUE and the SAM file has paired-end
		reads, then one read of the overlapping paired-end read pai will be
		ignored for methylation calling.</li><li><b>READ CONTEXT:</b>
		One of the 'CpG' or 'All'. Determines what type of methylation context
		will be read. If given as 'all', cytosine methylation information in
		all sequence context will be read.</li></ul>
</div>

<div id="containerSAMPLENAME" style="float: left; padding-right: 10px;">

	<div>

		<table>
			<tbody>				
				<tr>
					<td valign="middle"><p
							style="color: #00557F; font-size: 1.0em; font-weight: normal;">NO
							OVERLAP</td>
					<td valign="middle"><select id="no_overlap">
							<option value="1">TRUE</option>
							<option value="0">FALSE</option>
					</select></td>
				</tr>

				<tr>
					<td valign="middle"><p
							style="color: #00557F; font-size: 1.0em; font-weight: normal;">READ
							CONTEXT</td>
					<td valign="middle"><select id="read_context">
							<option value="CpG">CpG</option>
							<option value="All">All</option>
					</select></td>
				</tr>
			</tbody>
		</table>
	</div>

</div>
<div style="clear: both;"></div>

<div id="INFO"></div>