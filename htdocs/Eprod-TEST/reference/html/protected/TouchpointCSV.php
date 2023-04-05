<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('DRIFIRE CSV files for Touchpoint Warehouse','default.css','touchpointCSV.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print("		<p class='warning'>Could Not Select $db!\n");
		} else {
			$baseDir = 'TouchpointFTP/Outbound';
			$pendingDir = $baseDir . "/Pending";
			$sentDir	= $baseDir . "/Sent";
			$errorDir	= $baseDir . "/Error";
			$holdDir	= $baseDir . "/Hold";

			print(" <table>");
			print(" 	<tr>");
			print(" 		<td>Order ID:</td>");
			print(" 		<td>");
			print(" 			<input type='text' id='tb_ID_ORD' name='tb_ID_ORD'>");
			print(" 		</td>");
			print(" 		<td>");
			print(" 			<INPUT type='submit' value='Find Shipments' onClick=\"sendLookupOrderShipment()\">");
			print(" 		</td>");
			print(" 	</tr>");
			print(" 	<tr>");
			print(" 		<td></td>");
			print(" 		<td><div id='shipDiv'></div>");
			print(" 		</td>");
			print(" 	</tr>");
			print(" </table>");

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
			print(" 		<th>Hold Files:</th>");
			print(" 	</tr>");
			$HoldArray	= scandir($holdDir,1);
			foreach ($HoldArray as $HoldFile) {
				if ($HoldFile <> "." && $HoldFile <> "..") {
					print(" 	<tr>");
					print(" 		<td>". $HoldFile ."</td>");
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
