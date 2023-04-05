<?php
	$DEBUG = 0;
	$READONLY = '';
	//$READONLY = 'READONLY';

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Vendor History','default.css','vendorhist.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td>Starting Date: </td>\n");
			print(" 		<td colspan=4>");

			$myCalendar = new tc_calendar('df', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d'), date('m')-1, date('Y'));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Ending Date: </td>\n");
			print(" 		<td colspan=4>");

			$myCalendar = new tc_calendar('dt', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$futTS = strtotime("+7 days", time());
			$myCalendar->setDate(date('d',$futTS), date('m',$futTS), date('Y',$futTS));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Starting Item: </td>\n");
			print(" 		<td><input type='text' id='StartItem' value='A' " . $READONLY . "></td>\n");
			print(" 		<td>Ending Item: </td>\n");
			print(" 		<td><input type='text' id='EndItem' value='Z' " . $READONLY . "></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Starting Vendor Number: </td>\n");
			print(" 		<td><input type='text' id='StartVendNum' value='1' " . $READONLY . "></td>\n");
			print(" 		<td>Ending Vendor Number: </td>\n");
			print(" 		<td><input type='text' id='EndVendNum' value='999999999' " . $READONLY . "></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td><INPUT type='button' value='Submit' onClick=\"submitForm()\"></td>\n");
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

	PrintFooter("emenu.php");

?>
