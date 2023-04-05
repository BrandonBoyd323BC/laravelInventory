<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Holiday Schedule','default.css','holdef.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			print("	<body onload=\"submitForm('show')\">");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td>Year: </td>\n");
			print(" 		<td>\n");
			print("		 		<select id='YR'>\n");

			$i = -1;
			while ($i <= 5) {
				$o = '';
				$iYR = (date('Y') + $i);
				if ($iYR == date('Y')) {
					$o = "selected='selected'";
				}
				print("			 		<option id='" . $iYR . "' ". $o .">" . $iYR . "</option>\n");
				$i++;
			}
			print("		 		</select>\n");
			print(" 		</td>\n");
			print(" 		<td>\n");
			print("				<INPUT type='button' value='Submit' onClick=\"submitForm('show')\">\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'></div>\n");
			print(" <br />");
			print(" <table>");
			print("		<tr>\n");
			print("			<td><strong>Add New</strong></td>");
			print(" 	</tr>");
			print(" 	<tr>");
			print(" 		<td>Date:</td>");
			print(" 		<td>");
			//$prevTS = strtotime("-1 days", time());
			$prevTS = time();

			$myCalendar = new tc_calendar('dh', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',$prevTS), date('m',$prevTS), date('Y',$prevTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>\n");
			print("			<td>Holiday:</td>\n");
			print("			<td><input id='dscr' type='text'></input></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>\n");
			print("			<input type='button' value='Add' onClick=\"DateAdd()\"></input>");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
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
