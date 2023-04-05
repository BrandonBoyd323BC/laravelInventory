<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			if (isset($_POST["id_so"]))  {
				$ret = "";
				$ID_SO = $_POST["id_so"];

				$sql  = "SELECT ";
				$sql .= " CONVERT(varchar(20), sh.DATE_CMPL, 107) as DATE_CMPL3, ";
				$sql .= " * from nsa.SHPORD_HDR sh ";
				$sql .= " WHERE ltrim(sh.ID_SO) = '" . $ID_SO . "' ";
				QueryDatabase($sql, $results);
				$ret .= "	<table class=center>\n";
				if (mssql_num_rows($results) < 1 ) {
					$ret .= "	<tr>";
					$ret .= "		<td>Tracking Number Not Found<td>";
					$ret .= "	</tr>";
				} else {
					while ($row = mssql_fetch_assoc($results)) {
/*
						$ret .= "		<tr>\n";
						$ret .= "			<th>Tracking Number:</th>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>" . $row['ID_SO'] . "</td>\n";
						$ret .= "		</tr>\n";
*/

						$ret .= "		<tr>\n";
						$ret .= "			<th>Item:</th>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>" . $row['ID_ITEM_PAR'] . "</td>\n";
						$ret .= "		</tr>\n";

						$ret .= "		<tr>\n";
						$ret .= "			<th>Description:</th>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>" . $row['DESCR_ITEM_1'] . "</td>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>" . $row['DESCR_ITEM_2'] . "</td>\n";
						$ret .= "		</tr>\n";

						$ret .= "		<tr>\n";
						$ret .= "			<th>Date Manufactured: </th>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>" . $row['DATE_CMPL3'] . "</td>\n";
						$ret .= "		</tr>\n";

						$ret .= "		<tr>\n";
						$ret .= "			<th>Country of Origin: </th>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>United States of America</td>\n";
						$ret .= "		</tr>\n";

						$ret .= "		<tr>\n";
						$ret .= "			<th>Manufactured by: </th>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>National Safety Apparel, Inc.</td>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>15825 Industrial Parkway</td>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>Cleveland, OH 44135</td>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td><a href='http://www.thinkNSA.com' target='_blank'>www.thinkNSA.com</a></td>\n";
						$ret .= "		</tr>\n";
						$ret .= "		<tr>\n";
						$ret .= "			<td>216.941.1111</td>\n";
						$ret .= "		</tr>\n";
					}
				}

				$ret .= "	</table>\n";


				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
