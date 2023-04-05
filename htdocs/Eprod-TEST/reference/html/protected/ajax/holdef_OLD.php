<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print( "		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["YR"]) && isset($_POST["action"]))  {
				$YR = $_POST["YR"];
				$action = $_POST["action"];

				$ret .= "		<h3>Holidays for Year: " . $YR . "</h3>\n";
				$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

				//$sql = "SET ANSI_NULLS ON";
				//QueryDatabase($sql, $results);
				//$sql = "SET ANSI_WARNINGS ON";
				//QueryDatabase($sql, $results);

				$ret .= " <table class='sample'>\n";
				$ret .= " 	<tr>\n";
				$ret .= " 		<th>\n";
				$ret .= "				<font>Date</font>\n";
				$ret .= " 		</th>\n";
				$ret .= " 		<th>\n";
				$ret .= "				<font>Description</font>\n";
				$ret .= " 		</th>\n";
				$ret .= " 		<th>\n";
				$ret .= "				<font>Added By</font>\n";
				$ret .= " 		</th>\n";
				$ret .= " 	</tr>\n";

				$sql =  "select ";
				$sql .= "	CONVERT(char(10),DATE_HOL,101) as DATE_HOL2, ";
				$sql .= "	CONVERT(varchar(8), DATE_HOL, 112) as DATE_HOL3, ";
				$sql .= "	* ";
				$sql .= " from ";
				$sql .= "  nsa.HOLIDAY_DEF ";
				$sql .= " where ";
				$sql .= "	DATEPART(yy, DATE_HOL) = " . $YR;
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$ret .= " 	<tr>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font>" . $row['DATE_HOL2'] . "</font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font>" . $row['DESCR'] . "</font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 		<td>\n";
					$ret .= "				<font>" . $row['ID_USER_ADD'] . "</font>\n";
					$ret .= " 		</td>\n";
					$ret .= " 	</tr>\n";
				}

				//$sql = "SET ANSI_NULLS OFF";
				//QueryDatabase($sql, $results);
				//$sql = "SET ANSI_WARNINGS OFF";
				//QueryDatabase($sql, $results);

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
