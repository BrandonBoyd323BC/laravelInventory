<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	require_once("../classes/mail.class.php");
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

			$action = $_POST["action"];

			switch($action){
				case "show_location_change";
					if (isset($_POST["show_location"]))  {
						$ShowLocation	= $_POST["show_location"];

						$ret .= "				<select id='filterTeam' onChange=\"showStatusChange()\">";
						$ret .= "					<option value='ALL'>-- ALL --</option>\n";
						$sqlT = "SELECT DISTINCT ltrim(mm.ID_BADGE_TEAM) as ID_BADGE_TEAM, de.NAME_EMP,";
						$sqlT .= " CASE ";
						$sqlT .= " 		WHEN de.NAME_EMP is null THEN ID_BADGE_TEAM";
						$sqlT .= " 		else (ltrim(mm.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP)";
						$sqlT .= " END as BADGETEAM";
						$sqlT .= " FROM nsa.MAINT_MACHINERY mm";
						$sqlT .= " left join nsa.DCEMMS_EMP de";
						$sqlT .= " on ltrim(mm.ID_BADGE_TEAM) = ltrim(de.ID_BADGE)";
						$sqlT .= " WHERE mm.LOCATION = '".$ShowLocation."'";
						$sqlT .= " ORDER BY ID_BADGE_TEAM";
						QueryDatabase($sqlT, $resultsT);
						while ($rowT = mssql_fetch_assoc($resultsT)) {
							$ret .= "					<option value='" . $rowT['ID_BADGE_TEAM'] . "'>" . $rowT['BADGETEAM'] . "</option>\n";
						}
						$ret .= " 			</select>\n";

					}//end if
				break;
		
				case "show_add_team_dropdown_list";
					if (isset($_POST["add_location"]))  {
						$AddLocation	= $_POST["add_location"];

						$ret .= "				<select id='add_ID_BADGE_TEAM' onChange=\"checkIfNewTeamSelected(this.id)\">";

						$sqlT = "SELECT DISTINCT ltrim(mm.ID_BADGE_TEAM) as ID_BADGE_TEAM, de.NAME_EMP,";
						$sqlT .= " CASE ";
						$sqlT .= " 		WHEN de.NAME_EMP is null THEN ID_BADGE_TEAM";
						$sqlT .= " 		else (ltrim(mm.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP)";
						$sqlT .= " END as BADGETEAM";
						$sqlT .= " FROM nsa.MAINT_MACHINERY mm";
						$sqlT .= " left join nsa.DCEMMS_EMP de";
						$sqlT .= " on ltrim(mm.ID_BADGE_TEAM) = ltrim(de.ID_BADGE)";
						$sqlT .= " WHERE mm.LOCATION = '".$AddLocation."'";
						$sqlT .= " ORDER BY ID_BADGE_TEAM";
						QueryDatabase($sqlT, $resultsT);
						while ($rowT = mssql_fetch_assoc($resultsT)) {
							$ret .= "					<option value='" . $rowT['ID_BADGE_TEAM'] . "'>" . $rowT['BADGETEAM'] . "</option>\n";
						}
						$ret .= "					<option value='NEW'>-- NEW --</option>\n";
						$ret .= " 			</select>\n";

					}//end if
				break;

				case "show_add_new_team_textbox";
					//if (isset($_POST["add_location"]))  {
					//	$AddLocation	= $_POST["add_location"];
						$ret .= " 		<input size=10 type=text id='add_ID_BADGE_TEAM'>\n";
					//}//end if
				break;

				case "show_status";
					if (isset($_POST["show_status"]))  {
						$SortField = "ID_MACH";
						$SortDir = "asc";
						if (isset($_POST["sort_field"]))  {
							$SortField = $_POST["sort_field"];
						}
						if (isset($_POST["sort_dir"]))  {
							$SortDir = $_POST["sort_dir"];
						}

						$ShowStatus	= $_POST["show_status"];
						$ShowLocation	= $_POST["show_location"];
						$ShowTeam	= $_POST["show_team"];

						$sql  = "SELECT DISTINCT";
						//$sql .= " mm.*, ";
						$sql .=	" CASE ";
						$sql .= "  WHEN de.NAME_EMP is null THEN ID_BADGE_TEAM";
						$sql .= "  ELSE (ltrim(mm.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP)";
						$sql .= " END as BADGETEAM, ";
						

						$sql .= " mm.ID_MACH, ";
						$sql .= " mm.ID_BADGE_TEAM, ";
						$sql .= " mm.HEAD_BRAND, ";
						$sql .= " mm.HEAD_TYPE, ";
						$sql .= " mm.HEAD_SN, ";
						$sql .= " mm.MODEL_NUM, ";
						$sql .= " mm.ASSET_NUM, ";
						$sql .= " mm.STATUS, ";
						$sql .= " mm.COMMENTS, ";
						$sql .= " mm.PRIORITY, ";
						$sql .= " isnull(mm.LOCATION,'') as LOCATION, ";
						$sql .= " mm.rowid ";
						//$sql .= " mm.*";
						$sql .= " FROM nsa.MAINT_MACHINERY mm ";
						$sql .= " LEFT JOIN nsa.DCEMMS_EMP de";
						$sql .= " on ltrim(mm.ID_BADGE_TEAM) = ltrim(de.ID_BADGE)";
						$sql .= " WHERE 1=1 ";
						if ($ShowStatus <> 'ALL') {
							$sql .= "and mm.STATUS = '" . $ShowStatus . "' ";
						}
						if ($ShowLocation <> 'ALL') {
							$sql .= "and mm.LOCATION = '" . $ShowLocation . "' ";
						}
						if ($ShowTeam <> 'ALL') {
							$sql .= "and mm.ID_BADGE_TEAM = '" . $ShowTeam . "' ";
						}	
						$sql .= "ORDER BY " . $SortField . " " . $SortDir;
						//error_log($sql);
						QueryDatabase($sql, $results);

						$sqlS  = "SELECT CODE_STATUS, DESCR ";//pull in machine status from status table for drop downs
						$sqlS .= " FROM nsa.MAINT_STATUS ";
						$sqlS .= " ORDER BY CODE_STATUS asc ";
						//error_log($sqlS);
						QueryDatabase($sqlS, $resultsS);
						while ($rowS = mssql_fetch_assoc($resultsS)) {
							$a_maint_stats[$rowS['CODE_STATUS']] = ltrim($rowS['DESCR']);
						}

						$prevrowId = '';
						$b_flip = true;

						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' colspan=12>Add New Record</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample'>Machine ID</th>\n";
						$ret .= " 		<th class='sample'>Location</th>\n";
						$ret .= " 		<th class='sample'>Team</th>\n";
						$ret .= " 		<th class='sample'>Head Brand</th>\n";
						$ret .= " 		<th class='sample'>Head Type</th>\n";
						$ret .= " 		<th class='sample'>Head SN</th>\n";
						$ret .= " 		<th class='sample'>Model #</th>\n";
						$ret .= " 		<th class='sample'>Priority</th>\n";
						$ret .= " 		<th class='sample'>Comments</th>\n";
						$ret .= " 		<th class='sample'>Status</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_ID_MACH'></td>\n";
						$ret .= " 		<td class='sample'>\n";
						$ret .= "			<select id='add_LOCATION' onChange=\"getAddTeamDropdownList(this.id)\">\n";
						$ret .= "				<option value='' SELECTED>--SELECT--</option>\n";
						$ret .= "				<option value='CLEVELAND'>Cleveland</option>\n";
						$ret .= "				<option value='CHICAGO'>Chicago</option>\n";
						$ret .= "				<option value='ARKANSAS'>Arkansas</option>\n";
						$ret .= "				<option value='KANSAS'>Kansas</option>\n";
						$ret .= "				<option value='BELLEVILLE'>Belleville</option>\n";
						$ret .= "			</select>\n";
						$ret .= "		</td>\n";
						//$ret .= " 		<td class='sample'><input size=10 type=text id='add_ID_BADGE_TEAM'></td>\n";



						$ret .= " 		<td class='sample' id='td_addTeamDropdownList'>";
						$ret .= "			<select id='add_ID_BADGE_TEAM'>";

						$ret .= "			</select>\n";
						$ret .= "		</td>\n";


						$ret .= " 		<td class='sample'><input size=10 type=text id='add_HEAD_BRAND'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_HEAD_TYPE'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_HEAD_SN'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_MODEL_NUM'></td>\n";
						$ret .= " 		<td class='sample'>\n";
						$ret .= "			<select id='add_PRIORITY'>\n";
						$ret .= "				<option value='' SELECTED>Normal</option>\n";
						$ret .= "				<option value='CRITICAL'>CRITICAL</option>\n";
						$ret .= "			</select>\n";
						$ret .= "		</td>\n";

						$ret .= " 		<td class='sample'><input size=10 type=text id='add_COMMENTS'></td>\n";
						$ret .= " 		<td class='sample'>\n";
						$ret .= "			<select id='add_STATUS'>\n";
						foreach ($a_maint_stats as $code_status => $descr) {
							$ret .= "				<option value='" . $code_status . "'>" . $descr . "</option>\n";
						}
						$ret .= "			</select>\n";
						$ret .= "		</td>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= "		<th class='sample' colspan=12> <input type='button' id='btnInsertRecord' value='Add Record' onClick='insertNewRecord()'></input>\n";

						$ret .= " 	<tr>\n";
						$ret .= " 		<th colspan=12>Inventory</th>\n";
						$ret .= " 	</tr>\n";

						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='ID_MACH' onClick=\"sortBy(this.id)\">Machine ID</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='LOCATION' onClick=\"sortBy(this.id)\">Location</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='ID_BADGE_TEAM' onClick=\"sortBy(this.id)\">Team</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='HEAD_BRAND' onClick=\"sortBy(this.id)\">Head Brand</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='HEAD_TYPE' onClick=\"sortBy(this.id)\">Head Type</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='HEAD_SN' onClick=\"sortBy(this.id)\">Head SN</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='MODEL_NUM' onClick=\"sortBy(this.id)\">Model #</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='PRIORITY' onClick=\"sortBy(this.id)\">Priority</th>\n";
						$ret .= " 		<th class='sample' style='cursor:pointer;' id='COMMENTS' onClick=\"sortBy(this.id)\">Comments</th>\n";
						$ret .= " 		<th class='sample' id='STATUS' onClick=\"sortBy(this.id)\">Status</th>\n";
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
							$ret .= " 		<td class='" . $trClass . "' id='ID_MACH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['ID_MACH'] . "'>" . $row['ID_MACH'] . "</td>\n";



							$ret .= " 		<td class='" . $trClass . "' id='LOCATION__" . $row['rowid']."'>\n";
							$ret .= "			<select class='" . $trClass . "' onChange=\"saveLocation(this.id)\" id='SEL_LOCATION__" . $row['rowid'] ."'>\n";
							
							$SELECTED_CLEVELAND = '';
							$SELECTED_CHICAGO = '';
							$SELECTED_ARKANSAS = '';
							$SELECTED_KANSAS = '';
							$SELECTED_BELLEVILLE = '';

							if (trim($row['LOCATION']) == 'CLEVELAND') {
								$SELECTED_CLEVELAND = 'SELECTED';
								$SELECTED_CHICAGO = '';
								$SELECTED_ARKANSAS = '';
								$SELECTED_KANSAS = '';
								$SELECTED_BELLEVILLE = '';
							}
							if (trim($row['LOCATION']) == 'CHICAGO') {
								$SELECTED_CLEVELAND = '';
								$SELECTED_CHICAGO = 'SELECTED';
								$SELECTED_ARKANSAS = '';
								$SELECTED_KANSAS = '';
								$SELECTED_BELLEVILLE = '';	
							}
							if (trim($row['LOCATION']) == 'ARKANSAS') {
								$SELECTED_CLEVELAND = '';
								$SELECTED_CHICAGO = '';
								$SELECTED_ARKANSAS = 'SELECTED';
								$SELECTED_KANSAS = '';
								$SELECTED_BELLEVILLE = '';	
							}
							if (trim($row['LOCATION']) == 'KANSAS') {
								$SELECTED_CLEVELAND = '';
								$SELECTED_CHICAGO = '';
								$SELECTED_ARKANSAS = '';
								$SELECTED_KANSAS = 'SELECTED';
								$SELECTED_BELLEVILLE = '';	
							}
							if (trim($row['LOCATION']) == 'BELLEVILLE') {
								$SELECTED_CLEVELAND = '';
								$SELECTED_CHICAGO = '';
								$SELECTED_ARKANSAS = '';
								$SELECTED_KANSAS = '';
								$SELECTED_BELLEVILLE = 'SELECTED';	
							}

							$ret .= "				<option class='" . $trClass . "' value='' " . $SELECTED_CLEVELAND . ">--SELECT--</option>\n";
							$ret .= "				<option class='" . $trClass . "' value='CLEVELAND' " . $SELECTED_CLEVELAND . ">Cleveland</option>\n";
							$ret .= "				<option class='" . $trClass . "' value='CHICAGO' " . $SELECTED_CHICAGO . ">Chicago</option>\n";
							$ret .= "				<option class='" . $trClass . "' value='ARKANSAS' " . $SELECTED_ARKANSAS . ">Arkansas</option>\n";
							$ret .= "				<option class='" . $trClass . "' value='KANSAS' " . $SELECTED_KANSAS . ">Kansas</option>\n";
							$ret .= "				<option class='" . $trClass . "' value='Belleville' " . $SELECTED_BELLEVILLE . ">Belleville</option>\n";
							$ret .= "			</select>\n";
							$ret .= "		</td>\n";






							$ret .= " 		<td class='" . $trClass . "' id='ID_BADGE_TEAM__" . $row['rowid']."' onDblClick=\"showEditSelect(this.id)\" value='" . $row['BADGETEAM'] . "'>" . $row['BADGETEAM'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='HEAD_BRAND__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['HEAD_BRAND'] . "'>" . $row['HEAD_BRAND'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='HEAD_TYPE__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['HEAD_TYPE'] . "'>" . $row['HEAD_TYPE'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='HEAD_SN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['HEAD_SN'] . "'>" . $row['HEAD_SN'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='MODEL_NUM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MODEL_NUM'] . "'>" . $row['MODEL_NUM'] . "</td>\n";


							$ret .= " 		<td class='" . $trClass . "' id='PRIORITY__" . $row['rowid']."'>\n";
							$ret .= "			<select class='" . $trClass . "' onChange=\"savePriority(this.id)\" id='SEL_PRIORITY__" . $row['rowid'] ."'>\n";
							
							if (trim($row['PRIORITY']) == '') {
								$SELECTED_Normal = 'SELECTED';
								$SELECTED_Critical = '';
							}
							if (trim($row['PRIORITY']) == 'CRITICAL') {
								$SELECTED_Normal = '';
								$SELECTED_Critical = 'SELECTED';
							}

							$ret .= "				<option class='" . $trClass . "' value='' " . $SELECTED_Normal . ">Normal</option>\n";
							$ret .= "				<option class='" . $trClass . "' value='CRITICAL' " . $SELECTED_Critical . ">CRITICAL</option>\n";
							$ret .= "			</select>\n";
							$ret .= "		</td>\n";


							$ret .= " 		<td class='" . $trClass . "' id='COMMENTS__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['COMMENTS'] . "'>" . $row['COMMENTS'] . "</td>\n";	
							$ret .= " 		<td class='" . $trClass . "' id='STATUS__" . $row['rowid']."'>\n";
							$ret .= "			<select class='" . $trClass . "' onChange=\"saveStatus(this.id)\" id='SEL_STATUS__" . $row['rowid'] ."'>\n";
							foreach ($a_maint_stats as $code_status => $descr) {
								$SELECTED = '';
								$CURRENT = '';
								if (trim($row['STATUS']) == trim($code_status)) {
									$SELECTED = 'SELECTED';
									$CURRENT = '*';
								}
								$ret .= "				<option class='" . $trClass . "' value='" . $CURRENT . $code_status . "' " . $SELECTED . ">" . $CURRENT . $descr . "</option>\n";
							}
							$ret .= "			</select>\n";
							$ret .= "		</td>\n";
							$ret .= " 	</tr>\n";
						}
						$ret .= " 	<tr>\n";
						$ret .= " 	</tr>\n";
						$ret .= " </table>\n";
						$ret .= " </br>\n";
						$ret .= " </br>\n";
					}//end if
				break;

				case("show_edit_select");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];

						error_log("FieldID: ".$FieldID);
						error_log("FieldValue: ".$FieldValue);

						if ((substr(trim($FieldID),0,15) == "ID_BADGE_TEAM__") && (is_numeric(substr(trim($FieldValue),0,3)))) {
							$FieldValue = substr(trim($FieldValue),0,3);
							error_log("FieldValue: ".$FieldValue);
						}

						$ret .= " 		<input type='text' id='" . $FieldID . "_TXT' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
					}//end if
				break;

				case("showedit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];

						error_log("FieldID: ".$FieldID);
						error_log("FieldValue: ".$FieldValue);

						if ((substr(trim($FieldID),0,15) == "ID_BADGE_TEAM__") && (is_numeric(substr(trim($FieldValue),0,3)))) {
							$FieldValue = substr(trim($FieldValue),0,3);
							error_log("FieldValue: ".$FieldValue);
						}

						$ret .= " 		<input type='text' id='" . $FieldID . "_TXT' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
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

						$sqlu = "UPDATE nsa.MAINT_MACHINERY set " . $field . " = ltrim('" . $StrippedFieldValue . "') where rowid = " . $rowid;
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


				///////////////////////////////////////////////////
				////INSERT NEW RECORD INTO SQL
				///////////////////////////////////////////////////
				case("insert_record");
					if (isset($_POST["machID"]) && isset($_POST["status"]) && isset($_POST["location"]) && isset($_POST["badge"]) && isset($_POST["headBrand"]) && isset($_POST["headType"]) && isset($_POST["headSn"]) && isset($_POST["modelNum"]) && isset($_POST["priority"]) && isset($_POST["comments"]))
					{
						$machID = $_POST["machID"];
						$badge = $_POST["badge"];
						$headBrand = $_POST["headBrand"];
						$headType = $_POST["headType"];
						$headSn = $_POST["headSn"];
						$modelNum = $_POST["modelNum"];
						$priority = $_POST["priority"];
						$status = $_POST["status"];
						$location = $_POST["location"];
						$show_status = $_POST["show_status"];
						$show_location = $_POST["show_location"];
						$show_team = $_POST["show_team"];
						$comments = $_POST["comments"];

						$sql = " INSERT INTO nsa.MAINT_MACHINERY (";
						$sql .= " USER_ADD,  ";
						$sql .= " DATE_ADD, ";
						$sql .= " ID_MACH, ";
						$sql .= " LOCATION, ";
						$sql .= " ID_BADGE_TEAM, ";
						$sql .= " HEAD_BRAND, ";
						$sql .= " HEAD_TYPE, ";
						$sql .= " HEAD_SN, ";
						$sql .= " MODEL_NUM, ";
						$sql .= " PRIORITY, ";
						$sql .= " COMMENTS, ";
						if($status == 'D'){
							$sql .= " DATE_DECOM, ";
						}
						$sql .= " STATUS";
						$sql .= " )VALUES(";
						$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
						$sql .= " GetDate(), ";
						$sql .= " '" . ms_escape_string($machID) . "', ";
						$sql .= " '" . ms_escape_string($location) . "', ";
						$sql .= " '" . ms_escape_string($badge) . "', ";
						$sql .= " '" . ms_escape_string($headBrand) . "', ";
						$sql .= " '" . ms_escape_string($headType) . "', ";
						$sql .= " '" . ms_escape_string($headSn) . "', ";
						$sql .= " '" . ms_escape_string($modelNum) . "', ";
						$sql .= " '" . ms_escape_string($priority) . "', ";
						$sql .= " '" . ms_escape_string($comments) . "', ";
						if($status == 'D'){
							$sql .= " GetDate(), ";
						}
						$sql .= " '" . ms_escape_string($status) . "' ";
						$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";

						error_log($sql);
						QueryDatabase($sql, $results);
						$row = mssql_fetch_assoc($results);
						$BaseRowID = $row['LAST_INSERT_ID'];
						
						$ret = refreshRecords($show_status,$show_team,$show_location);

					}//end if insert new record into SQl
				break;


				case("refresh_record");
					if (isset($_POST["refreshRec"])) {
						$ret = refreshRecords();
						error_log($ret);
					}
				break;

				case("saveStatus");
					$ShowStatus = $_POST["show_status"];
					$ShowTeam = $_POST["show_team"];

					if (isset($_POST["newStatus"]) )  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$newStatus = $_POST["newStatus"];

						$StrippedFieldValue = stripIllegalChars2($FieldValue);
						$vals = explode("__", $FieldID);
						$field = $vals[0];
						$rowid = $vals[1];

						$sql = "UPDATE nsa.MAINT_MACHINERY set";
						$sql .=" STATUS = '" . $newStatus . "', ";
						if($newStatus == 'D'){//when decominssioning a machine, set the date for finance purposes
							$sql .= " DATE_DECOM = GetDate(), ";
						}
						$sql .= " DATE_CHG = GetDate(), ";
						$sql .= " USER_CHG = '" . stripIllegalChars2($UserRow['ID_USER']) . "' ";
						$sql .=" where rowid = " . $rowid;
						error_log($sql);
						QueryDatabase($sql, $results);

						$sql2 = "SELECT mm.*, ms.* ";
						$sql2 .= "FROM nsa.MAINT_MACHINERY mm";
						$sql2 .= " left join nsa.MAINT_STATUS ms";
						$sql2 .= " on mm.STATUS = ms.CODE_STATUS";
						$sql2 .= " WHERE mm.rowid = " . $rowid;
						error_log($sql2);
						QueryDatabase($sql2, $results2);
						while ($row2 = mssql_fetch_assoc($results2)) {

							if ($TEST_ENV) {
								$head = array(
									'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
									'from'    =>array('MACHINEINVENTORY@thinkNSA.com' =>'NSA'),
						       );
							} else {
								$head = array(
							       'to'      =>array('ddesvari@thinknsa.com'=>'Dave Desvari'),//email address to send report to
							       'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
							       'from'    =>array('MACHINEINVENTORY@thinkNSA.com' =>'NSA'),
							       );
							}
							$subject = date("m.d.y")." Status of a fixed asset has changed!!!!";
							$body ='';
							$body.="<div style='font-family:Arial;font-size:10pt;'>";
							$body.=    "<br>"."Hello accountant friends,";
							$body.=    "<br>"."";
							$body.=    "<br>"."On " . $row2['DATE_CHG'] . " the status of a fixed asset " . $row2['ASSET_NUM'] . " changed to " . $row2['DESCR'] . ".  We thought you would like to know.";
							$body.=    "<br>"."";
							$body.=    "<br>"."-NSA IT Team";
							$body.="</div>";
							 
							mail::send($head,$subject,$body);
						}//end while
						//$ret .= $StrippedFieldValue; //maybe not needed?
					}//end if

					$ret = refreshRecords($ShowStatus,$ShowTeam);
				break;

				case("savePriority");
					$ShowStatus = $_POST["show_status"];
					$ShowTeam = $_POST["show_team"];
					if (isset($_POST["newPriority"]) )  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$newPriority = $_POST["newPriority"];

						$StrippedFieldValue = stripIllegalChars2($FieldValue);
						$vals = explode("__", $FieldID);
						$field = $vals[0];
						$rowid = $vals[1];

						$sql = "UPDATE nsa.MAINT_MACHINERY set";
						$sql .=" PRIORITY = '" . $newPriority . "', ";
						$sql .= " DATE_CHG = GetDate(), ";
						$sql .= " USER_CHG = '" . stripIllegalChars2($UserRow['ID_USER']) . "' ";
						$sql .=" where rowid = " . $rowid;
						//error_log($sql);
						QueryDatabase($sql, $results);
					}
					$ret = refreshRecords($ShowStatus,$ShowTeam);
				break;

				case("saveLocation");
					$ShowStatus = $_POST["show_status"];
					$ShowTeam = $_POST["show_team"];
					if (isset($_POST["newLocation"]) )  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$newLocation = $_POST["newLocation"];

						$StrippedFieldValue = stripIllegalChars2($FieldValue);
						$vals = explode("__", $FieldID);
						$field = $vals[0];
						$rowid = $vals[1];

						$sql = "UPDATE nsa.MAINT_MACHINERY set";
						$sql .=" LOCATION = '" . $newLocation . "', ";
						$sql .= " DATE_CHG = GetDate(), ";
						$sql .= " USER_CHG = '" . stripIllegalChars2($UserRow['ID_USER']) . "' ";
						$sql .=" where rowid = " . $rowid;
						//error_log($sql);
						QueryDatabase($sql, $results);
					}
					$ret = refreshRecords($ShowStatus,$ShowTeam);
				break;

			}//end switch

			echo json_encode(array("returnValue"=> $ret));
			
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

	function refreshRecords($ShowStatus,$ShowTeam,$ShowLocation){

		global $ret;

	
		$SortField = "ID_MACH";
		$SortDir = "asc";
		if (isset($_POST["sort_field"]))  {
			$SortField = $_POST["sort_field"];
		}
		if (isset($_POST["sort_dir"]))  {
			$SortDir = $_POST["sort_dir"];
		}
		
		$sql  = "SELECT DISTINCT";
		$sql .=	" CASE ";
		$sql .= "  WHEN de.NAME_EMP is null THEN ID_BADGE_TEAM";
		$sql .= "  ELSE (ltrim(mm.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP)";
		$sql .= " END as BADGETEAM, mm.*";
		$sql .= " FROM nsa.MAINT_MACHINERY mm ";
		$sql .= " LEFT JOIN nsa.DCEMMS_EMP de";
		$sql .= " on ltrim(mm.ID_BADGE_TEAM) = ltrim(de.ID_BADGE)";
		if ($ShowStatus <> 'ALL') {
			$sql .= "where mm.STATUS = '" . $ShowStatus . "' ";
		} else {
			$sql .= "where mm.STATUS like '" . $ShowStatus . "' ";
		}
		if ($ShowLocation <> 'ALL') {
			$sql .= "and mm.LOCATION = '" . $ShowLocation . "' ";
		}	
		if ($ShowTeam <> 'ALL') {
			$sql .= "and mm.ID_BADGE_TEAM = '" . $ShowTeam . "' ";
		}	
		$sql .= "ORDER BY " . $SortField . " " . $SortDir;
		error_log($sql);
		QueryDatabase($sql, $results);

		$sqlS  = "SELECT ";
		$sqlS .= " ms.* ";
		$sqlS .= "from nsa.MAINT_STATUS ms ";
		$sqlS .= "ORDER BY CODE_STATUS asc ";
		error_log($sqlS);
		QueryDatabase($sqlS, $resultsS);
		while ($rowS = mssql_fetch_assoc($resultsS)) {
			$a_maint_stats[$rowS['CODE_STATUS']] = ltrim($rowS['DESCR']);
		}

		$prevrowId = '';
		$b_flip = true;

		$ret .= " <table class='sample'>\n";
		$ret .= " 	<tr>\n";
		$ret .= " 		<th class='sample' colspan=12>Add New Record</th>\n";
		$ret .= " 	</tr>\n";
		$ret .= " 	<tr>\n";
		$ret .= " 		<th class='sample'>Machine ID</th>\n";
		$ret .= " 		<th class='sample'>Location</th>\n";
		$ret .= " 		<th class='sample'>Team</th>\n";
		$ret .= " 		<th class='sample'>Head Brand</th>\n";
		$ret .= " 		<th class='sample'>Head Type</th>\n";
		$ret .= " 		<th class='sample'>Head SN</th>\n";
		$ret .= " 		<th class='sample'>Model #</th>\n";
		$ret .= " 		<th class='sample'>Priority</th>\n";
		$ret .= " 		<th class='sample'>Comments</th>\n";
		$ret .= " 		<th class='sample'>Status</th>\n";
		$ret .= " 	</tr>\n";
		$ret .= " 	<tr>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_ID_MACH'></td>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_LOCATION'></td>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_ID_BADGE_TEAM'></td>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_HEAD_BRAND'></td>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_HEAD_TYPE'></td>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_HEAD_SN'></td>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_MODEL_NUM'></td>\n";
		$ret .= " 		<td class='sample'>\n";
		$ret .= "			<select class='sample' id='add_PRIORITY'>\n";
		$ret .= "				<option value='' SELECTED>Normal</option>\n";
		$ret .= "				<option value='CRITICAL'>CRITICAL</option>\n";
		$ret .= "			</select>\n";
		$ret .= "		</td>\n";
		$ret .= " 		<td class='sample'><input size=10 type=text id='add_COMMENTS'></td>\n";
		$ret .= " 		<td class='sample'>\n";
		$ret .= "			<select  id='add_STATUS'>\n";
		foreach ($a_maint_stats as $code_status => $descr) {
			$ret .= "				<option value='" . $code_status . "'>" . $descr . "</option>\n";
		}
		$ret .= "			</select>\n";
		$ret .= "		</td>\n";
		$ret .= " 	</tr>\n";
		$ret .= " 	<tr>\n";
		$ret .= "	<th class='sample' colspan=12> <input type='button' id='btnInsertRecord' value='Make It Sew' onClick='insertNewRecord()'></input>\n";
		$ret .= " 	</tr>\n";
		$ret .= " 	<tr>\n";
		$ret .= " 		<th colspan=12>Inventory</th>\n";
		$ret .= " 	</tr>\n";
		$ret .= " 	<tr>\n";
		$ret .= " 		<th class='sample' id='ID_MACH' onClick=\"sortBy(this.id)\">Machine ID</th>\n";
		$ret .= " 		<th class='sample' id='LOCATION' onClick=\"sortBy(this.id)\">Location</th>\n";
		$ret .= " 		<th class='sample' id='ID_BADGE_TEAM' onClick=\"sortBy(this.id)\">Team</th>\n";
		$ret .= " 		<th class='sample' id='HEAD_BRAND' onClick=\"sortBy(this.id)\">Head Brand</th>\n";
		$ret .= " 		<th class='sample' id='HEAD_TYPE' onClick=\"sortBy(this.id)\">Head Type</th>\n";
		$ret .= " 		<th class='sample' id='HEAD_SN' onClick=\"sortBy(this.id)\">Head SN</th>\n";
		$ret .= " 		<th class='sample' id='MODEL_NUM' onClick=\"sortBy(this.id)\">Model #</th>\n";
		$ret .= " 		<th class='sample' id='PRIORITY' onClick=\"sortBy(this.id)\">Priority</th>\n";
		$ret .= " 		<th class='sample' id='COMMENTS' onClick=\"sortBy(this.id)\">Comments</th>\n";
		$ret .= " 		<th class='sample' id='STATUS' onClick=\"sortBy(this.id)\">Status</th>\n";
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
			$ret .= " 		<td class='" . $trClass . "' id='ID_MACH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['ID_MACH'] . "'>" . $row['ID_MACH'] . "</td>\n";




			$ret .= " 		<td class='" . $trClass . "' id='LOCATION__" . $row['rowid']."'>\n";
			$ret .= "			<select class='" . $trClass . "' onChange=\"saveLocation(this.id)\" id='SEL_LOCATION__" . $row['rowid'] ."'>\n";
			
			$SELECTED_CLEVELAND = '';
			$SELECTED_CHICAGO = '';
			$SELECTED_ARKANSAS = '';
			$SELECTED_KANSAS = '';
			$SELECTED_BELLEVILLE = '';

			if (trim($row['LOCATION']) == 'CLEVELAND') {
				$SELECTED_CLEVELAND = 'SELECTED';
				$SELECTED_CHICAGO = '';
				$SELECTED_ARKANSAS = '';
				$SELECTED_KANSAS = '';
				$SELECTED_BELLEVILLE = '';
			}
			if (trim($row['LOCATION']) == 'CHICAGO') {
				$SELECTED_CLEVELAND = '';
				$SELECTED_CHICAGO = 'SELECTED';
				$SELECTED_ARKANSAS = '';
				$SELECTED_KANSAS = '';
				$SELECTED_BELLEVILLE = '';	
			}
			if (trim($row['LOCATION']) == 'ARKANSAS') {
				$SELECTED_CLEVELAND = '';
				$SELECTED_CHICAGO = '';
				$SELECTED_ARKANSAS = 'SELECTED';
				$SELECTED_KANSAS = '';
				$SELECTED_BELLEVILLE = '';	
			}
			if (trim($row['LOCATION']) == 'KANSAS') {
				$SELECTED_CLEVELAND = '';
				$SELECTED_CHICAGO = '';
				$SELECTED_ARKANSAS = '';
				$SELECTED_KANSAS = 'SELECTED';
				$SELECTED_BELLEVILLE = '';	
			}
			if (trim($row['LOCATION']) == 'BELLEVILLE') {
				$SELECTED_CLEVELAND = '';
				$SELECTED_CHICAGO = '';
				$SELECTED_ARKANSAS = '';
				$SELECTED_KANSAS = '';
				$SELECTED_BELLEVILLE = 'SELECTED';	
			}

			$ret .= "				<option class='" . $trClass . "' value='' " . $SELECTED_CLEVELAND . ">--SELECT--</option>\n";
			$ret .= "				<option class='" . $trClass . "' value='CLEVELAND' " . $SELECTED_CLEVELAND . ">Cleveland</option>\n";
			$ret .= "				<option class='" . $trClass . "' value='CHICAGO' " . $SELECTED_CHICAGO . ">Chicago</option>\n";
			$ret .= "				<option class='" . $trClass . "' value='ARKANSAS' " . $SELECTED_ARKANSAS . ">Arkansas</option>\n";
			$ret .= "				<option class='" . $trClass . "' value='KANSAS' " . $SELECTED_KANSAS . ">Kansas</option>\n";
			$ret .= "				<option class='" . $trClass . "' value='Belleville' " . $SELECTED_BELLEVILLE . ">Belleville</option>\n";
			$ret .= "			</select>\n";
			$ret .= "		</td>\n";














			
			$ret .= " 		<td class='" . $trClass . "' id='ID_BADGE_TEAM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['BADGETEAM'] . "'>" . $row['BADGETEAM'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='HEAD_BRAND__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['HEAD_BRAND'] . "'>" . $row['HEAD_BRAND'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='HEAD_TYPE__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['HEAD_TYPE'] . "'>" . $row['HEAD_TYPE'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='HEAD_SN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['HEAD_SN'] . "'>" . $row['HEAD_SN'] . "</td>\n";
			$ret .= " 		<td class='" . $trClass . "' id='MODEL_NUM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MODEL_NUM'] . "'>" . $row['MODEL_NUM'] . "</td>\n";


			$ret .= " 		<td class='" . $trClass . "' id='PRIORITY__" . $row['rowid']."'>\n";
			$ret .= "			<select class='" . $trClass . "' onChange=\"savePriority(this.id)\" id='SEL_PRIORITY__" . $row['rowid'] ."'>\n";
			
			if (trim($row['PRIORITY']) == '') {
				$SELECTED_Normal = 'SELECTED';
				$SELECTED_Critical = '';
			}
			if (trim($row['PRIORITY']) == 'CRITICAL') {
				$SELECTED_Normal = '';
				$SELECTED_Critical = 'SELECTED';
			}

			$ret .= "				<option class='" . $trClass . "' value='' " . $SELECTED_Normal . ">Normal</option>\n";
			$ret .= "				<option class='" . $trClass . "' value='CRITICAL' " . $SELECTED_Critical . ">CRITICAL</option>\n";
			$ret .= "			</select>\n";
			$ret .= "		</td>\n";


			$ret .= " 		<td class='" . $trClass . "' id='COMMENTS__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['COMMENTS'] . "'>" . $row['COMMENTS'] . "</td>\n";						
			$ret .= " 		<td class='" . $trClass . "' id='STATUS__" . $row['rowid']."'>\n";
			$ret .= "			<select class='" . $trClass . "' onChange=\"saveStatus(this.id)\" id='SEL_STATUS__" . $row['rowid'] ."'>\n";
			foreach ($a_maint_stats as $code_status => $descr) {
				$SELECTED = '';
				$CURRENT = '';
				if (trim($row['STATUS']) == trim($code_status)) {
					$SELECTED = 'SELECTED';
					$CURRENT = '*';
				}
				$ret .= "				<option class='" . $trClass . "' value='" . $CURRENT . $code_status . "' " . $SELECTED . ">" . $CURRENT . $descr . "</option>\n";
			}
			$ret .= "			</select>\n";
			$ret .= "		</td>\n";
			$ret .= " 	</tr>\n";
		}
		$ret .= " 	<tr>\n";
		$ret .= " 	</tr>\n";

		$ret .= " </table>\n";
		$ret .= " </br>\n";
		$ret .= " </br>\n";

		return $ret;
	
}//end refresh records
?>