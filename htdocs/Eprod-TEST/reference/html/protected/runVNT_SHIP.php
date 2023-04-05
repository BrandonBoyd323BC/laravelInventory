<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runVNT_SHIP cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runVNT_SHIP cannot select " . $dbName);
		} else {
			$filename = "/tmp/ups/NSA_UPS_FF.csv";
			error_log("#############################################");
			error_log("### runVNT_SHIP started at " . date('Y-m-d g:i:s a'));
			$fp = fopen($filename, 'w');

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);

			$sql  = "select ";
			$sql .= "	sh.ZIP, ";
			$sql .= "	sa.ACCT_SHIP_VIA_CP, ";
			$sql .= "	sh.ID_ST, ";
			$sql .= "	sh.ADDR_2, ";
			$sql .= "	sh.ADDR_1, ";
			$sql .= "	sh.NAME_CUST_SHIPTO, ";
			$sql .= "	sh.CODE_SHIP_VIA_CP, ";
			$sql .= "	sh.COUNTRY, ";
			$sql .= "	sh.CITY, ";
			$sql .= "	sh.CODE_SHIP_VIA_CP, ";
			$sql .= "	sh.ID_ORD, ";
			$sql .= "	sh.ID_PO_CUST, ";
			$sql .= "	sh.ID_SHIP ";
			$sql .= " from  ";
			$sql .= "	nsa.CP_SHPHDR sh, ";
			$sql .= "	nsa.CP_SHP_SHIP_ACCT sa ";
			$sql .= " where  ";
			$sql .= "	sh.ID_SHIP = sa.ID_SHIP ";
			$sql .= "	and ID_CUST_SOLDTO like 'V%' ";

			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				fputcsv($fp, $row, ";", "\"");
			}

			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabase($sql, $results);

			fclose($fp);
			error_log("### runVNT_SHIP finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runWC_OPEN cannot disconnect from database");
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