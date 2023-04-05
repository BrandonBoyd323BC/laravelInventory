<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('Management Dashboard','default.css','mgmtdash.js');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $dbName!\n";
		} else {
			print("<html xmlns='http://www.w3.org/1999/xhtml'>\n");
			print("<head>\n");
			print("  <meta http-equiv='content-type' content='text/html; charset=utf-8' />\n");
			print("  <title>Management Dashboard</title>\n");
			print("  <script type='text/javascript' src='http://www.google.com/jsapi'></script>\n");
			print("  <script type='text/javascript'>\n");

			$prevDay = '';
			$sql  = " SELECT max(DATE_INVC) as prevDay ";
			//$sql .= " FROM nsa.SLSHST_HDR ";
			$sql .= " FROM nsa.CP_INVHDR_HIST ";
			//$sql .= " WHERE 1=1 ";
			QueryDatabase($sql, $results);
			while($row = mssql_fetch_assoc($results)) {
				$prevDay = $row['prevDay'];
			}
/*
			$sql  = " WITH date_range (calc_date) AS ( ";
			$sql .= " 	SELECT DATEADD(DAY,DATEDIFF(DAY,0,'". $prevDay ."') - DATEDIFF(DAY, dateAdd(dd,-90,'". $prevDay ."'),'". $prevDay ."'),0) ";
			$sql .= "		UNION ALL SELECT DATEADD(DAY,1,calc_date) ";
			$sql .= " 	FROM date_range ";
			$sql .= " 	WHERE DATEADD(DAY,1,calc_date) <= '". $prevDay ."') ";
			$sql .= " SELECT dr.calc_date, ";
			$sql .= "	CONVERT(varchar(12), calc_date, 107) as calc_date2, ";
			$sql .= "	COALESCE(sum(l.SLS),0) as SUM_SLS, ";
			$sql .= "	count(l.SLS) as reccount ";
			$sql .= " FROM nsa.BOKHST_LINE l ";
			$sql .= "	RIGHT JOIN date_range dr ";
			$sql .= "	on l.DATE_BOOK_LAST = dr.calc_date ";
			//$sql .= " WHERE 1=1 ";
			$sql .= " GROUP BY dr.calc_date ";
			$sql .= " ORDER BY dr.calc_date asc ";
*/

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
/*
			print("    google.load('visualization', '1', {packages: ['annotatedtimeline']});\n");
			print("    function drawVisualization() {\n");
			print("      var data = new google.visualization.DataTable();\n");
			print("      data.addColumn('date', 'Date');\n");
			print("      data.addColumn('number', 'Sold Pencils');\n");
			print("      data.addColumn('string', 'title1');\n");
			print("      data.addColumn('string', 'text1');\n");
			print("      data.addColumn('number', 'Sold Pens');\n");
			print("      data.addColumn('string', 'title2');\n");
			print("      data.addColumn('string', 'text2');\n");
			print("      data.addRows([\n");
			print("        [new Date(2008, 1 ,1), 30000, null, null, 40645, null, null],\n");
			print("        [new Date(2008, 1 ,2), 14045, null, null, 20374, null, null],\n");
			print("        [new Date(2008, 1 ,3), 55022, null, null, 50766, null, null],\n");
			print("        [new Date(2008, 1 ,4), 75284, null, null, 14334, 'Out of Stock', 'Ran out of stock on pens at 4pm'],\n");
			print("        [new Date(2008, 1 ,5), 41476, 'Bought Pens', 'Bought 200k pens', 66467, null, null],\n");
			print("        [new Date(2008, 1 ,6), 33322, null, null, 39463, null, null]\n");
			print("      ]);\n");
*/



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

		}
	}
	PrintFooter('emenu.php');
?>