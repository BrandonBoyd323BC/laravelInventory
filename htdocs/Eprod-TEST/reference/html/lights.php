<?php

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	$DEBUG = 1;	

	PrintHeaderJQ('Lights','default.css','lights.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$Location = '';
			$TeamBadge = '';
			$ShopOrd = '';
			$Suffix = '';
			$ItemNum = '';
			
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='selTeam'>Team: </LABEL>\n");
			print("				<select name='selTeam' id='selTeam' onkeypress='searchKeyPress(event);'>\n");

/*
			$sql  = "SELECT ";
			$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
			$sql .= " ltrim(ID_BADGE) as ID_BADGE, ";
			$sql .= " NAME_EMP ";
			$sql .= " FROM nsa.DCEMMS_EMP ";
			$sql .= " WHERE TYPE_BADGE = 'X' ";
			$sql .= " and CODE_ACTV = '0' ";
			$sql .= " and ID_BADGE < 900 ";
			$sql .= " ORDER BY BADGE_NAME asc";
*/

			$sql  = "SELECT ";
			$sql .= " ltrim(ID_BADGE) as ID_BADGE, ";
			$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
			$sql .= " NAME_EMP ";
			$sql .= " FROM nsa.DCEMMS_EMP ";
			$sql .= " WHERE TYPE_BADGE = 'X' ";
			$sql .= " and CODE_ACTV = '0' ";
			$sql .= " and ID_BADGE < 900 ";
			$sql .= " union ";
			$sql .= " SELECT ";
			$sql .= " distinct m.ID_BADGE_TEAM as ID_BADGE, ";
			$sql .= " CASE isnull(e.NAME_EMP,'') ";
			$sql .= " WHEN '' THEN rtrim(ltrim(m.ID_BADGE_TEAM)) ";
			$sql .= " ELSE ltrim(m.ID_BADGE_TEAM) + ' - ' + isnull(e.NAME_EMP,'') ";
			$sql .= " END as BADGE_NAME, ";
			$sql .= " isnull(e.NAME_EMP,m.ID_BADGE_TEAM) as NAME_EMP ";
			$sql .= " FROM nsa.MAINT_MACHINERY m ";
			$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
			$sql .= " on ltrim(m.ID_BADGE_TEAM) = ltrim(e.ID_BADGE) ";
			$sql .= " and e.CODE_ACTV = 0 ";
			$sql .= " and e.TYPE_BADGE = 'X' ";
			$sql .= " WHERE m.STATUS = 'A' ";
			$sql .= " order by ID_BADGE asc ";
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
			print("<div id='lightsDiv' name='lightsDiv'></div>\n");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
