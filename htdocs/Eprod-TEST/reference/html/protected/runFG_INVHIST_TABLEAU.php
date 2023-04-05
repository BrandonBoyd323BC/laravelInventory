<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runFG_INVHIST_TABLEAU cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runFG_INVHIST_TABLEAU cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runFG_INVHIST_TABLEAU started at " . date('Y-m-d g:i:s a'));
			error_log("### runFG_INVHIST_TABLEAU CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runFG_INVHIST_TABLEAU' ";
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
				$sql .= "'runFG_INVHIST_TABLEAU', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runFG_INVHIST_TABLEAU SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING ON";
				QueryDatabase($sql, $results);
				$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
				QueryDatabase($sql, $results);

				$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
				QueryDatabase($sql, $results);
	

				//ini_set('memory_limit', '-1');

				error_log("### runFG_INVHIST_TABLEAU SELECTING DATA ");
				$sql = " SELECT * ";
				$sql .= " INTO nsa.FG_INVHIST_TABLEAU_TEMP";
				$sql .= " FROM  (";
				$sql .= " select ";
				$sql .= " 	GETDATE() as DATE_ADD,";
				$sql .= " 	case when sl.[GROUP] like 'M%' then 'MIL' else 'MTO' end as GRP,";
				$sql .= " 	sl.[GROUP], ";
				$sql .= " 	cf.comm_desc_full,";
				$sql .= " 	b.id_item,";
				$sql .= "      b.code_cat_prdt,";
				$sql .= "     b.code_user_3_im,";
				$sql .= " 	sl.SORT,";
				$sql .= " 	b.DESCR_1,";
				$sql .= " 	sl.ADV,";
				$sql .= " 	b.code_um_stk,";
				$sql .= "     b.CODE_USER_1_IM, ";
				$sql .= " 	case when l.BIN_PRIM like '2%' then 'FC' else case when l.id_item like 'DF%' then 'TP' else 'HQ' end end as BIN,";
				$sql .= "     l.id_item as item_id,";
				$sql .= " 	l.qty_onhd,";
				$sql .= " 	(l.qty_alloc) as ALLOC,";
				$sql .= "     ro.LEVEL_ROP,";
				$sql .= " 	l.FLAG_SOURCE as FLAG_MFG_PUR,";
				$sql .= "	(l.qty_onord) as On_Order,";
				$sql .= " 	case when l.FLAG_STK = 'N' then 'MTO' else 'S' end as FLAG_STK ";
				$sql .= "	c.COST_TOTAL_ACCUM_STD as Std_cost,";
				$sql .= "	CASE WHEN b.code_user_3_im like 'W' then 0 else l.qty_onhd*c.COST_TOTAL_ACCUM_STD end as Std_EXT";
				$sql .= " from ";
				$sql .= " nsa.ITMMAS_LOC l";
				$sql .= " left join ";
				$sql .= " 	nsa.ITMMAS_BASE b on l.id_item = b.ID_ITEM";
				$sql .= " left join ";
				$sql .= " 	nsa.itmmas_stk_list sl on l.id_item = sl.ID_ITEM";
				$sql .= " left join";
				$sql .= " 	nsa.ITMMAS_REORD ro";
				$sql .= " 	on b.id_item=ro.id_item";
				$sql .= " left join";
				$sql .= " 	nsa.ITMMAS_COST c";
				$sql .= " 	on b.id_item=c.id_item";
				$sql .= " left join";
				$sql .= " 	(select ID_ITEM, sum(QTY_SHIP) as SBNB from nsa.CP_SHPLIN group by ID_ITEM) as tSBNB";
				$sql .= " 	on b.id_item = tSBNB.id_item";
				$sql .= " left join";
				$sql .= " 	nsa.cus_comm_code_full cf";
				$sql .= " 	on sl.[GROUP]=cf.comm_code";
				$sql .= " left join ";
				$sql .= " 	(select STAT_REC_SO, ";
				$sql .= " 	sum(QTY_ONORD) as SUM_QTY_ONORD,";
				$sql .= " 	ID_ITEM_PAR";
				$sql .= " 	from nsa.SHPORD_HDR sh ";
				$sql .= " 	where sh.STAT_REC_SO in ('R')";
				$sql .= " 	group by ID_ITEM_PAR, STAT_REC_SO";
				$sql .= " 	) WPR";
				$sql .= " 	on b.ID_ITEM = WPR.ID_ITEM_PAR";
				$sql .= " left join ";
				$sql .= " 	(select STAT_REC_SO, ";
				$sql .= " 	sum(QTY_ONORD) as SUM_QTY_ONORD,";
				$sql .= " 	ID_ITEM_PAR";
				$sql .= " 	from nsa.SHPORD_HDR sh ";
				$sql .= " 	where sh.STAT_REC_SO in ('S')";
				$sql .= " 	group by ID_ITEM_PAR, STAT_REC_SO";
				$sql .= " 	) WPS";
				$sql .= " 	on b.ID_ITEM = WPS.ID_ITEM_PAR";
				$sql .= " where ";
				$sql .= " 	l.id_loc = '10'";
				$sql .= " 	and sl.[GROUP] is not null";
				$sql .= " ) as FGI";
				error_log($sql);
				QueryDatabase($sql, $results);

				error_log("### runFG_INVHIST_TABLEAU CHECKING FOR nsa.FG_INVHIST_TABLEAU ");
				$sql = " IF OBJECT_ID('nsa.FG_INVHIST_TABLEAU', 'U') IS NOT NULL";
				$sql .= " INSERT INTO nsa.FG_INVHIST_TABLEAU ( ";
				$sql .= " DATE_ADD, ";
				$sql .= " GRP, ";
				$sql .= " [GROUP], ";
				$sql .= " comm_desc_full, ";
				$sql .= " id_item, ";
				$sql .= " code_cat_prdt, ";
				$sql .= " code_user_3_im, ";
				$sql .= " SORT, ";
				$sql .= " DESCR_1, ";
				$sql .= " ADV, ";
				$sql .= " code_um_stk, ";
				$sql .= " CODE_USER_1_IM, ";
				$sql .= " BIN, ";
				$sql .= " item_id, ";
				$sql .= " qty_onhd, ";
				$sql .= " ALLOC, ";
				$sql .= " LEVEL_ROP, ";
				$sql .= " FLAG_MFG_PUR, ";
				$sql .= " On_Order, ";
				$sql .= " FLAG_STK, ";
				$sql .= " STF_COST, ";
				$sql .= " STD_EXT ";
				$sql .= " )";
				$sql .= " SELECT  ";
				$sql .= " DATE_ADD, ";
				$sql .= " GRP, ";
				$sql .= " [GROUP], ";
				$sql .= " comm_desc_full, ";
				$sql .= " id_item, ";
				$sql .= " code_cat_prdt, ";
				$sql .= " code_user_3_im, ";
				$sql .= " SORT, ";
				$sql .= " DESCR_1, ";
				$sql .= " ADV, ";
				$sql .= " code_um_stk, ";
				$sql .= " CODE_USER_1_IM, ";
				$sql .= " BIN, ";
				$sql .= " item_id, ";
				$sql .= " qty_onhd, ";
				$sql .= " ALLOC, ";
				$sql .= " LEVEL_ROP, ";
				$sql .= " FLAG_MFG_PUR, ";
				$sql .= " On_Order, ";
				$sql .= " FLAG_STK, ";
				$sql .= " STD_COST, ";
				$sql .= " STD_EXT ";
				$sql .= " FROM nsa.FG_INVHIST_TABLEAU_TEMP ";
				error_log($sql);
				QueryDatabase($sql, $results);

				error_log("### runFG_INVHIST_TABLEAU DROPPING TEMP TABLE ");
				$sql = " DROP TABLE ";
				$sql .= " nsa.FG_INVHIST_TABLEAU_TEMP ";
				error_log($sql);
				QueryDatabase($sql, $results);

				error_log("### runFG_INVHIST_TABLEAU DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runFG_INVHIST_TABLEAU ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runFG_INVHIST_TABLEAU finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runFG_INVHIST_TABLEAU cannot disconnect from database");
		}
	}
?>
