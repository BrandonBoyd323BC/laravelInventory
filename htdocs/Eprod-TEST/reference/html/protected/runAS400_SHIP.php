<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	error_log("#############################################");
	error_log("### runAS400_SHIP started at " . date('Y-m-d g:i:s a'));
	$retvalODBC = odbc_ConnectToDatabaseServer($odbc_DSN);
	if ($retvalODBC == 0) {
		error_log("### runAS400_SHIP cannot connect to " . $odbc_DSN);
	} else {
		error_log("### runAS400_SHIP CONNECTED to " . $odbc_DSN);
		$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			error_log("runAS400_SHIP cannot connect to " . $db);
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				error_log("runAS400_SHIP cannot select " . $dbName);
			} else {
				$sql = "select top 1 VIND as DateParam from nsa.AS400_RPAHSHP order by VIND desc ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$DateParam = $row['DateParam'];
				}
				//$DateParam	= "20150731";
				error_log("### runAS400_SHIP querying RPAHSHP > " . $DateParam);
				$as400_sql  = "select ";
/*				$as400_sql .= " s.VINV, "; 
				$as400_sql .= " s.VSLD, ";
				$as400_sql .= " s.VOTYP, "; 
				$as400_sql .= " s.VSHP, "; 
				$as400_sql .= " s.VNAME, "; 
				$as400_sql .= " s.VORD, "; 
				$as400_sql .= " s.VPO, "; 
				$as400_sql .= " s.VIND, "; 
				$as400_sql .= " s.VSTY, "; 
				$as400_sql .= " s.VSIZE, "; 
				$as400_sql .= " s.VDIM, "; 
				$as400_sql .= " s.VSKU, "; 
				$as400_sql .= " s.VPRC, "; 
				$as400_sql .= " s.VQTY, "; 
				$as400_sql .= " s.VMER, "; 
				$as400_sql .= " s.VFRT, "; 
				$as400_sql .= " s.VNMC, "; 
				$as400_sql .= " s.VCMT, "; 
				$as400_sql .= " s.VENT, "; 
				$as400_sql .= " s.VUSER, "; 
				$as400_sql .= " s.VPRID ";
*/
				$as400_sql .= " s.* ";
				$as400_sql .= " FROM REED400.REEDDATA.RPAHSHP s ";
				$as400_sql .= " WHERE (s.VIND > '" . $DateParam . "') ";
				$as400_sql .= " ORDER BY s.VINV ";
				odbc_QueryDatabase($as400_sql, $as400_results);

				$odbc_num_rows = odbc_num_rows($as400_results);
				if ($odbc_num_rows > 0) {

/*
					error_log("### runAS400_SHIP CHECKING FOR AS400_RPAHSHP_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPAHSHP_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPAHSHP_TEMP";
					QueryDatabase($sql, $results);

					error_log("### runAS400_SHIP CREATING AS400_RPAHSHP_TEMP ");
					$sql  = "CREATE TABLE [nsa].[AS400_RPAHSHP_TEMP]( ";
					
					$sql .= " [VINV] [numeric](8,0) NOT NULL, "; 
					$sql .= " [VSLD] [varchar](10) NOT NULL, ";
					$sql .= " [VOTYP] [varchar](2) NOT NULL, "; 
					$sql .= " [VSHP] [varchar](5) NOT NULL, "; 
					$sql .= " [VNAME] [varchar](50) NOT NULL, "; 
					$sql .= " [VORD] [numeric](8,0) NOT NULL, "; 
					$sql .= " [VPO] [varchar](30) NULL, "; 
					//$sql .= " [VIND] [datetime] NOT NULL, "; //20150817
					$sql .= " [VIND] [varchar](8) NOT NULL, "; //20150817
					$sql .= " [VSTY] [varchar](8) NOT NULL, "; 
					$sql .= " [VSIZE] [char](3) NOT NULL, "; 
					$sql .= " [VDIM] [char](3) NOT NULL, "; 
					$sql .= " [VSKU] [varchar](20) NOT NULL, "; 
					$sql .= " [VPRC] [numeric](8,2) NOT NULL, "; 
					$sql .= " [VQTY] [numeric](6,0) NOT NULL, "; 
					$sql .= " [VMER] [numeric](8,2) NOT NULL, "; 
					$sql .= " [VFRT] [numeric](8,2) NOT NULL, "; 
					$sql .= " [VNMC] [numeric](8,2) NOT NULL, "; 
					$sql .= " [VCMT] [varchar](30) NOT NULL, "; 
					$sql .= " [VENT] [datetime] NOT NULL, "; 
					$sql .= " [VUSER] [varchar](15) NOT NULL, "; 
					$sql .= " [VPRID] [datetime] NOT NULL,";
					$sql .= " [DATE_LAST_UPDATE] [datetime] NOT NULL, ";
					$sql .= " [rowid] [int] IDENTITY(1,1) NOT NULL, ";
					$sql .= " [rowversion] [timestamp] NOT NULL ";
					$sql .= ") ";
					QueryDatabase($sql, $results);
*/
					$rownum = 0;
					//error_log("### runAS400_SHIP POPULATING nsa.AS400_RPAHSHP_TEMP with " . $odbc_num_rows . " records");
					error_log("### runAS400_SHIP POPULATING nsa.AS400_RPAHSHP with " . $odbc_num_rows . " records");
					while ($as400_row = odbc_fetch_array($as400_results)) {
						if (++$rownum % 50 == 0) {
							error_log("### runAS400_SHIP row " . $rownum . " of " . $odbc_num_rows . " records");
						}

						//error_log($as400_row['XORD'] . " " . $as400_row['XNAME']);
						//$sql2  = "INSERT INTO nsa.AS400_RPAHSHP_TEMP( ";
						$sql2  = "INSERT INTO nsa.AS400_RPAHSHP( ";
						$sql2 .= " VINV, "; 
						$sql2 .= " VSLD, ";
						$sql2 .= " VOTYP, "; 
						$sql2 .= " VSHP, "; 
						$sql2 .= " VNAME, "; 
						$sql2 .= " VORD, "; 
						$sql2 .= " VPO, "; 
						$sql2 .= " VIND, "; 
						$sql2 .= " VSTY, "; 
						$sql2 .= " VSIZE, "; 
						$sql2 .= " VDIM, "; 
						$sql2 .= " VSKU, "; 
						$sql2 .= " VPRC, "; 
						$sql2 .= " VQTY, "; 
						$sql2 .= " VMER, "; 
						$sql2 .= " VFRT, "; 
						$sql2 .= " VNMC, "; 
						$sql2 .= " VCMT, "; 
						$sql2 .= " VENT, "; 
						$sql2 .= " VUSER, "; 
						$sql2 .= " VPRID, ";
						$sql2 .= " DATE_LAST_UPDATE ";
						$sql2 .= ") VALUES ( ";
						$sql2 .= "'" . trim($as400_row['VINV']) . "', ";
						$sql2 .= "'" . trim($as400_row['VSLD']) . "', ";
						$sql2 .= "'" . trim($as400_row['VOTYP']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VSHP'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VNAME'])) . "', ";
						$sql2 .= "'" . trim($as400_row['VORD']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VPO'])) . "', ";
						$sql2 .= "'" . trim($as400_row['VIND']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VSTY'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VSIZE'])) . "', ";
						$sql2 .= "'" . trim($as400_row['VDIM']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VSKU'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VPRC'])) . "', ";
						$sql2 .= "'" . trim($as400_row['VQTY']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VMER'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VFRT'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VNMC'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VCMT'])) . "', ";
						$sql2 .= "'" . trim($as400_row['VENT']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['VUSER'])) . "', ";
						$sql2 .= "'" . trim($as400_row['VPRID']) . "', ";
						$sql2 .= " GetDate() ";
						$sql2 .= ")";
						//error_log("query: " . $sql2);
						QueryDatabase($sql2, $results2);
					}
/*
					error_log("### runAS400_SHIP CHECKING FOR nsa.AS400_RPAHSHP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPAHSHP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPAHSHP";
					QueryDatabase($sql, $results);

					error_log("### runAS400_SHIP RENAMING nsa.AS400_RPAHSHP_TEMP to nsa.AS400_RPAHSHP ");
					$sql = " SP_RENAME 'nsa.AS400_RPAHSHP_TEMP','AS400_RPAHSHP'";
					QueryDatabase($sql, $results);

					error_log("### runAS400_SHIP CHECKING FOR AS400_RPAHSHP_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPAHSHP_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPAHSHP_TEMP";
					QueryDatabase($sql, $results);
*/
				}		
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runAS400_SHIP cannot disconnect from " . $db);
		}

		odbc_close($odbc_db);
		error_log("### runAS400_SHIP DISCONNECTED from AS400");
	}
	error_log("### runAS400_SHIP finished at " . date('Y-m-d g:i:s a'));
	error_log("#############################################");
?>
