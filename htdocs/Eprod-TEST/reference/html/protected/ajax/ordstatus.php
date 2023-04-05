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
					if (isset($_POST["show_status"]) && isset($_POST["company_code"]) && isset($_POST["sort_fieldC"]) && isset($_POST["sort_dir_flagC"]))  {

						$sort_fieldC 	=   $_POST["sort_fieldC"];
						$sort_dir_flagC =   $_POST["sort_dir_flagC"];
						$status 		=   $_POST["show_status"];
						$company_code	=   $_POST["company_code"];

						if ($status == 'ALL') {
							$status = '%';
						}

						$sql  = "select" ;
						$sql .= " oh.ID_ORD,";
						$sql .= " oh.ID_CUST_SOLDTO,";
						$sql .= " oh.NAME_CUST,";
						$sql .= " oh.ID_PO_CUST,";
						$sql .= " oh.DATE_ORD,";
						$sql .= " oh.CODE_STAT_ORD,";
						$sql .= " oh.rowid";
						$sql .= " from ".$company_code.".CP_ORDHDR oh";
						$sql .= " where oh.CODE_STAT_ORD LIKE '".$status."' ";
						//$sql .= " ORDER BY rowid"; //. $sort_fieldC . " " . $sort_dir_flagC;
						$sql .= "ORDER BY " . $sort_fieldC . " " . $sort_dir_flagC;
						//error_log($sql);
						QueryDatabase($sql, $results);


						$prevrowId = '';
						$b_flip = true;

				
						$ret .= " <table class ='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='NAME_CUST'       	onClick=\"sortColumnBy(this.id)\">Customer Name</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='ID_ORD'    		onClick=\"sortColumnBy(this.id)\">Order ID</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='ID_CUST_SOLDTO'    onClick=\"sortColumnBy(this.id)\">Customer ID</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='DATE_ORD'          onClick=\"sortColumnBy(this.id)\">Date Ordered</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;' value ='0' id='ID_PO_CUST'        onClick=\"sortColumnBy(this.id)\">Purchase Order ID</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;'  id='RadioB' 			>Change Order Status</th>\n";
						$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;'  id='SaveB' 			>Save Button</th>\n";
									$ret .= " 		<th class='sample' style='cursor:default;background-color:#00BFFF;'  id='SaveB' 			>OK!</th>\n";
						$ret .= " 	</tr>\n";



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
							$ret .= " 		<td class='" . $trClass . "' id='NAME_CUST__" . $row['rowid']."' >" . $row['NAME_CUST'] . "</td>\n";
							$ret .= " 		<td style='text-align: center;'class='" . $trClass . "' id='ID_ORDER__" . $row['rowid']."'  >" . $row['ID_ORD'] . "</td>\n";
							$ret .= " 		<td style='text-align: center;'class='" . $trClass . "' id='ID_CUST_SOLDTO__" . $row['rowid']."'  >" . $row['ID_CUST_SOLDTO'] . "</td>\n";
							$ret .= " 		<td style='text-align: center;'class='" . $trClass . "' id='DATE_ORD__" . $row['rowid']."'  >" . $row['DATE_ORD'] . "</td>\n";
							$ret .= " 		<td style='text-align: center;'class='" . $trClass . "' id='ID_PO_CUST__" . $row['rowid']."'  >" . $row['ID_PO_CUST'] . "</td>\n";
							$ret .= " 		<td style='text-align: center;' class='" . $trClass . "' id='RadioButtons" . $row['rowid']."'  >\n";


							$CheckedA = "";
							$CheckedH = "";
							$CheckedX = "";

							if ($row['CODE_STAT_ORD'] == 'A') {
								$CheckedA = "CHECKED";
							}
							if ($row['CODE_STAT_ORD'] == 'H') {
								$CheckedH = "CHECKED";
							}
							if ($row['CODE_STAT_ORD'] == 'X') {
								$CheckedX = "CHECKED";
							}

							$ret .= " 			<form id='radiostatus' ".$row['rowid']."' onChange= \"activateSaveButton('".$row['rowid']."')\">\n";
							//$ret .= "				<input type='radio' id='r1__".$row['rowid']."'' name='stat__".$row['rowid']."' value='A' onClick=\"rating('');check('')\" >A</input>";
							//$ret .= "				<input type='radio' id='r2__".$row['rowid']."'' name='stat__".$row['rowid']."' value='H' onClick=\"rating('');check('')\" >H</input>";
							//$ret .= "				<input type='radio' id='r3__".$row['rowid']."'' name='stat__".$row['rowid']."' value='X' onClick=\"rating('');check('')\" >X</input>";							
							$ret .= " 				<input type='radio' id='r1__".$row['rowid']."'' name='stat__".$row['rowid']."' value='A' ".$CheckedA.">A</input>\n";
							$ret .= " 				<input type='radio' id='r2__".$row['rowid']."'' name='stat__".$row['rowid']."' value='H' ".$CheckedH.">H</input>\n";
							$ret .= " 				<input type='radio' id='r3__".$row['rowid']."'' name='stat__".$row['rowid']."' value='X' ".$CheckedX.">X</input>\n";
							$ret .= " 			</form>\n";

							$ret .= " 		</td>\n";
							$ret .= " 		<td style='text-align: center;' class='" . $trClass . "' id='tdSaveButton" . $row['rowid']."'  >\n";
							$ret .= "                <input type='button' id='saveButton__".$row['rowid']."' Onclick= \"ConfirmChange('".$row['rowid']."')\" value='Save' disabled align= 'center'></input>\n";
							$ret .= " 		</td>\n";
							$ret .= " 		<td class='" . $trClass . "' id='retVal__" . $row['rowid']."'  ></td>\n";
							$ret .= " 	</tr>\n";
						}
					
						$ret .= " </table>\n";
						$ret .= "<input type=hidden id='sortDirFlag' value='0'>\n";
						$ret .= " </br>\n";
						$ret .= " </br>\n";

					}//end if
				break;

				case "saveRecord";
					if (isset($_POST["company_code"]) && isset($_POST["rowid"]) && isset($_POST["valueSelected"])) {
						$company_code 	=   $_POST["company_code"];
						$rowid 			=   $_POST["rowid"];
						$valueSelected 	=   $_POST["valueSelected"];

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

						$sql  = "UPDATE ".$company_code.".CP_ORDHDR ";
						$sql .= " set CODE_STAT_ORD = '".$valueSelected."' ";
						$sql .= " where rowid = '".$rowid."' ";
						QueryDatabase($sql, $results);

						$ret .= "OK!";

						$sql = "SET ANSI_NULLS OFF";
						QueryDatabase($sql, $results);
						$sql = "SET ANSI_WARNINGS OFF";
						QueryDatabase($sql, $results);
						$sql = "SET QUOTED_IDENTIFIER OFF";
						QueryDatabase($sql, $results);
						$sql = "SET ANSI_PADDING OFF";
						QueryDatabase($sql, $results);

					}
				break;

			}//end switch

			echo json_encode(array("returnValue"=> $ret));
			
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	} 

///////// ENDING TO ALL FILTERED DROP DOWNS. //////////////
	?>