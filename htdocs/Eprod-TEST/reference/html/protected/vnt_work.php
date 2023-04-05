<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

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

	print("		<title>Work Orders</title>\n");
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
			print("<style>td {font-size:16px}</style>\n");
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			$page = 0;

			$sql =  "select distinct ol.ID_ORD ";
			$sql .= " from ";
			$sql .= " 	nsa.CP_ORDLIN ol ";
			$sql .= " 	left join nsa.CP_SHPLIN sl ";
			$sql .= " 	on ol.ID_ORD = sl.ID_ORD ";
			$sql .= " 	and ";
			$sql .= "	ol.ID_ITEM = sl.ID_ITEM ";
			$sql .= " where ";
			$sql .= " 	sl.ID_SHIP is NULL ";
			$sql .= " 	and ";
			$sql .= " 	(ol.ID_ITEM like 'VNT%' ";
			$sql .= " 	or ";
			$sql .= " 	ol.ID_ITEM like 'VNC%') ";
			//TESTING ONLY////////
			//$sql .= " 	ID_ITEM like 'V%' ";
			//////////////////////
			$sql .= " order by ID_ORD desc ";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				$page++;
				$ID_ORD = trim($row['ID_ORD']);

				$sql1 =  "select ";
				$sql1 .= "	CONVERT(varchar(8), oh.DATE_ADD, 112) as DATE_ORD3, ";
				$sql1 .= "	CONVERT(varchar(8), oh.DATE_CHG, 112) as DATE_CHG3, ";
				$sql1 .= " 	oh.TIME_ADD, ";
				$sql1 .= " 	oh.TIME_CHG, ";
				$sql1 .= " 	oh.NAME_CUST, ";
				$sql1 .= " 	oh.NAME_CUST_SHIPTO, ";
				$sql1 .= " 	oh.ID_USER_ADD, ";
				$sql1 .= " 	ID_PO_CUST ";
				$sql1 .= " from ";
				$sql1 .= " 	nsa.CP_ORDHDR oh ";
				$sql1 .= " where ";
				$sql1 .= " 	ID_ORD = '" . $row['ID_ORD'] . "' ";
				QueryDatabase($sql1, $results1);

				while ($row1 = mssql_fetch_assoc($results1)) {
					$dateOrdTS = strtotime($row1['DATE_ORD3'] . " " . str_pad($row1['TIME_ADD'],6,"0",STR_PAD_LEFT));
					$dateChgTS = strtotime($row1['DATE_CHG3'] . " " . str_pad($row1['TIME_CHG'],6,"0",STR_PAD_LEFT));

					print("	<h2>Work Order</h2>\n");
					print(" <table valign=center width=100% border=1 cellspacing=0 cellpadding=0 style='margin-bottom:2px'>\n");
					print(" 	<tr align=center>\n");
					print("			<td width='10%'>Order date</td>\n");
					print("			<td width='15%'>" . date('m/d/Y h:i:s A',$dateOrdTS) . "</td>\n");
					print("			<td width='10%'>Bill To</td>\n");
					print("			<td width='20%'>" . $row1['NAME_CUST'] . "</td>\n");
					print("			<td width='10%'>Taker</td>\n");
					print("			<td width='15%'>" . $row1['ID_USER_ADD'] . "</td>\n");
					print("			<td width='10%'>Page</td>\n");
					print("			<td width='10%'>" . $page . "</td>\n");
					print(" 	</tr>\n");
					print(" 	<tr align=center>\n");
					print("			<td>SO</td>\n");
					print("			<td>" . $row['ID_ORD'] . "</td>\n");
					print("			<td>Ship To</td>\n");
					print("			<td>" . $row1['NAME_CUST_SHIPTO'] . "</td>\n");
					print("			<td>Last Change</td>\n");
					print("			<td>" . date('m/d/Y h:i:s A',$dateChgTS) . "</td>\n");
					print("			<td>Printed</td>\n");
					print("			<td></td>\n");
					print(" 	</tr>\n");
					print(" 	<tr align=center>\n");
					print("			<td>PO</td>\n");
					print("			<td>" . $row1['ID_PO_CUST'] . "</td>\n");
					print("			<td colspan=6></td>\n");
					print(" 	</tr>\n");

					$sql2 =  "select ";
					$sql2 .= " 	c.CODE_COMMENT, ";
					$sql2 .= " 	c.NOTE ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.CP_COMMENT c ";
					$sql2 .= " where ";
					$sql2 .= " 	ID_ORD = '" . $row['ID_ORD'] . "' ";
					$sql2 .= " 	and SEQ_LINE_ORD = 0 ";
					$sql2 .= " order by CODE_COMMENT asc ";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						print(" 	<tr align=center>\n");
						print("			<td colspan=8><B>" . $row2['NOTE'] . "</B></td>\n");
						print(" 	</tr>\n");
					}
				}
				print(" </table>\n");

				////////////////////////////////////
				/// NONSTOCK ITEMS
				////////////////////////////////////
				$sql1 =  "select ";
				$sql1 .= "	CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
				$sql1 .= " 	ol.ID_ITEM, ";
				$sql1 .= " 	ol.ID_SO, ";
				$sql1 .= " 	ol.CODE_UM_ORD, ";
				$sql1 .= " 	ol.QTY_OPEN ";
				$sql1 .= " 	,b.ID_BUYER ";
				$sql1 .= " from ";
				$sql1 .= " 	nsa.CP_ORDLIN ol ";
				$sql1 .= " 	left join nsa.ITMMAS_BASE b ";
				$sql1 .= " 	on ol.ID_ITEM = b.ID_ITEM ";
				$sql1 .= " where ";
				$sql1 .= " 	ol.ID_ORD = '" . $row['ID_ORD'] . "' ";
				$sql1 .= " 	and ol.FLAG_STK = 'N' ";
				$sql1 .= " order by ol.ID_ITEM asc";
				QueryDatabase($sql1, $results1);

				print("	<div id='div_" . $row['ID_ORD'] . "'>\n");
				print(" <table valign=center width=100% border=1 cellspacing=0 cellpadding=0>\n");
				print(" 	<tr align=center>\n");
				print("			<td>Shop Ord</td>\n");
				print("			<td>Due date</td>\n");
				print("			<td>Item ID</td>\n");
				print("			<td colspan=3>Extended Desc</td>\n");
				print("			<td>UOM</td>\n");
				print("			<td>Qty Ordered</td>\n");
				print("			<td>Qty Complete</td>\n");
				print(" 	</tr>\n");

				while ($row1 = mssql_fetch_assoc($results1)) {
					$descr= '';
					$st1 = "";
					$st2 = "";
					$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");

					if ($row1['ID_BUYER'] <> 'VN') {
						$st1 = "<font color=red><del>";
						$st2 = "</del>Being Made in CLE</font>";
					}

					print(" 	<tr align=center>\n");
					print("			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n");
					print("			<td>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</td>\n");
					print("			<td><b>".$st1."&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;".$st2."</b></td>\n");

					$sql2 =  "select ";
					$sql2 .= " 	d.SEQ_DESCR, ";
					$sql2 .= " 	d.DESCR_ADDL ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.ITMMAS_DESCR d ";
					$sql2 .= " where ";
					$sql2 .= " 	ltrim(d.ID_ITEM) = '" . $row1['ID_ITEM'] . "' ";
					$sql2 .= " order by d.SEQ_DESCR asc";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$descr .= " " . $row2['DESCR_ADDL'];
					}
					print("			<td colspan=3>" . $descr . "</td>\n");
					print("			<td>" . $row1['CODE_UM_ORD'] . "</td>\n");
					print("			<td><b>" . $row1['QTY_OPEN'] . "</b></td>\n");
					print("			<td> </td>\n");
					print(" 	</tr>\n");
				}

				////////////////////////////////////
				/// WOCHG MAKE
				////////////////////////////////////
				$sql1 =  "select ";
				$sql1 .= " 	vw.rowid as wrowid, ";
				$sql1 .= " 	vw.QTY_MAKE, ";
				$sql1 .= "	CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
				$sql1 .= " 	ol.ID_ITEM, ";
				$sql1 .= " 	ol.ID_SO, ";
				$sql1 .= " 	ol.CODE_UM_ORD, ";
				$sql1 .= " 	b.ID_BUYER ";
				$sql1 .= " from ";
				$sql1 .= " 	nsa.CP_VINA_WOCHG vw ";
				$sql1 .= " 	left join nsa.CP_ORDLIN ol  ";
				$sql1 .= " 	on vw.ID_ORD = ol.ID_ORD  ";
				$sql1 .= " 	and vw.SEQ_LINE_ORD = ol.SEQ_LINE_ORD  ";
				$sql1 .= " 	left join nsa.ITMMAS_BASE b  ";
				$sql1 .= " 	on ol.ID_ITEM = b.ID_ITEM  ";
				$sql1 .= " where ";
				$sql1 .= " 	vw.ID_ORD = '" . $row['ID_ORD'] . "' ";
				$sql1 .= " 	and vw.QTY_MAKE <> 0 ";
				$sql1 .= " order by ol.ID_ITEM asc";
				QueryDatabase($sql1, $results1);

				while ($row1 = mssql_fetch_assoc($results1)) {
					$descr= '';
					$st1 = "";
					$st2 = "";
					$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");

					if ($row1['ID_BUYER'] <> 'VN') {
						$st1 = "<font color=red><del>";
						$st2 = "</del>Being Made in CLE</font>";
					}

					print(" 	<tr align=center>\n");
					print("			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n");
					print("			<td><font color=blue>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</font></td>\n");
					print("			<td><font color=blue><b>".$st1."&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;".$st2."</b></font></td>\n");

					$sql2 =  "select ";
					$sql2 .= " 	SEQ_DESCR, ";
					$sql2 .= " 	DESCR_ADDL ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.ITMMAS_DESCR ";
					$sql2 .= " where ";
					$sql2 .= " 	ltrim(ID_ITEM) = '" . $row1['ID_ITEM'] . "' ";
					$sql2 .= " order by SEQ_DESCR asc";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$descr .= " " . $row2['DESCR_ADDL'];
					}
					print("			<td colspan=3><font color=blue>" . $descr . "</font></td>\n");
					print("			<td><font color=blue>" . $row1['CODE_UM_ORD'] . "</font></td>\n");
					print("			<td title='Double-click to change' onDblClick=\"showWOC('". $row1['wrowid'] ."','m')\"><font color=blue><b>" . $row1['QTY_MAKE'] . "</b></font></td>\n");
					print("			<td> </td>\n");
					print(" 	</tr>\n");
					print("		<tr id='wcm_" . $row1['wrowid'] . "'></tr>\n");
				}

				////////////////////////////////////
				/// WOCHG PULL
				////////////////////////////////////
				$sql1 =  "select ";
				$sql1 .= " 	vw.rowid as wrowid, ";
				$sql1 .= " 	vw.QTY_PULL, ";
				$sql1 .= "	CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
				$sql1 .= " 	ol.ID_ITEM, ";
				$sql1 .= " 	ol.ID_SO, ";
				$sql1 .= " 	ol.CODE_UM_ORD, ";
				$sql1 .= " 	b.ID_BUYER ";
				$sql1 .= " from ";
				$sql1 .= " 	nsa.CP_VINA_WOCHG vw ";
				$sql1 .= " 	left join nsa.CP_ORDLIN ol  ";
				$sql1 .= " 	on vw.ID_ORD = ol.ID_ORD  ";
				$sql1 .= " 	and vw.SEQ_LINE_ORD = ol.SEQ_LINE_ORD  ";
				$sql1 .= " 	left join nsa.ITMMAS_BASE b  ";
				$sql1 .= " 	on ol.ID_ITEM = b.ID_ITEM  ";
				$sql1 .= " where ";
				$sql1 .= " 	vw.ID_ORD = '" . $row['ID_ORD'] . "' ";
				$sql1 .= " 	and vw.QTY_PULL <> 0 ";
				$sql1 .= " order by ol.ID_ITEM asc";
				QueryDatabase($sql1, $results1);

				if (mssql_num_rows($results1) > 0) {
					print("		<tr align=center>");
					print("			<td colspan=8><h3><font color=green>PULL VINA STOCK</font></h3></td>\n");
					print(" 	</tr>\n");
				}

				while ($row1 = mssql_fetch_assoc($results1)) {
					$descr= '';
					$st1 = "";
					$st2 = "";
					$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");

					if ($row1['ID_BUYER'] <> 'VN') {
						$st1 = "<font color=red><del>";
						$st2 = "</del>Being Made in CLE</font>";
					}

					print(" 	<tr align=center>\n");
					print("			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n");
					print("			<td><font color=green>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</font></td>\n");
					print("			<td><font color=green><b>".$st1."&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;".$st2."</b></font></td>\n");

					$sql2 =  "select ";
					$sql2 .= " 	SEQ_DESCR, ";
					$sql2 .= " 	DESCR_ADDL ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.ITMMAS_DESCR ";
					$sql2 .= " where ";
					$sql2 .= " 	ltrim(ID_ITEM) = '" . $row1['ID_ITEM'] . "' ";
					$sql2 .= " order by SEQ_DESCR asc";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$descr .= " " . $row2['DESCR_ADDL'];
					}
					print("			<td colspan=3><font color=green>" . $descr . "</font></td>\n");
					print("			<td><font color=green>" . $row1['CODE_UM_ORD'] . "</font></td>\n");
					print("			<td title='Double-click to change' onDblClick=\"showWOC('". $row1['wrowid'] ."','p')\"><font color=green><b>" . $row1['QTY_PULL'] . "</b></font></td>\n");
					print("			<td> </td>\n");
					print(" 	</tr>\n");
					print("		<tr id='wcp_" . $row1['wrowid'] . "'></tr>\n");
				}

				////////////////////////////////////
				/// DECISION NEEDED FOR STOCK ITEMS
				////////////////////////////////////
				$sql1 =  "select ";
				$sql1 .= "	CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
				$sql1 .= " 	ol.ID_ITEM, ";
				$sql1 .= " 	ol.ID_ORD, ";
				$sql1 .= " 	ol.ID_SO, ";
				$sql1 .= " 	ol.CODE_UM_ORD, ";
				$sql1 .= " 	ol.SEQ_LINE_ORD, ";
				$sql1 .= "	ol.QTY_OPEN, ";
				$sql1 .= " 	b.ID_BUYER ";
				$sql1 .= " from ";
				$sql1 .= " 	nsa.CP_ORDLIN ol ";
				$sql1 .= " 	left join nsa.CP_VINA_WOCHG wc";
				$sql1 .= " 	on ol.ID_ORD = wc.ID_ORD ";
				$sql1 .= " 	and ol.SEQ_LINE_ORD = wc.SEQ_LINE_ORD ";
				$sql1 .= " 	left join nsa.ITMMAS_BASE b  ";
				$sql1 .= " 	on ol.ID_ITEM = b.ID_ITEM  ";
				$sql1 .= " where ";
				$sql1 .= " 	wc.QTY_MAKE is null ";
				$sql1 .= " 	and ";
				$sql1 .= " 	ol.ID_ORD = '" . $row['ID_ORD'] . "' ";
				$sql1 .= " 	and ";
				$sql1 .= " 	ol.FLAG_STK = 'S' ";
				$sql1 .= " order by ol.ID_ITEM asc";
				QueryDatabase($sql1, $results1);

				if (mssql_num_rows($results1) > 0) {
					print("		<tr align=center>");
					print("			<td colspan=8><h3><font color=red>DECISION NEEDED</font></h3><input id='ref_" . $row['ID_ORD'] . "' type=button value='Refresh Order' onClick=\"refreshOrd('". $row['ID_ORD'] ."')\"></td>\n");
					print(" 	</tr>\n");
				}

				while ($row1 = mssql_fetch_assoc($results1)) {
					$descr = '';
					$st1 = "";
					$st2 = "";
					$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");

					if ($row1['ID_BUYER'] <> 'VN') {
						$st1 = "<font color=red><del>";
						$st2 = "</del>Being Made in CLE</font>";
					}

					print(" 	<tr align=center>\n");
					print("			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n");
					print("			<td><font color=red>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</font></td>\n");
					print("			<td><font color=red><b>".$st1."&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;".$st2."</b></font></td>\n");

					$sql2 =  "select ";
					$sql2 .= " 	SEQ_DESCR, ";
					$sql2 .= " 	DESCR_ADDL ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.ITMMAS_DESCR ";
					$sql2 .= " where ";
					$sql2 .= " 	ID_ITEM = '" . $row1['ID_ITEM'] . "' ";
					$sql2 .= " order by SEQ_DESCR asc";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$descr .= " " . $row2['DESCR_ADDL'];
					}
					print("			<td colspan=3><font color=red>" . $descr . "</font></td>\n");
					print("			<td><font color=red>" . $row1['CODE_UM_ORD'] . "</font></td>\n");
					print("			<td><font color=red><input type=hidden id='T_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' value='". $row1['QTY_OPEN'] ."'><b>" . $row1['QTY_OPEN'] . "</b></font></td>\n");
					print("			<td><input type=button value='Save' id='" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' onClick=\"saveQtys(this.id)\"><div id='div_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "'></div></td>\n");
					print(" 	</tr>\n");
					print(" 	<tr align=center>\n");
					print("			<td colspan=6> </td>\n");
					print("			<td><font color=red>\n");
					print("				Qty Make:<input size=4 type=textbox id='M_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "'>\n");
					print("			</font></td>\n");
					print("			<td><font color=red>\n");
					print("				Qty Pull:<input size=4 type=textbox id='P_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "'>\n");
					print("			</font></td>\n");
					print(" 	</tr>\n");
				}
				print(" </table>\n");
				print(" </div>\n");
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