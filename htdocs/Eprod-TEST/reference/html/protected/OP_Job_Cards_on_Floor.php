<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2('OP Job Cards On Floor','default.css','OP_Job_Cards_on_Floor.js');
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
			print(" <form id='SO_Form' method='post' enctype='multipart/form-data'>\n");			
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Enter New Record: </th>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so1'>\n");
			print(" 		<td>Shop Order: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so1' type=text onkeyup=\"nextOnDash('so1','sufx_so1')\" maxlength=9 size=10 autofocus tabindex=1> -\n");
			print("				<input id='sufx_so1' type=text onkeyup=\"checkSufxLength('so1','sufx_so1')\" maxlength=3 size=4 tabindex=2>\n");
			print("			</td>\n");
			print(" 	</tr>\n");

			print(" 	<tr></tr>\n");
			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print(" 		<td><INPUT id='dw_submit' type='button' value='Add Record' onClick=\"sendAddValue()\" tabindex=3></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" </form>\n");
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
			print("				<option value='1000'>1,000</option>\n");
			print("				<option value='10000'>10,000</option>\n");
			print("				<option value='50000'>50,000</option>\n");
			print("				<option value='100000'>100,000</option>\n");
			print("				<option value='250000'>250,000</option>\n");
			print("				<option value='500000'>500,000</option>\n");
			print("			</select> Records: </th>\n");
			print(" 		<th colspan=2>SO Lookup<input id='searchSO' name='searchSO' type='text' value='ALL' onblur=\"numRecsChange()\"></input><th>\n");			
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" </div>\n");
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