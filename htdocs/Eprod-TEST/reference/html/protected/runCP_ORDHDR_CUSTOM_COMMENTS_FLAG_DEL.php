<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL started at " . date('Y-m-d g:i:s a'));
			error_log("### runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			$today = date('Ymd');

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL' ";
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
				$sql .= "'runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				error_log("### runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL SETTING FLAG_DEL FOR OLD");
				$sql1  = "UPDATE nsa.CP_ORDHDR_CUSTOM_COMMENTS ";
				$sql1 .= " SET FLAG_DEL = 'D' ";
				$sql1 .= " FROM nsa.CP_ORDHDR_CUSTOM_COMMENTS cc ";
				$sql1 .= " LEFT JOIN ( ";
				$sql1 .= "  SELECT max(DATE_INVC) as MAX_DATE_INVC, ID_ORD ";
				$sql1 .= "  FROM nsa.CP_INVHDR_HIST ";
				$sql1 .= "  WHERE DATE_INVC > '2019-01-01' ";
				$sql1 .= "  GROUP BY ID_ORD ";
				$sql1 .= " ) ih ";
				$sql1 .= " on cc.ID_ORD = ih.ID_ORD ";
				$sql1 .= " WHERE (ih.MAX_DATE_INVC >= CONVERT(DATE,cc.DATE_ADD) AND ih.MAX_DATE_INVC >= CONVERT(DATE,cc.DATE_CHG))  ";
				QueryDatabase($sql1, $results1);
		

				error_log("### runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCP_ORDHDR_CUSTOM_COMMENTS_FLAG_DEL cannot disconnect from database");
		}
	}
?>
