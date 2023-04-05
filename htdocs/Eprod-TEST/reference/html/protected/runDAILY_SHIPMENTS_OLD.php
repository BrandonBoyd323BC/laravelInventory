<?php
	$DEBUG = 1;
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
			$today = '2018-02-12';

			//$FC_Users = "'SCB','MSG'";
			$FC_Users = "'ASP','MSG'";

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
/*
			$sql  = " SELECT ";
			//$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO NOT like 'V%' and ID_CUST_SOLDTO NOT like 'S%' and ID_CUST_SOLDTO NOT like 'T%') as NSA_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO NOT like 'T%' and ID_CUST_SOLDTO NOT like 'D%') as NSA_COUNT, ";
			//$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO LIKE 'V%') as VINA_COUNT, ";
			//$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO LIKE 'S%') as SPX_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO LIKE 'T%') as TCG_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO LIKE 'D%') as DRF_COUNT, ";
			//$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_INVHDR_HIST s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO LIKE 'T%') as TCG_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."') as TOTAL_COUNT, ";

			$sql .= " (select count(*) FROM nsa.CP_SHPLIN s left join nsa.CP_SHPHDR h on s.ID_SHIP = h.ID_SHIP where s.DATE_ADD = '".$today."' and s.FLAG_STK = 'S' and h.ID_CUST_SOLDTO like 'T%') as TCG_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN s left join nsa.CP_SHPHDR h on s.ID_SHIP = h.ID_SHIP where s.DATE_ADD = '".$today."' and s.FLAG_STK = 'S' and h.ID_CUST_SOLDTO like 'D%') as DRF_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN s left join nsa.CP_SHPHDR h on s.ID_SHIP = h.ID_SHIP where s.DATE_ADD = '".$today."' and s.FLAG_STK = 'S' and h.ID_CUST_SOLDTO not like 'T%' and h.ID_CUST_SOLDTO not like 'D%') as NSA_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN s where s.DATE_ADD = '".$today."' and s.FLAG_STK = 'S') as TOTAL_STOCK_LINE_COUNT, ";

			//$sql .= " (select COALESCE(sum(s.AMT_ORD_TOTAL),0) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO NOT like 'V%' and ID_CUST_SOLDTO NOT like 'S%' and ID_CUST_SOLDTO NOT like 'T%') as NSA_SLS, ";
			$sql .= " (select COALESCE(sum(s.AMT_ORD_TOTAL),0) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO NOT like 'T%' and ID_CUST_SOLDTO NOT like 'D%') as NSA_SLS, ";
			//$sql .= " (select COALESCE(sum(s.AMT_ORD_TOTAL),0) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO like 'V%') as VINA_SLS, ";
			//$sql .= " (select COALESCE(sum(s.AMT_ORD_TOTAL),0) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO like 'S%') as SPX_SLS, ";
			$sql .= " (select COALESCE(sum(s.AMT_ORD_TOTAL),0) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO like 'T%') as TCG_SLS, ";
			$sql .= " (select COALESCE(sum(s.AMT_ORD_TOTAL),0) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO like 'D%') as DRF_SLS, ";
			//$sql .= " (select COALESCE(sum(h.AMT_ORD_TOTAL),0) FROM nsa.CP_INVHDR_HIST h where h.DATE_SHIP = '".$today."' and ID_CUST_SOLDTO like 'T%') as TCG_SLS, ";
			$sql .= " (select COALESCE(sum(s.AMT_ORD_TOTAL),0) FROM nsa.CP_SHPHDR s where s.DATE_SHIP = '".$today."') as TOTAL_SLS ";
			QueryDatabase($sql, $results);
*/
/*
			$sql  = " SELECT ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and ((sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER in ('D1','D2')) OR sh.ID_CUST_SOLDTO = 'D01686')) as DRF_IND_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686') as DRF_MIL_INT_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."') as TOTAL_COUNT,  ";

			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and ((sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER in ('D1','D2')) OR sh.ID_CUST_SOLDTO = 'D01686')) as DRF_IND_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686') as DRF_MIL_INT_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S') as TOTAL_STOCK_LINE_COUNT, ";

			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and ((sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER in ('D1','D2')) OR sh.ID_CUST_SOLDTO = 'D01686')) as DRF_IND_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686') as DRF_MIL_INT_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."') as TOTAL_SLS ";
			QueryDatabase($sql, $results);
*/

