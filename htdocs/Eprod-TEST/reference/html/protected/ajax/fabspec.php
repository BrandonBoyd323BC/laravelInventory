<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	require_once("../mpdf60/mpdf.php");
	setlocale(LC_MONETARY, 'en_US');

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

			//////////////////////
			// LOOKUP FABRIC SPEC RECORD
			//////////////////////
			if (isset($_POST["lookupFabSpec"]) && isset($_POST["id_item"])) {
				$ID_ITEM = stripIllegalChars2(trim($_POST["id_item"]));
				
				$sql =  "select ";
				$sql .= " ib.DESCR_1, ";
				$sql .= " ib.DESCR_2, ";
				//$sql .= " ib.RATIO_STK_PUR, ";
				$sql .= " ib.CODE_UM_PUR, ";
				//$sql .= " ic.COST_TOTAL_ACCUM_CRNT, ";
				$sql .= " (ib.RATIO_STK_PUR * ic.COST_TOTAL_ACCUM_CRNT) as COST_PER_UNIT_PUR, ";
				$sql .= " fs.rowid as fs_rowid, ";
				$sql .= " fs.* ";
				$sql .= " from ";
				$sql .= " nsa.ITMMAS_BASE ib ";
				$sql .= " left join nsa.ITMMAS_COST ic ";
				$sql .= " on ic.ID_ITEM = ib.ID_ITEM ";
				$sql .= " left join nsa.MU_FABRIC_SPEC fs ";
				$sql .= " on fs.ID_ITEM = ib.ID_ITEM ";
				$sql .= " where ib.ID_ITEM = '" . $ID_ITEM . "' ";
				$sql .= " and ib.ID_ITEM <> '' ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$TABLE_1_CHECKED = '';
					$TABLE_2_CHECKED = '';
					$TABLE_3_CHECKED = '';
					$TABLE_4_CHECKED = '';
					if ($row['FLAG_TABLE_1'] == 'T') {
						$TABLE_1_CHECKED = 'CHECKED';
					}
					if ($row['FLAG_TABLE_2'] == 'T') {
						$TABLE_2_CHECKED = 'CHECKED';
					}
					if ($row['FLAG_TABLE_3'] == 'T') {
						$TABLE_3_CHECKED = 'CHECKED';
					}
					if ($row['FLAG_TABLE_4'] == 'T') {
						$TABLE_4_CHECKED = 'CHECKED';
					}

					$strCostInfo = "";

					$ret .= "<table>\n";
					$ret .= "   <input type='hidden' id='h_fs_rowid' name='h_fs_rowid' value='" . $row['fs_rowid'] . "'>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<th colspan=2>" . $row['DESCR_1']  . "</th>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<th colspan=2>" . $row['DESCR_2'] . "</th>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr rowspan=2>\n";
					$ret .= "		<th colspan=2>" . money_format('%.2n', $row['COST_PER_UNIT_PUR']) . " per " . $row['CODE_UM_PUR'] . "</th>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Marker Width:</td>\n";
					$ret .= "		<td><input id='tb_marker_width' name='tb_marker_width' type='text' value='" . $row['MARKER_WIDTH'] . "'></input></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Max # of Layers:</td>\n";
					$ret .= "		<td><input id='tb_max_num_layers' name='tb_max_num_layers' type='text' value='" . $row['MAX_NUM_LAYERS'] . "'></input></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Max Marker Length:</td>\n";
					$ret .= "		<td><input id='tb_max_marker_length' name='tb_max_marker_length' type='text' value='" . $row['MAX_MARKER_LENGTH'] . "'></input></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Default Tables:</td>\n";
					$ret .= "		<td>\n";
					$ret .= "			1:<input id='cb_table1' name='cb_table1' type='checkbox' ".$TABLE_1_CHECKED."></input>\n";
					$ret .= "			2:<input id='cb_table2' name='cb_table2' type='checkbox' ".$TABLE_2_CHECKED."></input>\n";
					$ret .= "			3:<input id='cb_table3' name='cb_table3' type='checkbox' ".$TABLE_3_CHECKED."></input>\n";
					$ret .= "			4:<input id='cb_table4' name='cb_table4' type='checkbox' ".$TABLE_4_CHECKED."></input>\n";
					$ret .= "		</td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Direction for Length:</td>\n";
					$ret .= "		<td>\n";
					$ret .= "			<select name='sel_direction_length' id='sel_direction_length'>\n";
					if ($row['DIRECTION_LENGTH'] == NULL) {
						$ret .= "					<option value='SELECT'> -- Select -- </option>\n";
					}
					$A_DOLs = array("LENGTH OF MATERIAL","WIDTH OF MATERIAL","90 DEGREE ROTATION","NO LIMITATIONS");
					foreach ($A_DOLs as $DOL) {
						$SELECTED = '';
						$CURRENT = '';

						if (trim($row['DIRECTION_LENGTH']) == trim($DOL)) {
							$SELECTED = 'SELECTED';
							$CURRENT = '*';
						}
						$ret .= "				<option value='". $DOL . "' " . $SELECTED . ">" . $CURRENT . $DOL .  "</option>\n";
					}
					$ret .= "			</select>\n";
					$ret .= "		</td>\n";
					$ret .= "	</tr>\n";					
					$ret .= "	<tr>\n";
					$ret .= "		<td>Additional Length:</td>\n";
					$ret .= "		<td><input id='tb_length_add' name='tb_length_add' type='text' value='" . $row['LENGTH_ADD'] . "'></input></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Knife Type:</td>\n";
					$ret .= "		<td>\n";
					$ret .= "			<select name='sel_knife_type' id='sel_knife_type'>\n";
					if ($row['KNIFE_TYPE'] == NULL) {
						$ret .= "					<option value='SELECT'> -- Select -- </option>\n";
					}
					$A_KTs = array("REGULAR","SERRATED");
					foreach ($A_KTs as $KT) {
						$SELECTED = '';
						$CURRENT = '';

						if (trim($row['KNIFE_TYPE']) == trim($KT)) {
							$SELECTED = 'SELECTED';
							$CURRENT = '*';
						}
						$ret .= "				<option value='". $KT . "' " . $SELECTED . ">" . $CURRENT . $KT .  "</option>\n";
					}
					$ret .= "			</select>\n";
					$ret .= "		</td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Open or Tubular Cloth:</td>\n";
					$ret .= "		<td>\n";
					$ret .= "			<select name='sel_open_tubular' id='sel_open_tubular'>\n";
					if ($row['OPEN_TUBULAR'] == NULL) {
						$ret .= "					<option value='SELECT'> -- Select -- </option>\n";
					}
					$A_OTCs = array("OPEN","TUBULAR","BOTH");
					foreach ($A_OTCs as $OTC) {
						$SELECTED = '';
						$CURRENT = '';

						if (trim($row['OPEN_TUBULAR']) == trim($OTC)) {
							$SELECTED = 'SELECTED';
							$CURRENT = '*';
						}
						$ret .= "				<option value='". $OTC . "' " . $SELECTED . ">" . $CURRENT . $OTC .  "</option>\n";
					}
					$ret .= "			</select>\n";
					$ret .= "		</td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td># Layers of Paper:</td>\n";
					$ret .= "		<td><input id='tb_layers_paper' name='tb_layers_paper' type='text' value='" . $row['LAYERS_PAPER'] . "'></input></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td>Notes:</td>\n";
					$ret .= "		<td><textarea id='ta_notes' name='ta_notes'>" . $row['NOTES'] . "</textarea></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td></td>\n";
					if ($row['MARKER_WIDTH'] == NULL) {
						$ret .= "		<td><input id='button_SubmitNew' name='button_SubmitNew' type='button' value='Submit' onClick=\"insertFabSpec()\"></input></td>\n";
					} else {
						$ret .= "		<td><input id='button_Update' name='button_Update' type='button' value='Update' onClick=\"updateFabSpec()\"></input></td>\n";
					}
					$ret .= "	</tr>\n";
					$ret .= "	<tr>\n";
					$ret .= "		<td colspan=2><div id='div_submitResp' name='div_submitResp'></div></td>\n";
					$ret .= "	</tr>\n";
					$ret .= "</table>\n";
				}
			}
			
			//////////////////////
			// INSERT FABRIC SPEC RECORD
			//////////////////////
			if (isset($_POST["insertFabSpec"]) && isset($_POST["id_item"]) && isset($_POST["marker_width"]) && isset($_POST["max_num_layers"]) && isset($_POST["max_marker_length"]) 
				&& isset($_POST["flag_table_1"]) && isset($_POST["flag_table_2"]) && isset($_POST["flag_table_3"]) && isset($_POST["flag_table_4"])  && isset($_POST["direction_length"])
				&& isset($_POST["length_add"]) && isset($_POST["knife_type"]) && isset($_POST["open_tubular"]) && isset($_POST["layers_paper"])  && isset($_POST["notes"])
			){

				$ID_ITEM = strtoupper($_POST["id_item"]);
				$MARKER_WIDTH = $_POST["marker_width"];
				$MAX_NUM_LAYERS = $_POST["max_num_layers"];
				$MAX_MARKER_LENGTH = $_POST["max_marker_length"];
				$FLAG_TABLE_1 = $_POST["flag_table_1"];
				$FLAG_TABLE_2 = $_POST["flag_table_2"];
				$FLAG_TABLE_3 = $_POST["flag_table_3"];
				$FLAG_TABLE_4 = $_POST["flag_table_4"];
				$DIRECTION_LENGTH = strtoupper($_POST["direction_length"]);
				$LENGTH_ADD = $_POST["length_add"];
				$KNIFE_TYPE = strtoupper($_POST["knife_type"]);
				$OPEN_TUBULAR = strtoupper($_POST["open_tubular"]);
				$LAYERS_PAPER = $_POST["layers_paper"];
				$NOTES = $_POST["notes"];

				//INSERT FABRIC SPEC RECORD
				$sql  = "INSERT INTO nsa.MU_FABRIC_SPEC (";
				$sql .= " ID_USER_ADD, ";
				$sql .= " DATE_ADD, ";
				$sql .= " ID_ITEM, ";
				$sql .= " MARKER_WIDTH, ";
				$sql .= " MAX_NUM_LAYERS, ";
				$sql .= " MAX_MARKER_LENGTH, ";
				$sql .= " FLAG_TABLE_1, ";
				$sql .= " FLAG_TABLE_2, ";
				$sql .= " FLAG_TABLE_3, ";
				$sql .= " FLAG_TABLE_4, ";
				$sql .= " DIRECTION_LENGTH, ";
				$sql .= " LENGTH_ADD, ";
				$sql .= " KNIFE_TYPE, ";
				$sql .= " OPEN_TUBULAR, ";
				$sql .= " LAYERS_PAPER, ";
				$sql .= " NOTES ";
				$sql .= " ) values ( ";
				$sql .= " '" . $UserRow['ID_USER'] . "', ";
				$sql .= " getDate(), ";
				$sql .= " '" . $ID_ITEM . "', ";
				$sql .= " " . $MARKER_WIDTH . ", ";
				$sql .= " " . $MAX_NUM_LAYERS . ", ";
				$sql .= " '" . $MAX_MARKER_LENGTH . "', ";
				$sql .= " '" . $FLAG_TABLE_1 . "', ";
				$sql .= " '" . $FLAG_TABLE_2 . "', ";
				$sql .= " '" . $FLAG_TABLE_3 . "', ";
				$sql .= " '" . $FLAG_TABLE_4 . "', ";
				$sql .= " '" . $DIRECTION_LENGTH . "', ";
				$sql .= " " . $LENGTH_ADD . ", ";
				$sql .= " '" . $KNIFE_TYPE . "', ";
				$sql .= " '" . $OPEN_TUBULAR . "', ";
				$sql .= " " . $LAYERS_PAPER . ", ";
				$sql .= " '" . $NOTES . "' ";
				$sql .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$v = refreshFabricSpecRecs();
				$ret .= $v;
			}

			//////////////////////
			// UPDATE FABRIC SPEC RECORD
			//////////////////////
			if (isset($_POST["updateFabSpec"]) && isset($_POST["fs_rowid"]) && isset($_POST["id_item"]) && isset($_POST["marker_width"]) && isset($_POST["max_num_layers"]) && isset($_POST["max_marker_length"]) 
				&& isset($_POST["flag_table_1"]) && isset($_POST["flag_table_2"]) && isset($_POST["flag_table_3"]) && isset($_POST["flag_table_4"])  && isset($_POST["direction_length"])
				&& isset($_POST["length_add"]) && isset($_POST["knife_type"]) && isset($_POST["open_tubular"]) && isset($_POST["layers_paper"])  && isset($_POST["notes"])
			){

				$ID_ITEM = strtoupper($_POST["id_item"]);
				$FS_ROWID = $_POST["fs_rowid"];
				$MARKER_WIDTH = $_POST["marker_width"];
				$MAX_NUM_LAYERS = $_POST["max_num_layers"];
				$MAX_MARKER_LENGTH = $_POST["max_marker_length"];
				$FLAG_TABLE_1 = $_POST["flag_table_1"];
				$FLAG_TABLE_2 = $_POST["flag_table_2"];
				$FLAG_TABLE_3 = $_POST["flag_table_3"];
				$FLAG_TABLE_4 = $_POST["flag_table_4"];
				$DIRECTION_LENGTH = strtoupper($_POST["direction_length"]);
				$LENGTH_ADD = $_POST["length_add"];
				$KNIFE_TYPE = strtoupper($_POST["knife_type"]);
				$OPEN_TUBULAR = strtoupper($_POST["open_tubular"]);
				$LAYERS_PAPER = $_POST["layers_paper"];
				$NOTES = $_POST["notes"];

				//UPDATE FABRIC SPEC RECORD
				$sql  = "UPDATE nsa.MU_FABRIC_SPEC SET ";
				$sql .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
				$sql .= " DATE_CHG = getDate(), ";
				$sql .= " ID_ITEM = '" . $ID_ITEM . "', ";
				$sql .= " MARKER_WIDTH = '" . $MARKER_WIDTH . "', ";
				$sql .= " MAX_NUM_LAYERS = '" . $MAX_NUM_LAYERS . "', ";
				$sql .= " MAX_MARKER_LENGTH = '" . $MAX_MARKER_LENGTH . "', ";
				$sql .= " FLAG_TABLE_1 = '" . $FLAG_TABLE_1 . "', ";
				$sql .= " FLAG_TABLE_2 = '" . $FLAG_TABLE_2 . "', ";
				$sql .= " FLAG_TABLE_3 = '" . $FLAG_TABLE_3 . "', ";
				$sql .= " FLAG_TABLE_4 = '" . $FLAG_TABLE_4 . "', ";
				$sql .= " DIRECTION_LENGTH = '" . $DIRECTION_LENGTH . "', ";
				$sql .= " LENGTH_ADD = '" . $LENGTH_ADD . "', ";
				$sql .= " KNIFE_TYPE = '" . $KNIFE_TYPE . "', ";
				$sql .= " OPEN_TUBULAR = '" . $OPEN_TUBULAR . "', ";
				$sql .= " LAYERS_PAPER = '" . $LAYERS_PAPER . "', ";
				$sql .= " NOTES = '" . $NOTES . "' ";
				$sql .= " WHERE rowid = " . $FS_ROWID;

				QueryDatabase($sql, $results);
				$v = refreshFabricSpecRecs();
				$ret .= $v;
				
			}

			///////////////////////////
			/// refreshFabricSpecRecs
			///////////////////////////
			if (isset($_POST["refreshFabricSpecRecs"])) {
				$ret .= refreshFabricSpecRecs();
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}

