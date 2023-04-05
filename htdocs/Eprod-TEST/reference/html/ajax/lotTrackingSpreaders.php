<?php

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../protected/procfile.php");
	require_once('../protected/classes/tc_calendar.php');

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

			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				switch ($action) {
					case "searchLotNum":
						///////////////////////////////
						////LOOKUP WHETHER LOT NUMBER MATCHES MATERIAL ON MARKER
						///////////////////////////////
						if (isset($_POST["markID1"]) && isset($_POST["lotNumb"])) {
							$markID = $_POST["markID1"];
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
								$sql .= " WHERE ml.rowid = '".$markID."' ";
								$sql .= " and ltrim(bt.KEY_BIN_3) = '".$LotNum."' ";
								QueryDatabase($sql, $results);
								if (mssql_num_rows($results) > 0) {
									$ret .= $LotNum."\n";
									error_log("Found LotNum: " . $LotNum);
								} else {
									if (strpos($LotNum,"**NOT FOUND**") !== false) {
										$ret .= $LotNum."\n";
										error_log("NOT FOUND LotNum: " . $LotNum);
									} else {
										$ret .= "**NOT FOUND**".$LotNum."\n";
										error_log("NOT FOUND LotNum: " . $LotNum);
									}
								}
							}
						}
					break;


					case "searchAdditionalMarkerIDs":
						///////////////////////////////
						////LOOKUP WHETHER MARKERS USE SAME MATERIAL
						///////////////////////////////
						if (isset($_POST["markID1"]) && isset($_POST["markIDThis"])) {
							$markID1 = $_POST["markID1"];
							$markIDThis = $_POST["markIDThis"];

							$mark1_ITEM = "";
							$markThis_ITEM = "";

							$sql  = " SELECT ml1.MARKER_ID_ITEM_COMP ";
							$sql .= " FROM nsa.MU_MARKER_LOG ml1 ";
							$sql .= " WHERE ml1.rowid = '".$markID1."' ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$mark1_ITEM = $row["MARKER_ID_ITEM_COMP"];
								error_log($mark1_ITEM);
							}

							$sql1  = " SELECT ml2.MARKER_ID_ITEM_COMP ";
							$sql1 .= " FROM nsa.MU_MARKER_LOG ml2 ";
							$sql1 .= " WHERE ml2.rowid = '".$markIDThis."' ";
							QueryDatabase($sql1, $results1);
							while ($row1 = mssql_fetch_assoc($results1)) {
								$markThis_ITEM = $row1["MARKER_ID_ITEM_COMP"];
								error_log($markThis_ITEM);
							}

							if ($mark1_ITEM == $markThis_ITEM) {
								error_log("MARKER MATERIALS MATCH");
								$ret .= $markIDThis;
							} else {
								error_log("MARKER MATERIALS DO NOT MATCH");
								$ret .= "INVALID";
							}
						}
					break;


					case "searchIDBadge":
						///////////////////////////////
						////LOOKUP BADGE NUMER AND DISPLAY EMPLOYEE NAME
						///////////////////////////////					
						if (isset($_POST["idBadgeSpreader"])) {
							$idBadgeSpreader = trim($_POST["idBadgeSpreader"]);

							if (is_numeric($idBadgeSpreader)) {
								$sql  = " SELECT NAME_EMP ";
								$sql .= " FROM nsa.DCEMMS_EMP ";
								$sql .= " WHERE ltrim(ID_BADGE) = '".$idBadgeSpreader."' ";
								$sql .= " and CODE_ACTV = '0' ";
								QueryDatabase($sql, $results);
								while ($row = mssql_fetch_assoc($results)) {
									error_log("EMPLOYEE: " . $row['NAME_EMP']);
									$ret .= $row['NAME_EMP'];
								} 
							}
						}
					break;


					case "insertNewRecord":
						///////////////////////////////
						////INSERT NEW RECORD INTO SQL
						///////////////////////////////
						if (isset($_POST["idBadgeSpreader"]) && isset($_POST["machNumb"]) && isset($_POST["lotNumb"]) && isset($_POST["num_recs"]) && isset($_POST["markID1"]) && isset($_POST["spreadL1"]) && isset($_POST["markID2"]) && isset($_POST["spreadL2"]) && isset($_POST["markID3"]) && isset($_POST["spreadL3"]) && isset($_POST["markID4"]) && isset($_POST["spreadL4"]) && isset($_POST["markID5"]) && isset($_POST["spreadL5"]) && isset($_POST["markID6"]) && isset($_POST["spreadL6"]) && isset($_POST["markID7"]) && isset($_POST["spreadL7"]) && isset($_POST["markID8"]) && isset($_POST["spreadL8"]) && isset($_POST["markID9"]) && isset($_POST["spreadL9"]) && isset($_POST["markID10"]) && isset($_POST["spreadL10"])) {
							
							$markID1 = $_POST["markID1"];
							$markID2 = $_POST["markID2"];
							$markID3 = $_POST["markID3"];
							$markID4 = $_POST["markID4"];
							$markID5 = $_POST["markID5"];
							$markID6 = $_POST["markID6"];
							$markID7 = $_POST["markID7"];
							$markID8 = $_POST["markID8"];
							$markID9 = $_POST["markID9"];
							$markID10 = $_POST["markID10"];

							$spreadL1 = $_POST["spreadL1"];
							$spreadL2 = $_POST["spreadL2"];
							$spreadL3 = $_POST["spreadL3"];
							$spreadL4 = $_POST["spreadL4"];
							$spreadL5 = $_POST["spreadL5"];
							$spreadL6 = $_POST["spreadL6"];
							$spreadL7 = $_POST["spreadL7"];
							$spreadL8 = $_POST["spreadL8"];
							$spreadL9 = $_POST["spreadL9"];
							$spreadL10 = $_POST["spreadL10"];

							$idBadgeSpreader = $_POST["idBadgeSpreader"];
							$machNumb = $_POST["machNumb"];
							$lotNumb = $_POST["lotNumb"];
							$NUM_RECS = $_POST["num_recs"];

							for ($x = 1; $x <= 10; $x++) {
								error_log("markID : ".$x ." " . ${"markID".$x});
								if (${"markID".$x} <> '' && ${"spreadL".$x} <> '') {

									$LOT_NUMS_Ar = explode("\n", $lotNumb);
									$LOT_NUMS_Ar = array_filter($LOT_NUMS_Ar, 'trim'); // remove any extra \r characters left behind

									foreach ($LOT_NUMS_Ar as $LotNum) {
										$FLAG_CUSTOM_LOT = "";
										if (strpos($LotNum,"**NOT FOUND**") !== false) {
											$FLAG_CUSTOM_LOT = "Y";
										}

										$strip_LotNum = str_ireplace("**NOT FOUND**", "", $LotNum);

										$sql = " INSERT INTO nsa.MU_LOT_TRACKING_SPREADERS" . $DB_TEST_FLAG . " (  ";
										$sql .= " ID_LOT,  ";
										$sql .= " MU_MARKER_rowid, ";
										$sql .= " MATL_LENGTH_SPREAD, ";
										$sql .= " ID_BADGE, ";
										$sql .= " DATE_ADD, ";
										$sql .= " ID_MACHINE, ";
										$sql .= " FLAG_CUSTOM_LOT, ";
										$sql .= " FLAG_DEL ";
										$sql .= " ) VALUES (";
										$sql .= " '" . stripIllegalChars2($strip_LotNum) . "', ";
										$sql .= " '" . stripIllegalChars2(${"markID".$x}) . "', ";
										$sql .= " '" . stripIllegalChars2(${"spreadL".$x}) . "', ";
										$sql .= " '" . stripIllegalChars2($idBadgeSpreader) . "', ";
										$sql .= " GetDate(), ";
										$sql .= " '" . stripIllegalChars2($machNumb) . "', ";
										$sql .= " '" . $FLAG_CUSTOM_LOT . "', ";
										$sql .= " '' ";
										$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";
										QueryDatabase($sql, $results);
										$row = mssql_fetch_assoc($results);
										$BaseRowID = $row['LAST_INSERT_ID'];
									}
								}
							} // end of $x for

							$v = refreshRecords($NUM_RECS);
							$ret .= $v;
						}
					break;

					case "numRecsChange":
						if (isset($_POST["numRecsChange"]) && isset($_POST["num_recs"]) ) {
							$NUM_RECS = $_POST["num_recs"];
							$ret .= refreshRecords($NUM_RECS);
						}
					break;

				} //END SWITCH
			}
			echo json_encode(array("returnValue"=> $ret));
		}
	}


