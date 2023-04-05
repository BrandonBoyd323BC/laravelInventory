<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";

	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Parts Inventory','default.css','partsinv.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			print("	<body onload=\"showOnLoad()\">");
			print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
			print(" </br>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			//print(" 		<th colspan=2>Parts: </th>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <input type=hidden id='sortDirFlag' value='asc'>\n");
			print(" <input type=hidden id='sortField' value='MANUFACTURER'>\n");
			print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('emenu.php');
?>
