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
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if ($UserRow['PERM_HR'] == '1') {
				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
				$ret = '';

				$sql =  "select ";
				$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
				$sql .= " 	ID_BADGE,";
				$sql .= " 	NAME_EMP,";
				$sql .= "	* ";
				$sql .= " from ";
				$sql .= " 	nsa.DCEMMS_EMP ";
				$sql .= " where ";
				$sql .= " 	TYPE_BADGE = 'X'";
				$sql .= " 	and";
				$sql .= " 	CODE_ACTV = '0'";
				$sql .= " order by BADGE_NAME asc";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {

					//////////////
					//TEAM
					//////////////
					$ID_BADGE = trim($row['ID_BADGE']);
					$teamColor = GetColorBadgeFlag($row['FLAG_ATTEND']);

					$ret .= "<div id='div_" . $ID_BADGE . "' name='div_" . $ID_BADGE . "'>\n";
					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th colspan=4>\n";
					$ret .= "				<font class='" . $teamColor . "'>" . $row['BADGE_NAME'] . "</font>\n";
					$ret .= " 		</th>\n";
					$ret .= "			<td id='x_" . $ID_BADGE . "' name='x_" . $ID_BADGE . "' onclick=\"closeDiv('div_" . $ID_BADGE . "')\" TITLE='Remove Table'>X</td>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Name</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Team Current</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Time</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Type</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 	</tr>\n";

					$sql2 = "select ";
					//$sql2 .= "	CONVERT(varchar(8), DATE_ATTIN, 112) as DATE_ATTIN3, ";
					$sql2 .= "	CONVERT(varchar(8), DATE_TRX_PRIOR, 112) as DATE_TRX_PRIOR3, ";
					$sql2 .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
					$sql2 .= "	* ";
					$sql2 .= " from ";
					$sql2 .= "	nsa.DCEMMS_EMP ";
					$sql2 .= " where ";
					$sql2 .= " 	TYPE_BADGE = 'E' ";
					$sql2 .= " 	and ";
					$sql2 .= " 	STAT_BADGE = 'A' ";
					$sql2 .= " 	and ";
					$sql2 .= "	ID_BADGE_TEAM_STD = '" . $row['ID_BADGE'] . "'";
					QueryDatabase($sql2, $results2);

					while ($row2 = mssql_fetch_assoc($results2)) {
						$memberColor = GetColorBadgeFlag($row2['FLAG_ATTEND']);
						$trxColor = GetColorCodeTrxFONT($row2['CODE_TRX_PRIOR']);
						$currPR_R = '';

						$currPR = $row2['DATE_TRX_PRIOR3'] . " " . str_pad($row2['TIME_TRX_PRIOR'],6,"0",STR_PAD_LEFT);
						if (!is_null($row2['DATE_TRX_PRIOR3'])) {
							$currtsPR = strtotime($currPR);
							$currPR_R = date('m/d/Y h:i:s A',$currtsPR);
						}

						$ret .= " 	<tr>\n";
						$ret .= " 		<td>\n";
						$ret .= "				<font class='" . $memberColor . "'>" . $row2['BADGE_NAME'] . "</font>\n";
						$ret .= " 		</td>\n";
						$ret .= " 		<td>\n";
						$ret .= "				<font>" . $row2['ID_BADGE_TEAM_CRNT'] . "</font>\n";
						$ret .= " 		</td>\n";
						$ret .= " 		<td>\n";
						$ret .= "				<font class='" . $trxColor . "'>" . $currPR_R . "</font>\n";
						$ret .= " 		</td>\n";
						$ret .= " 		<td>\n";
						$ret .= "				<font class='" . $trxColor . "'>" . GetStrCodeTrx($row2['CODE_TRX_PRIOR']) . "</font>\n";
						$ret .= " 		</td>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<td></td>\n";
						$ret .= " 		<td></td>\n";
						$ret .= " 		<th>Timestamp</th>\n";
						$ret .= " 		<th>Trans</th>\n";
						$ret .= " 	</tr>\n";





						$sql3  = " select ";
						$sql3 .= "	CONVERT(varchar(8), DATE_TRX, 112) as DATE_TRX3, ";
						$sql3 .= "	code_trx, ";
						$sql3 .= "	* ";
						$sql3 .= " from ";
						$sql3 .= "	nsa.DCUTRX_ZERO_PERM ";
						$sql3 .= " where ";
						$sql3 .= "	ID_BADGE = '" . $row2['ID_BADGE'] . "' ";
						$sql3 .= "	and ";
						$sql3 .= "	DATE_TRX >= '" . $DateFrom . "' ";
						$sql3 .= "	and ";
						$sql3 .= "	DATE_TRX <= '" . $DateTo . "' ";
						$sql3 .= "	and ";
						$sql3 .= "	FLAG_DEL = '' ";

						//$sql3 .= "	and ";
						//$sql3 .= "	CODE_TRX in (100,101)";
						//$sql3 .= "	CODE_TRX  not in (104,105)";
						$sql3 .= " order by ";
						$sql3 .= "	DATE_TRX asc, ";
						$sql3 .= "	TIME_TRX asc ";
						QueryDatabase($sql3, $results3);
						while ($row3 = mssql_fetch_assoc($results3)) {
							$currTX_R = '';
							$currTX = $row3['DATE_TRX3'] . " " . str_pad($row3['TIME_TRX'],6,"0",STR_PAD_LEFT);
							if (!is_null($row3['DATE_TRX3'])) {
								$currtsTX = strtotime($currTX);
								$currTX_R = date('m/d/Y h:i:s A',$currtsTX);
							}
							$ret .= " 	<tr>\n";
							$ret .= " 		<td></td>\n";
							$ret .= " 		<td></td>\n";
							$ret .= " 		<td>\n";
							$ret .= "				<font>" . $currTX_R . "</font>\n";
							$ret .= " 		</td>\n";
							$ret .= " 		<td>\n";
							$ret .= "				<font>" . GetStrCodeTrx($row3['CODE_TRX']) . "</font>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
						}
					}

					$ret .= " 	</tr>\n";
					$ret .= " </table>\n";
					$ret .= " 	</br>\n";
					$ret .= " </div>\n";
				}
				echo json_encode(array("returnValue"=> $ret));

			} else {
				echo json_encode(array("returnValue"=> '<h1>Invalid Permissions</h1>'));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
