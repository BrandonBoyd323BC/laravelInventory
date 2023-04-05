<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				switch ($action) {
					/////////////////
					// Show Dashboard Records
					/////////////////
					case "showDashRecords":
						if (isset($_POST["category"]))  {
							$category = $_POST["category"];

							$ret .= "		<h4>Refreshed On: " . date('Y-m-d g:i a') ."</h4>\n";


							$sql  = "SELECT count(*) as cnt ";
							$sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
							$sql .= " WHERE la.FLAG_COMPLETE is NULL ";
							if ($category <> 'ALL') {
								$sql .= " AND la.CATEGORY = '".$category."' ";
							}
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<font style=\"font-size: 100px\">".$row["cnt"]."</font>\n";
							}


							$sql  = "SELECT e.NAME_EMP, la.*, op.*, op.DATE_ADD as OP_DATE_ADD ";
							$sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
							$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
							$sql .= " on ltrim(la.TEAM_BADGE) = ltrim(e.ID_BADGE) ";
							$sql .= " and e.CODE_ACTV = 0 ";
							$sql .= " LEFT JOIN nsa.ORD_PREP_MISSING_TEST op ";
							$sql .= " on la.TEAM_BADGE = op.ID_BADGE_ADD and op.FLAG_COMPLETE != 1 ";
							$sql .= " WHERE la.FLAG_COMPLETE is NULL ";
							if ($category <> 'ALL') {
								$sql .= " AND la.CATEGORY = '".$category."' ";
							}
							$sql .= " ORDER BY la.DATE_ADD asc ";
							QueryDatabase($sql, $results);

							if (mssql_num_rows($results) > 0) {
								$ret .= "<table class='sample'>\n";
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<th>Category</th> ";
								$ret .= "			<th>Team</th> ";
								$ret .= "			<th>Time</th> ";
								$ret .= "			<th><font>SO</font></th>\n";
								$ret .= "			<th><font>Missing Item</font></th>\n";
								$ret .= "			<th><font>QTY Missing</font></th>\n";
								$ret .= "			<th><font>Comments</font></th>\n";
								$ret .= "		</tr> ";
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= "		<tr class='sample'>\n";
									$ret .= "			<td>".$row["CATEGORY"]."</td> ";
									$ret .= "			<td>".$row["TEAM_BADGE"]." - ".$row["NAME_EMP"]."</td> ";
									$ret .= "			<td>".$row["OP_DATE_ADD"]."</td> ";
									$ret .= "			<td><font>" . $row['ID_SO'] . "</font></td>\n";
									$ret .= "			<td><font>" . $row['ID_ITEM_COMP'] . "</font></td>\n";
									$ret .= "			<td><font>" . $row['QTY_MISSING'] . "</font></td>\n";
									$ret .= "			<td><font>" . $row['COMMENTS'] . "</font></td>\n";
									$ret .= "		</tr> ";
								}
								$ret .= "</table>\n";
							}
						}
					break;



				}//end Switch
			}

			echo json_encode(array("returnValue"=> $ret));
			
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
