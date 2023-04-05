<?php

	require_once("../protected/procfile.php");

	$DEBUG = 2;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}
	
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			$ret = "";

			$ua=getBrowser();
			if ($DEBUG > 1) {
			    error_log("userAgent: " . $ua['userAgent']);
			    error_log("name: " . $ua['name']);
			    error_log("short_name: " . $ua['short_name']);
			    error_log("version: " . $ua['version']);
			    error_log("platform: " . $ua['platform']);
			    error_log("pattern: " . $ua['pattern']);
			}

			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				if ($DEBUG) {
				    error_log("Action: " . $action);
				}
				switch ($action) {

					/////////////////
					// Show Team Lights
					/////////////////
					case "showTeamLights":
						if (isset($_POST["team"]))  {
							$teamBadge = $_POST["team"];
							//$divDisplay = $_POST["divDisplay"];
							$divMaintDisplay 	= "none";
							$divQADisplay		= "none";
							$divOrdPrepDisplay 	= "none";
							$divRnDDisplay  	= "none";

							$ret .= "	<table id='tableLightSwitches'>\n";

							//$colorsArray = array('RED','YELLOW','BLUE');
							$colorsArray = array('RED','BLUE');
							foreach ($colorsArray as $color) {
								$ret .= "		<tr>\n";
								$ret .= "			<td><font>".$color." Light</font></td>\n";

								$sql  = " SELECT top 1 * FROM nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " ";
								$sql .= " WHERE ltrim(TEAM_BADGE) = '".$teamBadge."' ";
								$sql .= " AND CATEGORY = '".$color."' ";
								$sql .= " AND FLAG_COMPLETE is NULL ";
								$sql .= " ORDER BY DATE_ADD asc ";
								QueryDatabase($sql, $results);


								///////////////////////////////////
								// Slider does not work in IE 7
								///////////////////////////////////
								if (($ua['short_name'] == 'MSIE' && $ua['version'] == '7.0') || $ua['version'] == '?'){
									if (mssql_num_rows($results) > 0) {
										while ($row = mssql_fetch_assoc($results)) {
											//$ret .= "		<tr class='sample'>\n";
											$ret .= "			<td> ";
											//$ret .= "				<label class='switch'> ";
											$ret .= "					<input type='checkbox' id='".$color."' onchange=\"sliderChanged(this.id,'$teamBadge')\" CHECKED> ";
											//$ret .= "					<span class='slider".$color." round'></span> ";
											//$ret .= "				</label> ";
											$ret .= "			</td> ";
											if ($color=='RED') {
												$divMaintDisplay = "inline-block";
												//$divQADisplay = "none";
												//$divOrdPrepDisplay = "none";
												//$divRnDDisplay = "none";
											}
											if ($color=='YELLOW') {
												//$divMaintDisplay = "none";
												$divQADisplay = "inline-block";
												//$divOrdPrepDisplay = "none";
												//$divRnDDisplay = "none";
											}
											if ($color=='BLUE') {
												//$divMaintDisplay = "none";
												//$divQADisplay = "none";
												$divOrdPrepDisplay = "inline-block";
												//$divRnDDisplay = "none";
											}
											if ($color=='PURPLE') {
												//$divMaintDisplay = "none";
												//$divQADisplay = "none";
												//$divOrdPrepDisplay = "none";
												$divRnDDisplay = "inline-block";
											}
										}
									} else {
										$ret .= "			<td> ";
										//$ret .= "				<label class='switch'> ";
										$ret .= "					<input type='checkbox' id='".$color."' onchange=\"sliderChanged(this.id,'$teamBadge')\"> ";
										//$ret .= "					<span class='slider".$color." round'></span> ";
										//$ret .= "				</label> ";
										$ret .= "			</td> ";								
										$ret .= "			<td><font></font></td>\n";
									}
								} else {
								///////////////////////////////////
								// All other browsers
								///////////////////////////////////
									if (mssql_num_rows($results) > 0) {
										while ($row = mssql_fetch_assoc($results)) {
											//$ret .= "		<tr class='sample'>\n";
											$ret .= "			<td> ";
											$ret .= "				<label class='switch'> ";
											$ret .= "					<input type='checkbox' id='".$color."' onchange=\"sliderChanged(this.id,'$teamBadge')\" CHECKED> ";
											$ret .= "					<span class='slider".$color." round'></span> ";
											$ret .= "				</label> ";
											$ret .= "			</td> ";
											if ($color=='RED') {
												$divMaintDisplay = "inline-block";
												//$divQADisplay = "none";
												//$divOrdPrepDisplay = "none";
												//$divRnDDisplay = "none";
											}
											if ($color=='YELLOW') {
												//$divMaintDisplay = "none";
												$divQADisplay = "inline-block";
												//$divOrdPrepDisplay = "none";
												//$divRnDDisplay = "none";
											}
											if ($color=='BLUE') {
												//$divMaintDisplay = "none";
												//$divQADisplay = "none";
												$divOrdPrepDisplay = "inline-block";
												//$divRnDDisplay = "none";
											}
											if ($color=='PURPLE') {
												//$divMaintDisplay = "none";
												//$divQADisplay = "none";
												//$divOrdPrepDisplay = "none";
												$divRnDDisplay = "inline-block";
											}											
										}
									} else {
										$ret .= "			<td> ";
										$ret .= "				<label class='switch'> ";
										$ret .= "					<input type='checkbox' id='".$color."' onchange=\"sliderChanged(this.id,'$teamBadge')\"> ";
										$ret .= "					<span class='slider".$color." round'></span> ";
										$ret .= "				</label> ";
										$ret .= "			</td> ";								
										$ret .= "			<td><font></font></td>\n";
									}
								}
								$ret .= "		</tr>\n";
							}
							$ret .= "	</table>\n";






							$ret .= " <table id='tableCatDivs' align='center'>\n";
							$ret .= " 	<tr><td>\n";


							////////////////////////////////////
							// RED - MAINTENANCE
							////////////////////////////////////
							$Team = $_POST["team"];

							$ret .= " <div id='divMaint' style='background-color: #ff9999; display:". $divMaintDisplay .";'>\n";
							$ret .= " <table align='center'>\n";
							$ret .= " 	<tr >\n";
							$ret .= " 		<td colspan=2 align='center'><h3>Maintenance</h3></td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr class='center'>\n";
							$ret .= " 		<td>Machine ID: </td>\n";
							$ret .= "			<td id='machineTD' name='machineTD'>\n";
							$ret .= "				<select name='selMachID' id='selMachID' >\n";
							$ret .= "					<option value='OTHER'>--OTHER--</option>\n";
							
							$sql  = "SELECT ID_MACH, HEAD_BRAND, HEAD_TYPE, rowid as MM_rowid ";
							$sql .= " FROM nsa.MAINT_MACHINERY ";
							$sql .= " WHERE ltrim(ID_BADGE_TEAM) = '" . $teamBadge . "' ";
							$sql .= " and STATUS = 'A' ";
							$sql .= " ORDER BY ID_MACH asc";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$SELECTED = '';
								//if (trim($row['ID_ITEM_COMP']) == trim($ItemNum)) {
								//	$SELECTED = 'SELECTED';
								//}
								$ret .= "					<option value='".$row['ID_MACH']."__".$row['MM_rowid']."' ".$SELECTED.">".$row['ID_MACH']." - ".$row['HEAD_BRAND']."</option>\n";
							}
							$ret .= "				</select>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<td>Comments: </td>\n";
							$ret .= "			<td>\n";
							$ret .= "				<textarea name='comments_TXT' id='comments_TXT' rows='3' maxlength='30'></textarea>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<td colspan='2'>\n";
							$ret .= " 			<button id='addMaintRequest' name='submit' value='Submit' onclick='addMaintRecord()'>Send Request</button>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr >\n";
							$ret .= " 		<td colspan=2 align='center'><h3>Open Maintenance Requests</h3> <font onClick=\"refreshOpenMaintReqs('".$Team."')\">Refresh</font></td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " </table>\n";


							
							////////////////////////
							////////SHOW OPEN MAINTENANCE REQUESTS
							////////////////////////
							$ret .= "<div id='openMaintReqDiv' name='openMaintReqDiv'>";

							$sql  = "SELECT mm.HEAD_BRAND, mr.rowid as MaintReqRowid, convert(varchar(30), mr.DATE_ADD,100) as DATE_ADD2, mr.* ";
							$sql .= " FROM nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " mr ";
							$sql .= " LEFT JOIN nsa.MAINT_MACHINERY mm ";
							$sql .= " on mr.ID_MACH = mm.ID_MACH ";
							$sql .= " WHERE ltrim(mr.ID_BADGE) = '" . $Team . "' ";
							$sql .= " AND ISNULL(mr.FLAG_COMPLETE,'N') <> 'Y' ";
							$sql .= " ORDER BY mr.DATE_ADD asc ";
							QueryDatabase($sql, $results);

							$ret .= "	<table class='sample' align='center'>\n";

							if (mssql_num_rows($results) < 1 ) {
								$ret .= "	<tr>";
								$ret .= "		<td>No Open Requests<td>";
								$ret .= "	</tr>";
							} else {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<th><font>Date</font></th>\n";
								$ret .= "			<th><font>Machine</font></th>\n";
								$ret .= "			<th><font>Comment</font></th>\n";
								$ret .= "			<th><font>Link</font></th>\n";
								$ret .= "			<th><font></font></th>\n";
								$ret .= "		</tr>\n";
							}

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<td><font>" . $row['DATE_ADD2'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['ID_MACH'] . " - " . $row['HEAD_BRAND'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['COMMENTS'] . "</font></td>\n";

								///////////////////////////////////
								// NEW WINDOW does not work in IE 7
								///////////////////////////////////
								if (($ua['short_name'] == 'MSIE' && $ua['version'] == '7.0') || $ua['version'] == '?'){
									$ret .= "			<td><font><a href='\maintlog.php?MaintReqRowid=".$row['MaintReqRowid']."' target='_blank' onClick=\"event.preventDefault();popup = window.open('\maintlog.php?MaintReqRowid=".$row['MaintReqRowid']."', 'PopupPage', 'height=600,width=600,scrollbars=yes,resizable=yes')\";' title='Maintenance Log'>Maintenance Log</font></td>\n";
								} else {
									$ret .= "			<td><font><a href='' onClick=\"event.preventDefault();popup = window.open('maintlog.php?MaintReqRowid=".$row['MaintReqRowid']."', 'PopupPage', 'height=600,width=600,scrollbars=yes,resizable=yes')\";' title='Maintenance Log'>Maintenance Log</font></td>\n";
								}
								$ret .= "			<td>\n";
								$ret .= "				<input type='checkbox' id='maintCheckboxComplete__".$row['rowid']."' value='".$row['rowid']."' onChange=\"maintCheckboxCompleteChange(".$row['rowid'].")\"></input>\n";
								$ret .= "				<input type='button' id='buttonMaintSaveCheckboxComplete__".$row['rowid']."' value='Close' style='display:none;' onClick=\"saveCompleteMaint(".$row['rowid'].")\"></input>\n";
								$ret .= "			</td>\n";
								$ret .= "		</tr>\n";
							}
							$ret .= "	</table>\n";
							$ret .= "</div>\n";// closes openMaintReqDiv
							$ret .= "</div>";//closes divMaint
							$ret .= "</td></tr>\n";//closes maint

















							////////////////////////////////////
							// BLUE - ORDER PREP
							////////////////////////////////////
							$Location = '';
							$ShopOrd = '';
							$Suffix = '';
							$ItemNum = '';

							$ret .= " <tr><td>\n";
							$ret .= " <div id='divOrdPrep' style='background-color: #80bfff; display:". $divOrdPrepDisplay .";'>\n";
							$ret .= " <table align='center'>\n";

							$ret .= " 	<tr >\n";
							$ret .= " 		<td colspan=2 align='center'><h3>Order Prep</h3></td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr class='center'>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<td>Shop Order: </td>\n";
							$ret .= "		<td>\n";
							$ret .= "			<input id='op_so' type=text onkeyup=\"nextOnDash('so','sufx')\" maxlength=9 size=10 autofocus> -\n";
							$ret .= "			<input id='op_sufx' type=text onkeyup=\"sufxEntered()\" maxlength=3 size=4>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<td>Missing Item Num: </td>\n";
							$ret .= "			<td id='op_missingTD' name='op_missingTD'>\n";

							if (isset($_GET["loc"]) && isset($_GET["shopOrd"]) && isset($_GET["sufx"]) && isset($_GET["itemNum"])) {
								$ret .= "				<select name='op_missingItem' id='missingItem' >\n";
								$ret .= "					<option value='OTHER'>--OTHER--</option>\n";
								
								$sql  = "SELECT ID_ITEM_COMP, DESCR_ITEM_1, DESCR_ITEM_2, ID_OPER ";
								$sql .= " FROM nsa.SHPORD_MATL ";
								$sql .= " WHERE ltrim(ID_SO) = '" . $ShopOrd . "'";
								$sql .= " and SUFX_SO = '" . $Suffix . "'";
								$sql .= " and ID_LOC = '" . $Location . "'";
								$sql .= " ORDER BY id_oper, id_item_comp";
								QueryDatabase($sql, $results);

								while ($row = mssql_fetch_assoc($results)) {
									$SELECTED = '';
									if (trim($row['ID_ITEM_COMP']) == trim($ItemNum)) {
										$SELECTED = 'SELECTED';
									}
									$ret .= "					<option value='" . $row['ID_ITEM_COMP'] . "' " . $SELECTED . " title='" . $row['DESCR_ITEM_1'] . " " . $row['DESCR_ITEM_2'] . "'>" . $row['ID_ITEM_COMP'] . "</option>\n";
								}
								$ret .= "				</select>\n";
							} else {
								$ret .= "				<input type='text' name='op_missingItem' id='op_missingItem' value='".$ItemNum."'>\n";
							}
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<td>Qty Missing: </td>\n";
							$ret .= "			<td>\n";
							$ret .= "				<input type='text' name='op_qtyMissing' id='op_qtyMissing'>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<td>Comments: </td>\n";
							$ret .= "			<td>\n";
							$ret .= "				<textarea name='op_comments_TXT' id='op_comments_TXT' rows='3' maxlength='30'></textarea>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<td colspan='2'>\n";
							$ret .= " 			<button id='addOrdPrepRequest' name='submit' value='Submit' onclick='addOrdPrepRecord()'>Send Request</button>\n";
							$ret .= " 		</td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr >\n";
							$ret .= " 		<td colspan=2 align='center'><h3>Open Order Prep Requests</h3></td>\n";
							$ret .= " 	</tr>\n";
							$ret .= " </table>\n";

							$Team = $_POST["team"];
							
							////////////////////////
							////////SHOW OPEN ORDER PREP REQUESTS
							////////////////////////
							$ret .= "<div id='openOrdPrepReqDiv' name='openOrdPrepReqDiv'>";

							$sql  = "SELECT convert(varchar(30), DATE_ADD,100) as DATE_ADD2, * from nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . " ";
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
								$ret .= "			<th><font>QTY Missing</font></th>\n";
								$ret .= "			<th><font>Comments</font></th>\n";
								$ret .= "			<th><font></font></th>\n";
								$ret .= "		</tr>\n";
							}

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<td><font>" . $row['ID_SO'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['ID_ITEM_COMP'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['DATE_ADD2'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['QTY_MISSING'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['COMMENTS'] . "</font></td>\n";
								$ret .= "			<td>\n";
								$ret .= "				<input type='checkbox' id='ordPrepCheckboxComplete__".$row['rowid']."' value='".$row['rowid']."' onChange=\"ordPrepCheckboxCompleteChange(".$row['rowid'].")\"></input>\n";
								$ret .= "				<input type='button' id='buttonOrdPrepSaveCheckboxComplete__".$row['rowid']."' value='Close' style='display:none;' onClick=\"saveCompleteOrdPrep(".$row['rowid'].")\"></input>\n";								
								$ret .= "			</td>\n";
								$ret .= "		</tr>\n";
							}
							$ret .= "	</table>\n";
							$ret .= "</div>\n";//closes openOrdPrepReqDiv
							$ret .= "</div>";//closes divOrdPrep
							$ret .= "</td></tr>\n";//closes tableOrdPrep
							$ret .= "</table>\n";
						}

					break;






					/////////////////////////////////////
					// GET COMPONENT LIST
					/////////////////////////////////////
					case "getCompsBySO":
						if (isset($_POST["loc"]) && isset($_POST["id_so"]) && isset($_POST["sufx"])) {
							$Suffix = stripNonANDChars($_POST["sufx"]);
							$Location = stripNonANChars($_POST["loc"]);
							$ShopOrd = stripNonANDChars($_POST["id_so"]);
							$ItemNum = '';

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
								$ret .= "				<select name='op_missingItem' id='op_missingItem' >\n";
								$ret .= "				<option value='OTHER'>--OTHER--</option>\n";
							} else {
								$ret .= "				<input type='text' name='op_missingItem' id='op_missingItem'>\n";
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
							//$ret .= "				<input type='text' name='op_missingItem' id='op_missingItem' value='".$ItemNum."'>\n";
							$ret .= "				<input type='text' name='op_missingItem' id='op_missingItem'>\n";
						}
					break;



					/////////////////////////////////////
					// ADD NEW MAINTENANCE REQUEST
					/////////////////////////////////////

					case "addNewMaintReq":
						if (isset($_POST["team"]) && isset($_POST["id_mach_rowid"]) && isset($_POST["comment"])) {
							$Team = $_POST["team"];
							$Comment = stripIllegalChars($_POST["comment"]);
							$vals = explode("__", $_POST["id_mach_rowid"]);
							$IdMach = $vals[0];
							$MmRowid = $vals[1];

							$sql  = "INSERT into nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " ( ";
							$sql .= " DATE_ADD, ";
							$sql .= " ID_MACH, ";
							$sql .= " ID_BADGE, ";
							$sql .= " ID_BADGE_TEAM, ";
							$sql .= " COMMENTS ";
							$sql .= " ) VALUES ( ";
							$sql .= " GetDate(), ";
							$sql .= " '" . $IdMach . "', ";
							$sql .= " '" . $Team . "', ";
							$sql .= " '" . $Team . "', ";
							$sql .= " '" . $Comment . "' ";
							$sql .= " ) ";
							QueryDatabase($sql, $results);

							$sql  = "SELECT mm.HEAD_BRAND, mr.rowid as MaintReqRowid, convert(varchar(30), mr.DATE_ADD,100) as DATE_ADD2, mr.* ";
							$sql .= " FROM nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " mr ";
							$sql .= " LEFT JOIN nsa.MAINT_MACHINERY mm ";
							$sql .= " on mr.ID_MACH = mm.ID_MACH ";
							$sql .= " WHERE ltrim(mr.ID_BADGE) = '" . $Team . "' ";
							$sql .= " AND ISNULL(mr.FLAG_COMPLETE,'N') <> 'Y' ";
							$sql .= " ORDER BY mr.DATE_ADD asc ";
							QueryDatabase($sql, $results);
							error_log($sql);

							$ret .= "	<table class='sample'>\n";
							if (mssql_num_rows($results) < 1 ) {
								$ret .= "	<tr>";
								$ret .= "		<td>No Open Requests<td>";
								$ret .= "	</tr>";
							} else {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<th><font>Date</font></th>\n";
								$ret .= "			<th><font>Machine</font></th>\n";
								$ret .= "			<th><font>Comment</font></th>\n";
								$ret .= "			<th><font>Link</font></th>\n";
								$ret .= "			<th><font></font></th>\n";
								$ret .= "		</tr>\n";
							}

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<td><font>" . $row['DATE_ADD2'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['ID_MACH'] . " - " . $row['HEAD_BRAND'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['COMMENTS'] . "</font></td>\n";
								$ret .= "			<td><font><a href='' onClick=\"event.preventDefault();popup = window.open('maintlog.php?MaintReqRowid=".$row['MaintReqRowid']."', 'PopupPage', 'height=600,width=600,scrollbars=yes,resizable=yes')\";' title='Maintenance Log'>Maintenance Log</font></td>\n";
								$ret .= "			<td>\n";
								$ret .= "				<input type='checkbox' id='maintCheckboxComplete__".$row['rowid']."' value='".$row['rowid']."' onChange=\"maintCheckboxCompleteChange(".$row['rowid'].")\"></input>\n";
								$ret .= "				<input type='button' id='buttonMaintSaveCheckboxComplete__".$row['rowid']."' value='Close' style='display:none;' onClick=\"saveCompleteMaint(".$row['rowid'].")\"></input>\n";
								$ret .= "			</td>\n";
								$ret .= "		</tr>\n";
							}
							$ret .= "	</table>\n";
						}
					break;



					/////////////////////////////////////
					// ADD NEW ORDER PREP REQUEST
					/////////////////////////////////////
					case "addNewOrdPrepReq":
						if (isset($_POST["loc"]) && isset($_POST["id_so"]) && isset($_POST["comment"]) && isset($_POST["miss_item"]) && isset($_POST["sufx"]) && isset($_POST["team"]) ) {
							$Suffix = stripNonANChars($_POST["sufx"]);
							$ShopOrd = trim(stripNonANDChars($_POST["id_so"]));
							$Location = stripNonANChars($_POST["loc"]);
							$Comment = stripIllegalChars($_POST["comment"]);
							$MissItem = stripNonANChars($_POST["miss_item"]);
			 				$QtyMissing = stripNonNumericChars($_POST["qty_missing"]);
			 				$Team = $_POST["team"];
							if ($QtyMissing == '') {
								$QtyMissing = 0;
							}

							if (!strpos($ShopOrd,"-") === false) {
								$a = explode("-", $ShopOrd);
								$ShopOrd = $a[0];
								$Suffix = $a[1];
							}

							$sql  = "INSERT into nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . " ( ";
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

							$sql  = "SELECT convert(varchar(30), DATE_ADD,100) as DATE_ADD2, * from nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . " ";
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
								$ret .= "			<th><font>QTY Missing</font></th>\n";
								$ret .= "			<th><font>Comments</font></th>\n";
								$ret .= "			<th><font>Completed</font></th>\n";
								$ret .= "		</tr>\n";
							}

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<td><font>" . $row['ID_SO'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['ID_ITEM_COMP'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['DATE_ADD2'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['QTY_MISSING'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['COMMENTS'] . "</font></td>\n";
								$ret .= "			<td>\n";
								$ret .= "				<input type='checkbox' id='ordPrepCheckboxComplete__".$row['rowid']."' value='".$row['rowid']."' onChange=\"ordPrepCheckboxCompleteChange(".$row['rowid'].")\"></input>\n";
								$ret .= "				<input type='button' id='buttonOrdPrepSaveCheckboxComplete__".$row['rowid']."' value='Close' style='display:none;' onClick=\"saveCompleteOrdPrep(".$row['rowid'].")\"></input>\n";
								$ret .= "			</td>\n";
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


					case "refreshOpenMaintReqs":
						if (isset($_POST["team"])){
							$Team = $_POST["team"];

							$sql  = "SELECT mm.HEAD_BRAND, mr.rowid as MaintReqRowid, convert(varchar(30), mr.DATE_ADD,100) as DATE_ADD2, mr.* ";
							$sql .= " FROM nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " mr ";
							$sql .= " LEFT JOIN nsa.MAINT_MACHINERY mm ";
							$sql .= " on mr.ID_MACH = mm.ID_MACH ";
							$sql .= " WHERE ltrim(mr.ID_BADGE) = '" . $Team . "' ";
							$sql .= " AND ISNULL(mr.FLAG_COMPLETE,'N') <> 'Y' ";
							$sql .= " ORDER BY mr.DATE_ADD asc ";
							QueryDatabase($sql, $results);

							$ret .= "	<table class='sample' align='center'>\n";

							if (mssql_num_rows($results) < 1 ) {
								$ret .= "	<tr>";
								$ret .= "		<td>No Open Requests<td>";
								$ret .= "	</tr>";
							} else {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<th><font>Date</font></th>\n";
								$ret .= "			<th><font>Machine</font></th>\n";
								$ret .= "			<th><font>Comment</font></th>\n";
								$ret .= "			<th><font>Link</font></th>\n";
								$ret .= "			<th><font></font></th>\n";
								$ret .= "		</tr>\n";
							}

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<td><font>" . $row['DATE_ADD2'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['ID_MACH'] . " - " . $row['HEAD_BRAND'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['COMMENTS'] . "</font></td>\n";
								$ret .= "			<td><font><a href='' onClick=\"event.preventDefault();popup = window.open('maintlog.php?MaintReqRowid=".$row['MaintReqRowid']."', 'PopupPage', 'height=600,width=600,scrollbars=yes,resizable=yes')\";' title='Maintenance Log'>Maintenance Log</font></td>\n";
								$ret .= "			<td>\n";
								$ret .= "				<input type='checkbox' id='maintCheckboxComplete__".$row['rowid']."' value='".$row['rowid']."' onChange=\"maintCheckboxCompleteChange(".$row['rowid'].")\"></input>\n";
								$ret .= "				<input type='button' id='buttonMaintSaveCheckboxComplete__".$row['rowid']."' value='Close' style='display:none;' onClick=\"saveCompleteMaint(".$row['rowid'].")\"></input>\n";
								$ret .= "			</td>\n";
								$ret .= "		</tr>\n";
							}
							$ret .= "	</table>\n";



							
						}//end if
					break;

