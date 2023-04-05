<?php

	$DEBUG = 0;
	$SHOW_DEL = 0;

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

			if (isset($_POST["df"]) && isset($_POST["dt"]))  {
				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];

				$sql = "select ";
				//$sql .= "	top 10 ";
				$sql .= "	* ";
				$sql .= " from ";
				$sql .= "	nsa.DCAPPROVALS ";
				$sql .= " where ";
				$sql .= "	CODE_APP = '200'";
				$sql .= "   and ";
				$sql .= "   (AVAIL_MINS is NULL or INDIR_MINS is NULL) ";
				$sql .= "	and ";
				$sql .= "	DATE_APP between '" . $DateFrom . "' and '" . $DateTo . "' ";
				QueryDatabase($sql, $resultsw);

				$num_recs = mssql_num_rows($resultsw);
				$this_row = 0;

				$ret .= "<table class='sample'>\n";
				$ret .= "	<tr class='sample'>\n";
				$ret .= "		<th class='sample'>DATE_APP</th>\n";
				$ret .= "		<th class='sample'>BADGE_APP</th>\n";
				$ret .= "		<th class='sample'>ACTUAL_MINS</th>\n";
				$ret .= "		<th class='sample'>EARNED_MINS</th>\n";
				$ret .= "		<th class='sample'>AVAIL_MINS</th>\n";
				$ret .= "		<th class='sample'>INDIR_MINS</th>\n";
				$ret .= "	</tr>\n";

				while ($roww = mssql_fetch_assoc($resultsw)) {
					$this_row++;

					$ret .= "	<tr class='sample'>\n";
					$ret .= "		<td class='sample'>" . $roww['DATE_APP'] . "</td>\n";
					$ret .= "		<td class='sample'>" . $roww['BADGE_APP'] . "</td>\n";
					$ret .= "		<td class='sample'>" . $roww['ACTUAL_MINS'] . "</td>\n";
					$ret .= "		<td class='sample'>" . $roww['EARNED_MINS'] . "</td>\n";
					$ret .= "		<td class='sample'>" . $roww['AVAIL_MINS'] . "</td>\n";
					$ret .= "		<td class='sample'>" . $roww['INDIR_MINS'] . "</td>\n";
					$ret .= "	</tr>\n";

					$DateFrom = $roww['DATE_APP'];
					$DateTo = $roww['DATE_APP'];
					$Team = $roww['BADGE_APP'];
					$AppRow = $roww['rowid'];

					$sql =  "select ";
					$sql .= " 	NAME_EMP";
					$sql .= " from ";
					$sql .= " 	nsa.DCEMMS_EMP ";
					$sql .= " where ";
					$sql .= " 	ltrim(ID_BADGE) = '" . $Team ."'";
					$sql .= " 	and";
					$sql .= " 	TYPE_BADGE = 'X'";
					$sql .= " 	and";
					$sql .= " 	CODE_ACTV = '0'";
					QueryDatabase($sql, $results);
					$row = mssql_fetch_assoc($results);

					createTempTable();
					$a_team_members = populateTempTable($DateFrom, $DateTo, $Team);

					/////////////////////
					//QUERY TEMP TABLE FOR INDIVIDUAL INDIRECT HOURS
					/////////////////////
					$tot_indir_sec = 0;
					$tot_team_actual_sec = 0;
					$tot_team_actual_sec_UNADJUSTED = 0;
					foreach ($a_team_members as $member) {

						//$ret .= "<div id='div_" . $member . "' name='div_" . $member . "'>\n";
						//$ret .= "<table class='sample'>\n";
						//$ret .= "	<th class='sample'>Time Stamp</th>\n";
						//$ret .= "	<th class='sample'>ID Badge</th>\n";
						//$ret .= "	<th class='sample'>Code Trx</th>\n";
						//$ret .= "	<th class='sample'>SO</th>\n";
						//$ret .= "	<th class='sample'>Duration</th>\n";
						//$ret .= "	<td id='x_" . $member . "' name='x_" . $member . "' onclick=\"closeDiv('div_" . $member . "')\" TITLE='Remove Table'>X</td>\n";
						$tot_indiv_indir_sec = 0;
						$tot_indiv_day_sec = 0;
						$name = '';
						$pct = 100;
						$nowts = time();

						$DateFromTS = strtotime($DateFrom);
						$DateToTS = strtotime($DateTo);
						$LoopTS = $DateFromTS;

						while ($LoopTS <= $DateToTS) {
							$LoopDT = date('Y-m-d', $LoopTS);
							//$onTime = '';
							//$offTime = '235959';
							$onTimeA = array();
							$offTimeA = array();

							$sql =  "select ";
							$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
							$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql .= "	e.NAME_EMP, ";
							$sql .= "	e.CODE_USER_1_DC, ";
							$sql .= "	tx.* ";
							$sql .= " from ";
							$sql .= "	#temp_trx tx, ";
							$sql .= "	nsa.DCEMMS_EMP e ";
							$sql .= " where ";
							$sql .= " 	ltrim(tx.ID_BADGE) = '". $member . "'";
							$sql .= "   and ";
							$sql .= " 	tx.ID_BADGE = e.ID_BADGE ";
							$sql .= "   and ";
							$sql .= " 	tx.CODE_TRX in (304,305) ";
							$sql .= "   and ";
							$sql .= " 	tx.DATE_TRX = '" . $LoopDT . "' ";
							$sql .= " order by ";
							$sql .= " 	DATE_TRX asc, ";
							$sql .= " 	ID_BADGE asc, ";
							$sql .= " 	time_trx asc ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								if ($row['CODE_TRX'] == '304') {
									//$onTime = $row['TIME_TRX'];
									$onTimeA[] = $row['TIME_TRX'];
								}
								if ($row['CODE_TRX'] == '305') {
									//$offTime = $row['TIME_TRX'];
									$offTimeA[] = $row['TIME_TRX'];
								}
							}

							sort($onTimeA);
							sort($offTimeA);

							for ($i=0; $i<sizeof($onTimeA); $i++) {
								if (!isset($offTimeA[$i])) {
									$offTimeA[$i] = '235959';
								}
								$sql =  "select ";
								$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
								$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
								$sql .= "	e.NAME_EMP, ";
								$sql .= "	e.CODE_USER_1_DC, ";
								$sql .= "	tx.* ";
								$sql .= " from ";
								$sql .= "	#temp_trx tx, ";
								$sql .= "	nsa.DCEMMS_EMP e ";
								$sql .= " where ";
								$sql .= " 	ltrim(tx.ID_BADGE) = '". $member . "'";
								$sql .= "   and ";
								$sql .= " 	tx.ID_BADGE = e.ID_BADGE ";
								$sql .= "   and ";
								$sql .= " 	tx.CODE_TRX in (104,105,304,305) ";
								$sql .= "   and ";
								$sql .= " 	tx.DATE_TRX = '" . $LoopDT . "' ";
								$sql .= "   and ";
								//$sql .= " 	tx.TIME_TRX between '" . $onTime . "' and '" . $offTime . "' ";
								$sql .= " 	tx.TIME_TRX between '" . $onTimeA[$i] . "' and '" . $offTimeA[$i] . "' ";
								$sql .= " order by ";
								$sql .= " 	DATE_TRX asc, ";
								$sql .= " 	ID_BADGE asc, ";
								$sql .= " 	time_trx asc ";
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
									$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
									$currts = strtotime($curr);

									if ($row['CODE_TRX'] == '105') {
										$sql2 =  "select top 1 ";
										$sql2 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
										$sql2 .= "	tx.* ";
										$sql2 .= " from ";
										$sql2 .= "	#temp_trx tx ";
										$sql2 .= " where ";
										$sql2 .= " 	tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
										$sql2 .= " 	and ";
										$sql2 .= " 	ID_BADGE = '" . $row['ID_BADGE'] ."' ";
										$sql2 .= " 	and ";
										$sql2 .= " 	DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
										$sql2 .= " 	and ";
										$sql2 .= " 	TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
										$sql2 .= " order by ";
										$sql2 .= " 	DATE_TRX desc, ";
										$sql2 .= " 	time_trx desc ";
										QueryDatabase($sql2, $results2);

										while ($row2 = mssql_fetch_assoc($results2)) {
											$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
											$prevts = strtotime($prev);
											$diff_sec = $currts - $prevts;
											if ($nowts >= $currts) {
												$tot_indiv_indir_sec += $diff_sec;
											}
										}
									}
									//<
									if ($row['CODE_TRX'] == '305') {
										$sql2 =  "select top 1 ";
										$sql2 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
										$sql2 .= "	tx.* ";
										$sql2 .= " from ";
										$sql2 .= "	#temp_trx tx ";
										$sql2 .= " where ";
										$sql2 .= " 	tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
										$sql2 .= " 	and ";
										$sql2 .= " 	ID_BADGE = '" . $row['ID_BADGE'] ."' ";
										$sql2 .= " 	and ";
										$sql2 .= " 	DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
										$sql2 .= " 	and ";
										$sql2 .= " 	TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
										$sql2 .= " order by ";
										$sql2 .= " 	DATE_TRX desc, ";
										$sql2 .= " 	time_trx desc ";
										QueryDatabase($sql2, $results2);

										while ($row2 = mssql_fetch_assoc($results2)) {
											$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
											$prevts = strtotime($prev);
											$diff_sec = rounddown15($currts) - roundup15($prevts);
											if ($nowts >= $currts) {
												$tot_indiv_day_sec += $diff_sec;
											}
										}
									} else {
										/////////////////////
										// If there is no matching "Off Team" record for the Badge ID for that day, calculate the difference so far in the day.
										/////////////////////
										$sql3 =  "select ";
										$sql3 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
										$sql3 .= "	tx.* ";
										$sql3 .= " from ";
										$sql3 .= "	#temp_trx tx ";
										$sql3 .= " where ";
										$sql3 .= " 	tx.CODE_TRX in (301,305) ";
										$sql3 .= " 	and ";
										$sql3 .= " 	ID_BADGE = '" . $row['ID_BADGE'] . "' ";
										$sql3 .= " 	and ";
										$sql3 .= " 	DATE_TRX = '" . $row['DATE_TRX'] . "' ";
										$sql3 .= " order by ";
										$sql3 .= " 	DATE_TRX desc ";
										QueryDatabase($sql3, $results3);
										if (mssql_num_rows($results3) == 0) {
											$currts_rnd = roundup15($currts);
											$nowts_rnd = rounddown15($nowts);
											//$diff_sec =  $nowts_rnd - $currts_rnd;
											$diff_sec2 =  $nowts - $currts;
											if ($row['CODE_TRX'] == '304') {
												$tot_indiv_day_sec += $diff_sec2;
											}
										}
									}

									//if ($nowts >= $currts) {
									//	$ret .= "	<tr class='" . $td_class . "'>\n";
									//	$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
									//	$ret .= "		<td class='" . $td_class . "'>" . $row['ID_BADGE'] . "</td>\n";
									//	$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
									//	$ret .= "		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n";
									//	if ($diff_sec <> '') {
									//		$ret .= "		<td class='" . $td_class . "' colspan='2'>" . $diff_sec / 60 . "</td>\n";
									//	} else {
									//		$ret .= "		<td class='" . $td_class . "' colspan='2'></td>\n";
									//	}
									//	$ret .= "	</tr>\n";
									//}
								}
							}

							$LoopTS = strtotime("+1 days" , $LoopTS);
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

						//$ret .= "	<tr class='sample'>\n";
						//$ret .= " 	<td class='sample' colspan = 6><b>" . $name . "</b></td>";
						//$ret .= "	</tr>\n";
						//$ret .= "	<tr class='sample'>\n";
						//$ret .= "		<td class='sample' colspan = 4><b>Individual Shift Time (minutes)</b></td>\n";
						//$ret .= "		<td class='sample' colspan = 2><b>" . round($tot_indiv_day_sec / 60,3) . "</b></td>\n";
						//$ret .= "	</tr>\n";
						//$ret .= "	<tr class='sample'>\n";
						//$ret .= "		<td class='sample' colspan = 4><b>Total Individual Indirect Time (minutes)</b></td>\n";
						//$ret .= "		<td class='sample' colspan = 2><b>" . round($tot_indiv_indir_sec / 60,3) . "</b></td>\n";
						//$ret .= "	</tr>\n";
						//$ret .= "	<tr class='sample'>\n";
						//$ret .= "		<td class='" . $cls . "' colspan = 4><b>" . $txt ."</b></td>\n";
						//$ret .= "		<td class='" . $cls . "' colspan = 2><b>" . round($tot_indiv_actual_sec / 60,3) . "</b></td>\n";
						//$ret .= "	</tr>\n";
						//$ret .= "</table>\n";
						//$ret .= "	</br>\n";
						//$ret .= "</div>\n";

						$tot_indir_sec += $tot_indiv_indir_sec;
						$tot_team_actual_sec += $tot_indiv_actual_sec;
						$tot_team_actual_sec_UNADJUSTED += $tot_indiv_actual_sec_UNADJUSTED;
					}


					/////////////////////
					//QUERY TEMP TABLE FOR TEAM CHANGES
					/////////////////////<
					$sql =  "select ";
					$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
					$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
					$sql .= "	e.CODE_ACTV, ";
					$sql .= "	e.NAME_EMP, ";
					$sql .= "	e.CODE_USER_1_DC, ";
					$sql .= "	tx.* ";
					$sql .= " from ";
					$sql .= "	#temp_trx tx, ";
					$sql .= " 	nsa.DCEMMS_EMP e ";
					$sql .= " where ";
					$sql .= " 	e.ID_BADGE = tx.ID_BADGE ";
					$sql .= " 	and ";
					$sql .= " 	e.CODE_ACTV = 0 ";
					$sql .= " 	and ";
					$sql .= " 	tx.CODE_TRX in (300,301,304,305) ";
					$sql .= " order by ";
					$sql .= " 	DATE_TRX asc, ";
					$sql .= " 	ID_BADGE asc, ";
					$sql .= " 	rowid asc ";
					QueryDatabase($sql, $results);

					//$ret .= "<div id='div_team' name='div_team'>\n";
					//$ret .= "<table class='sample'>\n";
					//$ret .= "	<th class='sample'>Time Stamp</th>\n";
					//$ret .= "	<th class='sample'>Name</th>\n";
					//$ret .= "	<th class='sample'>ID Badge</th>\n";
					//$ret .= "	<th class='sample'>Team Badge</th>\n";
					//$ret .= "	<th class='sample'>Code Trx</th>\n";
					//$ret .= "	<th class='sample'>Duration</th>\n";
					//$ret .= "	<td id='x_team' name='x_team' onclick=\"closeDiv('div_team')\" TITLE='Remove Table'>X</td>\n";
					$tot_day_sec = 0;

					while ($row = mssql_fetch_assoc($results)) {
						$prev = '';
						$diff_sec = '';
						$td_class = GetColorCodeTrx($row['CODE_TRX']);
						$trxType = GetStrCodeTrx($row['CODE_TRX']);
						$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
						$currts = strtotime($curr);

						if ($row['CODE_TRX'] == '301' || $row['CODE_TRX'] == '305')  {
							$sql2 =  "select top 1 ";
							$sql2 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql2 .= "	tx.* ";
							$sql2 .= " from ";
							$sql2 .= "	#temp_trx tx ";
							$sql2 .= " where ";
							$sql2 .= " 	tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
							$sql2 .= " 	and ";
							$sql2 .= " 	ID_BADGE = '" . $row['ID_BADGE'] . "' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	DATE_TRX <= '" . $row['DATE_TRX'] . "' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	TIME_TRX <= '" . $row['TIME_TRX'] . "' ";
							$sql2 .= " order by ";
							$sql2 .= " 	DATE_TRX desc, ";
							$sql2 .= " 	time_trx desc ";
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
								$prevts = strtotime($prev);
								$currts_rnd = rounddown15($currts);
								$prevts_rnd = roundup15($prevts);
								$diff_sec = $currts_rnd - $prevts_rnd;
								if ($row['CODE_TRX'] == '305') {
									$tot_day_sec += $diff_sec;
								}
							}
						} else {
							/////////////////////
							// If there is no matching "Off Team" record for the Badge ID for that day, calculate the difference so far in the day.
							/////////////////////
							$sql3 =  "select ";
							$sql3 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql3 .= "	tx.* ";
							$sql3 .= " from ";
							$sql3 .= "	#temp_trx tx ";
							$sql3 .= " where ";
							$sql3 .= " 	tx.CODE_TRX in (301,305) ";
							$sql3 .= " 	and ";
							$sql3 .= " 	ID_BADGE = '" . $row['ID_BADGE'] . "' ";
							$sql3 .= " 	and ";
							$sql3 .= " 	DATE_TRX = '" . $row['DATE_TRX'] . "' ";
							$sql3 .= " order by ";
							$sql3 .= " 	DATE_TRX desc ";
							QueryDatabase($sql3, $results3);
							if (mssql_num_rows($results3) == 0) {
								$currts_rnd = roundup15($currts);
								$nowts_rnd = rounddown15($nowts);
								//$diff_sec =  $nowts_rnd - $currts_rnd;
								$diff_sec =  $nowts - $currts;
								if ($row['CODE_TRX'] == '304') {
									$tot_day_sec += $diff_sec;
								}
							}
						}

						//$ret .= "	<tr class='" . $td_class . "'>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['NAME_EMP'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['ID_BADGE'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['ID_BADGE_TEAM'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "' >" . round($diff_sec / 60,3) . "</td>\n";
						//$ret .= "	</tr>\n";
					}

					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='6'></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='5'><b>Total Team Day Minutes</b></td>\n";
					//$ret .= "		<td><b>" . round($tot_day_sec / 60,3) . "</b></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='5'><b>Total Team Actual Minutes</b></td>\n";
					//$ret .= "		<td class='sample'><b>" . round($tot_team_actual_sec / 60,3) . "</b></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "</table>\n";
					//$ret .= "	</br>\n";
					//$ret .= "	</div>\n";


					/////////////////////
					//QUERY TEMP TABLE FOR SHOP ORDERS - INDIRECT
					/////////////////////
					$sql =  "select ";
					$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
					$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
					$sql .= "	h.ID_ITEM_PAR, ";
					$sql .= "	o.HR_MACH_SR as HR_MACH_SF, ";
					$sql .= "	o.DESCR_OPER_1, ";
					$sql .= "	tx.* ";
					$sql .= " from ";
					$sql .= "	#temp_trx tx, ";
					$sql .= " 	nsa.SHPORD_HDR h, ";
					$sql .= "	nsa.SHPORD_OPER so,";
					$sql .= "	nsa.ROUTMS_OPER o, ";
					$sql .= "   nsa.ITMMAS_BASE b ";
					$sql .= " where ";
					$sql .= " 	tx.CODE_TRX in (102,103) ";
					$sql .= " 	and ";
					$sql .= " 	tx.ID_SO = h.ID_SO ";
					$sql .= " 	and ";
					$sql .= " 	h.id_item_par=o.id_item ";
					$sql .= " 	and ";
					$sql .= " 	so.id_oper=o.id_oper ";
					$sql .= " 	and ";
					$sql .= " 	so.ID_SO = h.ID_SO ";
					$sql .= " 	and ";
					$sql .= " 	tx.SUFX_SO = h.SUFX_SO ";
					$sql .= " 	and ";
					$sql .= " 	tx.SUFX_SO = so.SUFX_SO ";
					$sql .= " 	and ";
					$sql .= " 	so.ID_OPER = tx.ID_OPER ";

					$sql .= " 	and ";
					$sql .= " 	so.FLAG_DIR_INDIR = 'I' ";
					$sql .= " 	and ";
					$sql .= "   b.ID_ITEM = h.ID_ITEM_PAR ";
					$sql .= "   and ";
					$sql .= "   b.ID_RTE = o.ID_RTE ";

					$sql .= " order by ";
					$sql .= " 	DATE_TRX asc, ";
					$sql .= " 	time_trx asc, ";
					$sql .= " 	ID_ITEM_PAR asc, ";
					$sql .= " 	ID_SO asc, ";
					$sql .= "	ID_OPER asc ";
					QueryDatabase($sql, $results);


					//$ret .= "<div id='div_so_I' name='div_so_I'>\n";
					//$ret .= "<table class='sample'>\n";
					//$ret .= "	<tr>";
					//$ret .= "		<th class='sample' colspan=11>Indirect Shop Orders</th>\n";
					//$ret .= "		<td id='div_so_I' name='div_so_I' onclick=\"closeDiv('div_so_I')\" TITLE='Remove Table'>X</td>\n";
					//$ret .= "	</tr>";
					//$ret .= "	<th class='sample'>Time Stamp</th>\n";
					//$ret .= "	<th class='sample'>Code Trx</th>\n";
					//$ret .= "	<th class='sample'>SO</th>\n";
					//$ret .= "	<th class='sample'>Sufx</th>\n";
					//$ret .= "	<th class='sample'>Oper</th>\n";
					//$ret .= "	<th class='sample'>Item #</th>\n";
					//$ret .= "	<th class='sample'>Qty Ord</th>\n";
					//$ret .= "	<th class='sample'>Qty Rem</th>\n";
					//$ret .= "	<th class='sample'>Qty Cmp</th>\n";
					//$ret .= "	<th class='sample'>Duration</th>\n";
					$tot_qty = 0;
					$min_earned = 0;
					$tot_so_indir_sec = 0;
					while ($row = mssql_fetch_assoc($results)) {
						$prev = '';
						$diff_sec = 0;
						$qty_ord = '';
						$qty_rem = '';
						$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
						$currts = strtotime($curr);

						if ($row['CODE_TRX'] == '103')  {
							$sql2 =  "select top 1 ";
							$sql2 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql2 .= "	tx.* ";
							$sql2 .= " from ";
							$sql2 .= "	#temp_trx tx ";
							$sql2 .= " where ";
							$sql2 .= " 	tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
							$sql2 .= " 	and ";
							$sql2 .= " 	ID_SO = '" . $row['ID_SO'] ."' ";

							$sql2 .= " 	and ";
							$sql2 .= " 	CODE_ACTV = '" . $row['CODE_ACTV'] ."' ";

							$sql2 .= " 	and ";//<
							$sql2 .= " 	DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
							$sql2 .= " order by ";
							$sql2 .= " 	DATE_TRX desc, ";
							$sql2 .= " 	time_trx desc ";
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
								$prevts = strtotime($prev);
								$diff_sec = $currts - $prevts;
							}

							$sql2  = "select ";
							$sql2 .= "	o.qty_ord";
							$sql2 .= " from ";
							$sql2 .= "	nsa.SHPORD_OPER o ";
							$sql2 .= " where ";
							$sql2 .= " 	ltrim(o.ID_SO) = '" . trim($row['ID_SO']) ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	o.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	o.ID_OPER = '" . $row['ID_OPER'] ."' ";
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								$qty_ord = $row2['qty_ord'];
							}


							$sql2  = "select ";
							$sql2 .= "	sum(nz.qty_good) as sum_qty_good";
							$sql2 .= " from ";
							$sql2 .= "	nsa.DCUTRX_NONZERO_PERM nz ";
							$sql2 .= " where ";
							$sql2 .= " 	ltrim(nz.ID_SO) = '" . trim($row['ID_SO']) ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	nz.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	nz.ID_OPER = '" . $row['ID_OPER'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	nz.FLAG_DEL = '' ";
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
						//$ret .= "	<tr class='" . $td_class . "'>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['SUFX_SO'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "' TITLE='" . $row['DESCR_OPER_1'] . "'>" . $row['ID_OPER'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['ID_ITEM_PAR'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $qty_ord . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $qty_rem . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['QTY_GOOD'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . round($diff_sec / 60,3) . "</td>\n";
						//$ret .= "	</tr>\n";
					}

					$tot_so_indir_min = round($tot_so_indir_sec / 60,3);

					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='9'></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='8'><b>Total</b></td>\n";
					//$ret .= "		<td><b>" . $tot_qty . "</b></td>\n";
					//$ret .= "		<td colspan=2><b>" . $tot_so_indir_min . "</b></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='8'><b>Adjusted Team Actual Minutes</b></td>\n";
					//$ret .= "		<td colspan=2><b>" . round(($tot_team_actual_sec - $tot_so_indir_sec) / 60,3) . "</b></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "</table>\n";
					//$ret .= "	</br>\n";
					//$ret .= "</div>\n";









					/////////////////////
					//QUERY TEMP TABLE FOR SHOP ORDERS - DIRECT
					/////////////////////
					$sql =  "select ";
					$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
					$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
					$sql .= "	h.ID_ITEM_PAR, ";
					$sql .= "	o.HR_MACH_SR as HR_MACH_SF, ";
					$sql .= "	o.DESCR_OPER_1, ";
					$sql .= "	tx.* ";
					$sql .= " from ";
					$sql .= "	#temp_trx tx, ";
					$sql .= " 	nsa.SHPORD_HDR h, ";
					$sql .= "	nsa.SHPORD_OPER so,";
					$sql .= "	nsa.ROUTMS_OPER o, ";
					$sql .= "   nsa.ITMMAS_BASE b ";
					$sql .= " where ";
					$sql .= " 	tx.CODE_TRX in (102,103) ";
					$sql .= " 	and ";
					$sql .= " 	tx.ID_SO = h.ID_SO ";
					$sql .= " 	and ";
					$sql .= " 	h.id_item_par=o.id_item ";
					$sql .= " 	and ";
					$sql .= " 	so.id_oper=o.id_oper ";
					$sql .= " 	and ";
					$sql .= " 	so.ID_SO = h.ID_SO ";
					$sql .= " 	and ";
					$sql .= " 	tx.SUFX_SO = h.SUFX_SO ";
					$sql .= " 	and ";
					$sql .= " 	tx.SUFX_SO = so.SUFX_SO ";
					$sql .= " 	and ";
					$sql .= " 	so.ID_OPER = tx.ID_OPER ";

					$sql .= " 	and ";
					$sql .= " 	so.FLAG_DIR_INDIR = 'D' ";
					$sql .= " 	and ";
					$sql .= "   b.ID_ITEM = h.ID_ITEM_PAR ";
					$sql .= "   and ";
					$sql .= "   b.ID_RTE = o.ID_RTE ";

					$sql .= " order by ";
					$sql .= " 	DATE_TRX asc, ";
					$sql .= " 	time_trx asc, ";
					$sql .= " 	ID_ITEM_PAR asc, ";
					$sql .= " 	ID_SO asc, ";
					$sql .= "	ID_OPER asc ";
					QueryDatabase($sql, $results);


					//$ret .= "<div id='div_so' name='div_so'>\n";
					//$ret .= "<table class='sample'>\n";
					//$ret .= "	<tr>";
					//$ret .= "		<th class='sample' colspan=11>Direct Shop Orders</th>\n";
					//$ret .= "		<td id='x_so' name='x_so' onclick=\"closeDiv('div_so')\" TITLE='Remove Table'>X</td>\n";
					//$ret .= "	</tr>";
					//$ret .= "	<th class='sample'>Time Stamp</th>\n";
					//$ret .= "	<th class='sample'>Code Trx</th>\n";
					//$ret .= "	<th class='sample'>SO</th>\n";
					//$ret .= "	<th class='sample'>Sufx</th>\n";
					//$ret .= "	<th class='sample'>Oper</th>\n";
					//$ret .= "	<th class='sample'>Item #</th>\n";
					//$ret .= "	<th class='sample'>Qty Ord</th>\n";
					//$ret .= "	<th class='sample'>Qty Rem</th>\n";
					//$ret .= "	<th class='sample'>Qty Cmp</th>\n";
					//$ret .= "	<th class='sample'>Stand Mins</th>\n";
					//$ret .= "	<th class='sample'>Earned Mins</th>\n";

					$tot_qty = 0;
					$min_earned = 0;
					$tot_min_earned = 0;
					while ($row = mssql_fetch_assoc($results)) {
						$prev = '';
						$diff_sec = 0;
						$qty_ord = '';
						$qty_rem = '';
						$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
						$currts = strtotime($curr);

						if ($row['CODE_TRX'] == '103')  {
							$sql2 =  "select top 1 ";
							$sql2 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql2 .= "	tx.* ";
							$sql2 .= " from ";
							$sql2 .= "	#temp_trx tx ";
							$sql2 .= " where ";
							$sql2 .= " 	tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
							$sql2 .= " 	and ";
							$sql2 .= " 	ID_SO = '" . $row['ID_SO'] ."' ";
							$sql2 .= " 	and ";//<
							$sql2 .= " 	DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
							$sql2 .= " order by ";
							$sql2 .= " 	DATE_TRX desc, ";
							$sql2 .= " 	time_trx desc ";
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
								$prevts = strtotime($prev);
								$diff_sec = $currts - $prevts;
							}

							$sql2  = "select ";
							$sql2 .= "	o.qty_ord";
							$sql2 .= " from ";
							$sql2 .= "	nsa.SHPORD_OPER o ";
							$sql2 .= " where ";
							$sql2 .= " 	ltrim(o.ID_SO) = '" . trim($row['ID_SO']) ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	o.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	o.ID_OPER = '" . $row['ID_OPER'] ."' ";
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								$qty_ord = $row2['qty_ord'];
							}


							$sql2  = "select ";
							$sql2 .= "	sum(nz.qty_good) as sum_qty_good";
							$sql2 .= " from ";
							$sql2 .= "	nsa.DCUTRX_NONZERO_PERM nz ";
							$sql2 .= " where ";
							$sql2 .= " 	ltrim(nz.ID_SO) = '" . trim($row['ID_SO']) ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	nz.SUFX_SO = '" . $row['SUFX_SO'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	nz.ID_OPER = '" . $row['ID_OPER'] ."' ";
							$sql2 .= " 	and ";
							$sql2 .= " 	nz.FLAG_DEL = '' ";
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
						//$ret .= "	<tr class='" . $td_class . "'>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['SUFX_SO'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "' TITLE='" . $row['DESCR_OPER_1'] . "'>" . $row['ID_OPER'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['ID_ITEM_PAR'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $qty_ord . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $qty_rem . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $row['QTY_GOOD'] . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "'>" . $MIN_MACH_SF . "</td>\n";
						//$ret .= "		<td class='" . $td_class . "' colspan=2>" . $min_earned . "</td>\n";
						//$ret .= "	</tr>\n";
					}

					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='11'></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='8'><b>Total</b></td>\n";
					//$ret .= "		<td><b>" . $tot_qty . "</b></td>\n";
					//$ret .= "		<td></td>\n";
					//$ret .= "		<td colspan=2><b>" . $tot_min_earned . "</b></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "</table>\n";
					//$ret .= "	</br>\n";
					//$ret .= "</div>\n";


					/////////////////////
					//OVERALL EFFICIENCY
					/////////////////////


					$tot_team_actual_min_UNADJUSTED = $tot_team_actual_sec_UNADJUSTED / 60;
					$tot_so_indir_min = $tot_so_indir_sec / 60;

					$tot_team_actual_min = ($tot_team_actual_sec - $tot_so_indir_sec) / 60;
					$ovral_eff = $tot_min_earned / $tot_team_actual_min;

					//$ret .= "<div id='div_eff' name='div_eff'>\n";
					//$ret .= "<table class='sample'>\n";
					//$ret .= "	<th class='sample'>Total Earned Mins</th>\n";
					//$ret .= "	<th class='sample'>Total Actual Mins</th>\n";
					//$ret .= "	<th class='sample'>Eff Score*</th>\n";
					//$ret .= "	<td id='x_eff' name='x_eff' onclick=\"closeDiv('div_eff')\" TITLE='Remove Table'>X</td>\n";
					//$ret .= "	<tr>\n";
					//$ret .= "		<td colspan='4'>* = Total Earned Mins / Total Actual Mins</td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "	<tr>\n";
					//$ret .= "		<input type='hidden' id='earned' value='" . round($tot_min_earned,3) . "'></input>\n";
					//$ret .= "		<input type='hidden' id='actual' value='" . round($tot_team_actual_min,3) . "'></input>\n";
					//$ret .= "		<input type='hidden' id='unadj' value='" . round($tot_team_actual_min_UNADJUSTED,3) . "'></input>\n";
					//$ret .= "		<input type='hidden' id='indir' value='" . round($tot_so_indir_min,3) . "'></input>\n";
					//$ret .= "		<td><b>" . round($tot_min_earned,3) ."</b></td>\n";
					//$ret .= "		<td><b>" . round($tot_team_actual_min,3) . "</b></td>\n";
					//$ret .= "		<td colspan=2><b>" . round($ovral_eff * 100,2) . "</b></td>\n";
					//$ret .= "	</tr>\n";
					//$ret .= "</table>\n";
					//$ret .= "	</br>\n";
					//$ret .= "</div>\n";







					$sqlu  = "UPDATE nsa.DCAPPROVALS set ";
					$sqlu .= " AVAIL_MINS = '" . round($tot_team_actual_min_UNADJUSTED,3) . "', ";
					$sqlu .= " INDIR_MINS = '" . round($tot_so_indir_min,3) . "', ";
					$sqlu .= " FLAG_RETRO = 'T' ";
					$sqlu .= " where rowid = '" . $AppRow . "' ";
					QueryDatabase($sqlu, $resultsu);

					error_log("UPDATE QUERY " . $this_row . " of " . $num_recs . ": " . $sqlu);

					$ret .= "	<tr class='sample'>\n";
					$ret .= "		<td class='sample'></td>\n";
					$ret .= "		<td class='sample'></td>\n";
					$ret .= "		<td class='sample'></td>\n";
					$ret .= "		<td class='sample'></td>\n";
					$ret .= "		<td class='sample'>" . round($tot_team_actual_min_UNADJUSTED,3) . "</td>\n";
					$ret .= "		<td class='sample'>" . round($tot_so_indir_min,3) . "</td>\n";
					$ret .= "	</tr>\n";

				}
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
