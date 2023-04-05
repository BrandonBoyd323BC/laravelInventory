<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ2('Pull from Stock Request','default.css','stkreq.js');
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
			print(" 		<th colspan=2>Enter Request: </th>\n");
			print(" 	</tr>\n");
/*			
			print("		<tr id='tr_so1'>\n");
			print(" 		<td>Item 1: </td>\n");
			print(" 		<td>\n");
			print("				<input id='itm1' type=text onkeyup=\"getSoFabCodes('itm1')\" maxlength=30 size=20 autofocus >\n");
			print("				<font onclick=\"showNextRow('itm2')\">+</font>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
*/

			for ($y = 1; $y <= 10; $y++) {
				if ($y==1) {
					$style = 'table-row';
				} else {
					$style = 'display:none;';
				}
				$z = $y +1;
				print("	<tr class='dbdl'>\n");
				print("		<th colspan='2' style='".$style."'>Request #" . $y . "</th>\n");
				print("	</tr>\n");
				print("	<tr class='dbc' id='tr_itm".$y."' style='".$style."'>\n");
				print("		<td>Item:</td>\n");
				print("		<td>\n");
				print("			<input type='text' id='tb_item".$y."' name='tb_item".$y."' onkeyup=\"IdItemChange('itm1')\" maxlength=30></input>\n");
				print("		</td>\n");
				print("	</tr>\n");
				print("	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n");
				print("		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font></td>\n");
				print("	</tr>\n");
			}				

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