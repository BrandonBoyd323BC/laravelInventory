<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");

	$DEBUG = 1;
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			if (isset($_POST["action"]) && isset($_POST["team"]))  {
				$ret = "";
				$Action = $_POST["action"];
				$Team = $_POST["team"];

				switch ($Action) {
					/////////////////////////////////////
					// GET COMPONENT LIST
					/////////////////////////////////////
					case "getCompsBySO":
						if (isset($_POST["loc"]) && isset($_POST["id_so"]) && isset($_POST["sufx"])) {
							$Suffix = stripNonANDChars($_POST["sufx"]);
							$Location = stripNonANChars($_POST["loc"]);
							$ShopOrd = stripNonANDChars($_POST["id_so"]);

							//if (!strpos($ShopOrd,"-") === false) {
							//	$a = explode("-", $ShopOrd);
							//	$ShopOrd = $a[0];
							//	$Suffix = $a[1];
							//}
							error_log("TEST");
							$sql =  "SELECT * ";
							$sql .= " FROM nsa.SHPORD_MATL ";
							$sql .= " WHERE ltrim(ID_SO) = '" . $ShopOrd . "' ";
							$sql .= " and SUFX_SO = '" . $Suffix . "' ";
							$sql .= " and ID_LOC = '" . $Location . "' ";
							$sql .= " ORDER BY id_oper, id_item_comp";
							QueryDatabase($sql, $results);
							if (mssql_num_rows($results) > 0 ) {
								$ret .= "				<select name='missingItem' id='missingItem' >\n";
								$ret .= "				<option value='OTHER'>--OTHER--</option>\n";
							} else {
								$ret .= "				<input type='text' name='missingItem' id='missingItem'>\n";
							}
							while ($row = mssql_fetch_assoc($results)) {
								$SELECTED = '';
								if (trim($row['ID_ITEM_COMP']) == trim($ItemNum)) {
									$SELECTED = 'SELECTED';
								}
								$ret .= "					<option value='" . $row['ID_ITEM_COMP'] . "' " . $SELECTED . " title='" . $row['DESCR_ITEM_1'] . " " . $row['DESCR_ITEM_2'] . "'>" . $row['ID_ITEM_COMP'] . "</option>\n";
							}
							$ret .= "			</select>\n";
						} else {
							//$ret .= "				<input type='text' name='missingItem' id='missingItem' value='".$ItemNum."'>\n";
							$ret .= "				<input type='text' name='missingItem' id='missingItem'>\n";
						}
					break;


					/////////////////////////////////////
					// GET OPEN REQUESTS
					/////////////////////////////////////
					case "getOpenReqs":
						$sql  = "SELECT * from nsa.ORD_PREP_MISSING ";
						$sql .= " WHERE ltrim(ID_BADGE_ADD) = '" . $Team . "' ";
						$sql .= " AND FLAG_COMPLETE <> '1' ";
						$sql .= " ORDER BY DATE_ADD desc ";
						QueryDatabase($sql, $results);
						$ret .= "	<table class='sample'>\n";
						if (mssql_num_rows($results) < 1 ) {
							$ret .= "	<tr>";
							$ret .= "		<td>No Open Requests<td>";
							$ret .= "	</tr>";
						} else {
							$ret .= "		<tr class='sample'>\n";
							$ret .= "			<th><font>SO</font></th>\n";
							$ret .= "			<th><font>Missing Item</font></th>\n";
							$ret .= "			<th><font>Date</font></th>\n";
							$ret .= "		</tr>\n";
						}

						while ($row = mssql_fetch_assoc($results)) {
							$ret .= "		<tr class='sample'>\n";
							$ret .= "			<td><font>" . $row['ID_SO'] . "</font></td>\n";
							$ret .= "			<td><font>" . $row['ID_ITEM_COMP'] . "</font></td>\n";
							$ret .= "			<td><font>" . $row['DATE_ADD'] . "</font></td>\n";
							$ret .= "		</tr>\n";
						}
						$ret .= "	</table>\n";
					break;

					/////////////////////////////////////
					// ADD NEW REQUEST
					/////////////////////////////////////
					case "addNewReq":
						if (isset($_POST["loc"]) && isset($_POST["id_so"]) && isset($_POST["comment"]) && isset($_POST["miss_item"])) {
							$Suffix = '0';
							$ShopOrd = trim(stripNonANDChars($_POST["id_so"]));
							$Location = stripNonANChars($_POST["loc"]);
							$Comment = stripIllegalChars($_POST["comment"]);
							$MissItem = stripNonANChars($_POST["miss_item"]);
			 				$QtyMissing = stripNonNumericChars($_POST["qty_missing"]);
							if ($QtyMissing == '') {
								$QtyMissing = 0;
							}

							if (!strpos($ShopOrd,"-") === false) {
								$a = explode("-", $ShopOrd);
								$ShopOrd = $a[0];
								$Suffix = $a[1];
							}

							$sql  = "INSERT into nsa.ORD_PREP_MISSING ( ";
							$sql .= " ID_LOC, ";
							$sql .= " ID_SO, ";
							$sql .= " SUFX_SO, ";
							$sql .= " ID_ITEM_COMP, ";
							$sql .= " QTY_MISSING, ";
							$sql .= " COMMENTS, ";
							$sql .= " ID_BADGE_ADD, ";
							$sql .= " DATE_ADD, ";
							$sql .= " FLAG_COMPLETE ";
							$sql .= " ) VALUES ( ";
							$sql .= " '" . $Location . "', ";
							$sql .= " '" . $ShopOrd . "', ";
							$sql .= " '" . $Suffix . "', ";
							$sql .= " '" . $MissItem . "', ";
							$sql .= " '" . $QtyMissing . "', ";
							$sql .= " '" . $Comment . "', ";
							$sql .= " '" . $Team . "', ";
							$sql .= " GetDate(), ";
							$sql .= " '' ";
							$sql .= " ) ";
							QueryDatabase($sql, $results);

							$sql  = "SELECT * from nsa.ORD_PREP_MISSING ";
							$sql .= " WHERE ltrim(ID_BADGE_ADD) = '" . $Team . "' ";
							$sql .= " AND FLAG_COMPLETE <> '1' ";
							$sql .= " ORDER BY DATE_ADD desc ";
							QueryDatabase($sql, $results);

							$ret .= "	<table class='sample'>\n";
							if (mssql_num_rows($results) < 1 ) {
								$ret .= "	<tr>";
								$ret .= "		<td>No Open Requests<td>";
								$ret .= "	</tr>";
							} else {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<th><font>SO</font></th>\n";
								$ret .= "			<th><font>Missing Item</font></th>\n";
								$ret .= "			<th><font>Date</font></th>\n";
								$ret .= "		</tr>\n";
							}

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<td><font>" . $row['ID_SO'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['ID_ITEM_COMP'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['DATE_ADD'] . "</font></td>\n";
								$ret .= "		</tr>\n";
							}
							$ret .= "	</table>\n";

							/*
							$fn = "/tmp/" . $Team . "_" . time();
							error_log("fn: ". $fn);
							$msg = "New Missing Items Request from Team: " . $Team;
							system("echo '" . $msg ."' > " . $fn);
							system("smbclient -NM prod-23.nsamfg.local <".$fn);
							system("rm -f ".$fn);
							*/


						}
					break;
				}

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
