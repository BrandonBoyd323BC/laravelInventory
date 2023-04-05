<?php
	
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	$DEBUG = 1;
	
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_BOOKINGS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS cannot select " . $dbName);
		} else {
			$StartTime = date('Y-m-d g:i:s a');
			error_log("#############################################");
			error_log("### runDAILY_BOOKINGS started at " . $StartTime);
			$today = date('Y-m-d');
			//$today = date('2020-05-12');

			$sCount  = " select count(distinct(l.ID_ORD)) ";
			$sSls    = " select COALESCE(sum(l.SLS),0) ";

			$sFROM  = " FROM nsa.BOKHST_LINE l ";
			$sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
			$sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";

			//$NSA_COUNT = '0';
			//$DRF_IND_COUNT = '0';
			//$DRF_MIL_COUNT = '0';
			//$DRF_INT_COUNT = '0';
			//$DRF_OTHER_COUNT = '0';
			$TOTAL_COUNT = '0';
			$RBN_COUNT = '0';

			$NSA_RBN_IC_COUNT = '0';			
			//$NSA_RBN_IND_IC_COUNT = '0';
			//$NSA_RBN_MIL_IC_COUNT = '0';
			$RBN_NSA_IC_COUNT = '0';
			
			//$NSA_SLS = '0';
			//$DRF_IND_SLS = '0';
			//$DRF_MIL_SLS = '0';
			//$DRF_INT_SLS = '0';
			//$DRF_OTHER_SLS = '0';
			$TOTAL_SLS = '0';
			$RBN_TOTAL_SLS = '0';
			$NSA_RBN_IC_TOTAL_SLS = '0';
			//$NSA_RBN_IND_IC_TOTAL_SLS = '0';
			//$NSA_RBN_MIL_IC_TOTAL_SLS = '0';
			$RBN_NSA_IC_TOTAL_SLS = '0';
			

/*
			$sql = $sCount." as NSA_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST NOT like 'D%' and ib.CODE_CAT_PRDT not in ('D1','D2')";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_COUNT = $row["NSA_COUNT"];
			}

			$sql = $sCount." as DRF_IND_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST NOT like 'D%' and ib.CODE_CAT_PRDT in ('D1','D2')";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_IND_COUNT = $row["DRF_IND_COUNT"];
			}

			$sql = $sCount." as DRF_MIL_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ltrim(bh.ID_SLSREP_1) = '103'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_MIL_COUNT = $row["DRF_MIL_COUNT"];
			}

			$sql = $sCount." as DRF_INT_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ltrim(bh.ID_SLSREP_1) = '104'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_INT_COUNT = $row["DRF_INT_COUNT"];
			}

			$sql = $sCount." as DRF_OTHER_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_SLSREP_1) not in ('103','104')";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_OTHER_COUNT = $row["DRF_OTHER_COUNT"];
			}
*/
			$sql = $sCount." as TOTAL_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_LOC = '10' ";
			$sql .= " and l.CODE_USER_2_IM <> 'USPS'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_COUNT = $row["TOTAL_COUNT"];
			}
/*
			$sql = "SELECT count(*) as EDI_COUNT FROM nsa.CP_ORDHDR where DATE_ADD = '".$today."' and ID_USER_ADD = 'EDI'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$EDI_COUNT = $row["EDI_COUNT"];
			}			

			$sql = $sSls." as NSA_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST NOT like 'D%' and ib.CODE_CAT_PRDT not in ('D1','D2')";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_SLS = $row["NSA_SLS"];
			}

			$sql = $sSls." as DRF_IND_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST NOT like 'D%' and ib.CODE_CAT_PRDT in ('D1','D2')";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_IND_SLS = $row["DRF_IND_SLS"];
			}

			$sql = $sSls." as DRF_MIL_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ltrim(bh.ID_SLSREP_1) = '103'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_MIL_SLS = $row["DRF_MIL_SLS"];
			}

			$sql = $sSls." as DRF_INT_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and ltrim(bh.ID_SLSREP_1) = '104'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_INT_SLS = $row["DRF_INT_SLS"];
			}

			$sql = $sSls." as DRF_OTHER_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_SLSREP_1) not in ('103','104') ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$DRF_OTHER_SLS = $row["DRF_OTHER_SLS"];
			}
*/
			$sql = $sSls." as TOTAL_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_LOC = '10' ";
			$sql .= " and l.CODE_USER_2_IM <> 'USPS'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_SLS = $row["TOTAL_SLS"];
			}
