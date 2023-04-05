<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');
	PrintHeaderJQ('Management Dashboard','default.css','mgmtdashG.js');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {

			print(" <table>");
			print(" 	<tr>");
			print(" 		<td colspan='2'>\n");
			print(" 			<LABEL for='company'>Company: </LABEL>\n");
			print("				<select id='company'>\n");
			print("					<option value='ALL'> -- ALL -- </option>\n");
			print("					<option value='NSA'>NSA</option>\n");
			print("					<option value='VINA'>Vinatronics</option>\n");
			print("				</select>\n");
			print(" 			<INPUT type='submit' value='Submit' onClick=\"sendValue()\">\n");
			print(" 		</td>\n");;
			print(" 	</tr>\n");
			print(" </table>\n");
			print(" <div id='dataDiv'></div>\n");
			print(" <div id='backgroundPopup'></div>\n");
			print(" <div id='dataPopup'></div>\n");
			print(" <div id='visualization' style='width: 800px; height: 400px;'></div>\n");








/*
			print("<html xmlns='http://www.w3.org/1999/xhtml'>\n");
			print("<head>\n");
			print("  <meta http-equiv='content-type' content='text/html; charset=utf-8' />\n");
			print("  <title>Management Dashboard</title>\n");
			print("  <script type='text/javascript' src='http://www.google.com/jsapi'></script>\n");
			print("  <script type='text/javascript'>\n");

			$prevDay = '';
			$sql  = " SELECT max(DATE_INVC) as prevDay ";
			$sql .= " FROM nsa.SLSHST_HDR ";
			//$sql .= " WHERE 1=1 ";
			QueryDatabase($sql, $results);
			while($row = mssql_fetch_assoc($results)) {
				$prevDay = $row['prevDay'];
			}


			$sql  = " SELECT ";
			$sql .= "	DATE_BOOK_LAST, ";
			$sql .= "	CONVERT(varchar(12), DATE_BOOK_LAST, 107) as calc_date2, ";
			$sql .= "	COALESCE(sum(l.SLS),0) as SUM_SLS, ";
			$sql .= "	count(l.SLS) as reccount ";
			$sql .= " FROM nsa.BOKHST_LINE l ";
			//$sql .= " WHERE DATE_BOOK_LAST > DATEADD(DAY,-365,GETDATE()) ";
			//$sql .= " WHERE DATE_BOOK_LAST > DATEADD(DAY,-730,'". $prevDay ."') ";
			$sql .= " GROUP BY DATE_BOOK_LAST ";
			$sql .= " ORDER BY DATE_BOOK_LAST asc ";


			$DEBUG=1;
			QueryDatabase($sql, $results);
			$DEBUG=0;
			$sumSlsArray = '';
			$calcDateArray = '';
			$combinedArray = array();
			$maxDateTS = '';
			$maxDate = '';

			//while($row = mssql_fetch_assoc($results)) {
			//	if ($row['reccount'] <> 0) {
			//		//$MyData->addPoints($row['SUM_SLS'],"Booked");
			//		$sumSlsArray[] = $row['SUM_SLS'];
			//		$calcDateArray[] = $row['calc_date2'];
			//		$combinedArray[$row['calc_date2']] = array("calc_date2" => $row['calc_date2'],"booked" => $row['SUM_SLS'], "billed" => "");
			//	}
			//}


			print("    google.load('visualization', '1', {packages: ['annotatedtimeline']});\n");
			print("    function drawVisualization() {\n");
			print("      var data = new google.visualization.DataTable();\n");
			print("      data.addColumn('date', 'Date');\n");
			print("      data.addColumn('number', 'Sales Booked');\n");
			print("      data.addRows([\n");

			while($row = mssql_fetch_assoc($results)) {
				if ($row['reccount'] <> 0) {
					if (strtotime($row['DATE_BOOK_LAST']) > $maxDateTS) {
						$maxDateTS = strtotime($row['DATE_BOOK_LAST']);
						$maxDate = $row['calc_date2'];
					}
					print("	[new Date('". $row['calc_date2'] ."'), ". $row['SUM_SLS'] . "],\n");
				}
			}
			print("      ]);\n");

			$dfTS = strtotime("-90 days" , $maxDateTS);
			$df = date('Y-m-d', $dfTS);




			print("      var annotatedtimeline = new google.visualization.AnnotatedTimeLine(\n");
			print("          document.getElementById('visualization'));\n");
			print("      annotatedtimeline.draw(data, {'displayAnnotations': true, 'zoomStartTime': new Date('".$df."'), 'zoomEndTime': new Date('".$maxDate."')});\n");
			//print("      annotatedtimeline.draw(data, {'displayAnnotations': true});\n");
			print("    }\n");
			print("    google.setOnLoadCallback(drawVisualization);\n");
			print("  </script>\n");
			print("</head>\n");
			print("<body style='font-family: Arial;border: 0 none;'>\n");
			print("<div id='visualization' style='width: 800px; height: 400px;'></div>\n");
			print("</body>\n");
			print("</html>\n");
*/

		}
	}
	PrintFooter('emenu.php');
?>