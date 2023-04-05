<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCHANGE_2FT_BREAKS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCHANGE_2FT_BREAKS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runCHANGE_2FT_BREAKS started at " . date('Y-m-d g:i:s a'));
			error_log("### runCHANGE_2FT_BREAKS CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			$today = date('Ymd');

			$sql  = "SELECT * FROM nsa.RUNNING_PROC ";
			$sql .= " WHERE PROC_NAME = 'runCHANGE_2FT_BREAKS' ";
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
				$sql1 .= "'runCHANGE_2FT_BREAKS', ";
				$sql1 .= "1, ";
				$sql1 .= " getDate(), ";
				$sql1 .= " dateadd(minute,5,getDate()) ";
				$sql1 .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql1, $results1);
				$row1 = mssql_fetch_assoc($results1);
				$ProcRowID = $row1['LAST_INSERT_ID'];
				error_log("### runCHANGE_2FT_BREAKS SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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



				
/*
				select CODE_TRX, * from nsa.DCUTRX_ZERO 
				where CODE_SHIFT = '2FT' 
				and CODE_TRX = '105'
				and DATE_TRX = CAST( GETDATE() AS Date )
				and DATENAME(WEEKDAY, GETDATE()) = 'Friday'
				and TIME_TRX = '173500'
				order by ID_BADGE asc, DATE_TRX asc, TIME_TRX asc
*/
				error_log("### UPDATING nsa.DCUTRX_ZERO ");

				$sql1  = "UPDATE nsa.DCUTRX_ZERO ";
				$sql1 .= " SET TIME_TRX = '171000', TIME_CORR_TRX = '171000' ";
				$sql1 .= " WHERE CODE_SHIFT = '2FT' ";
				$sql1 .= " and CODE_TRX = '105' ";
				$sql1 .= " and DATE_TRX = CAST( GETDATE() AS Date ) ";
				$sql1 .= " and DATENAME(WEEKDAY, GETDATE()) = 'Friday' ";
				$sql1 .= " and TIME_TRX = '173500' ";
				QueryDatabase($sql1, $results1);


				$sql1 = "SET ANSI_NULLS OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_PADDING OFF";
				QueryDatabase($sql1, $results1);


				error_log("### runCHANGE_2FT_BREAKS DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runCHANGE_2FT_BREAKS ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runCHANGE_2FT_BREAKS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCHANGE_2FT_BREAKS cannot disconnect from database");
		}
	}
?>
