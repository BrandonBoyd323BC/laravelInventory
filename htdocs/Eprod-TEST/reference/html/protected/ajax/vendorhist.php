<?php

	$DEBUG = 1;

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
				$DateFrom 		= $_POST["df"];
				$DateTo 		= $_POST["dt"];
				$StartItem 		= $_POST["StartItem"];
				$EndItem 		= $_POST["EndItem"];
				$StartVendNum 		= $_POST["StartVendNum"];
				$EndVendNum 		= $_POST["EndVendNum"];

				$DateFromLY 		= date('Y-m-d', strtotime("-1 year",strtotime($_POST["df"])));
				$DateToLY 		= date('Y-m-d', strtotime("-1 year",strtotime($_POST["dt"])));

				$ret .= "		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>\n";
				$ret .= "		<h4>" . $DateFromLY . " -- " . $DateToLY . "</h4>\n";
				$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

				$sql1 = "SET ANSI_NULLS ON";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql1, $results1);

				$sql  = "SELECT ";
				$sql .= "	distinct r.ID_VND_ORDFM as ID_VND, ";
				$sql .= "	vm.NAME_VND ";
				$sql .= " FROM ";
				$sql .= " 	nsa.POHIST_LINE_RCPT r ";
				$sql .= " left join ";
				$sql .= "	nsa.VENMAS_ORDFM vm ";
				$sql .= "	on ";
				$sql .= "	r.ID_VND_ORDFM = vm.ID_VND ";
				$sql .= " WHERE ";
				$sql .= "	(r.DATE_ADD between '" . $DateFrom ."' and  '" . $DateTo ."'  OR r.DATE_ADD between '" . $DateFromLY ."' and  '" . $DateToLY ."' )";
				$sql .= "	and ";
				$sql .= "	(r.id_item>='" . $StartItem ."' ";
				$sql .= "	and ";
				$sql .= "	r.id_item<='" . $EndItem ."') ";
				$sql .= "	and ";
				$sql .= "	(r.id_vnd_payto>=(" . $StartVendNum .") ";
				$sql .= "	and ";
				$sql .= "	r.id_vnd_payto<=(" . $EndVendNum .")) ";
				QueryDatabase($sql, $results);

				//$ret .= "		<h4>Num Vendors: " . mssql_num_rows($results) ."</h4>\n";
				$ret .= "		<table class='sample'>\n";
				$ret .= "			<tr>\n";
				$ret .= "			</tr>\n";

				$prevIdVnd = '';
				$b_flip = true;


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
					$ret .= "				<th class='" . $trClass . "' colspan=3>" . $row['NAME_VND'] . "</th>\n";
					$ret .= "				<th class='" . $trClass . "'></th>\n";
					$ret .= "				<th class='" . $trClass . "'></th>\n";
					$ret .= "				<th class='" . $trClass . "'></th>\n";
					$ret .= "				<th class='" . $trClass . "'></th>\n";
					$ret .= "		 	<tr>\n";
					$ret .= "		 	<tr class='" . $trClass . "'>\n";
					$ret .= "				<th class='" . $trClass . "'></th>\n";
					$ret .= "				<th class='" . $trClass . "'>ID ITEM</th>\n";
					$ret .= "				<th class='" . $trClass . "'>ID ITEM VND</th>\n";
					$ret .= "				<th class='" . $trClass . "'>DESCR</th>\n";
					$ret .= "				<th class='" . $trClass . "'>LY Qty</th>\n";
					$ret .= "				<th class='" . $trClass . "'>LY Avg Unit Price</th>\n";
					$ret .= "				<th class='" . $trClass . "'>LY Cost</th>\n";
					$ret .= "				<th class='" . $trClass . "'>TY Qty</th>\n";
					$ret .= "				<th class='" . $trClass . "'>TY Avg Unit Price</th>\n";
					$ret .= "				<th class='" . $trClass . "'>TY Cost</th>\n";
					$ret .= "		 	<tr>\n";

					$sql1  = " select  ";
					$sql1 .= "	distinct iv.ID_ITEM, ";
					$sql1 .= "	iv.ID_ITEM_VND, ";
					$sql1 .= " 	r.ID_VND_ORDFM as ID_VND, ";
					//$sql1 .= " 	r.DATE_ADD, ";
					$sql1 .= "	ib.RATIO_STK_PUR, ";
					$sql1 .= "	ib.DESCR_1, ";
					$sql1 .= "	ib.DESCR_2, ";
					$sql1 .= "	Calcs.LY_Qty, ";
					$sql1 .= "	Calcs.LY_Cost, ";
					$sql1 .= "	Calcs.TY_Qty, ";
					$sql1 .= "	Calcs.TY_Cost ";
					//$sql1 .= " 	r.ID_PO ";
					//$sql1 .= " 	r.* ";
					$sql1 .= " from  ";
					$sql1 .= "	nsa.POHIST_LINE_RCPT r ";
					$sql1 .= " left join ";
					$sql1 .= "	nsa.ITMMAS_VND iv ";
					$sql1 .= "	on ";
					$sql1 .= "	iv.ID_ITEM = r.ID_ITEM ";
					$sql1 .= "	and	r.ID_VND_ORDFM = iv.ID_VND_ORDFM ";
					$sql1 .= " left join ";
					$sql1 .= "	nsa.ITMMAS_BASE ib ";
					$sql1 .= "	on ";
					$sql1 .= "	ib.ID_ITEM = r.ID_ITEM ";

					$sql1 .= " left join ";
					$sql1 .= "	(select iv.id_item as id_item, ";
					$sql1 .= "		iv.id_item_vnd as id_item_vnd, ";
					$sql1 .= "		sum(case when r.DATE_ADD between '" . $DateFromLY ."' and  '" . $DateToLY . "' ";
					$sql1 .= "				then i.QTY_PAID ";
					$sql1 .= "				else 0 ";
					$sql1 .= "			end) as LY_Qty, ";
					$sql1 .= "		sum(case when r.DATE_ADD between '" . $DateFromLY ."' and  '" . $DateToLY . "' ";
					//$sql1 .= "				--then r.COST_EXPECT ";
					$sql1 .= "				then i.AMT_DISTRIB ";
					$sql1 .= "			end) as LY_Cost, ";
					$sql1 .= "		sum(case when r.DATE_ADD between '" . $DateFrom ."' and '" . $DateTo . "'  ";
					//$sql1 .= "				--then r.QTY_RCV ";
					$sql1 .= "				then i.QTY_PAID ";
					$sql1 .= "				else 0 ";
					$sql1 .= "			end) as TY_Qty, ";
					$sql1 .= "		sum(case when r.DATE_ADD between '" . $DateFrom ."' and '" . $DateTo . "'  ";
					//$sql1 .= "				--then r.COST_EXPECT ";
					$sql1 .= "				then i.AMT_DISTRIB ";
					$sql1 .= "			end) as TY_Cost ";
					$sql1 .= "		from nsa.POHIST_LINE_RCPT r ";
					$sql1 .= "		left join nsa.ITMMAS_VND iv ";
					$sql1 .= "			on r.ID_ITEM = iv.ID_ITEM ";
					$sql1 .= "			and	r.ID_VND_ORDFM = iv.ID_VND_ORDFM ";
					$sql1 .= "		left join nsa.POHIST_LINE_invc i ";
					$sql1 .= "			on r.ID_PO = i.ID_PO  ";
					$sql1 .= "			and r.ID_ITEM = i.ID_ITEM ";
					$sql1 .= "		group by iv.id_item_vnd, iv.id_item) Calcs ";
					$sql1 .= "	on Calcs.id_item = iv.id_item  ";
					$sql1 .= "	and Calcs.id_item_vnd = iv.id_item_vnd ";

					$sql1 .= " where  ";
					$sql1 .= "	r.ID_VND_ORDFM = '" . $row['ID_VND'] . "' ";
					$sql1 .= "	and ";
					$sql1 .= "	(r.DATE_ADD between '" . $DateFrom ."' and  '" . $DateTo ."'  OR r.DATE_ADD between '" . $DateFromLY ."' and  '" . $DateToLY ."' )";
					$sql1 .= "	and ";
					$sql1 .= "	(r.id_item>='" . $StartItem ."' ";
					$sql1 .= "	and ";
					$sql1 .= "	r.id_item<='" . $EndItem ."') ";
					$sql1 .= "	and ";
					$sql1 .= "	(r.id_vnd_payto>=(" . $StartVendNum .") ";
					$sql1 .= "	and ";
					$sql1 .= "	r.id_vnd_payto<=(" . $EndVendNum .")) ";
					$sql1 .= "	and ";
					$sql1 .= "	iv.ID_ITEM_VND is not null ";
					QueryDatabase($sql1, $results1);

