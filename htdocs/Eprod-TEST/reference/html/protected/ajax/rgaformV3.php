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

			if (isset($_POST["action"])) {
				$Div = "mainDiv";
				$Action = $_POST["action"];

				if (isset($_POST["divclose"])) {
					$ret .= "		<button onClick=\"disablePopup(". $Div .")\">CLOSE</button>\n<br>"; //close popup button
				}

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING ON";
				QueryDatabase($sql, $results);

				$action = $_POST["action"];

				switch($action){

					case "RGA_List";

						
						$customerStatus			= 	$_POST["customerStatus"];
						$isoStatus				= 	$_POST["isoStatus"];
						$filterRGA				= 	$_POST["filterRGA"];
						$filterCustomerNumber	= 	$_POST["filterCustomerNumber"];
						$filterCustomerName		= 	$_POST["filterCustomerName"];
						$filterCreatedBy		= 	$_POST["filterCreatedBy"];
						$filterNumResults		= 	$_POST["filterNumResults"];

						if ($customerStatus == 'ALL') {
							$customerStatus = '%';
						}
						if ($isoStatus == 'ALL') { 
							$isoStatus = '%';
						}
						if ($filterRGA == 'ALL') {
							$filterRGA = '%';
						}
						if ($filterCustomerNumber == 'ALL') {
							$filterCustomerNumber = '%';
						}
						if ($filterCustomerName == 'ALL') {
							$filterCustomerName = '%';
						}
						if ($filterCreatedBy == 'ALL') {
							$filterCreatedBy = '%';
						}



						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr class='blueHeader'>\n";
						$ret .= " 	<th colspan=12>RGA Requests for Review</th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th >RGA #</th>\n";
						$ret .= " 		<th >Customer #</th>\n";
						$ret .= "		<th >Customer Name</th>";
						$ret .= " 		<th >Date Created</th>\n";
						$ret .= " 		<th >Created By</th>\n";
						$ret .= " 		<th >ISO Status</th>\n";
						$ret .= " 		<th >Customer Status</th>\n";
						$ret .= " 	</tr>\n";

						$sql  = "select" ;
						if ($filterNumResults <> 'ALL') {
							$sql .= " top " . $filterNumResults . " ";
						} else {
							$sql .= " distinct ";
						}
						$sql .= " rb.RGA_NUMBER, ";
						$sql .= " rb.ID_CUST, ";
						$sql .= " rb.NAME_CUST, ";
						$sql .= " rb.DATE_ADD, ";
						$sql .= " rb.ID_USER_ADD, ";
						$sql .= " ri.ISO_STATUS, ";
						$sql .= " ri.CUST_STATUS ";
						$sql .= " from nsa.RGAV3_BASE". $DB_TEST_FLAG." rb ";
						$sql .= " left join nsa.RGAV3_ISO". $DB_TEST_FLAG." ri ";
						$sql .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
						$sql .= " where ri.CUST_STATUS LIKE '".$customerStatus."' and ri.ISO_STATUS LIKE '".$isoStatus."' and rb.RGA_NUMBER LIKE '".$filterRGA."' and rb.ID_CUST LIKE '".$filterCustomerNumber."' and rb.NAME_CUST LIKE '".$filterCustomerName."' and rb.ID_USER_ADD LIKE '".$filterCreatedBy."'";
						$sql .= " Order By rb.RGA_NUMBER desc ";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							$ret .= " 	<tr>\n";
							$ret .= " 		<td style='cursor: hand' onclick=\"goToReviewPopUp('".$row['RGA_NUMBER']."')\" ><u><font color='blue'>".$row['RGA_NUMBER']."</font></u></td>\n";
							$ret .= " 		<td >".$row['ID_CUST']."</td>\n";
							$ret .= "		<td >".$row['NAME_CUST']."</td>";
							$ret .= " 		<td >".$row['DATE_ADD']."</td>\n";
							$ret .= " 		<td ><center>".$row['ID_USER_ADD']."</center></td>\n";
							$ret .= " 		<td >".$row['ISO_STATUS']."</td>\n";
							$ret .= " 		<td >".$row['CUST_STATUS']."</td>\n";
							$ret .= " 	</tr>\n";
						}
					break;

					//case "new_form";
					case "show_form";
						$closeDiv = "";
						//$closeDiv = $_GET["closeDiv"];

						if (isset($_POST["rgaNumber"])) {
							$rgaNumber = $_POST["rgaNumber"];
							$sql  = "select ";
							$sql .= " rb.RGA_NUMBER, ";
							$sql .= " rb.ID_CUST, ";
							$sql .= " rb.NAME_CUST, ";
							$sql .= " rb.ID_INVC, ";
							$sql .= " rb.ID_SLSREP, ";
							$sql .= " rb.DATE_ADD, ";
							//$sql .= " rb.DATE_SHIP, ";
							$sql .= " convert(varchar(10),rb.DATE_SHIP,120) as DATE_SHIP, ";
							$sql .= " rb.CONTACT_NAME, ";
							$sql .= " rb.CONTACT_INFO, ";
							$sql .= " rb.ID_ORD, ";
							$sql .= " rb.ID_PO_CUST, ";									
							$sql .= " rb.ID_USER_ADD, ";
							$sql .= " rb.REQ_OR_COMP, ";
							$sql .= " rb.FLAG_EMAIL_SENT, ";
							$sql .= " rb.FLAG_RETURNING, ";
							$sql .= " rb.FLAG_REWORK, ";
							$sql .= " rb.FLAG_CREDIT, ";
							$sql .= " rb.ID_INVC_CREDIT, ";
							$sql .= " rb.ID_ORD_REWORK, ";
							$sql .= " rb.ID_ORD_REPLACE, ";
							$sql .= " ri.ISO_STATUS, ";
							$sql .= " ri.CUST_STATUS, ";
							$sql .= " ri.ERROR, ";
							$sql .= " ri.ERROR_TYPE, ";
							$sql .= " ri.INSPECTOR, ";
							$sql .= " ri.CAR_NUM, ";
							$sql .= " ri.EMAIL_ADD, ";
							$sql .= " ri.DEPARTMENT, ";
							$sql .= " ri.TEAM, ";
							$sql .= " ri.INVESTIGATION_NOTES, ";
							$sql .= " ri.RGA_COST ";							
							$sql .= " from nsa.RGAV3_BASE". $DB_TEST_FLAG." rb ";
							$sql .= " left join nsa.RGAV3_ISO". $DB_TEST_FLAG." ri ";
							$sql .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
							$sql .= " WHERE rb.RGA_NUMBER = '".$rgaNumber."'";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {

								//// RGAV3_BASE

								$rgaNumber = $row['RGA_NUMBER'];
								$ID_CUST = $row['ID_CUST'];
								$NAME_CUST = $row['NAME_CUST'];
								$DATE_SHIP = $row['DATE_SHIP'];
								$ID_INVC = $row['ID_INVC'];
							    $ID_SLSREP = $row['ID_SLSREP'];
								$DATE_ADD = $row['DATE_ADD'];
								$CONTACT_NAME = $row['CONTACT_NAME'];
								$REQ_OR_COMP = $row['REQ_OR_COMP'];
								$CONTACT_INFO = $row['CONTACT_INFO'];
								$FLAG_RETURNING = $row['FLAG_RETURNING'];
								$ID_ORD = $row['ID_ORD'];
								$FLAG_REWORK = $row['FLAG_REWORK'];
								$ID_PO_CUST = $row['ID_PO_CUST'];
								$FLAG_CREDIT = $row['FLAG_CREDIT'];
								$FLAG_EMAIL_SENT = $row['FLAG_EMAIL_SENT'];
								$RI1 = $row['ID_INVC_CREDIT'];
								$RI2 = $row['ID_ORD_REWORK'];
								$RI3 = $row['ID_ORD_REPLACE'];


								$ERROR1 = $row['ERROR'];		
								$ERROR_TYPE = $row['ERROR_TYPE'];
								$INSPECTOR = $row['INSPECTOR'];
								$CAR_NUM = $row['CAR_NUM'];
								$EMAIL_ADD = $row['EMAIL_ADD'];
								$DEPARTMENT = $row['DEPARTMENT'];
								$TEAM = $row['TEAM'];
								$INVESTIGATION_NOTES = $row['INVESTIGATION_NOTES'];
								$ISO_STATUS = $row['ISO_STATUS'];
								$CUST_STATUS = $row['CUST_STATUS'];
								$RGA_COST = $row['RGA_COST'];

								
						

							}
						} else {
							$rgaNumber = "";
							$ID_CUST = "";
							$NAME_CUST = "";
							$DATE_SHIP  = "";
							$ID_INVC = "";
							$ID_SLSREP = "";
							$DATE_ADD = "";
							$CONTACT_NAME = "";
							$REQ_OR_COMP = "";
							$CONTACT_INFO = "";
							$FLAG_RETURNING = "";
							$ID_ORD = "";
							$FLAG_REWORK = "";
							$ID_PO_CUST = "";
							$FLAG_CREDIT = "";
							$FLAG_EMAIL_SENT = "";
							$RI1 = "";
							$RI2 = "";
							$RI3 = "";
						
							$ERROR1 = "";
							$ERROR_TYPE = "";
							$INSPECTOR = "";
							$CAR_NUM = "";
							$EMAIL_ADD = "";
							$DEPARTMENT = "";
							$TEAM = "";
							$INVESTIGATION_NOTES = "";
							$ISO_STATUS = "";
							$CUST_STATUS = "";
							$RGA_COST = "";

							
						
						}

						////////////////Start of Request Information

						$ret .= " <table class='sample'>\n";
						$ret .= "<h2>Request Information</h2>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>RGA#</th>\n"; 
						$ret .= " 		<th class='sample'> <input type='text' id ='txt_rgaNumber' value='".$rgaNumber."'  disabled> </th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Date</th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='Date' value=" . date('Y-m-d') . " ></th>\n";
						$ret .= " 	</tr>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Customer Number</th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='Customer_Number' maxlength='6' value='".$ID_CUST."'></th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Customer Name</th>\n";
						$ret .= " 		<th class='sample' > <input type='text' id ='Customer_Name' maxlength='30' value='".$NAME_CUST."'></th>\n";					
						$ret .= " 	</tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Invoice #</th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='Invoice' maxlength='8' value='".$ID_INVC."' ></th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Territory</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample' id ='TerritoryS'>\n";
						$ret .= " 	<select id='TerritoryStatus'\">\n";
						$ret .="		<option value=''> --SELECT--</option>\n";						
						$sqlT = "SELECT ID_SLSREP, NAME_SLSREP ";
						$sqlT .="FROM nsa.tables_slsrep ";
						$sqlT .="Where ADDR_EMAIL is not null";

						QueryDatabase($sqlT, $resultsT);
						while ($rowT = mssql_fetch_assoc($resultsT)) {
								$SELECTED = '';
								if($ID_SLSREP == $rowT['ID_SLSREP']){
									$SELECTED = 'SELECTED';
								}					
						
							$ret .="		<option value='". $rowT['ID_SLSREP'] ."' ".$SELECTED."> ". $rowT['ID_SLSREP'] ." - ".$rowT['NAME_SLSREP']."</option>\n";		
						}

						$ret .= " 			</select>\n";

						$ret .= " 	</tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Ship Date</th>\n";
						$ret .= " 		<th class='sample' > <input type='text' value='".$DATE_SHIP."' id ='ShipDate'> </th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample' >Issued By</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample' id ='Issued_By' >" . $UserRow['NAME_EMP'] . "</td>\n";						
						$ret .= " 	</tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Contact Name</th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='Contact_Name' maxlength='80' value='".$CONTACT_NAME."'> </th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Request or Complaint?</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";

						$ReqCompR = '';
						$ReqCompC = '';
						$ReqCompS = '';

						if ($REQ_OR_COMP == 'R'){
							$ReqCompR = "SELECTED";
						}
						if ($REQ_OR_COMP == 'C'){
							$ReqCompC = "SELECTED";
						}
						if ($REQ_OR_COMP == 'S'){
							$ReqCompS = "SELECTED";
						}

						$ret .= " 			<select id='RoC' value='".$REQ_OR_COMP."'>\n";
						$ret .= " 				<option value='S'>--SELECT--</option>\n";							
						$ret .= " 				<option value='R' ".$ReqCompR.">Request</option>\n";	
						$ret .= " 				<option value='C' ".$ReqCompC.">Complaint</option>\n";		
						$ret .= " 			</select>\n";
						$ret .="</td>\n";							
						$ret .= " 	</tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Contact Information</th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='Contact_Information' value='".$CONTACT_INFO."' > </th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Will NSA recieve a return?</th>\n";

						$receiveRYes = '';
						$receiveRNo = '';

						if ($FLAG_RETURNING == 'Y'){
							$receiveRYes = "checked = 'checked'";
						}
						if ($FLAG_RETURNING == 'N'){
							$receiveRNo = "checked = 'checked'";
						}

						$ret .= " 		<td style='text-align: center;' class='sample'>\n";					
						$ret .= " 				<input type='radio' id='receiveReturnY' name='RecieveR'  ".$receiveRYes." value='Y' >Y</input>\n";
						$ret .= " 				<input type='radio' id='receiveReturnN' name='RecieveR'  ".$receiveRNo."  value='N' >N</input>\n";
						$ret .="		</td>\n";	
						$ret .= " 	</tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Order Number</th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='Order_Number' maxlength='30' value='".$ID_ORD."'></th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Rework Required?</th>\n";

						$ReworkYes = '';
						$ReworkNo = '';

						if ($FLAG_REWORK == 'Y'){
							$ReworkYes = "checked = 'checked'";
						}
						if ($FLAG_REWORK == 'N'){
							$ReworkNo = "checked = 'checked'";
						}
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";					
						$ret .= " 				<input type='radio' id='Rework_RequiredY' name='ReworkR'  ".$ReworkYes." value='Y' >Y</input>\n";
						$ret .= " 				<input type='radio' id='Rework_RequiredN' name='ReworkR'  ".$ReworkNo."  value='N' >N</input>\n";
						$ret .="		</td>\n";	
						$ret .= " 	</tr>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>PO#</th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='PO' maxlength='25' value='".$ID_PO_CUST."'></th>\n";
						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Credit Required?</th>\n";

						$CreditYes = '';
						$CreditNo = '';

						if ($FLAG_CREDIT == 'Y'){
							$CreditYes = "checked = 'checked'";
						}
						if ($FLAG_CREDIT == 'N'){
							$CreditNo = "checked = 'checked'";
						}
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";					
						$ret .= " 				<input type='radio' id='Credit_RequiredY' name='CreditR'  ".$CreditYes." value='Y' >Y</input>\n";
						$ret .= " 				<input type='radio' id='Credit_RequiredN' name='CreditR'  ".$CreditNo."  value='N' >N</input>\n";
						$ret .="		</td>\n";						
						$ret .= " 	</tr>\n";																												
						$ret .=" </table>\n";
////////////////End of Request Information Part 1
////////////////Part 2 Request Table
						$ret .=" <h2>Request Detail</h2>\n";

						$ret .= "<table class='sample' width='850' id='table_shipping' style='display:table;'>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "	</tr>\n";

						


						for ($y = 1; $y <= 10; $y++) {
							${"PartNum".$y} = '' ;
							${"TotalReceived".$y} = ''  ;
							${"Price".$y} = '' ;
							${"Total".$y} = '' ;
							${"Desc_of_Comp".$y} = '';
							$SEQ_LINE = $y;

							
							$sql = "Select ";
							$sql .= " rb.RGA_NUMBER, ";
							$sql .= " rl.ID_ITEM, ";
							$sql .= " rl.QTY_RECEIVED, ";
							$sql .= " rl.PRICE, ";
							$sql .= " rl.TOTAL, ";
							$sql .= " rl.COMPLAINT_DESC, ";
							$sql .= " rl.SEQ_LINE_RGA, ";
							$sql .= " rl.RGA_NUMBER ";	
							$sql .= " from nsa.RGAV3_BASE". $DB_TEST_FLAG." rb ";
							$sql .= " left join nsa.RGAV3_LINE". $DB_TEST_FLAG." rl ";
							$sql .= " on rb.RGA_NUMBER = rl.RGA_NUMBER ";
							$sql .= " WHERE rb.RGA_NUMBER = '".$rgaNumber."'";
							$sql .= " and SEQ_LINE_RGA = '".$y."' ";
							QueryDatabase($sql, $results);

																					// IF SEQ_LINE_RGA <> '' ... continue ... else move on to the next line of code  							 
							while ($row = mssql_fetch_assoc($results)) {
								${"PartNum".$y} 		= $row['ID_ITEM'];
								${"TotalReceived".$y} 	= $row['QTY_RECEIVED'];
								${"Price".$y} 			= $row['PRICE'];
								${"Total".$y} 			= $row['TOTAL'];
								${"Desc_of_Comp".$y} 	= $row['COMPLAINT_DESC'];
								$SEQ_LINE 				= $row['SEQ_LINE_RGA'];


							}

							if (isset($_POST["rgaNumber"])) {					
								if (${"PartNum".$y} <> ' ' || ${"TotalReceived".$y} <> '' || ${"Price".$y} <> '' || ${"Total".$y} <> '' || ${"Desc_of_Comp".$y} <> '' ) {
										$style = 'table-row';
									} elseif ($SEQ_LINE == '1') {
										$style = 'table-row';
									
									} else {
										$style = 'display:none;';
									}
									$z = $y +1;

							}else{
								if (${"PartNum".$y} <> '' || ${"TotalReceived".$y} <> '' || ${"Price".$y} <> '' || ${"Total".$y} <> '' || ${"Desc_of_Comp".$y} <> '' ) {
										$style = 'table-row';
									} elseif ($SEQ_LINE == '1') {
										$style = 'table-row';
									
									} else {
										$style = 'display:none;';
									}
								$z = $y +1;
							}

				

							$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
							$ret .= "		<td colspan=4>Request Detail ".$y."</td>\n";
							$ret .= "	</tr>\n";							
							$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
							$ret .= "       <td>Part Number:</td>\n";
							$ret .= "		<td><input id='txt_PartNum".$y."'  name='txt_PartNum".$y."'  type='text' value='".${"PartNum".$y}."' maxlength='30'></td>\n";
							$ret .= "       <td>Quantity Received:</td>\n";
							$ret .= "		<td><input id='txt_TotalReceived".$y."'  name='txt_TotalReceived".$y."'  type='text' value='".${"TotalReceived".$y}."' maxlength='4'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
							$ret .= "		<td>Price:</td>\n";
							$ret .= "		<td><input id='txt_Price".$y."'  name='txt_Price".$y."' type='text' value='".${"Price".$y}."' maxlength='11' ></input></td>\n";
							$ret .= "		<td>Total:</td>\n";
							$ret .= "		<td><input id='txt_Total".$y."'  name='txt_Total".$y."' type='text' value='".${"Total".$y}."' maxlength='11'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .=" 	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."'>\n";
							$ret .= "		<td>Description of Complaint:</td>\n";
							$ret .= "		<td colspan=4><textarea id='txt_Desc_of_Comp".$y."'  name='txt_Desc_of_Comp".$y."'  cols='55'> ".${"Desc_of_Comp".$y}." </textarea></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
							$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','4')\">+New Part   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','4','1')\"></font></td>\n";
							$ret .= "	</tr>\n";	
						}

						
