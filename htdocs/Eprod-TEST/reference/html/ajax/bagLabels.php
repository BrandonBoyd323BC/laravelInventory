<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");
	require_once('../protected/classes/tc_calendar.php');
	require_once("../protected/mpdf60/mpdf.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print("		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				
				switch ($action) {
					case "selModeChange":
						if (isset($_POST["selMode"])) {
							$mode = $_POST["selMode"];

							if ($mode == "pickTicket") {
								//$ret .= " <form id='Bag_Label_Form' method='post' enctype='multipart/form-data'>";
								$ret .= " <table>";
								$ret .= " 	<tr>";
								$ret .= " 		<th colspan=2>Scan Pick Ticket: </th>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_id_ord'>";
								$ret .= " 		<td>Customer Order: </td>";
								$ret .= " 		<td>";
								$ret .= "			<input id='id_ord' type=text onkeyup=\"idOrdEntered(event)\" maxlength=6 size=8 autofocus>";
								$ret .= "		</td>";
								$ret .= " 	</tr>";
								$ret .= " </table>";

								$ret .= " <table id='table_ret_form'>";
								/*
								$ret .= "	<tr id='tr_line_item'>";
								$ret .= " 		<td>Line Item: </td>";
								$ret .= " 		<td id='td_id_item'>";


								$ret .= "			<select name='selIdItem' id='selIdItem' onChange=\"selIdItemChange()\">";
								//$ret .= "					<option value='--SELECT--'>--SELECT--</option>";
								$ret .= "			</select>";
								$ret .= "		</td>";
								$ret .= " 	</tr>";
								*/
								//$ret .= "	<tr id='tr_qty2'>";
								//$ret .= " 		<td>Qty in Bag: </td>";
								//$ret .= " 		<td id='td_qty_in_bag'>";
								//$ret .= "			<input id='qty_in_bag' type=text maxlength=7 size=8>";
								//$ret .= "		</td>";
								//$ret .= " 	</tr>";
								//$ret .= " 	<tr>";
								//$ret .= " 		<td></td>";
								//$ret .= " 		<td><INPUT id='submit' type='button' value='Get Labels' onClick=\"getBagLabels()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>";
								//$ret .= " 	</tr>";
								$ret .= " </table>";
								//$ret .= " </form>";								
							}

							if ($mode == "shopOrder") {
								//$ret .= " <form id='Bag_Label_Form' method='post' enctype='multipart/form-data'>";
								$ret .= " <table>";
								$ret .= " 	<tr>";
								$ret .= " 		<th colspan=2>Scan Shop Order: </th>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_id_ord'>";
								$ret .= " 		<td>Shop Order: </td>";
								$ret .= " 		<td>";
								//$ret .= "			<input id='id_ord' type=text onkeyup=\"idOrdEntered(event)\" maxlength=6 size=8 autofocus>";

								$ret .= "			<input id='so' type=text onkeyup=\"nextOnDash('so','sufx')\" maxlength=9 size=10 autofocus> -\n";
								$ret .= "			<input id='sufx' type=text onkeyup=\"sufxEntered()\" maxlength=3 size=4>\n";

								$ret .= "		</td>";
								$ret .= " 	</tr>";
								$ret .= " </table>";

								$ret .= " <table id='table_ret_form'>";
								/*
								$ret .= "	<tr id='tr_line_item'>";
								$ret .= " 		<td>Line Item: </td>";
								$ret .= " 		<td id='td_id_item'>";


								$ret .= "			<select name='selIdItem' id='selIdItem' onChange=\"selIdItemChange()\">";
								//$ret .= "					<option value='--SELECT--'>--SELECT--</option>";
								$ret .= "			</select>";
								$ret .= "		</td>";
								$ret .= " 	</tr>";
								*/
								//$ret .= "	<tr id='tr_qty2'>";
								//$ret .= " 		<td>Qty in Bag: </td>";
								//$ret .= " 		<td id='td_qty_in_bag'>";
								//$ret .= "			<input id='qty_in_bag' type=text maxlength=7 size=8>";
								//$ret .= "		</td>";
								//$ret .= " 	</tr>";
								//$ret .= " 	<tr>";
								//$ret .= " 		<td></td>";
								//$ret .= " 		<td><INPUT id='submit' type='button' value='Get Labels' onClick=\"getBagLabels()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>";
								//$ret .= " 	</tr>";
								$ret .= " </table>";
								//$ret .= " </form>";								
							}

							if ($mode == "manual") {
								//$ret .= " <form id='Bag_Label_Form' method='post' enctype='multipart/form-data'>";
								$ret .= " <table>";
								$ret .= " 	<tr>";
								$ret .= " 		<th colspan=2>Manual Entry: </th>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_so1'>";
								$ret .= " 		<td>Text to Barcode: </td>";
								$ret .= " 		<td>";
								$ret .= "				<input id='so' type=text size=30 autofocus>";
								$ret .= "		</td>";
								$ret .= " 	</tr>";
								//$ret .= " 	<tr>";
								//$ret .= " 		<td></td>";
								//$ret .= " 		<td><INPUT id='submit' type='button' value='Get Labels' onClick=\"getBagLabels()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>";
								//$ret .= " 	</tr>";
								$ret .= " </table>";
								//$ret .= " </form>";								
							}
						}
					break;


					case "getOrdLines":
						error_log("getOrdLines");
						if (isset($_POST["id_ord"])) {
							$id_ord = $_POST["id_ord"];
							$id_cust = "";
							$name_cust = "";
							$code_user_3_ar = "";
							
							$sql  = "SELECT oh.ID_CUST_SOLDTO, cs.NAME_CUST, cs.CODE_USER_3_AR ";
							$sql .= " from nsa.CP_ORDHDR oh ";
							$sql .= " left join nsa.CUSMAS_SOLDTO cs on oh.ID_CUST_SOLDTO = cs.ID_CUST ";
							$sql .= " WHERE oh.ID_ORD = '".$id_ord."'";
							QueryDatabase($sql, $results);							
							while ($row = mssql_fetch_assoc($results)) {
								$id_cust = $row["ID_CUST_SOLDTO"];
								$name_cust = $row["NAME_CUST"];
								$code_user_3_ar = $row["CODE_USER_3_AR"];
							}

							$sql  = "SELECT ol.SEQ_LINE_ORD, ol.ID_ITEM, ol.ID_ITEM_CUST, ol.QTY_OPEN ";
							$sql .= " from nsa.CP_ORDLIN ol ";
							$sql .= " WHERE ol.ID_ORD = '".$id_ord."'";
							$sql .= " ORDER BY ol.SEQ_LINE_ORD asc ";
							QueryDatabase($sql, $results);

							if (mssql_num_rows($results) > 0) {
								//$ret .= " <table id='table_ret_form'>";

								$ret .= "	<tr id='tr_cust'>";						
								$ret .= " 		<input id='hid_id_ord' type='hidden' value='".$id_ord."'></input>";
								$ret .= " 		<input id='hid_id_cust' type='hidden' value='".$id_cust."'></input>";
								$ret .= "		<input id='hid_bc_entered' type='hidden' value=''></input>";
								$ret .= " 		<td>Customer: </td>";
								$ret .= " 		<td>".$name_cust."</td>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_line_item'>";
								$ret .= " 		<td>Line Item: </td>";
								$ret .= " 		<td id='td_id_item'>";
								$ret .= "			<select name='selIdItem' id='selIdItem' onkeypress=\"scanLineItem(event)\">";
								$ret .= "					<option value=''>--SELECT--</option>";
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= "					<option value='".str_pad($row["SEQ_LINE_ORD"],4,'0',STR_PAD_LEFT)."'>".$row["ID_ITEM"]." (".$row["ID_ITEM_CUST"].")</option>";
								}
								$ret .= "			</select>";
								$ret .= " 		</td>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_qty_per_bag'>";
								$ret .= " 		<td>Qty per Bag: </td>";
								$ret .= " 		<td>";
								$ret .= "			<input id='txt_qty_per_bag' type=text value='1' maxlength=4 size=1>";
								$ret .= " 		</td>";
								$ret .= " 	</tr>";								
								$ret .= " 	<tr>";
								$ret .= " 		<td></td>";
								$ret .= " 		<td><INPUT id='buttonGetLabels' type='button' value='Get Labels' onClick=\"getPTBagLabels()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>";
								$ret .= " 	</tr>";
							} else {
								$ret .= "NO LINES FOUND";
							}
						}
					break;

					case "getPTBagLabels":
						error_log("getPTBagLabels");
						if (isset($_POST["id_ord"]) && isset($_POST["seq_line_ord"]) && isset($_POST["qty_per_bag"])) {
							$id_ord = $_POST["id_ord"];
							$seq_line_ord = $_POST["seq_line_ord"];
							$qty_per_bag = $_POST["qty_per_bag"];

							error_log("id_ord: ".$id_ord);
							error_log("seq_line_ord: ".$seq_line_ord);
							error_log("qty_per_bag: ".$qty_per_bag);

							$sql  = "SELECT ol.SEQ_LINE_ORD, ol.ID_ITEM, ol.ID_ITEM_CUST, ol.QTY_OPEN ";
							$sql .= " from nsa.CP_ORDLIN ol ";
							$sql .= " WHERE ol.ID_ORD = '".$id_ord."' and ol.SEQ_LINE_ORD = '".$seq_line_ord."'";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$id_item = $row["ID_ITEM"];
								$id_item_cust = $row["ID_ITEM_CUST"];
								error_log("id_item_cust: ".$id_item_cust);

								$itemLength = strlen($id_item_cust);
								$itemFontSize = getFontSize($itemLength);
								$barcodeData = $qty_per_bag . " " . $id_item_cust;
								$barcodeTextLabel = "(".$qty_per_bag.") ".$id_item_cust;

								$html  = "";
								$html .= "<html>";
								$html .= "	<head>";
								$html .= "		<style>";
								$html .= "			body {";
								$html .= "				line-height: 30px;";
								$html .= "				font-family: 'Lucinda Console', Monaco, monospace;";
								//$html .= "				vertical-align: top;";
								$html .= "				vertical-align: top;";
								$html .= "				text-align: center;";
								$html .= "				background: transparent;";
								$html .= "				padding: 0px;";
								$html .= "				margin: 0px;";
								$html .= "				border: 0px;";
								$html .= "			}";
								$html .= "			div.item {";
								$html .= "				background: transparent;";
								$html .= "				vertical-align: top;";
								$html .= "				margin: auto;";
								$html .= "				width: 98%;";
								$html .= "				height: 48%;";
								$html .= "				font-weight: bold;";
								$html .= "				font-size: ".$itemFontSize."vw;";
								$html .= "			}";
								$html .= "			div.spacer {";
								$html .= "				margin: auto;";
								$html .= "				width: 98%;";
								$html .= "				height: 1%;";
								$html .= "				background: transparent;";
								$html .= "			}";
								$html .= "			.barcode {";
								$html .= "				padding: 1.5mm;";
								$html .= "				margin: 0;";
								$html .= "				vertical-align: top;";
								$html .= "				color: #000000;";
								$html .= "			}";
								$html .= "			.barcodecell {";
								$html .= "				text-align: center;";
								$html .= "				vertical-align: middle;";
								$html .= "				padding: 0;";
								$html .= "			}";
								$html .= "		</style>";
								$html .= "	</head>";
								$html .= "	<body>";
								/*
								$html .= "		<table>";
								$html .= " 			<tr>";
								$html .= "				<td>".$barcodeTextLabel."</td>";
								$html .= " 			</tr>";
								$html .= " 			<tr>";
								$html .= "				<td class='barcodecell'><barcode code='".$barcodeData."' type='C39' text=1 class='barcode' /></td>";
								$html .= " 			</tr>";
								$html .= " 		</table>";
								*/
								$html .= "		<div class='item'>".$barcodeTextLabel."<br/><barcode code='".$barcodeData."' type='C39' text=1 class='barcode' /></div>";
								//$html .= "		<div class='item'><barcode code='".$barcodeData."' type='C39' text=1 class='barcode' /></div>";
								//$html .= "		<table>";
								//$html .= " 			<tr>";
								//$html .= "				<td class='barcodecell'><barcode code='".$barcodeData."' type='C39' text=1 class='barcode' /></td>";
								//$html .= " 			</tr>";
								//$html .= " 		</table>";

								//$html .= "		<div class='static'>".date("m/d/Y")."</div>";
								//$html .= "		<div class='spacer'></div>";
								//$html .= "		<div class='bin'>".$row['BIN_PRIM']."</div>";
								$html .= "	</body>";
								$html .= "</html>";

								$labelOutputFile = "/tmp/bagLabel/" . $id_item ."___".$id_item_cust."___item.pdf";
								error_log("labelOutputFile: ".$labelOutputFile);
								//$mpdf=new mPDF('','A4-L','','',10,10,120,10,10,10);
								//$mpdf=new mPDF('utf-8',array(100,53),0,0,0,0,2,0);
								$mpdf=new mPDF('utf-8',array(66,25),0,0,0,0,2,0);
								$mpdf->WriteHTML($html);
								$mpdf->Output($labelOutputFile,'F'); 
								$labelOutputFile = str_replace('/tmp','',$labelOutputFile);

								$ret .= "<table>\n";
								$ret .= " <tr>\n";
								$ret .= "  <td class='icon'><a href='".$labelOutputFile."' target='_blank'><img class='icon' src='/images/labels.png' href='".$labelOutputFile."' target='_blank'></br>Item Label<br>".$id_item_cust."</a></td>\n";
								$ret .= " </tr>\n";
								$ret .= "</table>\n";
							}
						}
					break;
				}
			}


