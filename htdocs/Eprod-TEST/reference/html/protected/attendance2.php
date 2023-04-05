<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Attendance Log','default.css','attendance.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");

			if ($UserRow['PERM_HR'] == '1') {

				print(" <table>");
				print(" 	<tr>");
				print(" 		<td>Date From: </td>");
				print(" 		<td>");

				$myCalendar = new tc_calendar('df', true, true);
				$myCalendar->setIcon("images/iconCalendar.gif");
				$myCalendar->setDate(date('d'), date('m'), date('Y'));
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
				$myCalendar->setDate(date('d'), date('m'), date('Y'));
				$myCalendar->setPath("/protected");
				$myCalendar->setYearInterval(1970, 2030);
				$myCalendar->setAlignment('left', 'bottom');
				$myCalendar->writeScript();

				print(" 		</td>");
				print(" 	</tr>");

				print(" </table>");
				print(" 	<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
				//print(" </form>");
				print(" <div id='dataDiv'></div>");

			} else {
				print "					<p class='warning'>Permission Denied!</p>\n";
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('emenu.php');


?>
