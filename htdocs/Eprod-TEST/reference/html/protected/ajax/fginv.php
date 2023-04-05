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

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

			if (mssql_num_rows($results) == 0) {

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);

				$sql  = "Select ";
				$sql .= "	ID_WC, ";
				$sql .= "	sum(wc.CAP) as SUM_WC_CAP";
				$sql .= " from ";
				$sql .= "	nsa.WC_CAP wc ";
				$sql .= " group by ";
				$sql .= "	wc.ID_WC ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					${$row['ID_WC']} = array("cap"=>$row['SUM_WC_CAP'], "MaxTotal"=>0, "CurrToMaxTotal"=>0);//<
				}

				$sql  = "select ";
				$sql .= "	ro.LEVEL_ROP, ";
				$sql .= "	ro.QTY_MIN_ROP, ";
				$sql .= "	ro.QTY_MULT_ORD_ROP, ";
				$sql .= "	(ro.LEVEL_ROP / 2) as CALC_ROP, ";
				$sql .= "	case when ((l.qty_onhd + l.qty_onord - l.qty_alloc) < (ro.LEVEL_ROP / 2)) ";
				$sql .= "		then (case when (ro.QTY_MULT_ORD_ROP <> '0') then floor((ro.LEVEL_ROP - (l.qty_onhd + l.qty_onord - l.qty_alloc))/ro.QTY_MULT_ORD_ROP) * ro.QTY_MULT_ORD_ROP else '0' end) ";
				$sql .= "		else ('0') ";
				$sql .= "	end as SUG_RO_QTY, ";
				$sql .= "	(Select ";
				$sql .= "			sum(PS.qty_per*SL.qty_ship) ";
				$sql .= "		from ";
				$sql .= "			nsa.PRDSTR PS ";
				$sql .= "		left join ";
				//$sql .= "			nsa.slshst_line Sl ";
				$sql .= "			nsa.CP_INVLIN_HIST Sl ";
				$sql .= "			on ";
				$sql .= "			sl.id_item=ps.id_item_par ";
				$sql .= "		left join ";
				$sql .= "			nsa.Cp_invhdr_hist IH ";
				$sql .= "			on sl.id_invc=IH.id_invc ";
				$sql .= "		where ";
				$sql .= "			id_item_comp = b.id_item ";
				$sql .= "			and datediff (day, ih.date_invc, getdate()) < 180 ";
				$sql .= "	) as qty_comp, ";
				$sql .= "	(select  ";
				$sql .= "			sum(ln.qty_ship) as roll_six ";
				$sql .= "		from  ";
				$sql .= "			nsa.CP_INVLIN_Hist ln  ";
				$sql .= "		where  ";
				$sql .= "			datediff (day, date_invc, getdate()) < 180 ";
				$sql .= "			and ";
				$sql .= "			ln.id_item = b.id_item ";
				$sql .= "		group by  ";
				$sql .= "			ln.id_item ";
				$sql .= "	) as qty_noncomp, ";
				$sql .= "	b.code_comm,  ";
				$sql .= "	cf.comm_desc_full, ";
				$sql .= "	b.ID_ITEM, ";
				$sql .= "	b.code_user_3_im, ";
				$sql .= "	b.DESCR_1, ";
				$sql .= "	b.DESCR_2, ";
				$sql .= "	b.code_um_stk,  ";
				$sql .= "	l.qty_onhd, ";
				$sql .= "	(l.qty_alloc) as ALLOC, ";
				$sql .= "	(l.qty_onord) as On_Order, ";
				$sql .= "	(l.qty_onhd-l.qty_alloc+l.qty_onord) as Net_Avail, ";
				$sql .= "    (l.qty_onhd-l.qty_alloc+l.qty_onord)-ro.LEVEL_ROP as Opt_Variance, ";
				$sql .= "	(l.QTY_USAGE_MTD) as MTD, ";
				$sql .= "	(l.QTY_USAGE_YTD) as YTD, ";
				$sql .= "	(l.QTY_USAGE_YR_LAST) as LST_YR, ";
				$sql .= "	b.code_user_2_im, ";
				if ($dbName == 'TCM96') {
					$sql .= "	l.FLAG_SOURCE, ";
					$sql .= "	l.FLAG_STK, ";
					$sql .= "	l.ID_RTE, ";
				} else {
					$sql .= "	b.FLAG_MFG_PUR, ";
					$sql .= "	b.FLAG_STK, ";
					$sql .= "	b.ID_RTE, ";
				}
				$sql .= "	ro.QTY_MULT_ORD_ROP, ";
				$sql .= "    c.COST_TOTAL_ACCUM_STD as Std_cost, ";
				$sql .= "	l.qty_onhd*c.COST_TOTAL_ACCUM_STD as Std_EXT, ";
				$sql .= "	l.cost_base_lifo, ";
				$sql .= "	l.qty_onhd*l.cost_base_lifo as Base_Cost_EXT, ";
				$sql .= "	1 as count ";
				$sql .= " from ";
				$sql .= "	nsa.ITMMAS_BASE b ";
				$sql .= " left join ";
				$sql .= "	nsa.ITMMAS_LOC l ";
				$sql .= "	on b.id_item=l.id_item ";
				$sql .= " left join ";
				$sql .= "	nsa.tables_code_comm cc ";
				$sql .= "	on b.code_comm=cc.code_comm ";
				$sql .= " left join ";
				$sql .= "	nsa.ITMMAS_REORD ro ";
				$sql .= "	on b.id_item=ro.id_item ";
				$sql .= " left join ";
				$sql .= "	nsa.ITMMAS_COST c ";
				$sql .= "	on b.id_item=c.id_item ";
				$sql .= " left join ";
				$sql .= "	nsa.cus_comm_code_full cf ";
				$sql .= "	on b.code_comm=cf.comm_code ";
				$sql .= " where ";
				$sql .= "	b.code_comm like 'F0%' ";
				$sql .= "	or ";
				$sql .= "	b.code_comm like 'F1%' ";
				$sql .= "	or ";
				$sql .= "	b.code_comm like 'F2%' ";
				$sql .= "	or ";
				$sql .= "	b.code_comm like 'F3%' ";
				$sql .= "	or ";
				$sql .= "	b.code_comm like 'F4%' ";
				$sql .= "	or ";
				$sql .= "	b.code_comm like 'F5%' ";
				$sql .= "	or ";
				$sql .= "	b.code_comm like 'F6%' ";
				$sql .= " Order by ";
				$sql .= "	b.code_comm, ";
				$sql .= "	b.code_user_2_im ASC, ";
				$sql .= "	b.id_item ASC ";
				QueryDatabase($sql, $results);

				if ($_POST['action'] == 'itemDetail') {
					$ret .= "		<h4>Row Count: " . mssql_num_rows($results) ."</h4>\n";
					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Item</th>\n";
					$ret .= " 		<th class='sample'>Description</th>\n";
					$ret .= " 		<th class='sample'>UOM</th>\n";
					$ret .= " 		<th class='sample'>Qty OH</th>\n";
					$ret .= " 		<th class='sample'>Qty on Ord</th>\n";
					$ret .= " 		<th class='sample'>Qty Alloc</th>\n";
					$ret .= " 		<th class='sample'>Var.</th>\n";
					$ret .= " 		<th class='sample'># Mo OH</th>\n";
					$ret .= " 		<th class='sample'>6 Mo Usage / Mo</th>\n";
					$ret .= " 		<th class='sample'>Min Stk</th>\n";
					$ret .= " 		<th class='sample'>Max Stk</th>\n";
					$ret .= " 		<th class='sample'>ROP</th>\n";
					$ret .= " 		<th class='sample'>RO Qty</th>\n";
					$ret .= " 		<th class='sample'>WC Days Max</th>\n";
					$ret .= " 		<th class='sample'>WC Days Curr to Max</th>\n";
					$ret .= " 	</tr>\n";

					while ($row = mssql_fetch_assoc($results)) {
						if ($DEBUG) {
							error_log("ITEM: " . $row['ID_ITEM']);
						}

						$Variance = $row['qty_onhd'] - $row['LEVEL_ROP'];
						$NoMonthsOH = round($row['qty_onhd'] / ( round((($row['qty_comp'] + $row['qty_noncomp']/6)))));
						$SixMonthPerMonth = round((($row['qty_comp'] + $row['qty_noncomp'])/6));

						$b_flip = !$b_flip;
						if ($b_flip) {
							$trClass = 'd1s';
						} else {
							$trClass = 'd0s';
						}

						$ret .= " 	<tr class='" . $trClass . "'>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['ID_ITEM'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['DESCR_1'] . " " . $row['DESCR_2'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['code_um_stk'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['qty_onhd'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['On_Order'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['ALLOC'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $Variance . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $NoMonthsOH  . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $SixMonthPerMonth . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['QTY_MIN_ROP'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['LEVEL_ROP'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['CALC_ROP'] . "</td>\n";
						$ret .= " 		<td class='" . $trClass . "'>" . $row['SUG_RO_QTY'] . "</td>\n";

						//QUERY TO GET THE NUMBER OF DAYS FOR THE WC AT CAPACITY
						$tot_time_all_WCs = 0;
						$tot_days_all_WCs = 0;
						$sql1  = "Select ";
						$sql1 .= "	distinct(ID_WC) ";
						$sql1 .= " from ";
						$sql1 .= "	nsa.ROUTMS_OPER ro ";
						$sql1 .= " where ";
						$sql1 .= "	ro.ID_ITEM = '" . $row['ID_ITEM'] . "' ";
						$sql1 .= "	and ";
						$sql1 .= "	ro.ID_RTE = '" . $row['ID_RTE'] . "' ";
						$sql1 .= "	and ";
						$sql1 .= "	ro.ID_WC between 2100 and 7999 ";
						QueryDatabase($sql1, $results1);
						while ($row1 = mssql_fetch_assoc($results1)) {
							$WC_CAP = ${$row1['ID_WC']}['cap'];
							$tot_min_WC = 0;

							$sql2  = "Select ";
							$sql2 .= "	* ";
							$sql2 .= " from ";
							$sql2 .= "	nsa.ROUTMS_OPER ro ";
							$sql2 .= " where ";
							$sql2 .= "	ro.ID_ITEM = '" . $row['ID_ITEM'] . "' ";
							$sql2 .= "	and ";
							$sql2 .= "	ro.ID_RTE = '" . $row['ID_RTE'] . "' ";
							$sql2 .= "	and ";
							$sql2 .= "	ro.ID_WC = '" . $row1['ID_WC'] . "' ";
							QueryDatabase($sql2, $results2);
							while ($row2 = mssql_fetch_assoc($results2)) {
								$tot_min_WC += ($row2['HR_MACH_SR'] * 60);
							}
							$tot_days_WC = ($tot_min_WC / $WC_CAP);
							$tot_days_all_WCs += $tot_days_WC;
						}

						$ret .= " 		<td class='sample'>" . round($tot_days_all_WCs * $row['LEVEL_ROP'],3) . "</td>\n";
						//$ret .= " 		<td class='sample'>" . round($tot_days_all_WCs * ($row['LEVEL_ROP'] - $row['qty_onhd']),3) . "</td>\n";
						$ret .= " 		<td class='sample'>" . round($tot_days_all_WCs * ($row['LEVEL_ROP'] - $row['Net_Avail']),3) . "</td>\n";
						$ret .= " 	</tr>\n";
					}
					$ret .= " </table>\n";
					$ret .= " </br>\n";

				} else if ($_POST['action'] == 'dash') {

					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Workcenter</th>\n";
					$ret .= " 		<th class='sample'>Description</th>\n";
					$ret .= " 		<th class='sample'>Capacity</th>\n";
					$ret .= " 		<th class='sample'>WC Days Max</th>\n";
					$ret .= " 		<th class='sample'>WC Days Curr to Max</th>\n";
					$ret .= " 	</tr>\n";

					$counter = 0;
					while ($row = mssql_fetch_assoc($results)) {
						$counter++;
						$sql1  = "Select ";
						$sql1 .= "	* ";
						$sql1 .= " from ";
						$sql1 .= "	nsa.ROUTMS_OPER ro ";
						$sql1 .= " where ";
						$sql1 .= "	ro.ID_ITEM = '" . $row['ID_ITEM'] . "' ";
						$sql1 .= "	and ";
						$sql1 .= "	ro.ID_RTE = '" . $row['ID_RTE'] . "' ";
						$sql1 .= "	and ";
						$sql1 .= "	ro.ID_WC between '1999' and '7999' ";
						QueryDatabase($sql1, $results1);
						while ($row1 = mssql_fetch_assoc($results1)) {
							${$row1['ID_WC']}['MaxTotal'] += ($row1['HR_MACH_SR'] * 60 * $row['LEVEL_ROP']);
							//${$row1['ID_WC']}['CurrToMaxTotal'] += ($row1['HR_MACH_SR'] * 60 * ($row['LEVEL_ROP'] - $row['qty_onhd']));
							${$row1['ID_WC']}['CurrToMaxTotal'] += ($row1['HR_MACH_SR'] * 60 * ($row['LEVEL_ROP'] - $row['Net_Avail']));
							if ($DEBUG) {
								error_log("ITEM: " . $counter . " of " . mssql_num_rows($results));
							}
						}
					}

					$sql1 =  "select ";
					$sql1 .= "	wc.ID_WC, ";
					$sql1 .= "	wc.DESCR_WC ";
					$sql1 .= " from ";
					$sql1 .= "  nsa.tables_loc_dept_wc wc ";
					$sql1 .= " where ";
					$sql1 .= "	wc.ID_WC between '1999' and '7999' ";
					QueryDatabase($sql1, $results1);
					while ($row1 = mssql_fetch_assoc($results1)) {
						$b_flip = !$b_flip;
						if ($b_flip) {
							$trClass = 'd1s';
						} else {
							$trClass = 'd0s';
						}
						$ret .= " 	<tr class='" . $trClass . "'>\n";
						$ret .= " 		<td class='sample'>" . $row1['ID_WC'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . $row1['DESCR_WC'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . ${$row1['ID_WC']}['cap'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . round(${$row1['ID_WC']}['MaxTotal'] / ${$row1['ID_WC']}['cap'],3) . "</td>\n";
						$ret .= " 		<td class='sample'>" . round(${$row1['ID_WC']}['CurrToMaxTotal'] / ${$row1['ID_WC']}['cap'],3) . "</td>\n";
						$ret .= " 	</tr>\n";
					}

					$ret .= " </table>\n";
					$ret .= " </br>\n";
				}

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
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
