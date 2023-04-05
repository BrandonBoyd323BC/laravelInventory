<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runMTOwROP cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runMTOwROP cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runMTOwROP started at " . date('Y-m-d g:i:s a'));
			error_log("### runMTOwROP CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runMTOwROP' ";
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
				$sql .= "'runMTOwROP', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runMTOwROP SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

				error_log("### runMTOwROP CHECKING Location and Reord ");

				$sql  = " select "; 
				$sql .= "  L.ID_ITEM, "; 
				$sql .= "  L.FLAG_PLCY_ORD, ";
				$sql .= "  R.LEVEL_ROP, "; 
				$sql .= "  R.ID_USER_ADD, ";
				$sql .= "  R.DATE_ADD, ";
				$sql .= "  CONVERT(varchar(10), L.DATE_ADD, 101) as DATE_ADD1 ";
				$sql .= " from nsa.itmmas_loc L ";
				$sql .= " left join nsa.itmmas_reord R ";
				$sql .= "  on R.id_item = L.id_item ";
				$sql .= "  and L.ID_LOC = R.ID_LOC_HOME ";
				$sql .= " where L.flag_stk = 'N' "; 
				$sql .= "  and R.level_rop >0 "; 
				$sql .= "  and L.FLAG_SOURCE = 'M' "; 
				$sql .= "  and L.id_item not like 'I %' ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					//$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$DATE = $row['DATE_ADD1'];

					$subject = "MTO Item setup with ROP: " . $row['ID_ITEM'];
					$body = "An item was recently configured as Made to Order with a Reorder Point. Please correct this so these emails will stop." .
						"\r\n\r\nItem: " . $row['ID_ITEM'] . "\r\nLevel ROP: " . $row['LEVEL_ROP'] . "\r\nOrder Policy Flag: " . $row['FLAG_PLCY_ORD'] .
						"\r\nEntered By: " . $row['ID_USER_ADD'] . "\r\nOn: " . $DATE;

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					$to = "group-pd@thinknsa.com";
					error_log("### USER: " . $row['ID_USER_ADD']);
					error_log("### ITEM: " . $row['ID_ITEM']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);
					$to = "gvandyne@thinknsa.com";
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);
				}

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runMTOwROP DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runMTOwROP ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runMTOwROP finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runMTOwROP cannot disconnect from database");
		}
	}
?>
