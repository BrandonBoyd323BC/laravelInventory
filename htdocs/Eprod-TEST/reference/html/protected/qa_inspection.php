<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
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
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
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
			print(" 		<td style='font-size:28pt;'>Date: </td>\n");
			print(" 		<td>\n");
			$prevTS = strtotime("-1 days", time());

			$myCalendar = new tc_calendar('dw', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',time()), date('m',time()), date('Y',time()));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			//print(" 		</td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td style='font-size:28pt;'>Inspection Type*: </td>\n");
			print(" 		<td><select id='insp_type' onChange='hideElements()' style='height:50px;width:500px;font-size:28pt;'>");
			print("				<option value=''>--Select</option>");
			print("				<option value='First Item'>1st Item</option>");
			print("				<option value='Random'>Random</option>");
			print("				<option value='Stock Sample'>Stock Sample</option>");
			//print("				<option value='Sample'>Sample</option>");
			//print("				<option value='First Time Order'>1st Time Order</option>");
			print("				<option value='100%'>100%</option>");
			print("				<option value='Shipping'>Shipping</option>");
			print("			</select></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr id='soRow'>\n");
			print("		<td id='label_soNumber' style='font-size:28pt;'>SO Number: </td>\n");
			print("		<td><input type='text' maxlength='9' id='soNumber' onkeyup=\"nextOnDash('soNumber','soNumber_suffix')\" style='height:50px;width:400px;font-size:28pt;'>\n");
			print(" 	<font id='label_soNumber_suffix' style='font-size:28pt;'>-</font>");
			print("		<input type='text' id='soNumber_suffix' maxlength='4' onkeyup=\"getSoInfo('soNumber','soNumber_suffix')\" maxlength=3 style='height:50px;width:80px;font-size:28pt;'>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id ='label_itemNumber' style='font-size:28pt;'>Item Number*: </td>\n");
			print("		<td><div id='div_itemNumber'><input id='txt_ID_ITEM_PAR' maxlength='30' style='height:50px;width:500px;font-size:28pt;'></input></div>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td style='font-size:28pt;'>Order Number: </td>\n");
			print("		<td><div id='div_orderNumber'><input id='txt_ID_ORD' onkeyup=\"getOrdInfo('ordNumber')\" maxlength='9' type='text' style='height:50px;width:500px;font-size:28pt;'></div>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td style='font-size:28pt;'>Customer (ship to): </td>\n");
			print("		<td><div id='div_nameCust'><input id='txt_NAME_CUST' type='text' maxlength='30' style='height:50px;width:500px;font-size:28pt;'></div>\n");
			print(" 	</tr>\n");

			
			print("		<tr>\n");
			print("		<td><input id='passFail_P' name='passFail' type='radio' value='P' style='width: 3em;height: 3em;'><label id='label_passFail_P' style='font-size:28pt;'>Pass</label></td>\n");
			print("		<td><input id='passFail_F' name='passFail' type='radio' value='F' style='width: 3em;height: 3em;'><label id='label_passFail_F' style='font-size:28pt;'>Fail</label></td>\n");
			print("		</tr>\n");

			//100% pass/fail box
			print("		<tr>\n");
			print("		<td><label id='label_100_pass' maxlength='4' style='font-size:28pt;display:none;'># Pass  </label><input id='pass100' type='text' value='' style='height:50px;width:250px;font-size:28pt;display:none;' ></td>\n");
			print("		<td><label id='label_100_fail' maxlength='4' style='font-size:28pt;display:none;'># Fail  </label><input id='fail100' type='text' value='' style='height:50px;width:250px;font-size:28pt;display:none;' ></td>\n");
			print("		</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_noLines' style='font-size:28pt;display:none;'>Number of Lines: </td>\n");//unhide when shipping is selected
			print("		<td><input type='text' id='noLines' maxlength='3' style='height:50px;width:500px;font-size:28pt;display:none;'>\n");//unhide when shipping is selected
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td style='font-size:28pt;'>QA Notes: </td>\n");
			print("		<td><textarea type='textarea' id='descText' rows='4' cols='40' value='' style='font-size:20pt'></textarea>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_prodDevSig' style='font-size:28pt;display:none;'>R&D Signature: </td>\n");//unhide when 1st/sample is selected
			print("		<td><input type='text' id='prodDevSig' maxlength='3' value='' style='height:50px;width:500px;font-size:28pt;display:none;'>\n");//unhide when 1st/sample is selected
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td id='label_prodDevNotes' style='font-size:28pt;display:none;'>R&D Notes: </td>\n");//unhide when 1st/sample is selected
			print("		<td><textarea  id='prodDevNotes' rows='4' cols='40' value='' style='font-size:20pt;display:none;'></textarea>\n");//unhide when 1st/sample is selected
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td style='font-size:28pt;'>Team Badge: </td>\n");
			print("		<td><input type='text' id='teamBadge' maxlength='9' value='' style='height:50px;width:500px;font-size:28pt;'>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print("		<td style='font-size:28pt;'>Inspector Initials*: </td>\n");
			print("		<td><input id='inspecInitals' type='text' maxlength='4' value='". $UserRow['ID_USER'] ."' style='height:50px;width:500px;font-size:28pt;'>\n");
			print(" 	</tr>\n");

			print(" 	<tr id='trWI' style='display:none'>\n");//unhide when WI scanned
			print("		<td style='font-size:28pt;'>Work Instructions: </td>\n");
			print("		<td style='font-size:28pt;'><div id='div_WiLink'></div></td>\n");
			print(" 	</tr>\n");

			//<a href='file://///fs1/NETSHARE/Work%20Instructions/Trad/C22CW----161.ppt' target='_blank'>

			print(" 	<tr id='trLS' style='display:none'>\n");
			print("		<td style='font-size:28pt;'>Label Sheet: </td>\n");
			$filename = "ISO9001_PUBLIC/Work%20Instructions/WI-7.5.1-07%20Secondary%20Materials%20Label%20Information.xls";

			//print("		<td style='font-size:28pt;'><div id='div_LabelLink'><a href='file://///fs1/NETSHARE/ISO9001/PUBLIC/Work%20Instructions/WI-7.5.1-07%20Secondary%20Materials%20Label%20Information.xls' target='_blank'><img src='../images/label info.jpg' style='width:128px;height:128px;'></a></div></td>\n");

			print("		<td style='font-size:28pt;'><div id='div_LabelLink'><a href='".$filename."' target='_blank'><img src='../images/label info.jpg' style='width:128px;height:128px;'></a></div></td>\n");

			print(" 	</tr>\n");

			print(" 	<tr id='trSubmit' >\n");
			print("		<th colspan=2><div></br></br><input type='button' style='width: 200px;height: 100px;border-radius: 25px;' value='Submit' onClick='insertNewRecord()' id='btnInsertRecord'></div></th>\n");
			print(" 	</tr>\n");

			print(" </table>\n");
			print(" </form>\n");

			print("<body onLoad='doOnLoads()'>");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Show <select id='user_recs' onChange=\"numRecsChange()\">\n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			
			$sql  = "select distinct qa.ID_USER_ADD as ID_USER2, ";
			$sql .= " wa.NAME_EMP ";
			$sql .= " from nsa.QA_LOG qa ";
			$sql .= " left join nsa.DCWEB_AUTH wa ";
			$sql .= " on qa.ID_USER_ADD = wa.ID_USER ";
			$sql .= " where FLAG_DEL = '' ";
			$sql .= " order by ID_USER2 ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				if ($UserRow['ID_USER'] == $row['ID_USER2']) {
					$SELECTED = 'SELECTED';
				} else {
					$SELECTED = '';
				}
				print("					<option value='" . $row['ID_USER2'] . "' " . $SELECTED . ">" . $row['NAME_EMP'] . "</option>\n");
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
			print("				<option value='First Item'>First Item</option>\n");
			print("				<option value='Random'>Random</option>\n");
			print("				<option value='Stock Sample'>Stock Sample</option>\n");
			print("				<option value='100%'>100%</option>\n");
			print("				<option value='Shipping'>Shipping</option>\n");
			print("			</select></th>\n");
			print(" 		<th colspan=2>Order #:<input id='searchOrd' name='searchOrd' size='4' type='text' value='ALL' onblur=\"numRecsChange()\"></input></th>\n");
			print("			<th colspan=2>Pass/Fail: <select id='searchPF' onChange=\"numRecsChange()\">\n");
			print("				<option value='ALL'>ALL</option>\n");
			print("				<option value='P'>Pass</option>\n");
			print("				<option value='F'>Fail</option>\n");
			print("			</select></th>\n");
			print(" 		<th colspan=2>Team Badge:<input id='searchTeam' name='searchTeam' size='4' type='text' value='ALL' onblur=\"numRecsChange()\"></input></th>\n");
			print(" 		<th colspan=2>SO Lookup:<input id='searchSO' name='searchSO' type='text' value='ALL' onblur=\"numRecsChange()\"></input></th>\n");

			if(strpos($UserRow['USER_NAME'], "iso") !== FALSE){
				//print(" 	<tr>\n");
				$today = date("m-d-y");
				$yesterday  = date( "m-d-y", strtotime("today -1 Weekday") );

				print(" 		<th colspan=2>Date: <input id='searchDate' name='searchDate' size='8' type='text' value='" . $yesterday ."' onblur=\"numRecsChange()\"></input></th>\n");
								
			}//end if

			if(strpos($UserRow['USER_NAME'], "iso") == FALSE){
				$today = date("m-d-y");
				$yesterday  = date( "m-d-y", strtotime("today -1 Weekday") );
				print(" 		<th style='display:none;' colspan=2>Date: <input id='searchDate' name='searchDate' size='8' type='text' value=''></input></th>\n");
			}//end if
			print(" 	</tr>\n");

			print(" </table>\n");
			print(" </form>\n");
/********************
			print("		<tr id='tr_so1'>\n");
			print(" 		<td>Shop Order: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so1' type=text onkeyup=\"nextOnDash('so1','sufx_so1')\" maxlength=9 size=10 autofocus tabindex=1> -\n");
			print("				<input id='sufx_so1' type=text onkeyup=\"getSoFabCodes('so1','sufx_so1')\" maxlength=3 size=4 tabindex=2>\n");
			print("				<font onclick=\"showSoInputRow('tr_so2')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");

			for ($i=2; $i<=5; $i++) {
				print("		<tr id='tr_so".$i."' style='display:none;'>\n");
				print(" 		<td>Shop Order ".$i.": </td>\n");
				print(" 		<td>\n");
				print("				<input id='so".$i."' type=text onkeyup=\"nextOnDash('so".$i."','sufx_so".$i."')\" maxlength=9 size=10 tabindex=".($i+1)."> -\n");
				print("				<input id='sufx_so".$i."' type=text maxlength=3 size=4>\n");
				print("				<font onclick=\"showSoInputRow('tr_so".($i+1)."')\">+</font>\n");
				print("			</td>\n");
				print(" 	</tr>\n");
			}

			print("		<tr id='tr_so2' style='display:none;'>\n");
			print(" 		<td>Shop Order 2: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so2' type=text onkeyup=\"nextOnDash('so2','sufx_so2')\" maxlength=9 size=10 tabindex=5> -\n");
			print("				<input id='sufx_so2' type=text maxlength=3 size=4 tabindex=6>\n");
			print("				<font onclick=\"showSoInputRow('tr_so3')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so3' style='display:none;'>\n");
			print(" 		<td>Shop Order 3: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so3' type=text onkeyup=\"nextOnDash('so3','sufx_so3')\" maxlength=9 size=10 tabindex=7> -\n");
			print("				<input id='sufx_so3' type=text maxlength=3 size=4 tabindex=8>\n");
			print("				<font onclick=\"showSoInputRow('tr_so4')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so4' style='display:none;'>\n");
			print(" 		<td>Shop Order 4: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so4' type=text onkeyup=\"nextOnDash('so4','sufx_so4')\" maxlength=9 size=10 tabindex=9> -\n");
			print("				<input id='sufx_so4' type=text maxlength=3 size=4 tabindex=10>\n");
			print("				<font onclick=\"showSoInputRow('tr_so5')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so5' style='display:none;'>\n");
			print(" 		<td>Shop Order 5: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so5' type=text onkeyup=\"nextOnDash('so5','sufx_so5')\" maxlength=9 size=10 tabindex=11> -\n");
			print("				<input id='sufx_so5' type=text maxlength=3 size=4 tabindex=12>\n");
			print("				<font onclick=\"showSoInputRow('tr_so6')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so6' style='display:none;'>\n");
			print(" 		<td>Shop Order 6: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so6' type=text onkeyup=\"nextOnDash('so6','sufx_so6')\" maxlength=9 size=10 tabindex=12> -\n");
			print("				<input id='sufx_so6' type=text maxlength=3 size=4 tabindex=13>\n");
			print("				<font onclick=\"showSoInputRow('tr_so7')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so7' style='display:none;'>\n");
			print(" 		<td>Shop Order 7: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so7' type=text onkeyup=\"nextOnDash('so7','sufx_so7')\" maxlength=9 size=10 tabindex=14> -\n");
			print("				<input id='sufx_so7' type=text maxlength=3 size=4 tabindex=15>\n");
			print("				<font onclick=\"showSoInputRow('tr_so8')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so8' style='display:none;'>\n");
			print(" 		<td>Shop Order 8: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so8' type=text onkeyup=\"nextOnDash('so8','sufx_so8')\" maxlength=9 size=10 tabindex=16> -\n");
			print("				<input id='sufx_so8' type=text maxlength=3 size=4 tabindex=17>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Marker Name: </td>\n");
			print(" 		<td><input id='marker_name' type=text tabindex=19></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Shop Order Fabric Code: </td>\n");
			print(" 		<td><select id='so_fab_code' onChange=\"soFabCodeChange()\" tabindex=20></select></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Shop Order Length: </td>\n");
			print(" 		<td><div id='div_so_length'><input id='so_length' type=text  tabindex=21></div></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Marker Fabric Code: </td>\n");
			print(" 		<td><div id='div_marker_fab_code'><input id='marker_fab_code' type=text onkeyup=\"markerFabCodeChange()\" tabindex=23></div></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Marker Utilization: </td>\n");
			print(" 		<td><input id='marker_util' type=text size=5 tabindex=24> %</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Marker Length: </td>\n");
			print(" 		<td><input id='marker_length_y' type=text size=5 tabindex=25> yd(s) <input id='marker_length_in' type=text size=5 tabindex=26> in(s) </td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td># of Layers: </td>\n");
			print(" 		<td><input id='num_layers' type=text size=5 tabindex=27></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Recut: </td>\n");
			print(" 		<td><input id='flag_recut' type=checkbox tabindex=29 onclick=\"recutCheckBoxChange('tr_probcode')\"></td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_probcode' style='display:none;'>\n");
			print(" 		<td>Problem Code: </td>\n");
			print(" 		<td>\n");
			print(" 			<select name='prob_code' id='prob_code' tabindex=29>\n");
			print("					<option value=''> -- Select -- </option>\n");

			$sql =  "select ";
			$sql .= "  PROB_CODE, ";
			$sql .= "  DESCR ";
			$sql .= " from ";
			$sql .= "  nsa.MU_PROBLEM_CODES ";
			$sql .= " order by PROB_CODE asc";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				print("					<option value='" . $row['PROB_CODE'] . "'>" . $row['PROB_CODE'] . " - " . $row['DESCR'] . "</option>\n");
			}
			print("				</select>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_badgenum' style='display:none;'>\n");
			print(" 		<td>Badge Number: </td>\n");
			print(" 		<td><div id='div_badge_num'><input name='badge_num' id='badge_num' type=text maxlength=4 size=5 tabindex=30></div></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Marker PDF: </td>\n");
			print(" 		<td><input id='fileToUpload' type='file'></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print("			<td><input type='button' value='Upload File' onClick=\"uploadFile()\"></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print("			<td>\n");
			print("				<progress id='progressBar' value='0' max='100'></progress>\n");
			print("				<h4 id='status'><input id='ret_FileName' type='hidden' value=''></input></h4>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" 	<tr></tr>\n");
			print(" 	<tr>\n");
			print(" 		<td></td>\n");
			print(" 		<td><INPUT id='dw_submit' type='button' value='Add Record' onClick=\"sendAddValue()\" tabindex=31></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" </form>\n");
			print("<body onLoad='doOnLoads()'>");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Show <select id='user_recs' onChange=\"numRecsChange()\">\n");
			print("					<option value='--ALL--'>--ALL--</option>\n");
			
			$sql  = "select distinct mm.ID_USER_ADD as ID_USER2, ";
			$sql .= " wa.NAME_EMP ";
			$sql .= " from nsa.MU_MARKER_LOG mm ";
			$sql .= " left join nsa.DCWEB_AUTH wa ";
			$sql .= " on mm.ID_USER_ADD = wa.ID_USER ";
			$sql .= " where FLAG_DEL = '' ";
			$sql .= " order by ID_USER2 ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				if ($UserRow['ID_USER'] == $row['ID_USER2']) {
					$SELECTED = 'SELECTED';
				} else {
					$SELECTED = '';
				}
				print("					<option value='" . $row['ID_USER2'] . "' " . $SELECTED . ">" . $row['NAME_EMP'] . "</option>\n");
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
			print("			</select> Records: </th>\n");
			print(" 		<th colspan=2>SO Lookup<input id='searchSO' name='searchSO' type='text' value='ALL' onblur=\"numRecsChange()\"></input><th>\n");
			print(" 	</tr>\n");
*******/
			//print(" </table>\n");
			//print(" </form>\n");
			print(" <div id='dataDiv'>\n");
			print(" 	<br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" </div>\n");
			if(strpos($UserRow['USER_NAME'], "iso") !== FALSE){
				$today = date("m-d-y");
				$yesterday  = date( "m-d-y", strtotime("today -1 Weekday") );

				$sql1 = " select * ";
				$sql1 .= " from nsa.DCAPPROVALS ";
				$sql1 .= " where CODE_APP = 'QAR'";
				$sql1 .= " and convert(date,DATE_APP,101) = '" . $today ."' ";
				QueryDatabase($sql1, $results);
				if(mssql_num_rows($results) > 0){
					print("	Log Already Approved Today! ");
				}
				else{
					print("  <input type='button' value='Approve' onClick='approveDayInspection()' id='btnApproveDayInspection'>");
				}
			}//end if
			//print(" <div id='dataDiv'></div>\n");
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