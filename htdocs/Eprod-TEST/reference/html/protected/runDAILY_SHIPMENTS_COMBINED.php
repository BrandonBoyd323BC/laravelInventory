<?php

	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

 	$DEBUG = 1;

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_SHIPMENTS_COMBINED cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_SHIPMENTS_COMBINED cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runDAILY_SHIPMENTS_COMBINED started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');
			$today = '2021-01-11';

			$sql  = "SELECT ";
			//////NSA
			$sql .= "(select count(distinct(sl.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh"; 
			$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and sl.ID_LOC = '10') AS NSA_ORDER_COUNT_SHIPPED, ";
			$sql .= "(select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and sl.ID_LOC = '10') as NSA_SLS_SHIPPED, ";

			//////RUBIN
			$sql .= "(select count(distinct(sl.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh"; 
			$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and sl.ID_LOC = '20') AS RBN_ORDER_COUNT_SHIPPED, ";
			$sql .= "(select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and sl.ID_LOC = '20') as RBN_SLS_SHIPPED, ";

			//////NSA_RBN IC
			//$sql .= "(select count(distinct(sl.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh"; 
			//$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) in ('754050','D75405') and sl.ID_LOC = '10') AS NSA_RBN_IC_ORDER_COUNT_SHIPPED, ";
			//$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) in ('754050','D75405') and sl.ID_LOC = '10') as NSA_RBN_IC_TOTAL_SLS_SHIPPED, ";

			//////RBN_NSA IC
			//$sql .= "(select count(distinct(sl.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh"; 
			//$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) = '14244' and sl.ID_LOC = '20') AS RBN_NSA_IC_ORDER_COUNT_SHIPPED, ";
			//$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) = '14244' and sl.ID_LOC = '20') as RBN_NSA_IC_TOTAL_SLS_SHIPPED, ";

			//////KUNZ GLOVE
			$sql .= "(select SHIP from nsa.SUBSIDIARY_BOOK_SHIP_LOG where DATE_LOG = CAST('".$today."' AS date) and SUBSIDIARY = 'Kunz Glove') as KUNZ_SLS_SHIPPED, ";

			//////WILD THINGS
			$sql .= "(select SHIP from nsa.SUBSIDIARY_BOOK_SHIP_LOG where DATE_LOG = CAST('".$today."' AS date) and SUBSIDIARY = 'Wild Things') as WILDTHINGS_SLS_SHIPPED";

			QueryDatabase($sql, $results);


			while ($row = mssql_fetch_assoc($results)) {
				if ($row['NSA_ORDER_COUNT_SHIPPED'] <> '0' OR $row['NSA_SLS_SHIPPED'] <> '0' OR $row['RBN_ORDER_COUNT_SHIPPED'] <> '0' OR $row['RBN_SLS_SHIPPED'] <> '0') {
					
					$NSA_ORDER_COUNT_SHIPPED = $row['NSA_ORDER_COUNT_SHIPPED'];
					$NSA_SLS_SHIPPED = $row['NSA_SLS_SHIPPED'];
					$RBN_ORDER_COUNT_SHIPPED = $row['RBN_ORDER_COUNT_SHIPPED'];
					$RBN_SLS_SHIPPED = $row['RBN_SLS_SHIPPED'];
					$KUNZ_SLS_SHIPPED = $row['KUNZ_SLS_SHIPPED'];
					$WILDTHINGS_SLS_SHIPPED = $row['WILDTHINGS_SLS_SHIPPED'];

					$TOTAL_ORDER_COUNT_SHIPPED = $NSA_ORDER_COUNT_SHIPPED + $RBN_ORDER_COUNT_SHIPPED;
					$TOTAL_SLS_SHIPPED = $NSA_SLS_SHIPPED + $RBN_SLS_SHIPPED;

					//$NSA_RBN_IC_ORDER_COUNT_SHIPPED = $row['NSA_RBN_IC_ORDER_COUNT_SHIPPED'];
					//$NSA_RBN_IC_TOTAL_SLS_SHIPPED = $row['NSA_RBN_IC_TOTAL_SLS_SHIPPED'];
					//$RBN_NSA_IC_ORDER_COUNT_SHIPPED = $row['RBN_NSA_IC_ORDER_COUNT_SHIPPED'];
					//$RBN_NSA_IC_TOTAL_SLS_SHIPPED = $row['RBN_NSA_IC_TOTAL_SLS_SHIPPED'];

					//$TOTAL_IC_ORDER_COUNT_SHIPPED = $NSA_RBN_IC_ORDER_COUNT_SHIPPED + $RBN_NSA_IC_ORDER_COUNT_SHIPPED;
					//$TOTAL_IC_TOTAL_SLS_SHIPPED = $NSA_RBN_IC_TOTAL_SLS_SHIPPED + $RBN_NSA_IC_TOTAL_SLS_SHIPPED;

					//$GRAND_TOTAL_ORDER_COUNT_SHIPPED = $NSA_ORDER_COUNT_SHIPPED + $RBN_ORDER_COUNT_SHIPPED - $TOTAL_IC_ORDER_COUNT_SHIPPED;
					//$GRAND_TOTAL_SLS_SHIPPED = $NSA_SLS_SHIPPED + $RBN_SLS_SHIPPED - $TOTAL_IC_TOTAL_SLS_SHIPPED;

					$TOTAL_SUBSIDIARY_SLS_SHIPPED = $KUNZ_SLS_SHIPPED + $WILDTHINGS_SLS_SHIPPED;
					$GRAND_TOTAL_ORDER_COUNT_SHIPPED = $NSA_ORDER_COUNT_SHIPPED + $RBN_ORDER_COUNT_SHIPPED;
					$GRAND_TOTAL_SLS_SHIPPED = $NSA_SLS_SHIPPED + $RBN_SLS_SHIPPED + $KUNZ_SLS_SHIPPED + $WILDTHINGS_SLS_SHIPPED;


					$subject = "Shipment Summary for NSA & Rubin " . $today;
					$body  = "Shipments on " . $today . ".\r\n";

					$body .= "\r\n NSA:			".$NSA_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$NSA_SLS_SHIPPED);
					$body .= "\r\n Rubin:			".$RBN_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$RBN_SLS_SHIPPED);
					$body .= "\r\n Total:			".$TOTAL_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$TOTAL_SLS_SHIPPED);

					//$body .= "\r\n\r\n NSA-Rubin IC:		".$NSA_RBN_IC_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$NSA_RBN_IC_TOTAL_SLS_SHIPPED);
					//$body .= "\r\n Rubin-NSA IC:		".$RBN_NSA_IC_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$RBN_NSA_IC_TOTAL_SLS_SHIPPED);
					//$body .= "\r\n Total IC:		".$TOTAL_IC_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$TOTAL_IC_TOTAL_SLS_SHIPPED);

					$body .= "\r\n\r\n Kunz Glove:				Value	" . money_format('%(n',$KUNZ_SLS_SHIPPED);
					$body .= "\r\n Wild Things:				Value	" . money_format('%(n',$WILDTHINGS_SLS_SHIPPED);
					$body .= "\r\n Total Subsidiaries:			Value	" . money_format('%(n',$TOTAL_SUBSIDIARY_SLS_SHIPPED);

					$body .= "\r\n\r\n Grand Total:		".$GRAND_TOTAL_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$GRAND_TOTAL_SLS_SHIPPED);
					$body .= "\r\n\r\n **On the last working day of each month this report MAY NOT reflect true values due to same day Invoicing.";

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					if ($DEBUG) {
						$to = "gvandyne@thinknsa.com";
						error_log("SHIPMENTS_SUM_COMBINED: " . $to);
						mail($to, $subject, $body, $headers);					
					} else {
						$to = "group-shipmentsummary@thinknsa.com";
						if (isset($argv[1])) {
							if ($argv[1] == 'ALL')  {
								error_log("PARAMS: " . $argv[1]);
								$to = "group-shipmentsummary@thinknsa.com";
							} else {
								$to = $argv;
							}
						}
						error_log("SHIPMENTS_SUM_COMBINED: " . $to);
						mail($to, $subject, $body, $headers);
					}
				}
			}

			error_log("### runDAILY_SHIPMENTS_COMBINED finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_SHIPMENTS_COMBINED cannot disconnect from database");
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
