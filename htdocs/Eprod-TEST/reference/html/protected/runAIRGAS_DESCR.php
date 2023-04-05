<?php
	$DEBUG = 0;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runAIRGAS_DESCR cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runAIRGAS_DESCR cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runAIRGAS_DESCR started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');

			$sql  = " SELECT ";
			$sql .= " * FROM nsa.AIRGAS_DESCR2 ";
			$sql .= " WHERE EXT_DESCR = '' ";
			//$sql .= " WHERE ID_ITEM = 'A01NJ24I29I45' ";
			//$sql .= " OR CUST_STD = 'Custom' ";
			//$sql .= " OR CUST_STD = '' )";
			//$sql .= " and ID_ITEM not in ('K00TA30I20F','K01WG14F32I','K01WG15F10I','W40NA11I14F01','K00TA30I20F')";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				$descr = "";

				$sql2  = "SELECT ";
				$sql2 .= "	b.DESCR_1, ";
				$sql2 .= "	b.DESCR_2 ";
				$sql2 .= " FROM nsa.ITMMAS_BASE b ";
				$sql2 .= " WHERE b.ID_ITEM = '" . $row['ID_ITEM'] . "' ";
				QueryDatabase($sql2, $results2);
				while ($row2 = mssql_fetch_assoc($results2)) {
					$descr .= $row2['DESCR_1'] . " " . $row2['DESCR_2'];
				}

				$sql2  = "SELECT ";
				$sql2 .= "	d.DESCR_ADDL, ";
				$sql2 .= "	d.SEQ_DESCR ";
				$sql2 .= " FROM nsa.ITMMAS_DESCR d ";
				$sql2 .= " WHERE d.ID_ITEM = '" . $row['ID_ITEM'] . "' ";
				$sql2 .= " ORDER BY SEQ_DESCR asc ";
				QueryDatabase($sql2, $results2);
				while ($row2 = mssql_fetch_assoc($results2)) {
					$descr .= " " . $row2['DESCR_ADDL'];
				}

				error_log("### " . $row['ID_ITEM'] . ": " . $descr );
				$sql2 = "UPDATE nsa.AIRGAS_DESCR2 set EXT_DESCR = '". $descr ."' where ID_ITEM = '". $row['ID_ITEM'] ."'";
				//QueryDatabase($sql2, $results2);
			}
			error_log("### runAIRGAS_DESCR finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runAIRGAS_DESCR cannot disconnect from database");
		}
	}
?>