////////////////Part 2 Return Detail
						$ret .=" <table class='sample'>\n";
						$ret .=" <h2>Return Detail</h2>\n";



						for ($y = 1; $y <= 10; $y++) {
							${"Quantity_Return".$y} = '' ;
							${"Condition".$y} = ''  ;
							${"Location".$y} = '' ;
							${"Comments_Return".$y} = '' ;
							$SEQ_SHIP = $y;

							$sql  = "select ";
							$sql .= " rb.RGA_NUMBER, ";
							$sql .= " rs.QTY, ";
							$sql .= " rs.CONDITION, ";
							$sql .= " rs.LOCATION, ";
							$sql .= " rs.COMMENTS, ";
							$sql .= " rs.SEQ_SHIP_RGA, ";
							$sql .= " rs.RGA_NUMBER ";							
							$sql .= " from nsa.RGAV3_BASE". $DB_TEST_FLAG." rb ";
							$sql .= " left join nsa.RGAV3_SHIP". $DB_TEST_FLAG." rs ";
							$sql .= " on rb.RGA_NUMBER = rs.RGA_NUMBER ";
							$sql .= " WHERE rb.RGA_NUMBER = '".$rgaNumber."'"; //and SEQ_SHIP_RGA = '".SEQ_ship_RGA."'"  ;
							$sql .= " and SEQ_SHIP_RGA = '".$y."' ";
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
					
								${"Quantity_Return".$y} 		= $row['QTY'];
								${"Condition".$y} 				= $row['CONDITION'];
								${"Location".$y} 				= $row['LOCATION'];
								${"Comments_Return".$y} 		= $row['COMMENTS'];
								$SEQ_SHIP 						= $row['SEQ_SHIP_RGA'];

							}

							if (isset($_POST["rgaNumber"])) {					
								if (${"Quantity_Return".$y} <> '' || ${"Condition".$y} <> ' ' || ${"Location".$y} <> '' || ${"Comments_Return".$y} <> '' ) {
									$style = 'table-row';
								} elseif ($SEQ_SHIP == '1') {
									$style = 'table-row';
								
								} else {
									$style = 'display:none;';
								}
								$z = $y +1;
							}else{
								if (${"Quantity_Return".$y} <> '' || ${"Condition".$y} <> '' || ${"Location".$y} <> '' || ${"Comments_Return".$y} <> '' ) {
									$style = 'table-row';
								} elseif ($SEQ_SHIP == '1') {
									$style = 'table-row';
								
								} else {
									$style = 'display:none;';
								}
								$z = $y +1;
							}


							$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
							$ret .= "		<td colspan=4>Return Detail ".$y."</td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
							$ret .= "		<td>Quantity:</td>\n";
							$ret .= "		<td><input id='txt_Quantity_Return".$y."' name='txt_Quantity_Return".$y."'  value='".${"Quantity_Return".$y}."' type='text' $ maxlength='4'></input></td>\n";
							$ret .= "		<td>Condition:</td>\n";
							$ret .= "		<td><input id='txt_Condition".$y."' name='txt_Condition".$y."'  value='".${"Condition".$y}."' type='text' $ maxlength='25'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
							$ret .= "		<td>Location:</td>\n";
							$ret .= "		<td><input id='txt_Location".$y."' name='txt_Location".$y."'  type='text' value='".${"Location".$y}."' $ maxlength='25'></input></td>\n";
							$ret .= "		<td>Comments:</td>\n";
							$ret .= "		<td><textarea id='txt_Comments_Return".$y."' name='txt_Comments_Return".$y."'  type='text' $ >".${"Comments_Return".$y}."</textarea></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
							$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','3')\">+Add Return   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','3','1')\"></font></td>\n";
							$ret .= "	</tr>\n";
						}