////////////////////////////////////////
/////////Refresh Records
///////////////////////////////////////
function refreshRecords($NUM_RECS){
	global $DB_TEST_FLAG;
	$ret1 = "";

	$sql = "SELECT top " . $NUM_RECS;
	//$sql .= " CONVERT(VARCHAR(19),qa.DATE_ADD) as DATE_ADD, ";
	$sql .= " lt.ID_LOT, ";
	$sql .= " lt.MU_MARKER_rowid, ";
	$sql .= " lt.MATL_LENGTH_SPREAD, ";
	$sql .= " ml.MARKER_LENGTH, ";
	$sql .= " lt.ID_BADGE, ";
	$sql .= " lt.DATE_ADD, ";
	$sql .= " lt.ID_MACHINE,";
	$sql .= " lt.rowid ";
	$sql .= " FROM nsa.MU_LOT_TRACKING_SPREADERS" . $DB_TEST_FLAG . " lt ";
	$sql .= " LEFT JOIN nsa.MU_MARKER_LOG ml ";
	$sql .= " on lt.MU_MARKER_rowid =  ml.rowid ";
	$sql .= " where (lt.FLAG_DEL <> 'Y' or lt.FLAG_DEL is null) ";
	$sql .= " order by lt.rowid desc";
	QueryDatabase($sql, $results);

	$ret1 .= " <table class='sample'>\n";//Header for columns
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Marker</th>\n";
	$ret1 .= " 		<th class='sample'>Lot Number</th>\n";
	$ret1 .= " 		<th class='sample'>Spread Length</th>\n";
	$ret1 .= " 		<th class='sample'>Over/Under Length</th>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>Machine Number</th>\n";
	$ret1 .= " 		<th class='sample'>Spreader's Badge</th>\n";
	$ret1 .= " 	</tr>\n";

	while ($row = mssql_fetch_assoc($results)){
		$ret1 .= " 	<tr>\n";
		$ret1 .= " 		<td id='MU_MARKER_rowid__". $row['rowid']."' style='cursor:pointer;' >" . $row['MU_MARKER_rowid'] . "</td>\n";
		$ret1 .= " 		<td id='ID_LOT__". $row['rowid']."' style='cursor:pointer;' >" . $row['ID_LOT'] . "</td>\n";
		$ret1 .= " 		<td id='MATL_LENGTH_SPREAD__". $row['rowid']."' style='cursor:pointer;' >" . $row['MATL_LENGTH_SPREAD'] . "</td>\n";
		$ret1 .= " 		<td id='O-U_LENGTH_SPREAD__". $row['rowid']."' style='cursor:pointer;' >" . ($row['MATL_LENGTH_SPREAD'] - $row['MARKER_LENGTH']) . "</td>\n";
		$ret1 .= " 		<td id='DATE_ADD__". $row['rowid']."' style='cursor:pointer;' >" . $row['DATE_ADD'] . "</td>\n";
		$ret1 .= " 		<td id='ID_MACHINE__". $row['rowid']."' style='cursor:pointer;' >" . $row['ID_MACHINE'] . "</td>\n";
		$ret1 .= " 		<td id='ID_BADGE__". $row['rowid']."' style='cursor:pointer;' >" . $row['ID_BADGE'] . "</td>\n";
		$ret1 .= " 	</tr>\n";
	}

	$ret1 .= " </table>\n";

	return $ret1;
}//end refreshRecords

?>