function refreshFabricSpecRecs() {
	
	$sql  = "select ";
	$sql .= " ib.DESCR_1, ";
	$sql .= " ib.DESCR_2, ";
	$sql .= " ib.CODE_UM_PUR, ";
	$sql .= " (ib.RATIO_STK_PUR * ic.COST_TOTAL_ACCUM_CRNT) as COST_PER_UNIT_PUR, ";
	$sql .= " fs.rowid as fs_rowid, ";
	$sql .= " fs.* ";	
	$sql .= " from nsa.MU_FABRIC_SPEC fs ";
	$sql .= " left join nsa.ITMMAS_BASE ib ";
	$sql .= " on fs.ID_ITEM = ib.ID_ITEM ";
	$sql .= " left join nsa.ITMMAS_COST ic ";
	$sql .= " on fs.ID_ITEM = ic.ID_ITEM ";
	$sql .= " order by ID_ITEM desc ";
	QueryDatabase($sql, $results);

	$prevrowId = '';
	$b_flip = true;

	$ret1 = " <table class='sample'>\n";
	$ret1 .= " 	<tr>\n";
	$ret1 .= " 		<th class='sample'>Item</th>\n";
	$ret1 .= " 		<th class='sample'>Cost</th>\n";
	$ret1 .= " 		<th class='sample'>Marker Width</th>\n";
	$ret1 .= " 		<th class='sample'>Max Num Layers</th>\n";
	$ret1 .= " 		<th class='sample'>Max Marker Length</th>\n";
	$ret1 .= " 		<th class='sample'>Tables</th>\n";
	$ret1 .= " 		<th class='sample'>Direction Length</th>\n";
	$ret1 .= " 		<th class='sample'>Length Add</th>\n";
	$ret1 .= " 		<th class='sample'>Knife Type</th>\n";
	$ret1 .= " 		<th class='sample'>Open/Tubular</th>\n";
	$ret1 .= " 		<th class='sample'>Layers of Paper</th>\n";
	$ret1 .= " 		<th class='sample'>Notes</th>\n";
	$ret1 .= " 		<th class='sample'>Date</th>\n";
	$ret1 .= " 		<th class='sample'>User</th>\n";
	$ret1 .= " 	</tr>\n";

	while ($row = mssql_fetch_assoc($results)) {
		if ($prevrowId != $row['rowid']) {
			$b_flip = !$b_flip;
		}
		if ($b_flip) {
			$trClass = 'd1s';
		} else {
			$trClass = 'd0s';
		}
		$prevrowId = $row['rowid'];

		$tableList = "";
		if ($row['FLAG_TABLE_1'] == 'T') {
			$tableList .= "1 ";
		}
		if ($row['FLAG_TABLE_2'] == 'T') {
			$tableList .= "2 ";
		}
		if ($row['FLAG_TABLE_3'] == 'T') {
			$tableList .= "3 ";
		}
		if ($row['FLAG_TABLE_4'] == 'T') {
			$tableList .= "4 ";
		}

		$ret1 .= " 	<tr class='" . $trClass . "'>\n";
		$ret1 .= " 		<td class='" . $trClass . "' onDblClick=\"sendItemToSearchbox('".$row['ID_ITEM']."')\">" . $row['ID_ITEM'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . money_format('%.2n', $row['COST_PER_UNIT_PUR']) . " / " . $row['CODE_UM_PUR'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MARKER_WIDTH'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MAX_NUM_LAYERS'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['MAX_MARKER_LENGTH'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $tableList . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['DIRECTION_LENGTH'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['LENGTH_ADD'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['KNIFE_TYPE'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['OPEN_TUBULAR'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['LAYERS_PAPER'] . "</td>\n";
		$ret1 .= " 		<td class='" . $trClass . "'>" . $row['NOTES'] . "</td>\n";
		if ($row['DATE_CHG'] == NULL) {
			$ret1 .= " 		<td class='" . $trClass . "'>" . $row['DATE_ADD'] . "</td>\n";	
		} else {
			$ret1 .= " 		<td class='" . $trClass . "'>" . $row['DATE_CHG'] . "</td>\n";	
		}
		if ($row['ID_USER_CHG'] == NULL) {
			$ret1 .= " 		<td class='" . $trClass . "'>" . $row['ID_USER_ADD'] . "</td>\n";	
		} else {
			$ret1 .= " 		<td class='" . $trClass . "'>" . $row['ID_USER_CHG'] . "</td>\n";	
		}

		$ret1 .= " 	</tr>\n";
	}
	$ret1 .= " </table>\n";
	return $ret1;
}

?>
