<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM cannot select " . $dbName);
		} else {
			$date_now = date("Y-m-d"); // this format is string comparable

			error_log("#############################################");
			error_log("### Less than ");
			error_log("### runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM started at " . date('Y-m-d g:i:s a'));
			error_log("### runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM' ";
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
				$sql .= "'runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

				error_log("### runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM CHECKING AND DROPPING TABLE ");
				$sql = " IF OBJECT_ID('nsa.CP_INVLIN_HIST_1YR_ITEM_LOC_SUM', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.CP_INVLIN_HIST_1YR_ITEM_LOC_SUM";
				QueryDatabase($sql, $results);

				$sql  = "SELECT ";
				$sql .= " ID_ITEM, ";
				$sql .= " ID_LOC, ";
				$sql .= " sum(QTY_SHIP) as SUM_QTY_SHIP, ";
				$sql .= " getDate() as REFRESHED_DATE  ";
				$sql .= " into nsa.CP_INVLIN_HIST_1YR_ITEM_LOC_SUM ";
				$sql .= " from nsa.CP_INVLIN_HIST WITH (NOLOCK) ";
				$sql .= " where DATE_ADD > DATEADD(year,-1,GETDATE()) ";
				$sql .= " group by ID_ITEM, ID_LOC ";
				$sql .= " order by ID_ITEM, ID_LOC ";
				QueryDatabase($sql, $results);


				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runPOPULATE_CP_INVLIN_HIST_1YR_ITEM_LOC_SUM cannot disconnect from database");
		}
	}
?>
