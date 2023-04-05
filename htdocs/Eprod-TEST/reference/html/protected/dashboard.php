<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Supervisor Dashboard','default.css','dashboard.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {

			print(" <table>");
			print(" 	<tr>");
			print(" 		<td>Date From: </td>");
			print(" 		<td>");
			$prevTS = strtotime("-1 days", time());
			$todayTS = time();

			$myCalendar = new tc_calendar('df', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',$prevTS), date('m',$prevTS), date('Y',$prevTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Date To: </td>\n");
			print(" 		<td>\n");

			$myCalendar = new tc_calendar('dt', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			//$myCalendar->setDate(date('d',$prevTS), date('m',$prevTS), date('Y',$prevTS));
			$myCalendar->setDate(date('d',$todayTS), date('m',$todayTS), date('Y',$todayTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>");
			print(" 		<td colspan='2'>");
			print(" 			<LABEL for='zeroHour'>Zero Hour: </LABEL>");
			print("				<select id='zeroHour'>");
			print("					<option value='000000'>12:00:00 AM</option>");
			print("					<option value='003000'>12:30:00 AM</option>");
			print("					<option value='010000'>01:00:00 AM</option>");
			print("					<option value='013000' SELECTED>01:30:00 AM</option>");
			print("					<option value='020000'>02:00:00 AM</option>");
			print("					<option value='023000'>02:30:00 AM</option>");
			print("					<option value='030000'>03:00:00 AM</option>");
			print("				</select>");
			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>\n");
			print(" 		<td colspan=3>Calculate using ONLY approved time<INPUT type='checkbox' id='onlyappvd' value='onlyappvd' onClick=\"checkUncheck()\"></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr style='display:none' id='orderByRow'>\n");
			print(" 		<td colspan=3>Order By: <select id='orderby'><option value='OVERALL_EFF desc'>Percentage</option><option value='ID_BADGE asc'>Team Badge</option></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='supr'>Supervisor: </LABEL>\n");
			print("				<select id='supr'>\n");

			$sql  = "SELECT ";
			$sql .= " distinct ltrim(e2.ID_BADGE) as ID_BADGE,";
			$sql .= " e2.NAME_EMP as SUPRVSR_NAME";
			$sql .= " FROM nsa.DCEMMS_EMP e1, ";
			$sql .= " nsa.DCEMMS_EMP e2 ";
			$sql .= " WHERE e1.TYPE_BADGE = 'X'";
			$sql .= " and e1.CODE_ACTV = '0'";
			$sql .= " and e1.ID_BADGE_SUPRVSR = e2.ID_BADGE";
			$sql .= " and e2.CODE_ACTV = '0'";
			$sql .= " order by SUPRVSR_NAME asc";
			QueryDatabase($sql, $results);
			print("					<option value='ALL'> -- ALL -- </option>\n");
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['ID_BADGE'] . "'>" . $row['SUPRVSR_NAME'] . "</option>\n");
			}

			print("				</select>\n");
			print(" 			<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");



			print(" </table>\n");
			//print(" 	<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
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
