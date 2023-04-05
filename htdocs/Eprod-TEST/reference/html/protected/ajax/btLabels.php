<?php

	

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");

$DEBUG = 1;
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

			if (isset($_POST["action"])) {
				$action = $_POST["action"];

				switch ($action) {

					case "location_change":
						if (isset($_POST["location"]) && isset($_POST["printerSize"])) {
							$location = $_POST["location"];
							$printerSize = stripNonANChars($_POST["printerSize"]);
							$userStoredPrinterName = "";

							if ($printerSize == "2X2") {
								$userStoredPrinterName = $UserRow['LBL_PRINTER_2x2'];
							}
							if ($printerSize == "4X6") {
								$userStoredPrinterName = $UserRow['LBL_PRINTER_4x6'];
							}
							
							$ret .= "<option value='SELECT'>-- SELECT --</option>\n";

							$sql =  "SELECT ";
							$sql .= " LOCATION, ";
							$sql .= " PRINTER_NAME, ";
							$sql .= " DEFINED_SIZE, ";
							$sql .= " FLAG_SHOW ";
							$sql .= " FROM nsa.BARTENDER_PRINTERS ";
							$sql .= " WHERE LOCATION = '".$location."'";
							$sql .= " and (DEFINED_SIZE = '".$printerSize."' ";
							$sql .= "  OR isnull(DEFINED_SIZE,'')=''";
							$sql .= " )";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {	
								$SELECTED = "";
								if ($userStoredPrinterName == $row['PRINTER_NAME']) {
									$SELECTED = "SELECTED";
								}
								$ret .= "<option value='".$row['PRINTER_NAME']."' ".$SELECTED.">".$row['PRINTER_NAME']."</option>\n";
							}
						}
					break;

					case "savePrinterChange":
						if (isset($_POST["printerSelID"]) && isset($_POST["printer"])) {
							$printerSelID	= stripNonANChars(trim($_POST["printerSelID"]));
							$printer = stripIllegalChars4(trim($_POST["printer"]));
							$printerSizeField = "";

							if ($printerSelID == 'sel2x2printer') {
								$printerSizeField = 'LBL_PRINTER_2x2';
							} else if ($printerSelID = 'sel4x6printer') {
								$printerSizeField = 'LBL_PRINTER_4x6';
							} 

							$sql =  "UPDATE nsa.DCWEB_AUTH ";
							$sql .= " SET ".$printerSizeField." = '".$printer."' ";
							$sql .= " WHERE ID_USER = '".$UserRow['ID_USER']."' ";
							QueryDatabase($sql, $results);

							if ($results == '1') {
								$ret .= 'Saved';
							} else {
								$ret .= 'Error';
							} 
						}
					break;

					case "selModeChange":
						if (isset($_POST["selMode"])) {
							$mode = $_POST["selMode"];
							error_log("mode: " . $mode);

							if ($mode == "shopOrder") {
								$ret .= " <table>";
								$ret .= " 	<tr>";
								$ret .= " 		<th colspan=2>Scan Shop Order: </th>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_id_ord'>";
								$ret .= " 		<td>Shop Order: </td>";
								$ret .= " 		<td>";
								$ret .= "			<input id='so' type=text onkeyup=\"nextOnDash('so','sufx')\" maxlength=9 size=10 autofocus> -\n";
								$ret .= "			<input id='sufx' type=text onkeyup=\"checkSufxLength()\" maxlength=3 size=4>\n";
								$ret .= "		</td>";
								$ret .= " 	</tr>";

								$ret .= " 	<tr>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td><INPUT id='dw_submit' type='button' value='Get Labels' onClick=\"getSoLabelList()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
								$ret .= " 	</tr>\n";								
								$ret .= " </table>";
								$ret .= " <table id='table_ret_form'>";
								$ret .= " </table>";
							}
			
							if ($mode == "itemNumber") {
								$ret .= " <table>";
								$ret .= " 	<tr>";
								$ret .= " 		<th colspan=2>Item Number Entry: </th>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_so1'>";
								$ret .= " 		<td>Item Number: </td>";
								$ret .= " 		<td><div id='div_id_item'>";
								$ret .= "			<input id='id_item' type='text' onkeyup=\"idItemChange()\" size=30 autofocus>";
								$ret .= "			<input id='so' type='hidden' value=''>\n";
								$ret .= "			<input id='sufx' type='hidden' value=''>\n";
								$ret .= "		</div></td>";
								$ret .= " 	</tr>";
								$ret .= " 	<tr>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td><INPUT id='dw_submit' type='button' value='Get Labels' onClick=\"getItemLabelList()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
								$ret .= " 	</tr>\n";
								$ret .= " </table>";
								$ret .= " <table id='table_ret_form'>";
								$ret .= " </table>";
							}

							if ($mode == "poReceived") {
								$ret .= " <table>";
								$ret .= " 	<tr>";
								$ret .= " 		<th colspan=2>PO Number Entry: </th>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_PO'>";
								$ret .= " 		<td>PO Number: </td>";
								$ret .= " 		<td><div id='div_id_PO'>";
								$ret .= "			<input id='id_po' type='text'size=30 autofocus>";
								$ret .= "		</div></td>";
								$ret .= " 	</tr>";

								$ret .= " 	<tr>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td><INPUT id='dw_submit' type='button' value='Get Labels' onClick=\"getPOLabelList()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
								$ret .= " 	</tr>\n";
								$ret .= " </table>";
								$ret .= " <table id='table_ret_form'>";
								$ret .= " </table>";
							}

							if ($mode == "intransit") {
								$ret .= " <table>";
								$ret .= " 	<tr>";
								$ret .= " 		<th colspan=2>Intransit Number Entry: </th>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_INTRANSIT'>";
								$ret .= " 		<td>Intransit Number: </td>";
								$ret .= " 		<td><div id='div_id_INTRANSIT'>";
								$ret .= "			<input id='id_intransit' type='text'size=30 autofocus>";
								$ret .= "		</div></td>";
								$ret .= " 	</tr>";

								$ret .= " 	<tr>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td><INPUT id='dw_submit' type='button' value='Get Labels' onClick=\"getIntransitLabelList()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
								$ret .= " 	</tr>\n";
								$ret .= " </table>";
								$ret .= " <table id='table_ret_form'>";
								$ret .= " </table>";
							}
						}
					break;

					case "getSoLabelList":
						if (isset($_POST["so"]) && isset($_POST["sufx"])) {
							$SO	= stripNonANChars(trim($_POST["so"]));
							$SUFX = stripNonANChars(trim($_POST["sufx"]));

							$sql =  "SELECT ";
							$sql .= " sh.ID_SO, ";
							$sql .= " sh.SUFX_SO, ";
							$sql .= " sh.ID_ITEM_PAR as ID_ITEM, ";
							$sql .= " sh.QTY_ORD, ";
							$sql .= " attrUPC.VAL_STRING_ATTR as UPC_CODE, ";
							$sql .= " isnull(oh.ID_PO_CUST,'') as ID_PO_CUST ";
							$sql .= " FROM nsa.SHPORD_HDR sh ";
							$sql .= " LEFT JOIN nsa.IM_CMCD_ATTR_VALUE attrUPC ";
							$sql .= " on sh.ID_ITEM_PAR = attrUPC.ID_ITEM ";
							$sql .= " and attrUPC.ID_ATTR = 'UPC_CODE' ";
							$sql .= " LEFT JOIN nsa.ITMMAS_BASE ib ";
							$sql .= " on sh.ID_ITEM_PAR = ib.ID_ITEM ";
							$sql .= " LEFT JOIN nsa.CP_ORDLIN ol ";
							$sql .= " on sh.ID_SO = ol.ID_SO ";
							$sql .= " and sh.SUFX_SO = ol.SUFX_SO ";
							$sql .= " LEFT JOIN nsa.CP_ORDHDR oh ";
							$sql .= " on ol.ID_ORD = oh.ID_ORD ";
							$sql .= " WHERE ltrim(sh.ID_SO) = '".$SO."' ";
							$sql .= " and sh.SUFX_SO = '".$SUFX."' ";
							QueryDatabase($sql, $results);
							error_log($sql);
							if (mssql_num_rows($results) > 0) {
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= "<table>\n";
									$ret .= " <input type='hidden' id='hid_ID_ITEM' value='".$row['ID_ITEM']."'>";
									$ret .= "	<th colspan='3'>".$row['ID_ITEM']."</br>Labels</th>\n";

									if (trim($row['UPC_CODE']) !== "") {
										$ret .= "	<tr>\n";
										$ret .= "		<td class='icon'>2\"x2\"</br>UPC Label<a><img class='icon' src='images/UPCIcon.jpg'></a></td>\n";
										$ret .= "		<td class='icon'></td>\n";
										$ret .= "		<td class='icon'>Qty to Print</br><input id='tb_UPC_QTY_LABELS' type='text' maxlength=3 size=4 value='".$row['QTY_ORD']."'></br></br><input id='button_Print_UPC_Labels' type='button' value='Print' onClick=\"printUPCLabels()\"></td>\n";
										$ret .= "	</tr>\n";
									} else {
										$ret .= "	<tr>\n";
										$ret .= "		<td class='icon'>2\"x2\"</br>UPC Label<a><img class='icon' src='images/UPCIcon.jpg'></a></td>\n";
										$ret .= "		<td class='icon'></td>\n";
										$ret .= "		<td class='icon'>UPC not on record</br><input id='button_Request_UPC' type='button' value='Request from R&D' onClick=\"requestUPC('".$SO."','".$SUFX."')\"></br><font id='requestUPC_RESPONSE'></font></td>\n";
										$ret .= "	</tr>\n";
									}



									$ret .= "	<tr>\n";
									$ret .= "		<td class='icon'>4\"x6\"</br>Box Label<a><img class='icon' src='images/BoxQtyIcon.jpg'></a></td>\n";
									$ret .= "		<td class='icon'>Qty in Box</br>";
									$ret .= " 			<input id='tb_BoxQty_IN_BOX_QTY' type=text maxlength=3 size=4 value=''></br>PO Number</br>(Optional)</br>";
									$ret .= " 			<input id='tb_BoxQty_PO_NUM' type=text maxlength=20 size=10 value='".$row['ID_PO_CUST']."'></br>\n";
									$ret .= "		</td>\n";

									$ret .= "		<td class='icon'>Qty to Print</br><input id='tb_BoxQty_QTY_LABELS' type='text' maxlength=3 size=4 value=''></br><input id='button_Print_BoxQty_Labels' type='button' value='Print' onClick=\"printBoxQtyLabels()\"></td>\n";
									$ret .= "	</tr>\n";									

									$ret .= "</table>\n";

								}
							}
						}
					break;

					case "printUPCLabels":
						if (isset($_POST["id_item"]) && isset($_POST["label_qty"]) && isset($_POST["printer"])) {
							$ID_ITEM	= stripIllegalChars4(trim($_POST["id_item"]));
							$LABEL_QTY = stripNonANChars(trim($_POST["label_qty"]));
							$ID_SO = stripNonANChars(trim($_POST["id_so"]));
							$SUFX_SO = stripNonANChars(trim($_POST["sufx_so"]));
							$PRINTER = $_POST["printer"];
							$UPC_CODE = '';
							$DESCR_1 = '';
							$DESCR_2 = '';
							$TIMESTAMP = microtime();

							$sql =  "SELECT ";
							$sql .= " ib.ID_ITEM, ";
							$sql .= " ib.DESCR_1, ";
							$sql .= " ib.DESCR_2, ";
							$sql .= " attrUPC.VAL_STRING_ATTR as UPC_CODE ";
							$sql .= " FROM nsa.ITMMAS_BASE ib ";
							$sql .= " LEFT JOIN nsa.IM_CMCD_ATTR_VALUE attrUPC ";
							$sql .= " on ib.ID_ITEM = attrUPC.ID_ITEM ";
							$sql .= " and attrUPC.ID_ATTR = 'UPC_CODE' ";
							$sql .= " WHERE ib.ID_ITEM = '".$ID_ITEM."' ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$UPC_CODE = $row['UPC_CODE'];
								$DESCR_1 = $row['DESCR_1'];
								$DESCR_2 = $row['DESCR_2'];
							}

							$Line1 = '%BTW% /AF="\\\\Fs1\netshare\Bartender_Formats\UPC_Label_AUTO.btw" /PRN="'.$PRINTER.'" /R=3 /P /D="%Trigger File Name%" /DD' . PHP_EOL;
							$Line2 = '%END%' . PHP_EOL;

							$hdrLine  = 'LBL_Qty	';
							$hdrLine .= 'ID_SO	';
							$hdrLine .= 'SUFX_SO	';
							$hdrLine .= 'ID_ITEM	';
							$hdrLine .= 'DESCR_1	';
							$hdrLine .= 'DESCR_2	';
							$hdrLine .= 'UPC_CODE	'. PHP_EOL;

							$valLine  = $LABEL_QTY.'	';
							$valLine .= $ID_SO.'	';
							$valLine .= $SUFX_SO.'	';
							$valLine .= $ID_ITEM.'	';
							$valLine .=	$DESCR_1.'	';
							$valLine .=	$DESCR_2.'	';
							$valLine .=	$UPC_CODE.'	'. PHP_EOL;

							$FileContents = $Line1 . $Line2 . $hdrLine . $valLine;

							$tmpOutputFile = "/tmp/UPC_LBL_".$TIMESTAMP.".dat";
							$finalOutputFile = "../TCM_Labels/UPC_LBL_".$TIMESTAMP.".dat";

							$myfile = fopen($tmpOutputFile, "w") or die("Unable to open file!");
							fwrite($myfile, $FileContents);
							fclose($myfile);
							rename($tmpOutputFile,$finalOutputFile);
						}
					break;



					case "printBoxQtyLabels":
						if (isset($_POST["id_item"]) && isset($_POST["label_qty"]) && isset($_POST["printer"])) {
							$ID_ITEM	= stripIllegalChars4(trim($_POST["id_item"]));
							$LABEL_QTY = stripNonANChars(trim($_POST["label_qty"]));
							$QTY_IN_BOX = stripNonANChars(trim($_POST["qty_in_box"]));
							$ID_PO_CUST = stripNonANChars(trim($_POST["id_po_cust"]));
							$PRINTER = $_POST["printer"];

							$DESCR_1 = '';
							$DESCR_2 = '';
							$BIN_PRIM = '';
							$TIMESTAMP = microtime();

							$sql =  "SELECT ";
							$sql .= " ib.ID_ITEM, ";
							$sql .= " ib.DESCR_1, ";
							$sql .= " ib.DESCR_2, ";
							$sql .= " il.BIN_PRIM ";
							$sql .= " FROM nsa.ITMMAS_BASE ib ";
							$sql .= " LEFT JOIN nsa.ITMMAS_LOC il ";
							$sql .= " on ib.ID_ITEM = il.ID_ITEM ";
							$sql .= " and il.ID_LOC = '10' ";
							$sql .= " WHERE ib.ID_ITEM = '".$ID_ITEM."' ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$DESCR_1 = $row['DESCR_1'];
								$DESCR_2 = $row['DESCR_2'];
								$BIN_PRIM = $row['BIN_PRIM'];
							}

							$Line1 = '%BTW% /AF="\\\\Fs1\netshare\Bartender_Formats\Box_Qty_Label_AUTO.btw" /PRN="'.$PRINTER.'" /R=3 /P /D="%Trigger File Name%" /DD' . PHP_EOL;
							$Line2 = '%END%' . PHP_EOL;

							$hdrLine  = 'Lbl_Qty	';
							$hdrLine .= 'ID_ITEM	';
							$hdrLine .= 'DESCR_1	';
							$hdrLine .= 'DESCR_2	';
							$hdrLine .= 'QTY_IN_BOX	';
							$hdrLine .= 'BIN_PRIM	';
							$hdrLine .= 'PO_NUM	'. PHP_EOL;

							$valLine  = $LABEL_QTY.'	';
							$valLine .= $ID_ITEM.'	';
							$valLine .=	$DESCR_1.'	';
							$valLine .=	$DESCR_2.'	';
							$valLine .=	$QTY_IN_BOX.'	';
							$valLine .=	$BIN_PRIM.'	';
							$valLine .=	$ID_PO_CUST.'	'. PHP_EOL;

							$FileContents = $Line1 . $Line2 . $hdrLine . $valLine;

							$tmpOutputFile = "/tmp/BOX_QTY_LBL_".$TIMESTAMP.".dat";
							$finalOutputFile = "../TCM_Labels/BOX_QTY_LBL_".$TIMESTAMP.".dat";

							$myfile = fopen($tmpOutputFile, "w") or die("Unable to open file!");
							fwrite($myfile, $FileContents);
							fclose($myfile);
							rename($tmpOutputFile,$finalOutputFile);
						}
					break;


					case "getItemLabelList":
						if (isset($_POST["id_item"])) {
							$ID_ITEM = stripIllegalChars4(trim($_POST["id_item"]));

							$sql =  "SELECT ";
							$sql .= " ib.ID_ITEM, ";
							$sql .= " il.BIN_PRIM, ";
							$sql .= " ib.CODE_UM_STK, ";
							$sql .= " ib.CODE_UM_PUR, ";
							$sql .= " ib.RATIO_STK_PUR, ";
							$sql .= " rtrim(concat(ib.DESCR_1, ' ', ib.DESCR_2)) as ITEM_DESCR, ";
							$sql .= " ib.rowid as ib_rowid, ";
							$sql .= " convert(varchar, getDate(), 23) as DATE_PRINT, ";
							$sql .= " attrUPC.VAL_STRING_ATTR as UPC_CODE ";
							$sql .= " FROM nsa.ITMMAS_BASE ib ";
							$sql .= " LEFT JOIN nsa.IM_CMCD_ATTR_VALUE attrUPC ";
							$sql .= " on ib.ID_ITEM = attrUPC.ID_ITEM ";
							$sql .= " and attrUPC.ID_ATTR = 'UPC_CODE' ";
							$sql .= " LEFT JOIN nsa.ITMMAS_LOC il ";
							$sql .= " on ib.ID_ITEM = il.ID_ITEM ";
							$sql .= " and il.ID_LOC = '10' ";
							$sql .= " WHERE ib.ID_ITEM = '".$ID_ITEM."' ";

							QueryDatabase($sql, $results);
							error_log($sql);
							if (mssql_num_rows($results) > 0) {
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= "<table>\n";
									$ret .= " <input type='hidden' id='hid_ID_ITEM' value='".$row['ID_ITEM']."'>";

									$ret .= "			<input id='hid_RollTag_STK_UOM' type=hidden value='".$row['CODE_UM_STK']."'></input>\n";
									$ret .= "			<input id='hid_RollTag_PUR_UOM' type=hidden value='".$row['CODE_UM_PUR']."'></input>\n";
									$ret .= "			<input id='hid_RollTag_RATIO_STK_PUR' type=hidden value='".$row['RATIO_STK_PUR']."'></input>\n";
									$ret .= "			<input id='hid_RollTag_ITEM_DESCR' type=hidden value='".$row['ITEM_DESCR']."'></input>\n";
									$ret .= "			<input id='hid_RollTag_DATE_PRINT' type=hidden value='".$row['DATE_PRINT']."'></input>\n";


									$ret .= "	<th colspan='3'>".$row['ID_ITEM']."</br>Labels</th>\n";

									if (trim($row['UPC_CODE']) !== "") {
										$ret .= "	<tr>\n";
										$ret .= "		<td class='icon'>2\"x2\"</br>UPC Label<a><img class='icon' src='images/UPCIcon.jpg'></a></td>\n";
										$ret .= "		<td class='icon'></td>\n";
										$ret .= "		<td class='icon'>Qty to Print</br><input id='tb_UPC_QTY_LABELS' type='text' maxlength=3 size=4 value=''></br></br><input id='button_Print_UPC_Labels' type='button' value='Print' onClick=\"printUPCLabels()\"></td>\n";
										$ret .= "	</tr>\n";
									} else {
										$ret .= "	<tr>\n";
										$ret .= "		<td class='icon'>2\"x2\"</br>UPC Label<a><img class='icon' src='images/UPCIcon.jpg'></a></td>\n";
										$ret .= "		<td class='icon'></td>\n";
										$ret .= "		<td class='icon'>UPC not on record</br><input id='button_Request_UPC' type='button' value='Request from R&D' onClick=\"requestUPC('','')\"></br><font id='requestUPC_RESPONSE'></font></td>\n";
										$ret .= "	</tr>\n";
									}

									$ret .= "	<tr>\n";
									$ret .= "		<td class='icon'>4\"x6\"</br>Box Label<a><img class='icon' src='images/BoxQtyIcon.jpg'></a></td>\n";
									$ret .= "		<td class='icon'>Qty in Box</br>";
									$ret .= " 			<input id='tb_BoxQty_IN_BOX_QTY' type=text maxlength=3 size=4 value=''></br>PO Number</br>(Optional)</br>";
									$ret .= " 			<input id='tb_BoxQty_PO_NUM' type=text maxlength=20 size=10 value=''></br>\n";
									$ret .= "		</td>\n";
									$ret .= "		<td class='icon'>Qty to Print</br><input id='tb_BoxQty_QTY_LABELS' type='text' maxlength=3 size=4 value=''></br><input id='button_Print_BoxQty_Labels' type='button' value='Print' onClick=\"printBoxQtyLabels()\"></td>\n";
									$ret .= "	</tr>\n";

									$ret .= "	<tr>\n";
									$ret .= "		<td class='icon'>4\"x6\"</br>Roll Tag Label<a><img class='icon' src='images/NON-BIN-Icon.jpg'></a></td>\n";
									$ret .= "		<td class='icon'>Qty on Roll</br>";
									$ret .= " 			<input id='tb_RollTag_QTY_ON_TAG' type=text maxlength=5 size=3 value=''>";


									$ret .= "			<select id='sel_RollTag_UOM' onChange=\"changeRollTagUOM()\">\n";
									$ret .= "				<option SELECTED value='".$row['CODE_UM_PUR']."'>".$row['CODE_UM_PUR']."</option>\n";
									$ret .= "				<option value='".$row['CODE_UM_STK']."'>".$row['CODE_UM_STK']."</option>\n";
									$ret .= "			</select>\n";

									$ret .= " 			</br></br>BIN</br>";
									$ret .= " 			<input id='tb_RollTag_BIN_PRIM' type=text maxlength=20 size=10 value='".$row['BIN_PRIM']."'>\n";

									$ret .= " 			</br></br>PO Number</br>(Optional)</br>";
									$ret .= " 			<input id='tb_RollTag_PO_NUM' type=text maxlength=20 size=10 value=''></br>\n";
									$ret .= "		</td>\n";
									$ret .= "		<td class='icon'>Qty to Print</br><input id='tb_RollTag_QTY_LABELS' type='text' maxlength=3 size=4 value=''></br><input id='button_Print_RollTag_Labels' type='button' value='Print' onClick=\"printRollTagLabels()\"></td>\n";
									$ret .= "	</tr>\n";

									$ret .= "</table>\n";

								}
							}
						}
					break;


					case "requestUPC":
						if (isset($_POST["id_item"]) && isset($_POST["id_so"]) && isset($_POST["sufx_so"])) {
							$ID_ITEM	= stripIllegalChars4(trim($_POST["id_item"]));
							$ID_SO = stripNonANChars(trim($_POST["id_so"]));
							$SUFX_SO = stripNonANChars(trim($_POST["sufx_so"]));

							error_log("REQUEST UPC from R&D: ".$ID_ITEM);

							$to = "rd@thinknsa.com";
							
							if ($TEST_ENV) {
								$to = "gvandyne@thinknsa.com";
							}

							$subject = "UPC Code Requested for Item: " . $ID_ITEM;
							$body = "UPC Code Requested for Item: " . $ID_ITEM ."\r\nBy: " . $UserRow['NAME_EMP'];
							if ($ID_SO !== '' && $SUFX_SO !== '') {
								$body .= "\r\nShop Order: ".$ID_SO."-".$SUFX_SO;
							}
							
							$headers = "From: eProduction@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
							mail($to, $subject, $body, $headers);
							error_log("### UPC Request email sent to: " . $to);
						}
						
					break;


					case "printRollTagLabels":
						if (isset($_POST["id_po"]) && isset($_POST["id_item"]) && isset($_POST["uom"]) && isset($_POST["date_print"]) && isset($_POST["item_descr"]) && isset($_POST["bin_prim"]) && isset($_POST["qty_on_tag"]) && isset($_POST["qty_to_print"]) && isset($_POST["printer"])) {

							$ID_PO = stripIllegalChars4(trim($_POST["id_po"]));
							$ID_ITEM = stripIllegalChars4(trim($_POST["id_item"]));
							$ID_ITEM_VND = "";
							$UOM = stripIllegalChars4(trim($_POST["uom"]));
							$NAME_VND_ORDFM = "";
							$DATE_RCV = stripIllegalChars4(trim($_POST["date_print"]));
							$ITEM_DESCR = stripIllegalChars4(trim($_POST["item_descr"]));
							$BIN_PRIM = stripIllegalChars4(trim($_POST["bin_prim"]));
							$QTY_ON_TAG = stripNonANChars(trim($_POST["qty_on_tag"]));
							$QTY_TO_PRINT = stripNonANChars(trim($_POST["qty_to_print"]));
							$PRINTER = $_POST["printer"];
							$TIMESTAMP = microtime();

							$Line1 = '%BTW% /AF="\\\\Fs1\netshare\Bartender_Formats\NON-BINTAG.btw" /PRN="'.$PRINTER.'" /R=3 /P /D="%Trigger File Name%" /DD' . PHP_EOL;
							$Line2 = '%END%' . PHP_EOL;

							$hdrLine  = 'Lbl_Qty	';
							$hdrLine .= 'ID_ITEM	';
							$hdrLine .= 'QTY_IN_BOX	';
							$hdrLine .= 'UOM	';
							$hdrLine .= 'BIN_PRIM	';
							$hdrLine .= 'ID_PO	';
							$hdrLine .= 'ID_ITEM_VND	';
							$hdrLine .= 'NAME_VND_ORDFM	';
							$hdrLine .= 'DATE_RCV	';
							$hdrLine .= 'DESCR_1	'. PHP_EOL;

							$valLine  = $QTY_TO_PRINT.'	';
							$valLine .= $ID_ITEM.'	';
							$valLine .=	$QTY_ON_TAG.'	';
							$valLine .=	$UOM.'	';
							$valLine .=	$BIN_PRIM.'	';
							$valLine .=	$ID_PO.'	';
							$valLine .=	$ID_ITEM_VND.'	';
							$valLine .=	$NAME_VND_ORDFM.'	';
							$valLine .=	$DATE_RCV.'	';
							$valLine .=	$ITEM_DESCR.'	'. PHP_EOL;

							$FileContents = $Line1 . $Line2 . $hdrLine . $valLine;

							$tmpOutputFile = "/tmp/NON-BINTAG_LBL_".$TIMESTAMP.".dat";
							$finalOutputFile = "../TCM_Labels/NON-BINTAG_LBL_".$TIMESTAMP.".dat";

							$myfile = fopen($tmpOutputFile, "w") or die("Unable to open file!");
							fwrite($myfile, $FileContents);
							fclose($myfile);
							rename($tmpOutputFile,$finalOutputFile);
						}
					break;


					case "getPOLabelList":
						if (isset($_POST["id_po"])) {
							$ID_PO = stripIllegalChars4(trim($_POST["id_po"]));

							$sql  = " SELECT ";
							$sql .= " h.ID_PO, ";
							$sql .= " h.NAME_VND_ORDFM ";
							$sql .= " FROM nsa.POHIST_HDR h ";
							$sql .= " WHERE h.id_po = '".$ID_PO."' ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$NAME_VND_ORDFM = $row['NAME_VND_ORDFM'];
							}

							$sql  = " SELECT ";
							$sql .= " r.ID_PO, ";
							$sql .= " r.ID_LINE_PO, ";
							$sql .= " h.NAME_VND_ORDFM, ";
							$sql .= " l.BIN_PRIM, ";
							$sql .= " a.ID_ITEM, ";
							$sql .= " iv.ID_ITEM_VND, ";
							$sql .= " a.CODE_UM_PUR, ";
							$sql .= " b.CODE_UM_STK, ";
							$sql .= " b.RATIO_STK_PUR, ";
							$sql .= " rtrim(concat(b.DESCR_1, ' ', b.DESCR_2)) as ITEM_DESCR, ";
							$sql .= " convert(varchar, r.DATE_RCV, 23) as DATE_RCV, ";
							$sql .= " r.QTY_RCV, ";
							$sql .= " r.rowid as recv_rowid, ";
							$sql .= " l.FLAG_TRACK_BIN ";
							$sql .= " FROM nsa.POHIST_LINE_RCPT r ";
							$sql .= " LEFT JOIN nsa.POHIST_LINE_ADD a ";
							$sql .= " on r.ID_PO = a.ID_PO ";
							$sql .= " and r.ID_LINE_PO = a.ID_LINE_PO ";
							$sql .= " LEFT JOIN nsa.POHIST_HDR h ";
							$sql .= " on r.ID_PO = h.ID_PO ";
							$sql .= " LEFT JOIN nsa.ITMMAS_LOC l ";
							$sql .= " on r.ID_LOC = l.ID_LOC ";
							$sql .= " and r.ID_ITEM = l.ID_ITEM ";
							$sql .= " LEFT JOIN nsa.ITMMAS_BASE b ";
							$sql .= " on r.ID_ITEM = b.ID_ITEM ";
							$sql .= " LEFT JOIN nsa.ITMMAS_VND iv ";
							$sql .= " on r.ID_ITEM = iv.ID_ITEM ";
							$sql .= " and r.ID_VND_ORDFM = iv.ID_VND_ORDFM ";
							$sql .= " WHERE r.id_po = '".$ID_PO."' ";
							$sql .= " and l.FLAG_TRACK_BIN = 0 ";
							$sql .= " ORDER BY ID_LINE_PO asc, r.DATE_RCV asc ";

							QueryDatabase($sql, $results);
							error_log($sql);
							if (mssql_num_rows($results) > 0) {
								$prevrowId = '';
								$b_flip = true;
								$ret .= "<h3>PO: ".$ID_PO."</h3>\n";
								$ret .= "<h3>".$NAME_VND_ORDFM."</h3>\n";
								$ret .= "<table class='sample'>\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th>Line</th>\n";
								$ret .= "		<th>Item:</th>\n";
								$ret .= "		<th>Descr:</th>\n";
								$ret .= "		<th>Qty Recv</th>\n";
								$ret .= "		<th>Date Recv</th>\n";
								$ret .= "		<th>Bin</th>\n";
								$ret .= "		<th>Qty on Tag</th>\n";
								$ret .= "		<th>Qty to Print</th>\n";
								$ret .= "		<th>Print</th>\n";
								$ret .= "	</tr>\n";
								while ($row = mssql_fetch_assoc($results)) {
									if ($prevrowId != $row['recv_rowid']) {
										$b_flip = !$b_flip;
									}
									if ($b_flip) {
										$trClass = 'd1s';
									} else {
										$trClass = 'd0s';
									}

									$ret .= "	<tr class='" . $trClass . "'>\n";
									$ret .= "			<input id='hid_NonBinTag_ID_PO__".$row['recv_rowid']."' type=hidden value='".$row['ID_PO']."'></input>\n";
									$ret .= "			<input id='hid_NonBinTag_ID_ITEM__".$row['recv_rowid']."' type=hidden value='".$row['ID_ITEM']."'></input>\n";
									
									$ret .= "			<input id='hid_NonBinTag_STK_UOM__".$row['recv_rowid']."' type=hidden value='".$row['CODE_UM_STK']."'></input>\n";
									$ret .= "			<input id='hid_NonBinTag_PUR_UOM__".$row['recv_rowid']."' type=hidden value='".$row['CODE_UM_PUR']."'></input>\n";
									$ret .= "			<input id='hid_NonBinTag_RATIO_STK_PUR__".$row['recv_rowid']."' type=hidden value='".$row['RATIO_STK_PUR']."'></input>\n";
									
									$ret .= "			<input id='hid_NonBinTag_ID_ITEM_VND__".$row['recv_rowid']."' type=hidden value='".$row['ID_ITEM_VND']."'></input>\n";
									$ret .= "			<input id='hid_NonBinTag_NAME_VND_ORDFM__".$row['recv_rowid']."' type=hidden value='".$row['NAME_VND_ORDFM']."'></input>\n";
									$ret .= "			<input id='hid_NonBinTag_DATE_RCV__".$row['recv_rowid']."' type=hidden value='".$row['DATE_RCV']."'></input>\n";
									$ret .= "			<input id='hid_NonBinTag_ITEM_DESCR__".$row['recv_rowid']."' type=hidden value='".$row['ITEM_DESCR']."'></input>\n";


									$ret .= "		<td style='text-align: center;'>".$row['ID_LINE_PO']."</td>\n";
									$ret .= "		<td style='text-align: center;'>".$row['ID_ITEM']."</td>\n";
									$ret .= "		<td style='text-align: center;'>".$row['ITEM_DESCR']."</td>\n";
									$ret .= "		<td style='text-align: center;'>".$row['QTY_RCV']." ".$row['CODE_UM_PUR']."</td>\n";
									$ret .= "		<td style='text-align: center;'>".$row['DATE_RCV']."</td>\n";
									$ret .= "		<td style='text-align: center;'>";
									$ret .= "			<input id='tb_NonBinTag_BIN_PRIM__".$row['recv_rowid']."' type=text size=15 maxlength=20 value='".$row['BIN_PRIM']."'></input>\n";
									$ret .= "		</td>\n";
									$ret .= "		<td style='text-align: center;'>";
									$ret .= "			<input id='tb_NonBinTag_QTY_ON_TAG__".$row['recv_rowid']."' type=text size=5 maxlength=5 value='".$row['QTY_RCV']."'></input>\n";


									$ret .= "			<select id='sel_NonBinTag_UOM__".$row['recv_rowid']."' onChange=\"changeNonBinTagUOM('".$row['recv_rowid']."')\">\n";
									$ret .= "				<option value='".$row['CODE_UM_PUR']."'>".$row['CODE_UM_PUR']."</option>\n";
									$ret .= "				<option value='".$row['CODE_UM_STK']."'>".$row['CODE_UM_STK']."</option>\n";
									$ret .= "			</select>\n";


									$ret .= "		</td>\n";
									$ret .= "		<td style='text-align: center;'>";
									$ret .= "			<input id='tb_NonBinTag_QTY_TO_PRINT__".$row['recv_rowid']."' type=text size=5 maxlength=5 value='1'></input>\n";
									$ret .= "		</td>\n";
									$ret .= "		<td>\n";
									$ret .= "			<input id='button_NonBinTag_PRINT__".$row['recv_rowid']."' type=button value='Print' onClick=\"printNonBinTags(".$row['recv_rowid'].")\"></input>\n";
									$ret .= "		</td>\n";									
									$ret .= "	</tr>\n";
								}
								$ret .= "</table>\n";
							}
						}
					break;

					case "printNonBinTags":
						if (isset($_POST["id_po"]) && isset($_POST["id_item"]) && isset($_POST["id_item_vnd"]) && isset($_POST["uom"]) && isset($_POST["name_vnd_ordfm"]) && isset($_POST["date_rcv"]) && isset($_POST["item_descr"]) && isset($_POST["bin_prim"]) && isset($_POST["qty_on_tag"]) && isset($_POST["qty_to_print"]) && isset($_POST["printer"])) {

							$ID_PO = stripIllegalChars4(trim($_POST["id_po"]));
							$ID_ITEM = stripIllegalChars4(trim($_POST["id_item"]));
							$ID_ITEM_VND = stripIllegalChars4(trim($_POST["id_item_vnd"]));
							$UOM = stripIllegalChars4(trim($_POST["uom"]));
							$NAME_VND_ORDFM = stripIllegalChars4(trim($_POST["name_vnd_ordfm"]));
							$DATE_RCV = stripIllegalChars4(trim($_POST["date_rcv"]));
							$ITEM_DESCR = stripIllegalChars4(trim($_POST["item_descr"]));
							$BIN_PRIM = stripIllegalChars4(trim($_POST["bin_prim"]));
							$QTY_ON_TAG = stripNonANChars(trim($_POST["qty_on_tag"]));
							$QTY_TO_PRINT = stripNonANChars(trim($_POST["qty_to_print"]));
							$PRINTER = $_POST["printer"];
							$TIMESTAMP = microtime();

							$Line1 = '%BTW% /AF="\\\\Fs1\netshare\Bartender_Formats\NON-BINTAG.btw" /PRN="'.$PRINTER.'" /R=3 /P /D="%Trigger File Name%" /DD' . PHP_EOL;
							$Line2 = '%END%' . PHP_EOL;

							$hdrLine  = 'Lbl_Qty	';
							$hdrLine .= 'ID_ITEM	';
							$hdrLine .= 'QTY_IN_BOX	';
							$hdrLine .= 'UOM	';
							$hdrLine .= 'BIN_PRIM	';
							$hdrLine .= 'ID_PO	';
							$hdrLine .= 'ID_ITEM_VND	';
							$hdrLine .= 'NAME_VND_ORDFM	';
							$hdrLine .= 'DATE_RCV	';
							$hdrLine .= 'DESCR_1	'. PHP_EOL;

							$valLine  = $QTY_TO_PRINT.'	';
							$valLine .= $ID_ITEM.'	';
							$valLine .=	$QTY_ON_TAG.'	';
							$valLine .=	$UOM.'	';
							$valLine .=	$BIN_PRIM.'	';
							$valLine .=	$ID_PO.'	';
							$valLine .=	$ID_ITEM_VND.'	';
							$valLine .=	$NAME_VND_ORDFM.'	';
							$valLine .=	$DATE_RCV.'	';
							$valLine .=	$ITEM_DESCR.'	'. PHP_EOL;

							$FileContents = $Line1 . $Line2 . $hdrLine . $valLine;

							$tmpOutputFile = "/tmp/NON-BINTAG_LBL_".$TIMESTAMP.".dat";
							$finalOutputFile = "../TCM_Labels/NON-BINTAG_LBL_".$TIMESTAMP.".dat";

							$myfile = fopen($tmpOutputFile, "w") or die("Unable to open file!");
							fwrite($myfile, $FileContents);
							fclose($myfile);
							rename($tmpOutputFile,$finalOutputFile);
						}
					break;


					case "getIntransitLabelList":
						if (isset($_POST["id_intransit"])) {
							$ID_INTRANSIT = stripIllegalChars4(trim($_POST["id_intransit"]));

							$sql = "SET ANSI_NULLS ON";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS ON";
							QueryDatabase($sql, $results);

							$sql  = " SELECT ";
							$sql .= " ih.id_intransit, ";
							$sql .= " ih.rev_intransit, ";
							$sql .= " ih.id_loc_from, ";
							$sql .= " tl.descr as shipfrom_descr, ";
							$sql .= " ih.id_loc_to, ";
							$sql .= " tl2.descr as shipto_descr, ";
							$sql .= " tl.descr, ";
							$sql .= " ih.id_user_add, ";
							$sql .= " ih.intransit_status, ";
							$sql .= " ih.date_due, ";
							$sql .= " ih.note_1, ";
							$sql .= " ih.note_2 ";
							$sql .= " FROM nsa.intransit_hdr ih ";
							$sql .= " LEFT JOIN nsa.tables_loc tl ";
							$sql .= " on ih.id_loc_from = tl.id_loc ";
							$sql .= " LEFT JOIN nsa.tables_loc tl2 ";
							$sql .= " on ih.id_loc_to = tl2.id_loc ";
							$sql .= " WHERE ";
							$sql .= " ih.ID_INTRANSIT = ".$ID_INTRANSIT." ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$from = $row['id_loc_from']."-".$row['shipfrom_descr'];
								$to = $row['id_loc_to']."-".$row['shipto_descr'];
							}

							$sql  = " SELECT ";
							$sql .= "  concat(id.id_intransit,'__',ih.REV_INTRANSIT,'__',id.ID_ITEM) as concat_intr_rev_item, ";
							$sql .= "  ih.ID_INTRANSIT, ";
							$sql .= "  ih.rev_intransit, ";
							$sql .= "  ih.rev_intransit, ";
							$sql .= "  ih.id_loc_from, ";
							$sql .= "  tl.descr as shipfrom_descr, ";
							$sql .= "  ih.id_loc_to, ";
							$sql .= "  tl2.descr as shipto_descr, ";
							$sql .= "  tl.descr, ";
							$sql .= "  ih.id_user_add, ";
							$sql .= "  ih.intransit_status, ";
							$sql .= "  ih.date_due, ";
							$sql .= "  ih.note_1, ";
							$sql .= "  ih.note_2, ";
							$sql .= "  id.ID_ITEM, ";
							$sql .= "  concat(id.descr_1,id.descr_2) as ITEM_DESCR, ";
							$sql .= "  id.QTY_REQ, ";
							$sql .= "  id.date_rcvd, ";
							$sql .= "  id.CODE_UM_STK, ";
							$sql .= "  id.CODE_UM_PUR, ";
							$sql .= "  b.RATIO_STK_PUR, ";
							$sql .= "  il.qty_onhd, ";
							$sql .= "  il.qty_alloc, ";
							$sql .= "  il.qty_onord, ";
							$sql .= "  id.qty_picked, ";
							$sql .= "  id.QTY_RCVD, ";
							$sql .= "  ib.KEY_BIN_1_TO ";
							$sql .= " FROM nsa.intransit_dtl id ";
							$sql .= " LEFT JOIN nsa.intransit_hdr ih ";
							$sql .= "  on id.id_intransit = ih.id_intransit ";
							$sql .= "  and id.rev_intransit = ih.rev_intransit ";
							$sql .= " LEFT JOIN nsa.itmmas_loc il ";
							$sql .= "  on id.id_item = il.id_item ";
							$sql .= "  and ih.id_loc_from = il.id_loc ";
							$sql .= " LEFT JOIN nsa.tables_loc tl ";
							$sql .= "  on ih.id_loc_from = tl.id_loc ";
							$sql .= " LEFT JOIN nsa.tables_loc tl2 ";
							$sql .= "  on ih.id_loc_to = tl2.id_loc ";
							$sql .= " LEFT JOIN nsa.intransit_bin ib ";
							$sql .= "  on id.id_intransit = ib.id_intransit ";
							$sql .= "  and id.REV_INTRANSIT = ib.REV_INTRANSIT ";
							$sql .= "  and id.id_item = ib.ID_ITEM ";
							$sql .= " LEFT JOIN nsa.ITMMAS_BASE b ";
							$sql .= "  on id.id_item = b.id_item ";
							$sql .= " WHERE ";
							$sql .= "  id.id_item is not null ";
							$sql .= "  and id.ID_INTRANSIT = ".$ID_INTRANSIT." ";
							$sql .= " ORDER BY ";
							$sql .= "  ih.id_loc_from, ";
							$sql .= "  ih.date_due, ";
							$sql .= "  ih.id_intransit ";
							QueryDatabase($sql, $results);
							if (mssql_num_rows($results) > 0) {
								$prevrowId = '';
								$b_flip = true;
								$ret .= "<h3>Intransit No.: ".$ID_INTRANSIT."</h3>\n";
								$ret .= "<h5>From: ".$from." To: ".$to."</h5>\n";
								$ret .= "<table class='sample'>\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th>Item:</th>\n";
								$ret .= "		<th>Descr:</th>\n";
								$ret .= "		<th>Qty Requested</th>\n";
								$ret .= "		<th>Qty Received</th>\n";
								$ret .= "		<th>Bin</th>\n";
								$ret .= "		<th>Qty on Tag</th>\n";
								$ret .= "		<th>Qty to Print</th>\n";
								$ret .= "		<th>Print</th>\n";
								$ret .= "	</tr>\n";
								while ($row = mssql_fetch_assoc($results)) {
									if ($prevrowId != $row['concat_intr_rev_item']) {
										$b_flip = !$b_flip;
									}
									if ($b_flip) {
										$trClass = 'd1s';
									} else {
										$trClass = 'd0s';
									}

									$ret .= "	<tr class='" . $trClass . "'>\n";
									$ret .= "			<input id='hid_intransit_ID_INTRANSIT__".$row['concat_intr_rev_item']."' type=hidden value='".$row['ID_INTRANSIT']."'></input>\n";
									$ret .= "			<input id='hid_intransit_ID_ITEM__".$row['concat_intr_rev_item']."' type=hidden value='".$row['ID_ITEM']."'></input>\n";
									$ret .= "			<input id='hid_intransit_STK_UOM__".$row['concat_intr_rev_item']."' type=hidden value='".$row['CODE_UM_STK']."'></input>\n";
									$ret .= "			<input id='hid_intransit_PUR_UOM__".$row['concat_intr_rev_item']."' type=hidden value='".$row['CODE_UM_PUR']."'></input>\n";
									$ret .= "			<input id='hid_intransit_RATIO_STK_PUR__".$row['concat_intr_rev_item']."' type=hidden value='".$row['RATIO_STK_PUR']."'></input>\n";
									$ret .= "			<input id='hid_intransit_ITEM_DESCR__".$row['concat_intr_rev_item']."' type=hidden value='".$row['ITEM_DESCR']."'></input>\n";


									$ret .= "		<td style='text-align: center;'>".$row['ID_ITEM']."</td>\n";
									$ret .= "		<td style='text-align: center;'>".$row['ITEM_DESCR']."</td>\n";
									$ret .= "		<td style='text-align: center;'>".$row['QTY_REQ']." ".$row['CODE_UM_STK']."</td>\n";
									$ret .= "		<td style='text-align: center;'>".$row['QTY_RCVD']."</td>\n";
									$ret .= "		<td style='text-align: center;'>";
									$ret .= "			<input id='tb_intransit_BIN_PRIM__".$row['concat_intr_rev_item']."' type=text size=15 maxlength=20 value='".$row['KEY_BIN_1_TO']."'></input>\n";
									$ret .= "		</td>\n";
									
									$ret .= "		<td style='text-align: center;'>";
									$ret .= "			<input id='tb_intransit_QTY_ON_TAG__".$row['concat_intr_rev_item']."' type=text size=5 maxlength=5 value='".$row['QTY_RCVD']."'></input>\n";

									$ret .= "			<select id='sel_intransit_UOM__".$row['concat_intr_rev_item']."' onChange=\"changeIntransitUOM('".$row['concat_intr_rev_item']."')\">\n";
									$ret .= "				<option value='".$row['CODE_UM_STK']."'>".$row['CODE_UM_STK']."</option>\n";
									$ret .= "				<option value='".$row['CODE_UM_PUR']."'>".$row['CODE_UM_PUR']."</option>\n";
									$ret .= "			</select>\n";
									$ret .= "		</td>\n";

									$ret .= "		<td style='text-align: center;'>";
									$ret .= "			<input id='tb_intransit_QTY_TO_PRINT__".$row['concat_intr_rev_item']."' type=text size=5 maxlength=5 value='1'></input>\n";
									$ret .= "		</td>\n";
									$ret .= "		<td>\n";
									$ret .= "			<input id='button_intransit_PRINT__".$row['concat_intr_rev_item']."' type=button value='Print' onClick=\"printIntransitTags('".$row['concat_intr_rev_item']."')\"></input>\n";
									$ret .= "		</td>\n";									
									$ret .= "	</tr>\n";


								}
								$ret .= "</table>\n";
							}

							$sql = "SET ANSI_NULLS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS OFF";
							QueryDatabase($sql, $results);
						}
					break;

					case "printIntransitTags":

						if (isset($_POST["id_intransit"]) && isset($_POST["id_item"]) && isset($_POST["uom"]) && isset($_POST["item_descr"]) && isset($_POST["bin_prim"]) && isset($_POST["qty_on_tag"]) && isset($_POST["qty_to_print"]) && isset($_POST["printer"])) {

							$ID_INTRANSIT = stripIllegalChars4(trim($_POST["id_intransit"]));
							$ID_ITEM = stripIllegalChars4(trim($_POST["id_item"]));
							$UOM = stripIllegalChars4(trim($_POST["uom"]));
							$ITEM_DESCR = stripIllegalChars4(trim($_POST["item_descr"]));
							$BIN_PRIM = stripIllegalChars4(trim($_POST["bin_prim"]));
							$QTY_ON_TAG = stripNonANChars(trim($_POST["qty_on_tag"]));
							$QTY_TO_PRINT = stripNonANChars(trim($_POST["qty_to_print"]));
							$PRINTER = $_POST["printer"];
							$TIMESTAMP = microtime();

							$Line1 = '%BTW% /AF="\\\\Fs1\netshare\Bartender_Formats\NON-BINTAG.btw" /PRN="'.$PRINTER.'" /R=3 /P /D="%Trigger File Name%" /DD' . PHP_EOL;
							$Line2 = '%END%' . PHP_EOL;

							$hdrLine  = 'Lbl_Qty	';
							$hdrLine .= 'ID_ITEM	';
							$hdrLine .= 'QTY_IN_BOX	';
							$hdrLine .= 'UOM	';
							$hdrLine .= 'BIN_PRIM	';
							$hdrLine .= 'ID_PO	';
							$hdrLine .= 'ID_ITEM_VND	';
							$hdrLine .= 'NAME_VND_ORDFM	';
							$hdrLine .= 'DATE_RCV	';
							$hdrLine .= 'DESCR_1	'. PHP_EOL;

							$valLine  = $QTY_TO_PRINT.'	';
							$valLine .= $ID_ITEM.'	';
							$valLine .=	$QTY_ON_TAG.'	';
							$valLine .=	$UOM.'	';
							$valLine .=	$BIN_PRIM.'	';
							$valLine .=	$ID_PO.'	';
							$valLine .=	$ID_ITEM_VND.'	';
							$valLine .=	$NAME_VND_ORDFM.'	';
							$valLine .=	$DATE_RCV.'	';
							$valLine .=	$ITEM_DESCR.'	'. PHP_EOL;

							$FileContents = $Line1 . $Line2 . $hdrLine . $valLine;

							$tmpOutputFile = "/tmp/NON-BINTAG_LBL_".$TIMESTAMP.".dat";
							$finalOutputFile = "../TCM_Labels/NON-BINTAG_LBL_".$TIMESTAMP.".dat";

							$myfile = fopen($tmpOutputFile, "w") or die("Unable to open file!");
							fwrite($myfile, $FileContents);
							fclose($myfile);
							rename($tmpOutputFile,$finalOutputFile);
						}
					break;





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


