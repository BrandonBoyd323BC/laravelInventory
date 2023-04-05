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
				$sql .= "	PROC_NAME = 'runWC_OPEN' ";
				$sql .= "	and ";
				$sql .= "	FLAG_RUNNING = '1' ";
				$sql .= "	and ";
				$sql .= "	DATE_EXP > getDate()";
				QueryDatabase($sql, $results);

				if (mssql_num_rows($results) == 0) {
					$DateFrom 		= str_replace("-","",$_POST["df"]);
					$DateTo 		= str_replace("-","",$_POST["dt"]);
					$StartProdCat	= $_POST["StartProdCat"];
					$EndProdCat 	= $_POST["EndProdCat"];
					$StartItem 		= $_POST["StartItem"];
					$EndItem 		= $_POST["EndItem"];
					$StartCustNum 	= $_POST["StartCustNum"];
					$EndCustNum 	= $_POST["EndCustNum"];
					$Order_Num 		= $_POST["Order_Num"];
					$CapPCT 		= $_POST["CapPCT"];
					$FlagDetail		= $_POST["FlagDetail"];
					$FlagComments	= $_POST["FlagComments"];
					$CapPCT_Dec		= ($CapPCT / 100);

					$ret .= "		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>\n";
					$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

					$sql = "SET ANSI_NULLS ON";
					QueryDatabase($sql, $results);
					$sql = "SET ANSI_WARNINGS ON";
					QueryDatabase($sql, $results);

					$sql  = " select distinct ";
					$sql .= "	CONVERT(varchar(10), P.date_ord, 1) as DO, ";
					$sql .= "	Convert(DATETIME,P.DATE_RQST,1) as DR, ";
					$sql .= " 	Convert(DATETIME,P.DATE_PROM,1) as DP, ";
					$sql .= " 	Convert(varchar(10),P.DATE_PROM,1) as DP2, ";
					$sql .= " 	P.id_item, ";
					$sql .= "                 case when (select TOP 1 'LOW!' from nsa.ITMMAS_cost left join nsa.prdstr PS2 on PS2.id_item_par=P.id_item left join nsa.ITMMAS_Loc L2 on PS2.id_item_comp=L2.id_item ";
					$sql .= " 	left join nsa.ITMMAS_BASE B2 on PS2.id_item_comp=B2.id_item ";
					if ($dbName == 'TCM101') {
						$sql .= " 	where L2.qty_alloc>L2.qty_onHD and L2.flag_cntrl not like 'N') like 'LOW!' then 'LOW!'  ";
					} else {
						$sql .= " 	where L2.qty_alloc>L2.qty_onHD and B2.flag_cntrl not like 'N') like 'LOW!' then 'LOW!'  ";
					}
					$sql .= " 	else (select TOP 1 'OK' from nsa.ITMMAS_cost left join nsa.prdstr PS2 on PS2.id_item_par=P.id_item left join nsa.ITMMAS_Loc L2 on PS2.id_item_comp=L2.id_item ";
					$sql .= " 	left join nsa.ITMMAS_BASE B2 on PS2.id_item_comp=B2.id_item ";
					if ($dbName == 'TCM101') {
						$sql .= " 	where L2.qty_alloc<L2.qty_onHD and L2.flag_cntrl not like 'N') end as comp_status, ";
					} else {
						$sql .= " 	where L2.qty_alloc<L2.qty_onHD and B2.flag_cntrl not like 'N') end as comp_status, ";
					}
					$sql .= " 	P.code_cat_prdt, ";
					$sql .= " 	P.id_ord, ";
					$sql .= " 	P.seq_line_ord, ";
					$sql .= " 	P.id_so_odbc, ";
					$sql .= " 	P.qty_open, ";
					$sql .= " 	P.flag_stat_item, ";
					$sql .= " 	P.flag_stk, ";
					$sql .= " 	P.code_um_price, ";
					$sql .= " 	P.cust_soldto, ";
					$sql .= " 	P.name_cust_soldto, ";
					$sql .= " 	S.stat_rec_SO,";
					$sql .= " 	S.id_SO, ";
					$sql .= " 	S.sufx_so, ";
					$sql .= " 	S.qty_ord, ";
					$sql .= " 	S.qty_cmpl, ";
					$sql .= " 	P.qty_ship_total, ";
					$sql .= " 	CS.id_ship, ";
					$sql .= " 	P.code_stat_ord ";
					$sql .= " FROM  ";
					$sql .= " 	nsa.rpt50_ordlin_promDt P ";
					$sql .= " LEFT JOIN ";
					$sql .= " 	nsa.CP_ORDHDR CO ";
					$sql .= " 	on ";
					$sql .= " 	P.id_ord=CO.id_ord ";
					$sql .= " LEFT JOIN ";
					$sql .= " 	nsa.CP_ORDLIN CL ";
					$sql .= " 	on ";
					$sql .= " 	P.id_ord=CL.id_ord ";
					$sql .= " 	and ";
					$sql .= " 	P.id_item=CL.id_item ";
					$sql .= " LEFT JOIN  ";
					$sql .= " 	nsa.shpord_hdr S ";
					$sql .= " 	ON  ";
					$sql .= " 	P.id_so_odbc=S.id_so ";
					$sql .= " left JOIN  ";
					$sql .= " 	nsa.CP_SHPLIN CS ";
					$sql .= " 	ON  ";
					$sql .= " 	P.id_ord=CS.id_ord ";
					$sql .= " 	and  ";
					$sql .= " 	P.id_item=CS.id_item ";


					$sql .= " left join ";
					$sql .= " 	nsa.PRDSTR PS ";
					$sql .= " 	on ";
					$sql .= " 	PS.id_item_Par=P.id_item ";
					$sql .= " left join ";
					$sql .= " 	nsa.ITMMAS_LOC L ";
					$sql .= " 	ON ";
					$sql .= " 	PS.id_item_comp=L.id_item ";



					$sql .= " left join ";
					$sql .= " 	nsa.ITMMAS_BASE B ";
					$sql .= " 	on ";
					//$sql .= " 	P.id_item=B.id_item ";
					$sql .= " 	PS.id_item_comp=B.id_item ";
					$sql .= " WHERE  ";
					$sql .= " 	CS.id_ship is NULL ";
					$sql .= " 	and ";
					$sql .= " 	P.code_cat_prdt between '" . $StartProdCat . "' and  '" . $EndProdCat ."' ";
					$sql .= " 	and ";
					$sql .= " 	P.id_item between '" . $StartItem . "' and '" . $EndItem . "' ";
					$sql .= "   and ";
					$sql .= " 	rtrim(ltrim(P.cust_soldto)) between '" . $StartCustNum . "' and '" . $EndCustNum . "'";
					//$sql .= " 	P.cust_soldto >= '" . $StartCustNum . "' and P.cust_soldto <= '" . $EndCustNum . "'";
					$sql .= "   and ";
					$sql .= " 	P.DATE_PROM between '" . $DateFrom . "' and '" . $DateTo . "' ";
					$sql .= " 	and ";
					$sql .= " 	P.date_ord is not NULL ";
					if ($Order_Num != '') {
						$sql .=  "   and ";
						$sql .=  "   P.id_ord = '" . $Order_Num . "' ";
					}
					$sql .= " ORDER BY  ";
					//$sql .= "	Convert(DATETIME,P.DATE_RQST,1), ";
					$sql .= "	Convert(DATETIME,P.date_prom,1), ";
					$sql .= "	P.id_ord ASC,  ";
					$sql .= "	P.id_so_odbc ASC, ";
					$sql .= "	P.id_item ASC, ";
					$sql .= " 	P.seq_line_ord ASC, ";
					$sql .= " 	S.sufx_so ASC ";

					error_log("QUERY: ".$sql);
					QueryDatabase($sql, $results);
					$ret .= "		<h4>Num Rows: " . mssql_num_rows($results) ."</h4>\n";
					error_log("promise.php: " . mssql_num_rows($results) . " rows");
					$ret .= "		<table class='sample'>\n";

					$prevIdOrd = '';
					$b_flip = true;
					$i = 0;
					while ($row = mssql_fetch_assoc($results)) {
						$Ordlin_Rowid = '';
						$Ordhdr_Rowid = '';
						$DatePromTS = strtotime($row['DP']);

						$sql2  = "select ";
						$sql2 .= " CL.rowid as CLRowid ";
						$sql2 .= "FROM ";
						$sql2 .= " nsa.CP_ORDLIN CL ";
						$sql2 .= "WHERE ";
						$sql2 .= " CL.ID_ORD = '" . $row['id_ord'] . "' ";
						$sql2 .= " and ";
						$sql2 .= " CL.ID_ITEM = '" . $row['id_item'] . "' ";
						$sql2 .= " and ";
						$sql2 .= " CL.SEQ_LINE_ORD = '" . $row['seq_line_ord'] . "' ";
						QueryDatabase($sql2, $results2);
						if (mssql_num_rows($results2) > 1) {
							error_log("ORDLIN rows: " . mssql_num_rows($results2));
						}
						$row2 = mssql_fetch_assoc($results2);
						$Ordlin_Rowid = $row2['CLRowid'];

						$sql2  = "select ";
						$sql2 .= " CH.rowid as CHRowid, ";
						$sql2 .= " CH.ID_PO_CUST ";
						$sql2 .= "FROM ";
						$sql2 .= " nsa.CP_ORDHDR CH ";
						$sql2 .= "WHERE ";
						$sql2 .= " CH.ID_ORD = '" . $row['id_ord'] . "' ";
						QueryDatabase($sql2, $results2);
						if (mssql_num_rows($results2) > 1) {
							error_log("ORDHDR rows: " . mssql_num_rows($results2));
						}
						$row2 = mssql_fetch_assoc($results2);
						$Ordhdr_Rowid = $row2['CHRowid'];

						$i++;
						$wc8000 = false;
						$wcOTHER = false;
						if ($prevIdOrd != $row['id_ord']) {
							$b_flip = !$b_flip;
						}
						if ($b_flip) {
							$trClass = 'd1';
						} else {
							$trClass = 'd0';
						}

						if ($prevIdOrd != $row['id_ord']) {
							$ret .= "		 	<tr class='" . $trClass . "Top'>\n";
							$ret .= "				<th class='" . $trClass . "'>Order</th>\n";
							$ret .= "				<th class='" . $trClass . "'>" . $row['id_ord'] . "</th>\n";
							$ret .= "				<td colspan=2 class='" . $trClass . "'><font class='bold'>" . $row2['ID_PO_CUST'] . "</font></td>\n";
							$ret .= "				<td colspan=3 class='" . $trClass . "'><font class='bold'>" . $row['name_cust_soldto'] . "</font></td>\n";
							$ret .= "				<td colspan=11 class='" . $trClass . "' onclick=\"showAddCommentRow('" . $Ordhdr_Rowid . "', 'CH','" . $trClass . "', '" . $DatePromTS ."')\">+ Comments</td>\n";
							$ret .= "		 	<tr>\n";
							$ret .= "		 	<tr class='" . $trClass . "' id='CH_cmt_row_" . $Ordhdr_Rowid . "'>\n";
							if ($FlagComments == 'true') {
								$sql2  = "select ";
								$sql2 .= " convert(varchar(19),DATE_ADD,100) as DATE_ADD2, ";
								$sql2 .= " * ";
								$sql2 .= "FROM ";
								$sql2 .= " nsa.CUSTOM_COMMENTS CC ";
								$sql2 .= "WHERE ";
								$sql2 .= " CC.T_ROWID = '" . $Ordhdr_Rowid . "' ";
								$sql2 .= " and ";
								$sql2 .= " CC.TABLE_NAME = 'CP_ORDHDR' ";
								$sql2 .= " and ";
								$sql2 .= " CC.DATE_PROM_TS = '" . $DatePromTS . "' ";
								$sql2 .= "ORDER BY ";
								$sql2 .= " CC.DATE_ADD asc ";
								QueryDatabase($sql2, $results2);
								if (mssql_num_rows($results2) > 0) {
									$ret .= "				<td colspan=7></td>\n";
									$ret .= "				<td colspan=10>\n";
									$ret .= "		 			<table class='" . $trClass . "'>\n";
									$ret .= "						<tr class='" . $trClass . "'>\n";
									$ret .= "							<th onclick=\"closeDiv('CH_cmt_row_" . $Ordhdr_Rowid . "')\">X</th>\n";
									$ret .= "							<th>Date Added</th>\n";
									$ret .= "							<th>Added By</th>\n";
									$ret .= "							<th>Comment</th>\n";
									$ret .= "						</tr>\n";
									$p = 0;
									while ($row2 = mssql_fetch_assoc($results2)) {
										$p++;
										$ret .= "						<tr class='" . $trClass . "' id='" . $row2['rowid'] . "'>\n";
										$ret .= "							<td></td>\n";
										$ret .= "							<td>" . $row2['DATE_ADD2'] . "</td>\n";
										$ret .= "							<td>" . $row2['ID_USER_ADD'] . "</td>\n";
										$ret .= "							<td>" . $row2['COMMENT'] . "</td>\n";
										if ($p == mssql_num_rows($results2)) {
											$ret .= "							<td onclick=\"showAddCommentTextboxRow('" . $Ordhdr_Rowid . "', 'CH','" . $trClass . "', '" . $DatePromTS ."')\">+</td>\n";
										}
										$ret .= "						</tr>\n";

									}
									$ret .= "						<tr class='" . $trClass . "' id='CH_row_add_comment_" . $Ordhdr_Rowid . "'>\n";
									$ret .= "						</tr>\n";
									$ret .= "		 			</table>\n";
									$ret .= "				</td>\n";
								}
							}
							$ret .= "		 	</tr>\n";
							$ret .= "			<tr class='" . $trClass . "'>\n";
							$ret .= "				<th></th>\n";
							$ret .= "				<th>Add CMT</th>\n";
							$ret .= "				<th>Est_Date</th>\n";
							$ret .= "				<th>Mat'l Status</th>\n";
							$ret .= "				<th>Item #</th>\n";
							$ret .= "				<th>Qty Open (Ord)</th>\n";
							$ret .= "				<th>Qty Ord (SO)</th>\n";
							$ret .= "				<th>Qty Cmpl (SO)</th>\n";
							$ret .= "				<th>Qty Billed</th>\n";
							$ret .= "				<th>(S)tk Item</th>\n";
							$ret .= "				<th>Date_Added</th>\n";
							$ret .= "				<th>Promise_Date</th>\n";
							$ret .= "				<th>SO Num</th>\n";
							$ret .= "				<th>SO Sufx</th>\n";
							$ret .= "				<th>SO Stat</th>\n";
							$ret .= "				<th>Ord Stat</th>\n";
							$ret .= "			</tr>\n";
						}
						$prevIdOrd = $row['id_ord'];
						$DO_TS = strtotime($row['DO']);
						$DO_fmt = date("m-d-y",$DO_TS);
						$DP2_TS = strtotime($row['DP2']);
						$DP2_fmt = date("m-d-y",$DP2_TS);
						$today_midnight_TS = strtotime("midnight today");
						$pdrowFC = "class='normal'";
						$pdFC = "class='normal'";

						if ($row['DR'] == $row['DP']) {
							$pdrowFC = "class='bluebold'";
							$pdFC = "class='bluebold'";
						}

						if ($today_midnight_TS > $DP2_TS) {
							$pdFC = "class='redbold'";
						}

						if ($today_midnight_TS == $DP2_TS) {
							$pdFC = "class='darkgreenbold'";
						}

						$ret1 = "		 	<tr class='" . $trClass . "'>\n";
						$ret1 .= "				<td></td>\n";
						$ret1 .= "				<td class='" . $trClass . "' onclick=\"showAddCommentRow('" . $Ordlin_Rowid . "','CL','" . $trClass . "', '" . $DatePromTS ."')\">+</td>\n";
						$ret1 .= "				<td class='" . $trClass . "' onclick=\"showDetailRow('det_row_" . $i . "')\" TITLE='Show Detail'><font " . $pdrowFC . ">????</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['comp_status'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['id_item'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['qty_open'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['qty_ord'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['qty_cmpl'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['qty_ship_total'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['flag_stk'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $DO_fmt . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdFC . ">" . $DP2_fmt . "</font></td>\n";
						$ret1 .= "		 		<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['id_so_odbc'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['sufx_so'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['stat_rec_SO'] . "</font></td>\n";
						$ret1 .= "				<td class='" . $trClass . "'><font " . $pdrowFC . ">" . $row['code_stat_ord'] . "</font></td>\n";
						$ret1 .= "		 	</tr>\n";
						$ret1 .= "		 	<tr class='" . $trClass . "' id='CL_cmt_row_" . $Ordlin_Rowid . "'>\n";

						if ($FlagComments == 'true') {
							$sql2  = "select ";
							$sql2 .= " convert(varchar(19),DATE_ADD,100) as DATE_ADD2, ";
							$sql2 .= " * ";
							$sql2 .= "FROM ";
							$sql2 .= " nsa.CUSTOM_COMMENTS CC ";
							$sql2 .= "WHERE ";
							$sql2 .= " CC.T_ROWID = '" . $Ordlin_Rowid . "' ";
							$sql2 .= " and ";
							$sql2 .= " CC.TABLE_NAME = 'CP_ORDLIN' ";
							$sql2 .= " and ";
							$sql2 .= " CC.DATE_PROM_TS = '" . $DatePromTS . "' ";
							$sql2 .= "ORDER BY ";
							$sql2 .= " CC.DATE_ADD asc ";
							QueryDatabase($sql2, $results2);
							if (mssql_num_rows($results2) > 0) {
								$ret1 .= "				<td colspan=2></td>\n";
								$ret1 .= "				<td colspan=14>\n";
								$ret1 .= "		 			<table class='" . $trClass . "'>\n";
								$ret1 .= "						<tr class='" . $trClass . "'>\n";
								$ret1 .= "							<th onclick=\"closeDiv('CL_cmt_row_" . $Ordlin_Rowid . "')\">X</th>\n";
								$ret1 .= "							<th>Date Added</th>\n";
								$ret1 .= "							<th>Added By</th>\n";
								$ret1 .= "							<th>Comment</th>\n";
								$ret1 .= "						</tr>\n";
								$p = 0;
								while ($row2 = mssql_fetch_assoc($results2)) {
									$p ++;
									$ret1 .= "						<tr class='" . $trClass . "' id='" . $row2['rowid'] . "'>\n";
									$ret1 .= "							<td></td>\n";
									$ret1 .= "							<td>" . $row2['DATE_ADD2'] . "</td>\n";
									$ret1 .= "							<td>" . $row2['ID_USER_ADD'] . "</td>\n";
									$ret1 .= "							<td>" . $row2['COMMENT'] . "</td>\n";
									if ($p == mssql_num_rows($results2)) {
										$ret1 .= "							<td onclick=\"showAddCommentTextboxRow('" . $Ordlin_Rowid . "', 'CL','" . $trClass . "', , '" . $DatePromTS ."')\">+</td>\n";
									}
									$ret1 .= "						</tr>\n";

								}
								$ret1 .= "						<tr class='" . $trClass . "' id='CL_row_add_comment_" . $Ordlin_Rowid . "'>\n";
								$ret1 .= "						</tr>\n";
								$ret1 .= "		 			</table>\n";
								$ret1 .= "				</td>\n";
							}
						}
						$ret1 .= "		 	</tr>\n";

						$sql2  = "select ";
						$sql2 .= " * ";
						$sql2 .= "FROM ";
						$sql2 .= " nsa.WC_OPEN wo ";
						$sql2 .= "WHERE ";
						$sql2 .= " wo.ID_ORD = '" . $row['id_ord'] . "' ";
						$sql2 .= " and ";
						$sql2 .= " wo.ID_ITEM = '" . $row['id_item'] . "' ";
						$sql2 .= " and ";
						$sql2 .= " wo.SEQ_LINE_ORD = '" . $row['seq_line_ord'] . "' ";
						$sql2 .= " and ";
						$sql2 .= " wo.ID_WC between '1100' and '8000' ";
						$sql2 .= "ORDER BY ";
						$sql2 .= " wo.ID_OPER asc ";
						QueryDatabase($sql2, $results2);
						$ret2 = '';
						if (mssql_num_rows($results2) > 0) {//<
							$ret2 = "		 	<tr class='" . $trClass . "' id='det_row_" . $i . "' >\n";
							$ret2 .= "				<td colspan=2></td>\n";
							$ret2 .= "				<td colspan=14>\n";
							$ret2 .= "		 			<table class='" . $trClass . "'>\n";
							$ret2 .= "						<tr class='" . $trClass . "'>\n";
							$ret2 .= "							<th onclick=\"hideDetailRow('det_row_" . $i . "')\">X</th>\n";
							$ret2 .= "							<th>WC</th>\n";
							$ret2 .= "							<th>OPER</th>\n";
							$ret2 .= "							<th>MINS OPEN</th>\n";
							$ret2 .= "							<th>ACCUM MINS OPEN</th>\n";
							$ret2 .= "							<th>WC CAP</th>\n";
							$ret2 .= "							<th>Days</th>\n";
							$ret2 .= "						</tr>\n";
						}
						$totdays = 0;

						while ($row2 = mssql_fetch_assoc($results2)) {
							$accum = 0;
							$capsum = 0;
							$capzero = false;
							$days = 0;

							if (trim($row2['ID_WC']) == '8000') {
								$wc8000 = true;
							} else {
								$wcOTHER = true;
							}

							$sql3  = "select ";
							$sql3 .= " * ";
							$sql3 .= "FROM ";
							$sql3 .= " nsa.WC_OPEN wo ";
							$sql3 .= "WHERE ";
							$sql3 .= " wo.ID_WC = '" . $row2['ID_WC'] . "'";
							$sql3 .= " and ";
							$sql3 .= " wo.DATE_PROM <= " . $row2['DATE_PROM'];
							//DONT INCLUDE CUTTING 1100 and 1200
							$sql3 .= " and ";//<
							$sql3 .= " wo.ID_WC >= '1300'";
							QueryDatabase($sql3, $results3);
							while ($row3 = mssql_fetch_assoc($results3)) {
								if ($row2['DATE_PROM'] > $row3['DATE_PROM']) {
									$accum += $row3['MINS_OPEN'];
								} elseif ($row2['DATE_PROM'] == $row3['DATE_PROM']) {
									if ($row2['ID_ORD'] > $row3['ID_ORD']) {
										$accum += $row3['MINS_OPEN'];
									} elseif ($row2['ID_ORD'] == $row3['ID_ORD']) {
										if ($row2['ID_SO'] != '') {
											if ($row2['ID_SO'] >= $row3['ID_SO']) {
												$accum += $row3['MINS_OPEN'];
											}
										} else {
											if ($row2['ID_ITEM'] >= $row3['ID_ITEM']) {
												$accum += $row3['MINS_OPEN'];
											}
										}
									}
								} else {
									error_log($row3['rowid'] . " OOPS CASE 1");
								}
							}

							$sql3  = "select ";
							$sql3 .= " sum(CAP) as CAPSUM ";
							$sql3 .= "FROM ";
							$sql3 .= " nsa.WC_CAP ";
							$sql3 .= "WHERE ";
							$sql3 .= " ID_WC = '" . $row2['ID_WC'] . "'";
							QueryDatabase($sql3, $results3);
							while ($row3 = mssql_fetch_assoc($results3)) {
								$capsum = $row3['CAPSUM'];
							}

							if ($capsum > 0) {
								$days = round($accum / ($capsum * $CapPCT_Dec),3);
							} else {
								$capzero = true;
							}
							$totdays += $days;

							////////////////////////////
							//INCLUDE DAYS FOR CUTTING//<
							////////////////////////////
							if (($row2['ID_WC'] == '1100') || ($row2['ID_WC'] == '1200')){
								$sql3  = "select ";
								$sql3 .= " * ";
								$sql3 .= "FROM ";
								$sql3 .= " nsa.WC_OPEN wo ";
								$sql3 .= "WHERE ";
								$sql3 .= " wo.ID_ORD = '" . $row2['ID_ORD'] . "'";
								$sql3 .= " and ";
								$sql3 .= " wo.ID_ITEM = '" . $row['id_item'] . "'";
								$sql3 .= " and ";
								$sql3 .= " wo.ID_WC in ('1100','1200')";
								if ($row2['ID_SO'] != '') {
									$sql3 .= " and ";
									$sql3 .= " wo.ID_SO = '" . $row2['ID_SO'] . "'";
								}
								QueryDatabase($sql3, $results3);
								while ($row3 = mssql_fetch_assoc($results3)) {
									if ($row3['STAT_REC_OPER_1'] == 'A') {
										$days += 4;
										$totdays += 4;
									}
									if ($row3['STAT_REC_OPER_1'] == 'R') {
										$days += 2;
										$totdays += 2;
									}
								}
							}
							$ret2 .= "					 	<tr class='" . $trClass . "'>\n";
							$ret2 .= "							<td></td>\n";
							$ret2 .= "							<td>" . $row2['ID_WC'] . "</td>\n";
							$ret2 .= "							<td>" . $row2['ID_OPER'] . "</td>\n";
							$ret2 .= "							<td>" . $row2['MINS_OPEN'] . "</td>\n";
							$ret2 .= "							<td>" . $accum . "</td>\n";
							$ret2 .= "							<td>" . $capsum . "</td>\n";
							$ret2 .= "							<td>" . $days . "</td>\n";
							$ret2 .= "					 	</tr>\n";
						}

						if (mssql_num_rows($results2) > 0) {//<
							$ret2 .= "		 			</table>\n";
							$ret2 .= "				</td>\n";
							$ret2 .= "		 	</tr>\n";
						}


						if ($dbName == 'TCM101') {
							$sql3  = "select ";
							$sql3 .= "	FLAG_SOURCE as FLAG_MFG_PUR";
							$sql3 .= " FROM ";
							$sql3 .= "	nsa.ITMMAS_LOC ";
							$sql3 .= " WHERE ";
							$sql3 .= "	ID_ITEM = '" . $row['id_item'] . "'";
							$sql3 .= " AND ID_LOC = '10' ";
						} else {
							$sql3  = "select ";
							$sql3 .= "	FLAG_MFG_PUR ";
							$sql3 .= " FROM ";
							$sql3 .= "	nsa.ITMMAS_BASE ";
							$sql3 .= " WHERE ";
							$sql3 .= "	ID_ITEM = '" . $row['id_item'] . "'";
						}


						QueryDatabase($sql3, $results3);
						$row3 = mssql_fetch_assoc($results3);

						if (trim($row3['FLAG_MFG_PUR']) == 'P') {
							$ret1 = str_replace('????','Purch',$ret1);
						}

						if ($totdays == 0 && $wc8000 && !$wcOTHER && trim($row3['FLAG_MFG_PUR']) != 'P') {
							$ret1 = str_replace('????','DONE',$ret1);
						}

						if ($totdays > 0) {
							//////CALCULATE THE DATE (TODAY + TOTDAYS)
							$EstDate_TS = GetEstDateTS($totdays);
							$EstDate = date("m-d-y",$EstDate_TS);


							if ($EstDate_TS > $DP2_TS) {
								$EstDate = "<font class='redbold'>".$EstDate."</font>";
							}

							$ret1 = str_replace('????',$EstDate,$ret1);
						}

						if ($capzero) {
							$ret1 = str_replace('????','???? WC*',$ret1);
						}

						if ($FlagDetail == 'true') {
							$ret .= $ret1 . $ret2;
						} else {
							$ret .= $ret1;
						}
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
				$ret = iconv("UTF-8", "ISO-8859-1//IGNORE", $ret);
				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