/*
					$sql  = " select  ";
					$sql .= "	distinct iv.ID_ITEM, ";
					$sql .= "	iv.ID_ITEM_VND, ";
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
*/

					while ($row1 = mssql_fetch_assoc($results1)) {

						/*
						$ret .= "		 	<tr class='" . $trClass . "'>\n";
						$ret .= "				<td class='" . $trClass . "'>" . $row1['DATE_ADD'] . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . $row1['ID_ITEM'] . "</td>\n";
						$ret .= "		 	<tr>\n";
						*/


						$ret .= "		 	<tr class='" . $trClass . "'>\n";
						$ret .= "				<td class='" . $trClass . "'></td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . $row1['ID_ITEM'] . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . $row1['ID_ITEM_VND'] . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . $row1['DESCR_1'] . " " . $row1['DESCR_2'] . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . round($row1['LY_Qty'] / $row1['RATIO_STK_PUR']) . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . round($row1['LY_Cost'] / $row1['LY_Qty'],2) . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . round($row1['LY_Cost'] / $row1['RATIO_STK_PUR'],2). "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . round($row1['TY_Qty'] / $row1['RATIO_STK_PUR']) . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . round($row1['TY_Cost'] / $row1['TY_Qty'],2) . "</td>\n";
						$ret .= "				<td class='" . $trClass . "'>" . round($row1['TY_Cost'] / $row1['RATIO_STK_PUR'],2) . "</td>\n";
						$ret .= "		 	<tr>\n";

					}



				}
				$ret .= "		</table>\n";

				$sql1 = "SET ANSI_NULLS OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql1, $results1);

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
