<?php

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");
	
	$DEBUG = 1;

	$retval = ConnectToDatabaseServerBC($DBServerBC, $dbBCSQL1);
	if ($retval == 0) {
		error_log("runPAYLOCITY_BLUECHERRY_PUNCHES_CSV cannot connect to database");
	} else {
		$retval = SelectDatabaseBCSQL1($dbNameBCSQL1);
		if ($retval == 0) {
			error_log("runPAYLOCITY_BLUECHERRY_PUNCHES_CSV cannot select " . $dbNameBCSQL1);
		} else {
			$headers = true;
			$filename = "/tmp/Paylocity/blueCherry_Punches_for_Paylocity.csv";
			error_log("#############################################");
			error_log("### runPAYLOCITY_BLUECHERRY_PUNCHES_CSV started at " . date('Y-m-d g:i:s a'));
			//$fp = fopen($filename, 'w');
 			
			$sql = "SET ANSI_NULLS ON";
			QueryDatabaseBCSQL1($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabaseBCSQL1($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabaseBCSQL1($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabaseBCSQL1($sql, $results);
			
/*
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

			$sql .= " ORDER BY z.DATE_CORR_TRX asc, z.TIME_CORR_TRX asc ";
*/


/*
			$sql  = " SELECT ID_BADGE as  [EE ID], ";
			$sql .= " [Punch Date], ";
			$sql .= " PunchInAdjTime as [Punch Time], ";
			$sql .= " 'In' as [Punch Type] ";
			$sql .= " FROM PAYROLL_PUNCHES ";
			$sql .= " WHERE DATETIME_PunchInAdj >= DateAdd(Day, Datediff(Day,0, GetDate() -1),0) ";
			$sql .= " and ID_BADGE < 8000 ";
			$sql .= " UNION ";
			$sql .= " SELECT ID_BADGE as  [EE ID], ";
			$sql .= " [Punch Date], ";
			$sql .= " PunchOutAdjTime as PunchOut, ";
			$sql .= " 'Out' as [Punch Type] ";
			$sql .= " FROM PAYROLL_PUNCHES ";
			$sql .= " WHERE DATETIME_PunchOutAdj >= DateAdd(Day, Datediff(Day,0, GetDate() -1),0) ";
			$sql .= " and ID_BADGE < 8000 ";
			$sql .= " order by [Punch Date] asc, [Punch Time] asc ";
*/



			$sql  = " SELECT ID_BADGE as  [EE ID], ";
			$sql .= " [Punch Date], ";
			//$sql .= " MIN(DATETIME_PunchInAdj) as [Punch DATETime], ";
			$sql .= " MIN(PunchInAdjTime) as [Punch Time], ";
			$sql .= " 'In' as [Punch Type] ";
			$sql .= " FROM PAYROLL_PUNCHES ";
			$sql .= " WHERE DATETIME_PunchInAdj >= DateAdd(Day, Datediff(Day,0, GetDate() -1),0) ";
			$sql .= " and ID_BADGE < 8000 ";
			$sql .= " GROUP BY ID_BADGE, [Punch Date] ";
			$sql .= " UNION ";
			$sql .= " SELECT ID_BADGE as  [EE ID], ";
			$sql .= " [Punch Date], ";
			//$sql .= " MAX(DATETIME_PunchOutAdj) as [Punch DATETime], ";
			$sql .= " MAX(PunchOutAdjTime) as [Punch Time], ";
			$sql .= " 'Out' as [Punch Type] ";
			$sql .= " FROM PAYROLL_PUNCHES ";
			$sql .= " WHERE DATETIME_PunchOutAdj >= DateAdd(Day, Datediff(Day,0, GetDate() -1),0) ";
			$sql .= " and ID_BADGE < 8000 ";
			$sql .= " GROUP BY ID_BADGE, [Punch Date] ";
			$sql .= " ORDER BY ID_BADGE asc, [Punch Date] asc, [Punch Time] asc ";

/*
			error_log($sql);
			QueryDatabase($sql, $results);
			while($row = mssql_fetch_assoc($results)) {
				fputcsv($fp, $row, ",", "\"");
			}
*/

 			query2csv($sql, $filename, $headers = true);

			$sql = "SET ANSI_NULLS OFF";
			QueryDatabaseBCSQL1($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabaseBCSQL1($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabaseBCSQL1($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabaseBCSQL1($sql, $results);

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
			    	//'to'      =>array('obridges@thinknsa.com'=>'Olivia Bridges'),
			    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
			    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
			    	//'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
		    	);
			}

			$subject = "BlueCherry Punches for Paylocity";
			$body ='';
			$body.="<div style='font-family:Arial;font-size:10pt;'>";
			$body.=    "<br>"."Attached is the file containing yesterday's Blue Cherry Attendance Punches for upload to Paylocity.";
			$body.="</div>";
			$files = array($filename);
			 
			mail::send($head,$subject,$body, $files);

			error_log("### runPAYLOCITY_BLUECHERRY_PUNCHES_CSV finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServerBCSQL1($dbBCSQL1);
		if ($retval == 0) {
			error_log("runPAYLOCITY_BLUECHERRY_PUNCHES_CSV  cannot disconnect from database");
		}
	}

    function query2csv($query, $filename, $headers = true) {
        $fp = fopen($filename, 'w');
        QueryDatabaseBCSQL1($query, $result);

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