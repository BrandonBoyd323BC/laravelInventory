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
			$ret = '';

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["unit"]) && isset($_POST["flag_shift_summary"]) && isset($_POST["orderby"]))  {

				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
				$Unit_ID = $_POST["unit"];
				$Flag_Shift_Summary = $_POST["flag_shift_summary"];
				$OrderBy = $_POST["orderby"];

				$sql = " IF OBJECT_ID('tempdb..#temp_unit') IS NOT NULL";
				$sql .= " DROP TABLE #temp_unit";
				QueryDatabase($sql, $results);

				$sql = " CREATE TABLE #temp_unit (";
				$sql .= " REC_TYPE varchar(1),";
				$sql .= " UNIT_ID varchar(12),";
				$sql .= " BADGE_NAME varchar(30),";
				$sql .= " CODE_SHIFT varchar(2),";
				$sql .= " ID_BADGE_SUPRVSR varchar(9),";
				$sql .= " EM numeric(12,3),";
				$sql .= " AM numeric(12,3),";
				$sql .= " AVM numeric(12,3),";
				$sql .= " IM numeric(12,3),";
				$sql .= " OVERALL_EFF numeric(8,3),";
				$sql .= " rowid int IDENTITY(1,1) ";
				$sql .= ")";
				QueryDatabase($sql, $results);

				$ret .= "		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>\n";
				$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";
				$ret .= "		<input type='hidden' id='df' name='df' value='" . $DateFrom . "'>\n";
				$ret .= "		<input type='hidden' id='dt' name='dt' value='" . $DateTo . "'>\n";

				if ($Unit_ID == 'ALL') {
					$sql =  "SELECT distinct PHONE as UNIT_ID ";
					$sql .= " FROM nsa.DCEMMS_EMP ";
					$sql .= " WHERE TYPE_BADGE = 'X' ";
					$sql .= " and CODE_ACTV = '0' ";
					$sql .= " and PHONE <> '' ";
					$sql .= " ORDER BY PHONE asc ";
					QueryDatabase($sql, $results);
					$UnitsArray = array();

					while($row = mssql_fetch_assoc($results)) {
						$UnitsArray[] = $row['UNIT_ID'];
					}
				} else {
					$UnitsArray = array($Unit_ID);
				}

				foreach ($UnitsArray as $Unit_ID1) {
					$UnitEM = 0;
					$UnitAM = 0;
					$UnitAVM = 0;
					$UnitIM = 0;

					$sql  = "SELECT ";
					$sql .= " ltrim(e1.ID_BADGE) + ' - ' + e1.NAME_EMP as BADGE_NAME,";
					$sql .= " ltrim(e1.ID_BADGE) as ID_BADGE, ";
					$sql .= " ltrim(e1.ID_BADGE_SUPRVSR) as ID_BADGE_SUPRVSR, ";
					$sql .= " ltrim(e1.CODE_SHIFT) as CODE_SHIFT ";
					$sql .= " FROM nsa.DCEMMS_EMP e1 ";
					$sql .= " WHERE e1.TYPE_BADGE = 'X'";
					$sql .= " and e1.CODE_ACTV = '0'";
					$sql .= " and e1.PHONE = '" . $Unit_ID1 . "' ";
					$sql .= " and GETDATE() < e1.DATE_USER";//excluding teams that have been expired per Brian - 2/16/18
					$sql .= " ORDER BY BADGE_NAME asc";
					QueryDatabase($sql, $results);

					while ($row = mssql_fetch_assoc($results)) {
						$sql2  = "SELECT ";
						$sql2 .= " sum(ACTUAL_MINS) as SUM_AM, ";
						$sql2 .= " sum(EARNED_MINS) as SUM_EM, ";
						$sql2 .= " sum(AVAIL_MINS) as SUM_AVM, ";
						$sql2 .= " sum(INDIR_MINS) as SUM_IM ";
						$sql2 .= " FROM nsa.DCAPPROVALS ";
						$sql2 .= " WHERE ltrim(BADGE_APP) = '" . $row['ID_BADGE'] . "' ";
						$sql2 .= " and CODE_APP = '200' ";
						$sql2 .= " and DATE_APP  between '" . $DateFrom . "' and '" . $DateTo . "' ";
						QueryDatabase($sql2, $results2);
						$row2 = mssql_fetch_assoc($results2);

						if ((!is_null($row2['SUM_EM'])) && (!is_null($row2['SUM_AM'])) && (!is_null($row2['SUM_AVM'])) && (!is_null($row2['SUM_IM']))) {
							$UnitEM += $row2['SUM_EM'];
							$UnitAM += $row2['SUM_AM'];
							$UnitAVM += $row2['SUM_AVM'];
							$UnitIM += $row2['SUM_IM'];
							$team_eff = round(($row2['SUM_EM'] / $row2['SUM_AM']) * 100,2);
							$pctClass = GetColorPct($team_eff);

							$sql3  = " INSERT INTO #temp_unit( ";
							$sql3 .= "  REC_TYPE, ";
							$sql3 .= "  UNIT_ID, ";
							$sql3 .= "  BADGE_NAME, ";
							$sql3 .= "  CODE_SHIFT, ";
							$sql3 .= "  ID_BADGE_SUPRVSR, ";
							$sql3 .= "  EM, ";
							$sql3 .= "  AM, ";
							$sql3 .= "  AVM, ";
							$sql3 .= "  IM, ";
							$sql3 .= "  OVERALL_EFF ";
							$sql3 .= " ) VALUES ( ";
							$sql3 .= "  'T', ";
							$sql3 .= "  '" . $Unit_ID1 . "', ";
							$sql3 .= "  \"" . $row['BADGE_NAME'] . "\", ";
							$sql3 .= "  '" . $row['CODE_SHIFT'] . "', ";
							$sql3 .= "  '" . $row['ID_BADGE_SUPRVSR'] . "', ";
							$sql3 .= "  " . $row2['SUM_EM'] . ", ";
							$sql3 .= "  " . $row2['SUM_AM'] . ", ";
							$sql3 .= "  " . $row2['SUM_AVM'] . ", ";
							$sql3 .= "  " . $row2['SUM_IM'] . ", ";
							$sql3 .= "  " . $team_eff . " ";
							$sql3 .= " ) ";
							QueryDatabase($sql3, $results3);
						}
					}
					$unit_eff = round(($UnitEM / $UnitAM) * 100,2);

					$sql3  = " INSERT INTO #temp_unit( ";
					$sql3 .= "  REC_TYPE, ";
					$sql3 .= "  UNIT_ID, ";
					$sql3 .= "  BADGE_NAME, ";
					$sql3 .= "  CODE_SHIFT, ";
					$sql3 .= "  ID_BADGE_SUPRVSR, ";
					$sql3 .= "  EM, ";
					$sql3 .= "  AM, ";
					$sql3 .= "  AVM, ";
					$sql3 .= "  IM, ";
					$sql3 .= "  OVERALL_EFF ";
					$sql3 .= " ) VALUES ( ";
					$sql3 .= "  'U', ";
					$sql3 .= "  '" . $Unit_ID1 . "', ";
					$sql3 .= "  '', ";
					$sql3 .= "  '', ";
					$sql3 .= "  '', ";
					$sql3 .= "  " . $UnitEM . ", ";
					$sql3 .= "  " . $UnitAM . ", ";
					$sql3 .= "  " . $UnitAVM . ", ";
					$sql3 .= "  " . $UnitIM . ", ";
					$sql3 .= "  " . $unit_eff . " ";
					$sql3 .= " ) ";
					QueryDatabase($sql3, $results3);
				}

				$overall_EM = 0;
				$overall_AM = 0;
				$overall_IM = 0;
				$overall_AVM = 0;

				$overallTRAINING_EM = 0;
				$overallTRAINING_AM = 0;
				$overallTRAINING_IM = 0;
				$overallTRAINING_AVM = 0;

				$overallFACEMASK_EM = 0;
				$overallFACEMASK_AM = 0;
				$overallFACEMASK_IM = 0;
				$overallFACEMASK_AVM = 0;

				$sql  = "SELECT * from #temp_unit ";
				$sql .= " where REC_TYPE = 'U' ";
				$sql .= " ORDER BY OVERALL_EFF desc ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample' colspan=9>" . $row['UNIT_ID'] . "</th>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Team</th>\n";
					$ret .= " 		<th class='sample'>Supr</th>\n";
					$ret .= " 		<th class='sample'>Earned Min</th>\n";
					$ret .= " 		<th class='sample'>Adj. Actual Min</th>\n";
					$ret .= " 		<th class='sample'>Indirect Min</th>\n";
					$ret .= " 		<th class='sample'>Avail. Raw Min</th>\n";
					$ret .= " 		<th class='sample'>Eff. Percent</th>\n";
					$ret .= " 		<th class='sample'>Indirect Percent</th>\n";
					$ret .= " 		<th class='sample'>Raw Percent</th>\n";
					$ret .= " 	</tr>\n";

					$sql2 = "SELECT * from #temp_unit ";
					$sql2 .= " where REC_TYPE = 'T' ";
					$sql2 .= " and UNIT_ID = '" . $row['UNIT_ID'] . "' ";
					$sql2 .= " ORDER BY OVERALL_EFF desc ";
					QueryDatabase($sql2, $results2);

					while ($row2 = mssql_fetch_assoc($results2)) {
						$IndirPCT = round(($row2['IM'] / $row2['AVM'])*100,2);
						$RawPCT = round(($row2['EM'] / $row2['AVM'])*100,2);

						$pctClass = GetColorPct($row2['OVERALL_EFF']);
						$pctClassI = GetColorPct($IndirPCT);
						$pctClassR = GetColorPct($RawPCT);

						$ret .= " 	<tr class='d1s'>\n";
						$ret .= " 		<td class='sample'>" . $row2['BADGE_NAME'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . $row2['ID_BADGE_SUPRVSR'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . $row2['EM'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . $row2['AM'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . $row2['IM'] . "</td>\n";
						$ret .= " 		<td class='sample'>" . $row2['AVM'] . "</td>\n";
						$ret .= " 		<td class='sample'><font class='" . $pctClass . "'>" . $row2['OVERALL_EFF'] . "%</font></td>\n";
						$ret .= " 		<td class='sample'><font class='" . $pctClassI . "'>" . $IndirPCT . "%</font></td>\n";
						$ret .= " 		<td class='sample'><font class='" . $pctClassR . "'>" . $RawPCT . "%</font></td>\n";
						$ret .= " 	</tr>\n";
					}

					$IndirPCT = round(($row['IM'] / $row['AVM'])*100,2);
					$RawPCT = round(($row['EM'] / $row['AVM'])*100,2);

					$pctClass = GetColorPct($row['OVERALL_EFF']);
					$pctClassI = GetColorPct($IndirPCT);
					$pctClassR = GetColorPct($RawPCT);

					$ret .= " 	<tr></tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample' colspan=2>TOTALS</th>\n";
					$ret .= " 		<th class='sample'>" . $row['EM'] . "</th>\n";
					$ret .= " 		<th class='sample'>" . $row['AM'] . "</th>\n";
					$ret .= " 		<th class='sample'>" . $row['IM'] . "</th>\n";
					$ret .= " 		<th class='sample'>" . $row['AVM'] . "</th>\n";
					$ret .= " 		<th class='sample'><font class='" . $pctClass . "'>" . $row['OVERALL_EFF'] . "%</font></th>\n";
					$ret .= " 		<th class='sample'><font class='" . $pctClassI . "'>" . $IndirPCT . "%</font></th>\n";
					$ret .= " 		<th class='sample'><font class='" . $pctClassR . "'>" . $RawPCT . "%</font></th>\n";
					$ret .= " 	</tr>\n";
					$ret .= " </table>\n";
					//$ret .= " </br>\n";

					$overall_EM += $row['EM'];
					$overall_AM += $row['AM'];
					$overall_IM += $row['IM'];
					$overall_AVM += $row['AVM'];

					if ($row['UNIT_ID'] == 'TRAINING') {
						$overallTRAINING_EM += $row['EM'];
						$overallTRAINING_AM += $row['AM'];
						$overallTRAINING_IM += $row['IM'];
						$overallTRAINING_AVM += $row['AVM'];
					}
					
					if ($row['UNIT_ID'] == 'FACE MASK') {
						$overallFACEMASK_EM += $row['EM'];
						$overallFACEMASK_AM += $row['AM'];
						$overallFACEMASK_IM += $row['IM'];
						$overallFACEMASK_AVM += $row['AVM'];
					}					
				}

				$overall_IndirPCT = round(($overall_IM / $overall_AVM)*100,2);
				$overall_RawPCT = round(($overall_EM / $overall_AVM)*100,2);
				$overall_PCT = round(($overall_EM / $overall_AM) * 100,2);

				$pctClass = GetColorPct($overall_PCT);
				$pctClassI = GetColorPct($overall_IndirPCT);
				$pctClassR = GetColorPct($overall_RawPCT);

				//$ret .= " </br>\n";
				$ret .= " <table class='sample'>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample' colspan=8>OVERALL (WITH TRAINING / FACE MASKS)</th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample'></th>\n";
				$ret .= " 		<th class='sample'>Earned Min</th>\n";
				$ret .= " 		<th class='sample'>Adj. Actual Min</th>\n";
				$ret .= " 		<th class='sample'>Indirect Min</th>\n";
				$ret .= " 		<th class='sample'>Avail. Raw Min</th>\n";
				$ret .= " 		<th class='sample'>Eff. Percent</th>\n";
				$ret .= " 		<th class='sample'>Indirect Percent</th>\n";
				$ret .= " 		<th class='sample'>Raw Percent</th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " 	<tr></tr>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample'>TOTALS</th>\n";
				$ret .= " 		<th class='sample'>" . $overall_EM . "</th>\n";
				$ret .= " 		<th class='sample'>" . $overall_AM . "</th>\n";
				$ret .= " 		<th class='sample'>" . $overall_IM . "</th>\n";
				$ret .= " 		<th class='sample'>" . $overall_AVM . "</th>\n";
				$ret .= " 		<th class='sample'><font class='" . $pctClass . "'>" . $overall_PCT . "%</font></th>\n";
				$ret .= " 		<th class='sample'><font class='" . $pctClassI . "'>" . $overall_IndirPCT . "%</font></th>\n";
				$ret .= " 		<th class='sample'><font class='" . $pctClassR . "'>" . $overall_RawPCT . "%</font></th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " </table>\n";
				//$ret .= " </br>\n";



				$overallWOT_IndirPCT = round((($overall_IM - $overallTRAINING_IM - $overallFACEMASK_IM) / ($overall_AVM - $overallTRAINING_AVM - $overallFACEMASK_AVM))*100,2);
				$overallWOT_RawPCT = round((($overall_EM - $overallTRAINING_EM - $overallFACEMASK_EM) / ($overall_AVM - $overallTRAINING_AVM - $overallFACEMASK_AVM))*100,2);
				$overallWOT_PCT = round((($overall_EM - $overallTRAINING_EM - $overallFACEMASK_EM) / ($overall_AM - $overallTRAINING_AM - $overallFACEMASK_AM)) * 100,2);

				$pctClass = GetColorPct($overallWOT_PCT);
				$pctClassI = GetColorPct($overallWOT_IndirPCT);
				$pctClassR = GetColorPct($overallWOT_RawPCT);


				//$ret .= " </br>\n";
				$ret .= " <table class='sample'>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample' colspan=8>OVERALL (WITHOUT TRAINING / FACE MASKS)</th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample'></th>\n";
				$ret .= " 		<th class='sample'>Earned Min</th>\n";
				$ret .= " 		<th class='sample'>Adj. Actual Min</th>\n";
				$ret .= " 		<th class='sample'>Indirect Min</th>\n";
				$ret .= " 		<th class='sample'>Avail. Raw Min</th>\n";
				$ret .= " 		<th class='sample'>Eff. Percent</th>\n";
				$ret .= " 		<th class='sample'>Indirect Percent</th>\n";
				$ret .= " 		<th class='sample'>Raw Percent</th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " 	<tr></tr>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample'>TOTALS</th>\n";
				$ret .= " 		<th class='sample'>" . ($overall_EM - $overallTRAINING_EM - $overallFACEMASK_EM). "</th>\n";
				$ret .= " 		<th class='sample'>" . ($overall_AM - $overallTRAINING_AM - $overallFACEMASK_AM). "</th>\n";
				$ret .= " 		<th class='sample'>" . ($overall_IM - $overallTRAINING_IM - $overallFACEMASK_IM) . "</th>\n";
				$ret .= " 		<th class='sample'>" . ($overall_AVM - $overallTRAINING_AVM - $overallFACEMASK_AVM) . "</th>\n";
				$ret .= " 		<th class='sample'><font class='" . $pctClass . "'>" . $overallWOT_PCT . "%</font></th>\n";
				$ret .= " 		<th class='sample'><font class='" . $pctClassI . "'>" . $overallWOT_IndirPCT . "%</font></th>\n";
				$ret .= " 		<th class='sample'><font class='" . $pctClassR . "'>" . $overallWOT_RawPCT . "%</font></th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " </table>\n";
				//$ret .= " </br>\n";

				//$ret .= " </br>\n";
				$ret .= " <table class='sample'>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample' colspan=8>Supervisor Subtotals</th>\n";
				$ret .= " 	</tr>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample'>Supervisor</th>\n";
				$ret .= " 		<th class='sample'>Earned Min</th>\n";
				$ret .= " 		<th class='sample'>Adj. Actual Min</th>\n";
				$ret .= " 		<th class='sample'>Indirect Min</th>\n";
				$ret .= " 		<th class='sample'>Avail. Raw Min</th>\n";
				$ret .= " 		<th class='sample'>Eff. Percent</th>\n";
				$ret .= " 		<th class='sample'>Indirect Percent</th>\n";
				$ret .= " 		<th class='sample'>Raw Percent</th>\n";
				$ret .= " 	</tr>\n";

				$sql  = "SELECT ";
				$sql .= " sum(t.EM) as SUM_EM, ";
				$sql .= " sum(t.AM) as SUM_AM, ";
				$sql .= " sum(t.AVM) as SUM_AVM, ";
				$sql .= " sum(t.IM) as SUM_IM, ";
				$sql .= " t.ID_BADGE_SUPRVSR, ";
				$sql .= " w.NAME_EMP ";
				$sql .= " FROM #temp_unit t";
				$sql .= " LEFT JOIN nsa.DCWEB_AUTH w ";
				$sql .= " ON ltrim(t.ID_BADGE_SUPRVSR) = ltrim(w.ID_BADGE) ";
				$sql .= " WHERE t.REC_TYPE = 'T' ";
				$sql .= " GROUP BY t.ID_BADGE_SUPRVSR, w.NAME_EMP ";
				$sql .= " ORDER BY t.ID_BADGE_SUPRVSR asc ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$EffPCT = round(($row['SUM_EM'] / $row['SUM_AM'])*100,2);
					$IndirPCT = round(($row['SUM_IM'] / $row['SUM_AVM'])*100,2);
					$RawPCT = round(($row['SUM_EM'] / $row['SUM_AVM'])*100,2);

					$pctClassE = GetColorPct($EffPCT);
					$pctClassI = GetColorPct($IndirPCT);
					$pctClassR = GetColorPct($RawPCT);

					$ret .= " 	<tr></tr>\n";
					$ret .= " 	<tr class='d1s'>\n";
					$ret .= " 		<th class='sample'>" . $row['ID_BADGE_SUPRVSR'] . " - " . $row['NAME_EMP'] . "</th>\n";
					$ret .= " 		<th class='sample'>" . $row['SUM_EM'] . "</th>\n";
					$ret .= " 		<th class='sample'>" . $row['SUM_AM'] . "</th>\n";
					$ret .= " 		<th class='sample'>" . $row['SUM_IM'] . "</th>\n";
					$ret .= " 		<th class='sample'>" . $row['SUM_AVM'] . "</th>\n";
					$ret .= " 		<th class='sample'><font class='" . $pctClassE . "'>" . $EffPCT . "%</font></th>\n";
					$ret .= " 		<th class='sample'><font class='" . $pctClassI . "'>" . $IndirPCT . "%</font></th>\n";
					$ret .= " 		<th class='sample'><font class='" . $pctClassR . "'>" . $RawPCT . "%</font></th>\n";
					$ret .= " 	</tr>\n";
				}
				$ret .= " </table>\n";


				if ($Flag_Shift_Summary == 1) {
					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample' colspan=8>Shift Subtotals</th>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Shift</th>\n";
					$ret .= " 		<th class='sample'>Earned Min</th>\n";
					$ret .= " 		<th class='sample'>Adj. Actual Min</th>\n";
					$ret .= " 		<th class='sample'>Indirect Min</th>\n";
					$ret .= " 		<th class='sample'>Avail. Raw Min</th>\n";
					$ret .= " 		<th class='sample'>Eff. Percent</th>\n";
					$ret .= " 		<th class='sample'>Indirect Percent</th>\n";
					$ret .= " 		<th class='sample'>Raw Percent</th>\n";
					$ret .= " 	</tr>\n";

					$sql  = "SELECT ";
					$sql .= " sum(t.EM) as SUM_EM, ";
					$sql .= " sum(t.AM) as SUM_AM, ";
					$sql .= " sum(t.AVM) as SUM_AVM, ";
					$sql .= " sum(t.IM) as SUM_IM, ";
					$sql .= " left(ltrim(t.CODE_SHIFT),1) as Shift ";
					$sql .= " FROM #temp_unit t";
					$sql .= " WHERE t.REC_TYPE = 'T' ";
					$sql .= " GROUP BY left(ltrim(t.CODE_SHIFT),1) ";
					$sql .= " ORDER BY left(ltrim(t.CODE_SHIFT),1) asc ";
					QueryDatabase($sql, $results);
					while ($row = mssql_fetch_assoc($results)) {
						$EffPCT = round(($row['SUM_EM'] / $row['SUM_AM'])*100,2);
						$IndirPCT = round(($row['SUM_IM'] / $row['SUM_AVM'])*100,2);
						$RawPCT = round(($row['SUM_EM'] / $row['SUM_AVM'])*100,2);

						$pctClassE = GetColorPct($EffPCT);
						$pctClassI = GetColorPct($IndirPCT);
						$pctClassR = GetColorPct($RawPCT);

						$ret .= " 	<tr></tr>\n";
						$ret .= " 	<tr class='d1s'>\n";
						$ret .= " 		<th class='sample'>" . $row['Shift'] . "</th>\n";
						$ret .= " 		<th class='sample'>" . $row['SUM_EM'] . "</th>\n";
						$ret .= " 		<th class='sample'>" . $row['SUM_AM'] . "</th>\n";
						$ret .= " 		<th class='sample'>" . $row['SUM_IM'] . "</th>\n";
						$ret .= " 		<th class='sample'>" . $row['SUM_AVM'] . "</th>\n";
						$ret .= " 		<th class='sample'><font class='" . $pctClassE . "'>" . $EffPCT . "%</font></th>\n";
						$ret .= " 		<th class='sample'><font class='" . $pctClassI . "'>" . $IndirPCT . "%</font></th>\n";
						$ret .= " 		<th class='sample'><font class='" . $pctClassR . "'>" . $RawPCT . "%</font></th>\n";
						$ret .= " 	</tr>\n";
					}
					$ret .= " </table>\n";
				}

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
