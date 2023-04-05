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
			$ret = '';
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["id_ord"]))  {
				$ID_ORD = stripNonANChars($_POST["id_ord"]);
				$FileVersion = "ORIG";

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING ON";
				QueryDatabase($sql, $results);

				$sql  = " SELECT * ";
				$sql .= " from nsa.UNITED_FTP_FILES ";
				$sql .= " where ltrim(ID_ORD) = '". $ID_ORD ."'";
				QueryDatabase($sql, $results);
				if (mssql_num_rows($results) > 0) {
					$ret .= "<h1>ORDER ". $ID_ORD ." PREVIOUSLY ENTERED</h1>";
					$ret .= "<h1>SENDING AS REVISED</h1>";
					$FileVersion = "REVISED";
				}

				$sql  = " SELECT ltrim(ID_CUST_SOLDTO) as ID_CUST_SOLDTO, CODE_STAT_ORD ";
				$sql .= " from nsa.CP_ORDHDR ";
				$sql .= " where ltrim(ID_ORD) = '". $ID_ORD ."'";
				QueryDatabase($sql, $results);
				if (mssql_num_rows($results) < 1) {
					$ret .= "<h1>ORDER NOT FOUND</h1>";
				}

				while ($row = mssql_fetch_assoc($results)) {
					if (strpos($row['ID_CUST_SOLDTO'],'S') === false) {
						$ret .="<h1>NOT A SPENTEX CUSTOMER ORDER</h1>";
					} elseif ($row['CODE_STAT_ORD'] == 'H') {
						$ret .="<h1>ORDER ON HOLD</h1>";
					} else {
						$baseFile = "NSA_SPX_" . $ID_ORD . "_" . date('Ymd-His') . ".csv";
						$baseDir = '/mnt/UnitedFTP/Outbound';
						$archiveDir 	= $baseDir . "/Archive/";
						$pendingDir 	= $baseDir . "/Pending/";
						$sentDir 	= $baseDir . "/Sent/";
						$errorDir 	= $baseDir . "/Error/";
						$filename_archive = $archiveDir . $baseFile;
						$filename_pending = $pendingDir . $baseFile;
						$fp = fopen($filename_archive, 'w');

						$sql1  = " SELECT ";
						$sql1 .= "  oh.ID_ORD, ";
						$sql1 .= "  oh.ID_PO_CUST, ";
						$sql1 .= "  CONVERT(varchar(8), oh.DATE_ORD, 112) as DATE_ORD3, ";
						$sql1 .= "  CONVERT(varchar(8), ol.DATE_PROM, 112) as DATE_PROM3, ";
						$sql1 .= "  ol.SEQ_LINE_ORD, ";
						$sql1 .= "  ol.ID_ITEM, ";
						$sql1 .= "  ol.ID_LOT, ";
						$sql1 .= "  ol.CODE_UM_ORD, ";
						$sql1 .= "  ol.QTY_OPEN, ";
						$sql1 .= "  oh.NAME_CUST_SHIPTO, ";
						$sql1 .= "  oh.ADDR_1, ";
						$sql1 .= "  oh.ADDR_2, ";
						$sql1 .= "  oh.CITY, ";
						$sql1 .= "  oh.ID_ST, ";
						$sql1 .= "  oh.ZIP, ";
						$sql1 .= "  cc.ID_COUNTRY, ";
						$sql1 .= "  oh.COUNTRY, ";
						$sql1 .= "  oh.CODE_SHIP_VIA_CP, ";
						$sql1 .= "  sv.CARRIER, ";
						$sql1 .= "  sa.ACCT_SHIP_VIA_CP, ";
						$sql1 .= "  sv.DELIV_SVC, ";
						$sql1 .= "  sv.PKG_TYPE, ";
						$sql1 .= "  sv.PAYOR, ";
						$sql1 .= "  '' as FILE_VERSION, ";
						$sql1 .= "  '' as WHSE_COMMENT, ";
						$sql1 .= "  '' as BOL_COMMENT ";
						$sql1 .= " FROM ";
						$sql1 .= "  nsa.CP_ORDLIN ol ";
						$sql1 .= "  left join nsa.CP_ORDHDR oh ";
						$sql1 .= "  on ol.ID_ORD = oh.ID_ORD ";
						$sql1 .= "  left join nsa.UNITED_SHIP_VIA_DTL sv ";
						$sql1 .= "  on oh.CODE_SHIP_VIA_CP = sv.CODE_SHIP_VIA_CP ";
						$sql1 .= "  left join nsa.cm_ship_acct sa ";
						$sql1 .= "  on ltrim(oh.ID_CUST_SOLDTO) = ltrim(sa.ID_CUST_SOLDTO) ";
						$sql1 .= "  and oh.SEQ_SHIPTO = sa.SEQ_SHIPTO ";
						$sql1 .= "  and oh.CODE_SHIP_VIA_CP = sa.CODE_SHIP_VIA_CP ";
						$sql1 .= "  left join nsa.UNITED_COUNTRY_CODES cc ";
						$sql1 .= "  on upper(ltrim(oh.COUNTRY)) = upper(ltrim(CC.NAME_COUNTRY)) ";
						$sql1 .= " WHERE ltrim(ol.ID_ORD) = '". $ID_ORD ."' ";
						$sql1 .= "  and ol.ID_LOC = '90' ";
						$sql1 .= "  and ltrim(ol.ID_ITEM) not in ('SPXEMBPATCH','SPXEMBRY - CO. LOGO','SPXEMBRY - EE NAME', 'SPXEMBRY - SET UP FEE','SPXEMBRY-CO.LOGO','SPXEMBRY-EE NAME','SPXHIGH VIZ-COVERALL','SPXHIGH VIZ-SHIRT') ";
						$sql1 .= " ORDER BY ol.SEQ_LINE_ORD asc ";
						QueryDatabase($sql1, $results1);
						while ($row1 = mssql_fetch_assoc($results1)) {
							if (substr(strtoupper($row1['ID_ITEM']),0,3) == 'SPX' || substr(strtoupper($row1['ID_ITEM']),0,3) == 'SPC') {
								$row1['ID_ITEM'] = substr(strtoupper($row1['ID_ITEM']),3);
							}
							if (trim($row1['ID_LOT']) == "") {
								$row1['ID_LOT'] = "UNI";
							}
							if (trim($row1['ACCT_SHIP_VIA_CP']) == ""  && $row1['PAYOR'] == "S") {
								$sql2  = " SELECT ";
								$sql2 .= "  sv.PAYORID ";
								$sql2 .= " FROM ";
								$sql2 .= "  nsa.UNITED_SHIP_VIA_DTL sv ";
								$sql2 .= " WHERE ";
								$sql2 .= "  sv.CODE_SHIP_VIA_CP = '" . $row1['CODE_SHIP_VIA_CP'] . "' ";
								QueryDatabase($sql2, $results2);
								while ($row2 = mssql_fetch_assoc($results2)) {
									$row1['ACCT_SHIP_VIA_CP'] = $row2['PAYORID'];
								}
							}
							$row1['FILE_VERSION'] = $FileVersion;

							$sql2  = " SELECT ";
							$sql2 .= "  ID_ORD, SEQ_COMMENT, NOTE ";
							$sql2 .= " FROM ";
							$sql2 .= "  nsa.CP_COMMENT ";
							$sql2 .= " WHERE ";
							$sql2 .= "  ID_ORD = '" . $ID_ORD . "' ";
							$sql2 .= "  and CODE_COMMENT = 5 ";
							$sql2 .= "  and FLAG_PRNT_PICK = 1 ";
							$sql2 .= " ORDER BY SEQ_COMMENT asc ";
							QueryDatabase($sql2, $results2);
							while ($row2 = mssql_fetch_assoc($results2)) {
								$row1['WHSE_COMMENT'] .= str_replace("|","",$row2['NOTE']) . " " ;
							}

							$sql2  = " SELECT ";
							$sql2 .= "  ID_ORD, SEQ_COMMENT, NOTE ";
							$sql2 .= " FROM ";
							$sql2 .= "  nsa.CP_COMMENT ";
							$sql2 .= " WHERE ";
							$sql2 .= "  ID_ORD = '" . $ID_ORD . "' ";
							$sql2 .= "  and CODE_COMMENT = 5 ";
							$sql2 .= "  and FLAG_PRNT_BOL = 1 ";
							$sql2 .= " ORDER BY SEQ_COMMENT asc ";
							QueryDatabase($sql2, $results2);
							while ($row2 = mssql_fetch_assoc($results2)) {
								$row1['BOL_COMMENT'] .= str_replace("|","",$row2['NOTE']) . " " ;
							}

							$sql2  = " SELECT ";
							$sql2 .= "  rtrim(ltrim(ID_JOB)) as ID_JOB ";
							$sql2 .= " FROM ";
							$sql2 .= "  nsa.CP_ORDHDR ";
							$sql2 .= " WHERE ";
							$sql2 .= "  ID_ORD = '" . $ID_ORD . "' ";
							QueryDatabase($sql2, $results2);
							while ($row2 = mssql_fetch_assoc($results2)) {
								if (trim($row2['ID_JOB']) <> "") {
									$row1['ID_ORD'] .= "-" . $row2['ID_JOB'];
								}
							}

							//fputcsv($fp, $row1, "|", chr(0));
							fputcsv($fp, $row1, "|", "\"");
						}
						fclose($fp);

						$sql1  = " INSERT INTO nsa.UNITED_FTP_FILES ( ";
						$sql1 .= "  ID_ORD, ";
						$sql1 .= "  FLAG_UD, ";
						$sql1 .= "  STATUS, ";
						$sql1 .= "  FILE_VERSION, ";
						$sql1 .= "  FILE_NAME, ";
						$sql1 .= "  FILE_TYPE, ";
						$sql1 .= "  DATE_ADD, ";
						$sql1 .= "  ID_USER_ADD ";
						$sql1 .= " ) VALUES ( ";
						$sql1 .= "  '" . $ID_ORD . "', ";
						$sql1 .= "  'U', ";
						$sql1 .= "  'Created', ";
						$sql1 .= "  '" . $FileVersion . "', ";
						$sql1 .= "  '" . $filename_archive . "', ";
						$sql1 .= "  'CSV', ";
						$sql1 .= "  getDate(), ";
						$sql1 .= "  '" . stripIllegalChars($UserRow['ID_USER']) . "' ";
						$sql1 .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
						/*QueryDatabase($sql1, $results1);

						$row1 = mssql_fetch_assoc($results1);
						$LAST_INSERT_ID = $row1['LAST_INSERT_ID'];

						if (!copy($filename_archive, $filename_pending)) {
							error_log("Failed to copy " . $filename_archive . " to " . $filename_pending);
							$sql1  = " UPDATE nsa.UNITED_FTP_FILES set STATUS='FAIL2PENDING' where rowid = ". $LAST_INSERT_ID;
							QueryDatabase($sql1, $results1);
						} else {
							error_log($baseFile . " copied to pending dir");
							$sql1  = " UPDATE nsa.UNITED_FTP_FILES set STATUS='Pending' where rowid = ". $LAST_INSERT_ID;
							QueryDatabase($sql1, $results1);
						}*/
						$ret .= " <br>\n";
						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th>Error Files:</th>\n";
						$ret .= " 	</tr>\n";
						$ErrorArray = scandir($errorDir);
						foreach ($ErrorArray as $ErrorFile) {
							if ($ErrorFile <> "." && $ErrorFile <> "..") {
								$ret .= " 	<tr>\n";
								$ret .= " 		<td>". $ErrorFile ."</td>\n";
								$ret .= " 	</tr>\n";
							}
						}
						$ret .= " </table>\n";
						$ret .= " <br>\n";
						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th>Pending Files:</th>\n";
						$ret .= " 	</tr>\n";
						$PendingArray = scandir($pendingDir);
						foreach ($PendingArray as $PendingFile) {
							if ($PendingFile <> "." && $PendingFile <> "..") {
								$ret .= " 	<tr>\n";
								$ret .= " 		<td>". $PendingFile ."</td>\n";
								$ret .= " 	</tr>\n";
							}
						}
						$ret .= " </table>\n";
						$ret .= " <br>\n";
						$ret .= " <table class='sample'>\n";
						$ret .= " 	<tr>\n";
						$ret .= " 		<th>Sent Files:</th>\n";
						$ret .= " 	</tr>\n";
						$SentArray = scandir($sentDir);
						foreach ($SentArray as $SentFile) {
							if ($SentFile <> "." && $SentFile <> "..") {
								$ret .= " 	<tr>\n";
								$ret .= " 		<td>". $SentFile ."</td>\n";
								$ret .= " 	</tr>\n";
							}
						}
						$ret .= " </table>\n";
					}
				}
				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
?>