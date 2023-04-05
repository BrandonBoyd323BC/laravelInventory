<?php
	$DEBUG = 0;

	require_once("procfile.php");

	PrintHeader('Salesforce CSVs','default.css');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");

			if ($UserRow['PERM_SU'] == '1') {
				print("<table>\n");
				print("	<th colspan='3'>Salesforce</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='csvSF_Accts_SOLDTO.php'><img class='icon' src='/images/sold.gif' href='csvSF_Accts_SOLDTO.php'></br>Accounts SoldTo</a></td>\n");
				print("		<td class='icon'><a href='csvSF_Accts_SHIPTO.php'><img class='icon' src='/images/ship.jpg' href='csvSF_Accts_SHIPTO.php'></br>Accounts ShipTo</a></td>\n");
				print("		<td class='icon'><a href='csvSF_Contacts.php'><img class='icon' src='/images/contact.jpg' href='csvSF_Contacts.php'></br>Contacts</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

	PrintFooter("emenu.php");

?>