/*
			if (isset($_POST["getBoxQty"]) && isset($_POST["so"]) && isset($_POST["sufx"])) {
				$SO	= stripNonANChars(trim($_POST["so"]));
				$SUFX = stripNonANChars(trim($_POST["sufx"]));

				$sql =  "select ";
				$sql .= " sh.ID_SO, ";
				$sql .= " sh.QTY_CMPL, ";
				$sql .= " sh.QTY_ORD, ";
				$sql .= " sh.ID_ITEM_PAR, ";
				$sql .= " il.BIN_PRIM, ";
				$sql .= " il.FLAG_STK, ";
				$sql .= " id.DESCR_ADDL ";
				$sql .= " from nsa.SHPORD_HDR sh ";
				$sql .= " left join nsa.ITMMAS_LOC il ";
				$sql .= " on sh.ID_ITEM_PAR = il.ID_ITEM ";
				$sql .= " and il.ID_LOC = '10' ";
				$sql .= " left join nsa.ITMMAS_DESCR id ";
				$sql .= " on sh.ID_ITEM_PAR = id.ID_ITEM ";
				$sql .= " and id.DESCR_ADDL like '%BOX QTY%' ";
				$sql .= " where ltrim(ID_SO) = '" . $SO . "' ";
				$sql .= " and SUFX_SO = '" . $SUFX . "' ";
				QueryDatabase($sql, $results);

				if (mssql_num_rows($results) > 0) {
					while ($row = mssql_fetch_assoc($results)) {
						$DESCR_ADDL = trim($row['DESCR_ADDL']);
						$DESCR_ADDL = trim(str_replace("BOX QTY:","",$row['DESCR_ADDL']));
						$endPos = strpos($DESCR_ADDL, "BOX SIZE");
						$boxQty = trim(substr($DESCR_ADDL,0,$endPos));
						//"BOX QTY:30 BOX SIZE:18x14x20"

						$ret .=	"<input id='qty_in_box' type=text value='" . $boxQty . "' maxlength=7 size=8>\n";

					}
				}
			}


			if (isset($_POST["getBagLabels"]) && isset($_POST["so"]) && isset($_POST["sufx"]) && isset($_POST["qty_in_box"]) && isset($_POST["qty_in_bag"])) {
				$SO	= stripNonANChars(trim($_POST["so"]));
				$SUFX = stripNonANChars(trim($_POST["sufx"]));
				$QTY_IN_BOX = stripNonANChars(trim($_POST["qty_in_box"]));
				$QTY_IN_BAG = stripNonANChars(trim($_POST["qty_in_bag"]));

				$sql =  "select ";
				$sql .= " sh.ID_SO, ";
				$sql .= " sh.QTY_CMPL, ";
				$sql .= " sh.QTY_ORD, ";
				$sql .= " sh.ID_ITEM_PAR, ";
				$sql .= " il.BIN_PRIM, ";
				$sql .= " il.FLAG_STK ";
				$sql .= " from nsa.SHPORD_HDR sh ";
				$sql .= " left join nsa.ITMMAS_LOC il ";
				$sql .= " on sh.ID_ITEM_PAR = il.ID_ITEM ";
				$sql .= " and il.ID_LOC = '10' ";
				$sql .= " where ltrim(ID_SO) = '" . $SO . "' ";
				$sql .= " and SUFX_SO = '" . $SUFX . "' ";
				QueryDatabase($sql, $results);

				if (mssql_num_rows($results) > 0) {
					while ($row = mssql_fetch_assoc($results)) {
						$itemLength = strlen($row['ID_ITEM_PAR']);
						$binLength = strlen($row['BIN_PRIM']);
						$itemFontSize = getFontSize($itemLength);
						$binFontSize = getFontSize($binLength);

						if ($DEBUG) {
							error_log("itemLength: " . $itemLength . " itemFontSize: " . $itemFontSize);
							error_log("binLength: " . $binLength . " binFontSize: " . $binFontSize);
						}

						$html  = "";
						$html .= "<html>";
						$html .= "	<head>";
						$html .= "		<style>";
						$html .= "			body {";
						$html .= "				line-height: 80px;";
						$html .= "				font-family: 'Lucinda Console', Monaco, monospace;";
						$html .= "				vertical-align: top;";
						$html .= "				text-align: center;";
						$html .= "				background: transparent;";
						$html .= "				padding: 0px;";
						$html .= "				margin: 0px;";
						$html .= "				border: 0px;";
						$html .= "			}";
						$html .= "			div.item {";
						$html .= "				background: transparent;";
						$html .= "				vertical-align: middle;";
						$html .= "				margin: auto;";
						$html .= "				width: 98%;";
						$html .= "				height: 48%;";
						$html .= "				font-weight: bold;";
						$html .= "				font-size: ".$itemFontSize."vw;";
						$html .= "			}";
						$html .= "			div.bin {";
						$html .= "				background: transparent;";
						$html .= "				vertical-align: middle;";
						$html .= "				margin: auto;";
						$html .= "				width: 98%;";
						$html .= "				height: 48%;";
						$html .= "				font-weight: bold;";
						$html .= "				font-size: ".$binFontSize."vw;";
						$html .= "			}";
						$html .= "			div.static {";
						$html .= "				background: transparent;";
						$html .= "				vertical-align: middle;";
						$html .= "				margin: auto;";
						$html .= "				width: 98%;";
						$html .= "				height: 48%;";
						$html .= "				font-size: 46vw; ";
						$html .= "				font-weight: bold;";
						$html .= "			}";
						$html .= "			div.spacer {";
						$html .= "				margin: auto;";
						$html .= "				width: 98%;";
						$html .= "				height: 1%;";
						$html .= "				background: transparent;";
						$html .= "			}";						
						$html .= "		</style>";
						$html .= "	</head>";
						$html .= "	<body>";
						if ($QTY_IN_BAG <> "") {
							$html .= "		<div class='item'>".$row['ID_ITEM_PAR']."</div>";
							$html .= "		<div class='spacer'></div>";
							$html .= "		<div class='static'>QTY=".number_format($QTY_IN_BAG)."</div>";
							$html .= "		<pagebreak>";
						}
						if ($QTY_IN_BOX <> "") {
							$html .= "		<div class='item'>".$row['ID_ITEM_PAR']."</div>";
							$html .= "		<div class='spacer'></div>";
							$html .= "		<div class='static'>QTY=".number_format($QTY_IN_BOX)."</div>";
							$html .= "		<pagebreak>";
						}
						$html .= "		<div class='static'>".date("m/d/Y")."</div>";
						$html .= "		<div class='spacer'></div>";
						$html .= "		<div class='bin'>".$row['BIN_PRIM']."</div>";
						$html .= "	</body>";
						$html .= "</html>";

						$labelOutputFile = "/tmp/soLabel/" . $SO ."___item.pdf";
						//$mpdf=new mPDF('','A4-L','','',10,10,120,10,10,10);
						$mpdf=new mPDF('utf-8',array(100,53),0,0,0,0,2,0);
						$mpdf->WriteHTML($html);
						$mpdf->Output($labelOutputFile,'F'); 
						$labelOutputFile = str_replace('/tmp','',$labelOutputFile);

						$ret .= "<table>\n";
						$ret .= " <tr>\n";
						$ret .= "  <td class='icon'><a href='".$labelOutputFile."' target='_blank'><img class='icon' src='/images/labels.png' href='".$labelOutputFile."' target='_blank'></br>Item Label<br>".$row['ID_ITEM_PAR']."</a></td>\n";
						$ret .= " </tr>\n";
						$ret .= "</table>\n";
					}
				} else {
					$ret .= "					<font>NO MATCH</font>\n";
				}
			}


*/			
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function getFontSize($strLength) {
	$fontSize = 16;

/*
	if ($strLength >= 17) {
		$fontSize = 36;
	} elseif ($strLength == 16) {
		$fontSize = 37;
	} elseif ($strLength == 15) {
		$fontSize = 40;
	} elseif ($strLength == 14) {
		$fontSize = 43;
	} elseif ($strLength == 13) {
		$fontSize = 46;
	} elseif ($strLength == 12) {
		$fontSize = 50;
	} elseif ($strLength == 11) {
		$fontSize = 55;
	} elseif ($strLength == 10) {
		$fontSize = 60;
	} elseif ($strLength == 9) {
		$fontSize = 68;
	} elseif ($strLength == 8) {
		$fontSize = 75;
	} elseif ($strLength == 7) {
		$fontSize = 86;
	} elseif ($strLength <= 6) {
		$fontSize = 93;
	}
*/
	return $fontSize;
}

?>
