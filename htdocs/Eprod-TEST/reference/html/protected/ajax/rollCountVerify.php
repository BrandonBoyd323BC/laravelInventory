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
			$action = $_POST["action"];

			switch($action){

				//////////////////////////////////////////////
				//////////INSERT NEW RECORD INTO SQL
				//////////////////////////////////////////////
				case("insertRecord");
					if (isset($_POST["item"]) && isset($_POST["choice"]) && isset($_POST["findings"]))  {
						$Item	= strtoupper($_POST["item"]);
						$Choice	= $_POST["choice"];
						$Findings	= $_POST["findings"];

						$sql00 = "SELECT ID_ITEM FROM nsa.ITMMAS_BASE ";
						$sql00 .= " WHERE ID_ITEM = '".$Item."' ";
						QueryDatabase($sql00, $results00);

						if (mssql_num_rows($results00) > 0) {
							$sql0  = "SELECT * FROM nsa.ROLL_COUNT_VERIFY ";
							$sql0 .= " WHERE ID_ITEM = '".$Item."' ";
							QueryDatabase($sql0, $results0);

							if (mssql_num_rows($results0) > 0) {
								////UPDATE
								while ($row0 = mssql_fetch_assoc($results0)) {
									$sql  = "UPDATE nsa.ROLL_COUNT_VERIFY SET ";
									$sql .= " VERIFIED_OR_DISCREPANCY = '".$Choice."', ";
									$sql .= " FINDINGS = '".ms_escape_string($Findings)."', ";
									$sql .= " ID_USER_CHG = '".stripIllegalChars2($UserRow['ID_USER'])."', ";
									$sql .= " DATE_CHG = GetDate() ";
									$sql .= " WHERE rowid = ".$row0['rowid']." ";
									error_log($sql);
									QueryDatabase($sql, $results);
								}
							} else {
								////INSERT
								$sql  = "INSERT INTO nsa.ROLL_COUNT_VERIFY (";
								$sql .= " ID_ITEM,  ";
								$sql .= " VERIFIED_OR_DISCREPANCY, ";
								$sql .= " FINDINGS, ";
								$sql .= " ID_USER_ADD, ";
								$sql .= " DATE_ADD ";
								$sql .= " ) values ( ";
								$sql .= " '" . $Item . "', ";
								$sql .= " '" . $Choice . "', ";
								$sql .= " '" . ms_escape_string($Findings) . "', ";
								$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
								$sql .= " GetDate() ";
								$sql .= " )";
								error_log($sql);
								QueryDatabase($sql, $results);
							}
						} 
					}
				break;


				case("numRecsChange");
					if (isset($_POST["user_recs"]) && isset($_POST["num_recs"])) {
						$user_recs = $_POST["user_recs"];
						$num_recs = $_POST["num_recs"];

						$sql  = "SELECT top ".$num_recs;
						$sql .= " ID_ITEM, ";
						$sql .= " VERIFIED_OR_DISCREPANCY, ";
						$sql .= " FINDINGS, ";
						$sql .= " ID_USER_ADD, ";
						$sql .= " DATE_ADD, ";
						$sql .= " ID_USER_CHG, ";
						$sql .= " DATE_CHG, ";
						$sql .= " rowid ";
						$sql .= " FROM nsa.ROLL_COUNT_VERIFY ";
						if ($user_recs <> '--ALL--') {
							$sql .= " WHERE isnull(ID_USER_CHG,ID_USER_ADD) = '".$user_recs."' ";
						}
						//$sql .= " ORDER BY rowid desc ";
						$sql .= " ORDER BY isnull(DATE_CHG,DATE_ADD) desc ";
						QueryDatabase($sql, $results);

						$prevrowId = '';
						$b_flip = true;

						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample'>Item</th>\n";
						$ret .= " 		<th class='sample'>V or D</th>\n";
						$ret .= " 		<th class='sample'>Findings</th>\n";
						$ret .= " 		<th class='sample'>Added By</th>\n";
						$ret .= " 		<th class='sample'>Date Add</th>\n";
						$ret .= " 		<th class='sample'>Changed By</th>\n";
						$ret .= " 		<th class='sample'>Date Changed</th>\n";
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
							$ret .= " 		<td class='" . $trClass . "' id='ID_ITEM__" . $row['rowid']."' >" . $row['ID_ITEM'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='V_OR_D__" . $row['rowid']."' >" . $row['VERIFIED_OR_DISCREPANCY'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='FINDINGS__" . $row['rowid']."' >" . $row['FINDINGS'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='ID_USER_ADD__" . $row['rowid']."' >" . $row['ID_USER_ADD'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='DATE_ADD__" . $row['rowid']."' >" . $row['DATE_ADD'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='ID_USER_CHG__" . $row['rowid']."' >" . $row['ID_USER_CHG'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='DATE_CHG__" . $row['rowid']."' >" . $row['DATE_CHG'] . "</td>\n";
							$ret .= " 	</tr>\n";
						}
						$ret .= " </table>\n";
						$ret .= " </br>\n";
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


?>
