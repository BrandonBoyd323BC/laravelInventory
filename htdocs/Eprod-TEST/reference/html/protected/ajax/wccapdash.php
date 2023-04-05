<?php

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$DEBUG = 1;

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print( "		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			if (isset($_POST["action"])) {
				$action = $_POST["action"];

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);

				switch ($action) {
					case "showWcMembers":
						if (isset($_POST["WC"])) {
							$ID_WC = $_POST["WC"];

							if (isset($_POST["divclose"])) {
								$ret .= "		<p onClick=\"disablePopup(". $ID_WC .")\">CLOSE</p>\n";
							}

							$ret .= " <table class='sample'>\n";

							$sql  = " SELECT ";
							$sql .= " wc.ID_WC, ";
							$sql .= " wc.DESCR_WC, ";
							$sql .= " wc.ID_CELL ";
							$sql .= " FROM nsa.tables_loc_dept_wc wc ";
							$sql .= " WHERE wc.ID_WC = '".$ID_WC."' ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr>\n";
								$ret .= " 		<th colspan=4>".$ID_WC." - ".$row['DESCR_WC']."</th>\n";
								$ret .= " 	</tr>\n";
							}

							$ret .= " 	<tr>\n";
							$ret .= " 		<th><font>Team</font></th>\n";
							$ret .= " 		<th><font>Employee</font></th>\n";
							$ret .= " 		<th><font>Status</font></th>\n";
							$ret .= " 		<th><font>Shift</font></th>\n";
							$ret .= " 	</tr>\n";

							$sql  = " SELECT ";
							$sql .= " wc.ID_WC, ";
							$sql .= " wc.DESCR_WC, ";
							$sql .= " wc.ID_CELL, ";
							$sql .= " ltrim(e2.ID_BADGE) as ID_BADGE_TEAM, ";
							$sql .= " e2.NAME_EMP as NAME_TEAM, ";
							$sql .= " ltrim(e1.ID_BADGE) as ID_BADGE_EMPLOYEE, ";
							$sql .= " e1.NAME_EMP as NAME_EMPLOYEE, ";
							$sql .= " e1.STAT_BADGE, ";
							$sql .= " e1.CODE_SHIFT ";
							$sql .= " FROM nsa.DCEMMS_EMP e1 ";
							$sql .= " LEFT JOIN nsa.DCEMMS_EMP e2 ";
							$sql .= " on e1.ID_BADGE_TEAM_STD = e2.ID_BADGE ";
							$sql .= " LEFT JOIN nsa.tables_loc_dept_wc wc ";
							$sql .= " on e2.KEY_HOME_3RD = wc.ID_WC ";
							$sql .= " WHERE e1.STAT_BADGE <> 'T' ";
							$sql .= " and e1.CODE_ACTV = 0 ";
							$sql .= " and e2.CODE_ACTV = 0 ";
							$sql .= " and wc.ID_WC = '".$ID_WC."' ";
							$sql .= " ORDER BY wc.ID_WC, e2.ID_BADGE, e1.ID_BADGE ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr>\n";
								$ret .= " 		<td>".$row['ID_BADGE_TEAM']." - ".$row['NAME_TEAM']."</td>\n";
								$ret .= " 		<td>".$row['ID_BADGE_EMPLOYEE']." - ".$row['NAME_EMPLOYEE']."</td>\n";
								$ret .= " 		<td>".$row['STAT_BADGE']."</td>\n";
								$ret .= " 		<td>".$row['CODE_SHIFT']."</td>\n";
								$ret .= " 	</tr>\n";
							}
							$ret .= " </table>\n";

							if (isset($_POST["divclose"])) {
								$ret .= "		<p onClick=\"disablePopup(". $ID_WC .")\">CLOSE</p>\n";
							}
						}
					break;

					case "buildDash":
						if (isset($_POST["dt"]) && isset($_POST["effPct"]) && isset($_POST["mode"]) && isset($_POST["targetDays"])) {
							$DateDue = $_POST["dt"];
							$EffPct = $_POST["effPct"];
							$Mode = $_POST["mode"];
							$TargetDays = $_POST["targetDays"];

							$tot_ID_CELL = 0;
							$tot_no_mems_1st = 0;
							$tot_no_mems_1stPT = 0;
							$tot_no_mems_2ndFT = 0;
							$tot_no_mems_2ndPT = 0;
							$tot_no_mems_1st_MAX = 0;
							$tot_no_mems_2ndFT_MAX = 0;
							$tot_no_mems_2ndPT_MAX = 0;

							$tot_no_inac = 0;
							$tot_no_inac_1st = 0;
							$tot_no_inac_2nd = 0;
							$tot_no_inac_training = 0;
							$tot_act_days = 0;
							$tot_plan_days = 0;
							$tot_ready_days = 0;
							$tot_rel_days = 0;
							$tot_star_days = 0;
							$tot_days_out = 0;
							$wc_tot_days = 0;
							$tot_tot_days = 0;
							$tot_act_snot_done = 0;
							$tot_rel_snot_done = 0;
							$tot_plan_snot_done = 0;
							$tot_ready_snot_done = 0;
							$tot_star_snot_done = 0;
							$total_capacity = 0;
							$total_capacity_ID_CELL = 0;
							$totalSumEarnedMins = 0;
							$totalSumAvailMins = 0;

							$grp7100and7200NoMembers_1st = 0;
							$grp7100and7200NoMembers_1stPT = 0;
							$grp7100and7200NoMembers_2ndFT = 0;
							$grp7100and7200NoMembers_2ndPT = 0;
							$grp7100and7200NoMembers_1stTemp = 0;
							$grp7100and7200NoMembers_2ndTemp = 0;
							$grp7100and7200NoMembers_1st_MAX = 0;
							$grp7100and7200NoMembers_1stPT_MAX = 0;
							$grp7100and7200NoMembers_2ndFT_MAX = 0;
							$grp7100and7200NoMembers_2ndPT_MAX = 0;

							$no_7100_7200_inac = 0;
							$no_7100_7200_inac_1st = 0;
							$no_7100_7200_inac_2nd = 0;
							$grp7100and7200A = 0;
							$grp7100and7200R = 0;
							$grp7100and7200P = 0;
							$grp7100and7200S = 0;
							$grp7100and7200ALL = 0;
							$grp7100and7200SumEarnedMins = 0;
							$grp7100and7200SumAvailMins = 0;

							$grp7100and7200Id_Cell = 0;

							$ret .= "		<h5>Cutoff Due Date: " . $DateDue . "<br>Run On: " . date('Y-m-d g:i a') ."</h5>\n";

							$sql = "SET ANSI_NULLS ON";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS ON";
							QueryDatabase($sql, $results);

							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<th>Workcenter</th>\n";
							if ($_POST["effPct"] == 'AVG') {
								$ret .= " 		<th>3 Week<br>%</th>\n";
							}
							$ret .= " 		<th>Capacity<br>Per Shift</th>\n";
							//$ret .= " 		<th># 1st Shift<br>Members</th>\n";
							$ret .= " 		<th># 1st Shift<br>Full Time<br>Members</th>\n";
							$ret .= " 		<th># 2nd Shift<br>Full Time<br>Members</th>\n";
							$ret .= " 		<th># Part Time<br>Members<br>1st - 2nd</th>\n";
							$ret .= " 		<th># Inactive<br>1st - 2nd</th>\n";
							if ($Mode == 'OperStatus') {
								$ret .= " 		<th>Planned<br>Days</th>\n";
								$ret .= " 		<th>Ready<br>Days</th>\n";
								$ret .= " 		<th>Active<br>Days</th>\n";
							} else {
								$ret .= " 		<th>Allocated<br>Days</th>\n";
								$ret .= " 		<th>Released<br>Days</th>\n";
								$ret .= " 		<th>Started<br>Days</th>\n";
							}

							$ret .= " 		<th>Total<br>Days</th>\n";
							$ret .= " 		<th>Total Days<br>at Capacity</th>\n";
							$ret .= " 	</tr>\n";

							$sql  = "SELECT ";
							$sql .= " e.KEY_HOME_3RD as ID_WC, ";
							$sql .= " wc.DESCR_WC, ";
							$sql .= " wc.ID_CELL, ";
							$sql .= " isnull(CREW_1ST.WC_1ST_CREW_SIZE,0) as WC_1ST_CREW_SIZE, ";
							$sql .= " isnull(CREW_2FT.WC_2FT_CREW_SIZE,0) as WC_2FT_CREW_SIZE, ";
							$sql .= " isnull(CREW_2PT.WC_2PT_CREW_SIZE,0) as WC_2PT_CREW_SIZE, ";
							$sql .= " SUM(a.ACTUAL_MINS) as SUM_ACTUAL_MINS, ";
							$sql .= " SUM(a.EARNED_MINS) as SUM_EARNED_MINS, ";
							$sql .= " SUM(a.AVAIL_MINS) as SUM_AVAIL_MINS, ";
							$sql .= " SUM(a.INDIR_MINS) as SUM_INDIR_MINS, ";
							$sql .= " SUM(a.SAMPLE_MINS) as SUM_SAMPLE_MINS ";
							$sql .= " FROM nsa.DCAPPROVALS a ";
							$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
							$sql .= " on ltrim(a.BADGE_APP) = ltrim(e.ID_BADGE) ";
							$sql .= " and e.CODE_ACTV = 0 ";
							$sql .= " LEFT JOIN nsa.tables_loc_dept_wc wc ";
							$sql .= " on e.KEY_HOME_3RD = wc.ID_WC ";
//							$sql .= " LEFT JOIN ( ";
//							$sql .= " 	select sum(convert(int, e2.ID_EMP)) as SUM_CREW_SIZE, e2.KEY_HOME_3RD from nsa.DCEMMS_EMP e2 ";
//							$sql .= " 	where e2.CODE_ACTV = 0 and e2.TYPE_BADGE = 'X' group by e2.KEY_HOME_3RD ";
//							$sql .= "  ) wc_crew  on e.KEY_HOME_3RD = wc_crew.KEY_HOME_3RD ";

							$sql .= " LEFT JOIN ( ";
							$sql .= "  SELECT ";
							$sql .= "  CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' ";
							$sql .= "  ELSE e2.CODE_SHIFT ";
							$sql .= "  END as CODE_SHIFT, ";
							$sql .= "  e2.KEY_HOME_3RD, ";
							$sql .= "  sum(convert(int, e2.ID_EMP)) as WC_1ST_CREW_SIZE ";
							$sql .= "  FROM nsa.DCEMMS_EMP e2 ";
							$sql .= "  where e2.CODE_ACTV = 0 ";
							$sql .= "  and e2.TYPE_BADGE = 'X' ";
							$sql .= "  and CASE WHEN e2.CODE_SHIFT like '1%' AND e2.CODE_SHIFT not in ('1TE','1PT') THEN '1ST' WHEN e2.CODE_SHIFT like '2D' THEN '2PT' ELSE e2.CODE_SHIFT END = '1ST' ";
							$sql .= "  GROUP BY e2.KEY_HOME_3RD, CODE_SHIFT ";
							$sql .= "  ) CREW_1ST on e.KEY_HOME_3RD = CREW_1ST.KEY_HOME_3RD ";

							$sql .= " LEFT JOIN ( ";
							$sql .= "  SELECT ";
							$sql .= "  CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' WHEN e2.CODE_SHIFT like '2TE' THEN '2FT' ";
							$sql .= "  ELSE e2.CODE_SHIFT ";
							$sql .= "  END as CODE_SHIFT, ";
							$sql .= "  e2.KEY_HOME_3RD, ";
							$sql .= "  sum(convert(int, e2.ID_EMP)) as WC_2FT_CREW_SIZE ";
							$sql .= "  FROM nsa.DCEMMS_EMP e2 ";
							$sql .= "  where e2.CODE_ACTV = 0 ";
							$sql .= "  and e2.TYPE_BADGE = 'X' ";
							$sql .= "  and CASE WHEN e2.CODE_SHIFT like '1%' AND e2.CODE_SHIFT not in ('1TE','1PT') THEN '1ST' WHEN e2.CODE_SHIFT like '2D' THEN '2PT' WHEN e2.CODE_SHIFT like '2TE' THEN '2FT' ELSE e2.CODE_SHIFT END = '2FT' ";
							$sql .= "  GROUP BY e2.KEY_HOME_3RD, CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' WHEN e2.CODE_SHIFT like '2TE' THEN '2FT' ELSE e2.CODE_SHIFT END  ";
							$sql .= "  ) CREW_2FT on e.KEY_HOME_3RD = CREW_2FT.KEY_HOME_3RD ";

							$sql .= " LEFT JOIN ( ";
							$sql .= "  SELECT ";
							$sql .= "  CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' ";
							$sql .= "  ELSE e2.CODE_SHIFT ";
							$sql .= "  END as CODE_SHIFT, ";
							$sql .= "  e2.KEY_HOME_3RD, ";
							$sql .= "  sum(convert(int, e2.ID_EMP)) as WC_2PT_CREW_SIZE ";
							$sql .= "  FROM nsa.DCEMMS_EMP e2 ";
							$sql .= "  where e2.CODE_ACTV = 0 ";
							$sql .= "  and e2.TYPE_BADGE = 'X' ";
							$sql .= "  and CASE WHEN e2.CODE_SHIFT like '1%' AND e2.CODE_SHIFT not in ('1TE','1PT') THEN '1ST' WHEN e2.CODE_SHIFT like '2D' THEN '2PT' ELSE e2.CODE_SHIFT END = '2PT' ";
							$sql .= "  GROUP BY e2.KEY_HOME_3RD, CODE_SHIFT ";
							$sql .= "  ) CREW_2PT on e.KEY_HOME_3RD = CREW_2PT.KEY_HOME_3RD ";

							$sql .= " WHERE a.CODE_APP = 200 ";
							$sql .= " and a.DATE_APP > DATEADD(week, -3, GETDATE()) ";
							$sql .= " and wc.ID_WC between '1999' and '7999' ";

							//$sql .= " and wc.ID_WC = '7725' ";

							$sql .= " and wc.ID_WC not in ('7994','7995','7996','7997','7998') ";//excluding training WC since it will be included under the total

							$sql .= " and wc.ID_WC = '2500' ";

							$sql .= " GROUP BY e.KEY_HOME_3RD, wc.DESCR_WC, wc.ID_CELL, CREW_1ST.WC_1ST_CREW_SIZE, CREW_2FT.WC_2FT_CREW_SIZE, CREW_2PT.WC_2PT_CREW_SIZE ";
							$sql .= " ORDER BY e.KEY_HOME_3RD asc ";
							QueryDatabase($sql, $results);
							error_log($sql);

							while ($row = mssql_fetch_assoc($results)) {
								//////////////
								//WORKCENTER
								//////////////

								//$tot_ID_CELL += $row['ID_CELL'];
								$tot_no_mems_1st_MAX += $row['WC_1ST_CREW_SIZE'];
								$tot_no_mems_2ndFT_MAX += $row['WC_2FT_CREW_SIZE'];
								$tot_no_mems_2ndPT_MAX += $row['WC_2PT_CREW_SIZE'];

								$no_mems_wc_1st = 0;
								$no_mems_wc_1stPT = 0;
								$no_mems_wc_2ndFT = 0;
								$no_mems_wc_2ndPT = 0;
								$no_mems_wc_1stTemp = 0;
								$no_mems_wc_2ndTemp = 0;

								$no_inac = 0;
								$no_inac_1st = 0;
								$no_inac_2nd = 0;
								$cap_wc = 0;

								if ($_POST["effPct"] == 'AVG') {
									$wc_12w_Avg = round(($row['SUM_EARNED_MINS']/$row['SUM_AVAIL_MINS']) * 100,2);
									//error_log("WC: ".$row['ID_WC']." 12wAvg: " . $wc_12w_Avg);
									$EffPct = $wc_12w_Avg;

									///////////////////////////////////
									/// HARDCODE EffPct for Mask Sewing since they won't claim minutes in TCM
									///////////////////////////////////
									if ($row['ID_WC'] == '7995') {
										$EffPct = 90;
									}


								}
								$cap_wc_ID_CELL = (($row['ID_CELL'] * (450*2)) * ($EffPct/100));
								error_log("WC: ".$row['ID_WC']." 12wAvg: ".$wc_12w_Avg." ID_CELL: ".$row['ID_CELL']." CELL_DAYMINS: ".($row['ID_CELL'] * (450*2))." cap_wc_ID_CELL: ".$cap_wc_ID_CELL);


								if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
									$grp7100and7200SumEarnedMins += $row['SUM_EARNED_MINS'];
									$grp7100and7200SumAvailMins += $row['SUM_AVAIL_MINS'];

									$grp7100and7200NoMembers_1st_MAX += $row['WC_1ST_CREW_SIZE'];
									$grp7100and7200NoMembers_2ndFT_MAX += $row['WC_2FT_CREW_SIZE'];
									$grp7100and7200NoMembers_2ndPT_MAX += $row['WC_2PT_CREW_SIZE'];

									$grp7100and7200Id_Cell += $row['ID_CELL'];
								}

								$totalSumAvailMins += $row['SUM_AVAIL_MINS'];
								$totalSumEarnedMins += $row['SUM_EARNED_MINS'];

								$sql2  =  "SELECT ";
								$sql2 .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
								$sql2 .= " ID_BADGE, ";
								$sql2 .= " NAME_EMP ";
								$sql2 .= " FROM nsa.DCEMMS_EMP ";
								$sql2 .= " WHERE TYPE_BADGE = 'X' ";
								$sql2 .= " and CODE_ACTV = '0' ";
								$sql2 .= " and KEY_HOME_3RD = '" . $row['ID_WC'] . "' ";
								$sql2 .= " ORDER BY BADGE_NAME asc";
								QueryDatabase($sql2, $results2);
								while ($row2 = mssql_fetch_assoc($results2)) {
									////////////////
									////FIRST SHIFT
									////////////////
									$sql3 = "SELECT ";
									$sql3 .= " STAT_BADGE, count(*) as no_mems ";
									$sql3 .= " FROM nsa.DCEMMS_EMP ";
									$sql3 .= " WHERE TYPE_BADGE = 'E' ";
									$sql3 .= " and CODE_ACTV = '0' ";
									$sql3 .= " and STAT_BADGE in ('A','I') ";
									$sql3 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row2['ID_BADGE']) . "' ";
									//$sql3 .= " and ltrim(CODE_SHIFT) like '1_'";
									$sql3 .= " and ltrim(CODE_SHIFT) like '1%' ";
									$sql3 .= " and ltrim(CODE_SHIFT) <> '1TE' ";
									$sql3 .= " GROUP BY STAT_BADGE";
									QueryDatabase($sql3, $results3);
									while ($row3 = mssql_fetch_assoc($results3)) {
										if ($row3['STAT_BADGE'] == 'A') {
											$no_mems = $row3['no_mems'];
											$no_mems_wc_1st += $no_mems;
											$tot_no_mems_1st += $no_mems;

											$cap_team = $no_mems * (450 * ($EffPct/100));
											$cap_wc += $cap_team;
											$total_capacity += $cap_team;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$grp7100and7200NoMembers_1st += $no_mems;
											}
										}
										if ($row3['STAT_BADGE'] == 'I') {
											$no_inac += $row3['no_mems'];
											$no_inac_1st += $row3['no_mems'];
											$tot_no_inac += $row3['no_mems'];
											$tot_no_inac_1st += $row3['no_mems'];

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$no_7100_7200_inac += $row3['no_mems'];
												$no_7100_7200_inac_1st += $row3['no_mems'];
											}
										}
									}
									////////////////
									////FIRST SHIFT - TEMP
									////////////////
									$sql3 = "SELECT ";
									$sql3 .= " STAT_BADGE, count(*) as no_mems ";
									$sql3 .= " FROM nsa.DCEMMS_EMP ";
									$sql3 .= " WHERE TYPE_BADGE = 'E' ";
									$sql3 .= " and CODE_ACTV = '0' ";
									$sql3 .= " and STAT_BADGE in ('A','I') ";
									$sql3 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row2['ID_BADGE']) . "' ";
									//$sql3 .= " and ltrim(CODE_SHIFT) like '1_'";
									$sql3 .= " and ltrim(CODE_SHIFT) = '1TE'";
									$sql3 .= " GROUP BY STAT_BADGE";
									QueryDatabase($sql3, $results3);
									while ($row3 = mssql_fetch_assoc($results3)) {
										if ($row3['STAT_BADGE'] == 'A') {
											$no_mems = $row3['no_mems'];
											$no_mems_wc_1stTemp += $no_mems;
											$tot_no_mems_1stTemp += $no_mems;

											$cap_team = $no_mems * (450 * ($EffPct/100));
											$cap_wc += $cap_team;
											$total_capacity += $cap_team;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$grp7100and7200NoMembers_1stTemp += $no_mems;
											}
										}
										if ($row3['STAT_BADGE'] == 'I') {
											$no_inac += $row3['no_mems'];
											$no_inac_1st += $row3['no_mems'];
											$tot_no_inac += $row3['no_mems'];
											$tot_no_inac_1st += $row3['no_mems'];

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$no_7100_7200_inac += $row3['no_mems'];
												$no_7100_7200_inac_1st += $row3['no_mems'];
											}
										}
									}									
									////////////////
									////2ND SHIFT FULL TIME
									////////////////
									$sql3 = "SELECT ";
									$sql3 .= " STAT_BADGE, count(*) as no_mems ";
									$sql3 .= " FROM nsa.DCEMMS_EMP ";
									$sql3 .= " WHERE TYPE_BADGE = 'E' ";
									$sql3 .= " and CODE_ACTV = '0' ";
									$sql3 .= " and STAT_BADGE in ('A','I') ";
									$sql3 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row2['ID_BADGE']) . "' ";
									//$sql3 .= " and ltrim(CODE_SHIFT) = '2B'";
									$sql3 .= " and ltrim(CODE_SHIFT) in ('2B','2FT')";
									$sql3 .= " GROUP BY STAT_BADGE";
									QueryDatabase($sql3, $results3);
									while ($row3 = mssql_fetch_assoc($results3)) {
										if ($row3['STAT_BADGE'] == 'A') {
											$no_mems = $row3['no_mems'];
											$no_mems_2nd = $row3['no_mems'];
											$no_mems_wc_2ndFT += $no_mems;
											$tot_no_mems_2ndFT += $no_mems;

											$cap_team = $no_mems * (450 * ($EffPct/100));
											$cap_wc += $cap_team;
											$total_capacity += $cap_team;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$grp7100and7200NoMembers_2ndFT += $no_mems;
											}
										}
										if ($row3['STAT_BADGE'] == 'I') {
											$no_inac += $row3['no_mems'];
											$no_inac_2nd += $row3['no_mems'];
											$tot_no_inac += $row3['no_mems'];;
											$tot_no_inac_2nd += $row3['no_mems'];;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$no_7100_7200_inac += $row3['no_mems'];
												$no_7100_7200_inac_2nd += $row3['no_mems'];
											}
										}
									}
									////////////////
									////2ND SHIFT PART TIME
									////////////////
									$sql3 = "SELECT ";
									$sql3 .= " STAT_BADGE, count(*) as no_mems ";
									$sql3 .= " FROM nsa.DCEMMS_EMP ";
									$sql3 .= " WHERE TYPE_BADGE = 'E' ";
									$sql3 .= " and CODE_ACTV = '0' ";
									$sql3 .= " and STAT_BADGE in ('A','I') ";
									$sql3 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row2['ID_BADGE']) . "' ";
									//$sql3 .= " and (ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') ";
									$sql3 .= " and ((ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') OR ltrim(CODE_SHIFT) = '2PT') ";
									$sql3 .= " GROUP BY STAT_BADGE";
									QueryDatabase($sql3, $results3);
									while ($row3 = mssql_fetch_assoc($results3)) {
										if ($row3['STAT_BADGE'] == 'A') {
											$no_mems = $row3['no_mems'];
											$no_mems_wc_2ndPT += $no_mems;
											$tot_no_mems_2ndPT += $no_mems;

											$cap_team = $no_mems * ((450/2) * ($EffPct/100));
											$cap_wc += $cap_team;
											$total_capacity += $cap_team;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$grp7100and7200NoMembers_2ndPT += $no_mems;
											}
										}
										if ($row3['STAT_BADGE'] == 'I') {
											$no_inac += $row3['no_mems'];
											$tot_no_inac += $row3['no_mems'];;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$no_7100_7200_inac += $row3['no_mems'];
											}
										}
									}
									////////////////
									////2ND SHIFT - TEMP
									////////////////
									$sql3 = "SELECT ";
									$sql3 .= " STAT_BADGE, count(*) as no_mems ";
									$sql3 .= " FROM nsa.DCEMMS_EMP ";
									$sql3 .= " WHERE TYPE_BADGE = 'E' ";
									$sql3 .= " and CODE_ACTV = '0' ";
									$sql3 .= " and STAT_BADGE in ('A','I') ";
									$sql3 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row2['ID_BADGE']) . "' ";
									//$sql3 .= " and (ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') ";
									$sql3 .= " and ltrim(CODE_SHIFT) = '2TE' ";
									$sql3 .= " GROUP BY STAT_BADGE";
									QueryDatabase($sql3, $results3);
									while ($row3 = mssql_fetch_assoc($results3)) {
										if ($row3['STAT_BADGE'] == 'A') {
											$no_mems = $row3['no_mems'];
											$no_mems_wc_2ndTemp += $no_mems;
											$tot_no_mems_2ndTemp += $no_mems;

											$cap_team = $no_mems * ((450) * ($EffPct/100));
											$cap_wc += $cap_team;
											$total_capacity += $cap_team;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$grp7100and7200NoMembers_2ndTemp += $no_mems;
											}
										}
										if ($row3['STAT_BADGE'] == 'I') {
											$no_inac += $row3['no_mems'];
											$tot_no_inac += $row3['no_mems'];;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$no_7100_7200_inac += $row3['no_mems'];
											}
										}
									}


								}


								//if ($cap_wc > 0) {
								if ($row['ID_CELL'] > 0) {
									$tot_ID_CELL += $row['ID_CELL'];
									$wc_DaysOut = 0;
									$wc_DaysOut_ID_CELL = 0;

									$wc_Openings_1st = $row['ID_CELL'] - $no_mems_wc_1st;
									$wc_Openings_2nd = $row['ID_CELL'] - $no_mems_wc_2nd;

									$wc_OpeningsMinutes_1st = $wc_Openings_1st * (450 * ($EffPct/100));
									$wc_OpeningsMinutes_2nd = $wc_Openings_2nd * (450 * ($EffPct/100));

									$ret .= " 	<tr class='d1s'>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font ondblclick=\"showWcMembers('".$row['ID_WC']."')\" title='Double Click to see WC/Team Members'>". $row['ID_WC'] . " - " . $row['DESCR_WC'] . "</font>\n";
									$ret .= " 		</td>\n";
									if ($_POST["effPct"] == 'AVG') {

										///////////////////////////////////
										/// HARDCODE EffPct for Mask Sewing since they won't claim minutes in TCM
										///////////////////////////////////
										if ($row['ID_WC'] == '7995') {
											$wc_12w_Avg = 90;
										}										


										$ret .= " 		<td>\n";
										$ret .= "				<font>". $wc_12w_Avg . "</font>\n";
										$ret .= " 		</td>\n";
									}
									$ret .= " 		<td>\n";
									$ret .= "				<font>".$row['ID_CELL']."</font>\n";
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									//$ret .= "				<font>". $no_mems_wc_1st . " - ". $no_mems_wc_2ndFT ."</font>\n";
									if ($no_mems_wc_1stTemp <> 0) {
										$ret .= "				<font>". ($no_mems_wc_1st + $no_mems_wc_1stTemp) . " (". $no_mems_wc_1stTemp .")</font>\n";
									} else {
										$ret .= "				<font>". ($no_mems_wc_1st + $no_mems_wc_1stTemp) . "</font>\n";	
									}
									
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									//$ret .= "				<font>". $no_mems_wc_1stPT . " - " . $no_mems_wc_2ndPT . "</font>\n";
									if ($no_mems_wc_2ndTemp <> 0) {
										$ret .= "				<font>". ($no_mems_wc_2ndFT + $no_mems_wc_2ndTemp) . " (". $no_mems_wc_2ndTemp .")</font>\n";
									} else {
										$ret .= "				<font>". ($no_mems_wc_2ndFT + $no_mems_wc_2ndTemp) . "</font>\n";	
									}									
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font>". $no_mems_wc_1stPT . " - " . $no_mems_wc_2ndPT . "</font>\n";
									$ret .= " 		</td>\n";									
									$ret .= " 		<td>\n";
									$ret .= "				<font>". $no_inac_1st . " - " . $no_inac_2nd . "</font>\n";
									$ret .= " 		</td>\n";

									/////////////////
									// SWITCH based on $Mode
									/////////////////
									switch ($Mode) {
										case "OrdLin":
											/////////////////
											///MODE=='OrdLin'
											/////////////////

											$snot_done = 0;  //GVD 4/5/2019
											$wc_rel_days = 0;

											///////////////////////////////////////
											////Query/Loop for MANUFACTURED without ID_SO
											///////////////////////////////////////
											$sql4  = "SELECT ";
											$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
											$sql4 .= " 0 as O_QTY_CMPL, ";
											$sql4 .= " ol.QTY_OPEN as O_QTY_ORD, ";
											$sql4 .= " ol.ID_SO ";
											$sql4 .= " FROM nsa.CP_ORDLIN ol ";
											$sql4 .= " LEFT JOIN nsa.ITMMAS_LOC il ";
											$sql4 .= " on ol.ID_ITEM = il.ID_ITEM ";
											$sql4 .= " and il.ID_LOC = '10' ";
											$sql4 .= " LEFT JOIN nsa.ROUTMS_OPER rto "; 
											$sql4 .= " on ol.ID_ITEM = rto.ID_ITEM ";
											$sql4 .= " LEFT JOIN nsa.SHPORD_HDR sh ";
											$sql4 .= " on ol.ID_SO = sh.ID_SO ";
											$sql4 .= " and ol.SUFX_SO = sh.SUFX_SO ";
											$sql4 .= " WHERE ol.DATE_PROM <= '" . $DateDue . "' ";
											$sql4 .= " and rto.ID_WC = '" . $row['ID_WC'] . "' ";
											$sql4 .= " and rto.ID_RTE = 'TSS' "; 
											$sql4 .= " and il.FLAG_SOURCE = 'M' ";
											$sql4 .= " and ol.ID_LOC = '10' ";
											$sql4 .= " and sh.ID_SO is NULL ";
											$sql4 .= " ORDER BY ol.ID_ITEM asc ";
											QueryDatabase($sql4, $results4);

											while ($row4 = mssql_fetch_assoc($results4)) {
												$smin = $row4['RTO_HR_MACH_SR'] * 60;
												$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
												$sopentime = ($smin * $sqty);
												$snot_done += $sopentime;
											}

											$sdaysOut = round(($snot_done / $cap_wc), 2);
											if ($cap_wc == 0) {
												$sdaysOut = "Indefinite";
											}

											$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
											if ($cap_wc_ID_CELL == 0) {
												$sdaysOut_ID_CELL = "Indefinite";
											}

											$wc_DaysOut += $sdaysOut;
											$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

											////NONSTOCK GOES DIRECTLY TO RELEASED STATUS
											$tot_rel_snot_done += $snot_done;
											$tot_rel_days += $sdaysOut;
											$wc_rel_days += $sdaysOut;
											$wc_tot_days += $wc_DaysOut;

											if ($DEBUG) {
												error_log("~~~~~~~~~~~~~~~~~~~");
												error_log("smin: ".$smin);
												error_log("sqty: ".$sqty);
												error_log("sopentime: ".$sopentime);
												error_log("snot_done: ".$snot_done);
												error_log("sdaysOut: ".$sdaysOut);
												error_log("sdaysOut_ID_CELL: ".$sdaysOut_ID_CELL);
												error_log("wc_DaysOut: ".$wc_DaysOut);
												error_log("wc_DaysOut_ID_CELL: ".$wc_DaysOut_ID_CELL);
												error_log("tot_rel_snot_done: ".$tot_rel_snot_done);
												error_log("tot_rel_days: ".$tot_rel_days);
												error_log("wc_rel_days: ".$wc_rel_days);
												error_log("wc_tot_days: ".$wc_tot_days);
											}

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$grp7100and7200R += $snot_done;
												$grp7100and7200ALL += $snot_done;
											}

											///////////////////////////////////////
											////Query/Loop for MANUFACTURED with ID_SO
											///////////////////////////////////////
											$arrStatCodes = array("A","R","S");
											foreach($arrStatCodes as $StatCode) {
												$snot_done = 0;

												$sql4  = "SELECT ";
												$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
												$sql4 .= " so.QTY_CMPL as O_QTY_CMPL, ";
												$sql4 .= " so.QTY_ORD as O_QTY_ORD, ";
												$sql4 .= " ol.ID_SO ";
												$sql4 .= " FROM nsa.CP_ORDLIN ol ";
												$sql4 .= " LEFT JOIN nsa.ITMMAS_LOC il ";
												$sql4 .= " on ol.ID_ITEM = il.ID_ITEM ";
												$sql4 .= " and il.ID_LOC = '10' ";
												$sql4 .= " LEFT JOIN nsa.ROUTMS_OPER rto "; 
												$sql4 .= " on ol.ID_ITEM = rto.ID_ITEM ";
												$sql4 .= " LEFT JOIN nsa.SHPORD_HDR sh ";
												$sql4 .= " on ol.ID_SO = sh.ID_SO ";
												$sql4 .= " and ol.SUFX_SO = sh.SUFX_SO ";
												$sql4 .= " LEFT JOIN nsa.SHPORD_OPER so ";
												$sql4 .= " on sh.ID_SO = so.ID_SO ";
												$sql4 .= " and sh.SUFX_SO = so.SUFX_SO ";
												$sql4 .= " and rto.ID_OPER = so.ID_OPER ";
												$sql4 .= " WHERE ol.DATE_PROM <= '" . $DateDue . "' ";
												$sql4 .= " and sh.stat_rec_so in('" . $StatCode . "') ";
												$sql4 .= " and rto.ID_WC = '" . $row['ID_WC'] . "' ";
												$sql4 .= " and rto.ID_RTE = 'TSS' "; 
												$sql4 .= " and il.FLAG_SOURCE = 'M' ";
												$sql4 .= " and ol.ID_LOC = '10' ";
												$sql4 .= " and sh.ID_SO is NOT NULL ";
												$sql4 .= " ORDER BY ol.ID_ITEM asc ";
												QueryDatabase($sql4, $results4);

												while ($row4 = mssql_fetch_assoc($results4)) {
													$smin = $row4['RTO_HR_MACH_SR'] * 60;
													$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
													$sopentime = ($smin * $sqty);
													$snot_done += $sopentime;
												}

												$sdaysOut = round(($snot_done / $cap_wc), 2);
												if ($cap_wc == 0) {
													$sdaysOut = "Indefinite";
												}

												$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
												if ($cap_wc_ID_CELL == 0) {
													$sdaysOut_ID_CELL = "Indefinite";
												}

												$wc_DaysOut += $sdaysOut;
												$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

												switch ($StatCode) {
													case ('A'):
														$tot_act_snot_done += $snot_done;
														$tot_act_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case ('R'):
														$tot_rel_snot_done += $snot_done;
														$tot_rel_days += $sdaysOut;
														$wc_rel_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case('S'):
														$tot_star_snot_done += $snot_done;
														$tot_star_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
												}

												if ($DEBUG) {
													error_log("########################");
													error_log("smin: ".$smin);
													error_log("sqty: ".$sqty);
													error_log("sopentime: ".$sopentime);
													error_log("snot_done: ".$snot_done);
													error_log("sdaysOut: ".$sdaysOut);
													error_log("sdaysOut_ID_CELL: ".$sdaysOut_ID_CELL);
													error_log("wc_DaysOut: ".$wc_DaysOut);
													error_log("wc_DaysOut_ID_CELL: ".$wc_DaysOut_ID_CELL);
													error_log("tot_rel_snot_done: ".$tot_rel_snot_done);
													error_log("tot_rel_days: ".$tot_rel_days);
													error_log("wc_rel_days: ".$wc_rel_days);
													error_log("wc_tot_days: ".$wc_tot_days);
												}

												$sfont_class = GetColorDaysOutDash($sdaysOut);

												if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
													switch ($StatCode) {
														case ('A'):
															$grp7100and7200A += $snot_done;
														break;
														case ('R'):
															$grp7100and7200R += $snot_done;
														break;
														case ('S'):
															$grp7100and7200S += $snot_done;
														break;
													}
													$grp7100and7200ALL += $snot_done;
												}

												if ($StatCode == 'R') {
													$sfont_class = GetColorDaysOutDash($wc_rel_days);
													$ret .= " 		<td>\n";
													$ret .= "				<font class='" . $sfont_class . "'>".$wc_rel_days."</font>\n";
													$ret .= " 		</td>\n";
												} else {
													$ret .= " 		<td>\n";
													$ret .= "				<font class='" . $sfont_class . "'>".$sdaysOut."</font>\n";
													$ret .= " 		</td>\n";
												}
											}
									
										break; //END MODE='OrdLin'


										case 'ALLSO':
											/////////////////
											///MODE=='ALLSO'
											/////////////////
											$arrStatCodes = array("A","R","S");
											foreach($arrStatCodes as $StatCode) {
												$snot_done = 0;

												$sql4  = "SELECT ";
												$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
												$sql4 .= " so.QTY_CMPL as O_QTY_CMPL, ";
												$sql4 .= " so.QTY_ORD as O_QTY_ORD, ";
												$sql4 .= " sh.ID_ITEM_PAR ";
												$sql4 .= " FROM nsa.shpord_hdr sh ";
												$sql4 .= " LEFT JOIN nsa.shpord_oper so ";
												$sql4 .= " on sh.id_so = so.id_so ";
												$sql4 .= " and sh.sufx_so = so.sufx_so ";
												$sql4 .= " LEFT JOIN nsa.routms_oper rto ";
												$sql4 .= " on sh.id_item_par = rto.id_item ";
												$sql4 .= " and so.id_oper = rto.id_oper ";
												$sql4 .= " WHERE sh.stat_rec_so in('" . $StatCode . "') ";
												$sql4 .= " and (sh.date_due_ord<='" . $DateDue . "' OR sh.date_due_ord is null) ";
												$sql4 .= " and so.ID_WC = '" . $row['ID_WC'] . "' ";
												$sql4 .= " and sh.ID_SO <> 'PROD' ";
												$sql4 .= " and sh.ID_SO not like 'SAMPLE%' ";
												$sql4 .= " and rto.ID_RTE = 'TSS' ";
												$sql4 .= " ORDER BY sh.ID_ITEM_PAR ";
												QueryDatabase($sql4, $results4);

												while ($row4 = mssql_fetch_assoc($results4)) {
													$smin = $row4['RTO_HR_MACH_SR'] * 60;
													$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
													$sopentime = ($smin * $sqty);
													$snot_done += $sopentime;
												}

												$sdaysOut = round(($snot_done / $cap_wc), 2);
												if ($cap_wc == 0) {
													$sdaysOut = "Indefinite";
												}

												$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
												if ($cap_wc_ID_CELL== 0) {
													$sdaysOut_ID_CELL= "Indefinite";
												}

												$wc_DaysOut += $sdaysOut;
												$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

												switch ($StatCode) {
													case ('A'):
														$tot_act_snot_done += $snot_done;
														$tot_act_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case ('R'):
														$tot_rel_snot_done += $snot_done;
														$tot_rel_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case('S'):
														$tot_star_snot_done += $snot_done;
														$tot_star_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
												}

												$sfont_class = GetColorDaysOutDash($sdaysOut);

												if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
													switch ($StatCode) {
														case ('A'):
															$grp7100and7200A += $snot_done;
														break;
														case ('R'):
															$grp7100and7200R += $snot_done;
														break;
														case ('S'):
															$grp7100and7200S += $snot_done;
														break;
													}
													$grp7100and7200ALL += $snot_done;
												}

												$ret .= " 		<td>\n";
												$ret .= "				<font class='" . $sfont_class . "'>".$sdaysOut."</font>\n";
												$ret .= " 		</td>\n";
											}
										//}
										break; //END MODE='ALLSO'



										case 'OperStatus':
											/////////////////
											///MODE=='OperStatus'
											/////////////////
											$arrStatCodes = array("P","R","A");
											foreach($arrStatCodes as $StatCode) {
												$snot_done = 0;

												$sql4  = "SELECT ";
												$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
												$sql4 .= " so.QTY_CMPL as O_QTY_CMPL, ";
												$sql4 .= " so.QTY_ORD as O_QTY_ORD, ";
												$sql4 .= " sh.ID_ITEM_PAR ";
												$sql4 .= " FROM nsa.shpord_hdr sh ";
												$sql4 .= " LEFT JOIN nsa.shpord_oper so ";
												$sql4 .= " on sh.id_so = so.id_so ";
												$sql4 .= " and sh.sufx_so = so.sufx_so ";
												$sql4 .= " LEFT JOIN nsa.routms_oper rto ";
												$sql4 .= " on sh.id_item_par = rto.id_item ";
												$sql4 .= " and so.id_oper = rto.id_oper ";
												//$sql4 .= " WHERE sh.stat_rec_so in('" . $StatCode . "') ";
												$sql4 .= " WHERE so.stat_rec_oper in('" . $StatCode . "') ";
												$sql4 .= " and sh.STAT_REC_SO in ('A','R','S') ";
												$sql4 .= " and (sh.date_due_ord<='" . $DateDue . "' OR sh.date_due_ord is null) ";
												$sql4 .= " and so.ID_WC = '" . $row['ID_WC'] . "' ";
												$sql4 .= " and sh.ID_SO <> 'PROD' ";
												$sql4 .= " and sh.ID_SO not like 'SAMPLE%' ";
												$sql4 .= " and rto.ID_RTE = 'TSS' ";
												$sql4 .= " ORDER BY sh.ID_ITEM_PAR ";
												QueryDatabase($sql4, $results4);

												while ($row4 = mssql_fetch_assoc($results4)) {
													$smin = $row4['RTO_HR_MACH_SR'] * 60;
													$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
													$sopentime = ($smin * $sqty);
													$snot_done += $sopentime;
												}

												$sdaysOut = round(($snot_done / $cap_wc), 2);
												if ($cap_wc == 0) {
													$sdaysOut = "Indefinite";
												}

												$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
												if ($cap_wc_ID_CELL== 0) {
													$sdaysOut_ID_CELL= "Indefinite";
												}

												$wc_DaysOut += $sdaysOut;
												$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

												switch ($StatCode) {
													case ('A'):
														$tot_act_snot_done += $snot_done;
														$tot_act_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case ('R'):
														$tot_rel_snot_done += $snot_done;
														$tot_rel_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case('P'):
														$tot_plan_snot_done += $snot_done;
														$tot_plan_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
												}

												$sfont_class = GetColorDaysOutDash($sdaysOut);

												if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
													switch ($StatCode) {
														case ('A'):
															$grp7100and7200A += $snot_done;
														break;
														case ('R'):
															$grp7100and7200R += $snot_done;
														break;
														case ('P'):
															$grp7100and7200P += $snot_done;
														break;
													}
													$grp7100and7200ALL += $snot_done;
												}

												$ret .= " 		<td>\n";
												$ret .= "				<font class='" . $sfont_class . "'>".$sdaysOut."</font>\n";
												$ret .= " 		</td>\n";
											}
										//}
										break; //END MODE='OperStatus'

									}


									$sfont_class = GetColorDaysOutDashBlue($wc_DaysOut);
									$ret .= " 		<td>\n";
									$ret .= "				<font class='" . $sfont_class . "'>".$wc_DaysOut."</font>\n";
									$ret .= " 		</td>\n";

									$sfont_class = GetColorDaysOutDashBlue($wc_DaysOut_ID_CELL);
									$ret .= " 		<td>\n";
									$ret .= "				<font class='" . $sfont_class . "'>".$wc_DaysOut_ID_CELL."</font>\n";
									$ret .= " 		</td>\n";

									if ($wc_DaysOut > $TargetDays) {
										$ret .= " 		<td>\n";
										$ret .= "				<font>Add</font>\n";
										$ret .= " 		</td>\n";
									}


								}
								$ret .= " 	</tr>\n";
								
								/////////////////////////////////////////////////
								// AFTER 7200, Show subtotal for 7100 & 7200
								/////////////////////////////////////////////////
								if ($row['ID_WC'] == '7200') {

									if ($_POST["effPct"] == 'AVG') {
										$grp7100and7200_12w_Avg = round(($grp7100and7200SumEarnedMins/$grp7100and7200SumAvailMins) * 100,2);
										error_log("grp7100and7200_12w_Avg: " . $grp7100and7200_12w_Avg);
										$EffPct = $wc_12w_Avg;
									}
									
									$grp7100and7200Capacity = $grp7100and7200NoMembers_1st * (450 * ($EffPct/100));
									$grp7100and7200Capacity += $grp7100and7200NoMembers_2ndFT * (450 * ($EffPct/100));
									$grp7100and7200Capacity += $grp7100and7200NoMembers_2ndPT * ((450/2) * ($EffPct/100));
									$grp7100and7200CapacityId_Cell = $grp7100and7200Id_Cell * ((450*2) * ($EffPct/100));

									//$cap_team = $no_mems * (450 * ($EffPct/100));
									
									$ret .= " 	<tr class='d0s'>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font><b>7100 & 7200 Combined</b></font>\n";
									$ret .= " 		</td>\n";
									if ($_POST["effPct"] == 'AVG') {
										$ret .= " 		<td>\n";
										$ret .= "				<font><b>". $grp7100and7200_12w_Avg . "</b></font>\n";
										$ret .= " 		</td>\n";
									}
									$ret .= " 		<td>\n";
									$ret .= "				<font><b>". $grp7100and7200Id_Cell . "</b></font>\n";
									$ret .= " 		</td>\n";									
									$ret .= " 		<td>\n";
									//$ret .= "				<font><b>". $grp7100and7200NoMembers_1st . " - " . $grp7100and7200NoMembers_2ndFT . "</b></font>\n";
									if ($grp7100and7200NoMembers_1stTemp <> 0) {
										$ret .= "				<font>". ($grp7100and7200NoMembers_1st + $grp7100and7200NoMembers_1stTemp) . " (". $grp7100and7200NoMembers_1stTemp .")</font>\n";
									} else {
										$ret .= "				<font>". ($grp7100and7200NoMembers_1st + $grp7100and7200NoMembers_1stTemp) . "</font>\n";	
									}									
									
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									//$ret .= "				<font><b>". $grp7100and7200NoMembers_1stPT . " - " . $grp7100and7200NoMembers_2ndPT . "</b></font>\n";
									if ($grp7100and7200NoMembers_2ndTemp <> 0) {
										$ret .= "				<font>". ($grp7100and7200NoMembers_2ndFT + $grp7100and7200NoMembers_2ndTemp) . " (". $grp7100and7200NoMembers_2ndTemp .")</font>\n";
									} else {
										$ret .= "				<font>". ($grp7100and7200NoMembers_2ndFT + $grp7100and7200NoMembers_2ndTemp) . "</font>\n";	
									}		
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font><b>". $grp7100and7200NoMembers_1stPT . " - " . $grp7100and7200NoMembers_2ndPT . "</b></font>\n";
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font><b>". $no_7100_7200_inac_1st . " - " . $no_7100_7200_inac_2nd . "</b></font>\n";
									$ret .= " 		</td>\n";

									if ($Mode == 'OperStatus') {
										$ret .= " 		<td>\n";
										$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200P / $grp7100and7200Capacity), 2)."</b></font>\n";
										$ret .= " 		</td>\n";
										$ret .= " 		<td>\n";
										$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200R / $grp7100and7200Capacity), 2)."</b></font>\n";
										$ret .= " 		</td>\n";
										$ret .= " 		<td>\n";
										$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200A / $grp7100and7200Capacity), 2)."</b></font>\n";
										$ret .= " 		</td>\n";
									} else {
										$ret .= " 		<td>\n";
										$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200A / $grp7100and7200Capacity), 2)."</b></font>\n";
										$ret .= " 		</td>\n";
										$ret .= " 		<td>\n";
										$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200R / $grp7100and7200Capacity), 2)."</b></font>\n";
										$ret .= " 		</td>\n";
										$ret .= " 		<td>\n";
										$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200S / $grp7100and7200Capacity), 2)."</b></font>\n";
										$ret .= " 		</td>\n";
									}

									$ret .= " 		<td>\n";
									$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200ALL / $grp7100and7200Capacity), 2)."</b></font>\n";
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font class='" . $sfont_class . "'><b>".round(($grp7100and7200ALL / $grp7100and7200CapacityId_Cell), 2)."</b></font>\n";
									$ret .= " 		</td>\n";
									$ret .= " 	</tr>\n";
								}
							}


							/////////////////////////////////////////////////////////////
							// TOTAL LINE
							/////////////////////////////////////////////////////////////
							$tot_tot_days2 = round(($tot_act_snot_done+$tot_rel_snot_done+$tot_star_snot_done+$tot_plan_snot_done)/$total_capacity, 2);
							$ret .= " 	<tr class='d0sBold'>\n";
							$ret .= " 		<td>TOTAL</td>\n";
							if ($_POST["effPct"] == 'AVG') {
								$total_12w_Avg = round(($totalSumEarnedMins/$totalSumAvailMins) * 100,2);
								$ret .= " 		<td>".$total_12w_Avg."</td>\n";
								$EffPct = $total_12w_Avg;
							}
							$ret .= " 		<td>".$tot_ID_CELL."</td>\n";
							//$ret .= " 		<td>".$tot_no_mems_1st." - ".$tot_no_mems_2ndFT."</td>\n";
							if ($tot_no_mems_1stTemp <> 0) {
								$ret .= "				<td><font>". ($tot_no_mems_1st + $tot_no_mems_1stTemp) . " (". $tot_no_mems_1stTemp .")</font></td>\n";
							} else {
								$ret .= "				<td><font>". ($tot_no_mems_1st + $tot_no_mems_1stTemp) . "</font></td>\n";	
							}


							//$ret .= " 		<td>".$tot_no_mems_1stPT." - ".$tot_no_mems_2ndPT."</td>\n";
							if ($tot_no_mems_2ndTemp <> 0) {
								$ret .= "				<td><font>". ($tot_no_mems_2ndFT + $tot_no_mems_2ndTemp) . " (". $tot_no_mems_2ndTemp .")</font></td>\n";
							} else {
								$ret .= "				<td><font>". ($tot_no_mems_2ndFT + $tot_no_mems_2ndTemp) . "</font></td>\n";	
							}							
							$ret .= " 		<td>".$tot_no_mems_1stPT." - ".$tot_no_mems_2ndPT."</td>\n";
							//$ret .= " 		<td>".$tot_no_mems_1stTemp." - ".$tot_no_mems_2ndTemp."</td>\n";
							$ret .= " 		<td>".$tot_no_inac_1st." - ".$tot_no_inac_2nd."</td>\n";
							if ($Mode == 'OperStatus') {
								$ret .= " 		<td>".round($tot_plan_snot_done/$total_capacity, 2)."</td>\n";
								$ret .= " 		<td>".round($tot_rel_snot_done/$total_capacity, 2)."</td>\n";
								$ret .= " 		<td>".round($tot_act_snot_done/$total_capacity, 2)."</td>\n";
							} else {
								$ret .= " 		<td>".round($tot_act_snot_done/$total_capacity, 2)."</td>\n";
								$ret .= " 		<td>".round($tot_rel_snot_done/$total_capacity, 2)."</td>\n";
								$ret .= " 		<td>".round($tot_star_snot_done/$total_capacity, 2)."</td>\n";
							}

							$tot_capacity_ID_CELL = $tot_ID_CELL * ((450*2) * ($EffPct/100));
							$tot_tot_days2_ID_CELL = round(($tot_act_snot_done+$tot_rel_snot_done+$tot_star_snot_done+$tot_plan_snot_done)/$tot_capacity_ID_CELL, 2);

							$ret .= " 		<td>".$tot_tot_days2."</td>\n";
							$ret .= " 		<td>".$tot_tot_days2_ID_CELL."</td>\n";
							$ret .= " 	</tr>\n";



							////////////////////////////////////////////////////////////////////////////////////////////////
							///// Training
							///////////////////////////////////////////////////////////////////////////////////////////////
							$sql5  = "SELECT ";
							$sql5 .= " e.KEY_HOME_3RD as ID_WC, ";
							$sql5 .= " wc.DESCR_WC, ";
							$sql5 .= " wc.ID_CELL, ";
							$sql5 .= " isnull(CREW_1ST.WC_1ST_CREW_SIZE,0) as WC_1ST_CREW_SIZE, ";
							$sql5 .= " isnull(CREW_2FT.WC_2FT_CREW_SIZE,0) as WC_2FT_CREW_SIZE, ";
							$sql5 .= " isnull(CREW_2PT.WC_2PT_CREW_SIZE,0) as WC_2PT_CREW_SIZE, ";							
							$sql5 .= " SUM(a.ACTUAL_MINS) as SUM_ACTUAL_MINS, ";
							$sql5 .= " SUM(a.EARNED_MINS) as SUM_EARNED_MINS, ";
							$sql5 .= " SUM(a.AVAIL_MINS) as SUM_AVAIL_MINS, ";
							$sql5 .= " SUM(a.INDIR_MINS) as SUM_INDIR_MINS, ";
							$sql5 .= " SUM(a.SAMPLE_MINS) as SUM_SAMPLE_MINS ";
							$sql5 .= " FROM nsa.DCAPPROVALS a ";
							$sql5 .= " LEFT JOIN nsa.DCEMMS_EMP e ";
							$sql5 .= " on ltrim(a.BADGE_APP) = ltrim(e.ID_BADGE) ";
							$sql5 .= " and e.CODE_ACTV = 0 ";
							$sql5 .= " LEFT JOIN nsa.tables_loc_dept_wc wc ";
							$sql5 .= " on e.KEY_HOME_3RD = wc.ID_WC ";

							$sql5 .= " LEFT JOIN ( ";
							$sql5 .= "  SELECT ";
							$sql5 .= "  CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' ";
							$sql5 .= "  ELSE e2.CODE_SHIFT ";
							$sql5 .= "  END as CODE_SHIFT, ";
							$sql5 .= "  e2.KEY_HOME_3RD, ";
							$sql5 .= "  sum(convert(int, e2.ID_EMP)) as WC_1ST_CREW_SIZE ";
							$sql5 .= "  FROM nsa.DCEMMS_EMP e2 ";
							$sql5 .= "  where e2.CODE_ACTV = 0 ";
							$sql5 .= "  and e2.TYPE_BADGE = 'X' ";
							$sql5 .= "  and CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' WHEN e2.CODE_SHIFT like '2D' THEN '2PT' ELSE e2.CODE_SHIFT END = '1ST' ";
							$sql5 .= "  GROUP BY e2.KEY_HOME_3RD, CODE_SHIFT ";
							$sql5 .= "  ) CREW_1ST on e.KEY_HOME_3RD = CREW_1ST.KEY_HOME_3RD ";

							$sql5 .= " LEFT JOIN ( ";
							$sql5 .= "  SELECT ";
							$sql5 .= "  CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' ";
							$sql5 .= "  ELSE e2.CODE_SHIFT ";
							$sql5 .= "  END as CODE_SHIFT, ";
							$sql5 .= "  e2.KEY_HOME_3RD, ";
							$sql5 .= "  sum(convert(int, e2.ID_EMP)) as WC_2FT_CREW_SIZE ";
							$sql5 .= "  FROM nsa.DCEMMS_EMP e2 ";
							$sql5 .= "  where e2.CODE_ACTV = 0 ";
							$sql5 .= "  and e2.TYPE_BADGE = 'X' ";
							$sql5 .= "  and CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' WHEN e2.CODE_SHIFT like '2D' THEN '2PT' WHEN e2.CODE_SHIFT like '2TE' THEN '2FT' ELSE e2.CODE_SHIFT END = '2FT' ";
							$sql5 .= "  GROUP BY e2.KEY_HOME_3RD, CODE_SHIFT ";
							$sql5 .= "  ) CREW_2FT on e.KEY_HOME_3RD = CREW_2FT.KEY_HOME_3RD ";

							$sql5 .= " LEFT JOIN ( ";
							$sql5 .= "  SELECT ";
							$sql5 .= "  CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' ";
							$sql5 .= "  ELSE e2.CODE_SHIFT ";
							$sql5 .= "  END as CODE_SHIFT, ";
							$sql5 .= "  e2.KEY_HOME_3RD, ";
							$sql5 .= "  sum(convert(int, e2.ID_EMP)) as WC_2PT_CREW_SIZE ";
							$sql5 .= "  FROM nsa.DCEMMS_EMP e2 ";
							$sql5 .= "  where e2.CODE_ACTV = 0 ";
							$sql5 .= "  and e2.TYPE_BADGE = 'X' ";
							$sql5 .= "  and CASE WHEN e2.CODE_SHIFT like '1%' THEN '1ST' WHEN e2.CODE_SHIFT like '2D' THEN '2PT' ELSE e2.CODE_SHIFT END = '2PT' ";
							$sql5 .= "  GROUP BY e2.KEY_HOME_3RD, CODE_SHIFT ";
							$sql5 .= "  ) CREW_2PT on e.KEY_HOME_3RD = CREW_2PT.KEY_HOME_3RD ";

							$sql5 .= " WHERE a.CODE_APP = 200 ";
							$sql5 .= " and a.DATE_APP > DATEADD(week, -3, GETDATE()) ";
							$sql5 .= " and wc.ID_WC in ('7994','7995','7996','7997','7998') ";

							$sql5 .= " and wc.ID_WC in ('7997') ";


							$sql5 .= " GROUP BY e.KEY_HOME_3RD, wc.DESCR_WC, wc.ID_CELL, CREW_1ST.WC_1ST_CREW_SIZE, CREW_2FT.WC_2FT_CREW_SIZE, CREW_2PT.WC_2PT_CREW_SIZE ";
							$sql5 .= " ORDER BY e.KEY_HOME_3RD asc ";
							QueryDatabase($sql5, $results5);

							while ($row = mssql_fetch_assoc($results5)) {
								if ($_POST["effPct"] == 'AVG') {
									$wc_12w_Avg = round(($row['SUM_EARNED_MINS']/$row['SUM_AVAIL_MINS']) * 100,2);
									error_log("WC: ".$row['ID_WC']." 12wAvg: " . $wc_12w_Avg);
									$EffPct = $wc_12w_Avg;
									///////////////////////////////////
									/// HARDCODE EffPct for Mask Sewing since they won't claim minutes in TCM
									///////////////////////////////////
									if ($row['ID_WC'] == '7994' || $row['ID_WC'] == '7995') {
										$EffPct = 90;
									}
								}

								$no_mems_wc_1st_tr = 0;
								$no_mems_wc_1stPT_tr = 0;
								$no_mems_wc_1stTemp_tr = 0;
								$no_mems_wc_2nd_tr = 0;
								$no_mems_wc_2ndFT_tr = 0;
								$no_mems_wc_2ndPT_tr = 0;
								$no_mems_wc_2ndTemp_tr = 0;
								$cap_wc = 0;

								$wc_DaysOut = 0;
								$wc_DaysOut_ID_CELL = 0;


								$sql6  = "SELECT ";
								$sql6 .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
								$sql6 .= " ID_BADGE, ";
								$sql6 .= " NAME_EMP, ";
								$sql6 .= " * ";
								$sql6 .= " FROM nsa.DCEMMS_EMP ";
								$sql6 .= " WHERE TYPE_BADGE = 'X' ";
								$sql6 .= " and CODE_ACTV = '0' ";
								$sql6 .= " and KEY_HOME_3RD = " . $row['ID_WC'] ." ";
								$sql6 .= " ORDER BY BADGE_NAME asc";
								QueryDatabase($sql6, $results6);

								while ($row6 = mssql_fetch_assoc($results6)) {
									////////////////
									////FIRST SHIFT
									////////////////
									$sql7  = "SELECT ";
									$sql7 .= " count(*) as no_mems ";
									$sql7 .= " FROM nsa.DCEMMS_EMP ";
									$sql7 .= " WHERE TYPE_BADGE = 'E' ";
									$sql7 .= " and CODE_ACTV = '0' ";
									$sql7 .= " and STAT_BADGE = 'A' ";
									//$sql7 .= " and ltrim(CODE_SHIFT) not like '2_' ";
									$sql7 .= " and ltrim(CODE_SHIFT) like '1%' ";
									$sql7 .= " and ltrim(CODE_SHIFT) <> '1TE' ";
									$sql7 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row6['ID_BADGE']) . "' ";
									QueryDatabase($sql7, $results7);

									while ($row7 = mssql_fetch_assoc($results7)) {
										$no_mems = $row7['no_mems'];
										$no_mems_wc_1st_tr += $no_mems;

										$cap_team = $no_mems * (450 * ($EffPct/100));
										$cap_wc += $cap_team;
									}
									////////////////
									////FIRST SHIFT - TEMP
									////////////////
									$sql7  = "SELECT ";
									$sql7 .= " count(*) as no_mems ";
									$sql7 .= " FROM nsa.DCEMMS_EMP ";
									$sql7 .= " WHERE TYPE_BADGE = 'E' ";
									$sql7 .= " and CODE_ACTV = '0' ";
									$sql7 .= " and STAT_BADGE = 'A' ";
									$sql7 .= " and ltrim(CODE_SHIFT) = '1TE' ";
									$sql7 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row6['ID_BADGE']) . "' ";
									QueryDatabase($sql7, $results7);

									while ($row7 = mssql_fetch_assoc($results7)) {
										$no_mems = $row7['no_mems'];
										$no_mems_wc_1stTemp_tr += $no_mems;

										$cap_team = $no_mems * (450 * ($EffPct/100));
										$cap_wc += $cap_team;
									}


									////////////////
									////2ND SHIFT FULL TIME
									////////////////
									$sql7  = "SELECT ";
									$sql7 .= " count(*) as no_mems ";
									$sql7 .= " FROM nsa.DCEMMS_EMP ";
									$sql7 .= " WHERE TYPE_BADGE = 'E' ";
									$sql7 .= " and CODE_ACTV = '0' ";
									$sql7 .= " and STAT_BADGE = 'A' ";
									//$sql7 .= " and ltrim(CODE_SHIFT) = '2B' ";
									$sql7 .= " and rtrim(ltrim(CODE_SHIFT)) in ('2B','2FT') ";
									$sql7 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row6['ID_BADGE']) . "' ";
									QueryDatabase($sql7, $results7);

									while ($row7 = mssql_fetch_assoc($results7)) {
										$no_mems = $row7['no_mems'];
										$no_mems_wc_2ndFT_tr += $no_mems;

										$cap_team = $no_mems * (450 * ($EffPct/100));
										$cap_wc += $cap_team;
									}

									////////////////
									////2ND SHIFT PART TIME
									////////////////
									$sql7  = "SELECT ";
									$sql7 .= " count(*) as no_mems ";
									$sql7 .= " FROM nsa.DCEMMS_EMP ";
									$sql7 .= " WHERE TYPE_BADGE = 'E' ";
									$sql7 .= " and CODE_ACTV = '0' ";
									$sql7 .= " and STAT_BADGE = 'A' ";
									//$sql7 .= " and (ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') ";
									$sql7 .= " and ((ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') OR (rtrim(ltrim(CODE_SHIFT)) = '2PT'))";
									$sql7 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row6['ID_BADGE']) . "' ";
									QueryDatabase($sql7, $results7);

									while ($row7 = mssql_fetch_assoc($results7)) {
										$no_mems = $row7['no_mems'];
										$no_mems_wc_2ndPT_tr += $no_mems;

										$cap_team = $no_mems * ((450/2) * ($EffPct/100));
										$cap_wc += $cap_team;
									}

									////////////////
									////2ND SHIFT - TEMP
									////////////////
									$sql7  = "SELECT ";
									$sql7 .= " count(*) as no_mems ";
									$sql7 .= " FROM nsa.DCEMMS_EMP ";
									$sql7 .= " WHERE TYPE_BADGE = 'E' ";
									$sql7 .= " and CODE_ACTV = '0' ";
									$sql7 .= " and STAT_BADGE = 'A' ";
									//$sql7 .= " and (ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') ";
									$sql7 .= " and rtrim(ltrim(CODE_SHIFT)) = '2TE'";
									$sql7 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row6['ID_BADGE']) . "' ";
									QueryDatabase($sql7, $results7);

									while ($row7 = mssql_fetch_assoc($results7)) {
										$no_mems = $row7['no_mems'];
										$no_mems_wc_2ndTemp_tr += $no_mems;

										$cap_team = $no_mems * ((450) * ($EffPct/100));
										$cap_wc += $cap_team;
									}

									////////////////
									////TRAINING INACTIVE
									////////////////
									$sql7  = "SELECT ";
									$sql7 .= " count(*) as no_mems ";
									$sql7 .= " FROM nsa.DCEMMS_EMP ";
									$sql7 .= " WHERE TYPE_BADGE = 'E' ";
									$sql7 .= " and CODE_ACTV = '0' ";
									$sql7 .= " and STAT_BADGE = 'I' ";
									//$sql7 .= " and (ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') ";
									//$sql7 .= " and ((ltrim(CODE_SHIFT) like '2_' AND ltrim(CODE_SHIFT) <> '2B') OR (rtrim(ltrim(CODE_SHIFT)) = '2PT'))";
									$sql7 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row6['ID_BADGE']) . "' ";
									QueryDatabase($sql7, $results7);

									while ($row7 = mssql_fetch_assoc($results7)) {
										$no_mems = $row7['no_mems'];
										$tot_no_inac_training += $no_mems;
									}
								}

								//if ($cap_wc > 0 || $row['ID_WC'] == '7996' || $row['ID_WC'] == '7997' || $row['ID_WC'] == '7998') {
								if ($row['ID_CELL'] > 0 || $row['ID_WC'] == '7995' || $row['ID_WC'] == '7996' || $row['ID_WC'] == '7997' || $row['ID_WC'] == '7998') {

									if ($DEBUG) {
										error_log("ID_WC: ".$row['ID_WC']);
										error_log("no_mems_wc_1stTemp_tr: ".$no_mems_wc_1stTemp_tr);
										error_log("no_mems_wc_1st_tr: ".$no_mems_wc_1st_tr);
										error_log("no_mems_wc_1stPT_tr: ".$no_mems_wc_1stPT_tr);
										error_log("no_mems_wc_2ndTemp_tr: ".$no_mems_wc_2ndTemp_tr);
										error_log("no_mems_wc_2ndFT_tr: ".$no_mems_wc_2ndFT_tr);
										error_log("no_mems_wc_2ndPT_tr: ".$no_mems_wc_2ndPT_tr);
										error_log("tot_no_inac_training: ".$tot_no_inac_training);
									}									

									$wc_DaysOut = 0;
									$ret .= " 	<tr class='d1s'>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font ondblclick=\"showWcMembers('".$row['ID_WC']."')\">". $row['ID_WC'] . " - " . $row['DESCR_WC'] . "</font>\n";
									$ret .= " 		</td>\n";
									if ($_POST["effPct"] == 'AVG') {
										///////////////////////////////////
										/// HARDCODE EffPct for Mask Sewing since they won't claim minutes in TCM
										///////////////////////////////////
										if ($row['ID_WC'] == '7994' || $row['ID_WC'] == '7995') {
											$wc_12w_Avg = 90;
										}

										$ret .= " 		<td>\n";
										$ret .= "				<font>".$wc_12w_Avg."</font>\n";
										$ret .= " 		</td>\n";
									}
									$ret .= " 		<td>\n";
									$ret .= "				<font>".$row['ID_CELL']."</font>\n";
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									//$ret .= "				<font>". $no_mems_wc_1st_tr . " - ".$no_mems_wc_2ndFT_tr."</font>\n";
									if ($no_mems_wc_1stTemp_tr <> 0) {
										$ret .= "				<font>". ($no_mems_wc_1st_tr + $no_mems_wc_1stTemp_tr) . " (". $no_mems_wc_1stTemp_tr .")</font>\n";
									} else {
										$ret .= "				<font>". ($no_mems_wc_1st_tr + $no_mems_wc_1stTemp_tr) . "</font>\n";	
									}
									$ret .= " 		</td>\n";
									//$ret .= " 		<td>\n";
									//$ret .= "				<font>". $no_mems_wc_1st_tr . "</font>\n";
									//$ret .= " 		</td>\n";
									//$ret .= " 		<td>\n";
									//$ret .= "				<font>".$no_mems_wc_2ndFT_tr."</font>\n";
									//$ret .= " 		</td>\n";									
									$ret .= " 		<td>\n";
									//$ret .= "				<font>". $no_mems_wc_1stPT_tr . " - ".$no_mems_wc_2ndPT_tr . "</font>\n";
									if ($no_mems_wc_2ndTemp_tr <> 0) {
										$ret .= "				<font>". ($no_mems_wc_2ndFT_tr + $no_mems_wc_2ndTemp_tr) . " (". $no_mems_wc_2ndTemp_tr .")</font>\n";
									} else {
										$ret .= "				<font>". ($no_mems_wc_2ndFT_tr + $no_mems_wc_2ndTemp_tr) . "</font>\n";	
									}									
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font>". $no_mems_wc_1stPT_tr . " - ".$no_mems_wc_2ndPT_tr . "</font>\n";
									$ret .= " 		</td>\n";
									$ret .= " 		<td>\n";
									$ret .= "				<font>". $tot_no_inac_training . "</font>\n";
									$ret .= " 		</td>\n";
									//$ret .= " 	</tr>\n";



































































									/////////////////
									// SWITCH based on $Mode
									/////////////////
									switch ($Mode) {
										case "OrdLin":
											/////////////////
											///MODE=='OrdLin'
											/////////////////

											$snot_done = 0;  //GVD 4/5/2019
											$wc_rel_days = 0;

											///////////////////////////////////////
											////Query/Loop for MANUFACTURED without ID_SO
											///////////////////////////////////////
											$sql4  = "SELECT ";
											$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
											$sql4 .= " 0 as O_QTY_CMPL, ";
											$sql4 .= " ol.QTY_OPEN as O_QTY_ORD, ";
											$sql4 .= " ol.ID_SO ";
											$sql4 .= " FROM nsa.CP_ORDLIN ol ";
											$sql4 .= " LEFT JOIN nsa.ITMMAS_LOC il ";
											$sql4 .= " on ol.ID_ITEM = il.ID_ITEM ";
											$sql4 .= " and il.ID_LOC = '10' ";
											$sql4 .= " LEFT JOIN nsa.ROUTMS_OPER rto "; 
											$sql4 .= " on ol.ID_ITEM = rto.ID_ITEM ";
											$sql4 .= " LEFT JOIN nsa.SHPORD_HDR sh ";
											$sql4 .= " on ol.ID_SO = sh.ID_SO ";
											$sql4 .= " and ol.SUFX_SO = sh.SUFX_SO ";
											$sql4 .= " WHERE ol.DATE_PROM <= '" . $DateDue . "' ";
											$sql4 .= " and rto.ID_WC = '" . $row['ID_WC'] . "' ";
											$sql4 .= " and rto.ID_RTE = 'TSS' "; 
											$sql4 .= " and il.FLAG_SOURCE = 'M' ";
											$sql4 .= " and ol.ID_LOC = '10' ";
											$sql4 .= " and sh.ID_SO is NULL ";
											$sql4 .= " ORDER BY ol.ID_ITEM asc ";
											QueryDatabase($sql4, $results4);

											while ($row4 = mssql_fetch_assoc($results4)) {
												$smin = $row4['RTO_HR_MACH_SR'] * 60;
												$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
												$sopentime = ($smin * $sqty);
												$snot_done += $sopentime;
											}

											$sdaysOut = round(($snot_done / $cap_wc), 2);
											if ($cap_wc == 0) {
												$sdaysOut = "Indefinite";
											}

											$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
											if ($cap_wc_ID_CELL == 0) {
												$sdaysOut_ID_CELL = "Indefinite";
											}

											$wc_DaysOut += $sdaysOut;
											$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

											////NONSTOCK GOES DIRECTLY TO RELEASED STATUS
											$tot_rel_snot_done += $snot_done;
											$tot_rel_days += $sdaysOut;
											$wc_rel_days += $sdaysOut;
											$wc_tot_days += $wc_DaysOut;

											if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
												$grp7100and7200R += $snot_done;
												$grp7100and7200ALL += $snot_done;
											}

											///////////////////////////////////////
											////Query/Loop for MANUFACTURED with ID_SO
											///////////////////////////////////////
											$arrStatCodes = array("A","R","S");
											foreach($arrStatCodes as $StatCode) {
												$snot_done = 0;

												$sql4  = "SELECT ";
												$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
												$sql4 .= " so.QTY_CMPL as O_QTY_CMPL, ";
												$sql4 .= " so.QTY_ORD as O_QTY_ORD, ";
												$sql4 .= " ol.ID_SO ";
												$sql4 .= " FROM nsa.CP_ORDLIN ol ";
												$sql4 .= " LEFT JOIN nsa.ITMMAS_LOC il ";
												$sql4 .= " on ol.ID_ITEM = il.ID_ITEM ";
												$sql4 .= " and il.ID_LOC = '10' ";
												$sql4 .= " LEFT JOIN nsa.ROUTMS_OPER rto "; 
												$sql4 .= " on ol.ID_ITEM = rto.ID_ITEM ";
												$sql4 .= " LEFT JOIN nsa.SHPORD_HDR sh ";
												$sql4 .= " on ol.ID_SO = sh.ID_SO ";
												$sql4 .= " and ol.SUFX_SO = sh.SUFX_SO ";
												$sql4 .= " LEFT JOIN nsa.SHPORD_OPER so ";
												$sql4 .= " on sh.ID_SO = so.ID_SO ";
												$sql4 .= " and sh.SUFX_SO = so.SUFX_SO ";
												$sql4 .= " and rto.ID_OPER = so.ID_OPER ";
												$sql4 .= " WHERE ol.DATE_PROM <= '" . $DateDue . "' ";
												$sql4 .= " and sh.stat_rec_so in('" . $StatCode . "') ";
												$sql4 .= " and rto.ID_WC = '" . $row['ID_WC'] . "' ";
												$sql4 .= " and rto.ID_RTE = 'TSS' "; 
												$sql4 .= " and il.FLAG_SOURCE = 'M' ";
												$sql4 .= " and ol.ID_LOC = '10' ";
												$sql4 .= " and sh.ID_SO is NOT NULL ";
												$sql4 .= " ORDER BY ol.ID_ITEM asc ";
												QueryDatabase($sql4, $results4);

												while ($row4 = mssql_fetch_assoc($results4)) {
													$smin = $row4['RTO_HR_MACH_SR'] * 60;
													$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
													$sopentime = ($smin * $sqty);
													$snot_done += $sopentime;
												}

												$sdaysOut = round(($snot_done / $cap_wc), 2);
												if ($cap_wc == 0) {
													$sdaysOut = "Indefinite";
												}

												$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
												if ($cap_wc_ID_CELL == 0) {
													$sdaysOut_ID_CELL = "Indefinite";
												}

												$wc_DaysOut += $sdaysOut;
												$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

												switch ($StatCode) {
													case ('A'):
														$tot_act_snot_done += $snot_done;
														$tot_act_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case ('R'):
														$tot_rel_snot_done += $snot_done;
														$tot_rel_days += $sdaysOut;
														$wc_rel_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case('S'):
														$tot_star_snot_done += $snot_done;
														$tot_star_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
												}

												$sfont_class = GetColorDaysOutDash($sdaysOut);

												if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
													switch ($StatCode) {
														case ('A'):
															$grp7100and7200A += $snot_done;
														break;
														case ('R'):
															$grp7100and7200R += $snot_done;
														break;
														case ('S'):
															$grp7100and7200S += $snot_done;
														break;
													}
													$grp7100and7200ALL += $snot_done;
												}

												if ($StatCode == 'R') {
													$sfont_class = GetColorDaysOutDash($wc_rel_days);
													$ret .= " 		<td>\n";
													$ret .= "				<font class='" . $sfont_class . "'>".$wc_rel_days."</font>\n";
													$ret .= " 		</td>\n";
												} else {
													$ret .= " 		<td>\n";
													$ret .= "				<font class='" . $sfont_class . "'>".$sdaysOut."</font>\n";
													$ret .= " 		</td>\n";
												}
											}
									
										break; //END MODE='OrdLin'


										case 'ALLSO':
											/////////////////
											///MODE=='ALLSO'
											/////////////////
											$arrStatCodes = array("A","R","S");
											foreach($arrStatCodes as $StatCode) {
												$snot_done = 0;

												$sql4  = "SELECT ";
												$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
												$sql4 .= " so.QTY_CMPL as O_QTY_CMPL, ";
												$sql4 .= " so.QTY_ORD as O_QTY_ORD, ";
												$sql4 .= " sh.ID_ITEM_PAR ";
												$sql4 .= " FROM nsa.shpord_hdr sh ";
												$sql4 .= " LEFT JOIN nsa.shpord_oper so ";
												$sql4 .= " on sh.id_so = so.id_so ";
												$sql4 .= " and sh.sufx_so = so.sufx_so ";
												$sql4 .= " LEFT JOIN nsa.routms_oper rto ";
												$sql4 .= " on sh.id_item_par = rto.id_item ";
												$sql4 .= " and so.id_oper = rto.id_oper ";
												$sql4 .= " WHERE sh.stat_rec_so in('" . $StatCode . "') ";
												$sql4 .= " and (sh.date_due_ord<='" . $DateDue . "' OR sh.date_due_ord is null) ";
												$sql4 .= " and so.ID_WC = '" . $row['ID_WC'] . "' ";
												$sql4 .= " and sh.ID_SO <> 'PROD' ";
												$sql4 .= " and sh.ID_SO not like 'SAMPLE%' ";
												$sql4 .= " and rto.ID_RTE = 'TSS' ";
												$sql4 .= " ORDER BY sh.ID_ITEM_PAR ";
												QueryDatabase($sql4, $results4);

												while ($row4 = mssql_fetch_assoc($results4)) {
													$smin = $row4['RTO_HR_MACH_SR'] * 60;
													$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
													$sopentime = ($smin * $sqty);
													$snot_done += $sopentime;
												}

												$sdaysOut = round(($snot_done / $cap_wc), 2);
												if ($cap_wc == 0) {
													$sdaysOut = "Indefinite";
												}

												$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
												if ($cap_wc_ID_CELL== 0) {
													$sdaysOut_ID_CELL= "Indefinite";
												}

												$wc_DaysOut += $sdaysOut;
												$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

												switch ($StatCode) {
													case ('A'):
														$tot_act_snot_done += $snot_done;
														$tot_act_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case ('R'):
														$tot_rel_snot_done += $snot_done;
														$tot_rel_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case('S'):
														$tot_star_snot_done += $snot_done;
														$tot_star_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
												}

												$sfont_class = GetColorDaysOutDash($sdaysOut);

												if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
													switch ($StatCode) {
														case ('A'):
															$grp7100and7200A += $snot_done;
														break;
														case ('R'):
															$grp7100and7200R += $snot_done;
														break;
														case ('S'):
															$grp7100and7200S += $snot_done;
														break;
													}
													$grp7100and7200ALL += $snot_done;
												}

												$ret .= " 		<td>\n";
												$ret .= "				<font class='" . $sfont_class . "'>".$sdaysOut."</font>\n";
												$ret .= " 		</td>\n";
											}
										//}
										break; //END MODE='ALLSO'



										case 'OperStatus':
											/////////////////
											///MODE=='OperStatus'
											/////////////////
											$arrStatCodes = array("P","R","A");
											foreach($arrStatCodes as $StatCode) {
												$snot_done = 0;

												$sql4  = "SELECT ";
												$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
												$sql4 .= " so.QTY_CMPL as O_QTY_CMPL, ";
												$sql4 .= " so.QTY_ORD as O_QTY_ORD, ";
												$sql4 .= " sh.ID_ITEM_PAR ";
												$sql4 .= " FROM nsa.shpord_hdr sh ";
												$sql4 .= " LEFT JOIN nsa.shpord_oper so ";
												$sql4 .= " on sh.id_so = so.id_so ";
												$sql4 .= " and sh.sufx_so = so.sufx_so ";
												$sql4 .= " LEFT JOIN nsa.routms_oper rto ";
												$sql4 .= " on sh.id_item_par = rto.id_item ";
												$sql4 .= " and so.id_oper = rto.id_oper ";
												//$sql4 .= " WHERE sh.stat_rec_so in('" . $StatCode . "') ";
												$sql4 .= " WHERE so.stat_rec_oper in('" . $StatCode . "') ";
												$sql4 .= " and sh.STAT_REC_SO in ('A','R','S') ";
												$sql4 .= " and (sh.date_due_ord<='" . $DateDue . "' OR sh.date_due_ord is null) ";
												$sql4 .= " and so.ID_WC = '" . $row['ID_WC'] . "' ";
												$sql4 .= " and sh.ID_SO <> 'PROD' ";
												$sql4 .= " and sh.ID_SO not like 'SAMPLE%' ";
												$sql4 .= " and rto.ID_RTE = 'TSS' ";
												$sql4 .= " ORDER BY sh.ID_ITEM_PAR ";
												QueryDatabase($sql4, $results4);

												while ($row4 = mssql_fetch_assoc($results4)) {
													$smin = $row4['RTO_HR_MACH_SR'] * 60;
													$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
													$sopentime = ($smin * $sqty);
													$snot_done += $sopentime;
												}

												$sdaysOut = round(($snot_done / $cap_wc), 2);
												if ($cap_wc == 0) {
													$sdaysOut = "Indefinite";
												}

												$sdaysOut_ID_CELL = round(($snot_done / $cap_wc_ID_CELL), 2);
												if ($cap_wc_ID_CELL== 0) {
													$sdaysOut_ID_CELL= "Indefinite";
												}

												$wc_DaysOut += $sdaysOut;
												$wc_DaysOut_ID_CELL += $sdaysOut_ID_CELL;

												switch ($StatCode) {
													case ('A'):
														$tot_act_snot_done += $snot_done;
														$tot_act_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case ('R'):
														$tot_rel_snot_done += $snot_done;
														$tot_rel_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
													case('P'):
														$tot_plan_snot_done += $snot_done;
														$tot_plan_days += $sdaysOut;
														$wc_tot_days += $wc_DaysOut;
													break;
												}

												$sfont_class = GetColorDaysOutDash($sdaysOut);

												if ($row['ID_WC'] == '7100' || $row['ID_WC'] == '7200') {
													switch ($StatCode) {
														case ('A'):
															$grp7100and7200A += $snot_done;
														break;
														case ('R'):
															$grp7100and7200R += $snot_done;
														break;
														case ('P'):
															$grp7100and7200P += $snot_done;
														break;
													}
													$grp7100and7200ALL += $snot_done;
												}

												$ret .= " 		<td>\n";
												$ret .= "				<font class='" . $sfont_class . "'>".$sdaysOut."</font>\n";
												$ret .= " 		</td>\n";
											}
										//}
										break; //END MODE='OperStatus'

									}


									$sfont_class = GetColorDaysOutDashBlue($wc_DaysOut);
									$ret .= " 		<td>\n";
									$ret .= "				<font class='" . $sfont_class . "'>".$wc_DaysOut."</font>\n";
									$ret .= " 		</td>\n";

									$sfont_class = GetColorDaysOutDashBlue($wc_DaysOut_ID_CELL);
									$ret .= " 		<td>\n";
									$ret .= "				<font class='" . $sfont_class . "'>".$wc_DaysOut_ID_CELL."</font>\n";
									$ret .= " 		</td>\n";

									if ($wc_DaysOut > $TargetDays) {
										$ret .= " 		<td>\n";
										$ret .= "				<font>Add</font>\n";
										$ret .= " 		</td>\n";
									}

									$ret .= " 	</tr>\n";

								}
							}

							////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							$ret .= " </table>\n";

						}

					break;
				} // END SWITCH

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
