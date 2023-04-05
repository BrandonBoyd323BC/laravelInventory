<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Machine Inventory','default.css','machinv.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			print("	<body onload=\"showStatusChange()\">");
			print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");

			$sqlS  = "SELECT ";
			$sqlS .= " ms.* ";
			$sqlS .= "from nsa.MAINT_STATUS ms ";
			$sqlS .= "ORDER BY CODE_STATUS asc ";
			QueryDatabase($sqlS, $resultsS);
			while ($rowS = mssql_fetch_assoc($resultsS)) {
				$a_maint_stats[$rowS['CODE_STATUS']] = ltrim($rowS['DESCR']);
			}

			print(" </br>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Machinery: </th>\n");
			print(" 	</tr>\n");

			$building = findBuildingByIP($_SERVER['REMOTE_ADDR']);

			$SELECTED_CHICAGO = "";
			$SELECTED_CLEVELAND = "";
			$SELECTED_ARKANSAS = "";
			$SELECTED_KANSAS = "";
			$SELECTED_BELLEVILLE = "";

			switch ($building) {
				case "Chicago":
					$SELECTED_CHICAGO = "SELECTED";
				break;
				case "HQ":
					$SELECTED_CLEVELAND = "SELECTED";
				break;
				case "School":
					$SELECTED_CLEVELAND = "SELECTED";
				break;
				case "FC":
					$SELECTED_CLEVELAND = "SELECTED";
				break;
				case "Arkansas":
					$SELECTED_ARKANSAS = "SELECTED";
				break;
				case "Kansas":
					$SELECTED_KANSAS = "SELECTED";
				break;
				case "Belleville":
					$SELECTED_BELLEVILLE = "SELECTED";
				break;
			}


			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='show_location'>Location: </LABEL>\n");
			print(" 		</td>\n");
			print(" 		<td>\n");
			print("				<select id='show_location' onChange=\"showLocationChange()\">\n");
			print("					<option value='Cleveland' ".$SELECTED_CLEVELAND.">Cleveland</option>\n");
			print("					<option value='Chicago' ".$SELECTED_CHICAGO.">Chicago</option>\n");
			print("					<option value='Arkansas'".$SELECTED_ARKANSAS.">Arkansas</option>\n");
			print("					<option value='Kansas'".$SELECTED_KANSAS.">Kansas</option>\n");
			print("					<option value='Belleville'".$SELECTED_BELLEVILLE.">Belleville</option>\n");
			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Show Status: </td>\n");
			print(" 		<td>\n");
			print(" 			<select id='show_status' onChange=\"showStatusChange()\">\n");
			print("					<option value='ALL'>-- ALL --</option>\n");
			foreach ($a_maint_stats as $code_status => $descr) {
				$SELECTED = '';
				if($code_status == 'A'){
					$SELECTED = 'SELECTED';
				}
				print("					<option value='" . $code_status . "' " . $SELECTED . ">" . $descr . "</option>\n");
			}
			print(" 			</select>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			
			print(" 	<tr>\n");
			print(" 		<td>Filter Team: </td>\n");
			
			print(" 		<td id='td_filterTeam'>\n");
			print("				<select id='filterTeam' onChange=\"showStatusChange()\">");
			print("					<option value='ALL'>-- ALL --</option>\n");
			$sqlT = "SELECT DISTINCT ltrim(mm.ID_BADGE_TEAM) as ID_BADGE_TEAM, de.NAME_EMP,";
			$sqlT .= " CASE ";
			$sqlT .= " 		WHEN de.NAME_EMP is null THEN ID_BADGE_TEAM";
			$sqlT .= " 		else (ltrim(mm.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP)";
			$sqlT .= " END as BADGETEAM";
			$sqlT .= " FROM nsa.MAINT_MACHINERY mm";
			$sqlT .= " left join nsa.DCEMMS_EMP de";
			$sqlT .= " on ltrim(mm.ID_BADGE_TEAM) = ltrim(de.ID_BADGE)";
			$sqlT .= " ORDER BY ID_BADGE_TEAM";
			QueryDatabase($sqlT, $resultsT);
			while ($rowT = mssql_fetch_assoc($resultsT)) {
				print("					<option value='" . $rowT['ID_BADGE_TEAM'] . "' " . $SELECTED . ">" . $rowT['BADGETEAM'] . "</option>\n");
			}
			print(" 			</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <input type=hidden id='sortDirFlag' value='0'>\n");
			print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('emenu.php');
?>
