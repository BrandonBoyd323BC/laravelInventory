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
			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["zeroHour"]) && isset($_POST["team"]))  {

				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
				$ZeroHour = $_POST["zeroHour"];
				$Team = $_POST["team"];
				$DateFromTS = strtotime($DateFrom." ".$ZeroHour);
				$DateToTS = strtotime($DateTo." ".$ZeroHour);
				$seconds_diff = $DateToTS - $DateFromTS;
				$daysDiff = ($seconds_diff/3600)/24;				

				//NOT EVERY DAY HAS 86400 SECONDS.. THIS ACCOUNTS FOR DAYLIGHT SAVINGS
				if (($seconds_diff == 86400) || ($seconds_diff == 90000) || ($seconds_diff == 82800)){
					$daysDiff = 1;
				}

				$sql  = "SELECT ";
				$sql .= " ltrim(e1.ID_BADGE) + ' - ' + e1.NAME_EMP as BADGE_NAME, ";
				$sql .= " ltrim(e1.ID_BADGE) as ID_BADGE, ";
				$sql .= " e1.NAME_EMP, ";
				$sql .= " e1.ID_BADGE_SUPRVSR, ";
				$sql .= " e2.ID_USER as SUPRVSR_NAME ";
				$sql .= " FROM nsa.DCEMMS_EMP e1, ";
				$sql .= " nsa.DCWEB_AUTH e2 ";
				$sql .= " WHERE e1.TYPE_BADGE = 'X' ";
				$sql .= " and e1.CODE_ACTV = '0' ";
				$sql .= " and e1.ID_BADGE_SUPRVSR = e2.ID_BADGE ";
				$sql .= " and ltrim(e1.ID_BADGE) = '" . $Team . "' ";
				$sql .= " ORDER BY BADGE_NAME asc ";
				QueryDatabase($sql, $results);
				$num_teams = GetNumTeams();
				error_log("numTeams " . $num_teams);

				while ($row = mssql_fetch_assoc($results)) {
					$ovral_eff = '';
					$raw_ovral_eff = '';
					//if ($DateFrom == $DateTo) {
					if ($daysDiff == 1) {
						$resApp = checkLatestApproval("'200','201'", $DateFrom, $row['ID_BADGE']);
						if (mssql_num_rows($resApp) > 0) {
							while ($row2 = mssql_fetch_assoc($resApp)) {
								$ovral_eff = round(($row2['EARNED_MINS'] / $row2['ACTUAL_MINS']) * 100,2);
								$raw_ovral_eff = round(($row2['EARNED_MINS'] / $row2['AVAIL_MINS']) * 100,2);
								error_log($row['ID_BADGE'] . " skipped " . $ovral_eff);
							}
						}
					}
					if ($ovral_eff == '') {
						$ovral_eff = GetEffScore($DateFrom, $DateTo, $ZeroHour, $row['ID_BADGE']);
						error_log($row['ID_BADGE'] . " calculated " . $ovral_eff);
					}
					$pctClass = GetColorPct($ovral_eff);
					$rawPctClass = GetColorPct($raw_ovral_eff);
					//$ret .= "						<input type='hidden' id='i_" . $row['ID_BADGE'] . "' name='i_" . $row['ID_BADGE'] . "' value='" . $row['ID_BADGE'] . "'>\n";
					$ret .= "						<td class='sample' id='td_" . $row['ID_BADGE'] . "' name='td_" . $row['ID_BADGE'] . "'>". $row['BADGE_NAME'] . "</td>\n";
					$ret .= "						<td class='sample'>". $row['SUPRVSR_NAME'] . "</td>\n";
					$ret .= "						<td class='sample'><div id='div_" . $row['ID_BADGE'] . "' name='div_" . $row['ID_BADGE'] . "'><font class='" . $pctClass . "' onclick=\"goToActivityPopUp('" . $row['ID_BADGE'] . "')\">" . $ovral_eff . "</font></div></td>\n";
					$ret .= "						<td class='sample'><div id='div_raw_" . $row['ID_BADGE'] . "' name='div_raw_" . $row['ID_BADGE'] . "'><font class='" . $rawPctClass . "' onclick=\"goToActivityPopUp('" . $row['ID_BADGE'] . "')\">" . $ovral_eff . "</font></div></td>\n";
					//if ($DateFrom == $DateTo) {
					if ($daysDiff == 1) {
						$resApp = checkLatestApproval("'200','201'", $DateFrom, $row['ID_BADGE']);
						if (mssql_num_rows($resApp) > 0) {
							////////////////
							//HAS BEEN APPROVED BY SUPERVISOR
							////////////////
							while ($row2 = mssql_fetch_assoc($resApp)) {
								if ($row2['CODE_APP'] == '200') {
									$fclass = 'darkblue';
								}
								if ($row2['CODE_APP'] == '201') {
									$fclass = 'brown';
								}
								$ret .= "						<td id='AppBy_" . $row['ID_BADGE'] . "'><font class='" . $fclass . "'>" . $row2['APP_BY_ID_USER'] . "</font></td>\n";
								$ret .= "						<td id='AppOn_" . $row['ID_BADGE'] . "'><font class='" . $fclass . "'>" . $row2['DATE_ADD_SHORT'] . "</font></td>\n";
								$ret .= "						<td id='AppCmts_" . $row['ID_BADGE'] . "'><font class='" . $fclass . "'>" . $row2['COMMENTS'] . "</font></td>\n";
							}
						}
					}
				}
				////////////////
				//GVD USED FOR DEBUGGING
				////////////////
				//$ret = "";
				//$ret .= "						<td class='sample' id='td_XX' name='td_XX'>XX</td>\n";
				//$ret .= "						<td class='sample'>XX</td>\n";
				//$ret .= "						<td class='sample'><div id='div_XX' name='div_XX'><font onclick=\"goToActivityPopUp('XX')\">XX</font></div></td>\n";

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
