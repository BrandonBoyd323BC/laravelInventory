<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("protected/procfile.php");

	PrintHeaderJQ('Garment Tracking','default.css','GarmentTracking.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			$ShopOrd = '';
			$Suffix = '';

 			if (isset($_GET["shopOrd"]))  {
 				$ShopOrd = trim(stripNonANChars($_GET["shopOrd"]));
 			}
 			if (isset($_GET["sufx"]))  {
 				$Suffix = stripNonANChars($_GET["sufx"]);
 			}

			$ShopOrdWSfx = $ShopOrd;

			if ($Suffix <> '') {
				$ShopOrdWSfx .= "-" . $Suffix;
			}
			print(" <img src='images/ThinkNSA_logo.png'>\n");
			print(" <table>\n");
			print(" 	<tr></tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Tracking Number: </td>\n");
			print("			<td>\n");
			print("				<input type='text' name='id_so_TXT' id='id_so_TXT' value='".$ShopOrdWSfx."' onChange='searchCompBySO()'>\n");
			print(" 		</td>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<button id='submit' name='submit' value='Submit' onclick='lookupRecord()'>Get Information</button>\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print("<div id='dataDiv' name='dataDiv'></div>\n");

			print("<body onLoad='doOnLoads()'>");

		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
