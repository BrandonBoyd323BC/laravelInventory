<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2('Marker Log','default.css','markerlog.js');
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
			print(" <form id='MU_Input_Form' method='post' enctype='multipart/form-data'>\n");			
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2>Enter New Records: </th>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Date: </td>\n");
			print(" 		<td>\n");
			$prevTS = strtotime("-1 days", time());

			$myCalendar = new tc_calendar('dw', true, true);
			$myCalendar->setIcon("images/iconCalendar.gif");
			$myCalendar->setDate(date('d',time()), date('m',time()), date('Y',time()));
			$myCalendar->setPath("/protected");
			$myCalendar->setYearInterval(1970, 2030);
			$myCalendar->setAlignment('left', 'bottom');
			$myCalendar->writeScript();

			print(" 		</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_so1'>\n");
			print(" 		<td>Shop Order: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so1' type=text onkeyup=\"nextOnDash('so1','sufx_so1')\" maxlength=9 size=10 autofocus tabindex=1> -\n");
			print("				<input id='sufx_so1' type=text onkeyup=\"getSoFabCodes('so1','sufx_so1')\" maxlength=3 size=4 tabindex=2>\n");
			print("				<font onclick=\"showSoInputRow('tr_so2')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");

/*			for ($i=2; $i<=5; $i++) {
				print("		<tr id='tr_so".$i."' style='display:none;'>\n");
				print(" 		<td>Shop Order ".$i.": </td>\n");
				print(" 		<td>\n");
				print("				<input id='so".$i."' type=text onkeyup=\"nextOnDash('so".$i."','sufx_so".$i."')\" maxlength=9 size=10 tabindex=".($i+1)."> -\n");
				print("				<input id='sufx_so".$i."' type=text maxlength=3 size=4>\n");
				print("				<font onclick=\"showSoInputRow('tr_so".($i+1)."')\">+</font>\n");
				print("			</td>\n");
				print(" 	</tr>\n");
			}
*/
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
			//print(" 		<td><input id='num_layers' type=text size=5 tabindex=27 onfocus=\"checkMarkerLengthCost()\"></td>\n");
			print(" 		<td><input id='num_layers' type=text size=5 tabindex=27 ></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr>\n");
			print(" 		<td>Marker Page: </td>\n");
			print(" 		<td><input id='marker_page_from' type=text size=5 tabindex=28> of <input id='marker_page_to' type=text size=5 tabindex=29></td>\n");
			print(" 	</tr>\n");


			print(" 	<tr>\n");
			print(" 		<td>Recut: </td>\n");
			print(" 		<td><input id='flag_recut' type=checkbox tabindex=30 onclick=\"recutCheckBoxChange('tr_probcode')\"></td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_probcode' style='display:none;'>\n");
			print(" 		<td>Problem Code: </td>\n");
			print(" 		<td>\n");
			print(" 			<select name='prob_code' id='prob_code' tabindex=31>\n");
			print("					<option value=''> -- Select -- </option>\n");

			$sql =  "select ";
			$sql .= "  PROB_CODE, ";
			$sql .= "  DESCR ";
			$sql .= " from ";
			$sql .= "  nsa.MU_PROBLEM_CODES ";
			$sql .= " where PROB_CODE NOT IN ('203', '299', '501', '502', '503', '507', '508', '509', '510', '511', '603', '621', '701', '702', '703', '801', '802', '803', '804', '899')";//Excluding obsolete problem codes per Debbie request -RKB (08-22-16)
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
			print(" 		<td><div id='div_badge_num'><input name='badge_num' id='badge_num' type=text maxlength=4 size=5 tabindex=32></div></td>\n");
			print(" 	</tr>\n");


			print("		<tr id='tr_rc_trim1' style='display:none;'>\n");
			print(" 		<td>Trim Component: </td>\n");
			print(" 		<td>\n");
			print(" 			<select id='trimComp1'></select>\n");
			print("				Length: <input id='trimLength1' type=text maxlength=4 size=4> in(s)\n");
			print("				<font onclick=\"showTrimInputRow('tr_rc_trim2')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_rc_trim2' style='display:none;'>\n");
			print(" 		<td>Trim Component 2: </td>\n");
			print(" 		<td>\n");
			print(" 			<select id='trimComp2'></select>\n");
			print("				Length: <input id='trimLength2' type=text maxlength=4 size=4> in(s)\n");
			print("				<font onclick=\"showTrimInputRow('tr_rc_trim3')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print("		<tr id='tr_rc_trim3' style='display:none;'>\n");
			print(" 		<td>Trim Component 3: </td>\n");
			print(" 		<td>\n");
			print(" 			<select id='trimComp3'></select>\n");
			print("				Length: <input id='trimLength3' type=text maxlength=4 size=4> in(s)\n");
			//print("				<font onclick=\"showTrimInputRow('tr_rc_trim4')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");



			print("		<tr id='tr_comments' style='display:none;'>\n");
			print(" 		<td>Comments: </td>\n");
			print(" 		<td><div id='div_comments'><input name='comments' id='comments' type=textarea rows='7' cols='4' tabindex=31></div></td>\n");
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
			print(" 		<td><INPUT id='dw_submit' type='button' value='Add Record' onClick=\"sendAddValue()\" tabindex=33></td>\n");
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
			print("				<option value='25000'>25000</option>\n");
			print("				<option value='50000'>50000</option>\n");
			print("				<option value='75000'>75000</option>\n");
			print("				<option value='100000'>100000</option>\n");
			print("				<option value='500000'>500000</option>\n");
			print("			</select> Records: </th>\n");
			print(" 		<th colspan=2>SO Lookup ");
			print("				<input id='searchSO' name='searchSO' type='text' value='ALL' onblur=\"numRecsChange()\"></input>");
			print(" 			Include Deleted<input id='checkboxIncludelDelSO' name='checkboxIncludelDelSO' type='checkbox' onChange=\"numRecsChange()\"></input>");
			print("			</th>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'>\n");
			print(" 	</br>\n");
			print(" </div>\n");
			print(" </br>\n");
			print(" </div>\n");
			//print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter('emenu.php');
?>