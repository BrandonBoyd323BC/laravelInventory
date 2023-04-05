<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCSR_ORD_COUNT cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCSR_ORD_COUNT cannot select " . $dbName);
		} else {
			$filename = "/tmp/fastenal/NSA_stock_inv.csv";
			error_log("#############################################");
			error_log("### runCSR_ORD_COUNT started at " . date('Y-m-d g:i:s a'));
			

			$directory = "/mnt/netshare/IncomingFaxes/CS - Anna Ghallab/";//dir location
			$filecount = 0;
			$files = glob($directory . "*.pdf");
			if ($files){
			 $filecount = count($files);
			}
			echo "There are $filecount files";
			echo "\n";

			$directory = "/mnt/netshare/IncomingFaxes/CS - Annette Huffman/";//dir location
			$filecount = 0;
			$files = glob($directory . "*.pdf");
			if ($files){
			 $filecount = count($files);
			}
			echo "There are $filecount files";
			echo "\n";

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);
			
			

			error_log("### runCSR_ORD_COUNT finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCSR_ORD_COUNT  cannot disconnect from database");
		}
	}


?>