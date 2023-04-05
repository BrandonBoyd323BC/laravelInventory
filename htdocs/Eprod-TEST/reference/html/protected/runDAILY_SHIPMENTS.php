<?php
	$DEBUG = 0;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}


	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_SHIPMENTS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_SHIPMENTS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runDAILY_SHIPMENTS started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');


			//error_log("Today: " . $today);
			//$month = date('m');
			//$year = date('Y');
			//$LDOM = date("Y-m-t");
			//$LDOM_DOW = date('N',$LDOM);
			//error_log("LDOM: " . $LDOM);
			//error_log("LDOM_DOW: " . $LDOM_DOW);

			//$lastday = date('t',strtotime('today'));
			//error_log("lastday: " . $lastday);
			//$lastDayofMonth = get_last_weekday_in_month($month, $year);
			//error_log("lastDayofMonth: " . $lastDayofMonth);

			$sql  = "SELECT DRF_IND_STOCK_LINE_COUNT_SHIPPED as D_IND_STK_CNT, ";
			$sql .= " DRF_MIL_STOCK_LINE_COUNT_SHIPPED as D_MIL_STK_CNT, ";
			$sql .= " DRF_INT_STOCK_LINE_COUNT_SHIPPED as D_INT_STK_CNT, ";
			$sql .= " * FROM nsa.SHIPMENT_SUMMARY";
			QueryDatabase($sql, $results);



			while ($row = mssql_fetch_assoc($results)) {
				if ($row['TOTAL_SLS_SHIPPED'] <> '0' OR $row['TOTAL_ORDER_COUNT_SHIPPED'] <> '0') {

/*					
					$subject = "Shipment Summary for " . $today;
					$body  = "Shipments on " . $today . ".\r\n";
					$body .= "\r\n NSA: 		" . $row['NSA_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['NSA_SLS_SHIPPED']);
					$body .= "\r\n DRIFIRE IND: 	" . $row['DRF_IND_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['DRF_IND_SLS_SHIPPED']);
					$body .= "\r\n DRIFIRE MIL: 	" . $row['DRF_MIL_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['DRF_MIL_SLS_SHIPPED']);
					$body .= "\r\n DRIFIRE INT: 	" . $row['DRF_INT_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['DRF_INT_SLS_SHIPPED']);
					$body .= "\r\n Total: 		" . $row['TOTAL_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['TOTAL_SLS_SHIPPED']);
					$body .= "\r\n\r\n_____________________Informational____________________";
					//$body .= "\r\n EDI: 		" . $row['EDI_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['EDI_SLS']);
					//$body .= "\r\n EDI Order %: 	" . round(($row['EDI_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		EDI Shipment %:	" . round(($row['EDI_SLS']/$row['TOTAL_SLS'])*100,2)."%";
					//$body .= "\r\n WMS: 		" . $row['WMS_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['WMS_SLS']);
					//$body .= "\r\n WMS Order %: " . round(($row['WMS_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		WMS Shipment %:	" . round(($row['WMS_SLS']/$row['TOTAL_SLS'])*100,2)."%";
					//$body .= "\r\n TCM: 		" . $row['TCM_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['TCM_SLS']);
					//$body .= "\r\n TCM Order %: 	" . round(($row['TCM_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		TCM Shipment %:	" . round(($row['TCM_SLS']/$row['TOTAL_SLS'])*100,2)."%";

					$body .= "\r\n FC: 		" . $row['FC_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['FC_SLS_SHIPPED']);
					$body .= "\r\n FC Order %: 	" . round(($row['FC_ORDER_COUNT_SHIPPED']/$row['TOTAL_ORDER_COUNT_SHIPPED'])*100,2)."%,		FC Shipment %:	" . round(($row['FC_SLS_SHIPPED']/$row['TOTAL_SLS_SHIPPED'])*100,2)."%";
					$body .= "\r\n HQ: 		" . $row['HQ_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['HQ_SLS_SHIPPED']);
					$body .= "\r\n HQ Order %: 	" . round(($row['HQ_ORDER_COUNT_SHIPPED']/$row['TOTAL_ORDER_COUNT_SHIPPED'])*100,2)."%,		HQ Shipment %:	" . round(($row['HQ_SLS_SHIPPED']/$row['TOTAL_SLS_SHIPPED'])*100,2)."%";
					$body .= "\r\n TP: 		" . $row['TP_ORDER_COUNT_SHIPPED'] . " orders,	Shipment	" . money_format('%(n',$row['TP_SLS_SHIPPED']);
					$body .= "\r\n TP Order %: 	" . round(($row['TP_ORDER_COUNT_SHIPPED']/$row['TOTAL_ORDER_COUNT_SHIPPED'])*100,2)."%,		TP Shipment %:	" . round(($row['TP_SLS_SHIPPED']/$row['TOTAL_SLS_SHIPPED'])*100,2)."%";

					$body .= "\r\n\r\n NSA Stock Lines Picked: 		" . $row['NSA_STOCK_LINE_SHIPPED'];
					//$body .= "\r\n DRIFIRE IND Stock Lines Picked: 	" . $row['DRF_IND_STOCK_LINE_COUNT_SHIPPED'];
					$body .= "\r\n DRIFIRE IND Stock Lines Picked: 	" . $row['D_IND_STK_CNT'];
					//$body .= "\r\n DRIFIRE MIL Stock Lines Picked: 	" . $row['DRF_MIL_STOCK_LINE_COUNT_SHIPPED'];
					$body .= "\r\n DRIFIRE MIL Stock Lines Picked: 	" . $row['D_MIL_STK_CNT'];
					//$body .= "\r\n DRIFIRE INT Stock Lines Picked: 	" . $row['DRF_INT_STOCK_LINE_COUNT_SHIPPED'];
					$body .= "\r\n DRIFIRE INT Stock Lines Picked: 	" . $row['D_INT_STK_CNT'];

					$body .= "\r\n FC Stock Lines Picked: 		" . $row['FC_STOCK_LINE_COUNT_SHIPPED'];
					$body .= "\r\n HQ Stock Lines Picked: 		" . $row['HQ_STOCK_LINE_COUNT_SHIPPED'];
					$body .= "\r\n TP Stock Lines Picked: 		" . $row['TP_STOCK_LINE_COUNT_SHIPPED'];

					$body .= "\r\n Total Stock Lines Picked: 		" . $row['TOTAL_STOCK_LINE_COUNT_SHIPPED'];
					$body .= "\r\n\r\n **On the last working day of each month this report MAY NOT reflect true values due to same day Invoicing.";

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					if ($TEST_ENV) {
						$to = "gvandyne@thinknsa.com";
						error_log("SHP_SUM: " . $to);
						mail($to, $subject, $body, $headers);
					} else {
						if ($argv[1] == 'ALL')  {
							error_log("PARAMS: " . $argv[1]);
							$aa_to  = GetEmailSubscribers('SHP');
						} else {
							$aa_to = $argv;
						}
						foreach ($aa_to as $to) {
							if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
								$to = "gvandyne@thinknsa.com";
							}
							error_log("SHP_SUM: " . $to);
							mail($to, $subject, $body, $headers);
						}
					}

*/

					//$sql1  = "INSERT INTO nsa.SHIPMENT_SUMMARY_HIST" . $DB_TEST_FLAG . " ( ";
					$sql1  = "INSERT INTO nsa.SHIPMENT_SUMMARY_HIST ( ";
					$sql1 .= " DATE_ADD, ";
					$sql1 .= " FC_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " HQ_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " RBN_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " NSA_STOCK_LINE_SHIPPED, ";
					$sql1 .= " DRF_IND_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " DRF_MIL_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " DRF_INT_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " FC_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " HQ_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " RBN_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " NSA_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " DRF_IND_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " DRF_MIL_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " DRF_INT_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " FC_SLS_SHIPPED, ";
					$sql1 .= " HQ_SLS_SHIPPED, ";
					$sql1 .= " RBN_SLS_SHIPPED, ";
					$sql1 .= " NSA_SLS_SHIPPED, ";
					$sql1 .= " DRF_IND_SLS_SHIPPED, ";
					$sql1 .= " DRF_MIL_SLS_SHIPPED, ";
					$sql1 .= " DRF_INT_SLS_SHIPPED, ";
					$sql1 .= " TOTAL_NSA_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " TOTAL_NSA_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " TOTAL_NSA_SLS_SHIPPED, ";
					$sql1 .= " TOTAL_RBN_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " TOTAL_RBN_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " TOTAL_RBN_SLS_SHIPPED ";					
					$sql1 .= " TOTAL_ORDER_COUNT_SHIPPED, ";
					$sql1 .= " TOTAL_STOCK_LINE_COUNT_SHIPPED, ";
					$sql1 .= " TOTAL_SLS_SHIPPED ";
					$sql1 .= " ) VALUES ( ";
					$sql1 .= " GetDate(), ";
					$sql1 .= " ".$row['FC_STOCK_LINE_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['HQ_STOCK_LINE_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['RBN_STOCK_LINE_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['NSA_STOCK_LINE_SHIPPED'].", ";
					$sql1 .= " ".$row['D_IND_STK_CNT'].", ";
					$sql1 .= " ".$row['D_MIL_STK_CNT'].", ";
					$sql1 .= " ".$row['D_INT_STK_CNT'].", ";
					$sql1 .= " ".$row['FC_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['HQ_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['RBN_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['NSA_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['DRF_IND_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['DRF_MIL_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['DRF_INT_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['FC_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['HQ_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['RBN_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['NSA_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['DRF_IND_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['DRF_MIL_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['DRF_INT_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_NSA_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_NSA_STOCK_LINE_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_NSA_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_RBN_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_RBN_STOCK_LINE_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_RBN_SLS_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_ORDER_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_STOCK_LINE_COUNT_SHIPPED'].", ";
					$sql1 .= " ".$row['TOTAL_SLS_SHIPPED']." ";
					$sql1 .= " ) ";
					QueryDatabase($sql1, $results1);
				}
			}

			error_log("### runDAILY_SHIPMENTS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_SHIPMENTS cannot disconnect from database");
		}
	}

function get_last_weekday_in_month($month, $year) {
 	$getdate = getdate(mktime(null, null, null, $month + 1, 0, $year));
 	return $getdate['wday'];
}

function isTodayLastWorkingDay() {

//get number of days in month

//get DOW for LDOM
//if DOW > 5 {
//	subtract 1 from number of days in month
//	see if new DOW > 5

//}




}



function get_date($month, $year, $week, $day, $direction) {
	if($direction > 0) {
		$startday = 1;
	} else {
		$startday = date('t', mktime(0, 0, 0, $month, 1, $year));
	}

	$start = mktime(0, 0, 0, $month, $startday, $year);
	$weekday = date('N', $start);

	if($direction * $day >= $direction * $weekday) {
		$offset = -$direction * 7;
	} else {
		$offset = 0;
	}

	$offset += $direction * ($week * 7) + ($day - $weekday);
	return mktime(0, 0, 0, $month, $startday + $offset, $year);
}

?>
