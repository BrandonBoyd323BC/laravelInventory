<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");


	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print( "		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';
			
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			
			if (isset($_POST["action"])) {
				$action = $_POST["action"];

				switch($action) {
					case "show":
						if  (isset($_POST["so_num"]) && isset($_POST["sufx"])) {
							$SO	= trim($_POST["so_num"]);
							$SUFX = trim($_POST["sufx"]);
							if ($SUFX == '') {
								$SUFX = '0';
							}

							//$HDR_STATUS = array("A","C","R","S","U");
							//$OPER_STATUS = array("A","C","P","R");
							$HDR_STATUS = array('U' => "Unreleased", 'A' => "Allocated", 'R' => "Released", 'S' => "Started", 'E' => "Ended", 'C' => "Completed");
							$OPER_STATUS = array('P' => "Planned", 'R' => "Ready", 'A' => "Active", 'C' => "Complete");

							$ret .= " <h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>SO #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Item</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Date Added</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Qty Ordered</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Status</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";

							$sql =  "select ";
							$sql .= " 	ltrim(ID_SO) as ID_SO, ";
							$sql .= "	SUFX_SO, ";
							$sql .= " 	ID_ITEM_PAR, ";
							$sql .= " 	DATE_ADD, ";
							$sql .= "	Convert(varchar(10), DATE_ADD, 101) as DATE_ADD3, ";
							$sql .= "   QTY_ORD , ";
							$sql .= " 	STAT_REC_SO, ";
							$sql .= " 	ROWID ";
							$sql .= " from ";
							$sql .= " 	nsa.SHPORD_HDR ";
							$sql .= " where ltrim(ID_SO) = '" . $SO ."' ";
							$sql .= " and ltrim(SUFX_SO) = ". $SUFX ." ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								$dateADD = $row['DATE_ADD3'] . " " ."000000";
								$dateADD1 = strtotime($dateADD);
								$formatted_date = date('m/d/Y',$dateADD1);

								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_SO'] . " - " . $row['SUFX_SO'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['ID_ITEM_PAR'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row['QTY_ORD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "			<select id='STAT_REC_SO_" . $row['ROWID'] ."' onChange=\"showStatusChange_HDR(" . $row['ROWID'] .")\">\n";
								//foreach ($HDR_STATUS as $STAT_REC_SO) {
								foreach ($HDR_STATUS as $STAT_REC_SO => $STAT_REC_SO_FULL) {
									$SELECTED = '';
									$CURRENT = '';

									if (trim($row['STAT_REC_SO']) == trim($STAT_REC_SO)) {
										$SELECTED = 'SELECTED';
										$CURRENT = '*';
									}
									$ret .= "				<option value='". $CURRENT . $STAT_REC_SO . "' " . $SELECTED . ">" . $CURRENT . $STAT_REC_SO_FULL .  "</option>\n";
								}

			 					$ret .= "			</select> ";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<input type='button' value='Save' id='HDR_Save_". $row['ROWID'] ."' DISABLED onClick=\"saveStatusChange_HDR('" . $row['ROWID'] . "')\" >\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<div id='HDR_Save_div_". $row['ROWID'] ."'></div>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}

							$ret .= " <table class='sample'>\n";
							$ret .= " <br />";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Oper</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Description</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Date Added</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Added By</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Status</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";	

							$sql1 =  "select ";
							$sql1 .= " 	ID_OPER, ";
							$sql1 .= "	DESCR_OPER_1, ";
							$sql1 .= " 	DATE_ADD, ";
							$sql1 .= "	Convert(varchar(10), DATE_ADD, 101) as DATE_ADD3, ";
							$sql1 .= " 	ID_USER_ADD, ";
							$sql1 .= " 	STAT_REC_OPER, ";
							$sql1 .= "  ROWID ";
							$sql1 .= " from ";
							$sql1 .= " 	nsa.SHPORD_OPER ";
							$sql1 .= " where ltrim(ID_SO) = '" . $SO ."' ";
							$sql1 .= " and ltrim(SUFX_SO) = ". $SUFX ." ";
							$sql1 .= " order by ";
							$sql1 .= "  ID_OPER ";
							QueryDatabase($sql1, $results1);
						
							while ($row1 = mssql_fetch_assoc($results1)) {
								$dateADD = $row1['DATE_ADD3'] . " " ."000000";
								$dateADD1 = strtotime($dateADD);
								$formatted_date = date('m/d/Y',$dateADD1);
								
								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['ID_OPER'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['DESCR_OPER_1'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['ID_USER_ADD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "			<select id='STAT_REC_OPER_" . $row1['ROWID'] ."' onChange=\"showStatusChange_OPER(" . $row1['ROWID'] .")\">\n";
								foreach ($OPER_STATUS as $STAT_REC_OPER => $STAT_REC_OPER_FULL) {
									$SELECTED1 = '';
									$CURRENT1 = '';

									if (trim($row1['STAT_REC_OPER']) == trim($STAT_REC_OPER)) {
										$SELECTED1 = 'SELECTED';
										$CURRENT1 = '*';
									}
									$ret .= "				<option value='". $CURRENT1 . $STAT_REC_OPER . "' " . $SELECTED1 . ">" . $CURRENT1 . $STAT_REC_OPER_FULL .  "</option>\n";
								}

			 					$ret .= "			</select> ";
			 					$ret .= " 		<td>\n";
								$ret .= "				<input type='button' value='Save' id='OPER_Save_". $row1['ROWID'] ."' DISABLED onClick=\"saveStatusChange_OPER('" . $row1['ROWID'] ."')\" >\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<div id='OPER_Save_div_". $row1['ROWID'] ."'></div>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}
							
							$ret .= " <table class='sample'>\n";
							$ret .= " <br />";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>SO #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Oper</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Badge</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Team</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Date</font>\n";
							$ret .= " 		</th>\n";
							$ret .= "		<th>\n";
							$ret .= "				<font>Flag Cmpl</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Quantity</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";	

							$sql1 =  "select ";
							$sql1 .= "  np.ID_SO, ";
							$sql1 .= "  np.SUFX_SO, ";
							$sql1 .= "  np.ID_OPER, ";
							$sql1 .= "  np.ID_BADGE, ";
							$sql1 .= "  em.NAME_EMP, ";
							$sql1 .= "  Convert(varchar(10), np.DATE_TRX, 101) as DATE_TRX3, ";
							$sql1 .= "  np.TIME_TRX, ";
							$sql1 .= "  np.FLAG_DC_CMPL_SO, ";
							$sql1 .= "  np.QTY_GOOD ";
							$sql1 .= " from ";
							$sql1 .= "  nsa.DCUTRX_NONZERO_PERM np ";
							$sql1 .= " left join ";
							$sql1 .= "  nsa.DCEMMS_EMP em ";
							$sql1 .= "  on np.ID_BADGE=em.ID_BADGE ";
							$sql1 .= "  and em.CODE_ACTV = '0' ";
							$sql1 .= " where CODE_TRX = '103' ";
							$sql1 .= "  and np.FLAG_DEL = '' ";
							$sql1 .= "  and ltrim(ID_SO) = '" . $SO ."' ";
							$sql1 .= "  and ltrim(SUFX_SO) = ". $SUFX ." ";
							$sql1 .= " order by ";
							$sql1 .= "  np.ID_OPER, ";
							$sql1 .= "  DATE_TRX3 ";
							QueryDatabase($sql1, $results1);
						
							while ($row1 = mssql_fetch_assoc($results1)) {
								$date = $row1['DATE_TRX3'] . " " .str_pad($row1['TIME_TRX'],6 , "0", STR_PAD_LEFT);
								$date1 = strtotime($date);
								$formatted_date = date('m/d/Y h:i:s A',$date1);

								$ret .= " 	<tr class ='dbc'>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['ID_SO'] . " - " . $row1['SUFX_SO'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['ID_OPER'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['ID_BADGE'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['NAME_EMP'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $formatted_date . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['FLAG_DC_CMPL_SO'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>" . $row1['QTY_GOOD'] . "</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}	
						}
					break;
					case "update_hdr";
						if  (isset($_POST["rowid"]) && isset($_POST["so_status"])) {
							$strRet = 'ERROR!';
							$ROWID = (trim($_POST["rowid"]));
							$STAT_HDR = ($_POST["so_status"]);

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

							if ($STAT_HDR != 'C') {
								$sql = " UPDATE ";
								$sql .= " nsa.SHPORD_HDR ";
								$sql .= " Set STAT_REC_SO = '". $STAT_HDR ."' ";
								$sql .= " where ROWID = '" . $ROWID ."' ";
								QueryDatabase($sql, $results);

								if ($results == '1') {
									$strRet = 'OK!';
								}
							} else {
								$strRet = 'CANNOT CLOSE!';
							}
							$ret = "	<font>" . $strRet . "</font>\n";

							$sql = "SET ANSI_NULLS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET QUOTED_IDENTIFIER OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_PADDING OFF";
							QueryDatabase($sql, $results);
						}
					break;
					case "update_oper";
						if (isset($_POST["rowid"]) && isset($_POST["oper_status"])) {
							$strRet = 'ERROR!';
							$ROWID = (trim($_POST["rowid"]));
							$STAT_OPER = ($_POST["oper_status"]);

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

							if ($STAT_OPER != 'C') {
								$sql = " Update ";
								$sql .= " nsa.SHPORD_OPER ";
								$sql .= " Set STAT_REC_OPER  = '". $STAT_OPER ."', ";
								$sql .= " STAT_REC_OPER_1  = '". $STAT_OPER ."' ";
								$sql .= " where ROWID = '". $ROWID ."' ";
								QueryDatabase($sql, $results);

								if ($results == '1') {
									$strRet = 'OK!';
								}
							} else {
								$strRet = 'CANNOT CLOSE!';
							}
							$ret = "	<font>" . $strRet . "</font>\n";
							$sql = "SET ANSI_NULLS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_WARNINGS OFF";
							QueryDatabase($sql, $results);
							$sql = "SET QUOTED_IDENTIFIER OFF";
							QueryDatabase($sql, $results);
							$sql = "SET ANSI_PADDING OFF";
							QueryDatabase($sql, $results);

						}
					break;
				}
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>