<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runFTP_TO_TOUCHPOINT_V2 cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runFTP_TO_TOUCHPOINT_V2 cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runFTP_TO_TOUCHPOINT_V2 started at " . date('Y-m-d g:i:s a'));
			error_log("### runFTP_TO_TOUCHPOINT_V2 CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runFTP_TO_TOUCHPOINT_V2' ";
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
				$sql .= "'runFTP_TO_TOUCHPOINT_V2', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,1,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runFTP_TO_TOUCHPOINT_V2 SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);


				if ($TEST_ENV) {
					$ftp_server 	= "1.0.0.20";		//TEST DESKTOP-IT1
					$ftp_server 	= "DESKTOP-IT1";
					$ftp_user_name 	= "thinknsa";
					$ftp_user_pass 	= "eshgoTwed=4";
					$baseDir = '/mnt/TouchpointFTP_TEST/Outbound';
					$SqlTableName_FTP_FILES = "nsa.TOUCHPOINT_FTP_FILES_TEST";
				} else {
					$ftp_server 	= "ftp.touchpointlogistics.com";  	//LIVE TOUCHPOINT
					$ftp_user_name 	= "nsa@touchpointlogistics.com";
					$ftp_user_pass 	= "nsa$2016";
					$baseDir = '/mnt/TouchpointFTP/Outbound';
					$SqlTableName_FTP_FILES = "nsa.TOUCHPOINT_FTP_FILES";
				}

				$archiveDir 	= $baseDir . "/Archive/";
				$pendingDir 	= $baseDir . "/Pending/";
				$importedDir 	= $baseDir . "/Imported/";
				$errorDir 	= $baseDir . "/Error/";
				$sentDir 	= $baseDir . "/Sent/";

				$PendingFiles = array();
				$PendingDirArray = scandir($pendingDir);
				foreach ($PendingDirArray as $PendingDirFile) {
					if (substr(strtoupper($PendingDirFile), -4) == ".CSV") {
						error_log("### PendingDirFile: " . $PendingDirFile);
						$PendingFiles[] = $PendingDirFile;
					}
				}

				$numFiles = count($PendingFiles);
				error_log("### numFiles: " . $numFiles);
/*
				if ($numFiles > 0) {
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
						//ftp_pasv($conn_id, false);

						foreach ($PendingFiles as $File) {
							error_log("### File to upload: " . $File);
							$local_file = $pendingDir . $File;
							//$destination_file = "/home/thinknsa/incoming/" . $File;
							$destination_file = "/Orders/" . $File;

							// Upload the File
							//$upload = ftp_put($conn_id, $destination_file, $local_file, FTP_BINARY);
							$upload = ftp_put($conn_id, $destination_file, $local_file, FTP_ASCII);

							// Verify Upload Status
							if (!$upload) {
								error_log("### FTP upload of " . $destination_file . " has failed!");
								$status = "FTP ERROR";
								//MOVE FILE TO ERROR DIR
								//rename($local_file, $errorDir . $File . ".error");
								//SEND ERROR EMAIL ALERT
								$subject = "FTP to Touchpoint failed " . $File;
								$body = "FTP to Touchpoint failed " . $File . "\r\nTransmission will retry on next script execution.";
								$headers = "From: eProduction@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
								$to = "gvandyne@thinknsa.com, rbollinger@thinknsa.com";
								mail($to, $subject, $body, $headers);
								error_log("### Email alert sent to  " . $to);
							} else {
								$status = "Sent";
								error_log("### Success!" . $File . " has been uploaded to " . $ftp_server . $destination_file . "!");
								//MOVE FILE TO SENT DIR
								rename($local_file, $sentDir . $File . ".sent");
							}
							$sql  = " UPDATE ";
							$sql .= $SqlTableName_FTP_FILES;
							$sql .= " set STATUS='".$status."', ";
							$sql .= " DATE_CHG = getDate() ";
							$sql .= " WHERE FILE_NAME = '" . $archiveDir . $File . "'";
							QueryDatabase($sql, $results);
						}
						ftp_close($conn_id);
					}
				}
*/

				if ($numFiles > 0) {
					$pasv = true;
					foreach ($PendingFiles as $File) {

						$local_file = $pendingDir . $File;
						error_log("local_file: ".$local_file);
						//$command = "ftp -in -v -p ftp://".$ftp_user_name.":".$ftp_user_pass."@".$ftp_server."/Orders/ ".$local_file;
						$command = "curl -T ".$local_file." ftp://".$ftp_server."/Orders/".$File." --user ".$ftp_user_name.":".$ftp_user_pass;
						error_log("command: ".$command);
						shell_exec($command);



						



/*
						// Connect to FTP Server
						$conn_id = ftp_connect($ftp_server,21,30);
						// Login to FTP Server
						$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
						// Verify Log In Status
						if ((!$conn_id) || (!$login_result)) {
							error_log("### FTP connection has failed!");
							error_log("### Attempted to connect to " . $ftp_server . " for user " . $ftp_user_name);
						} else {
							error_log("### Connected to " . $ftp_server . ", for user " . $ftp_user_name);
							ftp_pasv($conn_id, $pasv);
							//ftp_pasv($conn_id, false);
							error_log("### pasv: " . $pasv);
							error_log("### File to upload: " . $File);

							$local_file = $pendingDir . $File;
							//$destination_file = "/home/thinknsa/incoming/" . $File;
							$destination_file = "/Orders/" . $File;

							// Upload the File
							//$upload = ftp_put($conn_id, $destination_file, $local_file, FTP_BINARY);
							$upload = ftp_put($conn_id, $destination_file, $local_file, FTP_ASCII);

							// Verify Upload Status
							if (!$upload) {
								$pasv = !$pasv;
								error_log("### FTP upload of " . $destination_file . " has failed!");
								$status = "FTP ERROR";
								//MOVE FILE TO ERROR DIR
								//rename($local_file, $errorDir . $File . ".error");
								//SEND ERROR EMAIL ALERT
								$subject = "FTP to Touchpoint failed " . $File;
								$body = "FTP to Touchpoint failed " . $File . "\r\nTransmission will retry on next script execution.";
								$headers = "From: eProductionFTP@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
								$to = "gvandyne@thinknsa.com, rbollinger@thinknsa.com";
								mail($to, $subject, $body, $headers);
								error_log("### Email alert sent to  " . $to);
							} else {
								$status = "Sent";
								error_log("### Success!" . $File . " has been uploaded to " . $ftp_server . $destination_file . "!");
								//MOVE FILE TO SENT DIR
								rename($local_file, $sentDir . $File . ".sent");
							}
							$sql  = " UPDATE ";
							$sql .= $SqlTableName_FTP_FILES;
							$sql .= " set STATUS='".$status."', ";
							$sql .= " DATE_CHG = getDate() ";
							$sql .= " WHERE FILE_NAME = '" . $archiveDir . $File . "'";
							QueryDatabase($sql, $results);
							ftp_close($conn_id);
							error_log("### Disconnected");
						}
					*/
					}
				}



				error_log("### runFTP_TO_TOUCHPOINT_V2 DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runFTP_TO_TOUCHPOINT_V2 ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}	
		}		
		error_log("### runFTP_TO_TOUCHPOINT_V2 finished at " . date('Y-m-d g:i:s a'));
		error_log("#############################################");

		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runWC_OPEN cannot disconnect from database");
		}
	}
?>