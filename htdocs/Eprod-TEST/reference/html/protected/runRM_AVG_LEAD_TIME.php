<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runRM_AVG_LEAD_TIME cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runRM_AVG_LEAD_TIME cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runRM_AVG_LEAD_TIME started at " . date('Y-m-d g:i:s a'));
			error_log("### runRM_AVG_LEAD_TIME CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runRM_AVG_LEAD_TIME' ";
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
				$sql .= "'runRM_AVG_LEAD_TIME', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runRM_AVG_LEAD_TIME SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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


				$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
				QueryDatabase($sql, $results);

				error_log("### runRM_AVG_LEAD_TIME CHECKING FOR TEMP ");
				$sql = " IF OBJECT_ID('nsa.RM_AVG_LEAD_TEMP', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.RM_AVG_LEAD_TEMP";
				QueryDatabase($sql, $results);

				error_log("### runRM_AVG_LEAD_TIME CREATING RM_AVG_LEAD_TEMP ");
				$sql  = "CREATE TABLE [nsa].[RM_AVG_LEAD_TEMP]( ";
				$sql .= "	[ID_ITEM] [varchar](30) NOT NULL DEFAULT (''), ";
				$sql .= "	[AVG_DAYS_OPEN] [numeric](12, 4) NOT NULL DEFAULT ((0)), ";
				$sql .= "	[rowid] [int] IDENTITY(1,1) NOT NULL ";
				$sql .= ") ";
				QueryDatabase($sql, $results);

				error_log("### runRM_AVG_LEAD_TIME SELECTING RM ITEMS ");
				$sql  = "select distinct ID_ITEM ";
				$sql .= " FROM nsa.ITMMAS_BASE ";
				$sql .= " WHERE (code_comm like 'RM%' ";
				$sql .= " OR code_user_2_im = 'VR') ";
				QueryDatabase($sql, $results);
				$num_rows = mssql_num_rows($results);
				while ($row = mssql_fetch_assoc($results)) {
					$sql2  = "select LD.ID_ITEM, avg(LD.LEAD_DAYS) as AVG_LEAD_DAYS ";
					$sql2 .= " from ( ";
					$sql2 .= " 	select top 2 plr.ID_ITEM, ";
					$sql2 .= " 	datediff(dd,ph.DATE_PO, plr.DATE_RCV) as LEAD_DAYS ";
					$sql2 .= " 	from nsa.POHIST_LINE_RCPT plr ";
					$sql2 .= " 	left join nsa.POHIST_HDR ph ";
					$sql2 .= " 	on plr.ID_PO = ph.ID_PO ";
					$sql2 .= " 	where plr.ID_ITEM =  '" . $row['ID_ITEM'] . "' ";
					$sql2 .= " 	order by plr.DATE_RCV desc ";
					$sql2 .= " ) as LD ";
					$sql2 .= " group by LD.ID_ITEM ";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$sql3  = "INSERT INTO nsa.RM_AVG_LEAD_TEMP( ";
						$sql3 .= " ID_ITEM, ";
						$sql3 .= " AVG_DAYS_OPEN ";
						$sql3 .= ") VALUES ( ";
						$sql3 .= "'" . $row['ID_ITEM'] . "', ";
						$sql3 .= "" . $row2['AVG_LEAD_DAYS'] . " ";
						$sql3 .= ")";
						QueryDatabase($sql3, $results3);
					}
				}

				$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
				QueryDatabase($sql, $results);

				error_log("### runRM_AVG_LEAD_TIME CHECKING FOR nsa.RM_AVG_LEAD ");
				$sql = " IF OBJECT_ID('nsa.RM_AVG_LEAD', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.RM_AVG_LEAD";
				QueryDatabase($sql, $results);

				error_log("### runRM_AVG_LEAD_TIME RENAMING nsa.RM_AVG_LEAD_TEMP to nsa.RM_AVG_LEAD ");
				$sql = " SP_RENAME 'nsa.RM_AVG_LEAD_TEMP','RM_AVG_LEAD'";
				QueryDatabase($sql, $results);

				print("<p class='warning'>runRM_AVG_LEAD_TIME populated nsa.RM_AVG_LEAD with " . $num_rows . " records at " . date('Y-m-d g:i:s a') . "</p>\n");

				error_log("### runRM_AVG_LEAD_TIME CHECKING FOR WC_CAP_TEMP ");
				$sql = " IF OBJECT_ID('nsa.WC_CAP_TEMP', 'U') IS NOT NULL";
				$sql .= "	DROP TABLE nsa.WC_CAP_TEMP";
				QueryDatabase($sql, $results);

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runRM_AVG_LEAD_TIME DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runRM_AVG_LEAD_TIME ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runRM_AVG_LEAD_TIME finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runRM_AVG_LEAD_TIME cannot disconnect from database");
		}
	}
?>
