<?php


	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");

	$DEBUG = 1;
	$DB_TEST_FLAG = "";
	//$DB_TEST_FLAG = "_TEST";


	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runBYTE_CSV_IMPORT cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runBYTE_CSV_IMPORT cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runBYTE_CSV_IMPORT started at " . date('Y-m-d g:i:s a'));
			error_log("### runBYTE_CSV_IMPORT CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			/////////////////////////////////////////////////
			// AFTER WE MERGE TCM COMPANY CODES, CHANGE THESE VARIABLE VALUES
			/////////////////////////////////////////////////
			//////
			//////
			$companySchema = "nsa";
			$LocationID = "20";
			//////
			//////
			/////////////////////////////////////////////////
			/////////////////////////////////////////////////

			$sql  = "SELECT ";
			$sql .= " * ";
			$sql .= " FROM nsa.RUNNING_PROC ";
			$sql .= " WHERE PROC_NAME = 'runBYTE_CSV_IMPORT' ";
			$sql .= " and FLAG_RUNNING = '1' ";
			$sql .= " and DATE_EXP > getDate()";
			QueryDatabase($sql, $results);
			if (mssql_num_rows($results) == 0) {
				$sql1  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql1 .= " PROC_NAME, ";
				$sql1 .= " FLAG_RUNNING, ";
				$sql1 .= " DATE_ADD, ";
				$sql1 .= " DATE_EXP ";
				$sql1 .= ") VALUES ( ";
				$sql1 .= "'runBYTE_CSV_IMPORT', ";
				$sql1 .= "1, ";
				$sql1 .= " getDate(), ";
				$sql1 .= " dateadd(minute,5,getDate()) ";
				$sql1 .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql1, $results1);
				$row1 = mssql_fetch_assoc($results1);
				$ProcRowID = $row1['LAST_INSERT_ID'];
				error_log("### runBYTE_CSV_IMPORT SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$baseDir = '/mnt/Byte-TCM';
				$archiveDir = $baseDir . "/Archive/";
				$pendingDir = $baseDir . "/Pending/";
				$importedDir = $baseDir . "/Imported/";
				$errorDir = $baseDir . "/Error/";
				$runID_Timestamp = time();

				////DIRECTORY LISTING FOR .CSV FILES
				$files = glob($baseDir."/*.csv");

				error_log("### ARRAY SIZE: ".count($files));
				foreach($files as $OrigFilePath) {
					$OrigFile = substr($OrigFilePath,strrpos($OrigFilePath,"/"));
					error_log("### CSV File found: " . $OrigFile);
					$NewFile = str_ireplace(".csv","_".$runID_Timestamp.".csv",$OrigFile);
					$NewFilePath = str_ireplace(".csv","_".$runID_Timestamp.".csv",$OrigFilePath);
					error_log("### Renaming " . $OrigFile . " to " . $NewFile);
					rename($OrigFilePath, $NewFilePath);

					$filename_archive = $archiveDir . $NewFile;
					$filename_pending = $pendingDir . $NewFile;

					error_log("### Copying file to archive");
					if (!copy($NewFilePath, $filename_archive)) {
						error_log("### FAILED to Copy file to archive: " . $NewFile);
					}
					error_log("### Moving file to pending");
					if (!rename($NewFilePath, $filename_pending)) {
						error_log("### FAILED to Move file to pending: " . $NewFile);
					}
				}
				
				$PendingFiles = array();
				$PendingDirArray = scandir($pendingDir);
				foreach ($PendingDirArray as $PendingDirFile) {
					if (substr(strtoupper($PendingDirFile), -4) == ".CSV") {
						error_log("### PendingDirFile: " . $PendingDirFile);
						$PendingFiles[] = $PendingDirFile;
					}
				}

				$numFiles = count($PendingFiles);
				error_log("### numFiles: " . $numFiles);

				if ($numFiles > 0) {
					$sql1 = "SET ANSI_NULLS ON";
					QueryDatabase($sql1, $results1);
					$sql1 = "SET ANSI_WARNINGS ON";
					QueryDatabase($sql1, $results1);
					$sql1 = "SET QUOTED_IDENTIFIER ON";
					QueryDatabase($sql1, $results1);
					$sql1 = "SET ANSI_PADDING ON";
					QueryDatabase($sql1, $results1);
					$sql1 = "SET CONCAT_NULL_YIELDS_NULL ON";
					QueryDatabase($sql1, $results1);
					
					foreach ($PendingFiles as $File) {
						$FLAG_ERR = "";
						$REASON_ERR = "";
						$ERR_BODY = "";
						$VALID_ITEM_COUNT = 0;
						$ALTKEY_ITEM_COUNT = 0;
						$MISSING_ITEM_COUNT = 0;
						error_log("### NEED TO PARSE FILE: " . $File);
						$fileRow = 1;

						if (($handle = fopen($pendingDir . $File, "r")) !== FALSE) {
							$ERR_BODY .= "\r\n" . $File ."\r\n\r\n";
							while (($fileData = fgetcsv($handle, 1000, ",")) !== FALSE) {
								$numFields = count($fileData);
						    	if ($numFields <> 3) {
						    		error_log("Incorrect number of fields in line " . $fileRow);
						    		$FLAG_ERR = "ERROR";
						    		$REASON_ERR .= "Incorrect number of fields in line. ";
						    	} else {
						    		error_log("### " . $File . " ROW NUMBER: " . $fileRow . " - Parsing");
						    		$ID_ITEM = $fileData[0];
						    		$QTY_OPEN = $fileData[1];
						    		$PCT_COMPLETE = $fileData[2];

									$sql2  = " INSERT INTO ".$companySchema.".BYTE_CSV_IMPORT ( ";
									$sql2 .= "  ID_ITEM, ";
									$sql2 .= "  QTY_OPEN, ";
									$sql2 .= "  PCT_COMPLETE, ";
									$sql2 .= "  FILE_NAME, ";
									$sql2 .= "  DATE_ADD, ";
									$sql2 .= "  RUNID_TIMESTAMP ";
									$sql2 .= " ) VALUES ( ";
									$sql2 .= "  '". ms_escape_string($ID_ITEM) ."', ";
									$sql2 .= "  '". substr(stripNonNumericCharsNoSpace($QTY_OPEN),0,8) . "', ";
									$sql2 .= "  '". ms_escape_string($PCT_COMPLETE) ."', ";
									$sql2 .= "  '". ms_escape_string($File) ."', ";
									$sql2 .= "  getDate(), ";
									$sql2 .= "  '". $runID_Timestamp ."' ";
									
									$sql2 .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
									QueryDatabase($sql2, $results2);
									error_log("### " . $File . " ROW NUMBER: " . $fileRow . " - Inserting");
									$row2 = mssql_fetch_assoc($results2);
									$LAST_INSERT_ID = $row2['LAST_INSERT_ID'];
							    	$fileRow++;
						    	}
							}
							fclose($handle);
						}

						$sql1  = "SELECT ";
						$sql1 .= " c.ID_ITEM, ";
						$sql1 .= " c.QTY_OPEN, ";
						$sql1 .= " c.PCT_COMPLETE, ";
						$sql1 .= " c.rowid as c_rowid, ";
						$sql1 .= " b.ID_ITEM as b_ID_ITEM, ";
 						//$sql1 .= " b2.KEY_ALT as b2_KEY_ALT, ";
 						$sql1 .= " b2.VAL_STRING_ATTR, ";
 						$sql1 .= " b2.VAL_STRING_ATTR as b2_KEY_ALT, ";
 						$sql1 .= " b2.ID_ITEM as b2_ID_ITEM, ";
 						$sql1 .= " isnull(b.ID_ITEM,isnull(b2.ID_ITEM,'NOT_FOUND')) as tcm_ID_ITEM, ";
						$sql1 .= " isnull(b.CODE_COMM,isnull(b2.CODE_COMM,'')) as tcm_CODE_COMM, ";
						$sql1 .= " l.ID_LOC as tcm_ID_LOC, ";
						$sql1 .= " l.QTY_ONORD as tcm_QTY_ONORD ";
						$sql1 .= " FROM ".$companySchema.".BYTE_CSV_IMPORT c ";
						$sql1 .= " LEFT JOIN ".$companySchema.".ITMMAS_BASE b ";
						$sql1 .= " on c.ID_ITEM = b.ID_ITEM ";
						
						//$sql1 .= " LEFT JOIN ".$companySchema.".ITMMAS_BASE b2 ";
						//$sql1 .= " on c.ID_ITEM = b2.KEY_ALT ";
 						$sql1 .= " LEFT JOIN ".$companySchema.".IM_CMCD_ATTR_VALUE b2 ";
 						$sql1 .= " on c.ID_ITEM = b2.VAL_STRING_ATTR ";
 						$sql1 .= " and b2.id_attr = 'SF_XREF' ";
 						
 						$sql1 .= " LEFT JOIN ".$companySchema.".ITMMAS_LOC" . $DB_TEST_FLAG . " l ";
 						$sql1 .= " on isnull(b.ID_ITEM,isnull(b2.ID_ITEM,'')) = l.ID_ITEM ";
 						$sql1 .= " and l.ID_LOC = '".$LocationID."' ";
						$sql1 .= " WHERE c.RUNID_TIMESTAMP = '".$runID_Timestamp."' ";
						$sql1 .= " order by c_rowid asc ";
						QueryDatabase($sql1, $results1);
						while ($row1 = mssql_fetch_assoc($results1)) {
							//if (is_null($row1["b_ID_ITEM"]) && !is_null($row1["b2_KEY_ALT"])) {
							if (is_null($row1["b_ID_ITEM"]) && !is_null($row1["VAL_STRING_ATTR"])) {
								//error_log("### ALT KEY Item Found: ".$row1["ID_ITEM"]." in TCM.");
								error_log("### ATTRIBUTE Item Found: ".$row1["ID_ITEM"]." in TCM.");
								$ALTKEY_ITEM_COUNT++;
							}

							if ($row1["tcm_ID_ITEM"] == "NOT_FOUND") {
								//FOR RECORDS NOT FOUND IN TCM, add to list for email
								error_log("### MISSING ITEM: ".$row1["ID_ITEM"]." in TCM.");
								$MISSING_ITEM_COUNT++;
								
								if ($MISSING_ITEM_COUNT == 1) { //FIRST MISSING ITEM, CREATE FILE
									$missingItemsCSVfilename = "/tmp/BYTE_CSV_OUTPUT/MISSING_ITEMS_". $File;
									error_log("MISSING ITEMS FILE NAME: ".$missingItemsCSVfilename);
									$fp = fopen($missingItemsCSVfilename, 'w');

									$colNamesA = array();
									for($i = 0; $i < mssql_num_fields($results1); $i++) {
									    $field_info = mssql_fetch_field($results1, $i);
									    $field = $field_info->name;
									    $colNamesA[$i] =  $field;
									}
									fputcsv($fp, $colNamesA);
								}
								fputcsv($fp, $row1, ",", "\"");

								$sql2  = "UPDATE ".$companySchema.".BYTE_CSV_IMPORT";
								$sql2 .= " set ITEM_STATUS = 'NOT_FOUND' ";
								$sql2 .= " WHERE rowid = ".$row1["c_rowid"];
								QueryDatabase($sql2, $results2);
							} else {
								//FOR RECORDS WITH VALUE IN tcm_ID_ITEM, UPDATE QTY IN ITMMAS_LOC	
								error_log("### Valid Item: ".$row1["ID_ITEM"]." found in TCM.");
								$VALID_ITEM_COUNT++;

								$sql2  = "UPDATE ".$companySchema.".BYTE_CSV_IMPORT";
								$sql2 .= " set tcm_ID_ITEM = '".$row1["tcm_ID_ITEM"]."' ";
								$sql2 .= " ,ITEM_STATUS = 'VALID' ";
								$sql2 .= " WHERE rowid = ".$row1["c_rowid"];
								QueryDatabase($sql2, $results2);
							}
						}

						if ($MISSING_ITEM_COUNT > 0) {
							fclose($fp);
						}

						error_log("### VALID ITEM COUNT: ".$VALID_ITEM_COUNT." found in TCM.");
						error_log("### ALTKEY ITEM COUNT: ".$ALTKEY_ITEM_COUNT." found in TCM.");
						error_log("### MISSING ITEM COUNT: ".$MISSING_ITEM_COUNT." missing in TCM.");

						switch($FLAG_ERR) {
							case "":

								///////////////////
								// GRAB A BACKUP COPY OF ITMMAS_LOC, JUST IN CASE
								///////////////////
								error_log("### DROPING OLD BACKUP COPY OF ITMMAS_LOC TABLE FOR ".date("l"));
								$sql1  = "IF OBJECT_ID('".$companySchema.".ITMMAS_LOC__B4_BYTE_CSV_".date("l") ."') IS NOT NULL ";
								$sql1 .= " DROP TABLE ".$companySchema.".ITMMAS_LOC__B4_BYTE_CSV_".date("l") ." ";
								QueryDatabase($sql1, $results1);

								error_log("### MAKING BACKUP COPY OF ITMMAS_LOC TABLE");
								$sql1  = " SELECT * INTO ".$companySchema.".ITMMAS_LOC__B4_BYTE_CSV_".date("l") ;
								$sql1 .= " FROM ".$companySchema.".ITMMAS_LOC" . $DB_TEST_FLAG . " ";
								QueryDatabase($sql1, $results1);




								///////////////////
								// UPDATE ALL nsa.ITMMAS_LOC QTY_ONORD = 0 WHERE ID_LOC = '20' AND CODE_COMM = 'FG' 
								///////////////////
								error_log("### ZEROING OUT ITMMAS_LOC.QTY_ONORD FOR FG ITEMS");
								$sql1  = "UPDATE ".$companySchema.".ITMMAS_LOC" . $DB_TEST_FLAG . " ";
								$sql1 .= " set QTY_ONORD = 0 ";
								$sql1 .= " FROM ".$companySchema.".ITMMAS_LOC" . $DB_TEST_FLAG . " l ";
								$sql1 .= " LEFT JOIN ".$companySchema.".ITMMAS_BASE b ";
								$sql1 .= " on l.ID_ITEM = b.ID_ITEM ";
								$sql1 .= " WHERE b.CODE_COMM = 'FG' ";
								$sql1 .= " and b.TYPE_COST <> 'KF' ";//EXCLUDE KUNZ ITEMS
								$sql1 .= " and l.ID_LOC = '".$LocationID."' ";
								QueryDatabase($sql1, $results1);


								///////////////////
								// UPDATE QTY_ONORD ACCORDING TO VALID RECORDS FROM FILE
								///////////////////
								error_log("### UPDATING ITMMAS_LOC.QTY_ONORD WITH VALUES FROM FILE");
								$sql1  = "UPDATE ".$companySchema.".ITMMAS_LOC" . $DB_TEST_FLAG . "";
								$sql1 .= " set QTY_ONORD = bci.QTY_OPEN ";
								$sql1 .= " FROM ".$companySchema.".BYTE_CSV_IMPORT bci ";
								$sql1 .= " LEFT JOIN nsa.ITMMAS_LOC" . $DB_TEST_FLAG . " il ";
								$sql1 .= " on bci.tcm_ID_ITEM = il.ID_ITEM ";
								$sql1 .= " WHERE bci.ITEM_STATUS = 'VALID' ";
								$sql1 .= " and bci.RUNID_TIMESTAMP = '".$runID_Timestamp."' ";
								$sql1 .= " and il.ID_LOC = '".$LocationID."' ";
								QueryDatabase($sql1, $results1);


								///////////////////
								// QUERY OPEN POs FOR FG ITEMS
								// THEN ADD THEIR OPEN QUANTITIES TO QTY_ONORD
								///////////////////
								error_log("### UPDATING ITMMAS_LOC.QTY_ONORD TO INCLUDE OPEN PO QTYS FOR FG ITEMS");
								$sql1  = " SELECT ";
								$sql1 .= " pol.ID_ITEM, ";
								$sql1 .= " sum((pol.QTY_ORD - pol.QTY_RCV)) as SUM_QTY_DUE ";
								$sql1 .= " FROM ".$companySchema.".PORHDR_HDR poh ";
								$sql1 .= " LEFT JOIN ".$companySchema.".PORLIN_ITEM pol ";
								$sql1 .= " on poh.ID_PO = pol.ID_PO ";
								$sql1 .= " LEFT JOIN ".$companySchema.".ITMMAS_BASE ib ";
								$sql1 .= " on pol.ID_ITEM = ib.ID_ITEM ";
								$sql1 .= " WHERE poh.FLAG_STAT_PO not in ('C', 'X') ";
								$sql1 .= " and pol.FLAG_STAT_LINE_PO != 'C' ";
								$sql1 .= " and pol.QTY_RCV < pol.QTY_ORD ";
								$sql1 .= " and ib.CODE_COMM = 'FG' ";
								$sql1 .= " and ib.TYPE_COST <> 'KF' ";//EXCLUDE KUNZ ITEMS
								$sql1 .= " and poh.ID_LOC_SHIPTO = '".$LocationID."' ";
								$sql1 .= " GROUP BY pol.ID_ITEM ";
								QueryDatabase($sql1, $results1);
								while ($row1 = mssql_fetch_assoc($results1)) {
									$sql2  = "UPDATE ".$companySchema.".ITMMAS_LOC" . $DB_TEST_FLAG . " ";
									$sql2 .= " SET QTY_ONORD = (QTY_ONORD + ".$row1['SUM_QTY_DUE'].") ";
									$sql2 .= " WHERE ID_ITEM = '".$row1['ID_ITEM']."'";
									$sql2 .= " and ID_LOC = '".$LocationID."' ";
									QueryDatabase($sql2, $results2);
								}

								error_log("### UPDATING BYTE_CSV_IMPORT TO FILE_STATUS = 'IMPORTED' for RUNID_TIMESTAMP = '" . $runID_Timestamp . "'");
								$sql1  = "UPDATE ".$companySchema.".BYTE_CSV_IMPORT";
								$sql1 .= " set FILE_STATUS = 'IMPORTED' ";
								$sql1 .= " WHERE RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
								$sql1 .= " and FILE_NAME='" . $File . "' ";
								QueryDatabase($sql1, $results1);

								//MOVE FILE FROM PENDING TO Imported
								rename($pendingDir . $File, $importedDir . $File . ".imported");
								error_log("### " . $File . " Moved from Pending to Imported");

								if ($TEST_ENV) {
									$head = array(
								    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
								    	'from'    =>array('ByteImport@thinkNSA.com' =>'BYTE Import'),
							    	);
							    } else {
						    		$head = array(
								    	//'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
								    	'to'      =>array('tbielenberg@thinknsa.com'=>'Toby Bielenberg','cluna@thinknsa.com'=>'Carla Luna'),
								    	'from'    =>array('ByteImport@thinkNSA.com' =>'BYTE Import'),
								    	'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne','bboyd@thinknsa.com'=>'Brandon Boyd'),
							    	);
								}

								if($MISSING_ITEM_COUNT > 0) {
									//SEND SUMMARY EMAIL WITH CSV OF MISSING ITEMS AS ATTACHMENT
									$subject = "Byte CSV Import Summary WITH MISSING ITEMS: " . $File;
									$body ='';
									$body.="<div style='font-family:Arial;font-size:10pt;'>";
									$body.=    "<br>"."VALID ITEM COUNT: ".$VALID_ITEM_COUNT." found in TCM.";
									$body.=    "<br>"."ALTKEY ITEM COUNT: ".$ALTKEY_ITEM_COUNT." found in TCM.";
									$body.=    "<br>"."MISSING ITEM COUNT: ".$MISSING_ITEM_COUNT." missing in TCM.";
									$body.=    "<br>"."";
									$body.=    "<br>"."The attached file contains BYTE items that do not exist in TCM.";
									$body.=    "<br>"."";
									$body.="</div>";
									$files = array($missingItemsCSVfilename);
									mail::send($head,$subject,$body, $files);

									if (!unlink($missingItemsCSVfilename)) {
									  error_log("### Error deleting ".$missingItemsCSVfilename);
									} else {
									  error_log("### Deleted ". $missingItemsCSVfilename);
									}

								} else {
									$subject = "Byte CSV Import Summary: " . $File;
									$body ='';
									$body.="<div style='font-family:Arial;font-size:10pt;'>";
									$body.=    "<br>"."VALID ITEM COUNT: ".$VALID_ITEM_COUNT." found in TCM.";
									$body.=    "<br>"."ALTKEY ITEM COUNT: ".$ALTKEY_ITEM_COUNT." found in TCM.";
									$body.=    "<br>"."";
									$body.="</div>";
									mail::send($head,$subject,$body);
								}
							







							break;

							case "ERROR":
								$sql1  = "UPDATE ".$companySchema.".BYTE_CSV_IMPORT";
								$sql1 .= " set FILE_STATUS = 'ERROR' ";
								$sql1 .= " WHERE RUNID_TIMESTAMP = '" . $runID_Timestamp . "' ";
								$sql1 .= " and FILE_NAME='" . $File . "' ";
								QueryDatabase($sql1, $results1);
								error_log("### ERROR records File: ". $File .", runID_timestamp " . $runID_Timestamp . " updated to 'ERROR'");

								//MOVE FILE FROM PENDING TO Error
								rename($pendingDir . $File, $errorDir . $File . ".error");
								error_log("### " . $File . " Moved from Pending to ERROR");
								$to = "gvandyne@thinknsa.com";

								if ($TEST_ENV) {
									$to = "gvandyne@thinknsa.com";
								}

								$subject = "Byte CSV File Error: " . $File;
								$body = "Byte CSV File Error: " . $File ."\r\n REASON: " . $REASON_ERR;
								$body .= $ERR_BODY;
								$headers = "From: ByteImport@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
								mail($to, $subject, $body, $headers);
								error_log("### Error email sent to: " . $to);
							break;

						} // end switch
					} //end foreach file
				} else {
					error_log("### ERROR - NO BYTE FILE TO IMPORT");
					$to = "group-it@thinknsa.com";

					if ($TEST_ENV) {
						$to = "gvandyne@thinknsa.com";
					}

					$subject = "NO Byte CSV File To Import";
					$body = "No Byte CSV File was found to import.";
					$headers = "From: ByteImport@thinknsa.com" . "\r\n" . "X-Mailer: PHP/" . phpversion();
					mail($to, $subject, $body, $headers);
					error_log("### No File Error email sent to: " . $to);
				}

				$sql1 = "SET ANSI_NULLS OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET ANSI_PADDING OFF";
				QueryDatabase($sql1, $results1);
				$sql1 = "SET CONCAT_NULL_YIELDS_NULL OFF";
				QueryDatabase($sql1, $results1);

				error_log("### runBYTE_CSV_IMPORT DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql1  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql1 .= " WHERE ";
				$sql1 .= " rowid = " . $ProcRowID;
				QueryDatabase($sql1, $results1);

			} else {
				error_log("### runBYTE_CSV_IMPORT ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}				
			error_log("### runBYTE_CSV_IMPORT finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runBYTE_CSV_IMPORT cannot disconnect from database");
		}
	}
?>