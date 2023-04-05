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
			$ret = "";

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);

			if (isset($_POST["action"])) {
				$action = $_POST["action"];

				switch ($action) {

					case "saveReasonCode":
						if (isset($_POST["ilh_ROWID"]) && isset($_POST["newLC"]))  {
							$ilh_ROWID = $_POST["ilh_ROWID"];
							$newLC = $_POST["newLC"];

							$sql  = " SELECT * ";
							$sql .= " FROM nsa.LATE_ORDER_LOG ";
							$sql .= " WHERE CP_INVLIN_HIST_rowid = '".$ilh_ROWID."' ";
							QueryDatabase($sql, $results);
							if (mssql_num_rows($results) == 0) {
								//NO RECORD FOUND, INSERT NEW
								$sql1  = " INSERT INTO nsa.LATE_ORDER_LOG( ";
								$sql1 .= " CP_INVLIN_HIST_rowid, ";
								$sql1 .= " LATE_CODE, ";
								$sql1 .= " DATE_ADD, ";
								$sql1 .= " ID_USER_ADD ";
								$sql1 .= " ) values ( ";
								$sql1 .= " '".$ilh_ROWID."', ";
								$sql1 .= " '".$newLC."', ";
								$sql1 .= " GetDate(), ";
								$sql1 .= " '".$UserRow['ID_USER']."' ";
								$sql1 .= " ) ";
								QueryDatabase($sql1, $results1);
								$ret .= "OK";
							} else {
								//EXISTING RECORD FOUND, UPDATE
								$sql1  = " UPDATE nsa.LATE_ORDER_LOG SET ";
								$sql1 .= " LATE_CODE = '".$newLC."', ";
								$sql1 .= " DATE_CHG = GetDate(), ";
								$sql1 .= " ID_USER_CHG = '".$UserRow['ID_USER']."' ";
								$sql1 .= " WHERE CP_INVLIN_HIST_rowid = '".$ilh_ROWID."' ";
								QueryDatabase($sql1, $results1);
								$ret .= "OK";
							}
						}
					break;


					case "showRecords":
						if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["sel_ShowRec"]))  {
							$df = $_POST["df"];
							$dt = $_POST["dt"];
							$sel_ShowRec = $_POST["sel_ShowRec"];
							$retLC = "";

							$ret .= "		<h3>Late Orders between " . $df . " and " . $dt . "</h3>\n";
							$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";

							$sqlLC  = " SELECT LATE_CODE, LATE_DESCR ";
							$sqlLC .= " FROM nsa.LATE_ORDER_CODES";
							$sqlLC .= " ORDER BY LATE_CODE asc ";
							QueryDatabase($sqlLC, $resultsLC);
							while ($rowLC = mssql_fetch_assoc($resultsLC)){
								$arrRowLC[] = $rowLC;
							}

							$sql =  "SELECT ";
							$sql .= " ilh.ID_ORD, ";
							$sql .= " cs.NAME_CUST, ";
							$sql .= " ilh.SEQ_LINE_ORD, ";
							$sql .= " ilh.ID_ITEM, ";
							$sql .= " convert(varchar, ilh.DATE_PROM, 101) as DATE_PROM, ";
							$sql .= " convert(varchar, ilh.DATE_INVC, 101) as DATE_INVC, ";
							$sql .= " ilh.rowid as ilh_ROWID, ";
							$sql .= " lol.LATE_CODE ";
							$sql .= " FROM nsa.CP_INVLIN_HIST ilh ";
							$sql .= " LEFT JOIN nsa.CUSMAS_SOLDTO cs ";
							$sql .= " on ilh.ID_CUST_SOLDTO = cs.ID_CUST ";
							$sql .= " LEFT JOIN nsa.LATE_ORDER_LOG lol ";
							$sql .= " on ilh.rowid = lol.CP_INVLIN_HIST_rowid ";
							$sql .= " WHERE ilh.DATE_INVC between '".$df."' and '".$dt."' ";
							$sql .= " and ilh.DATE_INVC > ilh.DATE_PROM ";
							if ($sel_ShowRec == "UNRECORDED") {
								$sql .= " and (lol.LATE_CODE is null OR lol.LATE_CODE = '')";
							}
							if ($sel_ShowRec == "RECORDED") {
								$sql .= " and lol.LATE_CODE is NOT null ";
							}
							$sql .= " ORDER BY ilh.ID_ORD asc, ";
							$sql .= " ilh.SEQ_LINE_ORD asc ";
							QueryDatabase($sql, $results);

 							$ret .= " <h4># Rows Returned: " . mssql_num_rows($results) ."</h4>\n";
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Order #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Customer</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Line #</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Item ID</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Promise Date</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Ship Date</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 		<th>\n";
							$ret .= "				<font>Reason Code</font>\n";
							$ret .= " 		</th>\n";
							$ret .= " 	</tr>\n";

							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>".$row['ID_ORD']."</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>".$row['NAME_CUST']."</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>".$row['SEQ_LINE_ORD']."</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>".$row['ID_ITEM']."</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>".$row['DATE_PROM']."</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= "				<font>".$row['DATE_INVC']."</font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 		<td>\n";
								$ret .= " 			<select id='selReasonCode__".$row['ilh_ROWID']."' onChange=\"saveReasonCode('".$row['ilh_ROWID']."')\">\n";
								$ret .= "			<option value=''>--Select--</option>";
								foreach ($arrRowLC as $rowLCc ) {
									$SELECTED = '';
									if ($row['LATE_CODE'] == $rowLCc['LATE_CODE']) {
										$SELECTED = "SELECTED";
									}
									$ret .= "			<option value='" . $rowLCc['LATE_CODE'] . "' $SELECTED>" . $rowLCc['LATE_DESCR'] . "</option>\n";
								}					
								$ret .= " 			</select><font id='resp_".$row['ilh_ROWID']."'></font>\n";
								$ret .= " 		</td>\n";
								$ret .= " 	</tr>\n";
							}
						}
					break;
				} // END SWITCH
			
				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
