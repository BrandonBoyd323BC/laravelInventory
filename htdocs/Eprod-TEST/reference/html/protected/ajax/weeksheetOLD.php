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
			if ($UserRow['PERM_HR'] == '1') {
				$ret = '';
				if (isset($_POST["weekOf"]) && isset($_POST["team"]))  {
					$weekOf = $_POST["weekOf"];
					$Team = $_POST["team"];

					$weekOfTS = strtotime($weekOf);
					$woA = getdate($weekOfTS);
					$dayNo = $woA['wday'];

					$total_company_inc = 0;

					$new_weekOfTS = $weekOfTS;
					if ($dayNo <> 0) {
						$new_weekOfTS = strtotime("-" . $dayNo . " days" , $weekOfTS);
					}
					$new_weekOf = date('Y-m-d', $new_weekOfTS);
					//$dayCount = 0;
					//$week_tot_day_min = 0;
					//$week_tot_indir_min = 0;
					//$week_tot_min_earned = 0;

					$sql =  "select ";
					$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_SORT as BADGE_NAME,";
					$sql .= " 	ltrim(ID_BADGE) as ID_BADGE,";
					$sql .= " 	NAME_SORT";
					$sql .= " from ";
					$sql .= " 	nsa.DCEMMS_EMP ";
					$sql .= " where ";
					$sql .= " 	TYPE_BADGE = 'X'";
					$sql .= " 	and";
					$sql .= " 	CODE_ACTV = '0'";
					if ($Team != 'ALL') {
						$sql .= " 	and";
						$sql .= " 	ltrim(ID_BADGE) = '" . $Team . "'";
					}
					$sql .= " order by ID_BADGE asc ";
					QueryDatabase($sql, $resultsX);

					while ($rowX = mssql_fetch_assoc($resultsX)) {
						$dayCount = 0;
						$week_tot_day_min = 0;
						$week_tot_indir_min = 0;
						$week_tot_min_earned = 0;
						$week_tot_min_actual = 0;

						$ID_BADGE = $rowX['ID_BADGE'];
						$Team = $rowX['ID_BADGE'];
						//error_log($Team);
						$ret .= " <div id='div_" . $ID_BADGE . "' name='div_" . $ID_BADGE . "'>\n";
						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample'  colspan = '11'>". $rowX['BADGE_NAME'] . "</th>\n";
						$ret .= "		<td id='x_" . $ID_BADGE . "' name='x_" . $ID_BADGE . "' onclick=\"closeDiv('div_" . $ID_BADGE . "')\" TITLE='Remove Table'>X</td>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<td class='sample'>Week Of:</td>\n";
						$ret .= " 		<td class='sample' colspan=2>". $new_weekOf . "</td>\n";
						$ret .= " 	</tr>\n";
					//}

						$ret .= " 	<tr>\n";
						$ret .= " 		<td>\n";
						$ret .= " 			<table class='sample2'>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th>Date</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th>Earned Min.</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th>Actual Min.</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th>Daily %</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 			</table>\n";
						$ret .= " 		</td>\n";

						while ($dayCount <= 6) {

							$loop_TS = strtotime("+" . $dayCount . " days" , $new_weekOfTS);
							$loop_Date = date('Y-m-d', $loop_TS);

							$DateFrom = $loop_Date;
							$DateTo = $loop_Date;

							createTempTable();
							$a_team_members = populateTempTable($DateFrom, $DateTo, $Team);

							/////////////////////
							//QUERY TEMP TABLE TO CALCULATE INDIRECT HOURS
							/////////////////////
							$sql =  "select ";
							$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
							$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql .= "	tx.* ";
							$sql .= " from ";
							$sql .= "	#temp_trx tx ";
							$sql .= " where ";
							$sql .= " 	tx.ID_BADGE_TEAM = '' ";
							$sql .= "   and ";
							$sql .= " 	tx.CODE_TRX in (104,105) ";
							$sql .= " order by ";
							$sql .= " 	DATE_TRX asc, ";
							$sql .= " 	ID_BADGE asc, ";
							$sql .= " 	time_trx asc ";
							QueryDatabase($sql, $results);

							$tot_indir_sec = 0;
							$nowts = time();

							while ($row = mssql_fetch_assoc($results)) {
								$td_class = GetColorCodeTrx($row['CODE_TRX']);
								$trxType = GetStrCodeTrx($row['CODE_TRX']);

								$prev = '';
								$diff_sec = '';
								$prevdate = '';
								$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
								$currts = strtotime($curr);

								if ($row['CODE_TRX'] == '105')  {
									$sql2 =  "select top 1 ";
									$sql2 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
									$sql2 .= "	tx.* ";
									$sql2 .= " from ";
									$sql2 .= "	#temp_trx tx ";
									$sql2 .= " where ";
									$sql2 .= " 	tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
									$sql2 .= " 	and ";
									$sql2 .= " 	ID_BADGE = '" . $row['ID_BADGE'] ."' ";
									$sql2 .= " 	and "; //<
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
											$tot_indir_sec += $diff_sec;
										}
									}
								}
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

							$tot_day_sec = 0;

							while ($row = mssql_fetch_assoc($results)) {
								$td_class = GetColorCodeTrx($row['CODE_TRX']);
								$trxType = GetStrCodeTrx($row['CODE_TRX']);

								$prev = '';
								$diff_sec = '';
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
							}



							$ovral_eff = '';
							$tot_min_actual = 0;
							$tot_min_earned = 0;
							$resApp = checkLatestApproval("'200'", $DateFrom, $Team);
							if (mssql_num_rows($resApp) > 0) {
								while ($row2 = mssql_fetch_assoc($resApp)) {
									$ovral_eff = round(($row2['EARNED_MINS'] / $row2['ACTUAL_MINS']) * 100,2);
									$tot_min_actual = $row2['ACTUAL_MINS'];
									$tot_min_earned = $row2['EARNED_MINS'];
									error_log($Team . " skipped " . $ovral_eff);
								}
							}

							$pctClass = GetColorPct($ovral_eff);

							$week_tot_min_earned += $tot_min_earned;
							$week_tot_min_actual += $tot_min_actual;

							$sqlx =  "select ";
							$sqlx .= " * ";
							$sqlx .= " from ";
							$sqlx .= "	nsa.HOLIDAY_DEF ";
							$sqlx .= " where ";
							$sqlx .= " 	DATE_HOL = '" . date('Y-m-d',$loop_TS) . "' ";
							$sqlx .= " 	and FLAG_DEL != 'D' ";
							QueryDatabase($sqlx, $resultsx);
							$fopen = "";
							$fclose = "";
							if (mssql_num_rows($resultsx) > 0) {
								$fopen = "<font class='red'>";
								$fclose = "</font>";
							}

							$ret .= " 		<td>\n";
							$ret .= " 			<table class='sample2'>\n";
							$ret .= " 				<tr>\n";
							$ret .= " 					<th>" . $fopen . date('D',$loop_TS) . " " . date('m/d', $loop_TS) . $fclose . "</th>\n";
							$ret .= " 				</tr>\n";
							$ret .= " 				<tr>\n";
							$ret .= " 					<td>" . $tot_min_earned . "</td>\n";
							$ret .= " 				</tr>\n";
							$ret .= " 				<tr>\n";
							$ret .= " 					<td>" . $tot_min_actual . "</td>\n";
							$ret .= " 				</tr>\n";
							$ret .= " 				<tr>\n";
							$ret .= " 					<td>" . $ovral_eff . " %</td>\n";
							$ret .= " 				</tr>\n";
							$ret .= " 			</table>\n";
							$ret .= " 		</td>\n";
							$dayCount ++;
						}

						$week_ovral_eff = round(($week_tot_min_earned / $week_tot_min_actual) * 100,2);
						$ret .= " 		<td>\n";
						$ret .= " 		</td>\n";
						$ret .= " 		<td>\n";
						$ret .= " 			<table class='sample2'>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th class='sample'>Weekly Total</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<td>" . round($week_tot_min_earned,3) . "</td>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<td>" . round($week_tot_min_actual,3) . "</td>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<td>" . $week_ovral_eff ." %</td>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 			</table>\n";
						$ret .= " 		</td>\n";
						$ret .= " 		<td>\n";
						$ret .= " 			<table class='sample2'>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th class='sample' colspan=2>Weekly %</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th colspan=2>" . $week_ovral_eff ." %</th>\n";
						$ret .= " 				</tr>\n";

						$Reg = 0;
						$OT = 0;
						$rnd_woe = 75;

						switch (true) {
							case ($week_ovral_eff <= 75):
								$rnd_woe = 75;
								break;
							case ($week_ovral_eff >= 125):
								$rnd_woe = 125;
								break;
							case ($week_ovral_eff <= 100):
								$rnd_woe = roundToNearestFraction($week_ovral_eff, 1/4);
								break;
							case ($week_ovral_eff > 100):
								$rnd_woe = roundToNearestFraction($week_ovral_eff, 1/5);
								break;
						}

						$sql =  "select ";
						$sql .= "  * ";
						$sql .= " from ";
						$sql .= " 	nsa.DCPERCENT_RATE ";
						$sql .= " where ";
						$sql .= " 	PCT ='" . $rnd_woe ."'";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							$Reg = $row['Reg'];
							$OT = $row['OT'];
						}

						$ret .= " 				<tr>\n";
						$ret .= " 					<th>Reg: </th>\n";
						$ret .= " 					<th>$" . number_format($Reg,2) . "</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 				<tr>\n";
						$ret .= " 					<th>OT: </th>\n";
						$ret .= " 					<th>$" . number_format($OT,2) . "</th>\n";
						$ret .= " 				</tr>\n";
						$ret .= " 			</table>\n";
						$ret .= " 		</td>\n";
						$ret .= " 	</tr>\n";
						$ret .= " </table>\n";
						$ret .= " </br>\n";
						$ret .= " </div>\n";



						/////////////////////////////
						// CALCULATE INDIVIDUAL MINUTES FOR WHOLE WEEK
						/////////////////////////////
						$TSMin = $new_weekOfTS;
						$TSMax = strtotime("+6 days" , $new_weekOfTS);

						$DateFrom = date('Y-m-d', $TSMin);
						$DateTo = date('Y-m-d', $TSMax);
						$DATE_HOL = '';

						$sql =  "select ";
						$sql .= " * ";
						$sql .= " from ";
						$sql .= "	nsa.HOLIDAY_DEF ";
						$sql .= " where ";
						$sql .= " 	DATE_HOL between '" . $DateFrom . "' and '" . $DateTo . "' ";
						$sql .= " 	and FLAG_DEL != 'D' ";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							$DATE_HOL = $row['DATE_HOL'];
						}
						$NumHolDays = mssql_num_rows($results);
						error_log("NUMBER OF HOLDAYS: " . $NumHolDays);


						createTempTable();
						$a_team_members = populateTempTable($DateFrom, $DateTo, $Team);

						if ($NumHolDays > 0) {
							/////////////////////////////////////
							// CALCULATE 12 Week Average
							/////////////////////////////////////
							$dtTS = strtotime($DATE_HOL);
							$dtA = getdate($dtTS);
							$dfTS = strtotime("-84 days" , $dtTS);
							$df = date('Y-m-d', $dfTS);
							$TwelveWeekAdditionalRate = 0;

							$sql =  "select ";
							$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_SORT as BADGE_NAME,";
							$sql .= " 	ltrim(ID_BADGE) as ID_BADGE,";
							$sql .= " 	NAME_SORT";
							$sql .= " from ";
							$sql .= " 	nsa.DCEMMS_EMP ";
							$sql .= " where ";
							$sql .= " 	TYPE_BADGE = 'X'";
							$sql .= " 	and";
							$sql .= " 	CODE_ACTV = '0'";
							if ($Team != 'ALL') {
								$sql .= " 	and";
								$sql .= " 	ltrim(ID_BADGE) = '" . $ID_BADGE . "'";
							}
							$sql .= " order by ID_BADGE asc ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$ID_BADGE = trim($row['ID_BADGE']);

								$sql2 =  "select ";
								$sql2 .= " 	* ";
								$sql2 .= " from ";
								$sql2 .= " 	nsa.DCAPPROVALS ";
								$sql2 .= " where ";
								$sql2 .= " 	CODE_APP = '200'";
								$sql2 .= " 	and";
								$sql2 .= " 	DATE_APP between '" . $df . "' and '" . $DATE_HOL . "' ";
								$sql2 .= " 	and";
								$sql2 .= " 	DATE_APP >= '2012-01-01' ";
								$sql2 .= " 	and";
								$sql2 .= " 	ltrim(BADGE_APP) = '" . $ID_BADGE . "'";
								$sql2 .= " order by DATE_APP asc ";
								QueryDatabase($sql2, $results2);

								$tot_min_earned = 0;
								$tot_min_actual = 0;
								while ($row2 = mssql_fetch_assoc($results2)) {
									$tot_min_earned += $row2['EARNED_MINS'];
									$tot_min_actual += $row2['ACTUAL_MINS'];
								}
								$TwelveWeekAvg = round((($tot_min_earned / $tot_min_actual) * 100),3);
								error_log("TWELVE WEEK AVG: " . $TwelveWeekAvg);


								$twrnd_woe = 75;
								switch (true) {
									case ($TwelveWeekAvg <= 75):
										$twrnd_woe = 75;
										break;
									case ($TwelveWeekAvg >= 125):
										$twrnd_woe = 125;
										break;
									case ($TwelveWeekAvg <= 100):
										$twrnd_woe = roundToNearestFraction($TwelveWeekAvg, 1/4);
										break;
									case ($TwelveWeekAvg > 100):
										$twrnd_woe = roundToNearestFraction($TwelveWeekAvg, 1/5);
										break;
								}

								$sql2 =  "select ";
								$sql2 .= "  * ";
								$sql2 .= " from ";
								$sql2 .= " 	nsa.DCPERCENT_RATE ";
								$sql2 .= " where ";
								$sql2 .= " 	PCT ='" . $twrnd_woe ."'";
								QueryDatabase($sql2, $results2);
								while ($row2 = mssql_fetch_assoc($results2)) {
									$TwelveWeekAdditionalRate = $row2['Reg'];
								}
								error_log("TWELVE WEEK AVG RATE: " . $TwelveWeekAdditionalRate);

							}
						}







						/////////////////////
						//QUERY TEMP TABLE FOR INDIVIDUAL INDIRECT HOURS
						/////////////////////
						$tot_team_inc = 0;
						$tot_indir_sec = 0;
						$tot_team_actual_sec = 0;
						$ret .= "<div id='div_emp_" . $Team . "' name='div_emp_" . $Team . "'>\n";
						$ret .= "<table class='sample'>\n";
						$ret .= "	<th class='sample'>Employee</th>\n";
						$ret .= "	<th class='sample'>Hours</th>\n";
						$ret .= "	<th class='sample'>Rate</th>\n";
						$ret .= "	<th class='sample'>Incentive</th>\n";
						$ret .= "	<td id='x_emp_" . $Team . "' name='x_emp_" . $Team . "' onclick=\"closeDiv('div_emp_" . $Team . "')\" TITLE='Remove Table'>X</td>\n";

						foreach ($a_team_members as $member) {


							/////////////////////
							//QUERY TEMP TABLE TO CALCULATE INDIRECT HOURS
							/////////////////////
							$sql =  "select ";
							$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
							$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql .= "	e.NAME_EMP, ";
							$sql .= "	e.CODE_USER_1_DC, ";
							//$sql .= "	e.RATE_LABOR_ACTUAL, ";
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
							$sql .= " order by ";
							$sql .= " 	DATE_TRX asc, ";
							$sql .= " 	ID_BADGE asc, ";
							$sql .= " 	time_trx asc ";
							QueryDatabase($sql, $results);

							$tot_indiv_indir_sec = 0;
							$tot_indiv_day_sec = 0;
							$name = '';
							$pct = 100;
							$nowts = time();

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
								//$rate = $row['RATE_LABOR_ACTUAL'];
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
							}

							$tot_indiv_actual_sec = $tot_indiv_day_sec - $tot_indiv_indir_sec;

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

							$tot_indiv_actual_hours = round(($tot_indiv_actual_sec / 60)/60,3);

							$hol_hours = ($NumHolDays * 8);
							//$reg_hours = $tot_indiv_actual_hours - $hol_hours;
							$reg_hours = $tot_indiv_actual_hours;
							$regCutoff = 40 - $hol_hours;
							$ot_hours = 0;
							if ($reg_hours > $regCutoff) {
								$ot_hours = $reg_hours - $regCutoff;
								$ot_hours = roundToNearestFraction($ot_hours, 1/4);
								$reg_hours = $regCutoff;
							}
							$Reg_Rate = number_format($Reg,2);
							$OT_Rate = number_format($OT,2);

							$Reg_inc = round($Reg_Rate * $reg_hours,2);
							$OT_inc = round($OT_Rate * $ot_hours,2);

							$Reg_inc_fmt = number_format($Reg_inc,2);
							$OT_inc_fmt = number_format($OT_inc,2);

							$ret .= "	<tr class='sample2'>\n";
							$ret .= " 		<th>" . $name . "</th>";
							$ret .= "		<td>" . $reg_hours . "</td>\n";
							$ret .= " 		<td>$" . $Reg_Rate . "</td>\n";
							$ret .= " 		<td>$" . $Reg_inc_fmt . "</td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='sample2'>\n";
							$ret .= " 		<th></th>";
							$ret .= "		<td>" . $ot_hours . "</td>\n";
							$ret .= " 		<td>$" . $OT_Rate . "</td>\n";
							$ret .= " 		<td>$" . $OT_inc_fmt . "</td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='sample2'>\n";
							$ret .= " 		<td colspan=3></td>";
							$ret .= " 		<th>$" . number_format($Reg_inc + $OT_inc,2) . "</th>\n";
							$ret .= "	</tr>\n";

							if ($hol_hours > 0) {
								$ret .= "	<tr class='sample2'>\n";
								$ret .= " 		<td></td>";
								$ret .= "		<th><font class='red'>Hol. Hrs</font></th>\n";
								$ret .= "		<th><font class='red'>12wRate</font></th>\n";
								$ret .= "		<th><font class='red'>Hol. Pay</font></th>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='sample2'>\n";
								$ret .= " 		<td></td>";
								$ret .= "		<td><font class='red'>" . $hol_hours . "</font></td>\n";
								$ret .= "		<td><font class='red'>$" . number_format($TwelveWeekAdditionalRate,2) . "</font></td>\n";
								$ret .= "		<th><font class='red'>$" . number_format(($hol_hours * $TwelveWeekAdditionalRate),2) . "</font></th>\n";
								$ret .= "	</tr>\n";
							}

							$tot_team_inc += ($Reg_inc + $OT_inc);
							$tot_indir_sec += $tot_indiv_indir_sec;
							$tot_team_actual_sec += $tot_indiv_actual_sec;
						}

						$ret .= "	<tr class='sample2'>\n";
						$ret .= " 		<td colspan=4></td>";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='sample2'>\n";
						$ret .= " 		<td colspan=3><b>Total Team Incentive</b></td>";
						$ret .= " 		<th>$" . number_format($tot_team_inc,2) . "</th>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						$ret .= "	</br>\n";
						$ret .= "</div>\n";

						$total_company_inc += $tot_team_inc;
					}

					if ($Team == 'ALL') {
						$ret .= "<div id='div_company_inc' name='div_company_inc'>\n";
						$ret .= "<table class='sample'>\n";
						$ret .= "	<tr class='sample'>\n";
						$ret .= "		<th class='sample'>Total Company Incentive</th>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='sample'>\n";
						$ret .= " 		<th>$" . number_format($total_company_inc,2) . "</th>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						$ret .= "	</br>\n";
						$ret .= "</div>\n";
					}

				}
				echo json_encode(array("returnValue"=> $ret));

			} else {
				echo json_encode(array("returnValue"=> '<h1>Invalid Permissions</h1>'));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
