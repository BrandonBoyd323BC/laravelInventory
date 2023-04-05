<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ2('Prop 65 Checker','default.css','prop65Checker.js');
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
			print(" 		<LABEL for='selMode'>Select Mode: </LABEL>\n");
			print("			<select name='selMode' id='selMode' onChange=\"selModeChange()\">\n");
			print("					<option value='--SELECT--'>--SELECT--</option>\n");
			print("					<option value='order' SELECTED>Order</option>\n");
			print("					<option value='shopOrder'>Shop Order</option>\n");
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
?>
