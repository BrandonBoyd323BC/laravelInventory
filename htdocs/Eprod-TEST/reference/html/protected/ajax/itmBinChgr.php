<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");

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

			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				error_log("action:" . $action);		

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING ON";
				QueryDatabase($sql, $results);
				$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
				QueryDatabase($sql, $results);

				switch ($action) {

					case "getItemBinInfo":
						if (isset($_POST["item"])) {
							$item	= trim($_POST["item"]);

							/////Lookup BIN on Location Record
							$sql =  "SELECT ";
							$sql .= " l.ID_ITEM, ";
							$sql .= " l.BIN_PRIM, ";
							$sql .= " l.rowid as LOC_ROWID ";
							//$sql .= " drf.FC_LOCATION ";
							$sql .= " FROM nsa.ITMMAS_LOC l ";
							//$sql .= " LEFT JOIN nsa.ItemVsBin_ForWH drf ";
							//$sql .= " on l.ID_ITEM = drf.ITEM ";
							$sql .= " WHERE ltrim(l.ID_ITEM) = '" . $item . "' ";
							$sql .= " and l.ID_LOC = '10' ";
							QueryDatabase($sql, $results);

							$ret .= "	<h3>Item Master Location</h3>";
							$ret .= "	<table class='sample'>";
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= "	<tr>";
								$ret .= "		<th>Item</th>";
								$ret .= "		<th>Current BIN</th>";
								$ret .= "		<th>New BIN</th>";
								$ret .= "	</tr>";
								$ret .= "	<tr>";
								$ret .= "		<td>".$row['ID_ITEM']."</td>";
								$ret .= "		<td>".$row['BIN_PRIM']."</td>";
								$ret .= "		<input type='hidden' id='locRowid' value='".$row['LOC_ROWID']."'>";
								$ret .= "		<input type='hidden' id='hdnItem' value='".$row['ID_ITEM']."'>";
								//$ret .= "		<td><input type='textbox' id='tbNewBin' value='".$row['FC_LOCATION']."'></td>";
								$ret .= "		<td><input type='textbox' id='tbNewBin'></td>";
								$ret .= "		<td><div id='div_updateLocButton'><input type='button' onClick=\"saveNewBinLoc()\"  value='Update'></input></div?</td>";
								$ret .= "	</tr>";
							}
							$ret .= "	</table>";
						}

					break;

					case "getOrderLinBinInfo":
						if (isset($_POST["item"])) {
							$item	= trim($_POST["item"]);

							/////Lookup Open Orders
							$sql =  "select ol.ID_ORD, ";
							$sql .= " ol.SEQ_LINE_ORD, ";
							$sql .= " oh.NAME_CUST, ";
							$sql .= " ol.BIN_PRIM, ";
							$sql .= " il.BIN_PRIM as IL_BIN_PRIM, ";
							$sql .= " ol.rowid as ORDLIN_ROWID ";
							$sql .= " FROM nsa.CP_ORDLIN ol ";
							$sql .= " LEFT JOIN nsa.CP_ORDHDR oh ";
							$sql .= " on ol.ID_ORD = oh.ID_ORD ";
							$sql .= " LEFT JOIN nsa.ITMMAS_LOC il ";
							$sql .= " on ol.ID_ITEM = il.ID_ITEM ";
							$sql .= " and il.ID_LOC = '10' ";
							$sql .= " where ltrim(ol.ID_ITEM) = '" . $item . "' ";
							QueryDatabase($sql, $results);

							$ret .= "	</br>";
							$ret .= "	<h3>Open Orders</h3>";
							$ret .= "	<table class='sample'>";

							if (mssql_num_rows($results) > 0) {
								$ret1 = "";
								$listOfOrderLineRowids = "";
								while ($row = mssql_fetch_assoc($results)) {
									if ($listOfOrderLineRowids == "") {
										$listOfOrderLineRowids .= $row['ORDLIN_ROWID'];
									} else {
										$listOfOrderLineRowids .= ", " . $row['ORDLIN_ROWID'];
									}

									$ret1 .= "	<tr>";
									$ret1 .= "		<td>".$row['ID_ORD']."</td>";
									$ret1 .= "		<td>".$row['SEQ_LINE_ORD']."</td>";
									$ret1 .= "		<td>".$row['NAME_CUST']."</td>";
									$ret1 .= "		<td>".$row['BIN_PRIM']."</td>";
									$ret1 .= "		<td><input type='textbox' id='tbOrdLinNewBin_".$row['ORDLIN_ROWID']."' value='".$row['IL_BIN_PRIM']."'></td>";
									$ret1 .= "		<td><div id='div_updateOrdLinLocButton_".$row['ORDLIN_ROWID']."'><input type='button' onClick=\"saveNewOrdLinBinLoc('".$row['ORDLIN_ROWID']."')\"  value='Update'></input></div></td>";
									$ret1 .= "	</tr>";
								}
								error_log($listOfOrderLineRowids);
								$ret .= "		<input type='hidden' id='listOfOrderLineRowids' value='".$listOfOrderLineRowids."'>";
								$ret .= "		<tr>";
								$ret .= "			<th>Order</th>";
								$ret .= "			<th>Line #</th>";
								$ret .= "			<th>Customer</th>";
								$ret .= "			<th>Current BIN</th>";
								$ret .= "			<th>New BIN</th>";
								//$ret .= "			<th><div id='div_updateALLOrdLinLocButton'><input type='button' onClick=\"updateALLOrdLinLoc()\"  value='Update ALL'></input></div></th>";
								$ret .= "		</tr>";
								$ret .= $ret1;
							} else {
								$ret .= "	<tr>";
								$ret .= "		<th colspan=2>No Open Orders for this Item</th>";
								$ret .= "	</tr>";
							}
							$ret .= "	</table>";
						}

					break;

					case "saveNewBinLoc":
						if (isset($_POST["newBin"]) && isset($_POST["locRowid"])) {
							$newBin	= trim($_POST["newBin"]);
							$locRowid = trim($_POST["locRowid"]);

							$sql =  " UPDATE nsa.ITMMAS_LOC set BIN_PRIM = '".$newBin."' where rowid = '".$locRowid."' ";
							error_log("SQL: ".$sql);
							QueryDatabase($sql, $results);

							$ret .= "OK";
						}

					break;


					case "saveNewOrdLinBinLoc":
						if (isset($_POST["ordLinNewBin"]) && isset($_POST["ordLinRowid"])) {
							$ordLinNewBin	= trim($_POST["ordLinNewBin"]);
							$ordLinRowid = trim($_POST["ordLinRowid"]);

							$sql =  " UPDATE nsa.CP_ORDLIN set BIN_PRIM = '".$ordLinNewBin."' where rowid = '".$ordLinRowid."' ";
							error_log("SQL: ".$sql);
							QueryDatabase($sql, $results);

							$ret .= "OK";
						}

					break;

				}

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}


?>
