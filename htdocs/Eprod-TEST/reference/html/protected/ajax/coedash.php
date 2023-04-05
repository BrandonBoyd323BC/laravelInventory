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

			if (isset($_POST["dt"]) && isset($_POST["team"]))  {
				$Date = $_POST["dt"];
				$Team = $_POST["team"];
				$DateTS = strtotime($Date);
				$DateDT = date('Y-m-d', $DateTS);

				$sql =  "select ";
				$sql .= " 	ltrim(ID_BADGE) + ' - ' + NAME_SORT as BADGE_NAME,";
				$sql .= " 	ltrim(ID_BADGE) as ID_BADGE,";
				$sql .= " 	NAME_SORT";
				$sql .= " from ";
				$sql .= " 	nsa.DCEMMS_EMP ";
				$sql .= " where ";
				$sql .= " 	TYPE_BADGE = 'X'";
				$sql .= " 	and";
				$sql .= " 	CODE_ACTV = '0'";
				if ($Team != 'ALL') {
					$sql .= " 	and";
					$sql .= " 	ltrim(ID_BADGE) = '" . $Team . "'";
				}
				$sql .= " order by ID_BADGE asc ";
				QueryDatabase($sql, $results);

				while ($row = mssql_fetch_assoc($results)) {
					$ID_BADGE = $row['ID_BADGE'];
					$Team = $row['ID_BADGE'];
					$ret .= " <div id='div_" . $ID_BADGE . "' name='div_" . $ID_BADGE . "'>\n";
					$ret .= " <table class='sample'>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'  colspan = '11'>". $row['BADGE_NAME'] . "</th>\n";
					$ret .= "		<td id='x_" . $ID_BADGE . "' name='x_" . $ID_BADGE . "' onclick=\"closeDiv('div_" . $ID_BADGE . "')\" TITLE='Remove Table'>X</td>\n";
					$ret .= " 	</tr>\n";
					$ret .= " 	<tr>\n";
					$ret .= " 		<th class='sample'>Trx</th>\n";
					$ret .= " 		<th class='sample'>Desc Trx</th>\n";
					$ret .= " 		<th class='sample'>Time</th>\n";
					$ret .= " 		<th class='sample'>Badge</th>\n";
					$ret .= " 		<th class='sample'>Team</th>\n";
					$ret .= " 		<th class='sample'>--</th>\n";
					$ret .= " 		<th class='sample'>Trx</th>\n";
					$ret .= " 		<th class='sample'>Desc Trx</th>\n";
					$ret .= " 		<th class='sample'>Time</th>\n";
					$ret .= " 		<th class='sample'>Badge</th>\n";
					$ret .= " 		<th class='sample'>Team</th>\n";
					$ret .= " 	</tr>\n";

					$BadgeList = "'0'";
					$sql2 =  "select ";
					$sql2 .= " 	distinct ID_BADGE as DisBadge";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCUTRX_ZERO_PERM ";
					$sql2 .= " where ";
					$sql2 .= "	DATE_CORR_TRX = '" . $DateDT . "' ";
					$sql2 .= "	and ";
					$sql2 .= "	(ltrim(ID_BADGE) = '" . $ID_BADGE . "' OR ltrim(ID_BADGE_TEAM) = '" . $ID_BADGE . "') ";
					$sql2 .= "	and ";
					$sql2 .= "	FLAG_DEL <> 'D' ";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$BadgeList .= ",'" . $row2['DisBadge'] . "'";
					}

					$sql2 =  "select ";
					$sql2 .= " 	ID_BADGE, ";
					$sql2 .= " 	ID_BADGE_TEAM, ";
					$sql2 .= " 	CODE_TRX, ";
					$sql2 .= " 	CODE_ACTV, ";
					$sql2 .= " 	DATE_CORR_TRX, ";
					$sql2 .= "	CONVERT(varchar(8), DATE_CORR_TRX, 112) as DATE_CORR_TRX3, ";
					$sql2 .= " 	TIME_CORR_TRX ";
					$sql2 .= " from ";
					$sql2 .= " 	nsa.DCUTRX_ZERO_PERM ";
					$sql2 .= " where ";
					$sql2 .= "	DATE_CORR_TRX = '" . $DateDT . "' ";
					$sql2 .= "	and ";
					$sql2 .= "	CODE_TRX in ('300','100','302','304','101','303','305','301') ";
					//$sql2 .= "	CODE_TRX in ('300','301') ";
					$sql2 .= "	and ";
					$sql2 .= "	(ID_BADGE in (" . $BadgeList . ") OR ID_BADGE_TEAM in (" . $BadgeList . ")) ";
					$sql2 .= "	and ";
					$sql2 .= "	FLAG_DEL <> 'D' ";
					$sql2 .= " order by CODE_TRX asc, TIME_CORR_TRX asc ";
					QueryDatabase($sql2, $results2);
					while ($row2 = mssql_fetch_assoc($results2)) {
						$td_class = GetColorCodeTrx($row2['CODE_TRX']);
						$trxType = GetStrCodeTrx($row2['CODE_TRX']);

						$curr = $row2['DATE_CORR_TRX3'] . " " . str_pad($row2['TIME_CORR_TRX'],6,"0",STR_PAD_LEFT);
						$currts = strtotime($curr);
						$ret .= " 	<tr>\n";
						if (is_odd($row2['CODE_TRX'])) {
							$ret .= " 		<td colspan=6></td>\n";
						}

						$ret .= " 		<td class='" . $td_class . "'>" . $row2['CODE_TRX'] . "</td>\n";
						$ret .= " 		<td class='" . $td_class . "'>" . $trxType . "</td>\n";
						$ret .= "		<td class='" . $td_class . "'>" . date('m/d/Y h:i:s A',$currts) . "</td>\n";
						$ret .= " 		<td class='" . $td_class . "'>" . $row2['ID_BADGE'] . "</td>\n";
						$ret .= " 		<td class='" . $td_class . "'>" . $row2['ID_BADGE_TEAM'] . "</td>\n";
						$ret .= " 	</tr>\n";
					}

					$ret .= " </table>\n";
					$ret .= " </br>\n";
					$ret .= " </div>\n";

				}
			}

			if (isset($_POST["divclose"])) {
				$ret .= "		<p onClick=\"disablePopup(". $Team .")\">CLOSE</p>\n";
			}

			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>
