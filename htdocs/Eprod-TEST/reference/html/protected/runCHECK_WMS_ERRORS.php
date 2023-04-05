<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/mail.class.php');

	$retval = ConnectToDatabaseServerWH($DBServerWHSQL, $dbWHSQL);
	if ($retval == 0) {
		error_log("runCHECK_WMS_ERRORS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbNameWHSQL);
		if ($retval == 0) {
			error_log("runCHECK_WMS_ERRORS cannot select " . $dbNameWHSQL);
		} else {
			error_log("#############################################");
			error_log("### runCHECK_WMS_ERRORS started at " . date('Y-m-d g:i:s a'));
			error_log("### runCHECK_WMS_ERRORS CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			error_log("### runCHECK_WMS_ERRORS CHECKING DUPLICATE HOST ORDERS ");
			$sql = " select count(HOST_ORDER) as HOST_ORD_COUNT, HOST_ORDER ";
			$sql .= " from dbo.PICKHEAD group by HOST_ORDER ";
			$sql .= " having count(HOST_ORDER) > 1";
			QueryDatabaseWHSQL($sql, $results);
			error_log($sql);

			if (mssql_num_rows($results) > 0){
				while ($row = mssql_fetch_assoc($results)) {
					$to_email = '';
					$subject = "DUPLICATE HOST ORDERS FOUND IN PICKHEAD ";
					$body = "A host order number has been found to be a duplicate in WMS PICKHEAD.";
					$body .= "\r\n\r\nOrder: " . $row['HOST_ORDER'] . "\r\nCount: " . $row['HOST_ORD_COUNT'] . ;
	
				}//end while

				if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
					$head = array(
				    	'to'      =>array('rbollinger@thinknsa.com'=>'Rich Bollinger'),
				    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
			    	);
		    	} else {
		    		$head = array(
				    	'to'      =>array('rbollinger@thinknsa.com'=>'Rich Bollinger'),
				    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
				    	'bcc'     =>array('rbollinger@thinknsa.com'=>'Rich Bollinger'),
			    	);
		    	}

				mail::send($head,$subject,$body);

			}//end if
			/*
			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runCHECK_WMS_ERRORS' ";
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
				$sql .= "'runCHECK_WMS_ERRORS', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runCHECK_WMS_ERRORSS SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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


				//////////////////////////////////////////
				// CHECK FOR DUPLICATE CUSTOMER PO'S
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING DUPLICATE CUSTOMER PO'S ");
				$sql  = " SELECT oh.ID_CUST_SOLDTO, oh.SEQ_SHIPTO, oh.ID_PO_CUST, count(oh.ID_ORD) as ORD_COUNT ";
				$sql .= " FROM nsa.CP_ORDHDR oh ";
				$sql .= " GROUP BY ID_CUST_SOLDTO, SEQ_SHIPTO, ID_PO_CUST";
				$sql .= " HAVING count(oh.ID_ORD) > 1";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$to_email = '';
					$subject = "DUPLICATE CUSTOMER PO'S DETECTED IN OPEN ORDERS";
					$body = "A Customer PO has been found to be a duplicate in Open Orders. Please correct this so these emails will stop.";

					$sql1  = " SELECT oh.ID_ORD, oh.ID_CUST_SOLDTO, oh.SEQ_SHIPTO, oh.NAME_CUST, oh.ID_PO_CUST, oh.TIME_ADD, oh.ID_USER_ADD, a.EMAIL ";
					$sql1 .= " CONVERT(varchar(8), oh.DATE_ADD, 112) as DATE_ADD3 ";
					$sql1 .= " FROM nsa.CP_ORDHDR oh ";
					$sql1 .= " LEFT JOIN nsa.DCWEB_AUTH a ";
					$sql1 .= " on oh.ID_USER_ADD = a.ID_USER ";
					$sql1 .= " WHERE oh.ID_CUST_SOLDTO = '".$row['ID_CUST_SOLDTO']."' ";
					$sql1 .= " and oh.SEQ_SHIPTO = '".$row['SEQ_SHIPTO']."' ";
					$sql1 .= " and oh.ID_PO_CUST = '".$row['ID_PO_CUST']."' ";
					QueryDatabase($sql1, $results1);
					while ($row1 = mssql_fetch_assoc($results1)) {
						$DATE = $row1['DATE_ADD3'] . " " . str_pad($row1['TIME_ADD'],6,"0",STR_PAD_LEFT);
						$to_email = $row1['EMAIL'];

						$body .= "\r\n\r\nOrder: " . $row1['ID_ORD'] . "\r\nCustomer ID: " . $row1['ID_CUST_SOLDTO'] . "\r\nShipTo: " . $row1['SEQ_SHIPTO'];
						$body .= "\r\nName Cust: " . $row1['NAME_CUST'] .  "\r\nCustomer PO: " . $row1['ID_PO_CUST'];
						$body .= "\r\nEntered By: " . $row1['ID_USER_ADD'] . "\r\nOn: " . $DATE;
						error_log("### USER: " . $row1['ID_USER_ADD']);
						error_log("### ORDER: " . $row1['ID_ORD']);
						error_log("### CUST PO: " . $row1['ID_PO_CUST']);
					}

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array($to_email=>$to_email),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy','jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
					    	'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);







					//$headers = "From: eProduction@thinknsa.com" . "\r\n" .
					//	"X-Mailer: PHP/" . phpversion();

					//$to = $row['EMAIL'];
					//if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
					//	$to = "gvandyne@thinknsa.com";
					//}
					//mail($to, $subject, $body, $headers);
					//error_log("### MAIL SENT TO: " . $to);
					//$to = "gvandyne@thinknsa.com";
					//mail($to, $subject, $body, $headers);
					//error_log("### MAIL SENT TO: " . $to);

				}

				//////////////////////////////////////////
				// CHECK FOR NULL DATES IN CP_ORDLIN
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR NULL DATES IN CP_ORDLIN ");
				$sql  = " select ";
				$sql .= "	l.ID_ORD, ";
				$sql .= "	l.SEQ_LINE_ORD, ";
				$sql .= "	l.ID_ITEM, ";
				$sql .= "	l.ID_USER_ADD, ";
				$sql .= "	CONVERT(varchar(8), l.DATE_ADD, 112) as DATE_ADD3, ";
				$sql .= "	l.DATE_ADD, ";
				$sql .= "	l.TIME_ADD, ";
				$sql .= "	l.DATE_RQST, ";
				$sql .= "	l.DATE_PROM, ";
				$sql .= "	a.EMAIL ";
				$sql .= " from  ";
				$sql .= " 	nsa.cp_ordlin l  ";
				$sql .= " 	left join nsa.DCWEB_AUTH a ";
				$sql .= " 	on l.ID_USER_ADD = a.ID_USER ";
				$sql .= " where ";
				$sql .= " 	l.DATE_PROM is null ";
				$sql .= " 	OR l.DATE_RQST is null ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);

					$subject = "ORDER LINE With NULL DATE: " . $row['ID_ORD'];
					$body = "An order that was recently entered is missing required Date information. Please correct this so these emails will stop." .
						"\r\n\r\nOrder: " . $row['ID_ORD'] . "\r\nLine: " . $row['SEQ_LINE_ORD'] . "\r\nItem: " . $row['ID_ITEM'] .
						"\r\nDate Rqst: " . $row['DATE_RQST'] .  "\r\nDate Prom: " . $row['DATE_PROM'] .
						"\r\nEntered By: " . $row['ID_USER_ADD'] . "\r\nOn: " . $DATE;

					$headers = "From: eProduction@thinknsa.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					$to = $row['EMAIL'];
					error_log("### USER: " . $row['ID_USER_ADD']);
					error_log("### ORDER: " . $row['ID_ORD']);
					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$to = "gvandyne@thinknsa.com";
					}
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);
					$to = "gvandyne@thinknsa.com";
					mail($to, $subject, $body, $headers);
					error_log("### MAIL SENT TO: " . $to);
				}


				//////////////////////////////////////////
				// CHECK FOR ORDERS PLACED UNDER V OR S CUSTOMERS
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR ORDERS PLACED UNDER V OR S OR T CUSTOMERS ");
				$sql  = "SELECT ";
				$sql .= " oh.ID_CUST_SOLDTO, ";
				$sql .= " oh.NAME_CUST, ";
				$sql .= " ol.ID_ORD, ";
				$sql .= " ol.SEQ_LINE_ORD, ";
				$sql .= " ol.ID_ITEM, ";
				$sql .= " ol.FLAG_STK, ";
				$sql .= " ol.ID_USER_ADD, "; 
				$sql .= " CONVERT(varchar(8), ol.DATE_ADD, 112) as DATE_ADD3, ";
				$sql .= " ol.DATE_ADD, ";
				$sql .= " ol.TIME_ADD, ";
				$sql .= " ol.DATE_RQST, ";
				$sql .= " ol.DATE_PROM, ";
				$sql .= " a.NAME_EMP, ";
				$sql .= " a.EMAIL ";
				$sql .= " from nsa.CP_ORDLIN ol ";
				$sql .= " left join nsa.CP_ORDHDR oh ";
				$sql .= " on ol.ID_ORD = oh.ID_ORD ";
				$sql .= " left join nsa.DCWEB_AUTH a ";
				$sql .= " on ol.ID_USER_ADD = a.ID_USER ";
				$sql .= " where (oh.ID_CUST_SOLDTO like 'S%' OR oh.ID_CUST_SOLDTO like 'T%' OR oh.ID_CUST_SOLDTO like 'V%') ";
				$sql .= " and oh.DATE_ADD > '2017-01-01' ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN ORDER WAS ENTERED UNDER A V, S, or T ACCOUNT. Order: " . $row['ID_ORD'];

					$body  = "<html>";
					$body .= "	<p>An order that was recently under a V, S, or T account. <br>Please correct this so these emails will stop.</p>";
					$body .= "	<p>Order: " . $row['ID_ORD'];
					$body .= "  	<br>Line: " . $row['SEQ_LINE_ORD'];
					$body .= "		<br>Item: " . $row['ID_ITEM'];
					$body .= "		<br>Customer ID: " . $row['ID_CUST_SOLDTO'];
					$body .= "		<br>Name Cust: " . $row['NAME_CUST'];
					$body .= "		<br>Entered By: " . $row['ID_USER_ADD'] . " On: " . $DATE;
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'mfigueroa@thinknsa.com'=>'Micel Figueroa','jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman','ddesvari@thinknsa.com'=>'Dave Desvari'),
					    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}


				//////////////////////////////////////////
				// CHECK FOR ORDERS PLACED UNDER T-98 OR T-102
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR ORDERS PLACED UNDER V OR S OR T CUSTOMERS ");
				$sql  = "SELECT ";
				$sql .= " oh.ID_CUST_SOLDTO, ";
				$sql .= " oh.NAME_CUST, ";
				$sql .= " oh.ID_TERR, ";
				$sql .= " oh.ID_SLSREP_1, ";
				$sql .= " oh.ID_ORD, ";
				$sql .= " oh.ID_USER_ADD, ";
				$sql .= " CONVERT(varchar(8), oh.DATE_ADD, 112) as DATE_ADD3, ";
				$sql .= " oh.DATE_ADD, ";
				$sql .= " oh.TIME_ADD, ";
				$sql .= " a.NAME_EMP, ";
				$sql .= " a.EMAIL ";
				$sql .= " from nsa.CP_ORDHDR oh ";
				$sql .= " left join nsa.DCWEB_AUTH a ";
				$sql .= " on oh.ID_USER_ADD = a.ID_USER ";
				$sql .= " where (ltrim(oh.ID_TERR) in ('98','102') OR ltrim(oh.ID_SLSREP_1) in ('98','102')) ";
				$sql .= " and oh.DATE_ADD > '2017-01-01' ";
				$sql .= " order by ID_ORD asc ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN ORDER WAS ENTERED UNDER INVALID TERRITORY 98 OR 102. Order: " . $row['ID_ORD'];

					$body  = "<html>";
					$body .= "	<p>An order that was recently under an invalid Territory. <br>Please correct this so these emails will stop.</p>";
					$body .= "	<p>Order: " . $row['ID_ORD'];
					$body .= "  	<br>Line: " . $row['SEQ_LINE_ORD'];
					$body .= "		<br>Item: " . $row['ID_ITEM'];
					$body .= "		<br>Customer ID: " . $row['ID_CUST_SOLDTO'];
					$body .= "		<br>Name Cust: " . $row['NAME_CUST'];
					$body .= "		<br>Territory: " . $row['ID_TERR'];
					$body .= "		<br>Sales Rep: " . $row['ID_SLSREP_1'];
					$body .= "		<br>Entered By: " . $row['ID_USER_ADD'] . " On: " . $DATE;
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'mfigueroa@thinknsa.com'=>'Micel Figueroa','jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman','ddesvari@thinknsa.com'=>'Dave Desvari'),
					    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}



				//////////////////////////////////////////
				// CHECK FOR CUSTOMER ORDERS IN WRONG TERRITORY OR SALESREP
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR CUSTOMER ORDERS IN WRONG TERRITORY OR SALESREP ");
				$sql  = "SELECT ";
				$sql .= " oh.ID_CUST_SOLDTO, ";
				$sql .= " oh.NAME_CUST, ";
				$sql .= " oh.ID_ORD, ";
				$sql .= " oh.ID_TERR, ";
				$sql .= " oh.ID_SLSREP_1, ";
				$sql .= " oh.ID_USER_ADD,  ";
				$sql .= " CONVERT(varchar(8), oh.DATE_ADD, 112) as DATE_ADD3, ";
				$sql .= " oh.DATE_ADD, ";
				$sql .= " oh.TIME_ADD, ";
				$sql .= " a.NAME_EMP, ";
				$sql .= " a.EMAIL ";
				$sql .= " FROM nsa.CP_ORDHDR oh ";
				$sql .= " LEFT JOIN nsa.DCWEB_AUTH a ";
				$sql .= " on oh.ID_USER_ADD = a.ID_USER ";
				$sql .= " WHERE ";
				$sql .= " ( ";
				$sql .= "  (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '781142'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //SLATE ROCK
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '20211'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //ALASKA TEXTILES
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '301151'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //AFFINITY APPAREL
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '367795'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //AMAZON
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '368800'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //AMAZON
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '39169'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //ANCHORTEX
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '50049'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('81') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('81'))) "; //ARAMARK
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '210550'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('64') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('64'))) "; //BAKER FOUNDRY SUPPLY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '210657'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('64') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('64'))) "; //BECKER SAFETY & SUPPLY
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '102950'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //BOOT BARN
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '128620'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //CARDINAL SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '134250'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //CEMENTEX
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '140241'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //CINTAS
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '140167'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //CINTAS
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '146889'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //CLEAN
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '153680'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //CONNEY SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '191570'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //DRD TECHNOLOGIES
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '203570'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //EATON
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '273585'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //FARWEST LINE SPECIALTIES
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '317500'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //WW. GRAINGER
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '317503'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //WW. GRAINGER
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '103262'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //WW. GRAINGER
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '364750'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('81') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('81'))) "; //HI-LINE UTILITY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '364996'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //HUB INDUSTRIAL
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '365111'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //HUDSON WORKWEAR
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '570375'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('81') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('81'))) "; //MIDWEST WORKWEAR
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '598400'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //NATIONAL SAFETY INC.
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '706460'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //PK SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '719300'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //PRITCHETT SUPPLY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '741270'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //REX ENERGY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '741280'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //REX ENERGY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '747950'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //RIVERSIDE / AFFINITY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '754050'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //RUBEN BROTHERS
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '769253'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('79') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('79'))) "; //SAFETY PROTECTION WAREHOUSE
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '787700'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //SAFETY SMART GEAR
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '769380'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //SAFETY SUPPLY AMERICA
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '770151'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //SAMCO FREEZERWEAR
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '785255'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //SHERMCO
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '784255'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('79') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('79'))) "; //SHERMCO CANADA
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '804700'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //SOUTH COAST FIRE & SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '575795'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //T & D				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '853550'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //TRUE SAFETY GEAR
				//^^^^^REMOVED 8-15-17 - RB - PER DAVE SINCE CHUCK MADE A CHANGE TO HOW THIS IS DONE OR SOMETHING
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '854150'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //TYNDALE
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '881330'  AND ltrim(oh.SEQ_SHIPTO) <> '73' AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))  "; //UNIFIRST
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '881330'  AND ltrim(oh.SEQ_SHIPTO) = '73' AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('81') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('81'))  "; //UNIFIRST
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '881341'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //UNIFIRST
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '68566'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //WILLBROS CHAPMAN & LINEAL
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '191656'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('12') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('12'))) "; //WREN
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '697425'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('81') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('81'))) "; //WYANDOTTE SAFETY SOLUTIONS
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '549961'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //J.L. MATTHEWS
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '245595'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //EPSCO

				$sql .= " ) ";
				$sql .= " AND oh.DATE_ADD > '2017-01-01' ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "A(n) ".$row['NAME_CUST']." ORDER WAS ENTERED UNDER AN INVALID TERRITORY OR SALESREP. Order: " . $row['ID_ORD'];

					$body  = "<html>";
					$body .= "	<p>A ".$row['NAME_CUST']." order was recently entered under an invalid Territory/Sales Rep. <br>Please correct this so these emails will stop.</p>";
					$body .= "	<p>Order: " . $row['ID_ORD'];
					$body .= "  	<br>Territory: " . $row['ID_TERR'];
					$body .= "		<br>Sales Rep: " . $row['ID_SLSREP_1'];
					$body .= "		<br>Customer ID: " . $row['ID_CUST_SOLDTO'];
					$body .= "		<br>Name Cust: " . $row['NAME_CUST'];
					$body .= "		<br>Entered By: " . $row['ID_USER_ADD'] . " On: " . $DATE;
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
					    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}


				
				/////////////////////////////////////////////////////////////
				//////////CHECK FOR LOCATION AND ORDER LINE BIN MISMATCHES
				/////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR LOCATION AND ORDER LINE BIN MISMATCHES ");
				$sql = "select il.ID_ITEM, il.BIN_PRIM as LOC_BIN, ol.BIN_PRIM as ORD_BIN, ol.ID_ORD, ol.rowid ";
				$sql .= " from nsa.ITMMAS_LOC il ";
				$sql .= " left join nsa.CP_ORDLIN ol ";
				$sql .= " on il.ID_ITEM = ol.ID_ITEM and ltrim(il.ID_LOC) = ltrim(ol.ID_LOC) ";
				$sql .= " where (il.BIN_PRIM != ol.BIN_PRIM) and (il.BIN_PRIM != '') ";//commenting out "and (ol.BIN_PRIM != '')" due to order being excluded due to having blank bin in ord line table.
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$sql1 = " UPDATE nsa.CP_ORDLIN ";
					$sql1 .= " SET BIN_PRIM = '" .$row['LOC_BIN']. "' ";
					$sql1 .= " WHERE rowid = '" .$row['rowid']. "' ";
					error_log("UPDATE Original Ord Bin = " . $row['ORD_BIN'] . " ### New Bin = " . $row['LOC_BIN']);
					QueryDatabase($sql1, $results1);
				}//end while


				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runCHECK_SETUP_ERRORS DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);






				//////////////////////////////////////////
				// CHECK FOR ORDERS PLACED UNDER WITH 999 IN SLSREP
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR ORDERS WITH 999 IN SLSREP ");
				$sql  = "SELECT ";
				$sql .= " oh.ID_CUST_SOLDTO, ";
				$sql .= " oh.NAME_CUST, ";
				$sql .= " oh.ID_TERR, ";
				$sql .= " oh.ID_SLSREP_1, ";
				$sql .= " oh.ID_ORD, ";
				$sql .= " oh.ID_USER_ADD, ";
				$sql .= " CONVERT(varchar(8), oh.DATE_ADD, 112) as DATE_ADD3, ";
				$sql .= " oh.DATE_ADD, ";
				$sql .= " oh.TIME_ADD, ";
				$sql .= " a.NAME_EMP, ";
				$sql .= " a.EMAIL ";
				$sql .= " from nsa.CP_ORDHDR oh ";
				$sql .= " left join nsa.DCWEB_AUTH a ";
				$sql .= " on oh.ID_USER_ADD = a.ID_USER ";
				$sql .= " where ltrim(oh.ID_SLSREP_1) in ('999')) ";
				$sql .= " and oh.DATE_ADD > '2017-01-01' ";
				$sql .= " order by ID_ORD asc ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN ORDER WAS ENTERED UNDER INVALID SALESREP 999. Order: " . $row['ID_ORD'];

					$body  = "<html>";
					$body .= "	<p>An order that was recently under an invalid Sales Rep. <br>Please correct this so these emails will stop.</p>";
					$body .= "	<p>Order: " . $row['ID_ORD'];
					$body .= "  	<br>Line: " . $row['SEQ_LINE_ORD'];
					$body .= "		<br>Item: " . $row['ID_ITEM'];
					$body .= "		<br>Customer ID: " . $row['ID_CUST_SOLDTO'];
					$body .= "		<br>Name Cust: " . $row['NAME_CUST'];
					$body .= "		<br>Territory: " . $row['ID_TERR'];
					$body .= "		<br>Sales Rep: " . $row['ID_SLSREP_1'];
					$body .= "		<br>Entered By: " . $row['ID_USER_ADD'] . " On: " . $DATE;
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
					    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}







				//////////////////////////////////////////
				// CHECK FOR ITEMS WITH INVALID UPC-A BARCODE VALUES
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR ITEMS WITH INVALID UPC-A BARCODE VALUES ");
				$sql  = "SELECT ";
				$sql .= " b.ID_ITEM, ";
				$sql .= " z.UPC_CODE ";
				$sql .= " FROM nsa.ITMMAS_BASE b ";
				$sql .= " left join nsa.ITMMAS_BASZ z ";
				$sql .= " on b.rowid = z.RFA ";
				$sql .= " WHERE z.UPC_CODE is not null ";
				$sql .= " and ltrim(z.UPC_CODE) <> '' ";
				$sql .= " ORDER BY b.ID_ITEM asc ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$validBarcode = validate_UPCABarcode($row['UPC_CODE']);
					if ($validBarcode == false) {
						error_log("Invalid Barcode for Item: " . $row['ID_ITEM'] . " UPC_CODE: " . $row['UPC_CODE']);
						$subject = "AN ITEM WAS ENTERED WITH AN INVALID UPC CODE: " . $row['ID_ITEM'];
						$body  = "<html>";
						$body .= "	<p>An item was recently setup with an invalid UPC Code . <br>Please correct this so these emails will stop.</p>";
						$body .= "	<p>Item: " . $row['ID_ITEM'];
						$body .= "		<br>Invalid UPC Code: " . $row['UPC_CODE'];
						$body .= "	</p>";
						$body .= "</html>";

						if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
							$head = array(
						    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
						    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
						    	'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
						    	//'bcc'     =>array('email4@email.net'=>'Admin'),
					    	);
				    	} else {
				    		$head = array(
						    	'to'      =>array('r&d@thinknsa.com'=>'R & D'),
						    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
						    	'cc'      =>array('jmartin@thinknsa.com'=>'Jeff Martin'),
						    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	);
				    	}
						mail::send($head,$subject,$body);
						sleep(1);
					}
				}




				


			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runCHECK_SETUP_ERRORS ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}
			*/
			error_log("### runCHECK_SETUP_ERRORS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($dbWHSQL);
		if ($retval == 0) {
			error_log("runCHECK_WMS_ERRORS cannot disconnect from database");
		}
	}
?>
