<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('R&D Inspection Log','default.css','RnDInspection.js');	

	$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Connect To $DBServer!\n";
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				print "		<p class='warning'>Could Not Select $dbName!\n";
			} else {
				$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

				print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
				print(" <table>\n");

				print(" </table>\n");

				print(" <div id='mainDiv'>\n");
				print(" <body onload=\"refreshMainDiv('mainDiv')\"></div>\n");
				print(" <div id='dataDiv'></div>\n");
				print(" <div id='backgroundPopup'></div>\n");
				print(" <div id='dataPopup'></div>\n");

			}//end else
		}//end else


			$retval = DisconnectFromDatabaseServer($db);
				if ($retval == 0) {
					print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
				}

	PrintFooter('inspectionLandingPage.php');
?>