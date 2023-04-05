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
			$READONLY = "READONLY style='background-color:#D0D0D0;'";

			if (isset($_POST["action"])) {
				$Div = "mainDiv";
				$Action = $_POST["action"];

				if (isset($_POST["divclose"])) {
					$ret .= "		<font onClick=\"disablePopup(". $Div .")\">CLOSE</font>\n";
				}

				switch($Action) {
					case "refresh_mainDiv":
						if (strpos($UserRow['EMP_ROLE'], ":REVIEWER:") !== FALSE ) { //:MAINT:REVIEWER:RND-HEAD:
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th colspan=5>Pending Requests for Review</th>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<th>Req #</th>\n";
							$ret .= " 		<th>Current Status</th>\n";
							$ret .= " 		<th>Item</th>\n";
							$ret .= " 		<th>Date Created</th>\n";
							$ret .= " 		<th>Created By</th>\n";
							$ret .= " 	</tr>\n";

							$sql =  "select ";
							$sql .= " 	* ";
							$sql .= " from ";
							$sql .= " 	nsa.RnD_REQ_BASE ";
							$sql .= " where ";
							$sql .= " 	CURR_STATUS in ('1','2')";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr class='dbc'>\n";
								$ret .= " 		<td id='Req_".$row['ID_REQ']."' name='Req_".$row['ID_REQ']."' onclick=\"goToReviewRequestPopUp('" . $row['ID_REQ'] . "')\">" . $row['ID_REQ'] . "</td>\n";
								$ret .= " 		<td>" . GetStrRnDStatusCode($row['CURR_STATUS']) . "</td>\n";
								$ret .= " 		<td>" . $row['ID_ITEM'] . "</td>\n";
								$ret .= " 		<td>" . $row['DATE_ADD'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
								$ret .= " 	</tr>\n";
							}
							$ret .= " </table>\n";
							$ret .= "	</br>\n";
						}

						if (strpos($UserRow['EMP_ROLE'], ":RND-HEAD:") !== FALSE ) { //:MAINT:REVIEWER:RND-HEAD:
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th colspan=5>Requests Awaiting Assignment within R&D</th>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<th>Req #</th>\n";
							$ret .= " 		<th>Current Status</th>\n";
							$ret .= " 		<th>Item</th>\n";
							$ret .= " 		<th>Date Created</th>\n";
							$ret .= " 		<th>Created By</th>\n";
							$ret .= " 	</tr>\n";

							$sql =  "SELECT ";
							$sql .= "  * ";
							$sql .= " FROM ";
							$sql .= "  nsa.RnD_REQ_BASE ";
							$sql .= " WHERE ";
							$sql .= "  CURR_STATUS = '10'";
							$sql .= "  and CURR_DEPT = 'RND'";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr class='dbc'>\n";
								$ret .= " 		<td id='Req_".$row['ID_REQ']."' name='Req_".$row['ID_REQ']."' onclick=\"goToAssignRequestPopUp('" . $row['ID_REQ'] . "')\">" . $row['ID_REQ'] . "</td>\n";
								$ret .= " 		<td>" . GetStrRnDStatusCode($row['CURR_STATUS']) . "</td>\n";
								$ret .= " 		<td>" . $row['ID_ITEM'] . "</td>\n";
								$ret .= " 		<td>" . $row['DATE_ADD'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
								$ret .= " 	</tr>\n";
							}
							$ret .= " </table>\n";
							$ret .= "	</br>\n";
						}

						if (strpos($UserRow['EMP_ROLE'], ":PREPROD-HEAD:") !== FALSE ) {
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th colspan=5>Requests Awaiting Assignment within Pre-Production</th>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<th>Req #</th>\n";
							$ret .= " 		<th>Current Status</th>\n";
							$ret .= " 		<th>Item</th>\n";
							$ret .= " 		<th>Date Created</th>\n";
							$ret .= " 		<th>Created By</th>\n";
							$ret .= " 	</tr>\n";

							$sql =  "SELECT ";
							$sql .= "  * ";
							$sql .= " FROM ";
							$sql .= "  nsa.RnD_REQ_BASE ";
							$sql .= " WHERE ";
							$sql .= "  CURR_STATUS = '10'";
							$sql .= "  and CURR_DEPT = 'PREPROD'";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr class='dbc'>\n";
								$ret .= " 		<td id='Req_".$row['ID_REQ']."' name='Req_".$row['ID_REQ']."' onclick=\"goToAssignRequestPopUp('" . $row['ID_REQ'] . "')\">" . $row['ID_REQ'] . "</td>\n";
								$ret .= " 		<td>" . GetStrRnDStatusCode($row['CURR_STATUS']) . "</td>\n";
								$ret .= " 		<td>" . $row['ID_ITEM'] . "</td>\n";
								$ret .= " 		<td>" . $row['DATE_ADD'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
								$ret .= " 	</tr>\n";
							}
							$ret .= " </table>\n";
							$ret .= "	</br>\n";
						}

						//// OPEN REQUESTS
						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr class='blueHeader'>\n";
						$ret .= " 		<th colspan=5>My Requests</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th>Req #</th>\n";
						$ret .= " 		<th>Current Status</th>\n";
						$ret .= " 		<th>Item</th>\n";
						$ret .= " 		<th>Date Created</th>\n";
						$ret .= " 		<th>Created By</th>\n";
						$ret .= " 	</tr>\n";

						$sql =  "SELECT ";
						$sql .= "  * ";
						$sql .= " FROM ";
						$sql .= "  nsa.RnD_REQ_BASE ";
						$sql .= " WHERE ";
						$sql .= "  CURR_STATUS >= 20 ";
						$sql .= "  and CURR_USER = '" . $UserRow['ID_USER'] . "'";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							$ret .= " 	<tr class='dbc'>\n";
							$ret .= " 		<td id='Req_".$row['ID_REQ']."' name='Req_".$row['ID_REQ']."' onclick=\"goToWorkRequestPopUp('" . $row['ID_REQ'] . "')\">" . $row['ID_REQ'] . "</td>\n";
							$ret .= " 		<td>" . GetStrRnDStatusCode($row['CURR_STATUS']) . "</td>\n";
							$ret .= " 		<td>" . $row['ID_ITEM'] . "</td>\n";
							$ret .= " 		<td>" . $row['DATE_ADD'] . "</td>\n";
							$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
							$ret .= " 	</tr>\n";
						}
						$ret .= " </table>\n";
						$ret .= "	</br>\n";
					break;
					case "form_newreq":
						$ret .= "<table >\n";
						$ret .= "	<tr class='blueHeader'>\n";
						$ret .= "		<th colspan=4>R&D Change Request Form</th>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbr'>\n";
						$ret .= "		<td>Item Number:</td>\n";
						$ret .= "		<td><input id='txt_itemNumber' name='txt_itemNumber' type='text'></input></td>\n";
						$ret .= "		<td>Related Shop Order #:</td>\n";
						$ret .= "		<td><input id='txt_relatedSO' name='txt_relatedSO' type='text'></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbr'>\n";
						$ret .= "		<td>Requested By:</td>\n";
						$ret .= "		<td>". $UserRow['NAME_EMP'] ."</td>\n";
						$ret .= "		<td>Date:</td>\n";
						$ret .= "		<td>". date('Y-m-d') ."</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbr'>\n";
						$ret .= "		<td>Description of Requested Change:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_Descr' name='txt_Descr' cols='55'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbr'>\n";
						$ret .= "		<td>Reason for Change:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_Reason' name='txt_Reason' cols='55'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td colspan=4><input id='button_SubmitNew' name='button_SubmitNew' type='button' value='Submit' onClick=\"saveNewRequest()\"></input><div id='div_submitResp' name='div_submitResp'></div></td>\n";

						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
					break;
					case "submit_newreq":
						if (isset($_POST["itemNumber"]) && isset($_POST["relatedSO"]) && isset($_POST["descr"]) && isset($_POST["reason"])) {
							$itemNumber = $_POST["itemNumber"];
							$relatedSO = $_POST["relatedSO"];
							$descr = $_POST["descr"];
							$reason = $_POST["reason"];

							//error_log($descr);

							$sql  = " insert into nsa.RnD_REQ_BASE( ";
							$sql .= "  ID_USER_ADD, ";
							$sql .= "  DATE_ADD, ";
							$sql .= "  ID_ITEM, ";
							$sql .= "  ID_SO, ";
							$sql .= "  DESCR, ";
							$sql .= "  REASON, ";
							$sql .= "  CURR_STATUS ";
							$sql .= " ) VALUES ( ";
 							$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
							$sql .= "  GetDate(), ";
							$sql .= " '" . stripIllegalChars2($itemNumber) . "', ";
							$sql .= " '" . stripIllegalChars2($relatedSO) . "', ";
							$sql .= " '" . stripIllegalChars2($descr) . "', ";
							$sql .= " '" . stripIllegalChars2($reason) . "', ";
							$sql .= " '1' ";
							$sql .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";

							QueryDatabase($sql, $results);
							$row = mssql_fetch_assoc($results);
							$BaseRowID = $row['LAST_INSERT_ID'];

							$a_to  = GetEmailSubscribers('REVIEWER');
							foreach ($a_to as $to) {
								if ($to <> '0') {
									$subject = "New R&D Change Request form to Review " . $_POST['dateapp'];
									$body = $UserRow['NAME_EMP'] . " submitted an R&D Change Request for item(s) " . stripIllegalChars2($itemNumber) .  ".\r\n\r\n" .
										"Please login to Review.";

									$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
										"X-Mailer: PHP/" . phpversion();
									if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
										$to = "gvandyne@thinkNSA.com";
									}
									error_log("REVIEWER: " . $to);
									mail($to, $subject, $body, $headers);
								}
							}

							$ret .= "<font>OK</font>\n";
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}

					break;
					case "form_reviewreq":
						if (isset($_POST["req"])) {
							$ID_REQ = $_POST["req"];

							$sql =  "select ";
							$sql .= " wa.NAME_EMP, ";
							$sql .= " rb.* ";
							$sql .= " from nsa.RND_REQ_BASE rb ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " where ";
							$sql .= " rb.ID_REQ = ". $ID_REQ;
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "<table >\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=4>Submitted Request</th>\n";
								$ret .= "		<input id='hidden_ID_REQ' name='hidden_ID_REQ' type='hidden' value='" . $ID_REQ . "'>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Item Number:</td>\n";
								$ret .= "		<td><input id='txt_itemNumber' name='txt_itemNumber' type='text' value='". $row['ID_ITEM'] ."' $READONLY></input></td>\n";
								$ret .= "		<td>Related Shop Order #:</td>\n";
								$ret .= "		<td><input id='txt_relatedSO' name='txt_relatedSO' type='text' value='". $row['ID_SO'] ."' $READONLY></input></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Requested By:</td>\n";
								$ret .= "		<td>". $row['NAME_EMP'] ."</td>\n";
								$ret .= "		<td>Date:</td>\n";
								$ret .= "		<td>". $row['DATE_ADD'] ."</td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Description of Requested Change:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Descr' name='txt_Descr' cols='55' $READONLY>".$row['DESCR']."</textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Reason for Change:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Reason' name='txt_Reason' cols='55' $READONLY>".$row['REASON']."</textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=4>Review of Request</th>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Approved?:</td>\n";
								$ret .= "		<td><select id='select_approve' onChange=\"showDeptSelect()\">\n";
								$ret .= "			<option value=''>-- Select --</option>\n";
								$ret .= "			<option value='0'>No</option>\n";
								$ret .= "			<option value='10'>Yes</option>\n";
								$ret .= "		</select></td>\n";
								$ret .= "		<td colspan=2 id='td_selectDept'><input type='hidden' id='select_dept' name='select_dept'></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Comments:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Comment' name='txt_Comment' cols='55'></textarea></td>\n";
								$ret .= "	</tr>\n";

								$ret .= "	<tr class='dbc'>\n";
								$ret .= "		<td colspan=4><input id='button_SubmitReview' name='button_SubmitReview' type='button' value='Submit' onClick=\"saveReviewRequest()\"></input><div id='div_submitResp' name='div_submitResp'></div></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "</table>\n";
							}
						}
					break;
					case "submit_reviewreq":
						if (isset($_POST["id_req"]) && isset($_POST["approve"]) && isset($_POST["dept"]) && isset($_POST["comments"])) {
							$id_req = $_POST["id_req"];
							$status = $_POST["approve"];
							$dept = $_POST["dept"];
							$comments = $_POST["comments"];

							//error_log($descr);

							$sql  = " insert into nsa.RnD_REQ_DETAIL( ";
							$sql .= "  ID_REQ, ";
							$sql .= "  ID_USER_ADD, ";
							$sql .= "  DATE_ADD, ";
							$sql .= "  STATUS, ";
							$sql .= "  DEPT, ";
							$sql .= "  COMMENT ";
							$sql .= " ) VALUES ( ";
 							$sql .= " '" . stripIllegalChars2($id_req) . "', ";
 							$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
							$sql .= "  GetDate(), ";
							$sql .= " '" . stripIllegalChars2($status) . "', ";
							$sql .= " '" . stripIllegalChars2($dept) . "', ";
							$sql .= " '" . stripIllegalChars2($comments) . "' ";
							$sql .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";

							QueryDatabase($sql, $results);
							$row = mssql_fetch_assoc($results);
							$DetailRowID = $row['LAST_INSERT_ID'];

							$sql  = " UPDATE nsa.RnD_REQ_BASE set ";
							$sql .= "  CURR_STATUS = '" . $status ."', ";
							if ($status == '0') {
								$sql .= "  DATE_CLOSED = GetDate(), ";
							}
							$sql .= "  CURR_DEPT = '" . $dept ."', ";
							$sql .= "  CURR_DETAIL_ROWID = '" . $DetailRowID ."' ";
							$sql .= " WHERE ID_REQ = '" . $id_req ."' ";
							QueryDatabase($sql, $results);

							$a_to  = GetEmailSubscribers($dept . '-HEAD');
							foreach ($a_to as $to) {
								if ($to <> '0') {
									$subject = "R&D Change Request form to Assign " . $_POST['dateapp'];
									$body = $UserRow['NAME_EMP'] . " approved an R&D Change Request to be assigned.\r\n\r\n" .
										"Please login to Assign.";

									$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
										"X-Mailer: PHP/" . phpversion();
									if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
										$to = "gvandyne@thinkNSA.com";
									}
									error_log("DEPT HEAD: " . $to);
									mail($to, $subject, $body, $headers);
								}
							}

							$ret .= "<font>OK</font>\n";
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}
					break;
					case "form_assignreq":
						if (isset($_POST["req"])) {
							$ID_REQ = $_POST["req"];

							$sql =  "select ";
							$sql .= " wa.NAME_EMP, ";
							$sql .= " rb.* ";
							$sql .= " from nsa.RND_REQ_BASE rb ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " where ";
							$sql .= " rb.ID_REQ = ". $ID_REQ;
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$assignedDept = $row['CURR_DEPT'];
								$ret .= "<table >\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=4>Submitted Request</th>\n";
								$ret .= "		<input type='hidden' id='hidden_ID_REQ' name='hidden_ID_REQ' value='" . $ID_REQ . "'>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Item Number:</td>\n";
								$ret .= "		<td><input id='txt_itemNumber' name='txt_itemNumber' type='text' value='". $row['ID_ITEM'] ."' $READONLY></input></td>\n";
								$ret .= "		<td>Related Shop Order #:</td>\n";
								$ret .= "		<td><input id='txt_relatedSO' name='txt_relatedSO' type='text' value='". $row['ID_SO'] ."' $READONLY></input></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Requested By:</td>\n";
								$ret .= "		<td>". $row['NAME_EMP'] ."</td>\n";
								$ret .= "		<td>Date:</td>\n";
								$ret .= "		<td>". $row['DATE_ADD'] ."</td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Description of Requested Change:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Descr' name='txt_Descr' cols='55' $READONLY>".$row['DESCR']."</textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Reason for Change:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Reason' name='txt_Reason' cols='55' $READONLY>".$row['REASON']."</textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=4>Review of Request</th>\n";
								$ret .= "	</tr>\n";

								$sql1 =  "SELECT ";
								$sql1 .= "  wa.NAME_EMP, ";
								$sql1 .= "  rd.* ";
								$sql1 .= " FROM nsa.RND_REQ_DETAIL rd ";
								$sql1 .= "  left join nsa.DCWEB_AUTH wa ";
								$sql1 .= "  on rd.ID_USER_ADD = wa.ID_USER ";
								$sql1 .= " WHERE ";
								$sql1 .= "  rd.ID_REQ = ". $ID_REQ;
								$sql1 .= "  and STATUS = '10'";
								$sql1 .= " ORDER BY rowid asc";
								QueryDatabase($sql1, $results1);

								while ($row1 = mssql_fetch_assoc($results1)) {
									$assignedDept = $row1['DEPT'];

									$ret .= "	<tr class='dbr'>\n";
									$ret .= "		<td>Approved By:</td>\n";
									$ret .= "		<td>". $row1['NAME_EMP'] ."</td>\n";
									$ret .= "		<td>Date:</td>\n";
									$ret .= "		<td>". $row1['DATE_ADD'] ."</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbr'>\n";
									$ret .= "		<td>Assigned to Dept:</td>\n";
									$ret .= "		<td>". $row1['DEPT'] ."</td>\n";
									$ret .= "		<td colspan=2></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbr'>\n";
									$ret .= "		<td>Comments:</td>\n";
									$ret .= "		<td colspan=3><textarea cols='55' $READONLY style='background-color:#D0D0D0;'>" . $row1['COMMENT'] . "</textarea></td>\n";
									$ret .= "	</tr>\n";
								}

								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=4>Assign Request</th>\n";
								$ret .= "		<input type='hidden' id='hidden_DEPT' name='hidden_DEPT' value='" . $assignedDept . "'>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Assign to:</td>\n";
								$ret .= "		<td><select id='select_assignUser' name='select_assignUser' onChange=\"selectAssignChanged()\">\n";
								$ret .= "			<option value=''>-- Select --</option>\n";
								$ret .= "			<option value='DECLINE'>-- DECLINE --</option>\n";

								$sql1 =  "SELECT ";
								$sql1 .= "  ID_USER, ";
								$sql1 .= "  NAME_EMP ";
								$sql1 .= " FROM nsa.DCWEB_AUTH ";
								$sql1 .= " WHERE ";
								$sql1 .= "  EMP_ROLE like '%:" . $assignedDept . ":%' ";
								$sql1 .= " ORDER BY NAME_EMP asc";
								QueryDatabase($sql1, $results1);
								while ($row1 = mssql_fetch_assoc($results1)) {
									$ret .= "			<option value='".$row1['ID_USER']."'>".$row1['NAME_EMP']."</option>\n";
								}

								$ret .= "		</select></td>\n";
								$ret .= "		<td colspan=2></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Comments:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Comment' name='txt_Comment' cols='55'></textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbc'>\n";
								$ret .= "		<td colspan=4><input id='button_SubmitAssign' name='button_SubmitAssign' type='button' value='Submit' onClick=\"saveAssignRequest()\"></input><div id='div_submitResp' name='div_submitResp'></div></td>\n";
								$ret .= "	</tr>\n";

								$ret .= "</table>\n";
							}
						}
					break;
					case "submit_assignreq":
						if (isset($_POST["id_req"]) && isset($_POST["assignUser"]) && isset($_POST["dept"]) && isset($_POST["comments"])) {
							$id_req = $_POST["id_req"];
							$assignUser = $_POST["assignUser"];
							$dept = $_POST["dept"];
							$comments = $_POST["comments"];
							$user = $assignUser;
							$status = 20;

							if ($assignUser == 'DECLINE') {
								$dept = '';
								$user = '';
								$status = 2;
							}

							$sql  = " insert into nsa.RnD_REQ_DETAIL( ";
							$sql .= "  ID_REQ, ";
							$sql .= "  ID_USER_ADD, ";
							$sql .= "  DATE_ADD, ";
							$sql .= "  STATUS, ";
							$sql .= "  DEPT, ";
							$sql .= "  USER1, ";
							$sql .= "  COMMENT ";
							$sql .= " ) VALUES ( ";
 							$sql .= " '" . stripIllegalChars2($id_req) . "', ";
 							$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
							$sql .= "  GetDate(), ";
							$sql .= " '" . stripIllegalChars2($status) . "', ";
							$sql .= " '" . stripIllegalChars2($dept) . "', ";
							$sql .= " '" . stripIllegalChars2($user) . "', ";
							$sql .= " '" . stripIllegalChars2($comments) . "' ";
							$sql .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
							QueryDatabase($sql, $results);

							$row = mssql_fetch_assoc($results);
							$DetailRowID = $row['LAST_INSERT_ID'];

							$sql  = " UPDATE nsa.RnD_REQ_BASE set ";
							$sql .= "  CURR_STATUS = '" . $status ."', ";
							$sql .= "  CURR_DEPT = '" . $dept ."', ";
							$sql .= "  CURR_USER = '" . $user ."', ";
							$sql .= "  CURR_DETAIL_ROWID = '" . $DetailRowID ."' ";
							$sql .= " WHERE ID_REQ = '" . $id_req ."' ";
							QueryDatabase($sql, $results);

							if ($assignUser <> 'DECLINE') {
								$sql  = "SELECT EMAIL";
								$sql .= " FROM nsa.DCWEB_AUTH ";
								$sql .= " WHERE ID_USER = '" . $user ."' ";
								QueryDatabase($sql, $results);
								$row = mssql_fetch_assoc($results);

								$to = $row['EMAIL'];
								$subject = "An R&D Change Request has been Assigned to you " . $_POST['dateapp'];
								$body = $UserRow['NAME_EMP'] . " assigned an R&D Change Request to you.\r\n\r\n" .
									"Please login to view.";

								$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
									"X-Mailer: PHP/" . phpversion();
								if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
									$to = "gvandyne@thinkNSA.com";
								}
								error_log("ASSIGNED : " . $to);
								mail($to, $subject, $body, $headers);
							} else {
								$a_to  = GetEmailSubscribers('REVIEWER');
								foreach ($a_to as $to) {
									if ($to <> '0') {
										$subject = "An R&D Change Request has been DECLINED " . $_POST['dateapp'];
										$body = $UserRow['NAME_EMP'] . " DECLINED an R&D Change Request \r\n\r\n" .
											"Please login to Review.";

										$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
											"X-Mailer: PHP/" . phpversion();
										if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
											$to = "gvandyne@thinkNSA.com";
										}
										error_log("DECLINED: " . $to);
										mail($to, $subject, $body, $headers);
									}
								}
							}
							$ret .= "<font>OK</font>\n";
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}
					break;
					case "form_workreq":
						if (isset($_POST["req"])) {
							$ID_REQ = $_POST["req"];

							$sql =  "select ";
							$sql .= " wa.NAME_EMP, ";
							$sql .= " rb.* ";
							$sql .= " from nsa.RND_REQ_BASE rb ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " where ";
							$sql .= " rb.ID_REQ = ". $ID_REQ;
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$assignedDept = $row['CURR_DEPT'];
								$ret .= "<table >\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=4>Submitted Request</th>\n";
								$ret .= "		<input type='hidden' id='hidden_ID_REQ' name='hidden_ID_REQ' value='" . $ID_REQ . "'>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Item Number:</td>\n";
								$ret .= "		<td><input id='txt_itemNumber' name='txt_itemNumber' type='text' value='". $row['ID_ITEM'] ."' $READONLY></input></td>\n";
								$ret .= "		<td>Related Shop Order #:</td>\n";
								$ret .= "		<td><input id='txt_relatedSO' name='txt_relatedSO' type='text' value='". $row['ID_SO'] ."' $READONLY></input></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Requested By:</td>\n";
								$ret .= "		<td>". $row['NAME_EMP'] ."</td>\n";
								$ret .= "		<td>Date:</td>\n";
								$ret .= "		<td>". $row['DATE_ADD'] ."</td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Description of Requested Change:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Descr' name='txt_Descr' cols='55' $READONLY>".$row['DESCR']."</textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Reason for Change:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Reason' name='txt_Reason' cols='55' $READONLY>".$row['REASON']."</textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=5>Reviewal & Assignment of Request</th>\n";
								$ret .= "	</tr>\n";

								$sql1 =  "SELECT ";
								$sql1 .= "  wa.NAME_EMP, ";
								$sql1 .= "  rd.* ";
								$sql1 .= " FROM nsa.RND_REQ_DETAIL rd ";
								$sql1 .= "  left join nsa.DCWEB_AUTH wa ";
								$sql1 .= "  on rd.ID_USER_ADD = wa.ID_USER ";
								$sql1 .= " WHERE ";
								$sql1 .= "  rd.ID_REQ = '". $ID_REQ ."' ";
								$sql1 .= "  and STATUS in (10,20) ";
								$sql1 .= " ORDER BY rowid asc";
								QueryDatabase($sql1, $results1);

								while ($row1 = mssql_fetch_assoc($results1)) {
									$assignedDept = $row1['DEPT'];
									switch($row1['STATUS']) {
										case "10":
											$ret .= "	<tr class='dbr'>\n";
											$ret .= "		<td>Approved By:</td>\n";
											$ret .= "		<td>". $row1['NAME_EMP'] ."</td>\n";
											$ret .= "		<td>Date:</td>\n";
											$ret .= "		<td>". $row1['DATE_ADD'] ."</td>\n";
											$ret .= "	</tr>\n";
											$ret .= "	<tr class='dbr'>\n";
											$ret .= "		<td>Assigned to Dept:</td>\n";
											$ret .= "		<td>". $row1['DEPT'] ."</td>\n";
											$ret .= "		<td colspan=2></td>\n";
											$ret .= "	</tr>\n";
											$ret .= "	<tr class='dbr'>\n";
											$ret .= "		<td>Comments:</td>\n";
											$ret .= "		<td colspan=3><textarea cols='55' $READONLY>" . $row1['COMMENT'] . "</textarea></td>\n";
											$ret .= "	</tr>\n";
										break;
										case "20":
											$ret .= "	<tr class='dbr'>\n";
											$ret .= "		<td>Assigned By:</td>\n";
											$ret .= "		<td>". $row1['NAME_EMP'] ."</td>\n";
											$ret .= "		<td>Date:</td>\n";
											$ret .= "		<td>". $row1['DATE_ADD'] ."</td>\n";
											$ret .= "	</tr>\n";
											$ret .= "	<tr class='dbr'>\n";
											$ret .= "		<td>Comments:</td>\n";
											$ret .= "		<td colspan=3><textarea cols='55' $READONLY>" . $row1['COMMENT'] . "</textarea></td>\n";
											$ret .= "	</tr>\n";
										break;
									}
								}

								$ret .= "	<tr class='blueHeader'>\n";
								$ret .= "		<th colspan=4>Working Request</th>\n";
								$ret .= "		<input type='hidden' id='hidden_DEPT' name='hidden_DEPT' value='" . $assignedDept . "'>\n";

								$sql1 =  "SELECT ";
								$sql1 .= "  wa.NAME_EMP, ";
								$sql1 .= "  rd.* ";
								$sql1 .= " FROM nsa.RND_REQ_DETAIL rd ";
								$sql1 .= "  left join nsa.DCWEB_AUTH wa ";
								$sql1 .= "  on rd.ID_USER_ADD = wa.ID_USER ";
								$sql1 .= " WHERE ";
								$sql1 .= "  rd.ID_REQ = '". $ID_REQ . "' ";
								$sql1 .= "  and STATUS in (30,100) ";
								$sql1 .= " ORDER BY rowid asc";
								QueryDatabase($sql1, $results1);
								while ($row1 = mssql_fetch_assoc($results1)) {
									$cmpl = '';
									if ($row1['STATUS'] == '100') {
										$cmpl = 'Closed by ';
									}

									$ret .= "	<tr class='dbr'>\n";
									$ret .= "		<td>" . $row1['DATE_ADD'] . "</td>\n";
									$ret .= "		<td colspan=3 rowspan=2><textarea cols='55' $READONLY>" . $row1['COMMENT'] . "</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbr'>\n";
									$ret .= "		<td>" . $cmpl . $row1['NAME_EMP'] . "</td>\n";
									$ret .= "	</tr>\n";
								}

								$ret .= "	<tr id='div_submitWorkResp' name='div_submitWorkResp' class='dbr'></tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>Comments:</td>\n";
								$ret .= "		<td colspan=3><textarea id='txt_Comment' name='txt_Comment' cols='55'></textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbc'>\n";
								$ret .= "		<td colspan=4>\n";

								$cmplchecked = '';
								if ($row['CURR_STATUS'] == '100') {
									$cmplchecked = 'CHECKED';
								}
								
								$ret .= "			<input id='checkbox_Complete' name='checkbox_Complete' type='checkbox' " . $cmplchecked . ">Complete</input>\n";
								$ret .= "			<input id='button_SubmitWork' name='button_SubmitWork' type='button' value='Submit' onClick=\"saveWorkRequest()\"></input>\n";
								$ret .= "		</td>\n";
								$ret .= "	</tr>\n";
								$ret .= "</table>\n";
							}
						}
					break;
					case "submit_workreq":
						if (isset($_POST["id_req"]) && isset($_POST["flag_complete"]) && isset($_POST["dept"]) && isset($_POST["comments"])) {
							$ID_REQ = $_POST["id_req"];
							$flag_complete = $_POST["flag_complete"];
							$dept = $_POST["dept"];
							$comments = $_POST["comments"];
							$status = 30;
							error_log("Flag Complete: " . $flag_complete);

							if ($flag_complete == "true") {
								$status = 100;
								error_log("Flag Complete2: ");
							}

							$sql  = " insert into nsa.RnD_REQ_DETAIL( ";
							$sql .= "  ID_REQ, ";
							$sql .= "  ID_USER_ADD, ";
							$sql .= "  DATE_ADD, ";
							$sql .= "  STATUS, ";
							$sql .= "  DEPT, ";
							$sql .= "  USER1, ";
							$sql .= "  COMMENT ";
							$sql .= " ) VALUES ( ";
 							$sql .= " '" . stripIllegalChars2($ID_REQ) . "', ";
 							$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
							$sql .= "  GetDate(), ";
							$sql .= " '" . stripIllegalChars2($status) . "', ";
							$sql .= " '" . stripIllegalChars2($dept) . "', ";
							$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
							$sql .= " '" . stripIllegalChars2($comments) . "' ";
							$sql .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
							QueryDatabase($sql, $results);

							$row = mssql_fetch_assoc($results);
							$DetailRowID = $row['LAST_INSERT_ID'];

							$sql  = " UPDATE nsa.RnD_REQ_BASE set ";
							$sql .= "  CURR_STATUS = '" . $status ."', ";
							$sql .= "  CURR_DEPT = '" . $dept ."', ";
							$sql .= "  CURR_USER = '" . $UserRow['ID_USER'] ."', ";
							if ($flag_complete == true) {
								$sql .= "  DATE_CLOSED = GetDate(), ";
							}
							$sql .= "  CURR_DETAIL_ROWID = '" . $DetailRowID ."' ";
							$sql .= " WHERE ID_REQ = '" . $ID_REQ ."' ";
							QueryDatabase($sql, $results);

							$sql1 =  "SELECT ";
							$sql1 .= "  wa.NAME_EMP, ";
							$sql1 .= "  rd.* ";
							$sql1 .= " FROM nsa.RND_REQ_DETAIL rd ";
							$sql1 .= "  left join nsa.DCWEB_AUTH wa ";
							$sql1 .= "  on rd.ID_USER_ADD = wa.ID_USER ";
							$sql1 .= " WHERE ";
							$sql1 .= "  rd.rowid = '" . $DetailRowID . "' ";
							$sql1 .= " ORDER BY rowid asc";
							QueryDatabase($sql1, $results1);
							while ($row1 = mssql_fetch_assoc($results1)) {
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>" . $row1['DATE_ADD'] . "</td>\n";
								$ret .= "		<td colspan=3 rowspan=2><textarea cols='55' $READONLY>" . $row1['COMMENT'] . "</textarea></td>\n";
								$ret .= "	</tr>\n";
								$ret .= "	<tr class='dbr'>\n";
								$ret .= "		<td>" . $row1['NAME_EMP'] . "</td>\n";
								$ret .= "	</tr>\n";
							}
							$ret .= "	<tr id='div_submitWorkResp' name='div_submitWorkResp' class='dbr'></tr>\n";
/*
							if ($assignUser <> 'DECLINE') {
								$sql  = "SELECT EMAIL";
								$sql .= " FROM nsa.DCWEB_AUTH ";
								$sql .= " WHERE ID_USER = '" . $user ."' ";
								QueryDatabase($sql, $results);
								$row = mssql_fetch_assoc($results);

								$to = $row['EMAIL'];
								$subject = "An R&D Change Request has been Assigned to you " . $_POST['dateapp'];
								$body = $UserRow['NAME_EMP'] . " assigned an R&D Change Request to you.\r\n\r\n" .
									"Please login to view.";

								$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
									"X-Mailer: PHP/" . phpversion();
								if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
									$to = "gvandyne@thinkNSA.com";
								}
								error_log("ASSIGNED : " . $to);
								mail($to, $subject, $body, $headers);
							} else {
								$a_to  = GetEmailSubscribers('REVIEWER');
								foreach ($a_to as $to) {
									if ($to <> '0') {
										$subject = "An R&D Change Request has been DECLINED " . $_POST['dateapp'];
										$body = $UserRow['NAME_EMP'] . " DECLINED an R&D Change Request \r\n\r\n" .
											"Please login to Review.";

										$headers = "From: eProduction@thinkNSA.com" . "\r\n" .
											"X-Mailer: PHP/" . phpversion();
										if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
											$to = "gvandyne@thinkNSA.com";
										}
										error_log("DECLINED: " . $to);
										mail($to, $subject, $body, $headers);
									}
								}
							}
*/

							//$ret .= "<font>OK</font>\n";
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}
					break;
				}
			}
			if (isset($_POST["divclose"])) {
				$ret .= "		<font onClick=\"disablePopup(". $Div .")\">CLOSE</font>\n";
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
