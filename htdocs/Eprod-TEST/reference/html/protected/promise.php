<?php
	$DEBUG = 0;
	$READONLY = '';
	//$READONLY = 'READONLY';

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Promise Date','default.css','promise.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td>Start Promise Date: </td>\n");
			print(" 		<td colspan=4>");

			$myCalendar = new tc_calendar('df', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d'), date('m'), date('Y')-1);
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>End Promise Date: </td>\n");
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
			print(" 		<td>Starting Prod Cat: </td>\n");
			print(" 		<td><input type='text' id='StartProdCat' value='A' " . $READONLY . "></td>\n");
			print(" 		<td>Ending Prod Cat: </td>\n");
			print(" 		<td><input type='text' id='EndProdCat' value='Z' " . $READONLY . "></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Starting Item: </td>\n");
			print(" 		<td><input type='text' id='StartItem' value='A' " . $READONLY . "></td>\n");
			print(" 		<td>Ending Item: </td>\n");
			print(" 		<td><input type='text' id='EndItem' value='Z' " . $READONLY . "></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Starting Cust Number: </td>\n");
			print(" 		<td><input type='text' id='StartCustNum' value='1' " . $READONLY . "></td>\n");
			print(" 		<td>Ending Cust Number: </td>\n");
			print(" 		<td><input type='text' id='EndCustNum' value='999999999' " . $READONLY . "></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Order Number: </td>\n");
			print(" 		<td><input type='text' id='Order_Num' " . $READONLY . "></td>\n");
			print(" 		<td>Show WC Detail: <input type='checkbox' id='FlagDetail' value='detail'></td>\n");
			print(" 		<td>Show Comments: <input type='checkbox' id='FlagComments' CHECKED></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Assumed Efficiency: </td>\n");
			print(" 		<td>\n");
			print(" 			<select id='CapPCT'>\n");
			$v=125;
			while ($v >= 0) {
				$o = '';
				if ($v == 100) {
					$o = "selected='selected'";
				}
				print(" 				<option value='" . $v . "' ". $o .">" . $v ." %</option>\n");
				$v = $v-5;
			}
			print(" 			</select>\n");
			print("			</td>\n");
			print(" 		<td><INPUT type='button' value='Submit' onClick=\"submitForm()\"></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td colspan=3><font class='red'>*Estimated Date assumes a FULL DAY of production remains for the day this report is run. </font></td>");
			print(" 	</tr>\n");
			//print(" 	<tr>\n");
			//print(" 		<td colspan=3><font class='red'>and that the teams are operating at 100% efficiency.</font></td>\n");
			//print(" 	</tr>\n");
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
