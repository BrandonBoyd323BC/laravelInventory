<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	PrintHeader('Data Collections Queued Transaction Count','default.css');
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

			if ($UserRow['PERM_SUPERVISOR'] == '1')  {
				print("<div id='div_dcTranRecCount' name='div_dcTranRecCount'>\n");
				print(" <table class='sample'>\n");
				print(" 	<tr>\n");
				print(" 		<th>Number of Records in Queue</th>\n");
				print(" 	</tr>\n");

				$sql =  "SELECT count(*) as RecCount FROM nsa.DCTRAN ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					print(" 	<tr>\n");
					print(" 		<th>" . $row['RecCount'] . "</th>\n");
					print(" 	</tr>\n");
				}
				print("	</table>\n");
				print("	</div>\n");
			} else {
				print "					<p class='warning'>Permission Denied!</p>\n";
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

	print(" </br>");
	PrintFooter("emenu.php");

?>
