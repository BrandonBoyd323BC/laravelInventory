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
			$action = $_POST["action"];

			switch($action){

				//////////////////////////////////////////////
				//////////INSERT NEW RECORD INTO SQL
				//////////////////////////////////////////////
				case("insertRecord");
					if (isset($_POST["item"]) && isset($_POST["bin"]) && isset($_POST["qty"]) )  {
						$Bin	= $_POST["bin"];
						$Item	= $_POST["item"];
						$Qty	= $_POST["qty"];

						$sql  = "INSERT INTO nsa.BIN_COUNT_CUSTOM (";
						$sql .= " ID_ITEM,  ";
						$sql .= " BIN, ";
						$sql .= " QTY_IN_BIN, ";
						$sql .= " ID_USER_ADD, ";
						$sql .= " DATE_ADD ";
						$sql .= " ) values ( ";
						$sql .= " '" . $Item . "', ";
						$sql .= " '" . $Bin . "', ";
						$sql .= " '" . $Qty . "', ";
						$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
						$sql .= " GetDate() ";
						$sql .= " )";
						QueryDatabase($sql, $results);
					}
				break;


				case("numRecsChange");
					if (isset($_POST["user_recs"]) && isset($_POST["num_recs"])) {
						$user_recs = $_POST["user_recs"];
						$num_recs = $_POST["num_recs"];

						$sql  = "SELECT top ".$num_recs;
						$sql .= " * ";
						$sql .= " FROM nsa.BIN_COUNT_CUSTOM ";
						$sql .= " WHERE (FLAG_DEL='' OR FLAG_DEL is NULL) ";
						if ($user_recs <> '--ALL--') {
							$sql .= " AND ID_USER_ADD = '".$user_recs."' ";
						}
						$sql .= " ORDER BY rowid desc ";
						QueryDatabase($sql, $results);

						$prevrowId = '';
						$b_flip = true;

						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample'>BIN</th>\n";
						$ret .= " 		<th class='sample'>Item</th>\n";
						$ret .= " 		<th class='sample'>Quantity</th>\n";
						$ret .= " 		<th class='sample'>Added By</th>\n";
						$ret .= " 		<th class='sample'>Date Add</th>\n";
						$ret .= " 		<th class='sample'></th>\n";
						$ret .= " 	</tr>\n";

						while ($row = mssql_fetch_assoc($results)) {
							if ($prevrowId != $row['rowid']) {
								$b_flip = !$b_flip;
							}
							if ($b_flip) {
								$trClass = 'd1';
							} else {
								$trClass = 'd0';
							}

							$ret .= " 	<tr class='" . $trClass . "'>\n";
							$ret .= " 		<td class='" . $trClass . "' id='BIN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['BIN'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='ID_ITEM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_ITEM'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='QTY_IN_BIN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['QTY_IN_BIN'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='ID_USER_ADD__" . $row['rowid']."' >" . $row['ID_USER_ADD'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='DATE_ADD__" . $row['rowid']."' >" . $row['DATE_ADD'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='delete_" . $row['rowid']."' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
							$ret .= " 	</tr>\n";
						}
						$ret .= " </table>\n";
						$ret .= " </br>\n";
					}
				break;


				/////////////////////////////////////
				///////////EDIT FIELD STUFF
				/////////////////////////////////////
				case("showedit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$ret .= " 		<input type='text' id='" . $FieldID . "_TXT' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
					}
				break;

				case("saveedit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];

						$StrippedFieldValue = stripIllegalChars2($FieldValue);
						$vals = explode("__", $FieldID);
						$field = $vals[0];
						$rowid = $vals[1];

						$sqlu = "UPDATE nsa.BIN_COUNT_CUSTOM set " . $field . " = ltrim('" . $StrippedFieldValue . "'), DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $rowid;
						error_log($sqlu);
						QueryDatabase($sqlu, $resultsu);

						$ret .= $StrippedFieldValue;
					}
				break;

				case("canceledit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$ret .= $FieldValue;
					}
				break;

				case("deleteRecord");
					if (isset($_POST["deleteRecord"]) && isset($_POST["rowid"])) {
						$ROWID = $_POST["rowid"];
						$sqlDel = "update nsa.BIN_COUNT_CUSTOM set FLAG_DEL = 'Y', DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
						QueryDatabase($sqlDel, $resultsDel);
					}
				break;

			}//end switch

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function refreshRecords(){
		$sql  = "SELECT top 20 ";
		$sql .= " CONVERT(varchar(8), mi.DATE_INCID, 112) as DATE_INCID3, ";
		$sql .= " mc.DESCR, ";
		$sql .= " mi.* ";
		$sql .= "from nsa.MAINT_INCIDENTS mi ";
		$sql .= " left join nsa.MAINT_CODES mc ";
		$sql .= " on mi.CODE_MAINT = mc.CODE_MAINT ";
		$sql .= "WHERE mi.FLAG_DEL='' ";
		$sql .= "ORDER BY rowid desc ";
		QueryDatabase($sql, $results);

		$prevrowId = '';
		$b_flip = true;

		$ret .= " </br>\n";
		$ret .= " <table>\n";
		$ret .= " 	<tr>";
		$ret .= " 		<th colspan=2>Last 20 Records: </th>";
		$ret .= " 	</tr>";
		$ret .= " </table>\n";
		$ret .= " <table class='sample'>\n";
		$ret .= " 	<tr>\n";
		$ret .= " 		<th class='sample'>Date Work</th>\n";
		$ret .= " 		<th class='sample'>Mechanic</th>\n";
		$ret .= " 		<th class='sample'>Team</th>\n";
		$ret .= " 		<th class='sample'>Employee</th>\n";
		$ret .= " 		<th class='sample'>Mach ID</th>\n";
		$ret .= " 		<th class='sample'>Maint Code</th>\n";
		$ret .= "		<th class='sample'>Maint Res Code</th>\n";
		$ret .= " 		<th class='sample'>Mins Down</th>\n";
		$ret .= " 		<th class='sample'></th>\n";
		$ret .= " 	</tr>\n";

		while ($row = mssql_fetch_assoc($results)) {
			if ($prevrowId != $row['rowid']) {
				$b_flip = !$b_flip;
			}
			if ($b_flip) {
				$trClass = 'd1';
			} else {
				$trClass = 'd0';
			}

			$curr = $row['DATE_INCID3'] . " " . str_pad($row['TIME_INCID'],6,"0",STR_PAD_LEFT);
			$currts = strtotime($curr);

			$ret .= " 	<tr class='" . $trClass . "'>\n";
			$ret .= " 		<td class='" . $trClass . "'>" . date('m/d/Y',$currts) . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='ID_BADGE_MECH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_BADGE_MECH'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='ID_BADGE_TEAM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_BADGE_TEAM'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='ID_BADGE__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_BADGE'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='ID_MACH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_MACH'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='CODE_MAINT__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" title='" . $row['DESCR'] . "'>" . $row['CODE_MAINT'] . "</td>\n";
			$ret .= "		<td class='" . $trClass . "' id='CODE_MAINT_RES__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" title='" . $row['DESCR'] . "'>" . $row['CODE_MAINT_RES'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='MINS_DOWN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['MINS_DOWN'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='delete_" . $row['rowid']."' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
			$ret .= " 	</tr>\n";
			if ($row['COMMENT'] <> '') {
				$ret .= " 	<tr class='" . $trClass . "'>\n";
				$ret .= " 		<td class='" . $trClass . "'></td>\n";
				$ret .= " 		<th class='" . $trClass . "'>Comments</th>\n";
				$ret .= " 		<td class='" . $trClass . "' id='COMMENT__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" colspan=6>" . $row['COMMENT'] . "</td>\n";
				$ret .= " 	</tr>\n";
			}
		}

	$ret .= " </table>\n";
	$ret .= " </br>\n";

	return $ret;

}

?>
