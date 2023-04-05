<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../protected/procfile.php");
	require_once('../protected/classes/tc_calendar.php');
	//require_once("../protected/mpdf60/mpdf.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';
			//$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);


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
						$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style=''>";
						}
						if($field == 'ID_ORD'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='9' value='" . trim($row[$field]) . "' style=''>";
						}
						if($field == 'NAME_CUST'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='30' value='" . trim($row[$field]) . "' style=''>";
						}
						//else{
							//$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:pt;'>";
						//}		
					}//end while
				}else{
					$ret = "<input id='txt_" . $field ."' type=text style='' value=''>";
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
						if($field == 'ID_ITEM_PAR'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style=''>";
						}
						//if($field == 'ID_ORD'){
						//$ret = "<input id='txt_" . $field ."' type=text maxlength='9' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						//}
						if($field == 'NAME_CUST'){
						$ret = "<input id='txt_" . $field ."' type=text maxlength='30' value='" . trim($row[$field]) . "' style=''>";
						}
						//else{
							//$ret = "<input id='txt_" . $field ."' type=text maxlength='20' value='" . trim($row[$field]) . "' style='height:50px;width:500px;font-size:28pt;'>";
						//}		
					}//end while
				}else{
					$ret = "<input id='txt_" . $field ."' type=text style='' value=''>";
				}
			}


			///////////////////////////////
			////INSERT NEW RECORD INTO SQL
			//////////////////////////////

			if (isset($_POST["insp_type"]) && isset($_POST["soNumber"]) 
				&& isset($_POST["soNumber_suffix"]) && isset($_POST["itemNumber"]) 
				&& isset($_POST["orderNumber"]) && isset($_POST["nameCust"]) 
				&& isset($_POST["passFail"]) && isset($_POST["descText"]) 
				&& isset($_POST["teamBadge"]) && isset($_POST["inspecInitals"]) 
				&& isset($_POST["pass100"]) && isset($_POST["fail100"])
				&& isset($_POST["noLines"]) && isset($_POST["num_recs"]) 
				&& isset($_POST["qtyInspected"]) && isset($_POST["user_recs"]) 
				&& isset($_POST["search_so"]) && isset($_POST["searchInspType"]) 
				&& isset($_POST["searchOrd"]) && isset($_POST["searchTeam"]) 
				&& isset($_POST["searchPF"]) && isset($_POST["searchStartDate"]) 
				&& isset($_POST["searchEndDate"]) && isset($_POST["probCode"]) 
				&& isset($_POST["stdComment"])
			){
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
				$qtyInspected = $_POST["qtyInspected"]; 
				$NUM_RECS = $_POST["num_recs"];
				$USER_RECS = $_POST["user_recs"];

				$SEARCH_SO = $_POST["search_so"];
				$SEARCHORD = $_POST["searchOrd"];
				$SEARCHTEAM = $_POST["searchTeam"];
				$SEARCHPF = $_POST["searchPF"];
				$SEARCHINSPTYPE = $_POST["searchInspType"];
				$SEARCHSTARTDATE = $_POST["searchStartDate"];
				$SEARCHENDDATE = $_POST["searchEndDate"];
				$probCode = $_POST["probCode"];
				$stdComment = $_POST["stdComment"];

				$sql = " INSERT INTO nsa.QA_LOG" . $DB_TEST_FLAG . " (  ";
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
				/*if($passFail != ''){
					$sql .= " FLAG_PASS_FAIL, ";
				}*/
				$sql .= " FLAG_PASS_FAIL, ";
				if($pass100 != ''){
					$sql .= " QTY_PASS, ";
				}
				if($fail100 != ''){
					$sql .= " QTY_FAIL, ";
				}
				if($noLines != ''){
					$sql .= " NO_LINES, ";
				}
				if($qtyInspected != ''){
					$sql .= " QTY_INSPECTED, ";
				}
				$sql .= " DESCR, ";
				$sql .= " TEAM_BADGE, ";
				$sql .= " INSP_INITIALS, ";
				$sql .= " FLAG_DEL, ";
				$sql .= " PROB_CODE, ";
				$sql .= " COMMENT_CODE ";
				$sql .= " ) VALUES (";
				$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
				$sql .= " GetDate(), ";
				$sql .= " '" . ms_escape_string($orderNumber) . "', ";
				$sql .= " '" . ms_escape_string($soNumber) . "', ";
				$sql .= " '" . ms_escape_string($soNumber_suffix) . "', ";
				$sql .= " '" . ms_escape_string($itemNumber) . "', ";				
				$sql .= " '" . ms_escape_string($nameCust) . "', ";
				$sql .= " '" . ms_escape_string($insp_type) . "', ";
				/*if($passFail != ''){
					$sql .= " '" . ms_escape_string($passFail) . "', ";
				}*/
				$sql .= " '" . ms_escape_string($passFail) . "', ";
				if($pass100 != ''){
					$sql .= " '" . ($pass100) . "', ";
				}
				if($fail100 != ''){
					$sql .= " '" . ($fail100) . "', ";
				}
				if($noLines != ''){
					$sql .= " '" . ms_escape_string($noLines) . "', ";
				}
				if($qtyInspected != ''){
					$sql .= " '" . ms_escape_string($qtyInspected) ."', ";
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
				$sql .= " '', ";
				$sql .= " '".$probCode."', ";
				$sql .= " '".$stdComment."' ";
				$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";
				error_log($sql);
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$BaseRowID = $row['LAST_INSERT_ID'];


				$v = refreshRecords($NUM_RECS,$USER_RECS,$SEARCH_SO,$SEARCHORD,$SEARCHTEAM,$SEARCHPF,$SEARCHINSPTYPE,$SEARCHSTARTDATE, $SEARCHENDDATE);
				$ret .= $v;

			}



			///////////////////////////
			/// NUM_RECS CHANGED
			///////////////////////////
			if (isset($_POST["numRecsChange"]) && isset($_POST["num_recs"]) 
				&& isset($_POST["user_recs"]) && isset($_POST["search_so"]) 
				&& isset($_POST["searchInspType"]) && isset($_POST["searchOrd"]) 
				&& isset($_POST["searchTeam"]) && isset($_POST["searchPF"]) 
				&& isset($_POST["searchStartDate"]) && isset($_POST["searchEndDate"]) 
			){
				$NUM_RECS = $_POST["num_recs"];
				$USER_RECS = $_POST["user_recs"];
				$SEARCH_SO = $_POST["search_so"];
				$SEARCHPF = $_POST["searchPF"];
				$SEARCHORD = $_POST["searchOrd"];
				$SEARCHTEAM = $_POST["searchTeam"];
				$SEARCHINSPTYPE = $_POST["searchInspType"];
				$SEARCHSTARTDATE = $_POST["searchStartDate"];
				$SEARCHENDDATE = $_POST["searchEndDate"];
				$ret .= refreshRecords($NUM_RECS,$USER_RECS,$SEARCH_SO,$SEARCHORD,$SEARCHTEAM,$SEARCHPF,$SEARCHINSPTYPE, $SEARCHSTARTDATE, $SEARCHENDDATE);
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

					$sqlu = "UPDATE nsa.QA_LOG" . $DB_TEST_FLAG . " set " . $field . " = '" . $StrippedFieldValue . "', DATE_CHG = getdate(), ID_USER_CHG = '" .  $UserRow['ID_USER'] . "' where rowid = " . $rowid;
					QueryDatabase($sqlu, $resultsu);

					$ret .= $StrippedFieldValue;
				}
			}


			///////////////////////////
			/// DELETE RECORDS
			///////////////////////////
			if (isset($_POST["deleteRecord"]) && isset($_POST["rowid"])) {
				$ROWID = $_POST["rowid"];

				$sqlDel = "update nsa.QA_LOG" . $DB_TEST_FLAG . " set FLAG_DEL = 'Y', DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
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
function refreshRecords($NUM_RECS,$USER_RECS,$SEARCH_SO,$SEARCHORD,$SEARCHTEAM,$SEARCHPF,$SEARCHINSPTYPE, $SEARCHSTARTDATE, $SEARCHENDDATE){
	
	global $DB_TEST_FLAG;

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
	$sql .= " qa.QTY_INSPECTED,";
	$sql .= " qa.DESCR,";
	//$sql .= " qa.PROD_DEV_INTIALS,";
	//$sql .= " qa.PROD_DEV_NOTES,";
	$sql .= " qa.TEAM_BADGE,";
	$sql .= " qa.INSP_INITIALS,";
	$sql .= " qa.rowid, ";
	$sql .= " qa.PROB_CODE,";
	$sql .= " pc.DESCR as PROBLEM_DESC,";
	$sql .= " qa.COMMENT_CODE,";
	$sql .= " sc.COMMENT as STD_COMMENT";

	$sql .= " FROM nsa.QA_LOG" . $DB_TEST_FLAG . " qa ";
	$sql .= " LEFT JOIN nsa.QA_PROBLEM_CODES pc ";
	$sql .= " on qa.PROB_CODE = pc.PROB_CODE ";
	$sql .= " LEFT JOIN nsa.QA_STANDARD_COMMENTS sc ";
	$sql .= " on qa.COMMENT_CODE = sc.COMMENT_CODE ";

	$sql .= " where qa.FLAG_DEL <> 'Y' ";
	if ($USER_RECS <> '--ALL--') {
		$sql .= " and qa.INSP_INITIALS = '".$USER_RECS."' ";
	}
	if ($SEARCH_SO <> 'ALL') {
		$sql .= " and rtrim(ltrim(qa.ID_SO)) ";
		$sql .= " like '" . trim($SEARCH_SO) . "%' ";
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
	if ($SEARCHSTARTDATE != '' && $SEARCHENDDATE != ''){
		$sql .= " and convert(date,DATE_ADD,101) between '".$SEARCHSTARTDATE."' and '".$SEARCHENDDATE."' ";
	}//end if


	$sql .= " order by qa.DATE_ADD desc";
	//$sql .= "	";
	error_log($sql);
	QueryDatabase($sql, $results);

	$ret1 = " <table class='sample'>\n";//Header for columns
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>Inspection Type</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order Suffix</th>\n";
	$ret1 .= " 		<th class='sample'>Item</th>\n";
	$ret1 .= " 		<th class='sample'>Order Number</th>\n";
	$ret1 .= " 		<th class='sample'>Customer (ship to)</th>\n";

	$ret1 .= " 		<th class='sample'>PASS/FAIL</th>\n";
	//$ret1 .= " 		<th class='sample'>No. Lines</th>\n";
	$ret1 .= " 		<th class='sample'>Problem Code</th>\n";
	$ret1 .= " 		<th class='sample'>Qty Pass</th>\n";
	$ret1 .= " 		<th class='sample'>Qty Fail</th>\n";
	//$ret1 .= " 		<th class='sample'>Qty Inspected</th>\n";
	$ret1 .= " 		<th class='sample'>Standard Comment</th>\n";
	$ret1 .= " 		<th class='sample'>Description of Nonconformance</th>\n";
	//$ret1 .= " 		<th class='sample'>Prod Dev Signoff</th>\n";
	//$ret1 .= " 		<th class='sample'>Prod Dev Notes</th>\n";
	$ret1 .= " 		<th class='sample'>Team Badge</th>\n";
	$ret1 .= " 		<th class='sample'>Inspector Initials</th>\n";

	$ret1 .= " 		<th class='sample'></th>\n";
	$ret1 .= " 	</tr>\n";

	while ($row = mssql_fetch_assoc($results)) {
		$ret1 .= " 	<tr>\n";
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
			$ret1 .= " 		<td id='FLAG_PASS_FAIL__". $row['rowid']."' >" . $row['FLAG_PASS_FAIL'] . "</td>\n";
			//$ret1 .= " 		<td id='NO_LINES__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['NO_LINES'] . "</td>\n";
			$ret1 .= " 		<td id='PROB_CODE__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\" title='".$row['PROBLEM_DESC']."'>" . $row['PROB_CODE'] . "</td>\n";
			$ret1 .= " 		<td id='QTY_PASS__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['QTY_PASS'] . "</td>\n";
			$ret1 .= " 		<td id='QTY_FAIL__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['QTY_FAIL'] . "</td>\n";
			//$ret1 .= " 		<td id='QTY_INSPECTED__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['QTY_INSPECTED'] . "</td>\n";
			$ret1 .= " 		<td id='COMMENT_CODE__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\" title='".$row['STD_COMMENT']."'>" . $row['COMMENT_CODE'] . "</td>\n";
			$ret1 .= " 		<td id='DESCR__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['DESCR'] . "</td>\n";
			//$ret1 .= " 		<td id='PROD_DEV_INTIALS__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['PROD_DEV_INTIALS'] . "</td>\n";
			//$ret1 .= " 		<td id='PROD_DEV_NOTESs__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['PROD_DEV_NOTES'] . "</td>\n";
			$ret1 .= " 		<td id='TEAM_BADGE__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['TEAM_BADGE'] . "</td>\n";
			$ret1 .= " 		<td id='INSP_INITIALS__". $row['rowid']."' style='cursor:pointer;' onDblClick=\"showEditField(this.id)\">" . $row['INSP_INITIALS'] . "</td>\n";

			//$ret1 .= " 		<td class='" . $trClass . "' id='delete_". $row['rowid']."' style='cursor:pointer;' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
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


?>
