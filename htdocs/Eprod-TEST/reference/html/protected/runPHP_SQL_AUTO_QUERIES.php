<?php
	$DEBUG = 0;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/mail.class.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runPHP_SQL_AUTO_QUERIES cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runPHP_SQL_AUTO_QUERIES cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runPHP_SQL_AUTO_QUERIES started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');

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


			$directory = str_replace(' ', '\ ',"/mnt/TableauFiles/PHP_SQL_AUTO/Queries/");

			error_log($directory);

			$filecount = 0;
			$files = glob($directory . "*.sql");
			if ($files){
				$attfiles = array();
				$filecount = count($files);
				error_log("filecount: ".$filecount);

				foreach ($files as $QueryFileName) {
					error_log("QueryFileName: ".$QueryFileName);

					$outputFileName = str_replace(".sql","",$QueryFileName)."_". date('Ymd-His') .".csv";
					$outputFileName = str_replace(' ', '_',$outputFileName);
					$outputFileName = str_replace('/Queries/', '/Results/',$outputFileName);
					header( 'Content-Type: text/csv' );
					header( 'Content-Disposition: attachment;filename='.$outputFileName);
					error_log("outputFileName: ".$outputFileName);

					$fp = fopen($outputFileName, 'w');
					$handle = fopen($QueryFileName, "r");
					if ($handle) {
						$sql = "";
						while (($line = fgets($handle)) !== false) {
							$sql .= $line;
						}
						QueryDatabase($sql, $results);

						$colNamesA = array();
						for($i = 0; $i < mssql_num_fields($results); $i++) {
						    $field_info = mssql_fetch_field($results, $i);
						    $field = $field_info->name;
						    $colNamesA[$i] =  $field;
						}
						fputcsv($fp, $colNamesA);
						while ($row = mssql_fetch_assoc($results)) {
							fputcsv($fp, $row, ",", "\"");
						}

					} else {
						error_log("### ERROR OPENING " . $QueryFileName);
					}
					fclose($handle);
					fclose($fp);

					array_push($attfiles,$outputFileName);
				}

				/////////
				//Email contents
				////////
				$head = array(
				       'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),//email address to send report to
				       //'cc'      =>array('mfigueroa@thinknsa.com'=>'Micel Figueroa'),
				       //'bcc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				       'from'    =>array('auto-email@thinkNSA.com' =>'NSA'),
				       );
				$subject = "SQL Query Output attached";
				$body ='';
				$body.="<div style='font-family:Arial;font-size:10pt;'>";
				$body.=    "<br>"."Hello,";
				$body.=    "<br>"."";
				$body.=    "<br>"."Attached are some query results.";
				$body.=    "<br>"."";
				$body.=    "<br>"."-NSA";
				$body.="</div>";

				//mail::send($head,$subject,$body,$attfiles);
			}


			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabase($sql, $results);


			error_log("### runPHP_SQL_AUTO_QUERIES finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runPHP_SQL_AUTO_QUERIES cannot disconnect from database");
		}
	}
?>