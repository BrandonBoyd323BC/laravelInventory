<?php
	$DEBUG = 1;
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
			$today = '2018-07-13';

/*
			$sql  = " SELECT ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.BOKHST_LINE l left join nsa.ITMMAS_LOC il on l.ID_ITEM = il.ID_ITEM where l.DATE_BOOK_LAST = '".$today."' and ID_CUST NOT like 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.BOKHST_LINE l left join nsa.ITMMAS_LOC il on l.ID_ITEM = il.ID_ITEM where l.DATE_BOOK_LAST = '".$today."' and ((ID_CUST NOT LIKE 'D%' and il.ID_PLANNER in ('D1','D2')) OR ID_CUST = 'D01686')) as DRF_IND_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.BOKHST_LINE l left join nsa.ITMMAS_LOC il on l.ID_ITEM = il.ID_ITEM where l.DATE_BOOK_LAST = '".$today."' and ID_CUST LIKE 'D%' and ID_CUST <> 'D01686') as DRF_MIL_INT_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.BOKHST_LINE l where l.DATE_BOOK_LAST = '".$today."') as TOTAL_COUNT, ";

			$sql .= " (select COALESCE(sum(l.SLS),0) FROM nsa.BOKHST_LINE l left join nsa.ITMMAS_LOC il on l.ID_ITEM = il.ID_ITEM where l.DATE_BOOK_LAST = '".$today."' and ID_CUST NOT like 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_SLS, ";
			$sql .= " (select COALESCE(sum(l.SLS),0) FROM nsa.BOKHST_LINE l left join nsa.ITMMAS_LOC il on l.ID_ITEM = il.ID_ITEM where l.DATE_BOOK_LAST = '".$today."' and ((ID_CUST NOT LIKE 'D%' and il.ID_PLANNER in ('D1','D2')) OR ID_CUST = 'D01686')) as DRF_IND_SLS, ";
			$sql .= " (select COALESCE(sum(l.SLS),0) FROM nsa.BOKHST_LINE l left join nsa.ITMMAS_LOC il on l.ID_ITEM = il.ID_ITEM where l.DATE_BOOK_LAST = '".$today."' and ID_CUST LIKE 'D%' and ID_CUST <> 'D01686') as DRF_MIL_INT_SLS, ";
			$sql .= " (select COALESCE(sum(l.SLS),0) FROM nsa.BOKHST_LINE l left join nsa.ITMMAS_LOC il on l.ID_ITEM = il.ID_ITEM where l.DATE_BOOK_LAST = '".$today."') as TOTAL_SLS ";
			QueryDatabase($sql, $results);
*/

			$sCount  = " select count(distinct(l.ID_ORD)) ";
			$sSls    = " select COALESCE(sum(l.SLS),0) ";

			//$sFROM  = " FROM nsa.BOKHST_LINE l ";
			//$sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";
			//$sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";



			$sFROM  = " FROM nsa.BOKHST_LINE l ";
			//$sFROM .= " left join nsa.BOKHST_HDR bh on l.ID_ORD = bh.ID_ORD and l.SEQ_SHIPTO = bh.SEQ_SHIPTO ";
			$sFROM .= " cross apply ( select top 1 bh.ID_TERR, bh.ID_SLSREP_1, bh.ID_CUST, bh.ID_ORD, bh.ID_PO_CUST, bh.SEQ_SHIPTO from nsa.BOKHST_HDR bh ";
			$sFROM .= " where l.ID_ORD = bh.ID_ORD AND l.ID_CUST = bh.ID_CUST AND l.SEQ_SHIPTO = bh.SEQ_SHIPTO ) bh ";
			$sFROM .= " left join nsa.ITMMAS_BASE ib on l.ID_ITEM = ib.ID_ITEM ";



/*
			$sql  = " SELECT ";
			$sql .= " (".$sCount.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST NOT like 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_COUNT, ";
			$sql .= " (".$sCount.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and il.ID_PLANNER in ('D1','D2') and ltrim(bh.ID_TERR) not in ('103','104')) as DRF_IND_COUNT, ";
			$sql .= " (".$sCount.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_TERR) = '103') as DRF_MIL_COUNT, ";
			$sql .= " (".$sCount.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_TERR) = '104') as DRF_INT_COUNT, ";
			$sql .= " (".$sCount.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_TERR) not in ('103','104')) as DRF_OTHER_COUNT, ";
			$sql .= " (".$sCount.$sFROM." where l.DATE_BOOK_LAST = '".$today."' ) as TOTAL_COUNT, ";
			$sql .= " (".$sSls.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST NOT like 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_SLS, ";
			$sql .= " (".$sSls.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and il.ID_PLANNER in ('D1','D2') and ltrim(bh.ID_TERR) not in ('103','104')) as DRF_IND_SLS, ";
			$sql .= " (".$sSls.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_TERR) = '103') as DRF_MIL_SLS, ";
			$sql .= " (".$sSls.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_TERR) = '104') as DRF_INT_SLS, ";
			$sql .= " (".$sSls.$sFROM." where l.DATE_BOOK_LAST = '".$today."' and l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_TERR) not in ('103','104')) as DRF_OTHER_SLS, ";
			$sql .= " (".$sSls.$sFROM." where l.DATE_BOOK_LAST = '".$today."' ) as TOTAL_SLS ";
			QueryDatabase($sql, $results);
*/


			$NSA_COUNT = '0';
			$DRF_IND_COUNT = '0';
			$DRF_MIL_COUNT = '0';
			$DRF_INT_COUNT = '0';
			$DRF_OTHER_COUNT = '0';
			$OTHER_COUNT = '0';
			$TOTAL_COUNT = '0';
			$NSA_SLS = '0';
			$DRF_IND_SLS = '0';
			$DRF_MIL_SLS = '0';
			$DRF_INT_SLS = '0';
			$DRF_OTHER_SLS = '0';
			$OTHER_SLS = '0';
			$TOTAL_SLS = '0';


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




