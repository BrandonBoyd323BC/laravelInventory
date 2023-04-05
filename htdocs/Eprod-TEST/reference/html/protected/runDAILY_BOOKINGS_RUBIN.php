<?php

	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	$DEBUG = 1;

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_BOOKINGS_RUBIN cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS_RUBIN cannot select " . $dbName);
		} else {
			$StartTime = date('Y-m-d g:i:s a');
			error_log("#############################################");
			error_log("### runDAILY_BOOKINGS_RUBIN started at " . $StartTime);
			$today = date('Y-m-d');
			//$today = '2019-08-07';

			$sCount  = " select count(distinct(l.ID_ORD)) ";
			$sSls    = " select COALESCE(sum(l.SLS),0) ";

			if (new DateTime($today) < new DateTime('2020-09-01')) {
				$sFROM  = " FROM rbn.BOKHST_LINE l ";
				$sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$sFROM .= " left join rbn.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				$sLOC_Clause = "";
			} else {
				$sFROM  = " FROM nsa.BOKHST_LINE l ";
				$sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				$sLOC_Clause = " and l.ID_LOC = '20' ";
			}
			$ADS_COUNT = '0';
			$CS_COUNT = '0';
			$ETA_COUNT = '0';
			$FR_COUNT = '0';
			$NFR_COUNT = '0';
			$USPS_UL_COUNT = '0';
			$USPS_PL_COUNT = '0';
			$NSA_COUNT = '0';
			$OTHER_COUNT = '0';
			$TOTAL_COUNT = '0';

			$ADS_SLS = '0';
			$CS_SLS = '0';
			$ETA_SLS = '0';
			$FR_SLS = '0';
			$NFR_SLS = '0';
			$USPS_UL_SLS = '0';
			$USPS_PL_SLS = '0';
			$NSA_SLS = '0';
			$OTHER_SLS = '0';
			$TOTAL_SLS = '0';


			$sql = $sCount." as ADS_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'ADS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ADS_COUNT = $row["ADS_COUNT"];
			}

			$sql = $sCount." as CS_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'CS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$CS_COUNT = $row["CS_COUNT"];
			}

			$sql = $sCount." as ETA_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'ETA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ETA_COUNT = $row["ETA_COUNT"];
			}

			$sql = $sCount." as FR_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'FR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$FR_COUNT = $row["FR_COUNT"];
			}

			$sql = $sCount." as NFR_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'NFR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NFR_COUNT = $row["NFR_COUNT"];
			}

			$sql = $sCount." as USPS_UL_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'USPS' and l.ID_LOC = '10'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$USPS_UL_COUNT = $row["USPS_UL_COUNT"];
			}

			$sql = $sCount." as USPS_PL_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'USPS' and l.ID_LOC = '20'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$USPS_PL_COUNT = $row["USPS_PL_COUNT"];
			}

			$sql = $sCount." as NSA_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'NSA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_COUNT = $row["NSA_COUNT"];
			}

			$sql = $sCount." as OTHER_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM not in ('ADS','CS','ETA','FR','NFR','USPS','NSA')";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$OTHER_COUNT = $row["OTHER_COUNT"];
			}

			$sql = $sCount." as TOTAL_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_COUNT = $row["TOTAL_COUNT"];
			}




			$sql = $sSls." as ADS_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'ADS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ADS_SLS = $row["ADS_SLS"];
			}

			$sql = $sSls." as CS_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'CS'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$CS_SLS = $row["CS_SLS"];
			}

			$sql = $sSls." as ETA_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'ETA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$ETA_SLS = $row["ETA_SLS"];
			}

			$sql = $sSls." as FR_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'FR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$FR_SLS = $row["FR_SLS"];
			}

			$sql = $sSls." as NFR_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'NFR'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NFR_SLS = $row["NFR_SLS"];
			}

			$sql = $sSls." as USPS_UL_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'USPS' and l.ID_LOC = '10'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$USPS_UL_SLS = $row["USPS_UL_SLS"];
			}
			
			$sql = $sSls." as USPS_PL_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'USPS' and l.ID_LOC = '20'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$USPS_PL_SLS = $row["USPS_PL_SLS"];
			}

			$sql = $sSls." as NSA_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM = 'NSA'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_SLS = $row["NSA_SLS"];
			}

			$sql = $sSls." as OTHER_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ib.CODE_USER_2_IM not in ('ADS','CS','ETA','FR','NFR','USPS','NSA')";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$OTHER_SLS = $row["OTHER_SLS"];
			}

			$sql = $sSls." as TOTAL_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_SLS = $row["TOTAL_SLS"];
			}

