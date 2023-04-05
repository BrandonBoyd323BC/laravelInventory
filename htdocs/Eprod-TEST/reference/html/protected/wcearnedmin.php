<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Workcenter Earned Minutes','default.css','wc.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
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

			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print(" 		<td >\n");
			//print(" 			<LABEL for='supr'>Supervisor: </LABEL>\n");
			//print("				<select id='supr'>\n");
			//$sql =  "select ";
			//$sql .= " 	distinct ltrim(e2.ID_BADGE) as ID_BADGE,";
			//$sql .= " 	e2.NAME_EMP as SUPRVSR_NAME";
			//$sql .= " from ";
			//$sql .= " 	nsa.DCEMMS_EMP e1, ";
			//$sql .= " 	nsa.DCEMMS_EMP e2 ";
			//$sql .= " where ";
			//$sql .= " 	e1.TYPE_BADGE = 'X'";
			//$sql .= " 	and";
			//$sql .= " 	e1.CODE_ACTV = '0'";
			//$sql .= " 	and";
			//$sql .= " 	e1.ID_BADGE_SUPRVSR = e2.ID_BADGE";
			//$sql .= " 	and";
			//$sql .= " 	e2.CODE_ACTV = '0'";
			//$sql .= " order by SUPRVSR_NAME asc";
			//QueryDatabase($sql, $results);
			//print("					<option value='ALL'> -- ALL -- </option>\n");
			//while ($row = mssql_fetch_assoc($results)) {
			//	print("					<option value='" . $row['ID_BADGE'] . "'>" . $row['SUPRVSR_NAME'] . "</option>\n");
			//}
			//print("				</select>\n");

			print(" 			<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
			print(" 		</td>\n");
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
