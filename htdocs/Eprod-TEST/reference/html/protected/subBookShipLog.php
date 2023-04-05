<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2('Subsidiary Book/Ship Log','default.css','subBookShipLog.js');
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

			if ($UserRow['PERM_SUBSID'] == '1')  {
				print(" <form id='Sub_Form' method='post' enctype='multipart/form-data'>\n");			
				print(" <table>\n");
				print(" 	<tr>");
				print(" 		<td colspan=3>\n");
				print(" 		<LABEL for='selSubsidiary'>Subsidiary: </LABEL>\n");
				print("			<select name='selSubsidiary' id='selSubsidiary' onChange=\"selSubsidiaryChange()\">\n");
				print("				<option value='' SELECTED>--SELECT--</option>\n");
				//print("				<option value='Enespro'>Enespro</option>\n");
				//print("				<option value='Kunz Glove'>Kunz Glove</option>\n");
				//print("				<option value='NSA Arkansas'>NSA Arkansas</option>\n");
				print("				<option value='ForumLegacy'>Forum (Legacy)</option>\n");
				print("				<option value='NSA Kansas - Ad Spec'>NSA Kansas - Ad Specialty</option>\n");
				//print("				<option value='NSA Kansas - Postal'>NSA Kansas - Postal</option>\n");
				//print("				<option value='Wild Things'>Wild Things</option>\n");
				print("			</select>\n");
				print("			</td>\n");
				print(" 	</tr>\n");


				print(" 	<tr>\n");
				print(" 		<th colspan=2>Enter New Record: </th>\n");
				print(" 	</tr>\n");
				print("		<tr id='tr_date'>\n");
				print(" 		<td>Date: </td>\n");
				print(" 		<td>\n");
				$myCalendar = new tc_calendar('date_log', true, true);
				$myCalendar->setIcon("images/iconCalendar.gif");
				$futTS = strtotime("+0 days", time());
				$myCalendar->setDate(date('d',$futTS), date('m',$futTS), date('Y',$futTS));
				$myCalendar->setPath("/protected");
				$myCalendar->setYearInterval(1970, 2030);
				$myCalendar->setAlignment('left', 'bottom');
				$myCalendar->writeScript();
				print("			</td>\n");
				print(" 	</tr>\n");
				print("		<tr id='tr_sales'>\n");
				print(" 		<td>Sales $: </td>\n");
				print(" 		<td>\n");
				print("				<input id='sales' type=text maxlength=12 size=10 autofocus>\n");
				print("			</td>\n");
				print(" 	</tr>\n");

				print("		<tr id='tr_ship'>\n");
				print(" 		<td>Shipments $: </td>\n");
				print(" 		<td>\n");
				print("				<input id='ship' type=text maxlength=12 size=10 autofocus>\n");
				print("			</td>\n");
				print(" 	</tr>\n");

				print("		<tr id='tr_ship'>\n");
				print(" 		<td>Backlog $: </td>\n");
				print(" 		<td>\n");
				print("				<input id='backlog' type=text maxlength=12 size=10 autofocus>\n");
				print("			</td>\n");
				print(" 	</tr>\n");

				print(" 	<tr></tr>\n");
				print(" 	<tr>\n");
				print(" 		<td></td>\n");
				print(" 		<td><INPUT id='dw_submit' type='button' value='Add Record' onClick=\"sendAddValue()\"></td>\n");
				print(" 	</tr>\n");
				print(" </table>\n");
				print(" </form>\n");
				print("<body onLoad='doOnLoads()'>");
				print(" <table>\n");
				print(" 	<tr>\n");
				print(" 		<th colspan=2>Last <select id='num_recs' onChange=\"numRecsChange()\">\n");
				print("				<option value='10'>10</option>\n");
				print("				<option value='20' SELECTED>20</option>\n");
				print("				<option value='50'>50</option>\n");
				print("				<option value='100'>100</option>\n");
				print("				<option value='200'>200</option>\n");
				print("				<option value='500'>500</option>\n");
				print("				<option value='1000'>1,000</option>\n");
				print("				<option value='10000'>10,000</option>\n");
				print("				<option value='50000'>50,000</option>\n");
				print("				<option value='100000'>100,000</option>\n");
				print("				<option value='250000'>250,000</option>\n");
				print("				<option value='500000'>500,000</option>\n");
				print("			</select> Records: </th>\n");
				//print(" 		<th colspan=2>SO Lookup<input id='searchSO' name='searchSO' type='text' value='ALL' onblur=\"numRecsChange()\"></input><th>\n");		
				print(" 	</tr>\n");

				print(" </table>\n");
				print(" <div id='formDiv'>\n");
				print(" 	</br>\n");
				print(" </div>\n");			
				print(" <div id='dataDiv'>\n");
				print(" 	</br>\n");
				print(" </div>\n");
				print(" </br>\n");
				print(" </div>\n");
				print(" <div id='backgroundPopup'></div>\n");
				print(" <div id='dataPopup'></div>\n");

			}


		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('emenu.php');
?>