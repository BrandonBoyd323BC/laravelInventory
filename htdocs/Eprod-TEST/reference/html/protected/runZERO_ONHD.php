<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runZERO_ONHD cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runZERO_ONHD cannot select " . $dbName);
		} else {
			$date_now = date("Y-m-d"); // this format is string comparable

			if ($date_now > '2023-01-02') {

			    error_log("#############################################");
				error_log("### Greater than ");
				error_log("### runZERO_ONHD SET TO NO LONGER RUN AFTER 1/2/2023");

				////////////////////////////
				//Email Reminder to turn off
				////////////////////////////
	    		$head = array(
			    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
			    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
		    	);
				$subject = "Disable runZERO_ONHD on LS1 crontab";
				$body ='';
				$body.="<div style='font-family:Arial;font-size:10pt;'>";
				$body.=    "<br>"."runZERO_ONHD is no longer required.";
				$body.="</div>";
				mail::send($head,$subject,$body);
				error_log("#############################################");

			} else{

				error_log("#############################################");
				error_log("### Less than ");
				error_log("### runZERO_ONHD started at " . date('Y-m-d g:i:s a'));
				error_log("### runZERO_ONHD CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

				$sql  = "SELECT ";
				$sql .= "	* ";
				$sql .= " FROM ";
				$sql .= " 	nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= "	PROC_NAME = 'runZERO_ONHD' ";
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
					$sql .= "'runZERO_ONHD', ";
					$sql .= "1, ";
					$sql .= " getDate(), ";
					$sql .= " dateadd(minute,5,getDate()) ";
					$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
					QueryDatabase($sql, $results);
					$row = mssql_fetch_assoc($results);
					$ProcRowID = $row['LAST_INSERT_ID'];
					error_log("### runZERO_ONHD SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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


					error_log("### runZERO_ONHD ZEROING nsa.BINTAG_ONHD.QTY_ONHD FOR NON-LOT FLOOR BINS");
					$sql  = "UPDATE nsa.BINTAG_ONHD set QTY_ONHD = 0 ";
					$sql .= " WHERE KEY_BIN_1 = 'FLOOR' and KEY_BIN_3 = ''";
					QueryDatabase($sql, $results);


					$sql = "SET ANSI_NULLS OFF";
					QueryDatabase($sql, $results);
					$sql = "SET ANSI_WARNINGS OFF";
					QueryDatabase($sql, $results);
					$sql = "SET QUOTED_IDENTIFIER OFF";
					QueryDatabase($sql, $results);
					$sql = "SET ANSI_PADDING OFF";
					QueryDatabase($sql, $results);

					error_log("### runZERO_ONHD DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
					error_log("### LAST INSERT ID: " . $ProcRowID);

					$sql  = "DELETE FROM nsa.RUNNING_PROC ";
					$sql .= " WHERE ";
					$sql .= " rowid = " . $ProcRowID;
					QueryDatabase($sql, $results);

				} else {
					// FUTURE ENHANCEMENT -- Sleep and reloop
					error_log("### runZERO_ONHD ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				}

				error_log("### runZERO_ONHD finished at " . date('Y-m-d g:i:s a'));
				error_log("#############################################");
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runZERO_ONHD cannot disconnect from database");
		}
	}
?>
