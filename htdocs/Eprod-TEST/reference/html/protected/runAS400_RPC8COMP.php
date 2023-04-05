<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	error_log("#############################################");
	error_log("### runAS400_RPC8COMP started at " . date('Y-m-d g:i:s a'));
	$retvalODBC = odbc_ConnectToDatabaseServer($odbc_DSN);
	if ($retvalODBC == 0) {
		error_log("### runAS400_RPC8COMP cannot connect to " . $odbc_DSN);
	} else {
		error_log("### runAS400_RPC8COMP CONNECTED to " . $odbc_DSN);
		$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			error_log("runAS400_RPC8COMP cannot connect to " . $db);
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				error_log("runAS400_RPC8COMP cannot select " . $dbName);
			} else {
				error_log("### runAS400_RPC8COMP querying RPC8COMP");
				$as400_sql  = "select ";
				$as400_sql .= " r.* ";
				$as400_sql .= " FROM REED400.REEDDATA.RPC8COMP r ";
				$as400_sql .= " ORDER BY r.ZORD ";
				odbc_QueryDatabase($as400_sql, $as400_results);

				$odbc_num_rows = odbc_num_rows($as400_results);
				if ($odbc_num_rows > 0) {

					error_log("### runAS400_RPC8COMP CHECKING FOR AS400_RPC8COMP_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPC8COMP_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPC8COMP_TEMP";
					QueryDatabase($sql, $results);

					error_log("### runAS400_RPC8COMP CREATING AS400_RPC8COMP_TEMP ");
					$sql  = "CREATE TABLE [nsa].[AS400_RPC8COMP_TEMP]( ";
					$sql .= " [ZORD] [numeric](8,0) NOT NULL, ";
					$sql .= " [ZSTAT] [varchar](30) NOT NULL, ";
					$sql .= " [DATE_LAST_UPDATE] [datetime] NOT NULL, ";
					$sql .= " [rowid] [int] IDENTITY(1,1) NOT NULL, ";
					$sql .= " [rowversion] [timestamp] NOT NULL ";
					$sql .= ") ";
					QueryDatabase($sql, $results);

					$rownum = 0;
					error_log("### runAS400_RPC8COMP POPULATING nsa.AS400_RPC8COMP_TEMP with " . $odbc_num_rows . " records");
					while ($as400_row = odbc_fetch_array($as400_results)) {
						if (++$rownum % 50 == 0) {
							error_log("### runAS400_RPC8COMP row " . $rownum . " of " . $odbc_num_rows . " records");
						}

						//error_log($as400_row['XORD'] . " " . $as400_row['XNAME']);
						$sql2  = "INSERT INTO nsa.AS400_RPC8COMP_TEMP( ";
						$sql2 .= " ZORD, ";
						$sql2 .= " ZSTAT, ";
						$sql2 .= " DATE_LAST_UPDATE ";
						$sql2 .= ") VALUES ( ";
						$sql2 .= "'" . trim($as400_row['ZORD']) . "', ";
						$sql2 .= "'" . trim($as400_row['ZSTAT']) . "', ";
						$sql2 .= " GetDate() ";
						$sql2 .= ")";
						//error_log("query: " . $sql2);
						QueryDatabase($sql2, $results2);
					}

					error_log("### runAS400_RPC8COMP CHECKING FOR nsa.AS400_RPC8COMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPC8COMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPC8COMP";
					QueryDatabase($sql, $results);

					error_log("### runAS400_RPC8COMP RENAMING nsa.AS400_RPC8COMP_TEMP to nsa.AS400_RPC8COMP ");
					$sql = " SP_RENAME 'nsa.AS400_RPC8COMP_TEMP','AS400_RPC8COMP'";
					QueryDatabase($sql, $results);

					error_log("### runAS400_RPC8COMP CHECKING FOR AS400_RPC8COMP_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPC8COMP_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPC8COMP_TEMP";
					QueryDatabase($sql, $results);

				}		
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runAS400_RPC8COMP cannot disconnect from " . $db);
		}

		odbc_close($odbc_db);
		error_log("### runAS400_RPC8COMP DISCONNECTED from AS400");
	}
	error_log("### runAS400_RPC8COMP finished at " . date('Y-m-d g:i:s a'));
	error_log("#############################################");


?>
