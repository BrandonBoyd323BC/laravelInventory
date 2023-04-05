<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../protected/procfile.php");
	require_once('../protected/classes/tc_calendar.php');
	include('../phpseclib1.0.11/Net/SSH2.php');

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
					
						if (isset($_POST["category"]) && isset($_POST["tvPowerStat"]))  {
							$category = $_POST["category"];
							$tvPowerStat = $_POST["tvPowerStat"];

							$ret .= "		<h4>Refreshed On: " . date('Y-m-d g:i a') ."</h4>\n";

							$ret .= "		<div style='width: 100%; overflow: hidden;'>\n";
							$ret .= "			<div style='width: 600px; float: left;''>\n";

							$sql  = "SELECT count(*) as cnt ";
							$sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
							$sql .= " WHERE la.FLAG_COMPLETE is NULL ";
							if ($category <> 'ALL') {
								$sql .= " AND la.CATEGORY = '".$category."' ";
								//$sql .= " AND la.CATEGORY = 'GREEN' ";
							}
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								if ($DB_TEST_FLAG == "") {
									if ($row["cnt"] > 0 && $tvPowerStat <> 'ON') {
										$backWarehouseTvIP = '192.168.101.204';
										$ssh = new Net_SSH2($backWarehouseTvIP);
										if (!$ssh->login("pi", "NSAmfg123")) {
										   error_log("rbPi: ".$backWarehouseTvIP." Login Failed");
										}
										$output = $ssh->exec('echo on 0 | cec-client -s -d 1');
										$tvPowerStat = "ON";
									} elseif ($row["cnt"] == 0 && $tvPowerStat == 'ON') {
										$backWarehouseTvIP = '192.168.101.204';
										$ssh = new Net_SSH2($backWarehouseTvIP);
										if (!$ssh->login("pi", "NSAmfg123")) {
										   error_log("rbPi: ".$backWarehouseTvIP." Login Failed");
										}
										$output = $ssh->exec('echo standby 0 | cec-client -s -d 1');
										$tvPowerStat = "OFF";
									}
								}
								$ret .= "		<input type='hidden' id='tvPowerStat' value='".$tvPowerStat."'>\n";
								$ret .= "		<font style=\"font-size: 225px\">".$row["cnt"]."</font>\n";
							}
							$ret .= "			</div>\n";
							$ret .= "			<div style='margin-left: 620px;'>\n";


							$sql  = "SELECT e.NAME_EMP, la.*, op.*, convert(varchar,op.DATE_ADD, 0) as OP_DATE_ADD ";
							$sql .= " FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " la ";
							$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
							$sql .= " on ltrim(la.TEAM_BADGE) = ltrim(e.ID_BADGE) ";
							$sql .= " and e.CODE_ACTV = 0 ";
							$sql .= " LEFT JOIN nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . " op ";
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
								//$ret .= "			<th>Category</th> ";
								$ret .= "			<th><font class ='heading'>Team</font></th> ";
								$ret .= "			<th><font class ='heading'>Time</th> ";
								$ret .= "			<th><font class ='heading'>SO</font></th>\n";
								$ret .= "			<th><font class ='heading'>Missing Item</font></th>\n";
								$ret .= "			<th><font class ='heading'>QTY Missing</font></th>\n";
								$ret .= "			<th><font class ='heading'>Comments</font></th>\n";
								$ret .= "		</tr> ";
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= "		<tr class='sample'>\n";
									//$ret .= "			<td><font class = 'tvblack'>".$row["CATEGORY"]."</font></td> ";
									$ret .= "			<td><font size = '5'>".$row["TEAM_BADGE"]." - ".$row["NAME_EMP"]."</font></td> ";
									$ret .= "			<td><font class = 'tvblack'>".$row["OP_DATE_ADD"]."</font></td> ";
									$ret .= "			<td><font class = 'tvblack'>" . $row['ID_SO'] . "-".$row['SUFX_SO']."</font></td>\n";
									$ret .= "			<td style=\"text-align: center\"><font class = 'tvblack'>" . $row['ID_ITEM_COMP'] . "</font></td>\n";
									$ret .= "			<td style=\"text-align: center\"><font class = 'tvblack'>" . $row['QTY_MISSING'] . "</font></td>\n";
									$ret .= "			<td style=\"text-align: center\"><font class = 'tvblack'>" . $row['COMMENTS'] . "</font></td>\n";
									$ret .= "		</tr> ";
								}
								$ret .= "</table>\n";
							}
							$ret .= "			</div>\n";
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
