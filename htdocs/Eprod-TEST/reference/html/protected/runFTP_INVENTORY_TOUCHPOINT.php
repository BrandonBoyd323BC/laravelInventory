<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}
	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runFTP_INVENTORY_TOUCHPOINT cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runFTP_INVENTORY_TOUCHPOINT cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runFTP_INVENTORY_TOUCHPOINT started at " . date('Y-m-d g:i:s a'));
			error_log("### runFTP_INVENTORY_TOUCHPOINT CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runFTP_INVENTORY_TOUCHPOINT' ";
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
				$sql .= "'runFTP_INVENTORY_TOUCHPOINT', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runFTP_INVENTORY_TOUCHPOINT SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);


				if ($TEST_ENV) {
					$ftp_server 	= "1.0.0.20";		//TEST DESKTOP-IT1
					$ftp_user_name 	= "thinknsa";
					$ftp_user_pass 	= "eshgoTwed=4";
					$baseDir = '/mnt/TouchpointFTP_TEST/Inventory';
					$SqlTableName_INVENTORY_DATA = "nsa.TOUCHPOINT_INVENTORY_TEST";
				} else {
					$ftp_server 	= "ftp.touchpointlogistics.com";  	//LIVE TOUCHPOINT
					$ftp_user_name 	= "nsa@touchpointlogistics.com";
					$ftp_user_pass 	= "nsa$2016";
					$baseDir = '/mnt/TouchpointFTP/Inventory';
					$SqlTableName_INVENTORY_DATA = "nsa.TOUCHPOINT_INVENTORY";
				}

				$archiveDir 	= $baseDir . "/Archive/";
				$pendingDir 	= $baseDir . "/Pending/";
				//$importedDir 	= $baseDir . "/Imported/";
				//$errorDir 	= $baseDir . "/Error/";


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
					$remote_dir = "/production/Inventory/";
					$bool_chdir = ftp_chdir($conn_id,$remote_dir);


					$RemoteFiles = ftp_nlist($conn_id,ftp_pwd($conn_id));
					$FileCount = count($RemoteFiles);
					if ($FileCount > 2) {
						error_log("### ". $FileCount ." File(s) to download (but really 2 are just system directories)");
						$i = 0;
						foreach ($RemoteFiles as $FullFile) {
							if ($FullFile <> "."  && $FullFile <> "..") {
								$i++;
								error_log("### File " . $i . " of " . $FileCount . " to download: " . $FullFile);
								$File = substr($FullFile,strrpos($FullFile,"/")+0);

								if (ftp_get($conn_id, $archiveDir . $File, $FullFile, FTP_BINARY)) {
									error_log("### File downloaded to: " . $archiveDir . $File);
									$filename_archive = $archiveDir . $File;
									$filename_pending = $pendingDir . $File;

									if (!copy($filename_archive, $filename_pending)) {
										error_log("### Failed to copy " . $filename_archive . " to " . $filename_pending);
									} else {
										error_log("### " . $File . " copied to pending dir");
									}

									if (ftp_delete($conn_id, $File)) {
										error_log("### " . $File ." deleted successfully from FTP server");
									} else {
										error_log("### FAILED TO DELETE " . $File ." from FTP server");
									}
								} else {
									error_log("### FTP download of " . $FullFile . " has failed!");
								}
							}
						}
					} else {
						error_log("### No Files to Download");

						$subject = "No Touchpoint Inventory File to Download";
						$body  = "No Touchpoint Inventory File to Download" . ".\r\n";
						$headers = "From: eProductionFTP@thinknsa.com" . "\r\n" .
							"X-Mailer: PHP/" . phpversion();


						$to = "gvandyne@thinknsa.com";
						error_log("Email to: " . $to);
						mail($to, $subject, $body, $headers);

						$to = "rbollinger@thinknsa.com";
						error_log("Email to: " . $to);
						mail($to, $subject, $body, $headers);

						$to = "jmartin@thinknsa.com";
						error_log("Email to: " . $to);
						mail($to, $subject, $body, $headers);

						$to = "tbielenberg@thinknsa.com";
						error_log("Email to: " . $to);
						mail($to, $subject, $body, $headers);

					}
					ftp_close($conn_id);
				}

				error_log("### runFTP_INVENTORY_TOUCHPOINT processing pending inventory at " . date('Y-m-d g:i:s a'));
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

					$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
					QueryDatabase($sql, $results);

					error_log("### runFTP_INVENTORY_TOUCHPOINT CHECKING FOR TEMP ");
					$sql = " IF OBJECT_ID('" . $SqlTableName_INVENTORY_DATA . "_TEMP', 'U') IS NOT NULL";
					$sql .= "	DROP TABLE " . $SqlTableName_INVENTORY_DATA . "_TEMP";
					QueryDatabase($sql, $results);

					error_log("### runFTP_INVENTORY_TOUCHPOINT CREATING " . $SqlTableName_INVENTORY_DATA . "_TEMP");
					$sql  = "CREATE TABLE " . $SqlTableName_INVENTORY_DATA . "_TEMP ( ";
					$sql .= "	[WHSE] [varchar](10) NOT NULL, ";
					$sql .= "	[CUSTOMER] [varchar](10) NOT NULL, ";
					$sql .= "	[PRODUCT] [varchar](30) NOT NULL, ";
					$sql .= "	[DESCRIPTION] [varchar](30) NOT NULL, ";
					$sql .= "	[AVAIL_QTY] [numeric](12,4) NOT NULL, ";
					$sql .= "	[ALLOC_QTY] [numeric](12,4) NOT NULL, ";
					$sql .= "	[HOLD_QTY] [numeric](12,4) NOT NULL, ";
					$sql .= "	[DMG_QTY] [numeric](12,4) NOT NULL, ";
					$sql .= "	[DUE_IN_QTY] [numeric](12,4) NOT NULL, ";
					$sql .= "	[BACKORDERED] [numeric](12,4) NOT NULL, ";
					$sql .= "	[TOTAL_QTY] [numeric](12,4) NOT NULL, ";
					$sql .= "	[TOTAL_WGT] [numeric](12,4) NOT NULL, ";
					$sql .= "	[DATE_UPDATED] datetime NOT NULL, ";
					$sql .= "	[rowid] [int] IDENTITY(1,1) NOT NULL ";
					$sql .= "	) ON [PRIMARY] ";
					QueryDatabase($sql, $results);

					foreach ($PendingFiles as $File) {
						$FLAG_ERR = "";
						$REASON_ERR = "";
						$ERR_BODY = "";
						error_log("### NEED TO PARSE FILE: " . $File);
						$fileRow = 1;
						error_log("### Parsing & Inserting rows");
						if (($handle = fopen($pendingDir . $File, "r")) !== FALSE) {
							$ERR_BODY .= "\r\n" . $File ."\r\n\r\n";
							while (($fileData = fgetcsv($handle, 1000, "|")) !== FALSE) {
								$numFields = count($fileData);
						    	if ($numFields <> 12) {
						    		error_log("Incorrect number of fields in line " . $fileRow);
						    		$FLAG_ERR = "ERROR";
						    		$REASON_ERR = "Incorrect number of fields in line";
						    	} else {
						    		//error_log("### " . $File . " ROW NUMBER: " . $fileRow . " - Parsing");

									$WHSE = $fileData[0];
									$CUSTOMER = $fileData[1];
									$PRODUCT = $fileData[2];
									$DESCRIPTION = $fileData[3];
									$AVAIL_QTY = $fileData[4];
									$ALLOC_QTY = $fileData[5];
									$HOLD_QTY = $fileData[6];
									$DMG_QTY = $fileData[7];
									$DUE_IN_QTY = $fileData[8];
									$BACKORDERED = $fileData[9];
									$TOTAL_QTY = $fileData[10];
									$TOTAL_WGT = $fileData[11];

									if ($fileRow > 1) {
										$sql  = " INSERT INTO " . $SqlTableName_INVENTORY_DATA ."_TEMP ( ";
										$sql .= "  WHSE, ";
										$sql .= "  CUSTOMER, ";
										$sql .= "  PRODUCT, ";
										$sql .= "  DESCRIPTION, ";
										$sql .= "  AVAIL_QTY, ";
										$sql .= "  ALLOC_QTY, ";
										$sql .= "  HOLD_QTY, ";
										$sql .= "  DMG_QTY, ";
										$sql .= "  DUE_IN_QTY, ";
										$sql .= "  BACKORDERED, ";
										$sql .= "  TOTAL_QTY, ";
										$sql .= "  TOTAL_WGT, ";
										$sql .= "  DATE_UPDATED ";
										$sql .= " ) VALUES ( ";
										$sql .= "  '". ms_escape_string($WHSE) ."', ";
										$sql .= "  '". ms_escape_string($CUSTOMER) ."', ";
										$sql .= "  '". strtoupper(ms_escape_string($PRODUCT)) ."', ";
										$sql .= "  '". ms_escape_string($DESCRIPTION) ."', ";
										$sql .= "  '". $AVAIL_QTY ."', ";
										$sql .= "  '". $ALLOC_QTY ."', ";
										$sql .= "  '". $HOLD_QTY ."', ";
										$sql .= "  '". $DMG_QTY ."', ";
										$sql .= "  '". $DUE_IN_QTY ."', ";
										$sql .= "  '". $BACKORDERED ."', ";
										$sql .= "  '". $TOTAL_QTY ."', ";
										$sql .= "  '". $TOTAL_WGT ."', ";
										$sql .= "  getDate() ";
										$sql .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
										QueryDatabase($sql, $results);
										//error_log("### " . $File . " ROW NUMBER: " . $fileRow . " - Inserting");

										$row = mssql_fetch_assoc($results);
										$LAST_INSERT_ID = $row['LAST_INSERT_ID'];
									}
							    	$fileRow++;
						    	}
							}
						    error_log("### Inserted " . $fileRow . " rows");

							$subject = "Touchpoint Inventory File Successfully loaded ".$fileRow." rows.";
							$body  = "Touchpoint Inventory File Successfully loaded ".$fileRow." rows.\r\n";
							$headers = "From: eProductionFTP@thinknsa.com" . "\r\n" .
								"X-Mailer: PHP/" . phpversion();

							$to = "gvandyne@thinknsa.com";
							error_log("Email to: " . $to);
							mail($to, $subject, $body, $headers);

							$to = "rbollinger@thinknsa.com";
							error_log("Email to: " . $to);
							mail($to, $subject, $body, $headers);

							$to = "jmartin@thinknsa.com";
							error_log("Email to: " . $to);
							mail($to, $subject, $body, $headers);

							$to = "tbielenberg@thinknsa.com";
							error_log("Email to: " . $to);
							mail($to, $subject, $body, $headers);

							fclose($handle);
						}

						error_log("### runFTP_INVENTORY_TOUCHPOINT CHECKING FOR " . $SqlTableName_INVENTORY_DATA);
						$sql = " IF OBJECT_ID('" . $SqlTableName_INVENTORY_DATA . "', 'U') IS NOT NULL";
						$sql .= "	DROP TABLE " . $SqlTableName_INVENTORY_DATA;
						QueryDatabase($sql, $results);

						error_log("### runFTP_INVENTORY_TOUCHPOINT RENAMING " . $SqlTableName_INVENTORY_DATA ."_TEMP to " . $SqlTableName_INVENTORY_DATA);
						$sql = " SP_RENAME '" . $SqlTableName_INVENTORY_DATA . "_TEMP' , '" . str_replace("nsa.","",$SqlTableName_INVENTORY_DATA) . "' ";
						QueryDatabase($sql, $results);

						if (!unlink($pendingDir . $File)) {
							error_log("Error deleting " . $pendingDir . $File);
						} else {
							error_log("### Successfully deleted " . $pendingDir . $File);
						}
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
				$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
				QueryDatabase($sql, $results);

				error_log("### runFTP_INVENTORY_TOUCHPOINT DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runFTP_INVENTORY_TOUCHPOINT ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}				
			error_log("### runFTP_INVENTORY_TOUCHPOINT finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runFTP_INVENTORY_TOUCHPOINT cannot disconnect from database");
		}
	}
?>