<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeader('Daily Team Production Completion Log','default.css');
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
				$sql .= " 	NAME_SORT";
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



				print("		<h2>" . $row['NAME_SORT'] ."</h2>");
				print("		<h4>" . $DateFrom . " -- " . $DateTo . "</h4>");

				print("<table class='sample'>");
				//print("	<tr>");
				//print("		<td colspan='2'>Team Name</td>");
				//print("		<td colspan='2'>" . $row['NAME_SORT'] . "</td>");
				//print("	</tr>");
				//print("	<tr>");
				//print("		<td>Date From</td>");
				//print("		<td>" . $DateFrom . "</td>");
				//print("		<td>Date To</td>");
				//print("		<td>" . $DateTo . "</td>");
				//print("	</tr>");
				print("	<th class='sample'>Shop Order</th>");
				print("	<th class='sample'>Oper ID</th>");
				print("	<th class='sample'>Oper Stat</th>");
				print("	<th class='sample'>Time Stamp</th>");
				print("	<th class='sample'>Stamp Type</th>");
				print("	<th class='sample'>Item #</th>");
				print("	<th class='sample'>Units Completed</th>");
				print("	<th class='sample'>Std. Unit Time</th>");
				print("	<th class='sample'>Total Earned Minutes</th>");



				$sql =  "select h.ID_ITEM_PAR, p.ID_SO, p.QTY_GOOD, o.HR_MACH_SF, p.ID_OPER,";
				$sql .= "  o.STAT_REC_OPER_1, p.CODE_TRX, convert(char(10),p.DATE_TRX,101) as DATE_TRX2, p.*  ";
				$sql .= " from nsa.DCUTRX_NONZERO_PERM p, nsa.SHPORD_HDR h, nsa.SHPORD_OPER o";
				$sql .= " where ltrim(p.ID_BADGE) = '" . $Team ."'";
				$sql .= " and p.ID_SO = h.ID_SO";
				$sql .= " and o.ID_SO = h.ID_SO";
				$sql .= " and o.ID_OPER = p.ID_OPER";
				//$sql .= " and o.FLAG_DIR_INDIR = 'D'";
				//$sql .= " and o.STAT_REC_OPER_1 = 'C'";
				$sql .= " and p.DATE_TRX between '" . $DateFrom . "' and '" . $DateTo . "'";


				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					print("	<tr class='sample'>");

					$TotEarnMin = $row['QTY_GOOD'] * $row['HR_MACH_SF'] * 60;
					$GTotUnitsC += $row['QTY_GOOD'];
					$GTotStdMin += $row['HR_MACH_SF'];
					$GTotEarnMin += $TotEarnMin ;
					$status = GetStrStatOperCode($row['STAT_REC_OPER_1']);
					$trxType = GetStrCodeTrx($row['CODE_TRX']);

					print("		<td class='sample'>" . $row['ID_SO'] . "</td>");
					print("		<td class='sample'>" . $row['ID_OPER'] . "</td>");
					print("		<td class='sample'>" . $status . "</td>");

					print("		<td class='sample'>" . $row['DATE_TRX2'] . " " . $row['TIME_TRX'] . "</td>");
					print("		<td class='sample'>" . $trxType . "</td>");

					print("		<td class='sample'>" . $row['ID_ITEM_PAR'] . "</td>");
					print("		<td class='sample'>" . $row['QTY_GOOD'] . "</td>");
					print("		<td class='sample'>" . $row['HR_MACH_SF'] . "</td>");
					print("		<td class='sample'>" . $TotEarnMin ."</td>");

					print("	</tr>");
				}
				print("	<tr>");
				print("		<td colspan='7'></td>");
				print("	</tr>");
				print("	<tr>");
				print("		<td colspan='6'><b>Total</b></td>");
				print("		<td><b>" . $GTotUnitsC . "</b></td>");
				print("		<td><b>" . $GTotStdMin . "</b></td>");
				print("		<td><b>" . $GTotEarnMin . "</b></td>");
				print("	</tr>");
				print("</table>");

				$GoalUnits = 0;
				$EfficPct = 0;

				print("<table class='sample'>");
				print("	<th colspan='2'>Scoreboard @ End of Day</th>");
				print("	<tr>");
				print("		<td>Goal Units</td>");
				print("		<td>" . $GoalUnits . "</td>");
				print("	</tr>");
				print("	<tr>");
				print("		<td>Actual Units</td>");
				print("		<td>" . $GTotUnitsC . "</td>");
				print("	</tr>");
				print("	<tr>");
				print("		<td>Efficiency %</td>");
				print("		<td>" . $EfficPct . "</td>");
				print("	</tr>");
				print("</table>");


			} else {

				print(" <form action='efficiency.php' method='POST'>");
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
				$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_SORT as BADGE_NAME,";
				$sql .= " 	ltrim(ID_BADGE) as ID_BADGE,";
				$sql .= " 	NAME_SORT";
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

	if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["team"]))  {
		PrintFooter("efficiency.php");
	} else {
		PrintFooter("index.php");
	}

?>
