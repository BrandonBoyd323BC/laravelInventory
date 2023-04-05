<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2('Lot Tracking','default.css','lottracking.js');
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
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Enter New Records: </th>\n");
			print(" 	</tr>\n");


			print("		<tr id='tr_idbadge'>\n");
			print(" 		<td>Table #: </td>\n");
			print(" 		<td><select id='tablenum' onkeypress=\"selectChangedNextElement(event,'idbadge');\" tabindex=1>");
			print("				<option value=''>Select one</option>");
			print("				<option value='0'>0</option>");
			print("				<option value='1'>1</option>");
			print("				<option value='2'>2</option>");
			print("				<option value='3'>3</option>");
			print("				<option value='4'>4</option>");
			print("				<option value='5'>5</option>");
			print("			</select></td>\n");
			print(" 	</tr>\n");

			print("		<tr id='tr_idbadge'>\n");
			print(" 		<td>Badge #: </td>\n");
			print(" 		<td>\n");
			print("				<input id='idbadge' type=text onkeypress='nextOnEnter(event,idbadge,markerrowid);' tabindex=2>\n");
			print("			</td>\n");
			print(" 	</tr>\n");

			print("		<tr id='tr_markerrowid'>\n");
			print(" 		<td>Marker ID: </td>\n");
			print(" 		<td>\n");
			print("				<input id='markerrowid' type=text onkeypress='searchKeyPress(event,lookupRowid);' tabindex=3><INPUT id='lookupRowid' type='button' value='Lookup Marker' onClick=\"getMarkerInfo()\" tabindex=4>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print(" </br>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=4>Search Existing Records: </th>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_soid'>\n");
			print(" 		<td>SO Number: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so_num' type=text onkeyup=\"nextOnDash('so_num','sufx')\" maxlength=9 size=10 > -\n");
			print("			</td>\n");
			print(" 		<td>\n");
			print("				<input id='sufx' type=text maxlength=3 size=3>");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_lotnum'>\n");
			print(" 		<td>Lot Number: </td>\n");
			print(" 		<td>\n");
			print("				<input id='lot_num' type=text size=10>\n");
			print("			</td>\n");
			print("		<tr id='tr_buttons'>\n");
			print(" 		<td></td>\n");
			print(" 		<td>\n");
			print("				<input id='clearSearchButton' type='button' value='Clear' onClick=\"clearSeachCriteria()\" >\n");
			print("				<input id='SearchExistingButton' type='button' value='Submit' onClick=\"numRecsChange()\" >\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");


			print("<div id='divMarkerInfo'></div>");
			print("<body onLoad='doOnLoads()'>");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Last <select id='num_recs' onChange=\"numRecsChange()\">\n");
			print("				<option value='10'>10</option>\n");
			print("				<option value='20' SELECTED>20</option>\n");
			print("				<option value='50'>50</option>\n");
			print("				<option value='100'>100</option>\n");
			print("				<option value='200'>200</option>\n");
			print("				<option value='500'>500</option>\n");
			print("				<option value='1000'>1000</option>\n");
			print("				<option value='2000'>2000</option>\n");
			print("				<option value='5000'>5000</option>\n");
			print("				<option value='8000'>8000</option>\n");
			print("				<option value='10000'>10000</option>\n");
			print("				<option value='50000'>50000</option>\n");
			print("				<option value='500000'>500000</option>\n");
			print("			</select> Records: </th>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
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
	PrintFooter('emenu.php');
?>