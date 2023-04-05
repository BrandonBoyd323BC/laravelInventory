<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("csvSF_Accts_SOLDTO cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("csvSF_Contacts cannot select " . $dbName);
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["df"]) && isset($_POST["dt"])) {
				$filename = "PayTrx_". date('Ymd-His') .".csv";
				header( 'Content-Type: text/csv' );
				header( 'Content-Disposition: attachment;filename='.$filename);
				$fp = fopen('php://output', 'w');

				$DateFrom 		= str_replace("-","",$_POST["df"]);
				$DateTo 		= str_replace("-","",$_POST["dt"]);




				$dtTS = strtotime($_POST['dt']);
				$dtA = getdate($dtTS);
				$dfTwelveTS = strtotime("-84 days" , $dtTS);
				$dfTwelve = date('Y-m-d', $dfTwelveTS);
				$DateFromTwelve	= str_replace("-","",$dfTwelve);

				///////////////////////////////////////
				/// CREATE TEMP TABLE FOR PCTS
				///////////////////////////////////////
				$sql = " IF OBJECT_ID('tempdb..#temp_pct') IS NOT NULL";
				$sql .= "	DROP TABLE #temp_pct";
				QueryDatabase($sql, $results);

				$sql = " create table #temp_pct(";
				$sql .= "	ID_BADGE_TEAM varchar(9) not null, ";
				$sql .= "	TWELVE_WEEK_PCT numeric(5,2) not null, ";
				$sql .= "	TWELVE_WEEK_REG_RATE numeric(4,2) not null, ";
				$sql .= "	TWELVE_WEEK_OT_RATE numeric(4,2) not null, ";
				$sql .= "	CURRENT_WEEK_PCT numeric(5,2) not null, ";
				$sql .= "	CURRENT_WEEK_REG_RATE numeric(4,2) not null, ";
				$sql .= "	CURRENT_WEEK_OT_RATE numeric(4,2) not null ";
				$sql .= ")";
				QueryDatabase($sql, $results);

				///////////////////////////////////////
				/// CALCULATE CURRENT EFFICIENCY FROM APPROVALS
				///////////////////////////////////////
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
				$sql .= " order by ID_BADGE asc ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$ID_BADGE = trim($row['ID_BADGE']);

					///////////////////////////////////////
					/// CALCULATE CURRENT EFFICIENCY FROM APPROVALS
					///////////////////////////////////////

					$sql2 =  "select ";
					$sql2 .= " 	sum(EARNED_MINS) as SUM_EARNED_MINS, ";
					$sql2 .= " 	sum(ACTUAL_MINS) as SUM_ACTUAL_MINS ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCAPPROVALS ";
					$sql2 .= " where ";
					$sql2 .= " 	CODE_APP = '200'";
					$sql2 .= " 	and";
					$sql2 .= " 	DATE_APP between '" . $DateFrom . "' and '" . $DateTo . "' ";
					$sql2 .= " 	and";
					$sql2 .= " 	DATE_APP >= '2012-01-01' ";
					$sql2 .= " 	and";
					$sql2 .= " 	ltrim(BADGE_APP) = '" . $ID_BADGE . "'";
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

					$sql2 =  "select ";
					$sql2 .= "  * ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCPERCENT_RATE ";
					$sql2 .= " where ";
					$sql2 .= " 	PCT ='" . $twrnd_woe ."'";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$CurrentWeekRegRate = $row2['Reg'];
						$CurrentWeekOTRate = $row2['OT'];
					}

					///////////////////////////////////////
					/// CALCULATE TWELVE WEEK EFFICIENCY FROM APPROVALS
					///////////////////////////////////////

					$sql2 =  "select ";
					$sql2 .= " 	sum(EARNED_MINS) as SUM_EARNED_MINS, ";
					$sql2 .= " 	sum(ACTUAL_MINS) as SUM_ACTUAL_MINS ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCAPPROVALS ";
					$sql2 .= " where ";
					$sql2 .= " 	CODE_APP = '200'";
					$sql2 .= " 	and";
					$sql2 .= " 	DATE_APP between '" . $DateFromTwelve . "' and '" . $DateTo . "' ";
					$sql2 .= " 	and";
					$sql2 .= " 	DATE_APP >= '2012-01-01' ";
					$sql2 .= " 	and";
					$sql2 .= " 	ltrim(BADGE_APP) = '" . $ID_BADGE . "'";
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

					$sql2 =  "select ";
					$sql2 .= "  * ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCPERCENT_RATE ";
					$sql2 .= " where ";
					$sql2 .= " 	PCT ='" . $twrnd_woe ."'";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$TwelveWeekRegRate = $row2['Reg'];
						$TwelveWeekOTRate = $row2['OT'];
					}

					///////////////////////////////
					/// INSERT INTO TEMP TABLE
					///////////////////////////////
					$sql2 =  "INSERT into #temp_pct ( ";
					$sql2 .= "	ID_BADGE_TEAM, ";
					$sql2 .= "	TWELVE_WEEK_PCT, ";
					$sql2 .= "	TWELVE_WEEK_REG_RATE, ";
					$sql2 .= "	TWELVE_WEEK_OT_RATE, ";
					$sql2 .= "	CURRENT_WEEK_PCT, ";
					$sql2 .= "	CURRENT_WEEK_REG_RATE, ";
					$sql2 .= "	CURRENT_WEEK_OT_RATE ";
					$sql2 .= " ) VALUES ( ";
					$sql2 .= "	'". $ID_BADGE ."', ";
					$sql2 .= $TwelveWeekAvg .", ";
					$sql2 .= $TwelveWeekRegRate .", ";
					$sql2 .= $TwelveWeekOTRate .", ";
					$sql2 .= $CurrentWeekAvg .", ";
					$sql2 .= $CurrentWeekRegRate .", ";
					$sql2 .= $CurrentWeekOTRate ." ";
					$sql2 .= " ) ";
					QueryDatabase($sql2, $results2);

				}









































				$sql  = " SELECT ";
				$sql .= " 	p.ID_BADGE, ";
				$sql .= " 	e.NAME_EMP as NAME, ";
				//$sql .= " 	e.CODE_USER_1_DC as PCT, ";
				$sql .= " 	ltrim(e.ID_BADGE_TEAM_STD) as ID_BADGE_TEAM_STD, ";
				$sql .= "	t.TWELVE_WEEK_REG_RATE, ";
				//$sql .= " 	dateadd(day, 90, DATE_USER) as DATE_ELIG, ";
				//$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = 'REG') then p.HR_PAID else 0 end) as 'REGULAR', ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.CODE_PAY_DC = 'REG') then p.HR_PAID else 0 end) as 'REGULAR', ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.CODE_PAY_DC = 'OVT') then p.HR_PAID else 0 end) as 'OT', ";
				$sql .= "	'' as INCENT, ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.CODE_PAY_DC = 'PTO') then p.HR_PAID else 0 end) as 'VAC', ";
				$sql .= "	'' as VACINC, ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.CODE_PAY_DC = 'HOL') then p.HR_PAID else 0 end) as 'HOL', ";
				$sql .= "	'' as HOLINC, ";
				$sql .= "	'' as BONUS, ";
				$sql .= "	'' as TRAIN, ";
				$sql .= "	'' as REFERRAL ";
				$sql .= " FROM nsa.PAYTRX p ";
				$sql .= " 	left join nsa.DCEMMS_EMP e ";
				$sql .= " 	on p.ID_BADGE = e.ID_BADGE ";
				$sql .= " 	and e.CODE_ACTV = 0 ";
				$sql .= " 	left join #temp_pct t ";
				$sql .= " 	on ltrim(t.ID_BADGE_TEAM) = ltrim(e.ID_BADGE_TEAM_STD) ";
				$sql .= " WHERE p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."' ";
				$sql .= " GROUP BY p.ID_BADGE, e.NAME_EMP, e.CODE_USER_1_DC, dateadd(day, 90, DATE_USER), e.ID_BADGE_TEAM_STD, t.TWELVE_WEEK_REG_RATE ";
				$sql .= " ORDER BY p.ID_BADGE asc ";
				QueryDatabase($sql, $results);

