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
			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["supr"]) && isset($_POST["orderby"]))  {

				$sql = " IF OBJECT_ID('tempdb..#temp_ord') IS NOT NULL";
				$sql .= "	DROP TABLE #temp_ord";
				QueryDatabase($sql, $results);

				$sql = " create table #temp_ord";
				$sql .= "	(";
				$sql .= "		ID_BADGE varchar(9),";
				$sql .= "		BADGE_NAME varchar(30),";
				$sql .= "		SUPRVSR_NAME varchar(9),";
				$sql .= "		OVERALL_EFF numeric(6,3),";
				$sql .= "		rowid int IDENTITY(1,1) ";
				$sql .= "	)";
				QueryDatabase($sql, $results);

				$DateFrom = $_POST["df"];
				$DateTo = $_POST["dt"];
				$Supervisor = $_POST["supr"];
				$OrderBy = $_POST["orderby"];

				$ret .= "		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>\n";
				$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";
				$ret .= "		<input type='hidden' id='df' name='df' value='" . $DateFrom . "'>\n";
				$ret .= "		<input type='hidden' id='dt' name='dt' value='" . $DateTo . "'>\n";

				$ret .= " <table class='sample'>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th class='sample'>Team</th>\n";
				$ret .= " 		<th class='sample'>Spvsr</th>\n";
				$ret .= " 		<th class='sample'>Percent</th>\n";
				if ($DateFrom == $DateTo) {
					$ret .= " 		<th class='sample'>Apv By</th>\n";
					$ret .= " 		<th class='sample'>Apv On</th>\n";
					$ret .= " 		<th class='sample'>Comments</th>\n";
				}
				$ret .= " 	</tr>\n";

				$sql =  "select ";
				$sql .= " 	ltrim(e1.ID_BADGE) + ' - ' + e1.NAME_EMP as BADGE_NAME,";
				$sql .= " 	ltrim(e1.ID_BADGE) as ID_BADGE,";
				$sql .= " 	e1.NAME_EMP,";
				$sql .= " 	e1.ID_BADGE_SUPRVSR,";
				$sql .= " 	e2.ID_USER as SUPRVSR_NAME";
				$sql .= " from ";
				$sql .= " 	nsa.DCEMMS_EMP e1, ";
				$sql .= " 	nsa.DCWEB_AUTH e2 ";
				$sql .= " where ";
				$sql .= " 	e1.TYPE_BADGE = 'X'";
				$sql .= " 	and";
				$sql .= " 	e1.CODE_ACTV = '0'";
				$sql .= " 	and";
				$sql .= " 	e1.ID_BADGE_SUPRVSR = e2.ID_BADGE";
				$sql .= " 	and";
				$sql .= " 	ltrim(e1.ID_BADGE_SUPRVSR) <> '' ";
				if ($Supervisor != 'ALL') {
					$sql .= " 	and";
					$sql .= " 	ltrim(e1.ID_BADGE_SUPRVSR) = '" . $Supervisor . "' ";
				}
				$sql .= " order by BADGE_NAME asc";
				QueryDatabase($sql, $results);
				$num_teams = GetNumTeams();

				$Gtot_earned_mins = '0';
				$Gtot_actual_mins = '0';


				while ($row = mssql_fetch_assoc($results)) {
					$ovral_eff = '';
					$tot_earned_mins = '0';
					$tot_actual_mins = '0';

					$dfTS = strtotime($DateFrom);
					$dtTS = strtotime($DateTo);
					$loopTS = $dfTS;

					while ($loopTS <= $dtTS) {
						//FOR EACH DAY BETWEEN DF AND DT
						$loopDT = date('Y-m-d', $loopTS);
						$resApp = checkLatestApproval("'200'", $loopDT, $row['ID_BADGE']);
						if (mssql_num_rows($resApp) > 0) {
							while ($row2 = mssql_fetch_assoc($resApp)) {
								$tot_earned_mins += $row2['EARNED_MINS'];
								$tot_actual_mins += $row2['ACTUAL_MINS'];
								$Gtot_earned_mins += $row2['EARNED_MINS'];
								$Gtot_actual_mins += $row2['ACTUAL_MINS'];
							}
						}
						$loopTS = strtotime("+1 days" , $loopTS);
					}

					$ovral_eff = round(($tot_earned_mins / $tot_actual_mins) * 100,2);

					$sql2  = " insert into #temp_ord( ";
					$sql2 .= "  ID_BADGE, ";
					$sql2 .= "  BADGE_NAME, ";
					$sql2 .= "  SUPRVSR_NAME, ";
					$sql2 .= "  OVERALL_EFF ";
					$sql2 .= " ) VALUES ( ";
					$sql2 .= "  '" . $row['ID_BADGE'] . "', ";
					$sql2 .= "  \"" . $row['BADGE_NAME'] . "\", ";
					$sql2 .= "  '" . $row['SUPRVSR_NAME'] . "', ";
					$sql2 .= "  " . $ovral_eff . " ";
					$sql2 .= " ) ";
					QueryDatabase($sql2, $results2);
				}

				$sql =  "select ";
				$sql .= " * ";
				$sql .= " from #temp_ord ";
				$sql .= " order by " . $OrderBy;
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$pctClass = GetColorPct($row['OVERALL_EFF']);
					$ret .= "					<tr class='sample' id='rowAppStat_" . $row['ID_BADGE'] . "'>\n";
					$ret .= "						<td class='sample' id='td_" . $row['ID_BADGE'] . "' name='td_" . $row['ID_BADGE'] . "'>". $row['BADGE_NAME'] . "</td>\n";
					$ret .= "						<td class='sample'>". $row['SUPRVSR_NAME'] . "</td>\n";
					$ret .= "						<td class='sample'><div id='div_" . $row['ID_BADGE'] . "' name='div_" . $row['ID_BADGE'] . "'><font class='" . $pctClass . "' onclick=\"goToActivityPopUp('" . $row['ID_BADGE'] . "')\">" . $row['OVERALL_EFF'] . "</font></div></td>\n";
					$ret .= "					</tr>\n";
				}

				$ret .= " </table>\n";
				$ret .= " </br>\n";

				$Govral_eff = round(($Gtot_earned_mins / $Gtot_actual_mins) * 100,2);
				$pctClass = GetColorPct($Govral_eff);

				$ret .= "<div id='div_overall' name='div_overall'>\n";
				$ret .= "<table class='sample'>\n";
				$ret .= "	<th class='sample'>Overall Efficiency</th>\n";
				$ret .= "	<td id='x_overall' onclick=\"closeDiv('div_overall')\" TITLE='Remove Table'>X</td>\n";
				$ret .= "	<tr>\n";
				$ret .= "		<td><font class='" . $pctClass . "'>" . $Govral_eff . "</font></td>\n";
				$ret .= "	</tr>\n";
				$ret .= "</table>\n";
				$ret .= "	</br>\n";
				$ret .= "</div>\n";



				if ($UserRow['PERM_MGMT'] == '1')  {
					if ($DateFrom == $DateTo) {

						$resApp = checkApprovals('200', $DateFrom, '%');
						if (mssql_num_rows($resApp) >= $num_teams) {
							////////////////
							//ALL TEAMS APPROVED BY SUPERVISORS
							////////////////
							$res300 = checkApprovals('300', $DateFrom, 'ALL');

							if (mssql_num_rows($res300) > 0) {
								////////////////
								//HAS BEEN APPROVED BY MGMT
								////////////////
								while ($row = mssql_fetch_assoc($res300)) {
									$ret .= "<div id='div_signoff' name='div_signoff'>\n";
									$ret .= "<table class='sample'>\n";
									$ret .= "	<th class='sample' colspan=3>Supervisor Approval</th>\n";
									$ret .= "	<td id='x_signoff' onclick=\"closeDiv('div_signoff')\" TITLE='Remove Table'>X</td>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>Approved by: </b></td>\n";
									$ret .= "		<td>" . $row['APP_BY_ID_USER'] . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>On: </b></td>\n";
									$ret .= "		<td>" . $row['DATE_ADD'] . "</td>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td><b>Comments: </b></td>\n";
									$ret .= "		<td>" . $row['COMMENTS'] . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									$ret .= "	</br>\n";
									$ret .= "</div>\n";
								}
							} else {
								////////////////
								//TO BE APPROVED BY MGMT
								////////////////
								$ret .= "<div id='div_dash_approve' name='div_dash_approve'>\n";
								$ret .= "<input type='hidden' id='earned' value='0'>\n";
								$ret .= "<input type='hidden' id='actual' value='0'>\n";
								$ret .= "<input type='hidden' id='txt_min' value=''>\n";
								$ret .= "<table class='sample'>\n";
								$ret .= "	<th class='sample' colspan=3>Supervisor Approval</th>\n";
								$ret .= "	<td id='x_signoff' onclick=\"closeDiv('div_dash_approve')\" TITLE='Remove Table'>X</td>\n";
								$ret .= "	<tr>\n";
								$ret .= "		<td colspan=2><b>" . $UserRow['NAME_EMP'] . "</b></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr>\n";
								//$ret .= "		<td><input type='checkbox' id='chk_approve' value='1' /> Approve</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "			<select id='select_app'>\n";
								$ret .= "				<option value='0'>-- Select --</option>\n";
								$ret .= "				<option value='300'>Approve</option>\n";
								$ret .= "			</select>\n";
								$ret .= "		</td>\n";
								$ret .= "		<td>Comments: <input type='text' id='cmts_approve' name='cmts_approve' /></td>\n";
								$ret .= "		<td><input type='submit' id='sub_approve' value='Submit' onClick=\"insertDCApprovalJS('ALL','" . $DateFrom . "','div_dash_approve')\" /></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "</table>\n";
								$ret .= "	</br>\n";
								$ret .= "</div>\n";

							}


						} else {
							$ret .= "		<h3>*There are outstanding teams that have not yet been approved</h3>\n";
						}

					}
				}

				//print("<body onLoad=\"dashsubValue('" . $l_teams . "')\">");

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
