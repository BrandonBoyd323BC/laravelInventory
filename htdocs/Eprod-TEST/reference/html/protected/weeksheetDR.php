<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/tc_calendar.php");

	PrintHeaderJQ('Weekly TSS Worksheet','default.css','weeksheetDR.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td>Week Of: </td>\n");
			print(" 		<td>\n");

			$myCalendar = new tc_calendar('weekOf', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d'), date('m'), date('Y'));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='team'>Team: </LABEL>\n");
			print("				<select id='team'>\n");

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

			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['ID_BADGE'] . "'>" . $row['BADGE_NAME'] . "</option>\n");
			}
			print("					<option value='ALL'> - ALL - (may take around 1-2 minutes)</option>\n");
			print("				</select>\n");
			print(" 			<INPUT id='submit' type='submit' value='Submit' onClick=\"sendValue()\">\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td colspan=4><font class='red'>Please be sure all days have been APPROVED to ensure accuracy.</font></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='LoadingDiv'></div>\n");
			print(" <div id='dataDiv'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter("emenu.php");
?>
