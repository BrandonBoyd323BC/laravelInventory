<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";

	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Stock List','default.css','stklist.js');
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
			print(" </br>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Show <select id='sel_group_filter' onChange=\"showTable()\" >\n");
			print("					<option value=''>--SELECT--</option>\n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			
			$sql  = "SELECT DISTINCT [group], ";
			$sql .= " cc.comm_desc_full ";
			$sql .= " FROM nsa.ITMMAS_STK_LIST". $DB_TEST_FLAG." sl ";
			$sql .= " LEFT JOIN nsa.cus_comm_code_full". $DB_TEST_FLAG." cc ";
			$sql .= " on sl.[group] = cc.comm_code ";
			$sql .= " ORDER BY sl.[group] asc";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				//print("					<option value='" . $row['group'] . "' " . $SELECTED . ">" . $row['group'] . " - " . $row['comm_desc_full'] . "</option>\n");
				print("					<option value='" . $row['group'] . "' >" . $row['group'] . " - " . $row['comm_desc_full'] . "</option>\n");
			}
			print("			</select></th>\n");
			//print(" 		<th colspan=2>ITEM Lookup<input id='searchItem' name='searchItem' type='text' value='ALL' onblur=\"showTable()\"></input><th>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
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
