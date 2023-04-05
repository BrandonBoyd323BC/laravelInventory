<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Order Prep Dashboard','default.css','ordprep_dash.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {

			print(" <h3>Current Record<h3>\n");
			print(" <table>");
			print(" 	<tr>");
			print(" 		<td>Time: </td>");
			print(" 		<td></td>\n");
			print(" 	</tr>\n");
			print(" 	<tr>\n");
			print(" 		<td>Team: </td>\n");
			print(" 		<td></td>\n");
			print(" 	</tr>\n");
			print(" </table>\n");

			print(" <h3>Open Requests<h3>\n");
			print(" <table class='sample'>");
			print(" 	<tr class='sample'>");
			print(" 		<th>Time</th>\n");
			print(" 		<th>Team</th>\n");
			print(" 		<th>Shop Ord</th>\n");
			print(" 		<th>Item</th>\n");
			print(" 		<th>Qty</th>\n");
			print(" 		<th>Comment</th>\n");
			print(" 	</tr>\n");

			$sql =  "select ";
			$sql .= " 	* ";
			$sql .= " from ";
			$sql .= " 	nsa.ORD_PREP_MISSING opm ";
			$sql .= " where ";
			$sql .= " 	opm.FLAG_COMPLETE = '' ";
			$sql .= " order by DATE_ADD asc";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				print(" 	<tr class='sample' id='tr_". $row['rowid'] ."' onDblClick='selectRow(this.id)'>\n");
				print(" 		<td>" . $row['DATE_ADD'] . "</td>\n");
				print(" 		<td>" . $row['ID_BADGE_ADD'] . "</td>\n");
				print(" 		<td>" . $row['ID_SO'] . "</td>\n");
				print(" 		<td>" . $row['ID_ITEM_COMP'] . "</td>\n");
				print(" 		<td>" . $row['QTY_MISSING'] . "</td>\n");
				print(" 		<td>" . $row['COMMENTS'] . "</td>\n");
				print(" 	</tr>\n");
			}
			print(" <div id='newRow'></div>\n");
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
	PrintFooter('emenu.php');
?>
