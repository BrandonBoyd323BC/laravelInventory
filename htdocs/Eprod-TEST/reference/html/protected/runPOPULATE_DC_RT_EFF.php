<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	error_log("#############################################");
	$StartTS = date('Y-m-d g:i:s a');
	error_log("### runPOPULATE_DC_RT_EFF started at " . $StartTS);
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runPOPULATE_DC_RT_EFF cannot connect to " . $db);
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runPOPULATE_DC_RT_EFF cannot select " . $dbName);
		} else {
			$today = date('Y-m-d');
 			$tomorrow = date("Y-m-d", strtotime("+1 day", strtotime($today)));
 			$yesterday = date("Y-m-d", strtotime("-1 day", strtotime($today)));
			$ZeroHour = '013000';
			$time = date("His",time());
			$RealTime = "T";

			if ($DEBUG > 1)	{
				error_log("today: ".$today);
				error_log("tomorrow: ".$tomorrow);
				error_log("yesterday: ".$yesterday);
				error_log("Time: ".$time);
			}
			
			if ($time > $ZeroHour) {
				$DateFrom = $today;
				$DateTo = $tomorrow;
			} else {
				$DateFrom = $yesterday;
				$DateTo = $today;
			}

			error_log("### runPOPULATE_DC_RT_EFF getting Team List");
			//lookup all teams
			$sql  = "SELECT FLAG_ATTEND, ID_BADGE, rowid ";
			$sql .= " FROM nsa.DCEMMS_EMP ";
			$sql .= " WHERE TYPE_BADGE = 'X' ";
			$sql .= " AND CODE_ACTV = 0 ";
			$sql .= " ORDER BY ID_BADGE asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				//lookup team in stored table
				$sql1  = "SELECT * ";
				$sql1 .= " FROM nsa.DC_RT_EFF ";
				$sql1 .= " WHERE ID_BADGE = '".$row["ID_BADGE"]."' ";
				QueryDatabase($sql1, $results1);

				if (mssql_num_rows($results1) > 0) {
					//update
					if ($row["FLAG_ATTEND"] == "1") {
						//ATTENDED IN, DO UPDATE
						$ovral_eff = GetEffScore($DateFrom, $DateTo, $ZeroHour, trim($row["ID_BADGE"]), $RealTime);
						error_log("### Updating " . $row["ID_BADGE"] . ": " . $ovral_eff);
						$sql1  = "UPDATE ";
						$sql1 .= " nsa.DC_RT_EFF ";
						$sql1 .= " set EFF_PCT = '" . $ovral_eff . "', ";
						$sql1 .= " DATE_CHG = getDate() ";
						$sql1 .= " WHERE ID_BADGE = '".$row["ID_BADGE"]."' ";
						QueryDatabase($sql1, $results1);

					} else {
						//NOT ATTENDED IN, NO UPDATE
						error_log("### NOT attended in " . $row["ID_BADGE"]);
					}
				} else {
					//insert
					if ($row["FLAG_ATTEND"] == "1") {
						//ATTENDED IN, DO UPDATE
						$ovral_eff = GetEffScore($DateFrom, $DateTo, trim($row["ID_BADGE"]), $RealTime);
						error_log("### INSERTING " . $row["ID_BADGE"] . ": " . $ovral_eff);
						$sql1  = " INSERT into  nsa.DC_RT_EFF (";
						$sql1 .= " ID_BADGE, ";
						$sql1 .= " EFF_PCT, ";
						$sql1 .= " DATE_CHG ";
						$sql1 .= " ) values ( ";
						$sql1 .= " '" . $row["ID_BADGE"] . "', ";
						$sql1 .= " '" . $ovral_eff . "', ";
						$sql1 .= " getDate() ";
						$sql1 .= " ) ";
						QueryDatabase($sql1, $results1);

					} else {
						//NOT ATTENDED IN, NO INSERT
						error_log("### NOT attended in " . $row["ID_BADGE"]);
					}					
				}
			}	

		}
	}
	$retval = DisconnectFromDatabaseServer($db);
	if ($retval == 0) {
		error_log("runPOPULATE_DC_RT_EFF cannot disconnect from " . $db);
	}
	$EndTS = date('Y-m-d g:i:s a');
	error_log("### runPOPULATE_DC_RT_EFF finished at " . $EndTS);
	error_log("#############################################");


?>
