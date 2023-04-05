<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ('Realtime Efficiency','default.css','realtime.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$ZeroHour = '013000';
			//$today = date('m-d-Y');
			$today = date('Y-m-d');
 			$tomorrow = date("Y-m-d", strtotime("+1 day", strtotime($today)));
 			$yesterday = date("Y-m-d", strtotime("-1 day", strtotime($today)));
			$time = date("His",time());

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


			

			print("		<input type='hidden' id='df' name='df' value='" . $DateFrom . "'>\n");
			print("		<input type='hidden' id='dt' name='dt' value='" . $DateTo . "'>\n");
			print("		<input type='hidden' id='zh' name='zh value='" . $ZeroHour . "'>\n");

			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='selTeam'>Team: </LABEL>\n");
			print("				<select name='selTeam' id='selTeam' onkeypress='searchKeyPress(event);'>\n");

			$sql  = "SELECT ";
			$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
			$sql .= " ltrim(ID_BADGE) as ID_BADGE, ";
			$sql .= " NAME_EMP ";
			$sql .= " FROM nsa.DCEMMS_EMP ";
			$sql .= " WHERE TYPE_BADGE = 'X' ";
			$sql .= " and CODE_ACTV = '0' ";
			$sql .= " ORDER BY BADGE_NAME asc";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['ID_BADGE'] . "'>" . $row['BADGE_NAME'] . "</option>\n");
			}

			print("				</select>\n");
			print(" 			<button id='submit' name='submit' value='Submit' onclick='subValue(document.getElementById(\"selTeam\").value)'>Go</button>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print("<body onLoad='doOnLoads()'>");
			print("<div id='scoreDiv' name='scoreDiv'></div>\n");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
