<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("protected/procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2('QA Inspection Log','default.css','qa_inspection.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			//$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			//print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
			print(" <form id='SO_Log_Input_Form' method='post' enctype='multipart/form-data'>\n");			
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Enter New Record: </th>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>\n");
			print("			<br/><button type='button' id='btnClearInput' align='center' onClick='clearInputs()'>Clear All Fields</button><br/>");
			print(" 		</th>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			/*print(" 		<td style='font-size:28pt;'>Date: </td>\n");
			print(" 		<td>\n");
			$prevTS = strtotime("-1 days", time());

			$myCalendar = new tc_calendar('dw', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',time()), date('m',time()), date('Y',time()));
			//$myCalendar->setPath("/protected");
			$myCalendar->setPath("/");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			//print(" 		</td>\n");
			print(" 	</tr>\n");*/

			print(" 	<tr>\n");
			print(" 		<td >Inspection Type*: </td>\n");
			print(" 		<td><select id='insp_type' onChange='hideElements()' >");
			print("				<option value=''>--Select--</option>");
			print("				<option value='First Item'>1st Item</option>");
			print("				<option value='Internal Error'>Internal Error</option>");
			//print("				<option value='Random'>Random</option>");
			print("				<option value='Random or ASQ'>Random or ASQ</option>");
			print("				<option value='ASQ FC'>ASQ Level Inspection FC</option>");
			print("				<option value='Stock Sample'>Stock Sample</option>");
			//print("				<option value='Sample'>Sample</option>");
			//print("				<option value='First Time Order'>1st Time Order</option>");
			//print("				<option value='100%'>100%</option>");
			//print("				<option value='Shipping'>Shipping</option>");
			print("				<option value='Reassigned'>Reassigned</option>");
			print("				<option value='Markers and Fabric'>Markers and Fabric</option>");
			print("				<option value='Markers'>Markers</option>");
			print("				<option value='Fabric'>Fabric</option>");
			print("				<option value='Labels'>Labels</option>");
			print("				<option value='Logo'>Logo</option>");
			print("				<option value='Gore'>Gore Inspection</option>");
			print("				<option value='Gore Water Test'>Gore Water Test</option>");
			print("				<option value='R&D'>R&D Inspection</option>");
			print("				<option value='Special Packaging'>Special Packaging</option>");
			print("				<option value='OrderPrep Components'>OrderPrep Components</option>");
			print("				<option value='PreSew Applications'>PreSew Applications</option>");
			print("			</select></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr id='soRow'>\n");
			print("		<td id='label_soNumber' >SO Number: </td>\n");
			print("		<td><input type='text' maxlength='9' id='soNumber' onkeyup=\"nextOnDash('soNumber','soNumber_suffix')\" >\n");
			print(" 	<font id='label_soNumber_suffix' style=''>-</font>");
			print("		<input type='text' id='soNumber_suffix' maxlength='4' onkeyup=\"getSoInfo('soNumber','soNumber_suffix')\" maxlength=3 style='width:60px;'>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_orderNumber'>Order Number: </td>\n");
			print("		<td><div id='div_orderNumber'><input id='txt_ID_ORD' onkeyup=\"getOrdInfo('ordNumber')\" maxlength='9' type='text' ></div>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id ='label_itemNumber' >Item Number*: </td>\n");
			print("		<td><div id='div_itemNumber'><input id='txt_ID_ITEM_PAR' maxlength='30' ></div>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_nameCust'>Customer (Ship To): </td>\n");
			print("		<td><div id='div_nameCust'><input id='txt_NAME_CUST' type='text' maxlength='30' ></div>\n");
			print(" 	</tr>\n");

			
			print("		<tr>\n");
			print("		<td id='label_passFail_P'><input id='passFail_P' name='passFail' type='radio' value='P' style='width: 1.5em;height: 1.5em;'><label id='label_passFail_P' >Pass</label></td>\n");
			print("		<td id='label_passFail_F'><input id='passFail_F' name='passFail' type='radio' value='F' style='width: 1.5em;height: 1.5em;'><label id='label_passFail_F' >Fail</label></td>\n");
			print("		</tr>\n");

			//100% pass/fail box
			print("		<tr>\n");
			print("		<td><label id='label_100_pass' maxlength='4' style='display:none;'># Pass  </label><input id='pass100' type='text' value='' style='display:none;' ></td>\n");
			print("		<td><label id='label_100_fail' maxlength='4' style='display:none;'># Fail  </label><input id='fail100' type='text' value='' style='display:none;' ></td>\n");
			print("		</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_qtyInspected' style='display:none;'>Qty Inspected: </td>\n");//unhide when random is selected
			print("		<td><input type='text' id='txt_qtyInspected' maxlength='4' style='display:none;'>\n");//unhide when random is selected
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_noLines' style='display:none;'>Number of Lines: </td>\n");//unhide when shipping is selected
			print("		<td><input type='text' id='noLines' maxlength='3' style='display:none;'>\n");//unhide when shipping is selected
			print(" 	</tr>\n");



			print(" 	<tr>\n");
			print(" 		<td id='label_probCode'>Problem Code: </td>\n");
			print(" 		<td><select id='sel_probCode'>");
			print("				<option value=''>--Select--</option>");
			$sql  = "SELECT PROB_CODE, DESCR ";
			$sql .= " FROM nsa.QA_PROBLEM_CODES ";
			$sql .= " ORDER BY cast(PROB_CODE as int) asc";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("				<option value='".$row['PROB_CODE']."'>".$row['PROB_CODE']." - ".$row['DESCR']."</option>\n");
			}
			print("			</select></td>\n");
			print(" 	</tr>\n");


			print(" 	<tr>\n");
			print(" 		<td id='label_stdComment'>Standard Comment: </td>\n");
			print(" 		<td><select id='sel_stdComment'>");
			print("				<option value=''>--Select--</option>");
			$sql  = "SELECT COMMENT_CODE, COMMENT ";
			$sql .= " FROM nsa.QA_STANDARD_COMMENTS ";
			$sql .= " ORDER BY cast(COMMENT_CODE as int) asc";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("				<option value='".$row['COMMENT_CODE']."'>".$row['COMMENT_CODE']." - ".$row['COMMENT']."</option>\n");
			}
			print("			</select></td>\n");
			print(" 	</tr>\n");



			print(" 	<tr>\n");
			print("		<td style=''>QA Notes: </td>\n");
			print("		<td><textarea type='textarea' id='descText' rows='4' cols='40' value='' style=''></textarea>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_prodDevSig' style='display:none;'>RnD Signature: </td>\n");//unhide when 1st/sample is selected
			print("		<td><input type='text' id='prodDevSig' maxlength='3' value='' style='display:none;'>\n");//unhide when 1st/sample is selected
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_prodDevNotes' style=';display:none;'>RnD Notes: </td>\n");//unhide when 1st/sample is selected
			print("		<td><textarea  id='prodDevNotes' rows='4' cols='40' value='' style=';display:none;'></textarea>\n");//unhide when 1st/sample is selected
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_teamBadge'>Team Badge: </td>\n");
			print("		<td><input type='text' id='teamBadge' maxlength='9' value='' style=''>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td style=''>Inspector Initials*: </td>\n");
			print("		<td><input id='inspecInitals' type='text' maxlength='4' value='' style=''>\n");
			print(" 	</tr>\n");

			print(" 	<tr id='trWI' style='display:none'>\n");//unhide when WI scanned
			print("		<td style=''>Work Instructions: </td>\n");
			print("		<td style=''><div id='div_WiLink'></div></td>\n");
			print(" 	</tr>\n");

			//<a href='file://///fs1/NETSHARE/Work%20Instructions/Trad/C22CW----161.ppt' target='_blank'>

			//print(" 	<tr id='trLS' style='display:none'>\n");
			//print("		<td style='font-size:28pt;'>Label Sheet: </td>\n");
			//$filename = "ISO9001_PUBLIC/Work%20Instructions/WI-7.5.1-07%20Secondary%20Materials%20Label%20Information.xls";

			//print("		<td style='font-size:28pt;'><div id='div_LabelLink'><a href='file://///fs1/NETSHARE/ISO9001/PUBLIC/Work%20Instructions/WI-7.5.1-07%20Secondary%20Materials%20Label%20Information.xls' target='_blank'><img src='../images/label info.jpg' style='width:128px;height:128px;'></a></div></td>\n");

			//print("		<td style='font-size:28pt;'><div id='div_LabelLink'><a href='".$filename."' target='_blank'><img src='../images/label info.jpg' style='width:128px;height:128px;'></a></div></td>\n");

			//print(" 	</tr>\n");

			print(" 	<tr id='trSubmit' >\n");
			print("		<th colspan=2><div></br></br><input type='button' style='width: 100px;height: 50px;border-radius: 25px;' value='Submit' onClick='insertNewRecord()' id='btnInsertRecord'></div></th>\n");
			print(" 	</tr>\n");

			print(" </table>\n");
			print(" </form>\n");

			print("<body onLoad='doOnLoads()'>");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Show Inspector<select id='user_recs' onChange=\"numRecsChange()\">\n");
			print("					<option value='--ALL--'>--ALL--</option>\n");

			$sql  = "select distinct qa.INSP_INITIALS as ID_USER2 ";
			$sql .= " from nsa.QA_LOG" . $DB_TEST_FLAG . " qa ";
			$sql .= " where FLAG_DEL = '' ";
			$sql .= " order by ID_USER2 ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				/*if ($UserRow['ID_USER'] == $row['ID_USER2']) {
					$SELECTED = 'SELECTED';
				} else {
					$SELECTED = '';
				}*/
				$SELECTED = '';
				print("					<option value='" . $row['ID_USER2'] . "' " . $SELECTED . ">" . $row['ID_USER2'] . "</option>\n");
			}

			print("			</select>'s </th>\n");
			print(" 		<th colspan=2>Last <select id='num_recs' onChange=\"numRecsChange()\">\n");
			print("				<option value='10'>10</option>\n");
			print("				<option value='20' SELECTED>20</option>\n");
			print("				<option value='50'>50</option>\n");
			print("				<option value='100'>100</option>\n");
			print("				<option value='200'>200</option>\n");
			print("				<option value='500'>500</option>\n");
			print("				<option value='1000'>1000</option>\n");
			print("				<option value='2000'>2000</option>\n");
			print("				<option value='5000'>5000</option>\n");
			print("				<option value='8000'>8000</option>\n");
			print("				<option value='10000'>10000</option>\n");
			print("			</select></th>\n");
			print("			<th colspan=2>Inspection Type: <select id='searchInspType' onChange=\"numRecsChange()\">\n");
			print("				<option value='ALL'>ALL</option>\n");
			print("				<option value='First Item'>1st Item</option>");
			print("				<option value='Internal Error'>Internal Error</option>");
			print("				<option value='Random or ASQ'>Random or ASQ</option>");
			print("				<option value='ASQ FC'>ASQ Level Inspection FC</option>");
			print("				<option value='Stock Sample'>Stock Sample</option>");
			print("				<option value='Reassigned'>Reassigned</option>");
			print("				<option value='Markers and Fabric'>Markers and Fabric</option>");
			print("				<option value='Markers'>Markers</option>");
			print("				<option value='Fabric'>Fabric</option>");
			print("				<option value='Labels'>Labels</option>");
			print("				<option value='Logo'>Logo</option>");
			print("				<option value='Gore'>Gore Inspection</option>");
			print("				<option value='Gore Water Test'>Gore Water Test</option>");
			print("				<option value='R&D'>R&D Inspection</option>");
			print("				<option value='Special Packaging'>Special Packaging</option>");
			print("				<option value='OrderPrep Components'>OrderPrep Components</option>");
			print("				<option value='PreSew Applications'>PreSew Applications</option>");
			print("			</select></th>\n");
			print(" 		<th colspan=2>Order #:<input id='searchOrd' name='searchOrd' size='4' type='text' value='ALL' onblur=\"numRecsChange()\"></input></th>\n");
			print("			<th colspan=2>Pass/Fail: <select id='searchPF' onChange=\"numRecsChange()\">\n");
			print("				<option value='ALL'>ALL</option>\n");
			print("				<option value='P'>Pass</option>\n");
			print("				<option value='F'>Fail</option>\n");
			print("			</select></th>\n");
			print(" 		<th colspan=2>Team Badge:<input id='searchTeam' name='searchTeam' size='4' type='text' value='ALL' onblur=\"numRecsChange()\"></input></th>\n");
			print(" 		<th colspan=2>SO Lookup:<input id='searchSO' name='searchSO' type='text' value='ALL' onblur=\"numRecsChange()\"></input></th>\n");

			//if(strpos($UserRow['USER_NAME'], "iso") !== FALSE){
				//print(" 	<tr>\n");
			$today = date("m-d-y");
			$yesterday  = date( "m-d-y", strtotime("today -1 Weekday") );

			print(" 		<th colspan=2>Start Date: <input id='searchStartDate' name='searchStartDate' size='8' type='text' value='" . $today ."' onblur=\"numRecsChange()\"></input></th>\n");
			print(" 		<th colspan=2>End Date: <input id='searchEndDate' name='searchEndDate' size='8' type='text' value='" . $today ."' onblur=\"numRecsChange()\"></input></th>\n");
								
			//}//end if

			print(" 	</tr>\n");

			print(" </table>\n");
			print(" </form>\n");

			print(" <div id='dataDiv'>\n");
			print(" 	<br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" </div>\n");

			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('inspectionLandingPage.php');
?>