/*
			$sql = "SELECT sum(AMT_ORD_TOTAL) as EDI_SLS FROM nsa.CP_ORDHDR where DATE_ADD = '".$today."' and ID_USER_ADD = 'EDI'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$EDI_SLS = $row["EDI_SLS"];
			}
*/


			////////////////////////////
			// RUBIN 
			////////////////////////////
			if (new DateTime($today) < new DateTime('2020-09-01')) {
				$RBN_sFROM  = " FROM rbn.BOKHST_LINE l ";
				$RBN_sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_sFROM .= " left join rbn.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				$sLOC_Clause = "";
			} else {
				$RBN_sFROM  = " FROM nsa.BOKHST_LINE l ";
				$RBN_sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				$sLOC_Clause = " and (l.ID_LOC = '20' OR (l.ID_LOC = '10' AND l.CODE_USER_2_IM = 'USPS'))";
			}

			$sql = $sCount." as RBN_COUNT ".$RBN_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_TOTAL_COUNT = $row["RBN_COUNT"];
			}

			$sql = $sSls." as RBN_TOTAL_SLS ".$RBN_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= $sLOC_Clause;
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_TOTAL_SLS = $row["RBN_TOTAL_SLS"];
			}




/*
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
			// NSA-RUBIN Intercompany
			////////////////////////////
			$NSA_RBN_IC_sFROM  = " FROM nsa.BOKHST_LINE l ";
			$NSA_RBN_IC_sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
			$NSA_RBN_IC_sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
			
			$sql = $sCount." as NSA_RBN_IC_TOTAL_COUNT ".$NSA_RBN_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_CUST in ('754050','D75380')";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_RBN_IC_TOTAL_COUNT = $row["NSA_RBN_IC_TOTAL_COUNT"];
			}

			$sql = $sSls." as NSA_RBN_IC_TOTAL_SLS ".$NSA_RBN_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and l.ID_CUST in ('754050','D75380')";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$NSA_RBN_IC_TOTAL_SLS = $row["NSA_RBN_IC_TOTAL_SLS"];
			}



			////////////////////////////
			// RUBIN-NSA Intercompany
			////////////////////////////
			if (new DateTime($today) < new DateTime('2020-09-01')) {
				$RBN_NSA_IC_sFROM  = " FROM rbn.BOKHST_LINE l ";
				$RBN_NSA_IC_sFROM .= " left join rbn.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_NSA_IC_sFROM .= " left join rbn.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				//$sLOC_Clause = "";
			} else {
				$RBN_NSA_IC_sFROM  = " FROM nsa.BOKHST_LINE l ";
				$RBN_NSA_IC_sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
				$RBN_NSA_IC_sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
				//$sLOC_Clause = " and l.ID_LOC = '20' ";
			}
			$sql = $sCount." as RBN_NSA_IC_TOTAL_COUNT ".$RBN_NSA_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and ltrim(l.ID_CUST) = '14244'";

			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_NSA_IC_TOTAL_COUNT = $row["RBN_NSA_IC_TOTAL_COUNT"];
			}

			$sql = $sSls." as RBN_NSA_IC_TOTAL_SLS ".$RBN_NSA_IC_sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and ltrim(l.ID_CUST) = '14244'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$RBN_NSA_IC_TOTAL_SLS = $row["RBN_NSA_IC_TOTAL_SLS"];
			}


/*
			$IC_TOTAL_COUNT = $NSA_RBN_IND_IC_TOTAL_COUNT + $NSA_RBN_MIL_IC_TOTAL_COUNT + $RBN_NSA_IC_TOTAL_COUNT;
			$IC_TOTAL_SLS = $NSA_RBN_IND_IC_TOTAL_SLS + $NSA_RBN_MIL_IC_TOTAL_SLS + $RBN_NSA_IC_TOTAL_SLS;
*/
			$IC_TOTAL_COUNT = $NSA_RBN_IC_TOTAL_COUNT + $RBN_NSA_IC_TOTAL_COUNT;
			$IC_TOTAL_SLS = $NSA_RBN_IC_TOTAL_SLS + $RBN_NSA_IC_TOTAL_SLS;

			if ($TOTAL_SLS <> '0' OR $TOTAL_COUNT <> '0' OR $RBN_TOTAL_SLS <> '0' OR $RBN_TOTAL_COUNT) {
/*
				$subject = "Bookings Summary for " . $today;

				$body  = "Bookings on " . $today . ".\r\n";
				//$body .= "\r\n NSA: 			" . $NSA_COUNT . " orders,	Bookings	" . money_format('%(n',$NSA_SLS);
				//$body .= "\r\n DRIFIRE IND: 		" . $DRF_IND_COUNT . " orders,	Bookings	" . money_format('%(n',$DRF_IND_SLS);	
				//$body .= "\r\n DRIFIRE MIL: 		" . $DRF_MIL_COUNT . " orders,	Bookings	" . money_format('%(n',$DRF_MIL_SLS);
				//$body .= "\r\n DRIFIRE INT: 		" . $DRF_INT_COUNT . " orders,	Bookings	" . money_format('%(n',$DRF_INT_SLS);
				//$body .= "\r\n Total: 			" . $TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$TOTAL_SLS);
				
				$body .= "\r\n NSA: 			" . $TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$TOTAL_SLS);
				$body .= "\r\n Rubin: 			" . $RBN_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$RBN_TOTAL_SLS);
				$body .= "\r\n Total: 			" . ($TOTAL_COUNT+$RBN_TOTAL_COUNT) . " orders,	Bookings	" . money_format('%(n',($TOTAL_SLS+$RBN_TOTAL_SLS));
				
				//$body .= "\r\n\r\n Intercompany(IC):";
				//$body .= "\r\n NSA-Rubin IND IC: 	" . $NSA_RBN_IND_IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$NSA_RBN_IND_IC_TOTAL_SLS);
				//$body .= "\r\n NSA-Rubin MIL IC: 	" . $NSA_RBN_MIL_IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$NSA_RBN_MIL_IC_TOTAL_SLS);
				$body .= "\r\n\r\n NSA-Rubin IC: 		" . ($NSA_RBN_MIL_IC_TOTAL_COUNT+$NSA_RBN_IND_IC_TOTAL_COUNT) . " orders,	Bookings	" . money_format('%(n',($NSA_RBN_MIL_IC_TOTAL_SLS+$NSA_RBN_IND_IC_TOTAL_SLS));

				$body .= "\r\n Rubin-NSA IC: 		" . $RBN_NSA_IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$RBN_NSA_IC_TOTAL_SLS);
				$body .= "\r\n Total IC: 		" . $IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$IC_TOTAL_SLS);
				
				$body .= "\r\n\r\n GRAND TOTAL: 	" . ($TOTAL_COUNT + $RBN_TOTAL_COUNT - $IC_TOTAL_COUNT) . " orders,	Bookings	" . money_format('%(n',($TOTAL_SLS + $RBN_TOTAL_SLS - $IC_TOTAL_SLS));

				//$body .= "\r\n\r\n_____________________Informational____________________";
				//$body .= "\r\n EDI: 			" . $EDI_COUNT . " orders,	Bookings	" . money_format('%(n',$EDI_SLS);
				//$body .= "\r\n EDI Order %: 		" . round(($EDI_COUNT / $TOTAL_COUNT)*100,2) . "%,		EDI Booking %:	" . round(($EDI_SLS / $TOTAL_SLS)*100,2) . "%";

*/

				$subject = "Bookings Summary for " . $today;

				$body  = "Bookings on " . $today . ".\r\n";

				$body .= "\r\n NSA: 			" . $TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$TOTAL_SLS);
				$body .= "\r\n Rubin: 			" . $RBN_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$RBN_TOTAL_SLS);
				$body .= "\r\n Total: 			" . ($TOTAL_COUNT+$RBN_TOTAL_COUNT) . " orders,	Bookings	" . money_format('%(n',($TOTAL_SLS+$RBN_TOTAL_SLS));

				$body .= "\r\n\r\n NSA-Rubin IC: 		" . ($NSA_RBN_IC_TOTAL_COUNT) . " orders,	Bookings	" . money_format('%(n',($NSA_RBN_IC_TOTAL_SLS));
				$body .= "\r\n Rubin-NSA IC: 		" . $RBN_NSA_IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$RBN_NSA_IC_TOTAL_SLS);
				$body .= "\r\n Total IC: 		" . $IC_TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$IC_TOTAL_SLS);
				
				$body .= "\r\n\r\n GRAND TOTAL: 	" . ($TOTAL_COUNT + $RBN_TOTAL_COUNT - $IC_TOTAL_COUNT) . " orders,	Bookings	" . money_format('%(n',($TOTAL_SLS + $RBN_TOTAL_SLS - $IC_TOTAL_SLS));



				$headers = "From: eProduction@thinknsa.com" . "\r\n" .
					"X-Mailer: PHP/" . phpversion();

				if ($DEBUG) {
					$to = "gvandyne@thinknsa.com";
					error_log("BOK_SUM: " . $to);
					mail($to, $subject, $body, $headers);					
				} else {
					$to = "group-bookingsummary@thinknsa.com";
					if (isset($argv[1])) {
						if ($argv[1] == 'ALL')  {
							error_log("PARAMS: " . $argv[1]);
							$to = "group-bookingsummary@thinknsa.com";
						} else {
							$to = $argv;
						}
					}
					error_log("BOK_SUM: " . $to);
					mail($to, $subject, $body, $headers);
				}

			}
			$EndTime = date('Y-m-d g:i:s a');
			error_log("### runDAILY_BOOKINGS finished at " . $EndTime);
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS cannot disconnect from database");
		}
	}
?>
