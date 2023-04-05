<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runONE_ITEM_TEAMS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runONE_ITEM_TEAMS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runONE_ITEM_TEAMS started at " . date('Y-m-d g:i:s a'));
			$a = array();
			$list = '';
			$listPct = '';
			//$QueryFileName = "QueryFile.txt";
			//$fp = fopen($QueryFileName, 'a');

			$sqld  = "select a.DATE_APP ";
			$sqld .= " from nsa.DCAPPROVALS a";
			$sqld .= " where a.CODE_APP = '300' ";
			$sqld .= " and a.DATE_APP > (select top 1 o.DATE_APP from nsa.DC_ONE_ITEM_TEAMS o order by o.DATE_APP desc)";
			$sqld .= " order by a.DATE_APP asc";
			QueryDatabase($sqld, $resultsd);
			//$date = '2015-03-31';
			//$end_date = '2015-03-31';

			//while (strtotime($date) <= strtotime($end_date)) {
			while ($rowd = mssql_fetch_assoc($resultsd)) {
				$date = $rowd['DATE_APP'];
				error_log("######## Date: " . $date);

				$sql  = "select ";
				$sql .= " 	e.ID_BADGE, ";
				$sql .= " 	e.NAME_EMP, ";
				$sql .= " 	ltrim(e.ID_BADGE) + ' - ' + e.NAME_EMP as BADGE_NAME, ";
				$sql .= " 	a.EARNED_MINS, ";
				$sql .= " 	a.ACTUAL_MINS ";
				$sql .= " from ";
				$sql .= "	nsa.DCEMMS_EMP e ";
				$sql .= "	left join nsa.DCAPPROVALS a ";
				$sql .= "	on ltrim(e.ID_BADGE) = a.BADGE_APP ";
				//$sql .= "	and a.DATE_APP = convert(DateTime,DateDiff(Day,1,GetDate())) ";
				$sql .= "	and a.DATE_APP = '" . $date . "' ";
				$sql .= "	and a.CODE_APP = '200' ";
				$sql .= " where ";
				$sql .= "	e.TYPE_BADGE = 'X' ";
				$sql .= "	and e.CODE_ACTV = 0 ";
				$sql .= " order by ID_BADGE asc";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$sql1  = "select ";
					$sql1 .= "	distinct(sh.ID_ITEM_PAR) ";
					$sql1 .= " FROM ";
					$sql1 .= "	nsa.DCUTRX_NONZERO_PERM nz ";
					$sql1 .= " LEFT JOIN nsa.SHPORD_HDR sh ";
					$sql1 .= "	on nz.ID_SO = sh.ID_SO ";
					$sql1 .= " WHERE FLAG_DEL = '' ";
					$sql1 .= "	and nz.DATE_TRX = '" . $date . "' ";
					$sql1 .= "	and nz.CODE_TRX = '102' ";
					//$sql1 .= "	and nz.CODE_TRX = '103' ";
					$sql1 .= "	and nz.ID_BADGE = '" . $row['ID_BADGE'] . "' ";
					$sql1 .= " ORDER BY ID_ITEM_PAR ";
					QueryDatabase($sql1, $results1);

					if (mssql_num_rows($results1) == '1') {
						while ($row1 = mssql_fetch_assoc($results1)) {
							$overall_eff = round(($row['EARNED_MINS'] / $row['ACTUAL_MINS']) * 100,2);

							$sql2  = "select ";
							$sql2 .= "	sh.ID_ITEM_PAR, ";
							$sql2 .= "	nz.ID_OPER, ";
							$sql2 .= "	sum(nz.QTY_GOOD) as SUM_QTY_GOOD, ";
							$sql2 .= "	avg(so.HR_MACH_SF) as HR_MACH_SF";
							$sql2 .= " FROM ";
							$sql2 .= "	nsa.DCUTRX_NONZERO_PERM nz ";  
							$sql2 .= " LEFT JOIN nsa.SHPORD_HDR sh "; 
							$sql2 .= "	on nz.ID_SO = sh.ID_SO "; 
							$sql2 .= " LEFT JOIN nsa.SHPORD_OPER so ";
							$sql2 .= "	on nz.ID_SO = so.ID_SO ";
							$sql2 .= "	and nz.ID_OPER = so.ID_OPER "; 
							$sql2 .= "	and nz.SUFX_SO = so.SUFX_SO "; 
							$sql2 .= " WHERE FLAG_DEL = '' "; 
							$sql2 .= "	and nz.DATE_TRX = '" . $date . "' "; 
							$sql2 .= "	and nz.CODE_TRX = '103' ";
							$sql2 .= "	and nz.ID_BADGE = '" . $row['ID_BADGE'] . "' "; 
							$sql2 .= " GROUP BY sh.ID_ITEM_PAR, nz.ID_OPER"; 
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								if (($row2['ID_ITEM_PAR'] != 'PRODUCTION') && ($row2['SUM_QTY_GOOD'] != 0)) {
									error_log("### ONE ITEM -- DATE: " . $date . " TEAM: " . ltrim($row['ID_BADGE']) . " ITEM: " . $row1['ID_ITEM_PAR'] . " QTY: " . $row2['SUM_QTY_GOOD'] . " OPER: " . $row2['ID_OPER'] . " STD: " . $row2['HR_MACH_SF'] . " PCT: " . $overall_eff);	
									
									$sql3  = "insert into nsa.DC_ONE_ITEM_TEAMS ( ";
									$sql3 .= " DATE_APP, ";
									$sql3 .= " BADGE_APP, ";
									$sql3 .= " ID_ITEM, ";
									$sql3 .= " ID_OPER, ";
									$sql3 .= " TOTAL_QTY_GOOD, ";
									$sql3 .= " HR_MACH_SF, ";
									$sql3 .= " ACTUAL_MINS, ";
									$sql3 .= " EARNED_MINS, ";
									$sql3 .= " EFF_PCT ";
									$sql3 .= " ) VALUES ( ";
									$sql3 .= " '" . $date . "', ";
									$sql3 .= " '" . $row['ID_BADGE'] . "', ";
									$sql3 .= " '" . $row2['ID_ITEM_PAR'] . "', ";
									$sql3 .= " '" . $row2['ID_OPER'] . "', ";
									$sql3 .= $row2['SUM_QTY_GOOD'] . ", ";
									$sql3 .= $row2['HR_MACH_SF'] . ", ";
									$sql3 .= $row['ACTUAL_MINS'] . ", ";
									$sql3 .= $row['EARNED_MINS'] . ", ";
									$sql3 .= $overall_eff ;
									$sql3 .= " ) ";
									error_log("### sql3: " . $sql3);
									QueryDatabase($sql3, $results3);
									//fwrite($fp, $sql3 . PHP_EOL);
								}
							}
						}
					}
				}
				$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
			}
			//fclose($fp);	
			error_log("### runONE_ITEM_TEAMS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runONE_ITEM_TEAMS cannot disconnect from database");
		}
	}
?>