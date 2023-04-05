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
			error_log("csvSF_Contacts cannot select " . $dbName);
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["df"]) && isset($_POST["dt"]))  {			
				$filename = "PayTrx_". date('Ymd-His') .".csv";
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename='.$filename);
				$fp = fopen('php://output', 'w');
			
				$DateFrom 		= str_replace("-","",$_POST["df"]);
				$DateTo 		= str_replace("-","",$_POST["dt"]);

				$sqlp  = " SELECT ";
				$sqlp .= " 	distinct p.CODE_PAY_DC ";
				$sqlp .= " FROM nsa.PAYTRX p ";
				$sqlp .= " WHERE ";
				$sqlp .= " 	p.FLAG_APPRV in ('Y','A') ";
				$sqlp .= " 	and ltrim(CODE_PAY_DC) <> '' ";
				$sqlp .= " order by p.CODE_PAY_DC asc ";
				QueryDatabase($sqlp, $resultsp);

				$sql  = " SELECT ";
				$sql .= " 	p.ID_BADGE, ";
				$sql .= " 	e.NAME_EMP, ";
				while ($rowp = mssql_fetch_assoc($resultsp)) {
					$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = '". $rowp['CODE_PAY_DC'] ."') then p.HR_PAID else 0 end) as '". trim($rowp['CODE_PAY_DC']) ."', ";
				}
				$sql .= " 	e.NAME_EMP as NAME_EMP2 ";
				$sql .= " FROM nsa.PAYTRX p ";
				$sql .= " 	left join nsa.DCEMMS_EMP e ";
				$sql .= " 	on p.ID_BADGE = e.ID_BADGE ";
				$sql .= " 	and e.CODE_ACTV = 0 ";
				$sql .= " WHERE p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."' ";
				$sql .= " 	and p.FLAG_APPRV in ('Y','A') ";
				$sql .= " GROUP BY p.ID_BADGE, e.NAME_EMP ";
				$sql .= " ORDER BY p.ID_BADGE asc ";
				QueryDatabase($sql, $results);
				
				$colNamesA = array();
				for($i = 0; $i < mssql_num_fields($results); $i++) {
				    $field_info = mssql_fetch_field($results, $i);
				    $field = $field_info->name;
				    $colNamesA[$i] =  $field;
				}
				fputcsv($fp, $colNamesA);				

				while ($row = mssql_fetch_assoc($results)) {
					fputcsv($fp, $row, ",", "\"");
				}
				fclose($fp);
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("csvSF_Contacts cannot disconnect from database");
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
