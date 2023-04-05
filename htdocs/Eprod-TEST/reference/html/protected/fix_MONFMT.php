<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	
	PrintHeaderJQ('Fix TCM "MONFMT" Errors','default.css','fix_MONFMT.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print(" <table>\n");
			print(" 	<tr>\n");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='table'>Table: </LABEL>\n");
			print("				<select id='table'>\n");

			/*
			select TABLE_NAME as TABLE_NAME1, 
			COLUMN_NAME as COLUMN_NAME1,
			*
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE COLUMN_NAME like '%_FC' 
			and TABLE_SCHEMA = 'nsa'
			and TABLE_NAME like 'CUSMAS_%'
			order by TABLE_NAME asc, COLUMN_NAME asc
			*/

			print("					<option value='unselected'>-- Select Table --</option>\n");
			print("					<option value='CUSMAS_SOLDTO'>Customer Master SOLDTO</option>\n");
			print("					<option value='CUSMAS_SHIPTO'>Customer Master SHIPTO</option>\n");
			print("				</select>\n");



			print("				<INPUT id='submit' type='submit' value='Submit' onClick=\"findBadRecs()\">\n");
			print(" 		</td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='LoadingDiv'></div>\n");
			print(" <div id='dataDiv'></div>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
	PrintFooter("emenu.php");
?>
