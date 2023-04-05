<?php

	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}


	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	require_once('../classes/mail.class.php');

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

			if (isset($_POST["action"])) {
				$Div = "mainDiv";
				$Action = $_POST["action"];

				if (isset($_POST["divclose"])) {
					$ret .= "		<button onClick=\"disablePopup(". $Div .")\">CLOSE</button>\n<br>"; //close popup button
				}

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);

				switch($Action) {

				case "refresh_mainDiv"://refreshing table on main div

					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr class='blueHeader'>\n";
					$ret .= " 	<th colspan=9>First Time / Sample Orders To Be Inspected</th>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th>SO #</th>\n";
					$ret .= " 		<th>Item Number</th>\n";
					$ret .= "		<th>Customer Name</th>";
					$ret .= " 		<th>Order Number</th>\n";
					$ret .= " 		<th>Date Due</th>\n";
					$ret .= " 		<th>Created By</th>\n";
					$ret .= " 	</tr>\n";

					$sql .= "	select ";
					$sql .= "	sh.ID_SO, ";
					$sql .= "	sh.ID_ITEM_PAR, ";
					$sql .= "	oh.NAME_CUST, ";
					$sql .= "	oh.ID_ORD, ";
					//$sql .= "	sh.DATE_DUE_ORD, ";
					$sql .= "	convert(varchar(10),sh.DATE_DUE_ORD,10) as DATE_DUE_ORD,";
					$sql .= "	sh.ID_USER_ADD, ";
					$sql .= "	sh.SUFX_SO ";
					$sql .= "	from nsa.SHPORD_HDR sh ";
					$sql .= "	left join nsa.CP_ORDHDR_PERM oh ";
					$sql .= "	on sh.REF_ORD = oh.ID_ORD ";
					$sql .= "	where sh.ID_BUYER = '1T' ";
					$sql .= "	order by sh.ID_SO asc";
					QueryDatabase($sql, $results);
					error_log($sql);
					while ($row = mssql_fetch_assoc($results)) {
						$ret .= " 	<tr class='dbc'>\n";
						$ret .= " 		<td style='cursor: hand' title='Show " . $row['ID_SO'] . " Details' onclick=\"goToReviewRequestPopUp('" . $row['ID_SO'] . "', '" .$row['SUFX_SO']. "')\">" . $row['ID_SO'] . "</td>\n";
						$ret .= " 		<td>" . $row['ID_ITEM_PAR'] . "</td>\n";
						$ret .= " 		<td>" . $row['NAME_CUST'] . "</td>\n";
						$ret .= " 		<td>" . $row['ID_ORD'] . "</td>\n";
						$ret .= " 		<td>" . $row['DATE_DUE_ORD'] . "</td>\n";
						$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
						$ret .= " 	</tr>\n";
					}//end while
					$ret .= " </table>\n";
					$ret .= "	</br>\n";

				break;

				case "form_newreq":	//new quality inspection inspection
					if (isset($_POST["soNumber"]) /*&& isset($_POST["idItem"]) && isset($_POST["nameCust"]) && isset($_POST["idOrd"])*/ && isset($_POST["soSuffix"]) ) {
							$soNumber = trim($_POST["soNumber"]);
							$soSuffix = $_POST["soSuffix"];

							$sql .= "	select ";
							$sql .= "	sh.ID_SO, ";
							$sql .= "	sh.ID_ITEM_PAR, ";
							$sql .= "	oh.NAME_CUST, ";
							$sql .= "	oh.ID_ORD, ";
							//$sql .= "	sh.DATE_DUE_ORD, ";
							$sql .= "	convert(varchar(10),sh.DATE_DUE_ORD,10) as DATE_DUE_ORD,";
							$sql .= "	sh.ID_USER_ADD, ";
							$sql .= "	sh.SUFX_SO, ";
							$sql .= "	ri.INSPECTION_TYPE, ";
							$sql .= "	ri.NOTES, ";
							$sql .= "	ri.TEAM_BADGE, ";
							$sql .= "	ri.FLAG_PASS_FAIL, ";
							$sql .= "	ri.QTY_PASS, ";
							$sql .= "	ri.QTY_FAIL, ";
							$sql .= "	ri.STATUS ";
							$sql .= "	from nsa.SHPORD_HDR sh ";
							$sql .= "	left join nsa.CP_ORDHDR_PERM oh ";
							$sql .= "	on sh.REF_ORD = oh.ID_ORD ";
							$sql .= "	left join nsa.RD_INSPECTION_INSP ri ";
							$sql .=	"	on ltrim(sh.ID_SO) = ltrim(ri.ID_SO) and sh.SUFX_SO = ri.ID_SO_SUFFIX ";
							$sql .= "	where ltrim(sh.ID_SO) = '" .$soNumber. "' ";

							QueryDatabase($sql, $results);
							error_log($sql);
							while ($row = mssql_fetch_assoc($results)) {

								$passFailSelectP = '';
								$passFailSelectF = '';
								if($row['FLAG_PASS_FAIL'] == 'P'){
									$passFailSelectP = "checked = 'checked'";
								}
								if($row['FLAG_PASS_FAIL'] == 'F'){
									$passFailSelectF = "checked = 'checked'";
								}

								$ret .= " 	<br>\n";
								$ret .= "	<table>";
								$ret .= "	<tr>";
								$ret .= "	<td id='label_soNumber' style='font-size:28pt;'>SO Number: </td>\n";
								$ret .= "	<td><input type='text' maxlength='9' id='soNumber' value='" . $row['ID_SO'] . "' style='height:50px;width:400px;font-size:28pt;'>\n";
								$ret .= " 	<font id='label_soNumber_suffix' style='font-size:28pt;'>-</font>";
								$ret .= "	<input type='text' id='soNumber_suffix' maxlength='4' value='" . $row['SUFX_SO'] ."' maxlength=3 style='height:50px;width:80px;font-size:28pt;'>\n";
								$ret .= "	</tr>";

								$ret .= "	<tr>";
								$ret .= "	<td id='label_soNumber' style='font-size:28pt;'>Item: </td>\n";
								$ret .= "	<td><input type='text' maxlength='25' id='idItem' value='" . $row['ID_ITEM_PAR'] . "' style='height:50px;width:400px;font-size:28pt;'>\n";
								$ret .= "	</tr>";

								$ret .= "	<tr>";
								$ret .= "	<td id='label_soNumber' style='font-size:28pt;'>Customer: </td>\n";
								$ret .= "	<td><input type='text' maxlength='30' id='nameCust' value='" . $row['NAME_CUST'] . "' style='height:50px;width:400px;font-size:28pt;'>\n";
								$ret .= "	</tr>";

								$ret .= "	<tr>";
								$ret .= "	<td id='label_idOrd' style='font-size:28pt;'>Order Number: </td>\n";
								$ret .= "	<td><input type='text' maxlength='9' id='idOrd' value='" . $row['ID_ORD'] . "' style='height:50px;width:400px;font-size:28pt;'>\n";
								$ret .= "	</tr>";

								$ret .= " 	<tr>\n";
								$ret .= " 		<td style='font-size:28pt;'>Inspection Type*: </td>\n";
								$ret .= " 		<td><select id='insp_type' onChange='hideElements()' style='height:50px;width:500px;font-size:28pt;'>";
								/*$ret .= "				<option value=''>--Select</option>";
								$ret .= "				<option value='First Item'>1st Item</option>";
								$ret .= "				<option value='Random'>Random</option>";
								$ret .= "				<option value='100%'>100%</option>";*/

								$inspectionTypeArray = array(
									array("","--Select--"),
									array("First Item","1st Item"),
									array("Random","Random"),
									array("100%","100%"),
								);
								for ($rowInsp = 0; $rowInsp < 4; $rowInsp++) {
									$SELECTED = '';
									$CURRENT = '';

									if(trim($row['INSPECTION_TYPE'])  == trim($inspectionTypeArray[$rowInsp][0]) ){
										$SELECTED = 'SELECTED';
										$CURRENT = '*';
									}
									$ret .= "	<option value='" . $inspectionTypeArray[$rowInsp][0] . "' " . $SELECTED .">" . $CURRENT . $inspectionTypeArray[$rowInsp][1] .  "</option>";
								}

								$ret .= "			</select></td>\n";
								$ret .= " 	</tr>\n";

								$ret .= "	<tr>\n";
								$ret .= "	<td><input id='passFail_P' name='passFail' type='radio' ".$passFailSelectP." value='P' style='width: 3em;height: 3em;'><label id='label_passFail_P' style='font-size:28pt;'>Pass</label></td>\n";
								$ret .= "	<td><input id='passFail_F' name='passFail' type='radio' ".$passFailSelectF." value='F' style='width: 3em;height: 3em;'><label id='label_passFail_F' style='font-size:28pt;'>Fail</label></td>\n";
								$ret .= "	</tr>\n";

								$ret .= "	<tr>\n";//hidden unless correct inspection type
								$ret .= "	<td><label id='label_100_pass' maxlength='4' style='font-size:28pt;display:none;'># Pass  </label><input id='pass100' type='text' value='" . $row['QTY_PASS'] . "' style='height:50px;width:250px;font-size:28pt;display:none;' ></td>\n";
								$ret .= "	<td><label id='label_100_fail' maxlength='4' style='font-size:28pt;display:none;'># Fail  </label><input id='fail100' type='text' value='" . $row['QTY_FAIL'] . "' style='height:50px;width:250px;font-size:28pt;display:none;' ></td>\n";
								$ret .= "	</tr>\n";

								$ret .= "	<tr>\n";
								$ret .= "	<td style='font-size:28pt;'>QA Notes: </td>\n";
								$ret .= "	<td><textarea type='textarea' id='notes' rows='4' cols='40' style='font-size:20pt'>" . $row['NOTES'] . "</textarea>\n";
								$ret .= "	</tr>\n";

								$ret .= "	<tr>\n";
								$ret .= "	<td style='font-size:28pt;'>Team Badge: </td>\n";
								$ret .= "	<td><input type='text' id='teamBadge' maxlength='9' value='" . $row['TEAM_BADGE'] . "' style='height:50px;width:500px;font-size:28pt;'>\n";
								$ret .= "	</tr>\n";

								$ret .= "	<tr>\n";
								$ret .= "	<td style='font-size:28pt;'>Inspector Initials*: </td>\n";
								$ret .= "	<td><input id='inspecInitals' type='text' maxlength='4' value='" . $UserRow['ID_USER'] . "' style='height:50px;width:500px;font-size:28pt;'>\n";
								$ret .= "	</tr>\n";

								$ret .= " 	<tr id='trSubmit' >\n";
								$ret .= "	<th colspan=2><div></br></br><input type='button' style='width: 200px;height: 100px;border-radius: 25px;' value='Submit' onClick='insertNewRecord()' id='btnInsertRecord'></div></th>\n";
								$ret .= " 	</tr>\n";

								$ret .= "	</table>";
								$ret .= " 	<br>\n";
							}//end while
					}//end if
				break;

				case "submit_newrec":
					if (isset($_POST["insp_type"]) && isset($_POST["soNumber"]) && isset($_POST["soNumber_suffix"]) && isset($_POST["itemNumber"]) && isset($_POST["orderNumber"]) && isset($_POST["nameCust"]) 
						&& isset($_POST["passFail"]) && isset($_POST["notes"]) && isset($_POST["teamBadge"]) && isset($_POST["inspecInitals"]) && isset($_POST["pass100"]) && isset($_POST["fail100"]) )
					{
						$insp_type = $_POST["insp_type"];
						$soNumber = $_POST["soNumber"];
						$soNumber_suffix = $_POST["soNumber_suffix"];
						$itemNumber = $_POST["itemNumber"];
						$orderNumber = $_POST["orderNumber"];
						$nameCust = $_POST["nameCust"];
						$passFail = $_POST["passFail"];
						$notes = $_POST["notes"];
						$teamBadge = $_POST["teamBadge"];
						$inspecInitals = $_POST["inspecInitals"];
						$pass100 = $_POST["pass100"];
						$fail100 = $_POST["fail100"];
						$status = 'Waiting on Production';


						$sql = " insert into nsa.RD_INSPECTION_INSP (";
						$sql .= " ID_USER_ADD, ";
						$sql .= " DATE_ADD, ";
						$sql .= " ID_ORD, ";
						$sql .= " ID_SO, ";
						$sql .= " ID_SO_SUFFIX, ";
						$sql .= " ID_ITEM, ";
						$sql .= " NAME_CUST, ";
						$sql .= " INSPECTION_TYPE, ";
						if($passFail != ''){
						$sql .= " FLAG_PASS_FAIL, ";
						}
						if($pass100 != ''){
							$sql .= " QTY_PASS, ";
						}
						if($fail100 != ''){
							$sql .= " QTY_FAIL, ";
						}
						$sql .= " NOTES, ";
						$sql .= " TEAM_BADGE, ";
						$sql .= " INSP_INITIALS, ";
						$sql .= " STATUS )";
						$sql .= " VALUES (";
						$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
						$sql .= " GetDate(), ";
						$sql .= " '" . ms_escape_string($orderNumber) . "', ";
						$sql .= " '" . ms_escape_string($soNumber) . "', ";
						$sql .= " '" . $soNumber_suffix . "', ";
						$sql .= " '" . ms_escape_string($itemNumber) . "', ";				
						$sql .= " '" . ms_escape_string($nameCust) . "', ";
						$sql .= " '" . ms_escape_string($insp_type) . "', ";
						if($passFail != ''){
							$sql .= " '" . ms_escape_string($passFail) . "', ";
						}
						if($pass100 != ''){
							$sql .= " '" . ($pass100) . "', ";
						}
						if($fail100 != ''){
							$sql .= " '" . ($fail100) . "', ";
						}
						$sql .= " '" . ms_escape_string($notes) . "', ";
						$sql .= " '" . trim(ms_escape_string($teamBadge)) . "', ";
						$sql .= " '" . ms_escape_string($inspecInitals) . "', ";
						$sql .= " '" . $status ."'";
						$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";
						$sql .= " ";
						error_log($sql);
						QueryDatabase($sql, $results);
						$row = mssql_fetch_assoc($results);
						$BaseRowID = $row['LAST_INSERT_ID'];

					}
				break;

				case "review_inspection":

					
				break;

				}//end switch

			}//end if

			if (isset($_POST["divclose"])) {
				$ret .= "		<button onClick=\"disablePopup(". $Div .")\">CLOSE</button>\n";//close popup button
			}
			echo json_encode(array("returnValue"=> $ret));
		}//end else
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}//end if

		return $ret;
	}//end else
?>