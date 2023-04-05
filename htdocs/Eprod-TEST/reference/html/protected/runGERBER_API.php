<?php
	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runGERBER_API cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runGERBER_API cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runGERBER_API started at " . date('Y-m-d g:i:s a'));
			error_log("### runGERBER_API CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			$today = date('Ymd');

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runGERBER_API' ";
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
				$sql .= "'runGERBER_API', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runGERBER_API SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);


				///////////////////////////
				///// GerberCONNECT API DATA
				///////////////////////////
			curl -X POST https://api.gerberconnect.gerbertechnology.com/v2/token -H "Content-Type: application/json" -d "{'username':'greg.vandyne','password':'RwLt#N36K'}"


				$URL = "https://api.gerberconnect.gerbertechnology.com/v2/token";
				$xmlData = file_get_contents($URL);
				error_log("GerberCONNECT API Record: " . $xmlData);

				$xmlArray = str_replace("\"","",str_replace("}","",str_replace("{","",explode(",",$xmlData))));

				$pair = '';
				$rate = 0;
				$minerFee = 0;
				$mLimit = 0;
				$minLimit = 0;
				$maxLimit = 0;

				foreach ($xmlArray as $i) {
					$pos = strpos($i,":");
					$field = substr($i,0,$pos);
					$value = substr($i,$pos+1);

					switch ($field) {
						case 'pair':
							$pair = $value;
						break;
						case 'rate':
							$rate = $value;
						break;
						case 'minerFee':
							$minerFee = $value;
						break;
						case 'limit':
							$mLimit = $value;
						break;
						case 'minimum':
							$minLimit = $value;
						break;
						case 'maxLimit':
							$maxLimit = $value;
						break;
					}
				}

				$sql  = "INSERT into ssMarketInfo (";
				$sql .= " pair, ";
				$sql .= " rate, ";
				$sql .= " minerFee, ";
				$sql .= " mlimit, ";
				$sql .= " minLimit, ";
				$sql .= " maxLimit, ";
				$sql .= " dateAdd, ";
				$sql .= " runTS ";
				$sql .= " ) values ( ";
				$sql .= " '".$pair."', ";
				$sql .= " ".$rate.", ";
				$sql .= " ".$minerFee.", ";
				$sql .= " ".$mLimit.", ";
				$sql .= " ".$minLimit.", ";
				$sql .= " ".$maxLimit.", ";
				$sql .= " NOW(), ";
				$sql .= " '".$runTS."' ";
				$sql .= " ) ";
				QueryDatabase($sql, $results);



/*
				///////////////////////////
				///// ShapeShift API data
				///////////////////////////
				$URL = "https://shapeshift.io/marketinfo/zec_btc";
				$xmlData = file_get_contents($URL);
				error_log("ShapeShift API Record: " . $xmlData);

				$xmlArray = str_replace("\"","",str_replace("}","",str_replace("{","",explode(",",$xmlData))));

				$pair = '';
				$rate = 0;
				$minerFee = 0;
				$mLimit = 0;
				$minLimit = 0;
				$maxLimit = 0;

				foreach ($xmlArray as $i) {
					$pos = strpos($i,":");
					$field = substr($i,0,$pos);
					$value = substr($i,$pos+1);

					switch ($field) {
						case 'pair':
							$pair = $value;
						break;
						case 'rate':
							$rate = $value;
						break;
						case 'minerFee':
							$minerFee = $value;
						break;
						case 'limit':
							$mLimit = $value;
						break;
						case 'minimum':
							$minLimit = $value;
						break;
						case 'maxLimit':
							$maxLimit = $value;
						break;
					}
				}

				$sql  = "INSERT into ssMarketInfo (";
				$sql .= " pair, ";
				$sql .= " rate, ";
				$sql .= " minerFee, ";
				$sql .= " mlimit, ";
				$sql .= " minLimit, ";
				$sql .= " maxLimit, ";
				$sql .= " dateAdd, ";
				$sql .= " runTS ";
				$sql .= " ) values ( ";
				$sql .= " '".$pair."', ";
				$sql .= " ".$rate.", ";
				$sql .= " ".$minerFee.", ";
				$sql .= " ".$mLimit.", ";
				$sql .= " ".$minLimit.", ";
				$sql .= " ".$maxLimit.", ";
				$sql .= " NOW(), ";
				$sql .= " '".$runTS."' ";
				$sql .= " ) ";
				my_QueryDatabase($sql, $results);
*/	

				error_log("### runGERBER_API DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runGERBER_API ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runGERBER_API finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runGERBER_API cannot disconnect from database");
		}
	}
?>
