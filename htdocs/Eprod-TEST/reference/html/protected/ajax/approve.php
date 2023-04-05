<?php

	//error_log("TEST");


	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	//print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/default.css'>\n");
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (($UserRow['PERM_SUPERVISOR'] == '1') || ($UserRow['PERM_HR'] == '1'))  {

				if (isset($_POST["code"]) && isset($_POST["badge"]) && isset($_POST["dateapp"]))  {
					$strRet = 'ERROR!';
					$today = date('m-d-Y');

					//CHECK FOR EXISTING RECORD
					$sqlcheck  = " SELECT * ";
					$sqlcheck .= " from nsa.DCAPPROVALS ";
					$sqlcheck .= " WHERE CODE_APP = '" . $_POST["code"] . "' ";
					$sqlcheck .= " and BADGE_APP = '" . $_POST["badge"] . "' ";
					$sqlcheck .= " and DATE_APP = '" . $_POST["dateapp"] . "' ";
					QueryDatabase($sqlcheck, $resultscheck);

					if (mssql_num_rows($resultscheck) == 0) {

						$sql  = " INSERT into ";
						$sql .= " nsa.DCAPPROVALS ( ";
						$sql .= " CODE_APP, ";
						$sql .= " DATE_APP, ";
						$sql .= " BADGE_APP, ";
						if (isset($_POST["earned"]) && isset($_POST["actual"])) {
							$sql .= " ACTUAL_MINS, ";
							$sql .= " EARNED_MINS, ";
						}
						if (isset($_POST["unadj"]) && isset($_POST["indir"])) {
							$sql .= " AVAIL_MINS, ";
							$sql .= " INDIR_MINS, ";
						}
						if (isset($_POST["sample"])) {
							$sql .= " SAMPLE_MINS, ";
						}
						$sql .= " APP_BY_ID_USER, ";
						$sql .= " COMMENTS, ";
						$sql .= " DATE_ADD ";
						$sql .= " ) values ( ";
						$sql .= " '" . $_POST['code'] . "', ";
						$sql .= " '" . $_POST['dateapp'] . "', ";
						$sql .= " '" . $_POST['badge'] . "', ";
						if (isset($_POST["earned"]) && isset($_POST["actual"])) {
							$sql .= " '" . $_POST['actual'] . "', ";
							$sql .= " '" . $_POST['earned'] . "', ";
						}
						if (isset($_POST["unadj"]) && isset($_POST["indir"])) {
							$sql .= " '" . $_POST['unadj'] . "', ";
							$sql .= " '" . $_POST['indir'] . "', ";
						}
						if (isset($_POST["sample"])) {
							$sql .= " '" . $_POST['sample'] . "', ";
						}						
						$sql .= " '" . $UserRow['ID_USER'] . "', ";
						$sql .= " '" . stripIllegalChars($_POST['comments']) . "', ";
						$sql .= " GetDate() ";
						$sql .= " ) ";
						QueryDatabase($sql, $results);

						if ($results == '1') {

							if ($_POST['code'] == '100') {
								$sql2  = "select ";
								$sql2 .= "	top 30 *";
								$sql2 .= " from ";
								$sql2 .= "	nsa.DCAPPROVALS a ";
								$sql2 .= " where ";
								$sql2 .= " 	CODE_APP = '100' ";
								$sql2 .= " order by ";
								$sql2 .= " 	DATE_APP desc ";

								QueryDatabase($sql2, $results2);
								$strRet = "	<table class='sample'>\n";
								$strRet .= "	<tr class='sample'>\n";
								$strRet .= "		<th class='sample'>Approved Date</td>\n";
								$strRet .= "		<th class='sample'>Badges Approved</td>\n";
								$strRet .= "		<th class='sample'>Approved By</td>\n";
								$strRet .= "		<th class='sample'>Approved On</td>\n";
								$strRet .= "		<th class='sample'>Comments</td>\n";
								$strRet .= "	</tr>\n";
								while ($row2 = mssql_fetch_assoc($results2)) {

									$strRet .= "	<tr class='sample'>\n";
									$strRet .= "		<td class='sample'>" . $row2['DATE_APP'] . "</td>\n";
									$strRet .= "		<td class='sample'>" . $row2['BADGE_APP'] . "</td>\n";
									$strRet .= "		<td class='sample'>" . $row2['APP_BY_ID_USER'] . "</td>\n";
									$strRet .= "		<td class='sample'>" . $row2['DATE_ADD'] . "</td>\n";
									$strRet .= "		<td class='sample'>" . $row2['COMMENTS'] . "</td>\n";
									$strRet .= "	</tr>\n";
								}
								$strRet .= "	</table>\n";

								$a_to  = GetEmailSubscribers('100');
								foreach ($a_to as $to) {
									if ($to <> '0') {
										$subject = "Timecards approved for " . $_POST['dateapp'];
										$body = "HR approved timecards for " . $_POST['dateapp'] . ".\r\nTeam Activity logs are ready for approval." . "\r\n\r\n" .
											"Comments: " . "\r\n" . stripIllegalChars($_POST['comments']);

										$headers = "From: eProduction@thinknsa.com" . "\r\n" .
											"X-Mailer: PHP/" . phpversion();
										if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
											$to = "gvandyne@thinknsa.com";
										}
										error_log("100: " . $to);
										mail($to, $subject, $body, $headers);
									}
								}

							}


							if ($_POST['code'] == '200') {

							}



							if (($_POST['code'] == '200') || ($_POST['code'] == '201') || ($_POST['code'] == '300')) {
								$strRet = '';
								$resApp = checkApprovals($_POST['code'], $_POST["dateapp"], $_POST["badge"]);

								if (mssql_num_rows($resApp) > 0) {
									while ($row = mssql_fetch_assoc($resApp)) {
										$strRet .= "<div id='div_signoff' name='div_signoff'>\n";
										$strRet .= "<table class='sample'>\n";
										$strRet .= "	<th class='sample' colspan=3>Supervisor Approval</th>\n";
										$strRet .= "	<td id='x_signoff' onclick=\"closeDiv('div_signoff')\" TITLE='Remove Table'>X</td>\n";
										$strRet .= "	<tr>\n";
										$strRet .= "		<td><b>Approved by: </b></td>\n";
										$strRet .= "		<td>" . $row['APP_BY_ID_USER'] . "</td>\n";
										$strRet .= "	</tr>\n";
										$strRet .= "	<tr>\n";
										$strRet .= "		<td><b>On: </b></td>\n";
										$strRet .= "		<td>" . $row['DATE_ADD'] . "</td>\n";
										$strRet .= "	<tr>\n";
										$strRet .= "		<td><b>Comments: </b></td>\n";
										$strRet .= "		<td>" . $row['COMMENTS'] . "</td>\n";
										$strRet .= "	</tr>\n";
										$strRet .= "</table>\n";
										$strRet .= "	</br>\n";
										$strRet .= "</div>\n";
									}

									if ($_POST['code'] == '200') {
										$num_teams = GetNumTeams();
										$resApp_a = checkApprovals('200', $_POST["dateapp"], '%');
										error_log(" " .mssql_num_rows($resApp_a) ." of " . $num_teams);

										if (mssql_num_rows($resApp_a) >= $num_teams) {
											$a_to  = GetEmailSubscribers('200');
											$toList = '';
											foreach ($a_to as $to) {
												if ($to <> '0') {
													if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
														$to = "gvandyne@thinknsa.com";
													}
													$toList .= "," . $to;
												}
											}
											$toList = substr($toList,1);
											$subject = "All Activity Logs approved for " . $_POST['dateapp'];
											$body = "Supervisors have approved all Activity Logs for " . $_POST['dateapp'] . ".\r\nThe Dashboard is ready for approval." . "\r\n\r\n";

											$headers = "From: eProduction@thinknsa.com" . "\r\n" .
												"X-Mailer: PHP/" . phpversion();

											error_log("200: " . $toList);
											mail($toList, $subject, $body, $headers);
										}
									}

									if ($_POST['code'] == '300') {
										$a_to  = GetEmailSubscribers('300');
										$toList = '';
										foreach ($a_to as $to) {
											if ($to <> '0') {
												if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
													$to = "gvandyne@thinkNSA.com";
												}
												$toList .= "," . $to;
											}
										}
										$toList = substr($toList,1);
										$subject = "Dashboard has been approved for " . $_POST['dateapp'];
										$body = "The Dashboard has been approved for " . $_POST['dateapp'] . ".\r\nData Collections is ready to be posted." . "\r\n\r\n" .
											"Comments: " . "\r\n" . stripIllegalChars($_POST['comments']);

										$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
											"X-Mailer: PHP/" . phpversion();

										error_log("300: " . $to);
										mail($toList, $subject, $body, $headers);

										/////////////////////////////
										// CHECK FOR ONE ITEM TEAMS
										/////////////////////////////
										$a = array();
										$list = '';
										$listPct = '';
										$sqla  = "select ";
										$sqla .= " 	e.ID_BADGE, ";
										$sqla .= " 	e.NAME_EMP, ";
										$sqla .= " 	ltrim(e.ID_BADGE) + ' - ' + e.NAME_EMP as BADGE_NAME, ";
										$sqla .= " 	a.EARNED_MINS, ";
										$sqla .= " 	a.ACTUAL_MINS ";
										$sqla .= " FROM nsa.DCEMMS_EMP e ";
										$sqla .= " left join nsa.DCAPPROVALS a ";
										$sqla .= " on ltrim(e.ID_BADGE) = a.BADGE_APP ";
										$sqla .= " and a.DATE_APP = '" . $_POST['dateapp'] . "' ";
										$sqla .= " and a.CODE_APP = '200' ";
										$sqla .= " WHERE e.TYPE_BADGE = 'X' ";
										$sqla .= " and e.CODE_ACTV = 0 ";
										$sqla .= " order by ID_BADGE asc";
										QueryDatabase($sqla, $resultsa);

										while ($rowa = mssql_fetch_assoc($resultsa)) {
											/////////////////////////////
											// CHECK FOR EXTREME PERCENTS
											/////////////////////////////
											$overal_eff = round(($rowa['EARNED_MINS'] / $rowa['ACTUAL_MINS']) * 100,2);
											if ((($overal_eff <= 75) OR ($overal_eff >= 125)) && $overal_eff <> 0) {
												$a[$rowa['BADGE_NAME']] = $overal_eff;
											}

											$sqlb  = "SELECT ";
											$sqlb .= "	distinct(sh.ID_ITEM_PAR) ";
											$sqlb .= " FROM ";
											$sqlb .= "	nsa.DCUTRX_NONZERO_PERM nz ";
											$sqlb .= " LEFT JOIN nsa.SHPORD_HDR sh ";
											$sqlb .= "	on nz.ID_SO = sh.ID_SO ";
											$sqlb .= " WHERE FLAG_DEL = '' ";
											$sqlb .= "	and nz.DATE_TRX = '". $_POST['dateapp'] . "' ";
											$sqlb .= "	and nz.CODE_TRX = '102' ";
											$sqlb .= "	and nz.ID_BADGE = '" . $rowa['ID_BADGE'] . "' ";
											$sqlb .= " ORDER BY ID_ITEM_PAR ";
											QueryDatabase($sqlb, $resultsb);

											if (mssql_num_rows($resultsb) == '1') {
												while ($rowb = mssql_fetch_assoc($resultsb)) {
													$list .= "\r\n" . $rowa['BADGE_NAME'] . ":   " . $overal_eff . "%:   " . $rowb['ID_ITEM_PAR'];
												}
											}
										}
										if ($list <> '') {
											$subject = "One Item Teams for " . $_POST['dateapp'];
											$body = "The following teams worked on a single item on " . $_POST['dateapp'] . ".\r\n\r\n";
											$body .= $list;
											$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
												"X-Mailer: PHP/" . phpversion();
/*
											$aa_to  = GetEmailSubscribers('001');
											$toList = '';
											foreach ($aa_to as $to) {
												if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
													$to = "gvandyne@thinkNSA.com";
												}
												$toList .= "," . $to;
											}
											$toList = substr($toList,1);
*/
											$toList = "Group-FactorySupervisors@thinknsa.com,jmartin@thinknsa.com";
											error_log("One Item Teams email sent to: " . $toList);
											mail($toList, $subject, $body, $headers);

										}
										if (count($a) > 0) {
											asort($a);
											foreach ($a as $team => $pct) {
												$listPct .= "\r\n" . $team . ":   " . $pct . "%";
											}
											$subject = "Teams with Percentage Extremes for " . $_POST['dateapp'];
											$body = "The following teams had Efficiency percentages <= 75 or >= 125 on " . $_POST['dateapp'] . ".\r\n\r\n";
											$body .= $listPct;
											$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
												"X-Mailer: PHP/" . phpversion();
/*
											$aa_to  = GetEmailSubscribers('001');
											$toList = '';
											foreach ($aa_to as $to) {
												if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
													$to = "gvandyne@thinkNSA.com";
												}
												$toList .= "," . $to;
											}
											$toList = substr($toList,1);
*/											
											$toList = "Group-FactorySupervisors@thinknsa.com,jmartin@thinknsa.com";
											error_log("Extreme Percent email sent to: " . $toList);
											mail($toList, $subject, $body, $headers);
										}
									}
								}
							}
						}
					} else {
						$strRet = "<div id='div_errordup' name='div_errordup'>\n";
						$strRet .= "<table class='sample'>\n";
						$strRet .= "	<th class='sample' colspan=3>ERROR: DUPLICATE RECORD</th>\n";
						$strRet .= "	<td id='x_errordup' onclick=\"closeDiv('div_errordup')\" TITLE='Remove Table'>X</td>\n";
						$strRet .= "	<tr>\n";
						//$strRet .= "		<td><b>Approved by: </b></td>\n";
						//$strRet .= "		<td>" . $row['APP_BY_ID_USER'] . "</td>\n";
						//$strRet .= "	</tr>\n";
						//$strRet .= "	<tr>\n";
						//$strRet .= "		<td><b>On: </b></td>\n";
						//$strRet .= "		<td>" . $row['DATE_ADD'] . "</td>\n";
						//$strRet .= "	<tr>\n";
						//$strRet .= "		<td><b>Comments: </b></td>\n";
						//$strRet .= "		<td>" . $row['COMMENTS'] . "</td>\n";
						//$strRet .= "	</tr>\n";
						$strRet .= "</table>\n";
						$strRet .= "	</br>\n";
						$strRet .= "</div>\n";
					}

					$ret = $strRet;
					echo json_encode(array("returnValue"=> $ret));
				}

			} else {
				print "					<p class='warning'>Permission Denied!</p>\n";
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

?>
