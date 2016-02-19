<?php
/*
 * Copyright 2015-2016 Fondazione Istituto Italiano di Tecnologia.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
?>
<form style="display: inline-block;" name="CONTROL" action=""
	method="post">
	<!--	    // FILTERING OPTIONS-->
	<table class="filtertable">
		<tbody>
			<tr>
				<th><b>Refine your search:</b></th>
				<td><select id="status" name="status">
						<option value="" selected>All status</option>
						<option value="completed"
							<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "completed") { echo "selected"; }?>>completed</option>
						<option value="running"
							<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "running") { echo "selected"; }?>>running</option>
						<option value="error"
							<?php if (isset ( $_POST ['status'] ) && $_POST ['status'] == "error") { echo "selected"; }?>>error</option>
				</select></td>
				<td><select name="level">
						<option value="" selected>All levels</option>
						<option value="primary"
							<?php if (isset ( $_POST ['level'] ) && $_POST ['level'] == "primary") { echo "selected"; }?>>primary</option>
						<option value="secondary"
							<?php if (isset ( $_POST ['level'] ) && $_POST ['level'] == "secondary") { echo "selected"; }?>>secondary</option>
				</select></td>
				<td><?php
    
    $sql = "SELECT 'primary' as level, seq_method as method FROM sample WHERE source <> 1 UNION SELECT 'secondary', method FROM secondary_analysis ORDER BY level, method ASC;";
    
    $result = mysqli_query($con, $sql);
    ?><select name="method">
						<option value="" selected>All method</option>
						<?php
    while ($row = mysqli_fetch_assoc($result)) {
        ?><option value="<?php echo $row["method"]; ?>"
							<?php if (isset ( $_POST ['method'] ) && $_POST ['method'] == $row["method"]) { echo "selected"; }?>><?php echo $row["level"] . ": " . $row["method"]; ?></option><?php
    }
    mysqli_free_result($result);
    ?>
                        </select></td>
				<?php if ( $_SESSION['grantedAdmin'] == 1 ) {?>
				<td><?php
        $sql = "SELECT DISTINCT users.user_id, user_name FROM primary_analysis, users WHERE users.user_id = primary_analysis.user_id ORDER BY user_name ASC;";
        $result = mysqli_query($con, $sql);
        ?>
                        <select id="user_id" name="user_id">
						<option value="" selected>All user</option>
						<?php
        while ($row = mysqli_fetch_assoc($result)) {
            ?><option value="<?php echo $row["user_id"]; ?>"
							<?php if (isset ( $_POST ['user_id'] ) && $_POST ['user_id'] == $row["user_id"]) { echo "selected"; }?>><?php echo $row["user_name"]; ?></option><?php
        }
        mysqli_free_result($result);
        ?>
                        </select></td>
				<td>
				<?php } ?>
				
				<td><input type="submit" value="filter" /></td>
			</tr>
		</tbody>
	</table>
</form>


<?php include 'submitOnEnter.php'; ?>


