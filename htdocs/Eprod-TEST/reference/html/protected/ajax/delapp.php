<?php

	//error_log("TEST");

	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	//PrintHeader('','default.css');
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
			if (($UserRow['PERM_SUPERVISOR'] == '1') && isset($_POST['rowid']) && is_numeric($_POST['rowid']))  {
				$rowid = $_POST['rowid'];

				$sql =  "select * ";
				$sql .= " from ";
				$sql .= "  nsa.DCAPPROVALS a ";
				$sql .= " where ";
				$sql .= "	CODE_APP = '300' " ;
				$sql .= "	and " ;
				$sql .= "	DATE_APP = (select a2.DATE_APP from nsa.DCAPPROVALS a2 where a2.rowid = " . $rowid . ") " ;
				QueryDatabase($sql, $results);
				if (mssql_num_rows($results) == 0) {
					//DASHBOARD HAS NOT BEEN APPROVED FOR THE DAY
					$sql =  "delete ";
					$sql .= " from ";
					$sql .= "  nsa.DCAPPROVALS ";
					$sql .= " where ";
					$sql .= "  rowid = " . $rowid;
					QueryDatabase($sql, $results);

					$ret .= " 	<font class='darkred'>Deleted</font>\n";

				} else {

					$sql =  "select * ";
					$sql .= " from ";
					$sql .= "	nsa.DCAPPROVALS a ";
					$sql .= " where ";
					$sql .= "	a.rowid = " .$rowid ;
					QueryDatabase($sql, $results);

					while ($row = mssql_fetch_assoc($results)) {
						if ($row['CODE_APP'] == '200') {
							$transText = "Approved by: ";
						}
						if ($row['CODE_APP'] == '201') {
							$transText = "Set for Review by: ";
						}
						$ret .= "<div id='div_signoff_" . $row['CODE_APP'] . "'>\n";
						$ret .= "<table class='sample'>\n";
						$ret .= "	<th class='sample' colspan=3>Supervisor Approval</th>\n";
						$ret .= "	<td id='x_signoff' onclick=\"closeDiv('div_signoff_" . $row['CODE_APP'] . "')\" TITLE='Remove Table'>X</td>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td><b>" . $transText . "</b></td>\n";
						$ret .= "		<td>" . $row['APP_BY_ID_USER'] . "</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td><b>On: </b></td>\n";
						$ret .= "		<td>" . $row['DATE_ADD'] . "</td>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td><b>Comments: </b></td>\n";
						$ret .= "		<td>" . $row['COMMENTS'] . "</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td><b>Earned: </b></td>\n";
						$ret .= "		<td>" . $row['EARNED_MINS'] . "</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td><b>Actual: </b></td>\n";
						$ret .= "		<td>" . $row['ACTUAL_MINS'] . "</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td><b>Pct: </b></td>\n";
						$ret .= "		<td>" . round(($row['EARNED_MINS'] / $row['ACTUAL_MINS']) * 100,2) . "</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td></td>\n";
						$ret .= "		<td><input type='button' id='del_approval_" . $row['rowid'] . "' value='Delete' onClick=\"deleteApproval('" . $row['rowid'] ."', 'div_signoff_" . $row['CODE_APP'] . "')\" /></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr>\n";
						$ret .= "		<td colspan=2><font class='darkred'>Cannot delete, Dashboard has already been approved.</font></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						$ret .= "	</br>\n";
						$ret .= "</div>\n";
					}
				}

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
