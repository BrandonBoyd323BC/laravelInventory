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

			if ( isset($_POST["id_badge"]) ) {

				$ID_BADGE = stripillegalChars2(trim($_POST["id_badge"]));

				//////////////////////////////////////////
				/////////DCEMMS_EMP
				/////////////////////////////////////////
				$sql1 =" select NAME_EMP ";//grab emp name from badge
				$sql1 .=" from nsa.DCEMMS_EMP ";
				$sql1 .=" where ltrim(ID_BADGE) = '" . $ID_BADGE ."' and CODE_ACTV = 0";
				error_log($sql1);
				QueryDatabase($sql1, $results1);

				while($row1 = mssql_fetch_assoc($results1)){
					$ret .= "<table>";
					$ret .= "<tr>\n";
					$ret .= "  <td><b>".$row1['NAME_EMP']."</b></td> ";
					$ret .= "</tr>\n";
					$ret .= "</table>\n";
				}

				$ret .= "<table class='sample'>";//main table headers
				$ret .= "<tr>\n";
				$ret .= " 	<th>Date</th>\n";
				$ret .= " 	<th>Time</th>\n";
				$ret .= " 	<th>Code Trx</th>\n";
				$ret .= "</tr>\n";

				////////////////////////////////////////////
				/////DC TRAN
				///////////////////////////////////////////
				$sql2 = "select ";
				$sql2 .= " convert(char(10),dz.DATE_TRX,121) as DATE_TRX, STUFF(STUFF(right('000000' + rtrim(dz.TIME_TRX),6), 3,0,':'),6,0,':') + '.000' as TIME_TRX, ";
				$sql2 .= "de.NAME_EMP, dz.ID_BADGE, ";
				$sql2 .= "	CASE 
								WHEN CODE_TRX = 100 THEN 'Attend In'
								WHEN CODE_TRX = 101 THEN 'Attend Out'
							END as CODE_TRX ";
				$sql2 .= "from nsa.DCTRAN dz ";
				$sql2 .= "left join nsa.DCEMMS_EMP de ";
				$sql2 .= "on dz.ID_BADGE = de.ID_BADGE ";
				$sql2 .= "where ltrim(dz.ID_BADGE) = '" . $ID_BADGE ."' and CODE_TRX in ('100','101') and (DATE_TRX >= DATEADD(day,-14,GETDATE()) )";
				$sql2 .= " order by DATE_TRX desc, TIME_TRX desc";
				error_log($sql2);
				QueryDatabase($sql2, $results2);

				while ($row2 = mssql_fetch_assoc($results2)) {
					$ret .= " 	<tr>\n";
						$ret .= "	<td>" .$row2['DATE_TRX']. "</td>\n";
						$ret .= "	<td>" .date('h:i:s A',strtotime($row2['TIME_TRX'])). "</td>\n";
						$ret .= "	<td>" .$row2['CODE_TRX']. " *</td>\n";
					$ret .= " 	</tr>\n";
				}

				//////////////////////////////////////////
				//////DCTRX
				/////////////////////////////////////////
				$sql = "select ";
				$sql .= "dz.ID_BADGE, ";
				$sql .= " convert(char(10),dz.DATE_TRX,121) as DATE_TRX, STUFF(STUFF(right('000000' + rtrim(dz.TIME_TRX),6), 3,0,':'),6,0,':') + '.000' as TIME_TRX, ";
				$sql .= " convert(char(10),dz.DATE_CORR_TRX,121) as DATE_CORR_TRX, STUFF(STUFF(right('000000' + rtrim(dz.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as TIME_CORR_TRX, ";
				$sql .= " CASE";					 
				$sql .= " 	WHEN CODE_TRX = 100 THEN 'Attend In' ";
				$sql .= " 	WHEN CODE_TRX = 101 THEN 'Attend Out' ";
				$sql .= " END as CODE_TRX ";
				$sql .= "from nsa.DCUTRX_ZERO_PERM dz ";
				$sql .= "where ltrim(dz.ID_BADGE) = '" . $ID_BADGE ."' and CODE_TRX in ('100','101') and FLAG_DEL != 'D' and (DATE_TRX >= DATEADD(day,-14,GETDATE()) )";
				$sql .= " order by DATE_TRX desc, TIME_TRX desc";
				error_log($sql);
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					if($row['TIME_TRX'] == $row['TIME_CORR_TRX']){
						$ret .= " 	<tr>\n";
							$ret .= "	<td>" .$row['DATE_TRX']. "</td>\n";
							$ret .= "	<td>" .date('h:i:s A',strtotime($row['TIME_TRX'])). "</td>\n";
							$ret .= "	<td>" .$row['CODE_TRX']. "</td>\n";
						$ret .= " 	</tr>\n";
					}
					else {
						$ret .= " 	<tr>\n";
							$ret .= "	<td>" .$row['DATE_TRX']. "</td>\n";
							$ret .= "	<td>" .date('h:i:s A',strtotime($row['TIME_CORR_TRX']))."</td>\n";
							$ret .= "	<td>" .$row['CODE_TRX']. " #</td>\n";
						$ret .= " 	</tr>\n";
					}
				}
				$ret .= " 	</tr>\n";
				$ret .= "</table>\n";


				echo json_encode(array("returnValue"=> $ret));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>
