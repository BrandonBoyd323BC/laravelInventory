<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	$ua=getBrowser();

	PrintHeaderJQ('RGA/ Customer Complaint Form','default.css','rgaform.js');
	if ($ua['short_name'] == 'Chrome') {
		$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Connect To $DBServer!\n";
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				print "		<p class='warning'>Could Not Select $dbName!\n";
			} else {
				$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
				print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
				print(" <table>\n");
				print(" 	<tr>\n");
				print(" 		<th><input id='button_NewReqForm' name='button_NewReqForm' type='button' value='New Request' onClick='goToNewRequestPopUp()'></input></th>\n");
				print(" 	</tr>\n");
				print("     <tr>\n");
				print("			<td>Filter RGA Status:\n");		
				print("				<select name='filterRgaStatus' id='filterRgaStatus' onchange=\"filterRgaStatus()\">\n");						
				print("					<option selected value='Open'>Open</option>\n");
				print("					<option value='Pending'>Pending Investigation</option>\n");
				print("					<option value='Cancelled'>Cancelled</option>\n");
				print("					<option value='Closed'>Closed</option>\n");
				print("					<option value='ALL'>ALL</option>\n");
				print("				</select>\n");							
				print("			</td>\n");
				print("		</tr>\n");
				print("     <tr>\n");
				print("			<td>Filter ISO Status:\n");
				print("				<select name='filterIsoStatus' id='filterIsoStatus' onchange=\"filterRgaStatus()\">\n");
				print("					<option value='ALL' selected>ALL</option>\n");
				$isoStatus = array("Drafted", 
					"Pending Extenuating Issues", 
					"Waiting for Approval", 
					"Waiting for Production", 
					"Waiting for Customer Service", 
					"Waiting for Inventory Audit", 
					"Waiting for Manufacturer Response", 
					"Waiting for Pricing Info", 
					"Waiting for Rework/Replacement Number", 
					"Working with Production Development", 
					"Working with Purchasing", 
					"Closed");
				foreach ($isoStatus as $SELECT_ISO_STATUS) {
					print("				<option value='" . $SELECT_ISO_STATUS . "'>" . $SELECT_ISO_STATUS .  "</option>\n");
				}
				print("				</select>\n");							
				print("			</td>\n");
				print("		</tr>\n");
				print("     <tr>\n");
				print(" 		<td>Filter RGA Number:<input id='filterRgaNumber' name='filterRgaNumber' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
				print("		</tr>\n");
				print("     <tr>\n");
				print(" 		<td>Filter Cust Number: <input id='filterCustNo' name='filterCustNo' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
				print("		</tr>\n");
				print("     <tr>\n");
				print(" 		<td>Filter PO Number: <input id='filterPONo' name='filterPONo' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
				print("		</tr>\n");
				print("     <tr>\n");
				print(" 		<td>Filter Order Number: <input id='filterOrdNo' name='filterOrdNo' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
				print("		</tr>\n");
				print("     <tr>\n");
				print(" 		<td>Filter Created By: <input id='filterCreatedBy' name='filterCreatedBy' type='textbox' value='ALL' onblur=\"filterRgaStatus()\"></input></td>\n");
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
				print(" </table>\n");
				print("	</br>\n");
				print(" <div id='mainDiv'>\n");
				//print(" <body onload=\"disablePopup('mainDiv')\"></div>");
				print(" <body onload=\"filterRgaStatus('mainDiv')\"></div>\n");
				print(" <div id='dataDiv'></div>\n");
				print(" <div id='backgroundPopup'></div>\n");
				print(" <div id='dataPopup'></div>\n");
			}
			$retval = DisconnectFromDatabaseServer($db);
			if ($retval == 0) {
				print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
			}
		}
	} else {
		print "		<p class='warning'>This Application requires Google Chrome.  You are currently using: " . $ua['name'] . "</p><br>\n";
		$yourbrowser= "Your browser: " . $ua['short_name'] . " " . $ua['version'] . " on " .$ua['platform'] . " reports: <br >" . $ua['userAgent'];
		print_r($yourbrowser);		
	}

	PrintFooter('emenu.php');

?>
