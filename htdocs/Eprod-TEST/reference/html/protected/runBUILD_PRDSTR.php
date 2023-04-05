<?php
	$DEBUG = 0;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runBUILD_PRDSTR cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runBUILD_PRDSTR cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runBUILD_PRDSTR started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');




/////PSEUDO CODE
//		for each ID_ITEM_PAR that is in the OLD list {
//			set ID_ITEM_PAR = ID_ITEM_NEW
//			if ID_ITEM_COMP  is in OLD {
//				set ID_ITEM_COMP = ID_ITEM_NEW
//			}
//		}








			$sql  = "select * ";
			$sql .= " from nsa.PRDSTR ";
			$sql .= " right join nsa.ITMMAS_OLD_NEW n ";
			$sql .= " on ps.ID_ITEM_PAR = n.ID_ITEM_OLD ";













/*
			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);
			$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
			QueryDatabase($sql, $results);



			$QueryFileName = "QueryFile.txt";

			$handle = fopen($QueryFileName, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					error_log("### line: " . $line);
					//QueryDatabase($line, $results);
				}
			} else {
				// error opening the file.
				error_log("### ERROR OPENING " . $QueryFileName);
			}
			fclose($handle);


			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabase($sql, $results);

*/
			error_log("### runBUILD_PRDSTR finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runBUILD_PRDSTR cannot disconnect from database");
		}
	}
?>