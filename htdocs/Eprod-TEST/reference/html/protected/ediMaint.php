<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");


	PrintHeaderJQ('EDI Order Maintenance','default.css','ediMaint.js');
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
			print(" 		<td>Order Number: </td>\n");
			print(" 		<td>\n");
			print("				<input id='txt_OrderNum' type=text size=10 autofocus tabindex=1> \n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>PO Number: </td>\n");
			print(" 		<td>\n");
			print("				<input id='txt_PONum' type=text size=10 autofocus tabindex=2> \n");
			print("			</td>\n");
			print(" 	</tr>\n");
			//print(" 	<tr>\n");
			//print(" 		<td>Shipment Number: </td>\n");
			//print(" 		<td>\n");
			//print("				<input id='txt_ShipNum' type=text size=10 autofocus tabindex=3> \n");
			//print("			</td>\n");
			//print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>\n");
			print("				<input id ='clear' type='button' value='Clear' onClick=\"clearForm('show')\" tabindex=4>\n");
			print("			</td>\n");
			print(" 		<td>\n");
			print("				<input id ='submit' type='button' value='Submit' onClick=\"submitForm('show')\" tabindex=5>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'></div>\n");
			//print(" <div id='backgroundPopup'></div>\n");
			//print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	
	}
		
	PrintFooter("emenu.php");

?>