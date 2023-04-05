<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ2('Item Inquiry','default.css','itemInquiry.js');
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
			print("			<td>Location: </td>\n");
			print("			<td>\n");
			print("				<select name='selLoc' id='selLoc' onChange='searchItem();'>\n");
			print("					<option value='10'>10</option>\n");
			print("					<option value='20'>20</option>\n");
			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");



			print("		<tr id='tr_id_item'>\n");
			print(" 		<th>Enter Item: </th>\n");
			print(" 		<td>\n");
			print("				<div id='div_id_item'><input id='id_item' name='id_item' type=text  maxlength=30 size=30 autofocus ></div>\n");
			print("			</td>\n");
			print(" 		<td>\n");
			print("				<div id ='div_submit_button'><input type='button' value='Submit' onClick='searchItem()' id='btnSubmit'></div>");
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