/*

			////////////////////////////
			// RUBIN 
			////////////////////////////
			$RBN_sFROM  = " FROM rbn.BOKHST_LINE l ";
			$RBN_sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
			$RBN_sFROM .= " left join rbn.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";

			$sql = $sCount." as RBN_COUNT ".$RBN_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_TOTAL_COUNT = $row["RBN_COUNT"];
			}

			$sql = $sSls." as RBN_TOTAL_SLS ".$RBN_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_TOTAL_SLS = $row["RBN_TOTAL_SLS"];
			}





			////////////////////////////
			// NSA-RUBIN Industrial Intercompany
			////////////////////////////
			$NSA_RBN_IND_IC_sFROM  = " FROM nsa.BOKHST_LINE l ";
			$NSA_RBN_IND_IC_sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
			$NSA_RBN_IND_IC_sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
			
			$sql = $sCount." as NSA_RBN_IND_IC_TOTAL_COUNT ".$NSA_RBN_IND_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_CUST = '754050'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_RBN_IND_IC_TOTAL_COUNT = $row["NSA_RBN_IND_IC_TOTAL_COUNT"];
			}

			$sql = $sSls." as NSA_RBN_IND_IC_TOTAL_SLS ".$NSA_RBN_IND_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_CUST = '754050'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_RBN_IND_IC_TOTAL_SLS = $row["NSA_RBN_IND_IC_TOTAL_SLS"];
			}



			////////////////////////////
			// NSA-RUBIN Military Intercompany
			////////////////////////////
			$NSA_RBN_MIL_IC_sFROM  = " FROM nsa.BOKHST_LINE l ";
			$NSA_RBN_MIL_IC_sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
			$NSA_RBN_MIL_IC_sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
			
			$sql = $sCount." as NSA_RBN_MIL_IC_TOTAL_COUNT ".$NSA_RBN_MIL_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_CUST = 'D75380'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_RBN_MIL_IC_TOTAL_COUNT = $row["NSA_RBN_MIL_IC_TOTAL_COUNT"];
			}

			$sql = $sSls." as NSA_RBN_MIL_IC_TOTAL_SLS ".$NSA_RBN_MIL_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_CUST = 'D75380'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_RBN_MIL_IC_TOTAL_SLS = $row["NSA_RBN_MIL_IC_TOTAL_SLS"];
			}
*/




			////////////////////////////
			// RUBIN-NSA Intercompany
			////////////////////////////
			if (new DateTime($today) < new DateTime('2020-09-01')) {
				$RBN_NSA_IC_sFROM  = " FROM rbn.BOKHST_LINE l ";
				$RBN_NSA_IC_sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_NSA_IC_sFROM .= " left join rbn.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				$sLOC_Clause = "";
			} else {
				$RBN_NSA_IC_sFROM  = " FROM nsa.BOKHST_LINE l ";
				$RBN_NSA_IC_sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_NSA_IC_sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				$sLOC_Clause = " and l.ID_LOC = '20' ";
			}

			$sql = $sCount." as RBN_NSA_IC_TOTAL_COUNT ".$RBN_NSA_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and ltrim(l.ID_CUST) = '14244'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_NSA_IC_TOTAL_COUNT = $row["RBN_NSA_IC_TOTAL_COUNT"];
			}

			$sql = $sSls." as RBN_NSA_IC_TOTAL_SLS ".$RBN_NSA_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and ltrim(l.ID_CUST) = '14244'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_NSA_IC_TOTAL_SLS = $row["RBN_NSA_IC_TOTAL_SLS"];
			}

			//$IC_TOTAL_COUNT = $NSA_RBN_IND_IC_TOTAL_COUNT + $NSA_RBN_MIL_IC_TOTAL_COUNT + $RBN_NSA_IC_TOTAL_COUNT;
			//$IC_TOTAL_SLS = $NSA_RBN_IND_IC_TOTAL_SLS + $NSA_RBN_MIL_IC_TOTAL_SLS + $RBN_NSA_IC_TOTAL_SLS;
			$IC_TOTAL_COUNT = $RBN_NSA_IC_TOTAL_COUNT;
			$IC_TOTAL_SLS = $RBN_NSA_IC_TOTAL_SLS;




			if ($TOTAL_SLS <> '0' OR $TOTAL_COUNT) {
				$subject = "NSA - Chicago Bookings Summary for " . $today;

				$body  = "NSA - Chicago Bookings on " . $today . ".\r\n";
				$body .= "\r\n Ad Specialty: 			" . $ADS_COUNT . " orders,	Bookings	" . money_format('%(n',$ADS_SLS);
				$body .= "\r\n Customer Specific: 		" . $CS_COUNT . " orders,	Bookings	" . money_format('%(n',$CS_SLS);	
				$body .= "\r\n ETA Emb. Garments: 		" . $ETA_COUNT . " orders,	Bookings	" . money_format('%(n',$ETA_SLS);
				$body .= "\r\n FR Garments: 			" . $FR_COUNT . " orders,	Bookings	" . money_format('%(n',$FR_SLS);
				$body .= "\r\n Non-FR Garments: 		" . $NFR_COUNT . " orders,	Bookings	" . money_format('%(n',$NFR_SLS);
				$body .= "\r\n USPS Union Line: 		" . $USPS_UL_COUNT . " orders,	Bookings	" . money_format('%(n',$USPS_UL_SLS);
				$body .= "\r\n USPS Private Label: 		" . $USPS_PL_COUNT . " orders,	Bookings	" . money_format('%(n',$USPS_PL_SLS);
				$body .= "\r\n NSA Items: 			" . $NSA_COUNT . " orders,	Bookings	" . money_format('%(n',$NSA_SLS);
				$body .= "\r\n Other: 				" . $OTHER_COUNT . " orders,	Bookings	" . money_format('%(n',$OTHER_SLS);
				$body .= "\r\n Total: 				" . $TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$TOTAL_SLS);
				

				//$body .= "\r\n\r\n Rubin: 			" . $RBN_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$RBN_TOTAL_SLS);
				//$body .= "\r\n Intercompany(IC):";
				//$body .= "\r\n NSA-Rubin IND IC: 	" . $NSA_RBN_IND_IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$NSA_RBN_IND_IC_TOTAL_SLS);
				//$body .= "\r\n NSA-Rubin MIL IC: 	" . $NSA_RBN_MIL_IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$NSA_RBN_MIL_IC_TOTAL_SLS);

				$body .= "\r\n\r\n NSA-Chicago - NSA IC: 			" . $RBN_NSA_IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$RBN_NSA_IC_TOTAL_SLS);
				//$body .= "\r\n Total IC: 		" . $IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$IC_TOTAL_SLS);
				
				$body .= "\r\n\r\n GRAND TOTAL Less IC: 		" . ($TOTAL_COUNT + $RBN_TOTAL_COUNT - $IC_TOTAL_COUNT) . " orders,	Bookings	" . money_format('%(n',($TOTAL_SLS + $RBN_TOTAL_SLS - $IC_TOTAL_SLS));

				//$body .= "\r\n\r\n_____________________Informational____________________";
				//$body .= "\r\n EDI: 			" . $EDI_COUNT . " orders,	Bookings	" . money_format('%(n',$EDI_SLS);
				//$body .= "\r\n EDI Order %: 		" . round(($EDI_COUNT / $TOTAL_COUNT)*100,2) . "%,		EDI Booking %:	" . round(($EDI_SLS / $TOTAL_SLS)*100,2) . "%";

				$headers = "From: eProduction@thinknsa.com" . "\r\n" .
					"X-Mailer: PHP/" . phpversion();

				if ($DEBUG) {
					$to = "gvandyne@thinknsa.com";
					error_log("BOK_SUM_RBN: " . $to);
					mail($to, $subject, $body, $headers);					
				} else {
					$to = "group-RBN-BookingSummary@thinknsa.com";
					if (isset($argv[1])) {
						if ($argv[1] == 'ALL')  {
							error_log("PARAMS: " . $argv[1]);
							$to = "group-RBN-BookingSummary@thinknsa.com";
						} else {
							$to = $argv;
						}
					}
					error_log("BOK_SUM_RBN: " . $to);
					mail($to, $subject, $body, $headers);
				}

			}
			$EndTime = date('Y-m-d g:i:s a');
			error_log("### runDAILY_BOOKINGS_RUBIN finished at " . $EndTime);
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS_RUBIN cannot disconnect from database");
		}
	}
?>
