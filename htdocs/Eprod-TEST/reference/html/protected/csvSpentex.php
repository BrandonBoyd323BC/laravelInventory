<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("csvSF_Accts_SOLDTO cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("csvSpentex cannot select " . $dbName);
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["ID_ORD"])) {
				error_log("ID_ORD: " . $_POST["ID_ORD"]);
				$ID_ORD = stripNonANChars($_POST["ID_ORD"]);
				$sql  = " SELECT ltrim(ID_CUST_SOLDTO) as ID_CUST_SOLDTO ";
				$sql .= " from nsa.CP_ORDHDR ";
				$sql .= " where ltrim(ID_ORD) = '". $ID_ORD ."'";
				QueryDatabase($sql, $results);
				if (mssql_num_rows($results) < 1) {
					print("<h1>ORDER NOT FOUND</h1>");
				}
				while ($row = mssql_fetch_assoc($results)) {
					if (strpos($row['ID_CUST_SOLDTO'],'S') === false) {
						print("<h1>NOT A SPENTEX CUSTOMER ORDER</h1>");
						error_log("NOT A SPENTEX CUSTOMER ORDER");
					} else {
						$filename = "NSA_SPX_". $ID_ORD .".csv";
						header( 'Content-Type: text/csv' );
						header( 'Content-Disposition: attachment;filename='.$filename);
						$fp = fopen('php://output', 'w');

						$sql1  = " SELECT ";
						$sql1 .= "  oh.ID_ORD, ";
						$sql1 .= "  oh.ID_PO_CUST, ";
						$sql1 .= "  oh.DATE_ORD, ";
						$sql1 .= "  ol.DATE_PROM, ";
						$sql1 .= "  ol.ID_ITEM ";
						$sql1 .= " FROM ";
						$sql1 .= "  nsa.CP_ORDLIN ol ";
						$sql1 .= "  left join nsa.CP_ORDHDR oh ";
						$sql1 .= "  on ol.ID_ORD = oh.ID_ORD ";
						$sql1 .= " WHERE ltrim(oh.ID_ORD) = '". $ID_ORD ."' ";

						QueryDatabase($sql1, $results1);
						while ($row1 = mssql_fetch_assoc($results1)) {
							//$row1['ID_ORD'] = $incent_dollar;
							//$row1['ID_PO_CUST'] = $VacIncDollars;
							//$row1['DATE_ORD'] = $HolIncDollars;
							fputcsv($fp, $row1, ",", "\"");
						}
						fclose($fp);
					}
				}
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("csvSpentex cannot disconnect from database");
		}
	}

    function query_to_csv($db_conn, $query, $filename, $attachment = false, $headers = true) {
        if($attachment) {
            // send response headers to the browser
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
            $fp = fopen('php://output', 'w');
        } else {
            $fp = fopen($filename, 'w');
        }
        $result = mysql_query($query, $db_conn) or die( mysql_error( $db_conn ) );

        if($headers) {
            // output header row (if at least one row exists)
            $row = mysql_fetch_assoc($result);
            if($row) {
                fputcsv($fp, array_keys($row));
                // reset pointer back to beginning
                mysql_data_seek($result, 0);
            }
        }
        while($row = mysql_fetch_assoc($result)) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
?>