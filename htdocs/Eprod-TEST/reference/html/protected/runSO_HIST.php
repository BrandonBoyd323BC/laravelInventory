<?php
	$DEBUG = 0;

	require_once("procfile.php");

	$my_username = "script";
	$my_password = "Difg2bAG";
	$my_DBServer = "172.19.100.101";
	$my_db = 'web';


	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}





	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runSO_HIST cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runSO_HIST cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runSO_HIST started at " . date('Y-m-d g:i:s a'));
			error_log("### runSO_HIST CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));


			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runSO_HIST' ";
			$sql .= "	and ";
			$sql .= "	FLAG_RUNNING = '1' ";
			$sql .= "	and ";
			$sql .= "	DATE_EXP > getDate()";
			QueryDatabase($sql, $results);

			if (mssql_num_rows($results) == 0) {

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING ON";
				QueryDatabase($sql, $results);
				$sql = "SET CONCAT_NULL_YIELDS_NULL ON";
				QueryDatabase($sql, $results);


				$sql  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql .= " PROC_NAME, ";
				$sql .= " FLAG_RUNNING, ";
				$sql .= " DATE_ADD, ";
				$sql .= " DATE_EXP ";
				$sql .= ") VALUES ( ";
				$sql .= "'runSO_HIST', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runSO_HIST SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				error_log("### runSO_HIST SELECTING DATA ");
				$sql  = " select ";
				$sql .= "	sh.ID_SO, ";
				$sql .= "	sh.ID_LOC, ";
				$sql .= "	sh.ID_ITEM_PAR, ";
				$sql .= "	sh.DESCR_ITEM_1, ";
				$sql .= "	sh.DESCR_ITEM_2, ";
				$sql .= "	sh.QTY_ORD, ";
				$sql .= "	sh.QTY_CMPL, ";
				$sql .= "	sh.DATE_CMPL, ";
				$sql .= "	Convert(varchar(10),sh.DATE_CMPL,111) as DATE_CMPL3, ";
				$sql .= "	sh.rowid ";
				$sql .= " from ";
				$sql .= " 	nsa.SHPORD_HDR sh ";
				$sql .= " where ";
				$sql .= " 	sh.STAT_REC_SO = 'C' ";
				$sql .= " 	and ";
				$sql .= " 	sh.CODE_USER_1 = '' ";
				$sql .= " order by ";
				$sql .= " 	sh.ID_SO asc ";
				$DEBUG=1;
				QueryDatabase($sql, $results);
				$DEBUG=0;
				$num_rows = mssql_num_rows($results);
				error_log("### runSO_HIST found " . $num_rows . " Completed Shop Orders to post");

				if ($num_rows > 0) {
					$myLink = mysqli_connect($my_DBServer,$my_username,$my_password,$my_db);
					if (mysqli_connect_errno()) {
					    error_log("### Connect failed: " . mysqli_connect_error());
					    exit();
					}

					while ($row = mssql_fetch_assoc($results)) {
						///Get complete descriptions from ITMMAS_DESCR for ID_ITEM_PAR
						$DESCR_FULL = $row['DESCR_ITEM_1'] . "<br>" . $row['DESCR_ITEM_2'] . "<br>";
						$SPECS_FULL = "";
						$FileName = "";
						$FileMd5 = "";
						$ITEM_SUM_ROWID = NULL;

						$sql1  = " select ";
						$sql1 .= "	d.SEQ_DESCR, ";
						$sql1 .= "	d.DESCR_ADDL ";
						$sql1 .= " from ";
						$sql1 .= " 	nsa.ITMMAS_DESCR d ";
						$sql1 .= " where ";
						$sql1 .= " 	d.ID_ITEM = '". $row['ID_ITEM_PAR'] ."' ";
						$sql1 .= " order by ";
						$sql1 .= " 	d.SEQ_DESCR asc ";
						QueryDatabase($sql1, $results1);

						while ($row1 = mssql_fetch_assoc($results1)) {
							if ($row1['SEQ_DESCR'] < 900) {
								$DESCR_FULL .= $row1['DESCR_ADDL'] . "<br>";
							} else {
								$SPECS_FULL .= $row1['DESCR_ADDL'] . "<br>";
							}
						}

						///Lookup Product Summary rowid
						$sql1  = "select top 1";
						$sql1 .= "	xi.ID_ITEM, ";
						$sql1 .= "	xd.NAME_FILE ";
						$sql1 .= " from ";
						$sql1 .= "	nsa.DOC_XREF_ITEM xi ";
						$sql1 .= "	join nsa.DOC_XREF_DTL xd ";
						$sql1 .= "	on xi.NAME_DOC = xd.NAME_DOC ";
						$sql1 .= " where ";
						$sql1 .= "	xd.DESCR_DOC = 'PRODUCT SUMMARY' ";
						$sql1 .= "	and ID_ITEM = '". $row['ID_ITEM_PAR'] ."'";
						$sql1 .= " order by xd.rowid desc";
						QueryDatabase($sql1, $results1);

						while ($row1 = mssql_fetch_assoc($results1)) {
							$FileName = $row1['NAME_FILE'];
							$FileName = str_replace("\\\\FS1\\NETSHARE\\WORK INSTRUCTIONS\\PS PDFS\\","",$FileName);
							$FileName = str_replace('"','',$FileName);
							$FullFileName = "PS_PDFS/".$FileName;
							//$FileName = str_replace(' ','_',$FileName);

							$FileContents = file_get_contents($FullFileName );
							error_log("### FileContents: " . $FileContents);

							$FileMd5 = md5($FileContents);
							error_log("### FileMd5: " . $FileMd5);

							$FileSize = strlen($FileContents);
							error_log("### FileSize: " . $FileSize);

							if ($FileMd5 <> "") {
								$mysql1  = "SELECT * from ITEM_SUM ";
								$mysql1 .= " WHERE FILE_NAME = '".$FileName."'";
								$mysql1 .= " ORDER BY rowid desc limit 1";
								$myresult1 = mysqli_query($myLink, $mysql1);
								if (mysqli_num_rows($myresult1) > 0) {
									//FILE NAME FOUND IN MYSQL, COMPARE MD5s
									while ($myrow1 = mysqli_fetch_assoc($myresult1)) {
										if ($myrow1['MD5'] == $FileMd5) {
											//SAME FILE AS IN MYSQL, USE rowid
											$ITEM_SUM_ROWID = $myrow1['rowid'];
										} else {
											//INSERT FILE INTO MYSQL
											$mysql2  = "INSERT into ITEM_SUM (";
											$mysql2 .= " FILE_NAME, ";
											$mysql2 .= " FILE_TYPE, ";
											$mysql2 .= " FILE_SIZE, ";
											$mysql2 .= " FILE_CONTENT, ";
											$mysql2 .= " MD5 ";
											$mysql2 .= ") VALUES ( ";
											$mysql2 .= "'" . $FileName . "', ";
											$mysql2 .= "'" . mysqli_real_escape_string($myLink,'application/pdf') . "', ";
											$mysql2 .= "'" . $FileSize . "', ";
											$mysql2 .= "'" . mysqli_real_escape_string($myLink,$FileContents) . "', ";
											$mysql2 .= "'" . $FileMd5 . "' ";
											$mysql2 .= ")";
											if (mysqli_query($myLink, $mysql2) === TRUE) {
												$ITEM_SUM_ROWID = mysqli_insert_id($myLink);
												error_log("### INSERTED ITEM_SUM rowid: " . mysqli_insert_id($myLink));
											}
										}
									}
								} else {
									//INSERT FILE INTO MYSQL
									$mysql2  = "INSERT into ITEM_SUM (";
									$mysql2 .= " FILE_NAME, ";
									$mysql2 .= " FILE_TYPE, ";
									$mysql2 .= " FILE_SIZE, ";
									$mysql2 .= " FILE_CONTENT, ";
									$mysql2 .= " MD5 ";
									$mysql2 .= ") VALUES ( ";
									$mysql2 .= "'" . $FileName . "', ";
									$mysql2 .= "'" . mysqli_real_escape_string($myLink,'application/pdf') . "', ";
									$mysql2 .= "'" . $FileSize . "', ";
									$mysql2 .= "'" . mysqli_real_escape_string($myLink,$FileContents) . "', ";
									$mysql2 .= "'" . $FileMd5 . "' ";
									$mysql2 .= ")";
									if (mysqli_query($myLink, $mysql2) === TRUE) {
										$ITEM_SUM_ROWID = mysqli_insert_id($myLink);
										error_log("### INSERTED ITEM_SUM rowid: " . mysqli_insert_id($myLink));
									}
								}
							}
						}


						///Insert record into WS1 MySQL table
						$mysql1  = "INSERT INTO SO_HIST_HDR( ";
						$mysql1 .= " ID_LOC, ";
						$mysql1 .= " ID_SO, ";
						$mysql1 .= " ID_ITEM, ";
						$mysql1 .= " QTY_ORD, ";
						$mysql1 .= " QTY_CMPL, ";
						$mysql1 .= " DATE_CMPL, ";
						$mysql1 .= " DESCR_FULL, ";
						$mysql1 .= " SPECS_FULL, ";
						$mysql1 .= " ITEM_SUM_ROWID ";
						$mysql1 .= ") VALUES ( ";
						$mysql1 .= "'" . $row['ID_LOC'] . "', ";
						$mysql1 .= "'" . $row['ID_SO'] . "', ";
						$mysql1 .= "'" . $row['ID_ITEM_PAR'] . "', ";
						$mysql1 .= "'" . $row['QTY_ORD'] . "', ";
						$mysql1 .= "'" . $row['QTY_CMPL'] . "', ";
						$mysql1 .= "'" . $row['DATE_CMPL3'] . "', ";
						$mysql1 .= "'" . mysqli_real_escape_string($myLink,$DESCR_FULL) . "', ";
						$mysql1 .= "'" . mysqli_real_escape_string($myLink,$SPECS_FULL) . "', ";
						$mysql1 .= "'" . $ITEM_SUM_ROWID . "' ";
						$mysql1 .= ")";
						error_log("mysql1: " . $mysql1);


						if (mysqli_query($myLink, $mysql1) === TRUE) {
							error_log("### INSERTED SO_HIST_HDR rowid: " . mysqli_insert_id($myLink));
							$sql1  = " UPDATE nsa.SHPORD_HDR ";
							$sql1 .= " SET CODE_USER_1 = 'T' ";
							$sql1 .= " WHERE rowid = " . $row['rowid'];
							QueryDatabase($sql1, $results1);
							while ($row1 = mssql_fetch_assoc($results1)) {
								error_log("### UPDATED SHPORD_HDR CODE_USER_1flag on rowid: " . $row['rowid']);
							}
						}


					}
					mysqli_close($myLink);
				}
				error_log("### runSO_HIST DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runSO_HIST ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runSO_HIST finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runSO_HIST cannot disconnect from database");
		}
	}
?>
