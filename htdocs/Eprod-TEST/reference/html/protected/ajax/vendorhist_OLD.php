<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["df"]) && isset($_POST["dt"]))  {

				$sql  = "SELECT ";
				$sql .= "	* ";
				$sql .= " FROM ";
				$sql .= " 	nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= "	PROC_NAME = 'runVendorHist' ";
				$sql .= "	and ";
				$sql .= "	FLAG_RUNNING = '1' ";
				$sql .= "	and ";
				$sql .= "	DATE_EXP > getDate()";
				QueryDatabase($sql, $results);

				if (mssql_num_rows($results) == 0) {
					//$DateFrom 		= str_replace("-","",$_POST["df"]);
					//$DateTo 		= str_replace("-","",$_POST["dt"]);
					$DateFrom 		= $_POST["df"];
					$DateTo 		= $_POST["dt"];


					//$date_sufx 		= " 00:00:00.000";
					//$date_sufx 		= " 00:00:00";
					//$DateFrom 		= $_POST["df"] . $date_sufx;
					//$DateTo 		= $_POST["dt"] . $date_sufx;
					$StartItem 		= $_POST["StartItem"];
					$EndItem 		= $_POST["EndItem"];
					$StartVendNum 		= $_POST["StartVendNum"];
					$EndVendNum 		= $_POST["EndVendNum"];

					$ret .= "		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>\n";
					$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

					$sql = "SET ANSI_NULLS ON";
					QueryDatabase($sql, $results);
					$sql = "SET ANSI_WARNINGS ON";
					QueryDatabase($sql, $results);

					$sql  = " select  ";
					$sql .= "	distinct iv.ID_ITEM, ";
					$sql .= "	iv.ID_ITEM_VND, ";
					$sql .= "	vm.ID_VND, ";
					$sql .= "	vm.NAME_VND, ";
					$sql .= "	ib.RATIO_STK_PUR, ";
					$sql .= "	ib.DESCR_1, ";
					$sql .= "	ib.DESCR_2, ";
				//	$sql .= "	Calcs.LY_Qty, ";
				//	$sql .= "	Calcs.LY_Cost, ";
					$sql .= "	Calcs.TY_Qty, ";
					$sql .= "	Calcs.TY_Cost ";
					$sql .= " from  ";
					$sql .= "	nsa.POHIST_LINE_RCPT r ";
					$sql .= " left join ";
					$sql .= "	nsa.ITMMAS_VND iv ";
					$sql .= "	on ";
					$sql .= "	iv.ID_ITEM = r.ID_ITEM ";
					$sql .= "	and	r.ID_VND_ORDFM = iv.ID_VND_ORDFM ";
					$sql .= " left join ";
					$sql .= "	nsa.VENMAS_ORDFM vm ";
					$sql .= "	on ";
					$sql .= "	r.ID_VND_ORDFM = vm.ID_VND ";
					$sql .= " left join ";
					$sql .= "	nsa.ITMMAS_BASE ib ";
					$sql .= "	on ";
					$sql .= "	ib.ID_ITEM = r.ID_ITEM ";
					$sql .= " left join nsa.POHIST_LINE_invc i ";
					$sql .= "	on r.ID_PO = i.ID_PO  ";
					$sql .= "	and r.ID_ITEM = i.ID_ITEM ";
					$sql .= " left join ";
					$sql .= "	(select iv.id_item as id_item, ";
					$sql .= "		iv.id_item_vnd as id_item_vnd, ";
					//$sql .= "		--i.amt_distrib, ";
				//	$sql .= "		sum(case when r.DATE_ADD >= DateADD(yy,-1," . $DateFrom .") and r.DATE_ADD <= DateADD(yy,-1," . $DateTo .")  ";
				//	//$sql .= "				--then r.QTY_RCV ";
				//	$sql .= "				then i.QTY_PAID ";
				//	$sql .= "				else 0 ";
				//	$sql .= "			end) as LY_Qty, ";
				//	$sql .= "		sum(case when r.DATE_ADD >= DateADD(yy,-1," . $DateFrom .") and r.DATE_ADD <= DateADD(yy,-1," . $DateTo .")  ";
				//	//$sql .= "				--then r.COST_EXPECT ";
				//	$sql .= "				then i.AMT_DISTRIB ";
				//	$sql .= "			end) as LY_Cost, ";
					//$sql .= "		sum(case when r.DATE_ADD >= " . $DateFrom ." and r.DATE_ADD <= " . $DateTo ."  ";
					$sql .= "		sum(case when r.DATE_ADD between " . $DateFrom ." and " . $DateTo ."  ";
					//$sql .= "				--then r.QTY_RCV ";
					$sql .= "				then i.QTY_PAID ";
					$sql .= "				else 0 ";
					$sql .= "			end) as TY_Qty, ";
					//$sql .= "		sum(case when r.DATE_ADD >= " . $DateFrom ." and r.DATE_ADD <= " . $DateTo ."  ";
					$sql .= "		sum(case when r.DATE_ADD between " . $DateFrom ." and " . $DateTo ."  ";
					//$sql .= "				--then r.COST_EXPECT ";
					$sql .= "				then i.AMT_DISTRIB ";
					$sql .= "			end) as TY_Cost ";
					$sql .= "		from nsa.POHIST_LINE_RCPT r ";
					$sql .= "		left join nsa.ITMMAS_VND iv ";
					$sql .= "		on r.ID_ITEM = iv.ID_ITEM ";
					$sql .= "		and	r.ID_VND_ORDFM = iv.ID_VND_ORDFM ";
					$sql .= "		left join nsa.POHIST_LINE_invc i ";
					$sql .= "		on r.ID_PO = i.ID_PO  ";
					$sql .= "		and r.ID_ITEM = i.ID_ITEM ";
					$sql .= "		group by iv.id_item_vnd, iv.id_item) Calcs ";
					$sql .= "	on Calcs.id_item = iv.id_item  ";
					$sql .= "	and Calcs.id_item_vnd = iv.id_item_vnd ";
					$sql .= " where  ";
					//$sql .= "	((r.DATE_ADD >= DateADD(yy,-1,'" . $DateFrom ."') AND r.DATE_ADD <= DateADD(yy,-1,'" . $DateTo ."')) ";
					//$sql .= "		OR ";
					//$sql .= "	(r.DATE_ADD >= '" . $DateFrom ."' AND r.DATE_ADD <= '" . $DateTo ."')) ";
					//$sql .= "	and ";
					$sql .= "	(r.id_item>='" . $StartItem ."' ";
					$sql .= "	and ";
					$sql .= "	r.id_item<='" . $EndItem ."') ";
					$sql .= "	and ";
					$sql .= "	(r.id_vnd_payto>=(" . $StartVendNum .") ";
					$sql .= "	and ";
					$sql .= "	r.id_vnd_payto<=(" . $EndVendNum .")) ";
					$sql .= "	and ";
					$sql .= "	iv.ID_ITEM_VND is not null ";
					$DEBUG=1;
					QueryDatabase($sql, $results);
					$DEBUG=0;

					$ret .= "		<h4>Num Rows: " . mssql_num_rows($results) ."</h4>\n";
					error_log("vendorhist.php: " . mssql_num_rows($results) . " rows");
					$ret .= "		<table class='sample'>\n";
					$ret .= "			<tr>\n";
					$ret .= "			</tr>\n";
					$prevIdVnd = '';
					$b_flip = true;
					//$i = 0;
					while ($row = mssql_fetch_assoc($results)) {
						if ($prevIdVnd != $row['ID_VND']) {
							$b_flip = !$b_flip;
						}
						if ($b_flip) {
							$trClass = 'd1';
						} else {
							$trClass = 'd0';
						}




						$ret .= "		 	<tr class='" . $trClass . "Top'>\n";
						$ret .= "				<th class='" . $trClass . "'>" . $row['ID_VND'] . "</th>\n";
						$ret .= "				<th class='" . $trClass . "'>" . $row['ID_ITEM'] . "</th>\n";
						$ret .= "				<th class='" . $trClass . "'>" . $row['ID_ITEM_VND'] . "</th>\n";
						$ret .= "				<th class='" . $trClass . "'>" . $row['DESCR_1'] . " " . $row['DESCR_2'] . "</th>\n";
//						$ret .= "				<th class='" . $trClass . "'>" . $row['LY_Qty'] . "</th>\n";
//						$ret .= "				<th class='" . $trClass . "'>" . $row['LY_Cost'] . "</th>\n";
						$ret .= "				<th class='" . $trClass . "'>" . $row['TY_Qty'] . "</th>\n";
						$ret .= "				<th class='" . $trClass . "'>" . $row['TY_Cost'] . "</th>\n";
						$ret .= "		 	<tr>\n";
					}
					$ret .= "		</table>\n";

					$sql = "SET ANSI_NULLS OFF";
					QueryDatabase($sql, $results);
					$sql = "SET ANSI_WARNINGS OFF";
					QueryDatabase($sql, $results);
				} else {
					$row = mssql_fetch_assoc($results);
					$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";
					$ret .= "		<h1 class='red'>SERVER BUSY</h1>\n";
					$ret .= "		<h1 class='red'>Try again after " . $row['DATE_EXP'] . "</h1>\n";
				}

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
