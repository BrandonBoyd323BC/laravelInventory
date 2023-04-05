<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('PROD Oper Rebuild','default.css','prodOperRebuild.js');
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

			//$prevTS = strtotime("-1 days", time());

			$sql  = "SELECT max(DATE_APP) as MAX_DATE_APP, ";
			$sql .= " DATEDIFF(second,{d '1970-01-01'}, max(DATE_APP)) as MAX_TS ";
			$sql .= " FROM nsa.DC_PROD_OPERS_LOG ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$prevTS = strtotime("+2 days", $row['MAX_TS']);
			}

			//$todayTS = time();
			$todayTS = strtotime("-5 days", time());

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
			print(" 		<td colspan=3>\n");
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
