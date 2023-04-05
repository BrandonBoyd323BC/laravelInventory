<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ('Realtime Efficiency','default.css','realtimeauto.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			if (isset($_GET["team"]))  {
				$teamNum = htmlspecialchars($_GET["team"]);

				//$today = date('m-d-Y');
				//$DateFrom = $today;
				//$DateTo = $today;

				//print("		<input type='hidden' id='df' name='df' value='" . $DateFrom . "'>\n");
				//print("		<input type='hidden' id='dt' name='dt' value='" . $DateTo . "'>\n");
				print("<head>");
				print("<meta http-equiv='refresh' content='60'>");//auto refresh page every content='' seconds
				print("<meta name='viewport' content='initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,width=device-width,user-scalable=yes' />");
				print("<meta name='mobile-web-app-capable' content='yes'>");
				print("</head>");

				$sql = "select p.EFF_PCT, p.ID_BADGE, e.NAME_EMP ";
				$sql .= "from nsa.DC_RT_EFF p ";
				$sql .= "left join nsa.DCEMMS_EMP e ";
				$sql .= "on p.ID_BADGE = e.ID_BADGE and e.CODE_ACTV = 0 ";
				$sql .= "where ltrim(p.ID_BADGE) = ";
				$sql .= "". $teamNum ." ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					print("<span style='font-size:24px'>". htmlspecialchars($_GET["team"]) ."    </span>");
					print("<span style='font-size:24px'> ".$row['NAME_EMP']." </span>");
					print("<br>");
					$pctClass = GetColorPctAuto($row['EFF_PCT']);
					print("	<h1><font class='" . $pctClass . "'>" . $row['EFF_PCT'] . "</font></h1>");

				}

				print("<body onLoad='doOnLoads()'>");

				/*****
				print(" <table>\n");
				print(" 	<tr>\n");
				print(" 		<td colspan='2'>\n");
				print(" 			<LABEL for='selTeam'>Team: </LABEL>\n");
				print("				<select name='selTeam' id='selTeam' onkeypress='searchKeyPress(event);'>\n");
				*****/

				/*
				$sql =  "select ";
				$sql .= "  ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
				$sql .= "  ltrim(ID_BADGE) as ID_BADGE,";
				$sql .= "  NAME_EMP";
				$sql .= " from ";
				$sql .= "  nsa.DCEMMS_EMP ";
				$sql .= " where ";
				$sql .= "  TYPE_BADGE = 'X'";
				$sql .= "  and";
				$sql .= "  CODE_ACTV = '0'";
				$sql .= " order by BADGE_NAME asc";
				QueryDatabase($sql, $results);
				*/

				/*****
				while ($row = mssql_fetch_assoc($results)) {
					print("					<option value='" . $row['ID_BADGE'] . "'>" . $row['BADGE_NAME'] . "</option>\n");
				}

				print("				</select>\n");
				print(" 			<button id='submit' name='submit' value='Submit' onclick='subValue(document.getElementById(\"selTeam\").value)'>Go</button>\n");
				print(" 		</td>\n");
				print(" 	</tr>\n");
				print(" </table>\n");
				******/
			}

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
