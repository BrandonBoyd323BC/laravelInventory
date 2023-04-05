<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

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
			/// FORM SUBMITTED - INSERT INTO / UPDATE SQL
			///////////////////////////
			if (isset($_POST["sendAddValue"]) && isset($_POST["marker_id"])){
				$MARKER_ID = $_POST["marker_id"];
				$NUM_RECS = $_POST["num_recs"];

				$fulldate = date('Y-m-d H:i:s');
				$date = date('Y-m-d');
				$time = date('His');

				//SEARCH FOR EXISTING LOT TRACKING RECORD FOR MARKER_ID
				$sql1  = "SELECT * FROM nsa.MU_LOT_TRACKING" . $DB_TEST_FLAG . " ";
				$sql1 .= " WHERE MU_MARKER_rowid = ".$MARKER_ID." ";
				QueryDatabase($sql1, $results1);
				if (mssql_num_rows($results1) > 0) { // IF LOT TRACKING FOUND
					//SEARCH FOR EXISTING RECORD FOR MARKER_ID
					$sql  = "SELECT * FROM nsa.MU_MARKERS_CUT" . $DB_TEST_FLAG . " ";
					$sql .= " WHERE MU_MARKER_rowid = ".$MARKER_ID." ";
					QueryDatabase($sql, $results);
					if (mssql_num_rows($results) > 0) {
						while ($row = mssql_fetch_assoc($results)) {
							//UPDATE EXISTING RECORD
							$sql0  = "UPDATE nsa.MU_MARKERS_CUT" . $DB_TEST_FLAG . " SET ";
							$sql0 .= " ID_USER = '" . $UserRow['ID_USER'] . "', ";
							$sql0 .= " DATETIME = '" . $fulldate . "', ";
							$sql0 .= " DATE = '" . $date . "', ";
							$sql0 .= " TIME = '" . $time . "' ";
							$sql0 .= " WHERE rowid =  " . $row['rowid'] . " ";
							QueryDatabase($sql0, $results0);
						}
					} else {
						//INSERT MARKER CUT RECORD
						$sql0  = "INSERT INTO nsa.MU_MARKERS_CUT" . $DB_TEST_FLAG . " (";
						$sql0 .= " MU_MARKER_rowid, ";
						$sql0 .= " ID_USER, ";
						$sql0 .= " DATETIME, ";
						$sql0 .= " DATE, ";
						$sql0 .= " TIME ";
						$sql0 .= " ) values ( ";
						$sql0 .= " '" . $MARKER_ID . "', ";
						$sql0 .= " '" . $UserRow['ID_USER'] . "', ";
						$sql0 .= " '" . $fulldate . "', ";
						$sql0 .= " '" . $date . "', ";
						$sql0 .= " '" . $time . "' ";
						$sql0 .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
						QueryDatabase($sql0, $results0);
						$row0 = mssql_fetch_assoc($results0);
					}
					$ret .= refreshMarkerNumRecs($NUM_RECS);

				} else { // LOT TRACKING NOT FOUND
					error_log("Lot Tracking NOT Found for markerID: " . $MARKER_ID);
					$ret = " <h1>Lot Tracking NOT Found for Marker ID: ".$MARKER_ID.".</h1>\n";


					$to = 'group-LotTrackingMissing@thinknsa.com';
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
;
					$subject = "Lot Tracking Missing for MarkerID ".$MARKER_ID;
					$body = "The Markers Cut log program triggered an error for Marker ID " . $MARKER_ID . " because no Lot Number has been recorded.";

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();
					mail($to, $subject, $body, $headers);
					error_log("Email Sent to: " . $to);
				}
			}

			///////////////////////////
			/// NUM_RECS CHANGED
			///////////////////////////
			if (isset($_POST["numRecsChange"]) && isset($_POST["num_recs"]) && isset($_POST["search_marker_id"])) {
				$NUM_RECS = $_POST["num_recs"];
				$SEARCH_MARKER_ID = $_POST["search_marker_id"];
				$ret .= refreshMarkerNumRecs($NUM_RECS,$SEARCH_MARKER_ID);
			}


			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function refreshMarkerNumRecs($NUM_RECS,$SEARCH_MARKER_ID='') {
	global $DB_TEST_FLAG;

	$sql  = "SELECT distinct top " . $NUM_RECS;
	$sql .= " mc.MU_MARKER_rowid, ";
	$sql .= " mc.ID_USER, ";
	$sql .= " mc.DATETIME, ";
	$sql .= " mc.DATE, ";
	$sql .= " mc.TIME, ";
	$sql .= " mc.rowid ";
	$sql .= " FROM nsa.MU_MARKERS_CUT" . $DB_TEST_FLAG . " mc";
	if ($SEARCH_MARKER_ID <> 'ALL') {
		$sql .= " 	WHERE mc.MU_MARKER_rowid like '" . $SEARCH_MARKER_ID . "%' ";
	}
	$sql .= " ORDER BY mc.DATETIME desc ";
	QueryDatabase($sql, $results);

	$prevrowId = '';
	$b_flip = true;

	$ret1 = " <table class='sample'>\n";
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>Marker ID</th>\n";
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
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MU_MARKER_rowid'] . "</td>\n";
		$ret1 .= " 	</tr>\n";
	}
	$ret1 .= " </table>\n";
	return $ret1;
}

?>
