<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	$DEBUG = 1;

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
			if (isset($_POST["sendAddValue"]) && isset($_POST["so1"]) && isset($_POST["sufx_so1"]) && isset($_POST["num_recs"]) ){
				$SO1 = strtoupper($_POST["so1"]);
				$SUFX_SO1 = $_POST["sufx_so1"];
				$NUM_RECS = $_POST["num_recs"];

				$fulldate = date('Y-m-d H:i:s');
				error_log($fulldate);

				$date = date('Y-m-d');
				error_log($date);

				$time = date('His');
				error_log($time);				


				//INSERT MARKER RECORD
				$sql0  = "INSERT INTO nsa.OP_JOB_CARDS_PLANNED (";
				$sql0 .= " ID_SO, ";
				$sql0 .= " SUFX_SO, ";
				$sql0 .= " ID_USER, ";
				$sql0 .= " DATETIME, ";
				$sql0 .= " DATE, ";
				$sql0 .= " TIME ";
				$sql0 .= " ) values ( ";
				$sql0 .= " '" . $SO1 . "', ";
				$sql0 .= " '" . $SUFX_SO1 . "', ";
				$sql0 .= " '" . $UserRow['ID_USER'] . "', ";
				$sql0 .= " '" . $fulldate . "', ";
				$sql0 .= " '" . $date . "', ";
				$sql0 .= " '" . $time . "' ";
				$sql0 .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql0, $results0);
				$row0 = mssql_fetch_assoc($results0);

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

				$sqlDel = "update nsa.OP_JOB_CARDS_PLANNED set FLAG_DEL = 'Y', DATE_CHG = getdate(), ID_USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
				QueryDatabase($sqlDel, $resultsDel);

				$ret .= "DELETED";
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
	$sql .= " op.ID_SO, ";
	$sql .= " op.SUFX_SO, ";
	$sql .= " op.ID_USER, ";
	$sql .= " op.DATETIME, ";
	$sql .= " op.DATE, ";
	$sql .= " op.TIME, ";
	$sql .= " op.rowid ";
	$sql .= " from nsa.OP_JOB_CARDS_PLANNED op";
	$sql .= " where isnull(op.FLAG_DEL,'') <> 'Y' ";
	if ($SEARCH_SO <> 'ALL') {
		$sql .= " 	and op.ID_SO like '" . $SEARCH_SO . "%' ";
	}

	$sql .= " order by op.rowid desc ";
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
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['DATETIME'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['ID_SO'] . "-".$row['SUFX_SO']."</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "' id='delete_". $row['rowid']."' onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
		$ret1 .= " 	</tr>\n";
	}
	$ret1 .= " </table>\n";
	return $ret1;
}

?>
