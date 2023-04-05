<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

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

			if ($UserRow['PERM_SUBSID'] == '1')  {

				if (isset($_POST["action"])) {
					$action = $_POST["action"];

					switch ($action) {
						case "subsidiaryChanged":
							if (isset($_POST["subsidiary"]) && isset($_POST["num_recs"])) {
								$SUBSIDIARY = $_POST["subsidiary"];
								$NUM_RECS = $_POST["num_recs"];

								error_log("SUBSIDIARY CHANGED: ".$SUBSIDIARY);
								$ret .= refreshNumRecs($NUM_RECS,$SUBSIDIARY);
							}
						break;

						case "numRecsChange":
							if (isset($_POST["subsidiary"]) && isset($_POST["num_recs"])) {
								$SUBSIDIARY = $_POST["subsidiary"];
								$NUM_RECS = $_POST["num_recs"];

								error_log("NUM_RECS CHANGED: ".$NUM_RECS);
								$ret .= refreshNumRecs($NUM_RECS,$SUBSIDIARY);
							}
						break;

						case "buildDash":

						break;

						case "sendAddValue":
							///////////////////////////
							/// FORM SUBMITTED - INSERT INTO SQL
							///////////////////////////

							if (isset($_POST["subsidiary"]) && isset($_POST["date_log"]) && isset($_POST["sales"]) && isset($_POST["ship"]) && isset($_POST["backlog"]) && isset($_POST["num_recs"])){

								$SUBSIDIARY = $_POST["subsidiary"];
								$DATE_LOG = strtoupper($_POST["date_log"]);
								$SALES = $_POST["sales"];
								
								$SALES = $_POST["sales"];
								$SHIP = $_POST["ship"];
								$BACKLOG = $_POST["backlog"];
								$NUM_RECS = $_POST["num_recs"];

								error_log("Backlog:'".$BACKLOG."'");

								$sql  = "SELECT * FROM nsa.SUBSIDIARY_BOOK_SHIP_LOG ";
								$sql .= " WHERE DATE_LOG = '".$DATE_LOG."' ";
								$sql .= " AND SUBSIDIARY = '".$SUBSIDIARY."' ";
								QueryDatabase($sql, $results);
								if (mssql_num_rows($results) > 0) {
									//RECORD ALREADY EXISTS
									error_log("RECORD FOR ".$SUBSIDIARY." on ".$DATE_LOG." ALREADY EXISTS");
									$ret .= " <font>RECORD FOR ".$SUBSIDIARY." on ".$DATE_LOG." ALREADY EXISTS</font>\n";
								} else {
									//INSERT RECORD
									error_log("NEW RECORD FOR ".$SUBSIDIARY." on ".$DATE_LOG);
									$sql0  = "INSERT INTO nsa.SUBSIDIARY_BOOK_SHIP_LOG (";
									$sql0 .= " SUBSIDIARY, ";
									$sql0 .= " DATE_LOG, ";
									$sql0 .= " SLS, ";
									$sql0 .= " SHIP, ";
									if ($BACKLOG <> "") {
										$sql0 .= " BACKLOG, ";	
									}
									$sql0 .= " ID_USER_ADD, ";
									$sql0 .= " DATETIME_ADD ";
									$sql0 .= " ) values ( ";
									$sql0 .= " '" . $SUBSIDIARY . "', ";
									$sql0 .= " '" . $DATE_LOG . "', ";
									$sql0 .= " '" . $SALES . "', ";
									$sql0 .= " '" . $SHIP . "', ";
									if ($BACKLOG <> "") {
										$sql0 .= " '" . $BACKLOG . "', ";
									}
									$sql0 .= " '" . $UserRow['ID_USER'] . "', ";
									$sql0 .= " getDate() ";
									$sql0 .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
									QueryDatabase($sql0, $results0);
									$row0 = mssql_fetch_assoc($results0);
								}



								$ret .= refreshNumRecs($NUM_RECS,$SUBSIDIARY);

							}
						break;

						case("showedit");
							if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
								$FieldID = $_POST['field_id'];
								$FieldValue = $_POST['field_value'];

								error_log("FieldID: ".$FieldID);
								error_log("FieldValue: ".$FieldValue);

								$StrippedFieldValue = stripIllegalChars2($FieldValue);
								$vals = explode("__", $FieldID);
								$field = $vals[0];
								$rowid = $vals[1];							

								error_log("field: ".$field);
								error_log("rowid: ".$rowid);

								//if ((substr(trim($FieldID),0,15) == "ID_BADGE_TEAM__") && (is_numeric(substr(trim($FieldValue),0,3)))) {
								//	$FieldValue = substr(trim($FieldValue),0,3);
								//	error_log("FieldValue: ".$FieldValue);
								//}

								if ($field == "SUBSIDIARY") {
									$Enespro_SELECTED = '';
									$Kunz_SELECTED = '';
									$NSAArkansas_SELECTED = '';
									$NSAKansasAdSpec_SELECTED = '';
									$NSAKansasPostal_SELECTED = '';
									$WildThings_SELECTED = '';
									if($FieldValue == "Enespro"){
										$Enespro_SELECTED = 'SELECTED';
									}
									if($FieldValue == "NSA Arkansas"){
										$NSAArkansas_SELECTED = 'SELECTED';
									}
									if($FieldValue == "NSA Kansas - Ad Spec"){
										$NSAKansasAdSpec_SELECTED = 'SELECTED';
									}
									if($FieldValue == "NSA Kansas - Postal"){
										$NSAKansasPostal_SELECTED = 'SELECTED';
									}
									if($FieldValue == "Kunz Glove"){
										$Kunz_SELECTED = 'SELECTED';
									}
									if($FieldValue == "Wild Things"){
										$WildThings_SELECTED = 'SELECTED';
									}


									//$ret .= " 		<LABEL for='selSubsidiary'>Subsidiary: </LABEL>\n";
									$ret .= "			<select name='" . $FieldID . "_TXT' id='" . $FieldID . "_TXT' >\n";
									$ret .= "				<option value='Enespro' ".$Enespro_SELECTED.">Enespro</option>\n";
									$ret .= "				<option value='Kunz Glove' ".$Kunz_SELECTED.">Kunz Glove</option>\n";
									$ret .= "				<option value='NSA Arkansas' ".$NSAArkansas_SELECTED.">NSA Arkansas</option>\n";
									$ret .= "				<option value='NSA Kansas - Ad Spec' ".$NSAKansasAdSpec_SELECTED.">NSA Kansas - Ad Specialty</option>\n";
									$ret .= "				<option value='NSA Kansas - Postal' ".$NSAKansasPostal_SELECTED.">NSA Kansas - Postal</option>\n";
									$ret .= "				<option value='Wild Things' ".$WildThings_SELECTED.">Wild Things</option>\n";
									$ret .= "			</select>\n";
									$ret .= "			<br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "_TXT','" . $FieldValue . "')\">\n";
								} else {
									$ret .= " 		<input type='text' id='" . $FieldID . "_TXT' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
								}



							}//end if
						break;

						case("saveedit");
							if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
								$FieldID = $_POST['field_id'];
								$FieldValue = $_POST['field_value'];

								$StrippedFieldValue = stripIllegalChars2($FieldValue);
								$vals = explode("__", $FieldID);
								$field = $vals[0];
								$rowid = $vals[1];

								$sqlu  = "UPDATE nsa.SUBSIDIARY_BOOK_SHIP_LOG set " . $field . " = ltrim('" . $StrippedFieldValue . "'), ";
								$sqlu .= " ID_USER_CHG = '".$UserRow['ID_USER']."', ";
								$sqlu .= " DATETIME_CHG = getDate() ";
								$sqlu .= " WHERE rowid = " . $rowid;
								QueryDatabase($sqlu, $resultsu);

								$ret .= $StrippedFieldValue;
							}//end if
						break;

						case("canceledit");
							if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
								$FieldID = $_POST['field_id'];
								$FieldValue = $_POST['field_value'];
								$ret .= $FieldValue;
							}//end if
						break;


					}//end Switch
				}
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function refreshNumRecs($NUM_RECS,$SUBSIDIARY='') {
	$sql  = "select distinct top " . $NUM_RECS;
	$sql .= " sbs.SUBSIDIARY, ";
	//$sql .= " convert(VARCHAR(12),sbs.DATE_LOG,107) as DATE_LOG2, ";
	$sql .= " convert(VARCHAR(12),sbs.DATE_LOG,110) as DATE_LOG, ";
	$sql .= " sbs.SLS, ";
	$sql .= " sbs.SHIP, ";
	$sql .= " sbs.BACKLOG, ";
	$sql .= " sbs.ID_USER_ADD, ";
	$sql .= " sbs.DATETIME_ADD, ";
	$sql .= " sbs.ID_USER_CHG, ";
	$sql .= " sbs.DATETIME_CHG, ";
	$sql .= " sbs.rowid ";
	$sql .= " from nsa.SUBSIDIARY_BOOK_SHIP_LOG sbs ";
	if ($SUBSIDIARY <> 'ALL') {
		//$sql .= " 	WHERE sbs.SUBSIDIARY like '" . $SUBSIDIARY . "%' ";
		$sql .= " 	WHERE sbs.SUBSIDIARY = '" . $SUBSIDIARY . "' ";
	}

	$sql .= " order by sbs.rowid desc ";
	QueryDatabase($sql, $results);

	$prevrowId = '';
	$b_flip = true;

	$ret1 = " <table class='sample'>\n";
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>Subsidiary</th>\n";
	$ret1 .= " 		<th class='sample'>Sales</th>\n";
	$ret1 .= " 		<th class='sample'>Shipments</th>\n";
	$ret1 .= " 		<th class='sample'>Backlog</th>\n";
	$ret1 .= " 	</tr>\n";

	while ($row = mssql_fetch_assoc($results)) {
		if ($prevrowId != $row['rowid']) {
			$b_flip = !$b_flip;
		}
		if ($b_flip) {
			$trClass = 'd1';
		} else {
			$trClass = 'd0';
		}

		$ret1 .= " 	<tr class='" . $trClass . "'>\n";
		//$ret .= " 		<td class='" . $trClass . "' id='HEAD_TYPE__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['HEAD_TYPE'] . "'>" . $row['HEAD_TYPE'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='DATE_LOG__".$row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['DATE_LOG'] . "'>" . $row['DATE_LOG'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='SUBSIDIARY__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['SUBSIDIARY'] . "'>" . $row['SUBSIDIARY'] ."</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='SLS__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['SLS'] . "'>" . $row['SLS'] ."</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='SHIP__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['SHIP'] . "'>" . $row['SHIP'] ."</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='BACKLOG__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['BACKLOG'] . "'>" . $row['BACKLOG'] ."</td>\n";
		$ret1 .= " 	</tr>\n";
	}
	$ret1 .= " </table>\n";
	return $ret1;
}

?>
