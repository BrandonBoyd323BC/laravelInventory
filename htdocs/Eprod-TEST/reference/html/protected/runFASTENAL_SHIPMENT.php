<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runFASTENAL_SHIPMENT cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runFASTENAL_SHIPMENT cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runFASTENAL_SHIPMENT started at " . date('Y-m-d g:i:s a'));
			error_log("### runFASTENAL_SHIPMENT CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runFASTENAL_SHIPMENT' ";
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
				$sql .= "'runFASTENAL_SHIPMENT', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,1,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runFASTENAL_SHIPMENT SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

				error_log("### runFASTENAL_SHIPMENT CHECKING Location and Reord ");


				///////////////////////////////////////
				////////////	Fastenal
				//////////////////////////////////////

				$sql  = " select "; 
				$sql .= "  sh.ID_ORD, "; 
				$sql .= "  sh.ID_SHIP, ";
				$sql .= "  sh.ID_USER_ADD, ";
				$sql .= "  oh.ID_USER_ADD as ORD_TAKER,";
				$sql .= "  oh.ID_PO_CUST, ";
				$sql .= "  Convert(varchar(10), sh.DATE_ADD, 101) as DATE_ADD3, ";
				$sql .= "  sh.DESCR_SHIP_VIA, ";
				$sql .= "  sh.CODE_SHIP_VIA_CP, ";
				$sql .= "  sh.TIME_ADD ";
				$sql .= " from nsa.CP_SHPHDR sh ";
				$sql .= " left join nsa.CP_ORDHDR_PERM oh";
				$sql .= "  on oh.ID_ORD = sh.ID_ORD";
				$sql .= " left join nsa.fastenal_shipments fs ";
				$sql .= "  on sh.ID_ORD = fs.ID_ORD ";
				$sql .= "  and sh.ID_SHIP = fs.ID_SHIP ";
				$sql .= " where sh.NAME_CUST like '%Fastenal%' "; 
				$sql .= "  and fs.ID_ORD is NULL "; 
				$sql .= "  and fs.ID_SHIP is NULL "; 
				$sql .= "  and ltrim(sh.CODE_SHIP_VIA_CP) in ('16', '71') "; 
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {

					$date = $row['DATE_ADD3'] . " " .str_pad($row['TIME_ADD'],6 , "0", STR_PAD_LEFT);
					$date1 = strtotime($date);
					$formatted_date = date('m/d/Y h:i:s A',$date1);

					$subject = "Fastenal Order Shipment Created: " . $row['ID_ORD'];
					$body = "A new Fastenal Shipment has been created. Details are below." .
						"\r\n\r\nORDER #: " . $row['ID_ORD'] . "\r\nCUST PO #: " . $row['ID_PO_CUST'] . "\r\nSHIPMENT #: " . $row['ID_SHIP'] . 
						"\r\nEntered By: " . $row['ORD_TAKER'] . "\r\nOn: " .  $formatted_date . "\r\nShipping Method: " . $row['DESCR_SHIP_VIA'];
						

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					error_log("### ORDER: " . $row['ID_ORD']);
					$to = "customerservice@thinknsa.com";
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);

					$sql1  = " INSERT INTO nsa.fastenal_shipments( "; 
					$sql1 .= "  ID_ORD, ";
					$sql1 .= "  ID_SHIP "; 
					$sql1 .= " ) VALUES ( ";
					$sql1 .= " " .$row['ID_ORD'] .", ";
					$sql1 .= " " .$row['ID_SHIP'] .") ";
					QueryDatabase($sql1, $results1);
					
				}

			
				///////////////////////////////////
				/////  PEPCO
				///////////////////////////////////

				$sql  = " select "; 
				$sql .= "  sh.ID_ORD, "; 
				$sql .= "  sh.ID_SHIP, ";
				$sql .= "  sh.ID_USER_ADD, ";
				$sql .= "  oh.ID_USER_ADD as ORD_TAKER,";
				$sql .= "  oh.ID_PO_CUST, ";
				$sql .= "  Convert(varchar(10), sh.DATE_ADD, 101) as DATE_ADD3, ";
				$sql .= "  sh.DESCR_SHIP_VIA, ";
				$sql .= "  sh.CODE_SHIP_VIA_CP, ";
				$sql .= "  sh.TIME_ADD ";
				$sql .= " from nsa.CP_SHPHDR sh ";
				$sql .= " left join nsa.CP_ORDHDR_PERM oh";
				$sql .= "  on oh.ID_ORD = sh.ID_ORD";
				$sql .= " left join nsa.fastenal_shipments fs ";
				$sql .= "  on sh.ID_ORD = fs.ID_ORD ";
				$sql .= "  and sh.ID_SHIP = fs.ID_SHIP ";
				$sql .= " where sh.NAME_CUST like '%PEPCO%' ";
				$sql .= "  and fs.ID_ORD is NULL "; 
				$sql .= "  and fs.ID_SHIP is NULL ";
				$sql .= "  and ltrim(sh.CODE_SHIP_VIA_CP) in ('16', '71') "; 

				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {

					$date = $row['DATE_ADD3'] . " " .str_pad($row['TIME_ADD'],6 , "0", STR_PAD_LEFT);
					$date1 = strtotime($date);
					$formatted_date = date('m/d/Y h:i:s A',$date1);

					$subject = "PEPCO Order Ready for pick up: " . $row['ID_ORD'];
					$body = "A new PEPCO order is ready. Details are below." .
						"\r\n\r\nORDER #: " . $row['ID_ORD'] . "\r\nCUST PO #: " . $row['ID_PO_CUST'] . "\r\nSHIPMENT #: " . $row['ID_SHIP'] . 
						"\r\nEntered By: " . $row['ORD_TAKER'] . "\r\nOn: " .  $formatted_date . "\r\nShipping Method: " . $row['DESCR_SHIP_VIA'];
						

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
							   "X-Mailer: PHP/" . phpversion();

					$to = "group-csr@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);

					$to = "psobus@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);


					$sql1  = " INSERT INTO nsa.fastenal_shipments( "; 
					$sql1 .= "  ID_ORD, ";
					$sql1 .= "  ID_SHIP "; 
					$sql1 .= " ) VALUES ( ";
					$sql1 .= " " .$row['ID_ORD'] .", ";
					$sql1 .= " " .$row['ID_SHIP'] .") ";
					QueryDatabase($sql1, $results1);
					
				}

				/////////////////////////////////////////
				/////////////	Darling
				////////////////////////////////////////

				$sql  = " select "; 
				$sql .= "  sh.ID_ORD, "; 
				$sql .= "  sh.ID_SHIP, ";
				$sql .= "  sh.ID_USER_ADD, ";
				$sql .= "  oh.ID_USER_ADD as ORD_TAKER,";
				$sql .= "  oh.ID_PO_CUST, ";
				$sql .= "  Convert(varchar(10), sh.DATE_ADD, 101) as DATE_ADD3, ";
				$sql .= "  sh.DESCR_SHIP_VIA, ";
				$sql .= "  sh.CODE_SHIP_VIA_CP, ";
				$sql .= "  sh.TIME_ADD ";
				$sql .= " from nsa.CP_SHPHDR sh ";
				$sql .= " left join nsa.CP_ORDHDR_PERM oh";
				$sql .= "  on oh.ID_ORD = sh.ID_ORD";
				$sql .= " left join nsa.fastenal_shipments fs ";
				$sql .= "  on sh.ID_ORD = fs.ID_ORD ";
				$sql .= "  and sh.ID_SHIP = fs.ID_SHIP ";
				$sql .= " where sh.NAME_CUST like '%DARLING%' ";
				$sql .= "  and fs.ID_ORD is NULL "; 
				$sql .= "  and fs.ID_SHIP is NULL "; 
				$sql .= "  and ltrim(sh.CODE_SHIP_VIA_CP) in ('16', '71') "; 

				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {

					$date = $row['DATE_ADD3'] . " " .str_pad($row['TIME_ADD'],6 , "0", STR_PAD_LEFT);
					$date1 = strtotime($date);
					$formatted_date = date('m/d/Y h:i:s A',$date1);

					$subject = "DARLING SAFETY & FIRE OrderReady for pick up: " . $row['ID_ORD'];
					$body = "A new DARLING Order is ready. Details are below." .
						"\r\n\r\nORDER #: " . $row['ID_ORD'] . "\r\nCUST PO #: " . $row['ID_PO_CUST'] . "\r\nSHIPMENT #: " . $row['ID_SHIP'] . 
						"\r\nEntered By: " . $row['ORD_TAKER'] . "\r\nOn: " .  $formatted_date . "\r\nShipping Method: " . $row['DESCR_SHIP_VIA'];
						

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
							   "CC: tleckrone@thinknsa.com";
							   "X-Mailer: PHP/" . phpversion();

					$to = "group-csr@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);

					$to = "psobus@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);


					$sql1  = " INSERT INTO nsa.fastenal_shipments( "; 
					$sql1 .= "  ID_ORD, ";
					$sql1 .= "  ID_SHIP "; 
					$sql1 .= " ) VALUES ( ";
					$sql1 .= " " .$row['ID_ORD'] .", ";
					$sql1 .= " " .$row['ID_SHIP'] .") ";
					QueryDatabase($sql1, $results1);
					
				}

				
				///////////////////////////////////////////
				/////////COLONY HARDWARE
				//////////////////////////////////////////
				$sql  = " select "; 
				$sql .= "  sh.ID_ORD, "; 
				$sql .= "  sh.ID_SHIP, ";
				$sql .= "  sh.ID_USER_ADD, ";
				$sql .= "  oh.ID_USER_ADD as ORD_TAKER,";
				$sql .= "  oh.ID_PO_CUST, ";
				$sql .= "  Convert(varchar(10), sh.DATE_ADD, 101) as DATE_ADD3, ";
				$sql .= "  sh.DESCR_SHIP_VIA, ";
				$sql .= "  sh.CODE_SHIP_VIA_CP, ";
				$sql .= "  sh.TIME_ADD ";
				$sql .= " from nsa.CP_SHPHDR sh ";
				$sql .= " left join nsa.CP_ORDHDR_PERM oh";
				$sql .= "  on oh.ID_ORD = sh.ID_ORD";
				$sql .= " left join nsa.fastenal_shipments fs ";
				$sql .= "  on sh.ID_ORD = fs.ID_ORD ";
				$sql .= "  and sh.ID_SHIP = fs.ID_SHIP ";
				$sql .= " WHERE sh.ID_CUST_SOLDTO = '454955' ";
				$sql .= "  and fs.ID_ORD is NULL "; 
				$sql .= "  and fs.ID_SHIP is NULL ";
				$sql .= "  and ltrim(sh.CODE_SHIP_VIA_CP) in ('16', '71') "; 

				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {

					$date = $row['DATE_ADD3'] . " " .str_pad($row['TIME_ADD'],6 , "0", STR_PAD_LEFT);
					$date1 = strtotime($date);
					$formatted_date = date('m/d/Y h:i:s A',$date1);

					$subject = "COLONY HARDWARE Order Ready for pick up: " . $row['ID_ORD'];
					$body = "A new COLONY HARDWARE order is ready. Details are below." .
						"\r\n\r\nORDER #: " . $row['ID_ORD'] . "\r\nCUST PO #: " . $row['ID_PO_CUST'] . "\r\nSHIPMENT #: " . $row['ID_SHIP'] . 
						"\r\nEntered By: " . $row['ORD_TAKER'] . "\r\nOn: " .  $formatted_date . "\r\nShipping Method: " . $row['DESCR_SHIP_VIA'];
						

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
							   "X-Mailer: PHP/" . phpversion();

					$to = "group-csr@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);

					$to = "psobus@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);

					$sql1  = " INSERT INTO nsa.fastenal_shipments( "; 
					$sql1 .= "  ID_ORD, ";
					$sql1 .= "  ID_SHIP "; 
					$sql1 .= " ) VALUES ( ";
					$sql1 .= " " .$row['ID_ORD'] .", ";
					$sql1 .= " " .$row['ID_SHIP'] .") ";
					QueryDatabase($sql1, $results1);	
				}//end while


				/////////////////////////////////////////
				/////////////	International
				////////////////////////////////////////

				$sql  = " select "; 
				$sql .= "  sh.ID_ORD, "; 
				$sql .= "  sh.ID_SHIP, ";
				$sql .= "  sh.ID_USER_ADD, ";
				$sql .= "  oh.ID_USER_ADD as ORD_TAKER, ";
				$sql .= "  oh.ID_PO_CUST, ";
				$sql .= "  oh.NAME_CUST, ";
				$sql .= "  ltrim(oh.ID_SLSREP_1) as ID_SLSREP_1, ";
				$sql .= "  Convert(varchar(10), sh.DATE_ADD, 101) as DATE_ADD3, ";
				$sql .= "  sh.DESCR_SHIP_VIA, ";
				$sql .= "  sh.CODE_SHIP_VIA_CP, ";
				$sql .= "  sh.TIME_ADD ";
				$sql .= " from nsa.CP_SHPHDR sh ";
				$sql .= " left join nsa.CP_ORDHDR_PERM oh";
				$sql .= "  on oh.ID_ORD = sh.ID_ORD";
				$sql .= " left join nsa.fastenal_shipments fs ";
				$sql .= "  on sh.ID_ORD = fs.ID_ORD ";
				$sql .= "  and sh.ID_SHIP = fs.ID_SHIP ";
				$sql .= " where ltrim(oh.ID_SLSREP_1) in ('91','96','99','104') ";
				$sql .= "  and fs.ID_ORD is NULL "; 
				$sql .= "  and fs.ID_SHIP is NULL "; 
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {

					$date = $row['DATE_ADD3'] . " " .str_pad($row['TIME_ADD'],6 , "0", STR_PAD_LEFT);
					$date1 = strtotime($date);
					$formatted_date = date('m/d/Y h:i:s A',$date1);

					$subject = "International order for " . $row['NAME_CUST'] . " has been scanned out for shipment: " . $row['ID_ORD'];
					$body = "A new International Order " . $row['NAME_CUST'] . " is ready. Details are below." .
						"\r\n\r\nORDER #: " . $row['ID_ORD'] . "\r\nCUST PO #: " . $row['ID_PO_CUST'] . "\r\nSHIPMENT #: " . $row['ID_SHIP'] . 
						"\r\nEntered By: " . $row['ORD_TAKER'] . "\r\nOn: " .  $formatted_date . "\r\nShipping Method: " . $row['DESCR_SHIP_VIA'];
						

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
							   //"CC: tleckrone@thinknsa.com";
							   "X-Mailer: PHP/" . phpversion();

					$to = "jgrossman@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);

					/*$to = "bgrabowski@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);
					
					
					if ($row['ID_SLSREP_1'] == '96') {
						$to = "MFigueroa2@thinknsa.com";
						error_log("### ORDER: " . $row['ID_ORD']);
						if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
							$to = "gvandyne@thinknsa.com";
						}
						mail($to, $subject, $body, $headers);
						error_log("### MAIL SENT TO: " . $to);
					}
					*/

					$to = "aklein@thinknsa.com";
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);



					$sql1  = " INSERT INTO nsa.fastenal_shipments( "; 
					$sql1 .= "  ID_ORD, ";
					$sql1 .= "  ID_SHIP "; 
					$sql1 .= " ) VALUES ( ";
					$sql1 .= " " .$row['ID_ORD'] .", ";
					$sql1 .= " " .$row['ID_SHIP'] .") ";
					QueryDatabase($sql1, $results1);
					
				}


				

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runFASTENAL_SHIPMENT DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runFASTENAL_SHIPMENT ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runFASTENAL_SHIPMENT finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runFASTENAL_SHIPMENT cannot disconnect from database");
		}
	}
?>
