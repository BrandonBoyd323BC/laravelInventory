<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	//ONLY RUN IF AFTER 6:00 AM
	if (date('H') >= 6) {	
		//PrintHeaderJQ('Realtime Efficiency','default.css','realtimeauto.js');
		$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Connect To $DBServer!\n";
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				print "		<p class='warning'>Could Not Select $db!\n";
			} else {
				if (isset($_GET["teamList"]))  {
					$Flag_ON = False;
					$teamList = htmlspecialchars($_GET["teamList"]);

					$sql = "SELECT p.EFF_PCT, p.ID_BADGE, e.NAME_EMP, e.FLAG_ATTEND ";
					$sql .= "FROM nsa.DC_RT_EFF p ";
					$sql .= "LEFT JOIN nsa.DCEMMS_EMP e ";
					$sql .= "on p.ID_BADGE = e.ID_BADGE and e.CODE_ACTV = 0 ";
					$sql .= "WHERE ltrim(p.ID_BADGE) in ";
					$sql .= "(". $teamList .") ";
					$sql .= "AND e.FLAG_ATTEND = 1 ";
					$sql .= "ORDER BY p.ID_BADGE asc ";
					QueryDatabase($sql, $results);
					$TeamCount = mssql_num_rows($results);

					if ($TeamCount == 1) {
						$FontSize = '500px';
					} else {
						$FontSize = '300px';
					}

					while ($row = mssql_fetch_assoc($results)) {
						if ($row['FLAG_ATTEND']) {
							$Flag_ON = True;
							print("<br>");
							print("<div style='text-align: center;'><span style='font-size:36px;'>". ltrim($row['ID_BADGE']) ." ".$row['NAME_EMP']."</span></div>");
							print("<br>");
							$pctClass = GetColorPct($row['EFF_PCT']);
							print("<div style='text-align: center;'><font color='" . $pctClass . "' style='font-size:".$FontSize.";'>" . $row['EFF_PCT'] . "</font></div>");
						}
					}

					//BLACK SCREEN TO SAVE DISPLAY IF NO TEAMS ARE LOGGED ON
					if (!$Flag_ON) {
						print("<html><body bgcolor='black'></body></html>");
					}
				}
			}
			$retval = DisconnectFromDatabaseServer($db);
			if ($retval == 0) {
				print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
			}
		}
	} else {
		//BLACK SCREEN TO SAVE DISPLAY
		print("<html><body bgcolor='black'></body></html>");
	}
?>