/*
			$sql  = " SELECT ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and il.ID_PLANNER in ('D1','D2') and ltrim(sh.ID_TERR) not in ('103','104')) as DRF_IND_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686' and ltrim(sh.ID_TERR) = '103') as DRF_MIL_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686' and ltrim(sh.ID_TERR) = '104') as DRF_INT_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."') as TOTAL_COUNT,  ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."' and sh.CODE_SRC_EDI = 850) as EDI_COUNT,  ";

			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and il.ID_PLANNER in ('D1','D2') and ltrim(sh.ID_TERR) not in ('103','104')) as DRF_IND_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686' and ltrim(sh.ID_TERR) = '103') as DRF_MIL_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686' and ltrim(sh.ID_TERR) = '104') as DRF_INT_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM and il.ID_LOC = '10' left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S') as TOTAL_STOCK_LINE_COUNT, ";

			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and il.ID_PLANNER not in ('D1','D2')) as NSA_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and il.ID_PLANNER in ('D1','D2') and ltrim(sh.ID_TERR) not in ('103','104')) as DRF_IND_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686' and ltrim(sh.ID_TERR) = '103') as DRF_MIL_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO LIKE 'D%' and sh.ID_CUST_SOLDTO <> 'D01686' and ltrim(sh.ID_TERR) = '104') as DRF_INT_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."') as TOTAL_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_LOC il on sl.ID_ITEM = il.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.CODE_SRC_EDI = 850) as EDI_SLS ";
			QueryDatabase($sql, $results);
*/

			$sql  = " SELECT ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and ib.CODE_CAT_PRDT not in ('D1','D2')) as NSA_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and ib.CODE_CAT_PRDT in ('D1','D2')) as DRF_IND_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and ltrim(sh.ID_SLSREP_1) = '103') as DRF_MIL_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and ltrim(sh.ID_SLSREP_1) = '104') as DRF_INT_COUNT, ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."') as TOTAL_COUNT,  ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."' and sh.CODE_SRC_EDI = 850) as EDI_COUNT,  ";
			//$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD = 'TCM') as WMS_COUNT,  ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD <> 'TCM') as TCM_COUNT,  ";
			//$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD in (".$FC_Users.")) as FC_COUNT,  ";
			$sql .= " (select count(distinct(pl.ID_ORD)) FROM nsa.PACKSLIP_LOG pl where pl.DATE_ADD = '".$today."' and replace(pl.PRINTED_TO,'\\\\TCM2\\','') like 'FC-%') as FC_COUNT,  ";
			//$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD NOT in (".$FC_Users.") and sh.ID_SHIP NOT in (select distinct ID_SHIP from nsa.TOUCHPOINT_FTP_FILES)) as HQ_COUNT,  ";
			$sql .= " (select count(distinct(pl.ID_ORD)) FROM nsa.PACKSLIP_LOG pl where pl.DATE_ADD = '".$today."' and replace(pl.PRINTED_TO,'\\\\TCM2\\','') like 'HQ-%' and pl.ID_SHIP NOT in (select distinct ID_SHIP from nsa.TOUCHPOINT_FTP_FILES)) as HQ_COUNT,  ";
			$sql .= " (select count(distinct(sh.ID_ORD)) FROM nsa.CP_SHPHDR sh where sh.DATE_SHIP = '".$today."' and sh.ID_SHIP in (select distinct ID_SHIP from nsa.TOUCHPOINT_FTP_FILES)) as TP_COUNT,  ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and ib.CODE_CAT_PRDT not in ('D1','D2')) as NSA_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and ib.CODE_CAT_PRDT in ('D1','D2')) as DRF_IND_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and ltrim(sh.ID_SLSREP_1) = '103') as DRF_MIL_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and ltrim(sh.ID_SLSREP_1) = '104') as DRF_INT_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S') as TOTAL_STOCK_LINE_COUNT, ";
			//$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_USER_ADD = 'TCM') as WMS_TOTAL_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_USER_ADD <> 'TCM') as TCM_TOTAL_STOCK_LINE_COUNT, ";
			//$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_USER_ADD in (".$FC_Users.")) as FC_TOTAL_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP left join nsa.PACKSLIP_LOG pl on sh.ID_SHIP = pl.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and pl.FLAG_REPRINT = 'N' and replace(pl.PRINTED_TO,'\\\\TCM2\\','') like 'FC-%' ) as FC_TOTAL_STOCK_LINE_COUNT, ";
			//$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_USER_ADD NOT in (".$FC_Users.") and sh.ID_SHIP NOT in (select distinct ID_SHIP from nsa.TOUCHPOINT_FTP_FILES)) as HQ_TOTAL_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP left join nsa.PACKSLIP_LOG pl on sl.ID_SHIP = pl.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and pl.FLAG_REPRINT = 'N' and replace(pl.PRINTED_TO,'\\\\TCM2\\','') like 'HQ-%' ) as HQ_TOTAL_STOCK_LINE_COUNT, ";
			$sql .= " (select count(*) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sl.DATE_ADD = '".$today."' and sl.FLAG_STK = 'S' and sh.ID_SHIP in (select distinct ID_SHIP from nsa.TOUCHPOINT_FTP_FILES)) as TP_TOTAL_STOCK_LINE_COUNT, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and ib.CODE_CAT_PRDT not in ('D1','D2')) as NSA_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_CUST_SOLDTO NOT LIKE 'D%' and ib.CODE_CAT_PRDT in ('D1','D2')) as DRF_IND_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and ltrim(sh.ID_SLSREP_1) = '103') as DRF_MIL_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and ltrim(sh.ID_SLSREP_1) = '104') as DRF_INT_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."') as TOTAL_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.CODE_SRC_EDI = 850) as EDI_SLS, ";
			//$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD = 'TCM') as WMS_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD <> 'TCM') as TCM_SLS, ";
			//$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD in (".$FC_Users.")) as FC_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP left join nsa.PACKSLIP_LOG pl on sh.ID_SHIP = pl.ID_SHIP where sh.DATE_SHIP = '".$today."' and pl.FLAG_REPRINT = 'N' and replace(pl.PRINTED_TO,'\\\\TCM2\\','') like 'FC-%') as FC_SLS, ";
			//$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_USER_ADD NOT in (".$FC_Users.") and sh.ID_SHIP NOT in (select distinct ID_SHIP from nsa.TOUCHPOINT_FTP_FILES)) as HQ_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP left join nsa.PACKSLIP_LOG pl on sh.ID_SHIP = pl.ID_SHIP where sh.DATE_SHIP = '".$today."' and pl.FLAG_REPRINT = 'N' and replace(pl.PRINTED_TO,'\\\\TCM2\\','') like 'HQ-%') as HQ_SLS, ";
			$sql .= " (select COALESCE(sum(sl.PRICE_NET),0) FROM nsa.CP_SHPLIN sl left join nsa.ITMMAS_BASE ib on sl.ID_ITEM = ib.ID_ITEM left join nsa.CP_SHPHDR sh on sl.ID_SHIP = sh.ID_SHIP where sh.DATE_SHIP = '".$today."' and sh.ID_SHIP in (select distinct ID_SHIP from nsa.TOUCHPOINT_FTP_FILES)) as TP_SLS ";
			QueryDatabase($sql, $results);



			while ($row = mssql_fetch_assoc($results)) {
				if ($row['TOTAL_SLS'] <> '0' OR $row['TOTAL_COUNT'] <> '0') {
					$subject = "Shipment Summary for " . $today;
					$body  = "Shipments on " . $today . ".\r\n";
					$body .= "\r\n NSA: 		" . $row['NSA_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['NSA_SLS']);
					$body .= "\r\n DRIFIRE IND: 	" . $row['DRF_IND_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['DRF_IND_SLS']);
					$body .= "\r\n DRIFIRE MIL: 	" . $row['DRF_MIL_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['DRF_MIL_SLS']);
					$body .= "\r\n DRIFIRE INT: 	" . $row['DRF_INT_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['DRF_INT_SLS']);
					$body .= "\r\n Total: 		" . $row['TOTAL_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['TOTAL_SLS']);
					$body .= "\r\n\r\n_____________________Informational____________________";
					//$body .= "\r\n EDI: 		" . $row['EDI_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['EDI_SLS']);
					//$body .= "\r\n EDI Order %: 	" . round(($row['EDI_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		EDI Shipment %:	" . round(($row['EDI_SLS']/$row['TOTAL_SLS'])*100,2)."%";
					//$body .= "\r\n WMS: 		" . $row['WMS_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['WMS_SLS']);
					//$body .= "\r\n WMS Order %: " . round(($row['WMS_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		WMS Shipment %:	" . round(($row['WMS_SLS']/$row['TOTAL_SLS'])*100,2)."%";
					//$body .= "\r\n TCM: 		" . $row['TCM_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['TCM_SLS']);
					//$body .= "\r\n TCM Order %: 	" . round(($row['TCM_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		TCM Shipment %:	" . round(($row['TCM_SLS']/$row['TOTAL_SLS'])*100,2)."%";

					$body .= "\r\n FC: 		" . $row['FC_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['FC_SLS']);
					$body .= "\r\n FC Order %: 	" . round(($row['FC_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		FC Shipment %:	" . round(($row['FC_SLS']/$row['TOTAL_SLS'])*100,2)."%";
					$body .= "\r\n HQ: 		" . $row['HQ_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['HQ_SLS']);
					$body .= "\r\n HQ Order %: 	" . round(($row['HQ_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		HQ Shipment %:	" . round(($row['HQ_SLS']/$row['TOTAL_SLS'])*100,2)."%";
					$body .= "\r\n TP: 		" . $row['TP_COUNT'] . " orders,	Shipment	" . money_format('%(n',$row['TP_SLS']);
					$body .= "\r\n TP Order %: 	" . round(($row['TP_COUNT']/$row['TOTAL_COUNT'])*100,2)."%,		TP Shipment %:	" . round(($row['TP_SLS']/$row['TOTAL_SLS'])*100,2)."%";

					$body .= "\r\n\r\n NSA Stock Lines Picked: 		" . $row['NSA_STOCK_LINE_COUNT'];
					$body .= "\r\n DRIFIRE IND Stock Lines Picked: 	" . $row['DRF_IND_STOCK_LINE_COUNT'];
					$body .= "\r\n DRIFIRE MIL Stock Lines Picked: 	" . $row['DRF_MIL_STOCK_LINE_COUNT'];
					$body .= "\r\n DRIFIRE INT Stock Lines Picked: 	" . $row['DRF_INT_STOCK_LINE_COUNT'];

					$body .= "\r\n FC Stock Lines Picked: 		" . $row['FC_TOTAL_STOCK_LINE_COUNT'];
					$body .= "\r\n HQ Stock Lines Picked: 		" . $row['HQ_TOTAL_STOCK_LINE_COUNT'];
					$body .= "\r\n TP Stock Lines Picked: 		" . $row['TP_TOTAL_STOCK_LINE_COUNT'];

					$body .= "\r\n Total Stock Lines Picked: 		" . $row['TOTAL_STOCK_LINE_COUNT'];
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

					$sql1  = "INSERT INTO nsa.DAILY_SHIPMENTS_SUMMARY" . $DB_TEST_FLAG . " ( ";
					$sql1 .= " DATE_ADD, ";
					$sql1 .= " NSA_COUNT, ";
					$sql1 .= " DRF_IND_COUNT, ";
					$sql1 .= " DRF_MIL_COUNT, ";
					$sql1 .= " DRF_INT_COUNT, ";
					$sql1 .= " TOTAL_COUNT, ";
					$sql1 .= " EDI_COUNT, ";
					$sql1 .= " TCM_COUNT, ";
					$sql1 .= " WMS_COUNT, ";
					$sql1 .= " FC_COUNT, ";
					$sql1 .= " HQ_COUNT, ";
					$sql1 .= " TP_COUNT, ";
					$sql1 .= " NSA_STOCK_LINE_COUNT, ";
					$sql1 .= " DRF_IND_STOCK_LINE_COUNT, ";
					$sql1 .= " DRF_MIL_STOCK_LINE_COUNT, ";
					$sql1 .= " DRF_INT_STOCK_LINE_COUNT, ";
					$sql1 .= " TOTAL_STOCK_LINE_COUNT, ";
					$sql1 .= " TCM_TOTAL_STOCK_LINE_COUNT, ";
					$sql1 .= " WMS_TOTAL_STOCK_LINE_COUNT, ";
					$sql1 .= " FC_TOTAL_STOCK_LINE_COUNT, ";
					$sql1 .= " HQ_TOTAL_STOCK_LINE_COUNT, ";
					$sql1 .= " TP_TOTAL_STOCK_LINE_COUNT, ";
					$sql1 .= " NSA_SLS, ";
					$sql1 .= " DRF_IND_SLS, ";
					$sql1 .= " DRF_MIL_SLS, ";
					$sql1 .= " DRF_INT_SLS, ";
					$sql1 .= " TOTAL_SLS, ";
					$sql1 .= " EDI_SLS, ";
					$sql1 .= " TCM_SLS, ";
					$sql1 .= " WMS_SLS, ";
					$sql1 .= " FC_SLS, ";
					$sql1 .= " HQ_SLS, ";
					$sql1 .= " TP_SLS ";

					$sql1 .= " ) VALUES ( ";

					$sql1 .= " GetDate(), ";
					$sql1 .= $row['NSA_COUNT'] . ", ";
					$sql1 .= $row[' DRF_IND_COUNT'] . ", ";
					$sql1 .= $row[' DRF_MIL_COUNT'] . ", ";
					$sql1 .= $row[' DRF_INT_COUNT'] . ", ";
					$sql1 .= $row[' TOTAL_COUNT'] . ", ";
					$sql1 .= $row[' EDI_COUNT'] . ", ";
					$sql1 .= $row[' TCM_COUNT'] . ", ";
					$sql1 .= $row[' WMS_COUNT'] . ", ";
					$sql1 .= $row[' FC_COUNT'] . ", ";
					$sql1 .= $row[' HQ_COUNT'] . ", ";
					$sql1 .= $row[' TP_COUNT'] . ", ";
					$sql1 .= $row[' NSA_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' DRF_IND_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' DRF_MIL_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' DRF_INT_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' TOTAL_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' TCM_TOTAL_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' WMS_TOTAL_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' FC_TOTAL_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' HQ_TOTAL_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' TP_TOTAL_STOCK_LINE_COUNT'] . ", ";
					$sql1 .= $row[' NSA_SLS'] . ", ";
					$sql1 .= $row[' DRF_IND_SLS'] . ", ";
					$sql1 .= $row[' DRF_MIL_SLS'] . ", ";
					$sql1 .= $row[' DRF_INT_SLS'] . ", ";
					$sql1 .= $row[' TOTAL_SLS'] . ", ";
					$sql1 .= $row[' EDI_SLS'] . ", ";
					$sql1 .= $row[' TCM_SLS'] . ", ";
					$sql1 .= $row[' WMS_SLS'] . ", ";
					$sql1 .= $row[' FC_SLS'] . ", ";
					$sql1 .= $row[' HQ_SLS'] . ", ";
					$sql1 .= $row[' TP_SLS'] . " ";
					$sql1 .= " ) ";



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
