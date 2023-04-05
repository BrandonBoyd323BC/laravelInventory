<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../protected/procfile.php");
	
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
			$sql .= " 	top 10 ID_ITEM ";
			$sql .= " from ";
			$sql .= " 	nsa.ITMMAS_BASE ";
			$sql .= " where ID_ITEM like '" . $string . "%' ";
			//$sql .= " 	and CODE_UM_STK in ('LI','IN') ";
			$sql .= " 	and ID_ITEM not like  '%\"%' ";
			$sql .= " order by ID_ITEM asc";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
			    if (!$first) { 
			    	$json .=  ','; 
			    } else { 
			    	$first = false; 
			    }
			    $json .= '{"value":"'.$row['ID_ITEM'].'"}';
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
