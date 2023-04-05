<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("run0DOLLAR_SHIPLIN cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("run0DOLLAR_SHIPLIN cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### run0DOLLAR_SHIPLIN started at " . date('Y-m-d g:i:s a'));
			error_log("### run0DOLLAR_SHIPLIN CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'run0DOLLAR_SHIPLIN' ";
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
				$sql .= "'run0DOLLAR_SHIPLIN', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### run0DOLLAR_SHIPLIN SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

				error_log("### run0DOLLAR_SHIPLIN CHECKING CP_SHPLIN ");

				$sql  = " select ";
				$sql .= "	ih.id_ord, ";
				$sql .= "	ih.date_add, ";
				$sql .= "	ih.time_add, ";
				$sql .= "	ih.id_ship, ";
				$sql .= "	ih.seq_line_ord, ";
				$sql .= "	ih.qty_ship, ";
				$sql .= "	ih.PRICE_LIST_VP, ";
				$sql .= "	ih.PRICE_SELL_VP, ";
				$sql .= "	ih.PRICE_SELL_NET_VP, ";
				$sql .= "	ih.PRICE_NET, ";
				$sql .= "	ih.flag_mthd_price, ";
				$sql .= "	ih.PCT_DISC_MARKUP_1_VP, ";
				$sql .= "	bh.sls ";
				$sql .= "	FROM nsa.cp_shplin ih ";
				$sql .= "	left join nsa.bokhst_line as bh ";
				$sql .= "	on ih.id_ord=bh.id_ord ";
				$sql .= "	and ih.seq_line_ord=bh.seq_line_ord ";
				$sql .= "	and ih.id_item=bh.id_item ";
				$sql .= "	WHERE (ih.PRICE_LIST_VP = '0;001:0000000000')  ";
				$sql .= "	AND (ih.FLAG_MTHD_PRICE = 'D')  ";
				$sql .= "	AND (ih.PRICE_NET = '0.00')  ";
				$sql .= "	AND ih.qty_ship <> '0'  ";
				$sql .= "	AND (ih.PRICE_SELL_VP <> '0;001:0000000000') ";
				$sql .= "	order by ih.date_add desc ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					error_log("### run0DOLLAR_SHIPLIN found ZERO DOLLAR ID_ORD: " . $row['id_ord']);

					$sql0  = "SELECT * from nsa.CP_SHPLIN_ZERO_DOLLAR_ALERTS ";
					$sql0 .= " WHERE ID_ORD = '" . $row['id_ord'] . "' ";
					$sql0 .= " AND id_ship = '" . $row['id_ship'] . "' ";
					$sql0 .= " AND SEQ_LINE_ORD = '" . $row['seq_line_ord'] . "' ";
					QueryDatabase($sql0, $results0);
					if (mssql_num_rows($results0) == 0) {
						error_log("### run0DOLLAR_SHIPLIN INSERTING RECORD for ID_ORD: " . $row['id_ord']);
						$sql1  = "INSERT INTO nsa.CP_SHPLIN_ZERO_DOLLAR_ALERTS( ";
						$sql1 .= " ID_ORD, ";
						$sql1 .= " DATE_ADD, ";
						$sql1 .= " TIME_ADD, ";
						$sql1 .= " ID_SHIP, ";
						$sql1 .= " SEQ_LINE_ORD, ";
						$sql1 .= " QTY_SHIP, ";
						$sql1 .= " PRICE_LIST_VP, ";
						$sql1 .= " PRICE_SELL_VP, ";
						$sql1 .= " PRICE_SELL_NET_VP, ";
						$sql1 .= " PRICE_NET, ";
						$sql1 .= " FLAG_MTHD_PRICE, ";
						$sql1 .= " PCT_DISC_MARKUP_1_VP, ";
						$sql1 .= " bh_SLS, ";
						$sql1 .= " DATE_ALERT ";
						$sql1 .= ") VALUES ( ";
						$sql1 .= " '" . $row['id_ord'] . "', ";
						$sql1 .= " '" . $row['date_add'] . "', ";
						$sql1 .= " '" . $row['time_add'] . "', ";
						$sql1 .= " '" . $row['id_ship'] . "', ";
						$sql1 .= " '" . $row['seq_line_ord'] . "', ";
						$sql1 .= " '" . $row['qty_ship'] . "', ";
						$sql1 .= " '" . $row['PRICE_LIST_VP'] . "', ";
						$sql1 .= " '" . $row['PRICE_SELL_VP'] . "', ";
						$sql1 .= " '" . $row['PRICE_SELL_NET_VP'] . "', ";
						$sql1 .= " '" . $row['PRICE_NET'] . "', ";
						$sql1 .= " '" . $row['flag_mthd_price'] . "', ";
						$sql1 .= " '" . $row['PCT_DISC_MARKUP_1_VP'] . "', ";
						$sql1 .= " '" . $row['sls'] . "', ";
						$sql1 .= " getDate() ";
						$sql1 .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
						QueryDatabase($sql1, $results1);

						$subject = "NSA SHIPMENT LINE With ZERO DOLLAR: " . $row['id_ord'];
						$body = "A shipment with a ZERO dollar line item was recently entered. Please investigate." .
							"\r\n\r\nOrder: " . $row['id_ord'] .
							"\r\nDate Add: " . $row['date_add'] .
							"\r\nID Ship: " . $row['id_ship'] .
							"\r\nLine: " . $row['seq_line_ord'] .
							"\r\nQty Ship: " . $row['qty_ship'] .
							"\r\nPrice List VP: " . $row['PRICE_LIST_VP'] .
							"\r\nPrice Sell VP: " . $row['PRICE_SELL_VP'] .
							"\r\nPrice Sell Net VP: " . $row['PRICE_SELL_NET_VP'] .
							"\r\nPrice Net: " . $row['PRICE_NET'] .
							"\r\nFlag Method Price: " . $row['flag_mthd_price'] .
							"\r\nPct Disc Markup 1 VP: " . $row['PCT_DISC_MARKUP_1_VP'] .
							"\r\nSales: " . $row['sls'];

						$headers = "From: eProduction@thinknsa.com" . "\r\n" .
							"X-Mailer: PHP/" . phpversion();

						//$to = "rpapa@thinknsa.com";
						//mail($to, $subject, $body, $headers);
						//error_log("### MAIL SENT TO: " . $to);

						//$to = "jmartin@thinknsa.com";
						//mail($to, $subject, $body, $headers);
						//error_log("### MAIL SENT TO: " . $to);

						//$to = "gvandyne@thinknsa.com";
						//mail($to, $subject, $body, $headers);
						//error_log("### MAIL SENT TO: " . $to);

						$to = "group-zerodollardebug@thinknsa.com";
						mail($to, $subject, $body, $headers);
						error_log("### MAIL SENT TO: " . $to);

					} else {
						error_log("### run0DOLLAR_SHIPLIN Alert for ID_ORD: " . $row['id_ord'] . " ID_SHIP: " . $row['id_ship'] . " SEQ_LINE_ORD: " . $row['seq_line_ord'] . " Previously sent.");
					}
				}

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### run0DOLLAR_SHIPLIN DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				error_log("### run0DOLLAR_SHIPLIN ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### run0DOLLAR_SHIPLIN finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("run0DOLLAR_SHIPLIN cannot disconnect from database");
		}
	}
?>
