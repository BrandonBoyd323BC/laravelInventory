<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Team Activity Log','default.css','realtime.js');
	//PrintHeader('Team Activity Log','default.css');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["team"]))  {

				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
				$Team = $_POST["team"];

				$GTotUnitsC = 0;
				$GTotStdMin = 0;
				$GTotEarnMin = 0;

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

				print("		<h2>" . $row['NAME_EMP'] ."</h2>\n");
				print("		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>\n");

				createTempTable();
				$a_team_members = populateTempTable($DateFrom, $DateTo, $Team);

				/////////////////////
				//QUERY TEMP TABLE FOR INDIVIDUAL INDIRECT HOURS
				/////////////////////
				$tot_indir_sec = 0;
				$tot_team_actual_sec = 0;
				$inc = 0;
				foreach ($a_team_members as $member) {
					$inc ++;
					if ($odd = $inc%2) {
						$align = 'left';
					} else {
						$align = 'right';
					}

					//print("<script type='text/javascript'>alert('" . $inc . "')</script>");

					/////////////////////
					//QUERY TEMP TABLE TO CALCULATE INDIRECT HOURS
					/////////////////////
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
					$sql .= " order by ";
					$sql .= " 	DATE_TRX asc, ";
					$sql .= " 	ID_BADGE asc, ";
					$sql .= " 	time_trx asc ";
					QueryDatabase($sql, $results);


					print("<div id='div_" . $member . "' name='div_" . $member . "'>\n");
					print("<table class='sample'>\n");
					print("	<th class='sample'>Time Stamp</th>\n");
					print("	<th class='sample'>ID Badge</th>\n");
					print("	<th class='sample'>Code Trx</th>\n");
					print("	<th class='sample'>SO</th>\n");
					print("	<th class='sample'>Duration</th>\n");
					print("	<td id='x_" . $member . "' name='x_" . $member . "' onclick=\"closeDiv('div_" . $member . "')\" TITLE='Remove Table'>X</td>\n");
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

						if ($nowts >= $currts) {
							print("	<tr class='" . $td_class . "'>\n");
							print("		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n");
							print("		<td class='" . $td_class . "'>" . $row['ID_BADGE'] . "</td>\n");
							print("		<td class='" . $td_class . "'>" . $trxType . "</td>\n");
							print("		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n");
							if ($diff_sec <> '') {
								print("		<td class='" . $td_class . "' colspan='2'>" . $diff_sec / 60 . "</td>\n");
							} else {
								print("		<td class='" . $td_class . "' colspan='2'></td>\n");
							}
							print("	</tr>\n");
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

					print("	<tr class='sample'>\n");
					print(" 	<td class='sample' colspan = 6><b>" . $name . "</b></td>");
					print("	</tr>\n");
					print("	<tr class='sample'>\n");
					print("		<td class='sample' colspan = 4><b>Individual Shift Time (minutes)</b></td>\n");
					print("		<td class='sample' colspan = 2><b>" . round($tot_indiv_day_sec / 60,3) . "</b></td>\n");
					print("	</tr>\n");
					print("	<tr class='sample'>\n");
					print("		<td class='sample' colspan = 4><b>Total Individual Indirect Time (minutes)</b></td>\n");
					print("		<td class='sample' colspan = 2><b>" . round($tot_indiv_indir_sec / 60,3) . "</b></td>\n");
					print("	</tr>\n");
					print("	<tr class='sample'>\n");
					print("		<td class='" . $cls . "' colspan = 4><b>" . $txt ."</b></td>\n");
					print("		<td class='" . $cls . "' colspan = 2><b>" . round($tot_indiv_actual_sec / 60,3) . "</b></td>\n");
					print("	</tr>\n");
					print("</table>\n");
					print("	</br>\n");
					print("</div>\n");

					$tot_indir_sec += $tot_indiv_indir_sec;
					$tot_team_actual_sec += $tot_indiv_actual_sec;

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

				print("<div id='div_team' name='div_team'>\n");
				print("<table class='sample'>\n");
				print("	<th class='sample'>Time Stamp</th>\n");
				print("	<th class='sample'>Name</th>\n");
				print("	<th class='sample'>ID Badge</th>\n");
				print("	<th class='sample'>Team Badge</th>\n");
				print("	<th class='sample'>Code Trx</th>\n");
				print("	<th class='sample'>Duration</th>\n");
				print("	<th class='sample'>Actual</th>\n");
				print("	<td id='x_team' name='x_team' onclick=\"closeDiv('div_team')\" TITLE='Remove Table'>X</td>\n");
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

					print("	<tr class='" . $td_class . "'>\n");
					print("		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['NAME_EMP'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['ID_BADGE'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['ID_BADGE_TEAM'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $trxType . "</td>\n");
					print("		<td class='" . $td_class . "' >" . round($diff_sec / 60,3) . "</td>\n");
					print("	</tr>\n");
				}

				print("	<tr>\n");
				print("		<td colspan='6'></td>\n");
				print("	</tr>\n");
				print("	<tr>\n");
				print("		<td colspan='5'><b>Total Team Day Minutes</b></td>\n");
				print("		<td><b>" . round($tot_day_sec / 60,3) . "</b></td>\n");
				print("		<td class='sample' colspan=2><b>" . round($tot_team_actual_sec / 60,3) . "</b></td>\n");
				print("</table>\n");
				print("	</br>\n");
				print("	</div>\n");




				/////////////////////VERSION 3
				//QUERY TEMP TABLE FOR SHOP ORDERS
				/////////////////////VERSION 3

				$sql =  "select distinct ";
				$sql .= "	(CONVERT(varchar(8), DATE_TRX, 112) + '_' + CONVERT(VARCHAR,ID_SO) + '_' + CONVERT(VARCHAR,SUFX_SO) + '_' + CONVERT(VARCHAR,ID_OPER) + '_' + CONVERT(VARCHAR,CODE_ACTV)) as BIGID, ";
				$sql .=  "	DATE_TRX, ";
				//$sql .=  "	TIME_TRX, ";
				$sql .=  "	ID_SO, ";
				$sql .=  "	SUFX_SO, ";
				$sql .=  "	ID_OPER, ";
				$sql .=  "	CODE_ACTV ";
				$sql .= " from ";
				$sql .= "	#temp_trx tx ";
				$sql .= " where ";
				$sql .= " 	tx.CODE_TRX in (102,103) ";
				$sql .= " order by ";
				$sql .= " 	BIGID asc, ";
				$sql .= " 	DATE_TRX asc, ";
				//$sql .= " 	TIME_TRX asc, ";
				$sql .= " 	ID_SO asc, ";
				$sql .= "	ID_OPER asc ";
				QueryDatabase($sql, $results);


				print("<div id='div_so2' name='div_so2'>\n");
				print("<table class='sample'>\n");
				print("	<th class='sample'>StartTime</th>\n");
				print("	<th class='sample'>EndTime</th>\n");
				print("	<th class='sample'>SO</th>\n");
				print("	<th class='sample'>Sufx</th>\n");
				print("	<th class='sample'>Oper</th>\n");
				print("	<th class='sample'>Actv</th>\n");
				print("	<th class='sample'>Item #</th>\n");
				print("	<th class='sample'>Qty Ord</th>\n");
				print("	<th class='sample'>Qty Rem</th>\n");
				print("	<th class='sample'>Qty Cmp</th>\n");
				print("	<th class='sample'>Stand Mins</th>\n");
				print("	<th class='sample'>Earned Mins</th>\n");
				print("	<td id='x_so' name='x_so' onclick=\"closeDiv('div_so2')\" TITLE='Remove Table'>X</td>\n");

				$tot_qty = 0;
				$min_earned = 0;
				$tot_min_earned = 0;

				while ($row = mssql_fetch_assoc($results)) {

					$sql2 =  "select ";
					$sql2 .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
					$sql2 .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
					$sql2 .= "	h.ID_ITEM_PAR, ";
					$sql2 .= "	o.HR_MACH_SR as HR_MACH_SF, ";
					$sql2 .= "	so.QTY_ORD, ";
					$sql2 .= "	tx.* ";
					$sql2 .= " from ";
					$sql2 .= "	#temp_trx tx, ";
					$sql2 .= " 	nsa.SHPORD_HDR h, ";
					$sql2 .= "	nsa.SHPORD_OPER so,";
					$sql2 .= "	nsa.ROUTMS_OPER o ";
					$sql2 .= " where ";
					$sql2 .= " 	tx.CODE_TRX in (102,103) ";
					$sql2 .= " 	and ";
					$sql2 .= " 	tx.ID_SO = h.ID_SO ";
					$sql2 .= " 	and ";
					$sql2 .= " 	h.id_item_par=o.id_item ";
					$sql2 .= " 	and ";
					$sql2 .= " 	so.id_oper=o.id_oper ";
					$sql2 .= " 	and ";
					$sql2 .= " 	so.ID_SO = h.ID_SO ";
					$sql2 .= " 	and ";
					$sql2 .= " 	tx.SUFX_SO = h.SUFX_SO ";
					$sql2 .= " 	and ";
					$sql2 .= " 	tx.SUFX_SO = so.SUFX_SO ";
					$sql2 .= " 	and ";
					$sql2 .= " 	so.ID_OPER = tx.ID_OPER ";

					$sql2 .= " 	and ";
					$sql2 .= " 	tx.DATE_TRX = '" . $row['DATE_TRX'] . "'" ;
					$sql2 .= " 	and ";
					$sql2 .= " 	tx.ID_SO = '" . $row['ID_SO'] . "'" ;
					$sql2 .= " 	and ";
					$sql2 .= " 	tx.SUFX_SO = '" . $row['SUFX_SO'] . "'" ;
					$sql2 .= " 	and ";
					$sql2 .= " 	tx.ID_OPER = '" . $row['ID_OPER'] . "'" ;
					$sql2 .= " 	and ";
					$sql2 .= " 	tx.CODE_ACTV = '" . $row['CODE_ACTV'] . "'" ;

					$sql2 .= " order by ";
					$sql2 .= " 	DATE_TRX asc, ";
					$sql2 .= " 	time_trx asc, ";
					$sql2 .= " 	ID_ITEM_PAR asc, ";
					$sql2 .= " 	ID_SO asc, ";
					$sql2 .= "	ID_OPER asc ";
					QueryDatabase($sql2, $results2);


					$start = '';
					$stop = '';
					$ID_SO = '';
					$SUFX_SO = '';
					$ID_OPER = '';
					$CODE_ACTV = '';
					$ID_ITEM_PAR = '';
					$MIN_MACH_SF = '';
					$QO = '';
					$QR = '';
					$QC = '';
					$EMin = '';

					while ($row2 = mssql_fetch_assoc($results2)) {
						$CODE_ACTV = $row2['CODE_ACTV'];
						$ID_SO = $row2['ID_SO'];
						$SUFX_SO = $row2['SUFX_SO'];
						$ID_OPER = $row2['ID_OPER'];
						$ID_ITEM_PAR = $row2['ID_ITEM_PAR'];
						$QO = $row2['QTY_ORD'];
						$MIN_MACH_SF = $row2['HR_MACH_SF'] * 60;
						if ($MIN_MACH_SF == 0) {
							$MIN_MACH_SF = '';
						}

						if ($row2['CODE_TRX'] == '102') {
							$curr = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
							$currts = strtotime($curr);
							$start = date('m/d/Y h:i:s A',$currts);
						}

						if ($row2['CODE_TRX'] == '103') {
							$curr = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
							$currts = strtotime($curr);
							$stop = date('m/d/Y h:i:s A',$currts);

							$QC = $row2['QTY_GOOD'];
							$EMin = $row2['QTY_GOOD'] * ($row2['HR_MACH_SF'] * 60);

							$sql3  = "select ";
							$sql3 .= "	sum(nz.qty_good) as sum_qty_good";
							$sql3 .= " from ";
							$sql3 .= "	nsa.DCUTRX_NONZERO_PERM nz ";
							$sql3 .= " where ";
							$sql3 .= " 	ltrim(nz.ID_SO) = '" . trim($row2['ID_SO']) ."' ";
							$sql3 .= " 	and ";
							$sql3 .= " 	nz.SUFX_SO = '" . $row2['SUFX_SO'] ."' ";
							$sql3 .= " 	and ";
							$sql3 .= " 	nz.ID_OPER = '" . $row2['ID_OPER'] ."' ";
							$sql3 .= " 	and ";
							$sql3 .= " 	nz.FLAG_DEL = '' ";
							QueryDatabase($sql3, $results3);

							while ($row3 = mssql_fetch_assoc($results3)) {
								$sum_qty_good = $row3['sum_qty_good'];
								$QR = $QO - $sum_qty_good;
							}
							$tot_qty += $row2['QTY_GOOD'];
							$tot_min_earned += $EMin;
						}
					}

					print("		<tr class='sample'>\n");
					print("			<td class='sample'>" . $start . "</div></td>\n");
					print("			<td class='sample'>" . $stop . "</td>\n");
					print("			<td class='sample'>" . $ID_SO . "</td>\n");
					print("			<td class='sample'>" . $SUFX_SO . "</td>\n");
					print("			<td class='sample'>" . $ID_OPER . "</td>\n");
					print("			<td class='sample'>" . $CODE_ACTV . "</td>\n");
					print("			<td class='sample'>" . $ID_ITEM_PAR . "</td>\n");
					print("			<td class='sample'>" . $QO . "</td>\n");
					print("			<td class='sample'>" . $QR . "</td>\n");
					print("			<td class='sample'>" . $QC . "</td>\n");
					print("			<td class='sample'>" . $MIN_MACH_SF . "</td>\n");
					print("			<td class='sample'>" . $EMin . "</td>\n");
					print("		</tr>\n");
				}

				print("	<tr>\n");
				print("		<td colspan='11'></td>\n");
				print("	</tr>\n");
				print("	<tr>\n");
				print("		<td colspan='9'><b>Total</b></td>\n");
				print("		<td><b>" . $tot_qty . "</b></td>\n");
				print("		<td></td>\n");
				print("		<td><b>" . $tot_min_earned . "</b></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
				print("</div>\n");































				/////////////////////
				//QUERY TEMP TABLE FOR SHOP ORDERS
				/////////////////////
				$sql =  "select ";
				$sql .= "	convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
				$sql .= "	CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql .= "	h.ID_ITEM_PAR, ";
				$sql .= "	o.HR_MACH_SR as HR_MACH_SF, ";
				$sql .= "	tx.* ";
				$sql .= " from ";
				$sql .= "	#temp_trx tx, ";
				$sql .= " 	nsa.SHPORD_HDR h, ";
				$sql .= "	nsa.SHPORD_OPER so,";
				$sql .= "	nsa.ROUTMS_OPER o ";
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
				$sql .= " order by ";
				$sql .= " 	DATE_TRX asc, ";
				$sql .= " 	time_trx asc, ";
				$sql .= " 	ID_ITEM_PAR asc, ";
				$sql .= " 	ID_SO asc, ";
				$sql .= "	ID_OPER asc ";
				QueryDatabase($sql, $results);


				print("<div id='div_so' name='div_so'>\n");
				print("<table class='sample'>\n");
				print("	<th class='sample'>Time Stamp</th>\n");
				print("	<th class='sample'>Code Trx</th>\n");
				print("	<th class='sample'>SO</th>\n");
				print("	<th class='sample'>Sufx</th>\n");
				print("	<th class='sample'>Oper</th>\n");
				print("	<th class='sample'>Item #</th>\n");
				print("	<th class='sample'>Qty Ord</th>\n");
				print("	<th class='sample'>Qty Rem</th>\n");
				print("	<th class='sample'>Qty Cmp</th>\n");
				print("	<th class='sample'>Stand Mins</th>\n");
				print("	<th class='sample'>Earned Mins</th>\n");
				print("	<td id='x_so' name='x_so' onclick=\"closeDiv('div_so')\" TITLE='Remove Table'>X</td>\n");
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
					print("	<tr class='" . $td_class . "'>\n");
					print("		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $trxType . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['ID_SO'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['SUFX_SO'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['ID_OPER'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['ID_ITEM_PAR'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $qty_ord . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $qty_rem . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $row['QTY_GOOD'] . "</td>\n");
					print("		<td class='" . $td_class . "'>" . $MIN_MACH_SF . "</td>\n");
					print("		<td class='" . $td_class . "' colspan=2>" . $min_earned . "</td>\n");
					print("	</tr>\n");
				}

				print("	<tr>\n");
				print("		<td colspan='11'></td>\n");
				print("	</tr>\n");
				print("	<tr>\n");
				print("		<td colspan='8'><b>Total</b></td>\n");
				print("		<td><b>" . $tot_qty . "</b></td>\n");
				print("		<td></td>\n");
				print("		<td colspan=2><b>" . $tot_min_earned . "</b></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
				print("</div>\n");


				/////////////////////
				//OVERALL EFFICIENCY
				/////////////////////

				$tot_team_actual_min = $tot_team_actual_sec / 60;
				$ovral_eff = $tot_min_earned / $tot_team_actual_min;

				print("<div id='div_eff' name='div_eff'>\n");
				print("<table class='sample'>\n");
				print("	<th class='sample'>Total Earned Mins</th>\n");
				print("	<th class='sample'>Total Actual Mins</th>\n");
				print("	<th class='sample'>Eff Score*</th>\n");
				print("	<td id='x_eff' name='x_eff' onclick=\"closeDiv('div_eff')\" TITLE='Remove Table'>X</td>\n");
				print("	<tr>\n");
				print("		<td colspan='4'>* = Total Earned Mins / Total Actual Mins</td>\n");
				print("	</tr>\n");
				print("	<tr>\n");
				print("		<td><b>" . round($tot_min_earned,3) ."</b></td>\n");
				print("		<td><b>" . round($tot_team_actual_min,3) . "</b></td>\n");
				print("		<td colspan=2><b>" . round($ovral_eff * 100,2) . "</b></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("</div>\n");

			} else {

				print(" <form action='activity.php' method='POST'>");
				print(" <table>");
				print(" 	<tr>");
				print(" 		<td>Date From: </td>");
				print(" 		<td>");

				$myCalendar = new tc_calendar('df', true, true);
				$myCalendar->setIcon("images/iconCalendar.gif");
				$myCalendar->setDate(date('d'), date('m'), date('Y'));
				$myCalendar->setPath("/");
				$myCalendar->setYearInterval(1970, 2030);
				$myCalendar->setAlignment('left', 'bottom');
				$myCalendar->writeScript();

				print(" 		</td>");
				print(" 	</tr>");
				print(" 	<tr>");
				print(" 		<td>Date To: </td>");
				print(" 		<td>");

				$myCalendar = new tc_calendar('dt', true, true);
				$myCalendar->setIcon("images/iconCalendar.gif");
				$myCalendar->setDate(date('d'), date('m'), date('Y'));
				$myCalendar->setPath("/");
				$myCalendar->setYearInterval(1970, 2030);
				$myCalendar->setAlignment('left', 'bottom');
				$myCalendar->writeScript();

				print(" 		</td>");
				print(" 	</tr>");
				print(" 	<tr>");
				print(" 		<td colspan='2'>");
				print(" 			<LABEL for='team'>Team: </LABEL>");
				print("				<select name='team'>");

				$sql =  "select ";
				$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
				$sql .= " 	ltrim(ID_BADGE) as ID_BADGE,";
				$sql .= " 	NAME_EMP";
				$sql .= " from ";
				$sql .= " 	nsa.DCEMMS_EMP ";
				$sql .= " where ";
				$sql .= " 	TYPE_BADGE = 'X'";
				$sql .= " 	and";
				$sql .= " 	CODE_ACTV = '0'";
				$sql .= " order by BADGE_NAME asc";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					print("					<option value='" . $row['ID_BADGE'] . "'>" . $row['BADGE_NAME'] . "</option>");
				}

				print("				</select>");
				print(" 			<INPUT type='submit' value='Submit'> <INPUT type='reset'>");
				print(" 		</td>");
				print(" 	</tr>");
				print(" </table>");
				print(" </form>");

			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

	//if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["team"]))  {
	//	PrintFooter("activity.php");
	//} else {
	//	PrintFooter("index.php");
	//}
	print(" </br>");
	PrintFooter("");


?>
