<?php

	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	$DEBUG = 1;

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_OPEN_ORDERS_RUBIN cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_OPEN_ORDERS_RUBIN cannot select " . $dbName);
		} else {
			$StartTime = date('Y-m-d g:i:s a');
			error_log("#############################################");
			error_log("### runDAILY_OPEN_ORDERS_RUBIN started at " . $StartTime);
			$today = date('Y-m-d');
			//$today = '2019-08-07';

			$sCount  = " select count(distinct(l.ID_ORD)) ";
			$sSls    = " select COALESCE(sum(l.PRICE_NET),0) ";

			if (new DateTime($today) < new DateTime('2020-09-01')) {
				$sFROM  = " FROM rbn.CP_ORDLIN l ";
				$sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$sFROM .= " left join rbn.CP_ORDHDR oh on l.ID_ORD = oh.ID_ORD ";
				$sLOC_Clause = "";
			} else {
				$sFROM  = " FROM nsa.CP_ORDLIN l ";
				$sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$sFROM .= " left join nsa.CP_ORDHDR oh on l.ID_ORD = oh.ID_ORD ";
				$sLOC_Clause = " and l.ID_LOC = '20' ";
			}
			

			$ADS_COUNT = '0';
			$CS_COUNT = '0';
			$ETA_COUNT = '0';
			$FR_COUNT = '0';
			$NFR_COUNT = '0';
			$USPS_COUNT = '0';
			$NSA_COUNT = '0';
			$OTHER_COUNT = '0';
			$TOTAL_COUNT = '0';

			$ADS_SLS = '0';
			$CS_SLS = '0';
			$ETA_SLS = '0';
			$FR_SLS = '0';
			$NFR_SLS = '0';
			$USPS_SLS = '0';
			$NSA_SLS = '0';
			$OTHER_SLS = '0';
			$TOTAL_SLS = '0';


			$sql = $sCount." as ADS_COUNT ".$sFROM." where ib.CODE_USER_2_IM = 'ADS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ADS_COUNT = $row["ADS_COUNT"];
			}

			$sql = $sCount." as CS_COUNT ".$sFROM." where ib.CODE_USER_2_IM = 'CS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$CS_COUNT = $row["CS_COUNT"];
			}

			$sql = $sCount." as ETA_COUNT ".$sFROM." where ib.CODE_USER_2_IM = 'ETA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ETA_COUNT = $row["ETA_COUNT"];
			}

			$sql = $sCount." as FR_COUNT ".$sFROM." where ib.CODE_USER_2_IM = 'FR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$FR_COUNT = $row["FR_COUNT"];
			}

			$sql = $sCount." as NFR_COUNT ".$sFROM." where ib.CODE_USER_2_IM = 'NFR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NFR_COUNT = $row["NFR_COUNT"];
			}

			$sql = $sCount." as USPS_COUNT ".$sFROM." where ib.CODE_USER_2_IM = 'USPS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$USPS_COUNT = $row["USPS_COUNT"];
			}

			$sql = $sCount." as NSA_COUNT ".$sFROM." where ib.CODE_USER_2_IM = 'NSA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_COUNT = $row["NSA_COUNT"];
			}

			$sql = $sCount." as OTHER_COUNT ".$sFROM." where ib.CODE_USER_2_IM not in ('ADS','CS','ETA','FR','NFR','USPS','NSA')";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$OTHER_COUNT = $row["OTHER_COUNT"];
			}

			$sql = $sCount." as TOTAL_COUNT ".$sFROM." ";
			if (new DateTime($today) >= new DateTime('2020-09-01')) {
				$sql .= " WHERE l.ID_LOC = '20' ";
			}
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_COUNT = $row["TOTAL_COUNT"];
			}




			$sql = $sSls." as ADS_SLS ".$sFROM." where ib.CODE_USER_2_IM = 'ADS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ADS_SLS = $row["ADS_SLS"];
			}

			$sql = $sSls." as CS_SLS ".$sFROM." where ib.CODE_USER_2_IM = 'CS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$CS_SLS = $row["CS_SLS"];
			}

			$sql = $sSls." as ETA_SLS ".$sFROM." where ib.CODE_USER_2_IM = 'ETA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ETA_SLS = $row["ETA_SLS"];
			}

			$sql = $sSls." as FR_SLS ".$sFROM." where ib.CODE_USER_2_IM = 'FR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$FR_SLS = $row["FR_SLS"];
			}

			$sql = $sSls." as NFR_SLS ".$sFROM." where ib.CODE_USER_2_IM = 'NFR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NFR_SLS = $row["NFR_SLS"];
			}

			$sql = $sSls." as USPS_SLS ".$sFROM." where ib.CODE_USER_2_IM = 'USPS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$USPS_SLS = $row["USPS_SLS"];
			}

			$sql = $sSls." as NSA_SLS ".$sFROM." where ib.CODE_USER_2_IM = 'NSA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_SLS = $row["NSA_SLS"];
			}

			$sql = $sSls." as OTHER_SLS ".$sFROM." where ib.CODE_USER_2_IM not in ('ADS','CS','ETA','FR','NFR','USPS','NSA')";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$OTHER_SLS = $row["OTHER_SLS"];
			}

			$sql = $sSls." as TOTAL_SLS ".$sFROM." ";
			if (new DateTime($today) >= new DateTime('2020-09-01')) {
				$sql .= " WHERE l.ID_LOC = '20' ";
			}
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_SLS = $row["TOTAL_SLS"];
			}






			////////////////////////////
			// RUBIN-NSA Intercompany
			////////////////////////////
			$RBN_NSA_IC_sFROM  = " FROM rbn.CP_ORDLIN l ";
			$RBN_NSA_IC_sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
			$RBN_NSA_IC_sFROM .= " left join rbn.CP_ORDHDR oh on l.ID_ORD = oh.ID_ORD ";

			if (new DateTime($today) <= new DateTime('2020-09-01')) {
				$RBN_NSA_IC_sFROM  = " FROM rbn.CP_ORDLIN l ";
				$RBN_NSA_IC_sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_NSA_IC_sFROM .= " left join rbn.CP_ORDHDR oh on l.ID_ORD = oh.ID_ORD ";
				$sLOC_Clause = "";
			} else {
				$RBN_NSA_IC_sFROM  = " FROM nsa.CP_ORDLIN l ";
				$RBN_NSA_IC_sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_NSA_IC_sFROM .= " left join nsa.CP_ORDHDR oh on l.ID_ORD = oh.ID_ORD ";
				$sLOC_Clause = " and l.ID_LOC = '20' ";
			}			
			
			$sql = $sCount." as RBN_NSA_IC_TOTAL_COUNT ".$RBN_NSA_IC_sFROM." ";
			$sql .= " WHERE ltrim(oh.ID_CUST_SOLDTO) = '14244'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_NSA_IC_TOTAL_COUNT = $row["RBN_NSA_IC_TOTAL_COUNT"];
			}

			$sql = $sSls." as RBN_NSA_IC_TOTAL_SLS ".$RBN_NSA_IC_sFROM." ";
			$sql .= " WHERE ltrim(oh.ID_CUST_SOLDTO) = '14244'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_NSA_IC_TOTAL_SLS = $row["RBN_NSA_IC_TOTAL_SLS"];
			}

			$IC_TOTAL_COUNT = $RBN_NSA_IC_TOTAL_COUNT;
			$IC_TOTAL_SLS = $RBN_NSA_IC_TOTAL_SLS;

			if ($TOTAL_SLS <> '0' OR $TOTAL_COUNT) {
				$subject = "NSA - Chicago Open Orders Summary for " . $today;

				$body  = "NSA - Chicago Open Orders as of " . $today . ".\r\n";
				$body .= "\r\n Ad Specialty: 			" . $ADS_COUNT . " orders,	Value	" . money_format('%(n',$ADS_SLS);
				$body .= "\r\n Customer Specific: 		" . $CS_COUNT . " orders,	Value	" . money_format('%(n',$CS_SLS);	
				$body .= "\r\n ETA Emb. Garments: 		" . $ETA_COUNT . " orders,	Value	" . money_format('%(n',$ETA_SLS);
				$body .= "\r\n FR Garments: 			" . $FR_COUNT . " orders,	Value	" . money_format('%(n',$FR_SLS);
				$body .= "\r\n Non-FR Garments: 		" . $NFR_COUNT . " orders,	Value	" . money_format('%(n',$NFR_SLS);
				$body .= "\r\n USPS Garments: 		" . $USPS_COUNT . " orders,	Value	" . money_format('%(n',$USPS_SLS);
				$body .= "\r\n NSA Items: 			" . $NSA_COUNT . " orders,	Value	" . money_format('%(n',$NSA_SLS);
				$body .= "\r\n Other: 				" . $OTHER_COUNT . " orders,	Value	" . money_format('%(n',$OTHER_SLS);
				$body .= "\r\n Total: 				" . $TOTAL_COUNT . " orders,	Value	" . money_format('%(n',$TOTAL_SLS);

				$body .= "\r\n\r\n NSA-Chicago - NSA IC: 			" . $RBN_NSA_IC_TOTAL_COUNT . " orders,	Value	" . money_format('%(n',$RBN_NSA_IC_TOTAL_SLS);
				
				$body .= "\r\n\r\n GRAND TOTAL Less IC: 		" . ($TOTAL_COUNT + $RBN_TOTAL_COUNT - $IC_TOTAL_COUNT) . " orders,	Value	" . money_format('%(n',($TOTAL_SLS + $RBN_TOTAL_SLS - $IC_TOTAL_SLS));

				$headers = "From: eProduction@thinknsa.com" . "\r\n" .
					"X-Mailer: PHP/" . phpversion();

				if ($DEBUG) {
					$to = "gvandyne@thinknsa.com";
					error_log("Email sent to: " . $to);
					mail($to, $subject, $body, $headers);
					error_log($body);
				} else {
					$to = "tbielenberg@thinknsa.com";
					error_log("OPEN_ORDERS_RBN: " . $to);
					mail($to, $subject, $body, $headers);

					$to = "cgrossman2@thinknsa.com";
					error_log("OPEN_ORDERS_RBN: " . $to);
					mail($to, $subject, $body, $headers);

					$to = "sgeraci@thinknsa.com";
					error_log("OPEN_ORDERS_RBN: " . $to);
					mail($to, $subject, $body, $headers);
				}

			}
			$EndTime = date('Y-m-d g:i:s a');
			error_log("### runDAILY_OPEN_ORDERS_RUBIN finished at " . $EndTime);
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_OPEN_ORDERS_RUBIN cannot disconnect from database");
		}
	}
?>
