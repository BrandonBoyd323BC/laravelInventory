<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");
	require_once('classes/tc_calendar.php');

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	PrintHeaderJQ('Door Scans','default.css','doors.js');
	$retval = ConnectToDatabaseServer($DBServerAC, $dbAC);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServerAC!\n";
	} else {
		$retval = SelectDatabase($dbNameAC);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbNameAC!\n";
		} else {

			print("<body onLoad='doOnLoads()'>");

			print(" <table>\n");
			print(" 	<tr>\n");

			print(" 		<th colspan=2>Department <select id='sel_filterDepartment' onChange=\"deptChange()\"> \n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			print("					<option value='--TV List--'>--TV List--</option>\n");

			$sql  = "SELECT code, DeptName ";
			$sql .= " FROM dbo.DEPARTMENTS ";
			$sql .= " WHERE code not in (1,9999) ";
			$sql .= " ORDER BY DeptName asc";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['code'] . "'>" . $row['DeptName'] . "</option>\n");
			}
			print(" 		</th>\n");
			print(" 	</tr>\n");

			print(" </table>\n");
			print(" <div id='dataDiv'>\n");
			print(" </table>\n");
			print(" </br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($dbAC);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('');
?>
