<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/mail.class.php');
	//$DB_TEST_FLAG = "_TEST";
	$DB_TEST_FLAG = "";

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runPOST_LOT_TRACKING_TO_MATL_ISS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runPOST_LOT_TRACKING_TO_MATL_ISS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS started at " . date('Y-m-d g:i:s a'));
			error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runPOST_LOT_TRACKING_TO_MATL_ISS' ";
			$sql .= "	and ";
			$sql .= "	FLAG_RUNNING = '1' ";
			$sql .= "	and ";
			$sql .= "	DATE_EXP > getDate()";
			QueryDatabase($sql, $results);

			if (mssql_num_rows($results) == 0) {
				$sql  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql .= " PROC_NAME, ";
				$sql .= " FLAG_RUNNING, ";
				$sql .= " DATE_ADD, ";
				$sql .= " DATE_EXP ";
				$sql .= ") VALUES ( ";
				$sql .= "'runPOST_LOT_TRACKING_TO_MATL_ISS', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

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


				$filename = "/tmp/POST_LOT_TRACKING_EXCEPTIONS.csv";
				error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS opening file: ".$filename);
				$fp = fopen($filename, 'w');
				$List = "";

				///////////////////////////////////////////////////////////////////
				///////////////GET LIST OF TRACKING NUMBERS LOGGED
				///////////////////////////////////////////////////////////////////
				error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS QUERYING FOR UNPOSTED LOT NUMBERS ");

/*
				$sql  = " SELECT ";
 				$sql .= " ml.rowid as MU_MARKER_rowid, ";
  				$sql .= " lt.rowid as MU_LOT_TRACKING_rowid, ";
  				$sql .= " lt.DATE_ADD as LT_DATE_ADD, ";
  				$sql .= " '10' as ID_LOC, ";
  				$sql .= " muso.ID_SO, ";
  				$sql .= " muso.SUFX_SO, ";
  				$sql .= " ml.MARKER_ID_ITEM_COMP as MARKER_ID_ITEM, ";
  				$sql .= " ml.SO_ID_ITEM_COMP as SO_ID_ITEM, ";
  				$sql .= " lt.ID_LOT as LT_KEY_BIN_3, ";
  				$sql .= " round(((sm.QTY_PER * sh.QTY_ORD) / (sm2.SO_QTY_PER_X_QTY_ORD)) * ((ml.MARKER_LENGTH +4) * ml.MARKER_LAYERS),0) as QTY_ISS, "; 
  				$sql .= " 'N' as ISS_STATUS ";

 				$sql .= " FROM nsa.MU_LOT_TRACKING lt WITH (NOLOCK) ";
 				
 				$sql .= " LEFT JOIN nsa.MU_SO muso WITH (NOLOCK) ";
  				$sql .= "  on muso.MU_MARKER_rowid = lt.MU_MARKER_rowid ";
  				$sql .= "  and muso.FLAG_DEL = '' ";

 				$sql .= " LEFT JOIN nsa.MU_MARKER_LOG ml WITH (NOLOCK) ";
 				$sql .= "  on lt.MU_MARKER_rowid = ml.rowid ";
 				$sql .= "  and ml.FLAG_DEL = '' ";

 				$sql .= " LEFT JOIN nsa.BINTAG_ONHD boh WITH (NOLOCK) ";
 				$sql .= "  on ml.MARKER_ID_ITEM_COMP = boh.ID_ITEM ";
 				$sql .= "  and lt.ID_LOT = boh.KEY_BIN_3 ";

 				$sql .= " LEFT JOIN nsa.SHPORD_MATL sm WITH (NOLOCK) ";
 				$sql .= "  on ltrim(muso.ID_SO) = ltrim(sm.ID_SO) ";
 				$sql .= "  and muso.SUFX_SO = sm.SUFX_SO ";
 				$sql .= "  and ml.MARKER_ID_ITEM_COMP = sm.ID_ITEM_COMP ";
 				$sql .= "  and sm.ID_LOC = '10' ";

 				$sql .= " LEFT JOIN nsa.SHPORD_HDR sh ";
 				$sql .= "  on muso.ID_SO = ltrim(sh.ID_SO) ";
 				$sql .= "  and muso.SUFX_SO = sh.SUFX_SO ";

 				$sql .= " LEFT JOIN ( ";
 				$sql .= "  SELECT ";
 				$sql .= "  muso1.MU_MARKER_rowid, ";
 				$sql .= "  sm1.ID_ITEM_COMP, ";
 				$sql .= "  sum(sm1.QTY_PER * sh1.QTY_ORD) as SO_QTY_PER_X_QTY_ORD ";

 				$sql .= "  FROM nsa.SHPORD_MATL sm1 WITH (NOLOCK) ";
 
 				$sql .= "  LEFT JOIN nsa.MU_SO muso1 WITH (NOLOCK) ";
 				$sql .= "  on ltrim(sm1.ID_SO) = muso1.ID_SO ";
 				$sql .= "  and sm1.SUFX_SO = muso1.SUFX_SO ";
 
 				$sql .= "  LEFT JOIN nsa.MU_MARKER_LOG ml1 WITH (NOLOCK) ";
 				$sql .= "  on muso1.MU_MARKER_rowid = ml1.rowid ";
 				$sql .= "  and sm1.ID_ITEM_COMP = ml1.SO_ID_ITEM_COMP ";
 
 				$sql .= "  LEFT JOIN nsa.SHPORD_HDR sh1 ";
 				$sql .= "  on muso1.ID_SO = ltrim(sh1.ID_SO) ";
 				$sql .= "  and muso1.SUFX_SO = sh1.SUFX_SO ";
 
 				$sql .= "  WHERE muso1.FLAG_DEL = '' ";
 				$sql .= "  and sm1.ID_ITEM_COMP = ml1.MARKER_ID_ITEM_COMP ";
 
 				$sql .= "  GROUP BY muso1.MU_MARKER_rowid, ";
 				$sql .= "  sm1.ID_ITEM_COMP ";

 				$sql .= " ) sm2 ";
 				$sql .= "  on ml.rowid = sm2.MU_MARKER_rowid ";
 				$sql .= "  and ml.MARKER_ID_ITEM_COMP = sm2.ID_ITEM_COMP ";

 				$sql .= " WHERE lt.DATETIME_POST_TO_MATL_ISS is null ";
 				$sql .= "  and lt.DATE_ADD >= '2023-02-01' ";
 				$sql .= "  and lt.FLAG_DEL = '' ";
 				$sql .= "  and muso.ID_SO is not null ";

 				$sql .= " ORDER BY lt.DATE_ADD asc ";
*/

				$sql  = "  SELECT ";
				$sql .= "  ml.rowid as MU_MARKER_rowid, ";
				$sql .= "  lt.rowid as MU_LOT_TRACKING_rowid, ";
				$sql .= "  lt.DATE_ADD as LT_DATE_ADD, ";
				$sql .= "  '10' as ID_LOC, ";
				$sql .= "  muso.ID_SO, ";
				$sql .= "  muso.SUFX_SO, ";
				$sql .= "  ml.MARKER_ID_ITEM_COMP as MARKER_ID_ITEM, ";
				$sql .= "  ml.SO_ID_ITEM_COMP as SO_ID_ITEM, ";
				$sql .= "  CASE ";
				$sql .= "   WHEN  smMKR2.SO_QTY_PER_X_QTY_ORD IS NOT NULL ";
				$sql .= "    THEN ml.MARKER_ID_ITEM_COMP ";
				$sql .= "   ELSE ml.SO_ID_ITEM_COMP ";
				$sql .= "  END as ISSUED_ID_ITEM, ";

				$sql .= "  round(((smMKR.QTY_PER * sh.QTY_ORD) / (smMKR2.SO_QTY_PER_X_QTY_ORD)) * ((ml.MARKER_LENGTH +4) * ml.MARKER_LAYERS),0) as QTY_ISS_MKR,";
				$sql .= "  round(((smSO.QTY_PER * sh.QTY_ORD) / (smSO2.SO_QTY_PER_X_QTY_ORD)) * ((ml.MARKER_LENGTH +4) * ml.MARKER_LAYERS),0) as QTY_ISS_SO,";
				$sql .= "  CASE ";
				$sql .= " 	WHEN  smMKR2.SO_QTY_PER_X_QTY_ORD IS NOT NULL ";
				$sql .= " 		THEN round(((smMKR.QTY_PER * sh.QTY_ORD) / (smMKR2.SO_QTY_PER_X_QTY_ORD)) * ((ml.MARKER_LENGTH +4) * ml.MARKER_LAYERS),0) "; //--QTY_ISS_MKR
				$sql .= " 	ELSE ";
				$sql .= " 		round(((smSO.QTY_PER * sh.QTY_ORD) / (smSO2.SO_QTY_PER_X_QTY_ORD)) * ((ml.MARKER_LENGTH +4) * ml.MARKER_LAYERS),0) "; //--QTY_ISS_SO  
				$sql .= "  END as QTY_ISS,  ";

				$sql .= "  boh_MKR.KEY_BIN_3 as MKR_KEY_BIN_3,";
				$sql .= "  boh_SO.KEY_BIN_3 as SO_KEY_BIN_3,";
				$sql .= "  lt.ID_LOT as LT_KEY_BIN_3, ";

				$sql .= "  smMKR.QTY_PER as MKR_QTY_PER,";
				$sql .= "  smSO.QTY_PER as SO_QTY_PER,";
				$sql .= "  sh.QTY_ORD,";
				$sql .= "  smMKR2.SO_QTY_PER_X_QTY_ORD,";
				$sql .= "  smSO2.SO_QTY_PER_X_QTY_ORD,";
				$sql .= "  ml.MARKER_LENGTH,";
				$sql .= "  ml.MARKER_LAYERS,";

				$sql .= "  'N' as ISS_STATUS ";

				$sql .= "  FROM nsa.MU_LOT_TRACKING lt WITH (NOLOCK) ";

				$sql .= "  LEFT JOIN nsa.MU_SO muso WITH (NOLOCK) ";
				$sql .= "   on muso.MU_MARKER_rowid = lt.MU_MARKER_rowid ";
				$sql .= "   and muso.FLAG_DEL = '' ";

				$sql .= "  LEFT JOIN nsa.MU_MARKER_LOG ml WITH (NOLOCK) ";
				$sql .= "   on lt.MU_MARKER_rowid = ml.rowid ";
				$sql .= "   and ml.FLAG_DEL = '' ";

				$sql .= "  LEFT JOIN nsa.BINTAG_ONHD boh_MKR WITH (NOLOCK) ";
				$sql .= "   on ml.MARKER_ID_ITEM_COMP = boh_MKR.ID_ITEM ";
				$sql .= "   and lt.ID_LOT = boh_MKR.KEY_BIN_3 ";

				$sql .= "  LEFT JOIN nsa.BINTAG_ONHD boh_SO WITH (NOLOCK) ";
				$sql .= "   on ml.SO_ID_ITEM_COMP = boh_SO.ID_ITEM ";
				$sql .= "   and lt.ID_LOT = boh_SO.KEY_BIN_3 ";

				$sql .= "  LEFT JOIN nsa.SHPORD_MATL smMKR WITH (NOLOCK) ";
				$sql .= "   on ltrim(muso.ID_SO) = ltrim(smMKR.ID_SO) ";
				$sql .= "   and muso.SUFX_SO = smMKR.SUFX_SO ";
				$sql .= "   and ml.MARKER_ID_ITEM_COMP = smMKR.ID_ITEM_COMP ";
				$sql .= "   and smMKR.ID_LOC = '10' ";

				$sql .= "  LEFT JOIN nsa.SHPORD_MATL smSO WITH (NOLOCK) ";
				$sql .= "   on ltrim(muso.ID_SO) = ltrim(smSO.ID_SO) ";
				$sql .= "   and muso.SUFX_SO = smSO.SUFX_SO ";
				$sql .= "   and ml.SO_ID_ITEM_COMP = smSO.ID_ITEM_COMP ";
				$sql .= "   and smSO.ID_LOC = '10' ";

				$sql .= "  LEFT JOIN nsa.SHPORD_HDR sh ";
				$sql .= "   on muso.ID_SO = ltrim(sh.ID_SO) ";
				$sql .= "   and muso.SUFX_SO = sh.SUFX_SO ";

				$sql .= "  LEFT JOIN ( ";
				$sql .= "   SELECT ";
				$sql .= "   muso1.MU_MARKER_rowid, ";
				$sql .= "   sm1.ID_ITEM_COMP, ";
				$sql .= "   sum(sm1.QTY_PER * sh1.QTY_ORD) as SO_QTY_PER_X_QTY_ORD ";

				$sql .= "   FROM nsa.SHPORD_MATL sm1 WITH (NOLOCK) ";

				$sql .= "   LEFT JOIN nsa.MU_SO muso1 WITH (NOLOCK) ";
				$sql .= "   on ltrim(sm1.ID_SO) = muso1.ID_SO ";
				$sql .= "   and sm1.SUFX_SO = muso1.SUFX_SO ";

				$sql .= "   LEFT JOIN nsa.MU_MARKER_LOG ml1 WITH (NOLOCK) ";
				$sql .= "   on muso1.MU_MARKER_rowid = ml1.rowid ";
				$sql .= "   and sm1.ID_ITEM_COMP = ml1.MARKER_ID_ITEM_COMP ";

				$sql .= "   LEFT JOIN nsa.SHPORD_HDR sh1 WITH (NOLOCK)";
				$sql .= "   on muso1.ID_SO = ltrim(sh1.ID_SO) ";
				$sql .= "   and muso1.SUFX_SO = sh1.SUFX_SO ";

				$sql .= "   WHERE muso1.FLAG_DEL = '' ";
				$sql .= "   and sm1.ID_ITEM_COMP = ml1.MARKER_ID_ITEM_COMP ";

				$sql .= "   GROUP BY muso1.MU_MARKER_rowid, ";
				$sql .= "   sm1.ID_ITEM_COMP ";

				$sql .= "  ) smMKR2 ";
				$sql .= "   on ml.rowid = smMKR2.MU_MARKER_rowid ";
				$sql .= "   and ml.MARKER_ID_ITEM_COMP = smMKR2.ID_ITEM_COMP ";

				$sql .= "  LEFT JOIN ( ";
				$sql .= "   SELECT ";
				$sql .= "   muso1.MU_MARKER_rowid, ";
				$sql .= "   sm1.ID_ITEM_COMP, ";
				$sql .= "   sum(sm1.QTY_PER * sh1.QTY_ORD) as SO_QTY_PER_X_QTY_ORD ";

				$sql .= "   FROM nsa.SHPORD_MATL sm1 WITH (NOLOCK) ";

				$sql .= "   LEFT JOIN nsa.MU_SO muso1 WITH (NOLOCK) ";
				$sql .= "   on ltrim(sm1.ID_SO) = muso1.ID_SO ";
				$sql .= "   and sm1.SUFX_SO = muso1.SUFX_SO ";

				$sql .= "   LEFT JOIN nsa.MU_MARKER_LOG ml1 WITH (NOLOCK) ";
				$sql .= "   on muso1.MU_MARKER_rowid = ml1.rowid ";
				$sql .= "   and sm1.ID_ITEM_COMP = ml1.SO_ID_ITEM_COMP ";

				$sql .= "   LEFT JOIN nsa.SHPORD_HDR sh1 WITH (NOLOCK)";
				$sql .= "   on muso1.ID_SO = ltrim(sh1.ID_SO) ";
				$sql .= "   and muso1.SUFX_SO = sh1.SUFX_SO ";

				$sql .= "   WHERE muso1.FLAG_DEL = '' ";
				$sql .= "   and sm1.ID_ITEM_COMP = ml1.SO_ID_ITEM_COMP ";

				$sql .= "   GROUP BY muso1.MU_MARKER_rowid, ";
				$sql .= "   sm1.ID_ITEM_COMP ";

				$sql .= "  ) smSO2 ";
				$sql .= "   on ml.rowid = smSO2.MU_MARKER_rowid ";
				$sql .= "   and ml.SO_ID_ITEM_COMP = smSO2.ID_ITEM_COMP "; 				

 				$sql .= " WHERE lt.DATETIME_POST_TO_MATL_ISS is null ";
 				$sql .= "  and lt.DATE_ADD >= '2023-02-01' ";
 				$sql .= "  and lt.FLAG_DEL = '' ";
 				$sql .= "  and muso.ID_SO is not null ";

 				$sql .= " ORDER BY lt.DATE_ADD asc ";
				QueryDatabase($sql, $results);
				$colNamesA = array();
				for($i = 0; $i < mssql_num_fields($results); $i++) {
				    $field_info = mssql_fetch_field($results, $i);
				    $field = $field_info->name;
				    $colNamesA[$i] =  $field;
				}
				fputcsv($fp, $colNamesA);

				$NumRecs = mssql_num_rows($results);
				error_log("### Found ".$NumRecs ." records.");

				while ($row = mssql_fetch_assoc($results)) {
					if ($row['QTY_ISS']!='') {
						error_log("### POSTING MU_LOT_TRACKING_rowid: ".$row['MU_LOT_TRACKING_rowid']. " MU_MARKER_rowid: ".$row['MU_MARKER_rowid']." ID_SO: ".$row['ID_SO']." SUFX_SO:".$row['SUFX_SO']);

						$sql1  = " INSERT into nsa.MATL_ISS" . $DB_TEST_FLAG . " (";
						$sql1 .= "  ID_MARKER, ";
						$sql1 .= "  ID_LOT_TRACK_ROWID, ";
						$sql1 .= "  ID_LOC, ";
						$sql1 .= "  ID_SO, ";
						$sql1 .= "  SUFX_SO, ";
						$sql1 .= "  ID_ITEM, ";
						$sql1 .= "  KEY_BIN_3, ";
						$sql1 .= "  QTY_ISS, ";
						$sql1 .= "  ISS_STATUS ";

						$sql1 .= " ) VALUES ( ";
						$sql1 .= "  ".$row['MU_MARKER_rowid'].", ";
						$sql1 .= "  ".$row['MU_LOT_TRACKING_rowid'].", ";
						$sql1 .= "  '".$row['ID_LOC']."', ";
						$sql1 .= "  '".str_pad($row['ID_SO'],9," ",STR_PAD_LEFT)."', ";
						$sql1 .= "  ".$row['SUFX_SO'].", ";
						//$sql1 .= "  '".$row['MARKER_ID_ITEM']."', ";
						$sql1 .= "  '".$row['ISSUED_ID_ITEM']."', ";
						$sql1 .= "  '".substr(trim($row['LT_KEY_BIN_3']),0,20)."', ";
						$sql1 .= "  ".$row['QTY_ISS'].", ";
						$sql1 .= "  '".$row['ISS_STATUS']."' ";
						$sql1 .= " ) ";
						QueryDatabase($sql1, $results1);

						$sql2  = " UPDATE nsa.MU_LOT_TRACKING ";
						$sql2 .= " SET DATETIME_POST_TO_MATL_ISS = getDate() ";
						$sql2 .= " WHERE rowid = ".$row['MU_LOT_TRACKING_rowid'] ;
						QueryDatabase($sql2, $results2);

					} else {
						error_log("### QTY_ISS is blank MU_LOT_TRACKING_rowid: ".$row['MU_LOT_TRACKING_rowid']. " MU_MARKER_rowid: ".$row['MU_MARKER_rowid']." ID_SO: ".$row['ID_SO']." SUFX_SO:".$row['SUFX_SO']);

						fputcsv($fp, $row, ",", "\"");
						
						$List .= "	<br><p>MU_MARKER_rowid: " . $row['MU_MARKER_rowid'];
						$List .= "		<br>MU_LOT_TRACKING_rowid: " . $row['MU_LOT_TRACKING_rowid'];
						$List .= "		<br>ID_SO: " . $row['ID_SO'];
						$List .= "		<br>SUFX_SO: " . $row['SUFX_SO'];
						$List .= "		<br>MARKER_ID_ITEM: " . $row['MARKER_ID_ITEM'];
						$List .= "		<br>SO_ID_ITEM: " . $row['SO_ID_ITEM'];
						$List .= "		<br>ISSUED_ID_ITEM: " . $row['ISSUED_ID_ITEM'];
						$List .= "		<br>MKR_ID_LOT: " . $row['MKR_KEY_BIN_3'];
						$List .= "		<br>SO_ID_LOT: " . $row['SO_KEY_BIN_3'];
						$List .= "		<br>LT_ID_LOT: " . $row['LT_KEY_BIN_3'];
						$List .= "		<br>MKR_QTY_ISS: " . $row['QTY_ISS_MKR'];
						$List .= "		<br>SO_QTY_ISS: " . $row['QTY_ISS_SO'];
						$List .= "		<br>QTY_ISS: " . $row['QTY_ISS'];
						$List .= "	</p><br>";


						$sql2  = " UPDATE nsa.MU_LOT_TRACKING ";
						$sql2 .= " SET DATETIME_POST_TO_MATL_ISS = getDate(), FLAG_ERR_POST = 'Y' ";
						$sql2 .= " WHERE rowid = ".$row['MU_LOT_TRACKING_rowid'] ;
						QueryDatabase($sql2, $results2);

					}
				}

				fclose($fp);

				if ($List != "") {
					$subject = "Post Lot Tracking found unissued material(s)";

					$body  = "<html>";
					$body .= $List;
					$body .= "</html>";

					$files = array($filename);

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('jmartin@thinknsa.com'=>'Jeff Martin'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	//'bcc'     =>array('sabdelsayed@thinknsa.com'=>'Sabrina Abdelsayed'),
				    	);
			    	}
					mail::send($head,$subject,$body, $files);
					sleep(1);
				}	


				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runPOST_LOT_TRACKING_TO_MATL_ISS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runPOST_LOT_TRACKING_TO_MATL_ISS cannot disconnect from database");
		}
	}
?>
