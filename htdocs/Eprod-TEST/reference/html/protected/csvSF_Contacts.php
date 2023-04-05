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
			$filename = "NSA_SF_CONTACTS_". date('Ymd-His') .".csv";
			error_log("#############################################");
			error_log("### csvSF_Contacts started at " . date('Y-m-d g:i:s a'));

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

			//TYPE_REC_CUST = '1' SOLDTO
			//TYPE_REC_CUST = '2' SHIPTO

			$sql  = "	select ";
			$sql .= "		ltrim(c.ID_CUST) as ID_CUST, ";
			$sql .= "		ltrim(c.SEQ_SHIPTO) as SEQ_SHIPTO, ";
			$sql .= "		(cast(ltrim(c.ID_CUST) as varchar(6)) + '_' + cast(c.SEQ_SHIPTO as varchar(4))) as ID_CUST_SEQ_SHIPTO, ";
			$sql .= "		(cast(ltrim(c.ID_CUST) as varchar(6)) + '_' + cast(c.SEQ_SHIPTO as varchar(4)) + '_' + cast(ltrim(c.ID_CONTACT) as varchar(6))) as ID_CUST_SEQ_SHIPTO_ID_CONTACT, ";
			$sql .= "		c.NAME_CONTACT As FULL_NAME_CONTACT, ";
			$sql .= "		c.TITLE_CONTACT as TITLE_CONTACT, ";
			$sql .= "		c.PHONE_BUSINESS as PHONE_BUSINESS, ";
			$sql .= "		c.PHONE_BUSINESS_2 as PHONE_BUSINESS_2, ";
			$sql .= "		c.PHONE_MOBILE as PHONE_MOBILE, ";
			$sql .= "		c.PHONE_PAGER as PHONE_PAGER, ";
			$sql .= "		c.PHONE_OTHER as PHONE_OTHER, ";
			$sql .= "		c.PHONE_FAX as PHONE_FAX, ";
			$sql .= "		lower(c.EMAIL_CONTACT) as EMAIL_CONTACT, ";
			$sql .= "		lower(c.WEB_SITE) as WEB_SITE, ";
			$sql .= "		c.rowid as ROW_ID ";
			$sql .= "	from nsa.CM_CONTACTS_CUSTOMER c ";
			$sql .= "	where  ";
			$sql .= "		FLAG_TYPE_CONTACT = 'C'  ";
			$sql .= "	Order by ID_CUST asc, FULL_NAME_CONTACT asc ";
			QueryDatabase($sql, $results);

			$colNamesA = array();
			for($i = 0; $i < mssql_num_fields($results); $i++) {
			    $field_info = mssql_fetch_field($results, $i);
			    $field = $field_info->name;
			    $colNamesA[$i] =  $field;
			}
 			fputcsv($fp, $colNamesA);

			while ($row = mssql_fetch_assoc($results)) {
				if ($row['PHONE_OTHER'] == 'Y') {
					$row['PHONE_OTHER'] = '';
				}
/*
				$Address1Stripped = str_replace("ATTN: ACCTS. PAYABLE","",$row['AddressSold1']);
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
				$row['AddressSold1'] = $Address1Stripped;
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
			error_log("### csvSF_Contacts finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
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