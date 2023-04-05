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
		error_log("runDAILY_SHIPMENTS_RUBIN cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_SHIPMENTS_RUBIN cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runDAILY_SHIPMENTS_RUBIN started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');


			if (new DateTime($today) < new DateTime('2020-09-01')) {
				$sql  = "SELECT ";
				$sql .= "(select count(distinct(sl.ID_ORD)) FROM rbn.CP_SHPLIN sl left join rbn.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join rbn.CP_SHPHDR sh"; 
				$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) = '14244') AS RBN_NSA_IC_ORDER_COUNT_SHIPPED, ";
				$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM rbn.CP_SHPLIN sl left join rbn.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join rbn.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) = '14244') as RBN_NSA_IC_TOTAL_SLS_SHIPPED, ";
				$sql .= "(select count(distinct(sl.ID_ORD)) FROM rbn.CP_SHPLIN sl left join rbn.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join rbn.CP_SHPHDR sh"; 
				$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date)) AS TOTAL_ORDER_COUNT_SHIPPED, ";
				$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM rbn.CP_SHPLIN sl left join rbn.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join rbn.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) ) as TOTAL_SLS_SHIPPED ";
				QueryDatabase($sql, $results);
			} else {
				$sql  = "SELECT ";
				$sql .= "(select count(distinct(sl.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh"; 
				$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) = '14244' and sl.ID_LOC = '20') AS RBN_NSA_IC_ORDER_COUNT_SHIPPED, ";
				$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and ltrim(sh.ID_CUST_SOLDTO) = '14244'  and sl.ID_LOC = '20') as RBN_NSA_IC_TOTAL_SLS_SHIPPED, ";
				$sql .= "(select count(distinct(sl.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh"; 
				$sql .= " on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and sl.ID_LOC = '20') AS TOTAL_ORDER_COUNT_SHIPPED, ";
				$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR  sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = CAST('".$today."' AS date) and sl.ID_LOC = '20') as TOTAL_SLS_SHIPPED ";
				QueryDatabase($sql, $results);
			}
			


			while ($row = mssql_fetch_assoc($results)) {
				if ($row['TOTAL_SLS_SHIPPED'] <> '0' OR $row['TOTAL_ORDER_COUNT_SHIPPED'] <> '0') {
					
					$RBN_NSA_IC_ORDER_COUNT_SHIPPED = $row['RBN_NSA_IC_ORDER_COUNT_SHIPPED'];
					$RBN_NSA_IC_TOTAL_SLS_SHIPPED = $row['RBN_NSA_IC_TOTAL_SLS_SHIPPED'];
					$TOTAL_ORDER_COUNT_SHIPPED = $row['TOTAL_ORDER_COUNT_SHIPPED'];
					$TOTAL_SLS_SHIPPED = $row['TOTAL_SLS_SHIPPED'];

					$ADS_COUNT = 0;
					$CS_COUNT = 0;
					$ETA_COUNT = 0;
					$FR_COUNT = 0;
					$NFR_COUNT = 0;
					$USPS_COUNT = 0;
					$NSA_COUNT = 0;
					$OTHER_COUNT = 0;

					$ADS_DOLLARS = 0;
					$CS_DOLLARS = 0;
					$ETA_DOLLARS = 0;
					$FR_DOLLARS = 0;
					$NFR_DOLLARS = 0;
					$USPS_DOLLARS = 0;
					$NSA_DOLLARS = 0;
					$OTHER_DOLLARS = 0;
					
					if (new DateTime($today) < new DateTime('2020-09-01')) {
						$sql1  = "SELECT ";
						$sql1 .= " CASE WHEN ib.CODE_USER_2_IM in ('ADS','CS','ETA','FR','NFR','USPS','NSA') THEN ib.CODE_USER_2_IM ELSE 'OTHER' END as ProductType,";
						$sql1 .= " count(distinct(sl.ID_ORD)) as SHIPPED_ORDERS_COUNT, ";
						$sql1 .= " COALESCE(sum(sl.PRICE_NET),0) as SHIPPED_VALUE";
						$sql1 .= " FROM rbn.CP_SHPLIN sl ";
						$sql1 .= " left join rbn.ITMMAS_BASE ib ";
						$sql1 .= " on sl.ID_ITEM = ib.ID_ITEM ";
						$sql1 .= " left join rbn.CP_SHPHDR sh ";
						$sql1 .= " on sl.ID_SHIP = sh.ID_SHIP ";
						$sql1 .= " WHERE sh.DATE_SHIP = CAST('".$today."' as date) ";
						$sql1 .= " GROUP BY CASE WHEN ib.CODE_USER_2_IM in ('ADS','CS','ETA','FR','NFR','USPS','NSA') THEN ib.CODE_USER_2_IM ELSE 'OTHER' END";
						QueryDatabase($sql1, $results1);
					} else {
						$sql1  = "SELECT ";
						$sql1 .= " CASE WHEN ib.CODE_USER_2_IM in ('ADS','CS','ETA','FR','NFR','USPS','NSA') THEN ib.CODE_USER_2_IM ELSE 'OTHER' END as ProductType,";
						$sql1 .= " count(distinct(sl.ID_ORD)) as SHIPPED_ORDERS_COUNT, ";
						$sql1 .= " COALESCE(sum(sl.PRICE_NET),0) as SHIPPED_VALUE";
						$sql1 .= " FROM nsa.CP_SHPLIN sl ";
						$sql1 .= " left join nsa.ITMMAS_BASE ib ";
						$sql1 .= " on sl.ID_ITEM = ib.ID_ITEM ";
						$sql1 .= " left join nsa.CP_SHPHDR sh ";
						$sql1 .= " on sl.ID_SHIP = sh.ID_SHIP ";
						$sql1 .= " WHERE sh.DATE_SHIP = CAST('".$today."' as date) ";
						$sql1 .= " and sl.ID_LOC = '20' ";
						$sql1 .= " GROUP BY CASE WHEN ib.CODE_USER_2_IM in ('ADS','CS','ETA','FR','NFR','USPS','NSA') THEN ib.CODE_USER_2_IM ELSE 'OTHER' END";
						QueryDatabase($sql1, $results1);
					}
					while ($row1 = mssql_fetch_assoc($results1)) {
						$ProductType = trim($row1['ProductType']);
						switch ($ProductType) {
							case "ADS":
								error_log("ADS");
								$ADS_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$ADS_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
							case "CS":
								error_log("CS");
								$CS_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$CS_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
							case "ETA":
								error_log("ETA");
								$ETA_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$ETA_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
							case "FR":
								error_log("FR");
								$FR_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$FR_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
							case "NFR":
								error_log("NFR");
								$NFR_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$NFR_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
							case "USPS":
								error_log("USPS");
								$USPS_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$USPS_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
							case "NSA":
								error_log("NSA");
								$NSA_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$NSA_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
							case "OTHER":
								error_log("OTHER");
								$OTHER_COUNT = $row1['SHIPPED_ORDERS_COUNT'];
								$OTHER_DOLLARS = $row1['SHIPPED_VALUE'];
							break;
						}
					}

					$subject = "Shipment Summary for Rubin " . $today;
					$body  = "Shipments on " . $today . ".\r\n";

					if ($ADS_COUNT <> 0 || $ADS_DOLLARS <> 0) {
						$body .= "\r\n Ad Specialty: 			".$ADS_COUNT." orders,	Value	" . money_format('%(n',$ADS_DOLLARS);	
					}
					if ($CS_COUNT <> 0 || $CS_DOLLARS <> 0) {
						$body .= "\r\n Customer Specific: 		".$CS_COUNT." orders,	Value	" . money_format('%(n',$CS_DOLLARS);	
					}
					if ($ETA_COUNT <> 0 || $ETA_DOLLARS <> 0) {
						$body .= "\r\n ETA Emb. Garments: 		".$ETA_COUNT." orders,	Value	" . money_format('%(n',$ETA_DOLLARS);
					}
					if ($FR_COUNT <> 0 || $FR_DOLLARS <> 0) {
						$body .= "\r\n FR Garments: 			".$FR_COUNT." orders,	Value	" . money_format('%(n',$FR_DOLLARS);
					}
					if ($NFR_COUNT <> 0 || $NFR_DOLLARS <> 0) {
						$body .= "\r\n Non-FR Garments: 		".$NFR_COUNT." orders,	Value	" . money_format('%(n',$NFR_DOLLARS);
					}
					if ($USPS_COUNT <> 0 || $USPS_DOLLARS <> 0) {
						$body .= "\r\n USPS Garments: 		".$USPS_COUNT." orders,	Value	" . money_format('%(n',$USPS_DOLLARS);
					}
					if ($NSA_COUNT <> 0 || $NSA_DOLLARS <> 0) {
						$body .= "\r\n NSA Items: 			".$NSA_COUNT." orders,	Value	" . money_format('%(n',$NSA_DOLLARS);
					}
					if ($OTHER_COUNT <> 0 || $OTHER_DOLLARS <> 0) {
						$body .= "\r\n Other: 				".$OTHER_COUNT." orders,	Value	" . money_format('%(n',$OTHER_DOLLARS);
					}

					$body .= "\r\n\r\n Total:				".$TOTAL_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',($TOTAL_SLS_SHIPPED));
					$body .= "\r\n Rubin-NSA-IC: 			".$RBN_NSA_IC_ORDER_COUNT_SHIPPED." orders,	Value	" . money_format('%(n',$RBN_NSA_IC_TOTAL_SLS_SHIPPED);
					$body .= "\r\n\r\n Grand Total Net of IC:		".($TOTAL_ORDER_COUNT_SHIPPED - $RBN_NSA_IC_ORDER_COUNT_SHIPPED)." orders,	Value	" . money_format('%(n',($TOTAL_SLS_SHIPPED - $RBN_NSA_IC_TOTAL_SLS_SHIPPED));
					$body .= "\r\n\r\n **On the last working day of each month this report MAY NOT reflect true values due to same day Invoicing.";

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					if ($DEBUG) {
						$to = "gvandyne@thinknsa.com";
						error_log("RBN_SHP_SUM: " . $to);
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
							error_log("RBN_SHP_SUM: " . $to);
							mail($to, $subject, $body, $headers);
						}
					}
				}
			}

			error_log("### runDAILY_SHIPMENTS_RUBIN finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_SHIPMENTS_RUBIN cannot disconnect from database");
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
