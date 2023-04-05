<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runFTP_FROM_UNITED cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runFTP_FROM_UNITED cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runFTP_FROM_UNITED started at " . date('Y-m-d g:i:s a'));

			if ($TEST_ENV) {
				$ftp_server 	= "1.0.0.20";		//TEST DESKTOP-IT1
				$ftp_user_name 	= "thinknsa";
				$ftp_user_pass 	= "eshgoTwed=4";
				$baseDir = '/mnt/UnitedFTP_TEST/Inbound';
			} else {
				$ftp_server 	= "64.207.228.146";  	//LIVE UNITED
				$ftp_user_name 	= "thinknsa";
				$ftp_user_pass 	= "eshgoTwed=4";
				$baseDir = '/mnt/UnitedFTP/Inbound';
			}

			$archiveDir 	= $baseDir . "/Archive/";
			$pendingDir 	= $baseDir . "/Pending/";
			$importedDir 	= $baseDir . "/Imported/";
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
				$remote_dir = "/thinknsa/outgoing/";
				$bool_chdir = ftp_chdir($conn_id,$remote_dir);


				$RemoteFiles = ftp_nlist($conn_id,ftp_pwd($conn_id));
				if (count($RemoteFiles) > 0) {
					foreach ($RemoteFiles as $FullFile) {
						error_log("### File to download: " . $FullFile);
						$File = substr($FullFile,strrpos($FullFile,"/")+1);
						//error_log("### File: " . $File);
						if (ftp_get($conn_id, $archiveDir . $File, $FullFile, FTP_BINARY)) {
							error_log("### File downloaded to: " . $archiveDir . $File);
						} else {
							error_log("### FTP download of " . $FullFile . " has failed!");
						}

	/*
						$local_file = $pendingDir . $File;
						$destination_file = "/home/thinknsa/incoming/" . $File;
						//$destination_dir = "/home/thinknsa/incoming/";
						//$bool_chdir = ftp_chdir($conn_id, $destination_dir);
						//error_log("### Change Dir Status: " . $bool_chdir);
						//// Upload the File
						//$upload = ftp_put($conn_id, $File, $local_file, FTP_BINARY);
						$upload = ftp_put($conn_id, $destination_file, $local_file, FTP_BINARY);

						// Verify Upload Status
						if (!$upload) {
							error_log("### FTP upload of " . $destination_file . " has failed!");
							//error_log("### FTP upload of " . $File . " has failed!");
							$status = "FTP ERROR";
							//MOVE FILE TO ERROR DIR
							rename($local_file, $errorDir . $File . ".error");
							//SEND ERROR EMAIL ALERT
							$subject = "FTP to United failed " . $File;
							$body = "FTP to United failed " . $File;
							$headers = "From: eProduction@nsamfg.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
							$to = "gvandyne@nsamfg.com";
							mail($to, $subject, $body, $headers);
							error_log("### Email alert sent to  " . $to);
						} else {
							$status = "Sent";
							error_log("### Success!" . $File . " has been uploaded to " . $ftp_server . $destination_file . "!");
							//MOVE FILE TO SENT DIR
							rename($local_file, $sentDir . $File . ".sent");
						}
						$sql  = " UPDATE nsa.UNITED_FTP_FILES ";
						$sql .= " set STATUS='".$status."', ";
						$sql .= " DATE_CHG = getDate() ";
						$sql .= " WHERE FILE_NAME = '" . $archiveDir . $File . "'";
						QueryDatabase($sql, $results);
	*/
					}
				} else {
					error_log("### No Files to Download");
				}
				ftp_close($conn_id);




/*
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

					foreach ($PendingFiles as $File) {
						error_log("### File to upload: " . $File);
						$local_file = $pendingDir . $File;
						$destination_file = "/home/thinknsa/incoming/" . $File;
						//$destination_dir = "/home/thinknsa/incoming/";
						//$bool_chdir = ftp_chdir($conn_id, $destination_dir);
						//error_log("### Change Dir Status: " . $bool_chdir);
						//// Upload the File
						//$upload = ftp_put($conn_id, $File, $local_file, FTP_BINARY);
						$upload = ftp_put($conn_id, $destination_file, $local_file, FTP_BINARY);

						// Verify Upload Status
						if (!$upload) {
							error_log("### FTP upload of " . $destination_file . " has failed!");
							//error_log("### FTP upload of " . $File . " has failed!");
							$status = "FTP ERROR";
							//MOVE FILE TO ERROR DIR
							rename($local_file, $errorDir . $File . ".error");
							//SEND ERROR EMAIL ALERT
							$subject = "FTP to United failed " . $File;
							$body = "FTP to United failed " . $File;
							$headers = "From: eProduction@nsamfg.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
							$to = "gvandyne@nsamfg.com";
							mail($to, $subject, $body, $headers);
							error_log("### Email alert sent to  " . $to);
						} else {
							$status = "Sent";
							error_log("### Success!" . $File . " has been uploaded to " . $ftp_server . $destination_file . "!");
							//MOVE FILE TO SENT DIR
							rename($local_file, $sentDir . $File . ".sent");
						}
						$sql  = " UPDATE nsa.UNITED_FTP_FILES ";
						$sql .= " set STATUS='".$status."', ";
						$sql .= " DATE_CHG = getDate() ";
						$sql .= " WHERE FILE_NAME = '" . $archiveDir . $File . "'";
						QueryDatabase($sql, $results);
					}
					ftp_close($conn_id);
				}
*/
			}

			error_log("### runFTP_FROM_UNITED finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runWC_OPEN cannot disconnect from database");
		}
	}
?>