/*
					/////////////////////////////////////
					// GET OPEN REQUESTS
					/////////////////////////////////////
					case "getOpenReqs":
						if (isset($_POST["team"])){
							$Team = $_POST["team"];

							$sql  = "SELECT convert(varchar(30), DATE_ADD,100) as DATE_ADD2, * ";
							$sql .= " FROM nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . "  ";
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
								$ret .= "			<th><font>QTY Missing</font></th>\n";
								$ret .= "			<th><font>Comments</font></th>\n";
								$ret .= "			<th><font></font></th>\n";
								$ret .= "		</tr>\n";
							}

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "		<tr class='sample'>\n";
								$ret .= "			<td><font>" . $row['ID_SO'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['ID_ITEM_COMP'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['DATE_ADD2'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['QTY_MISSING'] . "</font></td>\n";
								$ret .= "			<td><font>" . $row['COMMENTS'] . "</font></td>\n";
								$ret .= "			<td>\n";
								$ret .= "				<input type='checkbox' id='ordPrepCheckboxComplete__".$row['rowid']."' value='".$row['rowid']."' onChange=\"ordPrepCheckboxCompleteChange(".$row['rowid'].")\"></input>\n";
								$ret .= "				<input type='button' id='buttonOrdPrepSaveCheckboxComplete__".$row['rowid']."' value='Close' style='display:none;' onClick=\"saveCompleteOrdPrep(".$row['rowid'].")\"></input>\n";							
								$ret .= "			</td>\n";	
								$ret .= "		</tr>\n";
							}
							$ret .= "	</table>\n";
						}//end if
					break;
*/


					case "insertLightAlert":
						if (isset($_POST["team"]) && isset($_POST["category"]))  {
							$teamBadge = stripIllegalChars2($_POST["team"]);
							$category = stripIllegalChars2($_POST["category"]);

							error_log("INSERTING ".$category ." Light Alert for ".$teamBadge);

							$sql  = "INSERT INTO nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " ( ";
							$sql .= " TEAM_BADGE, ";
							$sql .= " CATEGORY, ";
							$sql .= " DATE_ADD ";
							$sql .= " ) VALUES ( ";
							$sql .= " '".$teamBadge."', ";
							$sql .= " '".$category."', ";
							$sql .= " GetDate() ";
							$sql .= " ) ";
							QueryDatabase($sql, $results);	

						}
					break;


					case "clearLightAlert":
						if (isset($_POST["team"]) && isset($_POST["category"]))  {
							$teamBadge = stripIllegalChars2($_POST["team"]);
							$category = stripIllegalChars2($_POST["category"]);

							error_log("CLEARING ".$category ." Light Alert for ".$teamBadge);

							$sql  = "UPDATE nsa.LIGHTS_ALERTS" . $DB_TEST_FLAG . " SET ";
							$sql .= " DATE_CHG = GetDate(), ";
							$sql .= " FLAG_COMPLETE = 'Y' ";
							$sql .= " WHERE ltrim(TEAM_BADGE) = '".$teamBadge."' ";
							$sql .= " AND CATEGORY = '".$category."' ";
							$sql .= " AND FLAG_COMPLETE is NULL ";
							QueryDatabase($sql, $results);

						}
					break;

					case "saveCompleteMaint":
						if (isset($_POST["maintReq_rowid"]))  {
							$maintReq_rowid = stripIllegalChars2($_POST["maintReq_rowid"]);
							error_log("UPDATING MAINT_REQUESTS" . $DB_TEST_FLAG . " table rowid  ".$maintReq_rowid . " as complete.");

							$sql  = "UPDATE nsa.MAINT_REQUESTS" . $DB_TEST_FLAG . " ";
							$sql .= " SET FLAG_COMPLETE = 'Y', DATE_COMPLETE = GetDate() ";
							$sql .= " WHERE rowid = ".$maintReq_rowid;
							QueryDatabase($sql, $results);	
						}
					break;

					case "saveCompleteOrdPrep":
						if (isset($_POST["opm_rowid"]))  {
							$opm_rowid = stripIllegalChars2($_POST["opm_rowid"]);
							error_log("UPDATING ORD_PREP_MISSING" . $DB_TEST_FLAG . " table rowid  ".$opm_rowid . " as complete.");

							$sql  = "UPDATE nsa.ORD_PREP_MISSING" . $DB_TEST_FLAG . " ";
							$sql .= " SET FLAG_COMPLETE = '1', DATE_CHG = GetDate() ";
							$sql .= " WHERE rowid = ".$opm_rowid;
							QueryDatabase($sql, $results);	
						}
					break;

					


				} // END SWITCH
			}
			echo json_encode(array("returnValue"=> $ret));

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
