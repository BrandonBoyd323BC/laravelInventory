<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runBOKHHST_TABLEAU cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runBOKHHST_TABLEAU cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runBOKHHST_TABLEAU started at " . date('Y-m-d g:i:s a'));
			error_log("### runBOKHHST_TABLEAU CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runBOKHHST_TABLEAU' ";
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
				$sql .= "'runBOKHHST_TABLEAU', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runBOKHHST_TABLEAU SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

				error_log("### runBOKHHST_TABLEAU SELECTING DATA ");
				$sql = " SELECT * ";
				$sql .= " INTO nsa.BOKHHST_TABLEAU_TEMP";
				$sql .= " FROM  ";
				$sql .= "      ( ";
				$sql .= "      SELECT * FROM  ";
				$sql .= "      ( ";
				$sql .= "      SELECT ID_ORD AS ID_ORD_A,SEQ_LINE_ORD AS SEQ_LINE_ORD_A , max(DATE_BOOK_LAST) AS MAX_DATE_BOOK_LAST  ";
				$sql .= "      FROM nsa.BOKHST_LINE ";
				$sql .= "      GROUP BY ID_ORD,SEQ_LINE_ORD ";
				$sql .= "      ) AS bl_datemax ";
				$sql .= "      INNER JOIN ";
				$sql .= "      ( ";
				$sql .= "      SELECT ID_ORD AS L_ID_ORD, ID_CUST AS L_ID_CUST ,SEQ_SHIPTO AS L_SEQ_SHIPTO, SEQ_LINE_ORD,ID_ITEM,VER_BO,ID_LOC,DESCR_1,DESCR_2,CODE_UM_PRICE,CODE_CAT_PRDT,CODE_USER_1_IM,DATE_RQST,DATE_PROM,DATE_BOOK_LAST,FLAG_DROP_SHIP,SUM(QTY_OPEN) AS QuantityOpen, SUM(QTY_SHIP) AS QuantityShip ";
				$sql .= "      FROM nsa.BOKHST_LINE ";
				$sql .= "      GROUP BY ID_ORD, ID_CUST,SEQ_SHIPTO,ID_ORD,SEQ_LINE_ORD,ID_ITEM,VER_BO,ID_LOC,DESCR_1,DESCR_2,CODE_UM_PRICE,CODE_CAT_PRDT,CODE_USER_1_IM,DATE_RQST,DATE_PROM,DATE_BOOK_LAST,FLAG_DROP_SHIP ";
				$sql .= "      ) bl_org ";
				$sql .= "      ON  bl_datemax.ID_ORD_A=bl_org.L_ID_ORD ";
				$sql .= "      AND bl_datemax.SEQ_LINE_ORD_A=bl_org.SEQ_LINE_ORD ";
				$sql .= "      AND bl_datemax.MAX_DATE_BOOK_LAST=bl_org.DATE_BOOK_LAST ";
				$sql .= "      INNER JOIN  ";
				$sql .= "      ( ";
				$sql .= "      SELECT ID_ORD AS ID_ORD_B,SEQ_LINE_ORD AS SEQ_LINE_ORD_B , SUM(SLS) AS NET_SLS, SUM(COST) AS NET_COST ";
				$sql .= "      FROM nsa.BOKHST_LINE ";
				$sql .= "      GROUP BY ID_ORD,SEQ_LINE_ORD ";
				$sql .= "      ) bl_netsales ";
				$sql .= "      ON  bl_datemax.ID_ORD_A=bl_netsales.ID_ORD_B ";
				$sql .= "      AND bl_datemax.SEQ_LINE_ORD_A=bl_netsales.SEQ_LINE_ORD_B ";
				$sql .= "      ) bl ";
				$sql .= " LEFT JOIN ( ";
				$sql .= "                SELECT ID_ORD, max(ID_REV) as MAX_ID_REV FROM nsa.BOKHST_HDR ";
				$sql .= "                GROUP BY ID_ORD ";
				$sql .= "             ) bh_max ";
				$sql .= " ON bl.L_ID_ORD = bh_max.ID_ORD ";
				$sql .= " CROSS APPLY (SELECT TOP 1 bh.ID_ORD AS H_ID_ORD,bh.ID_CUST AS H_ID_CUST,bh.SEQ_SHIPTO AS H_SEQ_SHIPTO,bh.NAME_CUST_SOLDTO,bh.CITY,bh.ID_ST,bh.ZIP,bh.PROV,bh.COUNTRY,bh.ID_TERR,bh.ID_SLSREP_1,bh.DATE_ORD, bh.VER_BO AS H_VER_BO  ";
				$sql .= "                 FROM nsa.BOKHST_HDR bh  ";
				$sql .= "                 WHERE bl.L_ID_ORD = bh.ID_ORD  ";
				$sql .= "                   AND bl.L_ID_CUST = bh.ID_CUST  ";
				$sql .= "                   AND bh.ID_REV = bh_max.MAX_ID_REV ";
				$sql .= "                 ORDER BY rowid DESC ";
				$sql .= "                   ) bh ";
				$sql .= " WHERE bh.H_ID_CUST IS NOT NULL ";
				//error_log($sql);
				QueryDatabase($sql, $results);

				error_log("### runBOKHHST_TABLEAU CHECKING FOR nsa.BOKHHST_TABLEAU ");
				$sql = " IF OBJECT_ID('nsa.BOKHHST_TABLEAU', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.BOKHHST_TABLEAU ";
				QueryDatabase($sql, $results);


				error_log("### runBOKHHST_TABLEAU INSERT COMPLETE.....RENAMING");
				$sql = " SP_RENAME 'nsa.BOKHHST_TABLEAU_TEMP','BOKHHST_TABLEAU'";
				QueryDatabase($sql, $results);

				error_log("### runBOKHHST_TABLEAU DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runBOKHHST_TABLEAU ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runBOKHHST_TABLEAU finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runBOKHHST_TABLEAU cannot disconnect from database");
		}
	}
?>
