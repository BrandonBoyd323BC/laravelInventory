<?php
	$DEBUG = 0;

	require_once("protected/procfile.php");
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {
			print("<html>\n");
			print("	<head>\n");
			print("		<meta http-equiv='Pragma' content='no-cache'>\n");
			print("		<meta http-equiv='Expires' content='-1'>\n");
			print("		<script type='text/javascript' src='JavaScript/jquery-1.6.4.js'  charset='utf-8'></script>\n");
			print("		<script type='text/javascript' src='JavaScript/jquery-ui-1.8.5.custom.min.js'  charset='utf-8'></script>\n");
			print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/default.css'>\n");
			print("		<title>NSA TV</title>\n");
			print("	</head>\n");
			print("	<body>\n");

			///////////////////
			// DISABLE DASHBOARDS DURING NIGHT HOURS TO SAVE TABLEAU DISK USAGE
			///////////////////
			if (date('H') >= 6) { //6AM or later

				if (isset($_GET['ID'])) {
					$dashId = "";
					$ticketUser = "";
					$ID = $_GET['ID'];

					switch ($ID) {
						case "preProdDailyProgress":
							$dashId = "DailyProgress-forTV&#47;DailyProgress";  //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "preProdWorkDistribution":
							$dashId = 'WorkDistribution-forTV&#47;WorkDistribution'; //Verified 11/14/19
							$ticketUser = "preprod";
						break;
						
						case "DaysworkavailablebyWorkCenter":
							//$dashId = 'DaysworkavailablebyWorkCentervs_StageinProduction&#47;DaysworkavailablebyWCvs_StageinProduction';  //Name Changed 
							$dashId = 'DaysworkavailablebyWorkCentervs_StageinProduction&#47;DaysworkavailablebyWCvs_StageinProductionTV';
							$ticketUser = "administrator";
						break;

						case "averagePlyHeight":
							//$dashId = 'AveragePlyHeight&#47;AveragePlyHeight'; //Name Changed 11/14/19
							$dashId = 'AveragePlyHeight&#47;AveragePlyHeightbyCutDate';
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "ReadyDaysbyWorkcenter": //NO LONGER FOUND ON AS1 SERVER??
							$dashId = 'ReadyDaysbyWorkcenter&#47;ReadyDaysbyWorkcenter'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "TSScreatedminutesfromcutting":
							$dashId = 'TSScreatedminutesfromcutting&#47;TSScreatedminutesfromcutting'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "ShipmentSummary":
							$dashId = 'ShipmentSummary-TV&#47;ShipmentSummary-TV'; //Verified 11/14/19
							$ticketUser = "preprod";
						break;

						case "WarehouseMetrics":
							$dashId = 'WarehouseMetrics-InvoicedLinesandSales-TV&#47;WarehouseMetrics-InvoicedLinesandSales-TV'; //Verified 11/14/19
							$ticketUser = "preprod";
						break;

						case "FCWarehouseMetrics":
							$dashId = 'TVFCWarehouseMetrics&#47;FCWarehouseMetrics'; //Verified 11/14/19
							$ticketUser = "preprod";
						break;

						case "CustomerServiceMetrics":
							$dashId = 'TVCustomerServiceMetrics&#47;CustomerServiceMetrics'; //Verified 11/14/19
							$ticketUser = "preprod";
						break;

						case "OrdersEnteredByCSR":
							$dashId = 'OrdersLinesEnteredbyCSRToday&#47;OrdersLinesEnteredBYCSRToday'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "RubberGloveTestLabMetrics":
							$dashId = 'RubberGloveTestLabMetrics_0&#47;RuberGloveTestLabMetrics'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "LateOrders":
							$dashId = 'LateOrders&#47;Lateorders'; //NOT FOUND 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "SQRSampleShopOrderStatus":
							$dashId = 'SQR&#47;SampleShopOrderStatus'; //Verified 11/14/19
							$ticketUser = "preprod";
						break;

						case "SQROpenSQR": //Used on R&D
							$dashId = 'SQR&#47;OpenSQR' ; //ERROR below 11/14/19
							$ticketUser = "preprod";
							/*
							An unexpected error occurred. If you continue to receive this error please contact your Tableau Server Administrator.
							Session ID: 2C15CF31F44048D2BDD1968D120A8985-1:2
							Abstract query SELECT [Active SQR].[Request #] is not defined.
							Unable to properly calculate the domain for the field 'Due Date'. Displayed data may be incorrect.
							2019-11-14 13:39:28.107, (Xc1ZAad@qaxOKQNBG-jKVgAAATk,1:2)
							Would you like to reset the view?
							*/
						break;

						case "StockLevelsGoalVsTarget":
							$dashId = 'StockLevels&#47;GoalStockvs_TargetStock'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "StockLevelsUnderstockLevel":
							$dashId = 'StockLevels&#47;UnderstockLevel'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						//case "OrdDelMetAvgLeadTime90Days":
						//	$dashId = 'OrderDeliveryMetrics&#47;IndustrialAverageLeadTimeLast30Days' ;
						//	$ticketUser = "ie1";
						//break;

						//case "OrdDelMetIndlMTOD1":
						//	$dashId = 'OrderDeliveryMetrics&#47;IndustrialMade-To-OrderDelivery1'  ;
						//	$ticketUser = "ie1";
						//break;

						//case "OrdDelMetIndlMTOD2":
						//	$dashId = 'OrderDeliveryMetrics&#47;IndustrialMade-To-OrderDelivery2'  ;
						//	$ticketUser = "ie1";
						//break;

						case "OrdDelMetSFMLxD":
							$dashId = 'OrderDeliveryMetrics&#47;StockFulfillmentMetricsLastxDays'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "OrdDelMetOTDPFxD":
							$dashId = 'OrderDeliveryMetrics&#47;On-TimeDeliveryPerformanceLastxDays'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "OrdDelMetAvgLeadTime30Days":
							$dashId = 'OrderDeliveryMetrics&#47;AverageLeadTimeLast30Days'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "OrdDelMetMTODelivery":
							$dashId = 'OrderDeliveryMetrics&#47;Made-To-OrderDelivery'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "5SQuestionOfTheDay":
							$dashId = '5S-SortSetShineStandardizeSustain&#47;5SQuestionoftheday'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "SizeTest":
							$dashId = 'TestforTV&#47;TestForTV'; //Verified 11/14/19
							//$ticketUser = "ie1";
							$ticketUser = "preprod";
						break;

						case "RollGoodCurrentLocation":
							$dashId = 'ReportRollGoodswithcurrentlocation&#47;ReportRollGoodswithcurrentlocations';
							$ticketUser = "operations";
						break;


					}

					if ($dashId <> "") {
						$ticket = getTableauTicket($ticketUser);
						print("<script type='text/javascript' src='http://as1/javascripts/api/viz_v1.js'></script>\n");
						print("<div class='tableauPlaceholder' style='width: 100%; height: auto;'>\n");
						print("	<object class='tableauViz' width=100% height=100% style='display:none;'>\n");
						print("		<param name='host_url' value='http%3A%2F%2Fas1%2F' />\n");
						print("		<param name='embed_code_version' value='3' />\n");
						print("		<param name='site_root' value='' />\n");
						print("		<param name='name' value='".$dashId."' />\n");
						print("		<param name='ticket' value='".$ticket."' />\n");
						print("		<param name='tabs' value='no' />\n");
						print("		<param name='toolbar' value='yes' />\n");
						print("		<param name='showAppBanner' value='false' />\n");
						print("		<param name='refresh' value='y' />\n");
						print("		<param name='filter' value='iframeSizedToWindow=true' />\n");
						print("	</object>\n");
						print("</div>\n");
					}
				}
			} else {
				//MESSAGE FOR OUTSIDE WORKING HOURS
				print("<h4>Server Sleepy-time</h4>\n");	
				print("<img src='images/sleepyServer.jpg'>\n");	
			}
			print("</body>\n");
			print("</html>\n");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}
?>


