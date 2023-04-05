<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2OL('Late Order Codes','default.css','lateordercodes.js','doOnLoads');
	//PrintHeaderJQ2('Late Order Codes','default.css','lateordercodes.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);

			print(" <table>");
			print(" 	<tr>");
			print(" 		<td>Date From: </td>");
			print(" 		<td>");

			$yesterdayTS = strtotime("yesterday");

			$myCalendar = new tc_calendar('df', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',$yesterdayTS), date('m',$yesterdayTS), date('Y',$yesterdayTS));
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
			$myCalendar->setDate(date('d',$yesterdayTS), date('m',$yesterdayTS), date('Y',$yesterdayTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>");
			print(" 		<td>Show: </td>");
			print(" 		<td>");
			print(" 			<select id='sel_ShowRec'>\n");
			print(" 				<option value='UNRECORDED' SELECTED>Unrecorded</option>\n");
			print(" 				<option value='RECORDED'>Recorded</option>\n");
			print(" 				<option value='ALL'>ALL</option>\n");
			print(" 			</select>\n");
			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>");
			print(" 		<td colspan='2'>");
			print(" 			<INPUT type='submit' value='Submit' onClick=\"showRecords()\">");
			print(" 		</td>");
			print(" 	</tr>");
			print(" </table>");
			print(" <div id='dataDiv'></div>\n");
			print("<body onLoad='doOnLoads()'>");

			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}


	PrintFooter("emenu.php");


?>
