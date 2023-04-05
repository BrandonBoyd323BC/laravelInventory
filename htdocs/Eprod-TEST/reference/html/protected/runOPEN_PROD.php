<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runOPEN_PROD cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runOPEN_PROD cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runOPEN_PROD started at " . date('Y-m-d g:i:s a'));
			error_log("### runOPEN_PROD CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runOPEN_PROD' ";
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
				$sql .= "'runOPEN_PROD', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runOPEN_PROD SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

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

				error_log("### runOPEN_PROD REOPENING CLOSED PROD OPER");
				$sql  = "UPDATE nsa.SHPORD_OPER set STAT_REC_OPER_1 = 'A', STAT_REC_OPER = 'A' ";
				$sql .= " WHERE ltrim(ID_SO) = 'PROD' and (STAT_REC_OPER_1 = 'C' or STAT_REC_OPER = 'C')";
				QueryDatabase($sql, $results);

				error_log("### runOPEN_PROD REOPENING CLOSED SAMPLE1 OPER");
				$sql  = "UPDATE nsa.SHPORD_OPER set STAT_REC_OPER_1 = 'A', STAT_REC_OPER = 'A' ";
				$sql .= " WHERE ltrim(ID_SO) = 'SAMPLE1' and (STAT_REC_OPER_1 = 'C' or STAT_REC_OPER = 'C')";
				QueryDatabase($sql, $results);

				error_log("### runOPEN_PROD REOPENING CLOSED SAMPLE1 HDR");
				$sql  = "UPDATE nsa.SHPORD_HDR set STAT_REC_SO = 'R' ";
				$sql .= " WHERE ltrim(ID_SO) = 'SAMPLE1' and STAT_REC_SO = 'C'";
				QueryDatabase($sql, $results);


				//error_log("### runOPEN_PROD REOPENING CLOSED SAMPLE OPER");
				//$sql  = "UPDATE nsa.SHPORD_OPER set STAT_REC_OPER_1 = 'A', STAT_REC_OPER = 'A' ";
				//$sql .= " WHERE ltrim(ID_SO) like 'SAMPLE%' and (STAT_REC_OPER_1 = 'C' or STAT_REC_OPER = 'C')";
				//$sql .= " AND ltrim(ID_SO) not in ('SAMPLE62','SAMPLE63')";
				//QueryDatabase($sql, $results);

				//error_log("### runOPEN_PROD SWITCHING SAMPLE OPER to INDIRECT");
				//$sql  = "UPDATE nsa.SHPORD_OPER set FLAG_DIR_INDIR = 'I' ";
				//$sql .= " WHERE ltrim(ID_SO) like 'S%' and FLAG_DIR_INDIR = 'D' ";
				//QueryDatabase($sql, $results);

				error_log("### runOPEN_PROD REOPENING CLOSED PROD HDR");
				$sql  = "UPDATE nsa.SHPORD_HDR set STAT_REC_SO = 'R' ";
				$sql .= " WHERE ltrim(ID_SO) = 'PROD' and STAT_REC_SO = 'C'";
				QueryDatabase($sql, $results);

				//error_log("### runOPEN_PROD REOPENING CLOSED SAMPLE HDR");
				//$sql  = "UPDATE nsa.SHPORD_HDR set STAT_REC_SO = 'R' ";
				//$sql .= " WHERE ltrim(ID_SO) like 'SAMPLE%' and STAT_REC_SO = 'C'";
				//$sql .= " AND ltrim(ID_SO) not in ('SAMPLE62','SAMPLE63')";
				//QueryDatabase($sql, $results);

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runOPEN_PROD DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runOPEN_PROD ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runOPEN_PROD finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runOPEN_PROD cannot disconnect from database");
		}
	}
?>
