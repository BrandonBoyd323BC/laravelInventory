<?php

	$DEBUG = 1;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			$ret = "";
			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				error_log("action:" . $action);	
				switch ($action) {
					case "selModeChange":
						if (isset($_POST["selMode"])) {
							$mode = $_POST["selMode"];
							error_log("mode: " . $mode);
							if ($mode == "shopOrder") {
								$ret .= " <table>";
									$ret .= " 	<tr>";
									$ret .= " 		<th colspan=2>Shop Order Entry: </th>";
									$ret .= " 	</tr>";
									$ret .= "	<tr id='tr_id_ord'>";
									$ret .= " 		<td>Shop Order: </td>";
									$ret .= " 		<td>";
									$ret .= "			<input id='so' type=text onkeyup=\"nextOnDash('so','sufx')\" maxlength=9 size=10 autofocus> -\n";
									$ret .= "			<input id='sufx' type=text onkeyup=\"sufxEntered()\" maxlength=3 size=4>\n";
									$ret .= "		</td>";
									$ret .= " 	</tr>";
									$ret .= " 	<tr>\n";
									$ret .= " 		<td></td>\n";
									$ret .= " 		<td><INPUT id='submit' type='button' value='Submit' onClick=\"checkSO()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
									$ret .= " 	</tr>\n";								
									$ret .= " </table>";
									$ret .= " <table id='table_ret_form'>";
									$ret .= " </table>";
							}//end if
						}//end if

							if ($mode == "itemNumber") {
									$ret .= " <table>";
									$ret .= " 	<tr>";
									$ret .= " 		<th colspan=2>Item Number Entry: </th>";
									$ret .= " 	</tr>";
									$ret .= "	<tr id='tr_so1'>";
									$ret .= " 		<td>Item Number: </td>";
									$ret .= " 		<td><div id='div_id_item'>";
									$ret .= "			<input id='id_item' type=text onkeyup=\"idItemChange()\" size=30 autofocus>";
									$ret .= "		</div></td>";
									$ret .= " 	</tr>";			
									$ret .= " 	<tr>\n";
									$ret .= " 		<td></td>\n";
									$ret .= " 		<td><INPUT id='submit' type='button' value='Submit' onClick=\"checkItem()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
									$ret .= " 	</tr>\n";
									$ret .= " </table>";
									$ret .= " <table id='table_ret_form'>";
									$ret .= " </table>";
							}//end if

							if ($mode == "order") {
									$ret .= " <table>";
									$ret .= " 	<tr>";
									$ret .= " 		<th colspan=2>Order Number Entry: </th>";
									$ret .= " 	</tr>";
									$ret .= "	<tr id='tr_so1'>";
									$ret .= " 		<td>Order Number: </td>";
									$ret .= " 		<td><div id='div_id_order'>";
									$ret .= "			<input id='id_order' type=text size=6 autofocus>";
									$ret .= "		</div></td>";
									$ret .= " 	</tr>";			
									$ret .= " 	<tr>\n";
									$ret .= " 		<td></td>\n";
									$ret .= " 		<td><INPUT id='submit' type='button' value='Submit' onClick=\"checkOrder()\" >  <INPUT id='reset' type='button' value='Clear' onClick=\"clearForm()\" ></td>\n";
									$ret .= " 	</tr>\n";
									$ret .= " </table>";
									$ret .= " <table id='table_ret_form'>";
									$ret .= " </table>";
							}//end if
					break;

					case "checkSO":
						if (isset($_POST["so"]) && isset($_POST["sufx"])) {
							$SO	= stripNonANChars(trim($_POST["so"]));
							$SUFX = stripNonANChars(trim($_POST["sufx"]));


							$sql = " select sh.ID_ITEM_PAR, ";
							$sql .= " CASE ";
							$sql .= " WHEN sh.ID_ITEM_PAR in (select ID_ITEM from nsa.ITMMAS_DESCR where SEQ_DESCR = 600 and DESCR_ADDL like '%prop 65%') THEN 'NEEDS PROP 65 STICKER' ";
							$sql .= " WHEN sh.ID_ITEM_PAR not in (select ID_ITEM from nsa.ITMMAS_DESCR where SEQ_DESCR = 600 and DESCR_ADDL like '%prop 65%') THEN 'NO PROP 65 STICKER' ";
							$sql .= " ELSE 'UNKNOWN' ";
							$sql .= " END as YES_NO ";
							$sql .= " from nsa.SHPORD_HDR sh ";
							$sql .= ' WHERE ltrim(sh.ID_SO) = "' . $SO . '" and sh.SUFX_SO = "'. $SUFX .'" ';
							error_log("sql: ".$sql);
							QueryDatabase($sql, $results);

							if (mssql_num_rows($results) > 0) {
								$ret .= "</br>\n";
								$ret .= "	<table class='sample'>\n";
								$ret .= "		<th>Item</th>\n";
								$ret .= "		<th>Prop 65 Sticker?</th>\n";
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= " 	<tr>\n";
										$ret .= "	<td>" .$row['ID_ITEM_PAR']. "</td>\n";
										$ret .= "	<td>" .$row['YES_NO']. "</td>\n";
									$ret .= " 	</tr>\n";
								}//end while

								$ret .= "</table>\n";

							}//end if

						}//end if

					break;

					case "checkItem":
						if (isset($_POST["item"]) ) {
							$ITEM = trim($_POST["item"]);

							$sql = " select ID_ITEM, ";
							$sql .= " CASE ";
							$sql .= " WHEN ID_ITEM in (select ID_ITEM from nsa.ITMMAS_DESCR where SEQ_DESCR = 600 and DESCR_ADDL like '%prop 65%') THEN 'NEEDS PROP 65 STICKER' ";
							$sql .= " WHEN ID_ITEM not in (select ID_ITEM from nsa.ITMMAS_DESCR where SEQ_DESCR = 600 and DESCR_ADDL like '%prop 65%') THEN 'NO PROP 65 STICKER' ";
							$sql .= " ELSE 'UNKNOWN' ";
							$sql .= " END as YES_NO ";
							$sql .= " from nsa.ITMMAS_BASE ";
							$sql .= ' where ID_ITEM = "'. $ITEM .'" ';
							error_log("sql: ".$sql);
							QueryDatabase($sql, $results);

							if (mssql_num_rows($results) > 0) {
								$ret .= "</br>\n";
								$ret .= "	<table class='sample'>\n";
								$ret .= "		<th>Item</th>\n";
								$ret .= "		<th>Prop 65 Sticker?</th>\n";
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= " 	<tr>\n";
										$ret .= "	<td>" .$row['ID_ITEM']. "</td>\n";
										$ret .= "	<td>" .$row['YES_NO']. "</td>\n";
									$ret .= " 	</tr>\n";
								}//end while

								$ret .= "</table>\n";

							}//end if

						}//end if
					break;

					case "checkOrder":
						if (isset($_POST["order"]) ){
							$ORDER = trim($_POST["order"]);

							$sql = " select ol.SEQ_LINE_ORD, ol.ID_ITEM, ";
							$sql .= " CASE ";
							$sql .= " WHEN ol.ID_ITEM in (select ID_ITEM from nsa.ITMMAS_DESCR where SEQ_DESCR = 600 and DESCR_ADDL like '%prop 65%') THEN 'NEEDS PROP 65 STICKER' ";
							$sql .= " WHEN ol.ID_ITEM not in (select ID_ITEM from nsa.ITMMAS_DESCR where SEQ_DESCR = 600 and DESCR_ADDL like '%prop 65%') THEN 'NO STICKER' ";
							$sql .= " ELSE 'UNKNOWN' ";
							$sql .= " END as YES_NO ";
							$sql .= " from nsa.CP_ORDLIN ol ";
							$sql .= " RIGHT JOIN ";
							$sql .= " (select ID_ITEM from nsa.ITMMAS_DESCR where SEQ_DESCR = 600 and DESCR_ADDL like '%prop 65%') p65 ";
							$sql .= " on ol.ID_ITEM = p65.ID_ITEM ";
							$sql .= ' where ol.ID_ORD = "'. $ORDER .'" ';
							$sql .= " order by ol.SEQ_LINE_ORD ";
							error_log("sql: ".$sql);
							QueryDatabase($sql, $results);

							if (mssql_num_rows($results) > 0) {
								$ret .= "</br>\n";
								$ret .= "	<table class='sample'>\n";
								$ret .= "		<th>Line</th>\n";
								$ret .= "		<th>Item</th>\n";
								$ret .= "		<th>Prop 65 Sticker?</th>\n";
								while ($row = mssql_fetch_assoc($results)) {
									$ret .= " 	<tr>\n";
										$ret .= "	<td>" .$row['SEQ_LINE_ORD']. "</td>\n";
										$ret .= "	<td>" .$row['ID_ITEM']. "</td>\n";
										$ret .= "	<td>" .$row['YES_NO']. "</td>\n";
									$ret .= " 	</tr>\n";
								}//end while

								$ret .= "</table>\n";

							}//end if

							if (mssql_num_rows($results) == 0) {
									$ret .= "</br>\n";
									$ret .= ' <h3>NO PROP 65 ITEMS ON ORDER '.$ORDER.'</h3>';
							}//end if
						}//end if	
					break;


				}//end switch
			}//end if
			echo json_encode(array("returnValue"=> $ret));
		}//end else
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}//end if
	}//end else
?>
