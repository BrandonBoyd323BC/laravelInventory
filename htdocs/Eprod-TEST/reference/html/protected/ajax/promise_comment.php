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

			if (isset($_POST["action"]) && isset($_POST["Rowid"]) && isset($_POST["tb"]) && isset($_POST["DatePromTS"]))  {

				$action = $_POST["action"];
				$Rowid = $_POST["Rowid"];
				$tb = $_POST["tb"];
				$trClass = $_POST["trClass"];
				$DatePromTS = $_POST["DatePromTS"];

				switch ($tb) {
					case "CL":
						$table_name = 'CP_ORDLIN';
						$indent=2;
						break;
					case "CH":
						$table_name = 'CP_ORDHDR';
						$indent=7;
						break;
					default:
						$table_name = '';
				}

				if ($action == 'showAddCommentRow') {
					$sql  = "select ";
					$sql .= " convert(varchar(19),DATE_ADD,100) as DATE_ADD2, ";
					$sql .= " * ";
					$sql .= "FROM ";
					$sql .= " nsa.CUSTOM_COMMENTS CC ";
					$sql .= "WHERE ";
					$sql .= " CC.T_ROWID = '" . $Rowid . "' ";
					$sql .= " and ";
					$sql .= " CC.TABLE_NAME = '" . $table_name . "' ";
					$sql .= " and ";
					$sql .= " CC.DATE_PROM_TS = '" . $DatePromTS . "' ";
					$sql .= "ORDER BY ";
					$sql .= " CC.DATE_ADD asc ";
					QueryDatabase($sql, $results);

					$ret .= "				<td colspan=" . $indent . "></td>\n";
					$ret .= "				<td colspan=" . (17-$indent) . ">\n";
					$ret .= "		 			<table class='" . $trClass . "'>\n";
					$ret .= "						<tr class='" . $trClass . "'>\n";
					$ret .= "							<th onclick=\"closeDiv('" . $tb . "_cmt_row_" . $Rowid . "')\">X</th>\n";
					$ret .= "							<th>Date Added</th>\n";
					$ret .= "							<th>Added By</th>\n";
					$ret .= "							<th>Comment</th>\n";
					$ret .= "						</tr>\n";
					while ($row = mssql_fetch_assoc($results)) {
						$ret .= "						<tr class='" . $trClass . "' id='" . $row['rowid'] . "'>\n";
						$ret .= "							<td></td>\n";
						$ret .= "							<td>" . $row['DATE_ADD2'] . "</td>\n";
						$ret .= "							<td>" . $row['ID_USER_ADD'] . "</td>\n";
						$ret .= "							<td>" . $row['COMMENT'] . "</td>\n";
						$ret .= "						</tr>\n";
					}
					$ret .= "						<tr class='" . $trClass . "' id='" . $tb . "_row_add_comment_" . $Rowid . "'>\n";
					$ret .= "							<td></td>\n";
					$ret .= "							<td colspan=3>\n";
					$ret .= "								<input id='" . $tb . "_txt_add_comment_" . $Rowid ."' name='" . $tb . "_txt_add_comment_" . $Rowid ."' type='textbox' size=40 onkeypress='searchKeyPress(event);'></input>\n";
					$ret .= "								<input id='" . $tb . "_submit_add_comment_" . $Rowid ."' type='button' value='Add' onclick=\"addComment('" . $Rowid . "','" . $tb . "','" . $trClass . "', '" . $DatePromTS ."')\"></input>\n";
					$ret .= "							</td>\n";
					$ret .= "						</tr>\n";
					$ret .= "		 			</table>\n";
					$ret .= "				</td>\n";
				}


				if ($action == 'showAddCommentTextboxRow') {
					$ret .= "							<td></td>\n";
					$ret .= "							<td colspan=3>\n";
					$ret .= "								<input id='" . $tb . "_txt_add_comment_" . $Rowid ."' type='textbox' size=40 onkeypress='searchKeyPress(event);'></input>\n";
					$ret .= "								<input id='" . $tb . "_submit_add_comment_" . $Rowid ."' type='button' value='Add' onclick=\"addComment('" . $Rowid . "','" . $tb . "', '" . $trClass . "', '" . $DatePromTS ."')\"></input>\n";
					$ret .= "							</td>\n";
				}

				if (($action == 'addComment') && (isset($_POST["comment"]))) {
					//Validate input data
					$strpd_comment = stripIllegalChars($_POST["comment"]);

					if ((strlen($strpd_comment) > 0) && ($UserRow['PERM_MGMT'] == '1')) {
						$sql  = "insert into ";
						$sql .= " nsa.CUSTOM_COMMENTS ";
						$sql .= " ( ";
						$sql .= "  T_ROWID, ";
						$sql .= "  TABLE_NAME, ";
						$sql .= "  DATE_PROM_TS, ";
						$sql .= "  DATE_ADD, ";
						$sql .= "  ID_USER_ADD, ";
						$sql .= "  COMMENT ";
						$sql .= " ) VALUES ( ";
						$sql .= "  " . $Rowid . ", ";
						$sql .= "  '" . $table_name . "', ";
						$sql .= "  '" . $DatePromTS . "', ";
						$sql .= "  getdate(), ";
						$sql .= "  '" . $UserRow['ID_USER'] . "', ";
						$sql .= "  '" . $strpd_comment . "' ";
						$sql .= " ) ";
						QueryDatabase($sql, $results);
					}

					$sql  = "select ";
					$sql .= " convert(varchar(19),DATE_ADD,100) as DATE_ADD2, ";
					$sql .= " * ";
					$sql .= "FROM ";
					$sql .= " nsa.CUSTOM_COMMENTS CC ";
					$sql .= "WHERE ";
					$sql .= " CC.T_ROWID = '" . $Rowid . "' ";
					$sql .= " and ";
					$sql .= " CC.TABLE_NAME = '" . $table_name . "' ";
					$sql .= " and ";
					$sql .= " CC.DATE_PROM_TS = '" . $DatePromTS . "' ";
					$sql .= "ORDER BY ";
					$sql .= " CC.DATE_ADD asc ";
					QueryDatabase($sql, $results);

					$ret .= "				<td colspan=" . $indent . "></td>\n";
					$ret .= "				<td colspan=" . (17-$indent) . ">\n";
					$ret .= "		 			<table class='" . $trClass . "'>\n";
					$ret .= "						<tr class='" . $trClass . "'>\n";
					$ret .= "							<th onclick=\"closeDiv('" . $tb . "_cmt_row_" . $Rowid . "')\">X</th>\n";
					$ret .= "							<th>Date Added</th>\n";
					$ret .= "							<th>Added By</th>\n";
					$ret .= "							<th>Comment</th>\n";
					$ret .= "						</tr>\n";
					$p = 0;
					while ($row = mssql_fetch_assoc($results)) {
						$p++;
						$ret .= "						<tr class='" . $trClass . "' id='" . $row['rowid'] . "'>\n";
						$ret .= "							<td></td>\n";
						$ret .= "							<td>" . $row['DATE_ADD2'] . "</td>\n";
						$ret .= "							<td>" . $row['ID_USER_ADD'] . "</td>\n";
						$ret .= "							<td>" . $row['COMMENT'] . "</td>\n";
						if ($p == mssql_num_rows($results)) {
							$ret .= "							<td onclick=\"showAddCommentTextboxRow('" . $Rowid . "', '" . $tb . "','" . $trClass . "', '" . $DatePromTS ."')\">+</td>\n";
						}
						$ret .= "						</tr>\n";
					}
					$ret .= "						<tr class='" . $trClass . "' id='" . $tb . "_row_add_comment_" . $Rowid . "'>\n";
					//$ret .= "							<td></td>\n";
					//$ret .= "							<td colspan=3>\n";
					//if ($UserRow['PERM_MGMT'] == '1') {
					//	$ret .= "								<input id='" . $tb . "_txt_add_comment_" . $Rowid ."' type='textbox' size=40 onkeypress='searchKeyPress(event);'></input>\n";
					//	$ret .= "								<input id='" . $tb . "_submit_add_comment_" . $Rowid ."' type='button' value='Add' onclick=\"addComment('" . $Rowid . "','" . $tb . "','" . $trClass . "')\"></input>\n";
					//} else {
					//	$ret .= "								<font class='red'>Sorry, you do not have sufficient privileges to add comments.</font>\n";
					//}
					//$ret .= "							</td>\n";
					$ret .= "						</tr>\n";
					$ret .= "		 			</table>\n";
					$ret .= "				</td>\n";
				}

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
