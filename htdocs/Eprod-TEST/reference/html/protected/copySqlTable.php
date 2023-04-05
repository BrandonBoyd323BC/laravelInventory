<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Copy SQL Table','default.css','copySqlTable.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			print("		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n");

			print("<div id='div_" . $ID_BADGE . "' name='div_" . $ID_BADGE . "'>\n");
			print(" <table class='sample'>\n");
			
			print(" 	<tr>\n");
			print(" 		<th>Table Name</th>\n");
			print(" 		<th></th>\n");
			print(" 		<th></th>\n");
			print(" 	</tr>\n");


			$sql  = " SELECT TABLE_SCHEMA, TABLE_NAME ";
			$sql .= " FROM INFORMATION_SCHEMA.TABLES ";
			$sql .= " WHERE TABLE_TYPE='BASE TABLE' ";
			$sql .= " and TABLE_SCHEMA = 'nsa' ";
			$sql .= " ORDER BY TABLE_NAME asc ";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				$TABLE_NAME = trim($row['TABLE_NAME']);
				$TABLE_SCHEMA = trim($row['TABLE_SCHEMA']);

				print(" 	<tr>\n");
				print(" 		<td>\n");
				print("				<font>".$TABLE_SCHEMA.".".$TABLE_NAME."</font>\n");
				print(" 		</td>\n");
				print(" 		<td>\n");
				print("				<input id='button_".$TABLE_NAME."' type='button' value='Copy' onClick='copyTableButtonClick('".$TABLE_NAME."')'></input>\n");
				print(" 		</td>\n");
				print(" 		<td>\n");
				print(" 		</td>\n");
				print(" 	</tr>\n");

			}

			print(" </table>\n");
			print(" 	</br>\n");
			print(" </div>\n");

			print(" <div id='dataDiv'></div>\n");
			//print(" <div id='backgroundPopup'></div>\n");
			//print(" <div id='dataPopup2'></div>\n");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

?>
