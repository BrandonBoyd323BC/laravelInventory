<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	error_log("#############################################");
	error_log("### runAS400_TRACKING started at " . date('Y-m-d g:i:s a'));
	$retvalODBC = odbc_ConnectToDatabaseServer($odbc_DSN);
	if ($retvalODBC == 0) {
		error_log("### runAS400_TRACKING cannot connect to " . $odbc_DSN);
	} else {
		error_log("### runAS400_TRACKING CONNECTED to " . $odbc_DSN);
		$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			error_log("runAS400_TRACKING cannot connect to " . $db);
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				error_log("runAS400_TRACKING cannot select " . $dbName);
			} else {
				error_log("### runAS400_TRACKING querying TRACKING");

				$as400_sql  = "select ";
				$as400_sql .= " md.MDORD#, ";
				$as400_sql .= " c.CTACCT, ";
				$as400_sql .= " c.CTSHIP, ";
				$as400_sql .= " c.CTCTN#, ";
				$as400_sql .= " c.CTLIN#, ";
				$as400_sql .= " c.CTSTY, ";
				$as400_sql .= " c.CTSIZE, ";
				$as400_sql .= " c.CTDIM, ";
				$as400_sql .= " c.CTQTY, ";
				$as400_sql .= " t.DTRK, ";
				$as400_sql .= " mn.MMASDT ";
				$as400_sql .= " FROM ";
				$as400_sql .= " REED400.REEDDATA.RPUPSTRK t, ";
				$as400_sql .= " REED400.REEDDATA.ZPFCTN c, ";
				$as400_sql .= " REED400.REEDDATA.ZPFMND md, ";
				$as400_sql .= " REED400.REEDDATA.ZPFMNM mn ";
				$as400_sql .= " WHERE ";
				$as400_sql .= " md.MDCTN# = c.CTCTN# ";
				$as400_sql .= " AND c.CTCTN# = t.DTCTN# ";
				$as400_sql .= " AND md.MDMAN# = mn.MMMAN# ";
				//$as400_sql .= " AND ((md.MDACCT='AH615') AND (md.MDORD#=?)) ";
				$as400_sql .= " AND (md.MDACCT='AH615') ";
				$as400_sql .= " AND (mn.MMASDT>='20150801') ";
				$as400_sql .= " ORDER BY md.MDORD# ";
				//$as400_sql .= " fetch first 10 rows only ";
				odbc_QueryDatabase($as400_sql, $as400_results);

				$odbc_num_rows = odbc_num_rows($as400_results);
				if ($odbc_num_rows > 0) {

					error_log("### runAS400_TRACKING CHECKING FOR AS400_TRACKING_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_TRACKING_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_TRACKING_TEMP";
					QueryDatabase($sql, $results);

					error_log("### runAS400_TRACKING CREATING AS400_TRACKING_TEMP ");
					$sql  = "CREATE TABLE [nsa].[AS400_TRACKING_TEMP]( ";
					$sql .= " [MDORD#] [numeric](8,0) NOT NULL, "; 
					$sql .= " [CTACCT] [varchar](10) NOT NULL, ";
					$sql .= " [CTSHIP] [varchar](5) NOT NULL, "; 
					$sql .= " [CTCTN#] [numeric](8,0) NOT NULL, "; 
					$sql .= " [CTLIN#] [numeric](8,0) NOT NULL, "; 
					$sql .= " [CTSTY] [varchar](8) NOT NULL, "; 
					$sql .= " [CTSIZE] [char](3) NOT NULL, "; 
					$sql .= " [CTDIM] [char](3) NOT NULL, "; 
					$sql .= " [CTQTY] [numeric](6,0) NOT NULL, "; 
					$sql .= " [DTRK] [varchar](50) NOT NULL, "; 
					$sql .= " [MMASDT] [varchar](8) NOT NULL, ";
					$sql .= " [DATE_LAST_UPDATE] [datetime] NOT NULL, ";
					$sql .= " [rowid] [int] IDENTITY(1,1) NOT NULL, ";
					$sql .= " [rowversion] [timestamp] NOT NULL ";
					$sql .= ") ";
					QueryDatabase($sql, $results);

					$rownum = 0;
					error_log("### runAS400_TRACKING POPULATING nsa.AS400_TRACKING_TEMP with " . $odbc_num_rows . " records");
					while ($as400_row = odbc_fetch_array($as400_results)) {
						if (++$rownum % 100 == 0) {
							error_log("### runAS400_TRACKING row " . $rownum . " of " . $odbc_num_rows . " records");
						}

						//error_log($as400_row['XORD'] . " " . $as400_row['XNAME']);
						$sql2  = "INSERT INTO nsa.AS400_TRACKING_TEMP( ";
						$sql2 .= " MDORD#, "; 
						$sql2 .= " CTACCT, ";
						$sql2 .= " CTSHIP, "; 
						$sql2 .= " CTCTN#, "; 
						$sql2 .= " CTLIN#, "; 
						$sql2 .= " CTSTY, "; 
						$sql2 .= " CTSIZE, "; 
						$sql2 .= " CTDIM, "; 
						$sql2 .= " CTQTY, "; 
						$sql2 .= " DTRK, "; 
						$sql2 .= " MMASDT, ";
						$sql2 .= " DATE_LAST_UPDATE ";
						$sql2 .= ") VALUES ( ";
						$sql2 .= "'" . trim($as400_row['MDORD#']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTACCT']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTSHIP']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTCTN#']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTLIN#']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTSTY']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTSIZE']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTDIM']) . "', ";
						$sql2 .= "'" . trim($as400_row['CTQTY']) . "', ";
						$sql2 .= "'" . trim($as400_row['DTRK']) . "', ";
						$sql2 .= "'" . trim($as400_row['MMASDT']) . "', ";						
						$sql2 .= " GetDate() ";
						$sql2 .= ")";
						//error_log("query: " . $sql2);
						QueryDatabase($sql2, $results2);
					}

					error_log("### runAS400_TRACKING CHECKING FOR nsa.AS400_TRACKING ");
					$sql = " IF OBJECT_ID('nsa.AS400_TRACKING', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_TRACKING";
					QueryDatabase($sql, $results);

					error_log("### runAS400_TRACKING RENAMING nsa.AS400_TRACKING_TEMP to nsa.AS400_TRACKING ");
					$sql = " SP_RENAME 'nsa.AS400_TRACKING_TEMP','AS400_TRACKING'";
					QueryDatabase($sql, $results);

					error_log("### runAS400_TRACKING CHECKING FOR AS400_TRACKING_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_TRACKING_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_TRACKING_TEMP";
					QueryDatabase($sql, $results);

				}		
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runAS400_TRACKING cannot disconnect from " . $db);
		}

		odbc_close($odbc_db);
		error_log("### runAS400_TRACKING DISCONNECTED from AS400");
	}
	error_log("### runAS400_TRACKING finished at " . date('Y-m-d g:i:s a'));
	error_log("#############################################");


?>
