<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('','','badgestatus.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print("<style>td {font-size:16px}</style>\n");
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			$page = 0;




			$sql =  "select ";
			$sql .= "	CONVERT(varchar(8), sh.DATE_ADD, 112) as DATE_ADD3, ";
			$sql .= "	CONVERT(varchar(8), sh.DATE_DUE_ORD, 112) as DATE_DUE3, ";
			$sql .= "	b.ID_BUYER, ";
			$sql .= "	sh.* ";
			$sql .= " from ";
			$sql .= " 	nsa.SHPORD_HDR sh ";
			$sql .= " left join nsa.ITMMAS_BASE b ";
			$sql .= " 	on sh.ID_ITEM_PAR = b.ID_ITEM ";
			$sql .= " where ";
			$sql .= " 	b.FLAG_STK = 'S' ";
			$sql .= " 	and sh.ID_ITEM_PAR like 'VNT%' ";
			$sql .= " 	and sh.STAT_REC_SO not in('C','E') ";
			$sql .= " 	and sh.ID_SO not like 'N%' ";
			$sql .= " order by sh.ID_SO desc ";
			$DEBUG=1;
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				$page++;
				$descr= '';
				$st1 = "";
				$st2 = "";
				$dateDueTS = strtotime($row['DATE_DUE3'] . " 000000");
				$dateAddTS = strtotime($row['DATE_ADD3'] . " 000000");

				$sql1 =  "select ";
				$sql1 .= " 	* ";
				$sql1 .= " from ";
				$sql1 .= " 	nsa.ITMMAS_DESCR ";
				$sql1 .= " where ";
				$sql1 .= " 	ltrim(ID_ITEM) = '" . $row['ID_ITEM_PAR'] . "' ";
				$sql1 .= " order by SEQ_DESCR asc";

				QueryDatabase($sql1, $results1);
				while ($row1 = mssql_fetch_assoc($results1)) {
					$descr .= " " . $row1['DESCR_ADDL'];
				}

				if ($row['ID_BUYER'] <> 'VN') {
					$st1 = "<font color=red><del>";
					$st2 = "</del>Being Made in CLE</font>";
				}

				if ($row['DATE_ADD3'] == '20140905') {
					error_log("HERE WE GO");
				}

				print("	<H2 style='margin:0;padding:0' align=center>Stocking Order</H2>\n");
				print(" <TABLE valign=center width=100% border=1 cellpadding=0 cellspacing=0 style='margin-bottom:2px'>\n");
				print(" 	<tr align=center>\n");
				print("			<td width='20%'>Due date</td>\n");
				print("			<td width='20%'>" . date('m/d/Y',$dateDueTS) . "</td>\n");
				print("			<td width='20%'>Created By</td>\n");
				print("			<td width='20%'>" . $row['ID_USER_ADD'] . "</td>\n");
				print("			<td width='10%'>Page</td>\n");
				print("			<td width='10%'>" . $page . "</td>\n");
				print(" 	</tr>\n");
				print(" 	<tr align=center>\n");
				print("			<td>Production Order</td>\n");
				print("			<td>" . $row['ID_SO'] . "</td>\n");
				print("			<td>Created</td>\n");
				print("			<td>" . date('m/d/Y',$dateAddTS) . "</td>\n");
				print("			<td>Printed</td>\n");
				print("			<td></td>\n");
				print(" 	</tr>\n");
				print(" </table>\n");
				print(" <TABLE width=100% border=1 cellpadding=0 cellspacing=0>\n");
				print(" 	<tr align=center>\n");
				print(" 		<td>Item Id</td>\n");
				print(" 		<td>Extended Desc</td>\n");
				print(" 		<td>UOM</td>\n");
				print(" 		<td>Qty to Make</td>\n");
				print(" 		<td>Qty Complete</td>\n");
				print(" 	</tr>\n");
				print(" 	<tr align=center>\n");
				//print(" 		<td>" . $row['ID_ITEM_PAR'] . "</td>\n");
				print(" 		<td>".$st1."" . substr($row['ID_ITEM_PAR'],3) . "".$st2."</td>\n");
				print("			<td>" . $descr . "</td>\n");
				print(" 		<td>" . $row['CODE_UM'] . "</td>\n");
				print(" 		<td>" . $row['QTY_ORD'] . "</td>\n");

				print(" 	</tr>\n");
				print(" </table>\n");


				print("<HR style='page-break-before: always; margin:0;padding:0'>\n");
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

	PrintFooter("emenu.php");

?>
