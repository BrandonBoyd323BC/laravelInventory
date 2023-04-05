<?php
	$DEBUG = 0;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_BOOKINGS_DRIFIRE cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS_DRIFIRE cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runDAILY_BOOKINGS_DRIFIRE started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');
			//$today = '11-14-16';

			mssql_query("SET ANSI_NULLS ON"); 
			mssql_query("SET ANSI_WARNINGS ON");

			////////////////////////////////////
			/////////BOOKINGS STUFF
			///////////////////////////////////
			$sql = "select CONCAT(bh.ID_TERR, ' - ', sr.NAME_SLSREP) as ID_TERR, count(distinct bh.ID_ORD) as NUM_BOOKINGS, SUM(bl.SLS) as SLS_BOOKINGS from nsa.BOKHST_HDR bh left join nsa.BOKHST_LINE bl on bh.ID_ORD = bl.ID_ORD left join nsa.tables_slsrep sr on bh.ID_TERR = sr.ID_SLSREP where (bh.ID_CUST like 'D%') and (bl.DATE_BOOK_LAST = '".$today."') and (bh.ID_CUST != 'D00000') and (bh.ID_TERR in ('101','102','103','104')) group by CONCAT(bh.ID_TERR, ' - ', sr.NAME_SLSREP)";
			QueryDatabase($sql, $results);

			$sqlT = "select count(distinct bh.ID_ORD) as TOTAL_BOOK, SUM(bl.SLS) as TOTAL_SLS from nsa.BOKHST_HDR bh left join nsa.BOKHST_LINE bl on bh.ID_ORD = bl.ID_ORD left join nsa.tables_slsrep sr on bh.ID_TERR = sr.ID_SLSREP where (bh.ID_CUST like 'D%') and (bl.DATE_BOOK_LAST = '".$today."') and (bh.ID_CUST != 'D00000') and (bh.ID_TERR in ('101','102','103','104'))";
			QueryDatabase($sqlT, $resultsT);
			

			$subject = "DRIFIRE Daily Summary for  " . $today;

			$body  = "Bookings on " . $today . ".";
			while ($row = mssql_fetch_assoc($results)) {
				if ($row['NUM_BOOKINGS'] <> '0' OR $row['SLS_BOOKINGS'] <> '0') {				
					$body .= "\r\n Territory: " . $row['ID_TERR'] . ",   Number of Orders:  " . $row['NUM_BOOKINGS'] . " orders,   Bookings: " . money_format('%(n',$row['SLS_BOOKINGS']);	
				}
			}//end while

			while ($row = mssql_fetch_assoc($resultsT)) {
				$body .= "\r\n Total Orders: " . $row['TOTAL_BOOK'] . ",   Total Bookings: " . money_format('%(n',$row['TOTAL_SLS']);
			}//end while

			//////////////////////////////////
			////SHIPMENT STUFF
			/////////////////////////////////
			$sql2 = " select CONCAT(ID_SLSREP_1, ' - ', sr.NAME_SLSREP) as ID_TERR, COUNT(distinct ID_ORD) as NUM_SHIPMENTS, SUM(AMT_ORD_TOTAL) as SLS_SHIPMENTS from nsa.CP_SHPHDR_PERM left join nsa.tables_slsrep sr on ID_SLSREP_1 = sr.ID_SLSREP where (ID_CUST_SOLDTO like 'D%') and (DATE_SHIP = '".$today."') and (ID_SLSREP_1 in ('101','102','103','104')) group by CONCAT(ID_SLSREP_1, ' - ', sr.NAME_SLSREP)";
			QueryDatabase($sql2, $results2);

			$sqlT = " select COUNT(distinct ID_ORD) as TOTAL_SHIP, SUM(AMT_ORD_TOTAL) as TOTAL_SLS_SHIP from nsa.CP_SHPHDR_PERM left join nsa.tables_slsrep sr on ID_SLSREP_1 = sr.ID_SLSREP where (ID_CUST_SOLDTO like 'D%') and (DATE_SHIP = '".$today."') and (ID_SLSREP_1 in ('101','102','103','104'))";
			QueryDatabase($sqlT, $resultsT);

			$body  .= "\r\n\nShipments on " . $today . ".";
			while ($row = mssql_fetch_assoc($results2)) {	
				$body .= "\r\n Territory: " . $row['ID_TERR'] . ",   Number of Shipments:  " . $row['NUM_SHIPMENTS'] . ",  Shipment Amount: " . money_format('%(n',$row['SLS_SHIPMENTS']);	
			}//end while

			while ($row = mssql_fetch_assoc($resultsT)) {	
				$body .= "\r\n Total Orders Shipped: " . $row['TOTAL_SHIP'] . ",   Total Shipment Amount: " . money_format('%(n',$row['TOTAL_SLS_SHIP']);
			}//end while

			//////////////////////////////////
			////OPEN ORD STUFF
			/////////////////////////////////
			$sql3 = "select CONCAT(oh.ID_TERR, ' - ', sr.NAME_SLSREP) as ID_TERR, count(distinct oh.ID_ORD) as NUM_OPEN_ORDS, SUM(oh.AMT_ORD_TOTAL) as AMT_OPEN_ORDS from nsa.CP_ORDHDR oh left join nsa.tables_slsrep sr on oh.ID_TERR = sr.ID_SLSREP where oh.ID_CUST_SOLDTO like 'D%' and (oh.ID_TERR in ('101','102','103','104')) group by CONCAT(oh.ID_TERR, ' - ', sr.NAME_SLSREP)";
			QueryDatabase($sql3, $results3);

			$sqlT = "select count(distinct oh.ID_ORD) as TOTAL_OPEN_ORDS, SUM(oh.AMT_ORD_TOTAL) as TOTAL_AMT_OPEN_ORDS from nsa.CP_ORDHDR oh left join nsa.tables_slsrep sr on oh.ID_TERR = sr.ID_SLSREP where oh.ID_CUST_SOLDTO like 'D%' and (oh.ID_TERR in ('101','102','103','104'))";
			QueryDatabase($sqlT, $resultsT);

			$body  .= "\r\n\nOpen Orders as of " . $today . ".";
			while ($row = mssql_fetch_assoc($results3)) {
				$body .= "\r\n Territory: " . $row['ID_TERR'] . ",   Number of Orders Open:  " . $row['NUM_OPEN_ORDS'] . ",   Amount: " . money_format('%(n',$row['AMT_OPEN_ORDS']);	
			}//end while

			while ($row = mssql_fetch_assoc($resultsT)) {
				$body .= "\r\n Total Open Orders: " . $row['TOTAL_OPEN_ORDS'] . ",   Total Amount: " . money_format('%(n',$row['TOTAL_AMT_OPEN_ORDS']);	
			}//end while

			//////////////////////////////////
			////MONTH TO DATE STUFF
			/////////////////////////////////
			$sql4 = "select CONCAT(bh.ID_TERR, ' - ', sr.NAME_SLSREP) as ID_TERR, count(distinct bh.ID_ORD) as MTD_ORDS, sum(bl.SLS) as MTD_SLS from nsa.BOKHST_HDR bh left join nsa.BOKHST_LINE bl on bh.ID_ORD = bl.ID_ORD left join nsa.tables_slsrep sr on bh.ID_TERR = sr.ID_SLSREP where bh.ID_CUST like 'D%' and MONTH(bl.DATE_BOOK_LAST) like month(GETDATE()) and (bh.ID_TERR in ('101','102','103','104')) group by CONCAT(bh.ID_TERR, ' - ', sr.NAME_SLSREP)";
			QueryDatabase($sql4, $results4);

			$sqlT = "select count(distinct bh.ID_ORD) as TOTAL_MTD_ORDS, sum(bl.SLS) as TOTAL_MTD_SLS from nsa.BOKHST_HDR bh left join nsa.BOKHST_LINE bl on bh.ID_ORD = bl.ID_ORD left join nsa.tables_slsrep sr on bh.ID_TERR = sr.ID_SLSREP where bh.ID_CUST like 'D%' and MONTH(bl.DATE_BOOK_LAST) like month(GETDATE()) and (bh.ID_TERR in ('101','102','103','104'))";
			QueryDatabase($sqlT, $resultsT);

			$body  .= "\r\n\nMonth to date as of " . $today . ".";
			while ($row = mssql_fetch_assoc($results4)) {		
				$body .= "\r\n Territory: " . $row['ID_TERR'] . ",    Number of Orders:  " . $row['MTD_ORDS'] . ",   Amount: " . money_format('%(n',$row['MTD_SLS']);	
			}//end while
			while ($row = mssql_fetch_assoc($resultsT)) {		
				$body .= "\r\n Total MTD Ords: " . $row['TOTAL_MTD_ORDS'] . ",   Total Amount: " . money_format('%(n',$row['TOTAL_MTD_SLS']);	
			}//end while


			//////////////////////////////////
			////YEAR TO DATE STUFF
			/////////////////////////////////
			$sql5 = "select CONCAT(bh.ID_TERR, ' - ', sr.NAME_SLSREP) as ID_TERR, count(distinct bh.ID_ORD) as YTD_ORDS, sum(bl.SLS) as YTD_SLS from nsa.BOKHST_HDR bh left join nsa.BOKHST_LINE bl on bh.ID_ORD = bl.ID_ORD left join nsa.tables_slsrep sr on bh.ID_TERR = sr.ID_SLSREP where bh.ID_CUST like 'D%' and YEAR(bl.DATE_BOOK_LAST) like YEAR(GETDATE()) and bh.ID_TERR !='' and (bh.ID_TERR in ('101','102','103','104')) group by CONCAT(bh.ID_TERR, ' - ', sr.NAME_SLSREP)";
			QueryDatabase($sql5, $results5);

			$sqlT = "select count(distinct bh.ID_ORD) as TOTAL_YTD_ORDS, sum(bl.SLS) as TOTAL_YTD_SLS from nsa.BOKHST_HDR bh left join nsa.BOKHST_LINE bl on bh.ID_ORD = bl.ID_ORD left join nsa.tables_slsrep sr on bh.ID_TERR = sr.ID_SLSREP where bh.ID_CUST like 'D%' and YEAR(bl.DATE_BOOK_LAST) like YEAR(GETDATE()) and bh.ID_TERR !='' and (bh.ID_TERR in ('101','102','103','104'))";
			QueryDatabase($sqlT, $resultsT);

			$body  .= "\r\n\nYear to date as of " . $today . ".";
			while ($row = mssql_fetch_assoc($results5)) {
					$body .= "\r\n Territory: " . $row['ID_TERR'] . ",   Number of Orders:  " . $row['YTD_ORDS'] . "   Amount: " . money_format('%(n',$row['YTD_SLS']);		
			}//end while
			while ($row = mssql_fetch_assoc($resultsT)) {
					$body .= "\r\n Total YTD Ords: " . $row['TOTAL_YTD_ORDS'] . ",   Total Amount: " . money_format('%(n',$row['TOTAL_YTD_SLS']);		
			}//end while


			///////////////////////////////
			////////SEND DA EMAIL
			///////////////////////////////
			$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					if ($argv[1] == 'ALL')  {
						error_log("PARAMS: " . $argv[1]);
						$aa_to  = GetEmailSubscribers('DFBOK');
					} else {
						$aa_to = $argv;
					}
					foreach ($aa_to as $to) {
						if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
							$to = "rbollinger@thinknsa.com";
						}
						error_log("BOK_SUM: " . $to);
						mail($to, $subject, $body, $headers);
					}
			error_log("### runDAILY_BOOKINGS_DRIFIRE finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS_DRIFIRE cannot disconnect from database");
		}
	}
?>
