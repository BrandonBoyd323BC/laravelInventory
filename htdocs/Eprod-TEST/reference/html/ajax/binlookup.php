<?php

	$DEBUG = 1;
	$SHOW_DEL = 0;

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
			$ret = "";
			if (isset($_POST["lookupBin"]) && isset($_POST["id_item"])) {
				$ID_ITEM = stripillegalChars2(trim($_POST["id_item"]));

				$sql = "select ";
				$sql .= "ID_ITEM, BIN_PRIM ";
				$sql .= "from nsa.ITMMAS_LOC ";
				$sql .= "where ID_ITEM = '" . $ID_ITEM ."'";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$ret .= "<table>";
					$ret .= "<tr>\n";
					$ret .= "	<th>Item:</th>\n";
					$ret .= "	<td>" .$ID_ITEM ."</td>\n";
					$ret .= "</tr>\n";
					$ret .= "<tr>\n";
					$ret .= "	<th>Bin:</th>\n";
					if(trim($row['BIN_PRIM']) == ""){
						$ret .= "<td><font size='6'>NO BIN DEFINED</font></td>\n";
					}
					else{
						$ret .= "	<td><font size='6'>" .$row['BIN_PRIM']. "</font></td>\n";
					}
					$ret .= "</tr>\n";
					$ret .= "</table>\n";
				}

				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
