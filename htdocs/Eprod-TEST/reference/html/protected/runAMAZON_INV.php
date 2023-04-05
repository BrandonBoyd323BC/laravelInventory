<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runAMAZON_INV cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runAMAZON_INV cannot select " . $dbName);
		} else {
			$filename = "/tmp/amazon/NSA_AMAZON_stock_inv.csv";
			error_log("#############################################");
			error_log("### runAMAZON_INV started at " . date('Y-m-d g:i:s a'));
			$fp = fopen($filename, 'w');

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);
			
			$sql  = "SELECT a.NSA_ID_ITEM, ";
			$sql .= " CASE ";
			$sql .= "   WHEN (a.OVERRIDE_QTY is NULL and (l.QTY_ONHD + l.QTY_ONORD) > 0) THEN (l.QTY_ONHD + l.QTY_ONORD) ";
			$sql .= "   WHEN (a.OVERRIDE_QTY is NULL and (l.QTY_ONHD + l.QTY_ONORD) <= 0) THEN 0 ";
			$sql .= "   WHEN (a.OVERRIDE_QTY is NOT NULL) THEN a.OVERRIDE_QTY ";
			$sql .= "   ELSE 0 ";
			$sql .= " END as QTY_IN_STOCK, ";
			$sql .= " CONVERT(varchar(10),GETDATE(), 101) as DATE ";
			$sql .= " FROM nsa.AMAZON_INVENTORY a ";
			$sql .= " LEFT JOIN nsa.ITMMAS_LOC l ";
			$sql .= " on l.ID_ITEM = a.NSA_ID_ITEM ";
			$sql .= " and l.ID_LOC = '10' ";
			$sql .= " ORDER BY a.NSA_ID_ITEM ";
			error_log($sql);
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
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
			
			/////////
			//Email contents
			////////
			$head = array(
			       //'to'      =>array('marketing@thinknsa.com'=>'Marketing','mporter@thinknsa.com'=>'Megan Porter'),//email address to send report to
			       'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
			       'from'    =>array('auto-email@thinkNSA.com' =>'NSA'),
			       );
			$subject = date("m.d.y")." Weekly AMAZON Inventory Report";
			$body ='';
			$body.="<div style='font-family:Arial;font-size:10pt;'>";
			$body.=    "<br>"."Customer,";
			$body.=    "<br>"."";
			$body.=    "<br>"."Attached is the file containing our weekly inventory report.";
			$body.=    "<br>"."";
			$body.=    "<br>"."-NSA";
			$body.="</div>";
			$files = array($filename);
			 
			mail::send($head,$subject,$body, $files);

			error_log("### runAMAZON_INV finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runAMAZON_INV  cannot disconnect from database");
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