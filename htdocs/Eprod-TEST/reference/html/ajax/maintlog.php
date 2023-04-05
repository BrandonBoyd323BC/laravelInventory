<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");
	require_once('../classes/tc_calendar.php');
	

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
			//$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			$action = $_POST["action"];

			switch($action){
				case "location_change";
					if (isset($_POST["location"]))  {
						$Location	= $_POST["location"];

						//$ret .= "				<select id='team' onChange=\"showTeamChange()\">";
						$ret .= "					<option value='ALL'>-- ALL --</option>\n";
						$sqlT = "SELECT DISTINCT ltrim(mm.ID_BADGE_TEAM) as ID_BADGE_TEAM, de.NAME_EMP,";
						$sqlT .= " CASE ";
						$sqlT .= " 		WHEN de.NAME_EMP is null THEN ID_BADGE_TEAM";
						$sqlT .= " 		else (ltrim(mm.ID_BADGE_TEAM) + ' - ' + de.NAME_EMP)";
						$sqlT .= " END as BADGETEAM";
						$sqlT .= " FROM nsa.MAINT_MACHINERY mm";
						$sqlT .= " left join nsa.DCEMMS_EMP de";
						$sqlT .= " on ltrim(mm.ID_BADGE_TEAM) = ltrim(de.ID_BADGE)";
						$sqlT .= " WHERE mm.LOCATION = '".$Location."'";
						$sqlT .= " ORDER BY ID_BADGE_TEAM";
						QueryDatabase($sqlT, $resultsT);
						while ($rowT = mssql_fetch_assoc($resultsT)) {
							$ret .= "					<option value='" . $rowT['ID_BADGE_TEAM'] . "'>" . $rowT['BADGETEAM'] . "</option>\n";
						}
						//$ret .= " 			</select>\n";

						//msTeamsTest($Location);

					}//end if
				break;				

				case "team_change";
					//if (isset($_POST["team_change"])) {
						$Team	= trim($_POST["team_change"]);
						$Location	= trim($_POST["location"]);
						$sql  = "SELECT ";
						//$sql .= " ltrim(ID_MACH) + ' - ' + ID_CLUSTER + ' - ' + HEAD_BRAND + ' - ' + MODEL_NUM as MACH_DESC, ";
						$sql .= " ltrim(ID_MACH) + ' - ' + HEAD_BRAND + ' - ' + MODEL_NUM as MACH_DESC, ";
						$sql .= " ltrim(ID_MACH) as ID_MACH ";
						$sql .= " FROM nsa.MAINT_MACHINERY ";
						$sql .= " WHERE STATUS = 'A' ";
						$sql .= " and LOCATION = '" . $Location . "' ";
						$sql .= " and ltrim(ID_BADGE_TEAM) = '" . $Team . "' ";
						$sql .= " ORDER BY len(ID_MACH) asc, ID_MACH asc";
						error_log($sql);
						QueryDatabase($sql, $results);
						$ret .= "					<option value='SELECT'> -- Select -- </option>\n";
						if (mssql_num_rows($results) > 0) {
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "					<option value='" . $row['ID_MACH'] . "'>" . $row['MACH_DESC'] . "</option>\n";
							}
						} else {
							$sql  = "SELECT ";
							//$sql .= " ltrim(ID_MACH) + ' - ' + ID_CLUSTER + ' - ' + HEAD_BRAND + ' - ' + MODEL_NUM as MACH_DESC, ";
							$sql .= " ltrim(ID_MACH) + ' - ' + HEAD_BRAND + ' - ' + MODEL_NUM as MACH_DESC, ";
							$sql .= " ltrim(ID_MACH) as ID_MACH ";
							$sql .= " FROM nsa.MAINT_MACHINERY ";
							$sql .= " WHERE STATUS = 'A' ";
							$sql .= " and LOCATION = '" . $Location . "' ";
							$sql .= " ORDER BY len(ID_MACH) asc, ID_MACH asc";
							error_log($sql);
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "					<option value='" . $row['ID_MACH'] . "'>" . $row['MACH_DESC'] . "</option>\n";
							}
						}
					//}
				break;

			//////////////////////////////////////////////
			//////////INSERT NEW RECORD INTO SQL
			//////////////////////////////////////////////
				case("insertRecord");
					if (isset($_POST["location"]) && isset($_POST["mechanic"]) && isset($_POST["maintReqRowid"]) && isset($_POST["dw"]) && isset($_POST["team"]) && isset($_POST["machine_id"]) && isset($_POST["maint_code"]) && isset($_POST["maint_res_code"]) && isset($_POST["mins_down"]) && isset($_POST["comments"]))  {
						$maintReqRowid = $_POST["maintReqRowid"];
						$Location	= $_POST["location"];
						$Mechanic	= $_POST["mechanic"];
						$DateWork	= $_POST["dw"];
						$Team		= $_POST["team"];
						//$Employee	= $_POST["employee"];
						$MachineID	= $_POST["machine_id"];
						$MaintCode	= $_POST["maint_code"];
						$MaintResCode	= $_POST["maint_res_code"];
						$MinsDown	= $_POST["mins_down"];
						$Comments	= stripIllegalChars($_POST["comments"]);

						if ($maintReqRowid == "") {
							$maintReqRowid = "NULL";
						}

						$sql  = "INSERT INTO nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " (";
						$sql .= " ID_USER_ADD,  ";
						$sql .= " DATE_INCID, ";
						$sql .= " ID_MACH, ";
						//$sql .= " ID_BADGE, ";
						$sql .= " LOCATION, ";
						$sql .= " ID_BADGE_TEAM, ";
						$sql .= " ID_BADGE_MECH, ";
						$sql .= " CODE_MAINT, ";
						$sql .= " CODE_MAINT_RES, ";
						$sql .= " MINS_DOWN, ";
						if (trim($Comments) <> '') {
							$sql .= " COMMENT, ";
						}
						$sql .= " FLAG_DEL, ";
						$sql .= " DATE_CREATED, ";
						$sql .= " MAINT_REQ_Rowid ";

						$sql .= " ) values ( ";
						//$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
						$sql .= " '', ";
						$sql .= " '" . $DateWork . "', ";
						$sql .= " '" . $MachineID . "', ";
						//$sql .= " '" . $Employee . "', ";
						$sql .= " '" . $Location . "', ";
						$sql .= " '" . $Team . "', ";
						$sql .= " '" . $Mechanic . "', ";
						$sql .= " " . $MaintCode . ", ";
						$sql .= " " . $MaintResCode . ", ";
						$sql .= " " . $MinsDown . ", ";
						if (trim($Comments) <> '') {
							$sql .= " '" . trim($Comments) . "', ";
						}
						$sql .= " '', ";
						$sql .= " GetDate(), ";
						$sql .= " ".$maintReqRowid." ";
						$sql .= " )";
						error_log("SQL: " . $sql);
						QueryDatabase($sql, $results);

						/////////////////////
						// Mark the Maint Request record complete
						/////////////////////
						if ($maintReqRowid <> "NULL") {
							$sql  = "UPDATE nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " SET ";
							$sql .= " FLAG_COMPLETE = 'Y', ";
							$sql .= " DATE_COMPLETE = getDate(), ";
							$sql .= " ID_USER_COMPLETE = '".$Mechanic."' ";
							$sql .= " WHERE rowid = ".$maintReqRowid;
							QueryDatabase($sql, $results);
						}

						$NUM_RECS = 20;
						
						$v = refreshRecords($Location,$NUM_RECS);
						$ret .= $v;

					}
				break;
				/////////////////////////////////////
				///////////EDIT FIELD STUFF
				/////////////////////////////////////
				case("showedit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$ret .= " 	<input type='text' id='" . $FieldID . "_TXT' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
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

						//$sqlu = "UPDATE nsa.MAINT_INCIDENTS set " . $field . " = ltrim('" . $StrippedFieldValue . "'), DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $rowid;
						$sqlu = "UPDATE nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " set " . $field . " = ltrim('" . $StrippedFieldValue . "'), DATE_CHG = getdate(), ID_USER_CHG = '' where rowid = " . $rowid;
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
						//$sqlDel = "update nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " set FLAG_DEL = 'Y', DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
						$sqlDel = "update nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " set FLAG_DEL = 'Y', DATE_CHG = getdate(), ID_USER_CHG = '' where rowid = " . $ROWID;
						QueryDatabase($sqlDel, $resultsDel);

						/*$sqlDel = "update nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " set FLAG_DEL = 'Y' where MAINT_INCIDENTS_rowid = " . $ROWID;
						QueryDatabase($sqlDel, $resultsDel);
						$ret .= "DELETED";*/
					}
				break;

				case("numRecsChange");
					if(isset($_POST["location"]) && isset($_POST["mechanic"]) && isset($_POST["num_recs"]) && isset($_POST["team"]) && isset($_POST["machID"]) && isset($_POST["maintCode"]) && isset($_POST["maintCode"])){
						$location = $_POST["location"];
						$mechanic = $_POST["mechanic"];
						$NUM_RECS = $_POST["num_recs"];
						$team = $_POST["team"];
						$machID = $_POST["machID"];
						$maintCode = $_POST["maintCode"];
						$maintResCode = $_POST["maintResCode"];
						$v = refreshRecords($location,$NUM_RECS,$mechanic,$team,$machID,$maintCode,$maintResCode);
						$ret .= $v;
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

function refreshRecords($location,$NUM_RECS,$mechanic="--ALL--",$team="--ALL--",$machID="--ALL--",$maintCode="--ALL--",$maintResCode="--ALL--"){
		global $DB_TEST_FLAG;

		$sql  = "SELECT top " . $NUM_RECS;
		$sql .= " CONVERT(varchar(8), mi.DATE_INCID, 112) as DATE_INCID3, ";
		$sql .= " mc.DESCR, ";
		$sql .= " mi.* ";
		$sql .= " FROM nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " mi ";
		$sql .= " left join nsa.MAINT_CODES mc ";
		$sql .= " on mi.CODE_MAINT = mc.CODE_MAINT ";
		$sql .= " WHERE mi.FLAG_DEL='' ";
		$sql .= " and mi.LOCATION='".$location."' ";
		if ($mechanic <> "--ALL--") {
			$sql .= " and ltrim(ID_BADGE_MECH) = '".$mechanic."' ";
		}
		if ($team <> "--ALL--") {
			$sql .= " and ltrim(ID_BADGE_TEAM) = '".$team."' ";
		}
		if ($machID <> "--ALL--") {
			$sql .= " and ltrim(ID_MACH) = '".$machID."' ";
		}
		if ($maintCode <> "--ALL--") {
			$sql .= " and ltrim(mi.CODE_MAINT) = '".$maintCode."' ";
		}
		if ($maintResCode <> "--ALL--") {
			$sql .= " and ltrim(mi.CODE_MAINT_RES) = '".$maintResCode."' ";
		}
		$sql .= " ORDER BY rowid desc ";
		QueryDatabase($sql, $results);

		$prevrowId = '';
		$b_flip = true;

		$ret1 = " <table class='sample'>\n";
		$ret1 .= " 	<tr>\n";
		$ret1 .= " 		<th class='sample'>Date Work</th>\n";
		$ret1 .= " 		<th class='sample'>Mechanic</th>\n";
		$ret1 .= " 		<th class='sample'>Team</th>\n";
		$ret1 .= " 		<th class='sample'>Mach ID</th>\n";
		$ret1 .= " 		<th class='sample'>Maint Code</th>\n";
		$ret1 .= "		<th class='sample'>Maint Res Code</th>\n";
		$ret1 .= " 		<th class='sample'>Mins to Fix</th>\n";
		$ret1 .= " 		<th class='sample'></th>\n";
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

			$curr = $row['DATE_INCID3'] . " " . str_pad($row['TIME_INCID'],6,"0",STR_PAD_LEFT);
			$currts = strtotime($curr);

			$ret1 .= " 	<tr class='" . $trClass . "'>\n";
			$ret1 .= " 		<td class='" . $trClass . "'>" . date('m/d/Y',$currts) . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='ID_BADGE_MECH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_BADGE_MECH'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='ID_BADGE_TEAM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_BADGE_TEAM'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='ID_MACH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_MACH'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='CODE_MAINT__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" title='" . $row['DESCR'] . "'>" . $row['CODE_MAINT'] . "</td>\n";
			$ret1 .= "		<td class='" . $trClass . "' id='CODE_MAINT_RES__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" title='" . $row['DESCR'] . "'>" . $row['CODE_MAINT_RES'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='MINS_DOWN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['MINS_DOWN'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='delete_" . $row['rowid']."' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
			$ret1 .= " 	</tr>\n";
			if ($row['COMMENT'] <> '') {
				$ret1 .= " 	<tr class='" . $trClass . "'>\n";
				$ret1 .= " 		<td class='" . $trClass . "'></td>\n";
				$ret1 .= " 		<th class='" . $trClass . "'>Comments</th>\n";
				$ret1 .= " 		<td class='" . $trClass . "' id='COMMENT__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" colspan=6>" . $row['COMMENT'] . "</td>\n";
				$ret1 .= " 	</tr>\n";
			}
		}

		$ret1 .= " </table>\n";
		$ret1 .= " </br>\n";

		return $ret1;

}//end refreshRecords


function Webhook($Name){
    $url = 'https://thinknsa.webhook.office.com/webhookb2/4348ec16-413b-492c-868a-47420ec36bbc@8eb4906b-7946-44bb-86f5-a8fc805406c7/IncomingWebhook/e0726c4ad68644479d074b6a71e4d3d8/e5d98068-71cc-44b0-9d61-a014fda9166f';

    error_log("URL: ".$url);

    $ch = curl_init();

    $jsonData = array(
        'text' => 'Hello '.$Name.' !!'
    );
    $jsonDataEncoded = json_encode($jsonData, true);

    $header = array();
    $header[] = 'Content-type: application/json';


    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	error_log("ch: ".$ch);

    $result = curl_exec($ch);
    curl_close($ch);

    error_log("result: ".$result);

    var_dump($result);


}


function msTeamsTest($msg){
	error_log("msTeamsTest: ".$msg);

	$url = 'https://thinknsa.webhook.office.com/webhookb2/4348ec16-413b-492c-868a-47420ec36bbc@8eb4906b-7946-44bb-86f5-a8fc805406c7/IncomingWebhook/e0726c4ad68644479d074b6a71e4d3d8/e5d98068-71cc-44b0-9d61-a014fda9166f';
	error_log("URL: ".$url);
	
	$ch = curl_init($url);
	error_log("ch: " . $ch);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'text='.$msg);
	//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	error_log("result: " . $result);
	var_dump($result);
}


?>
