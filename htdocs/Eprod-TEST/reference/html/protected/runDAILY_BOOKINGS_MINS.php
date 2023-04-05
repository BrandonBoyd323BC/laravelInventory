<?php
	$DEBUG = 1;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_BOOKINGS_MINS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS_MINS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runDAILY_BOOKINGS_MINS started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');
			$today = date('2017-04-12');

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);			

			$sql  = " SELECT mt.ID_WC, wc.DESCR_WC, sum(mt.MINUTES) as SUM_MIN_WC ";
			$sql .= " from ( ";
			$sql .= " 	select ro.ID_ITEM, ro.ID_WC, ro.HR_MACH_SR, bl1.SUM_QTY_OPEN, (ro.HR_MACH_SR * 60 * bl1.SUM_QTY_OPEN) as MINUTES ";
			$sql .= " 	from nsa.ROUTMS_OPER ro ";
			$sql .= " 	left join ( ";
			$sql .= " 		select bl.ID_ITEM, sum(bl.QTY_OPEN) as SUM_QTY_OPEN ";
			$sql .= " 		from nsa.BOKHST_LINE bl ";
			$sql .= " 		where bl.DATE_BOOK_LAST = '".$today."' ";
			$sql .= " 		group by bl.ID_ITEM ";
			$sql .= " 	) bl1 ";
			$sql .= " 	on ro.ID_ITEM = bl1.ID_ITEM ";
			$sql .= " 	left join nsa.ITMMAS_LOC il ";
			$sql .= " 	on ro.ID_ITEM = il.ID_ITEM ";
			$sql .= " 	and il.ID_LOC = '10' ";
			$sql .= " 	where ro.ID_RTE = 'TSS' ";
			$sql .= " 	and bl1.SUM_QTY_OPEN is not NULL ";
			$sql .= " 	and ro.HR_MACH_SR <> 0 ";
			$sql .= " 	and ro.id_wc >= 2000 and ro.id_wc < 8000 ";
			$sql .= " 	and il.FLAG_SOURCE = 'M' ";
			$sql .= " ) mt ";
			$sql .= " left join nsa.tables_loc_dept_wc wc ";
			$sql .= " on mt.id_wc = wc.id_wc ";
			$sql .= " group by mt.ID_WC, wc.DESCR_WC ";
			$sql .= " order by ID_WC asc ";
			QueryDatabase($sql, $results);

			if (mssql_num_rows($results) > 0) {
				$subject = "Bookings Summary of Incoming Workcenter Minutes for " . $today;
				$body  = "Bookings Summary of Incoming Workcenter Minutes on " . $today . ".\r\n";
				$headers = "From: eProduction@thinknsa.com"."\r\n"."X-Mailer: PHP/".phpversion();

				$TOTAL_MINS = 0;
				$body .= "<table>";
				while ($row = mssql_fetch_assoc($results)) {
					$TOTAL_MINS += $row['SUM_MIN_WC'];
					//$body .= "\r\n ".$row['ID_WC']." 		" . $row['DESCR_WC'] . "			" . $row['SUM_MIN_WC'];
					$body .= "<tr><td>" . $row['ID_WC'] . "</td><td>" . $row['DESCR_WC'] . "</td><td>" . $row['SUM_MIN_WC'] . "</td></tr>";
				}
				//$body .= "\r\n\r\n Total: 					" . $TOTAL_MINS;
				$body .= "<tr><td colspan=2>Total:</td><td>" . $TOTAL_MINS . "</td></tr>";
				$body .= "</table>";

				if ($TEST_ENV) {
					$to = "gvandyne@thinknsa.com";
					error_log("BOK_SUM_MINS: " . $to);
					mail($to, $subject, $body, $headers);
				} else {
					if ($argv[1] == 'ALL')  {
						error_log("PARAMS: " . $argv[1]);
						$aa_to  = GetEmailSubscribers('BOK_MINS');
					} else {
						$aa_to = $argv;
					}
					foreach ($aa_to as $to) {
						error_log("BOK_SUM_MINS: " . $to);
						mail($to, $subject, $body, $headers);
					}
				}
			}


