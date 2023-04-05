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
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			$ret = '';

			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["zeroHour"]))  {

				$ret .= "<div id='div_results' name='div_results'>\n";
				$ret .= "<table class='sample'>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td>Date</td>\n";
				$ret .= "		<td>Team</td>\n";
				$ret .= "		<td>Oper</td>\n";
				$ret .= "		<td>Mins</td>\n";
				$ret .= "	</tr>\n";

				$pDateFrom = $_POST["df"];
				$pDateTo = $_POST["dt"];

				$DateFrom = $pDateFrom;
				$DateTo = $pDateTo;
				while ($DateFrom < $pDateTo) {
					$DateTo = date("Y-m-d", strtotime($DateFrom." +1 Day"));

					$ZeroHour = $_POST["zeroHour"];
					$fmtZeroHour = substr($ZeroHour,0,2).":".substr($ZeroHour,2,2).":".(string)substr($ZeroHour,4,2);
					$DateFromTS = strtotime($DateFrom." ".$ZeroHour);
					$DateToTS = strtotime($DateTo." ".$ZeroHour);
					$seconds_diff = $DateToTS - $DateFromTS;
					$daysDiff = ($seconds_diff/3600)/24;
					
					//NOT EVERY DAY HAS 86400 SECONDS.. THIS ACCOUNTS FOR DAYLIGHT SAVINGS
					if (($seconds_diff == 86400) || ($seconds_diff == 90000) || ($seconds_diff == 82800)){
						$daysDiff = 1;
					}

					$sqlA  = "SELECT ";
					$sqlA .= " BADGE_APP ";
					$sqlA .= " FROM nsa.DCAPPROVALS a ";
					$sqlA .= " WHERE DATE_APP = '".$DateFrom."' ";
					$sqlA .= " and CODE_APP = 200 ";
					$sqlA .= " order by BADGE_APP asc ";
					QueryDatabase($sqlA, $resultsA);
					while ($rowA = mssql_fetch_assoc($resultsA)) {

						$Team = $rowA['BADGE_APP'];
						//error_log("DateFrom: ".$DateFrom." Team: ". $Team);
						$sql  = "SELECT ";
						$sql .= " NAME_EMP";
						$sql .= " FROM nsa.DCEMMS_EMP ";
						$sql .= " WHERE ltrim(ID_BADGE) = '" . $Team ."'";
						$sql .= " and TYPE_BADGE = 'X'";
						$sql .= " and CODE_ACTV = '0'";
						QueryDatabase($sql, $results);
						$row = mssql_fetch_assoc($results);

						createTempTable();
						$a_team_members = populateTempTable($DateFrom, $DateTo, $ZeroHour, $Team);

						
						/////////////////////
						//BUILD VARIABLES FOR PROD OPERS
						/////////////////////

						$sql  = "SELECT ";
						$sql .= " ID_OPER ";
						$sql .= " FROM nsa.SHPORD_OPER ";
						$sql .= " WHERE ID_SO = 'PROD' ";
						$sql .= " ORDER BY ID_OPER asc ";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							${'Prod_'.$row['ID_OPER']} = 0;
						}


						/////////////////////
						//QUERY TEMP TABLE FOR SHOP ORDERS - INDIRECT
						/////////////////////
						$sql  = "SELECT ";
						$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
						$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql .= " h.ID_ITEM_PAR, ";
						$sql .= " o.HR_MACH_SR as HR_MACH_SF, ";
						$sql .= " o.DESCR_OPER_1, ";
						$sql .= " tx.* ";
						$sql .= " FROM #temp_trx tx, ";
						$sql .= " nsa.SHPORD_HDR h, ";
						$sql .= " nsa.SHPORD_OPER so,";
						$sql .= " nsa.ROUTMS_OPER o, ";
						$sql .= " nsa.ITMMAS_BASE b, ";
						$sql .= " nsa.ITMMAS_LOC l ";
						$sql .= " WHERE tx.CODE_TRX in (102,103) ";
						$sql .= " and ltrim(tx.ID_SO) not like 'S%' ";
						$sql .= " and tx.ID_SO = h.ID_SO ";
						$sql .= " and h.id_item_par=o.id_item ";
						$sql .= " and so.id_oper=o.id_oper ";
						$sql .= " and so.ID_SO = h.ID_SO ";
						$sql .= " and tx.SUFX_SO = h.SUFX_SO ";
						$sql .= " and tx.SUFX_SO = so.SUFX_SO ";
						$sql .= " and so.ID_OPER = tx.ID_OPER ";
						$sql .= " and so.FLAG_DIR_INDIR = 'I' ";
						$sql .= " and b.ID_ITEM = h.ID_ITEM_PAR ";
						$sql .= " and l.ID_ITEM = b.ID_ITEM ";
						$sql .= " and l.ID_LOC = '10' ";
						$sql .= " and l.ID_RTE = o.ID_RTE ";
						$sql .= " and ((ltrim(tx.ID_SO) = 'PROD' AND tx.ID_OPER <> '1000') OR ltrim(tx.ID_SO) <> 'PROD')";
						$sql .= " ORDER BY ";
						$sql .= " DATE_TRX asc, ";
						$sql .= " time_trx asc, ";
						$sql .= " ID_ITEM_PAR asc, ";
						$sql .= " ID_SO asc, ";
						$sql .= " ID_OPER asc ";
						QueryDatabase($sql, $results);

						$tot_qty = 0;
						$min_earned = 0;
						$tot_so_indir_sec = 0;
						while ($row = mssql_fetch_assoc($results)) {
							$prev = '';
							$diff_sec = 0;
							$qty_ord = '';
							$qty_rem = '';
							$currts = $row['DATETIME_TRX_TS'];

							if ($row['CODE_TRX'] == '103')  {
								$sql2  = "SELECT top 1 ";
								$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
								$sql2 .= " tx.* ";
								$sql2 .= " FROM #temp_trx tx ";
								$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
								$sql2 .= " and ID_SO = '" . $row['ID_SO'] ."' ";
								$sql2 .= " and CODE_ACTV = '" . $row['CODE_ACTV'] ."' ";
								$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
								$sql2 .= " ORDER BY ";
								$sql2 .= " DATE_TRX desc, ";
								$sql2 .= " time_trx desc ";
								QueryDatabase($sql2, $results2);

								while ($row2 = mssql_fetch_assoc($results2)) {
									$prevts = $row2['DATETIME_TRX_TS'];
									$diff_sec = $currts - $prevts;
								}

								$sql2  = "SELECT ";
								$sql2 .= " o.qty_ord";
								$sql2 .= " FROM nsa.SHPORD_OPER o ";
								$sql2 .= " WHERE ltrim(o.ID_SO) = '" . trim($row['ID_SO']) ."' ";
								$sql2 .= " and o.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
								$sql2 .= " and o.ID_OPER = '" . $row['ID_OPER'] ."' ";
								QueryDatabase($sql2, $results2);

								while ($row2 = mssql_fetch_assoc($results2)) {
									$qty_ord = $row2['qty_ord'];
								}


								$sql2  = "SELECT ";
								$sql2 .= " sum(nz.qty_good) as sum_qty_good";
								$sql2 .= " FROM nsa.DCUTRX_NONZERO_PERM nz ";
								$sql2 .= " WHERE ltrim(nz.ID_SO) = '" . trim($row['ID_SO']) ."' ";
								$sql2 .= " and nz.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
								$sql2 .= " and nz.ID_OPER = '" . $row['ID_OPER'] ."' ";
								$sql2 .= " and nz.FLAG_DEL = '' ";
								QueryDatabase($sql2, $results2);

								while ($row2 = mssql_fetch_assoc($results2)) {
									$sum_qty_good = $row2['sum_qty_good'];
									$qty_rem = $qty_ord - $sum_qty_good;
								}


								$tot_qty += $row['QTY_GOOD'];

								//////////
								//HARDCODED FOR FIBERGLASS CLEANUP
								//////////
								if (($row['ID_SO'] == 'PROD') && ($row['ID_OPER'] == '6000')) {
									$diff_sec = 300 * $row['QTY_GOOD'];
								}
								//////////
								//Machine Down - Multiply by number of members "Claimed"
								//////////
								if (($row['ID_SO'] == 'PROD') && (($row['ID_OPER'] == '1500') || ($row['ID_OPER'] == '2000') || ($row['ID_OPER'] == '2500') || ($row['ID_OPER'] == '3500') || ($row['ID_OPER'] == '4000'))) {
									$qtyGood = $row['QTY_GOOD'];
									if ($qtyGood == 0) {
										$qtyGood = 1;
									}
									$diff_sec = $diff_sec * $qtyGood;
								}
								$tot_so_indir_sec += $diff_sec;

							} else {
								$row['HR_MACH_SF'] = 0;
								$min_earned = '';
							}

							if ($row['QTY_GOOD'] == '0') {
								$row['QTY_GOOD'] = '';
							}

							$MIN_MACH_SF = $row['HR_MACH_SF'] * 60;
							if ($MIN_MACH_SF == 0) {
								$MIN_MACH_SF = '';
							}

							//ADD TO SUBTOTAL FOR OPER
							${'Prod_'.$row['ID_OPER']} += round($diff_sec / 60,3);
						}




						/////////////////////
						//QUERY TEMP TABLE FOR SHOP ORDERS - DIRECT
						/////////////////////
						$sql  = "SELECT ";
						$sql .= " h.ID_ITEM_PAR, ";
						$sql .= " so.HR_MACH_SF as soHR_MACH_SF, ";
						$sql .= " so.DESCR_OPER_1 as soDESCR_OPER_1, ";
						$sql .= " o.HR_MACH_SR as oHR_MACH_SF, ";
						$sql .= " o.DESCR_OPER_1 as oDESCR_OPER_1, ";
						$sql .= " case when o.HR_MACH_SR is null THEN so.HR_MACH_SF else o.HR_MACH_SR end as HR_MACH_SF, ";
						$sql .= " case when o.DESCR_OPER_1 is null THEN so.DESCR_OPER_1 else o.DESCR_OPER_1 end as DESCR_OPER_1, ";
						$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql .= " tx.*  ";
						$sql .= " FROM #temp_trx tx  ";
						$sql .= " left join nsa.SHPORD_HDR h ";
						$sql .= "  on tx.ID_SO = h.ID_SO ";
						$sql .= "  and tx.SUFX_SO = h.SUFX_SO ";
						$sql .= " left join nsa.SHPORD_OPER so ";
						$sql .= "  on h.ID_SO = so.ID_SO ";
						$sql .= "  and tx.SUFX_SO = so.SUFX_SO ";
						$sql .= "  and tx.ID_OPER = so.ID_OPER ";
						$sql .= "  and so.FLAG_DIR_INDIR = 'D' ";
						$sql .= " left join nsa.ITMMAS_BASE b ";
						$sql .= "  on b.ID_ITEM = h.ID_ITEM_PAR ";
						$sql .= " left join nsa.ITMMAS_LOC l ";
						$sql .= "  on l.ID_ITEM = b.ID_ITEM    ";
						$sql .= "  and l.ID_LOC = '10'    ";
						$sql .= " left join nsa.ROUTMS_OPER o  ";
						$sql .= "  on h.id_item_par=o.id_item    ";
						$sql .= "  and so.id_oper=o.id_oper ";
						$sql .= "  and l.ID_RTE = o.ID_RTE   ";
						$sql .= " where tx.CODE_TRX in (102,103)  ";
						$sql .= "  and so.FLAG_DIR_INDIR = 'D' ";
						$sql .= "  and ltrim(tx.ID_SO) not like 'S%' ";
						$sql .= " order by DATE_TRX asc,    ";
						$sql .= "  time_trx asc,    ";
						$sql .= "  ID_ITEM_PAR asc,    ";
						$sql .= "  ID_SO asc,   ";
						$sql .= "  ID_OPER asc ";
						QueryDatabase($sql, $results);

						$tot_qty = 0;
						$min_earned = 0;
						$tot_min_earned = 0;
						while ($row = mssql_fetch_assoc($results)) {
							$prev = '';
							$diff_sec = 0;
							$qty_ord = '';
							$qty_rem = '';
							$currts = $row['DATETIME_TRX_TS'];

							if ($row['CODE_TRX'] == '103')  {
								$sql2  = "SELECT top 1 ";
								$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
								$sql2 .= " tx.* ";
								$sql2 .= " FROM #temp_trx tx ";
								$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
								$sql2 .= " and ID_SO = '" . $row['ID_SO'] ."' ";
								$sql2 .= " and TIME_TRX <= '" . $row['DATETIME_TRX_TS'] ."' ";
								$sql2 .= " ORDER BY ";
								$sql2 .= " DATE_TRX desc, ";
								$sql2 .= " time_trx desc ";
								QueryDatabase($sql2, $results2);

								while ($row2 = mssql_fetch_assoc($results2)) {
									$prevts = $row['DATETIME_TRX_TS'];
									$diff_sec = $currts - $prevts;
								}

								$sql2  = "SELECT ";
								$sql2 .= " o.qty_ord ";
								$sql2 .= " FROM nsa.SHPORD_OPER o ";
								$sql2 .= " WHERE ltrim(o.ID_SO) = '" . trim($row['ID_SO']) ."' ";
								$sql2 .= " and o.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
								$sql2 .= " and o.ID_OPER = '" . $row['ID_OPER'] ."' ";
								QueryDatabase($sql2, $results2);

								while ($row2 = mssql_fetch_assoc($results2)) {
									$qty_ord = $row2['qty_ord'];
								}

								$sql2  = "SELECT ";
								$sql2 .= " sum(nz.qty_good) as sum_qty_good ";
								$sql2 .= " FROM nsa.DCUTRX_NONZERO_PERM nz ";
								$sql2 .= " WHERE ltrim(nz.ID_SO) = '" . trim($row['ID_SO']) ."' ";
								$sql2 .= " and nz.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
								$sql2 .= " and nz.ID_OPER = '" . $row['ID_OPER'] ."' ";
								$sql2 .= " and nz.FLAG_DEL = '' ";
								QueryDatabase($sql2, $results2);

								while ($row2 = mssql_fetch_assoc($results2)) {
									$sum_qty_good = $row2['sum_qty_good'];
									$qty_rem = $qty_ord - $sum_qty_good;
								}

								$tot_qty += $row['QTY_GOOD'];
								$min_earned = ($row['QTY_GOOD'] * $row['HR_MACH_SF'] * 60);
								$tot_min_earned += $min_earned;


								//ADD TO SUBTOTAL FOR OPER
								if ($row['ID_SO'] == 'PROD') {
									${'Prod_'.$row['ID_OPER']} += $min_earned;
								}

							} else {
								$row['HR_MACH_SF'] = 0;
								$min_earned = '';
							}

							if ($row['QTY_GOOD'] == '0') {
								$row['QTY_GOOD'] = '';
							}

							$MIN_MACH_SF = $row['HR_MACH_SF'] * 60;
							if ($MIN_MACH_SF == 0) {
								$MIN_MACH_SF = '';
							}

						}

						/////////////////////
						//SAMPLE SHOP ORDERS
						/////////////////////

						$sql  = "SELECT ";
						$sql .= " h.ID_ITEM_PAR, ";
						$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
						$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql .= " tx.* ";
						$sql .= " FROM #temp_trx tx ";
						$sql .= " left join nsa.SHPORD_HDR h ";
						$sql .= "  on tx.ID_SO = h.ID_SO ";
						$sql .= "  and tx.SUFX_SO = h.SUFX_SO ";
						$sql .= " WHERE tx.CODE_TRX in (102,103) ";
						$sql .= "  and (ltrim(tx.ID_SO) like 'S%' OR (ltrim(tx.ID_SO) = 'PROD' AND tx.ID_OPER = '1000'))";
						$sql .= " ORDER BY ";
						$sql .= "  DATE_TRX asc, ";
						$sql .= "  TIME_TRX asc, ";
						$sql .= "  ID_BADGE asc, ";
						$sql .= "  rowid asc ";
						QueryDatabase($sql, $results);

						$tot_sample_sec = 0;

						while ($row = mssql_fetch_assoc($results)) {
							$prev = '';
							$diff_sec = '';
							$currts = $row['DATETIME_TRX_TS'];

							if ($row['CODE_TRX'] == '103')  {
								$sql2  = "SELECT top 1 ";
								$sql2 .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
								$sql2 .= "  tx.* ";
								$sql2 .= " FROM #temp_trx tx ";
								$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
								$sql2 .= "  and ID_SO = '" . $row['ID_SO'] . "' ";
								$sql2 .= "  and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
								$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
								$sql2 .= " ORDER BY ";
								$sql2 .= "  DATE_TRX desc, ";
								$sql2 .= "  time_trx desc ";
								QueryDatabase($sql2, $results2);

								while ($row2 = mssql_fetch_assoc($results2)) {
									$prevts = $row2['DATETIME_TRX_TS'];
									$diff_sec = $currts - $prevts;
									if ($row['CODE_TRX'] == '103') {
										$tot_sample_sec += $diff_sec;
									}
								}
							} 

							//ADD TO SUBTOTAL FOR OPER
							if ($row['ID_SO'] == 'PROD' && $row['ID_OPER'] == '1000') {
								${'Prod_'.$row['ID_OPER']} += round(($diff_sec * 1.25) / 60,3);
							}
						}

						/////////////////////
						//READ THROUGH PROD VARIABLES
						/////////////////////
						$sql  = "SELECT ";
						$sql .= " ID_OPER ";
						$sql .= " FROM nsa.SHPORD_OPER ";
						$sql .= " WHERE ID_SO = 'PROD' ";
						$sql .= " ORDER BY ID_OPER asc ";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							if (${'Prod_'.$row['ID_OPER']} > 0) {
								error_log("DateFrom: ".$DateFrom." Team: ".$Team." Oper: ".$row['ID_OPER']. " " .  ${'Prod_'.$row['ID_OPER']});

								$ret .= "	<tr>\n";
								$ret .= "		<td>".$DateFrom."</td>\n";
								$ret .= "		<td>".$Team."</td>\n";
								$ret .= "		<td>".$row['ID_OPER']."</td>\n";
								$ret .= "		<td>".${'Prod_'.$row['ID_OPER']}."</td>\n";
								$ret .= "	<tr>\n";

								$sqli  = "INSERT into nsa.DC_PROD_OPERS_LOG (";
								$sqli .= " DATE_APP, ";
								$sqli .= " BADGE_APP, ";
								$sqli .= " ID_SO, ";
								$sqli .= " ID_OPER, ";
								$sqli .= " MINS, ";
								$sqli .= " FLAG_RETRO ";
								$sqli .= " ) values ( ";
								$sqli .= "'".$DateFrom."', ";
								$sqli .= "'".$Team."', ";
								$sqli .= " 'PROD', ";
								$sqli .= "'".$row['ID_OPER']."', ";
								$sqli .= ${'Prod_'.$row['ID_OPER']}.", ";
								$sqli .= " 'T' ";
								$sqli .= " ) ";
								QueryDatabase($sqli, $resultsi);
							}
						}
					}
					$DateFrom = $DateTo; // INCRIMENT DATE FROM 
				}
				$ret .= "</table>\n";
				$ret .= "	</br>\n";
				$ret .= "</div>\n";
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
