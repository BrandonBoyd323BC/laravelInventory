<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runUPDATE_ORDER_BINS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runUPDATE_ORDER_BINS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runUPDATE_ORDER_BINS started at " . date('Y-m-d g:i:s a'));
			error_log("### runUPDATE_ORDER_BINS CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runUPDATE_ORDER_BINS' ";
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
				$sql .= "'runUPDATE_ORDER_BINS', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runUPDATE_ORDER_BINS SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

				error_log("### runUPDATE_ORDER_BINS UPDATING ORDER BINS WITH LOCATION BINS");
				$sql  = "UPDATE ol ";
				$sql .= " SET ol.BIN_PRIM = il.BIN_PRIM ";
				$sql .= " FROM nsa.CP_ORDLIN as ol ";
				$sql .= " LEFT JOIN nsa.ITMMAS_LOC as il ";
				$sql .= " on ol.ID_ITEM = il.ID_ITEM ";
				$sql .= " and ol.ID_LOC = il.ID_LOC ";
				$sql .= " WHERE ol.BIN_PRIM <> il.BIN_PRIM ";
				QueryDatabase($sql, $results);

				/*
				--CHECKS FOR MISMATCHED ORDER BINS
				
				select ol.ID_ITEM, ol.BIN_PRIM, il.ID_ITEM, il.BIN_PRIM
				FROM nsa.CP_ORDLIN as ol
				LEFT JOIN nsa.ITMMAS_LOC as il
				on ol.ID_ITEM = il.ID_ITEM
				and ol.ID_LOC = il.ID_LOC
				WHERE ol.BIN_PRIM <> il.BIN_PRIM
				*/

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runUPDATE_ORDER_BINS DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runUPDATE_ORDER_BINS ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runUPDATE_ORDER_BINS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runUPDATE_ORDER_BINS cannot disconnect from database");
		}
	}
?>
