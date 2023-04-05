<?php

	$DEBUG = 1;



	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	$DEBUG = 1;

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
			
			if (isset($_POST["action"])) {
				$action = $_POST["action"];

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);
				//$sql = "SET QUOTED_IDENTIFIER ON";
				//QueryDatabase($sql, $results);
				//$sql = "SET ANSI_PADDING ON";
				//QueryDatabase($sql, $results);

				switch($action) {
					case "show":
						//if (isset($_POST["order_num"]) && isset($_POST["po_num"]) && isset($_POST["ship_num"])){
						if (isset($_POST["order_num"]) && isset($_POST["po_num"])){

							$ORD = trim($_POST["order_num"]);
							$PO	= trim($_POST["po_num"]);
							//$SHIP	= trim($_POST["ship_num"]);

							$ret .= " <h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

							if ($ORD == '') {
								////////////////////////////////////
								/////QUICK QUERY TO GET ORDER NUMBER
								////////////////////////////////////
								$sql =  "SELECT ID_ORD ";
								$sql .=  " FROM nsa.CP_ORDHDR_PERM ";
								$sql .=  " WHERE ltrim(ID_PO_CUST) = '" . $PO ."' ";
								QueryDatabase($sql, $results);

								while ($row = mssql_fetch_assoc($results)) {
									$ORD = $row['ID_ORD'];
								}

							}

							if ($PO == '') {
								////////////////////////////////////
								/////QUICK QUERY TO GET PO NUMBER
								////////////////////////////////////
								$sql =  "SELECT ID_PO_CUST ";
								$sql .=  " FROM nsa.CP_ORDHDR_PERM ";
								$sql .=  " WHERE ltrim(ID_ORD) = '" . $ORD ."' ";
								QueryDatabase($sql, $results);
								while ($row = mssql_fetch_assoc($results)) {
									$PO = $row['ID_PO_CUST'];
								}
							}

							error_log("ORD: ".$ORD);
							error_log("PO: ".$PO);

							////////////////////////////////
							/////QUERY ORDER HEADER FOR INFO
							////////////////////////////////
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'><th colspan=8>Order Header</th></tr>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Order</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>PO #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Status</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Name Ship To</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Ship To Address</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Date Added</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Added By</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";

							$sql =  "SELECT ";
							$sql .= " ID_PO_CUST, ";
							$sql .= " ID_ORD, ";
							$sql .= " NAME_CUST_SHIPTO, ";
							$sql .= " ADDR_1, ";
							$sql .= " ADDR_2, ";
							$sql .= " ADDR_3, ";
							$sql .= " CODE_STAT_ORD, ";
							$sql .= " DATE_ADD, ";
							$sql .= " ID_USER_ADD ";
							$sql .= " FROM nsa.CP_ORDHDR_PERM ";
							$sql .= " WHERE ltrim(ID_PO_CUST) = '" . $PO ."' ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$dateADD = $row['DATE_ADD'];
								$dateADD1 = strtotime($dateADD);
								$formatted_date = date('m/d/Y',$dateADD1);

								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_ORD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_PO_CUST'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['CODE_STAT_ORD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['NAME_CUST_SHIPTO'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ADDR_1'] . " " . $row['ADDR_2'] . " " . $row['ADDR_3'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_USER_ADD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}//end while


							///////////////////////////////
							/////////QUEREY FOR ORDER LINE INFO
							///////////////////////////////
							$ret .= " <table class='sample'>\n";
							$ret .= " <br />";
							$ret .= " 	<tr class='blueHeader'><th colspan=10>Order Lines</th></tr>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Line</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>EDI Line</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>ID Item</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>ID Item Cust</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Descr</font>\n";
							$ret .= " 		</th>\n";							
							$ret .= " 		<th>\n";
							$ret .= "				<font>Qty</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Unit Price</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Price Net</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Date Added</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Added By</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";	

							$sql =  "SELECT ";
							$sql .= " DATE_ADD, ";
							$sql .= " SEQ_LINE_ORD, ";
							$sql .= " ID_LINE_PO_EDI, ";
							$sql .= " ID_USER_ADD, ";
							$sql .= " ID_ITEM, ";
							$sql .= " ID_ITEM_CUST, ";
							$sql .= " concat(DESCR_1,' ',DESCR_2) as DESCR_CONCAT, ";
							$sql .= " QTY_ORG, ";
							$sql .= " convert(int,(substring(PRICE_SELL_NET_VP_FC,7,10)))*.01 as UNIT_PRICE, ";
							$sql .= " PRICE_NET ";
							$sql .= " FROM nsa.CP_ORDLIN_PERM ";
							$sql .= " WHERE ltrim(ID_ORD) = '" . $ORD ."' ";
							$sql .= " ORDER BY SEQ_LINE_ORD asc";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$dateADD = $row['DATE_ADD'];
								$dateADD1 = strtotime($dateADD);
								$formatted_date = date('m/d/Y',$dateADD1);

								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['SEQ_LINE_ORD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_LINE_PO_EDI'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_ITEM'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_ITEM_CUST'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['DESCR_CONCAT'] . "</font>\n";
								$ret .= " 		</td>\n";								
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['QTY_ORG'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['UNIT_PRICE'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['PRICE_NET'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_USER_ADD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}//end while


							////////////////////////////
							///////QUERY FOR SHIPMENT HDR INFO
							///////////////////////////
							$ret .= " <table class='sample'>\n";
							$ret .= " <br />";
							$ret .= " 	<tr class='blueHeader'><th colspan=7>Shipment Header</th></tr>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Ship #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Ship Via</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>ASN Flag</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Ship Confirm Flag</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Tracking #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Ship Date</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Added By</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";	

							$sql =  "SELECT ";
							$sql .= " sp.DATE_ADD, ";
							$sql .= " sp.ID_SHIP, ";
							$sql .= " sp.ID_USER_ADD, ";
							$sql .= " sp.CODE_SHIP_VIA_CP, ";
							$sql .= " sp.DESCR_SHIP_VIA, ";
							$sql .= " sp.FLAG_ASN_EDI, ";
							$sql .= " sp.CODE_STAT_CONFIRM, ";
							$sql .= " si.ID_TRACK ";
							$sql .= " FROM nsa.CP_SHPHDR_PERM sp ";
							$sql .= " LEFT JOIN nsa.CP_SHIP_IMPORT si ";
							$sql .= " on sp.ID_SHIP = si.ID_SHIP ";
							$sql .= " WHERE ltrim(sp.ID_ORD) = '" . $ORD ."' ";
							$sql .= " ORDER BY sp.ID_SHIP asc";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$dateADD = $row['DATE_ADD'];
								$dateADD1 = strtotime($dateADD);
								$formatted_date = date('m/d/Y',$dateADD1);

								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_SHIP'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['CODE_SHIP_VIA_CP'] . " - " . $row['DESCR_SHIP_VIA'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['FLAG_ASN_EDI'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['CODE_STAT_CONFIRM'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_TRACK'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_USER_ADD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}//end while


							////////////////////////////
							///////QUERY FOR SHIPMENT LINE INFO
							///////////////////////////
							$ret .= " <table class='sample'>\n";
							$ret .= " <br />";
							$ret .= " 	<tr class='blueHeader'><th colspan=11>Shipment Lines</th></tr>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>ID Ship</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Ship Line</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>EDI Line</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Item</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Descr</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Qty Shipped</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Qty Cont 2 (Box #)</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Confirm Ship</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>UCC-128</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Date Added</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Added By</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";	

							$sql =  "SELECT ";
							$sql .= " l.ID_SHIP, ";
							$sql .= " l.SEQ_LINE_ORD, ";
							$sql .= " l.ID_LINE_PO_EDI, ";
							$sql .= " l.ID_ITEM, ";
							$sql .= " concat(l.DESCR_1,' ',l.DESCR_2) as DESCR_CONCAT, ";
							$sql .= " l.QTY_SHIP, ";
							$sql .= " l.QTY_CONT_2, ";
							$sql .= " l.ID_USER_ADD, ";
							$sql .= " l.DATE_ADD, ";
							$sql .= " l.FLAG_CONFIRM_SHIP, ";
							$sql .= " u.WRK_SSCC as UCC_128 ";
							$sql .= " FROM nsa.CP_SHPLIN_PERM l ";
							$sql .= " LEFT JOIN nsa.CP_BOX_DETAIL u ";
							$sql .= " on l.ID_SHIP = u.ID_SHIP ";
							$sql .= " and l.SEQ_LINE_ORD = u.SEQ_LINE_ORD ";
							$sql .= " WHERE ltrim(l.ID_ORD) = '" . $ORD ."' ";
							$sql .= " ORDER BY l.ID_SHIP asc, l.SEQ_LINE_ORD asc ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$dateADD = $row['DATE_ADD'];
								$dateADD1 = strtotime($dateADD);
								$formatted_date = date('m/d/Y',$dateADD1);

								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_SHIP'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['SEQ_LINE_ORD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_LINE_PO_EDI'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_ITEM'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['DESCR_CONCAT'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['QTY_SHIP'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['QTY_CONT_2'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['FLAG_CONFIRM_SHIP'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['UCC_128'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_USER_ADD'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}//end while

							///////////////////////////////////
							////////QUERY FOR INVOICE HDR INFO
							///////////////////////////////////
							$ret .= " <table class='sample'>\n";
							$ret .= " <br />";
							$ret .= " 	<tr class='blueHeader'><th colspan=5>Invoice Header</th></tr>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Invoice #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Invoice Date</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Code Source</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Flag 810</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Flag ASN EDI</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";	

							$sql =  "SELECT ";
							$sql .= " DATE_ADD, ";
							$sql .= " ID_INVC, ";
							$sql .= " CODE_SRC_EDI, ";
							$sql .= " FLAG_810, ";
							$sql .= " FLAG_ASN_EDI ";
							$sql .= " FROM nsa.CP_INVHDR_HIST ";
							$sql .= " WHERE ltrim(ID_ORD) = '" . $ORD ."' ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {	
								$dateADD = $row['DATE_ADD'];
								$dateADD1 = strtotime($dateADD);
								$formatted_date = date('m/d/Y',$dateADD1);

								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_INVC'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['CODE_SRC_EDI'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['FLAG_810'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['FLAG_ASN_EDI'] . " </font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}//end while
						}//end if

					break;


/*					
					case "update_hdr";
						if  (isset($_POST["rowid"]) && isset($_POST["so_status"])) {
							$strRet = 'ERROR!';
							$ROWID = (trim($_POST["rowid"]));
							$STAT_HDR = ($_POST["so_status"]);

							$sql = "SET ANSI_NULLS ON";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS ON";
							QueryDatabase($sql, $results);
							$sql = "SET QUOTED_IDENTIFIER ON";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_PADDING ON";
							QueryDatabase($sql, $results);
							$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
							QueryDatabase($sql, $results);

							$sql = " UPDATE ";
							$sql .= " nsa.SHPORD_HDR ";
							$sql .= " Set STAT_REC_SO = '". $STAT_HDR ."' ";
							$sql .= " where ROWID = '" . $ROWID ."' ";
							QueryDatabase($sql, $results);

							if ($results == '1') {
								$strRet = 'OK!';
							}
							$ret = "	<font>" . $strRet . "</font>\n";

							$sql = "SET ANSI_NULLS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET QUOTED_IDENTIFIER OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_PADDING OFF";
							QueryDatabase($sql, $results);
						}
					break;
					case "update_oper";
						if (isset($_POST["rowid"]) && isset($_POST["oper_status"])) {
							$strRet = 'ERROR!';
							$ROWID = (trim($_POST["rowid"]));
							$STAT_OPER = ($_POST["oper_status"]);

							$sql = "SET ANSI_NULLS ON";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS ON";
							QueryDatabase($sql, $results);
							$sql = "SET QUOTED_IDENTIFIER ON";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_PADDING ON";
							QueryDatabase($sql, $results);
							$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
							QueryDatabase($sql, $results);

							$sql = " Update ";
							$sql .= " nsa.SHPORD_OPER ";
							$sql .= " Set STAT_REC_OPER  = '". $STAT_OPER ."', ";
							$sql .= " STAT_REC_OPER_1  = '". $STAT_OPER ."' ";
							$sql .= " where ROWID = '". $ROWID ."' ";
							QueryDatabase($sql, $results);

							if ($results == '1') {
								$strRet = 'OK!';
							}
							$ret = "	<font>" . $strRet . "</font>\n";

							$sql = "SET ANSI_NULLS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET QUOTED_IDENTIFIER OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_PADDING OFF";
							QueryDatabase($sql, $results);
						}
					break;       */
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