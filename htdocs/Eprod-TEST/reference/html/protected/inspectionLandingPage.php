<?php

$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	PrintHeader('NSA QA Inspection','default.css');

	$retval = ConnectToDatabaseServer($DBServer, $db);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Connect To $DBServer!\n";
		} else {
			$retval = SelectDatabase($dbName);
			if ($retval == 0) {
				print "		<p class='warning'>Could Not Select $dbName!\n";
			} else {
				$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
				if ($UserRow['PERM_QA'] == '1')  {
					print("	<h3>Current User: " . $UserRow['NAME_EMP'] . "</h3>\n");
					print(" <table>\n");
					print("	<th colspan='5'>Inspection Log</th>\n");
					print("	<tr>\n");
					print("		<td class='icon'><a href='qa_inspection.php'><img class='icon' src='/images/InspectionLog.jpg' href='qa_inspection.php'></br>QA Inspection Log</a></td>\n");
					print("		<td class='icon'><a href='RnDInspection.php'><img class='icon' src='/images/InspectionLog.jpg' href='RnDInspection.php'></br>First Time/Sample Orders Inspection Log</a></td>\n");
					print("		<td class='icon'><a href=''><img class='icon' src='/images/construction.gif' href=''></br>GORE Inspection Log.  Coming <s>Soon</s> Sometime</a></td>\n");
					print("	</tr>\n");
					print(" </table>\n");
				}
			}//end else	

			$retval = DisconnectFromDatabaseServer($db);
			if ($retval == 0) {
				print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
			}
		}
	PrintFooter('emenu.php');


?>