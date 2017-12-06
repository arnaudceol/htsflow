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

$lims = [ ];
$external = [ ];
$merged = [ ];
$primary = [ ];
$secondary = [ ];

$sqlUser = "";
if (isset($user_id)) {
	$sqlUser = " WHERE user_id = " . $user_id;
}

// get samples
$sql = "SELECT user_id, source, count(*) as c FROM sample ". $sqlUser . " group by user_id, source";
$result = mysqli_query ( $con, $sql );
while ( $row = mysqli_fetch_assoc ( $result ) ) {
	$userId = $row ["user_id"];
	$source = $row ["source"];
	$count = $row ["c"];
	if ($source == 0) {
		$lims [$userId] = $count;
	} elseif ($source == 1) {
		$merged [$userId] = $count;
	} elseif ($source == 2) {
		$external [$userId] = $count;
	}
}

// get primary
$sql = "SELECT user_id, count(*) as c FROM primary_analysis ". $sqlUser . " group by user_id";
$result = mysqli_query ( $con, $sql );
while ( $row = mysqli_fetch_assoc ( $result ) ) {
	$userId = $row ["user_id"];
	$count = $row ["c"];
	$primary [$userId] = $count;
}

// get secondary
$sql = "SELECT user_id, count(*) as c FROM secondary_analysis ". $sqlUser . " group by user_id";
$result = mysqli_query ( $con, $sql );
while ( $row = mysqli_fetch_assoc ( $result ) ) {
	$userId = $row ["user_id"];
	$count = $row ["c"];
	$secondary [$userId] = $count;
}

// User table
$sql = "SELECT * FROM users ". $sqlUser . " ORDER BY user_name";

$result = mysqli_query ( $con, $sql );
?>
<div class="datagrid">
	<div class="table-container">
		<table class="mytable filterable" id="sf2" style="width: 100%;">
			<thead>
				<tr>
					<th>User ID</th>
					<th>User name</th>
					<th>System ID</th>
					<th>Samples in LIMS</th>
					<th>External samples</th>
					<th>Merged samples</th>
					<th>Primary analysis</th>
					<th>Secondary analysis</th>
					<?php if (0 != $_SESSION['grantedAdmin']) { ?><th>Permissions</th><?php }?>
				</tr>
			</thead>
			<tbody>
        	    <?php
													// if the number of rows returned from the DB is 0, we have no
													// results, so we print only dashes. Values otherwise.
													while ( $row = mysqli_fetch_assoc ( $result ) ) {
														$userId = $row ["user_id"];
														$userName = $row ["user_name"];
														?><tr class="centered">
					<td><?php echo $userId; ?></td>
					<td><?php echo $userName; ?></td>
					<td><?php echo $row["system_id"]; ?></td>
					<td><?php if (isset($lims[$userId])) {echo $lims[$userId];}  else { echo "-"; } ?></td>
					<td><?php if (isset($external[$userId])) {echo $external[$userId];}  else { echo "-"; } ?></td>
					<td><?php if (isset($merged[$userId])) {echo $merged[$userId];}  else { echo "-"; } ?></td>
					<td><?php if (isset($primary[$userId])) {echo $primary[$userId];}  else { echo "-"; } ?></td>
					<td><?php if (isset($secondary[$userId])) {echo $secondary[$userId];}  else { echo "-"; } ?></td>
					<td><?php if (1 == $_SESSION['grantedAdmin']) { ?>
						<form id="permissions<?php echo $userId; ?>" method="post"
							action="pages/users/update.php">
							<input type="hidden" name="userId" value="<?php echo $userId; ?>" />
							<input type="checkbox" name="grantBrowse" 
								<?php if ($row["granted_browse"] == TRUE) { echo "checked" ;}?> />
							browse <input type="checkbox" name="grantPrimary"
								<?php if ($row["granted_primary"] == TRUE) { echo "checked" ;}?> />
							primary <input type="checkbox" name="grantSecondary"
								<?php if ($row["granted_secondary"] == TRUE) { echo "checked" ;}?> />
							secondary <input type="checkbox" name="grantAdmin"
								<?php if ($row["granted_admin"] == TRUE) { echo "checked" ;}?> />
							admin <input type="submit" value="update"
								class="fa fa-arrow-circle-o-right" />
						</form> <script>
            						
        						$('#permissions<?php echo $userId; ?>').submit(function() { // catch the form's submit event
        						    $.ajax({ // create an AJAX call...
        						        data: $(this).serialize(), // get the form data
        						        type: $(this).attr('method'), // GET or POST
        						        url: $(this).attr('action'), // the file to call
        						        success: function(response) { // on success..
        						            $('#messages').html(response); // update the DIV
        						        }
        						    });
        						    return false; // cancel original event to prevent form submitting
        						});
        						</script>
        						<?php } else { 
        							if ($row["granted_browse"] == TRUE) { echo "Browse " ;}
									if ($row["granted_primary"] == TRUE) { echo "Primary " ;}
        							if ($row["granted_secondary"] == TRUE) { echo "Secondary " ;}
        							if ($row["granted_admin"] == TRUE) { echo "Admin" ;}
        						}
        						?>
					</td>
				</tr>
        						<?php
													}							
													
													?>
        	</tbody>
		</table>


	</div>
</div>
