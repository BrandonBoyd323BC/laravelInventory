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

			//if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["sendValue"]))  {
			if (isset($_POST["sendValue"]))  {

				//$today = date('m-d-Y');
				$today = date('Y-m-d');
				$DateFrom = $today;
				$DateTo = $today;


				//$DateFrom = $_POST["df"];
				//$DateTo = $_POST["dt"];

				$Team = $_POST["sendValue"];

				$ovral_eff = GetEffScore($DateFrom, $DateTo, $Team);
				$pctClass = GetColorPctAuto($ovral_eff);
				$ret = "	<h1><font class='" . $pctClass . "'>" . $ovral_eff . "</font></h1>\n";
				//$ret = "	<h1><font class='darkred'>Under Construction</font></h1>\n";

				//$sql  = " select ";
				//$sql .= "  NAME_EMP ";
				//$sql .= " from ";
				//$sql .= "  nsa.DCEMMS_EMP ";
				//$sql .= " where ";
				//$sql .= "  TYPE_BADGE = 'E' ";
				//$sql .= "  and ";
				//$sql .= "  STAT_BADGE = 'A' ";
				//$sql .= "  and ";
				//$sql .= "  ltrim(ID_BADGE_TEAM_CRNT) = '" . $Team . "' ";
				//$sql .= "  and ltrim(ID_BADGE) not in ('TCM','GLOVE') ";
				//QueryDatabase($sql, $results);

				//while ($row = mssql_fetch_assoc($results)) {
					//$ret .= "	<font class='green'>" . $row['NAME_EMP'] . "</font></br>\n";
				//}
				//$ret .= "</br>\n";

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
