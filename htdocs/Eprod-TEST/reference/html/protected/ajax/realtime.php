<?php

	//error_log("TEST");

	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	//PrintHeader('','default.css');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			//error_log("SendValue " . $_POST["sendValue"]);
			//if (isset($_POST["sendValue"]))  {
			if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["sendValue"]))  {

				$ovral_eff = GetEffScore($_POST["df"], $_POST["dt"], $_POST["sendValue"]);
				$pctClass = GetColorPct($ovral_eff);

				//error_log($ovral_eff);

				if ($_POST["from"] == 'dash') {
					$ret = "			<form class='sample' id='redir_" . $Team . "' name='redir_" . $Team . "' method='POST' action='../protected/activity.php'>\n";
					$ret .= "	 				<input type='hidden' id='df' name='df' value='" . $DateFrom . "'>\n ";
					$ret .= "	 				<input type='hidden' id='dt' name='dt' value='" . $DateTo . "'>\n";
					//$ret .= "	 				<input type='hidden' id='zh' name='zh' value='013000'>\n";
					$ret .= "	 				<input type='hidden' id='team' name='team' value='" . $Team . "'>\n";
					$ret .= "					<font class='" . $pctClass . "' onclick=\"goToActivity('" . $Team . "')\">" . $ovral_eff . "</font>\n";
					$ret .= "	 		</form>\n";
					//$ret .= "	<font class='" . $pctClass . "' onclick=\"goToActivity('" . $Team . "')\">" . $ovral_eff . "</font>\n";
				} else {
					$ret = "	<font class='" . $pctClass . "'>" . $ovral_eff . "</font>\n";
				}



				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
