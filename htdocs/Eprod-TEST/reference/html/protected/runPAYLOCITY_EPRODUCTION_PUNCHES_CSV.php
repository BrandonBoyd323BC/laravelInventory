<?php

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");
	
	$DEBUG = 1;

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runPAYLOCITY_EPRODUCTION_PUNCHES_CSV cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runPAYLOCITY_EPRODUCTION_PUNCHES_CSV cannot select " . $dbName);
		} else {
			$headers = true;
			$filename = "/tmp/Paylocity/eProduction_Punches_for_Paylocity.csv";
			error_log("#############################################");
			error_log("### runPAYLOCITY_EPRODUCTION_PUNCHES_CSV started at " . date('Y-m-d g:i:s a'));
			//$fp = fopen($filename, 'w');
 			
			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);
			

			$sql  = "SELECT ";
			$sql .= " ltrim(z.ID_BADGE) as [EE ID], ";
			$sql .= " convert(varchar,z.DATE_CORR_TRX,101) as [Punch Date], ";
			$sql .= " format(convert(datetime, concat(CONVERT(varchar(10), z.DATE_TRX, 20), ' ', STUFF(STUFF(right('000000' + rtrim(z.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000')), 'hh:mm tt') as [Punch Time], ";
			$sql .= " CASE z.CODE_TRX WHEN 100 THEN 'In' when 101 THEN 'Out' END as [Punch Type] ";
			$sql .= " FROM nsa.DCUTRX_ZERO z ";
			$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
			$sql .= " on z.ID_BADGE = e.ID_BADGE ";
			$sql .= " and e.CODE_ACTV = 0 ";
			$sql .= " WHERE z.DATE_TRX >= DateAdd(Day, Datediff(Day,0, GetDate() -1),0) ";
			//$sql .= " WHERE z.DATE_TRX = '2020-05-16' ";
			$sql .= " and z.CODE_TRX in (100,101) ";
			$sql .= " and z.FLAG_DEL <> 'D' ";
			$sql .= " and ltrim(z.ID_BADGE) NOT in ('9988','9876','9977') ";
			$sql .= " and ltrim(e.CODE_SHIFT) NOT LIKE '_TE' ";
			//$sql .= " and ltrim(ID_BADGE) NOT in ('2096','2328','2432','2553','2599','2640','2652','2732','2734','2763','2777','2782','2788','2791','2804','2874','2875','2899','2939','2945','2966','2967','2994','3003','3018','3025','3061','3108','3113','3114','3217','3259','3270','3276','3284','3306','3308','3322','3341','3355','3358','3369','8000') "; //TEMP AGENCY EMPLOYEES
			$sql .= " ORDER BY z.DATE_CORR_TRX asc, z.TIME_CORR_TRX asc ";
/*
			error_log($sql);
			QueryDatabase($sql, $results);
			while($row = mssql_fetch_assoc($results)) {
				fputcsv($fp, $row, ",", "\"");
			}
*/

 			query2csv($sql, $filename, $headers = true);

			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabase($sql, $results);

			//fclose($fp);
			
			////////////////////////////
			//Email contents
			////////////////////////////
			
			if ($TEST_ENV) {
				$head = array(
			    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg'),
			    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
		    	);
		    } else {
	    		$head = array(
			    	'to'      =>array('obridges@thinknsa.com'=>'Olivia Bridges'),
			    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
			    	//'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
		    	);
			}

			$subject = "eProduction Punches for Paylocity";
			$body ='';
			$body.="<div style='font-family:Arial;font-size:10pt;'>";
			$body.=    "<br>"."Attached is the file containing yesterday's eProduction Attendance Punches for upload to Paylocity.";
			$body.="</div>";
			$files = array($filename);
			 
			mail::send($head,$subject,$body, $files);

			error_log("### runPAYLOCITY_EPRODUCTION_PUNCHES_CSV finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runPAYLOCITY_EPRODUCTION_PUNCHES_CSV  cannot disconnect from database");
		}
	}

    function query2csv($query, $filename, $headers = true) {
        $fp = fopen($filename, 'w');
        QueryDatabase($query, $result);

        if($headers) {
            // output header row (if at least one row exists)
            $row = mssql_fetch_assoc($result);
            if($row) {
                fputcsv($fp, array_keys($row));
                // reset pointer back to beginning
                mssql_data_seek($result, 0);
            }
        }
        while($row = mssql_fetch_assoc($result)) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }

/*
    function query_to_csv($db_conn, $query, $filename, $attachment = false, $headers = true) {
        if($attachment) {
            // send response headers to the browser
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
            $fp = fopen('php://output', 'w');
        } else {
            $fp = fopen($filename, 'w');
        }
        $result = mssql_query($query, $db_conn) or die( mssql_error( $db_conn ) );

        if($headers) {
            // output header row (if at least one row exists)
            $row = mssql_fetch_assoc($result);
            if($row) {
                fputcsv($fp, array_keys($row));
                // reset pointer back to beginning
                mssql_data_seek($result, 0);
            }
        }
        while($row = mssql_fetch_assoc($result)) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
*/

?>