<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);



			///////////////////////////
			/// FORM SUBMITTED - INSERT INTO SQL
			///////////////////////////
			if (isset($_POST["sendAddValue"]) && isset($_POST["so1"]) && isset($_POST["sufx_so1"]) && isset($_POST["num_recs"])){
				$SO1 = strtoupper($_POST["so1"]);
				$SUFX_SO1 = $_POST["sufx_so1"];
				$NUM_RECS = $_POST["num_recs"];

				$fulldate = date('Y-m-d H:i:s');
				$date = date('Y-m-d');
				$time = date('His');


				//SEARCH FOR EXISTING RECORD FOR MARKER_ID
				$sql  = "SELECT * FROM nsa.SHPORD_APPROVE ";
				$sql .= " WHERE ID_SO = ".$SO1." ";
				$sql .= " AND SUFX_SO = ".$SUFX_SO1." ";
				QueryDatabase($sql, $results);
				if (mssql_num_rows($results) > 0) {
					while ($row = mssql_fetch_assoc($results)) {
						//UPDATE EXISTING RECORD
						$sql0  = "UPDATE nsa.SHPORD_APPROVE SET ";
						$sql0 .= " DATETIME_APPROVED = '" . $fulldate . "', ";
						$sql0 .= " DATE_APPROVED = '" . $date . "', ";
						$sql0 .= " TIME_APPROVED = '" . $time . "' ";
						$sql0 .= " WHERE rowid =  " . $row['rowid'] . " ";
						QueryDatabase($sql0, $results0);
					}
				} else {
					//INSERT MARKER RECORD
					$sql0  = "INSERT INTO nsa.SHPORD_APPROVE (";
					$sql0 .= " ID_SO, ";
					$sql0 .= " SUFX_SO, ";
					$sql0 .= " DATETIME_APPROVED, ";
					$sql0 .= " DATE_APPROVED, ";
					$sql0 .= " TIME_APPROVED ";
					$sql0 .= " ) values ( ";
					$sql0 .= " '" . $SO1 . "', ";
					$sql0 .= " '" . $SUFX_SO1 . "', ";
					$sql0 .= " '" . $fulldate . "', ";
					$sql0 .= " '" . $date . "', ";
					$sql0 .= " '" . $time . "' ";
					$sql0 .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
					QueryDatabase($sql0, $results0);
					$row0 = mssql_fetch_assoc($results0);
				}

				$ret .= refreshMarkerNumRecs($NUM_RECS);

			}



			///////////////////////////
			/// NUM_RECS CHANGED
			///////////////////////////
			if (isset($_POST["numRecsChange"]) && isset($_POST["num_recs"]) && isset($_POST["search_so"])) {
				$NUM_RECS = $_POST["num_recs"];
				$SEARCH_SO = $_POST["search_so"];
				$ret .= refreshMarkerNumRecs($NUM_RECS,$SEARCH_SO);
			}


			///////////////////////////
			/// DELETE RECORDS
			///////////////////////////
			if (isset($_POST["deleteRecord"]) && isset($_POST["rowid"])) {
				$ROWID = $_POST["rowid"];

				if (is_numeric($ROWID)) {
					$sqlDel = "DELETE from nsa.SHPORD_APPROVE where rowid = " . $ROWID;
					error_log("DEL_QUERY: ".$sqlDel);
					QueryDatabase($sqlDel, $resultsDel);
					$ret .= "DELETED";

				} else {
					error_log("NON NUMERIC ROWID -- CANNOT DELETE");
				}
			}



			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function refreshMarkerNumRecs($NUM_RECS,$SEARCH_SO='') {
	$sql  = "select distinct top " . $NUM_RECS;
	$sql .= " sa.ID_SO, ";
	$sql .= " sa.SUFX_SO, ";
	$sql .= " sa.DATETIME_APPROVED, ";
	$sql .= " sa.DATE_APPROVED, ";
	$sql .= " sa.TIME_APPROVED, ";
	$sql .= " sa.rowid ";
	$sql .= " from nsa.SHPORD_APPROVE sa ";
	if ($SEARCH_SO <> 'ALL') {
		$sql .= " 	WHERE sa.ID_SO like '" . $SEARCH_SO . "%' ";
	}

	$sql .= " order by sa.rowid desc ";
	QueryDatabase($sql, $results);

	$prevrowId = '';
	$b_flip = true;

	$ret1 = " <table class='sample'>\n";
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>Shop Order</th>\n";
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

		$ret1 .= " 	<tr class='" . $trClass . "'>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['DATETIME_APPROVED'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['ID_SO'] . "-".$row['SUFX_SO']."</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='delete_". $row['rowid']."' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
		$ret1 .= " 	</tr>\n";
	}
	$ret1 .= " </table>\n";
	return $ret1;
}

?>
