<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Workcenter Capacity','default.css','wccap.js');
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
			print(" 		<td>Select Status: </td>");
			print(" 		<td>");
			print(" 			<select id='StatCode'>\n");
			print(" 				<option value='S'>Started</option>\n");
			print(" 				<option value='R'>Released</option>\n");
			print(" 				<option value='A'>Active</option>\n");
			print(" 				<option value='U'>Unreleased</option>\n");
			print(" 				<option value='ALL' SELECTED>--ALL--</option>\n");
			print(" 			</select>\n");
			print(" 		</td>");
			print(" 	</tr>");

			$sql =  "select ";
			$sql .= " wc.ID_WC, ";
			$sql .= " wc.DESCR_WC ";
			$sql .= " from ";
			$sql .= " nsa.tables_loc_dept_wc wc ";
			$sql .= " where ";
			$sql .= " wc.ID_WC between '1999' and '7999' ";
			QueryDatabase($sql, $results);

			print(" 	<tr>");
			print(" 		<td>Select Work Center: </td>");
			print(" 		<td>");
			print(" 			<select id='selWC'>\n");
			print(" 				<option value='ALL' SELECTED>--ALL--</option>\n");
			while ($row = mssql_fetch_assoc($results)) {
				print(" 				<option value='".$row['ID_WC']."'>".$row['ID_WC']." - ".$row['DESCR_WC']."</option>\n");
			}
			print(" 			</select>\n");
			print(" 		</td>");
			print(" 	</tr>");


			print(" 	<tr>");
			print(" 		<td>Enter Cutoff Due Date: </td>");
			print(" 		<td>");

			$myCalendar = new tc_calendar('dt', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$futTS = strtotime("+35 days", time());
			$myCalendar->setDate(date('d',$futTS), date('m',$futTS), date('Y',$futTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>");
			print(" 		<td colspan='2'>");
			print(" 			<INPUT type='submit' value='Submit' onClick=\"submitForm()\">");
			print(" 		</td>");
			print(" 	</tr>");
			print(" </table>");
			print(" <div id='dataDiv'></div>\n");

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
