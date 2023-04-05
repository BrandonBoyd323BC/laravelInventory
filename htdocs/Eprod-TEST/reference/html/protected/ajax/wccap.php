<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

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
			if (isset($_POST["dt"]) && isset($_POST["StatCode"]) && isset($_POST["selWC"]))  {
				$DateDue = $_POST["dt"];
				$StatCode = $_POST["StatCode"];
				$selWC = $_POST["selWC"];
				
				if ($_POST["StatCode"] == 'ALL') {
					$StatCode = "S','A','R','U";
				}

				$ret .= "		<h3>Cutoff Due Date: " . $DateDue . "</h3>\n";
				$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);

				$sql =  "SELECT ";
				$sql .= " wc.ID_WC, ";
				$sql .= " wc.DESCR_WC ";
				$sql .= " FROM nsa.tables_loc_dept_wc wc ";
				$sql .= " WHERE ";
				$sql .= " wc.ID_WC between '1999' and '7999' ";
				if ($selWC <> "ALL") {
					$sql .= " and wc.ID_WC = '".$selWC."'";
				}
				

				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					//////////////
					//WORKCENTER
					//////////////
					$ret .= "<div id='div_" . $row['ID_WC'] . "' name='div_" . $row['ID_WC'] . "'>\n";
					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th colspan=6>\n";
					$ret .= "				<font>" . $row['ID_WC'] . " - " . $row['DESCR_WC'] ."</font>\n";
					$ret .= " 		</th>\n";
					$ret .= "	<td id='x_" . $row['ID_WC'] . "' name='x_" . $row['ID_WC'] . "' onclick=\"closeDiv('div_" . $row['ID_WC'] . "')\" TITLE='Remove Table'>X</td>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Team</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>No. Members</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Daily Cap (minutes)</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Open Minutes</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					$ret .= "				<font>Days Open</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 		<th>\n";
					switch ($StatCode) {
						case "S" :
							$hdrTxt = 'Days Cut';
							break;
						case "R" :
							$hdrTxt = 'Days Released';
							break;
						case "A" :
							$hdrTxt = 'Days Active';
							break;
						case "S','A','R','U" :
							$hdrTxt = 'Days ALL';
							break;
					}
					$ret .= "				<font>" . $hdrTxt . "</font>\n";
					$ret .= " 		</th>\n";
					$ret .= " 	</tr>\n";

					$sql2  =  "SELECT ";
					$sql2 .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
					$sql2 .= " ID_BADGE,";
					$sql2 .= " NAME_EMP";
					$sql2 .= " FROM nsa.DCEMMS_EMP ";
					$sql2 .= " WHERE TYPE_BADGE = 'X'";
					$sql2 .= " and CODE_ACTV = '0'";
					$sql2 .= " and KEY_HOME_3RD = '" . $row['ID_WC'] . "'";
					$sql2 .= " and ltrim(ID_BADGE) < '900'";  //no longer include 2nd shift teams per Brian G 2/14/2017
					$sql2 .= " ORDER BY BADGE_NAME asc";
					QueryDatabase($sql2, $results2);

					$no_mems_wc = 0;
					$cap_wc = 0;
					$not_done = 0;
					$snot_done = 0;

					while ($row2 = mssql_fetch_assoc($results2)) {
						$ret .= " 	<tr>\n";
						$ret .= " 		<td>\n";
						$ret .= "				<font>" . $row2['BADGE_NAME'] . "</font>\n";
						$ret .= " 		</td>\n";

						$sql3  = "SELECT ";
						$sql3 .= " COUNT(*) as no_mems";
						$sql3 .= " FROM nsa.DCEMMS_EMP ";
						$sql3 .= " WHERE TYPE_BADGE = 'E'";
						$sql3 .= " and CODE_ACTV = '0'";
						$sql3 .= " and STAT_BADGE = 'A' ";
						$sql3 .= " and ltrim(ID_BADGE_TEAM_STD) = '" . trim($row2['ID_BADGE']) . "'";
						QueryDatabase($sql3, $results3);

						while ($row3 = mssql_fetch_assoc($results3)) {
							/////2nd shift members are counted as half a member
							$no_mems = $row3['no_mems'];
							if (trim($row2['ID_BADGE']) >= 900 && trim($row2['ID_BADGE']) <= 999) {
								$no_mems = $no_mems/2;
							} 

							$cap_team = $no_mems * 455;	
							$cap_wc += $cap_team;
							$no_mems_wc += $no_mems;

							$ret .= " 		<td>\n";
							$ret .= "				<font>" . $no_mems . "</font>\n";
							$ret .= " 		</td>\n";
							$ret .= " 		<td>\n";
							$ret .= "				<font>" . $cap_team . "</font>\n";
							$ret .= " 		</td>\n";
						}
						$ret .= " 	</tr>\n";
					}

					$sql3  = "SELECT ";
					$sql3 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
					$sql3 .= " so.QTY_CMPL_UEDT as O_QTY_CMPL, ";
					$sql3 .= " so.QTY_ORD as O_QTY_ORD ";
					$sql3 .= " FROM nsa.shpord_hdr sh ";
					$sql3 .= " LEFT JOIN nsa.shpord_oper so ";
					$sql3 .= " on so.id_so=sh.id_so ";
					$sql3 .= " and so.sufx_SO=sh.sufx_so ";
					$sql3 .= " LEFT JOIN nsa.routms_oper rto ";
					$sql3 .= " on sh.id_item_par=rto.id_item ";
					$sql3 .= " and so.id_oper=rto.id_oper ";
					$sql3 .= " WHERE sh.stat_rec_so in('" . $StatCode . "') ";
					$sql3 .= " and (sh.date_due_ord<='" . $DateDue . "' OR sh.date_due_ord is null) ";
					$sql3 .= " and ltrim(rto.ID_WC) = '" . $row['ID_WC'] . "' ";
					$sql3 .= " and sh.ID_SO <> 'PROD' ";
					$sql3 .= " and sh.ID_SO not like 'SAMPLE%' ";
					$sql3 .= " and rto.ID_RTE = 'TSS' ";
					QueryDatabase($sql3, $results3);

					while ($row3 = mssql_fetch_assoc($results3)) {
						$min = $row3['RTO_HR_MACH_SR'] * 60;
						$qty = ($row3['O_QTY_ORD'] - $row3['O_QTY_CMPL']);
						$not_done += ($min * $qty);
					}

					$daysOut = round(($not_done / $cap_wc), 2);
					if ($cap_wc == 0) {
						$daysOut = "Indefinite";
					}
					$font_class = GetColorDaysOut($daysOut);

					$sql4  = "SELECT ";
					$sql4 .= " rto.HR_MACH_SR as RTO_HR_MACH_SR, ";
					$sql4 .= " so.QTY_CMPL as O_QTY_CMPL, ";
					$sql4 .= " so.QTY_ORD as O_QTY_ORD, ";
					$sql4 .= " sh.ID_ITEM_PAR, ";
					$sql4 .= " so.ID_SO ";
					$sql4 .= " FROM nsa.shpord_hdr sh ";
					$sql4 .= " LEFT JOIN nsa.shpord_oper so ";
					$sql4 .= " on so.id_so=sh.id_so ";
					$sql4 .= " and so.sufx_SO=sh.sufx_so ";
					$sql4 .= " LEFT JOIN nsa.routms_oper rto ";
					$sql4 .= " on sh.id_item_par=rto.id_item ";
					$sql4 .= " and so.id_oper=rto.id_oper ";
					$sql4 .= " WHERE sh.stat_rec_so in('" . $StatCode . "') ";
					$sql4 .= " and (sh.date_due_ord<='" . $DateDue . "' OR sh.date_due_ord is null) ";
					$sql4 .= " and ltrim(rto.ID_WC) = '" . $row['ID_WC'] . "' ";
					$sql4 .= " and sh.ID_SO <> 'PROD' ";
					$sql4 .= " and sh.ID_SO not like 'SAMPLE%' ";
					$sql4 .= " and rto.ID_RTE = 'TSS' ";
					$sql4 .= " ORDER BY sh.ID_ITEM_PAR ";
					QueryDatabase($sql4, $results4);

					$ret2  = "<div id='div_so_" . $row['ID_WC'] . "' name='div_so_" . $row['ID_WC'] . "'>\n";
					$ret2 .= " <table class='sample'>\n";
					$ret2 .= " 	<tr>\n";
					$ret2 .= " 		<th>\n";
					$ret2 .= "				<font>ID SO</font>\n";
					$ret2 .= " 		</th>\n";
					$ret2 .= " 		<th>\n";
					$ret2 .= "				<font>Item</font>\n";
					$ret2 .= " 		</th>\n";
					$ret2 .= " 		<th>\n";
					$ret2 .= "				<font>Qty Ord</font>\n";
					$ret2 .= " 		</th>\n";
					$ret2 .= " 		<th>\n";
					$ret2 .= "				<font>Qty Cmpl</font>\n";
					$ret2 .= " 		</th>\n";
					$ret2 .= " 		<th>\n";
					$ret2 .= "				<font>Mins Open</font>\n";
					$ret2 .= " 		</th>\n";
					$ret2 .= "		<td id='x_so_" . $row['ID_WC'] . "' name='x_so_" . $row['ID_WC'] . "' onclick=\"closeDiv('div_so_" . $row['ID_WC'] . "')\" TITLE='Remove Table'>X</td>\n";
					$ret2 .= " 	</tr>\n";
					while ($row4 = mssql_fetch_assoc($results4)) {
						$smin = $row4['RTO_HR_MACH_SR'] * 60;
						$sqty = ($row4['O_QTY_ORD'] - $row4['O_QTY_CMPL']);
						$sopentime = ($smin * $sqty);
						$snot_done += $sopentime;
						if ($sqty > 0) {
							$ret2 .= " 	<tr>\n";
							$ret2 .= " 		<td>\n";
							$ret2 .= "				<font>" . $row4['ID_SO'] . "</font>\n";
							$ret2 .= " 		</td>\n";
							$ret2 .= " 		<td>\n";
							$ret2 .= "				<font>" . $row4['ID_ITEM_PAR'] . "</font>\n";
							$ret2 .= " 		</td>\n";
							$ret2 .= " 		<td>\n";
							$ret2 .= "				<font>" . $row4['O_QTY_ORD'] . "</font>\n";
							$ret2 .= " 		</td>\n";
							$ret2 .= " 		<td>\n";
							$ret2 .= "				<font>" . $row4['O_QTY_CMPL'] . "</font>\n";
							$ret2 .= " 		</td>\n";
							$ret2 .= " 		<td>\n";
							$ret2 .= "				<font>" . $sopentime . "</font>\n";
							$ret2 .= " 		</td>\n";
							$ret2 .= " 	</tr>\n";
						}
					}
					$ret2 .= " </table>\n";
					$ret2 .= " </div>\n";
					$ret2 .= " 	</br>\n";

					$sdaysOut = round(($snot_done / $cap_wc), 2);
					if ($cap_wc == 0) {
						$sdaysOut = "Indefinite";
					}

					$sfont_class = GetColorDaysOut($sdaysOut);

					$ret .= " 	<tr>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font><b>Totals</b></font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font><b>" . $no_mems_wc . "</b></font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font><b>" . $cap_wc . "</b></font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font><b>" . number_format(round($not_done,0)) . "</b></font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font class='" . $font_class . "'><b>" . $daysOut . "</b></font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font class='" . $sfont_class . "'><b>" . $sdaysOut . "</b></font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 	</tr>\n";
					$ret .= " </table>\n";
					if ($DEBUG) {
						$ret .= " 	<h3>Days Open Query</h3>\n";
						$ret .= "<p>" . $sql3 . "</p>";
						$ret .= " 	<h3>Days Cut Query</h3>\n";
						$ret .= "<p>" . $sql4 . "</p>";
					}
					$ret .= " </div>\n";
					$ret .= $ret2;
				}

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
