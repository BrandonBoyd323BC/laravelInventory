<?php

///////////////////////////////////////
/////OLD SITE DO NOT MAINTAIN
//////////////////////////////////////
///////////////////////////////////////
/////OLD SITE DO NOT MAINTAIN
//////////////////////////////////////
///////////////////////////////////////
/////OLD SITE DO NOT MAINTAIN
//////////////////////////////////////



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

/*
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
*/
			///////////////////////////
			/// SHOP ORDER ENTERED - POPULATE Item, Cust, Order
			///////////////////////////
			if (isset($_POST["soNumber"]) && isset($_POST["field"]) && isset($_POST["sufx"]) ) {
				error_log("IN IF");
				$soNumber	= stripNonANChars(trim($_POST["soNumber"]));
				$sufx = stripNonANChars(trim($_POST["sufx"]));
				$field = $_POST["field"];
				error_log("PRE SQL");

				$sql =  "select ";
				$sql .= " 	so.ID_SO, so.ID_ITEM_PAR, so.REF_ORD, oh.ID_ORD, oh.NAME_CUST, so.DATE_DUE_ORD, xi.NAME_DOC, xd.DESCR_DOC, xd.NAME_FILE ";
				$sql .= " from nsa.SHPORD_HDR so ";
				$sql .= " left join nsa.CP_ORDHDR_PERM oh ";
				$sql .= " on RIGHT(so.REF_ORD, 6) = convert(varchar(8), oh.ID_ORD) ";
				$sql .= " left join nsa.DOC_XREF_ITEM xi";
				$sql .= " on so.ID_ITEM_PAR = xi.ID_ITEM";
				$sql .= " left join nsa.DOC_XREF_DTL xd";
				$sql .= " on xi.NAME_DOC = xd.NAME_DOC";
				$sql .= " where ltrim(so.ID_SO) = '" . $soNumber ."' and so.SUFX_SO = " . $sufx . " ";			
				QueryDatabase($sql, $results);
				error_log($sql);
				if (mssql_num_rows($results) > 0) {
					while ($row = mssql_fetch_assoc($results)) {
						if($field == 'NAME_FILE'){	
							$filename = $row[$field];
							//$filename = str_replace('..', '/protected', $filename);
							$filename = str_replace('"', '', $filename);
							$filename = str_replace('work instructions', 'Work_Instructions', $filename);
							$filename = str_replace('\\\\fs1\\netshare\\', '', $filename);
							//$short_filename = substr($filename, strrpos($filename, '/') + 1);

							//error_log("filename: " . $filename);
							$ret = "<a href='" . $filename . "  ' target='_blank'><img src='../images/wi_img.jpg' id='img_" . $field ."' type=text value='".$filename."' style='width:128px;height:128px;'></a>";					
						}
						if($field == 'ID_ITEM_PAR'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						}
						if($field == 'ID_ORD'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='9' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						}
						if($field == 'NAME_CUST'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='30' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						}
						//else{
							//$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						//}		
					}//end while
				}else{
					$ret = "<input id='txt_" . $field ."' type=text style='height:50px;width:500px;font-size:28pt;' value=''>";
				}
			}

			///////////////////////////
			/// ORDER ENTERED - POPULATE Cust
			///////////////////////////
			if (isset($_POST["ordNumber"]) && isset($_POST["field"]) ) {
				error_log("IN IF");
				$ordNumber	= stripNonANChars(trim($_POST["ordNumber"]));
				$field = $_POST["field"];
				error_log("PRE SQL");

				$sql =  "select ";
				$sql .= " 	so.ID_SO, so.ID_ITEM_PAR, so.REF_ORD, oh.ID_ORD, oh.NAME_CUST, so.DATE_DUE_ORD, xi.NAME_DOC, xd.DESCR_DOC, xd.NAME_FILE ";
				$sql .= " from nsa.SHPORD_HDR so ";
				$sql .= " left join nsa.CP_ORDHDR_PERM oh ";
				$sql .= " on RIGHT(so.REF_ORD, 6) = convert(varchar(8), oh.ID_ORD) ";
				$sql .= " left join nsa.DOC_XREF_ITEM xi";
				$sql .= " on so.ID_ITEM_PAR = xi.ID_ITEM";
				$sql .= " left join nsa.DOC_XREF_DTL xd";
				$sql .= " on xi.NAME_DOC = xd.NAME_DOC";
				$sql .= " where ltrim(oh.ID_ORD) = '" . $ordNumber ."' ";			
				QueryDatabase($sql, $results);
				error_log($sql);
				if (mssql_num_rows($results) > 0) {
					while ($row = mssql_fetch_assoc($results)) {
						if($field == 'NAME_FILE'){	
							$filename = $row[$field];
							//$filename = str_replace('..', '/protected', $filename);
							$filename = str_replace('"', '', $filename);
							//$filename = str_replace('work instructions', 'Work_Instructions', $filename);
							//$filename = str_replace('\\\\fs1\\netshare\\', '', $filename);
							//$short_filename = substr($filename, strrpos($filename, '/') + 1);
							$ret = "<a href='" . $filename . "  ' target='_blank'><img src='../images/wi_img.jpg' id='img_" . $field ."' type=text value='".$filename."' style='width:128px;height:128px;'></a>";					
						}
						//if($field == 'ID_ITEM_PAR'){
						//$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						//}
						//if($field == 'ID_ORD'){
						//$ret = "<input id='txt_" . $field ."' type=text maxlength='9' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						//}
						if($field == 'NAME_CUST'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='30' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						}
						//else{
							//$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						//}		
					}//end while
				}else{
					$ret = "<input id='txt_" . $field ."' type=text style='height:50px;width:500px;font-size:28pt;' value=''>";
				}
			}

