<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");


	PrintHeaderJQ('Shop Order Open','default.css','soOpen.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td>SO: </td>\n");
			print(" 		<td>\n");
			print("				<input id='so_num' type=text onkeyup=\"nextOnDash('so_num','sufx')\" maxlength=9 size=10 autofocus tabindex=1> -\n");
			print("			</td>\n");
			print(" 		<td>\n");
			print("				<input id='sufx' type=text maxlength=3 size=3 tabindex=2 onkeypress='searchKeyPress(event);'>");
			print(" 		</td>\n");
			print(" 		<td>\n");
			print("				<input id ='submit' type='button' value='Submit' onClick=\"submitForm('show')\" tabindex=3>\n");
			print("			</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	
	}
		
	PrintFooter("emenu.php");

?>