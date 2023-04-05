<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runFTP_FROM_TOUCHPOINT cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runFTP_FROM_TOUCHPOINT cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runFTP_FROM_TOUCHPOINT started at " . date('Y-m-d g:i:s a'));
			error_log("### runFTP_FROM_TOUCHPOINT CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runFTP_FROM_TOUCHPOINT' ";
			$sql .= "	and ";
			$sql .= "	FLAG_RUNNING = '1' ";
			$sql .= "	and ";
			$sql .= "	DATE_EXP > getDate()";
			QueryDatabase($sql, $results);
			if (mssql_num_rows($results) == 0) {
				$sql  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql .= " PROC_NAME, ";
				$sql .= " FLAG_RUNNING, ";
				$sql .= " DATE_ADD, ";
				$sql .= " DATE_EXP ";
				$sql .= ") VALUES ( ";
				$sql .= "'runFTP_FROM_TOUCHPOINT', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runFTP_FROM_TOUCHPOINT SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				if ($TEST_ENV) {
					$ftp_server 	= "1.0.0.20";		//TEST DESKTOP-IT1
					$ftp_user_name 	= "thinknsa";
					$ftp_user_pass 	= "eshgoTwed=4";
					$baseDir = '/mnt/TouchpointFTP_TEST/Inbound';
					$SqlTableName_FTP_FILES = "nsa.TOUCHPOINT_FTP_FILES_TEST";
					$SqlTableName_INBOUND_FILE_DATA = "nsa.TOUCHPOINT_INBOUND_FILE_DATA_TEST";
					$SqlTableName_CP_SHIP_IMPORT = "nsa.CP_SHIP_IMPORT_TEST";
					$EnvironmentCharacterOffset = 1;
				} else {
					$ftp_server 	= "ftp.touchpointlogistics.com";  	//LIVE TOUCHPOINT
					$ftp_user_name 	= "nsa@touchpointlogistics.com";
					$ftp_user_pass 	= "nsa$2016";
					$baseDir = '/mnt/TouchpointFTP/Inbound';
					$SqlTableName_FTP_FILES = "nsa.TOUCHPOINT_FTP_FILES";
					$SqlTableName_INBOUND_FILE_DATA = "nsa.TOUCHPOINT_INBOUND_FILE_DATA";
					$SqlTableName_CP_SHIP_IMPORT = "nsa.CP_SHIP_IMPORT";
					$EnvironmentCharacterOffset = 0;
				}

				$archiveDir 	= $baseDir . "/Archive/";
				$pendingDir 	= $baseDir . "/Pending/";
				$importedDir 	= $baseDir . "/Imported/";
				$plDir 	= $baseDir . "/Imported/PL-Order/";
				$errorDir 	= $baseDir . "/Error/";

				// Connect to FTP Server
				$conn_id = ftp_connect($ftp_server);
				// Login to FTP Server
				$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
				// Verify Log In Status
				if ((!$conn_id) || (!$login_result)) {
					error_log("### FTP connection has failed!");
					error_log("### Attempted to connect to " . $ftp_server . " for user " . $ftp_user_name);
				} else {
					error_log("### Connected to " . $ftp_server . ", for user " . $ftp_user_name);
					ftp_pasv($conn_id, true);

					//$remote_dir = "/home/thinknsa/outgoing/";
					$remote_dir = "/Confirms/";
					$remote_archive_dir = "/Confirms/archive/";
					$bool_chdir = ftp_chdir($conn_id,$remote_dir);

					$RemoteFiles = ftp_nlist($conn_id,ftp_pwd($conn_id));
					$FileCount = count($RemoteFiles);
					if ($FileCount > 0) {
						error_log("### ". $FileCount ." File(s) to download");
						$i = 0;
						foreach ($RemoteFiles as $FullFile) {
							$i++;
							error_log("### File " . $i . " of " . $FileCount . " to download: " . $FullFile);
							$File = substr($FullFile,strrpos($FullFile,"/")+$EnvironmentCharacterOffset);
							//error_log("### File: " . $File);
							if (ftp_get($conn_id, $archiveDir . $File, $FullFile, FTP_BINARY)) {
								error_log("### File downloaded to: " . $archiveDir . $File);
								$filename_archive = $archiveDir . $File;
								$filename_pending = $pendingDir . $File;

								if (!copy($filename_archive, $filename_pending)) {
									error_log("### Failed to copy " . $filename_archive . " to " . $filename_pending);
									//$sql1  = " UPDATE " . $SqlTableName_FTP_FILES . " set STATUS='FAIL2PENDING' where rowid = ". $LAST_INSERT_ID;
									//QueryDatabase($sql1, $results1);
								} else {
									error_log("### " . $File . " copied to pending dir");
									//$sql1  = " UPDATE " . $SqlTableName_FTP_FILES . " set STATUS='Pending' where rowid = ". $LAST_INSERT_ID;
									//QueryDatabase($sql1, $results1);

									if (ftp_rename($conn_id, $File, "./archive/".$File)) {
										error_log("### " . $File ." archived successfully on FTP server");
									} else {
										error_log("### FAILED TO ARCHIVE " . $File ." on FTP server");
									}
								}

								//if (ftp_delete($conn_id, $File)) {
								//	error_log("### " . $File ." deleted successfully from FTP server");
								//} else {
								//	error_log("### FAILED TO DELETE " . $File ." from FTP server");
								//}

							} else {
								error_log("### FTP download of " . $FullFile . " has failed!");
							}
						}
					} else {
						error_log("### No Files to Download");
					}
					ftp_close($conn_id);
				}

				error_log("### runFTP_FROM_TOUCHPOINT processing pending inbound at " . date('Y-m-d g:i:s a'));
				$runID_Timestamp = time();
				$PendingFiles = array();
				$PendingDirArray = scandir($pendingDir);
				foreach ($PendingDirArray as $PendingDirFile) {
					if (substr(strtoupper($PendingDirFile), -4) == ".TXT" || substr(strtoupper($PendingDirFile), -4) == ".XML") {
						error_log("### PendingDirFile: " . $PendingDirFile);
						$PendingFiles[] = $PendingDirFile;
					}
				}

				$numFiles = count($PendingFiles);
				error_log("### numFiles: " . $numFiles);

				if ($numFiles > 0) {

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

					foreach ($PendingFiles as $File) {
						$FLAG_ERR = "";
						$REASON_ERR = "";
						$ERR_BODY = "";
						error_log("### NEED TO PARSE FILE: " . $File);
						$fileRow = 1;
						if (($handle = fopen($pendingDir . $File, "r")) !== FALSE) {
							$ERR_BODY .= "\r\n" . $File ."\r\n\r\n";
							while (($fileData = fgetcsv($handle, 1000, ",")) !== FALSE) {
								$numFields = count($fileData);
						    	if ($numFields <> 10) {
						    		error_log("Incorrect number of fields in line " . $fileRow);
						    		$FLAG_ERR = "ERROR";
						    		$REASON_ERR .= "Incorrect number of fields in line. ";
						    	} else {
						    		error_log("### " . $File . " ROW NUMBER: " . $fileRow . " - Parsing");
						    		$ID_ORD = $fileData[0];
						    		$ID_CUST_PO = $fileData[1];
						    		$ID_SHIP = $fileData[2];
						    		$DATE_PROCESSED = $fileData[3];
						    		$SERVICE = $fileData[4];
						    		$ID_TRACK = $fileData[5];
						    		$CHARGE_PKG = $fileData[6];
						    		$SAP_NUM = $fileData[7];
						    		$QTY_SHIP = $fileData[8];
						    		$SEQ_LINE_ORD = $fileData[9];

									$WGT_PKG_ACTUAL = "0.00";
									$ID_ITEM = "";

									//THERE WILL BE NOTHING TO INSERT INTO ID_ORD IF NO NUMBERS
									if (stripNonNumericCharsNoSpace($ID_ORD) == "") {
										$FLAG_ERR = "ERROR";
										$REASON_ERR .= "ID_ORD contains NO Numbers. ";
										error_log("ID_ORD contains NO Numbers, appending 0");
										$ID_ORD .= '0';
									}

/*
									if (strlen(stripNonNumericCharsNoSpace($ID_ORD)) > 8) {
										$FLAG_ERR = "ERROR";
										$REASON_ERR = "ID_ORD too long";
										error_log("ID_ORD is too long");
									}
*/

									if (strtoupper(substr(trim($ID_ORD),0,2)) == "PL") {
											$FLAG_ERR = "PL-ORDER";
											$REASON_ERR .= "PL Order. ";
											error_log("PL-ORDER detected");
									} else {
										$sql  = " SELECT * ";
										$sql .= " FROM nsa.CP_ORDHDR_PERM ";
										$sql .= " WHERE ID_ORD like '".stripNonNumericCharsNoSpace($ID_ORD)."%' ";
										QueryDatabase($sql, $results);
										if (mssql_num_rows($results) == 0) {
											$FLAG_ERR = "ERROR";
											$REASON_ERR .= "ID_ORD " . $ID_ORD . " not found in CP_ORDHDR_PERM. ";
											error_log("ID_ORD " . $ID_ORD . " not found in CP_ORDHDR_PERM");
										}
									}


									//if (stripNonNumericCharsNoSpace($ID_SHIP) == "" {
									//	$FLAG_ERR = "ERROR";
									//}
									$ERR_BODY .= "ID_ORD:		" . $ID_ORD . "\r\n";
									$ERR_BODY .= "ID_CUST_PO:		" . $ID_CUST_PO . "\r\n";
									$ERR_BODY .= "ID_SHIP:		" . $ID_SHIP . "\r\n";
									$ERR_BODY .= "DATE_PROCESSED:	" . $DATE_PROCESSED . "\r\n";
									$ERR_BODY .= "SERVICE:		" . $SERVICE . "\r\n";
									$ERR_BODY .= "ID_TRACK:		" . $ID_TRACK . "\r\n";
									$ERR_BODY .= "CHARGE_PKG:		" . $CHARGE_PKG . "\r\n";
									$ERR_BODY .= "SAP_NUM:		" . $SAP_NUM . "\r\n";
									$ERR_BODY .= "QTY_SHIP:		" . $QTY_SHIP . "\r\n\r\n";

									$sql  = " INSERT INTO " . $SqlTableName_INBOUND_FILE_DATA ." ( ";
									$sql .= "  ID_ORD, ";
									$sql .= "  ID_ORD_NUMERIC_ONLY, ";
									$sql .= "  ID_CUST_PO, ";
									$sql .= "  ID_SHIP, ";
									$sql .= "  ID_SHIP_NUMERIC_ONLY, ";
									$sql .= "  SEQ_LINE_ORD, ";
									$sql .= "  DATE_PROCESSED, ";
									$sql .= "  SERVICE, ";
									$sql .= "  ID_TRACK, ";
									$sql .= "  CHARGE_PKG, ";
									$sql .= "  WGT_PKG_ACTUAL, ";
									$sql .= "  ID_ITEM, ";
									$sql .= "  SAP_NUM, ";
									$sql .= "  QTY_SHIP, ";
									$sql .= "  FILE_NAME, ";
									$sql .= "  DATE_ADD, ";
									$sql .= "  RUNID_TIMESTAMP, ";
									$sql .= "  STATUS ";
									$sql .= " ) VALUES ( ";
									$sql .= "  '". ms_escape_string($ID_ORD) ."', ";
									$sql .= "  '". substr(stripNonNumericCharsNoSpace($ID_ORD),0,8) . "', ";
									$sql .= "  '". ms_escape_string($ID_CUST_PO) ."', ";
									$sql .= "  '". ms_escape_string($ID_SHIP) ."', ";
									$sql .= "  '". substr(stripNonNumericCharsNoSpace($ID_SHIP),0,9) . "', ";
									$sql .= "  '". ms_escape_string($SEQ_LINE_ORD) ."', ";
									$sql .= "  '". ms_escape_string($DATE_PROCESSED) ."', ";
									$sql .= "  '". ms_escape_string($SERVICE) ."', ";
									$sql .= "  '". ms_escape_string($ID_TRACK) ."', ";
									$sql .= "  '". ms_escape_string($CHARGE_PKG) ."', ";
									$sql .= "  '". ms_escape_string($WGT_PKG_ACTUAL) ."', ";
									$sql .= "  '". ms_escape_string($ID_ITEM) ."', ";
									$sql .= "  '". ms_escape_string($SAP_NUM) ."', ";
									$sql .= "  '". ms_escape_string($QTY_SHIP) ."', ";
									$sql .= "  '". ms_escape_string($File) ."', ";
									$sql .= "  getDate(), ";
									$sql .= "  '". $runID_Timestamp ."', ";
									$sql .= "  'READ-IN' ";
									$sql .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
									QueryDatabase($sql, $results);
									error_log("### " . $File . " ROW NUMBER: " . $fileRow . " - Inserting");
									$row = mssql_fetch_assoc($results);
									$LAST_INSERT_ID = $row['LAST_INSERT_ID'];
							    	$fileRow++;
						    	}
							}
							fclose($handle);
						}

						switch($FLAG_ERR) {
							case "":
								$sql  = "UPDATE " . $SqlTableName_INBOUND_FILE_DATA;
								$sql .= " set STATUS = 'IMPORTED' ";
								$sql .= " WHERE RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
								$sql .= " and FILE_NAME='" . $File . "' ";
								QueryDatabase($sql, $results);

								//MOVE FILE FROM PENDING TO Imported
								rename($pendingDir . $File, $importedDir . $File . ".imported");
								error_log("### " . $File . " Moved from Pending to Imported");
							break;

							case "ERROR":
								$sql  = "UPDATE " . $SqlTableName_INBOUND_FILE_DATA;
								$sql .= " set STATUS = 'ERROR' ";
								$sql .= " WHERE RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
								$sql .= " and FILE_NAME='" . $File . "' ";
								QueryDatabase($sql, $results);
								error_log("### ERROR records File: ". $File .", runID_timestamp " . $runID_Timestamp . " updated to 'ERROR'");

								//MOVE FILE FROM PENDING TO Error
								rename($pendingDir . $File, $errorDir . $File . ".error");
								error_log("### " . $File . " Moved from Pending to ERROR");
								//$to = "gvandyne@thinknsa.com, rbollinger@thinknsa.com";
								$to = "gvandyne@thinknsa.com, rbollinger@thinknsa.com, jgrossman@thinknsa.com, tbielenberg@thinknsa.com, jmanganaro@thinknsa.com, jfinau@thinknsa.com";

								if ($TEST_ENV) {
									$to = "gvandyne@thinknsa.com";
								}

								$subject = "Touchpoint Inbound File Error: " . $File;
								$body = "Touchpoint Inbound File Error: " . $File ."\r\n REASON: " . $REASON_ERR;
								$body .= $ERR_BODY;
								$headers = "From: eProduction@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
								mail($to, $subject, $body, $headers);
								error_log("### Error email sent to: " . $to);
							break;

							case "PL-ORDER":
								$sql  = "UPDATE " . $SqlTableName_INBOUND_FILE_DATA;
								$sql .= " set STATUS = 'PL-ORDER' ";
								$sql .= " WHERE RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
								$sql .= " and FILE_NAME='" . $File . "' ";
								QueryDatabase($sql, $results);

								//MOVE FILE FROM PENDING TO Error
								rename($pendingDir . $File, $plDir . $File . ".pl-order");
								error_log("### " . $File . " Moved from Pending to PL-ORDER");
								$to = "gvandyne@thinknsa.com, rbollinger@thinknsa.com, jgrossman@thinknsa.com, tbielenberg@thinknsa.com, jmanganaro@thinknsa.com, jfinau@thinknsa.com";

								if ($TEST_ENV) {
									$to = "gvandyne@thinknsa.com";
								}

								$subject = "Touchpoint Inbound File PL-Order: " . $File;
								//$body = "Touchpoint Inbound File Error: " . $File ."\r\n REASON: " . $REASON_ERR;
								$body = "";
								$body .= $ERR_BODY;
								$headers = "From: eProduction@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
								mail($to, $subject, $body, $headers);
								error_log("### PL-ORDER email sent to: " . $to);
							break;
						}
					}

					$sql  = "SELECT distinct ";
					$sql .= " ib.ID_ORD_NUMERIC_ONLY  as ID_ORD, ";
					$sql .= " ib.ID_ORD_NUMERIC_ONLY, ";
					$sql .= " ib.ID_SHIP, ";
					$sql .= " ib.ID_SHIP_NUMERIC_ONLY, ";
					$sql .= " ib.ID_TRACK, ";
					$sql .= " '' as DATE_SHIP_ALPHA, ";
					$sql .= " '' as ID_CARRIER, ";
					$sql .= " ib.SERVICE as ID_SERVICE, ";
					$sql .= " ib.CHARGE_PKG, ";
					$sql .= " ib.WGT_PKG_ACTUAL, ";
					$sql .= " '0' as TIME_SHIP, ";
					$sql .= " '0' as FLAG_STATUS, ";
					$sql .= " ib.DATE_PROCESSED, ";
					$sql .= " convert(char(10),ib.DATE_PROCESSED,101) as DATE_PROCESSED3, ";
					$sql .= " sh.CODE_SHIP_VIA_CP, ";
					$sql .= " sh.DESCR_SHIP_VIA, ";
					$sql .= " sh.DESCR_TRMS, ";
					$sql .= " sh.DESCR_COL_PPD, ";
					$sql .= " sa.ACCT_SHIP_VIA_CP, ";
					$sql .= " '0' as FLAG_VOID ";
					$sql .= " from " . $SqlTableName_INBOUND_FILE_DATA . " ib ";
					$sql .= " left join nsa.CP_SHPHDR_PERM sh ";
					$sql .= " on ib.ID_SHIP_NUMERIC_ONLY = sh.ID_SHIP ";
					$sql .= " left join nsa.CP_SHP_SHIP_ACCT sa ";
					$sql .= " on ib.ID_SHIP_NUMERIC_ONLY = sa.ID_SHIP ";
					$sql .= " and ib.ID_ORD_NUMERIC_ONLY = sa.ID_SHIP ";
					$sql .= " WHERE ib.RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
					$sql .= " and ib.STATUS = 'IMPORTED' ";
					$sql .= " order by ib.ID_ORD_NUMERIC_ONLY asc ";
					QueryDatabase($sql, $results);
					$numToBill = mssql_num_rows($results);
					$body = "The following " . $numToBill . " shipments are ready to be billed:\r\n\r\n";
					while ($row = mssql_fetch_assoc($results)) {
						$SUM_QTY_SHIP = 0;

						$sql2  = "SELECT sum(ib.QTY_SHIP) as SUM_QTY_SHIP ";
						$sql2 .= " FROM " . $SqlTableName_INBOUND_FILE_DATA . " ib ";
						$sql2 .= " WHERE ib.RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
						$sql2 .= " and ib.ID_ORD_NUMERIC_ONLY = '" . $row['ID_ORD_NUMERIC_ONLY'] . "' ";
						$sql2 .= " and ib.ID_SHIP_NUMERIC_ONLY = '" . $row['ID_SHIP_NUMERIC_ONLY'] . "' ";
						QueryDatabase($sql2, $results2);
						while ($row2 = mssql_fetch_assoc($results2)) {
							$SUM_QTY_SHIP = $row2['SUM_QTY_SHIP'];
						}
						$body .= "\r\nOrder:		" . $row['ID_ORD'];
						$body .= "\r\nShipID:		" . $row['ID_SHIP'];
						$body .= "\r\nDate Ship:	" . $row['DATE_PROCESSED3'];
						$body .= "\r\nService:	" . $row['ID_SERVICE'];
						$body .= "\r\nDescr ShipVia:	" . $row['DESCR_SHIP_VIA'];
						$body .= "\r\nShip Charges:	" . $row['CHARGE_PKG'];
						$body .= "\r\nTerms:		" . $row['DESCR_COL_PPD'];
						$body .= "\r\nAccount:	" . $row['ACCT_SHIP_VIA_CP'];
						$body .= "\r\nTracking:	" . $row['ID_TRACK'];
						$body .= "\r\nTotal Qty:	" . $SUM_QTY_SHIP;
						$body .= "\r\n\r\n";

						if (stripNonNumericCharsNoSpace($row['ID_SHIP']) == "") {
							$row['ID_SHIP'] = "0";
						}

						$sql1  = "INSERT into " . $SqlTableName_CP_SHIP_IMPORT . " ( ";
						$sql1 .= " ID_ORD, ";
						$sql1 .= " ID_SHIP, ";
						$sql1 .= " ID_TRACK, ";
						$sql1 .= " DATE_SHIP_ALPHA, ";
						$sql1 .= " ID_CARRIER, ";
						$sql1 .= " ID_SERVICE, ";
						$sql1 .= " CHARGE_PKG, ";
						$sql1 .= " WGT_PKG_ACTUAL, ";
						$sql1 .= " TIME_SHIP, ";
						$sql1 .= " FLAG_STATUS, ";
						//$sql1 .= " DATE_PROCESSED, ";
						$sql1 .= " FLAG_VOID ";
						$sql1 .= " ) VALUES ( ";
						$sql1 .= " '" . stripNonNumericCharsNoSpace($row['ID_ORD_NUMERIC_ONLY']) . "', ";
						$sql1 .= " '" . stripNonNumericCharsNoSpace($row['ID_SHIP_NUMERIC_ONLY']) . "', ";
						$sql1 .= " '" . ms_escape_string(substr(trim($row['ID_TRACK']),0,20)) . "', ";
						$sql1 .= " '" . ms_escape_string(trim($row['DATE_SHIP_ALPHA'])) . "', ";
						$sql1 .= " '" . ms_escape_string(trim($row['ID_CARRIER'])). "', ";
						$sql1 .= " '" . ms_escape_string(substr(trim($row['ID_SERVICE']),0,30)) . "', ";
						$sql1 .= " '" . ms_escape_string(trim($row['CHARGE_PKG'])) . "', ";
						$sql1 .= " '" . ms_escape_string(trim($row['WGT_PKG_ACTUAL'])) . "', ";
						$sql1 .= " '" . trim($row['TIME_SHIP']) . "', ";
						$sql1 .= " '" . trim($row['FLAG_STATUS']) . "', ";
						//$sql1 .= " '" . ms_escape_string(trim($row['DATE_PROCESSED'])) . "', ";
						$sql1 .= " '" . trim($row['FLAG_VOID']) . "' ";
						$sql1 .= " ) ";
						QueryDatabase($sql1, $results1);
						error_log("### Inserted consolidated data to: " . $SqlTableName_CP_SHIP_IMPORT);
					}

					$to = "gvandyne@thinknsa.com, rbollinger@thinknsa.com, jgrossman@thinknsa.com, tbielenberg@thinknsa.com, jmanganaro@thinknsa.com, jfinau@thinknsa.com";

					if ($TEST_ENV) {
						$to = "gvandyne@thinknsa.com";
					}

					$subject = "Touchpoint orders (" . $numToBill . ") to be billed";
					$headers = "From: eProduction@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
					mail($to, $subject, $body, $headers);
					error_log("### Billing List email sent to: " . $to);

/*
					$sql  = "UPDATE " . $SqlTableName_INBOUND_FILE_DATA;
					$sql .= " set STATUS = 'IMPORTED' ";
					$sql .= " WHERE RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
					$sql .= " and STATUS='READ-IN' ";
					QueryDatabase($sql, $results);
					error_log("### runID_timestamp records " . $runID_Timestamp . " updated to 'IMPORTED'");
*/					
				}

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);
				$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
				QueryDatabase($sql, $results);

				error_log("### runFTP_FROM_TOUCHPOINT DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runFTP_FROM_TOUCHPOINT ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}				
			error_log("### runFTP_FROM_TOUCHPOINT finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runFTP_FROM_TOUCHPOINT cannot disconnect from database");
		}
	}
?>