/*
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
*/

/*			///////////////////////////
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
*/
			///////////////////////////////
			////INSERT NEW RECORD INTO SQL
			//////////////////////////////

			if (isset($_POST["insp_type"]) && isset($_POST["soNumber"]) && isset($_POST["soNumber_suffix"]) && isset($_POST["itemNumber"]) && isset($_POST["orderNumber"]) && isset($_POST["nameCust"]) 
				&& isset($_POST["passFail"]) && isset($_POST["descText"]) && isset($_POST["teamBadge"]) && isset($_POST["inspecInitals"]) && isset($_POST["pass100"]) && isset($_POST["fail100"])
				&& isset($_POST["noLines"]) && isset($_POST["num_recs"]) && isset($_POST["user_recs"]) && isset($_POST["search_so"]) && isset($_POST["searchInspType"]) && isset($_POST["searchOrd"]) 
				&& isset($_POST["searchTeam"]) && isset($_POST["searchPF"]))
			{
				$insp_type = $_POST["insp_type"];
				$soNumber = $_POST["soNumber"];
				$soNumber_suffix = $_POST["soNumber_suffix"];
				$itemNumber = $_POST["itemNumber"];
				$orderNumber = $_POST["orderNumber"];
				$nameCust = $_POST["nameCust"];
				$passFail = $_POST["passFail"];
				$descText = $_POST["descText"];
				$teamBadge = $_POST["teamBadge"];
				$inspecInitals = $_POST["inspecInitals"];
				$pass100 = $_POST["pass100"];
				$fail100 = $_POST["fail100"];
				$noLines = $_POST["noLines"];
				$NUM_RECS = $_POST["num_recs"];
				$USER_RECS = $_POST["user_recs"];

				$SEARCH_SO = $_POST["search_so"];
				$SEARCHORD = $_POST["searchOrd"];
				$SEARCHTEAM = $_POST["searchTeam"];
				$SEARCHPF = $_POST["searchPF"];
				$SEARCHINSPTYPE = $_POST["searchInspType"];

				$sql = " INSERT INTO nsa.QA_LOG (  ";
				$sql .= " ID_USER_ADD,  ";
				$sql .= " DATE_ADD, ";
				//$sql .= " ID_USER_CHG, ";
				//$sql .= " DATE_CHG, ";
				$sql .= " ID_ORD, ";
				$sql .= " ID_SO, ";
				$sql .= " ID_SO_SUFFIX, ";
				$sql .= " ID_ITEM, ";
				$sql .= " NAME_CUST, ";
				$sql .= " INSPECTION_TYPE, ";
				if($passFail != ''){
					$sql .= " FLAG_PASS_FAIL, ";
				}
				if($pass100 != ''){
					$sql .= " QTY_PASS, ";
				}
				if($fail100 != ''){
					$sql .= " QTY_FAIL, ";
				}
				if($noLines != ''){
					$sql .= " NO_LINES, ";
				}
				$sql .= " DESCR, ";
				$sql .= " TEAM_BADGE, ";
				$sql .= " INSP_INITIALS, ";
				$sql .= " FLAG_DEL ";
				$sql .= " )VALUES (";
				$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
				$sql .= " GetDate(), ";
				$sql .= " '" . ms_escape_string($orderNumber) . "', ";
				$sql .= " '" . ms_escape_string($soNumber) . "', ";
				$sql .= " '" . ms_escape_string($soNumber_suffix) . "', ";
				$sql .= " '" . ms_escape_string($itemNumber) . "', ";				
				$sql .= " '" . ms_escape_string($nameCust) . "', ";
				$sql .= " '" . ms_escape_string($insp_type) . "', ";
				if($passFail != ''){
					$sql .= " '" . ms_escape_string($passFail) . "', ";
				}
				if($pass100 != ''){
					$sql .= " '" . ($pass100) . "', ";
				}
				if($fail100 != ''){
					$sql .= " '" . ($fail100) . "', ";
				}
				if($noLines != ''){
					$sql .= " '" . ms_escape_string($noLines) . "', ";
				}
				$sql .= " '" . ms_escape_string($descText) . "', ";
				if($prodDevSig != ''){
					$sql .= " '" . ms_escape_string($prodDevSig) . "', ";
				}
				if($prodDevNotes != ''){
					$sql .= " '" . ms_escape_string($prodDevNotes) . "', ";
				}
				$sql .= " '" . trim(ms_escape_string($teamBadge)) . "', ";
				$sql .= " '" . ms_escape_string($inspecInitals) . "', ";
				$sql .= " '' ";
				$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";
				error_log($sql);
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$BaseRowID = $row['LAST_INSERT_ID'];

				$v = refreshRecords($NUM_RECS,$USER_RECS,$SEARCH_SO,$SEARCHORD,$SEARCHTEAM,$SEARCHPF,$SEARCHINSPTYPE);
				$ret .= $v;

			}

