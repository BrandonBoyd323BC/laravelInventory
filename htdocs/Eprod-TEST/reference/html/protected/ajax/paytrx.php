<?php



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

			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["zeroHour"])) {
				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
				$ZeroHour = $_POST["zeroHour"];
				$fmtZeroHour = substr($ZeroHour,0,2).":".substr($ZeroHour,2,2).":".(string)substr($ZeroHour,4,2);
				$DateFromTS = strtotime($DateFrom." ".$ZeroHour);
				$DateToTS = strtotime($DateTo." ".$ZeroHour);


				$DateToPayTRX_TS = strtotime("-1 days" , $DateToTS);
				$DateToPayTRX = date('Y-m-d', $DateToPayTRX_TS);
				$DateToPayTRX	= str_replace("-","",$DateToPayTRX);
				$DateFrom 		= str_replace("-","",$DateFrom);
				$DateTo 		= str_replace("-","",$DateTo);


				$dtTS = strtotime($_POST['dt']);
				//$dtA = getdate($dtTS);
				$dfTwelveTS = strtotime("-84 days" , $dtTS);
				$dfTwelve = date('Y-m-d', $dfTwelveTS);
				$DateFromTwelve	= str_replace("-","",$dfTwelve);
				$b_flip = true;

				///////////////////////////////////////
				/// CREATE TEMP TABLE FOR PCTS
				///////////////////////////////////////
				$sql = " IF OBJECT_ID('tempdb..#temp_pct') IS NOT NULL";
				$sql .= " DROP TABLE #temp_pct";
				QueryDatabase($sql, $results);

				$sql = " CREATE TABLE #temp_pct(";
				$sql .= " ID_BADGE_TEAM varchar(9) not null, ";
				$sql .= " TWELVE_WEEK_PCT numeric(5,2) not null, ";
				$sql .= " TWELVE_WEEK_REG_RATE numeric(4,2) not null, ";
				$sql .= " TWELVE_WEEK_OT_RATE numeric(4,2) not null, ";
				$sql .= " CURRENT_WEEK_PCT numeric(7,2) not null, ";
				$sql .= " CURRENT_WEEK_REG_RATE numeric(7,2) not null, ";
				$sql .= " CURRENT_WEEK_OT_RATE numeric(7,2) not null ";
				$sql .= ")";
				QueryDatabase($sql, $results);

				///////////////////////////////////////
				/// CALCULATE CURRENT EFFICIENCY FROM APPROVALS
				///////////////////////////////////////
				$sql  = "SELECT ";
				$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_SORT as BADGE_NAME,";
				$sql .= " ltrim(ID_BADGE) as ID_BADGE,";
				$sql .= " ltrim(CODE_USER) as CODE_USER,";
				$sql .= " NAME_SORT";
				$sql .= " FROM nsa.DCEMMS_EMP ";
				$sql .= " WHERE TYPE_BADGE = 'X'";
				$sql .= " and CODE_ACTV = '0'";
				$sql .= " ORDER BY ID_BADGE asc ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$ID_BADGE = trim($row['ID_BADGE']);

					///////////////////////////////////////
					/// CALCULATE CURRENT EFFICIENCY FROM APPROVALS
					///////////////////////////////////////

					$sql2  = "SELECT ";
					$sql2 .= " sum(EARNED_MINS) as SUM_EARNED_MINS, ";
					$sql2 .= " sum(ACTUAL_MINS) as SUM_ACTUAL_MINS ";
					$sql2 .= " FROM nsa.DCAPPROVALS ";
					$sql2 .= " WHERE CODE_APP = '200'";
					$sql2 .= " and DATE_APP between '" . $DateFrom . "' and '" . $DateToPayTRX . "' ";
					$sql2 .= " and DATE_APP >= '2012-01-01' ";
					$sql2 .= " and ltrim(BADGE_APP) = '" . $ID_BADGE . "'";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$tot_min_earned = $row2['SUM_EARNED_MINS'];
						$tot_min_actual = $row2['SUM_ACTUAL_MINS'];
					}

					$CurrentWeekAvg = round((($tot_min_earned / $tot_min_actual) * 100),3);
					$CurrentWeekRegRate = 0;
					$CurrentWeekOTRate = 0;
					$twrnd_woe = 75;

					switch (true) {
						case ($CurrentWeekAvg <= 75):
							$twrnd_woe = 75;
							break;
						case ($CurrentWeekAvg >= 125):
							$twrnd_woe = 125;
							break;
						case ($CurrentWeekAvg <= 100):
							$twrnd_woe = roundToNearestFraction($CurrentWeekAvg, 1/4);
							break;
						case ($CurrentWeekAvg > 100):
							$twrnd_woe = roundToNearestFraction($CurrentWeekAvg, 1/5);
							break;
					}

					//$sql2  = "SELECT * ";
					//$sql2 .= " FROM nsa.DCPERCENT_RATE ";
					//$sql2 .= " WHERE PCT ='" . $twrnd_woe ."'";
					//QueryDatabase($sql2, $results2);

					$sql2  = "SELECT TOP 1 * ";
					$sql2 .= " FROM nsa.DCPERCENT_RATE_CLASS ";
					$sql2 .= " WHERE PCT <='" . $twrnd_woe ."'";
					$sql2 .= " and ID_CLASS = '".$row['CODE_USER']."'";
					$sql2 .= " ORDER BY PCT desc";
					QueryDatabase($sql2, $results2);

					while ($row2 = mssql_fetch_assoc($results2)) {
						$CurrentWeekRegRate = $row2['Reg'];
						$CurrentWeekOTRate = $row2['OT'];
					}

					///////////////////////////////////////
					/// CALCULATE TWELVE WEEK EFFICIENCY FROM APPROVALS
					///////////////////////////////////////

					$sql2  = "SELECT ";
					$sql2 .= " sum(EARNED_MINS) as SUM_EARNED_MINS, ";
					$sql2 .= " sum(ACTUAL_MINS) as SUM_ACTUAL_MINS ";
					$sql2 .= " FROM nsa.DCAPPROVALS ";
					$sql2 .= " WHERE CODE_APP = '200' ";
					$sql2 .= " and DATE_APP between '" . $DateFromTwelve . "' and '" . $DateToPayTRX . "' ";
					$sql2 .= " and DATE_APP >= '2012-01-01' ";
					$sql2 .= " and ltrim(BADGE_APP) = '" . $ID_BADGE . "'";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$tot_min_earned = $row2['SUM_EARNED_MINS'];
						$tot_min_actual = $row2['SUM_ACTUAL_MINS'];
					}

					$TwelveWeekAvg = round((($tot_min_earned / $tot_min_actual) * 100),3);
					$TwelveWeekRegRate = 0;
					$TwelveWeekOTRate = 0;
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

					//$sql2  = "SELECT * ";
					//$sql2 .= " FROM nsa.DCPERCENT_RATE ";
					//$sql2 .= " WHERE PCT ='" . $twrnd_woe ."'";
					//QueryDatabase($sql2, $results2);

					$sql2  = "SELECT TOP 1 * ";
					$sql2 .= " FROM nsa.DCPERCENT_RATE_CLASS ";
					$sql2 .= " WHERE PCT <='" . $twrnd_woe ."'";
					$sql2 .= " and ID_CLASS = '".$row['CODE_USER']."'";
					$sql2 .= " ORDER BY PCT desc";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$TwelveWeekRegRate = $row2['Reg'];
						$TwelveWeekOTRate = $row2['OT'];
					}

					///////////////////////////////
					/// INSERT INTO TEMP TABLE
					///////////////////////////////
					$sql2 =  "INSERT into #temp_pct ( ";
					$sql2 .= " ID_BADGE_TEAM, ";
					$sql2 .= " TWELVE_WEEK_PCT, ";
					$sql2 .= " TWELVE_WEEK_REG_RATE, ";
					$sql2 .= " TWELVE_WEEK_OT_RATE, ";
					$sql2 .= " CURRENT_WEEK_PCT, ";
					$sql2 .= " CURRENT_WEEK_REG_RATE, ";
					$sql2 .= " CURRENT_WEEK_OT_RATE ";
					$sql2 .= " ) VALUES ( ";
					$sql2 .= " '". $ID_BADGE ."', ";
					$sql2 .= $TwelveWeekAvg .", ";
					$sql2 .= $TwelveWeekRegRate .", ";
					$sql2 .= $TwelveWeekOTRate .", ";
					$sql2 .= $CurrentWeekAvg .", ";
					$sql2 .= $CurrentWeekRegRate .", ";
					$sql2 .= $CurrentWeekOTRate ." ";
					$sql2 .= " ) ";
					QueryDatabase($sql2, $results2);
				}

				$ret .= "		<table class='sample'>\n";
				$ret .= "		 	<tr>\n";
				$ret .= "				<th>ID_BADGE</th>\n";
				$ret .= "				<th>NAME</th>\n";
				$ret .= "				<th>PCT</th>\n";
				$ret .= "				<th>ID_BADGE_TEAM_STD</th>\n";
				$ret .= "				<th>DATE_ELIG</th>\n";
				$ret .= "				<th>REGULAR</th>\n";
				$ret .= "				<th>OT</th>\n";
				$ret .= "				<th>$ INCENT</th>\n";
				$ret .= "				<th>VAC</th>\n";
				$ret .= "				<th>$ VACINC</th>\n";
				$ret .= "				<th>HOL</th>\n";
				$ret .= "				<th>$ HOLINC</th>\n";
				$ret .= "				<th>BONUS</th>\n";
				$ret .= "				<th>TRAIN</th>\n";
				$ret .= "				<th>REFERRAL</th>\n";
				$ret .= "		 	</tr>\n";

				$sql  = " SELECT top 15";
				$sql .= " p.ID_BADGE, ";
				$sql .= " e.NAME_EMP as NAME, ";
				$sql .= " e.CODE_USER_1_DC as PCT, ";
				$sql .= " ltrim(e.ID_BADGE_TEAM_STD) as ID_BADGE_TEAM_STD, ";
				$sql .= " dateadd(day, 90, DATE_USER) as DATE_ELIG, ";
				//$sql .= " sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateToPayTRX ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = 'REG') then p.HR_PAID else 0 end) as 'REGULAR', ";
				$sql .= " sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateToPayTRX ."') and p.CODE_PAY_DC = 'REG') then p.HR_PAID else 0 end) as 'REGULAR', ";
				$sql .= " sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateToPayTRX ."') and p.CODE_PAY_DC in ('OVT','SUN','HOLW')) then p.HR_PAID else 0 end) as 'OT', ";
				$sql .= " '' as INCENT, ";
				$sql .= " sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateToPayTRX ."') and p.CODE_PAY_DC = 'PTO') then p.HR_PAID else 0 end) as 'VAC', ";
				$sql .= " '' as VACINC, ";
				$sql .= " sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateToPayTRX ."') and p.CODE_PAY_DC = 'HOL') then p.HR_PAID else 0 end) as 'HOL', ";
				$sql .= " '' as HOLINC, ";
				$sql .= " '' as BONUS, ";
				$sql .= " '' as TRAIN, ";
				$sql .= " '' as REFERRAL ";
				$sql .= " FROM nsa.PAYTRX p ";
				$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
				$sql .= " on p.ID_BADGE = e.ID_BADGE ";
				$sql .= " and e.CODE_ACTV = 0 ";
				$sql .= " WHERE p.DATE_TRX between '". $DateFrom ."' and '". $DateToPayTRX ."' ";
				//$sql .= " 	and p.FLAG_APPRV in ('Y','A') ";

				//$sql .= "	and ltrim(p.ID_BADGE) in ('2804','3204','2808','3128','3134','2702','3111','2293','2821') ";
				//$sql .= "	and ltrim(p.ID_BADGE) in ('2228') ";

				$sql .= " GROUP BY p.ID_BADGE, e.NAME_EMP, e.CODE_USER_1_DC, dateadd(day, 90, DATE_USER), e.ID_BADGE_TEAM_STD ";
				$sql .= " ORDER BY p.ID_BADGE asc ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$b_flip = !$b_flip;
					if ($b_flip) {
						$trClass = 'd1';
					} else {
						$trClass = 'd0';
					}

					//$Bank = -37.917;
					$Bank = -40;
					$Bank += $row['VAC'];
					$Bank += $row['HOL'];

					$ret2 = "";
					$incent_dollar = 0;
					$grand_tot_indiv_actual_hours = 0;
					$OT_act = 0;
					$VacInc = 0;
					$VacIncRate = 0;
					$member = trim($row['ID_BADGE']);
					$pct = trim($row['PCT']);
					if ($pct == '' or $pct == '0') {
						$pct = '100';
					}

					/////////////////////////////
					//DETERMINE EACH TEAM THE MEMBER WAS ON
					/////////////////////////////
					$sql1  = " SELECT distinct (dc.ID_BADGE_TEAM) ";
					$sql1 .= " FROM nsa.DCUTRX_ZERO_PERM dc ";
					$sql1 .= " WHERE dc.DATE_TRX between '". $DateFrom ."' and '". $DateToPayTRX ."'";
					$sql1 .= " and ltrim(dc.ID_BADGE) = '". trim($row['ID_BADGE']) . "'";
					//$sql1 .= " and dc.CODE_TRX in (305) ";
					$sql1 .= " and dc.CODE_TRX in (304) ";
					$sql1 .= " and dc.FLAG_DEL = '' ";
					QueryDatabase($sql1, $results1);
					$num_rows = mssql_num_rows($results1);

					if ($num_rows > 1) {
						error_log("MULTIPLE TEAMS");
					}

					while ($row1 = mssql_fetch_assoc($results1)) {
						$Team = trim($row1['ID_BADGE_TEAM']);
						if ($DEBUG > 1) {
							error_log("Badge: " . trim($row['ID_BADGE']) . " Team: " . $row1['ID_BADGE_TEAM']);
						}
						/////////////////////////////
						//FOR EACH TEAM THEY WERE ON, DETERMINE TIME ON TEAM FOR AND INCENTIVE DOLLAR AMOUNT
						/////////////////////////////
						createTempTable();
						$a_team_members = populateTempTable($DateFrom, $DateTo, $ZeroHour, $Team);

						/////////////////////
						//QUERY TEMP TABLE TO CALCULATE INDIRECT HOURS
						/////////////////////
						$tot_indiv_indir_sec = 0;
						$tot_indiv_day_sec = 0;
						$name = '';
						$team_std = '';
						$nowts = time();

						/////////////////////
						//QUERY TEMP TABLE TO CALCULATE OFF TEAM
						/////////////////////
						$sqlz = "SELECT ";
						$sqlz.= " CONVERT(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
						$sqlz.= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
						$sqlz.= " e.NAME_EMP, ";
						$sqlz.= " e.ID_BADGE_TEAM_STD, ";
						$sqlz.= " e.CODE_USER_1_DC, ";
						$sqlz.= " tx.* ";
						$sqlz.= " FROM #temp_trx tx, ";
						$sqlz.= " nsa.DCEMMS_EMP e ";
						$sqlz.= " WHERE ltrim(tx.ID_BADGE) = '". $member . "' ";
						$sqlz.= " and tx.ID_BADGE = e.ID_BADGE ";
						$sqlz.= " and tx.CODE_TRX in (305) ";
						$sqlz.= " and ltrim(tx.ID_BADGE_TEAM) = '" . $Team . "' ";
						$sqlz.= " ORDER BY ";
						$sqlz.= " ID_BADGE asc, ";
						$sqlz.= " DATE_TRX asc, ";
						$sqlz.= " time_trx asc ";
						QueryDatabase($sqlz, $resultsz);

						while ($rowz = mssql_fetch_assoc($resultsz)) {
							if ($DEBUG > 1) {
								error_log(" Badge: " . $rowz['ID_BADGE'] . " Date: " . $rowz['DATE_TRX3'] . " Time: " .  $rowz['TIME_TRX'] . " TRX: " . $rowz['CODE_TRX']);	
							}							

							/////////////////////
							//QUERY TEMP TABLE TO CALCULATE OFF TEAM
							/////////////////////
							$sql2z  = "SELECT top 1 ";
							$sql2z .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql2z .= " tx.* ";
							$sql2z .= " FROM #temp_trx tx ";
							$sql2z .= " WHERE tx.CODE_TRX in (304) ";
							$sql2z .= " and ID_BADGE = '" . $rowz['ID_BADGE'] ."' ";
							//$sql2z .= " and DATE_TRX <= '" . $rowz['DATE_TRX'] ."' ";
							//$sql2z .= " and TIME_TRX <= '" . $rowz['TIME_TRX'] ."' ";
							$sql2z .= " and DATETIME_TRX_TS <= '" . $rowz['DATETIME_TRX_TS'] ."' ";
							$sql2z .= " ORDER BY ";
							$sql2z .= " DATE_TRX desc, ";
							$sql2z .= " time_trx desc ";
							QueryDatabase($sql2z, $results2z);

							while ($row2z = mssql_fetch_assoc($results2z)) {
								if ($DEBUG > 1) {
									error_log(" Badge: " . $row2z['ID_BADGE'] . " Date: " . $row2z['DATE_TRX3'] . " Time: " .  $row2z['TIME_TRX'] . " TRX: " . $row2z['CODE_TRX']);
								}
								$sql3z  = "SELECT ";
								$sql3z .= " CONVERT(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
								$sql3z .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
								$sql3z .= " e.NAME_EMP, ";
								$sql3z .= " e.CODE_USER_1_DC, ";
								$sql3z .= " tx.* ";
								$sql3z .= " FROM #temp_trx tx, ";
								$sql3z .= " nsa.DCEMMS_EMP e ";
								$sql3z .= " WHERE ltrim(tx.ID_BADGE) = '". $member . "' ";
								$sql3z .= " and tx.ID_BADGE = e.ID_BADGE ";
								$sql3z .= " and tx.CODE_TRX in (105) ";
								//$sql3z .= " and tx.DATE_TRX = '" . $rowz['DATE_TRX'] . "' ";
								//$sql3z .= " and tx.TIME_TRX between '" . $row2z['TIME_TRX'] . "' and '" . $rowz['TIME_TRX'] . "' ";
								$sql3z .= " and tx.DATETIME_TRX_TS between '" . $row2z['DATETIME_TRX_TS'] . "' and '" . $rowz['DATETIME_TRX_TS'] . "' ";
								$sql3z .= " ORDER BY ";
								$sql3z .= " DATE_TRX asc, ";
								$sql3z .= " ID_BADGE asc, ";
								$sql3z .= " time_trx asc ";
								QueryDatabase($sql3z, $results3z);

								while ($row3z = mssql_fetch_assoc($results3z)) {
									if ($DEBUG > 1) {
										error_log(" Badge: " . $row3z['ID_BADGE'] . " Date: " . $row3z['DATE_TRX3'] . " Time: " .  $row3z['TIME_TRX'] . " TRX: " . $row3z['CODE_TRX']);
									}
									$sql4z  = "SELECT top 1 ";
									$sql4z .= " CONVERT(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
									$sql4z .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
									$sql4z .= " e.NAME_EMP, ";
									$sql4z .= " e.CODE_USER_1_DC, ";
									$sql4z .= " tx.* ";
									$sql4z .= " FROM #temp_trx tx, ";
									$sql4z .= " nsa.DCEMMS_EMP e ";
									$sql4z .= " WHERE ltrim(tx.ID_BADGE) = '". $member . "' ";
									$sql4z .= " and tx.ID_BADGE = e.ID_BADGE ";
									$sql4z .= " and tx.CODE_TRX in (104) ";
									//$sql4z .= " and tx.DATE_TRX = '" . $rowz['DATE_TRX'] . "' ";
									//$sql4z .= " and tx.TIME_TRX between '" . $row2z['TIME_TRX'] . "' and '" . $rowz['TIME_TRX'] . "' ";
									//$sql4z .= " and tx.TIME_TRX <= '" . $row3z['TIME_TRX'] . "' ";
									$sql4z .= " and tx.DATETIME_TRX_TS between '" . $row2z['DATETIME_TRX_TS'] . "' and '" . $rowz['DATETIME_TRX_TS'] . "' ";
									$sql4z .= " and tx.DATETIME_TRX_TS <= '" . $row3z['DATETIME_TRX_TS'] . "' ";
									$sql4z .= " ORDER BY ";
									$sql4z .= " DATE_TRX asc, ";
									$sql4z .= " ID_BADGE asc, ";
									$sql4z .= " time_trx desc ";
									QueryDatabase($sql4z, $results4z);

									while ($row4z = mssql_fetch_assoc($results4z)) {
										if ($DEBUG > 1) {
											error_log(" Badge: " . $row4z['ID_BADGE'] . " Date: " . $row4z['DATE_TRX3'] . " Time: " .  $row4z['TIME_TRX'] . " TRX: " . $row4z['CODE_TRX']);
										}
										//$prev = $row3z['DATE_TRX3'] . " " . str_pad($row3z['TIME_TRX'],6,"0",STR_PAD_LEFT);
										//$prevts = strtotime($prev);
										$prevts = $row3z['DATETIME_TRX_TS'];
										//$curr = $row4z['DATE_TRX3'] . " " . str_pad($row4z['TIME_TRX'],6,"0",STR_PAD_LEFT);
										//$currts = strtotime($curr);
										$currts = $row4z['DATETIME_TRX_TS'];
										
										$diff_sec = $prevts - $currts;

										if ($nowts >= $currts) {
											$tot_indiv_indir_sec += $diff_sec;
										}
									}
								}

								$name = $rowz['NAME_EMP'];
								$team_std = $rowz['ID_BADGE_TEAM_STD'];
								//$prev = $row2z['DATE_TRX3'] . " " . str_pad($row2z['TIME_TRX'],6,"0",STR_PAD_LEFT);
								//$prevts = strtotime($prev);
								$prevts = $row2z['DATETIME_TRX_TS'];
								//$curr = $rowz['DATE_TRX3'] . " " . str_pad($rowz['TIME_TRX'],6,"0",STR_PAD_LEFT);
								//$currts = strtotime($curr);
								$currts = $rowz['DATETIME_TRX_TS'];

								//$diff_sec = rounddown15($currts) - roundup15($prevts);
								$diff_sec = ($currts) - ($prevts);
								if ($nowts >= $currts) {
									$tot_indiv_day_sec += $diff_sec;
								}

								$tot_indiv_actual_sec = $tot_indiv_day_sec - $tot_indiv_indir_sec;
							}
						}

						//////////////////////////
						/// ADD INCENTIVE FOR CURRENT TEAM TO INDIVIDUAL'S TOTAL INCENTIVE
						//////////////////////////
						error_log("Team: " . $Team);

						$tot_indiv_actual_hours = round(($tot_indiv_actual_sec / 60)/60,3);
						$grand_tot_indiv_actual_hours += $tot_indiv_actual_hours;

						error_log("tot_indiv_actual_hours: " . $tot_indiv_actual_hours);
						error_log("grand_tot_indiv_actual_hours: " . $grand_tot_indiv_actual_hours);

						error_log("Bank1: " . $Bank);
						$Bank += $tot_indiv_actual_hours;
						error_log("Bank2: " . $Bank);

						if ($Bank > 0) {
							$tot_indiv_actual_hours -= $Bank;
							$OT_act = $Bank;
						}
						error_log("tot_indiv_actual_hours: " . $tot_indiv_actual_hours);
						if ($tot_indiv_actual_hours > 0) {
							$ret2 .= "		 	<tr class='" . $trClass . "'>\n";
							$ret2 .= "		 		<td class='" . $trClass . "' colspan=4></td>\n";
							$ret2 .= "		 		<td class='" . $trClass . "'>Team: " . $Team . "</td>\n";
							$ret2 .= "		 		<td class='" . $trClass . "'>Hours: " . $tot_indiv_actual_hours . "</td>\n";



							$sql5  = "SELECT * FROM #temp_pct ";
							$sql5 .= " WHERE ltrim(ID_BADGE_TEAM) = '". $Team ."' ";
							QueryDatabase($sql5, $results5);
							while ($row5 = mssql_fetch_assoc($results5)) {
								$team_inc_dol = round(($tot_indiv_actual_hours * $row5['CURRENT_WEEK_REG_RATE']),2);
								$incent_dollar += $team_inc_dol;
								//error_log("HOURS: " . $tot_indiv_actual_hours);
								//error_log("RATE: " . $row5['CURRENT_WEEK_REG_RATE']);
								//error_log("DOLLAR: " . $incent_dollar);
								$ret2 .= "		 		<td class='" . $trClass . "'>Rate: " . $row5['CURRENT_WEEK_REG_RATE'] . "</td>\n";
								$ret2 .= "		 		<td class='" . $trClass . "'>Incentive: " . $team_inc_dol . "</td>\n";
							}
							$ret2 .= "		 	</tr>\n";
						}


					}

