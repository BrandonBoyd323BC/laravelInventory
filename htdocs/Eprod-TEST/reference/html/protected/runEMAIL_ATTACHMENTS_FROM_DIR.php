<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/mail.class.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runEMAIL_ATTACHMENTS_FROM_DIR cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runEMAIL_ATTACHMENTS_FROM_DIR cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runEMAIL_ATTACHMENTS_FROM_DIR started at " . date('Y-m-d g:i:s a'));

			$baseDir = 'CPA_2016';
	
			$archiveDir 	= $baseDir . "/Sent2/";
			$pendingDir 	= $baseDir;
			$errorDir 	= $baseDir . "/Error/";

			$PendingFiles = array();
			$PendingDirArray = scandir($pendingDir);
			foreach ($PendingDirArray as $PendingDirFile) {
				//error_log("### PendingDirFile: " . $PendingDirFile);
				if (substr(strtoupper($PendingDirFile), -5) == ".XLSX") {
					error_log("### PendingDirFile: " . $PendingDirFile);
					$PendingFiles[] = $PendingDirFile;
				}
			}

			$numFiles = count($PendingFiles);
			error_log("### numFiles: " . $numFiles);

			if ($numFiles > 0) {
				foreach ($PendingFiles as $File) {
					error_log("### File to email: " . $File);
					$local_file = $pendingDir . $File;
					$cust = "";

					$files = array();
					$a_files = glob("CPA_2016/".$File, GLOB_BRACE);
					foreach ($a_files as $filename){
						$short_filename = substr($filename, strrpos($filename, '/') + 1);
						$cust = substr($short_filename, strrpos($filename, 'rpt_pricing_') + 3);
						error_log("CUST: ".$cust);
						$tempFilename = "/tmp/CPA/" . $short_filename;
						shell_exec("cp " . $filename . " " . $tempFilename);
						array_push($files, $tempFilename);
					}
/*
					$head = array(
						'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
						'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					);

					$subject = "Price Change Report " . $cust;
					//$body = GenerateHTMLforEmail($rgaNumber);
					$body = "See attached.";
					//$files = array($file1,$file2);
					if (!empty($files)) {
						mail::send($head,$subject,$body,$files);
					} else {
						mail::send($head,$subject,$body);
					}
					sleep(3);
*/

			
				}
			}
			error_log("### runEMAIL_ATTACHMENTS_FROM_DIR finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runEMAIL_ATTACHMENTS_FROM_DIR cannot disconnect from database");
		}
	}
?>