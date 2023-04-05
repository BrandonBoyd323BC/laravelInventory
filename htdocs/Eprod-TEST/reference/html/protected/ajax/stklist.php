<?php

	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../procfile.php");
	//require_once('../classes/tc_calendar.php');
	//require_once("../classes/mail.class.php");

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
				case "show";
					if (isset($_POST["sel_group_filter"])) {
						
						$groupFilter = $_POST["sel_group_filter"];

						$ret .= "<table class='sample'>\n";

						$sql  = "SELECT DISTINCT [group], ";
						$sql .= " cc.comm_desc_full ";
						$sql .= " FROM nsa.ITMMAS_STK_LIST". $DB_TEST_FLAG." sl ";
						$sql .= " LEFT JOIN nsa.cus_comm_code_full". $DB_TEST_FLAG." cc ";
						$sql .= " on sl.[group] = cc.comm_code ";
						if ($groupFilter != "--ALL--") {
							$sql .= " WHERE sl.[group] = '".$groupFilter."' ";
						}
						$sql .= " ORDER BY sl.[group] asc";
						QueryDatabase($sql, $results);
						
						while ($row = mssql_fetch_assoc($results)) {
							$ret .= "<tr class='blueHeader'>\n";
							$ret .= "	<th>".$row["group"]."</th>\n";
							$ret .= "	<th colspan=6>".stripNonANChars($row["comm_desc_full"])."</th>\n";
							$ret .= "</tr>\n";

							$ret .= "<tr>\n";
							$ret .= "	<th>Item</th>\n";
							$ret .= "	<th>Group</th>\n";
							$ret .= "	<th>Sub Group</th>\n";
							$ret .= "	<th>Sort</th>\n";
							$ret .= "	<th>ADV</th>\n";
							$ret .= "	<th>Reorder Point</th>\n";
							$ret .= "	<th>Write Off</th>\n";
							$ret .= "</tr>\n";

							$sql1  = "SELECT ";
							$sql1 .= " sl.ID_ITEM, ";
							$sql1 .= " sl.[GROUP], ";
							$sql1 .= " ltrim(sl.SUB_GROUP) as SUB_GROUP, ";
							$sql1 .= " sl.SORT, ";
							$sql1 .= " sl.ADV, ";
							$sql1 .= " sl.rowid as sl_rowid, ";
							$sql1 .= " ro.LEVEL_ROP, ";
							$sql1 .= " ro.rowid as ro_rowid, ";
							$sql1 .= " ib.CODE_USER_3_IM, ";
							$sql1 .= " ib.rowid as ib_rowid ";
							$sql1 .= " FROM nsa.ITMMAS_STK_LIST". $DB_TEST_FLAG." sl ";
							$sql1 .= " LEFT JOIN nsa.ITMMAS_REORD". $DB_TEST_FLAG." ro ";
							$sql1 .= " on sl.ID_ITEM = ro.ID_ITEM ";
							$sql1 .= " LEFT JOIN nsa.ITMMAS_BASE". $DB_TEST_FLAG." ib ";
							$sql1 .= " on sl.ID_ITEM = ib.ID_ITEM ";
							$sql1 .= " WHERE sl.[GROUP] = '".$row["group"]."' ";
							$sql1 .= " ORDER BY sl.[GROUP] asc, sl.SORT, sl.ID_ITEM asc ";
							QueryDatabase($sql1, $results1);
							
							while ($row1 = mssql_fetch_assoc($results1)) {
								$ret .= "<tr'>\n";
								$ret .= "	<td>".$row1["ID_ITEM"]."</td>\n";
								$ret .= "	<td id='GROUP__" . $row1['sl_rowid']."' onDblClick=\"showEditField(this.id)\">".$row1["GROUP"]."</td>\n";
								$ret .= "	<td id='SUB_GROUP__" . $row1['sl_rowid']."' onDblClick=\"showEditField(this.id)\">".$row1["SUB_GROUP"]."</td>\n";
								$ret .= "	<td id='SORT__" . $row1['sl_rowid']."' onDblClick=\"showEditField(this.id)\">".$row1["SORT"]."</td>\n";
								$ret .= "	<td id='ADV__" . $row1['sl_rowid']."' onDblClick=\"showEditField(this.id)\">".$row1["ADV"]."</td>\n";
								$ret .= "	<td id='LEVEL_ROP__" . $row1['ro_rowid']."' onDblClick=\"showEditField(this.id)\">".$row1["LEVEL_ROP"]."</td>\n";
								$ret .= "	<td id='CODE_USER_3_IM__" . $row1['ib_rowid']."' onDblClick=\"showEditField(this.id)\">".$row1["CODE_USER_3_IM"]."</td>\n";
								$ret .= "	<td>DEL</td>\n";
								$ret .= "</tr>\n";
							}
						}
						$ret .= "</table>\n";
					}
				break;

				case("showedit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = trim($_POST['field_value']);

						error_log("FieldValue:'".$FieldValue."'");

						$ret .= " 		<input id='" . $FieldID . "_TXT' type='text' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
					}//end if
				break;

				case("canceledit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$ret .= $FieldValue;
					}//end if
				break;


				case("saveedit");
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

						if ($field == 'GROUP' || $field == 'SUB_GROUP' || $field == 'SORT' || $field == 'ADV') {
							$sqlu  = "UPDATE nsa.ITMMAS_STK_LIST". $DB_TEST_FLAG." ";
							$sqlu .= " set [" . $field . "] = '" . $StrippedFieldValue . "', ";
							$sqlu .= " ID_USER_CHG = '".$UserRow['ID_USER']."', ";
							$sqlu .= " DATE_CHG = GetDate() ";
							$sqlu .= " WHERE rowid = " . $rowid;
							//QueryDatabase($sqlu, $resultsu);
							error_log($sqlu);
						}

						if ($field == 'LEVEL_ROP') {
							$sqlu  = "UPDATE nsa.ITMMAS_REORD". $DB_TEST_FLAG." ";
							$sqlu .= " set [" . $field . "] = '" . $StrippedFieldValue . "', ";
							$sqlu .= " ID_USER_CHG = '".$UserRow['ID_USER']."', ";
							$sqlu .= " DATE_CHG = GetDate() ";
							$sqlu .= " WHERE rowid = " . $rowid;
							//QueryDatabase($sqlu, $resultsu);
							error_log($sqlu);
						}						

						$ret .= $StrippedFieldValue;

					}//end if
				break;


