<?php
	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Shift Codes','default.css','realtime.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			print("		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n");




			$sql =  "select ";
			$sql .= " 	distinct CODE_SHIFT ";
			$sql .= " from ";
			$sql .= " 	nsa.DCEMMS_EMP ";
			//$sql .= " where ";
			$sql .= " order by CODE_SHIFT asc";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {

				//////////////
				//SHIFT
				//////////////
				$CODE_SHIFT = trim($row['CODE_SHIFT']);

				print("<div id='div_" . $CODE_SHIFT . "' name='div_" . $CODE_SHIFT . "'>\n");
				print(" <table class='sample'>\n");
				print(" 	<tr>\n");
				print(" 		<th colspan=5>\n");
				print("				<font>SHIFT CODE: " . $row['CODE_SHIFT'] . "</font>\n");
				print(" 		</th>\n");
				print("			<td id='x_" . $CODE_SHIFT . "' name='x_" . $CODE_SHIFT . "' onclick=\"closeDiv('div_" . $CODE_SHIFT . "')\" TITLE='Remove Table'>X</td>\n");
				print(" 	</tr>\n");
				print(" 	<tr>\n");
				print(" 		<th>\n");
				print("				<font>Name</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Code_Actv</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Type_Badge</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Stat_Badge</font>\n");
				print(" 		</th>\n");
				print(" 		<th>\n");
				print("				<font>Date_Trx_Prior</font>\n");
				print(" 		</th>\n");
				print(" 	</tr>\n");

				$sql2 = "select ";
				$sql2 .= "	CONVERT(varchar(8), DATE_TRX_PRIOR, 112) as DATE_TRX_PRIOR3, ";
				$sql2 .= " 	ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME,";
				$sql2 .= "	* ";
				$sql2 .= " from ";
				$sql2 .= "	nsa.DCEMMS_EMP ";
				$sql2 .= " where ";
				$sql2 .= " 	CODE_SHIFT = '" . $CODE_SHIFT . "' ";
				$sql2 .= "  and ";
				$sql2 .= " 	STAT_BADGE not in ('T','I') ";
				$sql2 .= " order by ";
				$sql2 .= " 	BADGE_NAME asc ";

				QueryDatabase($sql2, $results2);

				while ($row2 = mssql_fetch_assoc($results2)) {
					$currPR = $row2['DATE_TRX_PRIOR3'] . " " . str_pad($row2['TIME_TRX_PRIOR'],6,"0",STR_PAD_LEFT);
					if (!is_null($row2['DATE_TRX_PRIOR3'])) {
						$currtsPR = strtotime($currPR);
						$currPR_R = date('m/d/Y h:i:s A',$currtsPR);
					}

					print(" 	<tr>\n");
					print(" 		<td>\n");
					print("				<font>" . $row2['BADGE_NAME'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font>" . $row2['CODE_ACTV'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font>" . $row2['TYPE_BADGE'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font>" . $row2['STAT_BADGE'] . "</font>\n");
					print(" 		</td>\n");
					print(" 		<td>\n");
					print("				<font>" . $row2['DATE_TRX_PRIOR'] . "</font>\n");
					print(" 		</td>\n");
					print(" 	</tr>\n");
				}

				print(" 	</tr>\n");
				print(" </table>\n");
				print(" 	</br>\n");
				print(" </div>\n");
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

	if (isset($_POST["df"]) && isset($_POST["dt"]) && isset($_POST["team"]))  {
		PrintFooter("activity.php");
	} else {
		PrintFooter("emenu.php");
	}

?>
