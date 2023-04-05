<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	PrintHeaderJQ2('DRIFIRE FC BIN CHANGER-UPPER','default.css','drfToFCBin.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);


			print(" <table>");

			print("	<tr id='tr_so1'>");
			print(" 		<td>Item Number: </td>");
			print(" 		<td><div id='div_id_item'>");
			print("			<input id='id_item' type=text onkeyup=\"idItemChange()\" size=30 autofocus>");
			print("		</div></td>");
			print(" 		<td><INPUT id='submit' type='button' value='Lookup' onClick=\"getItemBinOrderInfo()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n");
			print(" 	</tr>");

			print(" </table>");
			print(" <table id='table_ret_form'>");
			print(" </table>");

			print(" <div id='formDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" <div id='dataDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
			//print("<body onLoad='selModeChange()'>\n");
			print("</body>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	print(" </br>");
	PrintFooter("emenu.php");
?>