////////////////Resolution Information
						$ret .=" <table class ='sample'>\n";
						$ret .= "<h2>Resolution Information</h2>\n";						
						$ret .=" <tr>\n";
						$ret .="  <th style= 'background-color:#87CEFA'; > <p>A credit was issued via invoice #</p></th>\n";
						$ret .= " 		<th class='sample'> <input type='text' maxlength='8' id ='RI1' value='".$RI1."'> </th>\n";
						$ret .=" </tr>\n";
						$ret .=" <tr>\n";
						$ret .="  <th style= 'background-color:#87CEFA'; > <p>A rework was ordered on order #</p></th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='RI2' maxlength='8' value='".$RI2."'> </th>\n";
						$ret .=" </tr>\n";
						$ret .=" <tr>\n";
						$ret .="  <th style= 'background-color:#87CEFA'; > <p>A replacement was ordered on order #</p></th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='RI3' maxlength='8' value='".$RI3."'> </th>\n";												
						$ret .=" </tr>\n";
						$ret .=" </table>\n";
////////////////End of Resolution Information
////////////////Tracking Information
						$ret .= "<h2>Tracking Information</h2>\n";						
						$ret .=" <table class ='sample'>\n";
						$ret .="  <th colspan='1' style= 'background-color:#87CEFA'; > <p>Is this an order entry or sales communication error?</p></th>\n";

						$ErrorY = '';
						$ErrorN = '';

						if ($ERROR1 == 'Y'){
							$ErrorY = "checked = 'checked'";
						}
						if ($ERROR1 == 'N'){
							$ErrorN = "checked = 'checked'";
						}
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";					
						$ret .= " 				<input type='radio' id='ErrorY' name='ERRORt'  ".$ErrorY." value='Y' >Y</input>\n";
						$ret .= " 				<input type='radio' id='ErrorN' name='ERRORt'  ".$ErrorN."  value='N' >N</input>\n";
						$ret .="		</td>\n";	
						$ret .="  <th style= 'background-color:#87CEFA'; > <p>If no, what department will be assigned this claim?</p></th>\n";
						$ret .= " 		<th class='sample'> <input type='text' id ='Department' maxlength='30' value='".$DEPARTMENT."' > </th>\n";												
						$ret .=" </tr>\n";
						$ret .=" <tr>\n";						
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; ><p1>Error Type</th>\n";
						$ret .=" 	<th colspan='1' ><input type='text' id ='Error_Type' maxlength='30' value='".$ERROR_TYPE."' ></th>\n";
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; >Team/Individual</th>\n";
						$ret .=" 	<th colspan='1' ><input type='text' id ='TeamInd' maxlength='30' value='".$TEAM."' ></th>\n";
						$ret .=" </tr>\n";
						$ret .=" <tr>\n";
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; >Inspector/Editor</th>\n";
						$ret .=" 	<th colspan='1' ><input type='text' id ='Inspector' maxlength='30' value='".$INSPECTOR."' ></th>\n";						
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; >Final RGA Cost</th>\n";
						$ret .=" 	<th colspan='1'><input type='text' id ='FinalCost' maxlength='11' value='".$RGA_COST."' ></th>\n";	
						$ret .=" </tr>\n";
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; ><p1>ISO Status:</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";

						$ISO1 = '';
						$ISO2 = '';
						$ISO3 = '';

						if ($ISO_STATUS == 'Open'){
							$ISO1 = "SELECTED";
						}
						if ($ISO_STATUS == 'Received'){
							$ISO2 = "SELECTED";
						}
						if ($ISO_STATUS == 'Closed'){
							$ISO3 = "SELECTED";
						}

						$ret .= " 			<select id='ISOStatus' value='".$ISO_STATUS."'>\n";
						$ret .= " 				<option value=''>--SELECT--</option>\n";							
						$ret .= " 				<option value='Open' ".$ISO1.">Open</option>\n";	
						$ret .= " 				<option value='Received' ".$ISO2.">Received</option>\n";
						$ret .= " 				<option value='Closed' ".$ISO3.">Closed</option>\n";			
						$ret .= " 			</select>\n";
						$ret .="</td>\n";
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; >If a CAR was issued, enter the CAR# here:</th>\n";
						$ret .=" 	<th colspan='1'><input type='text' id ='CarNum' maxlength='12' value='".$CAR_NUM."'  ></th>\n";																	
						$ret .=" </tr>\n";
						$ret .=" <tr>\n";
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; ><p1>Customer Status:</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";

						$CUSTSTATO = '';
						$CUSTSTATP = '';
						$CUSTSTATR = '';

						if ($CUST_STATUS == 'Open-Customer'){
							$CUSTSTATO = "SELECTED";
						}
						if ($CUST_STATUS == 'Open-NSA'){
							$CUSTSTATP = "SELECTED";
						}
						if ($CUST_STATUS == 'Resolved'){
							$CUSTSTATR = "SELECTED";
						}

						$ret .= " 			<select id='CustomerStatus' value='".$CUST_STATUS."'>\n";
						$ret .= " 				<option value=''>--SELECT--</option>\n";							
						$ret .= " 				<option value='Open-Customer' ".$CUSTSTATO.">Open-Customer</option>\n";	
						$ret .= " 				<option value='Open-NSA' ".$CUSTSTATP.">Open-NSA</option>\n";
						$ret .= " 				<option value='Resolved' ".$CUSTSTATR.">Resolved</option>\n";			
						$ret .= " 			</select>\n";
						$ret .="</td>\n";
