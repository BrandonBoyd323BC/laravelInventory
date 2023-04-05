<?php
	$DEBUG = 0;

	require_once("procfile.php");
	include('phpseclib1.0.11/Net/SSH2.php');
	
	PrintHeaderJQ2OL('TV Controller','default.css','tvControl.js','doOnLoad');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print(" <div id='dataDiv'></div>\n");



			PrintFooter("emenu.php");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>


