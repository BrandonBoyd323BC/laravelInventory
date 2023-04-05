<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Bin Count','default.css','binCount.js');
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
			print(" 	<tr>\n");
			print(" 		<td>Bin: </td>\n");
			print(" 		<td><input id='tb_bin' type=text maxlength=20 onkeypress=\"searchKeyPress(event,'tb_item');\"></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Item: </td>\n");
			print(" 		<td><input id='tb_item' type=text maxlength=30 onkeypress=\"searchKeyPress(event,'tb_qty');\"></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Qty: </td>\n");
			print(" 		<td><input id='tb_qty' type=text maxlength=8 onkeypress=\"searchKeyPress(event,'button_addRecord');\"></td>\n");
			print(" 	</tr>\n");			
			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print(" 		<td><input id='button_addRecord' type='button' value='Add Record' onClick=\"sendAddValue()\"></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Show <select id='user_recs' onChange=\"numRecsChange()\">\n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			
			$sql  = "select distinct bc.ID_USER_ADD as ID_USER2, ";
			$sql .= " wa.NAME_EMP ";
			$sql .= " from nsa.BIN_COUNT_CUSTOM bc ";
			$sql .= " left join nsa.DCWEB_AUTH wa ";
			$sql .= " on bc.ID_USER_ADD = wa.ID_USER ";
			$sql .= " where (bc.FLAG_DEL = '' OR bc.FLAG_DEL is NULL) ";
			$sql .= " order by ID_USER2 ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				if ($UserRow['ID_USER'] == $row['ID_USER2']) {
					$SELECTED = 'SELECTED';
				} else {
					$SELECTED = '';
				}
				print("					<option value='" . $row['ID_USER2'] . "' " . $SELECTED . ">" . $row['NAME_EMP'] . "</option>\n");
			}

			print("			</select>'s </th>\n");
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
			print("				<option value='25000'>25000</option>\n");
			print("				<option value='50000'>50000</option>\n");
			print("				<option value='75000'>75000</option>\n");
			print("				<option value='100000'>100000</option>\n");
			print("				<option value='500000'>500000</option>\n");
			print("			</select> Records: </th>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print(" <body onLoad='doOnLoads()'>");
			print(" <div id='dataAddDiv'></div>\n");
			print(" <div id='dataDiv'></div>\n");
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
