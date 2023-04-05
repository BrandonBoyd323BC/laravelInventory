<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Attendance Approval','default.css','attendance.js');
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

			if ($UserRow['PERM_HR'] == '1') {

				print(" <table>\n");
				print(" 	<tr>\n");
				print(" 		<td>Date to be Approved: </td>\n");
				print(" 		<td>\n");

				$myCalendar = new tc_calendar('da', true, true);
				$myCalendar->setIcon("images/iconCalendar.gif");
				$myCalendar->setDate(date('d'), date('m'), date('Y'));
				$myCalendar->setPath("/protected");
				$myCalendar->setYearInterval(1970, 2030);
				$myCalendar->setAlignment('left', 'bottom');
				$myCalendar->writeScript();

				print(" 		</td>\n");
				print(" 	</tr>\n");
				print(" 	<tr>\n");
				print(" 		<td colspan=2>Comments: <INPUT type='text' id='cmts_approve'></td>\n");
				print(" 	</tr>\n");
				print(" 	<tr>\n");
				print(" 		<td colspan=3></td>\n");
				print(" 	</tr>\n");
				print(" 	<tr>\n");
				print(" 		<td colspan=3>I certify that all Attendance related transactions are correct for the selected date.</td>\n");
				print(" 	</tr>\n");
				print(" 	<tr>\n");
				print("			<td><input type='checkbox' id='chk_approve' value='1' /> Approve</td>\n");

				print(" 	</tr>\n");
				print(" 	<tr>\n");
				print(" 		<td></td>\n");
				print(" 		<td><INPUT type='submit' value='Submit' onClick=\"insertDCApprovalHRJS('100','ALL')\" ></td>\n");
				print(" 	</tr>\n");
				print(" </table>\n");
				print(" <div id='div_sub_approve'>\n");

				$sql2  = "select ";
				$sql2 .= "	top 30 *";
				$sql2 .= " from ";
				$sql2 .= "	nsa.DCAPPROVALS a ";
				$sql2 .= " where ";
				$sql2 .= " 	CODE_APP = '100' ";
				$sql2 .= " order by ";
				$sql2 .= " 	DATE_APP desc ";

				QueryDatabase($sql2, $results2);
				print("	<table class='sample'>\n");
				print("	<tr class='sample'>\n");
				print("		<th class='sample'>Approved Date</td>\n");
				print("		<th class='sample'>Badges Approved</td>\n");
				print("		<th class='sample'>Approved By</td>\n");
				print("		<th class='sample'>Approved On</td>\n");
				print("		<th class='sample'>Comments</td>\n");
				print("	</tr>\n");
				while ($row2 = mssql_fetch_assoc($results2)) {
					print("	<tr class='sample'>\n");
					print("		<td class='sample'>" . $row2['DATE_APP'] . "</td>\n");
					print("		<td class='sample'>" . $row2['BADGE_APP'] . "</td>\n");
					print("		<td class='sample'>" . $row2['APP_BY_ID_USER'] . "</td>\n");
					print("		<td class='sample'>" . $row2['DATE_ADD'] . "</td>\n");
					print("		<td class='sample'>" . $row2['COMMENTS'] . "</td>\n");
					print("	</tr>\n");
				}
				print("	</table>\n");
				print("	</div>\n");

			} else {
				print "					<p class='warning'>Permission Denied!</p>\n";
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('emenu.php');


?>
