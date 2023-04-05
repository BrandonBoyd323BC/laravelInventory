<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	PrintHeaderJQ2('Comments Interface','default.css','comments.js');
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
			print(" 	<tr>");
			print(" 		<td>\n");
			print(" 		<LABEL for='selLocation'>Select Location: </LABEL>\n");
			print("			<select name='selLocation' id='selLocation' onChange=\"selLocationChange()\">\n");
			print("				<option value='SELECT' SELECTED>--SELECT--</option>\n");
			print("				<option value='10'>10 - NSA</option>\n");
			print("				<option value='20'>20 - Rubin</option>\n");
			print("			</select>\n");
			print("			</td>\n");
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
			//print("<body onLoad='selModeChange()'>\n");
			print("<body onLoad='selCompanyChange()'>\n");
			print("</body>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('');
?>