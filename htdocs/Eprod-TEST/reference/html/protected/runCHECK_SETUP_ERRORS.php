<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/mail.class.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCHECK_SETUP_ERRORS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCHECK_SETUP_ERRORS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runCHECK_SETUP_ERRORS started at " . date('Y-m-d g:i:s a'));
			error_log("### runCHECK_SETUP_ERRORS CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runCHECK_SETUP_ERRORS' ";
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
				$sql .= "'runCHECK_SETUP_ERRORS', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runCHECK_SETUP_ERRORS SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
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

					$sql1  = " SELECT oh.ID_ORD, oh.ID_CUST_SOLDTO, oh.SEQ_SHIPTO, oh.NAME_CUST, oh.ID_PO_CUST, oh.TIME_ADD, oh.ID_USER_ADD, a.EMAIL, ";
					$sql1 .= " CONVERT(varchar(8), oh.DATE_ADD, 112) as DATE_ADD3 ";
					$sql1 .= " FROM nsa.CP_ORDHDR oh ";
					$sql1 .= " LEFT JOIN nsa.DCWEB_AUTH a ";
					$sql1 .= " on oh.ID_USER_ADD = a.ID_USER ";
					$sql1 .= " WHERE oh.ID_CUST_SOLDTO = '".$row['ID_CUST_SOLDTO']."' ";
					$sql1 .= " and oh.SEQ_SHIPTO = '".$row['SEQ_SHIPTO']."' ";
					$sql1 .= " and oh.ID_PO_CUST = '".$row['ID_PO_CUST']."' ";
					$sql1 .= " and oh.ID_USER_ADD != 'RMA' ";
					$sql1 .= " and oh.ID_PO_CUST not in (select ID_PO_CUST from nsa.CP_ORDHDR where ID_USER_ADD = 'RMA') ";
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
					    	'cc'      =>array('llufkin@thinknsa.com'=>'Lisa Lufkin'),
					    	//'cc'      =>array('jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
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
				// CHECK FOR NULL DATES IN NSA CP_ORDLIN
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
				$sql .= " 	(l.DATE_PROM is null OR l.DATE_RQST is null) ";
				$sql .= " 	and l.ID_ORD > 100000 ";
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
				// CHECK FOR NULL DATES IN RUBIN CP_ORDLIN
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
				$sql .= " 	rbn.cp_ordlin l  ";
				$sql .= " 	left join nsa.DCWEB_AUTH a ";
				$sql .= " 	on l.ID_USER_ADD = a.ID_USER ";
				$sql .= " where ";
				$sql .= " 	(l.DATE_PROM is null OR l.DATE_RQST is null) ";
				$sql .= " 	and l.ID_ORD > 100000 ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);

					$subject = "RUBIN ORDER LINE With NULL DATE: " . $row['ID_ORD'];
					$body = "A Rubin order that was recently entered is missing required Date information. Please correct this so these emails will stop." .
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
					    	'to'      =>array('hsweeney@thinknsa.com'=>'Heidi Sweeney'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'bfitch@thinknsa.com'=>'Beth Fitch','llufkin@thinknsa.com'=>'Lisa Lufkin','jgrossman@thinknsa.com'=>'Joe Grossman','ddesvari@thinknsa.com'=>'Dave Desvari'),
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
					    	'to'      =>array('hsweeney@thinknsa.com'=>'Heidi Sweeney'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'bfitch@thinknsa.com'=>'Beth Fitch','llufkin@thinknsa.com'=>'Lisa Lufkin','jgrossman@thinknsa.com'=>'Joe Grossman','ddesvari@thinknsa.com'=>'Dave Desvari'),
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
				$sql .= " WHERE oh.DATE_ADD > '2017-01-01' ";
				$sql .= " AND ltrim(oh.ID_ORD) not in ('439912','439930','485994','493274','494556','494580','496248','497541','497534','501655','507937','516866','517934','598400','556352','557675','572985') "; //EXCEPTIONS
				$sql .= " AND ( ";
				$sql .= "  (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '781142'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //SLATE ROCK
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '20211'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //ALASKA TEXTILES
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '301151'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //AFFINITY APPAREL
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '367795'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //AMAZON
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '368800'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //AMAZON
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '39169'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //ANCHORTEX
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '50049'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //ARAMARK
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '210550'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('44') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('44'))) "; //BAKER FOUNDRY SUPPLY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '210657'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('64') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('64'))) "; //BECKER SAFETY & SUPPLY
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '102950'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //BOOT BARN
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '128620'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //CARDINAL SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '134250'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //CEMENTEX
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '140241'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //CINTAS //REMOVED 1/3/2019
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '140167'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //CINTAS
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '146889'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //CLEAN
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '153680'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //CONNEY SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '191570'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //DRD TECHNOLOGIES
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '203570'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //EATON
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '273585'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('64') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('64'))) "; //FARWEST LINE SPECIALTIES
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '317500'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //WW. GRAINGER
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '317503'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //WW. GRAINGER
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '103262'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //WW. GRAINGER
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '364750'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //HI-LINE UTILITY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '364996'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //HUB INDUSTRIAL
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '365111'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //HUDSON WORKWEAR
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '570375'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //MIDWEST WORKWEAR
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '598400'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //NATIONAL SAFETY INC.
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '706460'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('50') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('50'))) "; //PK SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '719300'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //PRITCHETT SUPPLY
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '741270'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //REX ENERGY //REMOVED 1-3-2019 Per Ashley
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '741280'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //REX ENERGY //REMOVED 1-3-2019 Per Ashley
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '747950'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //RIVERSIDE / AFFINITY //REMOVED 1-3-2019 Per Ashley
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '754050'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //RUBEN BROTHERS
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '769253'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('79') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('79'))) "; //SAFETY PROTECTION WAREHOUSE
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '787700'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //SAFETY SMART GEAR
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '769380'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('64') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('64'))) "; //SAFETY SUPPLY AMERICA
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '770151'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //SAMCO FREEZERWEAR
				$sql .= "  OR ((rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '785255'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) AND oh.DATE_ADD > '2019-02-04')"; //SHERMCO
				$sql .= "  OR ((rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '784255'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36')))  AND oh.DATE_ADD > '2019-02-04')"; //SHERMCO CANADA
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '804700'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //SOUTH COAST FIRE & SAFETY
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '73747'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24','999') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //SUMMIT SIGN
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '575795'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //T & D				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '853550'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('24') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('24'))) "; //TRUE SAFETY GEAR
				//^^^^^REMOVED 8-15-17 - RB - PER DAVE SINCE CHUCK MADE A CHANGE TO HOW THIS IS DONE OR SOMETHING
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '854150'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //TYNDALE
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '881330'  AND ltrim(oh.SEQ_SHIPTO) <> '73' AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17')))  "; //UNIFIRST
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '881330'  AND ltrim(oh.SEQ_SHIPTO) = '73' AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19')))  "; //UNIFIRST
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '881341'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('17') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('17'))) "; //UNIFIRST
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '68566'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('95') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('95'))) "; //WILLBROS CHAPMAN & LINEAL
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '191656'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('12') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('12'))) "; //WREN
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '697425'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('12') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('12'))) "; //WYANDOTTE SAFETY SOLUTIONS
				$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '549961'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('36') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('36'))) "; //J.L. MATTHEWS
				//$sql .= "  OR (rtrim(ltrim(oh.ID_CUST_SOLDTO)) = '245595'  AND (rtrim(ltrim(oh.ID_TERR)) NOT in ('19') OR rtrim(ltrim(oh.ID_SLSREP_1)) NOT in ('19'))) "; //EPSCO //REMOVED 1-3-2019 Per Ashley
				$sql .= " ) ";
				//$sql .= " AND oh.DATE_ADD > '2017-01-01' ";
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
					    	'to'      =>array($row['EMAIL']=>$row['NAME_EMP']),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
					    	'cc'      =>array('bfitch@thinknsa.com'=>'Beth Fitch','llufkin@thinknsa.com'=>'Lisa Lufkin'),
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
				$sql .= " where ltrim(oh.ID_SLSREP_1) in ('999') ";
				$sql .= " and oh.DATE_ADD > '2017-01-01' ";
				$sql .= " and oh.CODE_STAT_ORD = 'A' ";
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
						if ($row['ID_USER_ADD'] == 'RMA') {
				    		$head = array(
				    			////////STOP EMAILS FOR RMA's PER JEFF - RB - 10-02-2018////////////
						    	/*'to'      =>array('amalloy@thinknsa.com'=>'Ashley Malloy'),
						    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
						    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman','accounting@thinknsa.com'=>'Accounting Dept'),
						    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),*/
					    	);
						} else {
				    		$head = array(
						    	'to'      =>array('bfitch@thinknsa.com'=>'Beth Fitch'),
						    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
						    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP']'llufkin@thinknsa.com'=>'Lisa Lufkin'),
						    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	);
						}		    		
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}






				//////////////////////////////////////////
				// CHECK FOR INVOICES PLACED UNDER WITH 999 IN SLSREP
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR INVOICES WITH 999 IN SLSREP ");
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
				$sql .= " from nsa.CP_INVHDR oh ";
				$sql .= " left join nsa.DCWEB_AUTH a ";
				$sql .= " on oh.ID_USER_ADD = a.ID_USER ";
				$sql .= " where ltrim(oh.ID_SLSREP_1) in ('999') ";
				$sql .= " and oh.DATE_ADD > '2017-01-01' ";
				$sql .= " and oh.CODE_STAT_ORD = 'A' ";
				$sql .= " order by ID_ORD asc ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN INVOICE WAS ENTERED UNDER INVALID SALESREP 999. Order: " . $row['ID_ORD'];

					$body  = "<html>";
					$body .= "	<p>An invoice was recently created under an invalid Sales Rep. <br>Please correct this so these emails will stop.</p>";
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
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('accounting@thinknsa.com'=>'Accounting','claims@thinknsa.com'=>'Claims Department'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'jberingo@thinknsa.com'=>'Jody Beringo','jgrossman@thinknsa.com'=>'Joe Grossman'),
					    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}










				//////////////////////////////////////////
				// CHECK FOR ORDERS PLACED IN SOUTHERN CALIFORNIA UNDER THE WRONG TERRITORY
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR ORDERS PLACED IN SOUTHERN CALIFORNIA UNDER THE WRONG TERRITORY ");
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
				$sql .= " FROM nsa.CP_ORDHDR oh ";
				$sql .= " LEFT JOIN nsa.DCWEB_AUTH a ";
				$sql .= " on oh.ID_USER_ADD = a.ID_USER ";
				$sql .= " WHERE oh.ID_ST = 'CA' ";
				$sql .= " and ltrim(left(ZIP, 3)) <= '935' ";
				$sql .= " and ltrim(ID_SLSREP_1) = '50' ";
				$sql .= " and ltrim(ID_CUST_SOLDTO) not in ('706460','364851') ";
				$sql .= " ORDER BY ID_ORD asc ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN ORDER FOR SOUTHERN CALIFORNIA WAS ENTERED UNDER THE WRONG SALESREP. Order: " . $row['ID_ORD'];

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
					    	'to'      =>array('customerservice@thinknsa.com'=>'Customer Service'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'bfitch@thinknsa.com'=>'Beth Fitch','jgrossman@thinknsa.com'=>'Joe Grossman'),
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
						    	//'cc'      =>array('gvandyne@thinknsa.com'=>'Rich Bollinger'),
						    	//'bcc'     =>array('email4@email.net'=>'Admin'),
					    	);
				    	} else {
				    		$head = array(
						    	'to'      =>array('r&d@thinknsa.com'=>'R & D'),
						    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
						    	//'cc'      =>array('jmartin@thinknsa.com'=>'Jeff Martin'),
						    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	);
				    	}
						mail::send($head,$subject,$body);
						sleep(1);
					}
				}


				//////////////////////////////////////////////////////////////////////////////////////
				///////////////CHECK FOR TERR 103 with D% SOLD TO WITH A BLANK SHIP_TO_USER_CD1
				/////////////////////////////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR BLANK CODE_CUST_3 FOR ORDER WITH TERR 103 AND D% SOLD TO CUST ");
				$sql = " select * ";
				$sql .= " from nsa.CP_ORDHDR cp ";
				$sql .= " left join nsa.DCWEB_AUTH wa ";
				$sql .= " on cp.ID_USER_ADD = wa.ID_USER ";
				$sql .= " where ID_TERR in ('103','105','109') and ID_CUST_SOLDTO like 'D%' and CODE_CUST_3 = '' ";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN ORDER WAS ENTERED WITHOUT THE SHIP TO USER CD1 FIELD. Order: " . $row['ID_ORD'];

					$body  = "<html>";
					$body .= "	<p>An order was recently entered without the ship to user Cd1 field. <br>Please correct this so these emails will stop.</p>";
					$body .= "	<p>Order: " . $row['ID_ORD'];
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
					    	'to'      =>array('llufkin@thinknsa.com'=>'Lisa Lufkin'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'llufkin@thinknsa.com'=>'Lisa Lufkin'),
					    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}


				//////////////////////////////////////////////////////////////////////
				//////////CHECK FOR SHERMCO PARTS WITH DATE PROM GREATER THAN 15 BUSINESS DAYS
				//////////////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR SHERMCO PARTS WITH A PROMISE DATE GREATER THAN 15 BUSINESS DAYS ");
				 $sql = "     SELECT ";
				 $sql .= "        (DATEDIFF(dd, GETDATE(), ol.DATE_PROM) + 1) ";
				 $sql .= "       -(DATEDIFF(wk, GETDATE(), ol.DATE_PROM) * 2) ";
				 $sql .= "       -(CASE WHEN DATENAME(dw, GETDATE()) = 'Sunday' THEN 1 ELSE 0 END) ";
				 $sql .= "       -(CASE WHEN DATENAME(dw, ol.DATE_PROM) = 'Saturday' THEN 1 ELSE 0 END) as WORKING_DAYS_DIFF,  ";
				 $sql .= "		 -(CASE WHEN (select count(DATE_HOLIDAY) as NUM_HOLIDAYS from nsa.CALENDAR_HOLIDAY_VIEW where DATE_HOLIDAY between GETDATE() and ol.DATE_PROM) > 0 THEN (select count(DATE_HOLIDAY) as NUM_HOLIDAYS from nsa.CALENDAR_HOLIDAY_VIEW where DATE_HOLIDAY between GETDATE() and ol.DATE_PROM) ELSE 0 END),	";
				 $sql .= "		a.NAME_EMP,a.EMAIL, ol.ID_USER_ADD, oh.NAME_CUST, oh.ID_ORD, oh.ID_CUST_SOLDTO, ol.TIME_ADD, ol.DATE_ADD";
				 $sql .= "     FROM nsa.CP_ORDLIN ol ";
				 $sql .= "     Left Join nsa.cp_ordhdr oh ";
				 $sql .= "     on ol.ID_ORD=oh.ID_ORD ";
				 $sql .= "	   LEFT JOIN nsa.DCWEB_AUTH a ";
				 $sql .= " 	   on oh.ID_USER_ADD = a.ID_USER";
				 $sql .= "     WHERE ((DATEDIFF(dd, GETDATE(), ol.DATE_PROM) + 1) ";
				 $sql .= "       -(DATEDIFF(wk, GETDATE(), ol.DATE_PROM) * 2) ";
				 $sql .= "       -(CASE WHEN DATENAME(dw, GETDATE()) = 'Sunday' THEN 1 ELSE 0 END) ";
				 $sql .= "		 -(CASE WHEN (select count(DATE_HOLIDAY) as NUM_HOLIDAYS from nsa.CALENDAR_HOLIDAY_VIEW where DATE_HOLIDAY between GETDATE() and ol.DATE_PROM) > 0 THEN (select count(DATE_HOLIDAY) as NUM_HOLIDAYS from nsa.CALENDAR_HOLIDAY_VIEW where DATE_HOLIDAY between GETDATE() and ol.DATE_PROM) ELSE 0 END)	";
				 $sql .= "       -(CASE WHEN DATENAME(dw,ol.DATE_PROM) = 'Saturday' THEN 1 ELSE 0 END) ) > 15 ";
				 $sql .= "       and ";
				 $sql .= "       oh.ID_CUST_SOLDTO = '785255' ";
				 $sql .= "       and ";
				 $sql .= "       ID_ITEM in  ";
				 $sql .= "       ( ";					/////////////LIST PROVIDED BY JODY B.//////////////////////////
				 $sql .= "     	'C54VYLS-VPEA2X', ";
				 $sql .= "     	'C54VYLS-VPEA2XT', ";
				 $sql .= "     	'C54VYLS-VPEA3X', ";
				 $sql .= "     	'C54VYLS-VPEALG', ";
				 $sql .= "     	'C54VYLS-VPEAMD', ";
				 $sql .= "     	'C54VYLS-VPEASM', ";
				 $sql .= "     	'C54VYLS-VPEAXL', ";
				 $sql .= "     	'C54VYLS-VPEAXLT', ";
				 $sql .= "     	'V00HA2VSM*', ";
				 $sql .= "     	'V00HA2VMD*', ";
				 $sql .= "     	'V00HA2VLG*', ";
				 $sql .= "     	'V00HA2VXL*', ";
				 $sql .= "     	'V00HA2V2X*', ";
				 $sql .= "     	'V00HA2V3X*', ";
				 $sql .= "     	'V00HA2V4X*', ";
				 $sql .= "     	'SHRDR3SI1SMRG', ";
				 $sql .= "     	'SHRDR3SI1MDRG', ";
				 $sql .= "     	'SHRDR3SI1LGRG', ";
				 $sql .= "     	'SHRDR3SI1XLRG', ";
				 $sql .= "     	'SHRDR3SI12XRG', ";
				 $sql .= "     	'SHRDR3SI13XRG', ";
				 $sql .= "     	'SHRDR3SI14XRG', ";
				 $sql .= "     	'SHRDR3SI2SMRG', ";
				 $sql .= "     	'SHRDR3SI2MDRG', ";
				 $sql .= "     	'SHRDR3SI2LGRG', ";
				 $sql .= "     	'SHRDR3SI2XLRG', ";
				 $sql .= "     	'SHRDR3SI22XRG', ";
				 $sql .= "     	'SHRDR3SI23XRG', ";
				 $sql .= "     	'SHRDR3SI24XRG', ";
				 $sql .= "     	'C54WFLSNTSM', ";
				 $sql .= "     	'C54WFLSNTMD', ";
				 $sql .= "     	'C54WFLSNTLG', ";
				 $sql .= "     	'C54WFLSNTXL', ";
				 $sql .= "     	'C54WFLSNT2X', ";
				 $sql .= "     	'C54WFLSNT3X', ";
				 $sql .= "     	'C54WFLSVPSM', ";
				 $sql .= "     	'C54WFLSVPMD', ";
				 $sql .= "     	'C54WFLSVPLG', ";
				 $sql .= "     	'C54WFLSVPXL', ";
				 $sql .= "     	'C54WFLSVP2X', ";
				 $sql .= "     	'C54WFLSVP3X', ";
				 $sql .= "     	'C54PILSSI1SM', ";
				 $sql .= "     	'C54PILSSI1MD', ";
				 $sql .= "     	'C54PILSSI1LG', ";
				 $sql .= "     	'C54PILSSI1XL', ";
				 $sql .= "     	'C54PILSSI12X', ";
				 $sql .= "     	'C54PILSSI13X', ";
				 $sql .= "     	'C54PILSSI14X', ";
				 $sql .= "     	'C54PILSSI2SM', ";
				 $sql .= "     	'C54PILSSI2MD', ";
				 $sql .= "     	'C54PILSSI2LG', ";
				 $sql .= "     	'C54PILSSI2XL', ";
				 $sql .= "     	'C54PILSSI22X', ";
				 $sql .= "     	'C54PILSSI23X', ";
				 $sql .= "     	'C54PILSSI24X' ";
				 $sql .= "       ) ";
				 QueryDatabase($sql, $results);
				 while ($row = mssql_fetch_assoc($results)) {
				 	$DATE = $row['DATE_ADD'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN ORDER WAS ENTERED FOR SHERMCO WITH A DATE PROM GREATER THAN 15 BUSINESS DAYS. Order: " . $row['ID_ORD'];

					$body  = "<html>";
					$body .= "	<p>An order was recently entered with a date prom greater than 15 business days. <br>Please correct this so these emails will stop.</p>";
					$body .= "	<p>Order: " . $row['ID_ORD'];
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
					    	'to'      =>array('hsweeney@thinknsa.com'=>'Heidi Sweeney'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP'],'bfitch@thinknsa.com'=>'Beth Fitch','llufkin@thinknsa.com'=>'Lisa Lufkin'),
					    	//'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
			    	//09-07-18 - Turned off email alerts per Jody B because of 5 week led time on stock outs.
					//mail::send($head,$subject,$body);
					sleep(1);
				 }


				//////////////////////////////////////////////////////////////////////////////////////
				///////////////CHECK FOR RMAs WITH NULL ATTRIBUTES
				/////////////////////////////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR RMAs WITH NULL ATTRIBUTES ");
				$sql = " select rh.ID_ORD as ID_RMA, ";
				$sql .= " rh.DATE_RMA, ";
				$sql .= " rh.DATE_CLOSED,";
				$sql .= " rh.ID_CUST_SOLDTO, ";
				$sql .= " rh.NAME_CUST, ";
				$sql .= " rh.ID_PO_CUST, ";
				$sql .= " rh.ID_USER_ADD,";
				$sql .= " rl.SEQ_LINE_ORD, ";
				$sql .= " rl.TIME_ADD, ";
				$sql .= " CONVERT(varchar(8), rl.DATE_ADD, 112) as DATE_ADD3, ";
				$sql .= " ra.ID_ATTR,";
				$sql .= " wa.NAME_EMP, ";
				$sql .= " wa.EMAIL ";
				$sql .= " FROM nsa.CP_RMAHDR rh";
				$sql .= " left join nsa.CP_RMALIN rl";
				$sql .= " on rh.ID_ORD = rl.ID_ORD";
				$sql .= " left join nsa.CP_RMALIN_ATTRIBUTES ra";
				$sql .= " on rl.ID_ORD = ra.ID_ORD ";
				$sql .= " and rl.SEQ_LINE_ORD = ra.SEQ_LINE_ORD";
				$sql .= " left join nsa.DCWEB_AUTH wa ";
				$sql .= " on rh.ID_USER_ADD = wa.ID_USER ";	
				$sql .= " WHERE ra.VAL_NUM_ATTR is NULL ";
				$sql .= " and rh.STATUS_RMA = 0 ";
				$sql .= " order by ID_RMA, rl.SEQ_LINE_ORD";
				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATE = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$subject = "AN RMA LINE WAS ENTERED WITHOUT ATTRIBUTES DEFINED. RMA: " . $row['ID_RMA'] . " LINE: " . $row['SEQ_LINE_ORD'];

					$body  = "<html>";
					$body .= "	<p>An RMA line was recently entered without the attributes defined.";
					$body .= "	<br>Clicking OK in each RMA line should create the missing record.</p>";
					$body .= "	<p>RMA: " . $row['ID_RMA'];
					$body .= "		<br>Customer ID: " . $row['ID_CUST_SOLDTO'];
					$body .= "		<br>Name Cust: " . $row['NAME_CUST'];
					$body .= "		<br>Entered By: " . $row['ID_USER_ADD'] . " On: " . $DATE;
					$body .= "		<br>Line #: " . $row['SEQ_LINE_ORD'];
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('bfitch@thinknsa.com'=>'Beth Fitch'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array($row['EMAIL']=>$row['NAME_EMP']),
					    	'bcc'     =>array('sabdelsayed@thinknsa.com'=>'Sabrina Abdelsayed'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}



				//////////////////////////////////////////
				// CHECK FOR INVALID EMAIL ADDRESSES IN DDX_ROUTE
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS CHECKING FOR INVALID EMAIL ADDRESSES IN DDX_ROUTE ");
				$sql = " SELECT r.ID_CUST, ";
				$sql .= " r.TYPE_DOC, ";
				$sql .= " r.NAME_DOC, ";
				$sql .= " r.ID_USER_ADD, ";
				$sql .= " a1.EMAIL as EMAIL_ADD, ";
				$sql .= " a1.NAME_EMP as NAME_ADD, ";
				$sql .= " r.DATE_ADD, ";
				$sql .= " CONVERT(varchar(8), r.DATE_ADD, 112) as DATE_ADD3, ";
				$sql .= " r.TIME_ADD, ";
				$sql .= " r.ID_USER_CHG, ";
				$sql .= " a2.EMAIL as EMAIL_CHG, ";
				$sql .= " a2.NAME_EMP as NAME_CHG, ";
				$sql .= " r.DATE_CHG, ";
				$sql .= " CONVERT(varchar(8), r.DATE_CHG, 112) as DATE_CHG3, ";
				$sql .= " r.TIME_CHG, ";
				$sql .= " r.ADDR_EMAIL_TO_1, ";
				$sql .= " r.ADDR_EMAIL_TO_2, ";
				$sql .= " r.ADDR_EMAIL_CC_1, ";
				$sql .= " r.ADDR_EMAIL_CC_2, ";
				$sql .= " r.ADDR_EMAIL_BC_1, ";
				$sql .= " r.ADDR_EMAIL_BC_2 ";
				$sql .= " FROM nsa.DDX_ROUTE r ";
				$sql .= " LEFT JOIN nsa.DCWEB_AUTH a1 ";
				$sql .= " on r.ID_USER_ADD = a1.ID_USER ";
				$sql .= " LEFT JOIN nsa.DCWEB_AUTH a2 ";
				$sql .= " on r.ID_USER_CHG = a2.ID_USER ";
				$sql .= " WHERE ";
				$sql .= " (r.ADDR_EMAIL_TO_1 <> '' OR r.ADDR_EMAIL_TO_2 <> '' OR r.ADDR_EMAIL_CC_1 <> '' OR r.ADDR_EMAIL_CC_2 <> '' OR r.ADDR_EMAIL_BC_1 <> '' OR r.ADDR_EMAIL_BC_2 <> '') ";
				$sql .= " and ((r.ADDR_EMAIL_TO_1 <> '' AND (NOT r.ADDR_EMAIL_TO_1 LIKE '%_@__%.__%' AND PATINDEX('%[^a-z,0-9,@,.,_,\-]%', r.ADDR_EMAIL_TO_1) = 0)) ";
 				$sql .= " OR (r.ADDR_EMAIL_TO_2 <> '' AND (NOT r.ADDR_EMAIL_TO_2 LIKE '%_@__%.__%' AND PATINDEX('%[^a-z,0-9,@,.,_,\-]%', r.ADDR_EMAIL_TO_2) = 0)) ";
 				$sql .= " OR (r.ADDR_EMAIL_CC_1 <> '' AND (NOT r.ADDR_EMAIL_CC_1 LIKE '%_@__%.__%' AND PATINDEX('%[^a-z,0-9,@,.,_,\-]%', r.ADDR_EMAIL_CC_1) = 0)) ";
 				$sql .= " OR (r.ADDR_EMAIL_CC_2 <> '' AND (NOT r.ADDR_EMAIL_CC_2 LIKE '%_@__%.__%' AND PATINDEX('%[^a-z,0-9,@,.,_,\-]%', r.ADDR_EMAIL_CC_2) = 0)) ";
 				$sql .= " OR (r.ADDR_EMAIL_BC_1 <> '' AND (NOT r.ADDR_EMAIL_BC_1 LIKE '%_@__%.__%' AND PATINDEX('%[^a-z,0-9,@,.,_,\-]%', r.ADDR_EMAIL_BC_1) = 0)) ";
 				$sql .= " OR (r.ADDR_EMAIL_BC_2 <> '' AND (NOT r.ADDR_EMAIL_BC_2 LIKE '%_@__%.__%' AND PATINDEX('%[^a-z,0-9,@,.,_,\-]%', r.ADDR_EMAIL_BC_2) = 0)) ";
 				$sql .= " OR (r.ADDR_EMAIL_TO_1 like '%<%' OR r.ADDR_EMAIL_TO_1 like '%>%' OR r.ADDR_EMAIL_TO_1 like '%,%') ";
 				$sql .= " OR (r.ADDR_EMAIL_TO_2 like '%<%' OR r.ADDR_EMAIL_TO_2 like '%>%' OR r.ADDR_EMAIL_TO_2 like '%,%') ";
 				$sql .= " OR (r.ADDR_EMAIL_CC_1 like '%<%' OR r.ADDR_EMAIL_CC_1 like '%>%' OR r.ADDR_EMAIL_CC_1 like '%,%') ";
 				$sql .= " OR (r.ADDR_EMAIL_CC_2 like '%<%' OR r.ADDR_EMAIL_CC_2 like '%>%' OR r.ADDR_EMAIL_CC_2 like '%,%') ";
 				$sql .= " OR (r.ADDR_EMAIL_BC_1 like '%<%' OR r.ADDR_EMAIL_BC_1 like '%>%' OR r.ADDR_EMAIL_BC_1 like '%,%') ";
 				$sql .= " OR (r.ADDR_EMAIL_BC_2 like '%<%' OR r.ADDR_EMAIL_BC_2 like '%>%' OR r.ADDR_EMAIL_BC_2 like '%,%') ";
				$sql .= " ) ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$DATETIME_ADD = $row['DATE_ADD3'] . " " . str_pad($row['TIME_ADD'],6,"0",STR_PAD_LEFT);
					$DATETIME_CHG = $row['DATE_CHG3'] . " " . str_pad($row['TIME_CHG'],6,"0",STR_PAD_LEFT);
					$subject = "A DDX ROUTE RECORD WAS FOUND WITH INVALID EMAIL ADDRESSES.  CUSTOMER: " . $row['ID_CUST'];

					$body  = "<html>";
					$body .= "	<p>An invalid email address has been found. <br>Please correct this so these emails will stop.</p>";
					$body .= "	<p>Customer: " . $row['ID_CUST'];
					$body .= "		<br>Doc. Name: " . $row['NAME_DOC'];
					$body .= "		<br>Added By: " . $row['ID_USER_ADD'];
					$body .= "		<br>On: " . $DATETIME_ADD;
					$body .= "		<br>Changed By: " . $row['ID_USER_CHG'];
					$body .= "		<br>On: " . $DATETIME_CHG;
					$body .= "		<br>ADDR_EMAIL_TO_1: " . $row['ADDR_EMAIL_TO_1'];
					$body .= "		<br>ADDR_EMAIL_TO_2: " . $row['ADDR_EMAIL_TO_2'];

					$body .= "		<br>ADDR_EMAIL_CC_1: " . $row['ADDR_EMAIL_CC_1'];
					$body .= "		<br>ADDR_EMAIL_CC_2: " . $row['ADDR_EMAIL_CC_2'];
					$body .= "		<br>ADDR_EMAIL_BC_1: " . $row['ADDR_EMAIL_BC_1'];
					$body .= "		<br>ADDR_EMAIL_BC_2: " . $row['ADDR_EMAIL_BC_2'];
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array($row['EMAIL_ADD']=>$row['NAME_ADD'],$row['EMAIL_CHG']=>$row['NAME_CHG']),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'bcc'     =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}












				//////////////////////////////////////////
				// ZEROING ACTUAL_MINS & AVAIL_MINS IN DCAPPROVALS FOR SCHOOL TEAMS
				//////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS ZEROING ACTUAL_MINS & AVAIL_MINS IN DCAPPROVALS FOR SCHOOL TEAMS ");
				$sql  = " UPDATE nsa.DCAPPROVALS ";
				$sql .= " SET ACTUAL_MINS = 0, AVAIL_MINS = 0 ";
				$sql .= " where DATE_APP >= '2020-07-31' ";
				$sql .= " and CODE_APP = 200 ";
				$sql .= " and ltrim(BADGE_APP) in ('390','395','900','901') ";
				$sql .= " and EARNED_MINS = 0 ";
				$sql .= " and (ACTUAL_MINS <> 0 OR AVAIL_MINS <> 0) ";
				QueryDatabase($sql, $results);







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

			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runCHECK_SETUP_ERRORS ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runCHECK_SETUP_ERRORS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCHECK_SETUP_ERRORS cannot disconnect from database");
		}
	}
?>
