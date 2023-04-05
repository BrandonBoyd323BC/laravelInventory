<?php

	

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");
	require_once('../protected/classes/tc_calendar.php');
	require_once("../protected/mpdf60/mpdf.php");
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
				error_log("action:" . $action);		
				switch ($action) {
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
								$ret .= "			<input id='sufx' type=text onkeyup=\"sufxEntered()\" maxlength=3 size=4>\n";
								$ret .= "		</td>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_qty1'>\n";
								$ret .= " 		<td>Qty in Box: </td>\n";
								$ret .= " 		<td id='td_qty_in_box'>\n";
								$ret .= "			<input id='qty_in_box' type=text maxlength=7 size=8>\n";
								$ret .= "		</td>\n";
								$ret .= " 	</tr>\n";
								$ret .= " 	<tr>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td><INPUT id='submit' type='button' value='Get Labels' onClick=\"getSoLabels()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
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
								$ret .= "			<input id='id_item' type=text onkeyup=\"idItemChange()\" size=30 autofocus>";
								$ret .= "		</div></td>";
								$ret .= " 	</tr>";
								$ret .= "	<tr id='tr_qty1'>\n";
								$ret .= " 		<td>Qty in Box: </td>\n";
								$ret .= " 		<td id='td_qty_in_box'>\n";
								$ret .= "			<input id='qty_in_box' type=text maxlength=7 size=8>\n";
								$ret .= "		</td>\n";
								$ret .= " 	</tr>\n";								
								$ret .= " 	<tr>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td><INPUT id='submit' type='button' value='Get Labels' onClick=\"getItemLabels()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
								$ret .= " 	</tr>\n";
								$ret .= " </table>";
								$ret .= " <table id='table_ret_form'>";
								$ret .= " </table>";
							}
						}
					break;


					case "getBoxQty":
						if (isset($_POST["so"]) && isset($_POST["sufx"])) {
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
							error_log($sql);
							if (mssql_num_rows($results) > 0) {
								while ($row = mssql_fetch_assoc($results)) {

									$DESCR_ADDL = trim($row['DESCR_ADDL']);
									$DESCR_ADDL = trim(str_replace("BOX QTY:","",$row['DESCR_ADDL']));
									$endPos = strpos($DESCR_ADDL, "BOX SIZE");
									$boxQty = trim(substr($DESCR_ADDL,0,$endPos));
									error_log($DESCR_ADDL);
									//"BOX QTY:30 BOX SIZE:18x14x20"

									$ret .=	"<input id='qty_in_box' type=text value='" . $boxQty . "' maxlength=7 size=8>\n";
								}
							}
						}
					break;					


					case "getLabels":
						////////////
						//  BOX LABEL CSS
						////////////					
						$css_1  = "";
						$css_1 .= "<html>";
						$css_1 .= "	<head>";
						$css_1 .= "		<style>";
						$css_1 .= "			body {";
						if ($DEBUG) {						
							$css_1 .= "				background: yellow;";
						} else {
							$css_1 .= "				background: transparent;";
						}						
						$css_1 .= "				font-family: 'Lucinda Console', Monaco, monospace;";
						$css_1 .= "				vertical-align: top;";
						$css_1 .= "				horizontal-align: center;";
						$css_1 .= "				text-align: center;";
						$css_1 .= "				padding: 0px;";
						$css_1 .= "				margin: 0px;";
						$css_1 .= "				border: 0px;";
						$css_1 .= "			}";
						$css_1 .= "			div.item {";
						if ($DEBUG) {						
							$css_1 .= "				background: blue;";
						} else {
							$css_1 .= "				background: transparent;";
						}
						$css_1 .= "				vertical-align: top;";
						$css_1 .= "				horizontal-align: center;";
						$css_1 .= "				text-align: center;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 98%;";
						$css_1 .= "				height: 18%;";
						$css_1 .= "				font-weight: bold;";
						$css_1 .= "				font-size: 30vw;";
						$css_1 .= "				padding: 0px;";
						$css_1 .= "				border: 0px;";						
						$css_1 .= "			}";
						$css_1 .= "			div.bin {";
						if ($DEBUG) {						
							$css_1 .= "				background: orange;";
						} else {
							$css_1 .= "				background: transparent;";
						}
						$css_1 .= "				vertical-align: top;";
						$css_1 .= "				horizontal-align: center;";
						$css_1 .= "				text-align: center;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 98%;";
						$css_1 .= "				height: 40%;";
						$css_1 .= "				font-weight: bold;";
						$css_1 .= "				font-size: 50vw;";
						$css_1 .= "				padding: 0px;";
						$css_1 .= "				border: 0px;";						
						$css_1 .= "			}";						
						$css_1 .= "			div.barcode {";
						if ($DEBUG) {						
							$css_1 .= "				background: green;";
						} else {
							$css_1 .= "				background: transparent;";
						}
						$css_1 .= "				vertical-align: top;";
						$css_1 .= "				horizontal-align: center;";
						$css_1 .= "				text-align: center;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 98%;";
						$css_1 .= "				height: 18%;";
						$css_1 .= "				padding: 0px;";
						$css_1 .= "				border: 0px;";
						$css_1 .= "			}";	
						$css_1 .= "			div.qty {";
						if ($DEBUG) {						
							$css_1 .= "				background: red;";
						} else {
							$css_1 .= "				background: transparent;";
						}
						$css_1 .= "				vertical-align: top;";
						$css_1 .= "				horizontal-align: center;";
						$css_1 .= "				text-align: center;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 98%;";
						$css_1 .= "				height: 5%;";
						$css_1 .= "				font-weight: bold;";
						$css_1 .= "				font-size: 26vw; ";
						$css_1 .= "				padding: 0px;";
						$css_1 .= "				border: 0px;";						
						$css_1 .= "			}";	
						$css_1 .= "			div.date {";
						if ($DEBUG) {						
							$css_1 .= "				background: orange;";
						} else {
							$css_1 .= "				background: transparent;";
						}
						$css_1 .= "				vertical-align: top;";
						$css_1 .= "				horizontal-align: center;";
						$css_1 .= "				text-align: center;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 20%;";
						$css_1 .= "				height: 1%;";
						$css_1 .= "				text-align: right;";
						$css_1 .= "				font-size: 12vw; ";
						$css_1 .= "				padding: 0px;";
						$css_1 .= "				border: 0px;";						
						$css_1 .= "			}";

						$css_1 .= "			div.itemSmall {";
						if ($DEBUG) {						
							$css_1 .= "				background: orange;";
						} else {
							$css_1 .= "				background: transparent;";
						}
						$css_1 .= "				vertical-align: top;";
						$css_1 .= "				horizontal-align: center;";
						$css_1 .= "				text-align: center;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 98%;";
						$css_1 .= "				height: 1%;";
						$css_1 .= "				font-size: 12vw; ";
						$css_1 .= "				padding: 0px;";
						$css_1 .= "				border: 0px;";						
						$css_1 .= "			}";						
						$css_1 .= "			div.static {";
						$css_1 .= "				background: transparent;";
						$css_1 .= "				vertical-align: middle;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 98%;";
						$css_1 .= "				height: 18%;";
						$css_1 .= "				font-size: 46vw; ";
						$css_1 .= "				font-weight: bold;";
						$css_1 .= "			}";
						$css_1 .= "			div.spacer {";
						$css_1 .= "				background: transparent;";
						$css_1 .= "				margin: auto;";
						$css_1 .= "				width: 98%;";
						$css_1 .= "				height: 1%;";
						$css_1 .= "			}";	
						$css_1 .= "		</style>";
						$css_1 .= "	</head>";
						


						////////////
						//  BAG LABEL CSS
						////////////
						$css_2  = "";
						$css_2 .= "<html>";
						$css_2 .= "	<head>";
						$css_2 .= "		<style>";
						$css_2 .= "			body {";
						$css_2 .= "				font-family: 'Lucinda Console', Monaco, monospace;";
						$css_2 .= "				vertical-align: top;";
						$css_2 .= "				text-align: center;";
						$css_2 .= "				background: transparent;";
						$css_2 .= "				padding: 0px;";
						$css_2 .= "				margin: 0px;";
						$css_2 .= "				border: 0px;";
						$css_2 .= "			}";
						$css_2 .= "			div.barcode {";
						$css_2 .= "				background: transparent;";
						$css_2 .= "				vertical-align: bottom;";
						$css_2 .= "				width: 98%;";
						$css_2 .= "				height: 25%;";
						$css_2 .= "			}";
						$css_2 .= "			div.item {";
						$css_2 .= "				background: transparent;";
						$css_2 .= "				vertical-align: top;";
						$css_2 .= "				width: 96%;";
						$css_2 .= "				height: 10%;";
						$css_2 .= "				font-size: 10vw;";
						$css_2 .= "			}";
						$css_2 .= "			div.descr {";
						$css_2 .= "				background: transparent;";
						$css_2 .= "				vertical-align: middle;";
						$css_2 .= "				width: 96%;";
						$css_2 .= "				height: 12%;";
						$css_2 .= "				font-size: 10vw;";
						$css_2 .= "			}";
						$css_2 .= "			div.spacer {";
						$css_2 .= "				margin: auto;";
						$css_2 .= "				width: 98%;";
						$css_2 .= "				height: 5%;";
						$css_2 .= "				background: transparent;";
						$css_2 .= "			}";	
						$css_2 .= "		</style>";
						$css_2 .= "	</head>";


						////////////
						//  AUTOMATION LABEL CSS
						////////////
						$css_3  = "";
						$css_3 .= "<html>";
						$css_3 .= "	<head>";
						$css_3 .= "		<style>";
						$css_3 .= "			body {";
						$css_3 .= "				font-family: 'Lucinda Console', Monaco, monospace;";
						$css_3 .= "				vertical-align: top;";
						$css_3 .= "				text-align: center;";
						$css_3 .= "				background: transparent;";
						$css_3 .= "				padding: 0px;";
						$css_3 .= "				margin: 0px;";
						$css_3 .= "				border: 0px;";
						$css_3 .= "			}";
						$css_3 .= "			table.automation {";
						$css_3 .= "				text-align: center;";
						$css_3 .= "				border-collapse: collapse;";
						$css_3 .= "				border: 1px solid black;";
						$css_3 .= "			}";
						$css_3 .= "			td.automation {";
						$css_3 .= "				border: 1px solid black;";
						$css_3 .= "			}";
						$css_3 .= "			tr.automation {";
						$css_3 .= "				border: 1px solid black;";
						$css_3 .= "			}";
						$css_2 .= "			div.spacer {";
						$css_2 .= "				margin: auto;";
						$css_2 .= "				width: 98%;";
						$css_2 .= "				height: 5%;";
						$css_2 .= "				background: transparent;";
						$css_2 .= "			}";	
						$css_3 .= "		</style>";
						$css_3 .= "	</head>";

						////////////
						//  HANG TAG LABEL CSS
						////////////
						$css_4  = "";
						$css_4 .= "<html>";
						$css_4 .= "	<head>";
						$css_4 .= "		<style>";
						$css_4 .= "			body {";
						$css_4 .= "				font-family: 'Lucinda Console', Monaco, monospace;";
						$css_4 .= "				vertical-align: top;";
						$css_4 .= "				text-align: center;";
						if ($DEBUG) {						
							$css_4 .= "				background: yellow;";
						} else {
							$css_4 .= "				background: transparent;";
						}						
						$css_4 .= "				padding: 0px;";
						$css_4 .= "				margin: 0px;";
						$css_4 .= "				border: 0px;";
						$css_4 .= "			}";
						$css_4 .= "			div.barcode {";
						$css_4 .= "				background: transparent;";
						$css_4 .= "				vertical-align: bottom;";
						$css_4 .= "				width: 98%;";
						$css_4 .= "				height: 25%;";
						$css_4 .= "			}";
						$css_4 .= "			div.item {";
						$css_4 .= "				background: transparent;";
						$css_4 .= "				vertical-align: top;";
						$css_4 .= "				width: 96%;";
						$css_4 .= "				height: 10%;";
						$css_4 .= "				font-size: 8vw;";
						$css_4 .= "			}";
						$css_4 .= "			div.descr {";
						$css_4 .= "				background: transparent;";
						$css_4 .= "				vertical-align: middle;";
						$css_4 .= "				width: 96%;";
						$css_4 .= "				height: 12%;";
						$css_4 .= "				font-size: 8vw;";
						$css_4 .= "			}";
						$css_4 .= "			div.spacer {";
						$css_4 .= "				margin: auto;";
						$css_4 .= "				width: 98%;";
						$css_4 .= "				height: 5%;";
						$css_4 .= "				background: transparent;";
						$css_4 .= "			}";	
						$css_4 .= "		</style>";
						$css_4 .= "	</head>";						




						///////////
						// SHOP ORDER MODE
						///////////
						if (isset($_POST["so"]) && isset($_POST["sufx"]) && isset($_POST["qty_in_box"])) {
							$SO	= stripNonANChars(trim($_POST["so"]));
							$SUFX = stripNonANChars(trim($_POST["sufx"]));
							$QTY_IN_BOX = stripNonANChars(trim($_POST["qty_in_box"]));

							$sql =  "select ";
							$sql .= " sh.ID_SO, ";
							$sql .= " sh.QTY_CMPL, ";
							$sql .= " sh.QTY_ORD, ";
							$sql .= " sh.ID_ITEM_PAR as ID_ITEM, ";
							$sql .= " sh.FLAG_STK as SH_FLAG_STK, ";
							$sql .= " sh.REF_ORD, ";
							$sql .= " il.BIN_PRIM, ";
							$sql .= " il.FLAG_STK, ";
							$sql .= " il.QTY_ALLOC, ";
							$sql .= " il.QTY_ONHD, ";
							$sql .= " ib.DESCR_1, ";
							$sql .= " ib.DESCR_2 ";
							$sql .= " from nsa.SHPORD_HDR sh ";
							$sql .= " left join nsa.ITMMAS_LOC il ";
							$sql .= " on sh.ID_ITEM_PAR = il.ID_ITEM ";
							$sql .= " and il.ID_LOC = '10' ";
							$sql .= " left join nsa.ITMMAS_BASE ib ";
							$sql .= " on il.ID_ITEM = ib.ID_ITEM ";
							$sql .= " where ltrim(ID_SO) = '" . $SO . "' ";
							$sql .= " and SUFX_SO = '" . $SUFX . "' ";
							QueryDatabase($sql, $results);

							if (mssql_num_rows($results) > 0) {
								while ($row = mssql_fetch_assoc($results)) {

									if (substr($row['ID_ITEM'],-1) == '#') {
										$binItem = str_replace("#","",$row['ID_ITEM']);
									} else {
										$binItem = $row['ID_ITEM'];
									}

									$html = $css_1;
									$html .= "	<body>";
									$html .= "		<div class='barcode'><barcode code='".$binItem."' type='C93' class='barcode'></div>";
									$html .= "		<div class='item'>".$binItem."</div><br>";
									if ($QTY_IN_BOX <> "") {
										$html .= "		<div class='qty'>QTY=".number_format($QTY_IN_BOX)."<barcode code='".$QTY_IN_BOX."' type='C93' class='barcode'></div>";
									}
									$html .= "		<div class='date'>".date("m/d/Y")."</div>";
									
									if ($row['SH_FLAG_STK'] == 'N') {									
										$trim_REF_ORD = ltrim($row['REF_ORD'],'0');
										$ordLineNum = str_replace($trim_REF_ORD,'',trim($row['ID_SO']));
										$lotNum = $row['REF_ORD'] . str_pad($ordLineNum,4,"0",STR_PAD_LEFT);

										$html .= "      <pagebreak>";
										$html .= "		<div class='barcode'><barcode code='".$lotNum."' type='C93' class='barcode'></div>";
										$html .= "		<div class='item'>Lot: ".$lotNum."</div><br>";
										$html .= "		<div class='date'>".date("m/d/Y")."</div>";
									}
									

									// REMOVED OVER ALLOCATED PAGE Per BRIAN 1/29/2018
									//if ($row['QTY_ALLOC'] > $row['QTY_ONHD']) {									
									//	$html .= "      <pagebreak>";
									//	$html .= "		<div class='item'>OVER ALLOCATED</div><br>";
									//}


/*
									if ($row['SH_FLAG_STK'] == 'N') {
										$trim_REF_ORD = ltrim($row['REF_ORD'],'0');
										$ordLineNum = str_replace($trim_REF_ORD,'',trim($row['ID_SO']));
										$html .= "		<div class='barcode'><barcode code='".$row['ID_ITEM']."_".$trim_REF_ORD."_".$ordLineNum."' type='C93' class='barcode'></div>";
										$html .= "		<div class='item'>".$row['ID_ITEM']."<br>".$trim_REF_ORD."_".$ordLineNum."</div>";
									} else {
										$html .= "		<div class='barcode'><barcode code='".$row['ID_ITEM']."' type='C93' class='barcode'></div>";
										$html .= "		<div class='item'>".$row['ID_ITEM']."</div><br>";
									}

									if ($QTY_IN_BOX <> "") {
										$html .= "		<div class='qty'>QTY=".number_format($QTY_IN_BOX)."<barcode code='".$QTY_IN_BOX."' type='C93' class='barcode'></div>";
									}
									$html .= "		<div class='date'>".date("m/d/Y")."</div>";
*/
									$html .= "	</body>";
									$html .= "</html>";

									$boxLabelOutputFile = "/tmp/soLabel/" . $SO ."___box.pdf";						//$mpdf=new mPDF('','A4-L','','',10,10,120,10,10,10);
									$mpdf=new mPDF('utf-8',array(100,53),0,0,0,0,2,0);
									$mpdf->WriteHTML($html);
									$mpdf->Output($boxLabelOutputFile,'F'); 
									$boxLabelOutputFile = str_replace('/tmp','',$boxLabelOutputFile);

									$ret .= "<table>\n";
									$ret .= " <tr>\n";
									$ret .= "  <td class='icon'><a href='".$boxLabelOutputFile."' target='_blank'><img class='icon' src='/images/box.png' href='".$boxLabelOutputFile."' target='_blank'></br>Box Label<br>".str_replace("#","",$row['ID_ITEM'])."</a></td>\n";
									$ret .= " </tr>\n";
									$ret .= "</table>\n";


									////////////
									//  BAG LABEL ONLY IF UPC IS ON RECORD FOR ITEM
									////////////
									//$sql2  = "select z.UPC_CODE, b.DESCR_1, b.DESCR_2 ";
									//$sql2 .= " FROM nsa.ITMMAS_BASE b ";
									//$sql2 .= " LEFT JOIN nsa.ITMMAS_BASZ z ";
									//$sql2 .= " on b.rowid = z.RFA ";
									//$sql2 .= " WHERE b.ID_ITEM = '" . $binItem . "' ";

									$sql2  = "SELECT a.VAL_STRING_ATTR as UPC_CODE, b.DESCR_1, b.DESCR_2 ";
									$sql2 .= " FROM nsa.ITMMAS_BASE b ";
									$sql2 .= " LEFT JOIN nsa.IM_CMCD_ATTR_VALUE a ";
									$sql2 .= " on b.ID_ITEM = a.ID_ITEM ";
									$sql2 .= " and a.ID_ATTR = 'UPC_CODE' ";
									$sql2 .= " WHERE b.ID_ITEM = '" . $binItem . "' ";
									QueryDatabase($sql2, $results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										if ($row2['UPC_CODE'] <> "") {
											$DESCR_1 = str_replace("\x94",'&#34;',$row2['DESCR_1']); //double quote
											$DESCR_2 = str_replace("\x94",'&#34;',$row2['DESCR_2']); //double quote
											$DESCR_1 = str_replace("\x92",'&#39;',$DESCR_1); //single quote
											$DESCR_2 = str_replace("\x92",'&#39;',$DESCR_2); //single quote
											$DESCR_1 = str_replace("\xae",'&#174;',$DESCR_1); //Registered Trademark Symbol
											$DESCR_2 = str_replace("\xae",'&#174;',$DESCR_2); //Registered Trademark Symbol


											$html2 = $css_2;
											$html2 .= "	<body>";
											$html2 .= "		<div class='item'>Style No.: ".$binItem."</div>";
											$html2 .= "		<div class='descr'>".$DESCR_1." ".$DESCR_2."</div>";
											$html2 .= "		<div class='spacer'></div>";
											$html2 .= "		<div class='barcode'><barcode code='".$row2['UPC_CODE']."' type='UPCA' class='barcode' /></div>";
											$html2 .= "	</body>";
											$html2 .= "</html>";

											if ($DEBUG) {
												error_log("ID_ITEM: ".$row['ID_ITEM']);
												error_log("DESCR_1: ".$DESCR_1);
												error_log("DESCR_2: ".$DESCR_2);
												error_log("UPC_CODE: ".$row2['UPC_CODE']);
												error_log("validate UPCA: " . validate_UPCABarcode($row2['UPC_CODE']));

											}




											$bagLabelOutputFile = "/tmp/soLabel/" . $SO ."___bag.pdf";
											if ($DEBUG) {
												error_log("bagLabelOutputFile: ".$bagLabelOutputFile);
											}											
											$mpdf=new mPDF('utf-8',array(58,50),0,0,0,0,2,0);
											$mpdf->WriteHTML($html2);
											$mpdf->Output($bagLabelOutputFile,'F'); 
											$bagLabelOutputFile = str_replace('/tmp','',$bagLabelOutputFile);


											$ret .= "<table>\n";
											$ret .= " <tr>\n";
											$ret .= "  <td class='icon'><a href='".$bagLabelOutputFile."' target='_blank'><img class='icon' src='/images/bag.jpg' href='".$bagLabelOutputFile."' target='_blank'></br>Bag Label<br>".$row['ID_ITEM']."</a></td>\n";
											$ret .= " </tr>\n";
											$ret .= "</table>\n";
										}
									}


									////////////
									//  BIN PRIM LABEL ONLY IF BIN_PRIM IS LISTED
									////////////
									$sql2  = "select il.ID_ITEM, il.BIN_PRIM ";
									$sql2 .= " FROM nsa.ITMMAS_LOC il ";
									$sql2 .= " WHERE il.ID_ITEM = '" . $binItem . "' ";
									//$sql2 .= " WHERE il.ID_ITEM = '".$row['ID_ITEM'] . "' ";
									$sql2 .= " and il.ID_LOC = '10' ";
									QueryDatabase($sql2, $results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										if ($row2['BIN_PRIM'] <> "") {
											
											$html3 = $css_1;
											$html3 .= "	<body>";
											$html3 .= "	<br><br><br>";
											//$html3 .= "		<div class='barcode'><barcode code='".str_replace("-","",$row2['BIN_PRIM'])."' type='C39' class='barcode'></div>";
											$html3 .= "		<div class='bin'>".$row2['BIN_PRIM']."</div><br>";
											$html3 .= "		<div class='itemSmall'>".$row2['ID_ITEM']."</div>";
											$html3 .= "	</body>";
											$html3 .= "</html>";

											if ($DEBUG) {
												error_log("ID_ITEM: ".$row2['ID_ITEM']);
												error_log("BIN_PRIM: ".$row2['BIN_PRIM']);
											}

											$binLabelOutputFile = "/tmp/soLabel/" . $SO ."___bin.pdf";
											$mpdf=new mPDF('utf-8',array(100,53),0,0,0,0,2,0);
											$mpdf->WriteHTML($html3);
											$mpdf->Output($binLabelOutputFile,'F'); 
											$binLabelOutputFile = str_replace('/tmp','',$binLabelOutputFile);

											$ret .= "<table>\n";
											$ret .= " <tr>\n";
											$ret .= "  <td class='icon'><a href='".$binLabelOutputFile."' target='_blank'><img class='icon' src='/images/binLabel.jpg' href='".$binLabelOutputFile."' target='_blank'></br>Bin Label<br>".$row['ID_ITEM']."</a></td>\n";
											$ret .= " </tr>\n";
											$ret .= "</table>\n";
										}
									}


									////////////
									//  AUTOMATED SHIRT LABEL ONLY IF WC = 7600
									////////////
									$sql2  = "select top 1 sh.ID_SO, sh.ID_ITEM_PAR, sh.QTY_ORD ";
									$sql2 .= " FROM nsa.SHPORD_OPER so ";
									$sql2 .= " LEFT JOIN nsa.SHPORD_HDR sh ";
									$sql2 .= " ON so.ID_SO = sh.ID_SO ";
									$sql2 .= " AND so.SUFX_SO = sh.SUFX_SO ";
									$sql2 .= " WHERE so.ID_WC = '7600' ";
									$sql2 .= " and ltrim(sh.ID_SO) = '" . $SO . "' ";
									$sql2 .= " and sh.SUFX_SO = '" . $SUFX . "' ";
									QueryDatabase($sql2, $results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										
										$html3 = "";	
										$html3 .= $css_3;
										$html3 .= "	<body>";

										$hArray = array("CUFFS, COLLARS","BACKS, YOKES, SLEEVES, SLEEVE BINDING");
										$hArrayLength = count($hArray);

										for($x=0; $x < $hArrayLength; $x++) {
											if ($x == 1) {
												$html3 .= "      <pagebreak>";
											}
											$html3 .= "		<div>".$hArray[$x]."</div><br>";
											$html3 .= "		<div class='spacer'>";
											$html3 .= "		<center><table class='automation'>";
											$html3 .= "			<tr class='automation'>";
											$html3 .= "				<td class='automation'>SHOP ORDER #</td>";
											$html3 .= "				<td class='automation'>".$row2['ID_SO']."</td>";
											$html3 .= "			</tr>";
											$html3 .= "			<tr class='automation'>";
											$html3 .= "				<td class='automation'>PART #</td>";
											$html3 .= "				<td class='automation'>".$row2['ID_ITEM_PAR']."</td>";
											$html3 .= "			</tr>";
											$html3 .= "			<tr class='automation'>";
											$html3 .= "				<td class='automation'>QUANTITY</td>";
											$html3 .= "				<td class='automation'>".$row2['QTY_ORD']."</td>";
											$html3 .= "			</tr>";
											$html3 .= "			<tr class='automation'>";
											$html3 .= "				<td class='automation'>SIZE</td>";
											$html3 .= "				<td class='automation'></td>";
											$html3 .= "			</tr>";
											$html3 .= "			<tr class='automation'>";
											$html3 .= "				<td class='automation'>SHADE</td>";
											$html3 .= "				<td class='automation'></td>";
											$html3 .= "			</tr>";											
											$html3 .= "		</table></center>";
											$html3 .= "		</div>";
										}

										$html3 .= "	</body>";
										$html3 .= "</html>";

										$shirtAutoLabelOutputFile = "/tmp/soLabel/" . $SO ."___shirtAuto.pdf";
										$mpdf=new mPDF('utf-8',array(100,53),0,0,0,0,2,0);
										$mpdf->WriteHTML($html3);
										$mpdf->Output($shirtAutoLabelOutputFile,'F'); 
										$shirtAutoLabelOutputFile = str_replace('/tmp','',$shirtAutoLabelOutputFile);

										$ret .= "<table>\n";
										$ret .= " <tr>\n";
										$ret .= "  <td class='icon'><a href='".$shirtAutoLabelOutputFile."' target='_blank'><img class='icon' src='/images/robot.jpg' href='".$shirtAutoLabelOutputFile."' target='_blank'></br>Shirt Automation<br>".$row['ID_ITEM']."</a></td>\n";
										$ret .= " </tr>\n";
										$ret .= "</table>\n";
									}
								}
							} else {
								$ret .= "					<font>NO MATCH</font>\n";
							}
						}


						///////////
						// ITEM NUMBER MODE
						///////////
						if (isset($_POST["item"]) && isset($_POST["qty_in_box"])) {
							//$ITEM = stripNonANChars(trim($_POST["item"]));
							$ITEM = trim($_POST["item"]);
							//$ITEM = str_replace("'", "''", $ITEM);

							error_log("ITEM: ".$ITEM);

							$QTY_IN_BOX = stripNonANChars(trim($_POST["qty_in_box"]));

							$sqle  = "SET QUOTED_IDENTIFIER OFF ";
							QueryDatabase($sqle, $resultse);

							$sql  = "select ";
							$sql .= " ib.ID_ITEM, ";
							$sql .= " ib.DESCR_1, ";
							$sql .= " ib.DESCR_2, ";
							$sql .= " ib.rowid, ";
							$sql .= " il.QTY_ALLOC, ";
							$sql .= " il.QTY_ONHD ";
							$sql .= " from nsa.ITMMAS_BASE ib ";
							$sql .= " left join nsa.ITMMAS_LOC il ";
							$sql .= " on ib.ID_ITEM = il.ID_ITEM ";
							$sql .= " and il.ID_LOC = '10' ";
							$sql .= ' where ib.ID_ITEM = "' . $ITEM . '" ';
							error_log("sql: ".$sql);
							QueryDatabase($sql, $results);
							if (mssql_num_rows($results) > 0) {
								while ($row = mssql_fetch_assoc($results)) {
									if (substr($row['ID_ITEM'],-1) == '#') {
										$binItem = str_replace("#","",$row['ID_ITEM']);
									} else {
										$binItem = $row['ID_ITEM'];
									}

									$html = $css_1;
									$html .= "	<body>";
									$html .= '		<div class="barcode"><barcode code="'.$row['ID_ITEM'].'" type="C93" class="barcode"></div>';
									$html .= '		<div class="item">'.$row['ID_ITEM'].'</div><br>';
									if ($QTY_IN_BOX <> "") {
										$html .= "		<div class='qty'>QTY=".number_format($QTY_IN_BOX)."<barcode code='".$QTY_IN_BOX."' type='C93' class='barcode'></div>";
									}
									$html .= "		<div class='date'>".date("m/d/Y")."</div>";

									// REMOVED OVER ALLOCATED PAGE Per BRIAN 1/29/2018
									//if ($row['QTY_ALLOC'] > $row['QTY_ONHD']) {									
									//	$html .= "      <pagebreak>";
									//	$html .= "		<div class='item'>OVER ALLOCATED</div><br>";
									//}

									$html .= "	</body>";
									$html .= "</html>";

									$boxLabelOutputFile = "/tmp/soLabel/" . $row['rowid'] ."___box.pdf";						//$mpdf=new mPDF('','A4-L','','',10,10,120,10,10,10);
									
									$mpdf=new mPDF('utf-8',array(100,53),0,0,0,0,2,0);
									$mpdf->WriteHTML($html);
									$mpdf->Output($boxLabelOutputFile,'F'); 
									$boxLabelOutputFile = str_replace('/tmp','',$boxLabelOutputFile);

									$ret .= "<table>\n";
									$ret .= " <tr>\n";
									$ret .= "  <td class='icon'><a href='".$boxLabelOutputFile."' target='_blank'><img class='icon' src='/images/box.png' href='".$boxLabelOutputFile."' target='_blank'></br>Box Label<br>".$row['ID_ITEM']."</a></td>\n";
									$ret .= " </tr>\n";
									$ret .= "</table>\n";


									////////////
									//  BAG/HANG TAG LABELS ONLY IF UPC IS ON RECORD FOR ITEM
									////////////
									//$sql2  = "select z.UPC_CODE, b.DESCR_1, b.DESCR_2, b.rowid ";
									//$sql2 .= " FROM nsa.ITMMAS_BASE b ";
									//$sql2 .= " LEFT JOIN nsa.ITMMAS_BASZ z ";
									//$sql2 .= " on b.rowid = z.RFA ";
									//$sql2 .= ' WHERE b.ID_ITEM = "' . $binItem . '" ';
									$sql2  = "SELECT a.VAL_STRING_ATTR as UPC_CODE, b.DESCR_1, b.DESCR_2, b.rowid ";
									$sql2 .= " FROM nsa.ITMMAS_BASE b ";
									$sql2 .= " LEFT JOIN nsa.IM_CMCD_ATTR_VALUE a ";
									$sql2 .= " on b.ID_ITEM = a.ID_ITEM ";
									$sql2 .= " and a.ID_ATTR = 'UPC_CODE' ";
									$sql2 .= " WHERE b.ID_ITEM = '" . $binItem . "' ";
									QueryDatabase($sql2, $results2);
									error_log($sql2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										if ($row2['UPC_CODE'] <> "") {

											/////////////
											// BAG LABEL
											/////////////
											$html2 = $css_2;
											$html2 .= "	<body>";
											$html2 .= "		<div class='item'>Style No.: ".$binItem."</div>";
											$html2 .= "		<div class='descr'>".str_replace("\xae","",$row2['DESCR_1'])." ".$row2['DESCR_2']."</div>";
											$html2 .= "		<div class='spacer'></div>";
											$html2 .= "		<div class='barcode'><barcode code='".$row2['UPC_CODE']."' type='UPCA' class='barcode' /></div>";
											$html2 .= "	</body>";
											$html2 .= "</html>";

											if ($DEBUG) {
												error_log("ID_ITEM: ".$row['ID_ITEM']);
												error_log("DESCR_1: ".$row2['DESCR_1']);
												error_log("DESCR_2: ".$row2['DESCR_2']);
												error_log("UPC_CODE: ".$row2['UPC_CODE']);
												error_log("ROWID: ".$row2['rowid']);
											}

											$bagLabelOutputFile = "/tmp/soLabel/" . $row2['rowid'] ."___bag.pdf";
											$mpdf=new mPDF('utf-8',array(58,50),0,0,0,0,2,0);
											$mpdf->WriteHTML($html2);
											$mpdf->Output($bagLabelOutputFile,'F'); 
											$bagLabelOutputFile = str_replace('/tmp','',$bagLabelOutputFile);

											$ret .= "<table>\n";
											$ret .= " <tr>\n";
											$ret .= "  <td class='icon'><a href='".$bagLabelOutputFile."' target='_blank'><img class='icon' src='/images/bag.jpg' href='".$bagLabelOutputFile."' target='_blank'></br>Bag Label<br>".$row['ID_ITEM']."</a></td>\n";
											$ret .= " </tr>\n";
											$ret .= "</table>\n";

											/////////////
											// HANG TAG LABEL
											/////////////
											$html2 = $css_4;
											$html2 .= "	<body>";
											$html2 .= "		<div class='item'>Style No.: ".$binItem."</div>";
											$html2 .= "		<div class='descr'>".str_replace("\xae","",$row2['DESCR_1'])." ".$row2['DESCR_2']."</div>";
											$html2 .= "		<div class='spacer'></div>";
											$html2 .= "		<div class='barcode'><barcode code='".$row2['UPC_CODE']."' type='UPCA' class='barcode' height='.25'/></div>";
											$html2 .= "	</body>";
											$html2 .= "</html>";

											if ($DEBUG) {
												error_log("ID_ITEM: ".$row['ID_ITEM']);
												error_log("DESCR_1: ".$row2['DESCR_1']);
												error_log("DESCR_2: ".$row2['DESCR_2']);
												error_log("UPC_CODE: ".$row2['UPC_CODE']);
												error_log("ROWID: ".$row2['rowid']);
											}

											$hangLabelOutputFile = "/tmp/soLabel/" . $row2['rowid'] ."___hang.pdf";
											$mpdf=new mPDF('utf-8',array(38,25),0,0,0,0,2,0);
											$mpdf->WriteHTML($html2);
											$mpdf->Output($hangLabelOutputFile,'F'); 
											$hangLabelOutputFile = str_replace('/tmp','',$hangLabelOutputFile);

											$ret .= "<table>\n";
											$ret .= " <tr>\n";
											$ret .= "  <td class='icon'><a href='".$hangLabelOutputFile."' target='_blank'><img class='icon' src='/images/hangtag.jpg' href='".$hangLabelOutputFile."' target='_blank'></br>Hang Tag Label<br>".$row['ID_ITEM']."</a></td>\n";
											$ret .= " </tr>\n";
											$ret .= "</table>\n";



										}
									}



									////////////
									//  BIN PRIM LABEL ONLY IF BIN_PRIM IS LISTED
									////////////
									$sql2  = "select il.ID_ITEM, il.BIN_PRIM ";
									$sql2 .= " FROM nsa.ITMMAS_LOC il ";
									$sql2 .= ' WHERE il.ID_ITEM = "' . $binItem . '" ';
									//$sql2 .= " WHERE il.ID_ITEM = '".$row['ID_ITEM'] . "' ";
									$sql2 .= " and il.ID_LOC = '10' ";
									QueryDatabase($sql2, $results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										if ($row2['BIN_PRIM'] <> "") {
											
											$html3 = $css_1;
											$html3 .= "	<body>";
											$html3 .= "	<br><br><br>";
											//$html3 .= "		<div class='barcode'><barcode code='".str_replace("-","",$row2['BIN_PRIM'])."' type='C39' class='barcode'></div>";
											$html3 .= "		<div class='bin'>".$row2['BIN_PRIM']."</div><br>";
											$html3 .= "		<div class='itemSmall'>".$row2['ID_ITEM']."</div>";

											$html3 .= "	</body>";
											$html3 .= "</html>";

											if ($DEBUG) {
												error_log("ID_ITEM: ".$row2['ID_ITEM']);
												error_log("BIN_PRIM: ".$row2['BIN_PRIM']);
											}

											$binLabelOutputFile = "/tmp/soLabel/" . $row['rowid'] ."___bin.pdf";
											$mpdf=new mPDF('utf-8',array(100,53),0,0,0,0,2,0);
											$mpdf->WriteHTML($html3);
											$mpdf->Output($binLabelOutputFile,'F'); 
											$binLabelOutputFile = str_replace('/tmp','',$binLabelOutputFile);

											$ret .= "<table>\n";
											$ret .= " <tr>\n";
											$ret .= "  <td class='icon'><a href='".$binLabelOutputFile."' target='_blank'><img class='icon' src='/images/binLabel.jpg' href='".$binLabelOutputFile."' target='_blank'></br>Bin Label<br>".$row['ID_ITEM']."</a></td>\n";
											$ret .= " </tr>\n";
											$ret .= "</table>\n";
										}
									}
								}
							} else {
								$ret .= "					<font>NO MATCH</font>\n";
							}

							$sqle  = "SET QUOTED_IDENTIFIER ON ";
							QueryDatabase($sqle, $resultse);
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

function getFontSize($strLength) {
	if ($strLength >= 18) {
		$fontSize = 30;	
	} elseif ($strLength == 17) {
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

	$fontSize = 30;

	return $fontSize;
}

?>
