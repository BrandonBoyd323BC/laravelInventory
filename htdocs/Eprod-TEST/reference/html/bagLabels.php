<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ2('Bag Labels','default.css','bagLabels.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			
			//print(" <form id='Bag_Label_Form' method='post' enctype='multipart/form-data'>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<LABEL for='selMode'>Select Mode: </LABEL>\n");
			print("			<select name='selMode' id='selMode' onChange=\"selModeChange()\">\n");
			print("					<option value='--SELECT--'>--SELECT--</option>\n");
			print("					<option value='pickTicket' SELECTED>Scan Pick Ticket</option>\n");
			print("					<option value='shopOrder'>Scan Shop Order</option>\n");
			print("					<option value='manual'>Manual</option>\n");
			print("			</select>\n");
			//print(" 		<button id='submitMode' name='submitMode' value='Submit' onclick='subValue(document.getElementById(\"selMode\").value)'>Go</button>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			//print(" </form>\n");




/*
			print(" <form id='Bag_Label_Form' method='post' enctype='multipart/form-data'>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Scan Pick Ticket: </th>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so1'>\n");
			print(" 		<td>Shop Order: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so' type=text onkeyup=\"nextOnDash('so','sufx')\" maxlength=9 size=10 autofocus> -\n");
			print("				<input id='sufx' type=text onkeyup=\"sufxEntered()\" maxlength=3 size=4>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_qty1'>\n");
			print(" 		<td>Qty in Box: </td>\n");
			print(" 		<td id='td_qty_in_box'>\n");
			print("				<input id='qty_in_box' type=text maxlength=7 size=8>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_qty2'>\n");
			print(" 		<td>Qty in Bag: </td>\n");
			print(" 		<td id='td_qty_in_bag'>\n");
			print("				<input id='qty_in_bag' type=text maxlength=7 size=8>\n");
			print("			</td>\n");
			print(" 	</tr>\n");			
			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print(" 		<td><INPUT id='submit' type='button' value='Get Labels' onClick=\"getBagLabels()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" </form>\n");
*/
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