/*			///////////////////////////
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

						$labelOutputFile = "/mnt/GerberPDF/Pending/" . $MARKER_NAME ."___labels.pdf";
						$mpdf=new mPDF('','A4-L','','',10,10,120,10,10,10);
						$mpdf->WriteHTML($html);
						$mpdf->Output($labelOutputFile,'F'); 
						$combinedOutputFile = "/mnt/GerberPDF/Pending/_" . $MARKER_NAME .".pdf";
						$shell_cmd = "pdftk '" . $PDF_FILE . "' background '" . $labelOutputFile . "' output '" . $combinedOutputFile . "'";
						$combine_result = shell_exec($shell_cmd);
						$completedFileLocation = str_replace('Pending/', 'Complete/'.$BaseRowID, $combinedOutputFile);
						$shell_cmd = "mv " . $combinedOutputFile . " " . $completedFileLocation;
						$cmd_result = shell_exec($shell_cmd);
						$shell_cmd = "rm -f " . $PDF_FILE;
						$cmd_result = shell_exec($shell_cmd);
						$shell_cmd = "rm -f " . $labelOutputFile;
						$cmd_result = shell_exec($shell_cmd);
						$shell_cmd = "rm -f " . $combinedOutputFile;
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
*/

			///////////////////////////
			/// NUM_RECS CHANGED
			///////////////////////////
			if (isset($_POST["numRecsChange"]) && isset($_POST["num_recs"]) && isset($_POST["user_recs"]) && isset($_POST["search_so"]) && isset($_POST["searchInspType"]) && isset($_POST["searchOrd"]) 
				&& isset($_POST["searchTeam"]) && isset($_POST["searchPF"]) && isset($_POST["searchDate"]) ) {
				$NUM_RECS = $_POST["num_recs"];
				$USER_RECS = $_POST["user_recs"];
				$SEARCH_SO = $_POST["search_so"];
				$SEARCHPF = $_POST["searchPF"];
				$SEARCHORD = $_POST["searchOrd"];
				$SEARCHTEAM = $_POST["searchTeam"];
				$SEARCHINSPTYPE = $_POST["searchInspType"];
				$SEARCHDATE = $_POST["searchDate"];
				$ret .= refreshRecords($NUM_RECS,$USER_RECS,$SEARCH_SO,$SEARCHORD,$SEARCHTEAM,$SEARCHPF,$SEARCHINSPTYPE, $SEARCHDATE);
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

					$sqlu = "UPDATE nsa.QA_LOG set " . $field . " = '" . $StrippedFieldValue . "', DATE_CHG = getdate(), ID_USER_CHG = '" .  $UserRow['ID_USER'] . "' where rowid = " . $rowid;
					QueryDatabase($sqlu, $resultsu);

					$ret .= $StrippedFieldValue;
				}
			}


			///////////////////////////
			/// DELETE RECORDS
			///////////////////////////
			if (isset($_POST["deleteRecord"]) && isset($_POST["rowid"])) {
				$ROWID = $_POST["rowid"];

				$sqlDel = "update nsa.QA_LOG set FLAG_DEL = 'Y', DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
				QueryDatabase($sqlDel, $resultsDel);

				//$sqlDel = "update nsa.MU_SO set FLAG_DEL = 'Y' where MU_MARKER_rowid = " . $ROWID;
				//QueryDatabase($sqlDel, $resultsDel);

				$ret .= "DELETED";
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

////////////////////////////////////////
/////////Refresh Records
///////////////////////////////////////
function refreshRecords($NUM_RECS,$USER_RECS,$SEARCH_SO,$SEARCHORD,$SEARCHTEAM,$SEARCHPF,$SEARCHINSPTYPE, $SEARCHDATE){
	$sql = "SELECT top " . $NUM_RECS;
	$sql .= " CONVERT(VARCHAR(19),qa.DATE_ADD) as DATE_ADD, ";
	$sql .= " qa.INSPECTION_TYPE, ";
	$sql .= " qa.ID_SO, ";
	$sql .= " qa.ID_SO_SUFFIX, ";
	$sql .= " qa.ID_ITEM,";
	$sql .= " qa.ID_ORD, ";
	$sql .= " qa.NAME_CUST,";
	$sql .= " qa.FLAG_PASS_FAIL,";
	$sql .= " qa.NO_LINES,";
	$sql .= " qa.QTY_PASS,";
	$sql .= " qa.QTY_FAIL,";
	$sql .= " qa.DESCR,";
	//$sql .= " qa.PROD_DEV_INTIALS,";
	//$sql .= " qa.PROD_DEV_NOTES,";
	$sql .= " qa.TEAM_BADGE,";
	$sql .= " qa.INSP_INITIALS,";
	$sql .= " qa.rowid ";
	$sql .= " FROM nsa.QA_LOG qa";
	$sql .= " where qa.FLAG_DEL <> 'Y' ";
	if ($USER_RECS <> '--ALL--') {
		$sql .= " and qa.ID_USER_ADD = '".$USER_RECS."' ";
	}
	if ($SEARCH_SO <> 'ALL') {
		$sql .= " and qa.ID_SO ";
		$sql .= " like '" . $SEARCH_SO . "%' ";
	}
	if ($SEARCHINSPTYPE <> 'ALL') {
		$sql .= " and qa.INSPECTION_TYPE = '".$SEARCHINSPTYPE."' ";
	}
	if ($SEARCHORD <> 'ALL') {
		$sql .= " and qa.ID_ORD like '".$SEARCHORD."%' ";
	}
	if ($SEARCHPF <> 'ALL') {
		$sql .= " and qa.FLAG_PASS_FAIL = '".$SEARCHPF."' ";
	}
	if ($SEARCHTEAM <> 'ALL') {
		$sql .= " and qa.TEAM_BADGE like '".$SEARCHTEAM."%' ";
	}
	if ($SEARCHDATE <> '') {
		$sql .= " and convert(date,DATE_ADD,101) = '".$SEARCHDATE."' ";
	}

	$sql .= " order by qa.DATE_ADD desc";
	//$sql .= "	";
	error_log($sql);
	QueryDatabase($sql, $results);

	$ret1 .= " <table class='sample'>\n";//Header for columns
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>Inspection Type</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order Suffix</th>\n";
	$ret1 .= " 		<th class='sample'>Item</th>\n";
	$ret1 .= " 		<th class='sample'>Order Number</th>\n";
	$ret1 .= " 		<th class='sample'>Customer (ship to)</th>\n";

	$ret1 .= " 		<th class='sample'>PASS/FAIL</th>\n";
	$ret1 .= " 		<th class='sample'>No. Lines</th>\n";
	$ret1 .= " 		<th class='sample'>Qty Pass</th>\n";
	$ret1 .= " 		<th class='sample'>Qty Fail</th>\n";
	$ret1 .= " 		<th class='sample'>Description of Nonconformance</th>\n";
	//$ret1 .= " 		<th class='sample'>Prod Dev Signoff</th>\n";
	//$ret1 .= " 		<th class='sample'>Prod Dev Notes</th>\n";
	$ret1 .= " 		<th class='sample'>Team Badge</th>\n";
	$ret1 .= " 		<th class='sample'>Inspector Initials</th>\n";

	$ret1 .= " 		<th class='sample'></th>\n";
	$ret1 .= " 	</tr>\n";

	while ($row = mssql_fetch_assoc($results)) {
		$ret .= " 	<tr>\n";
			$ret1 .= " 		<td id='DATE_ADD__". $row['rowid']."'>" . $row['DATE_ADD'] . "</td>\n";
			$ret1 .= " 		<td id='INSPECTION_TYPE__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['INSPECTION_TYPE'] . "</td>\n";
			$ret1 .= " 		<td id='ID_SO__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['ID_SO'] . "</td>\n";
			$ret1 .= " 		<td id='ID_SO_SUFFIX__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['ID_SO_SUFFIX'] . "</td>\n";
			/*if($row['SHOP_ORDER'] <> '-'){//ensures blank SO isnt just '-'
			$ret1 .= " 		<td id='SHOP_ORDER__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['SHOP_ORDER'] . "</td>\n";
			}
			if($row['SHOP_ORDER'] == '-'){
			$ret1 .= " 		<td id='SHOP_ORDER__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\"></td>\n";
			}*/
			$ret1 .= " 		<td id='ID_ITEM__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['ID_ITEM'] . "</td>\n";
			$ret1 .= " 		<td id='ID_ORD__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['ID_ORD'] . "</td>\n";
			$ret1 .= " 		<td id='NAME_CUST__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['NAME_CUST'] . "</td>\n";
			$ret1 .= " 		<td id='FLAG_PASS_FAIL__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['FLAG_PASS_FAIL'] . "</td>\n";
			$ret1 .= " 		<td id='NO_LINES__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['NO_LINES'] . "</td>\n";
			$ret1 .= " 		<td id='QTY_PASS__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['QTY_PASS'] . "</td>\n";
			$ret1 .= " 		<td id='QTY_FAIL__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['QTY_FAIL'] . "</td>\n";
			$ret1 .= " 		<td id='DESCR__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['DESCR'] . "</td>\n";
			//$ret1 .= " 		<td id='PROD_DEV_INTIALS__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['PROD_DEV_INTIALS'] . "</td>\n";
			//$ret1 .= " 		<td id='PROD_DEV_NOTESs__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['PROD_DEV_NOTES'] . "</td>\n";
			$ret1 .= " 		<td id='TEAM_BADGE__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['TEAM_BADGE'] . "</td>\n";
			$ret1 .= " 		<td id='INSP_INITIALS__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['INSP_INITIALS'] . "</td>\n";

			$ret1 .= " 		<td class='" . $trClass . "' id='delete_". $row['rowid']."' style='cursor:pointer;' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
		$ret1 .= " 	</tr>\n";
	}//end while

	$ret1 .= " </table>\n";
	

	return $ret1;
}//end refreshRecords

////////////////////////////
///Approve Days Inspections
///////////////////////////

	if (isset($_POST["dateApproving"]) ){

		$approvalCode = 'QAR';
		$comments = 'Daily Inspection Log Approval';
		$userAddChg = '';
		$dateApproving = $_POST["dateApproving"];

		$sql = " INSERT INTO nsa.DCAPPROVALS( ";
		$sql .= " CODE_APP, ";
		$sql .= " DATE_APP, ";
		$sql .= " BADGE_APP, ";
		$sql .= " APP_BY_ID_USER, ";
		$sql .= " COMMENTS, ";
		$sql .= " ID_USER_ADD, ";
		$sql .= " ID_USER_CHG, ";
		$sql .= " DATE_ADD ";
		$sql .= " ) VALUES( ";
		$sql .= " '" . $approvalCode . "', ";
		$sql .= " '" . $dateApproving . "', ";
		$sql .= " '" . stripIllegalChars2($UserRow['ID_BADGE']) . "', ";
		$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
		$sql .= " '" . stripIllegalChars2($comments) . "', ";
		$sql .= " '" . stripIllegalChars2($userAddChg) . "', ";
		$sql .= " '" . stripIllegalChars2($userAddChg) . "', ";
		$sql .= " GetDate() ";
		$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";
		QueryDatabase($sql, $results);
		error_log($sql);
		$row = mssql_fetch_assoc($results);
		$BaseRowID = $row['LAST_INSERT_ID'];
		

	}//end if


/*
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
*/
?>
