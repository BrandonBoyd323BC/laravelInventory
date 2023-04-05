<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCLEAR_FLAG_PICK cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCLEAR_FLAG_PICK cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runCLEAR_FLAG_PICK started at " . date('Y-m-d g:i:s a'));
			error_log("### runCLEAR_FLAG_PICK CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			$today = date('Ymd');

			$sql  = "SELECT * FROM nsa.RUNNING_PROC ";
			$sql .= " WHERE PROC_NAME = 'runCLEAR_FLAG_PICK' ";
			$sql .= " and FLAG_RUNNING = '1' ";
			$sql .= " and DATE_EXP > getDate()";
			QueryDatabase($sql, $results);

			if (mssql_num_rows($results) == 0) {
				$sql1  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql1 .= " PROC_NAME, ";
				$sql1 .= " FLAG_RUNNING, ";
				$sql1 .= " DATE_ADD, ";
				$sql1 .= " DATE_EXP ";
				$sql1 .= ") VALUES ( ";
				$sql1 .= "'runCLEAR_FLAG_PICK', ";
				$sql1 .= "1, ";
				$sql1 .= " getDate(), ";
				$sql1 .= " dateadd(minute,5,getDate()) ";
				$sql1 .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql1, $results1);
				$row1 = mssql_fetch_assoc($results1);
				$ProcRowID = $row1['LAST_INSERT_ID'];
				error_log("### runCLEAR_FLAG_PICK SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql1 = "SET ANSI_NULLS ON";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET QUOTED_IDENTIFIER ON";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_PADDING ON";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET CONCAT_NULL_YIELDS_NULL ON";
				QueryDatabase($sql1, $results1);



				error_log("### RESETTING CP_ORDHDR.FLAG_PICK TO 1 WHERE EQUALS 2");
				$sql1 = "UPDATE nsa.CP_ORDHDR set FLAG_PICK = 1 WHERE FLAG_PICK = 2";
				QueryDatabase($sql1, $results1);

				error_log("### RESETTING CP_ORDLIN.FLAG_PICK TO 1 WHERE EQUALS 2");
				$sql1 = "UPDATE nsa.CP_ORDLIN set FLAG_PICK = 1 WHERE FLAG_PICK = 2";
				QueryDatabase($sql1, $results1);


				$sql1 = "SET ANSI_NULLS OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_PADDING OFF";
				QueryDatabase($sql1, $results1);


				error_log("### runCLEAR_FLAG_PICK DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runCLEAR_FLAG_PICK ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runCLEAR_FLAG_PICK finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCLEAR_FLAG_PICK cannot disconnect from database");
		}
	}
?>
