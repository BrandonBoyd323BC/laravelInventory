<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Workcenter Capacity Dashboard','default.css','wccapdash.js');
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
			print(" 		<td>Report Scope: </td>");
			print(" 		<td>");
			print(" 			<select id='sel_Mode' >\n");
			print("					<option value='OrdLin' title='Workload based on manufacturing all customer order lines, regardless of stock on hand, excluding stock replenishment orders'>Open Customer Orders</option>\n");
			print("					<option value='ALLSO' title='Workload based on manufacturing all open Shop Orders, including stock replenishment orders'>All Shop Orders</option>\n");
			print("					<option value='OperStatus' title='Workload based on open Shop Orders and the Operation Status for each Workcenter'>SO Operation Status</option>\n");
			print(" 			</select>\n");
			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>");
			print(" 		<td>Target Days: </td>");
			print(" 		<td>");
			print(" 			<select id='sel_TargetDays' >\n");
			$x = 30;
			while ($x >= 1) {
				$selected = '';
				if ($x == 15) {
					$selected = 'SELECTED';
				}
				print("					<option value='" . $x . "' ".$selected.">" . $x . "</option>\n");
				$x = $x-1;
			}
			print(" 			</select>\n");
			print(" 		</td>");
			print(" 	</tr>");


			print(" 	<tr>");	
			print(" 		<td>Efficiency Percentage: </td>");
			print(" 		<td>");
			print(" 			<select id='sel_EffPct' >\n");
			print("					<option value='AVG'>3 Week Average</option>\n");
			$x = 125;
			while ($x >= 50) {
				print("					<option value='" . $x . "'>" . $x . "</option>\n");
				$x = $x-2.5;
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
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
			
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}


	PrintFooter("emenu.php");


?>
