<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Spentex CSV files for United Warehouse','default.css','unitedCSV.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print("		<p class='warning'>Could Not Select $db!\n");
		} else {
			$baseDir = 'UnitedFTP/Outbound';
			$pendingDir 	= $baseDir . "/Pending";
			$sentDir	= $baseDir . "/Sent";
			$errorDir	= $baseDir . "/Error";

			print(" <table>");
			print(" 	<tr>");
			print(" 		<td>Order ID:</td>");
			print(" 		<td>");
			print(" 			<input type='text' id='ID_ORD' name='ID_ORD'>");
			print(" 		</td>");
			print(" 		<td>");
			print(" 			<INPUT type='submit' value='Generate CSV' onClick=\"sendGenerateCSV()\">");
			print(" 		</td>");
			print(" 	</tr>");
			print(" </table>");

			print(" <div id='LoadingDiv'></div>\n");
			print(" <div id='dataDiv'>\n");
			print(" <br>");
			print(" <table class='sample'>");
			print(" 	<tr>");
			print(" 		<th>Error Files:</th>");
			print(" 	</tr>");
			$ErrorArray	= scandir($errorDir,1);
			foreach ($ErrorArray as $ErrorFile) {
				if ($ErrorFile <> "." && $ErrorFile <> "..") {
					print(" 	<tr>");
					print(" 		<td>". $ErrorFile ."</td>");
					print(" 	</tr>");
				}
			}
			print(" </table>");
			print(" <br>");
			print(" <table class='sample'>");
			print(" 	<tr>");
			print(" 		<th>Pending Files:</th>");
			print(" 	</tr>");
			$PendingArray	= scandir($pendingDir,1);
			foreach ($PendingArray as $PendingFile) {
				if ($PendingFile <> "." && $PendingFile <> "..") {
					print(" 	<tr>");
					print(" 		<td>". $PendingFile ."</td>");
					print(" 	</tr>");
				}
			}
			print(" </table>");
			print(" <br>");
			print(" <table class='sample'>");
			print(" 	<tr>");
			print(" 		<th>Sent Files:</th>");
			print(" 	</tr>");
			$SentArray	= scandir($sentDir,1);
			foreach ($SentArray as $SentFile) {
				if ($SentFile <> "." && $SentFile <> "..") {
					print(" 	<tr>");
					print(" 		<td>". $SentFile ."</td>");
					print(" 	</tr>");
				}
			}
			print(" </table>");
			print(" </div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
	print(" </br>");
	PrintFooter("emenu.php");
?>
