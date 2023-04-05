<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Finished Goods Inventory','default.css','fginv.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {

			print(" <table>");
			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<select id='action'>\n");
			print(" 				<option value='itemDetail'>Stock Item Detail</option>\n");
			print(" 				<option value='dash'>WC Dashboard</option>\n");
			print(" 			</select>\n");
			print(" 		</td>\n");
			print(" 		<td >\n");
			print(" 			<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('emenu.php');
?>
