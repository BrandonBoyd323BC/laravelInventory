<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ2('Bin Lookup','default.css','binlookup.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print(" <table>\n");
			print("		<tr id='tr_id_item'>\n");
			print(" 		<th>Item: </th>\n");
			print(" 		<td>\n");
			print("				<div id='div_id_item'><input id='tb_id_item' name='tb_id_item' type=text onkeyup=\"IdItemChange()\" onblur=\"lookupBin()\" maxlength=30 size=30 autofocus ></div>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='div_inputform' name='div_inputform'></div>\n");
			print(" <body onLoad='doOnLoads()'>");
			print(" <div id='dataDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" </div>\n");
			//print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
