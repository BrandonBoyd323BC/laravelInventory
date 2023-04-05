<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Payroll Transactions','default.css','paytrx.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			print(" <form method='post' action='csvPaytrx.php'>");
			print(" <table>");
			print(" 	<tr>");
			print(" 		<td>Date From: </td>");
			print(" 		<td>");

			$LastSatTS = strtotime("last Saturday");
			$LastSunTS = strtotime("last Sunday");

			$prevTS = strtotime("-6 days", $LastSatTS);

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
			//$myCalendar->setDate(date('d',$LastSatTS), date('m',$LastSatTS), date('Y',$LastSatTS));
			$myCalendar->setDate(date('d',$LastSunTS), date('m',$LastSunTS), date('Y',$LastSunTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>");
			print(" 		<td colspan='2'>");
			print(" 			ZeroHour: <INPUT id='zeroHour' type='textbox' value='013000' READONLY>");
/*			
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
*/			
			print(" 		</td>");
			print(" 	</tr>");			
			print(" 	<tr>");
			print(" 		<td>");
			print(" 			<INPUT type='button' value='Submit' onClick=\"sendValue()\">");
			print(" 		</td>");
			print(" 		<td>");
			print(" 			<INPUT type='submit' value='Download CSV'>");
			print(" 		</td>");
			print(" 	</tr>");
			print(" </table>");
			print(" </form>");
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
