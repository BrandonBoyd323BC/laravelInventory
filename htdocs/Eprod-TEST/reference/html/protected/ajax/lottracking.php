<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	//require_once("../mpdf60/mpdf.php");


	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}


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
					case "getMarkerInfo":
						///////////////////////////
						/// MARKERID ENTERED - POPULATE INFORMATION
						///////////////////////////
						if (isset($_POST["markerrowid"])) {
							$MARKER_ROWID = stripNonANChars(trim($_POST["markerrowid"]));
							
							$sql  = "select distinct";
							$sql .= " ms2.MU_MARKER_rowid, ";
							$sql .= "  cast(substring( ";
							$sql .= "  ( ";
							//$sql .= "  		select ','+REPLACE ( REPLACE ( lot1.ID_LOT , CHAR(13) , '' ) , CHAR(10) , ', ' ) as [text()] ";
							$sql .= "    select ','+lot1.ID_LOT as [text()]  ";
							$sql .= "      from nsa.MU_LOT_TRACKING" . $DB_TEST_FLAG . " lot1 ";
							$sql .= "      where lot1.MU_MARKER_rowid = ms2.MU_MARKER_rowid ";
							$sql .= "      and lot1.FLAG_DEL <> 'Y' ";
							$sql .= "      order by lot1.rowid ";
							$sql .= "      for XML PATH ('') ";
							$sql .= "      ),2,1000) as varchar(1000)) as MU_LOTs, ";
							$sql .= " cast(substring( ";
							$sql .= " ( ";
							$sql .= "       select ','+ms1.ID_SO+'-'+rtrim(REPLICATE('0',3-len(CONVERT(char(3), ms1.SUFX_SO)))+CONVERT(char(3), ms1.SUFX_SO)) as [text()] ";
							$sql .= "       from nsa.MU_SO ms1 ";
							$sql .= "       where ms1.MU_MARKER_rowid = ms2.MU_MARKER_rowid ";
							$sql .= "       and ms1.FLAG_DEL <> 'Y' ";
							$sql .= "       order by ms1.rowid ";
							$sql .= "       for XML PATH ('') ";
							$sql .= "   ),2,1000) as varchar(1000)) as MU_SOs, ";
							$sql .= " mm.MARKER_ID_ITEM_COMP, ";
							$sql .= " mm.MARKER_NAME, ";
							$sql .= " mm.SO_ID_ITEM_COMP";
							$sql .= " from nsa.MU_SO ms2 ";
							$sql .= " left join nsa.MU_MARKER_LOG mm ";
							$sql .= " on ms2.MU_MARKER_rowid = mm.rowid ";
							$sql .= " where ms2.FLAG_DEL <> 'Y' ";
							$sql .= " and ms2.MU_MARKER_rowid = '" . $MARKER_ROWID . "'";
							QueryDatabase($sql, $results);
							if (mssql_num_rows($results) > 0) {
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= "<table class='sample'>\n";
									$ret .= "	<tr class='blueHeader'>\n";
									$ret .= "		<th>Marker ID</th>\n";
									$ret .= "		<th>Shop Order(s)</th>\n";
									$ret .= "		<th>Marker Name</th>\n";
									$ret .= "		<th>Marker Fabric</th>\n";
									$ret .= "		<th>SO Fabric</th>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td><input id='markerrowid' type=hidden value='" . $row['MU_MARKER_rowid'] . "'>" . $row['MU_MARKER_rowid'] . "</td>\n";
									$ret .= "		<td>" . $row['MU_SOs'] . "</td>\n";
									$ret .= "		<td>" . $row['MARKER_NAME'] . "</td>\n";
									$ret .= "		<td>" . $row['MARKER_ID_ITEM_COMP'] . "</td>\n";
									$ret .= "		<td>" . $row['SO_ID_ITEM_COMP'] . "</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									$ret .= "</br>\n";
									$ret .= "<table>\n";
									$ret .= "	<tr>\n";
									$ret .= "		<td>Lot Number(s) ONE PER LINE: </td>\n";
									$ret .= "		<td>\n";
									$ret .= "			<textarea id='lotNumb' style='width: 250px; height:100px;' onkeypress=\"searchLotNum(event);\">" . str_replace(",","\r\n",$row['MU_LOTs']) . "\r\n</textarea>\n";
									$ret .= "		</td>\n";
									$ret .= "	</tr>\n";
									$ret .= " 	<tr>\n";
									$ret .= " 		<td></td>\n";
									$ret .= " 		<td><INPUT id='lot_submit' type='button' value='Submit' onClick=\"sendAddValue()\"></td>\n";
									$ret .= " 	</tr>\n";
									$ret .= "</table>\n";
								}
							} else {
								$ret .= " <h1>Not Found</h1>\n";
							}
						}
					break;


					case "searchLotNum":
						///////////////////////////////
						////LOOKUP WHETHER LOT NUMBER MATCHES MATERIAL ON MARKER
						///////////////////////////////
						if (isset($_POST["markerrowid"]) && isset($_POST["lotNumb"])) {
							$markerrowid = $_POST["markerrowid"];
							$lotNumb = $_POST["lotNumb"];
							$LOT_NUMS_Ar = explode("\n", $lotNumb);
							$LOT_NUMS_Ar = array_filter($LOT_NUMS_Ar, 'trim'); // remove any extra \r characters left behind

							foreach ($LOT_NUMS_Ar as $LotNum) {
								$sql  = " SELECT ml.MARKER_ID_ITEM_COMP, ";
								$sql .= " bt.ID_LOC, ";
								$sql .= " bt.KEY_BIN_1, ";
								$sql .= " bt.KEY_BIN_2, ";
								$sql .= " bt.KEY_BIN_3, ";
								$sql .= " bt.QTY_ONHD, ";
								$sql .= " ib.CODE_UM_STK ";
								$sql .= " FROM nsa.MU_MARKER_LOG ml ";
								$sql .= " RIGHT JOIN nsa.BINTAG_ONHD bt ";
								$sql .= " on ml.MARKER_ID_ITEM_COMP = bt.ID_ITEM ";
								$sql .= " LEFT JOIN nsa.ITMMAS_BASE ib ";
								$sql .= " on bt.ID_ITEM = ib.ID_ITEM ";
								$sql .= " WHERE ml.rowid = '".$markerrowid."' ";
								$sql .= " and ltrim(bt.KEY_BIN_3) = '".$LotNum."' ";
								QueryDatabase($sql, $results);
								if (mssql_num_rows($results) > 0) {
									$ret .= $LotNum."\r\n";
									error_log("Found LotNum: " . $LotNum);
								} else {
									if (strpos($LotNum,"**NOT FOUND**") !== false) {
										$ret .= $LotNum."\r\n";
										error_log("NOT FOUND LotNum: " . $LotNum);
									} else {
										$ret .= "**NOT FOUND**".$LotNum."\r\n";
										error_log("NOT FOUND LotNum: " . $LotNum);
									}
								}
							}
						}
					break;


					case "submitAddLotNumber":
						///////////////////////////
						/// Lot Number Add submitted
						///////////////////////////
						if (isset($_POST["tablenum"]) && isset($_POST["idbadge"]) && isset($_POST["markerrowid"]) && isset($_POST["lotNumb"])) {
							
							$TABLE_NUM = stripNonNumericChars(trim($_POST["tablenum"]));
							$ID_BADGE = str_pad(stripNonNumericChars(trim($_POST["idbadge"])),9," ",STR_PAD_LEFT);
							$MARKER_ROWID = stripNonANChars(trim($_POST["markerrowid"]));

							//Mark existing records as deleted, then insert all new records
							$sql  = "UPDATE nsa.MU_LOT_TRACKING" . $DB_TEST_FLAG . " ";
							$sql .= " set FLAG_DEL = 'Y' ";
							$sql .= " WHERE MU_MARKER_rowid = " . $MARKER_ROWID;
							QueryDatabase($sql, $results);

							//$LOTNUMS = stripNonANChars(trim($_POST["lotNumb"]));
							$LOT_NUMS = trim($_POST["lotNumb"]);
							error_log("lotNumb: " . $LOT_NUMS);

							$LOT_NUMS_Ar = explode("\n", $LOT_NUMS);
							$LOT_NUMS_Ar = array_filter($LOT_NUMS_Ar, 'trim'); // remove any extra \r characters left behind

							foreach ($LOT_NUMS_Ar as $LotNum) {
								error_log("One Lot Num: " . $LotNum);
								$FLAG_CUSTOM_LOT = "";
								if (strpos($LotNum,"**NOT FOUND**") !== false) {
									$FLAG_CUSTOM_LOT = "Y";
								}
								$strip_LotNum = str_ireplace("**NOT FOUND**", "", $LotNum);

								$sql  = "INSERT into nsa.MU_LOT_TRACKING" . $DB_TEST_FLAG . " ( ";
								$sql .= " ID_LOT, ";
								$sql .= " MU_MARKER_rowid, ";
								$sql .= " ID_BADGE, ";
								$sql .= " TABLE_NUM, ";
								$sql .= " ID_USER_ADD, ";
								$sql .= " DATE_ADD, ";
								$sql .= " FLAG_DEL, ";
								$sql .= " FLAG_CUSTOM_LOT ";
								$sql .= " ) VALUES ( ";
								$sql .= " '" . trim(stripIllegalChars2($strip_LotNum)) . "', ";
								$sql .= " '" . $MARKER_ROWID . "', ";
								$sql .= " '" . $ID_BADGE . "', ";
								$sql .= " '" . $TABLE_NUM . "', ";
								$sql .= " '" . $UserRow['ID_USER'] . "', ";
								$sql .= " getDate(), ";
								$sql .= " '', ";
								$sql .= " '" . $FLAG_CUSTOM_LOT . "' ";
								$sql .= " ) ";
								error_log("SQL: " . $sql);
								QueryDatabase($sql, $results);
							} 
						}
					break;


					case "numRecsChange":
						///////////////////////////
						/// NUM_RECS CHANGED
						///////////////////////////
						if (isset($_POST["num_recs"]) && isset($_POST["so_num"]) && isset($_POST["sufx"]) && isset($_POST["lot_num"])) {
							//so_num: so_num, sufx: sufx, lot_num: lot_num
							$NUM_RECS = $_POST["num_recs"];
							$SO_NUM = $_POST["so_num"];
							$SUFX = $_POST["sufx"];
							$LOT_NUM = $_POST["lot_num"];
							$ret .= refreshMarkerNumRecs($NUM_RECS, $SO_NUM, $SUFX, $LOT_NUM);
						}
					break;

				} //END SWITCH
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function refreshMarkerNumRecs($NUM_RECS, $SO_NUM, $SUFX, $LOT_NUM) {
	global $DB_TEST_FLAG;

	$sql  = "select distinct top " . $NUM_RECS;
	$sql .= " ml.MU_MARKER_rowid, ";
	//$sql .= " ml.DATE_ADD, ";
	//$sql .= " convert(varchar(19),ml.DATE_ADD,100) as DATE_ADD2, ";
	$sql .= " convert(varchar(19),ml.DATE_ADD,120) as DATE_ADD2, ";
	$sql .= " cast(substring( ";
	$sql .= "  ( ";
	$sql .= "      select ','+ms1.ID_SO+'-'+rtrim(REPLICATE('0',3-len(CONVERT(char(3), ms1.SUFX_SO)))+CONVERT(char(3), ms1.SUFX_SO)) as [text()] ";
	$sql .= "           from nsa.MU_SO ms1 ";
	$sql .= "           where ms1.MU_MARKER_rowid = ml.MU_MARKER_rowid ";
	$sql .= "           and ms1.FLAG_DEL <> 'Y' ";
	$sql .= "           order by ms1.rowid ";
	$sql .= "           for XML PATH ('') ";
	$sql .= "   ),2,1000) as varchar(1000)) as MU_SOs, ";
	$sql .= "  cast(substring( ";
	$sql .= "  ( ";
	$sql .= "       select ','+ml2.ID_LOT as [text()] ";
	$sql .= "           from nsa.MU_LOT_TRACKING" . $DB_TEST_FLAG . " ml2 ";
	$sql .= "           where ml2.MU_MARKER_rowid = ml.MU_MARKER_rowid ";
	$sql .= "           and ml2.FLAG_DEL <> 'Y' ";
	$sql .= "           order by ml2.rowid ";
	$sql .= "           for XML PATH ('') ";
	$sql .= "       ),2,1000) as varchar(1000)) as MU_LOTs, ";
	$sql .= "  mm.SO_ID_ITEM_COMP, ";
	$sql .= "  mm.MARKER_ID_ITEM_COMP, ";
	$sql .= "  mm.MARKER_NAME ";
	$sql .= "  from nsa.MU_LOT_TRACKING" . $DB_TEST_FLAG . " ml ";
	$sql .= "  left join nsa.MU_MARKER_LOG mm ";
	$sql .= "  on ml.MU_MARKER_rowid = mm.rowid ";
	$sql .= "  where ml.FLAG_DEL <> 'Y' ";

	if ($SO_NUM <> "" && $SUFX <> "") {
		$sql .= " and  ";
		$sql .= " cast(substring( ";
		$sql .= "  ( ";
		$sql .= "       select ','+ms1.ID_SO+'-'+rtrim(REPLICATE('0',3-len(CONVERT(char(3), ms1.SUFX_SO)))+CONVERT(char(3), ms1.SUFX_SO)) as [text()] ";
		$sql .= "           from nsa.MU_SO ms1 ";
		$sql .= "           where ms1.MU_MARKER_rowid = ml.MU_MARKER_rowid ";
		$sql .= "           and ms1.FLAG_DEL <> 'Y' ";
		$sql .= "           order by ms1.rowid ";
		$sql .= "           for XML PATH ('') ";
		$sql .= "   ),2,1000) as varchar(1000)) like '%" . $SO_NUM . "-" . str_pad($SUFX, 3, "0") . "%' ";		
	}
	if ($LOT_NUM <> "") {
		$sql .= " and  ";
		$sql .= "  cast(substring( ";
		$sql .= "  ( ";
		$sql .= "       select ','+ml2.ID_LOT as [text()] ";
		$sql .= "           from nsa.MU_LOT_TRACKING" . $DB_TEST_FLAG . " ml2 ";
		$sql .= "           where ml2.MU_MARKER_rowid = ml.MU_MARKER_rowid ";
		$sql .= "           and ml2.FLAG_DEL <> 'Y' ";
		$sql .= "           order by ml2.rowid ";
		$sql .= "           for XML PATH ('') ";
		$sql .= "       ),2,1000) as varchar(1000)) like '%".$LOT_NUM."%' ";
	}
	$sql .= "  order by DATE_ADD2 desc ";

	error_log($sql);

	QueryDatabase($sql, $results);

	$prevrowId = '';
	$b_flip = true;

	$ret1 = " <table class='sample'>\n";
	$ret1 .= " 	<tr class='blueHeader'>\n";
	$ret1 .= " 		<th class='sample'>Date Add</th>\n";
	$ret1 .= " 		<th class='sample'>Marker ID</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order(s)</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order Fabric Code</th>\n";
	$ret1 .= " 		<th class='sample'>Marker Name</th>\n";
	$ret1 .= " 		<th class='sample'>Marker Fabric Code</th>\n";
	$ret1 .= " 		<th class='sample'>Lot Number(s)</th>\n";
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

		$ret1 .= " 	<tr class='" . $trClass . "'>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['DATE_ADD2'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MU_MARKER_rowid'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MU_SOs'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['SO_ID_ITEM_COMP'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MARKER_NAME'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MARKER_ID_ITEM_COMP'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MU_LOTs'] . "</td>\n";
		$ret1 .= " 	</tr>\n";
	}
	$ret1 .= " </table>\n";
	return $ret1;
}

?>
