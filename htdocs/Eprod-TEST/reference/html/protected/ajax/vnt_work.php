<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

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
			if (isset($_POST["action"]) ) {
				$Action = $_POST['action'];

				if ($Action == "saveQtys") {
					if (isset($_POST["id"]) && isset($_POST["qtyMake"]) && isset($_POST["qtyPull"]))  {
						$ID = $_POST["id"];
						$a = explode("_",$ID);
						$ID_ORD = $a[0];
						$SEQ_LINE_ORD = $a[1];
						$qtyMake = $_POST["qtyMake"];
						$qtyPull = $_POST["qtyPull"];

						$sql  = "SELECT * from nsa.CP_VINA_WOCHG where ";
						$sql .= " ID_ORD = '" . $ID_ORD . "' ";
						$sql .= " and SEQ_LINE_ORD = '" . $SEQ_LINE_ORD . "' ";
						QueryDatabase($sql, $results);

						if (mssql_num_rows($results) == 1) {
							while ($row = mssql_fetch_assoc($results)) {
								$sql1  = "update nsa.CP_VINA_WOCHG ";
								$sql1 .= " SET QTY_MAKE = " . $qtyMake . ", ";
								$sql1 .= " QTY_PULL = " . $qtyPull . ", ";
								$sql1 .= " DATE_ADD = getdate(), ";
								$sql1 .= " ID_USER_ADD = '" . stripIllegalChars($UserRow['ID_USER']) . "' ";
								$sql1 .= " WHERE rowid = " . $row['rowid'] . "";
								QueryDatabase($sql1, $results1);
							}
							$ret .= "OK";
						} elseif (mssql_num_rows($results) == 0) {
							$sql1  = "INSERT INTO nsa.CP_VINA_WOCHG (";
							$sql1 .= " ID_ORD, ";
							$sql1 .= " SEQ_LINE_ORD, ";
							$sql1 .= " QTY_MAKE, ";
							$sql1 .= " QTY_PULL, ";
							$sql1 .= " DATE_ADD, ";
							$sql1 .= " ID_USER_ADD ";
							$sql1 .= " ) values ( ";
							$sql1 .= " " . $ID_ORD . ", ";
							$sql1 .= " " . $SEQ_LINE_ORD . ", ";
							$sql1 .= " " . $qtyMake . ", ";
							$sql1 .= " " . $qtyPull . ", ";
							$sql1 .= " getdate(), ";
							$sql1 .= " '" . stripIllegalChars($UserRow['ID_USER']) . "' ";
							$sql1 .= " )";
							QueryDatabase($sql1, $results1);
							$ret .= "OK";
						} else {
							error_log("NON UNIQUE RECORDS in nsa.CP_VINA_WOCHG");
							$ret .= "ERROR";
						}
					}
				}

				if ($Action == "refreshOrd") {
					if (isset($_POST["ord"]))  {
						$ID_ORD = $_POST["ord"];

						////////////////////////////////////
						/// NONSTOCK ITEMS
						////////////////////////////////////
						$sql1 =  "select ";
						$sql1 .= "	CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
						$sql1 .= " 	ID_ITEM, ";
						$sql1 .= " 	ID_SO, ";
						$sql1 .= " 	CODE_UM_ORD, ";
						$sql1 .= " 	QTY_OPEN ";
						$sql1 .= " from ";
						$sql1 .= " 	nsa.CP_ORDLIN ol ";
						$sql1 .= " where ";
						$sql1 .= " 	ID_ORD = '" . $ID_ORD . "' ";
						$sql1 .= " 	and FLAG_STK = 'N' ";
						$sql1 .= " order by ol.ID_ITEM asc";
						QueryDatabase($sql1, $results1);

						$ret .= "	<div id='div_" . $ID_ORD . "'>\n";
						$ret .= " <table valign=center width=100% border=1 cellspacing=0 cellpadding=0>\n";
						$ret .= " 	<tr align=center>\n";
						$ret .= "			<td>Shop Ord</td>\n";
						$ret .= "			<td>Due date</td>\n";
						$ret .= "			<td>Item ID</td>\n";
						$ret .= "			<td colspan=3>Extended Desc</td>\n";
						$ret .= "			<td>UOM</td>\n";
						$ret .= "			<td>Qty Ordered</td>\n";
						$ret .= "			<td>Qty Complete</td>\n";
						$ret .= " 	</tr>\n";

						while ($row1 = mssql_fetch_assoc($results1)) {
							$descr= '';
							$checked = '';
							$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");
							if ($row1['FLAG_STK'] == 'N') {
								$checked = 'checked';
							}
							$ret .= " 	<tr align=center>\n";
							$ret .= "			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n";
							$ret .= "			<td>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</td>\n";
							$ret .= "			<td><b>&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;</b></td>\n";

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
							$ret .= "			<td colspan=3>" . $descr . "</td>\n";
							$ret .= "			<td>" . $row1['CODE_UM_ORD'] . "</td>\n";
							$ret .= "			<td><b>" . $row1['QTY_OPEN'] . "</b></td>\n";
							$ret .= "			<td> </td>\n";
							$ret .= " 	</tr>\n";
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
						$sql1 .= " 	ol.CODE_UM_ORD ";
						$sql1 .= " from ";
						$sql1 .= " 	nsa.CP_VINA_WOCHG vw ";
						$sql1 .= " 	left join nsa.CP_ORDLIN ol  ";
						$sql1 .= " 	on vw.ID_ORD = ol.ID_ORD  ";
						$sql1 .= " 	and vw.SEQ_LINE_ORD = ol.SEQ_LINE_ORD  ";
						$sql1 .= " where ";
						$sql1 .= " 	vw.ID_ORD = '" . $ID_ORD . "' ";
						$sql1 .= " 	and vw.QTY_MAKE <> 0 ";
						$sql1 .= " order by ol.ID_ITEM asc";
						QueryDatabase($sql1, $results1);

						while ($row1 = mssql_fetch_assoc($results1)) {
							$descr= '';
							$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");

							$ret .= " 	<tr align=center>\n";
							$ret .= "			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n";
							$ret .= "			<td><font color=blue>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</font></td>\n";
							$ret .= "			<td><font color=blue><b>&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;</b></font></td>\n";

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
							$ret .= "			<td colspan=3><font color=blue>" . $descr . "</font></td>\n";
							$ret .= "			<td><font color=blue>" . $row1['CODE_UM_ORD'] . "</font></td>\n";
							$ret .= "			<td title='Double-click to change' onDblClick=\"showWOC('". $row1['wrowid'] ."','m')\"><font color=blue><b>" . $row1['QTY_MAKE'] . "</b></font></td>\n";
							$ret .= "			<td> </td>\n";
							$ret .= " 	</tr>\n";
							$ret .= "	<tr id='wcm_" . $row1['wrowid'] . "'></tr>\n";
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
						$sql1 .= " 	ol.CODE_UM_ORD ";
						$sql1 .= " from ";
						$sql1 .= " 	nsa.CP_VINA_WOCHG vw ";
						$sql1 .= " 	left join nsa.CP_ORDLIN ol  ";
						$sql1 .= " 	on vw.ID_ORD = ol.ID_ORD  ";
						$sql1 .= " 	and vw.SEQ_LINE_ORD = ol.SEQ_LINE_ORD  ";
						$sql1 .= " where ";
						$sql1 .= " 	vw.ID_ORD = '" . $ID_ORD . "' ";
						$sql1 .= " 	and vw.QTY_PULL <> 0 ";
						$sql1 .= " order by ol.ID_ITEM asc";
						QueryDatabase($sql1, $results1);

						if (mssql_num_rows($results1) > 0) {
							$ret .= "		<tr align=center>";
							$ret .= "			<td colspan=8><h3><font color=green>PULL VINA STOCK</font></h3></td>\n";
							$ret .= " 	</tr>\n";
						}

						while ($row1 = mssql_fetch_assoc($results1)) {
							$descr= '';
							$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");

							$ret .= " 	<tr align=center>\n";
							$ret .= "			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n";
							$ret .= "			<td><font color=green>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</font></td>\n";
							$ret .= "			<td><font color=green><b>&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;</b></font></td>\n";

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
							$ret .= "			<td colspan=3><font color=green>" . $descr . "</font></td>\n";
							$ret .= "			<td><font color=green>" . $row1['CODE_UM_ORD'] . "</font></td>\n";
							$ret .= "			<td title='Double-click to change' onDblClick=\"showWOC('". $row1['wrowid'] ."','p')\"><font color=green><b>" . $row1['QTY_PULL'] . "</b></font></td>\n";
							$ret .= "			<td> </td>\n";
							$ret .= " 	</tr>\n";
							$ret .= "	<tr id='wcp_" . $row1['wrowid'] . "'></tr>\n";
						}

						////////////////////////////////////
						/// DECISION NEEDED
						////////////////////////////////////
						$sql1 =  "select ";
						$sql1 .= "	CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
						$sql1 .= " 	ol.ID_ITEM, ";
						$sql1 .= " 	ol.ID_ORD, ";
						$sql1 .= " 	ol.ID_SO, ";
						$sql1 .= " 	ol.CODE_UM_ORD, ";
						$sql1 .= " 	ol.SEQ_LINE_ORD, ";
						$sql1 .= "	ol.QTY_OPEN ";
						$sql1 .= " from ";
						$sql1 .= " 	nsa.CP_ORDLIN ol ";
						$sql1 .= " 	left join nsa.CP_VINA_WOCHG wc";
						$sql1 .= " 	on ol.ID_ORD = wc.ID_ORD ";
						$sql1 .= " 	and ol.SEQ_LINE_ORD = wc.SEQ_LINE_ORD ";
						$sql1 .= " where ";
						$sql1 .= " 	wc.QTY_MAKE is null ";
						$sql1 .= " 	and ";
						$sql1 .= " 	ol.ID_ORD = '" . $ID_ORD . "' ";
						$sql1 .= " 	and ";
						$sql1 .= " 	ol.FLAG_STK = 'S' ";
						$sql1 .= " order by ol.ID_ITEM asc";
						QueryDatabase($sql1, $results1);

						if (mssql_num_rows($results1) > 0) {
							$ret .= "		<tr align=center>";
							$ret .= "			<td colspan=8><h3><font color=red>DECISION NEEDED</font></h3><input id='ref_" . $ID_ORD . "' type=button value='Refresh Order' onClick=\"refreshOrd('". $ID_ORD ."')\"></td>\n";
							$ret .= " 	</tr>\n";
						}

						while ($row1 = mssql_fetch_assoc($results1)) {
							$descr = '';
							$datePromTS = strtotime($row1['DATE_PROM3'] . " 000000");
							$ret .= " 	<tr align=center>\n";
							$ret .= "			<td>&nbsp;" .  $row1['ID_SO'] . "&nbsp;&nbsp;</td>\n";
							$ret .= "			<td><font color=red>&nbsp;" . date('n/j/y',$datePromTS) . "&nbsp;&nbsp;</font></td>\n";
							$ret .= "			<td><font color=red><b>&nbsp;&nbsp;" . substr($row1['ID_ITEM'],3) . "&nbsp;</b></font></td>\n";

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
							$ret .= "			<td colspan=3><font color=red>" . $descr . "</font></td>\n";
							$ret .= "			<td><font color=red>" . $row1['CODE_UM_ORD'] . "</font></td>\n";
							$ret .= "			<td><font color=red><input type=hidden id='T_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' value='". $row1['QTY_OPEN'] ."'><b>" . $row1['QTY_OPEN'] . "</b></font></td>\n";
							$ret .= "			<td><input type=button value='Save' id='" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' onClick=\"saveQtys(this.id)\"><div id='div_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "'></div></td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr align=center>\n";
							$ret .= "			<td colspan=6> </td>\n";
							$ret .= "			<td><font color=red>\n";
							$ret .= "				Qty Make:<input size=4 type=textbox id='M_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "'>\n";
							$ret .= "			</font></td>\n";
							$ret .= "			<td><font color=red>\n";
							$ret .= "				Qty Pull:<input size=4 type=textbox id='P_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "'>\n";
							$ret .= "			</font></td>\n";
							$ret .= " 	</tr>\n";
						}
						$ret .= " </table>\n";
					}
				}

				if ($Action == "showWOC") {
					if (isset($_POST["rowid"]))  {
						$rowid = $_POST["rowid"];

						$sql1 =  "select ";
						$sql1 .= " 	* ";
						$sql1 .= " from ";
						$sql1 .= " 	nsa.CP_VINA_WOCHG wc";
						$sql1 .= " where ";
						$sql1 .= " 	wc.rowid = ". $rowid ." ";
						QueryDatabase($sql1, $results1);

						while ($row1 = mssql_fetch_assoc($results1)) {
							$TQ = 0;

							$sql2 =  "select ";
							$sql2 .= " 	QTY_OPEN ";
							$sql2 .= " from ";
							$sql2 .= " 	nsa.CP_ORDLIN ol";
							$sql2 .= " where ";
							$sql2 .= " 	ol.ID_ORD = " . $row1['ID_ORD'] . " ";
							$sql2 .= " 	and ";
							$sql2 .= " 	ol.SEQ_LINE_ORD = " . $row1['SEQ_LINE_ORD'] . " ";
							QueryDatabase($sql2, $results2);
							while ($row2 = mssql_fetch_assoc($results2)) {
								$TQ = $row2['QTY_OPEN'];
							}

							$ret .= "			<td colspan=5> </td>\n";
							$ret .= "			<td><input id='ref_" . $row1['ID_ORD'] . "' type=button value='Refresh Order' onClick=\"refreshOrd('". $row1['ID_ORD'] ."')\"> </td>\n";
							$ret .= "			<td><font color=blue>\n";
							$ret .= "				Qty Make:<input size=4 type=textbox id='M_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' value='" . $row1['QTY_MAKE'] . "'>\n";
							$ret .= "			</font></td>\n";
							$ret .= "			<td><font color=green>\n";
							$ret .= "				Qty Pull:<input size=4 type=textbox id='P_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' value='" . $row1['QTY_PULL'] . "'>\n";
							$ret .= "			</font></td>\n";
							$ret .= "			<td><input type=hidden id='T_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' value='". $TQ ."'><input type=button value='Save' id='" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "' onClick=\"saveQtys(this.id)\"><div id='div_" . $row1['ID_ORD'] . "_" . $row1['SEQ_LINE_ORD'] . "'> Ord Qty: " . $TQ . "</div></td>\n";
						}
					}
				}
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>