<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");
	require_once('classes/tc_calendar.php');

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	PrintHeaderJQ('Maintenance Log','default.css','maintlog.js');
	$DEBUG = 1;
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$passedIdBadgeTeam = "";
			$passedMachID = "";
			$MaintReqRowid = "";
			
			if (isset($_GET['MaintReqRowid'])) {
				$MaintReqRowid = stripNonNumericChars($_GET['MaintReqRowid']);

				$sql0  = "SELECT * FROM nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " mr ";
				$sql0 .= " WHERE rowid = ".$MaintReqRowid;
				QueryDatabase($sql0, $results0);

				while ($row0 = mssql_fetch_assoc($results0)) {
					$passedIdBadgeTeam = trim($row0['ID_BADGE_TEAM']);
					$passedMachID = trim($row0['ID_MACH']);
					error_log("passedIdBadgeTeam: " . $passedIdBadgeTeam);
					error_log("passedMachID: " . $passedMachID);
				}
			}

			print("<body onLoad='doOnLoads()'>");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Enter New Records: </th>\n");
			print(" 	</tr>\n");			
			//showLocationDropdown(findBuildingByIP($_SERVER['REMOTE_ADDR']));

			$SELECTED_CHICAGO = "";
			$SELECTED_CLEVELAND = "";
			$SELECTED_ARKANSAS = "";
			$SELECTED_KANSAS = "";
			$SELECTED_BELLEVILLE = "";
			$LOCATION = "";
			$building = findBuildingByIP($_SERVER['REMOTE_ADDR']);

			switch ($building) {
				case "Chicago":
					$SELECTED_CHICAGO = "SELECTED";
					$LOCATION = "CHICAGO";
				break;
				case "HQ":
					$SELECTED_CLEVELAND = "SELECTED";
					$LOCATION = "CLEVELAND";
				break;
				case "School":
					$SELECTED_CLEVELAND = "SELECTED";
					$LOCATION = "CLEVELAND";
				break;
				case "FC":
					$SELECTED_CLEVELAND = "SELECTED";
					$LOCATION = "CLEVELAND";
				break;
				case "Arkansas":
					$SELECTED_ARKANSAS = "SELECTED";
					$LOCATION = "ARKANSAS";
				break;
				case "Kansas":
					$SELECTED_KANSAS = "SELECTED";
					$LOCATION = "KANSAS";
				break;
				case "Belleville":
					$SELECTED_BELLEVILLE = "SELECTED";
					$LOCATION = "BELLEVILLE";
				break;
			}


			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='sel_Location'>Location: </LABEL>\n");
			print(" 		</td>\n");
			print(" 		<td>\n");
			print("				<select id='sel_Location' onChange=\"showLocationChange()\">\n");
			print("					<option value='Cleveland' ".$SELECTED_CLEVELAND.">Cleveland</option>\n");
			print("					<option value='Chicago' ".$SELECTED_CHICAGO.">Chicago</option>\n");
			print("					<option value='Arkansas'".$SELECTED_ARKANSAS.">Arkansas</option>\n");
			print("					<option value='Kansas'".$SELECTED_KANSAS.">Kansas</option>\n");
			print("					<option value='Belleville'".$SELECTED_BELLEVILLE.">Belleville</option>\n");
			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='mechanic_add'>Mechanic: </LABEL>\n");
			print(" 		</td>\n");
			print(" 		<td>\n");
			print("				<select id='mechanic_add'>\n");
			print("					<option value='SELECT'>--SELECT--</option>\n");
			$sql  = "SELECT ";
			$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as ID_BADGE_NAME, ";
			$sql .= " * ";
			$sql .= " FROM nsa.DCWEB_AUTH ";
			$sql .= " WHERE EMP_ROLE like '%:MAINT:%' ";
			$sql .= " AND STATUS = 'A' ";
			$sql .= " ORDER BY ID_BADGE asc ";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				$SELECTED = '';
				print("					<option value='" . $row['ID_BADGE'] . "' " . $SELECTED . ">" . $row['ID_BADGE_NAME'] . "</option>\n");
			}
			print("					<option value='GORE1'>Gore 1</option>\n");
			print("					<option value='GORE2'>Gore 2</option>\n");
			print("					<option value='GORE3'>Gore 3</option>\n");
			print("					<option value='0000' " . $SELECTED . ">OTHER</option>\n");
			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Date Work: </td>\n");
			print(" 		<td>\n");
			$prevTS = strtotime("-1 days", time());
			$myCalendar = new tc_calendar('dw', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',time()), date('m',time()), date('Y',time()));
			$myCalendar->setPath("/");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='team'>Team: </LABEL>\n");
			print(" 		</td>\n");
			print(" 		<td id='td_team'>\n");

			///////////////////////
			//PUT ONCHANGE EVENT HERE TO LOOKUP MACHINE ID'S BELOW
			///////////////////////
			print("				<select id='team' onChange=\"showTeamChange()\">\n");

			$sql = "SELECT DISTINCT ltrim(mm.ID_BADGE_TEAM) as ID_BADGE_TEAM, de.NAME_EMP,";
			$sql .= " CASE ";
			$sql .= "  WHEN de.NAME_EMP is null THEN ID_BADGE_TEAM";
			$sql .= "  ELSE (ltrim(mm.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP)";
			$sql .= " END as BADGETEAM";
			$sql .= " FROM nsa.MAINT_MACHINERY mm";
			$sql .= " LEFT JOIN nsa.DCEMMS_EMP de";
			$sql .= " on ltrim(mm.ID_BADGE_TEAM) = ltrim(de.ID_BADGE)";
			$sql .= " WHERE mm.LOCATION = '".$LOCATION."'";
			$sql .= " ORDER BY ID_BADGE_TEAM";

			QueryDatabase($sql, $results);
			print("					<option value='      000'> -- Other -- </option>\n");
			while ($row = mssql_fetch_assoc($results)) {
				$SELECTED = '';
				if (trim($row['ID_BADGE_TEAM']) == trim($passedIdBadgeTeam)) {
					$SELECTED = 'SELECTED';
				}
				print("					<option value='" . $row['ID_BADGE_TEAM'] . "' " . $SELECTED . ">" . $row['BADGETEAM'] . "</option>\n");				
			}

			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Machine ID: </td>\n");
			print(" 		<td>\n");
			print("				<select id='machine_id'>\n");

			$sql  = "SELECT ";
			//$sql .= " ltrim(ID_MACH) + ' - ' + ID_CLUSTER + ' - ' + HEAD_BRAND + ' - ' + MODEL_NUM as MACH_DESC, ";
			$sql .= " ltrim(ID_MACH) + ' - ' + HEAD_BRAND + ' - ' + MODEL_NUM as MACH_DESC, ";
			$sql .= " ltrim(ID_MACH) as ID_MACH ";
			$sql .= " FROM nsa.MAINT_MACHINERY ";
			$sql .= " WHERE STATUS = 'A' ";
			$sql .= " ORDER BY len(ID_MACH) asc, ID_MACH asc";
			QueryDatabase($sql, $results);
			print("					<option value='SELECT'> -- Select -- </option>\n");
			while ($row = mssql_fetch_assoc($results)) {
				$SELECTED = '';
				if (trim($row['ID_MACH']) == trim($passedMachID)) {
					$SELECTED = 'SELECTED';
				}
				print("					<option value='" . $row['ID_MACH'] . "' " . $SELECTED . ">" . $row['MACH_DESC'] . "</option>\n");
			}
			print("				</select>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='maint_code'>Maintenance Code: </LABEL>\n");
			print(" 		</td>\n");
			print(" 		<td>\n");
			print("				<select id='maint_code'>\n");

			$sql  = "SELECT ";
			$sql .= " ltrim(CODE_MAINT) + ' - ' + DESCR as CODE_DESCR, ";
			$sql .= " ltrim(CODE_MAINT) as CODE_MAINT, ";
			$sql .= " ltrim(DESCR) as DESCR ";
			$sql .= " FROM nsa.MAINT_CODES ";
			$sql .= " WHERE CODE_MAINT_RES = 'M' ";
			$sql .= " ORDER BY CODE_MAINT asc";
			QueryDatabase($sql, $results);
			print("					<option value='SELECT'> -- Select -- </option>\n");
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['CODE_MAINT'] . "'>" . $row['CODE_DESCR'] . "</option>\n");
			}

			print("				</select>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='maint_code'>Maintenance Resolution Code: </LABEL>\n");
			print(" 		</td>\n");
			print(" 		<td>\n");
			print("				<select id='maint_res_code'>\n");

			$sql  = "SELECT ";
			$sql .= " ltrim(CODE_MAINT) + ' - ' + DESCR as CODE_DESCR, ";
			$sql .= " ltrim(CODE_MAINT) as CODE_MAINT, ";
			$sql .= " ltrim(DESCR) as DESCR ";
			$sql .= " FROM nsa.MAINT_CODES ";
			$sql .= " WHERE CODE_MAINT_RES = 'R' ";
			$sql .= " ORDER BY CODE_MAINT asc";
			QueryDatabase($sql, $results);
			print("					<option value='SELECT'> -- Select -- </option>\n");
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['CODE_MAINT'] . "'>" . $row['CODE_DESCR'] . "</option>\n");
			}
			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Minutes to Fix: </td>\n");
			print(" 		<td><input id='mins_down' type=text></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Comments: <input type='hidden' id='hidden_MaintReqRowid' name='hidden_MaintReqRowid' value='".$MaintReqRowid."'></td>\n");
			print(" 		<td><input id='comments' type=text><INPUT type='submit' maxlength='100' value='Add Record' onClick=\"sendAddValue()\"></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print(" </br>\n");
			print(" <table>\n");
			print(" 	<tr>\n");


			///////////////////////////////////////////////
			// FILTERS
			///////////////////////////////////////////////

			print(" 		<th colspan=2>Last <select id='num_recs' onChange=\"numRecsChange()\"> \n");
			print("				<option value='10'>10</option>\n");
			print("				<option value='20' SELECTED>20</option>\n");
			print("				<option value='50'>50</option>\n");
			print("				<option value='100'>100</option>\n");
			print("				<option value='200'>200</option>\n");
			print("				<option value='500'>500</option>\n");
			print(" 		</th>\n");

			print(" 		<th colspan=2>Mechanic<select id='sel_filterMechanic' onChange=\"numRecsChange()\"> \n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			$sql  = "SELECT distinct ltrim(m.ID_BADGE_MECH) as ID_BADGE_MECH, ";
			$sql .= " CASE ";
			$sql .= "  WHEN e.NAME_EMP is NULL AND m.ID_BADGE_MECH = 'SELECT' THEN 'OTHER' ";
			$sql .= "  WHEN e.NAME_EMP is NULL AND m.ID_BADGE_MECH = '0000' THEN 'OTHER' ";
			$sql .= "  WHEN e.NAME_EMP is NULL AND m.ID_BADGE_MECH = 'GORE1' THEN 'Gore 1' ";
			$sql .= "  WHEN e.NAME_EMP is NULL AND m.ID_BADGE_MECH = 'GORE2' THEN 'Gore 2' ";
			$sql .= "  WHEN e.NAME_EMP is NULL AND m.ID_BADGE_MECH = 'GORE3' THEN 'Gore 3' ";
			$sql .= "  ELSE e.NAME_EMP ";
			$sql .= " END as NAME_EMP ";
			$sql .= " FROM nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " m ";
			$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
			$sql .= " on ltrim(m.ID_BADGE_MECH) = ltrim(e.ID_BADGE) ";
			$sql .= " and e.CODE_ACTV = 0 ";
			$sql .= " ORDER BY ID_BADGE_MECH asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['ID_BADGE_MECH'] . "'>" . $row['ID_BADGE_MECH'] . " - " . $row['NAME_EMP'] . "</option>\n");
			}
			print(" 		</th>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");

			print(" 		<th colspan=2>Team <select id='sel_filterTeam' onChange=\"numRecsChange()\"> \n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			$sql  = " SELECT distinct ltrim(mi.ID_BADGE_TEAM) as ID_BADGE_TEAM, de.NAME_EMP, ";
			$sql .= "  CASE ";
			$sql .= "   WHEN de.NAME_EMP is null THEN ltrim(ID_BADGE_TEAM) ";
			$sql .= "   else (ltrim(mi.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP) ";
			$sql .= "  END as BADGETEAM ";
			$sql .= " FROM nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " mi ";
			$sql .= " LEFT JOIN nsa.DCEMMS_EMP de ";
			$sql .= " on ltrim(mi.ID_BADGE_TEAM) = ltrim(de.ID_BADGE) ";
			$sql .= " GROUP BY mi.ID_BADGE_TEAM, de.NAME_EMP ";
			$sql .= " ORDER BY ID_BADGE_TEAM asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['ID_BADGE_TEAM'] . "'>" . $row['BADGETEAM'] . "</option>\n");
			}
			print(" 		</th>\n");

			print(" 		<th colspan=2>Mach ID <select id='sel_filterMachID' onChange=\"numRecsChange()\"> \n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			$sql  = " SELECT distinct ltrim(mi.ID_MACH) as ID_MACH, ";
			//$sql .= " ltrim(mm.ID_MACH) + ' - ' + mm.ID_CLUSTER + ' - ' + mm.HEAD_BRAND + ' - ' + mm.MODEL_NUM as MACH_DESC ";
			$sql .= " ltrim(mm.ID_MACH) + ' - ' + mm.HEAD_BRAND + ' - ' + mm.MODEL_NUM as MACH_DESC ";
			$sql .= " FROM nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " mi ";
			$sql .= " LEFT JOIN nsa.MAINT_MACHINERY mm ";
			$sql .= " on mi.ID_MACH = mm.ID_MACH ";
			//$sql .= " GROUP BY mi.ID_MACH, mm.ID_MACH, mm.ID_CLUSTER, mm.HEAD_BRAND, mm.MODEL_NUM ";
			$sql .= " GROUP BY mi.ID_MACH, mm.ID_MACH, mm.HEAD_BRAND, mm.MODEL_NUM ";
			$sql .= " ORDER BY ID_MACH asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['ID_MACH'] . "'>" . $row['MACH_DESC'] . "</option>\n");
			}
			print(" 		</th>\n");

			print(" 	</tr>\n");
			print(" 	<tr>\n");

			print(" 		<th colspan=2>Maint Code <select id='sel_filterMaintCode' onChange=\"numRecsChange()\"> \n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			$sql  = " SELECT distinct ltrim(mi.CODE_MAINT) as CODE_MAINT, ";
			$sql .= " ltrim(mc.CODE_MAINT) + ' - ' + mc.DESCR as CODE_DESCR ";
			$sql .= " FROM nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " mi ";
			$sql .= " LEFT JOIN nsa.MAINT_CODES mc ";
			$sql .= " on mi.CODE_MAINT = mc.CODE_MAINT ";
			$sql .= " and mc.CODE_MAINT_RES = 'M' ";
			$sql .= " GROUP BY mi.CODE_MAINT, mc.CODE_MAINT, mc.DESCR ";
			$sql .= " ORDER BY CODE_MAINT asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['CODE_MAINT'] . "'>" . $row['CODE_DESCR'] . "</option>\n");
			}
			print(" 		</th>\n");

			print(" 		<th colspan=2>Maint Res Code <select id='sel_filterMaintResCode' onChange=\"numRecsChange()\"> \n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			$sql  = " SELECT distinct ltrim(mi.CODE_MAINT_RES) as CODE_MAINT_RES, ";
			$sql .= " ltrim(mc.CODE_MAINT) + ' - ' + mc.DESCR as CODE_DESCR ";
			$sql .= " FROM nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " mi ";
			$sql .= " LEFT JOIN nsa.MAINT_CODES mc ";
			$sql .= " on mi.CODE_MAINT_RES = mc.CODE_MAINT ";
			$sql .= " and mc.CODE_MAINT_RES = 'R' ";
			$sql .= " GROUP BY mi.CODE_MAINT_RES, mc.CODE_MAINT, mc.DESCR ";
			$sql .= " ORDER BY CODE_MAINT_RES asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['CODE_MAINT_RES'] . "'>" . $row['CODE_DESCR'] . "</option>\n");
			}
			print(" 		</th>\n");

			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'>\n");
			print(" </table>\n");
			print(" </br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('');
?>
