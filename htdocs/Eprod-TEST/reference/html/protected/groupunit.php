<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Group Dashboard','default.css','groupunit.js');
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
			$myCalendar->setDate(date('d',$prevTS), date('m',$prevTS), date('Y',$prevTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr style='display:none' id='orderByRow'>\n");
			print(" 		<td colspan=3>Order By: <select id='orderby'><option value='OVERALL_EFF desc'>Percentage</option><option value='UNIT_ID asc'>Unit ID</option></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='unit'>Unit ID: </LABEL>\n");
			print("				<select id='unit'>\n");

			$sql =  "SELECT distinct PHONE as UNIT_ID ";
			$sql .= " FROM nsa.DCEMMS_EMP ";
			$sql .= " WHERE TYPE_BADGE = 'X' ";
			$sql .= " and ltrim(PHONE) <> '' ";
			$sql .= " ORDER BY PHONE asc";
			QueryDatabase($sql, $results);
			print("					<option value='ALL'> -- ALL -- </option>\n");
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['UNIT_ID'] . "'>" . $row['UNIT_ID'] . "</option>\n");
			}

			print("				</select>\n");
			//print(" 			<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			
			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='shift_summary'>Shift Summary: </LABEL>\n");
			print("				<select id='shift_summary'>\n");
			print("					<option value='0'>No</option>\n");
			print("					<option value='1'>Yes</option>\n");
			print("				</select>\n");
			print(" 			<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");


			print(" 	<tr>\n");
			print(" 		<td colspan=3>*Calculated using ONLY approved time</td>\n");
			print(" 	</tr>\n");



			print(" </table>\n");
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
