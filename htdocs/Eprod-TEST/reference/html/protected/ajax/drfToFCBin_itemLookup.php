<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$string = stripIllegalChars2($_GET['term']);
			$json = '[';
			$first = true;

			$sql =  "select ";
			$sql .= " 	top 10 ITEM ";
			$sql .= " from ";
			$sql .= " 	nsa.ItemVsBin_ForWH ";
			$sql .= " where ITEM like '" . $string . "%' ";
			$sql .= " 	and ITEM not like  '%\"%' ";
			$sql .= " order by ITEM asc";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
			    if (!$first) { 
			    	$json .=  ','; 
			    } else { 
			    	$first = false; 
			    }
			    $json .= '{"value":"'.$row['ITEM'].'"}';
			}
			$json .= ']';
			echo $json;
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
