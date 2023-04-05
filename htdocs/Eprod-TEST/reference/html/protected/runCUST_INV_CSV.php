<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCUST_INV_CSV cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCUST_INV_CSV cannot select " . $dbName);
		} else {
			$filename = "/tmp/cust_inv/NSA_stock_inv.csv";
			error_log("#############################################");
			error_log("### runCUST_INV_CSV started at " . date('Y-m-d g:i:s a'));
			$fp = fopen($filename, 'w');

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);
			
			$sql  = " select cr.ID_ITEM, cr.SIZE, cr.COLOR, ";
			$sql .= "  CASE ";
			$sql .= "  		WHEN (l.QTY_ONHD - l.QTY_ALLOC) < 0 THEN 0";
			$sql .= "  		ELSE (l.QTY_ONHD - l.QTY_ALLOC) ";
			$sql .= "  END as QTY_AVAIL";
			$sql .= " from nsa.CUST_INVENTORY_RELIABLE cr ";
			$sql .= " left join nsa.ITMMAS_LOC l ";
			$sql .= " on cr.ID_ITEM = l.ID_ITEM ";

			error_log($sql);
			QueryDatabase($sql, $results);
			while($row = mssql_fetch_assoc($results)) {
				fputcsv($fp, $row, ",", "\"");
			}

			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabase($sql, $results);

			fclose($fp);
			
			////////////////////////////
			//Email contents
			///////////////////////////
			
			if ($TEST_ENV) {
				$head = array(
			    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg'),
			    	'from'    =>array('donotreply@thinkNSA.com' =>'National Safety Apparel'),
		    	);
		    } 
			 else {
	    		$head = array(
			    	'to'      =>array('todd@shopreliable.com'=>'Todd Philipps'),
			    	'from'    =>array('donotreply@thinkNSA.com' =>'National Safety Apparel'),
			    	//'cc'      =>array('rbollinger@thinknsa.com'=>'Rich Bollinger'),
		    	);
			}

			$subject = date("m.d.y")." Weekly Inventory Report";
			$body ='';
			$body.="<div style='font-family:Arial;font-size:10pt;'>";
			$body.=    "<br>"."Hello,";
			$body.=    "<br>"."";
			$body.=    "<br>"."Attached is the file containing our weekly inventory report.";
			$body.=    "<br>"."";
			$body.=    "<br>"."-NSA";
			$body.="</div>";
			$files = array($filename);
			 
			mail::send($head,$subject,$body, $files);

			error_log("### runCUST_INV_CSV finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCUST_INV_CSV  cannot disconnect from database");
		}
	}

    function query_to_csv($db_conn, $query, $filename, $attachment = false, $headers = true) {
        if($attachment) {
            // send response headers to the browser
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename='.$filename);
            $fp = fopen('php://output', 'w');
        } else {
            $fp = fopen($filename, 'w');
        }
        $result = mysql_query($query, $db_conn) or die( mysql_error( $db_conn ) );

        if($headers) {
            // output header row (if at least one row exists)
            $row = mysql_fetch_assoc($result);
            if($row) {
                fputcsv($fp, array_keys($row));
                // reset pointer back to beginning
                mysql_data_seek($result, 0);
            }
        }
        while($row = mysql_fetch_assoc($result)) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
?>