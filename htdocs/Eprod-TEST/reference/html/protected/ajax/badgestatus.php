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
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			$ret = '';

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);
			$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
			QueryDatabase($sql, $results);

			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				switch ($action) {
					case "showCrewSizeEdit":
						if (isset($_POST["id_badge"]) && isset($_POST["par_id"]) && isset($_POST["curr_val"])) {
							$ID_BADGE = $_POST["id_badge"];
							$PAR_ID = $_POST["par_id"];
							$curr_val = $_POST["curr_val"];

							$ret .= " <input id='textbox_EditCrewSize__".$ID_BADGE."' type=textbox value='".$curr_val."' size=3 maxlength=2>\n";
							$ret .= " <input id='button_saveEditCrewSize__".$ID_BADGE."' type=button value='Save' onClick=\"saveCrewSizeEdit(".$PAR_ID.",".$ID_BADGE.")\">\n";
						}
					break;

					case "saveCrewSizeEdit":
						if (isset($_POST["id_badge"]) && isset($_POST["new_val"])) {
							$ID_BADGE = $_POST["id_badge"];
							$NEW_VAL = $_POST["new_val"];

							error_log("Updating MAX Crew Size for ID_BADGE: ".$ID_BADGE . " NEW_VAL: " . $NEW_VAL);	
							$sql  = " UPDATE nsa.DCEMMS_EMP ";
							$sql .= " SET ID_EMP = '".$NEW_VAL."' ";
							$sql .= " WHERE ltrim(ID_BADGE) = '".$ID_BADGE."' ";
							QueryDatabase($sql, $results);

							$sql  = "SELECT ID_BADGE, ";
							$sql .= " FLAG_ATTEND, ";
							$sql .= " ID_EMP as MAX_CREW ";
							$sql .= " FROM nsa.DCEMMS_EMP ";
							$sql .= " WHERE ltrim(ID_BADGE) = '".$ID_BADGE."' ";
							$sql .= " and CODE_ACTV = 0 ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$teamColor = GetColorBadgeFlag($row['FLAG_ATTEND']);
								$ret .= " <font class='" . $teamColor . "'>" . $row['MAX_CREW'] . "</font>\n";
							}
						}
					break;

					case "showJobClassEdit":
						if (isset($_POST["id_badge"]) && isset($_POST["par_id"]) && isset($_POST["curr_val"])) {
							$ID_BADGE = $_POST["id_badge"];
							$PAR_ID = $_POST["par_id"];
							$curr_val = trim($_POST["curr_val"]);

							$SELECTED_BLANK = '';
							$SELECTED_MO = '';
							$SELECTED_S1 = '';
							$SELECTED_HS1 = '';
							$SELECTED_HS2 = '';
							$SELECTED_HSF = '';

							if ($curr_val == ''){
								$SELECTED_BLANK = "SELECTED";
							}
							if ($curr_val == 'MO'){
								$SELECTED_MO = "SELECTED";
							}
							if ($curr_val == 'S1'){
								$SELECTED_S1 = "SELECTED";
							}
							if ($curr_val == 'HS1'){
								$SELECTED_HS1 = "SELECTED";
							}
							if ($curr_val == 'HS2'){
								$SELECTED_HS2 = "SELECTED";
							}
							if ($curr_val == 'HSF'){
								$SELECTED_HSF = "SELECTED";
							}							

							$ret .= "<select id='selJobClass__".$ID_BADGE."'>\n";
							$ret .= "	<option value='' ".$SELECTED_BLANK.">--SELECT--</option>\n";
							$ret .= "	<option value='MO' ".$SELECTED_MO.">Machine Operator</option>\n";
							$ret .= "	<option value='S1' ".$SELECTED_S1.">Skilled</option>\n";
							$ret .= "	<option value='HS1' ".$SELECTED_HS1.">Highly Skilled Level 1</option>\n";
							$ret .= "	<option value='HS2' ".$SELECTED_HS2.">Highly Skilled Level 2</option>\n";
							$ret .= "	<option value='HSF' ".$SELECTED_HSF.">Highly Skilled Flex</option>\n";
							$ret .= "</select>\n";
							$ret .= " <input id='button_saveJobClass__".$ID_BADGE."' type=button value='Save' onClick=\"saveJobClassEdit(".$PAR_ID.",".$ID_BADGE.")\">\n";
						}
					break;

					case "saveJobClassEdit":
						if (isset($_POST["id_badge"]) && isset($_POST["new_val"])) {
							$ID_BADGE = $_POST["id_badge"];
							$NEW_VAL = $_POST["new_val"];

							error_log("Updating Job Class for ID_BADGE: ".$ID_BADGE . " NEW_VAL: " . $NEW_VAL);	
							$sql  = " UPDATE nsa.DCEMMS_EMP ";
							$sql .= " SET CODE_USER = '".$NEW_VAL."' ";
							$sql .= " WHERE ltrim(ID_BADGE) = '".$ID_BADGE."' ";
							QueryDatabase($sql, $results);

							$sql  = "SELECT ID_BADGE, ";
							$sql .= " FLAG_ATTEND, ";
							$sql .= " CODE_USER ";
							$sql .= " FROM nsa.DCEMMS_EMP ";
							$sql .= " WHERE ltrim(ID_BADGE) = '".$ID_BADGE."' ";
							$sql .= " and CODE_ACTV = 0 ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$teamColor = GetColorBadgeFlag($row['FLAG_ATTEND']);
								$ret .= " <font class='" . $teamColor . "'>" . $row['CODE_USER'] . "</font>\n";
							}
						}
					break;

				} // END ACTION SWITCH
			}

			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabase($sql, $results);

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
