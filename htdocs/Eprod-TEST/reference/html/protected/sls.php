<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Sales','default.css','realtime.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			if ((isset($_POST["df"])) && (isset($_POST["dt"])) && (isset($_POST["tf"])) && (isset($_POST["tt"]))) {
				$DateFrom = str_replace('-','',$_POST["df"]);
				$DateTo = str_replace('-','',$_POST["dt"]);
				$TerrFrom = $_POST["tf"];
				$TerrTo = $_POST["tt"];
				error_log("df " . $DateFrom);
				error_log("df " . $DateTo);

				print("		<h3>Date Range: " . $DateFrom . " - " . $DateTo . "</h3>\n");
				print("		<h3>Territory Range: " . $TerrFrom . " - " . $TerrTo . "</h3>\n");
				print("		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n");

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);

				$sql  = " select  ";
				$sql .= " 	nsa.slshst_line.id_item, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_1 as ADD1, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_2 as ADD2, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_3 as ADD3, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_4 as ADD4, ";
				$sql .= " 	* ";
				$sql .= " from  ";
				$sql .= " 	nsa.slshst_hdr ";
				$sql .= " 	left join nsa.slshst_line ";
				$sql .= " 	on ";
				$sql .= " 	nsa.slshst_hdr.id_invc=nsa.slshst_line.id_invc ";
				$sql .= " 	left join nsa.slshst_ship ";
				$sql .= " 	on  ";
				$sql .= " 	nsa.slshst_hdr.id_invc=nsa.slshst_ship.id_invc ";
				$sql .= " 	left join nsa.CUSMAS_shipto ";
				$sql .= " 	on ";
				$sql .= " 	nsa.slshst_hdr.id_cust=nsa.CUSMAS_shipto.id_cust ";
				$sql .= " 	and nsa.slshst_hdr.seq_shipto=nsa.cusmas_shipto.seq_shipto ";
				$sql .= " 	left join nsa.tables_slsrep ";
				$sql .= " 	on  ";
				$sql .= " 	nsa.slshst_hdr.id_slsrep_1=nsa.tables_slsrep.id_slsrep ";
				$sql .= " 	left join nsa.cusmas_soldto ";
				$sql .= " 	on  ";
				$sql .= " 	nsa.slshst_hdr.id_cust=nsa.CUSMAS_soldto.id_cust ";
				$sql .= " where  ";
				$sql .= " 	(nsa.slshst_hdr.date_invc BETWEEN ('" . $DateFrom . "') AND ('" . $DateTo . "'))  ";
				$sql .= " 	and  ";
				$sql .= " 	(nsa.slshst_hdr.id_slsrep_1 between (" . $TerrFrom . ") AND (" . $TerrTo . ")) ";
				$sql .= " order by  ";
				$sql .= " 	nsa.slshst_hdr.id_slsrep_1, ";
				$sql .= " 	nsa.slshst_hdr.id_invc asc ";
				print("<font>Query: " . $sql . "</font>\n");
				QueryDatabase($sql, $results);

				$sum = 0;
				print("		<table class='sample'>\n");
				print("			<tr>\n");
				print("				<th>SlsRep</th>\n");
				print("				<th>INV</th>\n");
				print("				<th>SLS</th>\n");
				print("			</tr>\n");

				while ($row = mssql_fetch_assoc($results)) {
					error_log("SLS " . $row['SLS']);
					//if ($row['DATE_INVC'] != $row['DATE_TRX']) {
						print("			<tr>\n");
						print("				<td>". $row['ID_SLSREP_1'] ."</td>\n");
						print("				<td>". $row['ID_INVC'] ."</td>\n");
						print("				<td>". $row['SLS'] ."</td>\n");
						print("			</tr>\n");
					//}
					$sum += $row['SLS'];
				}
				print("		</table>\n");
				print("		<h4>Method 1: Invoice Date</h4>\n");
				print("		<h4>Sum: " . $sum ."</h4>\n");

				$sql  = " select  ";
				$sql .= " 	nsa.slshst_line.id_item, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_1 as ADD1, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_2 as ADD2, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_3 as ADD3, ";
				$sql .= " 	nsa.CUSMAS_soldto.addr_cust_4 as ADD4, ";
				$sql .= " 	* ";
				$sql .= " from  ";
				$sql .= " 	nsa.slshst_hdr ";
				$sql .= " 	left join nsa.slshst_line ";
				$sql .= " 	on ";
				$sql .= " 	nsa.slshst_hdr.id_invc=nsa.slshst_line.id_invc ";
				$sql .= " 	left join nsa.slshst_ship ";
				$sql .= " 	on  ";
				$sql .= " 	nsa.slshst_hdr.id_invc=nsa.slshst_ship.id_invc ";
				$sql .= " 	left join nsa.CUSMAS_shipto ";
				$sql .= " 	on ";
				$sql .= " 	nsa.slshst_hdr.id_cust=nsa.CUSMAS_shipto.id_cust ";
				$sql .= " 	and nsa.slshst_hdr.seq_shipto=nsa.cusmas_shipto.seq_shipto ";
				$sql .= " 	left join nsa.tables_slsrep ";
				$sql .= " 	on  ";
				$sql .= " 	nsa.slshst_hdr.id_slsrep_1=nsa.tables_slsrep.id_slsrep ";
				$sql .= " 	left join nsa.cusmas_soldto ";
				$sql .= " 	on  ";
				$sql .= " 	nsa.slshst_hdr.id_cust=nsa.CUSMAS_soldto.id_cust ";
				$sql .= " where  ";
				$sql .= " 	(nsa.slshst_line.date_trx BETWEEN ('" . $DateFrom . "') AND ('" . $DateTo . "'))  ";
				$sql .= " 	and  ";
				$sql .= " 	(nsa.slshst_hdr.id_slsrep_1 between (" . $TerrFrom . ") AND (" . $TerrTo . ")) ";
				$sql .= " order by  ";
				$sql .= " 	nsa.slshst_hdr.id_slsrep_1, ";
				$sql .= " 	nsa.slshst_hdr.id_invc asc ";
				print("<font>Query: " . $sql . "</font>\n");
				QueryDatabase($sql, $results);

				$sum = 0;
				print("		<table class='sample'>\n");
				print("			<tr>\n");
				print("				<th>SlsRep</th>\n");
				print("				<th>INV</th>\n");
				print("				<th>SLS</th>\n");
				print("			</tr>\n");
				while ($row = mssql_fetch_assoc($results)) {
					error_log("SLS " . $row['SLS']);
					//if ($row['DATE_INVC'] != $row['DATE_TRX']) {
						print("			<tr>\n");
						print("				<td>". $row['ID_SLSREP_1'] ."</td>\n");
						print("				<td>". $row['ID_INVC'] ."</td>\n");
						print("				<td>". $row['SLS'] ."</td>\n");
						print("			</tr>\n");
					//}
					$sum += $row['SLS'];
				}

				print("		</table>\n");
				print("		<h4>Method 2: Trans Date</h4>\n");
				print("		<h4>Sum: " . $sum ."</h4>\n");
				print("		</br>\n");

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);

			} else {

				print(" <form action='sls.php' method='POST'>");
				print(" <table>");
				print(" 	<tr>");
				print(" 		<td>Enter Start Date: </td>");
				print(" 		<td>");

				$myCalendar = new tc_calendar('df', true, true);
				$myCalendar->setIcon("images/iconCalendar.gif");
				$pastTS = strtotime("-7 days", time());
				$myCalendar->setDate(date('d',$pastTS), date('m',$pastTS), date('Y',$pastTS));
				$myCalendar->setPath("/protected");
				$myCalendar->setYearInterval(1970, 2030);
				$myCalendar->setAlignment('left', 'bottom');
				$myCalendar->writeScript();

				print(" 		</td>");
				print(" 		<td colspan='2'>");
				print(" 		</td>");
				print(" 	</tr>");
				print(" 	<tr>");
				print(" 		<td>Enter End Date: </td>");
				print(" 		<td>");

				$myCalendar = new tc_calendar('dt', true, true);
				$myCalendar->setIcon("images/iconCalendar.gif");
				$myCalendar->setDate(date('d'), date('m'), date('Y'));
				$myCalendar->setPath("/protected");
				$myCalendar->setYearInterval(1970, 2030);
				$myCalendar->setAlignment('left', 'bottom');
				$myCalendar->writeScript();

				print(" 		</td>");
				print(" 		<td colspan='2'>");
				print(" 		</td>");
				print(" 	</tr>");
				print(" 	<tr>");
				print(" 		<td>Min Territory: </td>");
				print(" 		<td><input type='textbox' id='tf' name='tf' value='12'></td>");
				print(" 		<td colspan='2'>");
				print(" 		</td>");
				print(" 	</tr>");
				print(" 	<tr>");
				print(" 		<td>Max Territory: </td>");
				print(" 		<td><input type='textbox' id='tt' name='tt' value='99'></td>");
				print(" 		<td colspan='2'>");
				print(" 			<INPUT type='submit' value='Submit'>");
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

	PrintFooter("emenu.php");

?>