/*
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; ><p1>ISO Status:</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";

						$ISO1 = '';
						$ISO2 = '';
						$ISO3 = '';

						if ($ISO_STATUS == 'Open'){
							$ISO1 = "SELECTED";
						}
						if ($ISO_STATUS == 'Received'){
							$ISO2 = "SELECTED";
						}
						if ($ISO_STATUS == 'Closed'){
							$ISO3 = "SELECTED";
						}

						$ret .= " 			<select id='ISOStatus' value='".$ISO_STATUS."'>\n";
						$ret .= " 				<option value=''>--SELECT--</option>\n";							
						$ret .= " 				<option value='Open' ".$ISO1.">Open</option>\n";	
						$ret .= " 				<option value='Received' ".$ISO2.">Received</option>\n";
						$ret .= " 				<option value='Closed' ".$ISO3.">Closed</option>\n";			
						$ret .= " 			</select>\n";
						$ret .="</td>\n";
*/
//////////////////////////
												$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Send an Email?</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";

						$SendEmailY = '';
						$SendEmailN = '';

						if ($FLAG_EMAIL_SENT == 'Y'){
							$SendEmailY = "SELECTED";
						}
						if ($FLAG_EMAIL_SENT == 'N'){
							$SendEmailN = "SELECTED";
						}

						$ret .= " 			<select id='FLAG_EMAIL_SENT' value='".$FLAG_EMAIL_SENT."'>\n";
						$ret .= " 				<option value='T'>--SELECT--</option>\n";							
						$ret .= " 				<option value='Y' ".$SendEmailY.">YES</option>\n";	
						$ret .= " 				<option value='N' ".$SendEmailN.">NO</option>\n";		
						$ret .= " 			</select>\n";
						$ret .="</td>\n";

	//////					//////////////
						$ret .=" </tr>\n";
						$ret .=" <tr>\n";	
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; >Investigation Notes (if applicable)</th>\n";
						$ret .= "		<td colspan=4><textarea id='InvestNotes' cols='55'>".$INVESTIGATION_NOTES."</textarea></td>\n";
					
						$ret .=" </tr>\n";
												$ret .=" <tr>\n";	
						$ret .=" 	<th colspan='1' style= 'background-color:#87CEFA'; >Email Addresses</th>\n";
						$ret .= "		<td colspan=4><textarea id='EmailAdd' cols='55'>".$EMAIL_ADD."</textarea></td>\n";
					
						$ret .=" </tr>\n";
						$ret .=" <tr>\n";

/*

						$ret .= " 		<th style= 'background-color:#87CEFA'; class='sample'>Send an Email?</th>\n";
						$ret .= " 		<td style='text-align: center;' class='sample'>\n";

						$SendEmailY = '';
						$SendEmailN = '';

						if ($FLAG_EMAIL_SENT == 'Y'){
							$SendEmailY = "SELECTED";
						}
						if ($FLAG_EMAIL_SENT == 'N'){
							$SendEmailN = "SELECTED";
						}

						$ret .= " 			<select id='FLAG_EMAIL_SENT' value='".$FLAG_EMAIL_SENT."'>\n";
						$ret .= " 				<option value='T'>--SELECT--</option>\n";							
						$ret .= " 				<option value='Y' ".$SendEmailY.">YES</option>\n";	
						$ret .= " 				<option value='N' ".$SendEmailN.">NO</option>\n";		
						$ret .= " 			</select>\n";
						$ret .="</td>\n";
*/
						$ret .=" </tr>\n";																								
						$ret .=" </table>\n";
						$ret .="<br>\n";
							if (isset($_POST["rgaNumber"])) {
								$ret .=" <button type='button' id='button_submit_UpdateReq' onclick=\" return submit_updatereq();\" >Save Changes/Send</button>\n";	
							} else {
								$ret .=" <button type='button' id='button_submit_newreq' onclick=\" return submit_newreq();\" >Save/Send</button>\n";	
							}

						/*
						if (isset($_POST["rgaNumber"])) {
							$ret .=" <button type='button' id='button_submit_UpdateReq' onclick=\"submit_updatereq(); disablePopup(". $Div ."); window.location.reload()\">Save Changes/Send</button>\n";	
						} else {
							$ret .=" <button type='button' id='button_submit_newreq' onclick=\"submit_newreq(); disablePopup(". $Div ."); window.location.reload()\">Save/Send</button>\n";	
						}
						*/
