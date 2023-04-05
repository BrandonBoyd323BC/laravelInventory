<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	//require_once("protected/procfile.php");
	require_once("procfile.php");

	PrintHeaderJQ2('Bartender Labels','default.css','btLabels.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			print(" <table>\n");

			$SELECTED_CHICAGO = "";
			$SELECTED_CLEVELAND_HQ= "";
			$SELECTED_CLEVELAND_FC = "";
			$SELECTED_CLEVELAND_SCHOOL = "";
			$SELECTED_ARKANSAS = "";
			$SELECTED_KANSAS = "";
			$SELECTED_BELLEVILLE = "";
			$LOCATION = "";
			$building = findBuildingByIP($_SERVER['REMOTE_ADDR']);

			switch ($building) {
				case "Chicago":
					$SELECTED_CHICAGO = "SELECTED";
					$LOCATION = "CHICAGO";
				break;
				case "HQ":
					$SELECTED_CLEVELAND_HQ = "SELECTED";
					$LOCATION = "CLEVELAND HQ";
				break;
				case "School":
					$SELECTED_CLEVELAND_SCHOOL = "SELECTED";
					$LOCATION = "CLEVELAND SCHOOL";
				break;
				case "FC":
					$SELECTED_CLEVELAND_FC = "SELECTED";
					$LOCATION = "CLEVELAND FC";
				break;
				case "Arkansas":
					$SELECTED_ARKANSAS = "SELECTED";
					$LOCATION = "ARKANSAS";
				break;
				case "Kansas":
					$SELECTED_KANSAS = "SELECTED";
					$LOCATION = "KANSAS";
				break;
				case "Belleville":
					$SELECTED_BELLEVILLE = "SELECTED";
					$LOCATION = "BELLEVILLE";
				break;
			}

			//$LOC_READONLY = 'DISABLED';
			$LOC_READONLY = '';

			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='sel_Location'>Location: </LABEL>\n");

			print("				<select id='sel_Location' onChange=\"showLocationChange()\" ".$LOC_READONLY.">\n");
			print("					<option value='Cleveland HQ' ".$SELECTED_CLEVELAND_HQ.">Cleveland HQ</option>\n");
			print("					<option value='Cleveland FC' ".$SELECTED_CLEVELAND_FC.">Cleveland FC</option>\n");
			print("					<option value='Cleveland School' ".$SELECTED_CLEVELAND_SCHOOL.">Cleveland School</option>\n");
			print("					<option value='Chicago' ".$SELECTED_CHICAGO.">Chicago</option>\n");
			print("					<option value='Arkansas'".$SELECTED_ARKANSAS.">Arkansas</option>\n");
			print("					<option value='Kansas'".$SELECTED_KANSAS.">Kansas</option>\n");
			print("					<option value='Belleville'".$SELECTED_BELLEVILLE.">Belleville</option>\n");
			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='sel2x2printer'>2\"x2\" Printer: </LABEL>\n");
			print("				<select id='sel2x2printer' onChange=\"savePrinterChange(this.id)\" >\n");
			print("					<option value='SELECT'>--SELECT--</option>\n");
			print("					<option value='HQ-Zebra-IT1'>HQ-Zebra-IT1</option>\n");
			print("				</select>\n");
			print("				<font id='sel2x2printerRESPONSE'></font>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='sel4x6printer'>4\"x6\" Printer: </LABEL>\n");
			print("				<select id='sel4x6printer' onChange=\"savePrinterChange(this.id)\">\n");
			print("					<option value='SELECT'>--SELECT--</option>\n");
			print("					<option value='HQ-Zebra-IT1'>HQ-Zebra-IT1</option>\n");
			print("				</select>\n");
			print("				<font id='sel4x6printerRESPONSE'></font>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td>\n");
			print(" 			<LABEL for='selMode'>Select Mode: </LABEL>\n");
			print("				<select name='selMode' id='selMode' onChange=\"selModeChange()\">\n");
			print("						<option value='--SELECT--'>--SELECT--</option>\n");
			print("						<option value='shopOrder'>Scan Shop Order</option>\n");
			print("						<option value='itemNumber'>Item Number</option>\n");
			print("						<option value='poReceived'>PO Received (Non-Bin 
				Tracked Only)</option>\n");
			print("						<option value='intransit'>Intransit # (Intercompany)</option>\n");
			print("				</select>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print(" <div id='formDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" <div id='dataDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
			print("<body onLoad='doOnLoads()'>\n");
			print("</body>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('');
?>