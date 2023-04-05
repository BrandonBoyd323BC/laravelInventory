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

			if (isset($_POST["id_loc"]) && isset($_POST["id_item"])) {
				$ret .= "</br>\n";
				$ID_LOC = stripillegalChars3(trim($_POST["id_loc"]));
				$ID_ITEM = stripillegalChars3(trim($_POST["id_item"]));

				///////////////////
				//// ITEM
				///////////////////
				$sql = " SELECT ib.ID_ITEM ";
				$sql .= " from nsa.ITMMAS_BASE ib";
				$sql .= " WHERE ib.ID_ITEM = '" . $ID_ITEM . "' ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$ret .= "<table class='sample'>\n";
					$ret .= "	<tr class='blueHeader'>\n";
					$ret .= "		<th>Item</th>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>" .$row['ID_ITEM']. "</td>\n";
					$ret .= "	</tr>\n";
					$ret .= "</table>\n";
					$ret .= "</br>\n";
				}


				///////////////////
				//// DOCUMENTS
				///////////////////
				$sql = " SELECT";
				$sql .= " ib.ID_ITEM, ";
				$sql .= " DESCR_1, DESCR_2, ";
				$sql .= " xd.NAME_DOC, ";
				$sql .= " xd.DESCR_DOC, ";
				$sql .= " xd.NAME_FILE ";
				$sql .= " FROM nsa.ITMMAS_BASE ib ";
				$sql .= " LEFT JOIN ( ";
				$sql .= "  select d.NAME_DOC, ";
				$sql .= "  d.DESCR_DOC, ";
				$sql .= "  d.NAME_FILE, ";
				$sql .= "  xi.ID_ITEM ";
				$sql .= "  FROM nsa.DOC_XREF_ITEM xi ";
				$sql .= "  LEFT JOIN nsa.DOC_XREF_DTL d ";
				$sql .= "  on xi.NAME_DOC = d.NAME_DOC ";
				$sql .= " ) xd ";
				$sql .= " on xd.ID_ITEM = ib.ID_ITEM ";
				$sql .= " WHERE ib.ID_ITEM = '" . $ID_ITEM . "' ";
				error_log($sql);
				QueryDatabase($sql, $results);

				$ret .= "<table class='sample'>\n";
				$ret .= " 	<tr class='blueHeader'>\n";
				$ret .= "		<th>Document Name</th>\n";
				$ret .= "		<th>Document Description</th>\n";
				$ret .= "		<th>Link</th>\n";
				$ret .= " 	</tr>\n";
				while ($row = mssql_fetch_assoc($results)) {
					$NF = str_replace('"','',$row['NAME_FILE']) ;
					$NF = str_replace("\\","/",$NF) ;
					$BN = basename($NF);
					//$NF = str_replace("//fs1/netshare/work instructions/", "/Work_Instructions/", $NF);

					$NF = str_ireplace("//FS1/NETSHARE/", "/netshare/", $NF);

					$NF = str_ireplace("/customer service/", "/Customer Service/", $NF);
					$NF = str_ireplace("/operations/", "/Operations/", $NF);
					$NF = str_ireplace("/pre-production/", "/Pre-Production/", $NF);
					$NF = str_ireplace("/ysizing charts/", "/ySizing Charts/", $NF);
					$NF = str_ireplace("/approved stock markers/", "/Approved Stock Markers/", $NF);

					error_log("NF: ".$NF);
					$NFL = str_replace(" ","\ ",$NF);
					$NFW = str_replace(" ","%20",$NF);
					error_log("NFL: ".$NFL);
					error_log("NFW: ".$NFW);

					$ret .= " 	<tr>\n";
					$ret .= "		<td>" .$row['NAME_DOC']. "</td>\n";	
					$ret .= "		<td>" .$row['DESCR_DOC']. "</td>\n";	
					$ret .= "		<td><a href='" . $NF . "' target='_blank'>" .$BN. "</td>\n";
					$ret .= " 	</tr>\n";	
				}
				$ret .= "</table>\n";
				$ret .= "</br>\n";


				///////////////////
				//// DESCRIPTION
				///////////////////
				$sql1 = "SELECT ";
				$sql1 .= "ib.ID_ITEM, ";
				$sql1 .= "DESCR_1, DESCR_2 ";
				$sql1 .= "FROM nsa.ITMMAS_BASE ib ";
				$sql1 .= "WHERE ib.ID_ITEM = '" . $ID_ITEM . "' ";
				QueryDatabase($sql1, $results1);

				while($row = mssql_fetch_assoc($results1)){
					$ret .= "<table class='sample'>\n";
					$ret .= "	<tr class='blueHeader'>\n";
					$ret .= "		<th>Description</th>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= "		<td>" .utf8_encode($row['DESCR_1']). "</td>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= "		<td>" .utf8_encode($row['DESCR_2']). "</td>\n";
					$ret .= " 	</tr>\n";
				}


				///////////////////
				//// DESCR ADDL
				///////////////////
				$sql = " SELECT";
				$sql .= " DESCR_ADDL";
				$sql .= " FROM nsa.ITMMAS_DESCR";
				$sql .= " WHERE ID_ITEM = '" . $ID_ITEM . "' ";
				$sql .= " ORDER BY SEQ_DESCR";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$ret .= " 	<tr>\n";
					$ret .= "		<td>" .utf8_encode($row['DESCR_ADDL']). "</td>\n";
					$ret .= " 	</tr>\n";
				}
				$ret .= "</table>\n";
				$ret .= "</br>\n";


				///////////////////
				///// QTY INFO
				///////////////////
				$sql = " SELECT";
				$sql .= " CASE WHEN rtrim(ltrim(il.ID_PLANNER)) = 'AS' THEN 'YES' ELSE 'NO' END as FLAG_ALT_STK,";
				$sql .= " il.BIN_PRIM,";
				$sql .= " il.FLAG_STK,";
				$sql .= " il.FLAG_SOURCE,";
				$sql .= " ib.FLAG_STAT_ITEM,";
				$sql .= " il.QTY_ONHD,";
				$sql .= " il.QTY_ALLOC,";
				$sql .= " il.QTY_ONORD";
				$sql .= " FROM nsa.ITMMAS_LOC il";
				$sql .= " LEFT JOIN nsa.ITMMAS_BASE ib ";
				$sql .= " on il.ID_ITEM = ib.ID_ITEM";
				$sql .= " WHERE il.ID_ITEM = '" . $ID_ITEM . "' ";
				error_log($sql);
				QueryDatabase($sql, $results);

				$ret .= "<table class='sample'>\n";
				$ret .= " 	<tr class='blueHeader'>\n";
				$ret .= "		<th>Altered Stock? /<br>Stocked Component</th>\n";
				$ret .= "		<th>Primary Bin</th>\n";
				$ret .= "		<th>Stock Flag</th>\n";
				$ret .= "		<th>Source Flag</th>\n";
				$ret .= "		<th>Item Status</th>\n";
				$ret .= "		<th>Qty On Hand</th>\n";
				$ret .= "		<th>Qty Alloc</th>\n";
				$ret .= "		<th>Qty On Order</th>\n";
				$ret .= " 	</tr>\n";
				while ($row = mssql_fetch_assoc($results)) {
					$ret .= " 	<tr>\n";
					$ret .= "		<td>" .$row['FLAG_ALT_STK']. "</td>\n";
					$ret .= "		<td>" .$row['BIN_PRIM']. "</td>\n";
					$ret .= "		<td>" .$row['FLAG_STK']. "</td>\n";
					$ret .= "		<td>" .$row['FLAG_SOURCE']. "</td>\n";
					$ret .= "		<td>" .$row['FLAG_STAT_ITEM']. "</td>\n";
					$ret .= "		<td>" .$row['QTY_ONHD']. "</td>\n";
					$ret .= "		<td>" .$row['QTY_ALLOC']. "</td>\n";
					$ret .= "		<td>" .$row['QTY_ONORD']. "</td>\n";
					$ret .= " 	</tr>\n";

					if ($row['FLAG_ALT_STK'] == 'YES') {
						$sql1  = " SELECT ";
						$sql1 .= " ps.ID_ITEM_COMP, ";
						$sql1 .= " ps.QTY_PER, ";
						$sql1 .= " il.BIN_PRIM, ";
						$sql1 .= " il.FLAG_STK, ";
						$sql1 .= " il.FLAG_SOURCE, ";
						$sql1 .= " ib.FLAG_STAT_ITEM, ";
						$sql1 .= " il.QTY_ONHD, ";
						$sql1 .= " il.QTY_ALLOC, ";
						$sql1 .= " il.QTY_ONORD ";
						$sql1 .= " FROM nsa.PRDSTR ps ";
						$sql1 .= " LEFT JOIN nsa.ITMMAS_LOC il ";
						$sql1 .= " on ps.ID_ITEM_COMP = il.ID_ITEM ";
						//$sql1 .= " and il.ID_LOC = 10 ";
						$sql1 .= " and il.ID_LOC = '".$ID_LOC."'";
						$sql1 .= " LEFT JOIN nsa.ITMMAS_BASE ib ";
						$sql1 .= " on il.ID_ITEM = ib.ID_ITEM ";
						$sql1 .= " WHERE ps.ID_ITEM_PAR = '".$ID_ITEM."' ";
						QueryDatabase($sql1, $results1);
						$ret .= " 	<tr>\n";
						$ret .= "		<td colspan=8> Components:</td>\n";
						$ret .= " 	</tr>\n";							
						while ($row1 = mssql_fetch_assoc($results1)) {
							$ret .= " 	<tr>\n";
							$ret .= "		<td>" .$row1['ID_ITEM_COMP']. "</td>\n";
							$ret .= "		<td>" .$row1['BIN_PRIM']. "</td>\n";
							$ret .= "		<td>" .$row1['FLAG_STK']. "</td>\n";
							$ret .= "		<td>" .$row1['FLAG_SOURCE']. "</td>\n";
							$ret .= "		<td>" .$row1['FLAG_STAT_ITEM']. "</td>\n";
							$ret .= "		<td>" .$row1['QTY_ONHD']. "</td>\n";
							$ret .= "		<td>" .$row1['QTY_ALLOC']. "</td>\n";
							$ret .= "		<td>" .$row1['QTY_ONORD']. "</td>\n";
							$ret .= " 	</tr>\n";
						}
					}

				}
				$ret .= "</table>\n";
				$ret .= "</br>\n";



				///////////////////
				///// BIN INFO
				///////////////////
				$sql = " SELECT";
				$sql .= " CASE bo.KEY_BIN_1";
				$sql .= "  WHEN 'FLOOR' THEN '0'";
				$sql .= "  ELSE '1'";
				$sql .= " END as sortRank,";
				$sql .= " CASE bo.KEY_BIN_1";
				$sql .= "  WHEN 'FLOOR' THEN 'PARTIAL'";
				$sql .= "  ELSE bo.KEY_BIN_1";
				$sql .= " END as KEY_BIN_1,";
				$sql .= " bo.KEY_BIN_2,";
				$sql .= " bo.KEY_BIN_3,";
				$sql .= " bo.QTY_ONHD,";
				$sql .= " format(bo.DATE_RCV_1ST,'MM-dd-yyyy') as DATE_RCV_1ST";
				$sql .= " FROM nsa.BINTAG_ONHD bo";
				$sql .= " WHERE bo.ID_ITEM = '".$ID_ITEM."'"; 
				$sql .= " and KEY_BIN_1 <> 'WHSE'"; 
				$sql .= " and bo.ID_LOC = '".$ID_LOC."'"; 
				$sql .= " and (bo.KEY_BIN_1 <> 'FLOOR' OR ltrim(bo.KEY_BIN_3) <> '')";
				$sql .= " ORDER BY sortRank asc,";  
				$sql .= " bo.DATE_RCV_1ST asc"; 
				error_log($sql);
				QueryDatabase($sql, $results);

				$ret .= "<table class='sample'>\n";
				$ret .= " 	<tr class='blueHeader'>\n";
				$ret .= "		<th>Bin</th>\n";
				$ret .= "		<th>KeyBin2</th>\n";
				$ret .= "		<th>Lot</th>\n";
				$ret .= "		<th>Qty On Hand</th>\n";
				$ret .= "		<th>Date 1st Received</th>\n";
				$ret .= " 	</tr>\n";
				while ($row = mssql_fetch_assoc($results)) {
					$ret .= " 	<tr>\n";
					$ret .= "		<td>" .$row['KEY_BIN_1']. "</td>\n";
					$ret .= "		<td>" .$row['KEY_BIN_2']. "</td>\n";
					$ret .= "		<td>" .$row['KEY_BIN_3']. "</td>\n";
					$ret .= "		<td>" .$row['QTY_ONHD']. "</td>\n";
					$ret .= "		<td>" .$row['DATE_RCV_1ST']. "</td>\n";
					$ret .= " 	</tr>\n";
				}
				$ret .= "</table>\n";
				$ret .= "</br>\n";

				error_log("RET: ".$ret);

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