////////////////End of Tracking Information		

						$ret .= " <button onClick=\"disablePopup(". $Div .")\">CLOSE</button>\n<br>";

			//}	// end if			
				break;	

				case 'submit_newreq';

		//// RGAV3_BASE Insert Query		 

						$rgaNum	=					$_POST["rgaNum"];
						$shipDate	= 				$_POST["shipDate"];
						$customerName	= 			$_POST["customerName"];
						$invoice	= 				$_POST["invoice"];
						$customerNumber	= 			$_POST["customerNumber"];
						$territoryS	= 				$_POST["territoryS"];
						$date	= 					$_POST["date"];
						$contactName	= 			$_POST["contactName"];
						$RoC	= 					$_POST["RoC"];
						$contactInformation	=		$_POST["contactInformation"];
						$receiveReturn	= 			$_POST["receiveReturn"];
						$orderNumber	= 			$_POST["Order_Number"];
						$reworkRequired	= 			$_POST["reworkRequired"];
						$poNumber	= 				$_POST["poNumber"];
						$creditRequired	= 			$_POST["creditRequired"];
						$FLAG_EMAIL_SENT	= 		$_POST["FLAG_EMAIL_SENT"];
						$RI1 = 						$_POST["RI1"];
						$RI2 = 						$_POST["RI2"];
						$RI3 = 						$_POST["RI3"];						

					//	error_log('innvoice: ' .$invoice);

						$sql = "Insert Into nsa.RGAV3_BASE". $DB_TEST_FLAG." ( ";
						$sql .= " DATE_SHIP, ";
						$sql .= " NAME_CUST, ";
						$sql .= " ID_ORD, ";
						$sql .= " ID_INVC, ";
						$sql .= " ID_PO_CUST, ";
						$sql .= " ID_CUST, ";
						$sql .= " ID_SLSREP, ";
						$sql .= " DATE_ADD, ";
						$sql .= " ID_USER_ADD, ";
						$sql .= " CONTACT_NAME, ";
						$sql .= " CONTACT_INFO, ";
						$sql .= " REQ_OR_COMP, ";
						$sql .= " FLAG_RETURNING, ";
						$sql .= " FLAG_REWORK, ";
						$sql .= " FLAG_CREDIT, ";
						$sql .= " ID_INVC_CREDIT, ";
						$sql .= " ID_ORD_REWORK, ";
						$sql .= " FLAG_EMAIL_SENT, ";
						$sql .= " ID_ORD_REPLACE ";
						$sql .= " ) VALUES ( ";
						$sql .= " '".$shipDate."', ";
						$sql .= " '".$customerName."', ";
						if(empty($orderNumber)){
							$orderNumber =  " null ";
							$sql .= " ".$orderNumber.", ";
						}else{
							$sql .= " '".$orderNumber."', ";
						}
						if(empty($invoice)){
							$invoice =  " null ";
							$sql .= " ".$invoice.", ";
						}else{
							$sql .= " '".$invoice."', ";
						}
						if(empty($poNumber)){
							$poNumber =  " null ";
							$sql .= " ".$poNumber.", ";
						}else{
							$sql .= " '".$poNumber."', ";
						}
						if(empty($customerNumber)){
							$customerNumber =  " null ";
							$sql .= " ".$customerNumber.", ";
						}else{
							$sql .= " '".$customerNumber."', ";
						}
						$sql .= " '".$territoryS."', ";
						$sql .= " '".$date."', ";
						$sql .= " '".$UserRow['ID_USER']."', ";
						if(empty($contactName)){
							$contactName =  " null ";
							$sql .= " ".$contactName.", ";
						}else{
							$sql .= " '".$contactName."', ";
						}
						if(empty($contactInformation)){
							$contactInformation =  " null ";
							$sql .= " ".$contactInformation.", ";
						}else{
							$sql .= " '".$contactInformation."', ";
						}
						$sql .= " '".$RoC."', ";
						$sql .= " '".$receiveReturn."', ";
						$sql .= " '".$reworkRequired."', ";
						$sql .= " '".$creditRequired."', ";
						if(empty($RI1)){
							$RI1 =  " null ";
							$sql .= " ".$RI1.", ";
						}else{
							$sql .= " '".$RI1."', ";
						}
						if(empty($RI2)){
							$RI2 =  " null ";
							$sql .= " ".$RI2.", ";
						}else{
							$sql .= " '".$RI2."', ";
						}
						$sql .= " '".$FLAG_EMAIL_SENT."', ";
						if(empty($RI3)){
							$RI3 =  " null ";
							$sql .= " ".$RI3." ";
						}else{
							$sql .= " '".$RI3."' ";
						}
						$sql .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
						QueryDatabase($sql, $results);
						$row = mssql_fetch_assoc($results);
						$RGA_NUMBER = $row['LAST_INSERT_ID'];
						//error_log("RGA_NUMBER" . $RGA_NUMBER);

		//// End of RGAV3_BASE inserts
		//// Start of RGAV3_Line Inserts	



						$PartNum1 = $_POST["PartNum1"];
						$PartNum2 = $_POST["PartNum2"];
						$PartNum3 = $_POST["PartNum3"];
						$PartNum4 = $_POST["PartNum4"];
						$PartNum5 = $_POST["PartNum5"];
						$PartNum6 = $_POST["PartNum6"];
						$PartNum7 = $_POST["PartNum7"];
						$PartNum8 = $_POST["PartNum8"];
						$PartNum9 = $_POST["PartNum9"];
						$PartNum10 = $_POST["PartNum10"];

						$TotalReceived1 = $_POST["TotalReceived1"];
						$TotalReceived2 = $_POST["TotalReceived2"];
						$TotalReceived3 = $_POST["TotalReceived3"];
						$TotalReceived4 = $_POST["TotalReceived4"];
						$TotalReceived5 = $_POST["TotalReceived5"];
						$TotalReceived6 = $_POST["TotalReceived6"];
						$TotalReceived7 = $_POST["TotalReceived7"];
						$TotalReceived8 = $_POST["TotalReceived8"];
						$TotalReceived9 = $_POST["TotalReceived9"];
						$TotalReceived10 = $_POST["TotalReceived10"];

						$Price1 = $_POST["Price1"];
						$Price2 = $_POST["Price2"];
						$Price3 = $_POST["Price3"];
						$Price4 = $_POST["Price4"];
						$Price5 = $_POST["Price5"];
						$Price6 = $_POST["Price6"];
						$Price7 = $_POST["Price7"];
						$Price8 = $_POST["Price8"];
						$Price9 = $_POST["Price9"];
						$Price10 = $_POST["Price10"];

						$Total1 = $_POST["Total1"];
						$Total2 = $_POST["Total2"];
						$Total3 = $_POST["Total3"];
						$Total4 = $_POST["Total4"];
						$Total5 = $_POST["Total5"];
						$Total6 = $_POST["Total6"];
						$Total7 = $_POST["Total7"];
						$Total8 = $_POST["Total8"];
						$Total9 = $_POST["Total9"];
						$Total10 = $_POST["Total10"];

						$Desc_of_Comp1 = trim($_POST["Desc_of_Comp1"]);
						$Desc_of_Comp2 = trim($_POST["Desc_of_Comp2"]);
						$Desc_of_Comp3 = trim($_POST["Desc_of_Comp3"]);
						$Desc_of_Comp4 = trim($_POST["Desc_of_Comp4"]);
						$Desc_of_Comp5 = trim($_POST["Desc_of_Comp5"]);
						$Desc_of_Comp6 = trim($_POST["Desc_of_Comp6"]);
						$Desc_of_Comp7 = trim($_POST["Desc_of_Comp7"]);
						$Desc_of_Comp8 = trim($_POST["Desc_of_Comp8"]);
						$Desc_of_Comp9 = trim($_POST["Desc_of_Comp9"]);
						$Desc_of_Comp10 = trim($_POST["Desc_of_Comp10"]);

						for ($y = 1; $y <= 10; $y++) {

							if(${"PartNum".$y} <> ' ' || ${"TotalReceived".$y} <> '' || ${"Price".$y} <> '' || 
								${"Total".$y} <> '' || ${"Desc_of_Comp".$y} <> '' //|| ${"SEQ_LINE_RGA".$y} <> ''
							){
								$sql = "Insert Into nsa.RGAV3_LINE". $DB_TEST_FLAG." ( ";
								$sql .= " RGA_NUMBER, ";
								$sql .= " ID_ITEM, ";
								$sql .= " QTY_RECEIVED, ";
								$sql .= " PRICE, ";
								$sql .= " TOTAL, ";
								$sql .= " COMPLAINT_DESC, ";
								$sql .= " SEQ_LINE_RGA ";
								$sql .= " ) VALUES ( ";
								$sql .= " '". $RGA_NUMBER."', ";
								if(empty(${"PartNum".$y})){
									${"PartNum".$y} = " ";
								$sql .= " '". ms_escape_string(${"PartNum".$y}) ."', ";
								}else{
								$sql .= " '". ms_escape_string(${"PartNum".$y}) ."', ";
								}
								if(empty(${"TotalReceived".$y})){
									${"TotalReceived".$y} =  " null ";
									$sql .= " ".${"TotalReceived".$y}.", ";
								}else{
									$sql .= " '".${"TotalReceived".$y}."', ";
								}
								if(empty(${"Price".$y})){
									${"Price".$y} =  " null ";
									$sql .= " ".${"Price".$y}.", ";
								}else{
									$sql .= " '".${"Price".$y}."', ";
								}
								if(empty(${"Total".$y})){
									${"Total".$y} =  " null ";
									$sql .= " ".${"Total".$y}.", ";
								}else{
									$sql .= " '".${"Total".$y}."', ";
								}
								if(empty(${"Desc_of_Comp".$y})){
									${"Desc_of_Comp".$y} =  " null ";
									$sql .= " ".${"Desc_of_Comp".$y}.", ";
								}else{
									$sql .= " '".${"Desc_of_Comp".$y}."', ";
								}
								$sql .= " '". $y ."'";
								$sql .= " )";
								QueryDatabase($sql, $results);
							} 
						}

			////End of RGAv3_LINE Inserts



			////Start of RGAv3_SHIP Inserts

						$Quantity_Return1 = $_POST["Quantity_Return1"];
						$Quantity_Return2 = $_POST["Quantity_Return2"];
						$Quantity_Return3 = $_POST["Quantity_Return3"];
						$Quantity_Return4 = $_POST["Quantity_Return4"];
						$Quantity_Return5 = $_POST["Quantity_Return5"];
						$Quantity_Return6 = $_POST["Quantity_Return6"];
						$Quantity_Return7 = $_POST["Quantity_Return7"];
						$Quantity_Return8 = $_POST["Quantity_Return8"];
						$Quantity_Return9 = $_POST["Quantity_Return9"];
						$Quantity_Return10 = $_POST["Quantity_Return10"];

						$Condition1 = $_POST["Condition1"];
						$Condition2 = $_POST["Condition2"];
						$Condition3 = $_POST["Condition3"];
						$Condition4 = $_POST["Condition4"];
						$Condition5 = $_POST["Condition5"];
						$Condition6 = $_POST["Condition6"];
						$Condition7 = $_POST["Condition7"];
						$Condition8 = $_POST["Condition8"];
						$Condition9 = $_POST["Condition9"];
						$Condition10 = $_POST["Condition10"];

						$Location1 = $_POST["Location1"];
						$Location2 = $_POST["Location2"];
						$Location3 = $_POST["Location3"];
						$Location4 = $_POST["Location4"];
						$Location5 = $_POST["Location5"];
						$Location6 = $_POST["Location6"];
						$Location7 = $_POST["Location7"];
						$Location8 = $_POST["Location8"];
						$Location9 = $_POST["Location9"];
						$Location10 = $_POST["Location10"];

						$Comments_Return1 = $_POST["Comments_Return1"];
						$Comments_Return2 = $_POST["Comments_Return2"];
						$Comments_Return3 = $_POST["Comments_Return3"];
						$Comments_Return4 = $_POST["Comments_Return4"];
						$Comments_Return5 = $_POST["Comments_Return5"];
						$Comments_Return6 = $_POST["Comments_Return6"];
						$Comments_Return7 = $_POST["Comments_Return7"];
						$Comments_Return8 = $_POST["Comments_Return8"];
						$Comments_Return9 = $_POST["Comments_Return9"];
						$Comments_Return10 = $_POST["Comments_Return10"];	

						for ($y = 1; $y <= 10; $y++) {
							if(${"Quantity_Return".$y} <> '' || ${"Condition".$y} <> ' ' || ${"Location".$y} <> '' || 
								${"Comments_Return".$y} <> '' //|| ${"SEQ_LINE_RGA".$y} <> ''
						){
								$sql = "Insert Into nsa.RGAV3_SHIP". $DB_TEST_FLAG." ( ";
								$sql .= " RGA_NUMBER, ";
								$sql .= " QTY, ";
								$sql .= " CONDITION, ";
								$sql .= " LOCATION, ";
								$sql .= " COMMENTS, ";
								$sql .= " SEQ_ship_RGA ";
								$sql .= " ) VALUES ( ";
								$sql .= " '". $RGA_NUMBER."', ";
								if(empty(${"Quantity_Return".$y})){
									${"Quantity_Return".$y} =  " null ";
									$sql .= " ".${"Quantity_Return".$y}.", ";
								}else{
									$sql .= " '".${"Quantity_Return".$y}."', ";
								}
								if(empty(${"Condition".$y})){
									${"Condition".$y} = " ";
									$sql .= " '". ms_escape_string(${"Condition".$y}) ."', ";
								}else{
									$sql .= " '". ms_escape_string(${"Condition".$y}) ."', ";
								}
								if(empty(${"Location".$y})){
									${"Location".$y} =  " null ";
									$sql .= " ".${"Location".$y}.", ";
								}else{
									$sql .= " '".${"Location".$y}."', ";
								}
								if(empty(${"Comments_Return".$y})){
									${"Comments_Return".$y} =  " null ";
									$sql .= " ".${"Comments_Return".$y}.", ";
								}else{
									$sql .= " '".${"Comments_Return".$y}."', ";
								}
								//$sql .= " '". ms_escape_string(${"Quantity_Return".$y}) ."', ";
								//$sql .= " '". ms_escape_string(${"Condition".$y}) ."', ";
								//$sql .= " '". ms_escape_string(${"Location".$y}) ."', ";
								//$sql .= " '". ms_escape_string(${"Comments_Return".$y}) ."', ";
								$sql .= " '".$y."'";
								$sql .= " )";
								QueryDatabase($sql, $results);
							} 
						}

			//End of RGAV3_SHIP Inserts
			//Start of RGAV3_ISO Inserts			

						$Error1 			= $_POST["Error1"];
						$Error_Type 		= $_POST["Error_Type"];
						$TeamInd 			= $_POST["TeamInd"];
						$Inspector 			= $_POST["Inspector"];
						$InvestNotes 		= $_POST["InvestNotes"];
						$CarNum 			= $_POST["CarNum"];
						$FinalCost 			= $_POST["FinalCost"];
						$CustomerStatus 	= $_POST["CustomerStatus"];
						$ISOStatus 			= $_POST["ISOStatus"];
						$EmailAdd 			= $_POST["EmailAdd"];
						$Department 		= $_POST["Department"];

						$sql = "Insert Into nsa.RGAV3_ISO". $DB_TEST_FLAG." ( ";
						$sql .= " RGA_NUMBER, ";
						$sql .= " ERROR, ";
						$sql .= " ERROR_TYPE, ";
						$sql .= " INSPECTOR, ";
						$sql .= " CAR_NUM, ";
						$sql .= " CUST_STATUS, ";
						$sql .= " EMAIL_ADD, ";
						$sql .= " DEPARTMENT, ";
						$sql .= " TEAM, ";
						$sql .= " INVESTIGATION_NOTES, ";
						$sql .= " RGA_COST, ";
						$sql .= " ISO_STATUS ";
						$sql .= " ) VALUES ( ";
						$sql .= " '".$RGA_NUMBER."', ";
						$sql .= " '".$Error1."', ";
						if(empty($Error_Type)){
							$Error_Type =  " null ";
							$sql .= " ".$Error_Type.", ";
						}else{
							$sql .= " '".$Error_Type."', ";
						}
						if(empty($Inspector)){
							$Inspector =  " null ";
							$sql .= " ".$Inspector.", ";
						}else{
							$sql .= " '".$Inspector."', ";
						}
						if(empty($CarNum)){
							$CarNum =  " null ";
							$sql .= " ".$CarNum.", ";
						}else{
							$sql .= " '".$CarNum."', ";
						}
						$sql .= " '".$CustomerStatus."', ";	
						if(empty($EmailAdd)){
							$EmailAdd =  " null ";
							$sql .= " ".$EmailAdd.", ";
						}else{
							$sql .= " '".$EmailAdd."', ";
						}
						if(empty($Department)){
							$Department =  " null ";
							$sql .= " ".$Department.", ";
						}else{
							$sql .= " '".$Department."', ";
						}		
						if(empty($TeamInd)){
							$TeamInd =  " null ";
							$sql .= " ".$TeamInd.", ";
						}else{
							$sql .= " '".$TeamInd."', ";
						}									
						if(empty($InvestNotes)){
							$InvestNotes =  " null ";
							$sql .= " ".$InvestNotes.", ";
						}else{
							$sql .= " '".$InvestNotes."', ";
						}
						if(empty($FinalCost)){
							$FinalCost =  " null ";
							$sql .= " ".$FinalCost.", ";
						}else{
							$sql .= " '".$FinalCost."', ";
						}
						$sql .= " '".$ISOStatus."' ";
						$sql .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
						QueryDatabase($sql, $results);
						$row = mssql_fetch_assoc($results);
						$RGA_NUMBER = $row['LAST_INSERT_ID'];

						/////////////////////////////////////////////////////
						////////////////////Email Attempt////////////////////
						/////////////////////////////////////////////////////
										

						if ($FLAG_EMAIL_SENT == 'Y') {
							if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
								$head = array(
								'to'      =>array(''.$EmailAdd.'' =>'Josh Wallace'),
								'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
								//'cc'      =>array('jwallace@thinknsa.com'=>$row['NAME_EMP']),
								//'bcc'     =>array('email4@email.net'=>'Admin'),
							);
							} else {
							$head = array(
								//'to'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy'),
								//'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
								//'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
								//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
							);
							}
							$subject = "New RGA " . $RGA_NUMBER . " - " . $customerName;
							$body = "RGA form #" . $RGA_NUMBER . " has been created.";
							mail::send($head,$subject,$body);

							//$sql1  = " UPDATE nsa.RGAV3_BASE" . $DB_TEST_FLAG . " ";
							//$sql1 .= " SET FLAG_EMAIL_SENT = 'T' ";
							//$sql1 .= " WHERE RGA_NUMBER = '". $RGA_NUMBER ."'";
							//QueryDatabase($sql1, $results1);
							//echo "Message has been sent";;

						}


			//End of RGAv3_ISO Inserts

						$ret .= "<input type='hidden' id='ret_rga_number' name='ret_rga_number' value='".$RGA_NUMBER."'></input>";
			break;

				case 'submit_UpdateReq';


						//$RGA_NUMBER = $row['LAST_INSERT_ID'];

						$RGA_NUMBER		=			trim($_POST["rgaNum"]);
						$shipDate	= 				trim($_POST["shipDate"]);
						$customerName	= 			trim($_POST["customerName"]);
						$invoice	= 				trim($_POST["invoice"]);
						$customerNumber	= 			trim($_POST["customerNumber"]);
						$territoryS	= 				trim($_POST["territoryS"]);
						$date	= 					trim($_POST["date"]);
						$contactName	= 			trim($_POST["contactName"]);
						$RoC	= 					trim($_POST["RoC"]);
						$contactInformation	=		trim($_POST["contactInformation"]);
						$receiveReturn	= 			trim($_POST["receiveReturn"]);
						$orderNumber	= 			trim($_POST["Order_Number"]);
						$reworkRequired	= 			trim($_POST["reworkRequired"]);
						$poNumber	= 				trim($_POST["poNumber"]);
						$creditRequired	= 			trim($_POST["creditRequired"]);
						$FLAG_EMAIL_SENT	= 		trim($_POST["FLAG_EMAIL_SENT"]);
						$RI1 = 						trim($_POST["RI1"]);
						$RI2 = 						trim($_POST["RI2"]);
						$RI3 = 						trim($_POST["RI3"]);					
				////BASE
						$sql = "Update nsa.RGAV3_BASE". $DB_TEST_FLAG." ";
						$sql .= " SET ";
						$sql .= " DATE_SHIP = '" . $shipDate . "', ";
						$sql .= " NAME_CUST = '" . $customerName . "', ";
						$sql .= " ID_INVC = ";
						if(empty($invoice)){
							$invoice =  " null ";
							$sql .= " ".$invoice.", ";
						}else{
							$sql .= " '".$invoice."', ";
						}
						$sql .= " ID_CUST = ";
						if(empty($customerNumber)){
							$customerNumber =  " null ";
							$sql .= " ".$customerNumber.", ";
						}else{
							$sql .= " '".$customerNumber."', ";
						}
						$sql .= " ID_SLSREP = '" . $territoryS . "', ";
						$sql .= " DATE_ADD = '" . $date . "', ";
						$sql .= " CONTACT_NAME = ";
						if(empty($contactName)){
							$contactName =  " null ";
							$sql .= " ".$contactName.", ";
						}else{
							$sql .= " '".$contactName."', ";
						}
						$sql .= " REQ_OR_COMP = '" . $RoC . "', ";
						$sql .= " CONTACT_INFO = ";
						if(empty($contactInformation)){
							$contactInformation =  " null ";
							$sql .= " ".$contactInformation.", ";
						}else{
							$sql .= " '".$contactInformation."', ";
						}
						$sql .= " FLAG_RETURNING = '" . $receiveReturn . "', ";
						$sql .= " ID_ORD = ";
						if(empty($orderNumber)){
							$orderNumber =  " null ";
							$sql .= " ".$orderNumber.", ";
						}else{
							$sql .= " '".$orderNumber."', ";
						}
						$sql .= " FLAG_REWORK = '" . $reworkRequired . "', ";
						$sql .= " ID_PO_CUST = ";
						if(empty($poNumber)){
							$poNumber =  " null ";
							$sql .= " ".$poNumber.", ";
						}else{
							$sql .= " '".$poNumber."', ";
						}
						$sql .= " FLAG_CREDIT = '" . $creditRequired . "', ";
						$sql .= " ID_INVC_CREDIT = ";
						if(empty($RI1)){
							$RI1 =  " null ";
							$sql .= " ".$RI1.", ";
						}else{
							$sql .= " '".$RI1."', ";
						}
						$sql .= " ID_ORD_REWORK = ";
						if(empty($RI2)){
							$RI2 =  " null ";
							$sql .= " ".$RI2.", ";
						}else{
							$sql .= " '".$RI2."', ";
						}
						$sql .= " ID_ORD_REPLACE = ";
						if(empty($RI3)){
							$RI3 =  " null ";
							$sql .= " ".$RI3." ";
						}else{
							$sql .= " '".$RI3."' ";
						}
						$sql .= " WHERE RGA_NUMBER = '" . $RGA_NUMBER . "' ";
						QueryDatabase($sql, $results);

				////END OF BASE
				////LINES
						$PartNum1 = $_POST["PartNum1"];
						$PartNum2 = $_POST["PartNum2"];
						$PartNum3 = $_POST["PartNum3"];
						$PartNum4 = $_POST["PartNum4"];
						$PartNum5 = $_POST["PartNum5"];
						$PartNum6 = $_POST["PartNum6"];
						$PartNum7 = $_POST["PartNum7"];
						$PartNum8 = $_POST["PartNum8"];
						$PartNum9 = $_POST["PartNum9"];
						$PartNum10 = $_POST["PartNum10"];

						$TotalReceived1 = $_POST["TotalReceived1"];
						$TotalReceived2 = $_POST["TotalReceived2"];
						$TotalReceived3 = $_POST["TotalReceived3"];
						$TotalReceived4 = $_POST["TotalReceived4"];
						$TotalReceived5 = $_POST["TotalReceived5"];
						$TotalReceived6 = $_POST["TotalReceived6"];
						$TotalReceived7 = $_POST["TotalReceived7"];
						$TotalReceived8 = $_POST["TotalReceived8"];
						$TotalReceived9 = $_POST["TotalReceived9"];
						$TotalReceived10 = $_POST["TotalReceived10"];

						$Price1 = $_POST["Price1"];
						$Price2 = $_POST["Price2"];
						$Price3 = $_POST["Price3"];
						$Price4 = $_POST["Price4"];
						$Price5 = $_POST["Price5"];
						$Price6 = $_POST["Price6"];
						$Price7 = $_POST["Price7"];
						$Price8 = $_POST["Price8"];
						$Price9 = $_POST["Price9"];
						$Price10 = $_POST["Price10"];

						$Total1 = $_POST["Total1"];
						$Total2 = $_POST["Total2"];
						$Total3 = $_POST["Total3"];
						$Total4 = $_POST["Total4"];
						$Total5 = $_POST["Total5"];
						$Total6 = $_POST["Total6"];
						$Total7 = $_POST["Total7"];
						$Total8 = $_POST["Total8"];
						$Total9 = $_POST["Total9"];
						$Total10 = $_POST["Total10"];

						$Desc_of_Comp1 = trim($_POST["Desc_of_Comp1"]);
						$Desc_of_Comp2 = trim($_POST["Desc_of_Comp2"]);
						$Desc_of_Comp3 = trim($_POST["Desc_of_Comp3"]);
						$Desc_of_Comp4 = trim($_POST["Desc_of_Comp4"]);
						$Desc_of_Comp5 = trim($_POST["Desc_of_Comp5"]);
						$Desc_of_Comp6 = trim($_POST["Desc_of_Comp6"]);
						$Desc_of_Comp7 = trim($_POST["Desc_of_Comp7"]);
						$Desc_of_Comp8 = trim($_POST["Desc_of_Comp8"]);
						$Desc_of_Comp9 = trim($_POST["Desc_of_Comp9"]);
						$Desc_of_Comp10 = trim($_POST["Desc_of_Comp10"]);

					for ($y = 1; $y <= 10; $y++) {
						if(${"PartNum".$y} <> '' || ${"TotalReceived".$y} <> '' || ${"Price".$y} <> '' || 
							${"Total".$y} <> '' || ${"Desc_of_Comp".$y} <> ''
						){

							$sql = "Update nsa.RGAV3_LINE". $DB_TEST_FLAG." ";
							$sql .= " SET ";
							$sql .= " ID_ITEM = ";
							if(empty(${"PartNum".$y})){
								${"PartNum".$y} =  "''";
								$sql .= " ".${"PartNum".$y}.", ";
							}else{
								$sql .= " '".${"PartNum".$y}."', ";
							}; 								
							//$sql .= " ".${"PartNum".$y}.", ";
							$sql .= " QTY_RECEIVED = ";
							if(empty(${"TotalReceived".$y})){
							${"TotalReceived".$y} =  " null ";
							$sql .= " ".${"TotalReceived".$y}.", ";
							}else{
							$sql .= " '".${"TotalReceived".$y}."', ";
							};
							$sql .= " PRICE = ";
							if(empty(${"Price".$y})){
							${"Price".$y} =  " null ";
							$sql .= " ".${"Price".$y}.", ";
							}else{
							$sql .= " '".${"Price".$y}."', ";
							};
							$sql .= " TOTAL = ";
							if(empty(${"Total".$y})){
							${"Total".$y} =  " null ";
							$sql .= " ".${"Total".$y}.", ";
							}else{
							$sql .= " '".${"Total".$y}."', ";
							};
							$sql .= " COMPLAINT_DESC = ";	
							if(empty(${"Desc_of_Comp".$y})){
								${"Desc_of_Comp".$y} =  " null ";
								$sql .= " ".${"Desc_of_Comp".$y}." ";
							}else{
								$sql .= " '".${"Desc_of_Comp".$y}."' ";
								};								
							$sql .= " WHERE RGA_NUMBER = '" . $RGA_NUMBER . "' and SEQ_LINE_RGA = '" .$y. "' ";
							QueryDatabase($sql, $results);
						}
					}

				////END OF LINES
				////SHIP
						$Quantity_Return1 = $_POST["Quantity_Return1"];
						$Quantity_Return2 = $_POST["Quantity_Return2"];
						$Quantity_Return3 = $_POST["Quantity_Return3"];
						$Quantity_Return4 = $_POST["Quantity_Return4"];
						$Quantity_Return5 = $_POST["Quantity_Return5"];
						$Quantity_Return6 = $_POST["Quantity_Return6"];
						$Quantity_Return7 = $_POST["Quantity_Return7"];
						$Quantity_Return8 = $_POST["Quantity_Return8"];
						$Quantity_Return9 = $_POST["Quantity_Return9"];
						$Quantity_Return10 = $_POST["Quantity_Return10"];

						$Condition1 = $_POST["Condition1"];
						$Condition2 = $_POST["Condition2"];
						$Condition3 = $_POST["Condition3"];
						$Condition4 = $_POST["Condition4"];
						$Condition5 = $_POST["Condition5"];
						$Condition6 = $_POST["Condition6"];
						$Condition7 = $_POST["Condition7"];
						$Condition8 = $_POST["Condition8"];
						$Condition9 = $_POST["Condition9"];
						$Condition10 = $_POST["Condition10"];

						$Location1 = $_POST["Location1"];
						$Location2 = $_POST["Location2"];
						$Location3 = $_POST["Location3"];
						$Location4 = $_POST["Location4"];
						$Location5 = $_POST["Location5"];
						$Location6 = $_POST["Location6"];
						$Location7 = $_POST["Location7"];
						$Location8 = $_POST["Location8"];
						$Location9 = $_POST["Location9"];
						$Location10 = $_POST["Location10"];

						$Comments_Return1 = $_POST["Comments_Return1"];
						$Comments_Return2 = $_POST["Comments_Return2"];
						$Comments_Return3 = $_POST["Comments_Return3"];
						$Comments_Return4 = $_POST["Comments_Return4"];
						$Comments_Return5 = $_POST["Comments_Return5"];
						$Comments_Return6 = $_POST["Comments_Return6"];
						$Comments_Return7 = $_POST["Comments_Return7"];
						$Comments_Return8 = $_POST["Comments_Return8"];
						$Comments_Return9 = $_POST["Comments_Return9"];
						$Comments_Return10 = $_POST["Comments_Return10"];

						for ($y = 1; $y <= 10; $y++) {
							if(${"Quantity_Return".$y} <> '' || ${"Condition".$y} <> '' || ${"Location".$y} <> '' || 
								${"Comments_Return".$y} <> '' //|| ${"SEQ_LINE_RGA".$y} <> ''
							){

								$sql = "Update nsa.RGAV3_SHIP". $DB_TEST_FLAG." ";
								$sql .= " SET ";
								$sql .= " QTY = "; 								
								if(empty(${"Quantity_Return".$y})){
								${"Quantity_Return".$y} =  " null ";
								$sql .= " ".${"Quantity_Return".$y}.", ";
								}else{
								$sql .= " '".${"Quantity_Return".$y}."', ";
								};
								$sql .= " CONDITION = ";
								if(empty(${"Condition".$y})){
								${"Condition".$y} =  " null ";
								$sql .= " ".${"Condition".$y}.", ";
								}else{
								$sql .= " '".${"Condition".$y}."', ";
								};
								$sql .= " LOCATION = ";
								if(empty(${"Location".$y})){
								${"Location".$y} =  " null ";
								$sql .= " ".${"Location".$y}.", ";
								}else{
								$sql .= " '".${"Location".$y}."', ";
								};
								$sql .= " COMMENTS = ";
								if(empty(${"Comments_Return".$y})){
								${"Comments_Return".$y} =  " null ";
								$sql .= " ".${"Comments_Return".$y}." ";
								}else{
								$sql .= " '".${"Comments_Return".$y}."' ";
								};							
								$sql .= " WHERE RGA_NUMBER = '" . $RGA_NUMBER . "' and SEQ_SHIP_RGA = '" .$y. "' ";
								QueryDatabase($sql, $results);
							}
						}


				////END OF SHIP
				////ISO

						$Error1 			= trim($_POST["Error1"]);
						$Error_Type 		= trim($_POST["Error_Type"]);
						$TeamInd 			= trim($_POST["TeamInd"]);
						$Inspector 			= trim($_POST["Inspector"]);
						$InvestNotes 		= trim($_POST["InvestNotes"]);
						$CarNum 			= trim($_POST["CarNum"]);
						$FinalCost 			= trim($_POST["FinalCost"]);
						$CustomerStatus 	= trim($_POST["CustomerStatus"]);
						$ISOStatus 			= trim($_POST["ISOStatus"]);
						$EmailAdd 			= trim($_POST["EmailAdd"]);
						$Department 		= trim($_POST["Department"]);


						$sql = "Update nsa.RGAV3_ISO". $DB_TEST_FLAG." ";
						$sql .= " SET ";
						$sql .= " ERROR = '" . $Error1 . "', ";
						$sql .= " ERROR_TYPE = ";
						if(empty($Error_Type)){
							$Error_Type =  " null ";
							$sql .= " ".$Error_Type.", ";
						}else{
							$sql .= " '".$Error_Type."', ";
						}
						$sql .= " INSPECTOR = ";
						if(empty($Inspector)){
							$Inspector =  " null ";
							$sql .= " ".$Inspector.", ";
						}else{
							$sql .= " '".$Inspector."', ";
						}
						$sql .= " CAR_NUM = ";
						if(empty($CarNum)){
							$CarNum =  " null ";
							$sql .= " ".$CarNum.", ";
						}else{
							$sql .= " '".$CarNum."', ";
						}
						$sql .= " EMAIL_ADD = ";
						if(empty($EmailAdd)){
							$EmailAdd =  " null ";
							$sql .= " ".$EmailAdd.", ";
						}else{
							$sql .= " '".$EmailAdd."', ";
						}
						$sql .= " DEPARTMENT = ";
						if(empty($Department)){
							$Department =  " null ";
							$sql .= " ".$Department.", ";
						}else{
							$sql .= " '".$Department."',";
						}
						$sql .= " TEAM = ";
						if(empty($TeamInd)){
							$TeamInd =  " null ";
							$sql .= " ".$TeamInd.", ";
						}else{
							$sql .= " '".$TeamInd."', ";
						}
						$sql .= " INVESTIGATION_NOTES = ";
						if(empty($InvestNotes)){
							$InvestNotes =  " null ";
							$sql .= " ".$InvestNotes.", ";
						}else{
							$sql .= " '".$InvestNotes."', ";
						}
						$sql .= " RGA_COST = ";
						if(empty($FinalCost)){
							$FinalCost =  "0";
							$sql .= " '".$FinalCost."', ";
						}else{
							$sql .= " '".$FinalCost."', ";
						}
						$sql .= " ISO_STATUS = '" . $ISOStatus . "', ";
						$sql .= " CUST_STATUS = '" . $CustomerStatus . "' ";
						$sql .= " WHERE RGA_NUMBER = '". $RGA_NUMBER ."' ";
						QueryDatabase($sql, $results);


				////END OF ISO		


						/////////////////////////////////////////////////////
						////////////////////Email Attempt////////////////////
						/////////////////////////////////////////////////////
										

						if ($FLAG_EMAIL_SENT == 'Y') {
							if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
								$head = array(
								'to'      =>array(''.$EmailAdd.'' =>'Josh Wallace'),
								'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
								//'cc'      =>array('jwallace@thinknsa.com'=>$row['NAME_EMP']),
								//'bcc'     =>array('email4@email.net'=>'Admin'),
							);
							} else {
							$head = array(
								//'to'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy'),
								//'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
								//'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
								//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
							);
							}
							$subject = "Updated RGA " . $RGA_NUMBER . " - " . $customerName;
							$body = "RGA form #" . $RGA_NUMBER . " has been updated.";
							mail::send($head,$subject,$body);

							//$sql1  = " UPDATE nsa.RGAV3_BASE" . $DB_TEST_FLAG . " ";
							//$sql1 .= " SET FLAG_EMAIL_SENT = 'T' ";
							//$sql1 .= " WHERE RGA_NUMBER = '". $RGA_NUMBER ."'";
							//QueryDatabase($sql1, $results1);
							//echo "Message has been sent";;

					}

			break;

		}///end switch

			echo json_encode(array("returnValue"=> $ret));
		}
	}

		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
		
	
	}//end show page else

?>