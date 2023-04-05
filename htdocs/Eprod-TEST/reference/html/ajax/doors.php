<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");
	require_once('../classes/tc_calendar.php');
	

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}	

	$retval = ConnectToDatabaseServer($DBServerAC, $dbAC);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServerAC!\n");
	} else {
		$retval = SelectDatabase($dbNameAC);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $dbAC!\n");
		} else {
			$ret = '';

			$action = $_POST["action"];

			switch($action){
				case "dept_change";
					$Dept	= trim($_POST["dept"]);
					$ret1 = "";
					$inHQCount = 0;
					$inFCCount = 0;
					$inSchoolCount = 0;
					$outHQCount = 0;
					$outFCCount = 0;
					$outSchoolCount = 0;
					$inCount = 0;
					$outCount = 0;

					$sql  = "SELECT ";
					$sql .= " u.Name, ";
					$sql .= " u.lastname, ";
					$sql .= " convert(varchar, ml.time, 0) as time, ";
					$sql .= " concat(u.Name, ' ', u.lastname) as FullName, ";
					$sql .= " pa.areaname as Building, ";
					$sql .= " d.door_name, ";
					$sql .= " CASE ml.state ";
					$sql .= "   WHEN 0 THEN 'In' ";
					$sql .= "   WHEN 1 THEN 'Out' ";
					$sql .= " END as Status, ";
					$sql .= " dp.DEPTNAME ";
					$sql .= " FROM dbo.USERINFO u ";
					$sql .= " LEFT JOIN ( ";
					$sql .= "   SELECT ";
					$sql .= "   max(m1.id) as max_ml_id, ";
					$sql .= "   m1.pin ";
					$sql .= "   from dbo.acc_monitor_log m1 ";
					$sql .= "   left join dbo.acc_door d1 ";
					$sql .= "   on m1.device_id = d1.device_id ";
					$sql .= "   and m1.event_point_id = d1.door_no ";
					$sql .= "   where d1.is_att = 1 ";
					$sql .= "   and m1.event_type in (0,207) ";
					$sql .= "   group by m1.pin ";
					$sql .= " ) m2 ";
					$sql .= " on u.Badgenumber = m2.pin ";
					$sql .= " LEFT JOIN dbo.acc_monitor_log ml ";
					$sql .= " on m2.max_ml_id = ml.id ";
					$sql .= " LEFT JOIN dbo.acc_door d ";
					$sql .= " on ml.device_id = d.device_id ";
					$sql .= " and ml.event_point_id = d.door_no ";
					$sql .= " LEFT JOIN dbo.departments dp ";
					$sql .= " on u.DEFAULTDEPTID = dp.DEPTID ";
					$sql .= " LEFT JOIN dbo.Machines m ";
					$sql .= " on d.device_id = m.ID ";
					$sql .= " LEFT JOIN dbo.personnel_area pa ";
					$sql .= " on m.area_id = pa.id ";
					$sql .= " WHERE m2.max_ml_id IS NOT NULL ";
					$sql .= " and dp.code not in (1,9999) ";
					//$sql .= " and ml.time > Convert(DateTime, DATEDIFF(DAY, 1, GETDATE())) ";

					$sql .= "and ((ml.state = 0 AND ml.time > Convert(DateTime, DATEDIFF(DAY, 0, GETDATE()))) OR (ml.state = 1 AND ml.time > Convert(DateTime, DATEDIFF(DAY, 1, GETDATE()))))";

					if ($Dept <> '--ALL--' && $Dept <> '--TV List--') {
						$sql .= " and dp.code = '".$Dept."'";
					}
					if ($Dept == '--TV List--') {
						$sql .= " and dp.code between 500 and 999";
					}
					$sql .= " order by u.Name asc, u.lastname asc ";

					QueryDatabase($sql, $results);
					while ($row = mssql_fetch_assoc($results)) {
						if ($row['Status'] == 'In'){
							$statColor = 'darkgreen';
							switch ($row['Building']){
								case "HQ";
									$inHQCount++;
								break;
								case "FC";
									$inFCCount++;
								break;
								case "School";
									$inSchoolCount++;
								break;
							}//end switch
							$inCount++;
						} else {
							$statColor = 'red';
							switch ($row['Building']){
								case "HQ";
									$outHQCount++;
								break;
								case "FC";
									$outFCCount++;
								break;
								case "School";
									$outSchoolCount++;
								break;
							}//end switch
							$outCount++;
						}

						$ret1 .= " <tr>";
						$ret1 .= " 	<td><font class='" . $statColor . "'>" . $row['FullName'] . "</font></td>";
						$ret1 .= " 	<td><font class='" . $statColor . "'>" . $row['Status'] . "</font></td>";
						$ret1 .= " 	<td><font class='" . $statColor . "'>" . $row['time'] . "</font></td>";
						$ret1 .= " 	<td><font class='" . $statColor . "'>" . $row['Building'] . "</font></td>";
						$ret1 .= " 	<td><font class='" . $statColor . "'>" . $row['door_name'] . "</font></td>";
						$ret1 .= " </tr>";

					}
					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Building</th>\n";
					$ret .= " 		<th class='sample'>In</th>\n";
					$ret .= " 		<th class='sample'>Out</th>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<td>HQ</td>";
					$ret .= " 		<td>" . $inHQCount . "</td>";
					$ret .= " 		<td>" . $outHQCount . "</td>";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<td>FC</td>";
					$ret .= " 		<td>" . $inFCCount . "</td>";
					$ret .= " 		<td>" . $outFCCount . "</td>";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<td>School</td>";
					$ret .= " 		<td>" . $inSchoolCount . "</td>";
					$ret .= " 		<td>" . $outSchoolCount . "</td>";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>TOTAL</th>";
					$ret .= " 		<th class='sample'>" . $inCount . "</th>";
					$ret .= " 		<th class='sample'>" . $outCount . "</th>";
					$ret .= " 	</tr>\n";
					$ret .= " </table>\n";

					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Name</th>\n";
					$ret .= " 		<th class='sample'>Status</th>\n";
					$ret .= " 		<th class='sample'>Time</th>\n";
					$ret .= " 		<th class='sample'>Building</th>\n";
					$ret .= " 		<th class='sample'>Door</th>\n";
					$ret .= " 	</tr>\n";					
					$ret .= $ret1;
					$ret .= " </table>\n";
				break;
			}//end switch


			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($dbAC);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

?>