/*
				$sql  = " SELECT ";
				$sql .= " 	p.ID_BADGE, ";
				$sql .= " 	e.NAME_EMP as NAME, ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = 'REG') then p.HR_PAID else 0 end) as 'REGULAR', ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = 'OVT') then p.HR_PAID else 0 end) as 'OT', ";
				$sql .= "	'' as INCENT, ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = 'PTO') then p.HR_PAID else 0 end) as 'VAC', ";
				$sql .= "	'' as VACINC, ";
				$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = 'HOL') then p.HR_PAID else 0 end) as 'HOL', ";
				$sql .= "	'' as HOLINC, ";
				$sql .= "	'' as BONUS, ";
				$sql .= "	'' as TRAIN, ";
				$sql .= "	'' as REFERRAL ";
				$sql .= " FROM nsa.PAYTRX p ";
				$sql .= " 	left join nsa.DCEMMS_EMP e ";
				$sql .= " 	on p.ID_BADGE = e.ID_BADGE ";
				$sql .= " 	and e.CODE_ACTV = 0 ";
				$sql .= " WHERE p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."' ";
				$sql .= " 	and p.FLAG_APPRV in ('Y','A') ";
				$sql .= " GROUP BY p.ID_BADGE, e.NAME_EMP ";
				$sql .= " ORDER BY p.ID_BADGE asc ";
				QueryDatabase($sql, $results);
*/
				$colNamesA = array();
				for($i = 0; $i < mssql_num_fields($results); $i++) {
				    $field_info = mssql_fetch_field($results, $i);
				    $field = $field_info->name;
				    $colNamesA[$i] =  $field;
				}
				fputcsv($fp, $colNamesA);

				while ($row = mssql_fetch_assoc($results)) {
					$comb = ($row['REGULAR'] + $row['VAC'] + $row['HOL']);
					if ($comb > 40) {
						$diff = $comb - 40;
						$row['OT'] += $diff;
						$row['REGULAR'] -= $diff;
					}
					fputcsv($fp, $row, ",", "\"");
				}
				fclose($fp);
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("csvSF_Contacts cannot disconnect from database");
		}
	}
	
    function query_to_csv($db_conn, $query, $filename, $attachment = false, $headers = true) {
        if($attachment) {
            // send response headers to the browser
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
            $fp = fopen('php://output', 'w');
        } else {
            $fp = fopen($filename, 'w');
        }
        $result = mysql_query($query, $db_conn) or die( mysql_error( $db_conn ) );

        if($headers) {
            // output header row (if at least one row exists)
            $row = mysql_fetch_assoc($result);
            if($row) {
                fputcsv($fp, array_keys($row));
                // reset pointer back to beginning
                mysql_data_seek($result, 0);
            }
        }
        while($row = mysql_fetch_assoc($result)) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
?>