/*
			$sql = " IF OBJECT_ID('tempdb..#temp_bok_mins') IS NOT NULL";
			$sql .= "	DROP TABLE #temp_bok_mins";
			QueryDatabase($sql, $results);

			$sql_tt  = " create table #temp_bok_mins ( ";
			$sql_tt .= " ID_ITEM varchar(30), ";
			$sql_tt .= " QTY_ORD numeric(8,0), ";
			$sql_Zeros = "";

			$sql  = "SELECT ";
			$sql .= " ID_WC, ";
			$sql .= " DESCR_WC ";
			$sql .= " FROM nsa.tables_loc_dept_wc ";
			$sql .= " WHERE ID_WC between '1000' and '7999' ";
			QueryDatabase($sql, $results);
			$num_wcs = mssql_num_rows($results);
			while ($row = mssql_fetch_assoc($results)) {
				$sql_tt .= " WC_" . $row['ID_WC'] . " numeric(12,4) default 0, ";
				$sql_Zeros .= " 0, ";
			}
			
			$sql_tt = rtrim($sql_tt,', ');
			$sql_tt .= " )";
			$sql_Zeros = rtrim($sql_Zeros,', ');
			QueryDatabase($sql_tt, $results_tt);

			////////////////
			// POPULATE #temp_bok_mins
			////////////////
			$sql = " Insert into #temp_bok_mins";
			$sql .= " SELECT ";
			$sql .= " bl.ID_ITEM, ";
			$sql .= " sum(bl.QTY_OPEN) as SUM_QTY_OPEN, ";
			$sql .= $sql_Zeros;
			$sql .= " FROM nsa.BOKHST_LINE bl ";
			//$sql .= " WHERE bl.DATE_BOOK_LAST = '".$today."' ";
			$sql .= " WHERE bl.DATE_BOOK_LAST = '2015-12-11' ";
			$sql .= " GROUP BY bl.ID_ITEM ";
			$sql .= " ORDER BY bl.ID_ITEM ";
			QueryDatabase($sql, $results);

			$sql  = "SELECT * ";
			$sql .= " FROM #temp_bok_mins";
			QueryDatabase($sql, $results);

			//for each item in the temp table, find all manufactured items in their BOM.  
			while ($row = mssql_fetch_assoc($results)) {
				$sql1  = "SELECT ";
				$sql1 .= " il.FLAG_SOURCE, ";
				$sql1 .= " ps.* ";
				$sql1 .= " FROM nsa.PRDSTR ps ";
				$sql1 .= " left join nsa.ITMMAS_LOC il ";
				$sql1 .= " on ps.ID_ITEM_COMP = il.ID_ITEM ";
				$sql1 .= " where ps.ID_ITEM_PAR = '" . $row['ID_ITEM'] . "' ";
				$sql1 .= " and il.FLAG_SOURCE = 'M' ";
				QueryDatabase($sql1, $results1);
				while ($row1 = mssql_fetch_assoc($results1)) {
					error_log("ID_ITEM_COMP: " . $row1['ID_ITEM_COMP']);
					error_log("QTY_PER: " . $row1['QTY_PER']);
				}
			}

			$sql  = " SELECT ";
			$sql .= " bl.ID_ITEM, "; 
			$sql .= " bl.QTY_OPEN, ";
			$sql .= " il.ID_RTE, ";
			$sql .= " ro.ID_OPER, ";
			$sql .= " ro.ID_WC, ";
			$sql .= " ro.HR_MACH_SR, ";
			$sql .= " (ro.HR_MACH_SR * 60) as MINS ";
			$sql .= " from nsa.BOKHST_LINE bl ";
			$sql .= " left join nsa.ITMMAS_LOC il ";
			$sql .= " on bl.ID_ITEM = il.ID_ITEM ";
			$sql .= " and il.ID_LOC = '10' ";
			$sql .= " left join nsa.ROUTMS_OPER ro ";
			$sql .= " on il.ID_ITEM = ro.ID_ITEM ";
			$sql .= " and il.ID_RTE = ro.ID_RTE ";
			$sql .= " where bl.DATE_BOOK_LAST = '2015-12-11' ";
			//$sql .= " where bl.DATE_BOOK_LAST = '".$today."' ";
			$sql .= " order by bl.ID_ORD ";
*/


			error_log("### runDAILY_BOOKINGS_MINS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS_MINS cannot disconnect from database");
		}
	}
?>
