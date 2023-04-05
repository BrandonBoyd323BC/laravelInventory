<?php

	$DEBUG = 1;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

/*
			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["sendValue"]))  {

				//$today = date('m-d-Y');
				$today = date('Y-m-d');
				//$DateFrom = $today;
				//$DateTo = $today;
				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
				$ZeroHour = '013000';
				$RealTime = "T";


				//$DateFrom = $_POST["df"];
				//$DateTo = $_POST["dt"];
*/

			if (isset($_POST["sendValue"]))  {
				$RealTime = "T";
				$ZeroHour = '013000';
				//$today = date('m-d-Y');
				$today = date('Y-m-d');
	 			$tomorrow = date("Y-m-d", strtotime("+1 day", strtotime($today)));
	 			$yesterday = date("Y-m-d", strtotime("-1 day", strtotime($today)));
				$time = date("His",time());
				$Team = $_POST["sendValue"];

				if ($DEBUG > 1)	{
					error_log("today: ".$today);
					error_log("tomorrow: ".$tomorrow);
					error_log("yesterday: ".$yesterday);
					error_log("Time: ".$time);
				}
				
				if ($time > $ZeroHour) {
					$DateFrom = $today;
					$DateTo = $tomorrow;
				} else {
					$DateFrom = $yesterday;
					$DateTo = $today;
				}

				$ovral_eff = GetEffScore($DateFrom, $DateTo, $ZeroHour, $Team, $RealTime);
				$pctClass = GetColorPct($ovral_eff);
				$ret = "	<h1><font class='" . $pctClass . "'>" . $ovral_eff . "</font></h1>\n";
				//$ret = "	<h1><font class='darkred'>Under Construction</font></h1>\n";

				$sql  = "SELECT ";
				$sql .= " NAME_EMP ";
				$sql .= " FROM nsa.DCEMMS_EMP ";
				$sql .= " WHERE TYPE_BADGE = 'E' ";
				$sql .= " and STAT_BADGE = 'A' ";
				$sql .= " and ltrim(ID_BADGE_TEAM_CRNT) = '" . $Team . "' ";
				$sql .= " and ltrim(ID_BADGE) not in ('TCM','GLOVE') ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$ret .= "	<font class='green'>" . $row['NAME_EMP'] . "</font></br>\n";
				}
				$ret .= "</br>\n";

				////////////
				//HISTORY
				////////////
				$sql  = "SELECT top 7 ";
				$sql .= " CONVERT(varchar(15), DATE_APP, 107) as DATE_APP3, ";
				$sql .= " DATE_APP, ";
				$sql .= " ACTUAL_MINS, ";
				$sql .= " EARNED_MINS, ";
				$sql .= " AVAIL_MINS ";
				$sql .= " FROM nsa.DCAPPROVALS ";
				$sql .= " WHERE ltrim(BADGE_APP) = '" . $Team . "' ";
				$sql .= " and CODE_APP = '200' ";
				$sql .= " and DATE_APP in (SELECT top 14 DATE_APP FROM nsa.DCAPPROVALS a2 WHERE a2.CODE_APP = '300' ORDER BY DATE_APP desc) ";
				$sql .= " ORDER BY ";
				$sql .= " DATE_APP desc ";
				QueryDatabase($sql, $results);

				$ret .= "	<table class='sample'>\n";
				$ret .= "		<th>Date</th>\n";
				$ret .= "		<th>Adj %</th>\n";
				$ret .= "		<th>Raw %</th>\n";

				while ($row = mssql_fetch_assoc($results)) {
					$pct = round((($row['EARNED_MINS'] / $row['ACTUAL_MINS']) * 100),2);
					$pctClass = GetColorPct($pct);

					$pctRaw = round((($row['EARNED_MINS'] / $row['AVAIL_MINS']) * 100),2);
					$pctRawClass = GetColorPct($pctRaw);

					$ret .= "		<tr class='sample'>\n";
					$ret .= "			<td><font>" . $row['DATE_APP3'] . "</font></td>\n";
					$ret .= "			<td><font class='" . $pctClass . "'>" . $pct . "</font></td>\n";
					$ret .= "			<td><font class='" . $pctRawClass . "'>" . $pctRaw . "</font></td>\n";
					$ret .= "		</tr>\n";
				}
				$ret .= "	</table>\n";

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
