<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ('Missing Pieces','default.css','ordprep.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$Location = '';
			$TeamBadge = '';
			$ShopOrd = '';
			$Suffix = '';
			$ItemNum = '';

 			if (isset($_GET["loc"]))  {
 				$Location = stripNonANChars($_GET["loc"]);
 			}
			if (isset($_GET["teamBadge"])) {
				$TeamBadge = stripNonANChars($_GET["teamBadge"]);
			}
 			if (isset($_GET["shopOrd"]))  {
 				$ShopOrd = trim(stripNonANChars($_GET["shopOrd"]));
 			}
 			if (isset($_GET["sufx"]))  {
 				$Suffix = stripNonANChars($_GET["sufx"]);
 			}
 			if (isset($_GET["itemNum"]))  {
 				$ItemNum = stripNonANChars($_GET["itemNum"]);
 			}
			$ShopOrdWSfx = $ShopOrd;

			if ($Suffix <> '') {
				$ShopOrdWSfx .= "-" . $Suffix;
			}

			print(" <table>\n");
			print(" 	<tr>\n");
			print("			<td>Team: </td>\n");
			print("			<td>\n");
			//print("				<select name='selTeam' id='selTeam' onkeypress='searchTeamOpenReqs(event);'>\n");
			print("				<select name='selTeam' id='selTeam' onChange='searchTeamOpenReqs(event);'>\n");
			print("					<option value=''>--Select Team--</option>\n");
			$sql =  "select ";
			$sql .= "  ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
			$sql .= "  ltrim(ID_BADGE) as ID_BADGE,";
			$sql .= "  NAME_EMP";
			$sql .= " from ";
			$sql .= "  nsa.DCEMMS_EMP ";
			$sql .= " where ";
			$sql .= "  TYPE_BADGE = 'X'";
			$sql .= "  and";
			$sql .= "  CODE_ACTV = '0'";
			$sql .= " order by BADGE_NAME asc";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				$SELECTED = '';
				if (trim($row['ID_BADGE']) == trim($TeamBadge)) {
					$SELECTED = 'SELECTED';
				}
				print("					<option value='" . $row['ID_BADGE'] . "' " . $SELECTED . ">" . $row['BADGE_NAME'] . "</option>\n");
			}

			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Shop Order: </td>\n");
			print("			<td>\n");
			//print("				<input type='text' name='id_so_TXT' id='id_so_TXT' value='".$ShopOrdWSfx."' onChange='searchCompBySO()'>\n");


			print("			<input id='so' type=text onkeyup=\"nextOnDash('so','sufx')\" maxlength=9 size=10 autofocus> -\n");
			print("			<input id='sufx' type=text onkeyup=\"sufxEntered()\" maxlength=3 size=4>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Missing Item Num: </td>\n");
			print("			<td id='missingTD' name='missingTD'>\n");

			if (isset($_GET["loc"]) && isset($_GET["shopOrd"]) && isset($_GET["sufx"]) && isset($_GET["itemNum"])) {
				print("				<select name='missingItem' id='missingItem' >\n");
				print("					<option value='OTHER'>--OTHER--</option>\n");
				$sql =  "select ";
				$sql .= "  * ";
				$sql .= " from ";
				$sql .= "  nsa.SHPORD_MATL ";
				$sql .= " where ";
				$sql .= "  ltrim(ID_SO) = '" . $ShopOrd . "'";
				$sql .= "  and";
				$sql .= "  SUFX_SO = '" . $Suffix . "'";
				$sql .= "  and";
				$sql .= "  ID_LOC = '" . $Location . "'";
				$sql .= " order by id_oper, id_item_comp";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$SELECTED = '';
					if (trim($row['ID_ITEM_COMP']) == trim($ItemNum)) {
						$SELECTED = 'SELECTED';
					}
					print("					<option value='" . $row['ID_ITEM_COMP'] . "' " . $SELECTED . " title='" . $row['DESCR_ITEM_1'] . " " . $row['DESCR_ITEM_2'] . "'>" . $row['ID_ITEM_COMP'] . "</option>\n");
				}
				print("				</select>\n");
			} else {
				print("				<input type='text' name='missingItem' id='missingItem' value='".$ItemNum."'>\n");
			}
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Qty Missing: </td>\n");
			print("			<td>\n");
			print("				<input type='text' name='qtyMissing' id='qtyMissing'>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td>Comments: </td>\n");
			print("			<td>\n");
			print("				<textarea name='comments_TXT' id='comments_TXT' rows='3'></textarea>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<button id='submit' name='submit' value='Submit' onclick='addRecord()'>Send Request</button>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print("<body onLoad='doOnLoads()'>");
			print("<h3>Open Requests</h3>");
			print("<div id='openReqDiv' name='openReqDiv'></div>\n");
			print("<div id='scoreDiv' name='scoreDiv'></div>\n");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
