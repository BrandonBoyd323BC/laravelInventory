<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			$ret = '';
			$a = array();
			//$filename = "PayTrx_". date('Ymd-His') .".csv";
			//header( 'Content-Type: text/csv' );
			//header( 'Content-Disposition: attachment;filename='.$filename);
			//$fp = fopen('php://output', 'w');

			if (isset($_POST["df"]) && isset($_POST["dt"]))  {
				$DateFrom 		= str_replace("-","",$_POST["df"]);
				$DateTo 		= str_replace("-","",$_POST["dt"]);

				$ret .= "		<table class='sample'>\n";
				$ret .= "		 	<tr>\n";
				$ret .= "				<th>ID_BADGE</th>\n";
				$ret .= "				<th>NAME</th>\n";

				$sqlp  = " SELECT ";
				$sqlp .= " 	distinct p.CODE_PAY_DC ";
				$sqlp .= " FROM nsa.PAYTRX p ";
				$sqlp .= " WHERE ";
				$sqlp .= " 	p.FLAG_APPRV in ('Y','A') ";
				$sqlp .= " 	and ltrim(CODE_PAY_DC) <> '' ";
				$sqlp .= " order by p.CODE_PAY_DC asc ";
				QueryDatabase($sqlp, $resultsp);

				$sql  = " SELECT ";
				$sql .= " 	p.ID_BADGE, ";
				$sql .= " 	e.NAME_EMP, ";
				while ($rowp = mssql_fetch_assoc($resultsp)) {
					$sql .= " 	sum(case when ((p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."') and p.FLAG_APPRV in ('Y','A') and p.CODE_PAY_DC = '". $rowp['CODE_PAY_DC'] ."') then p.HR_PAID else 0 end) as '". trim($rowp['CODE_PAY_DC']) ."', ";
					$ret .= "				<th>". $rowp['CODE_PAY_DC'] ."</th>\n";
					$a[] = trim($rowp['CODE_PAY_DC']);
				}
				$sql .= " 	e.NAME_EMP as NAME_EMP2 ";
				$sql .= " FROM nsa.PAYTRX p ";
				$sql .= " 	left join nsa.DCEMMS_EMP e ";
				$sql .= " 	on p.ID_BADGE = e.ID_BADGE ";
				$sql .= " 	and e.CODE_ACTV = 0 ";
				$sql .= " WHERE p.DATE_TRX between '". $DateFrom ."' and '". $DateTo ."' ";
				$sql .= " 	and p.FLAG_APPRV in ('Y','A') ";
				$sql .= " GROUP BY p.ID_BADGE, e.NAME_EMP ";
				$sql .= " ORDER BY p.ID_BADGE asc ";
				$ret .= "		 	<tr>\n";
				QueryDatabase($sql, $results);
				
				//$colNamesA = array();
				//for($i = 0; $i < mssql_num_fields($results); $i++) {
				//    $field_info = mssql_fetch_field($results, $i);
				//    $field = $field_info->name;
				//    $colNamesA[$i] =  $field;
				//}
				//fputcsv($fp, $colNamesA);				

				while ($row = mssql_fetch_assoc($results)) {
					//fputcsv($fp, $row, ",", "\"");
/*
					if ($row['OVT'] > 0 && $row['REG'] < 40) {
						error_log("FIXXME " . $row['ID_BADGE']);
						$row['REG'] += $row['OVT'];
						$row['OVT'] = $row['REG'] - 40;
						if ($row['REG'] > 40) {
							$row['REG'] = 40;
						}
						if ($row['OVT'] < 0) {
							$row['OVT'] = 0;
						}
						$ret .= "		 	<tr>\n";
						$ret .= "				<th>". $row['ID_BADGE'] ."</th>\n";
						$ret .= "				<th>". $row['NAME_EMP'] ."</th>\n";
						
					} else {
						$ret .= "		 	<tr>\n";
						$ret .= "				<td>". $row['ID_BADGE'] ."</td>\n";
						$ret .= "				<td>". $row['NAME_EMP'] ."</td>\n";
					}
*/
					$ret .= "		 	<tr>\n";
					$ret .= "				<td>". $row['ID_BADGE'] ."</td>\n";
					$ret .= "				<td>". $row['NAME_EMP'] ."</td>\n";
					foreach ($a as $code) {
						$ret .= "				<td>". $row[$code] ."</td>\n";
					}
					$ret .= "		 	<tr>\n";
				}
				$ret .= "		</table>\n";
				//fclose($fp);
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