/*
					$comb = ($row['REGULAR'] + $row['VAC'] + $row['HOL']);
					$comb_act = ($grand_tot_indiv_actual_hours + $row['VAC'] + $row['HOL']);

					if ($comb_act > 37.917) {
						$diff_act = $comb_act - 37.917;
						$OT_act += $diff;
						$row['REGULAR'] -= $diff;
						//$OT_act = ($grand_tot_indiv_actual_hours - (37.917) - $row['VAC'] - $row['HOL']);
					}

					if ($comb > 40) {
						$diff = $comb - 40;
						$row['OT'] += $diff;
						$row['REGULAR'] -= $diff;
					}
*/
					if (trim($row['ID_BADGE_TEAM_STD']) != '') {
						$sql2  = "SELECT * FROM #temp_pct ";
						$sql2 .= " WHERE ltrim(ID_BADGE_TEAM) = '".$row['ID_BADGE_TEAM_STD']."' ";
						QueryDatabase($sql2, $results2);
						while ($row2 = mssql_fetch_assoc($results2)) {
							$VacIncRate = $row2['TWELVE_WEEK_REG_RATE'];
							if ($row['OT'] > 0) {
								error_log("Badge " . $row['ID_BADGE']);
								error_log("Grand Total actual HOURS: " . $grand_tot_indiv_actual_hours);
								error_log("REGULAR HOURS: " . $row['REGULAR']);
								error_log("REGULAR RATE: " . $row2['CURRENT_WEEK_REG_RATE']);
								error_log("Incent Before OT: " . $incent_dollar);
								error_log("OT HOURS: " . $OT_act);
								error_log("OT RATE: " . $row2['CURRENT_WEEK_OT_RATE']);
								$incent_dollar += round(($OT_act * $row2['CURRENT_WEEK_OT_RATE']),2);
								error_log("Incent AFTER OT: " . $incent_dollar);
								$ret2 .= "		 	<tr class='" . $trClass . "'>\n";
								$ret2 .= "		 		<td class='" . $trClass . "' colspan=4></td>\n";
								$ret2 .= "		 		<td class='" . $trClass . "'>OT: </td>\n";
								$ret2 .= "		 		<td class='" . $trClass . "'>OT Hours: " . $OT_act . "</td>\n";
								$ret2 .= "		 		<td class='" . $trClass . "'>OT Rate: " . $row2['CURRENT_WEEK_OT_RATE'] . "</td>\n";
								$ret2 .= "		 		<td class='" . $trClass . "'>OT Incentive: " . round(($OT_act * $row2['CURRENT_WEEK_OT_RATE']),2) . "</td>\n";
								$ret2 .= "		 	</tr>\n";
							}
						}
					}

					$incent_dollar = round($incent_dollar,2);
					$VacIncDollars = round($row['VAC'] * $VacIncRate,2);
					$HolIncDollars = round($row['HOL'] * $VacIncRate,2);

					if ($pct != 100) {
						$incent_dollar = 0;
						$VacIncDollars = 0;
						$HolIncDollars = 0;
					}

					$ret .= "		 	<tr class='" . $trClass . "Top'>\n";
					$ret .= "				<td>". $row['ID_BADGE'] ."</td>\n";
					$ret .= "				<td>". $row['NAME'] ."</td>\n";
					$ret .= "				<td>". $row['PCT'] ."</td>\n";
					$ret .= "				<td>". $row['ID_BADGE_TEAM_STD'] ."</td>\n";
					$ret .= "				<td>". $row['DATE_ELIG'] ."</td>\n";
					$ret .= "				<td>". $row['REGULAR'] ."</td>\n";
					$ret .= "				<td>". $row['OT'] ."</td>\n";
					$ret .= "				<td class='" . $trClass . "'>". $incent_dollar ."</td>\n";
					$ret .= "				<td class='" . $trClass . "'>". $row['VAC'] ."</td>\n";
					$ret .= "				<td class='" . $trClass . "'>". $VacIncDollars ."</td>\n";
					$ret .= "				<td class='" . $trClass . "'>". $row['HOL'] ."</td>\n";
					$ret .= "				<td class='" . $trClass . "'>". $HolIncDollars ."</td>\n";
					$ret .= "				<td class='" . $trClass . "'></td>\n";
					$ret .= "				<td class='" . $trClass . "'></td>\n";
					$ret .= "				<td class='" . $trClass . "'></td>\n";
					$ret .= "		 	<tr>\n";
					$ret .= $ret2;
				}
				$ret .= "		</table>\n";
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>