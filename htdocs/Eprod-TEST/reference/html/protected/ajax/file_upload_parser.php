<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			$fileName = $_FILES["fileToUpload"] ["name"];
			$fileTmpLoc = $_FILES["fileToUpload"] ["tmp_name"];
			$fileType = $_FILES["fileToUpload"] ["type"];
			$fileSize = $_FILES["fileToUpload"] ["size"];
			$fileErrorMsg = $_FILES["fileToUpload"] ["error"];

			$markerName = $_POST["marker_name"];
			$newFilePath = "../GerberPDF/Upload/";
			$newFileName = $markerName . "___" . $UserRow['USER_NAME'] . "_tmp.pdf";
			$fullFilePathName = $newFilePath . $newFileName;

			if (!$fileTmpLoc) {
				echo "ERROR: Please browse for a file before clicking the upload button.";
				exit();
			}

			if (move_uploaded_file($fileTmpLoc, $fullFilePathName)) {
				echo "Upload is Complete";
				print("		<input id='ret_FileName' type='hidden' value='". $fullFilePathName ."'></input>");
			} else {
				echo "move_uploaded_file function failed";
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
