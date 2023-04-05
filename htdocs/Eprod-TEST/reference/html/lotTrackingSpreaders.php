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

	PrintHeaderJQ2('Lot And Actual Spread Tracking','default.css','lotTrackingSpreaders.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			print("<body onLoad='doOnLoads()'>");
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<th style='font-size:15pt;' colspan=2>Enter New Record: </th>\n");
			print(" 	</tr>\n");

			for ($y = 1; $y <= 10; $y++) {
				if ($y==1) {
					$style = 'table-row';
					$MarkerFunction = 'searchKeyPress';
				} else {
					$style = 'display:none;';
					$MarkerFunction = 'searchAdditionalMarkerIDs';
				}
				$z = $y+1;
				print("	<tr class='dbdl' rowspan=4  id='tr_marker".$y.".1' style='".$style."'>\n");
				print("		<td colspan=4>Marker Record ".$y."</td>\n");
				print("	</tr>\n");

				print("	<tr class='dbc' id='tr_marker".$y.".2' style='".$style."'>\n");
				print("		<td>Marker I.D.:</td>\n");
				print("		<td><input id='txt_markID".$y."' type='text' size='8' maxlength='8' onkeyup=\"".$MarkerFunction."(event,'txt_spreadL".$y."',this.id);\"></input></td>\n");
				print("	</tr>\n");

				print("	<tr class='dbc' id='tr_marker".$y.".3' style='".$style."'>\n");
				print("		<td>Spread Length (inches):</td>\n");
				print("		<td><input id='txt_spreadL".$y."' type='text' size='8' maxlength='10' onkeypress=\"searchKeyPress(event,'mach_numb');\"></input></td>\n");
				print("	</tr>\n");

				print("	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n");
				print("		<td colspan=4><font style='cursor: hand' title='Add Another Marker ID' onclick=\"showMarkerInputRow('$z','3')\">+Blank</font></td>\n");
				print("	</tr>\n");
			}

			print("		<tr class='dbdl' rowspan='2'>\n");
			print("			<td colspan=2></td>\n");
			print("		</tr>\n");
			print(" 	<tr class='dbc'>\n");
			print(" 		<td>Machine Number: </td>\n");
			print(" 		<td><select id='mach_numb' onkeypress=\"selectChangedNextElement(event,'idBadgeSpreader');\" >");
			print("				<option value=''>Select one</option>");
			print("				<option value='Gerber-1'>1-Gerber</option>");
			print("				<option value='Eastmen-2'>2-Eastmen</option>");
			print("				<option value='Gerber-3'>3-Gerber</option>");
			print("				<option value='Eastmen-4'>4-Eastmen</option>");
			print("			</select></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr class='dbc'>\n");
			print("			<td>Spreader's Badge Number: </td>\n");
			print("			<td><input type='text' id='idBadgeSpreader' onkeyup=\"searchIDBadge(event,'lotNumb');\" size='5' maxlength='4'><font id='fontNameEmp'></font></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr class='dbc'>\n");
			print("			<td style=''>Lot Number(s) ONE PER LINE:</td>\n");
			print("			<td><div id='div_lotNumb'><textarea type='textarea' id='lotNumb' rows='8' cols='30' value='' style='' onkeypress=\"searchLotNum(event);\"></textarea></div></td>\n");
			print(" 	</tr>\n");

			print(" 	<tr id='trSubmit' >\n");
			print("			<th colspan=2><div></br>\n");
			print("				<input type='button' id='btnInsertRecord' style='width: 100px;height: 50px;border-radius: 25px;' value='Submit' onClick='insertNewRecord()'>\n");
			print("				<input type='button' id='btnClearForm' style='width: 100px;height: 50px;border-radius: 25px;' value='Clear' onClick='clearInputs()'></div></th>\n");

			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<th colspan=2></br>Last \n");
			print("				<select id='num_recs' onChange=\"numRecsChange()\">\n");
			print("					<option value='10'>10</option>\n");
			print("					<option value='20'SELECTED>20</option>\n");
			print("					<option value='50'>50</option>\n");
			print("					<option value='100'>100</option>\n");
			print("					<option value='200'>200</option>\n");
			print("					<option value='500'>500</option>\n");
			print("					<option value='1000'>1000</option>\n");
			print("					<option value='2000'>2000</option>\n");
			print("					<option value='5000'>5000</option>\n");
			print("					<option value='8000'>8000</option>\n");
			print("					<option value='10000'>10000</option>\n");
			print("				</select> Records \n");
			print("			</th>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
			print("</body>");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

?>