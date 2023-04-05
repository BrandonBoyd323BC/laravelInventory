<?php
/*
	$DEBUG = 0;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}
	include("../class/pData.class.php");
	include("../class/pDraw.class.php");
	include("../class/pImage.class.php");
	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if (isset($_POST["company"]))  {
				$ret .= "		<h4>Run On: " . date('Y-m-d g:i a') ."</h4>\n";
				$intervalArray = array(0,7,30,90,365);
				$bookingsArray = array();
				$billingsArray = array();
				$company = $_POST["company"];
				$prevDay = '';
				$prevDay2 = '';
				$coClause = '';
				switch ($company) {
					case "NSA":
						$coClause .= " and ID_CUST not like 'V%' ";
						break;
					case "VINA":
						$coClause .= " and ID_CUST like 'V%' ";
						break;
					case "ALL":
						break;
				}
				$sql  = " SELECT max(DATE_INVC) as prevDay, ";
				$sql .= " CONVERT(varchar(12), max(DATE_INVC), 107) as prevDay2 ";
				$sql .= " FROM nsa.SLSHST_HDR ";
				$sql .= " WHERE 1=1 ";
				$sql .= $coClause;

				QueryDatabase($sql, $results);
				while($row = mssql_fetch_assoc($results)) {
					$prevDay = $row['prevDay'];
					$prevDay2 = $row['prevDay2'];
					$ret .= "		<h4>Previous Day: " . $prevDay2 . "</h4>\n";
				}
				$ret .= " <table>\n";
				$ret .= "	<tr>\n";


				////////////////////
				// Bookings
				////////////////////
				$ret .= "	<td>\n";
				$ret .= " 		<table class='sample'>\n";
				$ret .= " 			<tr class='d1r'>\n";
				$ret .= "	 			<th colspan=2>Bookings</th>\n";
				$ret .= " 			</tr>\n";
				foreach ($intervalArray as $interval) {
					$sql  = " SELECT sum(SLS) as SUM_SLS ";
					$sql .= " FROM nsa.BOKHST_LINE l ";
					$sql .= " WHERE l.DATE_BOOK_LAST between dateAdd(dd,-". $interval .",'". $prevDay ."') and '" . $prevDay . "' ";
					$sql .= $coClause;
					QueryDatabase($sql, $results);
					while($row = mssql_fetch_assoc($results)) {
						$sumSls = $row['SUM_SLS'];
						$bookingsArray[$interval] = $sumSls;
						if ($interval == 0) {
							$ret .= "				<tr class = 'd1r'>\n";
							$ret .= "					<td>Previous Day: </td>\n";
							$ret .= "					<td>" . money_format('%(#10n',$sumSls) . "</td>\n";
							$ret .= "				</tr>\n";
						} else {
							$ret .= "				<tr class = 'd1r'>\n";
							$ret .= "					<td>Last " . $interval . " Days: </td>\n";
							$ret .= "					<td>" . money_format('%(#10n',$sumSls) . "</td>";
							$ret .= "				<tr>\n";
						}
					}
				}
				$ret .= "		</table>\n";
				$ret .= " 	</td>\n";

				////////////////////
				// Bookings Chart
				////////////////////
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
				$sql .= $coClause;
				$sql .= " GROUP BY dr.calc_date ";
				$sql .= " ORDER BY dr.calc_date asc ";
				$DEBUG=1;
				QueryDatabase($sql, $results);
				$DEBUG=0;
				$MyData = new pData();
				$sumSlsArray = '';
				$calcDateArray = '';
				$combinedArray = array();

				while($row = mssql_fetch_assoc($results)) {
					if ($row['reccount'] <> 0) {
						//$MyData->addPoints($row['SUM_SLS'],"Booked");
						$sumSlsArray[] = $row['SUM_SLS'];
						$calcDateArray[] = $row['calc_date2'];
						$combinedArray[$row['calc_date2']] = array("calc_date2" => $row['calc_date2'],"booked" => $row['SUM_SLS'], "billed" => "");
					}
				}
				$MyData->addPoints($sumSlsArray,"Bookings");
				$MyData->addPoints($calcDateArray,"Date");
				$MyData->setSerieDescription("Date","Date");
				$MyData->setAbscissa("Date");
				$MyData->setAbscissaName("Date");
				$MyData->setAxisName(0,"Dollars");
				$MyData->loadPalette("../palettes/navy.color",TRUE);

				$myPicture = new pImage(800,230,$MyData);
				$myPicture->Antialias = FALSE;
				$myPicture->drawRectangle(0,0,760,229,array("R"=>0,"G"=>0,"B"=>0));
				$myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>1));
				$myPicture->drawText(150,35,"Bookings (90 Days)",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));
				$myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>7));
				$myPicture->setGraphArea(60,40,750,200);
				$scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"LabelSkip"=>10,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
				$myPicture->drawScale($scaleSettings);
				$myPicture->Antialias = TRUE;
				$myPicture->drawBestFit();
				$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
				$myPicture->drawPlotChart();
				$myPicture->drawLegend(580,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
				$myPicture->render("../images/graphs/bookings.png");

				$ret .= " 	<td>\n";
				$ret .= "		<IMG SRC='images/graphs/bookings.png'>";
				$ret .= " 	</td>\n";
				$ret .= " </tr>\n";
*/














				$ret = "[";
				$ret .= "    {";
				$ret .= "        'DisplayName': 'United States',";
				$ret .= "        'Name': 'US',";
				$ret .= "        'population': 307006550";
				$ret .= "    },";
				$ret .= "    {";
				$ret .= "        'DisplayName': 'Canada',";
				$ret .= "        'Name': 'CA',";
				$ret .= "        'population': 33739900";
				$ret .= "    },";
				$ret .= "    {";
				$ret .= "        'DisplayName': 'United Kingdom',";
				$ret .= "        'Name': 'GB',";
				$ret .= "        'population': 61838154";
				$ret .= "    },";
				$ret .= "    {";
				$ret .= "        'DisplayName': 'Mexico',";
				$ret .= "        'Name': 'MX',";
				$ret .= "        'population': 107431225";
				$ret .= "    }";
				$ret .= "]";



