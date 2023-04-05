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
			if (isset($_POST["rowid"]))  {

				$RowID = $_POST["rowid"];

				$sql = "select ";
				//$sql .= "	CONVERT(varchar(8), DATE_ATTIN, 112) as DATE_ATTIN3, ";
				$sql .= "	CONVERT(varchar(8), DATE_TRX_PRIOR, 112) as DATE_TRX_PRIOR3, ";
				$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
				$sql .= "	* ";
				$sql .= " from ";
				$sql .= "	nsa.DCEMMS_EMP ";
				$sql .= " where ";
				$sql .= " 	rowid = " . $RowID;
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$memberColor = GetColorBadgeFlag($row['FLAG_ATTEND']);
					$trxColor = GetColorCodeTrxFONT($row['CODE_TRX_PRIOR']);
					$currPR_R = '';

					$currPR = $row['DATE_TRX_PRIOR3'] . " " . str_pad($row['TIME_TRX_PRIOR'],6,"0",STR_PAD_LEFT);
					if (!is_null($row['DATE_TRX_PRIOR3'])) {
						$currtsPR = strtotime($currPR);
						$currPR_R = date('m/d/Y h:i:s A',$currtsPR);
					}

					//$ret .= " 	<div id='div_" . $row['rowid'] . "' >\n";
					//$ret .= " 	<tr id='row_" . $row['rowid'] . "' onClick=\"sendValue('" . $row['rowid'] . "')\">\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font class='" . $memberColor . "'>" . $row['BADGE_NAME'] . "</font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font>" . $row['CODE_USER_1_DC'] . "</font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font>" . $row['ID_BADGE_TEAM_CRNT'] . "</font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font class='" . $trxColor . "'>" . $currPR_R . "</font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font class='" . $trxColor . "'>" . GetStrCodeTrx($row['CODE_TRX_PRIOR']) . "</font>\n";
					$ret .= " 		</td>\n";
					//$ret .= " 	</tr>\n";
					//$ret .= " 	</div>\n";
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
