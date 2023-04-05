<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runARK_QUERIES cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runARK_QUERIES cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runARK_QUERIES started at " . date('Y-m-d g:i:s a'));
			error_log("### runARK_QUERIES CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runARK_QUERIES' ";
			$sql .= "	and ";
			$sql .= "	FLAG_RUNNING = '1' ";
			$sql .= "	and ";
			$sql .= "	DATE_EXP > getDate()";
			QueryDatabase($sql, $results);

			if (mssql_num_rows($results) == 0) {
				$sql  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql .= " PROC_NAME, ";
				$sql .= " FLAG_RUNNING, ";
				$sql .= " DATE_ADD, ";
				$sql .= " DATE_EXP ";
				$sql .= ") VALUES ( ";
				$sql .= "'runARK_QUERIES', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runARK_QUERIES SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING ON";
				QueryDatabase($sql, $results);
				$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
				QueryDatabase($sql, $results);

				error_log("### runARK_QUERIES POPULATING nsa.00ARK_FG");

				$sql = "IF OBJECT_ID(N'nsa.[00ARK_FG]', N'U') IS NOT NULL  ";
				$sql .= " DROP TABLE nsa.[00ARK_FG]  ";
				QueryDatabase($sql, $results);

				$sql  = " select  ";
				$sql .= "   b.id_item, ";
				$sql .= "   case when b.code_user_2_IM like 'CS' then 'Customer Specific' else ";
				$sql .= "   case when b.code_user_2_IM like 'ETA' then 'ETA Embroidered Garments' else ";
				$sql .= "   case when b.code_user_2_IM like 'ADS' then 'Ad Specialty' else ";
				$sql .= "   case when b.code_user_2_IM like 'FR' then 'FR Garments' else ";
				$sql .= "   case when b.code_user_2_IM like 'NFR' then 'Non-FR Garments' else ";
				$sql .= "   case when b.code_user_2_IM like 'USPS' then 'USPS Garments' else ";
				$sql .= "   case when b.code_user_2_IM like 'NSA' then 'NSA Intercompany Inventory' else ";
				$sql .= "   'Miscellaneous' end end end end end end end ";
				$sql .= "   as [GRP], ";
				$sql .= "   case when b.code_user_1_IM like 'S' then '1 - STOCK' else ";
				$sql .= "   case when b.code_user_1_IM like 'A' then '2 - ADVERTISED MTO' else ";
				$sql .= "   case when b.code_user_1_IM like 'M' then '3 - MTO' else ";
				$sql .= "   case when b.code_user_1_IM like 'P' then '4 - PHASE OUT' else ";
				$sql .= "   case when b.code_user_1_IM like 'D' then '5 - DISCONTINUED' else ";
				$sql .= "   'NOT CATEGORIZED' end end end end end ";
				$sql .= "   as [GROUP], ";
				$sql .= "   concat(b.DESCR_1,' ',b.DESCR_2) as DESCR, ";
				$sql .= "   sl.ADV, ";
				$sql .= "   b.code_um_stk,  ";
				$sql .= "   case when l.BIN_PRIM like '2%' then 'FC' else 'RB' end as BIN, ";
				$sql .= "   ro.LEVEL_ROP,   ";
				$sql .= "   l.qty_onhd, ";
				$sql .= "   l.qty_alloc as ALLOC, ";
				$sql .= "   isnull(tSBNB.SBNB,0) as SBNB, ";
				$sql .= "   (l.qty_onord) as On_Order, ";
				$sql .= "   isnull(WPR.SUM_QTY_ONORD,0) as Qty_Rel, ";
				$sql .= "   isnull(WPS.SUM_QTY_ONORD,0) as Qty_Start, ";
				$sql .= "   (l.qty_onhd-l.qty_alloc) as OH_Avail, ";
				$sql .= "   (l.qty_onhd-l.qty_alloc+l.qty_onord) as Net_Avail, ";
				$sql .= "   (l.qty_onhd-l.qty_alloc+l.qty_onord)-ro.LEVEL_ROP as Opt_Variance, ";
				$sql .= "   (l.QTY_USAGE_MTD) as MTD, ";
				$sql .= "   (l.QTY_USAGE_YTD) as YTD, ";
				$sql .= "   (l.QTY_USAGE_YR_LAST) as LST_YR, ";
				$sql .= "    case when  ";
				$sql .= "     month(Getdate())>3 then (case when l.QTY_usage_ytd =0 then 999 else ";
				$sql .= "     (l.QTY_ONHD- l.qty_alloc)/(l.qty_usage_ytd/month(getdate())) end ) ";
				$sql .= "     else  ";
				$sql .= "     case when l.qty_usage_yr_last = 0 then 999 else (l.QTY_ONHD- l.qty_alloc)/(l.QTY_USAGE_YR_LAST/12) end end as MOH, ";
				$sql .= "   l.FLAG_SOURCE as FLAG_MFG_PUR, ";
				$sql .= "   case when l.FLAG_STK = 'N' then 'MTO' else 'S' end as FLAG_STK, ";
				$sql .= "   ro.QTY_MULT_ORD_ROP, ";
				$sql .= "   c.COST_TOTAL_ACCUM_CRNT as Crnt_cost, ";
				$sql .= "   c.COST_TOTAL_ACCUM_STD as Std_cost, ";
				$sql .= "   CASE WHEN b.code_user_3_im like 'W' then 0 else l.qty_onhd*c.COST_TOTAL_ACCUM_STD end as Std_EXT, ";
				$sql .= "   case when l.flag_source like 'M' then CASE WHEN b.CODE_USER_3_IM like 'W' then 0 else l.qty_onhd*c.COST_TOTAL_ACCUM_STD end end as Std_EXT_MFG, ";
				$sql .= "   case when l.flag_source like 'P' then CASE WHEN b.CODE_USER_3_IM like 'W' then 0 else l.qty_onhd*c.COST_TOTAL_ACCUM_STD end end as Std_EXT_PUR, ";
				$sql .= "   ro.LEVEL_ROP*c.COST_TOTAL_ACCUM_STD as Opt_VAL, ";
				$sql .= "   case when l.flag_source like 'M' then ro.LEVEL_ROP*c.COST_TOTAL_ACCUM_STD else 0 end as Opt_VAL_MFG, ";
				$sql .= "   case when l.flag_source like 'P' then ro.LEVEL_ROP*c.COST_TOTAL_ACCUM_STD else 0 end as Opt_VAL_PUR, ";
				$sql .= "   l.cost_base_lifo, ";
				$sql .= "   l.qty_onhd*l.cost_base_lifo as Base_Cost_EXT, ";
				$sql .= "   1 as count, ";
				$sql .= "   case when l.flag_source like 'M' then 1 else 0 end as Count_MFG, ";
				$sql .= "   case when l.flag_source like 'P' then 1 else 0 end as Count_PUR ";
				$sql .= " INTO nsa.[00ARK_FG] ";
				$sql .= " from  ";
				$sql .= "   nsa.ITMMAS_BASE b ";
				$sql .= " left join  ";
				$sql .= "   nsa.ITMMAS_STK_LIST sl ";
				$sql .= "   on b.id_item = sl.ID_ITEM ";
				$sql .= " left join ";
				$sql .= "   nsa.ITMMAS_LOC l  ";
				$sql .= "   on b.id_item=l.id_item  ";
				$sql .= "   and l.id_loc = '50' ";
				$sql .= " left join ";
				$sql .= "   nsa.ITMMAS_REORD ro ";
				$sql .= "   on b.id_item=ro.id_item ";
				$sql .= "   and ro.ID_LOC_HOME = '50' ";
				$sql .= " left join ";
				$sql .= "   nsa.ITMMAS_COST c ";
				$sql .= "   on b.id_item=c.id_item ";
				$sql .= " left join ";
				$sql .= "   (select ID_ITEM, sum(QTY_SHIP) as SBNB from nsa.CP_SHPLIN group by ID_ITEM) as tSBNB ";
				$sql .= "   on b.id_item = tSBNB.id_item ";
				$sql .= " left join  ";
				$sql .= "   (select STAT_REC_SO,  ";
				$sql .= "   sum(QTY_ONORD) as SUM_QTY_ONORD, ";
				$sql .= "   ID_ITEM_PAR ";
				$sql .= "   from nsa.SHPORD_HDR sh  ";
				$sql .= "   where sh.STAT_REC_SO in ('R') ";
				$sql .= "   group by ID_ITEM_PAR, STAT_REC_SO ";
				$sql .= "   ) WPR ";
				$sql .= "   on b.ID_ITEM = WPR.ID_ITEM_PAR ";
				$sql .= " left join  ";
				$sql .= "   (select STAT_REC_SO,  ";
				$sql .= "   sum(QTY_ONORD) as SUM_QTY_ONORD, ";
				$sql .= "   ID_ITEM_PAR ";
				$sql .= "   from nsa.SHPORD_HDR sh  ";
				$sql .= "   where sh.STAT_REC_SO in ('S') ";
				$sql .= "   group by ID_ITEM_PAR, STAT_REC_SO ";
				$sql .= "   ) WPS ";
				$sql .= "   on b.ID_ITEM = WPS.ID_ITEM_PAR ";
				$sql .= " where  ";
				$sql .= "   l.id_loc = '50'    ";
				$sql .= "   and l.qty_onhd > 1  ";
				$sql .= "   and b.CODE_COMM = 'FG'  ";
				$sql .= "   and l.id_item not like 'NN STK%' ";
				$sql .= " Order by  ";
				$sql .= "   b.code_user_2_IM,  ";
				$sql .= "   b.id_item ";
				QueryDatabase($sql, $results);






				error_log("### runARK_QUERIES POPULATING nsa.00ARK_RM");
				$sql = " IF OBJECT_ID(N'nsa.[00ARK_RM]', N'U') IS NOT NULL   ";
				$sql .= "    DROP TABLE nsa.[00ARK_RM]   ";
				QueryDatabase($sql, $results);


				$sql = " select  ";
				$sql .= "   l.id_loc, ";
				$sql .= "   CASE b.code_user_2_IM  ";
				$sql .= "     WHEN 'CS' then 'Customer Specific' ";
				$sql .= "     WHEN 'ETA' then 'ETA Embroidered Fabric' ";
				$sql .= "     WHEN 'ADS' then 'Ad Specialty' ";
				$sql .= "     WHEN 'FR' then 'FR Fabric' ";
				$sql .= "     WHEN 'NFR' then 'Non-FR Fabric' ";
				$sql .= "     WHEN 'USPS' then 'USPS Fabric' ";
				$sql .= "     WHEN 'NSA' then 'NSA Intercompany Inventory' ";
				$sql .= "     ELSE 'Miscellaneous'  ";
				$sql .= "   END as [GRP], ";
				$sql .= "   case when l.flag_stk  like 'S' then '1 - STOCK' else ";
				$sql .= "   case when l.flag_stk like 'N' then '2 - Non-Stock' else ";
				$sql .= "   case when b.code_user_3_im like 'W' then '3 - WRITTEN OFF' else ";
				$sql .= "   'NOT CATEGORIZED' end end end ";
				$sql .= "   as [GROUP], ";
				$sql .= "   b.id_item, ";
				$sql .= "   b.DESCR_1, b.code_um_pur,  ";
				$sql .= "   l.bin_prim, ";
				$sql .= "   b.CODE_CAT_cost, ";
				$sql .= "   (CASE WHEN iv.FLAG_VND_PRIM='P' THEN vo.NAME_VND_SORT END) as PRIM_VEND, ";
				$sql .= "   count(CASE WHEN bn.KEY_BIN_1 not like 'FLOOR' AND bn.KEY_BIN_1 not like 'O-%' THEN bn.qty_onhd END) as FULL_ROLL_CNT, ";
				$sql .= "   sum(CASE WHEN bn.KEY_BIN_1 not like 'FLOOR' AND bn.KEY_BIN_1 not like 'O-%' THEN bn.qty_onhd/b.ratio_stk_pur END) as FULL_ROLL, ";
				$sql .= "   sum(CASE WHEN bn.KEY_BIN_1 like 'O-%' THEN bn.qty_onhd/b.ratio_stk_pur END) as O_FULL_ROLL, ";
				$sql .= "   avg(l.qty_alloc/b.ratio_stk_pur) as ALLOC, ";
				$sql .= "   avg(l.qty_onord/b.ratio_stk_pur) as On_Order, ";
				$sql .= "   ((sum(bn.qty_onhd)-avg(l.qty_alloc)+avg(l.qty_onord))/b.ratio_stk_pur) as AVAIL, ";
				$sql .= "                sum(CASE WHEN bn.KEY_BIN_1 like 'FLOOR' and bn.KEY_BIN_3>' ' THEN bn.qty_onhd END)/b.ratio_stk_pur as FLOOR_BIN,  ";
				$sql .= "   count(CASE WHEN bn.KEY_BIN_1 like 'FLOOR' and bn.KEY_BIN_3>' ' THEN bn.qty_onhd END) as PARTIAL_CNT,  ";
				$sql .= "   count(bn.KEY_BIN_1) as LOT, ";
				$sql .= "   (sum(CASE WHEN bn.KEY_BIN_1 not like 'FLOOR' THEN bn.qty_onhd/b.ratio_stk_pur END) - avg(l.qty_alloc/b.ratio_stk_pur)) as OH_AVAIL, ";
				$sql .= "   avg(l.QTY_USAGE_MTD/b.ratio_stk_pur) as MTD, ";
				$sql .= "   avg(l.QTY_USAGE_YTD/b.ratio_stk_pur) as YTD, ";
				$sql .= "   avg(l.QTY_USAGE_YR_LAST/b.ratio_stk_pur) as LST_YR, ";
				$sql .= "   avg(c.cost_total_accum_crnt*b.Ratio_stk_pur) as CRNT, ";
				$sql .= "   avg(c.cost_total_accum_std*b.Ratio_stk_pur) as STD, ";
				$sql .= "   avg(convert(money,right(iv.price_vnd_fc_1,10))/10000)  as price, ";
				$sql .= "   avg(convert(money,right(iv.price_vnd_fc_1,10))/10000 *(1+b.wgt_item)) as price_w_duty, ";
				$sql .= "   avg(case when iv.comment_user = 'STANDARD' then c.cost_total_accum_std*b.Ratio_stk_pur else ";
				$sql .= "     l.Cost_Last*b.Ratio_stk_pur  end) ";
				$sql .= "     as Last_price_paid,  ";
				$sql .= "   b.CODE_USER_2_IM,  ";
				$sql .= "   b.code_user_3_im, ";
				$sql .= "   l.DATE_RCV_LAST ";
				$sql .= " into nsa.[00ARK_RM] ";
				$sql .= " from  ";
				$sql .= "   nsa.ITMMAS_BASE b ";
				$sql .= " left join ";
				$sql .= "   nsa.ITMMAS_LOC l ";
				$sql .= "   on b.id_item=l.id_item ";
				$sql .= "   and l.id_loc = '50' ";
				$sql .= " left join ";
				$sql .= "   nsa.ITMMAS_COST c ";
				$sql .= "   on b.id_item=c.id_item ";
				$sql .= " left join ";
				$sql .= "   nsa.BINTAG_ONHD bn ";
				$sql .= "   on b.id_item=bn.id_item ";
				$sql .= "   and bn.id_loc = '50' ";
				$sql .= " left join ";
				$sql .= "   nsa.ITMMAS_VND iv ";
				$sql .= "   on b.id_item=iv.id_item ";
				$sql .= " left join ";
				$sql .= "   nsa.VENMAS_ORDFM vo ";
				$sql .= "   on iv.id_VND_ORDFM=vo.id_VND_ORDFM  ";
				$sql .= "   and iv.id_VND_payto=vo.id_VND ";
				$sql .= " left join ";
				$sql .= "   nsa.tables_code_comm cc ";
				$sql .= "   on b.code_comm=cc.code_comm ";
				$sql .= " where  ";
				$sql .= "   l.FLAG_TRACK_BIN = 6 and l.id_loc='50' ";
				$sql .= "   and bn.KEY_BIN_1 not like 'WHSE'  ";
				$sql .= "   and (iv.FLAG_VND_PRIM like 'P'  or iv.FLAG_VND_PRIM is null) ";
				$sql .= " group by  ";
				$sql .= "   l.id_loc, ";
				$sql .= "   b.code_comm, b.id_item,  ";
				$sql .= "   b.DESCR_1, b.code_um_pur,  ";
				$sql .= "   l.bin_prim, b.ratio_stk_pur,  ";
				$sql .= "   l.qty_alloc, l.qty_onord,  ";
				$sql .= "   l.flag_stk,cc.DESCR, vo.NAME_VND_SORT,  ";
				$sql .= "   iv.FLAG_VND_PRIM, b.code_user_1_im,b.code_user_2_im, ";
				$sql .= "   b.code_user_3_im, ";
				$sql .= "   b.CODE_CAT_cost, ";
				$sql .= "   l.DATE_RCV_LAST ";
				$sql .= " Order by  ";
				$sql .= "   b.code_user_2_im ,b.code_user_1_im,  b.id_item ASC ";
				QueryDatabase($sql, $results);


				error_log("### runARK_QUERIES POPULATING nsa.00ARK_IMHIST");
				$sql = " IF OBJECT_ID(N'nsa.[00ARK_IMHIST]', N'U') IS NOT NULL   ";
				$sql .= "    DROP TABLE nsa.[00ARK_IMHIST] ";
				QueryDatabase($sql, $results);

				$sql = " select *  ";
				$sql .= " INTO nsa.[00ARK_IMHIST] ";
				$sql .= " from nsa.imhist  ";
				$sql .= " where DATE_CHG_QTY between '2/1/2023' and '2/15/2023'  ";
				$sql .= " and id_loc = '50' ";
				QueryDatabase($sql, $results);



				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runARK_QUERIES DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runARK_QUERIES ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runARK_QUERIES finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runARK_QUERIES cannot disconnect from database");
		}
	}
?>
