<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

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

					case "selLocationChange":
						if (isset($_POST["selLocation"])) {
							$location = $_POST["selLocation"];
							error_log("location: " . $location);

							$ret .= " <table class='sample'>";
							$ret .= " 	<tr>";
							$ret .= " 		<th>Order Number</th>";
							$ret .= " 		<th>Estimated Ship Date<br>(yyyy-mm-dd)</th>";
							$ret .= " 		<th>Old Ship Date<br>(yyyy-mm-dd)</th>";
							$ret .= " 		<th>Late Code</th>";
							$ret .= " 		<th>Comment</th>";
							$ret .= " 		<th>Flag Del<br>(Invoiced)</th>";
							$ret .= " 	</tr>";

							$sql1  = "SELECT LATE_CODE, LATE_DESCR ";
							$sql1 .= " FROM nsa.LATE_ORDER_CODES ";
							$sql1 .= " ORDER BY LATE_CODE asc ";
							QueryDatabase($sql1, $results1);
							$LateCodesCount = mssql_num_rows($results1);
							
							$arrayCodesMD=array();
							while ($row1 = mssql_fetch_assoc($results1)) {
								array_push($arrayCodesMD,array($row1['LATE_CODE'],$row1['LATE_DESCR']));
							}

							$sql  = "SELECT ";
							$sql .= " CONVERT(varchar,getDate(),23) as DATE_TODAY, ";
							$sql .= " oh.ID_ORD, ";
							$sql .= " ol.ID_LOC, ";
							$sql .= " oh.NAME_CUST, ";
							$sql .= " oh.ID_PO_CUST, ";
							$sql .= " min(ol.DATE_PROM) as DATE_PROM, ";
							$sql .= " CONVERT(varchar,cc.DATE_EST_SHIP,23) as DATE_EST_SHIP, ";
							$sql .= " CONVERT(varchar,cc.DATE_OLD_SHIP,23) as DATE_OLD_SHIP, ";
							$sql .= " cc.COMMENT, ";
							$sql .= " isnull(cc.FLAG_DEL,'') as FLAG_DEL, ";
							$sql .= " ih.MAX_DATE_INVC, ";
							$sql .= " cc.DATE_ADD, ";
							$sql .= " isnull(cc.DATE_CHG,cc.DATE_ADD) as DATE_LAST_CHG, ";
							$sql .= " isnull(cc.LATE_CODE,'') as cc_LATE_CODE ";
							$sql .= " FROM nsa.CP_ORDLIN ol ";
							$sql .= " LEFT JOIN nsa.CP_ORDHDR oh ";
							$sql .= " on ol.ID_ORD = oh.ID_ORD ";
							$sql .= " LEFT JOIN nsa.CP_SHPLIN sl ";
							$sql .= " on ol.ID_ORD = sl.ID_ORD ";
							$sql .= " and ol.SEQ_LINE_ORD = sl.SEQ_LINE_ORD ";
							$sql .= " LEFT JOIN nsa.CP_ORDHDR_CUSTOM_COMMENTS cc ";
							$sql .= " on oh.ID_ORD = cc.ID_ORD ";
							$sql .= " LEFT JOIN ( ";
							$sql .= "  SELECT max(DATE_INVC) as MAX_DATE_INVC, ID_ORD ";
							$sql .= "  FROM nsa.CP_INVHDR_HIST ";
							$sql .= "  WHERE DATE_INVC > '2019-01-01' ";
							$sql .= "  GROUP BY ID_ORD ";
							$sql .= " ) ih ";
							$sql .= " on oh.ID_ORD = ih.ID_ORD ";
							$sql .= " WHERE oh.ID_ORD IS NOT NULL ";
							$sql .= " and ol.QTY_OPEN - isnull(sl.QTY_SHIP,0) > 0 ";
							$sql .= " and ol.ID_LOC = '".$location."' ";
							//$sql .= " and ol.DATE_RQST <> '2999-01-01'";
							$sql .= " GROUP BY oh.ID_ORD, oh.NAME_CUST, oh.ID_PO_CUST, ol.ID_LOC, cc.DATE_EST_SHIP, cc.DATE_OLD_SHIP, cc.COMMENT, cc.LATE_CODE, ih.MAX_DATE_INVC, cc.DATE_ADD, cc.DATE_CHG, isnull(cc.FLAG_DEL,'') ";
							$sql .= " ORDER BY min(ol.DATE_PROM) asc ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$bgColorDate = "";
								if ($row["DATE_TODAY"] > $row["DATE_EST_SHIP"] && isset($row["DATE_EST_SHIP"])) {
									$bgColorDate = "style = 'background-color:#FF3342;'";
								}

								$bgColorRow = "";
								if ($row["MAX_DATE_INVC"] > $row["DATE_LAST_CHG"] && isset($row["MAX_DATE_INVC"]) && isset($row["DATE_ADD"])) {
									$bgColorRow = "style = 'background-color:#FF3342;'";
								}

								$ret .= "	<tr id='tr_id_ord'>";
								$ret .= " 		<td ".$bgColorRow." title='".$row['NAME_CUST']."'>".$row["ID_ORD"]."</td>";

								$ret .= " 		<td ".$bgColorDate."><div id='div_est_ship_date' ".$bgColorDate.">";
								$ret .= "			<input id='tb_est_ship_date__".$row["ID_ORD"]."' type=text maxlength=10 size=10 autofocus value='".$row["DATE_EST_SHIP"]."'>";
								$ret .= "		</div></td>";

								$ret .= " 		<td><div id='div_old_ship_date'>";
								$ret .= "			<input id='tb_old_ship_date__".$row["ID_ORD"]."' type=text maxlength=10 size=10 autofocus value='".$row["DATE_OLD_SHIP"]."'>";
								$ret .= "		</div></td>";

								$ret .= " 		<td><div id='div_late_code'>";
								$ret .= "			<select id='sel_late_code__".$row["ID_ORD"]."' name='sel_late_code__".$row["ID_ORD"]."'> ";
								$ret .= "				<option value =''></option>";
								for ($rowCode = 0; $rowCode < $LateCodesCount; $rowCode++) {
									$SELECTED = '';
									$CURRENT = '';
									if (trim($row['cc_LATE_CODE']) == trim($arrayCodesMD[$rowCode][0])) {
										$SELECTED = 'SELECTED';
										$CURRENT = '*';
									}
									$ret .= "				<option value='" . $arrayCodesMD[$rowCode][0] . "' " . $SELECTED . ">" . $CURRENT . $arrayCodesMD[$rowCode][1] .  "</option>\n";
								}
								$ret .= "			</select>";
								$ret .= "		</div></td>";

								$ret .= " 		<td><div id='div_comment'>";
								$ret .= "			<textarea name='comments_TXT__".$row["ID_ORD"]."' id='comments_TXT__".$row["ID_ORD"]."' rows='1' maxlength='100'>".$row["COMMENT"]."</textarea>";
								$ret .= "		</div></td>";

								$SELECTED = "";
								if ($row["FLAG_DEL"] == 'D') {
									$SELECTED = "SELECTED";
								}
								$ret .= " 		<td><div id='div_flag_del'>";
								$ret .= "			<select id='sel_flag_del__".$row["ID_ORD"]."' name='sel_flag_del__".$row["ID_ORD"]."'> ";
								$ret .= "				<option value =''></option>";
								$ret .= "				<option value ='D' $SELECTED>Deleted</option>";
								$ret .= "			</select>";
								$ret .= "		</div></td>";

								$ret .= " 		<td><div id='div_save'>";
								$ret .= "			<INPUT id='submit' type='button' value='Save' onClick=\"saveOrdHdrComment('".$row["ID_ORD"]."')\" >";
								$ret .= "		</div></td>";
								$ret .= " 	</tr>";
							}
		
							$ret .= " </table>";
							$ret .= " <table id='table_ret_form'>";
							$ret .= " </table>";



						}
					break;

					case "saveOrdHdrComment":
						if (isset($_POST["selLocation"]) && isset($_POST["id_ord"]) && isset($_POST["est_ship_date"]) && isset($_POST["old_ship_date"]) && isset($_POST["late_code"]) && isset($_POST["comments"]) && isset($_POST["flag_del"])) {

							$location 		= trim($_POST["selLocation"]);
							$id_ord 		= trim($_POST["id_ord"]);
							$est_ship_date 	= $_POST["est_ship_date"];
							$old_ship_date	= $_POST["old_ship_date"];
							$late_code 		= trim(ms_escape_string($_POST["late_code"]));
							$comments 		= ms_escape_string($_POST["comments"]);
							$flag_del 		= trim(ms_escape_string($_POST["flag_del"]));

							if ($DEBUG) {
								error_log("location: ".$location);
								error_log("id_ord: ".$id_ord);
								error_log("est_ship_date: ".$est_ship_date);
								error_log("old_ship_date: ".$old_ship_date);
								error_log("late_code: ".$late_code);
								error_log("comments: ".$comments);
								error_log("flag_del: ".$flag_del);
							}

							$sql =  "SELECT * ";
							$sql .= " FROM nsa.CP_ORDHDR_CUSTOM_COMMENTS ";
							$sql .= " WHERE ltrim(ID_ORD) = '" . $id_ord . "' ";
							QueryDatabase($sql, $results);
							if (mssql_num_rows($results) > 0) {
								$sql =  "UPDATE nsa.CP_ORDHDR_CUSTOM_COMMENTS set ";
								
								if ($est_ship_date == "") {
									$sql .= " DATE_EST_SHIP = NULL, ";
								} else {
									$sql .= " DATE_EST_SHIP = '".$est_ship_date."', ";
								}
								
								if ($old_ship_date == "") {
									$sql .= " DATE_OLD_SHIP = NULL, ";
								} else {
									$sql .= " DATE_OLD_SHIP = '".$old_ship_date."', ";
								}
							
								if ($late_code == "") {
									$sql .= " LATE_CODE = NULL, ";
								} else {
									$sql .= " LATE_CODE = '".$late_code."', ";
								}

								$sql .= " COMMENT = '".$comments."', ";
								$sql .= " FLAG_DEL = '".$flag_del."', ";
								$sql .= " DATE_CHG = getDate(), ";
								$sql .= " ID_USER_CHG = '".$UserRow['ID_USER']."' ";
								$sql .= " WHERE ID_ORD = '".$id_ord."' ";
								QueryDatabase($sql, $results);
							} else {
								$sql =  "INSERT into nsa.CP_ORDHDR_CUSTOM_COMMENTS (";
								$sql .= " ID_ORD, ";
								$sql .= " DATE_EST_SHIP, ";
								$sql .= " DATE_OLD_SHIP, ";
								$sql .= " LATE_CODE, ";
								$sql .= " COMMENT, ";
								$sql .= " FLAG_DEL, ";
								$sql .= " DATE_ADD, ";
								$sql .= " ID_USER_ADD ";
								$sql .= " ) VALUES ( ";
								$sql .= " '".$id_ord."', ";
								
								if ($est_ship_date == "") {
									$sql .= " NULL, ";
								} else {
									$sql .= " '".$est_ship_date."', ";
								}
								
								if ($old_ship_date == "") {
									$sql .= " NULL, ";
								} else {
									$sql .= " '".$old_ship_date."', ";
								}

								if ($late_code == "") {
									$sql .= " NULL, ";
								} else {
									$sql .= " '".$late_code."', ";
								}

								$sql .= " '".$comments."', ";
								
								if ($flag_del == "") {
									$sql .= " NULL, ";
								} else {
									$sql .= " '".$flag_del."', ";
								}

								$sql .= " getDate(), ";
								$sql .= " '".$UserRow['ID_USER']."' ";
								$sql .= " ) ";
								QueryDatabase($sql, $results);
							}
						}
					break;

				}//end Switch
			}

			echo json_encode(array("returnValue"=> $ret));
			
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>



