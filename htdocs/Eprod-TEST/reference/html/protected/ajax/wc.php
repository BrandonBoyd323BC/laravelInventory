<?php

	//error_log("TEST");

	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	//PrintHeader('','default.css');
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

				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];

				$ret .= "		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>\n";
				$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";
				$ret .= "		<input type='hidden' id='df' name='df' value='" . $DateFrom . "'>\n";
				$ret .= "		<input type='hidden' id='dt' name='dt' value='" . $DateTo . "'>\n";

				$ret .= " <table class='sample'>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample'>Workcenter</th>\n";
				$ret .= " 		<th class='sample'>Description</th>\n";
				$ret .= " 		<th class='sample'>Earned Mins</th>\n";
				$ret .= " 		<th class='sample'>Adj. Actual Mins</th>\n";
				$ret .= " 		<th class='sample'>Avail Mins</th>\n";
				$ret .= " 	</tr>\n";

				$total_wc_earned_mins = 0;
				$total_wc_actual_mins = 0;
				$total_wc_avail_mins = 0;

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);

				$sql =  "select ";
				$sql .= "	wc.ID_WC, ";
				$sql .= "	wc.DESCR_WC ";
				$sql .= " from ";
				$sql .= "  nsa.tables_loc_dept_wc wc ";
				$sql .= " where ";
				$sql .= "	wc.ID_WC between '1999' and '7999' ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$wc_earned_mins = 0;
					$wc_actual_mins = 0;
					$wc_avail_sec = 0;
					$ret .= " 	<tr>\n";
					$ret .= " 		<td class='sample'>" . $row['ID_WC'] . "</td>\n";
					$ret .= " 		<td class='sample'>" . $row['DESCR_WC'] . "</td>\n";

					$sql2 =  "select ";
					$sql2 .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
					$sql2 .= " 	ltrim(ID_BADGE) as lt_ID_BADGE,";
					$sql2 .= " 	NAME_EMP,";
					$sql2 .= "	* ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCEMMS_EMP ";
					$sql2 .= " where ";
					$sql2 .= " 	TYPE_BADGE = 'X'";
					$sql2 .= " 	and";
					$sql2 .= " 	CODE_ACTV = '0'";
					$sql2 .= " 	and";
					$sql2 .= " 	KEY_HOME_3RD = '" . $row['ID_WC'] . "'";
					$sql2 .= " order by BADGE_NAME asc";
					QueryDatabase($sql2, $results2);


					while ($row2 = mssql_fetch_assoc($results2)) {

						//CALCULATE ACTUAL MINUTES WITHOUT ADJUSTMENTS FOR DATE RANGE
						createTempTable();
						$a_team_members = populateTempTable($DateFrom, $DateTo, $row2['lt_ID_BADGE']);

						/////////////////////
						//QUERY TEMP TABLE FOR INDIVIDUAL INDIRECT HOURS
						/////////////////////
						$tot_indir_sec = 0;
						$tot_team_actual_sec = 0;

						foreach ($a_team_members as $member) {

							$tot_indiv_indir_sec = 0;
							$tot_indiv_day_sec = 0;
							$name = '';
							$pct = 100;
							$nowts = time();

							$DateFromTS = strtotime($DateFrom);
							$DateToTS = strtotime($DateTo);
							$LoopTS = $DateFromTS;

							//error_log("member " . $member . " on team " . $row2['lt_ID_BADGE'] . " of WC " . $row['ID_WC']);




							while ($LoopTS <= $DateToTS) {
								$LoopDT = date('Y-m-d', $LoopTS);
								$onTime = '0';
								$offTime = '235959';

								$sqlx =  "select ";
								$sqlx .= "  convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
								$sqlx .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
								$sqlx .= "  e.NAME_EMP, ";
								$sqlx .= "  e.CODE_USER_1_DC, ";
								$sqlx .= "  tx.* ";
								$sqlx .= " from ";
								$sqlx .= "  #temp_trx tx, ";
								$sqlx .= "  nsa.DCEMMS_EMP e ";
								$sqlx .= " where ";
								$sqlx .= "  ltrim(tx.ID_BADGE) = '". $member . "'";
								$sqlx .= "  and ";
								$sqlx .= "  tx.ID_BADGE = e.ID_BADGE ";
								$sqlx .= "  and ";
								$sqlx .= "  tx.CODE_TRX in (304,305) ";
								$sqlx .= "  and ";
								$sqlx .= "  tx.DATE_TRX = '" . $LoopDT . "' ";
								$sqlx .= " order by ";
								$sqlx .= "  DATE_TRX asc, ";
								$sqlx .= "  ID_BADGE asc, ";
								$sqlx .= "  time_trx asc ";
								QueryDatabase($sqlx, $resultsx);

								while ($rowx = mssql_fetch_assoc($resultsx)) {
									if ($rowx['CODE_TRX'] == '304') {
										$onTime = $rowx['TIME_TRX'];
									}
									if ($rowx['CODE_TRX'] == '305') {
										$offTime = $rowx['TIME_TRX'];
									}
								}

								$sqlx =  "select ";
								$sqlx .= "  convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
								$sqlx .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
								$sqlx .= "  e.NAME_EMP, ";
								$sqlx .= "  e.CODE_USER_1_DC, ";
								$sqlx .= "  tx.* ";
								$sqlx .= " from ";
								$sqlx .= "  #temp_trx tx, ";
								$sqlx .= "  nsa.DCEMMS_EMP e ";
								$sqlx .= " where ";
								$sqlx .= "  ltrim(tx.ID_BADGE) = '". $member . "'";
								$sqlx .= "  and ";
								$sqlx .= "  tx.ID_BADGE = e.ID_BADGE ";
								$sqlx .= "  and ";
								$sqlx .= "  tx.CODE_TRX in (104,105,304,305) ";
								$sqlx .= "  and ";
								$sqlx .= "  tx.DATE_TRX = '" . $LoopDT . "' ";
								$sqlx .= "  and ";
								$sqlx .= "  tx.TIME_TRX between '" . $onTime . "' and '" . $offTime . "' ";
								$sqlx .= " order by ";
								$sqlx .= "  DATE_TRX asc, ";
								$sqlx .= "  ID_BADGE asc, ";
								$sqlx .= "  time_trx asc ";
								QueryDatabase($sqlx, $resultsx);

								while ($rowx = mssql_fetch_assoc($resultsx)) {
									$td_class = GetColorCodeTrx($rowx['CODE_TRX']);
									$trxType = GetStrCodeTrx($rowx['CODE_TRX']);

									if ((trim($rowx['CODE_USER_1_DC']) <> '') && (trim($rowx['CODE_USER_1_DC']) <> '100')) { //<
										$pct = trim($rowx['CODE_USER_1_DC']);
									}

									$prev = '';
									$diff_sec = '';
									$prevdate = '';
									$name = $rowx['NAME_EMP'];
									$curr = $rowx['DATE_TRX3'] . " " . str_pad($rowx['TIME_TRX'],6,"0",STR_PAD_LEFT);
									$currts = strtotime($curr);

									if ($rowx['CODE_TRX'] == '105') {
										$sqlxx =  "select top 1 ";
										$sqlxx .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
										$sqlxx .= "  tx.* ";
										$sqlxx .= " from ";
										$sqlxx .= " #temp_trx tx ";
										$sqlxx .= " where ";
										$sqlxx .= "  tx.CODE_TRX in ('" . ($rowx['CODE_TRX'] - 1) . "') ";
										$sqlxx .= "  and ";
										$sqlxx .= "  ID_BADGE = '" . $rowx['ID_BADGE'] ."' ";
										$sqlxx .= "  and ";
										$sqlxx .= "  DATE_TRX <= '" . $rowx['DATE_TRX'] ."' ";
										$sqlxx .= "  and ";
										$sqlxx .= "  TIME_TRX <= '" . $rowx['TIME_TRX'] ."' ";
										$sqlxx .= " order by ";
										$sqlxx .= "  DATE_TRX desc, ";
										$sqlxx .= "  time_trx desc ";
										QueryDatabase($sqlxx, $resultsxx);

										while ($rowxx = mssql_fetch_assoc($resultsxx)) {
											$prev = $rowxx['DATE_TRX3'] . " " . str_pad($rowxx['TIME_TRX'],6,"0",STR_PAD_LEFT);
											$prevts = strtotime($prev);
											$diff_sec = $currts - $prevts;
											if ($nowts >= $currts) {
												$tot_indiv_indir_sec += $diff_sec;
											}
										}
									}
									//<
									if ($rowx['CODE_TRX'] == '305') {
										$sqlxx =  "select top 1 ";
										$sqlxx .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
										$sqlxx .= "  tx.* ";
										$sqlxx .= " from ";
										$sqlxx .= "  #temp_trx tx ";
										$sqlxx .= " where ";
										$sqlxx .= "  tx.CODE_TRX in ('" . ($rowx['CODE_TRX'] - 1) . "') ";
										$sqlxx .= "  and ";
										$sqlxx .= "  ID_BADGE = '" . $rowx['ID_BADGE'] ."' ";
										$sqlxx .= "  and ";
										$sqlxx .= "  DATE_TRX <= '" . $rowx['DATE_TRX'] ."' ";
										$sqlxx .= "  and ";
										$sqlxx .= "  TIME_TRX <= '" . $rowx['TIME_TRX'] ."' ";
										$sqlxx .= " order by ";
										$sqlxx .= "  DATE_TRX desc, ";
										$sqlxx .= "  time_trx desc ";
										QueryDatabase($sqlxx, $resultsxx);

										while ($rowxx = mssql_fetch_assoc($resultsxx)) {
											$prev = $rowxx['DATE_TRX3'] . " " . str_pad($rowxx['TIME_TRX'],6,"0",STR_PAD_LEFT);
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
										//if ($DEBUG > 0) {
										//	error_log("5");
										//}
										$sql3 =  "select ";
										$sql3 .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
										$sql3 .= "  tx.* ";
										$sql3 .= " from ";
										$sql3 .= "  #temp_trx tx ";
										$sql3 .= " where ";
										$sql3 .= "  tx.CODE_TRX in (301,305) ";
										$sql3 .= "  and ";
										$sql3 .= "  ID_BADGE = '" . $rowx['ID_BADGE'] . "' ";
										$sql3 .= "  and ";
										$sql3 .= "  DATE_TRX = '" . $rowx['DATE_TRX'] . "' ";
										$sql3 .= " order by ";
										$sql3 .= "  DATE_TRX desc ";
										QueryDatabase($sql3, $results3);
										if (mssql_num_rows($results3) == 0) {
											$currts_rnd = roundup15($currts);
											$nowts_rnd = rounddown15($nowts);
											//$diff_sec =  $nowts_rnd - $currts_rnd;
											$diff_sec2 =  $nowts - $currts;
											if ($rowx['CODE_TRX'] == '304') {
												$tot_indiv_day_sec += $diff_sec2;
											}
										}
									}
								}
								$LoopTS = strtotime("+1 days" , $LoopTS);
							}





							$tot_indiv_actual_sec = $tot_indiv_day_sec - $tot_indiv_indir_sec;
							$tot_indir_sec += $tot_indiv_indir_sec;

							//error_log("Adding individual actual " . ($tot_indiv_actual_sec / 60) . " to team actual " . ($tot_team_actual_sec / 60));
							$tot_team_actual_sec += $tot_indiv_actual_sec;
							//error_log("Team Actual " . ($tot_team_actual_sec / 60));
							$wc_avail_sec += $tot_indiv_actual_sec;
						}

						$sql3 =  "select ";
						$sql3 .= "	* ";
						$sql3 .= " from ";
						$sql3 .= " 	nsa.DCAPPROVALS ";
						$sql3 .= " where ";
						$sql3 .= " 	CODE_APP = '200'";
						$sql3 .= " 	and";
						$sql3 .= " 	DATE_APP between '" . $DateFrom . "' and '" . $DateTo . "' ";
						$sql3 .= " 	and";
						$sql3 .= " 	ltrim(BADGE_APP) = '" . $row2['lt_ID_BADGE'] . "'";
						$sql3 .= " order by DATE_ADD desc";
						QueryDatabase($sql3, $results3);
						while ($row3 = mssql_fetch_assoc($results3)) {
							$wc_earned_mins += $row3['EARNED_MINS'];
							$wc_actual_mins += $row3['ACTUAL_MINS'];
						}
						//$wc_avail_mins = ($wc_avail_sec / 60);
					}



					$ret .= " 		<td class='sample'>" . $wc_earned_mins . "</td>\n";
					$ret .= " 		<td class='sample'>" . $wc_actual_mins . "</td>\n";
					$ret .= " 		<td class='sample'>" . ($wc_avail_sec / 60) . "</td>\n";
					$ret .= " 	</tr>\n";
					$total_wc_earned_mins += $wc_earned_mins;
					$total_wc_actual_mins += $wc_actual_mins;
					$total_wc_avail_mins += ($wc_avail_sec / 60);
				}
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample' colspan=2>Totals</th>\n";
				$ret .= " 		<th class='sample'>" . $total_wc_earned_mins . "</th>\n";
				$ret .= " 		<th class='sample'>" . $total_wc_actual_mins . "</th>\n";
				$ret .= " 		<th class='sample'>" . $total_wc_avail_mins . "</th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " </table>\n";
				$ret .= " </br>\n";

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
