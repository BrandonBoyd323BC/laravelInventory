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

	PrintHeader('NSA Internal WebServer ' . $SERVER_NAME,'default.css');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	error_log("db: " . $dbName);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			/*
			if ($baseSERVER_NAME == "LS1") {
				print("	</br>\n");
				print("	<h1>Your NSA IT department has moved this site to a new server.</h1>\n");
				print("	<h1>Please go to <a href='http://ls3" . $TEST_FLAG . "/'>http://ls3" . $TEST_FLAG . "/</a></h1>\n");
				print("	<h1>Use your CURRENT Windows username and password.</h1>\n");
				print("	</br>\n");
				print("	<h1>Please update any bookmarks or shortcuts to reflect the new location.</h1>\n");
				print("	<h1>(\"LS1\" is now \"LS3\")</h1>\n");
			*/
			//} else {

				///////////////////
				///// LOGIN
				///////////////////
				print("<table>\n");
				print("	<th colspan='3'>Login</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='/protected/emenu.php'><img class='icon' src='images/login_button_01.jpg' href='/protected/emenu.php'></br>Login</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("</br>\n");


				///////////////////
				///// SHOP FLOOR
				///////////////////
				print("<table>\n");
				print("	<th colspan='10'>Shop Floor</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('realtime.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='RealTime Efficiency'><img class='icon' src='images/percent.jpg' href='' onClick=\"popup = window.open('realtime.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='RealTime Efficiency'></br>Realtime Efficiency</a></td>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('lights.php', 'PopupPageLights', 'height=600,width=800,scrollbars=no,resizable=no')\"; return false' title='Lights'><img class='icon' src='images/led-strobe-light.jpg' href='' onClick=\"popup = window.open('lights.php', 'PopupPageLights', 'height=600,width=800,scrollbars=no,resizable=no')\"; return false' title='Lights'></br>Lights</a></td>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('soLabels.php', 'PopupPage', 'height=800,width=400,scrollbars=yes,resizable=no')\"; return false' title='Shop Order Labels'><img class='icon' src='images/labels.png' href='' onClick=\"popup = window.open('soLabels.php', 'PopupPage', 'height=800,width=400,scrollbars=yes,resizable=no')\"; return false' title='Shop Order Labels'></br>Shop Order Labels</a></td>\n");
				//print("		<td class='icon'><a href='' onClick=\"popup = window.open('lotTrackingSpreaders.php', 'PopupPage')\"; return false' title='Lot Tracking'><img class='icon' src='images/lottags.jpg' href='' onClick=\"popup = window.open('lotTrackingSpreaders.php', 'PopupPage')\"; return false' title='Lot Tracking'></br>Lot Tracking</a></td>\n");
				print("	</tr>\n");

				print("	<tr>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('qa_inspection.php', 'PopupPage' )\"; return false' title='QA Inspection'><img class='icon' src='images/InspectionLog.jpg' href='' onClick=\"popup = window.open('qa_inspection.php', 'PopupPage' )\"; return false' title='QA Inspection'></br>QA Inspection</a></td>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('itemInquiry.php', 'PopupPage' )\"; return false' title='Item Inquiry'><img class='icon' src='images/inquiry.jpg' href='' onClick=\"popup = window.open('itemInquiry.php', 'PopupPage' )\"; return false' title='Item Inquiry'></br>Item Inquiry</a></td>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('binlookup.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='Bin Lookup'><img class='icon' src='images/bin.jpg' href='' onClick=\"popup = window.open('binlookup.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='Bin Lookup'></br>Bin Lookup</a></td>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('prop65Checker.php', 'PopupPage' )\"; return false' title='Prop 65 Checker'><img class='icon' src='images/Prop65.jpg' href='' onClick=\"popup = window.open('prop65Checker.php', 'PopupPage' )\"; return false' title='Prop 65'></br>Prop 65 Checker</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("</br>\n");


				///////////////////
				///// TIME CLOCK
				///////////////////
				print("<table>\n");
				print("	<th colspan='3'>Time/Attendance</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('punchTracker.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='Punch Tracker'><img class='icon' src='images/punchTracker.jpg' href='' onClick=\"popup = window.open('punchTracker.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='Punch Tracker'></br>Punch Tracker</a></td>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('doors.php', 'PopupPage', 'height=800,width=800,scrollbars=no,resizable=yes')\"; return false' title='Door Scans'><img class='icon' src='images/rfid_fob.jpg' href='' onClick=\"popup = window.open('doors.php', 'PopupPage', 'height=800,width=800,scrollbars=no,resizable=no')\"; return false' title='Door Scans'></br>Door Scans</a></td>\n");

				print("	</tr>\n");
				print("</table>\n");
				print("</br>\n");


				///////////////////
				///// MAINTENANCE
				///////////////////
				print("<table>\n");
				print("	<th colspan='3'>Maintenance</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('maintlog.php', 'PopupPage', 'height=600,width=800,scrollbars=yes,resizable=yes')\"; return false' title='Maintenance Log'><img class='icon' src='images/man_maintenance.jpg' href='' onClick=\"popup = window.open('maintlog.php', 'PopupPage', 'height=600,width=800,scrollbars=yes,resizable=yes')\"; return false' title='Maintenance Log'></br>Maintenance Log</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("</br>\n");


				///////////////////
				///// SSRS Reports
				///////////////////
				print("<table>\n");
				print("	<th colspan='3'>Report Links</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='reportLinks.php'><img class='icon' src='/images/report.jpg' href='reportLinks.php'></br>Report Links</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("</br>\n");
				
				///////////////////
				///// WAREHOUSE
				///////////////////
				//print("<table>\n");
				//print("	<th colspan='3'>Warehouse</th>\n");
				//print("	<tr>\n");
				//print("		<td class='icon'><a href='' onClick=\"popup = window.open('bagLabels.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='Bag Labels'><img class='icon' src='images/bagger.jpg' href='' onClick=\"popup = window.open('bagLabels.php', 'PopupPage', 'height=600,width=400,scrollbars=no,resizable=no')\"; return false' title='Bag Labels'></br>Bag Labels</a></td>\n");				
				//print("	</tr>\n");				
				//print("</table>\n");
				//print("	</br>\n");
			//}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
