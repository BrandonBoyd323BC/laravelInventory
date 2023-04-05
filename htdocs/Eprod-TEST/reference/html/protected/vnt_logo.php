<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	print("<html>\n");
	print("	<head>\n");
	print("		<meta http-equiv='Pragma' content='no-cache'>\n");
	print("		<meta http-equiv='Expires' content='-1'>\n");
	print("		<script language='javascript' src='JavaScript/calendar.js'></script>\n");
 	print("		<script type='text/javascript' src='JavaScript/jquery-1.6.4.js'  charset='utf-8'></script>\n");
	print("		<script type='text/javascript' src='JavaScript/vnt_work.js'  charset='utf-8'></script>\n");
	print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/default.css'>\n");

	print("		<title>Logo Orders</title>\n");
	print("	</head>\n");
	print("	<body>\n");
	print("		<table align='center' width='100%' height='100%' style='background-color: white;' cellspacing='2' cellpadding='2'>\n");
	print("			<tr>\n");
	print("				<td align='center' valign='top'>\n");
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print("	<h2>Vinatronics Logo Orders</h2>\n");
			print("<style>td {font-size:16px}</style>\n");
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			$sql  = " select ";
			$sql .= " CONVERT(varchar(8), h.DATE_ADD, 112) as DATE_ORD3, ";
			$sql .= " CONVERT(varchar(8), l.DATE_PROM, 112) as DATE_PROM3, ";
			$sql .= " h.TIME_ADD, ";
			$sql .= " h.ID_USER_ADD, ";
			$sql .= " h.ID_CUST_SOLDTO, ";
			$sql .= " h.NAME_CUST, ";
			$sql .= " h.ID_ORD, ";
			$sql .= " l.SEQ_LINE_ORD, ";
			$sql .= " h.ID_PO_CUST, ";
			$sql .= " l.ID_ITEM, ";
			//$sql .= " l.DATE_ADD, ";
			$sql .= " l.DATE_PROM, ";
			$sql .= " h.rowid ";
			$sql .= " from  ";
			$sql .= " 	nsa.CP_ORDLIN l ";
			$sql .= " 	left join nsa.CP_ORDHDR h ";
			$sql .= " 		on l.ID_ORD = h.ID_ORD ";
			$sql .= " where ";
			$sql .= " 	(l.ID_ITEM like 'VNTR%' or ID_ITEM like 'VNTF%') ";
			$sql .= " order by DATE_PROM asc ";
			QueryDatabase($sql, $results);
			$b_flip = true;
			print(" <table valign=center width=100% border=1 cellspacing=0 cellpadding=0 style='margin-bottom:2px'>\n");
			while ($row = mssql_fetch_assoc($results)) {
				$dateOrdTS = strtotime($row['DATE_ORD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT));
				$datePROMTS = strtotime($row['DATE_PROM3'] . " " . str_pad("",6,"0",STR_PAD_LEFT));

				if ($b_flip) {
					$trClass = 'd1s';
					$b_flip = !$b_flip;
				} else {
					$trClass = 'd0s';
					$b_flip = !$b_flip;
				}

				print(" 	<tr align=center class='" . $trClass . "'>\n");
				print("			<td width='10%' class='" . $trClass . "'>Order Date</td>\n");
				print("			<td width='15%' class='" . $trClass . "'>" . date('m/d/Y h:i:s A',$dateOrdTS) . "</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>Bill To</td>\n");
				print("			<td width='20%' class='" . $trClass . "'>" . $row['NAME_CUST'] . "</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>Taker</td>\n");
				print("			<td width='15%' class='" . $trClass . "'>" . $row['ID_USER_ADD'] . "</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>Promise date</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>" . date('m/d/Y',$datePROMTS) . "</td>\n");
				print(" 	</tr>\n");
				print(" 	<tr align=center class='" . $trClass . "'>\n");
				print("			<td width='10%' class='" . $trClass . "'>Item</td>\n");
				print("			<td width='15%' class='" . $trClass . "'>" . $row['ID_ITEM'] . "</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>Order Number</td>\n");
				print("			<td width='20%' class='" . $trClass . "'>" . $row['ID_ORD'] . "</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>Line Number</td>\n");
				print("			<td width='15%' class='" . $trClass . "'>" . $row['SEQ_LINE_ORD'] . "</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>Cust PO</td>\n");
				print("			<td width='10%' class='" . $trClass . "'>" . $row['ID_PO_CUST'] . "</td>\n");
				print(" 	</tr>\n");

				$sql1  = "select ";
				$sql1 .= " 	NOTE ";
				$sql1 .= " from ";
				$sql1 .= " 	nsa.CP_COMMENT ";
				$sql1 .= " where ";
				$sql1 .= " 	ID_ORD = '" . $row['ID_ORD'] . "' ";
				//$sql1 .= " 	and SEQ_LINE_ORD in (0, " . $row['SEQ_LINE_ORD'] . ") ";
				$sql1 .= " 	and SEQ_LINE_ORD in (0) ";
				$sql1 .= " ORDER BY SEQ_LINE_ORD asc, SEQ_COMMENT asc ";
				QueryDatabase($sql1, $results1);
				$cmt = '';
				while ($row1 = mssql_fetch_assoc($results1)) {
					$cmt .= $row1['NOTE'];
				}

				print(" 	<tr align=center class='" . $trClass . "'>\n");
				print("			<td>Notes</td>\n");
				print("			<td colspan=7>". $cmt ."</td>\n");
				print(" 	</tr>\n");

				print(" 	<tr align=center><td colspan=8></td></tr>\n");
				print(" 	<tr align=center><td colspan=8></td></tr>\n");
				print(" 	<tr align=center><td colspan=8></td></tr>\n");

			}
			print(" </table>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter("emenu.php");
?>