/*
			$sql = $sCount." as OTHER_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and (l.ID_CUST like 'D%' and ib.CODE_CAT_PRDT not in ('D1','D2'))";
			$sql .= " and (l.ID_CUST like 'D%' and ib.CODE_CAT_PRDT in ('D1','D2'))";
			$sql .= " and (ltrim(bh.ID_SLSREP_1) <> '103')";
			$sql .= " and (ltrim(bh.ID_SLSREP_1) <> '104')";
			$sql .= " and (l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_SLSREP_1) not in ('103','104'))";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$OTHER_COUNT = $row["OTHER_COUNT"];
			}
*/



			$sql = $sCount." as TOTAL_COUNT ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_COUNT = $row["TOTAL_COUNT"];
			}

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


/*
			$sql = $sSls." as OTHER_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			$sql .= " and (l.ID_CUST NOT like 'D%' and ib.CODE_CAT_PRDT not in ('D1','D2'))";
			$sql .= " and (l.ID_CUST NOT like 'D%' and ib.CODE_CAT_PRDT in ('D1','D2'))";
			$sql .= " and (ltrim(bh.ID_SLSREP_1) = '103')";
			$sql .= " and (ltrim(bh.ID_SLSREP_1) = '104')";
			$sql .= " and (l.ID_CUST LIKE 'D%' and l.ID_CUST <> 'D01686' and ltrim(bh.ID_SLSREP_1) not in ('103','104'))";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$OTHER_SLS = $row["OTHER_SLS"];
			}
*/




			$sql = $sSls." as TOTAL_SLS ".$sFROM." where l.DATE_BOOK_LAST = '".$today."'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$TOTAL_SLS = $row["TOTAL_SLS"];
			}

			$sql = "SELECT sum(AMT_ORD_TOTAL) as EDI_SLS FROM nsa.CP_ORDHDR where DATE_ADD = '".$today."' and ID_USER_ADD = 'EDI'";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$EDI_SLS = $row["EDI_SLS"];
			}

			if ($TOTAL_SLS <> '0' OR $TOTAL_COUNT <> '0') {
				$subject = "Bookings Summary for " . $today;

				$body  = "Bookings on " . $today . ".\r\n";
				$body .= "\r\n NSA: 		" . $NSA_COUNT . " orders,	Bookings	" . money_format('%(n',$NSA_SLS);
				$body .= "\r\n DRIFIRE IND: 	" . $DRF_IND_COUNT . " orders,	Bookings	" . money_format('%(n',$DRF_IND_SLS);	
				$body .= "\r\n DRIFIRE MIL: 	" . $DRF_MIL_COUNT . " orders,	Bookings	" . money_format('%(n',$DRF_MIL_SLS);
				$body .= "\r\n DRIFIRE INT: 	" . $DRF_INT_COUNT . " orders,	Bookings	" . money_format('%(n',$DRF_INT_SLS);
				$body .= "\r\n OTHER: 	" . $OTHER_COUNT . " orders,	Bookings	" . money_format('%(n',$OTHER_SLS);
				$body .= "\r\n Total: 		" . $TOTAL_COUNT . " orders,	Bookings	" . money_format('%(n',$TOTAL_SLS);
				$body .= "\r\n\r\n_____________________Informational____________________";
				$body .= "\r\n EDI: 		" . $EDI_COUNT . " orders,	Bookings	" . money_format('%(n',$EDI_SLS);
				$body .= "\r\n EDI Order %: 	" . round(($EDI_COUNT / $TOTAL_COUNT)*100,2) . "%,		EDI Booking %:	" . round(($EDI_SLS / $TOTAL_SLS)*100,2) . "%";

				$headers = "From: eProduction@thinknsa.com" . "\r\n" .
					"X-Mailer: PHP/" . phpversion();

/*
				if ($argv[1] == 'ALL')  {
					error_log("PARAMS: " . $argv[1]);
					$aa_to  = GetEmailSubscribers('BOK');
				} else {
					$aa_to = $argv;
				}
				foreach ($aa_to as $to) {
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					error_log("BOK_SUM: " . $to);
					mail($to, $subject, $body, $headers);
				}
*/

					$to = "gvandyne@thinknsa.com";
					error_log("BOK_SUM: " . $to);
					mail($to, $subject, $body, $headers);


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
