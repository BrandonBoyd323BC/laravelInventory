<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/mail.class.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runTYNDALE_INV cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runTYNDALE_INV cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runTYNDALE_INV started at " . date('Y-m-d g:i:s a'));

			$date = "CONVERT(varchar(10),GETDATE(), 101)";
			//$date = "'02-27-2017'";

			$sql = "SET ANSI_NULLS ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS ON";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER ON";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING ON";
			QueryDatabase($sql, $results);
			
			$sql  = " select sl.ID_ORD, sl.ID_SHIP, sl.SEQ_LINE_ORD, sl.ID_ITEM, sl.ID_ITEM_CUST, sl.QTY_SHIP, sh.ID_PO_CUST ";
			$sql .= " from nsa.CP_SHPLIN sl ";
			$sql .= " left join nsa.CP_SHPHDR sh ";
			$sql .= " on sl.ID_ORD = sh.ID_ORD ";
			$sql .= " where sh.ID_CUST_SOLDTO = '854150' and sl.DATE_ADD = " . $date . " ";
			$sql .= " order by sl.ID_ORD, sl.SEQ_LINE_ORD ";
			error_log($sql);
			QueryDatabase($sql, $results);
			/*while ($row = mssql_fetch_assoc($results)) {
				fputcsv($fp, $row, ",", "\"");
			}*/

			
			/////////
			//Email contents
			////////
			$head = array(
			       'to'      =>array('rbollinger@thinknsa.com'=>'Hello'),//email address to send report to
			       'cc'      =>array('rbollinger@thinknsa.com'=>''),
			       'from'    =>array('DONOTREPLY@thinkNSA.com' =>'NSA'),
			       );
			$subject = date("m.d.y")." Tyndale Daily Shipping Report";

			$body = "";
			$body.="<div style='font-family:Arial;font-size:10pt;'>";
			$body.=    "<br>"."Hello,";
			$body.=    "<br>"."";
			$body.=    "<br>"."Here is our daily Tyndale shipping report.  This report may not be accurate on the last day of the month.";
			$body.=    "<br>"."";
			$body.=    "<br>"."-NSA";
			$body.="</div>";
			$body.=    "<br>\r\n";
			$body .="<table>";
			$body .=" <tr style='text-align: center;'>";
			$body .="  <th style='padding:0 15px 0 15px;'>Order #</th>";
			$body .="  <th style='padding:0 15px 0 15px;'>Shipment #</th>";
			$body .="  <th style='padding:0 15px 0 15px;'>Line Seq #</th>";
			$body .="  <th style='padding:0 15px 0 15px;'>Item</th>";
			$body .="  <th style='padding:0 15px 0 15px;'>Qty Ship</th>";
			$body .="  <th style='padding:0 15px 0 15px;'>Cust PO</th>";
			$body .="  <th style='padding:0 15px 0 15px;'>Cust Item</th>";
			$body .=" </tr>";
			while($row = mssql_fetch_assoc($results)) {
				$body .= "<tr style='text-align: center;'>  <td>" . $row['ID_ORD'] . "</td> <td>" . $row['ID_SHIP'] ."</td><td>". $row['SEQ_LINE_ORD'] . "</td><td>". $row['ID_ITEM'] . "</td><td>" . $row['QTY_SHIP'] . "</td> <td>" . $row['ID_PO_CUST'] . "</td> <td>". $row['ID_ITEM_CUST'] . "</td> </tr>";
			}//end while
			$body .="</table>";

			

			if (mssql_num_rows($results) > 0) { 
				mail::send($head,$subject,$body);
			}	

			$sql = "SET ANSI_NULLS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_WARNINGS OFF";
			QueryDatabase($sql, $results);
			$sql = "SET QUOTED_IDENTIFIER OFF";
			QueryDatabase($sql, $results);
			$sql = "SET ANSI_PADDING OFF";
			QueryDatabase($sql, $results);

			error_log("### runTYNDALE_INV finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runTYNDALE_INV  cannot disconnect from database");
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