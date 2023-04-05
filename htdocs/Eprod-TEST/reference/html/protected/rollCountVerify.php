<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2('Roll Count Verify','default.css','rollCountVerify.js');
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
			print(" 		<td>Item: </td>\n");
			print(" 		<td><div id='div_id_item'><input id='tb_item' type=text maxlength=30 onkeyup=\"itemCodeChange()\"></div></td>\n");
			print(" 	</tr>\n");
			print("		<tr>\n");
			print("			<td>Finding:</td>\n");
			print("			<td><input type='radio' id='V' name='choice' value='V'>Verified</input></td>\n");
			print("		<tr>\n");	
			print("		</tr>\n");
			print("			<td></td>\n");
			print("			<td><input type='radio' id='D' name='choice' value='D'>Discrepancy</input></td>\n");
			print("		</tr>\n");
			print(" 	<tr id='tr_textFindings' style='display:none'>\n");
			print(" 		<td>Findings: </td>\n");
			print(" 		<td><textarea id='text_findings' cols='1' rows='5' charswidth='12' style='resize:vertical'></textarea></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr rowspan=2></tr>\n");
			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print(" 		<td><input id='button_addRecord' type='button' value='Submit' onClick=\"sendAddValue()\"></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" </br>\n");

			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Show <select id='user_recs' onChange=\"numRecsChange()\">\n");
			print("					<option value='--ALL--'>--ALL--</option>\n");

			$sql  = "SELECT distinct rcv.ID_USER_ADD as ID_USER2, ";
			$sql .= " wa.NAME_EMP ";
			$sql .= " FROM nsa.ROLL_COUNT_VERIFY rcv ";
			$sql .= " LEFT JOIN nsa.DCWEB_AUTH wa ";
			$sql .= " on rcv.ID_USER_ADD = wa.ID_USER ";
			$sql .= " ORDER BY ID_USER2 ";
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
