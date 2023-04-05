<?php

	$DEBUG = 0;
	//$NoRound = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
$DEBUG = 1;
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

			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["zeroHour"]) && isset($_POST["team"]))  {
				$Team = $_POST["team"];
				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
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

				$nowts = time();

				if (isset($_POST["divclose"])) {
					$ret .= "		<p onClick=\"disablePopup(". $Team .")\">CLOSE</p>\n";
				}

				$sql  = "SELECT ";
				$sql .= " NAME_EMP";
				$sql .= " FROM nsa.DCEMMS_EMP ";
				$sql .= " WHERE ltrim(ID_BADGE) = '" . $Team ."'";
				$sql .= " and TYPE_BADGE = 'X'";
				$sql .= " and CODE_ACTV = '0'";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);

				$ret .= "		<h2>" . $row['NAME_EMP'] ."</h2>\n";
				$ret .= "		<h4>" . $DateFrom . " ".$fmtZeroHour." -- ".$DateTo." ". $fmtZeroHour."</h4>\n";
				createTempTable();
				$a_team_members = populateTempTable($DateFrom, $DateTo, $ZeroHour, $Team);

				/////////////////////
				//QUERY TEMP TABLE FOR INDIVIDUAL INDIRECT HOURS
				/////////////////////
				$tot_indir_sec = 0;
				$tot_team_actual_sec = 0;
				$tot_team_actual_sec_UNADJUSTED = 0;
				foreach ($a_team_members as $member) {

					$ret .= "<div id='div_" . $member . "' name='div_" . $member . "'>\n";
					$ret .= "<table class='sample'>\n";
					$ret .= "	<th class='sample'>Time Stamp</th>\n";
					$ret .= "	<th class='sample'>ID Badge</th>\n";
					$ret .= "	<th class='sample'>Code Trx</th>\n";
					$ret .= "	<th class='sample'>SO</th>\n";
					$ret .= "	<th class='sample'>Duration</th>\n";
					$ret .= "	<td id='x_" . $member . "' name='x_" . $member . "' onclick=\"closeDiv('div_" . $member . "')\" TITLE='Remove Table'>X</td>\n";
					$tot_indiv_indir_sec = 0;
					$tot_indiv_offPrem_sec = 0;
					$tot_indiv_day_sec = 0;
					$name = '';
					$pct = 100;
					

					//$LoopTS = $DateFromTS;

					//while ($LoopTS <= $DateToTS) {
						//$LoopDT = date('Y-m-d', $LoopTS);
						//$onTimeA = array();
						//$offTimeA = array();
						$onTSA = array();
						$offTSA = array();

						//FINDS ON TEAM and OFF TEAM TRX
						$sql  = "SELECT ";
						$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
						$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql .= " e.NAME_EMP, ";
						$sql .= " e.CODE_USER_1_DC, ";
						$sql .= " tx.* ";
						$sql .= " FROM #temp_trx tx, ";
						$sql .= " nsa.DCEMMS_EMP e ";
						$sql .= " WHERE ltrim(tx.ID_BADGE) = '". $member . "'";
						$sql .= " and tx.ID_BADGE = e.ID_BADGE ";
						$sql .= " and e.CODE_ACTV = 0 ";
						$sql .= " and tx.CODE_TRX in (304,305) ";
						//$sql .= " and tx.DATE_TRX = '" . $LoopDT . "' ";
						$sql .= " ORDER BY ";
						$sql .= " DATE_TRX asc, ";
						$sql .= " ID_BADGE asc, ";
						$sql .= " time_trx asc ";
						QueryDatabase($sql, $results);

						while ($row = mssql_fetch_assoc($results)) {
							if ($row['CODE_TRX'] == '304') {
								//$onTimeA[] = $row['TIME_TRX'];
								$onTSA[] = $row['DATETIME_TRX_TS'];
							}
							if ($row['CODE_TRX'] == '305') {
								//$offTimeA[] = $row['TIME_TRX'];
								$offTSA[] = $row['DATETIME_TRX_TS'];
							}
						}

						//sort($onTimeA);
						//sort($offTimeA);
						sort($onTSA);
						sort($offTSA);

						//for ($i=0; $i<sizeof($onTimeA); $i++) {
						for ($i=0; $i<sizeof($onTSA); $i++) {
							if ($DEBUG) {
								error_log("onTSA: " . $onTSA[$i]);
								error_log("offTSA: " . $offTSA[$i]);
							}
							

							//if (!isset($offTimeA[$i])) {
							//	$offTimeA[$i] = '235959';
							//}
							if (!isset($offTSA[$i])) {
								if ($DEBUG) {
									error_log("OFFTSA not Set");
								}
								$offTSA[$i] = $DateToTS;
							}
							$sql  = "SELECT ";
							$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
							$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql .= " e.NAME_EMP, ";
							$sql .= " e.CODE_USER_1_DC, ";
							$sql .= " tx.* ";
							$sql .= " FROM #temp_trx tx, ";
							$sql .= " nsa.DCEMMS_EMP e ";
							$sql .= " WHERE ";
							$sql .= " ltrim(tx.ID_BADGE) = '". $member . "'";
							$sql .= " and tx.ID_BADGE = e.ID_BADGE ";
							$sql .= " and e.CODE_ACTV = 0 ";
							$sql .= " and tx.CODE_TRX in (104,105,304,305,106,107) ";
							//$sql .= " and tx.DATE_TRX = '" . $LoopDT . "' ";
							//$sql .= " and tx.TIME_TRX between '" . $onTimeA[$i] . "' and '" . $offTimeA[$i] . "' ";
							$sql .= " and tx.DATETIME_TRX_TS between '" . $onTSA[$i] . "' and '" . $offTSA[$i] . "' ";
							$sql .= " ORDER BY ";
							$sql .= " DATE_TRX asc, ";
							$sql .= " ID_BADGE asc, ";
							$sql .= " time_trx asc ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$td_class = GetColorCodeTrx($row['CODE_TRX']);
								$trxType = GetStrCodeTrx($row['CODE_TRX']);

								if ((trim($row['CODE_USER_1_DC']) <> '') && (trim($row['CODE_USER_1_DC']) <> '100')) { //<
									$pct = trim($row['CODE_USER_1_DC']);
								}

								$prev = '';
								$diff_sec = '';
								$prevdate = '';
								$name = $row['NAME_EMP'];
								//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
								//$currts = strtotime($curr);
								$currts = $row['DATETIME_TRX_TS'];

								if ($row['CODE_TRX'] == '105') {
									$sql2  = "SELECT top 1 ";
									$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
									$sql2 .= " tx.* ";
									$sql2 .= " FROM #temp_trx tx ";
									$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
									$sql2 .= " and ID_BADGE = '" . $row['ID_BADGE'] ."' ";
									//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
									//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
									$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
									$sql2 .= " ORDER BY ";
									$sql2 .= " DATE_TRX desc, ";
									$sql2 .= " time_trx desc ";
									QueryDatabase($sql2, $results2);

									while ($row2 = mssql_fetch_assoc($results2)) {
										//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
										//$prevts = strtotime($prev);
										$prevts = $row2['DATETIME_TRX_TS'];
										$diff_sec = $currts - $prevts;
										if ($nowts >= $currts) {
											$tot_indiv_indir_sec += $diff_sec;
										}
									}
								}

								if ($row['CODE_TRX'] == '107') {
									$sql2  = "SELECT top 1 ";
									$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
									$sql2 .= " tx.* ";
									$sql2 .= " FROM #temp_trx tx ";
									$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
									$sql2 .= " and ID_BADGE = '" . $row['ID_BADGE'] ."' ";
									//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
									//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
									$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
									$sql2 .= " ORDER BY ";
									$sql2 .= " DATE_TRX desc, ";
									$sql2 .= " time_trx desc ";
									QueryDatabase($sql2, $results2);

									while ($row2 = mssql_fetch_assoc($results2)) {
										//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
										//$prevts = strtotime($prev);
										$prevts = $row2['DATETIME_TRX_TS'];
										$diff_sec = $currts - $prevts;
										if ($nowts >= $currts) {
											$tot_indiv_offPrem_sec += $diff_sec;
										}
									}
								}								

								if ($row['CODE_TRX'] == '305') {
									$sql2  = "SELECT top 1 ";
									$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
									$sql2 .= " tx.* ";
									$sql2 .= " FROM #temp_trx tx ";
									$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
									$sql2 .= " and ID_BADGE = '" . $row['ID_BADGE'] ."' ";
									//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
									//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
									$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
									$sql2 .= " ORDER BY ";
									$sql2 .= " DATE_TRX desc, ";
									$sql2 .= " time_trx desc ";
									QueryDatabase($sql2, $results2);

									while ($row2 = mssql_fetch_assoc($results2)) {
										//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
										//$prevts = strtotime($prev);
										$prevts = $row2['DATETIME_TRX_TS'];
										if ($NoRound) {
											$diff_sec = $currts - $prevts;
										} else {
											$diff_sec = rounddown15($currts) - roundup15($prevts);
										}
										
										if ($nowts >= $currts) {
											$tot_indiv_day_sec += $diff_sec;
										}
									}
//								}

								} else {

									/////////////////////
									// If there is no matching "Off Team" record for the Badge ID, calculate the difference so far in the day.
									/////////////////////
									$sql3  = "SELECT ";
									$sql3 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
									$sql3 .= " tx.* ";
									$sql3 .= " FROM #temp_trx tx ";
									$sql3 .= " WHERE tx.CODE_TRX in (301,305) ";
									$sql3 .= " and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
									//$sql3 .= " and DATE_TRX = '" . $row['DATE_TRX'] . "' ";
									$sql3 .= " ORDER BY ";
									$sql3 .= " DATE_TRX desc ";
									QueryDatabase($sql3, $results3);
									if (mssql_num_rows($results3) == 0) {
										if ($NoRound) {
											$currts_rnd = $currts;
											$nowts_rnd = $nowts;
										} else {
											$currts_rnd = roundup15($currts);
											$nowts_rnd = rounddown15($nowts);
										}
										$diff_sec2 =  $nowts - $currts;
										if ($row['CODE_TRX'] == '304') {
											$tot_indiv_day_sec += $diff_sec2;
										}
									}
								}

								if ($nowts >= $currts) {
									$ret .= "	<tr class='" . $td_class . "'>\n";
									$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
									$ret .= "		<td class='" . $td_class . "'>" . $row['ID_BADGE'] . "</td>\n";
									$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
									$ret .= "		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n";
									if ($diff_sec <> '') {
										$ret .= "		<td class='" . $td_class . "' colspan='2'>" . round($diff_sec / 60,3) . "</td>\n";
									} else {
										$ret .= "		<td class='" . $td_class . "' colspan='2'></td>\n";
									}
									$ret .= "	</tr>\n";
								}
							}
						//}

						//$LoopTS = strtotime("+1 days" , $LoopTS);
					}

					$tot_indiv_actual_sec = $tot_indiv_day_sec - $tot_indiv_indir_sec;
					$tot_indiv_actual_sec_UNADJUSTED = $tot_indiv_day_sec - $tot_indiv_indir_sec;

					/////////////////////
					// If individual is not to be counted at 100% of their time then only include a percentage of their Actual Minutes
					/////////////////////
					$txt = 'Individual Actual Minutes';
					$cls = 'sample';
					if ($pct <> '100') {
						$cls = 'stop';
						$txt = "Individual Actual Minutes (adjusted to " . $pct . "% of " . ($tot_indiv_actual_sec / 60) . ")";
						$tot_indiv_actual_sec = $tot_indiv_actual_sec * (intval($pct) / 100);
					}

					$ret .= "	<tr class='sample'>\n";
					$ret .= " 	<td class='sample' colspan = 6><b>" . $name . "</b></td>";
					$ret .= "	</tr>\n";
					$ret .= "	<tr class='sample'>\n";
					$ret .= "		<td class='sample' colspan = 4><b>Individual Shift Time (minutes)</b></td>\n";
					$ret .= "		<td class='sample' colspan = 2><b>" . round($tot_indiv_day_sec / 60,3) . "</b></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr class='sample'>\n";
					$ret .= "		<td class='sample' colspan = 4><b>Total Individual Indirect Time (minutes)</b></td>\n";
					$ret .= "		<td class='sample' colspan = 2><b>" . round($tot_indiv_indir_sec / 60,3) . "</b></td>\n";
					$ret .= "	</tr>\n";
					if ($tot_indiv_offPrem_sec > 0){
						$ret .= "	<tr class='sample'>\n";
						$ret .= "		<td class='sample' colspan = 4><b>Total Individual Off Prem Time (minutes)</b></td>\n";
						$ret .= "		<td class='sample' colspan = 2><b>" . round($tot_indiv_offPrem_sec / 60,3) . "</b></td>\n";
						$ret .= "	</tr>\n";						
					}
					$ret .= "	<tr class='sample'>\n";
					$ret .= "		<td class='" . $cls . "' colspan = 4><b>" . $txt ."</b></td>\n";
					$ret .= "		<td class='" . $cls . "' colspan = 2><b>" . round($tot_indiv_actual_sec / 60,3) . "</b></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "</table>\n";
					$ret .= "	</br>\n";
					$ret .= "</div>\n";

					$tot_indir_sec += $tot_indiv_indir_sec;
					$tot_team_actual_sec += $tot_indiv_actual_sec;
					$tot_team_actual_sec_UNADJUSTED += $tot_indiv_actual_sec_UNADJUSTED;
				}

				/////////////////////
				//QUERY TEMP TABLE FOR TEAM CHANGES
				/////////////////////<
				$sql  = "SELECT ";
				$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
				$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql .= " e.CODE_ACTV, ";
				$sql .= " e.NAME_EMP, ";
				$sql .= " e.CODE_USER_1_DC, ";
				$sql .= " tx.* ";
				$sql .= " FROM #temp_trx tx, ";
				$sql .= " nsa.DCEMMS_EMP e ";
				$sql .= " WHERE e.ID_BADGE = tx.ID_BADGE ";
				$sql .= " and e.CODE_ACTV = 0 ";
				$sql .= " and tx.CODE_TRX in (300,301,304,305) ";
				$sql .= " ORDER BY ";
				$sql .= " DATE_TRX asc, ";
				$sql .= " ID_BADGE asc, ";
				$sql .= " rowid asc ";
				QueryDatabase($sql, $results);

				$ret .= "<div id='div_team' name='div_team'>\n";
				$ret .= "<table class='sample'>\n";
				$ret .= "	<th class='sample'>Time Stamp</th>\n";
				$ret .= "	<th class='sample'>Name</th>\n";
				$ret .= "	<th class='sample'>ID Badge</th>\n";
				$ret .= "	<th class='sample'>Team Badge</th>\n";
				$ret .= "	<th class='sample'>Code Trx</th>\n";
				$ret .= "	<th class='sample'>Duration</th>\n";
				$ret .= "	<td id='x_team' name='x_team' onclick=\"closeDiv('div_team')\" TITLE='Remove Table'>X</td>\n";
				$tot_day_sec = 0;

				while ($row = mssql_fetch_assoc($results)) {
					$prev = '';
					$diff_sec = '';
					$td_class = GetColorCodeTrx($row['CODE_TRX']);
					$trxType = GetStrCodeTrx($row['CODE_TRX']);
					//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$currts = strtotime($curr);
					$currts = $row['DATETIME_TRX_TS'];

					if ($row['CODE_TRX'] == '301' || $row['CODE_TRX'] == '305') {
						$sql2  = "SELECT top 1 ";
						$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql2 .= " tx.* ";
						$sql2 .= " FROM #temp_trx tx ";
						$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
						$sql2 .= " and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
						//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] . "' ";
						//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] . "' ";
						$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] . "' ";
						$sql2 .= " ORDER BY ";
						$sql2 .= " DATE_TRX desc, ";
						$sql2 .= " time_trx desc ";
						QueryDatabase($sql2, $results2);

						while ($row2 = mssql_fetch_assoc($results2)) {
							//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
							//$prevts = strtotime($prev);
							$prevts = $row2['DATETIME_TRX_TS'];
							if ($NoRound) {
								$currts_rnd = $currts;
								$prevts_rnd = $prevts;
							} else {
								$currts_rnd = rounddown15($currts);
								$prevts_rnd = roundup15($prevts);
							}

							$diff_sec = $currts_rnd - $prevts_rnd;
							if ($row['CODE_TRX'] == '305') {
								$tot_day_sec += $diff_sec;
							}
						}
//					}

					} else {
						/////////////////////
						// If there is no matching "Off Team" record for the Badge ID for that day, calculate the difference so far in the day.
						/////////////////////
						$sql3  =  "SELECT ";
						$sql3 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql3 .= " tx.* ";
						$sql3 .= " FROM #temp_trx tx ";
						$sql3 .= " WHERE tx.CODE_TRX in (301,305) ";
						$sql3 .= " and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
						//$sql3 .= " and DATE_TRX = '" . $row['DATE_TRX'] . "' ";
						$sql3 .= " ORDER BY ";
						$sql3 .= " DATE_TRX desc ";
						QueryDatabase($sql3, $results3);
						if (mssql_num_rows($results3) == 0) {
							if ($NoRound) {
								$currts_rnd = $currts;
								$nowts_rnd = $nowts;
							} else {
								$currts_rnd = roundup15($currts);
								$nowts_rnd = rounddown15($nowts);
							}
							$diff_sec =  $nowts - $currts;
							if ($row['CODE_TRX'] == '304') {
								$tot_day_sec += $diff_sec;
							}
						}
					}

					$ret .= "	<tr class='" . $td_class . "'>\n";
					$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['NAME_EMP'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_BADGE'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_BADGE_TEAM'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
					$ret .= "		<td class='" . $td_class . "' colspan='2'>" . round($diff_sec / 60,3) . "</td>\n";
					$ret .= "	</tr>\n";
				}

				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='7'></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='5'><b>Total Team Day Minutes</b></td>\n";
				$ret .= "		<td colspan='2'><b>" . round($tot_day_sec / 60,3) . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='5'><b>Total Team Actual Minutes</b></td>\n";
				$ret .= "		<td class='sample' colspan='2'><b>" . round($tot_team_actual_sec / 60,3) . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "</table>\n";
				$ret .= "	</br>\n";
				$ret .= "	</div>\n";

				
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
				
				//THE 2 LINES BELOW USE SHOP ORDER OPERATIONS FOR STANDARD TIMES
				$sql .= " so.HR_MACH_SF as HR_MACH_SF, ";
				$sql .= " so.DESCR_OPER_1, ";
				//THE 2 LINES BELOW USE ROUTING OPERATIONS FOR STANDARD TIMES
				//$sql .= " o.HR_MACH_SR as HR_MACH_SF, ";
				//$sql .= " o.DESCR_OPER_1, ";

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


				$ret .= "<div id='div_so_I' name='div_so_I'>\n";
				$ret .= "<table class='sample'>\n";
				//$ret .= "	<tr>";
				$ret .= "		<th class='sample' colspan=10>Indirect Shop Orders</th>\n";
				$ret .= "		<td id='div_so_I' name='div_so_I' onclick=\"closeDiv('div_so_I')\" TITLE='Remove Table'>X</td>\n";
				//$ret .= "	</tr>";
				$ret .= "	<tr>";
				$ret .= "		<th class='sample'>Time Stamp</th>\n";
				$ret .= "		<th class='sample'>Code Trx</th>\n";
				$ret .= "		<th class='sample'>SO</th>\n";
				$ret .= "		<th class='sample'>Sufx</th>\n";
				$ret .= "		<th class='sample'>Oper</th>\n";
				$ret .= "		<th class='sample'>Item #</th>\n";
				$ret .= "		<th class='sample'>Qty Ord</th>\n";
				$ret .= "		<th class='sample'>Qty Rem</th>\n";
				$ret .= "		<th class='sample'>Qty Cmp</th>\n";
				$ret .= "		<th class='sample'>Duration</th>\n";
				$ret .= "	</tr>";
				$tot_qty = 0;
				$min_earned = 0;
				$tot_so_indir_sec = 0;
				while ($row = mssql_fetch_assoc($results)) {
					$prev = '';
					$diff_sec = 0;
					$qty_ord = '';
					$qty_rem = '';
					//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$currts = strtotime($curr);
					$currts = $row['DATETIME_TRX_TS'];

					if ($row['CODE_TRX'] == '103')  {
						$sql2  = "SELECT top 1 ";
						$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql2 .= " tx.* ";
						$sql2 .= " FROM #temp_trx tx ";
						$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
						$sql2 .= " and ID_SO = '" . $row['ID_SO'] ."' ";
						$sql2 .= " and CODE_ACTV = '" . $row['CODE_ACTV'] ."' ";
						//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
						//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
						$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
						$sql2 .= " ORDER BY ";
						$sql2 .= " DATE_TRX desc, ";
						$sql2 .= " time_trx desc ";
						QueryDatabase($sql2, $results2);

						while ($row2 = mssql_fetch_assoc($results2)) {
							//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
							//$prevts = strtotime($prev);
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
						//$min_earned = ($row['QTY_GOOD'] * $row['HR_MACH_SF'] * 60);

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

					$td_class = GetColorCodeTrx($row['CODE_TRX']);
					$trxType = GetStrCodeTrx($row['CODE_TRX']);
					$ret .= "	<tr class='" . $td_class . "'>\n";
					$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['SUFX_SO'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "' TITLE='" . $row['DESCR_OPER_1'] . "'>" . $row['ID_OPER'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_ITEM_PAR'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $qty_ord . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $qty_rem . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['QTY_GOOD'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . round($diff_sec / 60,3) . "</td>\n";
					$ret .= "	</tr>\n";

					//ADD TO SUBTOTAL FOR OPER
					${'Prod_'.$row['ID_OPER']} += round($diff_sec / 60,3);
				}

				$tot_so_indir_min = round($tot_so_indir_sec / 60,3);

				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='10'></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='8'><b>Total</b></td>\n";
				$ret .= "		<td><b>" . $tot_qty . "</b></td>\n";
				$ret .= "		<td><b>" . $tot_so_indir_min . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='9'><b>Adjusted Team Actual Minutes</b></td>\n";
				$ret .= "		<td><b>" . round(($tot_team_actual_sec - $tot_so_indir_sec) / 60,3) . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "</table>\n";
				$ret .= "	</br>\n";
				$ret .= "</div>\n";


				/////////////////////
				//QUERY TEMP TABLE FOR SHOP ORDERS - DIRECT
				/////////////////////
				$sql  = "SELECT ";
				$sql .= " h.ID_ITEM_PAR, ";

				//THE 2 LINES BELOW USE SHOP ORDER OPERATIONS FOR STANDARD TIMES
				$sql .= " so.HR_MACH_SF as HR_MACH_SF, ";
				$sql .= " so.DESCR_OPER_1 as DESCR_OPER_1, ";
				//THE 6 LINES BELOW USE ROUTING OPERATIONS FOR STANDARD TIMES, IF THEY EXIST
				//$sql .= " so.HR_MACH_SF as soHR_MACH_SF, ";
				//$sql .= " so.DESCR_OPER_1 as soDESCR_OPER_1, ";
				//$sql .= " o.HR_MACH_SR as oHR_MACH_SF, ";
				//$sql .= " o.DESCR_OPER_1 as oDESCR_OPER_1, ";
				//$sql .= " case when o.HR_MACH_SR is null THEN so.HR_MACH_SF else o.HR_MACH_SR end as HR_MACH_SF, ";
				//$sql .= " case when o.DESCR_OPER_1 is null THEN so.DESCR_OPER_1 else o.DESCR_OPER_1 end as DESCR_OPER_1, ";

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

				$ret .= "<div id='div_so' name='div_so'>\n";
				$ret .= "<table class='sample'>\n";
				$ret .= "	<tr>";
				$ret .= "		<th class='sample' colspan=11>Direct Shop Orders</th>\n";
				$ret .= "		<td id='x_so' name='x_so' onclick=\"closeDiv('div_so')\" TITLE='Remove Table'>X</td>\n";
				$ret .= "	</tr>";
				$ret .= "	<th class='sample'>Time Stamp</th>\n";
				$ret .= "	<th class='sample'>Code Trx</th>\n";
				$ret .= "	<th class='sample'>SO</th>\n";
				$ret .= "	<th class='sample'>Sufx</th>\n";
				$ret .= "	<th class='sample'>Oper</th>\n";
				$ret .= "	<th class='sample'>Item #</th>\n";
				$ret .= "	<th class='sample'>Qty Ord</th>\n";
				$ret .= "	<th class='sample'>Qty Rem</th>\n";
				$ret .= "	<th class='sample'>Qty Cmp</th>\n";
				$ret .= "	<th class='sample'>Stand Mins</th>\n";
				$ret .= "	<th class='sample'>Earned Mins</th>\n";

				$tot_qty = 0;
				$min_earned = 0;
				$tot_min_earned = 0;
				while ($row = mssql_fetch_assoc($results)) {
					$prev = '';
					$diff_sec = 0;
					$qty_ord = '';
					$qty_rem = '';
					//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$currts = strtotime($curr);
					$currts = $row['DATETIME_TRX_TS'];

					if ($row['CODE_TRX'] == '103')  {
						$sql2  = "SELECT top 1 ";
						$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql2 .= " tx.* ";
						$sql2 .= " FROM #temp_trx tx ";
						$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
						$sql2 .= " and ID_SO = '" . $row['ID_SO'] ."' ";
						//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
						//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
						$sql2 .= " and TIME_TRX <= '" . $row['DATETIME_TRX_TS'] ."' ";
						$sql2 .= " ORDER BY ";
						$sql2 .= " DATE_TRX desc, ";
						$sql2 .= " time_trx desc ";
						QueryDatabase($sql2, $results2);

						while ($row2 = mssql_fetch_assoc($results2)) {
							//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
							//$prevts = strtotime($prev);
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

					$td_class = GetColorCodeTrx($row['CODE_TRX']);
					$trxType = GetStrCodeTrx($row['CODE_TRX']);
					$ret .= "	<tr class='" . $td_class . "'>\n";
					$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['SUFX_SO'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "' TITLE='" . $row['DESCR_OPER_1'] . "'>" . $row['ID_OPER'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_ITEM_PAR'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $qty_ord . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $qty_rem . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['QTY_GOOD'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $MIN_MACH_SF . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $min_earned . "</td>\n";
					$ret .= "	</tr>\n";

					//ADD TO SUBTOTAL FOR OPER
					if ($row['ID_SO'] == 'PROD') {
						${'Prod_'.$row['ID_OPER']} += round($diff_sec / 60,3);
					}
				}

				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='11'></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='8'><b>Total</b></td>\n";
				$ret .= "		<td><b>" . $tot_qty . "</b></td>\n";
				$ret .= "		<td></td>\n";
				$ret .= "		<td><b>" . $tot_min_earned . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "</table>\n";
				$ret .= "	</br>\n";
				$ret .= "</div>\n";

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

				$ret .= "<div id='div_sample' name='div_sample'>\n";
				$ret .= "<table class='sample'>\n";
				$ret .= "	<tr>";
				$ret .= "		<th class='sample' colspan=9>Sample Shop Orders</th>\n";
				$ret .= "		<td id='div_so_SAMPLE' name='div_so_SAMPLE' onclick=\"closeDiv('div_so_SAMPLE')\" TITLE='Remove Table'>X</td>\n";
				$ret .= "	</tr>";				
				$ret .= "	<th class='sample'>Time Stamp</th>\n";
				$ret .= "	<th class='sample'>Code Trx</th>\n";
				$ret .= "	<th class='sample'>SO</th>\n";
				$ret .= "	<th class='sample'>Sufx</th>\n";
				$ret .= "	<th class='sample'>Oper</th>\n";
				$ret .= "	<th class='sample'>Item #</th>\n";
				$ret .= "	<th class='sample'>Duration</th>\n";
				$ret .= "	<th class='sample'>125% Sample Mins</th>\n";
				$tot_sample_sec = 0;

				while ($row = mssql_fetch_assoc($results)) {
					$prev = '';
					$diff_sec = '';
					$td_class = GetColorCodeTrx($row['CODE_TRX']);
					$trxType = GetStrCodeTrx($row['CODE_TRX']);
					//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$currts = strtotime($curr);
					$currts = $row['DATETIME_TRX_TS'];

					if ($row['CODE_TRX'] == '103')  {
						$sql2  = "SELECT top 1 ";
						$sql2 .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sql2 .= "  tx.* ";
						$sql2 .= " FROM #temp_trx tx ";
						$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
						$sql2 .= "  and ID_SO = '" . $row['ID_SO'] . "' ";
						$sql2 .= "  and ID_OPER = '" . $row['ID_OPER'] . "' ";
						$sql2 .= "  and CODE_ACTV = '" . $row['CODE_ACTV'] . "' ";
						$sql2 .= "  and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
						//$sql2 .= "  and DATE_TRX <= '" . $row['DATE_TRX'] . "' ";
						//$sql2 .= "  and TIME_TRX <= '" . $row['TIME_TRX'] . "' ";
						$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
						$sql2 .= " ORDER BY ";
						$sql2 .= "  DATE_TRX desc, ";
						$sql2 .= "  time_trx desc ";
						QueryDatabase($sql2, $results2);

						while ($row2 = mssql_fetch_assoc($results2)) {
							//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
							//$prevts = strtotime($prev);
							$prevts = $row2['DATETIME_TRX_TS'];
							$diff_sec = $currts - $prevts;
							if ($row['CODE_TRX'] == '103') {
								$tot_sample_sec += $diff_sec;
							}
						}
					} 

					$ret .= "	<tr class='" . $td_class . "'>\n";
					$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['SUFX_SO'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_OPER'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "'>" . $row['ID_ITEM_PAR'] . "</td>\n";
					$ret .= "		<td class='" . $td_class . "' >" . round($diff_sec / 60,3) . "</td>\n";
					$ret .= "		<td class='" . $td_class . "' >" . round(($diff_sec * 1.25) / 60,3) . "</td>\n";
					$ret .= "	</tr>\n";

					//ADD TO SUBTOTAL FOR OPER
					if ($row['ID_SO'] == 'PROD' && $row['ID_OPER'] == '1000') {
						${'Prod_'.$row['ID_OPER']} += round(($diff_sec * 1.25) / 60,3);
					}

				}

				$tot_team_sample_min  = round(($tot_sample_sec * 1.25) / 60,3);
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='8'></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='7'><b>Total Sample Shop Order Minutes</b></td>\n";
				$ret .= "		<td><b>" . $tot_team_sample_min . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='7'><b>Total Earned Minutes</b></td>\n";
				$ret .= "		<td><b>" . ($tot_team_sample_min + $tot_min_earned) . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "</table>\n";
				$ret .= "	</br>\n";
				$ret .= "	</div>\n";



				/////////////////////
				//TRACKING 'PROD' MINUTES
				/////////////////////

				$sql  = "SELECT distinct ID_OPER ";
				$sql .= " FROM nsa.SHPORD_OPER ";
				$sql .= " WHERE ID_SO = 'PROD' ";
				$sql .= " ORDER BY ID_OPER asc ";
				QueryDatabase($sql, $results);

				$retProdHidden = "";
				$retProdOperList = "";

				while ($row = mssql_fetch_assoc($results)) {
					//FOR EACH OPER, QUERY TEMP TABLE FOR TRANSACTIONS
					// THEN post a hidden input for each one with the nubmer of minutes
					$retProdOperList .= ",prodMins_".$row['ID_OPER'];
					$tot_prod_oper_sec = 0;

					$sql2  = "SELECT ";
					$sql2 .= "  h.ID_ITEM_PAR, ";
					$sql2 .= "  convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
					$sql2 .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
					$sql2 .= "  tx.* ";
					$sql2 .= " FROM #temp_trx tx ";
					$sql2 .= " LEFT JOIN nsa.SHPORD_HDR h ";
					$sql2 .= "  on tx.ID_SO = h.ID_SO ";
					$sql2 .= "  and tx.SUFX_SO = h.SUFX_SO ";				
					$sql2 .= " WHERE ";
					$sql2 .= "  tx.CODE_TRX in (102,103) ";
					$sql2 .= "  and (ltrim(tx.ID_SO) = 'PROD' AND tx.ID_OPER = '" . $row['ID_OPER'] . "')";
					$sql2 .= " ORDER BY ";
					$sql2 .= "  DATE_TRX asc, ";
					$sql2 .= "  TIME_TRX asc, ";
					$sql2 .= "  ID_BADGE asc, ";
					$sql2 .= "  rowid asc ";
					QueryDatabase($sql2, $results2);



					while ($row2 = mssql_fetch_assoc($results2)) {
						$prev = '';
						$diff_sec = '';
						$curr = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
						$currts = strtotime($curr);

						if ($row2['CODE_TRX'] == '103')  {
							$sql3  = "SELECT top 1 ";
							$sql3 .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql3 .= "  tx.* ";
							$sql3 .= " FROM #temp_trx tx ";
							$sql3 .= " WHERE tx.CODE_TRX in (" . ($row2['CODE_TRX'] - 1) . ") ";
							$sql3 .= "  and ID_SO = '" . $row2['ID_SO'] . "' ";
							$sql3 .= "  and ID_BADGE = '" . $row2['ID_BADGE'] . "' ";
							$sql3 .= "  and DATE_TRX <= '" . $row2['DATE_TRX'] . "' ";
							$sql3 .= "  and TIME_TRX <= '" . $row2['TIME_TRX'] . "' ";
							$sql3 .= " ORDER BY ";
							$sql3 .= "  DATE_TRX desc, ";
							$sql3 .= "  time_trx desc ";
							QueryDatabase($sql3, $results3);

							while ($row3 = mssql_fetch_assoc($results3)) {
								$prev = $row3['DATE_TRX3'] . " " . str_pad($row3['TIME_TRX'],6,"0",STR_PAD_LEFT);
								error_log("curr: " . $curr);
								error_log("prev: " . $prev);
								$prevts = strtotime($prev);
								$diff_sec = $currts - $prevts;
								if ($row2['CODE_TRX'] == '103') {
									$tot_prod_oper_sec += $diff_sec;
								}
							}
						} 
					}

					$tot_prod_oper_min  = round(($tot_prod_oper_sec / 60),3);
					if ($DEBUG) {
						error_log("PROD OPER: " . $row['ID_OPER']);
						error_log("MINUTES: " . $tot_prod_oper_min);
					}
					$retProdHidden .= "		<input type='hidden' id='prodMins_" . $row['ID_OPER'] . "' value='" . $tot_prod_oper_min . "'></input>\n";




				}

				/////////////////////
				//OVERALL EFFICIENCY
				/////////////////////
				
				$tot_min_earned += $tot_team_sample_min;

				$tot_team_actual_min_UNADJUSTED = $tot_team_actual_sec_UNADJUSTED / 60;
				$tot_so_indir_min = $tot_so_indir_sec / 60;

				$tot_team_actual_min = ($tot_team_actual_sec - $tot_so_indir_sec) / 60;
				$ovral_eff = $tot_min_earned / $tot_team_actual_min;
				$raw_eff = $tot_min_earned / $tot_team_actual_min_UNADJUSTED;

				$ret .= "<div id='div_eff' name='div_eff'>\n";
				$ret .= "<table class='sample'>\n";
				$ret .= "	<th class='sample'>Total Earned Mins</th>\n";
				$ret .= "	<th class='sample'>Total Actual Mins</th>\n";
				$ret .= "	<th class='sample'>Raw Eff</th>\n";
				$ret .= "	<th class='sample'>Eff Score*</th>\n";
				$ret .= "	<td id='x_eff' name='x_eff' onclick=\"closeDiv('div_eff')\" TITLE='Remove Table'>X</td>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td colspan='5'>* = Total Earned Mins / Total Actual Mins</td>\n";
				$ret .= "	</tr>\n";
				$ret .= "	<tr>\n";
				$ret .= $retProdHidden;
				$ret .= "		<input type='hidden' id='list_ProdOper' value='".ltrim($retProdOperList,",")."'></input>\n";
				$ret .= "		<input type='hidden' id='earned' value='" . round($tot_min_earned,3) . "'></input>\n";
				$ret .= "		<input type='hidden' id='actual' value='" . round($tot_team_actual_min,3) . "'></input>\n";
				$ret .= "		<input type='hidden' id='unadj' value='" . round($tot_team_actual_min_UNADJUSTED,3) . "'></input>\n";
				$ret .= "		<input type='hidden' id='indir' value='" . round($tot_so_indir_min,3) . "'></input>\n";
				$ret .= "		<input type='hidden' id='sample_mins' value='" . round($tot_team_sample_min,3) . "'></input>\n";
				$ret .= "		<td><b>" . round($tot_min_earned,3) ."</b></td>\n";
				$ret .= "		<td><b>" . round($tot_team_actual_min,3) . "</b></td>\n";
				$ret .= "		<td><b>" . round($raw_eff * 100,2) . "</b></td>\n";
				$ret .= "		<td colspan=2><b>" . round($ovral_eff * 100,2) . "</b></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "</table>\n";
				$ret .= "	</br>\n";
				$ret .= "</div>\n";

				if ($UserRow['PERM_SUPERVISOR']) {

					//if ($DateFrom == $DateTo) {
					if ($daysDiff == 1) {
						//$res100 = checkApprovals('100', $DateFrom, 'ALL');
						//if (mssql_num_rows($res100) > 0) {
							////////////////
							//APPROVED BY HR
							////////////////
							$res200n201 = checkApprovals("'200','201'", $DateFrom, $Team);

							if (mssql_num_rows($res200n201) > 0) {
								////////////////
								//HAS BEEN APPROVED OR SET FOR REVIEW BY SUPERVISOR
								////////////////
								while ($row = mssql_fetch_assoc($res200n201)) {
									if ($row['CODE_APP'] == '200') {
										$transText = "Approved by: ";
									}
									if ($row['CODE_APP'] == '201') {
										$transText = "Set for Review by: ";
									}
									$ret .= "<div id='div_signoff_" . $row['CODE_APP'] . "'>\n";
									$ret .= "<table class='sample'>\n";
									$ret .= "	<th class='sample' colspan=3>Supervisor Approval</th>\n";
									$ret .= "	<td id='x_signoff' onclick=\"closeDiv('div_signoff_" . $row['CODE_APP'] . "')\" TITLE='Remove Table'>X</td>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>" . $transText . "</b></td>\n";
									$ret .= "		<td>" . $row['APP_BY_ID_USER'] . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>On: </b></td>\n";
									$ret .= "		<td>" . $row['DATE_ADD'] . "</td>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>Comments: </b></td>\n";
									$ret .= "		<td>" . $row['COMMENTS'] . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>Earned: </b></td>\n";
									$ret .= "		<td>" . $row['EARNED_MINS'] . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>Actual: </b></td>\n";
									$ret .= "		<td>" . $row['ACTUAL_MINS'] . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>Pct: </b></td>\n";
									$ret .= "		<td>" . round(($row['EARNED_MINS'] / $row['ACTUAL_MINS']) * 100,2) . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td></td>\n";
									$ret .= "		<td><input type='button' id='del_approval_" . $row['rowid'] . "' value='Delete' onClick=\"deleteApproval('" . $row['rowid'] ."', 'div_signoff_" . $row['CODE_APP'] . "')\" /></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									$ret .= "	</br>\n";
									$ret .= "</div>\n";

									$res200 = checkApprovals("200", $DateFrom, $Team);
									if ((mssql_num_rows($res200) == 0) && ($UserRow['PERM_MGMT'])) {
										////////////////
										//TO BE REVIEWED BY MANAGEMENT
										////////////////
										$ret .= "<div id='div_mgmt_review'>\n";
										$ret .= "<table class='sample'>\n";
										$ret .= "	<th class='sample' colspan=3>Manager Review</th>\n";
										$ret .= "	<td id='x_mgmt_review' onclick=\"closeDiv('div_mgmt_review')\" TITLE='Remove Table'>X</td>\n";
										$ret .= "	<tr>\n";
										$ret .= "		<td colspan=2><b>" . $UserRow['NAME_EMP'] . "</b></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr>\n";
										$ret .= " 		<td>\n";
										$ret .= "			<select id='select_app'>\n";
										$ret .= "				<option value='0'>-- Select --</option>\n";
										$ret .= "				<option value='200'>Approve</option>\n";
										$ret .= "			</select>\n";
										$ret .= "		</td>\n";
										$ret .= "		<td>Comments: <input type='text' id='cmts_approve' name='cmts_approve' /></td>\n";
										$ret .= "		<td><input type='submit' id='sub_approve' value='Submit' onClick=\"insertDCApprovalJS('" . $Team ."','" . $DateFrom . "', 'div_mgmt_review')\" /></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
										$ret .= "	</br>\n";
										$ret .= "</div>\n";
									}
								}
							} else {
								////////////////
								//TO BE APPROVED BY SUPERVISOR
								////////////////
								$ret .= "<div id='div_sub_approve'>\n";
								$ret .= "<table class='sample'>\n";
								$ret .= "	<th class='sample' colspan=3>Supervisor Approval</th>\n";
								$ret .= "	<td id='x_sub_approve' onclick=\"closeDiv('div_sub_approve')\" TITLE='Remove Table'>X</td>\n";
								$ret .= "	<tr>\n";
								$ret .= "		<td colspan=2><b>" . $UserRow['NAME_EMP'] . "</b></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr>\n";
								$ret .= " 		<td>\n";
								$ret .= "			<select id='select_app'>\n";
								$ret .= "				<option value='0'>-- Select --</option>\n";
								$ret .= "				<option value='200'>Approve</option>\n";
								$ret .= "				<option value='201'>Review</option>\n";
								$ret .= "			</select>\n";
								$ret .= "		</td>\n";
								$ret .= "		<td>Comments: <input type='text' id='cmts_approve' name='cmts_approve' /></td>\n";
								$ret .= "		<td><input type='submit' id='sub_approve' value='Submit' onClick=\"insertDCApprovalJS('" . $Team ."','" . $DateFrom . "', 'div_sub_approve')\" /></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "</table>\n";
								$ret .= "	</br>\n";
								$ret .= "</div>\n";
							}
						//} else {
						//	$ret .= "		<h3>*The selected day's timecards have not yet been approved by HR</h3>\n";
						//}

					} else {
						$ret .= "		<h3>*To approve a team's work, only ONE day can be selected</h3>\n";
					}
				}
			}

			if (isset($_POST["divclose"])) {
				$ret .= "		<p onClick=\"disablePopup(". $Team .")\">CLOSE</p>\n";
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
