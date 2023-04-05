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
			//$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			$action = $_POST["action"];

			switch($action){
				case "dept_change";
					$Dept	= trim($_POST["dept"]);


					$SalaryDeptList = "'875','700','800','575','825','650'";

					$ret = " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Name</th>\n";
					$ret .= " 		<th class='sample'>Status</th>\n";
					$ret .= " 		<th class='sample'>Time</th>\n";
					$ret .= " 		<th class='sample'>Building</th>\n";
					$ret .= " 		<th class='sample'>Door</th>\n";
					$ret .= " 	</tr>\n";


					$sql  = "SELECT ";
					$sql .= " u.Name, ";
					$sql .= " u.lastname, ";
					//$sql .= " convert(varchar, ml.time, 0) as time, ";
					$sql .= " ml.time, ";
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
					if ($Dept <> '--ALL--' && $Dept <> '--SALARY--') {
						$sql .= " and dp.code = '".$Dept."'";
					}
					if ($Dept == '--SALARY--') {
						$sql .= " and dp.code in (".$SalaryDeptList.")";
					}
					$sql .= " order by u.Name asc, u.lastname asc ";

					QueryDatabase($sql, $results);
					while ($row = mssql_fetch_assoc($results)) {
						if ($row['Status'] == 'In'){
							$statColor = 'darkgreen';
						} else {
							$statColor = 'red';
						}


						$ret .= " <tr>";
						$ret .= " 	<td><font class='" . $statColor . "'>" . $row['FullName'] . "</font></td>";
						$ret .= " 	<td><font class='" . $statColor . "'>" . $row['Status'] . "</font></td>";
						$ret .= " 	<td><font class='" . $statColor . "'>" . $row['time'] . "</font></td>";
						$ret .= " 	<td><font class='" . $statColor . "'>" . $row['Building'] . "</font></td>";
						$ret .= " 	<td><font class='" . $statColor . "'>" . $row['door_name'] . "</font></td>";
						$ret .= " </tr>";

					}
	
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
/*
function refreshRecords($NUM_RECS,$mechanic="--ALL--",$team="--ALL--",$machID="--ALL--",$maintCode="--ALL--",$maintResCode="--ALL--"){
		global $DB_TEST_FLAG;

		$sql  = "SELECT top " . $NUM_RECS;
		$sql .= " CONVERT(varchar(8), mi.DATE_INCID, 112) as DATE_INCID3, ";
		$sql .= " mc.DESCR, ";
		$sql .= " mi.* ";
		$sql .= " FROM nsa.MAINT_INCIDENTS" . $DB_TEST_FLAG . " mi ";
		$sql .= " left join nsa.MAINT_CODES mc ";
		$sql .= " on mi.CODE_MAINT = mc.CODE_MAINT ";
		$sql .= " WHERE mi.FLAG_DEL='' ";
		if ($mechanic <> "--ALL--") {
			$sql .= " and ltrim(ID_BADGE_MECH) = '".$mechanic."' ";
		}
		if ($team <> "--ALL--") {
			$sql .= " and ltrim(ID_BADGE_TEAM) = '".$team."' ";
		}
		if ($machID <> "--ALL--") {
			$sql .= " and ltrim(ID_MACH) = '".$machID."' ";
		}
		if ($maintCode <> "--ALL--") {
			$sql .= " and ltrim(mi.CODE_MAINT) = '".$maintCode."' ";
		}
		if ($maintResCode <> "--ALL--") {
			$sql .= " and ltrim(mi.CODE_MAINT_RES) = '".$maintResCode."' ";
		}
		$sql .= " ORDER BY rowid desc ";
		QueryDatabase($sql, $results);

		$prevrowId = '';
		$b_flip = true;

		$ret1 = " <table class='sample'>\n";
		$ret1 .= " 	<tr>\n";
		$ret1 .= " 		<th class='sample'>Date Work</th>\n";
		$ret1 .= " 		<th class='sample'>Mechanic</th>\n";
		$ret1 .= " 		<th class='sample'>Team</th>\n";
		$ret1 .= " 		<th class='sample'>Mach ID</th>\n";
		$ret1 .= " 		<th class='sample'>Maint Code</th>\n";
		$ret1 .= "		<th class='sample'>Maint Res Code</th>\n";
		$ret1 .= " 		<th class='sample'>Mins to Fix</th>\n";
		$ret1 .= " 		<th class='sample'></th>\n";
		$ret1 .= " 	</tr>\n";

		while ($row = mssql_fetch_assoc($results)) {
			if ($prevrowId != $row['rowid']) {
				$b_flip = !$b_flip;
			}
			if ($b_flip) {
				$trClass = 'd1';
			} else {
				$trClass = 'd0';
			}

			$curr = $row['DATE_INCID3'] . " " . str_pad($row['TIME_INCID'],6,"0",STR_PAD_LEFT);
			$currts = strtotime($curr);

			$ret1 .= " 	<tr class='" . $trClass . "'>\n";
			$ret1 .= " 		<td class='" . $trClass . "'>" . date('m/d/Y',$currts) . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='ID_BADGE_MECH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_BADGE_MECH'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='ID_BADGE_TEAM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_BADGE_TEAM'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='ID_MACH__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['ID_MACH'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='CODE_MAINT__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" title='" . $row['DESCR'] . "'>" . $row['CODE_MAINT'] . "</td>\n";
			$ret1 .= "		<td class='" . $trClass . "' id='CODE_MAINT_RES__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" title='" . $row['DESCR'] . "'>" . $row['CODE_MAINT_RES'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='MINS_DOWN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" >" . $row['MINS_DOWN'] . "</td>\n";
			$ret1 .= " 		<td class='" . $trClass . "' id='delete_" . $row['rowid']."' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
			$ret1 .= " 	</tr>\n";
			if ($row['COMMENT'] <> '') {
				$ret1 .= " 	<tr class='" . $trClass . "'>\n";
				$ret1 .= " 		<td class='" . $trClass . "'></td>\n";
				$ret1 .= " 		<th class='" . $trClass . "'>Comments</th>\n";
				$ret1 .= " 		<td class='" . $trClass . "' id='COMMENT__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" colspan=6>" . $row['COMMENT'] . "</td>\n";
				$ret1 .= " 	</tr>\n";
			}
		}

		$ret1 .= " </table>\n";
		$ret1 .= " </br>\n";

		return $ret1;

}//end refreshRecords
*/
?>
