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

	$AreaName1 = '';
	if (isset($_GET['AreaName'])) {
		$AreaName1 = stripNonANChars($_GET['AreaName']);
	}

	//PrintHeaderJQ($AreaName1.' Occupants' ,'default.css','doorsTV.js');
	//PrintHeaderJQ('' ,'default.css','doorsTV.js');

	print("<html>\n");
	print("	<head>\n");
	print("		<meta http-equiv='Pragma' content='no-cache'>\n");
	print("		<meta http-equiv='Expires' content='-1'>\n");
	//print("		<script language='javascript' src='JavaScript/calendar.js'></script>\n");
	print("		<script type='text/javascript' src='JavaScript/jquery-1.6.4.js'  charset='utf-8'></script>\n");
	//if ($_SERVER['HTTPS']) {
	//	print("  	<script type='text/javascript' src='https://www.google.com/jsapi'></script>\n");	
	//} else {
	//	print("  	<script type='text/javascript' src='http://www.google.com/jsapi'></script>\n");	
	//}
	print("		<script type='text/javascript' src='JavaScript/doorsTV.js' charset='utf-8'></script>\n");
	print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/default.css'>\n");

	//print("		<title>$Title </title>\n");
	print("	</head>\n");
	print("	<body>\n");
	print("	<div id='divBody'>\n");
	print("		<table align='center' width='100%' height='100%' style='background-color: white;' cellspacing='2' cellpadding='2'>\n");
	//print("			<tr>\n");
	//print("				<td align='center' valign='top'>\n");
	//print("					<br/>\n");

	$retval = ConnectToDatabaseServer($DBServerAC, $dbAC);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServerAC!\n";
	} else {
		$retval = SelectDatabase($dbNameAC);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbNameAC!\n";
		} else {
			$ret = "";
			$ret .= " <tr>\n";


			//if (isset($_GET['AreaName'])) {
			//	$AreaName = stripNonANChars($_GET['AreaName']);
			//}



			$arrAreaNames = array('HQ','School','FC');

			$arrInOut = array(
				array('In','0'),
				array('Out','1')
			);

			foreach ($arrAreaNames as $AreaName) {

				//$ret .= " <td align='center' valign='top'>\n";
				//$ret .= " <br/>\n";
				//$ret .= "  <table class='sample'>\n";

				foreach ($arrInOut as $InOutValue) {

					error_log($InOutValue[0]);

					//$ret .= " <h1>".$AreaName." - ".$InOutValue[0]."</h1>\n";
					
					$ret .= " <td align='center' valign='top'>\n";
					$ret .= " <br/>\n";
					$ret .= "  <table class='sample'>\n";


					
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample' colspan=2>".$AreaName." - ".$InOutValue[0]."</th>\n";
					//$ret .= " 		<th class='sample'>Status</th>\n";

					$ret .= " 	</tr>\n";

					//$ret .= " 	<tr>\n";
					//$ret .= " 		<th class='sample'>Name</th>\n";
					////$ret .= " 		<th class='sample'>Status</th>\n";
					//$ret .= " 		<th class='sample'>Time</th>\n";
					////$ret .= " 		<th class='sample'>Door</th>\n";
					//$ret .= " 	</tr>\n";


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
					$sql .= " and pa.areaname = '".$AreaName."' ";
					$sql .= " and ml.state = '".$InOutValue[1]."' ";
					if ($InOutValue[0] == 'In') {
						$sql .= " and ml.time > Convert(DateTime, DATEDIFF(DAY, 0, GETDATE())) ";
					}
					if ($InOutValue[0] == 'Out') {
						$sql .= " and ml.time > Convert(DateTime, DATEDIFF(DAY, 0, GETDATE())) ";
					}
					$sql .= " and dp.code between 500 and 999";
					$sql .= " order by u.Name asc, u.lastname asc ";

					QueryDatabase($sql, $results);
					while ($row = mssql_fetch_assoc($results)) {
						if ($row['Status'] == 'In'){
							$statColor = 'darkgreen-small';
						} else {
							$statColor = 'red-small';
						}

						$ret .= " <tr>";
						$ret .= " 	<td><font class='" . $statColor . "'>" . $row['FullName'] . "</font></td>";
						//$ret .= " 	<td><font class='" . $statColor . "'>" . $row['Status'] . "</font></td>";
						$ret .= " 	<td><font class='" . $statColor . "'>" . $row['time'] . "</font></td>";
						//$ret .= " 	<td><font class='" . $statColor . "'>" . $row['door_name'] . "</font></td>";
						$ret .= " </tr>";

					}
					$ret .= " </table>\n";
					$ret .= "</td>";
				}

				//$ret .= " </table>\n";
				//$ret .= "</td>";
			}
		}








	









		print($ret);








			

/*
			//print("<body onLoad='doOnLoads()'>");
			print(" <table>\n");
			print(" 	<tr>\n");

			print(" 		<th colspan=2>Department <select id='sel_filterDepartment' onChange=\"deptChange()\"> \n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			print("					<option value='--SALARY--'>--SALARY--</option>\n");

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
*/
			print(" <div id='dataDiv'>\n");
			print(" </table>\n");
			print(" </br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		
		$retval = DisconnectFromDatabaseServer($dbAC);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('');
?>