/*
$ret = "		[new Date(2008, 1 ,1), 30000, undefined, undefined, 40645, undefined, undefined],";
$ret .= "		[new Date(2008, 1 ,2), 14045, undefined, undefined, 20374, undefined, undefined],";
$ret .= "		[new Date(2008, 1 ,3), 55022, undefined, undefined, 50766, undefined, undefined],";
$ret .= "		[new Date(2008, 1 ,4), 75284, undefined, undefined, 14334, 'Out of Stock','Ran out of stock on pens at 4pm'],";
$ret .= "		[new Date(2008, 1 ,5), 41476, 'Bought Pens','Bought 200k pens', 66467, undefined, undefined],";
$ret .= "		[new Date(2008, 1 ,6), 33322, undefined, undefined, 39463, undefined, undefined]";


$ret = "{";
$ret .= "  cols: [{id: 'A', label: 'NEW A', type: 'string'},";
$ret .= "         {id: 'B', label: 'B-label', type: 'number'},";
$ret .= "         {id: 'C', label: 'C-label', type: 'date'}";
$ret .= "        ],";
$ret .= "  rows: [{c:[{v: 'a'}, {v: 1.0, f: 'One'}, {v: new Date(2008, 1, 28, 0, 31, 26), f: '2/28/08 12:31 AM'}]},";
$ret .= "         {c:[{v: 'b'}, {v: 2.0, f: 'Two'}, {v: new Date(2008, 2, 30, 0, 31, 26), f: '3/30/08 12:31 AM'}]},";
$ret .= "         {c:[{v: 'c'}, {v: 3.0, f: 'Three'}, {v: new Date(2008, 3, 30, 0, 31, 26), f: '4/30/08 12:31 AM'}]}";
$ret .= "        ],";
$ret .= "  p: {foo: 'hello', bar: 'world!'}";
$ret .= "}";




				$ret .= " <tr>\n";

				$ret .= "  <script type='text/javascript' src='http://www.google.com/jsapi'></script>\n";
				$ret .= "  <script type='text/javascript'>\n";


				$ret .= "  alert('HELLO');\n";


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


				//$DEBUG=1;
				QueryDatabase($sql, $results);
				//$DEBUG=0;
				$maxDateTS = '';
				$maxDate = '';

				//$ret .= "    google.load('visualization', '1', {packages: ['annotatedtimeline']});\n";
				$ret .= "    function drawVisualization() {\n";
				$ret .= "      var data = new google.visualization.DataTable();\n";
				$ret .= "      data.addColumn('date', 'Date');\n";
				$ret .= "      data.addColumn('number', 'Sales Booked');\n";
				$ret .= "      data.addRows([\n";

				while($row = mssql_fetch_assoc($results)) {
					if ($row['reccount'] <> 0) {
						if (strtotime($row['DATE_BOOK_LAST']) > $maxDateTS) {
							$maxDateTS = strtotime($row['DATE_BOOK_LAST']);
							$maxDate = $row['calc_date2'];
						}
						$ret .= "	[new Date('". $row['calc_date2'] ."'), ". $row['SUM_SLS'] . "],\n";
					}
				}
				$ret .= "       ]);\n";

				$dfTS = strtotime("-90 days" , $maxDateTS);
				$df = date('Y-m-d', $dfTS);

				$ret .= "      var annotatedtimeline = new google.visualization.AnnotatedTimeLine(\n";
				$ret .= "          document.getElementById('visualization'));\n";
				$ret .= "      annotatedtimeline.draw(data, {'displayAnnotations': true, 'zoomStartTime': new Date('".$df."'), 'zoomEndTime': new Date('".$maxDate."')});\n";
				$ret .= "    }\n";
				//$ret .= "    google.setOnLoadCallback(drawVisualization);\n";
				$ret .= "  </script>\n";
				//$ret .= "</head>\n";
				//$ret .= "<body style='font-family: Arial;border: 0 none;'>\n";
				//$ret .= "<div id='visualization' style='width: 800px; height: 400px;'></div>\n";
				$ret .= " </tr>\n";










				//echo json_encode(array("returnValue"=> $ret));

			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}
*/
	echo json_encode($ret);
	//echo $ret;
?>
