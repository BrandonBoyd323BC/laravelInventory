<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");

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
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["id_ord"]))  {
				$ID_ORD = stripNonANChars($_POST["id_ord"]);
				$action = trim($_POST["action"]);
				error_log("ACTION:" . $action);

				switch($action) {
					case "getShip";
						$sql  = " SELECT sh.ID_SHIP, tf.STATUS ";
						$sql .= " from nsa.CP_SHPHDR sh ";
						$sql .= " left join nsa.TOUCHPOINT_FTP_FILES" . $DB_TEST_FLAG . " tf ";
						$sql .= " on sh.ID_ORD = tf.ID_ORD ";
						$sql .= " and sh.ID_SHIP = tf.ID_SHIP ";
						$sql .= " where ltrim(sh.ID_ORD) = '". $ID_ORD ."'";
						QueryDatabase($sql, $results);
						$ret .= "<table class='sample'>";
						$ret .= "<tr>\n";
						$ret .= "	<th>Order: ". $ID_ORD . "<input type=hidden id='hid_ID_ORD' value='".$ID_ORD."'></input></th>";
						$ret .= "</tr>";
						while ($row = mssql_fetch_assoc($results)) {
							if (trim($row['STATUS']) == "") {
								$buttonText = "Generate NEW CSV";
							} else {
								$buttonText = "Regenerate CSV";
							}
							$ret .= "<tr>\n";
							$ret .= "	<td>Shipment: ". $row['ID_SHIP'] . " Status: " . $row['STATUS'] . " ";
							$ret .= "		<input id='button_genCSV_".$row['ID_SHIP']."' type='button' value='" . $buttonText . "' onClick=\"sendGenerateCSV('".$row['ID_SHIP']."')\"></input>\n";
							$ret .= "	</td>\n";
							$ret .= "</tr>";
						}
						$ret .= "</table>";
					break;

					case "generateCSV":
						//$ID_ORD = stripNonANChars($_POST["id_ord"]);
						$ID_SHIP = trim($_POST["id_ship"]);
						$FileVersion = "ORIG";

						$sql = "SET ANSI_NULLS ON";
						QueryDatabase($sql, $results);
						$sql = "SET ANSI_WARNINGS ON";
						QueryDatabase($sql, $results);
						$sql = "SET QUOTED_IDENTIFIER ON";
						QueryDatabase($sql, $results);
						$sql = "SET ANSI_PADDING ON";
						QueryDatabase($sql, $results);

						$sql  = " SELECT * ";
						$sql .= " from nsa.TOUCHPOINT_FTP_FILES" . $DB_TEST_FLAG . " ";
						$sql .= " where ltrim(ID_ORD) = '". $ID_ORD ."'";
						$sql .= " and ltrim(ID_SHIP) = '". $ID_SHIP ."'";
						QueryDatabase($sql, $results);
						if (mssql_num_rows($results) > 0) {
							$ret .= "<h1>ID ORDER ". $ID_ORD .", ID SHIP ". $ID_SHIP ." PREVIOUSLY ENTERED</h1>";
							$ret .= "<h1>SENDING AS REVISED</h1>";
							$FileVersion = "REVISED";
						}

						$sql  = " SELECT ltrim(ID_CUST_SOLDTO) as ID_CUST_SOLDTO, CODE_STAT_ORD ";
						$sql .= " from nsa.CP_ORDHDR ";
						$sql .= " where ltrim(ID_ORD) = '". $ID_ORD ."'";
						QueryDatabase($sql, $results);
						if (mssql_num_rows($results) < 1) {
							$ret .= "<h1>ORDER NOT FOUND</h1>";
						}

						while ($row = mssql_fetch_assoc($results)) {
							//if (strpos($row['ID_CUST_SOLDTO'],'D') === false) {
							//	$ret .="<h1>NOT A DRIFIRE CUSTOMER ORDER</h1>";
							//} elseif ($row['CODE_STAT_ORD'] == 'H') {
							if ($row['CODE_STAT_ORD'] == 'H') {
								$ret .="<h1>ORDER ON HOLD</h1>";
							} else {
								$baseFile = "NSA_DRF_" . $ID_ORD . "_" . date('Ymd-His') . ".csv";
								//$baseDir = '/mnt/TouchpointFTP/Outbound';
								$baseDir = '/mnt/TouchpointFTP' . $DB_TEST_FLAG . '/Outbound';
								$baseNetDir = '//fs1/netshare/TouchpointFTP' . $DB_TEST_FLAG . '/Outbound';
								$archiveDir 	= $baseDir . "/Archive/";
								$pendingDir 	= $baseDir . "/Pending/";
								$sentDir 	= $baseDir . "/Sent/";
								$errorDir 	= $baseDir . "/Error/";
								$holdDir 	= $baseDir . "/Hold/";
								$holdNetDir = $baseNetDir . "/Hold/";
								$filename_archive = $archiveDir . $baseFile;
								$filename_pending = $pendingDir . $baseFile;
								$filename_hold = $holdDir . $baseFile;
								$FLAG_HOLD = "";

								$fp = fopen($filename_archive, 'w');

								$sql1  = " SELECT ";
								$sql1 .= "  oh.ID_ORD, ";
								$sql1 .= "  oh.ID_PO_CUST, ";
								$sql1 .= "  sl.ID_SHIP, ";
								$sql1 .= "  CONVERT(varchar(8), oh.DATE_ORD, 112) as DATE_ORD3, ";
								$sql1 .= "  CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
								$sql1 .= "  ol.SEQ_LINE_ORD, ";
								$sql1 .= "  ol.ID_ITEM as NSA_ID_ITEM, ";
								$sql1 .= "  ol.ID_ITEM_CUST as CUST_ID_ITEM, ";
								$sql1 .= "  id.DESCR_ADDL as SAP_ID_ITEM, ";
								$sql1 .= "  rtrim(ltrim(oh.NAME_ORD_BY)) as NAME_ORD_BY, ";
								$sql1 .= "  ol.CODE_UM_ORD, ";
								$sql1 .= "  sl.QTY_SHIP, ";
								//$sql1 .= "  oh.NAME_CUST_SHIPTO, ";
								//$sql1 .= "  oh.ADDR_1, ";
								//$sql1 .= "  oh.ADDR_2, ";
								//$sql1 .= "  oh.CITY, ";
								//$sql1 .= "  oh.ID_ST, ";
								//$sql1 .= "  oh.ZIP, ";
								//$sql1 .= "  oh.COUNTRY, ";
								$sql1 .= "  sh.NAME_CUST_SHIPTO, ";
								$sql1 .= "  sh.ADDR_1, ";
								$sql1 .= "  sh.ADDR_2, ";
								$sql1 .= "  sh.CITY, ";
								$sql1 .= "  sh.ID_ST, ";
								$sql1 .= "  sh.ZIP, ";
								$sql1 .= "  sh.COUNTRY, ";
								$sql1 .= "  st.NAME_CUST as SOLDTO_ADDR_1, ";
								$sql1 .= "  st.ADDR_CUST_2 as SOLDTO_ADDR_2, ";
								$sql1 .= "  st.ADDR_CUST_3 as SOLDTO_ADDR_3, ";
								$sql1 .= "  st.ADDR_CUST_4 as SOLDTO_ADDR_4, ";
								$sql1 .= "  oh.CODE_SHIP_VIA_CP, ";
								$sql1 .= "  sv.CARRIER, ";
								$sql1 .= "  sa.ACCT_SHIP_VIA_CP, ";
								$sql1 .= "  sv.DELIV_SVC, ";
								$sql1 .= "  sv.PKG_TYPE, ";
								$sql1 .= "  sv.PAYOR, ";
								// if oh.CODE_COL_PPD = 6 or 7, BLIND
								$sql1 .= "  case when oh.CODE_COL_PPD in (6,7) then 'T' else 'F' end as FLAG_BLIND, ";
								$sql1 .= "  'DRIFIRE-NSA' as FORM_LAYOUT, ";
								$sql1 .= "  '' as FILE_VERSION, ";
								$sql1 .= "  '' as COMMENT ";
								$sql1 .= " FROM ";
								$sql1 .= "  nsa.CP_SHPLIN sl ";
								$sql1 .= "  left join nsa.CP_ORDLIN ol ";
								$sql1 .= "  on sl.ID_ORD = ol.ID_ORD ";
								$sql1 .= "  and sl.SEQ_LINE_ORD = ol.SEQ_LINE_ORD ";
								$sql1 .= "  left join nsa.CP_SHPHDR sh ";
								$sql1 .= "  on sl.ID_SHIP = sh.ID_SHIP ";
								$sql1 .= "  left join nsa.CP_ORDHDR oh ";
								$sql1 .= "  on ol.ID_ORD = oh.ID_ORD ";
								$sql1 .= "  left join nsa.CUSMAS_SOLDTO st ";
								$sql1 .= "  on oh.ID_CUST_SOLDTO = st.ID_CUST ";
								$sql1 .= "  left join nsa.ITMMAS_DESCR id ";
								$sql1 .= "  on ol.ID_ITEM = id.ID_ITEM ";
								$sql1 .= "  and id.SEQ_DESCR = 888 ";
								$sql1 .= "  left join nsa.TOUCHPOINT_SHIP_VIA_DTL sv ";
								$sql1 .= "  on oh.CODE_SHIP_VIA_CP = sv.CODE_SHIP_VIA_CP ";
								//$sql1 .= "  left join nsa.cm_ship_acct sa ";
								//$sql1 .= "  on ltrim(oh.ID_CUST_SOLDTO) = ltrim(sa.ID_CUST_SOLDTO) ";
								//$sql1 .= "  and oh.SEQ_SHIPTO = sa.SEQ_SHIPTO ";
								//$sql1 .= "  and oh.CODE_SHIP_VIA_CP = sa.CODE_SHIP_VIA_CP ";
								$sql1 .= "  left join nsa.CP_SHP_SHIP_ACCT sa ";
								$sql1 .= "  on sl.ID_SHIP = sa.ID_SHIP ";
								$sql1 .= "  and sl.ID_ORD = sa.ID_ORD ";
								$sql1 .= " WHERE ltrim(ol.ID_ORD) = '". $ID_ORD ."' ";
								$sql1 .= "  and ltrim(sl.ID_SHIP) = '". $ID_SHIP ."' ";
								$sql1 .= " ORDER BY ol.SEQ_LINE_ORD asc ";
								error_log($sql1);
								QueryDatabase($sql1, $results1);
								while ($row1 = mssql_fetch_assoc($results1)) {

									if (strtoupper(trim($row1['NAME_ORD_BY'])) == "NSA") {
										$row1['NAME_CUST_SHIPTO'] = "National Safety Apparel";
										$row1['ADDR_1'] = "NATIONAL SAFETY APPAREL";
										$row1['ADDR_2'] = "ATTN: ";
										$row1['CITY'] = "CLEVELAND";
										$row1['ID_ST'] = "OH";
										$row1['ZIP'] = "44135-3319";
										$row1['COUNTRY'] = "USA";
										$row1['CARRIER'] = "UPS";
										$row1['DELIV_SVC'] = "GROUND";
										$row1['ACCT_SHIP_VIA_CP'] = "Y3F375";
										$row1['PAYOR'] = "S";
									}

									if (trim($row1['ACCT_SHIP_VIA_CP']) == ""  && $row1['PAYOR'] == "S") {
										$sql2  = " SELECT ";
										$sql2 .= "  sv.PAYORID ";
										$sql2 .= " FROM ";
										$sql2 .= "  nsa.TOUCHPOINT_SHIP_VIA_DTL sv ";
										$sql2 .= " WHERE ";
										$sql2 .= "  sv.CODE_SHIP_VIA_CP = '" . $row1['CODE_SHIP_VIA_CP'] . "' ";
										QueryDatabase($sql2, $results2);
										while ($row2 = mssql_fetch_assoc($results2)) {
											$row1['ACCT_SHIP_VIA_CP'] = $row2['PAYORID'];
										}
									}
									if (trim($row1['ACCT_SHIP_VIA_CP']) == ""  && $row1['PAYOR'] == "3") {
										$row1['ACCT_SHIP_VIA_CP'] = "Y3F375";
										$row1['PAYOR'] = "S";
									}

									$row1['FILE_VERSION'] = $FileVersion;

									$sql2  = " SELECT ";
									$sql2 .= "  ID_ORD, SEQ_COMMENT, NOTE ";
									$sql2 .= " FROM ";
									$sql2 .= "  nsa.CP_COMMENT ";
									$sql2 .= " WHERE ";
									$sql2 .= "  ID_ORD = '" . $ID_ORD . "' ";
									$sql2 .= "  and CODE_COMMENT = 5 ";
									$sql2 .= "  and FLAG_COPY_REVW_SF = 1 ";
									$sql2 .= " ORDER BY SEQ_COMMENT asc ";
									QueryDatabase($sql2, $results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										$row1['COMMENT'] .= str_replace("|","",$row2['NOTE']) . " " ;
									}

									$sql2  = " SELECT ";
									$sql2 .= "  rtrim(ltrim(ID_JOB)) as ID_JOB ";
									$sql2 .= " FROM ";
									$sql2 .= "  nsa.CP_ORDHDR ";
									$sql2 .= " WHERE ";
									$sql2 .= "  ID_ORD = '" . $ID_ORD . "' ";
									QueryDatabase($sql2, $results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										if (trim($row2['ID_JOB']) <> "") {
											$row1['ID_ORD'] .= "-" . $row2['ID_JOB'];
										}
									}

									//// NEED TO DETERMINE HOW WE WILL INDICATE CUSTOM LAYOUTS FOR BLIND SHIPMENTS, UNTIL THEN THEY WILL GO TO HOLD FOR MANUAL FIX
									if ($row1['FLAG_BLIND'] == 'T' && $row1['FORM_LAYOUT'] == 'DRIFIRE-NSA') {
										error_log($baseFile . " MARKED FOR BLIND SHIPMENT BUT HAS DRIFIRE-NSA LAYOUT: SETTING FILE FOR HOLD");
										$row1['FORM_LAYOUT'] = "GENERIC-BLIND";
										//$FLAG_HOLD = "T";
									}
									//fputcsv($fp, $row1, "|", chr(0));
									fputcsv($fp, $row1, "|", "\"");
								}
								fclose($fp);

								$sql1  = " INSERT INTO nsa.TOUCHPOINT_FTP_FILES" . $DB_TEST_FLAG . " ( ";
								$sql1 .= "  ID_ORD, ";
								$sql1 .= "  ID_SHIP, ";
								$sql1 .= "  FLAG_UD, ";
								$sql1 .= "  STATUS, ";
								$sql1 .= "  FILE_VERSION, ";
								$sql1 .= "  FILE_NAME, ";
								$sql1 .= "  FILE_TYPE, ";
								$sql1 .= "  DATE_ADD, ";
								$sql1 .= "  ID_USER_ADD ";
								$sql1 .= " ) VALUES ( ";
								$sql1 .= "  '" . $ID_ORD . "', ";
								$sql1 .= "  '" . $ID_SHIP . "', ";
								$sql1 .= "  'U', ";
								$sql1 .= "  'Created', ";
								$sql1 .= "  '" . $FileVersion . "', ";
								$sql1 .= "  '" . $filename_archive . "', ";
								$sql1 .= "  'CSV', ";
								$sql1 .= "  getDate(), ";
								$sql1 .= "  '" . stripIllegalChars($UserRow['ID_USER']) . "' ";
								$sql1 .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
								QueryDatabase($sql1, $results1);

								$row1 = mssql_fetch_assoc($results1);
								$LAST_INSERT_ID = $row1['LAST_INSERT_ID'];

								if ($FLAG_HOLD == "T") {
									if (!copy($filename_archive, $filename_hold)) {
										error_log("Failed to copy " . $filename_archive . " to " . $filename_hold);
										$sql1  = " UPDATE nsa.TOUCHPOINT_FTP_FILES" . $DB_TEST_FLAG . " set STATUS='FAIL2HOLD' where rowid = ". $LAST_INSERT_ID;
										QueryDatabase($sql1, $results1);
									} else {
										error_log($baseFile . " copied to hold dir");
										$sql1  = " UPDATE nsa.TOUCHPOINT_FTP_FILES" . $DB_TEST_FLAG . " set STATUS='Hold' where rowid = ". $LAST_INSERT_ID;
										QueryDatabase($sql1, $results1);

										$ret .= "<h1>FILE FOR ". $ID_ORD ." NEEDS ATTENTION</h1>";
										$ret .= "<h1>MOVED TO HOLD</h1>";

										$to = "gvandyne@thinknsa.com";
										$subject = "Touchpoint file sent to hold directory. Order: " . $ID_ORD;
										$body = str_replace("/","\\",$holdNetDir)."\r\n".$baseFile;
										$headers = "From: eProduction@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
										mail($to, $subject, $body, $headers);

										$to = "mfigueroa@thinknsa.com";
										$subject = "Touchpoint file sent to hold directory. Order: " . $ID_ORD;
										$body = str_replace("/","\\",$holdNetDir)."\r\n".$baseFile;
										$headers = "From: eProduction@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
										mail($to, $subject, $body, $headers);								

		/*					    		$head = array(
									    	//'to'      =>array('mfigueroa@thinknsa.com'=>'Micel Figueroa'),
									    	'to'      =>array('gvandyne@thinkNSA.com'=>'Greg VanDyne'),
									    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
									    	//'cc'      =>array('gvandyne@thinkNSA.com'=>'Greg VanDyne'),
									    	//'bcc'     =>array('email4@email.net'=>'Admin'),
									    	);

										$subject = "Touchpoint file sent to hold directory. Order: " . $ID_ORD;
										$body = "<p><a href='file://".$holdNetDir."'>".$baseFile."</a></p>";
										//mail::send($head,$subject,$body);
		*/								
									}
								} else {
									if (!copy($filename_archive, $filename_pending)) {
										error_log("Failed to copy " . $filename_archive . " to " . $filename_pending);
										$sql1  = " UPDATE nsa.TOUCHPOINT_FTP_FILES" . $DB_TEST_FLAG . " set STATUS='FAIL2PENDING' where rowid = ". $LAST_INSERT_ID;
										QueryDatabase($sql1, $results1);
									} else {
										error_log($baseFile . " copied to pending dir");
										$sql1  = " UPDATE nsa.TOUCHPOINT_FTP_FILES" . $DB_TEST_FLAG . " set STATUS='Pending' where rowid = ". $LAST_INSERT_ID;
										QueryDatabase($sql1, $results1);
									}
								}
								$ret .= " <br>\n";
								$ret .= " <table class='sample'>\n";
								$ret .= " 	<tr>\n";
								$ret .= " 		<th>Error Files:</th>\n";
								$ret .= " 	</tr>\n";
								$ErrorArray = scandir($errorDir);
								foreach ($ErrorArray as $ErrorFile) {
									if ($ErrorFile <> "." && $ErrorFile <> "..") {
										$ret .= " 	<tr>\n";
										$ret .= " 		<td>". $ErrorFile ."</td>\n";
										$ret .= " 	</tr>\n";
									}
								}
								$ret .= " </table>\n";
								$ret .= " <br>\n";
								$ret .= " <table class='sample'>\n";
								$ret .= " 	<tr>\n";
								$ret .= " 		<th>Hold Files:</th>\n";
								$ret .= " 	</tr>\n";
								$HoldArray = scandir($holdDir);
								foreach ($HoldArray as $HoldFile) {
									if ($HoldFile <> "." && $HoldFile <> "..") {
										$ret .= " 	<tr>\n";
										$ret .= " 		<td>". $HoldFile ."</td>\n";
										$ret .= " 	</tr>\n";
									}
								}
								$ret .= " </table>\n";
								$ret .= " <br>\n";
								$ret .= " <table class='sample'>\n";
								$ret .= " 	<tr>\n";
								$ret .= " 		<th>Pending Files:</th>\n";
								$ret .= " 	</tr>\n";
								$PendingArray = scandir($pendingDir);
								foreach ($PendingArray as $PendingFile) {
									if ($PendingFile <> "." && $PendingFile <> "..") {
										$ret .= " 	<tr>\n";
										$ret .= " 		<td>". $PendingFile ."</td>\n";
										$ret .= " 	</tr>\n";
									}
								}
								$ret .= " </table>\n";
								$ret .= " <br>\n";
								$ret .= " <table class='sample'>\n";
								$ret .= " 	<tr>\n";
								$ret .= " 		<th>Sent Files:</th>\n";
								$ret .= " 	</tr>\n";
								$SentArray = scandir($sentDir);
								foreach ($SentArray as $SentFile) {
									if ($SentFile <> "." && $SentFile <> "..") {
										$ret .= " 	<tr>\n";
										$ret .= " 		<td>". $SentFile ."</td>\n";
										$ret .= " 	</tr>\n";
									}
								}
								$ret .= " </table>\n";
							}
						}
						$sql = "SET ANSI_NULLS OFF";
						QueryDatabase($sql, $results);
						$sql = "SET ANSI_WARNINGS OFF";
						QueryDatabase($sql, $results);
						$sql = "SET QUOTED_IDENTIFIER OFF";
						QueryDatabase($sql, $results);
						$sql = "SET ANSI_PADDING OFF";
						QueryDatabase($sql, $results);
					break;
				}// end Switch
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>