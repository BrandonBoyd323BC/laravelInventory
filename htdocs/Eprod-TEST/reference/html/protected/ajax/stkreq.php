<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	require_once("../mpdf60/mpdf.php");

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


			///////////////////////////
			/// SHOP ORDER 1 ENTERED - POPULATE LIST OF FABRIC CODES
			///////////////////////////
			if (isset($_POST["getSoFabCodes"]) && isset($_POST["so"]) && isset($_POST["sufx"]) ) {
				$SO	= stripNonANChars(trim($_POST["so"]));
				$SUFX = stripNonANChars(trim($_POST["sufx"]));

				$sql =  "select ";
				$sql .= " 	ID_ITEM_COMP ";
				$sql .= " from ";
				$sql .= " 	nsa.SHPORD_MATL ";
				$sql .= " where CODE_UM in ('LI','IN') ";
				$sql .= " 	and ltrim(ID_SO) = '" . $SO . "' ";
				$sql .= " 	and SUFX_SO = '" . $SUFX . "' ";
				$sql .= " order by ID_ITEM_COMP asc";
				QueryDatabase($sql, $results);
				$ret .= "					<option value='SELECT'> -- Select -- </option>\n";
				if (mssql_num_rows($results) > 0) {
					while ($row = mssql_fetch_assoc($results)) {
						$ret .= "					<option value='" . $row['ID_ITEM_COMP'] . "'>" . $row['ID_ITEM_COMP'] . "</option>\n";
					}
				} else {
					$ret .= "					<option value='NO_MATCH'>NO_MATCH</option>\n";
				}
			}


			///////////////////////////
			/// SHOP ORDER FABRIC CODE CHANGED - PRE-POPULATE MARKER FABRIC CODE
			///////////////////////////
			if (isset($_POST["soFabCodeChange"]) && isset($_POST["so_fab_code"])) {
				$SO_FAB_CODE	= trim($_POST["so_fab_code"]);
				if ($SO_FAB_CODE == "SELECT") {
					$SO_FAB_CODE = "";
				}
				$ret .=	"<input id='marker_fab_code' type=text value='" . $SO_FAB_CODE . "' onkeyup=\"markerFabCodeChange()\" tabindex=13>\n";
				//$ret .=	"<input id='marker_fab_code' type=text value='" . $SO_FAB_CODE . "' tabindex=13>\n";
			}


			///////////////////////////
			/// SHOP ORDER FABRIC CODE CHANGED - PRE-POPULATE SHOP ORDER LENGTH
			///////////////////////////
			if (isset($_POST["getSoLength"]) && isset($_POST["so_fab_code"]) && isset($_POST["so1"]) && isset($_POST["sufx_so1"]) && isset($_POST["so2"]) && isset($_POST["sufx_so2"])
				&& isset($_POST["so3"]) && isset($_POST["sufx_so3"]) && isset($_POST["so4"]) && isset($_POST["sufx_so4"]) && isset($_POST["so5"]) && isset($_POST["sufx_so5"])
				&& isset($_POST["so6"]) && isset($_POST["sufx_so6"]) && isset($_POST["so7"]) && isset($_POST["sufx_so7"]) && isset($_POST["so8"]) && isset($_POST["sufx_so8"])
			){
				$SO_FAB_CODE	= trim($_POST["so_fab_code"]);
				$SO1	= trim($_POST["so1"]);
				$SO2	= trim($_POST["so2"]);
				$SO3	= trim($_POST["so3"]);
				$SO4	= trim($_POST["so4"]);
				$SO5	= trim($_POST["so5"]);
				$SO6	= trim($_POST["so6"]);
				$SO7	= trim($_POST["so7"]);
				$SO8	= trim($_POST["so8"]);
				$SUFX1	= trim($_POST["sufx_so1"]);
				$SUFX2	= trim($_POST["sufx_so2"]);
				$SUFX3	= trim($_POST["sufx_so3"]);
				$SUFX4	= trim($_POST["sufx_so4"]);
				$SUFX5	= trim($_POST["sufx_so5"]);
				$SUFX6	= trim($_POST["sufx_so6"]);
				$SUFX7	= trim($_POST["sufx_so7"]);
				$SUFX8	= trim($_POST["sufx_so8"]);
				$retLen = "";

				if($SUFX1 == ""){ 
					$SUFX1 = "0";
				}
				if($SUFX2 == ""){ 
					$SUFX2 = "0";
				}
				if($SUFX3 == ""){
					$SUFX3 = "0";
				}
				if($SUFX4 == ""){
					$SUFX4 = "0";
				}
				if($SUFX5 == ""){
					$SUFX5 = "0";
				}
				if($SUFX6 == ""){
					$SUFX6 = "0";
				}
				if($SUFX7 == ""){
					$SUFX7 = "0";
				}
				if($SUFX8 == ""){
					$SUFX8 = "0";
				}				
				if ($SO_FAB_CODE <> "SELECT") {
					$sql  = "select ";
					$sql .= " ID_ITEM_COMP, "; 
					$sql .= " (sum(QTY_ALLOC) + sum(QTY_ISS))as SUM_QTY_ALLOC_AND_ISS ";
					$sql .= " from nsa.SHPORD_MATL ";
					$sql .= " where ID_ITEM_COMP = '" . $SO_FAB_CODE . "' ";
					$sql .= " and ((ltrim(ID_SO) = '" . $SO1 . "' and SUFX_SO = '" . $SUFX1 . "') ";
					$sql .= " or (ltrim(ID_SO) = '" . $SO2 . "' and SUFX_SO = '" . $SUFX2 . "') ";
					$sql .= " or (ltrim(ID_SO) = '" . $SO3 . "' and SUFX_SO = '" . $SUFX3 . "') ";
					$sql .= " or (ltrim(ID_SO) = '" . $SO4 . "' and SUFX_SO = '" . $SUFX4 . "') ";
					$sql .= " or (ltrim(ID_SO) = '" . $SO5 . "' and SUFX_SO = '" . $SUFX5 . "') ";
					$sql .= " or (ltrim(ID_SO) = '" . $SO6 . "' and SUFX_SO = '" . $SUFX6 . "') ";
					$sql .= " or (ltrim(ID_SO) = '" . $SO7 . "' and SUFX_SO = '" . $SUFX7 . "') ";
					$sql .= " or (ltrim(ID_SO) = '" . $SO8 . "' and SUFX_SO = '" . $SUFX8 . "') ";
					$sql .= " ) group by ID_ITEM_COMP ";
					QueryDatabase($sql, $results);

					while ($row = mssql_fetch_assoc($results)) {
						$retLen = $row['SUM_QTY_ALLOC_AND_ISS'];
					}
				}
				$ret .=	"<input id='so_length' type=text value='" . $retLen . "' tabindex=21>\n";
			}			


			///////////////////////////
			/// FORM SUBMITTED - INSERT INTO SQL
			///////////////////////////
			if (isset($_POST["sendAddValue"]) && isset($_POST["dw"]) && isset($_POST["so1"]) && isset($_POST["so2"]) && isset($_POST["so3"]) 
				&& isset($_POST["so4"]) && isset($_POST["so5"]) && isset($_POST["so6"]) && isset($_POST["so7"]) && isset($_POST["so8"])
				&& isset($_POST["sufx_so1"]) && isset($_POST["sufx_so2"]) && isset($_POST["sufx_so3"]) && isset($_POST["sufx_so4"]) && isset($_POST["sufx_so5"]) 
				&& isset($_POST["sufx_so6"]) && isset($_POST["sufx_so7"]) && isset($_POST["sufx_so8"]) && isset($_POST["so_fab_code"])
				&& isset($_POST["so_length"]) && isset($_POST["marker_name"]) && isset($_POST["marker_fab_code"]) && isset($_POST["marker_util"]) && isset($_POST["marker_length_y"])
				&& isset($_POST["marker_length_in"]) && isset($_POST["num_layers"]) && isset($_POST["flag_recut"]) && isset($_POST["prob_code"]) && isset($_POST["badge_num"]) 
				&& isset($_POST["ret_FileName"]) && isset($_POST["num_recs"]) && isset($_POST["user_recs"])
			){
				$DW = $_POST["dw"];
				$SO1 = strtoupper($_POST["so1"]);
				$SO2 = strtoupper($_POST["so2"]);
				$SO3 = strtoupper($_POST["so3"]);
				$SO4 = strtoupper($_POST["so4"]);
				$SO5 = strtoupper($_POST["so5"]);
				$SO6 = strtoupper($_POST["so6"]);
				$SO7 = strtoupper($_POST["so7"]);
				$SO8 = strtoupper($_POST["so8"]);
				$SUFX_SO1 = $_POST["sufx_so1"];
				$SUFX_SO2 = $_POST["sufx_so2"];
				$SUFX_SO3 = $_POST["sufx_so3"];
				$SUFX_SO4 = $_POST["sufx_so4"];
				$SUFX_SO5 = $_POST["sufx_so5"];
				$SUFX_SO6 = $_POST["sufx_so6"];
				$SUFX_SO7 = $_POST["sufx_so7"];
				$SUFX_SO8 = $_POST["sufx_so8"];
				$SO_FAB_CODE = strtoupper($_POST["so_fab_code"]);
				$SO_LENGTH = $_POST["so_length"];
				$MARKER_NAME = strtoupper($_POST["marker_name"]);
				$MARKER_FAB_CODE = strtoupper($_POST["marker_fab_code"]);
				$MARKER_UTIL = $_POST["marker_util"];
				$MARKER_LENGTH_Y = $_POST["marker_length_y"];
				$MARKER_LENGTH_IN = $_POST["marker_length_in"];
				$NUM_LAYERS = $_POST["num_layers"];
				$PROB_CODE = $_POST["prob_code"];
				$BADGE_NUM = $_POST["badge_num"];
				$PDF_FILE = $_POST["ret_FileName"];
				$NUM_RECS = $_POST["num_recs"];
				$USER_RECS = $_POST["user_recs"];

				//CONVERT MARKER LENGTH TO LINEAR INCHES
				$MARKER_LENGTH = (($MARKER_LENGTH_Y * 36) + $MARKER_LENGTH_IN);

				//CHANGE FORMAT OF FLAG_RECUT TO 'Y'/''
				if ($_POST["flag_recut"]=="true") {
					$FLAG_RECUT = 'Y';
				} else {
					$FLAG_RECUT = '';
				}

				//MAKE SURE MARKER FABRIC CODE IS AN EXISTING ITEM
				$sql  = " SELECT b.ID_ITEM, ";
				$sql .= " b.DESCR_1, ";
				$sql .= " b.DESCR_2, ";
				$sql .= " l.BIN_PRIM ";
				$sql .= " FROM nsa.ITMMAS_BASE b ";
				$sql .= " LEFT JOIN nsa.ITMMAS_LOC l ";
				$sql .= " on b.ID_ITEM = l.ID_ITEM ";
				$sql .= " and l.ID_LOC = '10' ";
				$sql .= " WHERE ltrim(b.ID_ITEM) = '" . $MARKER_FAB_CODE . "' ";
				QueryDatabase($sql, $results);

				if (mssql_num_rows($results) > 0) {
					$row = mssql_fetch_assoc($results);
					$DESCR_1 = $row['DESCR_1'];
					$BIN_PRIM = $row['BIN_PRIM'];

					//INSERT MARKER RECORD
					$sql0  = "INSERT INTO nsa.MU_MARKER_LOG (";
					$sql0 .= " ID_USER_ADD, ";
					$sql0 .= " DATE_ADD, ";
					$sql0 .= " MARKER_NAME, ";
					$sql0 .= " MARKER_UTIL, ";
					$sql0 .= " MARKER_LENGTH, ";
					$sql0 .= " MARKER_LAYERS, ";
					$sql0 .= " MARKER_ID_ITEM_COMP, ";
					$sql0 .= " SO_ID_ITEM_COMP, ";
					$sql0 .= " SO_LENGTH, ";
					$sql0 .= " FLAG_RECUT, ";
					$sql0 .= " PROB_CODE, ";
					$sql0 .= " ID_BADGE, ";
					$sql0 .= " FLAG_DEL ";
					$sql0 .= " ) values ( ";
					$sql0 .= " '" . $UserRow['ID_USER'] . "', ";
					$sql0 .= " getDate(), ";
					$sql0 .= " '" . $MARKER_NAME . "', ";
					$sql0 .= " " . $MARKER_UTIL . ", ";
					$sql0 .= " " . $MARKER_LENGTH . ", ";
					$sql0 .= " " . $NUM_LAYERS . ", ";
					$sql0 .= " '" . $MARKER_FAB_CODE . "', ";
					$sql0 .= " '" . $SO_FAB_CODE . "', ";
					$sql0 .= " " . $SO_LENGTH . ", ";
					$sql0 .= " '" . $FLAG_RECUT . "', ";
					$sql0 .= " '" . $PROB_CODE . "', ";
					$sql0 .= " '" . $BADGE_NUM . "', ";
					$sql0 .= " '' ";
					$sql0 .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
					QueryDatabase($sql0, $results0);
					$row0 = mssql_fetch_assoc($results0);
					$BaseRowID = $row0['LAST_INSERT_ID'];

					//CREATE LABELS PDF AND MERGE WITH UPLOADED 
					if ($PDF_FILE <> "") {
						error_log("PDF_FILE: " . $PDF_FILE);
						//   ../GerberPDF/DocIncoming/Pending/C21WILG03T-2-59IN-GVD___itm_tmp.pdf

						$sql1  = "select ";
						$sql1 .= " sh.ID_SO, ";
						$sql1 .= " sh.ID_ITEM_PAR, ";
						$sql1 .= " sh.FLAG_STK, ";
						$sql1 .= " sh.QTY_ORD, ";
						$sql1 .= "	CONVERT(varchar(10), sh.DATE_ADD, 101) as DATE_ADD3, ";
						$sql1 .= " sh.DATE_ADD, ";
						$sql1 .= "	CONVERT(varchar(10), sh.DATE_DUE_ORD, 101) as DATE_DUE_ORD3, ";
						$sql1 .= " sh.DATE_DUE_ORD ";
						$sql1 .= " from nsa.SHPORD_HDR sh ";
						$sql1 .= " where (ltrim(sh.ID_SO) = '" . $SO1 . "' AND sh.SUFX_SO = '" . $SUFX_SO1 . "') ";
						for ($x = 2; $x <= 8; $x++) {
							if(${"SO".$x} <> '' && ${"SUFX_SO".$x} <> '') {
								$sql1 .= " OR (ltrim(sh.ID_SO) = '" . ${"SO".$x} . "' AND sh.SUFX_SO = '" . ${"SUFX_SO".$x} . "') ";
							}
						}
						QueryDatabase($sql1, $results1);

						///////////////////////////////////
						//	OVERLAY FOR MARKER INFORMATION
						///////////////////////////////////
						$html  = "";
						$html .= "<html>";
						$html .= "	<head>";
						$html .= "		<style>";
						$html .= "			body {";
						$html .= "				font-family: sans-serif;";
						$html .= "				font-size: 9pt;";
						$html .= "				background: transparent url('bgbarcode.png') repeat-y scroll left top;";
						$html .= "			}";
						$html .= "			h5, p {	";
						$html .= "				margin: 0pt;";
						$html .= "			}";
						$html .= "			table.items {";
						$html .= "				font-size: 12pt; ";
						$html .= "				border-collapse: collapse;";
						$html .= "				border: 3px solid #880000; ";
						$html .= "			}";
						$html .= "			td { ";
						$html .= "				vertical-align: top; ";
						$html .= "			}";
						$html .= "			table thead td { ";
						$html .= "				background-color: #EEEEEE;";
						$html .= "				text-align: center;";
						$html .= "			}";
						$html .= "			table tfoot td { ";
						$html .= "				background-color: #AAFFEE;";
						$html .= "				text-align: center;";
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
						$html .= "		<table class='items' width='100%' cellpadding='0' border='1'>";
						$html .= "		<thead>";

						while ($row1 = mssql_fetch_assoc($results1)) {
							$html .= "			<tr>";
							$html .= "				<td colspan=2>Part# ". $row1['ID_ITEM_PAR'] ."</td>";
							$html .= "				<td colspan=2>SO: <b>". $row1['ID_SO'] ." </b>Qty: <b>". $row1['QTY_ORD'] ."</b></td>";
							if ($row1['FLAG_STK'] == 'S') {
								$html .= "				<td>Date Issued: <b>". $row1['DATE_ADD3'] ."</b></td>";
							} else {
								$html .= "				<td>Due Date: <b>". $row1['DATE_DUE_ORD3'] ."</b></td>";
							}
							$html .= "			</tr>";
						}

						$html .= "			<tr>";
						$html .= "				<td>Material: <b>".$MARKER_FAB_CODE."</b></td>";
						$html .= "				<td>Bin: <b>".$BIN_PRIM."</b></td>";
						$html .= "				<td width='10%'>Layers</td>";
						$html .= "				<td width='10%'>Length</td>";
						$html .= "				<td>Marker: ".$MARKER_NAME."</td>";
						$html .= "			</tr>";
						$html .= "		</thead>";
						$html .= "		<tbody>";
						$html .= "			<tr>";
						$html .= "				<td align='center' colspan=2>".$DESCR_1."<br>".$DESCR_2."</td>";
						$html .= "				<td align='center'>".$NUM_LAYERS."</td>";
						$html .= "				<td align='center'>".$MARKER_LENGTH."\"</td>";
						$html .= "				<td class='barcodecell'><barcode code='".$MARKER_NAME."' type='C39' class='barcode' /></td>";
						$html .= "			</tr>";
						$html .= "		</tbody>";
						$html .= "		</table>";
						$html .= "		<h7><barcode code='".$BaseRowID."' type='C39' class='barcode' height='0.56' text='".$BaseRowID."'/>MarkerID: ".$BaseRowID."</h7>";
						$html .= "	</body>";
						$html .= "</html>";



						///////////////////////////////////
						//	OVERLAY FOR USER INITIALS BOXES
						///////////////////////////////////
						$html2  = "";
						$html2 .= "<html>";
						$html2 .= "	<head>";
						$html2 .= "		<style>";
						$html2 .= "			body {";
						$html2 .= "				font-family: sans-serif;";
						$html2 .= "				font-size: 9pt;";
						$html2 .= "				background: transparent url('bgbarcode.png') repeat-y scroll left top;";
						$html2 .= "			}";
						$html2 .= "			h5, p {	";
						$html2 .= "				margin: 0pt;";
						$html2 .= "			}";
						$html2 .= "			table.items {";
						$html2 .= "				font-size: 12pt; ";
						$html2 .= "				border-collapse: collapse;";
						$html2 .= "				border: 3px solid #880000; ";
						$html2 .= "			}";
						$html2 .= "			td { ";
						$html2 .= "				vertical-align: top; ";
						$html2 .= "			}";
						$html2 .= "			table thead td { ";
						$html2 .= "				background-color: #EEEEEE;";
						$html2 .= "				text-align: center;";
						$html2 .= "			}";
						$html2 .= "			table tfoot td { ";
						$html2 .= "				background-color: #AAFFEE;";
						$html2 .= "				text-align: center;";
						$html2 .= "			}";
						$html2 .= "			.barcode {";
						$html2 .= "				padding: 1.5mm;";
						$html2 .= "				margin: 0;";
						$html2 .= "				vertical-align: top;";
						$html2 .= "				color: #000000;";
						$html2 .= "			}";
						$html2 .= "			.barcodecell {";
						$html2 .= "				text-align: center;";
						$html2 .= "				vertical-align: middle;";
						$html2 .= "				padding: 0;";
						$html2 .= "			}";
						$html2 .= "		</style>";
						$html2 .= "	</head>";
						$html2 .= "	<body>";
						$html2 .= "		<table class='items' width='50%' cellpadding='0' border='1'>";
						$html2 .= "		<thead>";
						$html2 .= "			<tr>";
						$html2 .= "				<td>Marker Maker</td>";
						$html2 .= "				<td>Marker Checker</td>";
						$html2 .= "				<td>Spreader</td>";
						$html2 .= "				<td>Cutter</td>";
						$html2 .= "				<td>Cut Support</td>";
						$html2 .= "			</tr>";
						$html2 .= "		</thead>";
						$html2 .= "		<tbody>";
						$html2 .= "			<tr>";
						$html2 .= "				<td align='center'>" . $UserRow['ID_USER'] . "</td>";
						$html2 .= "				<td></td>";
						$html2 .= "				<td></td>";
						$html2 .= "				<td></td>";
						$html2 .= "				<td></td>";
						$html2 .= "			</tr>";
						$html2 .= "		</tbody>";
						$html2 .= "		</table>";
						$html2 .= "	</body>";
						$html2 .= "</html>";

						$labelOutputFile = "/mnt/GerberPDF/Pending/" . $MARKER_NAME ."___labels.pdf";
						$initialsOutputFile = "/mnt/GerberPDF/Pending/" . $MARKER_NAME ."___initials.pdf";
						/*
						$mpdf = new mPDF('',    // mode - default ''
							'',    // format - A4, for example, default ''
							0,     // font size - default 0
							'',    // default font family
							15,    // margin_left
							15,    // margin right
							16,     // margin top
							16,    // margin bottom
							9,     // margin header
							9,     // margin footer
							'L');  // L - landscape, P - portrait
						*/
						$mpdf=new mPDF('','A4-L','','',10,10,120,10,10,10);
						$mpdf->WriteHTML($html);
						$mpdf->Output($labelOutputFile,'F'); 
						$combinedOutputFile = "/mnt/GerberPDF/Pending/_cmb1_" . $MARKER_NAME .".pdf";
						$shell_cmd = "pdftk '" . $PDF_FILE . "' background '" . $labelOutputFile . "' output '" . $combinedOutputFile . "'";
						//error_log("CMD: " . $shell_cmd);
						$combine_result = shell_exec($shell_cmd);

						$mpdf=new mPDF('','A4-L','','',20,10,5,10,10,10);
						$mpdf->WriteHTML($html2);
						$mpdf->Output($initialsOutputFile,'F'); 
						$combinedOutputFile2 = "/mnt/GerberPDF/Pending/_" . $MARKER_NAME .".pdf";
						$shell_cmd = "pdftk '" . $initialsOutputFile . "' background '" . $combinedOutputFile . "' output '" . $combinedOutputFile2 . "'";
						//error_log("CMD: " . $shell_cmd);
						$combine_result = shell_exec($shell_cmd);

						$completedFileLocation = str_replace('Pending/', 'Complete/'.$BaseRowID, $combinedOutputFile2);
						$shell_cmd = "mv " . $combinedOutputFile2 . " " . $completedFileLocation;
						//error_log("CMD: " . $shell_cmd);
						$cmd_result = shell_exec($shell_cmd);
						$shell_cmd = "rm -f " . $PDF_FILE;
						$cmd_result = shell_exec($shell_cmd);
						$shell_cmd = "rm -f " . $labelOutputFile;
						$cmd_result = shell_exec($shell_cmd);
						$shell_cmd = "rm -f " . $initialsOutputFile;
						$cmd_result = shell_exec($shell_cmd);						
						$shell_cmd = "rm -f " . $combinedOutputFile;
						$cmd_result = shell_exec($shell_cmd);
						$shell_cmd = "rm -f " . $combinedOutputFile2;
						$cmd_result = shell_exec($shell_cmd);
						$fileNameToStore = str_replace('/mnt','/protected',$completedFileLocation);
						
						//error_log("PDF_FILE: " . $PDF_FILE);
						//error_log("labelOutputFile: " . $labelOutputFile);
						//error_log("combinedOutputFile: " . $combinedOutputFile);
						//error_log("completedFileLocation: " . $completedFileLocation);
						//error_log("shell_cmd: " . $shell_cmd);
						
						$sql2  = "UPDATE nsa.MU_MARKER_LOG set ";
						$sql2 .= " PDF_FILE = '" . $fileNameToStore . "' ";
						$sql2 .= " WHERE rowid = " . $BaseRowID;
						QueryDatabase($sql2, $results2);

					}

					for ($x = 1; $x <= 8; $x++) {
						if(${"SO".$x} <> '' && ${"SUFX_SO".$x} <> '') {
							$sql2  = "INSERT INTO nsa.MU_SO ( ";
							$sql2 .= " ID_SO, ";
							$sql2 .= " SUFX_SO, ";
							$sql2 .= " MU_MARKER_rowid, ";
							$sql2 .= " ID_USER_ADD, ";
							$sql2 .= " DATE_ADD, ";
							$sql2 .= " FLAG_DEL ";
							$sql2 .= " ) VALUES ( ";
							$sql2 .= " '".${"SO".$x}."', ";
							$sql2 .= " '".${"SUFX_SO".$x}."', ";
							$sql2 .= " ".$BaseRowID.", ";
							$sql2 .= " '" . $UserRow['ID_USER'] . "', ";
							$sql2 .= " getDate(), ";
							$sql2 .= " '' ";
							$sql2 .= " ) ";
							QueryDatabase($sql2, $results2);
						}
					}
					$v = refreshMarkerNumRecs($NUM_RECS,$USER_RECS);
					$ret .= $v;

				} else  {
					$ret .=	"<h1>INVALID Marker Fabric Code</h1>\n";
				}
			}


			///////////////////////////
			/// NUM_RECS CHANGED
			///////////////////////////
			if (isset($_POST["numRecsChange"]) && isset($_POST["num_recs"]) && isset($_POST["user_recs"]) && isset($_POST["search_so"])) {
				$NUM_RECS = $_POST["num_recs"];
				$USER_RECS = $_POST["user_recs"];
				$SEARCH_SO = $_POST["search_so"];
				$ret .= refreshMarkerNumRecs($NUM_RECS,$USER_RECS,$SEARCH_SO);
			}


			///////////////////////////
			/// EDIT RECORDS
			///////////////////////////
			if (isset($_POST["field_id"]) && isset($_POST["field_value"]) && isset($_POST["action"]))  {
				$FieldID = $_POST['field_id'];
				$FieldValue = $_POST['field_value'];
				$Action = $_POST['action'];

				if ($Action == "showedit") {
					$ret .= " 		<input type='text' id='" . $FieldID . "_TXT' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
				}

				if ($Action == "canceledit") {
					$ret .= $FieldValue;
				}

				if ($Action == "saveedit") {
					$StrippedFieldValue = stripIllegalChars($FieldValue);
					$vals = explode("__", $FieldID);
					$field = $vals[0];
					$rowid = $vals[1];

					$sqlu = "UPDATE nsa.MU_MARKER_LOG set " . $field . " = '" . $StrippedFieldValue . "', DATE_CHG = getdate(), ID_USER_CHG = '" .  $UserRow['ID_USER'] . "' where rowid = " . $rowid;
					QueryDatabase($sqlu, $resultsu);

					$ret .= $StrippedFieldValue;
				}
			}


			///////////////////////////
			/// DELETE RECORDS
			///////////////////////////
			if (isset($_POST["deleteRecord"]) && isset($_POST["rowid"])) {
				$ROWID = $_POST["rowid"];

				$sqlDel = "update nsa.MU_MARKER_LOG set FLAG_DEL = 'Y', DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
				QueryDatabase($sqlDel, $resultsDel);

				$sqlDel = "update nsa.MU_SO set FLAG_DEL = 'Y' where MU_MARKER_rowid = " . $ROWID;
				QueryDatabase($sqlDel, $resultsDel);

				$ret .= "DELETED";
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function refreshMarkerNumRecs($NUM_RECS,$USER_RECS,$SEARCH_SO) {
	$sql  = "select distinct top " . $NUM_RECS;
	$sql .= " ms2.MU_MARKER_rowid, ";
	$sql .= " cast(substring( ";
	$sql .= " 	( ";
	$sql .= " 		select ','+ms1.ID_SO+'-'+rtrim(REPLICATE('0',3-len(CONVERT(char(3), ms1.SUFX_SO)))+CONVERT(char(3), ms1.SUFX_SO)) as [text()] ";
	$sql .= " 		from nsa.MU_SO ms1 ";
	$sql .= " 		where ms1.MU_MARKER_rowid = ms2.MU_MARKER_rowid ";
	$sql .= " 		and ms1.FLAG_DEL <> 'Y' ";
	$sql .= " 		order by ms1.rowid ";
	$sql .= " 		for XML PATH ('') ";
	$sql .= " 	),2,1000) as varchar(1000)) as MU_SOs, ";
	$sql .= " mm.DATE_ADD, ";
	$sql .= " mm.SO_ID_ITEM_COMP, ";
	$sql .= " mm.SO_LENGTH, ";
	$sql .= " mm.MARKER_NAME, "; 
	$sql .= " mm.MARKER_ID_ITEM_COMP, ";
	$sql .= " mm.MARKER_UTIL, ";
	$sql .= " mm.MARKER_LENGTH, ";
	$sql .= " mm.MARKER_LAYERS, ";
	$sql .= " mm.FLAG_RECUT, ";
	$sql .= " mm.PROB_CODE, ";
	$sql .= " mm.ID_BADGE, ";
	$sql .= " mm.PDF_FILE, ";
	$sql .= " mm.rowid ";
	$sql .= " from nsa.MU_SO ms2 ";
	$sql .= " left join nsa.MU_MARKER_LOG mm ";
	$sql .= " on ms2.MU_MARKER_rowid = mm.rowid ";
	$sql .= " where ms2.FLAG_DEL <> 'Y' ";
	if ($USER_RECS <> '--ALL--') {
		$sql .= " and mm.ID_USER_ADD = '".$USER_RECS."' ";
	}
	if ($SEARCH_SO <> 'ALL') {
		$sql .= " and cast(substring( ";
		$sql .= " 	( ";
		$sql .= " 		select ','+ms1.ID_SO+'-'+rtrim(REPLICATE('0',3-len(CONVERT(char(3), ms1.SUFX_SO)))+CONVERT(char(3), ms1.SUFX_SO)) as [text()] ";
		$sql .= " 		from nsa.MU_SO ms1 ";
		$sql .= " 		where ms1.MU_MARKER_rowid = ms2.MU_MARKER_rowid ";
		$sql .= " 		and ms1.FLAG_DEL <> 'Y' ";
		$sql .= " 		order by ms1.rowid ";
		$sql .= " 		for XML PATH ('') ";
		$sql .= " 	),2,1000) as varchar(1000)) like '" . $SEARCH_SO . "%' ";
	}
	$sql .= " order by mm.rowid desc ";
	error_log("SQL: " . $sql);
	QueryDatabase($sql, $results);

	$prevrowId = '';
	$b_flip = true;

	$ret1 = " <table class='sample'>\n";
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order Fabric Code</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order Length</th>\n";
	$ret1 .= " 		<th class='sample'>Marker Name</th>\n";
	$ret1 .= " 		<th class='sample'>Marker Fabric Code</th>\n";
	$ret1 .= " 		<th class='sample'>Marker Utilization</th>\n";
	$ret1 .= " 		<th class='sample'>Marker Length</th>\n";
	$ret1 .= " 		<th class='sample'># of Layers</th>\n";
	$ret1 .= " 		<th class='sample'>Flag Recut</th>\n";
	$ret1 .= " 		<th class='sample'>Prob. Code</th>\n";
	$ret1 .= " 		<th class='sample'>Badge</th>\n";
	$ret1 .= " 		<th class='sample'>PDF</th>\n";
	$ret1 .= " 		<th class='sample'></th>\n";
	$ret1 .= " 	</tr>\n";

	while ($row = mssql_fetch_assoc($results)) {
		if ($prevrowId != $row['MU_MARKER_rowid']) {
			$b_flip = !$b_flip;
		}
		if ($b_flip) {
			$trClass = 'd1';
		} else {
			$trClass = 'd0';
		}
		$prevrowId = $row['MU_MARKER_rowid'];
		$pdfLink = "";

		if ($row['PDF_FILE'] <> "") {
			$pdfLink = "PDF";
		}

		$ret1 .= " 	<tr class='" . $trClass . "'>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['DATE_ADD'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MU_SOs'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['SO_ID_ITEM_COMP'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='SO_LENGTH__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['SO_LENGTH'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='MARKER_NAME__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['MARKER_NAME'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='MARKER_ID_ITEM_COMP__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['MARKER_ID_ITEM_COMP'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='MARKER_UTIL__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['MARKER_UTIL'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='MARKER_LENGTH__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['MARKER_LENGTH'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='MARKER_LAYERS__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['MARKER_LAYERS'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='FLAG_RECUT__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['FLAG_RECUT'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='PROB_CODE__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['PROB_CODE'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='ID_BADGE__". $row['rowid']."' onDblClick=\"showEditField(this.id)\">" . $row['ID_BADGE'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='PDF__". $row['rowid']."' ><a href='" . $row['PDF_FILE'] . "' target='_blank'>".$pdfLink."</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='delete_". $row['rowid']."' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
		$ret1 .= " 	</tr>\n";
	}
	$ret1 .= " </table>\n";
	return $ret1;
}

?>
