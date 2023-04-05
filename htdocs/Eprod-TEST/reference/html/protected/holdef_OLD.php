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
