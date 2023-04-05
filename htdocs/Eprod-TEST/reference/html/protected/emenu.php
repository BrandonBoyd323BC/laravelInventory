<?php
	$DEBUG = 0;

	require_once("procfile.php");

	PrintHeader('NSA Internal WebServer','default.css');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
			print("	<h4><a href='logout.php'>Logout</a></h4>\n");

			if ($UserRow['PERM_SUPERVISOR'] == '1') {
				print("<table>\n");
				print("	<th colspan='5'>Supervisor</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='activity.php'><img class='icon' src='/images/log.jpg' href='activity.php'></br>Team Activity Log</a></td>\n");
				print("		<td class='icon'><a href='dashboard.php'><img class='icon' src='/images/speedometer.jpg' href='dashboard.php'></br>Supervisor Dashboard</a></td>\n");
				print("		<td class='icon'><a href='groupunit.php'><img class='icon' src='/images/groups.jpg' href='groupunit.php'></br>Group Dashboard</a></td>\n");
				print("	</tr>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='badgestatus.php'><img class='icon' src='/images/badge.jpg' href='badgestatus.php'></br>Badge Status</a></td>\n");
				print("		<td class='icon'><a href='coedash.php'><img class='icon' src='/images/pencil_eraser.jpg' href='coedash.php'></br>CoE Dash</a></td>\n");
				print("		<td class='icon'><a href='dctrancount.php'><img class='icon' src='/images/finalCountdown.jpg' href='dctrancount.php'></br>DC Tran Countdown</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
			if ($UserRow['PERM_HR'] == '1') {
				print("<table>\n");
				print("	<th colspan='6'>Human Resources</th>\n");
				print("	<tr>\n");
				//print("		<td class='icon'><a href='attendance2.php'><img class='icon' src='/images/timeclock.jpg' href='attendance.php'></br>Attendance Log</a></td>\n");
				//print("		<td class='icon'><a href='attend_approve.php'><img class='icon' src='/images/timeclock_app.jpg' href='attend_approve.php'></br>Attendance Approval</a></td>\n");
				//print("		<td class='icon'><a href='weeksheet.php'><img class='icon' src='/images/calendar.jpg' href='weeksheet.php'></br>Weekly TSS Worksheet</a></td>\n");
				print("		<td class='icon'><a href='paytrx.php'><img class='icon' src='/images/check.jpg' href='paytrx.php'></br>Payroll Summary</a></td>\n");
				print("		<td class='icon'><a href='twelveweeks.php'><img class='icon' src='/images/12.jpg' href='twelveweeks.php'></br>Twelve Week Average</a></td>\n");
				//print("		<td class='icon'><a href='holdef.php'><img class='icon' src='/images/sorry_closed.gif' href='holdef.php'></br>Holiday Schedule</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
			if ($UserRow['PERM_SUBSID'] == '1') {
				print("<table>\n");
				print("	<th colspan='2'>Subsidiary</th>\n");
				print("	<tr>\n");
				//print("		<td class='icon'><a href='mgmtdash.php'><img class='icon' src='/images/speedometer.jpg' href='mgmtdash.php'></br>Management Dashboard</a></td>\n");
				//print("		<td class='icon'><a href='mgmtdashG.php'><img class='icon' src='/images/speedometer.jpg' href='mgmtdashG.php'></br>Management DashboardG</a></td>\n");
				print("		<td class='icon'><a href='subBookShipLog.php'><img class='icon' src='/images/speedometer.jpg' href='subBookShipLog.php'></br>Subsidiary Book/Ship Log</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
			if ($UserRow['PERM_PLAN'] == '1') {
				print("<table>\n");
				print("	<th colspan='6'>Planning</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='wccap.php'><img class='icon' src='/images/capacity.jpg' href='wccap.php'></br>Workcenter Capacity</a></td>\n");
				print("		<td class='icon'><a href='wccapdash.php'><img class='icon' src='/images/capacity.jpg' href='wccapdash.php'></br>Workcenter Capacity Dashboard</a></td>\n");
				print("		<td class='icon'><a href='wcearnedmin.php'><img class='icon' src='/images/minutes.jpg' href='wcearnedmin.php'></br>Workcenter Earned Minutes</a></td>\n");
				print("	</tr>\n");
				//print("	<tr>\n");
				//print("		<td class='icon'><a href='lateordercodes.php'><img class='icon' src='/images/lateOrder.jpg' href='lateordercodes.php'></br>Late Order Codes</a></td>\n");
				//print("		<td class='icon'><a href='promise.php'><img class='icon' src='/images/target-date.jpg' href='promise.php'></br>Promise Date</a></td>\n");
				//print("		<td class='icon'><a href='fginv.php'><img class='icon' src='/images/inventory.jpg' href='fginv.php'></br>Finished Goods Inventory</a></td>\n");
				////print("		<td class='icon'><a href='vendorhist.php'><img class='icon' src='/images/vendor.jpg' href='vendorhist.php'></br>Vendor History</a></td>\n");
				//print("	</tr>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='comments.php'><img class='icon' src='/images/comments.jpg' href='comments.php'></br>Comments</a></td>\n");
				print("		<td class='icon'><a href='OP_Job_Cards_Planned.php'><img class='icon' src='/images/plan.png' href='OP_Job_Cards_Planned.php'></br>SO's Planned</a></td>\n");
				print("		<td class='icon'><a href='OP_Job_Cards_on_Floor.php'><img class='icon' src='/images/workOrder.png' href='OP_Job_Cards_on_Floor.php'></br>SO's Received from PreProd</a></td>\n");
				print("	</tr>\n");				
				print("</table>\n");
				print("	</br>\n");
			}
			if ($UserRow['PERM_CS'] == '1') {
				print("<table>\n");
				print("	<th colspan='6'>Customer Service</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='promise.php'><img class='icon' src='/images/target-date.jpg' href='promise.php'></br>Promise Date</a></td>\n");
				//print("		<td class='icon'><a href='UnitedCSV.php'><img class='icon' src='/images/fork2.png' href='UnitedCSV.php'></br>United CSV</a></td>\n");
				print("		<td class='icon'><a href='TouchpointCSV.php'><img class='icon' src='/images/fork2.png' href='TouchpointCSV.php'></br>Touchpoint CSV</a></td>\n");
				print("		<td class='icon'><a href='drfToFCBin.php'><img class='icon' src='/images/AARACK.jpg' href='drfToFCBin.php'></br>DRIFIRE Bin Changer</a></td>\n");
				//print("		<td class='icon'><a href='rgaform.php'><img class='icon' src='/images/RGAimg.jpg' href='rgaform.php'></br>RGA/Customer Complaint Form</a></td>\n");
				print("	<tr>\n");
				print("	</tr>\n");
				print("		<td class='icon'><a href='rgaform.php'><img class='icon' src='/images/evil_monkey.png' href='rgaform.php'></br>RGA/Customer Complaint Form</a></td>\n");
				//print("		<td class='icon'><a href='rgaformV3.php'><img class='icon' src='/images/evil_monkey.png' href='rgaformV3.php'></br>RGA/Customer Complaint Form V3</a></td>\n");
				print("		<td class='icon'><a href='ordstatus.php'><img class='icon' src='/images/Change4.jpg' href='ordstatus.php'></br>Order Status Change</a></td>\n");
				print("		<td class='icon'><a href='ediMaint.php'><img class='icon' src='/images/edi.png' href='ediMaint.php'></br>EDI Order Maintenance</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
/*
			if ($UserRow['PERM_PRODMGT'] == '1') {
				print("<table>\n");
				print("	<th colspan='1'>Product Management</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='stklist.php'><img class='icon' src='/images/stock.jpg' href='stklist.php'></br>Stock List</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
*/
/*
			if ($UserRow['PERM_RND_REQ'] == '1') {
				print("<table>\n");
				print("	<th colspan='1'>R&D</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='rndchg.php'><img class='icon' src='/images/change-ahead.jpg' href='rndchg.php'></br>R&D Change Request Form</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
*/
			if ($UserRow['PERM_PREPROD'] == '1') {
				print("<table>\n");
				print("	<th colspan='5'>Pre Production</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='soOpen.php'><img class='icon' src='/images/open.jpg' href='soOpen.php'></br>Shop Order Open</a></td>\n");
				//print("		<td class='icon'><a href='fabspec.php'><img class='icon' src='/images/fabspec.jpg' href='fabspec.php'></br>Fabric Specs</a></td>\n");
				print("		<td class='icon'><a href='markerlog.php'><img class='icon' src='/images/marker_log.jpg' href='markerlog.php'></br>Marker Log</a></td>\n");
				print("		<td class='icon'><a href='soApproval.php' title='Shop Orders get scanned here when they are ready to go out to Production'><img class='icon' src='/images/approvalCN.png' href='soApproval.php' title='Shop Orders get scanned here when they are ready to go out to Production'></br>Shop Order Approval</a></td>\n");
				print("		<td class='icon'><a href='btLabels.php'><img class='icon' src='/images/UPCIcon.jpg' href='btLabels.php'></br>Bartender Labels</a></td>\n");	
				print("		<td class='icon'><a href='opLabelsReady.php'><img class='icon' src='/images/NSA_Label.jpg' href='opLabelsReady.php'></br>Labels Ready</a></td>\n");	
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}

			if ($UserRow['PERM_SPREADING'] == '1') {
				print("<table>\n");
				print("	<th colspan='3'>Spreading</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='lottracking.php' title='Spreaders log Markers and Lot Numbers here'><img class='icon' src='/images/lottags.jpg' href='lottracking.php' title='Spreaders log Markers and Lot Numbers here'></br>Lot Tracking</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}

			if ($UserRow['PERM_CUTTING'] == '1') {
				print("<table>\n");
				print("	<th colspan='3'>Cutting</th>\n");
				print("	<tr>\n");
				//print("		<td class='icon'><a href='OP_Job_Cards_on_Floor.php'><img class='icon' src='/images/workOrder.png' href='OP_Job_Cards_on_Floor.php'></br>SO's Received from PreProd</a></td>\n");
				print("		<td class='icon'><a href='markerscut.php'><img class='icon' src='/images/scissors.png' href='markerscut.php'></br>Markers Cut</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}

			if ($UserRow['PERM_ORD_PREP'] == '1') {
				print("<table>\n");
				print("	<th colspan='3'>Order Prep</th>\n");
				print("	<tr>\n");
				//print("		<td class='icon'><a href='lights_dash.php'><img class='icon' src='/images/led-strobe-light.jpg' href='lights_dash.php'></br>Lights Dashboard</a></td>\n");
				print("		<td class='icon'><a href='' onClick=\"popup = window.open('lights_dash.php', 'PopupPage', 'height=600,width=800,scrollbars=no,resizable=no')\"; return false' title='Lights Dashboard'><img class='icon' src='/images/led-strobe-light.jpg' href='' onClick=\"popup = window.open('lights_dash.php', 'PopupPage', 'height=600,width=800,scrollbars=no,resizable=no')\"; return false' title='Lights Dashboard'></br>Lights Dashboard</a></td>\n");				
				//print("		<td class='icon'><a href='ordprep_dash.php'><img class='icon' src='/images/missing-piece2.jpg' href='ordprep_dash.php'></br>Missing Pieces</a></td>\n");
				//print("		<td class='icon'><a href='lottracking.php'><img class='icon' src='/images/lottags.jpg' href='lottracking.php'></br>Lot Tracking</a></td>\n");

				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}



			if ($UserRow['PERM_MAINT'] == '1') {
				print("<table>\n");
				print("	<th colspan='3'>Maintenance</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='machinv.php'><img class='icon' src='/images/sewing_machine_02.png' href='machinv.php'></br>Machine Inventory</a></td>\n");
				print("		<td class='icon'><a href='partsinv.php'><img class='icon' src='/images/parts.jpg' href='partsinv.php'></br>Parts Inventory</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}

			if ($UserRow['PERM_WH'] == '1') {
				print("<table>\n");
				print("	<th colspan='4'>Warehouse</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='itmBinChgr.php'><img class='icon' src='/images/AARACK.jpg' href='itmBinChgr.php'></br>Item Primary Bin Changer</a></td>\n");
				print("		<td class='icon'><a href='binCount.php'><img class='icon' src='/images/count.jpg' href='binCount.php'></br>Bin Count</a></td>\n");
				print("		<td class='icon'><a href='rollCountVerify.php'><img class='icon' src='/images/count.jpg' href='rollCountVerify.php'></br>Roll Count Verify</a></td>\n");
				print("		<td class='icon'><a href='btLabels.php'><img class='icon' src='/images/UPCIcon.jpg' href='btLabels.php'></br>Bartender Labels</a></td>\n");	
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}

			if ($UserRow['PERM_QA'] == '1') {
				print("<table>\n");
				print("	<th colspan='3'>Quality Assurance</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='lottracking.php'><img class='icon' src='/images/lottags.jpg' href='lottracking.php'></br>Lot Tracking</a></td>\n");
				print("		<td class='icon'><a href='inspectionLandingPage.php'><img class='icon' src='/images/InspectionLog.jpg' href='inspectionLandingPage.php'></br>QA Inspection</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}

			if ($UserRow['PERM_SU'] == '1') {
				print("<table>\n");
				print("	<th colspan='5'>SuperUser</th>\n");
				print("	<tr>\n");
				print("		<td class='icon'><a href='su3.php'><img class='icon' src='/images/superuser.jpg' href='su3.php'></br>SuperUser</a></td>\n");
				print("		<td class='icon'><a href='prodOperRebuild.php'><img class='icon' src='/images/superuser.jpg' href='prodOperRebuild.php'></br>PROD Oper Rebuild</a></td>\n");
				print("		<td class='icon'><a href='tvControl.php'><img class='icon' src='/images/tvRemote.jpg' href='tvControl.php'></br>TV Control</a></td>\n");
				//print("		<td class='icon'><a href='fix_MONFMT.php'><img class='icon' src='/images/superuser.jpg' href='fix_MONFMT.php'></br>Fix TCM MONFMT Errors</a></td>\n");
				//print("		<td class='icon'><a href='recalcApprovals.php'><img class='icon' src='/images/superuser.jpg' href='recalcApprovals.php'></br>Recalc Approvals</a></td>\n");
				//print("		<td class='icon'><a href='salesforce.php'><img class='icon' src='/images/salesforce.jpg' href='salesforce.php'></br>Sales Force CSVs</a></td>\n");
				print("	</tr>\n");
				print("</table>\n");
				print("	</br>\n");
			}
			print("	</br>\n");
			print("<table>\n");
			print("			<tr>\n");
			print("				<td><a href='http://paypal.me/ITNSA' title='Buy a thirsty IT guy a beer?'><img src='/images/donate.gif' href='http://paypal.me/ITNSA'></a></td>\n");
			print("			</tr>\n");
			print("</table>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

?>
