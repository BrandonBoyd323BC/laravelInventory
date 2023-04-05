<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Badge Status','default.css','badgestatus.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			print("		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n");

			$sql  = " SELECT count(*) as count_in ";
			$sql .= " FROM nsa.DCEMMS_EMP ";
			$sql .= " WHERE TYPE_BADGE = 'E' ";
			$sql .= " and STAT_BADGE in ('A','I') ";
			$sql .= " and CODE_ACTV = 0 ";
			$sql .= " and FLAG_ATTEND = 1 ";
			$sql .= " and ltrim(isnull(ID_BADGE_TEAM_CRNT ,''))<> ''";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("		<h5>Total Attended In: " . $row['count_in'] ."</h5>\n");
			}

			$sql  = " SELECT ";
			$sql .= " ltrim(e1.ID_BADGE) + ' - ' + e1.NAME_EMP as BADGE_NAME, ";
			$sql .= " e1.ID_BADGE, ";
			$sql .= " e1.NAME_EMP, ";
			$sql .= " e1.KEY_HOME_3RD as ID_WC, ";
			$sql .= " e2.ID_USER as SUPRVSR_NAME, ";
			$sql .= " e1.CODE_SHIFT, ";
			$sql .= " isnull(e1.CODE_USER,'') as CODE_USER, ";
			$sql .= " e1.FLAG_ATTEND, ";
			$sql .= " CASE ";
			$sql .= "  WHEN (isnull(ltrim(ID_EMP),'0')) = '' THEN '0' ";
			$sql .= "  ELSE (isnull(ltrim(ID_EMP),'0')) ";
			$sql .= " END as MAX_CREW ";
			$sql .= " FROM nsa.DCEMMS_EMP e1 ";
			$sql .= " LEFT join  nsa.DCWEB_AUTH e2 ";
			$sql .= " on e1.ID_BADGE_SUPRVSR = e2.ID_BADGE ";
			$sql .= " WHERE e1.TYPE_BADGE = 'X' ";
			$sql .= " and e1.CODE_ACTV = '0' ";
			$sql .= " and e1.ID_BADGE_SUPRVSR <> '' ";
			$sql .= " ORDER BY BADGE_NAME asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {

				//////////////
				//TEAM
				//////////////
				$ID_BADGE = trim($row['ID_BADGE']);
				$ID_WC = trim($row['ID_WC']);
				$teamColor = GetColorBadgeFlag($row['FLAG_ATTEND']);

				print("<div id='div_" . $ID_BADGE . "' name='div_" . $ID_BADGE . "'>\n");
				print(" <table class='sample'>\n");
				print(" 	<tr>\n");
				print(" 		<th colspan=5>\n");
				print("				<font>Team Name</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Spvsr</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Job Class</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Shift Code</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Crew Size</font>\n");
				print(" 		</th>\n");
				print("			<td id='x_" . $ID_BADGE . "' name='x_" . $ID_BADGE . "' onclick=\"closeDiv('div_" . $ID_BADGE . "')\" TITLE='Remove Table'>X</td>\n");				
				print(" 	</tr>\n");				
				print(" 	<tr>\n");
				print(" 		<th colspan=5>\n");
				print("				<font class='" . $teamColor . "'>" . $row['BADGE_NAME'] . " - " . $ID_WC . "</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font class='" . $teamColor . "'>" . $row['SUPRVSR_NAME'] . "</font>\n");
				print(" 		</th>\n");
				if ($UserRow['PERM_MGMT']) {
					print(" 		<th id='jobClass__".$ID_BADGE."' onDblClick=\"showJobClassEdit(this.id,".$ID_BADGE.",'".$row['CODE_USER']."')\">\n");
					print("				<font class='" . $teamColor . "'>" . $row['CODE_USER'] . "</font>\n");
					print(" 		</th>\n");
				} else {
					print(" 		<th>\n");
					print("				<font class='" . $teamColor . "'>" . $row['CODE_USER'] . "</font>\n");
					print(" 		</th>\n");
				}
				print(" 		<th>\n");
				print("				<font class='" . $teamColor . "'>" . $row['CODE_SHIFT'] . "</font>\n");
				print(" 		</th>\n");
				if ($UserRow['PERM_MGMT']) {
					print(" 		<th id='crewSize__".$ID_BADGE."' onDblClick=\"showCrewSizeEdit(this.id,".$ID_BADGE.",".$row['MAX_CREW'].")\">\n");
					print("				<font class='" . $teamColor . "'>" . $row['MAX_CREW'] . "</font>\n");
					print(" 		</th>\n");
				} else {
					print(" 		<th>\n");
					print("				<font class='" . $teamColor . "'>" . $row['MAX_CREW'] . "</font>\n");
					print(" 		</th>\n");
				}

				print(" 	</tr>\n");
				print(" 	<tr>\n");
				print(" 		<th>\n");
				print("				<font>Name</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Stat</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Shift</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Adj %</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Team Current</font>\n");
				print(" 		</th>\n");
				print(" 		<th colspan=2>\n");
				print("				<font>Time</font>\n");
				print(" 		</th>\n");
				print(" 		<th colspan=2>\n");
				print("				<font>Type</font>\n");
				print(" 		</th>\n");
				print(" 	</tr>\n");

				$sql2  = " SELECT ";
				$sql2 .= " CONVERT(varchar(8), DATE_TRX_PRIOR, 112) as DATE_TRX_PRIOR3, ";
				$sql2 .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
				$sql2 .= " ID_BADGE_TEAM_CRNT, ";
				$sql2 .= " CODE_SHIFT, ";
				$sql2 .= " STAT_BADGE, ";
				$sql2 .= " CODE_USER_1_DC, ";
				$sql2 .= " CODE_TRX_PRIOR, ";
				$sql2 .= " TIME_TRX_PRIOR, ";
				$sql2 .= " FLAG_ATTEND, ";
				$sql2 .= " rowid ";
				$sql2 .= " FROM nsa.DCEMMS_EMP ";
				$sql2 .= " WHERE TYPE_BADGE = 'E' ";
				$sql2 .= " and STAT_BADGE in ('A','I') ";
				$sql2 .= " and ID_BADGE_TEAM_STD = '" . $row['ID_BADGE'] . "'";
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
					print(" 	<tr id='row_" . $row2['rowid'] . "' >\n");
					print(" 		<td>\n");
					print("				<font class='" . $memberColor . "'>" . $row2['BADGE_NAME'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font class='" . $memberColor . "'>" . $row2['STAT_BADGE'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font class='" . $memberColor . "'>" . $row2['CODE_SHIFT'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font class='" . $memberColor . "'>" . $row2['CODE_USER_1_DC'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font>" . $row2['ID_BADGE_TEAM_CRNT'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td colspan=2>\n");
					print("				<font class='" . $trxColor . "'>" . $currPR_R . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td colspan=2>\n");
					print("				<font class='" . $trxColor . "'>" . GetStrCodeTrx($row2['CODE_TRX_PRIOR']) . "</font>\n");
					print(" 		</td>\n");
					print(" 	</tr>\n");
				}
				print(" 	</tr>\n");
				print(" </table>\n");
				print(" 	</br>\n");
				print(" </div>\n");
			}
			print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup2'></div>\n");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

	if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["team"]))  {
		PrintFooter("activity.php");
	} else {
		PrintFooter("emenu.php");
	}

?>
