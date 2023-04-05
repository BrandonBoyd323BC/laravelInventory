<?php

	require_once("procfile.php");

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}
	$DEBUG = 1;

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runWC_OPEN cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runWC_OPEN cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runWC_OPEN started at " . date('Y-m-d g:i:s a'));
			error_log("### runWC_OPEN CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runWC_OPEN' ";
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
				$sql .= "'runWC_OPEN', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runWC_OPEN SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

				error_log("### runWC_OPEN DELETING NULL DATE_WORKED WCLDTL_DTL");
				$sql  = "DELETE from nsa.WCLDTL_DTL where DATE_WORK is null";
				QueryDatabase($sql, $results);

				$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN CHECKING FOR TEMP ");
				$sql = " IF OBJECT_ID('nsa.WC_OPEN_TEMP', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.WC_OPEN_TEMP";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN CREATING WC_OPEN_TEMP ");
				$sql  = "CREATE TABLE [nsa].[WC_OPEN_TEMP]( ";
				$sql .= "	[ID_WC] [char](4) NOT NULL DEFAULT (''), ";
				$sql .= "	[DATE_PROM] [numeric](8, 0) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[DATE_RQST] [numeric](8, 0) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[ID_ORD] [numeric](8, 0) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[SEQ_LINE_ORD] [numeric](4, 0) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[ID_ITEM] [varchar](30) NOT NULL DEFAULT (''), ";
				$sql .= "	[ID_SO] [varchar](9) NULL DEFAULT (''), ";
				$sql .= "	[SUFX_SO] [numeric](3, 0) NULL DEFAULT ((0)), ";
				$sql .= "	[ID_OPER] [numeric](4, 0) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[MINS_OPEN] [numeric](12, 4) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[STAT_REC_OPER_1] [char](1) NOT NULL DEFAULT (''), ";
				$sql .= "	[rowid] [int] IDENTITY(1,1) NOT NULL ";
				$sql .= ") ";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN SELECTING DATA ");
				$sql  = " SELECT ";
				$sql .= " so.ID_WC, ";
				$sql .= " Convert(varchar(10),l.DATE_PROM, 112) as DATE_PROM, ";
				$sql .= " Convert(varchar(10),l.DATE_RQST, 112) as DATE_RQST, ";
				$sql .= " l.ID_ORD, ";
				$sql .= " l.SEQ_LINE_ORD, ";
				$sql .= " l.ID_ITEM, ";
				$sql .= " so.ID_SO, ";
				$sql .= " so.SUFX_SO, ";
				$sql .= " so.ID_OPER, ";
				$sql .= " ((so.QTY_ORD - so.QTY_CMPL) * so.HR_MACH_SF * 60) as MINS_OPEN, ";
				$sql .= " so.STAT_REC_OPER_1 ";
				$sql .= "FROM nsa.CP_ORDLIN l ";
				$sql .= " LEFT JOIN nsa.CP_ORDHDR h ";
				$sql .= " on l.ID_ORD = h.ID_ORD ";
				$sql .= " LEFT JOIN nsa.SHPORD_OPER so ";
				$sql .= " on l.ID_SO = so.ID_SO ";
				$sql .= " and l.SUFX_SO = so.SUFX_SO ";
				$sql .= " LEFT JOIN nsa.ITMMAS_BASE b ";
				$sql .= " on l.ID_ITEM = b.ID_ITEM ";					
				$sql .= "WHERE so.STAT_REC_OPER_1 <> 'C' ";
				$sql .= " and so.ID_WC between '1100' and '8000'  ";
				$sql .= " and l.FLAG_STK = 'N' ";
				$sql .= "ORDER BY l.DATE_PROM asc, ";
				$sql .= " l.ID_ORD asc, ";
				$sql .= " so.ID_SO asc, ";
				$sql .= " so.ID_OPER asc ";
				QueryDatabase($sql, $results);
				$num_rows = mssql_num_rows($results);
				error_log("### runWC_OPEN POPULATING nsa.WC_OPEN_TEMP with " . $num_rows . " NONSTOCK records");

				while ($row = mssql_fetch_assoc($results)) {
					$sql2  = "INSERT INTO nsa.WC_OPEN_TEMP( ";
					$sql2 .= " ID_WC, ";
					$sql2 .= " DATE_PROM, ";
					$sql2 .= " DATE_RQST, ";
					$sql2 .= " ID_ORD, ";
					$sql2 .= " SEQ_LINE_ORD, ";
					$sql2 .= " ID_ITEM, ";
					$sql2 .= " ID_SO, ";
					$sql2 .= " SUFX_SO, ";
					$sql2 .= " ID_OPER, ";
					$sql2 .= " MINS_OPEN, ";
					$sql2 .= " STAT_REC_OPER_1 ";
					$sql2 .= ") VALUES ( ";
					$sql2 .= "'" . $row['ID_WC'] . "', ";
					$sql2 .= "'" . $row['DATE_PROM'] . "', ";
					$sql2 .= "'" . $row['DATE_RQST'] . "', ";
					$sql2 .= "'" . $row['ID_ORD'] . "', ";
					$sql2 .= "'" . $row['SEQ_LINE_ORD'] . "', ";
					$sql2 .= "'" . $row['ID_ITEM'] . "', ";
					$sql2 .= "'" . $row['ID_SO'] . "', ";
					$sql2 .= "'" . $row['SUFX_SO'] . "', ";
					$sql2 .= "'" . $row['ID_OPER'] . "', ";
					$sql2 .= "'" . $row['MINS_OPEN'] . "', ";
					$sql2 .= "'" . $row['STAT_REC_OPER_1'] . "' ";
					$sql2 .= ")";
					QueryDatabase($sql2, $results2);
				}

				error_log("### runWC_OPEN SELECTING STOCK DATA ");
				$sql = "select ";
				$sql .= "	o.ID_WC, ";
				$sql .= "	Convert(varchar(10),pd.DATE_PROM,1) as DATE_PROM, ";
				$sql .= "	Convert(varchar(10),pd.DATE_RQST,1) as DATE_RQST, ";
				$sql .= "	pd.ID_ORD, ";
				$sql .= "	pd.SEQ_LINE_ORD, ";
				$sql .= "	pd.ID_ITEM, ";
				$sql .= "	'' as ID_SO, ";
				$sql .= "	'' as SUFX_SO, ";
				$sql .= "	o.ID_OPER, ";
				$sql .= "	(pd.QTY_OPEN * o.HR_MACH_SR * 60) as MINS_OPEN ";
				$sql .= "from ";
				$sql .= "	nsa.ROUTMS_OPER o, ";
				$sql .= "	nsa.rpt50_ordlin_promDt pd ";
				$sql .= "where ";
				$sql .= "	pd.FLAG_STK = 'S' ";
				$sql .= "	and ";
				$sql .= "	pd.ID_ITEM = o.ID_ITEM ";
				$sql .= "	and ";
				$sql .= "	o.ID_OPER between '9000' and '9998' ";

				$sql .= "order by ";
				$sql .= " DATE_PROM asc, ";
				$sql .= " ID_ORD asc, ";
				$sql .= " ID_SO asc, ";
				$sql .= " ID_OPER asc ";
				QueryDatabase($sql, $results);
				$num_rows = mssql_num_rows($results);
				error_log("### runWC_OPEN POPULATING nsa.WC_OPEN_TEMP with " . $num_rows . " STOCK records");

				while ($row = mssql_fetch_assoc($results)) {
					$sql2  = "INSERT INTO nsa.WC_OPEN_TEMP( ";
					$sql2 .= " ID_WC, ";
					$sql2 .= " DATE_PROM, ";
					$sql2 .= " DATE_RQST, ";
					$sql2 .= " ID_ORD, ";
					$sql2 .= " SEQ_LINE_ORD, ";
					$sql2 .= " ID_ITEM, ";
					$sql2 .= " ID_SO, ";
					$sql2 .= " SUFX_SO, ";
					$sql2 .= " ID_OPER, ";
					$sql2 .= " MINS_OPEN, ";
					$sql2 .= " STAT_REC_OPER_1 ";
					$sql2 .= ") VALUES ( ";
					$sql2 .= "'" . $row['ID_WC'] . "', ";
					$sql2 .= "'" . $row['DATE_PROM'] . "', ";
					$sql2 .= "'" . $row['DATE_RQST'] . "', ";
					$sql2 .= "'" . $row['ID_ORD'] . "', ";
					$sql2 .= "'" . $row['SEQ_LINE_ORD'] . "', ";
					$sql2 .= "'" . $row['ID_ITEM'] . "', ";
					$sql2 .= " NULL, ";
					$sql2 .= " NULL, ";
					$sql2 .= "'" . $row['ID_OPER'] . "', ";
					$sql2 .= "'" . $row['MINS_OPEN'] . "', ";
					$sql2 .= " '' ";
					$sql2 .= ")";
					QueryDatabase($sql2, $results2);
				}

				error_log("### runWC_OPEN CHECKING FOR nsa.WC_OPEN ");
				$sql = " IF OBJECT_ID('nsa.WC_OPEN', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.WC_OPEN";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN RENAMING nsa.WC_OPEN_TEMP to nsa.WC_OPEN ");
				$sql = " SP_RENAME 'nsa.WC_OPEN_TEMP','WC_OPEN'";
				QueryDatabase($sql, $results);

				print("<p class='warning'>runWC_OPEN populated nsa.WC_OPEN with " . $num_rows . " records at " . date('Y-m-d g:i:s a') . "</p>\n");

				error_log("### runWC_OPEN CHECKING FOR WC_CAP_TEMP ");
				$sql = " IF OBJECT_ID('nsa.WC_CAP_TEMP', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.WC_CAP_TEMP";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN CREATING WC_CAP_TEMP ");
				$sql  = "CREATE TABLE [nsa].[WC_CAP_TEMP]( ";
				$sql .= "	[ID_WC] [char](4) NOT NULL DEFAULT (''), ";
				$sql .= "	[ID_BADGE] [varchar](9) NOT NULL DEFAULT (''), ";
				$sql .= "	[NO_MEMS] [numeric](4, 1) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[CAP] [numeric](12, 3) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[rowid] [int] IDENTITY(1,1) NOT NULL ";
				$sql .= ") ";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN SELECTING CAP DATA ");
				$sql =  "select ";
				$sql .= "	wc.ID_WC, ";
				$sql .= "	wc.DESCR_WC ";
				$sql .= " from ";
				$sql .= "  nsa.tables_loc_dept_wc wc ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$sql2 =  "select ";
					$sql2 .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
					$sql2 .= " 	ID_BADGE,";
					$sql2 .= " 	NAME_EMP,";
					$sql2 .= "	* ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCEMMS_EMP ";
					$sql2 .= " where ";
					$sql2 .= " 	TYPE_BADGE = 'X'";
					$sql2 .= " 	and";
					$sql2 .= " 	CODE_ACTV = '0'";
					$sql2 .= " 	and";
					$sql2 .= " 	KEY_HOME_3RD = '" . $row['ID_WC'] . "'";
					$sql2 .= " order by BADGE_NAME asc";
					QueryDatabase($sql2, $results2);

					while ($row2 = mssql_fetch_assoc($results2)) {
						$sql3 = "select ";
						$sql3 .= " count(*) as NO_MEMS";
						$sql3 .= " from ";
						$sql3 .= " 	nsa.DCEMMS_EMP ";
						$sql3 .= " where ";
						$sql3 .= " 	TYPE_BADGE = 'E'";
						$sql3 .= " 	and";
						$sql3 .= " 	CODE_ACTV = '0'";
						$sql3 .= " 	and ";
						$sql3 .= " 	STAT_BADGE = 'A' ";
						$sql3 .= " 	and";
						$sql3 .= " 	ltrim(ID_BADGE_TEAM_STD) = '" . trim($row2['ID_BADGE']) . "'";
						QueryDatabase($sql3, $results3);

						while ($row3 = mssql_fetch_assoc($results3)) {
							/////2nd shift members are counted as half a member
							$no_mems = $row3['NO_MEMS'];
							if (trim($row2['ID_BADGE']) >= 900 && trim($row2['ID_BADGE']) <= 999) {
								$no_mems = $no_mems/2;
							} 

							$cap_team = $no_mems * 455;
							$sql4  = "INSERT INTO nsa.WC_CAP_TEMP( ";
							$sql4 .= " ID_WC, ";
							$sql4 .= " ID_BADGE, ";
							$sql4 .= " NO_MEMS, ";
							$sql4 .= " CAP ";
							$sql4 .= ") VALUES ( ";
							$sql4 .= "'" . $row['ID_WC'] . "', ";
							$sql4 .= "'" . $row2['ID_BADGE'] . "', ";
							$sql4 .= "'" . $no_mems . "', ";
							$sql4 .= "'" . $cap_team . "' ";
							$sql4 .= ")";
							QueryDatabase($sql4, $results4);
						}
					}
				}

				error_log("### runWC_OPEN CHECKING FOR nsa.WC_CAP ");
				$sql = " IF OBJECT_ID('nsa.WC_CAP', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.WC_CAP";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN RENAMING nsa.WC_CAP_TEMP to nsa.WC_CAP ");
				$sql = " SP_RENAME 'nsa.WC_CAP_TEMP','WC_CAP'";
				QueryDatabase($sql, $results);

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runWC_OPEN DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runWC_OPEN ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runWC_OPEN finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runWC_OPEN cannot disconnect from database");
		}
	}
?>
