<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Team Activity Log','default.css','activity.js');
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
			$todayTS = time();

			$myCalendar = new tc_calendar('df', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',$prevTS), date('m',$prevTS), date('Y',$prevTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>");
			print(" 		<td>Date To: </td>");
			print(" 		<td>");

			$myCalendar = new tc_calendar('dt', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',$todayTS), date('m',$todayTS), date('Y',$todayTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>");
			print(" 	</tr>");
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
			print(" 	<tr>");
			print(" 		<td colspan='2'>");
			print(" 			<LABEL for='team'>Team: </LABEL>");
			print("				<select id='team'>");

			$sql =  "select ";
			$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
			$sql .= " 	ltrim(ID_BADGE) as ID_BADGE,";
			$sql .= " 	NAME_EMP";
			$sql .= " from ";
			$sql .= " 	nsa.DCEMMS_EMP ";
			$sql .= " where ";
			$sql .= " 	TYPE_BADGE = 'X'";
			$sql .= " 	and";
			$sql .= " 	CODE_ACTV = '0'";
			$sql .= "   and";
			$sql .= "   ID_BADGE_SUPRVSR <> ''";
			$sql .= " order by BADGE_NAME asc";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['ID_BADGE'] . "'>" . $row['BADGE_NAME'] . "</option>");
			}

			print("				</select>");
			print(" 			<INPUT type='submit' value='Submit' onClick=\"sendValue()\">");
			print(" 		</td>");
			print(" 	</tr>");
			print(" </table>");
			print(" <div id='LoadingDiv'></div>\n");
			print(" <div id='dataDiv'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	print(" </br>");
	PrintFooter("emenu.php");


?>
