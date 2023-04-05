<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	error_log("#############################################");
	error_log("### runAS400_ASHBR started at " . date('Y-m-d g:i:s a'));
	$retvalODBC = odbc_ConnectToDatabaseServer($odbc_DSN);
	if ($retvalODBC == 0) {
		error_log("### runAS400_ASHBR cannot connect to " . $odbc_DSN);
	} else {
		error_log("### runAS400_ASHBR CONNECTED to " . $odbc_DSN);
		$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			error_log("runAS400_ASHBR cannot connect to " . $db);
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				error_log("runAS400_ASHBR cannot select " . $dbName);
			} else {
				error_log("### runAS400_ASHBR querying RPASHBR");
				$as400_sql  = "select ";
				/*
				$as400_sql .= " RPASHBR.XLSU, ";
				$as400_sql .= " RPASHBR.XSHP, ";
				$as400_sql .= " RPASHBR.XCITY, ";
				$as400_sql .= " RPASHBR.XPO, ";
				$as400_sql .= " RPASHBR.XORD, ";
				$as400_sql .= " RPASHBR.XLIN, ";
				$as400_sql .= " RPASHBR.XENT, ";
				$as400_sql .= " RPASHBR.XPROM, ";
				$as400_sql .= " RPASHBR.XOTYP, ";
				$as400_sql .= " RPASHBR.XSTY, ";
				$as400_sql .= " RPASHBR.XSIZE, ";
				$as400_sql .= " RPASHBR.XDIM, ";
				$as400_sql .= " RPASHBR.XQTY, ";
				$as400_sql .= " RPASHBR.XNAME, ";
				$as400_sql .= " RPASHBR.XCMT, ";
				$as400_sql .= " RPASHBR.XDST, ";
				$as400_sql .= " RPASHBR.XENTBY, ";
				$as400_sql .= " RPASHBR.XREG, ";
				$as400_sql .= " RPASHBR.XPRID ";
				*/
				$as400_sql .= " RPASHBR.* ";
				$as400_sql .= " FROM REED400.REEDDATA.RPASHBR RPASHBR ";
				$as400_sql .= " ORDER BY RPASHBR.XCITY, RPASHBR.XPO ";
				odbc_QueryDatabase($as400_sql, $as400_results);

				$odbc_num_rows = odbc_num_rows($as400_results);
				if ($odbc_num_rows > 0) {

					error_log("### runAS400_ASHBR CHECKING FOR AS400_RPASHBR_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPASHBR_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPASHBR_TEMP";
					QueryDatabase($sql, $results);

					error_log("### runAS400_ASHBR CREATING AS400_RPASHBR_TEMP ");
					$sql  = "CREATE TABLE [nsa].[AS400_RPASHBR_TEMP]( ";
					$sql .= " [XORD] [numeric](8,0) NOT NULL, ";
					$sql .= " [XLIN] [numeric](3,0) NOT NULL, ";
					$sql .= " [XPO] [varchar](30) NULL, ";
					$sql .= " [XOTYP] [varchar](2) NOT NULL, ";
					$sql .= " [XSLD] [varchar](10) NOT NULL, ";
					$sql .= " [XSHP] [varchar](5) NOT NULL, ";
					$sql .= " [XCITY] [varchar](30) NOT NULL, ";
					$sql .= " [XSTY] [varchar](30) NOT NULL, ";
					$sql .= " [XSIZE] [char](3) NOT NULL, ";
					$sql .= " [XDIM] [char](3) NOT NULL, ";
					$sql .= " [XCMT] [varchar](30) NOT NULL, ";
					$sql .= " [XQTY] [numeric](6,0) NOT NULL, ";
					$sql .= " [XENT] [datetime] NOT NULL, ";
					$sql .= " [XPROM] [datetime] NULL, ";
					$sql .= " [XNAME] [varchar](50) NOT NULL, ";
					$sql .= " [XDST] [char](1) NULL, ";
					$sql .= " [XENTBY] [varchar](15) NOT NULL, ";
					$sql .= " [XREG] [numeric] (1,0) NOT NULL, ";
					$sql .= " [XLSU] [datetime] NULL, ";
					$sql .= " [XPRID] [datetime] NULL, ";
					$sql .= " [XCMT1] [varchar](30) NULL, ";
					$sql .= " [XCMT2] [varchar](30) NULL, ";
					$sql .= " [XCMT3] [varchar](30) NULL, ";
					$sql .= " [XCMT4] [varchar](30) NULL, ";
					$sql .= " [XCMT5] [varchar](30) NULL, ";
					$sql .= " [XCMT6] [varchar](30) NULL, ";
					$sql .= " [DATE_LAST_UPDATE] [datetime] NOT NULL, ";
					$sql .= " [rowid] [int] IDENTITY(1,1) NOT NULL, ";
					$sql .= " [rowversion] [timestamp] NOT NULL ";
					$sql .= ") ";
					QueryDatabase($sql, $results);

					$rownum = 0;
					error_log("### runAS400_ASHBR POPULATING nsa.AS400_RPASHBR_TEMP with " . $odbc_num_rows . " records");
					while ($as400_row = odbc_fetch_array($as400_results)) {
						if (++$rownum % 50 == 0) {
							error_log("### runAS400_ASHBR row " . $rownum . " of " . $odbc_num_rows . " records");
						}

						//error_log($as400_row['XORD'] . " " . $as400_row['XNAME']);
						$sql2  = "INSERT INTO nsa.AS400_RPASHBR_TEMP( ";
						$sql2 .= " XORD, ";
						$sql2 .= " XLIN, ";
						$sql2 .= " XPO, ";
						$sql2 .= " XOTYP, ";
						$sql2 .= " XSLD, ";
						$sql2 .= " XSHP, ";
						$sql2 .= " XCITY, ";
						$sql2 .= " XSTY, ";
						$sql2 .= " XSIZE, ";
						$sql2 .= " XDIM, ";
						$sql2 .= " XCMT, ";
						$sql2 .= " XQTY, ";
						$sql2 .= " XENT, ";
						$sql2 .= " XPROM, ";
						$sql2 .= " XNAME, ";
						$sql2 .= " XDST, ";
						$sql2 .= " XENTBY, ";
						$sql2 .= " XREG, ";
						$sql2 .= " XLSU, ";
						$sql2 .= " XPRID, ";
						$sql2 .= " XCMT1, ";
						$sql2 .= " XCMT2, ";
						$sql2 .= " XCMT3, ";
						$sql2 .= " XCMT4, ";
						$sql2 .= " XCMT5, ";
						$sql2 .= " XCMT6, ";
						$sql2 .= " DATE_LAST_UPDATE ";
						$sql2 .= ") VALUES ( ";
						$sql2 .= "'" . trim($as400_row['XORD']) . "', ";
						$sql2 .= "'" . trim($as400_row['XLIN']) . "', ";
						$sql2 .= "'" . trim($as400_row['XPO']) . "', ";
						$sql2 .= "'" . trim($as400_row['XOTYP']) . "', ";
						$sql2 .= "'" . trim($as400_row['XSLD']) . "', ";
						$sql2 .= "'" . trim($as400_row['XSHP']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCITY'])) . "', ";
						$sql2 .= "'" . trim($as400_row['XSTY']) . "', ";
						$sql2 .= "'" . trim($as400_row['XSIZE']) . "', ";
						$sql2 .= "'" . trim($as400_row['XDIM']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCMT'])) . "', ";
						$sql2 .= "'" . trim($as400_row['XQTY']) . "', ";
						$sql2 .= "'" . trim($as400_row['XENT']) . "', ";
						$sql2 .= "'" . trim($as400_row['XPROM']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XNAME'])) . "', ";
						$sql2 .= "'" . trim($as400_row['XDST']) . "', ";
						$sql2 .= "'" . trim($as400_row['XENTBY']) . "', ";
						$sql2 .= "'" . trim($as400_row['XREG']) . "', ";
						$sql2 .= "'" . trim($as400_row['XLSU']) . "', ";
						$sql2 .= "'" . trim($as400_row['XPRID']) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCMT1'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCMT2'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCMT3'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCMT4'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCMT5'])) . "', ";
						$sql2 .= "'" . trim(str_replace("'","",$as400_row['XCMT6'])) . "', ";
						$sql2 .= " GetDate() ";
						$sql2 .= ")";
						//error_log("query: " . $sql2);
						QueryDatabase($sql2, $results2);
					}

					error_log("### runAS400_ASHBR CHECKING FOR nsa.AS400_RPASHBR ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPASHBR', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPASHBR";
					QueryDatabase($sql, $results);

					error_log("### runAS400_ASHBR RENAMING nsa.AS400_RPASHBR_TEMP to nsa.AS400_RPASHBR ");
					$sql = " SP_RENAME 'nsa.AS400_RPASHBR_TEMP','AS400_RPASHBR'";
					QueryDatabase($sql, $results);

					error_log("### runAS400_ASHBR CHECKING FOR AS400_RPASHBR_TEMP ");
					$sql = " IF OBJECT_ID('nsa.AS400_RPASHBR_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE nsa.AS400_RPASHBR_TEMP";
					QueryDatabase($sql, $results);

				}		
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runAS400_ASHBR cannot disconnect from " . $db);
		}

		odbc_close($odbc_db);
		error_log("### runAS400_ASHBR DISCONNECTED from AS400");
	}
	error_log("### runAS400_ASHBR finished at " . date('Y-m-d g:i:s a'));
	error_log("#############################################");


?>
