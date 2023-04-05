<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("csvSF_Accts_SHIPTO cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("csvSF_Accts_SHIPTO cannot select " . $dbName);
		} else {
			$filename = "NSA_SF_SHIPTO_". date('Ymd-His') .".csv";
			error_log("#############################################");
			error_log("### csvSF_Accts_SHIPTO started at " . date('Y-m-d g:i:s a'));

			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment;filename='.$filename);
			$fp = fopen('php://output', 'w');

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);

			$sql  = "	select ";
			$sql .= "		(NAME_CUST + '  __' + cast(ltrim(ID_CUST) as varchar(6)) + '_' + cast(SEQ_SHIPTO as varchar(4))) As Name, ";
			$sql .= "		(cast(ltrim(ID_CUST) as varchar(6)) + '_' + cast(SEQ_SHIPTO as varchar(4))) as AccountNumber, ";
			//$sql .= "		(ADDR_CUST_1 + ' ' + ADDR_CUST_2 + ' ' + ADDR_CUST_3 + ' ' + ADDR_CUST_4) as BillingAddress, ";
			$sql .= "		PHONE_FAX as Fax, ";
			$sql .= "		(cast(ltrim(ID_CUST) as varchar(6)) + '_0')  as Parent, ";
			$sql .= "		PHONE as Phone, ";
			$sql .= "		ADDR_CUST_1 as AddressShip1, ";
			$sql .= "		ADDR_CUST_2 as AddressShip2, ";
			$sql .= "		ADDR_CUST_3 as AddressShip3, ";
			$sql .= "		ADDR_CUST_4 as AddressShip4, ";
			$sql .= "		ADDR_CUST_2 as STREET, ";
			$sql .= "		CITY, ";
			$sql .= "		ID_ST, ";
			$sql .= "		ZIP, ";
			$sql .= "		PROV, ";
			$sql .= "		COUNTRY, ";
			//$sql .= "	--	ID_CUST_BILLTO as Customer_Bill_To, ";
			$sql .= "		ID_CUST as ID_CUST, ";
			//$sql .= "		('SOLDTO_' + cast(rowid as varchar(10))) as Row_ID, ";
			$sql .= "		rowid as Row_ID, ";
			$sql .= "		SEQ_SHIPTO as Seq_Shipto, ";
			$sql .= "		ID_TERR as Territory ";
			$sql .= "	from nsa.CUSMAS_SHIPTO  ";
			$sql .= "	where  ";
			$sql .= "		ltrim(NAME_CUST) <> ''  ";
			$sql .= "		and ltrim(NAME_CUST) not like ('%DO NOT%')  ";
			$sql .= "	Order by Name asc ";
			QueryDatabase($sql, $results);

			$colNamesA = array();
			for($i = 0; $i < mssql_num_fields($results); $i++) {
			    $field_info = mssql_fetch_field($results, $i);
			    $field = $field_info->name;
			    $colNamesA[$i] =  $field;
			}
 			fputcsv($fp, $colNamesA);

			while ($row = mssql_fetch_assoc($results)) {
/*
				$Address1Stripped = str_replace("ATTN: ACCTS. PAYABLE","",$row['AddressShip1']);
				$Address1Stripped = str_replace("ATTN:  ACCTS. PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN:  ACCTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN: ACCTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN ACCTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN: ACCOUNTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN:ACCOUNTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN ACCOUNTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN: ACCOUNT PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN:  ACCOUNTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN ACCCOUNTS PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("Attn:  Accounts Payable","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN: ACCT PAYABLE","",$Address1Stripped);
				$Address1Stripped = str_replace("ATTN: ACCTS. PAYABEL","",$Address1Stripped);
				$Address1Stripped = str_replace("Attn:ACCOUNTS PAYABLE","",$Address1Stripped);
				//$Address1Stripped = str_replace("ACCOUNTS PAYABLE:","",$Address1Stripped);
				//$Address1Stripped = str_replace("ACCOUNTS PAYABLE","",$Address1Stripped);
				$row['AddressShip1'] = $Address1Stripped;
*/

				fputcsv($fp, $row, ",", "\"");
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
			error_log("### csvSF_Accts_SHIPTO finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("csvSF_Accts_SHIPTO cannot disconnect from database");
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