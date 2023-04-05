<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ2('Shop Order Labels','default.css','soLabels.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<LABEL for='selMode'>Select Mode: </LABEL>\n");
			print("			<select name='selMode' id='selMode' onChange=\"selModeChange()\">\n");
			print("					<option value='--SELECT--'>--SELECT--</option>\n");
			print("					<option value='shopOrder' SELECTED>Scan Shop Order</option>\n");
			print("					<option value='itemNumber'>Item Number</option>\n");
			print("			</select>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print(" <div id='formDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" <div id='dataDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
			print("<body onLoad='selModeChange()'>\n");
			print("</body>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('');
?>