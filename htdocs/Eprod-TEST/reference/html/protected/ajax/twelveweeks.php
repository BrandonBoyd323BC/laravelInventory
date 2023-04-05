<?php

	$DEBUG = 0;
	$SHOW_DEL = 0;

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
			if ($UserRow['PERM_HR'] == '1') {
				$ret = '';
				if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["team"]))  {
					$df = $_POST["df"];
					$dt = $_POST["dt"];
					$Team = $_POST["team"];

					//$dtTS = strtotime($dt);
					//$dtA = getdate($dtTS);
					//$dfTS = strtotime("-84 days" , $dtTS);
					//$df = date('Y-m-d', $dfTS);

					$sql  = "SELECT ";
					$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_SORT as BADGE_NAME,";
					$sql .= " ltrim(ID_BADGE) as ID_BADGE,";
					$sql .= " ltrim(CODE_USER) as CODE_USER,";
					$sql .= " NAME_SORT";
					$sql .= " FROM nsa.DCEMMS_EMP ";
					$sql .= " WHERE TYPE_BADGE = 'X'";
					$sql .= " and CODE_ACTV = '0'";
					if ($Team != 'ALL') {
						$sql .= " and";
						$sql .= " ltrim(ID_BADGE) = '" . $Team . "'";
					}
					$sql .= " ORDER BY ID_BADGE asc ";
					QueryDatabase($sql, $results);

					while ($row = mssql_fetch_assoc($results)) {
						$ID_BADGE = trim($row['ID_BADGE']);

						$sql2  = "SELECT * ";
						$sql2 .= " FROM nsa.DCAPPROVALS ";
						$sql2 .= " WHERE CODE_APP = '200'";
						$sql2 .= " and DATE_APP between '" . $df . "' and '" . $dt . "' ";
						$sql2 .= " and DATE_APP >= '2012-01-01' ";
						$sql2 .= " and ltrim(BADGE_APP) = '" . $ID_BADGE . "'";
						$sql2 .= " ORDER BY DATE_APP asc ";
						QueryDatabase($sql2, $results2);

						$ret .= " <div id='div_" . $ID_BADGE . "' name='div_" . $ID_BADGE . "'>\n";
						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' colspan=4>". $row['BADGE_NAME'] . "</th>\n";
						$ret .= "		<td id='x_" . $ID_BADGE . "' name='x_" . $ID_BADGE . "' onclick=\"closeDiv('div_" . $ID_BADGE . "')\" TITLE='Remove Table'>X</td>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' colspan=4>" . $df . " - " . $dt . "</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample'>Date App</th>\n";
						$ret .= " 		<th class='sample'>Earned Mins</th>\n";
						$ret .= " 		<th class='sample'>Actual Mins</th>\n";
						$ret .= " 		<th class='sample'>Avail Mins</th>\n";
						$ret .= " 	</tr>\n";
						$tot_min_earned = 0;
						$tot_min_actual = 0;
						while ($row2 = mssql_fetch_assoc($results2)) {
							$tot_min_earned += $row2['EARNED_MINS'];
							$tot_min_actual += $row2['ACTUAL_MINS'];
							$tot_min_avail += $row2['AVAIL_MINS'];

							$ret .= " 	<tr>\n";
							$ret .= " 		<td class='sample'>". $row2['DATE_APP'] . "</td>\n";
							$ret .= " 		<td class='sample'>". $row2['EARNED_MINS'] . "</td>\n";
							$ret .= " 		<td class='sample'>". $row2['ACTUAL_MINS'] . "</td>\n";
							$ret .= " 		<td class='sample'>". $row2['AVAIL_MINS'] . "</td>\n";
							$ret .= " 	</tr>\n";
						}

						$TwelveWeekAvg = round((($tot_min_earned / $tot_min_actual) * 100),3);
						$TwelveWeekAvgRAW = round((($tot_min_earned / $tot_min_avail) * 100),3);
						$TwelveWeekAdditionalRate = 0;
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
							$TwelveWeekAdditionalRate = $row2['Reg'];
						}
						//error_log("TWELVE WEEK AVG RATE: " . $TwelveWeekAdditionalRate);

						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample'>TOTALS</th>\n";
						$ret .= " 		<th class='sample'>". $tot_min_earned . "</th>\n";
						$ret .= " 		<th class='sample'>". $tot_min_actual . "</th>\n";
						$ret .= " 		<th class='sample'>". $tot_min_avail . "</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' colspan=3>PCT</th>\n";
						$ret .= " 		<th class='sample'>" . $TwelveWeekAvg . " %</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' colspan=3>Raw PCT</th>\n";
						$ret .= " 		<th class='sample'>" . $TwelveWeekAvgRAW . " %</th>\n";
						$ret .= " 	</tr>\n";						
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' colspan=3>Hourly Bonus</th>\n";
						$ret .= " 		<th class='sample'>$ " . $TwelveWeekAdditionalRate . " </th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " </table>\n";
						$ret .= " </br>\n";
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