/*

				///////////////////////////////////////////////////
				////INSERT NEW RECORD INTO SQL
				///////////////////////////////////////////////////
				case("insert_record");

					if (isset($_POST["id_item"]) && isset($_POST["group"]) && isset($_POST["sub_group"]) && isset($_POST["sort"]) && isset($_POST["adv"]) && isset($_POST["wo"]) && isset($_POST["source"])) {
						

						$id_item = $_POST["id_item "];
						$group = $_POST["group "];
						$sub_group = $_POST["sub_group "];
						$sort = $_POST["sort "];
						$adv = $_POST["adv "];
						$wo = $_POST["wo "];
						$source = $_POST["source"];



						$sql  = "INSERT INTO nsa.ITMMAS_STK_LIST ( ";
						$sql .= " USER_ADD, ";
						$sql .= " DATE_ADD, ";
						$sql .= " ID_ITEM, ";
						$sql .= " [GROUP], ";
						$sql .= " SUB_GROUP, ";
						$sql .= " [SORT], ";
						$sql .= " ADV, ";
						$sql .= " WO, ";
						$sql .= " SOURCE, ";
						$sql .= " FLAG_DEL ";
						$sql .= " ) VALUES ( ";
						$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
						$sql .= " GetDate(), ";
						$sql .= " '" . ms_escape_string($id_item) . "', ";
						$sql .= " '" . ms_escape_string($group) . "', ";
						$sql .= " '" . ms_escape_string($sub_group) . "', ";
						$sql .= " '" . ms_escape_string($sort) . "', ";
						$sql .= " '" . ms_escape_string($adv) . "', ";
						$sql .= " '" . ms_escape_string($wo) . "', ";
						$sql .= " '" . ms_escape_string($source) . "', ";
						$sql .= " '' ";
						$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";

						error_log($sql);
						QueryDatabase($sql, $results);
						$row = mssql_fetch_assoc($results);
						$BaseRowID = $row['LAST_INSERT_ID'];
						
						$ret = refreshRecords();

					}//end if insert new record into SQl


				break;


				case("refresh_record");
					if (isset($_POST["refreshRec"])) {
						$ret = refreshRecords();
						error_log($ret);
					}//end if
				break;

				

				case("deleteRecord");	
					if (isset($_POST["deleteRecord"]) && isset($_POST["rowid"])) {
						$ROWID = $_POST["rowid"];

						$sqlDel = "update nsa.ITMMAS_STK_LIST set FLAG_DEL = 'Y', DATE_CHG = getdate(), USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
						QueryDatabase($sqlDel, $resultsDel);
			

						$ret .= "DELETED";
						$ret = refreshRecords();
					}
				break;	
				refreshRecords();
*/
			}//end switch

			echo json_encode(array("returnValue"=> $ret));
			
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	} 

	function refreshRecords(){

		location.reload();
			}
	?>