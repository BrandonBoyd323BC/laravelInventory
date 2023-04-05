<?php

	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	require_once("../classes/mail.class.php");

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

			$action = $_POST["action"];

			switch($action){
				case "show";
					if (isset($_POST["show_mfg"]) && isset($_POST["show_model"]) && isset($_POST["show_model_num"]) && isset($_POST["show_drawer"]) && isset($_POST["show_row"]) && isset($_POST["show_bin"]) && isset($_POST["show_part_num"]) && isset($_POST["show_qty"]) && isset($_POST["sort_fieldC"]) && isset($_POST["sort_dir_flagC"]))  {
						//The line above makes sure the variable exist 

						//$SortFieldC = sort_fieldC;
						//$SortDirC = sort_dirC;	

						/*$SortFieldMo = "MODEL";			
						$SortFieldMn = "MODEL_NUM";		
						$SortFieldDr = "DRAWER";		
						$SortFieldRo = "ROW";			
						$SortFieldBi = "BIN";			
						$SortFieldPn = "PART_NUM";		
						$SortFieldQt = "QTY";
						*/			
/*						 
						if (isset($_POST["sort_field"]))  {
							$SortField = $_POST["sort_field"];
						}
						if (isset($_POST["sort_dir"]))  {
							$SortDir = $_POST["sort_dir"];
						}
						if (isset($_POST["sort_fieldC"]))  {
							$sortfieldC = $_POST["sort_fieldC"];
						}
						if (isset($_POST["sort_dir_flagC"]))  {
							$sortdirC = $_POST["sort_dir_flagC"];
						}
*/
						//$ShowStatus	= $_POST["Show"];
						$Show_MFG	    = 	$_POST["show_mfg"];
						$Show_MODEL  	= 	$_POST["show_model"];
						$Show_MODEL_NUM	= 	$_POST["show_model_num"];
						$Show_DRAWER	= 	$_POST["show_drawer"];
						$Show_ROW	    = 	$_POST["show_row"];
						$Show_BIN	    = 	$_POST["show_bin"];
						$Show_PART_NUM	= 	$_POST["show_part_num"];
						$Show_QTY    	= 	$_POST["show_qty"];
						$sort_fieldC 	=   $_POST["sort_fieldC"];
						$sort_dir_flagC =   $_POST["sort_dir_flagC"];

						$sqlF  = "SELECT ";
						$sqlF .= " pi.* ";
						$sqlF .= "from nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi ";
						$sqlF .= " where pi.FLAG_DEL <> 'Y' ";

						if ($Show_MFG <> 'ALL') {
							$sqlF .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlF .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlF.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlF .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlF .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlF .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlF .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlF .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						
						

						 $sqlF  .= "ORDER BY " . $sort_fieldC . " " . $sort_dir_flagC;
						//$sqlF .= "ORDER BY pi.BIN" ;
						error_log($sqlF);
						QueryDatabase($sqlF, $results);


						$prevrowId = '';
						$b_flip = true;

						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' style='background-color:#ADD8E6;' colspan=12>Add New Part</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >MFG</th>\n";
						$ret .= " 		<th colspan=1.75 class='sample' style='cursor:default;background-color:#00BFFF;' >Model</th>\n";
						$ret .= " 		<th colspan=1.75 class='sample' style='cursor:default;background-color:#00BFFF;' >Model Number</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Drawer</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Row</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Bin #</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Part Number</th>\n";
						$ret .= " 		<th colspan=2 class='sample' style='cursor:default;background-color:#00BFFF;' >Quantity</th>\n";
						
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_MANUFACTURER'></td>\n";
						$ret .= " 		<td class='sample'><input type=text id='add_MODEL'></td>\n";
						$ret .= " 		<td class='sample'><input type=text id='add_MODEL_NUM'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_DRAWER'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_ROW'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_BIN'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_PART_NUM'></td>\n";
						$ret .= " 		<td colspan=2 class='sample'><input size=10 type=text id='add_QUANTITY'></td>\n";

						//$ret .= " 		<td class='sample'>\n";
						/*$ret .= "			<select id='add_STATUS'>\n";
						foreach ($a_maint_stats as $code_status => $descr) {
							$ret .= "				<option value='" . $code_status . "'>" . $descr . "</option>\n";
						}
						$ret .= "			</select>\n";*/
						$ret .= "		</td>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= "	<th style='background-color:#ADD8E6;' class='sample' colspan=12> <input type='button' id='btnInsertRecord' value='Add Record' onClick='insertNewRecord()'></input>\n";

						$ret .= " 	<tr>\n";
						$ret .= " 	<th style='background-color:#ADD8E6;' colspan=12>Inventory</th>\n";
						$ret .= " 	<tr>\n";
						$ret .= "	<th style='background-color:#ADD8E6;' class='sample' colspan=12> <input type='button' id='' value='Reset Filter' onClick='location.reload();''></input>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='MANUFACTURER' onClick=\"sortColumnBy(this.id)\">Manufacturer</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='MODEL'        onClick=\"sortColumnBy(this.id)\">Model</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='MODEL_NUM'    onClick=\"sortColumnBy(this.id)\">Model Number</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='DRAWER'       onClick=\"sortColumnBy(this.id)\">Drawer</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='ROW'          onClick=\"sortColumnBy(this.id)\">Row</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='BIN'          onClick=\"sortColumnBy(this.id)\">Bin</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='Part_Num'    onClick=\"sortColumnBy(this.id)\">Part Number</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='QTY'          onClick=\"sortColumnBy(this.id)\">Quantity</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='BTNDELETE'    >Delete</th>\n";
						$ret .= " 	</tr>\n";

						

//////////////////////////
//Boxes for the filters //
//////////////////////////

///////////////////////////////Filter by Manufacture
						$ret .= " 	<tr>\n"; 
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='MANUFACTURER' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterMFG' onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlM = "SELECT DISTINCT ltrim(pi.MANUFACTURER) as MANUFACTURER ";
						$sqlM .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlM .= " where pi.FLAG_DEL <> 'Y' ";
						//if ($Show_MFG <> 'ALL') {
						//	$sqlM .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						//}	
						if ($Show_MODEL <> 'ALL') {
							$sqlM .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlM.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlM .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlM .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlM .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlM .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlM .= "and pi.QTY = '" . $Show_QTY . "' ";
						}
						$sqlM .= " ORDER BY MANUFACTURER";							
						QueryDatabase($sqlM, $resultsM);
						while ($rowM = mssql_fetch_assoc($resultsM)) {
							$SELECTED = '';
							if($Show_MFG == $rowM['MANUFACTURER']){
								$SELECTED = 'SELECTED';

							}

							$ret .="			<option value= '". $rowM['MANUFACTURER'] ."' ".$SELECTED."> ". $rowM['MANUFACTURER'] ." </option> ";
						}	
						$ret .="		</select>\n";
						
										
						$ret .="</td>\n"; 

						
						 
///////////////////////////// Filter by Model////////////////
						
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='MODEL' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterMODEL'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlMO = "SELECT DISTINCT ltrim(pi.MODEL) as MODEL ";
						$sqlMO .= " FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlMO .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlMO .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						//if ($Show_MODEL <> 'ALL') {
							//$sqlMO .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						//}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlMO.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlMO .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlMO .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlMO .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlMO .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlMO .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						$sqlMO .= " ORDER BY MODEL";
						QueryDatabase($sqlMO, $resultsMO);
						while ($rowMO = mssql_fetch_assoc($resultsMO)) {
							$SELECTED = '';
							if($Show_MODEL == $rowMO['MODEL']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowMO['MODEL'] ."'  ".$SELECTED."> ". $rowMO['MODEL'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 
						
						
///////////////////////////// Filter by Model Number						
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='MODEL_NUM' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterMODEL_NUM'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlMN = "SELECT DISTINCT ltrim(pi.MODEL_NUM) as MODEL_NUM ";
						$sqlMN .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlMN .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlMN .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlMN .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						//if ($Show_MODEL_NUM <> 'ALL') {
						//	$sqlMN.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						//}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlMN .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlMN .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlMN .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlMN .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlMN .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						$sqlMN .= " ORDER BY MODEL_NUM";
						QueryDatabase($sqlMN, $resultsMN);
						while ($rowMN = mssql_fetch_assoc($resultsMN)) {
							$SELECTED = '';
							if($Show_MODEL_NUM == $rowMN['MODEL_NUM']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowMN['MODEL_NUM'] ."'  ".$SELECTED."> ". $rowMN['MODEL_NUM'] ." </option> ";

						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 



////////////////////////////////// Filter by Drawer			
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='DRAWER' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterDRAWER'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlD = "SELECT DISTINCT ltrim(pi.DRAWER) as DRAWER ";
						$sqlD .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlD .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlD .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlD .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlD.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						//if ($Show_DRAWER <> 'ALL') {
						//	$sqlD .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						//}	
						if ($Show_ROW <> 'ALL') {
							$sqlD .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlD .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlD .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlD .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						$sqlD .= " ORDER BY DRAWER";
						QueryDatabase($sqlD, $resultsD);
						while ($rowD = mssql_fetch_assoc($resultsD)) {
							$SELECTED = '';
							if($Show_DRAWER == $rowD['DRAWER']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowD['DRAWER'] ."' ".$SELECTED."> ". $rowD['DRAWER'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 


////////////////////////////////// Filter by Row	
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='ROW' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterROW'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlR = "SELECT DISTINCT ltrim(pi.ROW) as ROW ";
						$sqlR .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlR .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlR .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlR .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlR.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlR .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						//if ($Show_ROW <> 'ALL') {
						//	$sqlR .= "and pi.ROW = '" . $Show_ROW . "' ";
						//}	
						if ($Show_BIN <> 'ALL') {
							$sqlR .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlR .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlR .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						$sqlR .= " ORDER BY ROW";

						QueryDatabase($sqlR, $resultsR);
						while ($rowR = mssql_fetch_assoc($resultsR)) {
							$SELECTED = '';
							if($Show_ROW == $rowR['ROW']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowR['ROW'] ."' ".$SELECTED."> ". $rowR['ROW'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 

					
////////////////////////////////// Filter by Bin
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='BIN' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterBIN'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlB = "SELECT DISTINCT ltrim(pi.BIN) as BIN ";
						$sqlB .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlB .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlB .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlB .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlB.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlB .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlB .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						//if ($Show_BIN <> 'ALL') {
						//	$sqlB .= "and pi.BIN = '" . $Show_BIN . "' ";
						//}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlB .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlB .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						$sqlB .= " ORDER BY BIN";

						QueryDatabase($sqlB, $resultsB);
						while ($rowB = mssql_fetch_assoc($resultsB)) {
							$SELECTED = '';
							if($Show_BIN == $rowB['BIN']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowB['BIN'] ."' ".$SELECTED."> ". $rowB['BIN'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 

						
////////////////////////////////// Filter by Part Number	
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='PART_NUM' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterPART_NUM'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlPN = "SELECT DISTINCT ltrim(pi.PART_NUM) as PART_NUM ";
						$sqlPN .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlPN .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlPN .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlPN .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlPN.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlPN .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlPN .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlPN .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						//if ($Show_PART_NUM <> 'ALL') {
						//	$sqlPN .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						//}	
						if ($Show_QTY <> 'ALL') {
							$sqlPN .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						$sqlPN .= " ORDER BY Part_Num";

						QueryDatabase($sqlPN, $resultsPN);
						while ($rowPN = mssql_fetch_assoc($resultsPN)) {
							$SELECTED = '';
							if($Show_PART_NUM == $rowPN['PART_NUM']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowPN['PART_NUM'] ."' ".$SELECTED."> ". $rowPN['PART_NUM'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 

						
////////////////////////////////// Filter by Quantity	
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='QTY' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterQTY'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlQTY = "SELECT DISTINCT ltrim(pi.QTY) as QTY ";
						$sqlQTY .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlQTY .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlQTY .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlQTY .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlQTY.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlQTY .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlQTY .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlQTY .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlQTY .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						//if ($Show_QTY <> 'ALL') {
						//	$sqlQTY .= "and pi.QTY = '" . $Show_QTY . "' ";
						//}	
						$sqlQTY .= " ORDER BY QTY";

						QueryDatabase($sqlQTY, $resultsQTY);
						while ($rowQTY = mssql_fetch_assoc($resultsQTY)) {
							$SELECTED = '';
							if($Show_QTY == $rowQTY['QTY']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowQTY['QTY'] ."' ".$SELECTED."> ". $rowQTY['QTY'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 
					

						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='BTNDELETE' onClick=\"sortBy(this.id)\"></td>\n";
						$ret .= " 	</tr>\n";
						

///////// ENDING TO ALL FILTERED DROP DOWNS. //////////////

						while ($row = mssql_fetch_assoc($results)) {
							if ($prevrowId != $row['rowid']) {
								$b_flip = !$b_flip;
							}
							if ($b_flip) {
								$trClass = 'd1';
							} else {
								$trClass = 'd0';
							}

							$ret .= " 	<tr class='" . $trClass . "'>\n";
							$ret .= " 		<td class='" . $trClass . "' id='MANUFACTURER__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MANUFACTURER'] . "'>" . $row['MANUFACTURER'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='MODEL__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MODEL'] . "'>" . $row['MODEL'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='MODEL_NUM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MODEL_NUM'] . "'>" . $row['MODEL_NUM'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='DRAWER__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['DRAWER'] . "'>" . $row['DRAWER'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='ROW__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['ROW'] . "'>" . $row['ROW'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='BIN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['BIN'] . "'>" . $row['BIN'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='PART_NUM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['PART_NUM'] . "'>" . $row['PART_NUM'] . "</td>\n";	
							$ret .= " 		<td class='" . $trClass . "' id='QTY__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['QTY'] . "'>" . $row['QTY'] . "</td>\n";
							//$ret .= " 		<td class='" . $trClass . "' id='FLAG_DEL__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['FLAG_DEL'] . "'>" . $row['FLAG_DEL'] //. "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' style='cursor:pointer;' id='BTNDELETE__" . $row['rowid']."'  onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
							$ret .= "		</td>\n";
							$ret .= " 	</tr>\n";
						}
						$ret .= " 	<tr>\n";
						$ret .= " 	</tr>\n";
						
						$ret .= " </table>\n";
						$ret .= "<input type=hidden id='sortDirFlag' value='0'>\n";
						$ret .= " </br>\n";
						$ret .= " </br>\n";
					}//end if
				break;

				case("showedit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$ret .= " 		<input type='text' id='" . $FieldID . "_TXT' value='" . $FieldValue . "'><br><input type='button' value='Save' onClick=\"saveEditField('" . $FieldID . "')\"><input type='button' value='Cancel' onClick=\"cancelEditField('" . $FieldID . "','" . $FieldValue . "')\">\n";
					}//end if
				break;

				case("saveedit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];

						$StrippedFieldValue = stripIllegalChars2($FieldValue);
						$vals = explode("__", $FieldID);
						$field = $vals[0];
						$rowid = $vals[1];


						$sqlu = "UPDATE nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " set " . $field . " = ltrim('" . $StrippedFieldValue . "') where rowid = " . $rowid;
						QueryDatabase($sqlu, $resultsu);
						error_log($sqlu);

						$ret .= $StrippedFieldValue;


					}//end if
				break;

				case("canceledit");
					if (isset($_POST["field_id"]) && isset($_POST["field_value"]))  {
						$FieldID = $_POST['field_id'];
						$FieldValue = $_POST['field_value'];
						$ret .= $FieldValue;
					}//end if
				break;


				///////////////////////////////////////////////////
				////INSERT NEW RECORD INTO SQL
				///////////////////////////////////////////////////
				case("insert_record");
					if (isset($_POST["manf"]) && isset($_POST["quantity"]) && isset($_POST["model"]) && isset($_POST["modelNum"]) && isset($_POST["drawer"]) && isset($_POST["row"]) && isset($_POST["bin"]) /*&& isset($_POST["assetNum"])*/ && isset($_POST["partNum"]))

					{
						$manf = $_POST["manf"];
						$model = $_POST["model"];
						$modelNum = $_POST["modelNum"];
						$drawer = $_POST["drawer"];
						$row = $_POST["row"];
						$bin = $_POST["bin"];
						//$assetNum = $_POST["assetNum"];
						$quantity = $_POST["quantity"];
						//$show_status = $_POST["show_status"];
						$partNum = $_POST["partNum"];



						$sql = " INSERT INTO nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " (";
						$sql .= " USER_ADD,  ";
						$sql .= " DATE_ADD, ";
						$sql .= " MANUFACTURER, ";
						$sql .= " MODEL, ";
						$sql .= " MODEL_NUM, ";
						$sql .= " DRAWER, ";
						$sql .= " ROW, ";
						$sql .= " BIN, ";
						//$sql .= " ASSET_NUM, ";
						$sql .= " PART_NUM, ";
						//if($status == 'D'){
						//$sql .= " DATE_DECOM, ";
						//}//end if
						$sql .= " QTY";
						$sql .= " )VALUES(";
						$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
						$sql .= " GetDate(), ";
						$sql .= " '" . ms_escape_string($manf) . "', ";
						$sql .= " '" . ms_escape_string($model) . "', ";
						$sql .= " '" . ms_escape_string($modelNum) . "', ";
						$sql .= " '" . ms_escape_string($drawer) . "', ";
						$sql .= " '" . ms_escape_string($row) . "', ";
						$sql .= " '" . ms_escape_string($bin) . "', ";
						//$sql .= " '" . ms_escape_string($assetNum) . "', ";
						$sql .= " '" . ms_escape_string($partNum) . "', ";
						//if($status == 'D'){
						//$sql .= " GetDate(), ";
						//}//end if
						$sql .= " '" . ms_escape_string($quantity) . "' ";
						$sql .= ") SELECT LAST_INSERT_ID=@@IDENTITY";

						error_log($sql);
						QueryDatabase($sql, $results);
						$row = mssql_fetch_assoc($results);
						$BaseRowID = $row['LAST_INSERT_ID'];
						
						$ret = refreshRecords();

					}//end if insert new record into SQl


				break;


				case("refresh_record");
					if (isset($_POST["refreshRec"])) {
						$ret = refreshRecords();
						error_log($ret);
					}//end if
				break;

				

				case("deleteRecord");	
					if (isset($_POST["deleteRecord"]) && isset($_POST["rowid"])) {
						$ROWID = $_POST["rowid"];

						$sqlDel = "update nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " set FLAG_DEL = 'Y', DATE_CHG = getdate(), USER_CHG = '" . $UserRow['ID_USER'] . "' where rowid = " . $ROWID;
						QueryDatabase($sqlDel, $resultsDel);
			

						$ret .= "DELETED";
						$ret = refreshRecords();
					}
				break;	
				refreshRecords();

			}//end switch

			echo json_encode(array("returnValue"=> $ret));
			
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	} 

	function refreshRecords(){

		location.reload();
		//global $ret,$DB_TEST_FLAG;


		/*

		//alert("hello"); 
					//	$SortFieldC  = sort_fieldC;
					//	$SortDirC    = sort_dirC;	
						$SortFieldMo = "MODEL";			
						$SortFieldMn = "MODEL_NUM";		
						$SortFieldDr = "DRAWER";		
						$SortFieldRo = "ROW";			
						$SortFieldBi = "BIN";			
						$SortFieldPn = "PART_NUM";		
						$SortFieldQt = "QTY";							
					 
						if (isset($_POST["show_mfg"]) && isset($_POST["show_model"]) && isset($_POST["show_model_num"]) && isset($_POST["show_drawer"]) && isset($_POST["show_row"]) && isset($_POST["show_bin"]) && isset($_POST["show_part_num"]) && isset($_POST["show_qty"]) && isset($_POST["sort_fieldC"]) && isset($_POST["sort_dir_flagC"]))  {

						if (isset($_POST["sort_field"]))  {
							$SortField = $_POST["sort_field"];
						}
						if (isset($_POST["sort_dir"]))  {
							$SortDir = $_POST["sort_dir"];
						}
						if (isset($_POST["sort_fieldC"]))  {
							$sortfieldC = $_POST["sort_fieldC"];
						}
						if (isset($_POST["sort_dir_flagC"]))  {
							$sortdirC = $_POST["sort_dir_flagC"];
						}
						//$ShowStatus	= $_POST["Show"];
/*
					
						$Show_MFG	    = 	$_POST["show_mfg"];
						$Show_MODEL  	= 	$_POST["show_model"];
						$Show_MODEL_NUM	= 	$_POST["show_model_num"];
						$Show_DRAWER	= 	$_POST["show_drawer"];
						$Show_ROW	    = 	$_POST["show_row"];
						$Show_BIN	    = 	$_POST["show_bin"];
						$Show_PART_NUM	= 	$_POST["show_part_num"];
						$Show_QTY    	= 	$_POST["show_qty"];
						$sort_fieldC 	=   $_POST["sort_fieldC"];
						$sort_dir_flagC =   $_POST["sort_dir_flagC"];

						$sqlF  = "SELECT ";
						$sqlF .= " pi.* ";
						$sqlF .= "from nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi ";
						$sqlF .= " where pi.FLAG_DEL <> 'Y' ";

						if ($Show_MFG <> 'ALL') {
							$sqlF .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlF .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlF.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlF .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlF .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlF .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlF .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlF .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	
						
						

						$sqlF  .= "ORDER BY " . $sort_fieldC . " " . $sort_dir_flagC;
	
						error_log($sqlF);
						QueryDatabase($sqlF, $results);

						$prevrowId = '';
						$b_flip = true;

						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' style='background-color:#ADD8E6;' colspan=12>Add New Part</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >MFG</th>\n";
						$ret .= " 		<th colspan=1.75 class='sample' style='cursor:default;background-color:#00BFFF;' >Model</th>\n";
						$ret .= " 		<th colspan=1.75 class='sample' style='cursor:default;background-color:#00BFFF;' >Model Number</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Drawer</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Row</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Bin #</th>\n";
						$ret .= " 		<th colspan=1.5 class='sample' style='cursor:default;background-color:#00BFFF;' >Part Number</th>\n";
						$ret .= " 		<th colspan=2 class='sample' style='cursor:default;background-color:#00BFFF;' >Quantity</th>\n";
						
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_MANUFACTURER'></td>\n";
						$ret .= " 		<td class='sample'><input type=text id='add_MODEL'></td>\n";
						$ret .= " 		<td class='sample'><input type=text id='add_MODEL_NUM'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_DRAWER'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_ROW'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_BIN'></td>\n";
						$ret .= " 		<td class='sample'><input size=10 type=text id='add_PART_NUM'></td>\n";
						$ret .= " 		<td colspan=2 class='sample'><input size=10 type=text id='add_QUANTITY'></td>\n";

						//$ret .= " 		<td class='sample'>\n";
						/*$ret .= "			<select id='add_STATUS'>\n";
						foreach ($a_maint_stats as $code_status => $descr) {
							$ret .= "				<option value='" . $code_status . "'>" . $descr . "</option>\n";
						}
						$ret .= "			</select>\n";
						$ret .= "		</td>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= "	<th style='background-color:#ADD8E6;' class='sample' colspan=12> <input type='button' id='btnInsertRecord' value='Add Record' onClick='insertNewRecord()'></input>\n";

						$ret .= " 	<tr>\n";
						$ret .= " 	<th style='background-color:#ADD8E6;' colspan=12>Inventory</th>\n";
						$ret .= " 	<tr>\n";
						$ret .= "	<th style='background-color:#ADD8E6;' class='sample' colspan=12> <input type='button' id='' value='Reset Filter' onClick='location.reload();''></input>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='MANUFACTURER' onClick=\"sortColumnBy(this.id)\">Manufacturer</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='MODEL'        onClick=\"sortColumnBy(this.id)\">Model</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='MODEL_NUM'    onClick=\"sortColumnBy(this.id)\">Model Number</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='DRAWER'       onClick=\"sortColumnBy(this.id)\">Drawer</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='ROW'          onClick=\"sortColumnBy(this.id)\">Row</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='BIN'          onClick=\"sortColumnBy(this.id)\">Bin</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='Part_Num'    onClick=\"sortColumnBy(this.id)\">Part Number</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='QTY'          onClick=\"sortColumnBy(this.id)\">Quantity</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='BTNDELETE'    onClick=\"sortColumnBy(this.id)\">Delete</th>\n";
						$ret .= " 	</tr>\n";
/*
						

//////////////////////////
//Boxes for the filters //
//////////////////////////

///////////////////////////////Filter by Manufacture
						$ret .= " 	<tr>\n"; 
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='MANUFACTURER' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterMFG' onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlM = "SELECT DISTINCT ltrim(pi.MANUFACTURER) as MANUFACTURER ";
						$sqlM .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlM .= " where pi.FLAG_DEL <> 'Y' ";
						//if ($Show_MFG <> 'ALL') {
						//	$sqlM .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						//}	
						if ($Show_MODEL <> 'ALL') {
							$sqlM .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlM.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlM .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlM .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlM .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlM .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlM .= "and pi.QTY = '" . $Show_QTY . "' ";
						}							
						QueryDatabase($sqlM, $resultsM);
						while ($rowM = mssql_fetch_assoc($resultsM)) {
							$SELECTED = '';
							if($Show_MFG == $rowM['MANUFACTURER']){
								$SELECTED = 'SELECTED';

							}

							$ret .="			<option value= '". $rowM['MANUFACTURER'] ."' ".$SELECTED."> ". $rowM['MANUFACTURER'] ." </option> ";
						}	
						$ret .="		</select>\n";
						
										
						$ret .="</td>\n"; 

						
						 
///////////////////////////// Filter by Model////////////////
						
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='MODEL' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterMODEL'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlMO = "SELECT DISTINCT ltrim(pi.MODEL) as MODEL ";
						$sqlMO .= " FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlMO .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlMO .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						//if ($Show_MODEL <> 'ALL') {
							//$sqlMO .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						//}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlMO.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlMO .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlMO .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlMO .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlMO .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlMO .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	

						QueryDatabase($sqlMO, $resultsMO);
						while ($rowMO = mssql_fetch_assoc($resultsMO)) {
							$SELECTED = '';
							if($Show_MODEL == $rowMO['MODEL']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowMO['MODEL'] ."'  ".$SELECTED."> ". $rowMO['MODEL'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 
						
						
///////////////////////////// Filter by Model Number						
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='MODEL_NUM' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterMODEL_NUM'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlMN = "SELECT DISTINCT ltrim(pi.MODEL_NUM) as MODEL_NUM ";
						$sqlMN .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlMN .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlMN .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlMN .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						//if ($Show_MODEL_NUM <> 'ALL') {
						//	$sqlMN.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						//}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlMN .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlMN .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlMN .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlMN .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlMN .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	

						QueryDatabase($sqlMN, $resultsMN);
						while ($rowMN = mssql_fetch_assoc($resultsMN)) {
							$SELECTED = '';
							if($Show_MODEL_NUM == $rowMN['MODEL_NUM']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowMN['MODEL_NUM'] ."'  ".$SELECTED."> ". $rowMN['MODEL_NUM'] ." </option> ";

						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 



////////////////////////////////// Filter by Drawer			
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='DRAWER' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterDRAWER'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlD = "SELECT DISTINCT ltrim(pi.DRAWER) as DRAWER ";
						$sqlD .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlD .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlD .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlD .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlD.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						//if ($Show_DRAWER <> 'ALL') {
						//	$sqlD .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						//}	
						if ($Show_ROW <> 'ALL') {
							$sqlD .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlD .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlD .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlD .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	

						QueryDatabase($sqlD, $resultsD);
						while ($rowD = mssql_fetch_assoc($resultsD)) {
							$SELECTED = '';
							if($Show_DRAWER == $rowD['DRAWER']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowD['DRAWER'] ."' ".$SELECTED."> ". $rowD['DRAWER'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 


////////////////////////////////// Filter by Row	
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='ROW' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterROW'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlR = "SELECT DISTINCT ltrim(pi.ROW) as ROW ";
						$sqlR .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlR .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlR .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlR .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlR.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlR .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						//if ($Show_ROW <> 'ALL') {
						//	$sqlR .= "and pi.ROW = '" . $Show_ROW . "' ";
						//}	
						if ($Show_BIN <> 'ALL') {
							$sqlR .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlR .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlR .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	

						QueryDatabase($sqlR, $resultsR);
						while ($rowR = mssql_fetch_assoc($resultsR)) {
							$SELECTED = '';
							if($Show_ROW == $rowR['ROW']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowR['ROW'] ."' ".$SELECTED."> ". $rowR['ROW'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 

					
////////////////////////////////// Filter by Bin
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='BIN' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterBIN'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlB = "SELECT DISTINCT ltrim(pi.BIN) as BIN ";
						$sqlB .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlB .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlB .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlB .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlB.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlB .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlB .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						//if ($Show_BIN <> 'ALL') {
						//	$sqlB .= "and pi.BIN = '" . $Show_BIN . "' ";
						//}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlB .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						if ($Show_QTY <> 'ALL') {
							$sqlB .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	

						QueryDatabase($sqlB, $resultsB);
						while ($rowB = mssql_fetch_assoc($resultsB)) {
							$SELECTED = '';
							if($Show_BIN == $rowB['BIN']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowB['BIN'] ."' ".$SELECTED."> ". $rowB['BIN'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 

						
////////////////////////////////// Filter by Part Number	
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='PART_NUM' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterPART_NUM'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlPN = "SELECT DISTINCT ltrim(pi.PART_NUM) as PART_NUM ";
						$sqlPN .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlPN .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlPN .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlPN .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlPN.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlPN .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlPN .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlPN .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						//if ($Show_PART_NUM <> 'ALL') {
						//	$sqlPN .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						//}	
						if ($Show_QTY <> 'ALL') {
							$sqlPN .= "and pi.QTY = '" . $Show_QTY . "' ";
						}	

						QueryDatabase($sqlPN, $resultsPN);
						while ($rowPN = mssql_fetch_assoc($resultsPN)) {
							$SELECTED = '';
							if($Show_PART_NUM == $rowPN['PART_NUM']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowPN['PART_NUM'] ."' ".$SELECTED."> ". $rowPN['PART_NUM'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 

						
////////////////////////////////// Filter by Quantity	
						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='QTY' onClick= sortBy(this.id)> ";

						$ret .="	<select id= 'filterQTY'  onChange=\"showStatusChange()\">";
						$ret .="	<option value='ALL'>-- ALL --</option> ";
						$sqlQTY = "SELECT DISTINCT ltrim(pi.QTY) as QTY ";
						$sqlQTY .=  "FROM nsa.PARTS_INVENTORY" . $DB_TEST_FLAG . " pi" ;
						$sqlQTY .= " where pi.FLAG_DEL <> 'Y' ";
						if ($Show_MFG <> 'ALL') {
							$sqlQTY .= "and pi.MANUFACTURER = '" . $Show_MFG . "' ";
						}	
						if ($Show_MODEL <> 'ALL') {
							$sqlQTY .= "and pi.MODEL = '" . $Show_MODEL . "' ";
						}	
						if ($Show_MODEL_NUM <> 'ALL') {
							$sqlQTY.= "and pi.MODEL_NUM = '" . $Show_MODEL_NUM . "' ";
						}	
						if ($Show_DRAWER <> 'ALL') {
							$sqlQTY .= "and pi.DRAWER = '" . $Show_DRAWER . "' ";
						}	
						if ($Show_ROW <> 'ALL') {
							$sqlQTY .= "and pi.ROW = '" . $Show_ROW . "' ";
						}	
						if ($Show_BIN <> 'ALL') {
							$sqlQTY .= "and pi.BIN = '" . $Show_BIN . "' ";
						}	
						if ($Show_PART_NUM <> 'ALL') {
							$sqlQTY .= "and pi.PART_NUM = '" . $Show_PART_NUM . "' ";
						}	
						//if ($Show_QTY <> 'ALL') {
						//	$sqlQTY .= "and pi.QTY = '" . $Show_QTY . "' ";
						//}	

						QueryDatabase($sqlQTY, $resultsQTY);
						while ($rowQTY = mssql_fetch_assoc($resultsQTY)) {
							$SELECTED = '';
							if($Show_QTY == $rowQTY['QTY']){
								$SELECTED = 'SELECTED';
							}
							$ret .="			<option value= '". $rowQTY['QTY'] ."' ".$SELECTED."> ". $rowQTY['QTY'] ." </option> ";
						}	
						$ret .="		</select>\n";
										
						$ret .="</td>\n"; 
					

						$ret .= " 		<td class='sample' style='cursor:default;background-color:#00BFFF;' id='BTNDELETE' onClick=\"sortBy(this.id)\"></td>\n";
						$ret .= " 	</tr>\n";
						

///////// ENDING TO ALL FILTERED DROP DOWNS. //////////////

						while ($row = mssql_fetch_assoc($results)) {
							if ($prevrowId != $row['rowid']) {
								$b_flip = !$b_flip;
							}
							if ($b_flip) {
								$trClass = 'd1';
							} else {
								$trClass = 'd0';
							}

							$ret .= " 	<tr class='" . $trClass . "'>\n";
							$ret .= " 		<td class='" . $trClass . "' id='MANUFACTURER__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MANUFACTURER'] . "'>" . $row['MANUFACTURER'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='MODEL__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MODEL'] . "'>" . $row['MODEL'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='MODEL_NUM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['MODEL_NUM'] . "'>" . $row['MODEL_NUM'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='DRAWER__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['DRAWER'] . "'>" . $row['DRAWER'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='ROW__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['ROW'] . "'>" . $row['ROW'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='BIN__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['BIN'] . "'>" . $row['BIN'] . "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='PART_NUM__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['PART_NUM'] . "'>" . $row['PART_NUM'] . "</td>\n";	
							$ret .= " 		<td class='" . $trClass . "' id='QTY__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['QTY'] . "'>" . $row['QTY'] . "</td>\n";
							//$ret .= " 		<td class='" . $trClass . "' id='FLAG_DEL__" . $row['rowid']."' onDblClick=\"showEditField(this.id)\" value='" . $row['FLAG_DEL'] . "'>" . $row['FLAG_DEL'] //. "</td>\n";
							$ret .= " 		<td class='" . $trClass . "' style='cursor:pointer;' id='BTNDELETE__" . $row['rowid']."'  onDblClick=\"deleteRecord('".$row['rowid']."')\">DEL</td>\n";
							$ret .= "		</td>\n";
							$ret .= " 	</tr>\n";
						}
						$ret .= " 	<tr>\n";
						$ret .= " 	</tr>\n";
						
						$ret .= " </table>\n";
						$ret .= "<input type=hidden id='sortDirFlag' value='0'>\n";
						$ret .= " </br>\n";
						$ret .= " </br>\n";
					}//end if
				}
			*/
			}
	?>