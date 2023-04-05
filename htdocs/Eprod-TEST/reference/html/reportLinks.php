<?php
	$DEBUG = 0;

	require_once("protected/procfile.php");

	$TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$TEST_FLAG = "TEST";
 	}

	//$SERVER_NAME = strtoupper(substr($_SERVER['HTTP_HOST'],0,3));
	$SERVER_NAME = strtoupper($_SERVER['HTTP_HOST']);
	$baseSERVER_NAME = substr($SERVER_NAME,0,3);

	PrintHeader('SSRS Report Links','default.css');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	error_log("db: " . $dbName);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			///////////////////
			///// SSRS Reports
			///////////////////
			print("<table>\n");
			print("	<th colspan='3'>SSRS Reports</th>\n");
			print("	<tr>\n");

			$baseURL = "http://wwsql/reportServer101/pages/ReportViewer.aspx?%2fTCM+reports+-+good%2f";


			$URL = $baseURL."rpt_NSA_Open_Orders_with_Prices2";
			$title = "Open Orders with Prices";
			print("		<td class='icon'><a href='".$URL."' target='_blank'><img class='icon' src='images/report.jpg' href='".$URL."' target='_blank'></br>".$title."</a></td>\n");

			$URL = $baseURL."rpt_NSA_Open_Lines_on_Order";
			$title = "Open Lines on an Order";
			print("		<td class='icon'><a href='".$URL."' target='_blank'><img class='icon' src='images/report.jpg' href='".$URL."' target='_blank'></br>".$title."</a></td>\n");


			$URL = $baseURL."rpt_NSA_Open_Orders_with_Prices2-DONE";
			$title = "MTO Done";
			print("		<td class='icon'><a href='".$URL."' target='_blank'><img class='icon' src='images/report.jpg' href='".$URL."' target='_blank'></br>".$title."</a></td>\n");

		
			print("	</tr>\n");
			print("</table>\n");
			print("</br>\n");
				
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
