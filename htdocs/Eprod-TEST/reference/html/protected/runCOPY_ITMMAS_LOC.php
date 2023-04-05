<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCOPY_ITMMAS_LOC cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCOPY_ITMMAS_LOC cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runCOPY_ITMMAS_LOC started at " . date('Y-m-d g:i:s a'));
			error_log("### runCOPY_ITMMAS_LOC CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			$today = date('Ymd');

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runCOPY_ITMMAS_LOC' ";
			$sql .= "	and ";
			$sql .= "	FLAG_RUNNING = '1' ";
			$sql .= "	and ";
			$sql .= "	DATE_EXP > getDate()";
			QueryDatabase($sql, $results);

			if (mssql_num_rows($results) == 0) {
				$sql  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql .= " PROC_NAME, ";
				$sql .= " FLAG_RUNNING, ";
				$sql .= " DATE_ADD, ";
				$sql .= " DATE_EXP ";
				$sql .= ") VALUES ( ";
				$sql .= "'runCOPY_ITMMAS_LOC', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runCOPY_ITMMAS_LOC SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				error_log("### COPYING nsa.ITMMAS_LOC to nsa.ITMMAS_LOC_" . $today);
				$sql1 = "select * into nsa.ITMMAS_LOC_".$today." from nsa.ITMMAS_LOC";
				QueryDatabase($sql1, $results1);

				error_log("### COPYING nsa.CP_ORDHDR to nsa.CP_ORDHDR_" . $today);
				$sql1 = "select * into nsa.CP_ORDHDR_".$today." from nsa.CP_ORDHDR";
				QueryDatabase($sql1, $results1);

				error_log("### COPYING nsa.CP_ORDLIN to nsa.CP_ORDLIN_" . $today);
				$sql1 = "select * into nsa.CP_ORDLIN_".$today." from nsa.CP_ORDLIN";
				QueryDatabase($sql1, $results1);				

				error_log("### runCOPY_ITMMAS_LOC DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runCOPY_ITMMAS_LOC ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runCOPY_ITMMAS_LOC finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCOPY_ITMMAS_LOC cannot disconnect from database");
		}
	}
?>
