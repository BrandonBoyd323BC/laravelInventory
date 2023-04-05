<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('RGA PAGE','default.css','rgaformV3.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			$rgaNumber = htmlspecialchars($_GET["rgaNumber"]);

			print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th><input id='button_NewReqForm' name='button_NewReqForm' type='button' value='New Request' onClick='goToNewRequestPopUp()'></input></th>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");			
			print("			<td>Filter Customer Status:\n");
			print(" 			<select id='filterCustomerStatus' onChange=\"filterRgaStatus()\">\n");
			print("					<option value='%'>-- ALL --</option>\n");
			$statusArray = array('Open-Customer','Open-NSA','Resolved');
			foreach ($statusArray as $code_status ) {
				$SELECTED = '';
				if($code_status == 'ALL'){
					$SELECTED = 'SELECTED';
				}
				print("					<option value='" . $code_status . "' " . $SELECTED . ">" . $code_status . "</option>\n");
			}
			print(" 			</select>\n");	
			print("			</td>\n");
			print("		</tr>\n");

			print(" 	<tr>\n");
			print("			<td>Filter ISO Status:\n");
			print(" 			<select id='filterisoStatus' onChange=\"filterRgaStatus()\">\n");
			print("					<option value='%'>-- ALL --</option>\n");
			$statusArray2 = array('Open','Recieved','Closed');
			foreach ($statusArray2 as $code_status2 ) {
				$SELECTED2 = '';
				if($code_status2 == 'ALL'){
					$SELECTED2 = 'SELECTED';
				}
				print("					<option value='" . $code_status2 . "' " . $SELECTED2 . ">" . $code_status2 . "</option>\n");
			}
			print(" 			</select>\n");				
			print("			</td>\n");
			print("		</tr>\n");
			print("     <tr>\n");
			print(" 		<td>Filter RGA Number:<input id='filterRgaNumber' name='filterRgaNumber' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
			print("		</tr>\n");
			print("     <tr>\n");
			print(" 		<td>Filter Customer Number:<input id='filterCustomerNumber' name='filterCustomerNumber' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
			print("		</tr>\n");
			print("     <tr>\n");
			print(" 		<td>Filter Customer Name:<input id='filterCustomerName' name='filterCustomerName' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
			print("		</tr>\n");
			print("     <tr>\n");
			print(" 		<td>Filter Created By:<input id='filterCreatedBy' name='filterCreatedBy' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
			print("		</tr>\n");
			print("     <tr>\n");
			print(" 		<td>Limit # Results: <select id='filterNumResults' name='filterNumResults' onChange=\"filterRgaStatus()\">\n");
			print("				<option value='10'>10</option>\n");
			print("				<option value='20' SELECTED>20</option>\n");
			print("				<option value='50'>50</option>\n");
			print("				<option value='100'>100</option>\n");
			print("				<option value='ALL'>ALL</option>\n");
			print("			</select></td>\n");
			print("		</tr>\n");
			print("</table>");
			print("	</br>\n");
			print(" <div id ='mainDiv'>\n");
			print(" <body onload=\"filterRgaStatus()\"></div>\n");
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
