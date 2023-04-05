<?php

	//GLOBAL variables ########################################################

	$DEBUG = 0;

	//$username = "sqlTestUser";
	//$password = "pass123";
	$TEST_ENV = 1;

	$username = "ls1WebUser";
	$password = "Kl33nex";

	$usernameWH = "ls1WebUserWH";
	$passwordWH = "Kl33nexWH";

	$usernameBC = "ls1WebUserBC";
	$passwordBC = "Kl33nexBC";


	//$DBServer = "sql1";
	//$db = 'TCM92';
	//$dbName = 'TCM92';

	//$DBServer = 'sql2';
	//$db = 'TCM96';
	//$dbName = 'TCM96';

	$DBServer = 'wwsql';
	$db = 'TCM101';
	$dbName = 'TCM101';


	$DBServerWHSQL = 'whsql';
	$dbWHSQL = 'A1W65';
	$dbNameWHSQL = 'A1W65';

	$DBServerBC = 'bcsql1';
	$dbBCSQL1 = 'lsdata';
	$dbNameBCSQL1 = 'lsdata';

	$DBServerAC = 'wwsql';
	$dbAC = 'Access2';
	$dbNameAC = 'Access2';

	$my_username = "websql";
	$my_password = "w3bu$3r";
	$my_DBServer = "localhost";
	$my_db = 'auth';


	$odbc_DSN = 'AS400';
	$odbc_username = 'gregv';
	$odbc_password = 'Stupid123';
	$odbc_db = '';

	//CHECK TO SEE IF DAYLIGHT SAVINGS TIME IS IN EFFECT, THEN ADJUST THE UTC OFFSET AS NEEDED
	if (date('I',time())) {
		$UTC_Offset = 4;
	} else {
		$UTC_Offset = 5;
	}

	//GLOBAL functions ########################################################

	function PrintHeader($Title,$css) {
		global $DEBUG, $dbName;

		print("<html>\n");
		print("	<head>\n");
		print("		<meta http-equiv='Pragma' content='no-cache'>\n");
		print("		<meta http-equiv='Expires' content='-1'>\n");
		print("		<script language='javascript' src='JavaScript/calendar.js'></script>\n");
		if (isset($css)) {
			print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/".$css."'>\n");
		}

		print("		<title>$Title </title>\n");
		print("	</head>\n");
		print("	<body>\n");
		print("		<table align='center' width='100%' height='100%' style='background-color: white;' cellspacing='2' cellpadding='2'>\n");
		print("			<tr>\n");
		print("				<td align='center' valign='top'>\n");
		print("					<br/>\n");
		print("					<h2>$Title</h2>\n");

	}

	function PrintHeaderJQ($Title,$css,$jq="") {
		global $DEBUG, $dbName;

		print("<html>\n");
		print("	<head>\n");
		print("		<meta http-equiv='Pragma' content='no-cache'>\n");
		print("		<meta http-equiv='Expires' content='-1'>\n");
		print("		<script language='javascript' src='JavaScript/calendar.js'></script>\n");
    	print("		<script type='text/javascript' src='JavaScript/jquery-1.6.4.js'  charset='utf-8'></script>\n");
    	if ($_SERVER['HTTPS']) {
    		print("  	<script type='text/javascript' src='https://www.google.com/jsapi'></script>\n");	
    	} else {
    		print("  	<script type='text/javascript' src='http://www.google.com/jsapi'></script>\n");	
    	}
 		if (isset($jq) && $jq <> "") {
			print("		<script type='text/javascript' src='JavaScript/" . $jq ."'  charset='utf-8'></script>\n");
		}
		if (isset($css)) {
			print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/".$css."'>\n");
		}

		print("		<title>$Title </title>\n");
		print("	</head>\n");
		print("	<body>\n");
		print("	<div id='divBody'>\n");
		print("		<table align='center' width='100%' height='100%' style='background-color: white;' cellspacing='2' cellpadding='2'>\n");
		print("			<tr>\n");
		print("				<td align='center' valign='top'>\n");
		print("					<br/>\n");
		if ($Title <> 'Realtime Efficiency') {
			print("					<h3>$Title</h3>\n");
		}
	}

	function PrintHeaderJQ2($Title,$css,$jq) {
		global $DEBUG, $dbName;

		print("<html lang='en' xml:lang='en'>\n");
		print("	<head>\n");
		print("		<meta http-equiv='Pragma' content='no-cache'>\n");
		print("		<meta http-equiv='Expires' content='-1'>\n");
		print("		<meta name='google' content='notranslate'>\n");
		print("		<meta http-equiv='Content-Language' content='en'>\n");
		print("		<script language='javascript' src='JavaScript/calendar.js'></script>\n");
    	print("		<script type='text/javascript' src='JavaScript/jquery-1.6.4.js'  charset='utf-8'></script>\n");
    	if ($_SERVER['HTTPS']) {
    		print("  	<script type='text/javascript' src='https://www.google.com/jsapi'></script>\n");	
    	} else {
    		print("  	<script type='text/javascript' src='http://www.google.com/jsapi'></script>\n");	
    	}
 		if (isset($jq)) {
			print("		<script type='text/javascript' src='JavaScript/" . $jq ."'  charset='utf-8'></script>\n");
			print("		<script type='text/javascript' src='JavaScript/jquery-ui-1.8.5.custom.min.js'  charset='utf-8'></script>\n");
		}
		if (isset($css)) {
			print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/".$css."'>\n");
			print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/jquery-ui-1.8.5.custom.css'>\n");
		}

		print("		<title>$Title </title>\n");
		print("	</head>\n");
		print("	<body>\n");
		print("	<div id='divBody'>\n");
		print("		<table align='center' width='100%' height='100%' style='background-color: white;' cellspacing='2' cellpadding='2'>\n");
		print("			<tr>\n");
		print("				<td align='center' valign='top'>\n");
		print("					<br/>\n");
		if ($Title <> 'Realtime Efficiency') {
			print("					<h2>$Title</h2>\n");
		}
	}

	function PrintHeaderJQ2OL($Title,$css,$jq,$onLoad) {
		global $DEBUG, $dbName;

		print("<html>\n");
		print("	<head>\n");
		print("		<meta http-equiv='Pragma' content='no-cache'>\n");
		print("		<meta http-equiv='Expires' content='-1'>\n");
		print("		<script language='javascript' src='JavaScript/calendar.js'></script>\n");
    	print("		<script type='text/javascript' src='JavaScript/jquery-1.6.4.js'  charset='utf-8'></script>\n");
    	if ($_SERVER['HTTPS']) {
    		print("  	<script type='text/javascript' src='https://www.google.com/jsapi'></script>\n");	
    	} else {
    		print("  	<script type='text/javascript' src='http://www.google.com/jsapi'></script>\n");	
    	}
 		if (isset($jq)) {
			print("		<script type='text/javascript' src='JavaScript/" . $jq ."'  charset='utf-8'></script>\n");
			print("		<script type='text/javascript' src='JavaScript/jquery-ui-1.8.5.custom.min.js'  charset='utf-8'></script>\n");
		}
		if (isset($css)) {
			print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/".$css."'>\n");
			print("		<LINK rel='stylesheet' type='text/css' href='StyleSheets/jquery-ui-1.8.5.custom.css'>\n");
		}

		print("		<title>$Title </title>\n");
		print("	</head>\n");
		print("	<body onLoad='".$onLoad."()'>\n");
		print("	<div id='divBody'>\n");
		print("		<table align='center' width='100%' height='100%' style='background-color: white;' cellspacing='2' cellpadding='2'>\n");
		print("			<tr>\n");
		print("				<td align='center' valign='top'>\n");
		print("					<br/>\n");
		if ($Title <> 'Realtime Efficiency') {
			print("					<h2>$Title</h2>\n");
		}
	}

	function PrintFooter($ReferringPage) {
		global $DEBUG, $dbName;

		if ($ReferringPage != "") {
			print("					<p style='color: black;'><a href='$ReferringPage' tabindex=999>Back</a></p>\n");
		}
		print("				</td>\n");
		print("			</tr>\n");
		print("		</table>\n");
		print("	</div>\n");
		print("	</body>\n");
		print("</html>\n");
	}

	function ConnectToDatabaseServer($DBServer, &$db) {
		global $DEBUG, $username, $password;

		// connect to database server
		$db = mssql_connect($DBServer, $username, $password);
		if (! $db)
			return 0;
		else
			return 1;
	}

	function ConnectToDatabaseServerWH($DBServer, &$db) {
		global $DEBUG, $usernameWH, $passwordWH;

		// connect to database server
		$db = mssql_connect($DBServer, $usernameWH, $passwordWH);
		if (! $db)
			return 0;
		else
			return 1;
	}	

	function ConnectToDatabaseServerBC($DBServer, &$db) {
		global $DEBUG, $usernameBC, $passwordBC;

		// connect to database server
		$db = mssql_connect($DBServer, $usernameBC, $passwordBC);
		if (! $db)
			return 0;
		else
			return 1;
	}

	function SelectDatabase($Database) {
		global $DEBUG, $dbName;

		// select database
		if (mssql_select_db($Database))
			return 1;
		else
			return 0;
	}

	function DisconnectFromDatabaseServer($db) {
		global $DEBUG, $dbName;

		// disconnect from database server
		if (mssql_close($db))
			return 1;
		else
			return 0;
	}

	function QueryDatabase($query, &$results) {
		global $DEBUG, $dbName;

		if ($DEBUG > 0) {
			error_log("Query (". $query .")");
		}	

		$results = mssql_query($query);
		$query = str_replace("\t", ' ', $query);

		if ($DEBUG > 2) {
			if (preg_match("/^select.*$/", $query)) {
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, mssql_num_rows($results));
				error_log("QueryDatabase(". $query .") = " . mssql_num_rows($results) . " Rows");
			} else {
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, mssql_affected_rows());
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, '999999999');
				error_log("QueryDatabase(" . $query . ") = 999999999 Rows");
			}
		}

		if (!$results) {
			printf("<h1>Database Error: %d: %s</h1>\n", mssql_errno(), mssql_error());
			exit;
		}
	}
///WHSQL CONNECTION
	function ConnectToDatabaseServerWHSQL($DBServerWHSQL, &$dbWHSQL) {
		global $DEBUGWHSQL, $usernameWHSQL, $passwordWHSQL;

		// connect to database server
		$dbWHSQL = mssql_connect($DBServerWHSQL, $usernameWHSQL, $passwordWHSQL);
		if (! $dbWHSQL)
			return 0;
		else
			return 1;
	}

	function SelectDatabaseWHSQL($DatabaseWHSQL) {
		global $DEBUGWHSQL, $dbNameWHSQL;

		// select database
		if (mssql_select_db($DatabaseWHSQL))
			return 1;
		else
			return 0;
	}

	function DisconnectFromDatabaseServerWHSQL($dbWHSQL) {
		global $DEBUGWHSQL, $dbNameWHSQL;

		// disconnect from database server
		if (mssql_close($dbWHSQL))
			return 1;
		else
			return 0;
	}

	function QueryDatabaseWHSQL($queryWHSQL, &$resultsWHSQL) {
		global $DEBUGWHSQL, $dbNameWHSQL;

		if ($DEBUGWHSQL > 0) {
			error_log("Query (". $queryWHSQL .")");
		}	

		$resultsWHSQL = mssql_query($query);
		$queryWHSQL = str_replace("\t", ' ', $queryWHSQL);

		if ($DEBUGWHSQL > 2) {
			if (preg_match("/^select.*$/", $queryWHSQL)) {
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, mssql_num_rows($results));
				error_log("QueryDatabase(". $queryWHSQL .") = " . mssql_num_rows($resultsWHSQL) . " Rows");
			} else {
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, mssql_affected_rows());
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, '999999999');
				error_log("QueryDatabase(" . $queryWHSQL . ") = 999999999 Rows");
			}
		}

		if (!$resultsWHSQL) {
			printf("<h1>Database Error: %d: %s</h1>\n", mssql_errno(), mssql_error());
			exit;
		}
	}
//END WHSQL CONNECTION


///BCSQL1 CONNECTION
	function ConnectToDatabaseServerBCSQL1($DBServerBCSQL1, &$dbBCSQL1) {
		global $DEBUGBCSQL1, $usernameBCSQL1, $passwordBCSQL1;

		// connect to database server
		$dbBCSQL1 = mssql_connect($DBServerBCSQL1, $usernameBCSQL1, $passwordBCSQL1);
		if (! $dbBCSQL1)
			return 0;
		else
			return 1;
	}

	function SelectDatabaseBCSQL1($DatabaseBCSQL1) {
		global $DEBUGBCSQL1, $dbNameBCSQL1;

		// select database
		if (mssql_select_db($DatabaseBCSQL1))
			return 1;
		else
			return 0;
	}

	function DisconnectFromDatabaseServerBCSQL1($dbBCSQL1) {
		global $DEBUGBCSQL1, $dbNameBCSQL1;

		// disconnect from database server
		if (mssql_close($dbBCSQL1))
			return 1;
		else
			return 0;
	}

	function QueryDatabaseBCSQL1($queryBCSQL1, &$resultsBCSQL1) {
		global $DEBUGBCSQL1, $dbNameBCSQL1;

		if ($DEBUGBCSQL1 > 0) {
			error_log("Query (". $queryBCSQL1 .")");
		}	

		$resultsBCSQL1 = mssql_query($queryBCSQL1);
		$queryBCSQL1 = str_replace("\t", ' ', $queryBCSQL1);

		if ($DEBUGBCSQL1 > 2) {
			if (preg_match("/^select.*$/", $queryBCSQL1)) {
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, mssql_num_rows($results));
				error_log("QueryDatabase(". $queryBCSQL1 .") = " . mssql_num_rows($resultsBCSQL1) . " Rows");
			} else {
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, mssql_affected_rows());
				//printf("QueryDatabase(%s) = %d Rows<br>\n",$query, '999999999');
				error_log("QueryDatabase(" . $queryBCSQL1 . ") = 999999999 Rows");
			}
		}

		if (!$resultsBCSQL1) {
			printf("<h1>Database Error: %d: %s</h1>\n", mssql_errno(), mssql_error());
			exit;
		}
	}
//END BCSQL1 CONNECTION

	function my_ConnectToDatabaseServer($my_DBServer, &$my_db) {
		global $DEBUG, $my_username, $my_password;

		// connect to database server
		$my_db = mysql_connect($my_DBServer, $my_username, $my_password);
		if (! $my_db)
			return 0;
		else
			return 1;
	}

	function my_SelectDatabase($my_Database) {
		global $DEBUG, $dbName;

		// select database
		if (mysql_select_db($my_Database))
			return 1;
		else
			return 0;
	}

	function my_DisconnectFromDatabaseServer($my_db) {
		global $DEBUG, $dbName;

		// disconnect from database server
		if (mysql_close($my_db))
			return 1;
		else
			return 0;
	}

	function my_QueryDatabase($query, &$results) {
		global $DEBUG, $dbName;
		$results = mysql_query($query);

		if ($DEBUG > 0) {
			if (preg_match("/^select.*$/", $query)) {
				//printf("my_QueryDatabase(%s) = %d Rows<br>\n",$query, mysql_num_rows($results));
				error_log("my_QueryDatabase(%s) = %d Rows<br>\n",$query, mysql_num_rows($results));
			} else {
				//printf("my_QueryDatabase(%s) = %d Rows<br>\n",$query, mysql_affected_rows());
				error_log("my_QueryDatabase(%s) = %d Rows<br>\n",$query, mysql_affected_rows());
				//printf("my_QueryDatabase(%s) = %d Rows<br>\n",$query, '999999999');
			}
		}

		if (!$results) {
			printf("<h1>Database Error: %d: %s</h1>\n", mysql_errno(), mysql_error());
			exit;
		}
	}






	function odbc_ConnectToDatabaseServer($odbc_DSN) {
		global $DEBUG, $odbc_username, $odbc_password, $odbc_db;

		// connect to database server
		$odbc_db = odbc_connect($odbc_DSN, $odbc_username, $odbc_password);
		if (! $odbc_db)
			return 0;
		else
			return 1;
	}

	function odbc_SelectDatabase($odbc_Database) {
		//global $DEBUG, $dbName;
		global $DEBUG;

		// select database
		if (odbc_select_db($odbc_Database))
			return 1;
		else
			return 0;
	}

	function odbc_DisconnectFromDatabaseServer($odbc_db) {
		//global $DEBUG, $dbName;
		global $DEBUG;

		// disconnect from database server
		if (odbc_close($odbc_db))
			return 1;
		else
			return 0;
	}

	function odbc_QueryDatabase($query, &$results) {
		//global $DEBUG, $dbName;
		global $DEBUG, $odbc_db;

		error_log($query);
		$results = odbc_exec($odbc_db, $query);

		if ($DEBUG > 0) {
			if (preg_match("/^select.*$/", $query)) {
				//printf("my_QueryDatabase(%s) = %d Rows<br>\n",$query, mysql_num_rows($results));
				error_log("odbc_QueryDatabase(%s) = %d Rows<br>\n",$query, odbc_num_rows($results));
			} else {
				//printf("my_QueryDatabase(%s) = %d Rows<br>\n",$query, mysql_affected_rows());
				error_log("odbc_QueryDatabase(%s) = %d Rows<br>\n",$query, odbc_num_rows($results));
				//printf("my_QueryDatabase(%s) = %d Rows<br>\n",$query, '999999999');
			}
		}

		if (!$results) {
			printf("<h1>Database Error: %d: %s</h1>\n", odbc_error(), odbc_errormsg());
			exit;
		}
	}


















	function stripIllegalChars($inputStr) {
		$retStr = preg_replace('[^A-Za-z0-9. -]', '', $inputStr);

		do {
			$retStr = str_replace('--','-',$retStr);
		} while (stripos($retStr, "--") !== false);

		return $retStr;
	}

	function stripIllegalChars2($inputStr) {
		//$retStr = ereg_replace('[^A-Za-z0-9. -]', '', $inputStr);
		$retStr = preg_replace('[^A-Za-z0-9. -]', '', $inputStr);

		do {
			$retStr = str_replace('--','-',$retStr);
		} while (stripos($retStr, "--") !== false);

		return $retStr;
	}

	function stripIllegalChars3($inputStr) {
		//$retStr = ereg_replace('[^A-Za-z0-9. -]', '', $inputStr);
		$retStr = preg_replace('[^A-Za-z0-9. -#*]', '', $inputStr);
		$retStr = str_replace("'", '', $retStr);

		do {
			$retStr = str_replace('--','-',$retStr);
		} while (stripos($retStr, "--") !== false);

		return $retStr;
	}

	function stripIllegalChars4($inputStr) {
		//$retStr = ereg_replace('[^A-Za-z0-9. -]', '', $inputStr);
		$retStr = preg_replace('[^A-Za-z0-9. -#*_]', '', $inputStr);
		$retStr = str_replace("'", '', $retStr);

		do {
			$retStr = str_replace('--','-',$retStr);
		} while (stripos($retStr, "--") !== false);

		return $retStr;
	}

	function stripNonANChars($inputStr) {
		$inputStr = str_replace('-','',$inputStr);
		$retStr = ereg_replace('[^A-Za-z0-9 ]', '', $inputStr);

		return $retStr;
	}

	function stripNonANDChars($inputStr) {
		$inputStr = str_replace('--','-',$inputStr);
		$retStr = ereg_replace('[^A-Za-z0-9 -]', '', $inputStr);

		return $retStr;
	}

	function stripNonNumericChars($inputStr) {
		$inputStr = str_replace('-','',$inputStr);
		$retStr = ereg_replace('[^0-9 ]', '', $inputStr);

		return $retStr;
	}

	function stripNonNumericCharsNoSpace($inputStr) {
		$inputStr = str_replace('-','',$inputStr);
		$retStr = preg_replace('[^0-9]', '', $inputStr);

		return $retStr;
	}

	function ms_escape_string($data) {
        if ( !isset($data) or empty($data) ) return '';
        if ( is_numeric($data) ) return $data;

        $non_displayables = array(
            '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/',             // url encoded 16-31
            '/[\x00-\x08]/',            // 00-08
            '/\x0b/',                   // 11
            '/\x0c/',                   // 12
            '/[\x0e-\x1f]/'             // 14-31
        );
        foreach ( $non_displayables as $regex )
            $data = preg_replace( $regex, '', $data );
        $data = str_replace("'", "''", $data );
        return $data;
    }

	function GetStrCodeTrx($code) {
		$strTrxCode = '';
		switch ($code) {
			case '100':
				$strTrxCode = 'Attend In';
				break;
			case '101':
				$strTrxCode = 'Attend Out';
				break;
			case '102':
				$strTrxCode = 'SO Start';
				break;
			case '103':
				$strTrxCode = 'SO End';
				break;
			case '104':
				$strTrxCode = 'Misc Start';
				break;
			case '105':
				$strTrxCode = 'Misc End';
				break;
			case '106':
				$strTrxCode = 'Off Prem';
				break;
			case '107':
				$strTrxCode = 'On Prem';
				break;
			case '300':
				$strTrxCode = 'Team Start';
				break;
			case '301':
				$strTrxCode = 'Team End';
				break;
			case '302':
				$strTrxCode = 'Add Member';
				break;
			case '303':
				$strTrxCode = 'Remove Member';
				break;
			case '304':
				$strTrxCode = 'On Team';
				break;
			case '305':
				$strTrxCode = 'Off Team';
				break;
		}
		return $strTrxCode;
	}

	function GetColorCodeTrxFONT($code) {
		$retval = '';
		switch ($code) {
			case '100':	//Attend In
				$retval = 'darkgreen';
				break;
			case '101':	//Attend Out
				$retval = 'red';
				break;
		}
		return $retval;
	}

	function GetStrStatOperCode($code) {
		$strStatOperCode = '';
		switch ($code) {
			case 'C':
				$strStatOperCode = 'Complete';
				break;
			case 'A':
				$strStatOperCode = 'Active';
				break;
			case 'R':
				$strStatOperCode = 'Ready';
				break;
			case 'P':
				$strStatOperCode = 'Planned';
				break;
		}
		return $strStatOperCode;
	}

	function GetColorCodeTrx($code) {
		switch ($code) {
			case '102':
				$td_class = 'start';
				break;
			case '103':
				$td_class = 'stop';
				break;
			case '104':
				$td_class = 'start';
				break;
			case '105':
				$td_class = 'stop';
				break;
			case '106': //Off Prem
				$td_class = 'TeamStart';
				break;
			case '107': //On Prem
				$td_class = 'TeamEnd';
				break;				
			case '300':
				$td_class = 'TeamStart';
				break;
			case '301':
				$td_class = 'TeamEnd';
				break;
			case '302':
				$td_class = 'start';
				break;
			case '303':
				$td_class = 'stop';
				break;
			case '304':
				$td_class = 'onTeam';
				break;
			case '305':
				$td_class = 'offTeam';
				break;
			default:
				$td_class = 'sample';
		}
		return $td_class;
	}

	function GetColorPct($pct) {
		switch (true) {
			case ($pct >= 100):
				$retval = 'darkgreen';
				break;
			case ($pct >= 90):
				$retval = 'goldenrod';
				break;
			case ($pct < 90):
				$retval = 'red';
				break;
		}
		return $retval;
	}

	function GetColorPctAuto($pct) {
		switch (true) {
			case ($pct >= 100):
				$retval = 'darkgreenauto';
				break;
			case ($pct >= 90):
				$retval = 'goldenrodauto';
				break;
			case ($pct < 90):
				$retval = 'redauto';
				break;
		}
		return $retval;
	}

	function GetColorDaysOut($days) {
		switch (true) {
			case ($days == 'Indefinite'):
				$retval = 'darkred';
				break;
			case ($days >= 30):
				$retval = 'red';
				break;
			case ($days >= 25):
				$retval = 'goldenrod';
				break;
			case ($days < 25):
				$retval = 'darkgreen';
				break;
		}
		return $retval;
	}

	function GetColorDaysOutDash($days) {
		switch (true) {
			case ($days == 'Indefinite'):
				$retval = 'darkred';
				break;
			case ($days < 2):
				$retval = 'darkred';
				break;
			case ($days >= 2):
				$retval = 'darkgreen';
				break;
		}
		return $retval;
	}

	function GetColorDaysOutDashBlue($days) {
		$redVal = 25;
		$sql = "SELECT DESCR_2 FROM nsa.ITMMAS_BASE WHERE ID_ITEM = 'NONSTOCKITEM'";
		QueryDatabase($sql, $results);
		while ($row = mssql_fetch_assoc($results)) {
			$redVal = $row["DESCR_2"];
		}

		$redVal = 20; //4-7-2020 -- Hardcoded to 20 days per Sal

		$retval = "";
		switch (true) {
			case ($days == 'Indefinite'):
				$retval = 'darkred';
				break;
			case ($days <= 2):
				$retval = 'blue';
				break;
			case ($days > $redVal):
				$retval = 'darkred';
				break;
		}
		return $retval;
	}


	function GetColorBadgeFlag($flag) {
		switch (true) {
			case ($flag == 1):
				$retval = 'darkgreen';
				break;
			case ($flag == 0):
				$retval = 'red';
				break;
			default:
				$retval == 'goldenrod';
		}
		return $retval;
	}

	function is_odd( $int )	{
		return( $int & 1 );
	}

	function roundToNearestFraction($number, $fractionAsDecimal) {
		 $factor = 1 / $fractionAsDecimal;
		 return round( $number * $factor ) / $factor;
	}

	function formatDateDiff($start, $end=null) {
		if(!($start instanceof DateTime)) {
			$start = new DateTime($start);
		}

		if($end === null) {
			$end = new DateTime();
		}

		if(!($end instanceof DateTime)) {
			$end = new DateTime($start);
		}

		$interval = $end->diff($start);
		$doPlural = function($nb,$str){return $nb>1?$str.'s':$str;}; // adds plurals

		$format = array();
		if($interval->y !== 0) {
			$format[] = "%y ".$doPlural($interval->y, "year");
		}
		if($interval->m !== 0) {
			$format[] = "%m ".$doPlural($interval->m, "month");
		}
		if($interval->d !== 0) {
			$format[] = "%d ".$doPlural($interval->d, "day");
		}
		if($interval->h !== 0) {
			$format[] = "%h ".$doPlural($interval->h, "hour");
		}
		if($interval->i !== 0) {
			$format[] = "%i ".$doPlural($interval->i, "minute");
		}
		if($interval->s !== 0) {
			if(!count($format)) {
				return "less than a minute ago";
			} else {
				$format[] = "%s ".$doPlural($interval->s, "second");
			}
		}

		// We use the two biggest parts
		if(count($format) > 1) {
			$format = array_shift($format)." and ".array_shift($format);
		} else {
			$format = array_pop($format);
		}

		// Prepend 'since ' or whatever you like
		return $interval->format($format);
	}

	function ufDateDiff($start, $end=null) {
		if(!($start instanceof DateTime)) {
			$start = new DateTime($start);
		}

		if($end === null) {
			$end = new DateTime();
		}

		if(!($end instanceof DateTime)) {
			$end = new DateTime($start);
		}

		$interval = $end->diff($start);

		return $interval;
	}

	function roundup5($timestamp) {
		$rounded = ceil($timestamp / (5 * 60)) * (5 * 60);
		return $rounded;
	}

	function rounddown5($timestamp) {
		//$rounded = floor($timestamp / (15 * 60)) * (15 * 60);
		$rounded = $timestamp - ($timestamp % (5 * 60));
		return $rounded;
	}

	function roundup15($timestamp) {
		$rounded = ceil($timestamp / (15 * 60)) * (15 * 60);
		return $rounded;
	}

	function rounddown15($timestamp) {
		//$rounded = floor($timestamp / (15 * 60)) * (15 * 60);
		$rounded = $timestamp - ($timestamp % (15 * 60));
		return $rounded;
	}

	function roundToQuarterHour($timestring) {
		$minutes = date('i', $timestring);
		return $minutes - ($minutes % 15);
	}

	function GetEstDate($totdays) {
		$totdaysRnd = ceil($totdays);
		$todayTS = strtotime("midnight today");
		$todayDate = date("m-d-y",$todayTS);
		$todayA = getdate($todayTS);
		$todayDayNo = $todayA['wday'];
		$futTS = $todayTS;
		$i = $totdaysRnd;

		while ($i > 1) {
			$futTS = strtotime("+1 days", $futTS);
			$futA = getdate($futTS);
			$futDayNo = $futA['wday'];
			if (($futDayNo > 0) && ($futDayNo < 6)) {
				$i--;
			}
		}

		$futDate = date("m-d-y",$futTS);
		return $futDate;
	}

	function GetEstDateTS($totdays) {
		$totdaysRnd = ceil($totdays);
		$todayTS = strtotime("midnight today");
		$todayDate = date("m-d-y",$todayTS);
		$todayA = getdate($todayTS);
		$todayDayNo = $todayA['wday'];
		$futTS = $todayTS;
		$i = $totdaysRnd;

		while ($i > 1) {
			$futTS = strtotime("+1 days", $futTS);
			$futA = getdate($futTS);
			$futDayNo = $futA['wday'];
			if (($futDayNo > 0) && ($futDayNo < 6)) {
				$i--;
			}
		}

		return $futTS;
	}

	function GetUserPerms($user_name) {
		$sql  = "SELECT wa.* ";
		$sql .= " FROM nsa.DCWEB_AUTH wa ";
		$sql .= " WHERE user_name='" . $user_name . "'";
		QueryDatabase($sql, $results);
		$row = mssql_fetch_assoc($results);
		return $row;
	}

	function insertDCApproval($codeApp, $dateApp, $badgeApp, $AppByUserId, $comments, $actual=NULL, $earned=NULL) {
		global $DEBUG, $dbName;

		//COMMENTS may need to be encoded to avoid special characters terminating string.
		$sql  = "INSERT INTO nsa.DCAPPROVALS ( ";
		$sql .= "  CODE_APP, ";
		$sql .= "  DATE_APP, ";
		$sql .= "  BADGE_APP, ";
		$sql .= "  ACTUAL_MINS, ";
		$sql .= "  EARNED_MINS, ";
		$sql .= "  APP_BY_ID_USER, ";
		$sql .= "  COMMENTS, ";
		$sql .= "  ID_USER_ADD, ";
		$sql .= "  DATE_ADD ";
		$sql .= " ) VALUES ( ";
		$sql .= "  '" . $codeApp . "', ";
		$sql .= "  '" . $dateApp . "', ";
		$sql .= "  '" . $badgeApp . "', ";
		$sql .= "  '" . $actual . "', ";
		$sql .= "  '" . $earned . "', ";
		$sql .= "  '" . $AppByUserId . "', ";
		$sql .= "  '" . stripIllegalChars($comments) . "', ";
		$sql .= "  '" . $AppByUserId  . "', ";
		$sql .= "  GetDate() ";
		$sql .= " ) ";

		if ($DEBUG > 0) {
			error_log("sql: " . $sql);
		}
		QueryDatabase($sql, $results);
		return $results;
	}

	function checkApprovals($Code_App, $Date_App, $Badge_App) {
		global $DEBUG, $dbName;

		$sql  = "SELECT ";
		$sql .= " convert(varchar, DATE_ADD, 100) as DATE_ADD_SHORT, ";
		$sql .= " * ";
		$sql .= " FROM nsa.DCAPPROVALS ";
		$sql .= " WHERE CODE_APP in (" . $Code_App . ") ";
		$sql .= " and DATE_APP = '" . $Date_App ."'";
		if ($Badge_App != '%') {
			$sql .= "  and";
			$sql .= "  BADGE_APP = '" . $Badge_App . "'";
		}
		QueryDatabase($sql, $results);
		return $results;
	}

	function checkLatestApproval($Code_App, $Date_App, $Badge_App) {
		global $DEBUG, $dbName;

		$sql  = "SELECT top 1 ";
		$sql .= " convert(varchar, DATE_ADD, 100) as DATE_ADD_SHORT, ";
		$sql .= " * ";
		$sql .= " FROM nsa.DCAPPROVALS ";
		$sql .= " WHERE CODE_APP in (" . $Code_App . ") ";
		$sql .= " and DATE_APP = '" . $Date_App ."'";
		if ($Badge_App != '%') {
			$sql .= " and BADGE_APP = '" . $Badge_App . "'";
		}
		$sql .= " ORDER BY DATE_ADD desc ";
		QueryDatabase($sql, $results);
		return $results;
	}

	function createTempTable() {
		global $DEBUG, $dbName;

		$sql  = "IF OBJECT_ID('tempdb..#temp_trx') IS NOT NULL ";
		$sql .= " DROP TABLE #temp_trx ";
		QueryDatabase($sql, $results);

		$sql  = "CREATE TABLE #temp_trx ";
		$sql .= " (";
		$sql .= " ID_BADGE varchar(9), ";
		$sql .= " DATE_TRX datetime, ";
		$sql .= " TIME_TRX numeric(6,0), ";
		$sql .= " DATETIME_TRX datetime, ";
		$sql .= " DATETIME_TRX_TS numeric(10,0), ";
		$sql .= " ID_BADGE_TEAM varchar(9), ";
		$sql .= " CODE_TRX numeric(3,0), ";
		$sql .= " ID_SO varchar(9), ";
		$sql .= " SUFX_SO numeric(3,0), ";
		$sql .= " ID_OPER numeric(4,0), ";
		$sql .= " CODE_ACTV numeric(2,0), ";
		$sql .= " QTY_GOOD numeric(8,0), ";
		$sql .= " rowid int IDENTITY(1,1) ";
		$sql .= " ) ";
		QueryDatabase($sql, $results);
	}

	function populateTempTable($DateFrom, $DateTo, $ZeroHour, $Team) {
		global $DEBUG, $dbName, $UTC_Offset;

		/////////////////////
		//POPULATE TEMP TABLE WITH RECORDS FROM nsa.DCUTRX_NONZERO_PERM
		/////////////////////
		$sql  = "INSERT INTO #temp_trx";
		$sql .= " SELECT ";
		$sql .= " p.ID_BADGE,";
		$sql .= " p.DATE_CORR_TRX as DATE_TRX,";
		$sql .= " p.TIME_CORR_TRX as TIME_TRX,";
		$sql .= " cast(convert(char(10),p.DATE_CORR_TRX,121) + ' ' + STUFF(STUFF(right('000000' + rtrim(p.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as DATETIME) as DATETIME_TRX, ";
		$sql .= " cast(DATEDIFF(s,'19700101', dateadd(hh,".$UTC_Offset.",cast(convert(char(10),p.DATE_CORR_TRX,121) + ' ' + STUFF(STUFF(right('000000' + rtrim(p.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as DATETIME))) as bigint) as DATETIME_TRX_TS, ";
		$sql .= " p.ID_BADGE_TEAM,";
		$sql .= " p.CODE_TRX, ";
		$sql .= " p.ID_SO,";
		$sql .= " p.SUFX_SO,";
		$sql .= " p.ID_OPER,";
		$sql .= " p.CODE_ACTV,";
		$sql .= " p.QTY_GOOD";
		$sql .= " FROM nsa.DCUTRX_NONZERO_PERM p ";
		$sql .= " WHERE (ltrim(p.ID_BADGE_TEAM) = '" . $Team ."' or ltrim(p.ID_BADGE) = '" . $Team ."') ";
		$sql .= " and p.DATE_CORR_TRX between '" . $DateFrom . "' and '" . $DateTo . "' ";
 		$sql .= " and ((p.DATE_CORR_TRX = '" . $DateFrom . "' AND p.TIME_CORR_TRX > '".$ZeroHour."') ";
		$sql .= "  OR (p.DATE_CORR_TRX = '" . $DateTo . "' AND p.TIME_CORR_TRX < '".$ZeroHour."') ";
		$sql .= "  OR p.DATE_CORR_TRX not in ('" . $DateFrom . "','" . $DateTo . "') ";
		$sql .= " ) ";		
		$sql .= " and p.FLAG_DEL='' ";
		$sql .= " and ltrim(p.ID_BADGE) <> 'GLOVE' ";
		QueryDatabase($sql, $results);


		/////////////////////
		//POPULATE TEMP TABLE WITH RECORDS FROM nsa.DCUTRX_ZERO_PERM
		/////////////////////
		$sql  = "INSERT INTO #temp_trx";
		$sql .= " SELECT ";
		$sql .= " p.ID_BADGE,";
		$sql .= " p.DATE_CORR_TRX as DATE_TRX,";
		$sql .= " p.TIME_CORR_TRX as TIME_TRX,";
		$sql .= " cast(convert(char(10),p.DATE_CORR_TRX,121) + ' ' + STUFF(STUFF(right('000000' + rtrim(p.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as DATETIME) as DATETIME_TRX, ";
		$sql .= " cast(DATEDIFF(s,'19700101', dateadd(hh,".$UTC_Offset.",cast(convert(char(10),p.DATE_CORR_TRX,121) + ' ' + STUFF(STUFF(right('000000' + rtrim(p.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as DATETIME))) as bigint) as DATETIME_TRX_TS, ";
		$sql .= " p.ID_BADGE_TEAM,";
		$sql .= " p.CODE_TRX, ";
		$sql .= " p.ID_SO,";
		$sql .= " p.SUFX_SO,";
		$sql .= " p.ID_OPER,";
		$sql .= " p.CODE_ACTV,";
		$sql .= " '0' as QTY_GOOD";
		$sql .= " FROM nsa.DCUTRX_ZERO_PERM p ";
		$sql .= " WHERE ";
		$sql .= " (ltrim(p.ID_BADGE_TEAM) = '" . $Team ."' OR ltrim(p.ID_BADGE) = '" . $Team . "') ";
		$sql .= " and p.DATE_CORR_TRX between '" . $DateFrom . "' AND '" . $DateTo . "' ";
 		$sql .= " and ((p.DATE_CORR_TRX = '" . $DateFrom . "' AND p.TIME_CORR_TRX > '".$ZeroHour."') ";
		$sql .= "  OR (p.DATE_CORR_TRX = '" . $DateTo . "' AND p.TIME_CORR_TRX < '".$ZeroHour."') ";
		$sql .= "  OR p.DATE_CORR_TRX not in ('" . $DateFrom . "','" . $DateTo . "') ";
		$sql .= " ) ";		
		$sql .= " and p.FLAG_DEL='' ";
		$sql .= " and ltrim(p.ID_BADGE) <> 'GLOVE' ";
		QueryDatabase($sql, $results);

		/////////////////////
		//QUERY TEMP TABLE TOTAL UNIQUE TEAM MEMBERS FOR DAY
		/////////////////////
		$sql  = "SELECT distinct tx.ID_BADGE ";
		$sql .= " FROM #temp_trx tx ";
		$sql .= " WHERE tx.CODE_TRX in (304) ";
		QueryDatabase($sql, $results);

		while ($row = mssql_fetch_assoc($results)) {
			$a_team_members[] = ltrim($row['ID_BADGE']);
		}

		if (!isset($a_team_members)) {
			$a_team_members[] = '0';
		}

		/////////////////////
		//POPULATE TEMP TABLE WITH INDIRECT HOUR RECORDS FROM nsa.DCUTRX_ZERO_PERM
		/////////////////////
		$sql  = "INSERT INTO #temp_trx";
		$sql .= " SELECT ";
		$sql .= " p.ID_BADGE,";
		$sql .= " p.DATE_CORR_TRX as DATE_TRX,";
		$sql .= " p.TIME_CORR_TRX as TIME_TRX,";
		$sql .= " cast(convert(char(10),p.DATE_CORR_TRX,121) + ' ' + STUFF(STUFF(right('000000' + rtrim(p.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as DATETIME) as DATETIME_TRX, ";
		$sql .= " cast(DATEDIFF(s,'19700101', dateadd(hh,".$UTC_Offset.",cast(convert(char(10),p.DATE_CORR_TRX,121) + ' ' + STUFF(STUFF(right('000000' + rtrim(p.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as DATETIME))) as bigint) as DATETIME_TRX_TS, ";
		$sql .= " p.ID_BADGE_TEAM,";
		$sql .= " p.CODE_TRX, ";
		$sql .= " p.ID_SO,";
		$sql .= " p.SUFX_SO,";
		$sql .= " p.ID_OPER,";
		$sql .= " p.CODE_ACTV,";
		$sql .= " '0' as QTY_GOOD";
		$sql .= " FROM nsa.DCUTRX_ZERO_PERM p ";
		$sql .= " WHERE p.ID_BADGE_TEAM = '' ";
		$sql .= " and ltrim(p.ID_BADGE) in ('" . implode("','", $a_team_members) ."') ";
		$sql .= " and p.CODE_TRX not in (300,301,304,305) ";
		$sql .= " and p.DATE_CORR_TRX between '" . $DateFrom . "' and '" . $DateTo . "' ";
 		$sql .= " and ((p.DATE_CORR_TRX = '" . $DateFrom . "' AND p.TIME_CORR_TRX > '".$ZeroHour."') ";
		$sql .= "  OR (p.DATE_CORR_TRX = '" . $DateTo . "' AND p.TIME_CORR_TRX < '".$ZeroHour."') ";
		$sql .= "  OR p.DATE_CORR_TRX not in ('" . $DateFrom . "','" . $DateTo . "') ";
		$sql .= " ) ";
		$sql .= " and p.FLAG_DEL='' ";
		$sql .= " and ltrim(p.ID_BADGE) <> 'GLOVE' ";
		QueryDatabase($sql, $results);

		return($a_team_members);
	}

	function GetEmailSubscribers($code) {
		global $DEBUG, $dbName;

		$sql  = "SELECT distinct wa.EMAIL ";
		$sql .= " FROM nsa.DCWEB_AUTH wa, ";
		$sql .= " nsa.DCEMAIL_SUBSCRIPTION e ";
		$sql .= " WHERE wa.ID_USER = e.ID_USER ";
		$sql .= " and e.CODE_APP = '" . $code ."' ";
		QueryDatabase($sql, $results);

		while ($row = mssql_fetch_assoc($results)) {
			$a_emails[] = $row['EMAIL'];
		}
		if (!isset($a_emails)) {
			$a_emails[] = '0';
		}
		return($a_emails);

	}

	function GetNumTeams() {
		global $DEBUG, $dbName;

		$sql  = "SELECT ";
		$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
		$sql .= " ltrim(ID_BADGE) as ID_BADGE, ";
		$sql .= " NAME_EMP, ";
		$sql .= " ID_BADGE_SUPRVSR ";
		$sql .= " FROM nsa.DCEMMS_EMP ";
		$sql .= " WHERE TYPE_BADGE = 'X' ";
		$sql .= " and CODE_ACTV = '0' ";
		$sql .= " and ID_BADGE_SUPRVSR <> '' ";
		$sql .= " and GetDate() < DATE_USER ";
		$sql .= " ORDER BY BADGE_NAME asc";
		QueryDatabase($sql, $results);
		$num_teams = mssql_num_rows($results);

		return $num_teams;
	}

	function GetNumTeamsDate($DateFrom) {
		global $DEBUG, $dbName;

		$sql  = "SELECT ";
		$sql .= " ltrim(ID_BADGE) + ' - ' + NAME_EMP as BADGE_NAME, ";
		$sql .= " ltrim(ID_BADGE) as ID_BADGE, ";
		$sql .= " NAME_EMP, ";
		$sql .= " ID_BADGE_SUPRVSR ";
		$sql .= " FROM nsa.DCEMMS_EMP ";
		$sql .= " WHERE TYPE_BADGE = 'X' ";
		$sql .= " and CODE_ACTV = '0' ";
		$sql .= " and ID_BADGE_SUPRVSR <> '' ";
		$sql .= " and '".$DateFrom."' < DATE_USER ";
		$sql .= " ORDER BY BADGE_NAME asc";
		error_log($sql);

		QueryDatabase($sql, $results);
		$num_teams = mssql_num_rows($results);

		return $num_teams;
	}


	function GetEffScore($DateFrom, $DateTo, $ZeroHour, $Team, $RealTime = "") {
		global $DEBUG, $dbName, $UTC_Offset;

		$today = date('m-d-Y');
		$nowts = time();

		$DateFromTS = strtotime($DateFrom." ".$ZeroHour);
		$DateToTS = strtotime($DateTo." ".$ZeroHour);
		$seconds_diff = $DateToTS - $DateFromTS;
		$daysDiff = ($seconds_diff/3600)/24;

		//NOT EVERY DAY HAS 86400 SECONDS.. THIS ACCOUNTS FOR DAYLIGHT SAVINGS
		if (($seconds_diff == 86400) || ($seconds_diff == 90000) || ($seconds_diff == 82800)){
			$daysDiff = 1;
		}		

		createTempTable();
		$a_team_members = populateTempTable($DateFrom, $DateTo, $ZeroHour, $Team);

		/////////////////////
		//QUERY TEMP TABLE FOR INDIVIDUAL INDIRECT HOURS
		/////////////////////
		$tot_indir_sec = 0;
		$tot_team_actual_sec = 0;
		$tot_team_actual_sec_UNADJUSTED = 0;
		foreach ($a_team_members as $member) {
			$tot_indiv_indir_sec = 0;
			$tot_indiv_day_sec = 0;
			$name = '';
			$pct = 100;

			//$LoopTS = $DateFromTS;

			//while ($LoopTS <= $DateToTS) {
				//$LoopDT = date('Y-m-d', $LoopTS);
				//$onTimeA = array();
				//$offTimeA = array();
				$onTSA = array();
				$offTSA = array();
/*
				$sql  = "SELECT ";
				$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
				$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql .= " e.NAME_EMP, ";
				$sql .= " e.CODE_USER_1_DC, ";
				$sql .= " tx.* ";
				$sql .= " FROM #temp_trx tx, ";
				$sql .= " nsa.DCEMMS_EMP e ";
				$sql .= " WHERE ";
				$sql .= " ltrim(tx.ID_BADGE) = '". $member . "'";
				$sql .= " and tx.ID_BADGE = e.ID_BADGE ";
				$sql .= " and e.CODE_ACTV = 0 ";
				$sql .= " and tx.CODE_TRX in (304,305) ";
				//$sql .= ""  and tx.DATE_TRX = '" . $LoopDT . "' ";
				$sql .= " ORDER BY ";
				$sql .= " DATE_TRX asc, ";
				$sql .= " ID_BADGE asc, ";
				$sql .= " time_trx asc ";
				QueryDatabase($sql, $results);
*/

				$sql  = "SELECT ";
				$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
				$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql .= " e.NAME_EMP, ";
				$sql .= " e.CODE_USER_1_DC, ";
				$sql .= " tx.* ";
				$sql .= " FROM #temp_trx tx ";
				$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
				$sql .= " on tx.ID_BADGE = e.ID_BADGE ";
				$sql .= " and e.CODE_ACTV = 0 ";
				$sql .= " WHERE ltrim(tx.ID_BADGE) = '". $member . "'";
				$sql .= " and tx.CODE_TRX in (304,305) ";
				//$sql .= ""  and tx.DATE_TRX = '" . $LoopDT . "' ";
				$sql .= " ORDER BY ";
				$sql .= " DATE_TRX asc, ";
				$sql .= " ID_BADGE asc, ";
				$sql .= " time_trx asc ";
				QueryDatabase($sql, $results);


				while ($row = mssql_fetch_assoc($results)) {
					if ($row['CODE_TRX'] == '304') {
						//$onTimeA[] = $row['TIME_TRX'];
						$onTSA[] = $row['DATETIME_TRX_TS'];
					}
					if ($row['CODE_TRX'] == '305') {
						//$offTimeA[] = $row['TIME_TRX'];
						$offTSA[] = $row['DATETIME_TRX_TS'];
					}
				}

				//sort($onTimeA);
				//sort($offTimeA);
				sort($onTSA);
				sort($offTSA);

				//////ARRAY OF ON TEAM TRX
				//for ($i=0; $i<sizeof($onTimeA); $i++) {
				for ($i=0; $i<sizeof($onTSA); $i++) {
					if ($DEBUG) {
						error_log("onTSA: " . $onTSA[$i]);
						error_log("offTSA: " . $offTSA[$i]);
					}

					//if (!isset($offTimeA[$i])) {
					//	$offTimeA[$i] = '235959';
					//	if ($DEBUG) {
					//		error_log("offTimeA OVERRIDE: " . $offTimeA[$i]);
					//	}
					//}
					if (!isset($offTSA[$i])) {
						if ($DEBUG) {
							error_log("OFFTSA not Set");
						}
						$offTSA[$i] = $DateToTS;
					}

/*
					$sql  = "SELECT ";
					$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
					$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
					$sql .= " e.NAME_EMP, ";
					$sql .= " e.CODE_USER_1_DC, ";
					$sql .= " tx.* ";
					$sql .= " FROM #temp_trx tx, ";
					$sql .= " nsa.DCEMMS_EMP e ";
					$sql .= " WHERE ";
					$sql .= " ltrim(tx.ID_BADGE) = '". $member . "'";
					$sql .= " and tx.ID_BADGE = e.ID_BADGE ";
					$sql .= " and e.CODE_ACTV = 0 ";
					$sql .= " and tx.CODE_TRX in (104,105,304,305) ";
					//$sql .= "  and tx.DATE_TRX = '" . $LoopDT . "' ";
					$sql .= " and tx.DATETIME_TRX_TS between '" . $onTSA[$i] . "' and '" . $offTSA[$i] . "' ";
					$sql .= " ORDER BY ";
					$sql .= " DATE_TRX asc, ";
					$sql .= " ID_BADGE asc, ";
					$sql .= " time_trx asc ";
					QueryDatabase($sql, $results);
*/

					$sql  = "SELECT ";
					$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
					$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
					$sql .= " e.NAME_EMP, ";
					$sql .= " e.CODE_USER_1_DC, ";
					$sql .= " tx.* ";
					$sql .= " FROM #temp_trx tx ";
					$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
					$sql .= " on tx.ID_BADGE = e.ID_BADGE ";
					$sql .= " and e.CODE_ACTV = 0 ";
					$sql .= " WHERE ltrim(tx.ID_BADGE) = '". $member . "'";
					$sql .= " and tx.CODE_TRX in (104,105,304,305) ";
					//$sql .= "  and tx.DATE_TRX = '" . $LoopDT . "' ";
					$sql .= " and tx.DATETIME_TRX_TS between '" . $onTSA[$i] . "' and '" . $offTSA[$i] . "' ";
					$sql .= " ORDER BY ";
					$sql .= " DATE_TRX asc, ";
					$sql .= " ID_BADGE asc, ";
					$sql .= " time_trx asc ";
					QueryDatabase($sql, $results);



					while ($row = mssql_fetch_assoc($results)) {
						$td_class = GetColorCodeTrx($row['CODE_TRX']);
						$trxType = GetStrCodeTrx($row['CODE_TRX']);

						if ((trim($row['CODE_USER_1_DC']) <> '') && (trim($row['CODE_USER_1_DC']) <> '100')) { //<
							$pct = trim($row['CODE_USER_1_DC']);
						}

						$prev = '';
						$diff_sec = '';
						$prevdate = '';
						$name = $row['NAME_EMP'];
						//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
						//$currts = strtotime($curr);
						$currts = $row['DATETIME_TRX_TS'];

						if ($row['CODE_TRX'] == '105') {
							$sql2  = "SELECT top 1 ";
							$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql2 .= " tx.* ";
							$sql2 .= " FROM #temp_trx tx ";
							$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
							$sql2 .= " and ID_BADGE = '" . $row['ID_BADGE'] ."' ";
							//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
							//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
							$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
							$sql2 .= " ORDER BY ";
							$sql2 .= " DATE_TRX desc, ";
							$sql2 .= " time_trx desc ";
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
								//$prevts = strtotime($prev);
								$prevts = $row2['DATETIME_TRX_TS'];
								$diff_sec = $currts - $prevts;
								if ($nowts >= $currts) {
									$tot_indiv_indir_sec += $diff_sec;
								}
							}
						}
						//<
						if ($row['CODE_TRX'] == '305') {
							$sql2  = "SELECT top 1 ";
							$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql2 .= " tx.* ";
							$sql2 .= " FROM #temp_trx tx ";
							$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
							$sql2 .= " and ID_BADGE = '" . $row['ID_BADGE'] ."' ";
							//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
							//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
							$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
							$sql2 .= " ORDER BY ";
							$sql2 .= " DATE_TRX desc, ";
							$sql2 .= " time_trx desc ";
							QueryDatabase($sql2, $results2);

							while ($row2 = mssql_fetch_assoc($results2)) {
								//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
								//$prevts = strtotime($prev);
								$prevts = $row2['DATETIME_TRX_TS'];
								$diff_sec = rounddown15($currts) - roundup15($prevts);
								if ($nowts >= $currts) {
									$tot_indiv_day_sec += $diff_sec;
								}
							}
						} else {
							/////////////////////
							// If there is no matching "Off Team" record for the Badge ID for that day, calculate the difference so far in the day.
							$sql3  = "SELECT ";
							$sql3 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
							$sql3 .= " tx.* ";
							$sql3 .= " FROM #temp_trx tx ";
							$sql3 .= " WHERE tx.CODE_TRX in (301,305) ";
							$sql3 .= " and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
							//$sql3 .= " and DATE_TRX = '" . $row['DATE_TRX'] . "' ";
							$sql3 .= " ORDER BY ";
							$sql3 .= " DATE_TRX desc ";
							QueryDatabase($sql3, $results3);
							if (mssql_num_rows($results3) == 0) {
								$currts_rnd = roundup15($currts);
								$nowts_rnd = rounddown15($nowts);
								$diff_sec2 =  $nowts - $currts;
								if ($row['CODE_TRX'] == '304') {
									$tot_indiv_day_sec += $diff_sec2;
								}
							}
						}
					}
				}

			//	$LoopTS = strtotime("+1 days" , $LoopTS);
			//}


			$tot_indiv_actual_sec = $tot_indiv_day_sec - $tot_indiv_indir_sec;

			/////////////////////
			// If individual is not to be counted at 100% of their time then only include a percentage of their Actual Minutes
			/////////////////////
			$txt = 'Individual Actual Minutes';
			$cls = 'sample';
			if ($pct <> '100') {
				$cls = 'stop';
				$txt = "Individual Actual Minutes (adjusted to " . $pct . "% of " . ($tot_indiv_actual_sec / 60) . ")";
				$tot_indiv_actual_sec = $tot_indiv_actual_sec * (intval($pct) / 100);
			}

			$tot_indir_sec += $tot_indiv_indir_sec;
			$tot_team_actual_sec += $tot_indiv_actual_sec;
		}

		/////////////////////
		//QUERY TEMP TABLE FOR TEAM CHANGES
		/////////////////////<
/*
		$sql  = "SELECT ";
		$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
		$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
		$sql .= " e.CODE_ACTV, ";
		$sql .= " e.NAME_EMP, ";
		$sql .= " e.CODE_USER_1_DC, ";
		$sql .= " tx.* ";
		$sql .= " FROM #temp_trx tx, ";
		$sql .= " nsa.DCEMMS_EMP e ";
		$sql .= " WHERE e.ID_BADGE = tx.ID_BADGE ";
		$sql .= " and e.CODE_ACTV = 0 ";
		$sql .= " and tx.CODE_TRX in (300,301,304,305) ";
		$sql .= " ORDER BY ";
		$sql .= " DATE_TRX asc, ";
		$sql .= " ID_BADGE asc, ";
		$sql .= " rowid asc ";
		QueryDatabase($sql, $results);
*/
		$sql  = "SELECT ";
		$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
		$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
		$sql .= " e.CODE_ACTV, ";
		$sql .= " e.NAME_EMP, ";
		$sql .= " e.CODE_USER_1_DC, ";
		$sql .= " tx.* ";
		$sql .= " FROM #temp_trx tx ";
		$sql .= " LEFT JOIN nsa.DCEMMS_EMP e ";
		$sql .= " ON e.ID_BADGE = tx.ID_BADGE ";
		$sql .= " and e.CODE_ACTV = 0 ";
		$sql .= " WHERE tx.CODE_TRX in (300,301,304,305) ";
		$sql .= " ORDER BY ";
		$sql .= " DATE_TRX asc, ";
		$sql .= " ID_BADGE asc, ";
		$sql .= " rowid asc ";
		QueryDatabase($sql, $results);


		$tot_day_sec = 0;

		while ($row = mssql_fetch_assoc($results)) {
			$prev = '';
			$diff_sec = '';
			//$td_class = GetColorCodeTrx($row['CODE_TRX']);
			$trxType = GetStrCodeTrx($row['CODE_TRX']);
			//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
			//$currts = strtotime($curr);
			$currts = $row['DATETIME_TRX_TS'];

			if ($row['CODE_TRX'] == '301' || $row['CODE_TRX'] == '305')  {
				$sql2  = "SELECT top 1 ";
				$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql2 .= " tx.* ";
				$sql2 .= " FROM #temp_trx tx ";
				$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
				$sql2 .= " and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
				//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] . "' ";
				//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] . "' ";
				$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] . "' ";
				$sql2 .= " ORDER BY ";
				$sql2 .= " DATE_TRX desc, ";
				$sql2 .= " time_trx desc ";
				QueryDatabase($sql2, $results2);

				while ($row2 = mssql_fetch_assoc($results2)) {
					//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$prevts = strtotime($prev);
					$prevts = $row2['DATETIME_TRX_TS'];
					$currts_rnd = rounddown15($currts);
					$prevts_rnd = roundup15($prevts);
					$diff_sec = $currts_rnd - $prevts_rnd;
					if ($row['CODE_TRX'] == '305') {
						$tot_day_sec += $diff_sec;
					}
				}
			} else {
				/////////////////////
				// If there is no matching "Off Team" record for the Badge ID for that day, calculate the difference so far in the day.
				/////////////////////
				$sql3  =  "SELECT ";
				$sql3 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql3 .= " tx.* ";
				$sql3 .= " FROM #temp_trx tx ";
				$sql3 .= " WHERE tx.CODE_TRX in (301,305) ";
				$sql3 .= " and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
				//$sql3 .= " and DATE_TRX = '" . $row['DATE_TRX'] . "' ";
				$sql3 .= " ORDER BY ";
				$sql3 .= " DATE_TRX desc ";
				QueryDatabase($sql3, $results3);
				if (mssql_num_rows($results3) == 0) {
					$currts_rnd = roundup15($currts);
					$nowts_rnd = rounddown15($nowts);
					$diff_sec =  $nowts - $currts;
					if ($row['CODE_TRX'] == '304') {
						$tot_day_sec += $diff_sec;
					}
				}
			}
		}

		/////////////////////
		//QUERY TEMP TABLE FOR SHOP ORDERS - INDIRECT
		/////////////////////
/*
		$sql  = "SELECT ";
		$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
		$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
		$sql .= " h.ID_ITEM_PAR, ";
		$sql .= " o.HR_MACH_SR as HR_MACH_SF, ";
		$sql .= " o.DESCR_OPER_1, ";
		$sql .= " tx.* ";
		$sql .= " FROM #temp_trx tx, ";
		$sql .= " nsa.SHPORD_HDR h, ";
		$sql .= " nsa.SHPORD_OPER so,";
		$sql .= " nsa.ROUTMS_OPER o, ";
		$sql .= " nsa.ITMMAS_BASE b, ";
		$sql .= " nsa.ITMMAS_LOC l ";
		$sql .= " WHERE tx.CODE_TRX in (102,103) ";
		$sql .= " and ltrim(tx.ID_SO) not like 'S%' ";
		$sql .= " and tx.ID_SO = h.ID_SO ";
		$sql .= " and h.id_item_par=o.id_item ";
		$sql .= " and so.id_oper=o.id_oper ";
		$sql .= " and so.ID_SO = h.ID_SO ";
		$sql .= " and tx.SUFX_SO = h.SUFX_SO ";
		$sql .= " and tx.SUFX_SO = so.SUFX_SO ";
		$sql .= " and so.ID_OPER = tx.ID_OPER ";
		$sql .= " and so.FLAG_DIR_INDIR = 'I' ";
		$sql .= " and b.ID_ITEM = h.ID_ITEM_PAR ";
		$sql .= " and l.ID_ITEM = b.ID_ITEM ";
		$sql .= " and l.ID_LOC = '10' ";
		$sql .= " and l.ID_RTE = o.ID_RTE ";
		$sql .= " and ((ltrim(tx.ID_SO) = 'PROD' AND tx.ID_OPER <> '1000') OR ltrim(tx.ID_SO) <> 'PROD')";
		$sql .= " ORDER BY ";
		$sql .= " DATE_TRX asc, ";
		$sql .= " time_trx asc, ";
		$sql .= " ID_ITEM_PAR asc, ";
		$sql .= " ID_SO asc, ";
		$sql .= " ID_OPER asc ";
		QueryDatabase($sql, $results);
*/

		$sql  = "SELECT ";
		$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
		$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
		$sql .= " h.ID_ITEM_PAR, ";

		//Below 2 lines pull standard times from Shop Order Operation
		$sql .= " so.HR_MACH_SF as HR_MACH_SF, ";
		$sql .= " so.DESCR_OPER_1, ";
		//Below 2 lines pull standard times from Routing Operation
		//$sql .= " o.HR_MACH_SR as HR_MACH_SF, ";
		//$sql .= " o.DESCR_OPER_1, ";
		
		$sql .= " tx.* ";
		$sql .= " FROM #temp_trx tx ";
		$sql .= " LEFT JOIN nsa.SHPORD_OPER so";
		$sql .= " on tx.ID_SO = so.ID_SO ";
		$sql .= " and tx.SUFX_SO = so.SUFX_SO ";
		$sql .= " and tx.ID_OPER = so.ID_OPER ";
		$sql .= " LEFT JOIN nsa.SHPORD_HDR h";
		$sql .= " on tx.ID_SO = h.ID_SO ";
		$sql .= " and tx.SUFX_SO = h.SUFX_SO ";
		$sql .= " LEFT JOIN nsa.ITMMAS_BASE b ";
		$sql .= " on h.ID_ITEM_PAR = b.ID_ITEM ";
		$sql .= " LEFT JOIN nsa.ITMMAS_LOC l ";
		$sql .= " on b.ID_ITEM = l.ID_ITEM ";
		$sql .= " and l.ID_LOC = '10' ";
		$sql .= " LEFT JOIN nsa.ROUTMS_OPER o ";
		$sql .= " on h.id_item_par=o.id_item ";
		$sql .= " and so.id_oper=o.id_oper ";
		$sql .= " and l.ID_RTE = o.ID_RTE ";
		$sql .= " WHERE tx.CODE_TRX in (102,103) ";
		$sql .= " and ltrim(tx.ID_SO) not like 'S%' ";
		$sql .= " and so.FLAG_DIR_INDIR = 'I' ";
		$sql .= " and ((ltrim(tx.ID_SO) = 'PROD' AND tx.ID_OPER <> '1000') OR ltrim(tx.ID_SO) <> 'PROD')";
		$sql .= " ORDER BY ";
		$sql .= " DATE_TRX asc, ";
		$sql .= " time_trx asc, ";
		$sql .= " ID_ITEM_PAR asc, ";
		$sql .= " ID_SO asc, ";
		$sql .= " ID_OPER asc ";
		QueryDatabase($sql, $results);






		$tot_so_indir_sec = 0;
		while ($row = mssql_fetch_assoc($results)) {
			$prev = '';
			$diff_sec = 0;
			$qty_ord = '';
			$qty_rem = '';
			//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
			//$currts = strtotime($curr);
			$currts = $row['DATETIME_TRX_TS'];

			if ($row['CODE_TRX'] == '103')  {
				$sql2  = "SELECT top 1 ";
				$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql2 .= " tx.* ";
				$sql2 .= " FROM #temp_trx tx ";
				$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
				$sql2 .= " and ID_SO = '" . $row['ID_SO'] ."' ";
				$sql2 .= " and CODE_ACTV = '" . $row['CODE_ACTV'] ."' ";
				//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
				//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
				$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
				$sql2 .= " ORDER BY ";
				$sql2 .= " DATE_TRX desc, ";
				$sql2 .= " time_trx desc ";
				QueryDatabase($sql2, $results2);

				while ($row2 = mssql_fetch_assoc($results2)) {
					//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$prevts = strtotime($prev);
					$prevts = $row2['DATETIME_TRX_TS'];
					$diff_sec = $currts - $prevts;
				}

				//////////
				//HARDCODED FOR FIBERGLASS CLEANUP
				//////////
				if (($row['ID_SO'] == 'PROD') && ($row['ID_OPER'] == '6000')) {
					$diff_sec = 300 * $row['QTY_GOOD'];
				}

				//////////
				//Machine Down - Multiply by number of members "Claimed"
				//////////
				if (($row['ID_SO'] == 'PROD') && (($row['ID_OPER'] == '1500') || ($row['ID_OPER'] == '2000') || ($row['ID_OPER'] == '2500') || ($row['ID_OPER'] == '3500') || ($row['ID_OPER'] == '4000'))) {
					$qtyGood = $row['QTY_GOOD'];
					if ($qtyGood == 0) {
						$qtyGood = 1;
					}
					$diff_sec = $diff_sec * $qtyGood;
				}
				$tot_so_indir_sec += $diff_sec;
			}

			//$td_class = GetColorCodeTrx($row['CODE_TRX']);
			$trxType = GetStrCodeTrx($row['CODE_TRX']);
		}

		/////////////////////
		//QUERY TEMP TABLE FOR SHOP ORDERS - DIRECT
		/////////////////////

		$sql  = "SELECT ";
		$sql .= " h.ID_ITEM_PAR, ";
		
		//THE 2 LINES BELOW USE STANDARD TIMES FROM SHOP ORDER OPERATIONS
		$sql .= " so.HR_MACH_SF as HR_MACH_SF, ";
		$sql .= " so.DESCR_OPER_1 as DESCR_OPER_1, ";
		//THE 6 LINES BELOW WILL USE THE ROUTING STANDARD TIMES IF THEY EXIST
		//$sql .= " so.HR_MACH_SF as soHR_MACH_SF, ";
		//$sql .= " so.DESCR_OPER_1 as soDESCR_OPER_1, ";
		//$sql .= " o.HR_MACH_SR as oHR_MACH_SF, ";
		//$sql .= " o.DESCR_OPER_1 as oDESCR_OPER_1, ";
		//$sql .= " case when o.HR_MACH_SR is null THEN so.HR_MACH_SF else o.HR_MACH_SR end as HR_MACH_SF, ";
		//$sql .= " case when o.DESCR_OPER_1 is null THEN so.DESCR_OPER_1 else o.DESCR_OPER_1 end as DESCR_OPER_1, ";

		$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
		$sql .= " tx.*  ";
		$sql .= " FROM #temp_trx tx  ";
		$sql .= " left join nsa.SHPORD_HDR h ";
		$sql .= "  on tx.ID_SO = h.ID_SO ";
		$sql .= "  and tx.SUFX_SO = h.SUFX_SO ";
		$sql .= " left join nsa.SHPORD_OPER so ";
		$sql .= "  on h.ID_SO = so.ID_SO ";
		$sql .= "  and tx.SUFX_SO = so.SUFX_SO ";
		$sql .= "  and tx.ID_OPER = so.ID_OPER ";
		$sql .= "  and so.FLAG_DIR_INDIR = 'D' ";
		$sql .= " left join nsa.ITMMAS_BASE b ";
		$sql .= "  on b.ID_ITEM = h.ID_ITEM_PAR ";
		$sql .= " left join nsa.ITMMAS_LOC l ";
		$sql .= "  on l.ID_ITEM = b.ID_ITEM    ";
		$sql .= "  and l.ID_LOC = '10'    ";
		$sql .= " left join nsa.ROUTMS_OPER o  ";
		$sql .= "  on h.id_item_par=o.id_item    ";
		$sql .= "  and so.id_oper=o.id_oper ";
		$sql .= "  and l.ID_RTE = o.ID_RTE   ";
		$sql .= " where tx.CODE_TRX in (102,103)  ";
		$sql .= "  and so.FLAG_DIR_INDIR = 'D' ";
		$sql .= "  and ltrim(tx.ID_SO) not like 'S%' ";
		$sql .= " order by DATE_TRX asc,    ";
		$sql .= "  time_trx asc,    ";
		$sql .= "  ID_ITEM_PAR asc,    ";
		$sql .= "  ID_SO asc,   ";
		$sql .= "  ID_OPER asc ";
		QueryDatabase($sql, $results);

		$tot_qty = 0;
		$tot_std_hrs = 0;
		$min_earned = 0;
		$tot_min_earned = 0;
		while ($row = mssql_fetch_assoc($results)) {
			$prev = '';
			$diff_sec = 0;
			//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
			//$currts = strtotime($curr);
			$currts = $row['DATETIME_TRX_TS'];

			if ($row['CODE_TRX'] == '103')  {
				$sql2  = "SELECT top 1 ";
				$sql2 .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql2 .= " tx.* ";
				$sql2 .= " FROM #temp_trx tx ";
				$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
				$sql2 .= " and ID_SO = '" . $row['ID_SO'] ."' ";
				//$sql2 .= " and DATE_TRX <= '" . $row['DATE_TRX'] ."' ";
				//$sql2 .= " and TIME_TRX <= '" . $row['TIME_TRX'] ."' ";
				$sql2 .= " and TIME_TRX <= '" . $row['DATETIME_TRX_TS'] ."' ";
				$sql2 .= " ORDER BY ";
				$sql2 .= " DATE_TRX desc, ";
				$sql2 .= " time_trx desc ";
				QueryDatabase($sql2, $results2);
				while ($row2 = mssql_fetch_assoc($results2)) {
					//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$prevts = strtotime($prev);
					$prevts = $row['DATETIME_TRX_TS'];
					$diff_sec = $currts - $prevts;
				}

				$tot_qty += $row['QTY_GOOD'];
				$tot_std_hrs += $row['HR_MACH_SF'];
				$min_earned = ($row['QTY_GOOD'] * $row['HR_MACH_SF'] * 60);
				$tot_min_earned += $min_earned;

			} else {
				$row['HR_MACH_SF'] = 0;
				$min_earned = '';
			}

			if ($row['QTY_GOOD'] == '0') {
				$row['QTY_GOOD'] = '';
			}

			$MIN_MACH_SF = $row['HR_MACH_SF'] * 60;
			if ($MIN_MACH_SF == 0) {
				$MIN_MACH_SF = '';
			}

			//$td_class = GetColorCodeTrx($row['CODE_TRX']);
			$trxType = GetStrCodeTrx($row['CODE_TRX']);
		}

		/////////////////////
		//SAMPLE SHOP ORDERS
		/////////////////////

		$sql  = "SELECT ";
		$sql .= " h.ID_ITEM_PAR, ";
		$sql .= " convert(char(10),tx.DATE_TRX,101) as DATE_TRX2, ";
		$sql .= " CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
		$sql .= " tx.* ";
		$sql .= " FROM #temp_trx tx ";
		$sql .= " left join nsa.SHPORD_HDR h ";
		$sql .= "  on tx.ID_SO = h.ID_SO ";
		$sql .= "  and tx.SUFX_SO = h.SUFX_SO ";				
		$sql .= " WHERE tx.CODE_TRX in (102,103) ";
		$sql .= "  and (ltrim(tx.ID_SO) like 'S%' OR (ltrim(tx.ID_SO) = 'PROD' AND tx.ID_OPER = '1000'))";
		$sql .= " ORDER BY ";
		$sql .= "  DATE_TRX asc, ";
		$sql .= "  TIME_TRX asc, ";
		$sql .= "  ID_BADGE asc, ";
		$sql .= "  rowid asc ";
		QueryDatabase($sql, $results);

		$tot_sample_sec = 0;

		while ($row = mssql_fetch_assoc($results)) {
			$prev = '';
			$diff_sec = '';
			//$td_class = GetColorCodeTrx($row['CODE_TRX']);
			$trxType = GetStrCodeTrx($row['CODE_TRX']);
			//$curr = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
			//$currts = strtotime($curr);
			$currts = $row['DATETIME_TRX_TS'];

			if ($row['CODE_TRX'] == '103')  {
				$sql2  = "SELECT top 1 ";
				$sql2 .= "  CONVERT(varchar(8), tx.DATE_TRX, 112) as DATE_TRX3, ";
				$sql2 .= "  tx.* ";
				$sql2 .= " FROM #temp_trx tx ";
				$sql2 .= " WHERE tx.CODE_TRX in (" . ($row['CODE_TRX'] - 1) . ") ";
				$sql2 .= "  and ID_SO = '" . $row['ID_SO'] . "' ";
				$sql2 .= "  and ID_BADGE = '" . $row['ID_BADGE'] . "' ";
				//$sql2 .= "  and DATE_TRX <= '" . $row['DATE_TRX'] . "' ";
				//$sql2 .= "  and TIME_TRX <= '" . $row['TIME_TRX'] . "' ";
				$sql2 .= " and DATETIME_TRX_TS <= '" . $row['DATETIME_TRX_TS'] ."' ";
				$sql2 .= " ORDER BY ";
				$sql2 .= "  DATE_TRX desc, ";
				$sql2 .= "  time_trx desc ";
				QueryDatabase($sql2, $results2);

				while ($row2 = mssql_fetch_assoc($results2)) {
					//$prev = $row2['DATE_TRX3'] . " " . str_pad($row2['TIME_TRX'],6,"0",STR_PAD_LEFT);
					//$prevts = strtotime($prev);
					$prevts = $row2['DATETIME_TRX_TS'];
					$diff_sec = $currts - $prevts;
					if ($row['CODE_TRX'] == '103') {
						$tot_sample_sec += $diff_sec;
					}
				}
			} 
		}

		$tot_team_sample_min  = round(($tot_sample_sec * 1.25) / 60,3);
		$tot_min_earned += $tot_team_sample_min;

		//$tot_team_actual_min_UNADJUSTED = $tot_team_actual_sec_UNADJUSTED / 60;
		$tot_so_indir_min = $tot_so_indir_sec / 60;

		//$tot_team_actual_min = ($tot_team_actual_sec - $tot_so_indir_sec) / 60;
		if ($RealTime == "T") {
			$sql  = "select ";
			$sql .= " replace(convert(varchar(8),getdate(), 108),':','') as nowTime, ";
			$sql .= " CONVERT(varchar(8), n.DATE_TRX, 112) as DATE_TRX3, ";
			$sql .= " n.TIME_TRX, ";
			$sql .= " (ro.HR_MACH_SR * 60 * (so.QTY_ORD - so.QTY_CMPL)) as UPCOMING_MINS ";
			$sql .= " from nsa.DCUTRX_NONZERO n ";
			$sql .= " left join nsa.SHPORD_HDR sh ";
			$sql .= " on n.ID_SO = sh.ID_SO ";
			$sql .= " and n.SUFX_SO = sh.SUFX_SO ";
			$sql .= " left join nsa.ITMMAS_LOC il ";
			$sql .= " on sh.ID_ITEM_PAR = il.ID_ITEM ";
			$sql .= " and il.ID_LOC = '10' ";
			$sql .= " left join nsa.SHPORD_OPER so ";
			$sql .= " on n.ID_SO = so.ID_SO ";
			$sql .= " and n.SUFX_SO = so.SUFX_SO ";
			$sql .= " and n.ID_OPER = so.ID_OPER ";
			$sql .= " left join nsa.ROUTMS_OPER ro ";
			$sql .= " on sh.ID_ITEM_PAR = ro.ID_ITEM ";
			$sql .= " and n.ID_OPER = ro.ID_OPER ";
			$sql .= " and ro.ID_RTE = il.ID_RTE ";
			$sql .= " left join nsa.DCUTRX_NONZERO n2 ";
			$sql .= " on n.DATE_TRX = n2.DATE_TRX ";
			$sql .= " and n.ID_SO = n2.ID_SO ";
			$sql .= " and n.ID_OPER = n2.ID_OPER ";
			$sql .= " and n.CODE_ACTV = n2.CODE_ACTV ";
			$sql .= " and n.CODE_TRX <> n2.CODE_TRX ";
			$sql .= " and n2.FLAG_DEL = '' ";
			$sql .= " where ltrim(n.ID_BADGE) = '".$Team."' ";
			//$sql .= " and n.DATE_TRX between '" . $DateFrom . "' and '" . $DateTo . "' ";
			$sql .= " and cast(DATEDIFF(s,'19700101', dateadd(hh,".$UTC_Offset.",cast(convert(char(10),n.DATE_CORR_TRX,121) + ' ' + STUFF(STUFF(right('000000' + rtrim(n.TIME_CORR_TRX),6), 3,0,':'),6,0,':') + '.000' as DATETIME))) as bigint) between ".$DateFromTS." and ".$DateToTS." ";
			$sql .= " and n.CODE_TRX = '102' ";
			$sql .= " and n.FLAG_DEL = '' ";
			$sql .= " and n.ID_SO <> 'PROD' ";
			$sql .= " and n2.CODE_TRX is null ";
			$sql .= " order by n.TIME_TRX asc ";
			QueryDatabase($sql, $results);
			while ($row = mssql_fetch_assoc($results)) {
				$startTM = $row['DATE_TRX3'] . " " . str_pad($row['TIME_TRX'],6,"0",STR_PAD_LEFT);
				$currTM = $row['DATE_TRX3'] . " " . str_pad($row['nowTime'],6,"0",STR_PAD_LEFT);
				$startTS = strtotime($startTM);
				$currTS = strtotime($currTM);
				$diff_sec = $currTS - $startTS;
				$diff_min = $diff_sec/60;

				if ($diff_min < $row['UPCOMING_MINS']) {
					error_log("ADDING diff_min: " . $diff_min);
					$tot_min_earned += $diff_min;
				} else {
					error_log("ADDING UPCOMING_MINS: " . $row['UPCOMING_MINS']);
					$tot_min_earned += $row['UPCOMING_MINS'];
				}
			}
		}


		/////////////////////
		//OVERALL EFFICIENCY
		/////////////////////
		$tot_team_actual_min = ($tot_team_actual_sec - $tot_so_indir_sec) / 60;

		if ($DEBUG > 0) { //>
			error_log("Earned " . $tot_min_earned);
			error_log("Actual " . $tot_team_actual_min);
		}
		$ovral_eff = round((($tot_min_earned / ($tot_team_actual_min)) * 100),2);

		return $ovral_eff;

	}

	function GetStrRnDStatusCode($code) {
		$strRndStatusCode = '';
		switch ($code) {
			case '0':
				$strRndStatusCode = 'Denied Approval';
				break;
			case '1':
				$strRndStatusCode = 'New';
				break;
			case '2':
				$strRndStatusCode = 'Declined by Dept Head';
				break;
			case '10':
				$strRndStatusCode = 'Awaiting Assignment';
				break;
			case '20':
				$strRndStatusCode = 'Assigned';
				break;
			case '30':
				$strRndStatusCode = 'In Progress';
				break;
			case '100':
				$strRndStatusCode = 'Complete';
				break;
		}
		return $strRndStatusCode;
	}

	function mssql_escape($data) {
		if(is_numeric($data)) {
			return $data;
		}
		$unpacked = unpack('H*hex', $data);
		return '0x' . $unpacked['hex'];
	}



function validate_UPCABarcode($barcode){
	// check to see if barcode is 12 digits long
	if(!preg_match("/^[0-9]{12}$/",$barcode)) {
		return false;
	}
	$digits = $barcode;
	// 1. sum each of the odd numbered digits
	$odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];  
	// 2. multiply result by three
	$odd_sum_three = $odd_sum * 3;
	// 3. add the result to the sum of each of the even numbered digits
	$even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9];
	$total_sum = $odd_sum_three + $even_sum;
	// 4. subtract the result from the next highest power of 10
	$next_ten = (ceil($total_sum/10))*10;
	$check_digit = $next_ten - $total_sum;
	// if the check digit and the last digit of the barcode are OK return true;
	if($check_digit == $digits[11]) {
		return true;
	} 
	return false;
}


function getTableauTicket($username){
	$url = 'http://as1/trusted';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'username='.$username);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$ticket = curl_exec($ch);
	curl_close($ch);
	error_log("ticket:" . $ticket);
	return $ticket;
}



########################## Begin Excel Functions
	function xlsBOF() {
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
	}

	function xlsEOF() {
		echo pack("ss", 0x0A, 0x00);
		return;
	}

	function xlsWriteNumber($Row, $Col, $Value) {
		echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		echo pack("d", $Value);
		return;
	}

	function xlsWriteLabel($Row, $Col, $Value ) {
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		echo $Value;
		return;
	}

########################## End Excel Functions

function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Edge/i',$u_agent)) 
    { 
        $bname = 'Microsoft Edge'; 
        $ub = "Edge"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    } 
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'short_name' => $ub,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
} 


function ip_in_range( $ip, $range ) {
/*
  Check if a given ip is in a network
  @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
  @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
  @return boolean true if the ip is in this range / false if not.
 */

	if ( strpos( $range, '/' ) == false ) {
		$range .= '/32';
	}
	// $range is in IP/CIDR format eg 127.0.0.1/24
	list( $range, $netmask ) = explode( '/', $range, 2 );
	$range_decimal = ip2long( $range );
	$ip_decimal = ip2long( $ip );
	$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
	$netmask_decimal = ~ $wildcard_decimal;
	return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
}

function findBuildingByIP($ip) {
	$retBuilding = "unknown";

	if (ip_in_range( $ip, '10.0.0.0/22' )) {// Chicago
		$retBuilding = "Chicago";
	}
	if (ip_in_range( $ip, '192.168.100.0/22' )) {// HQ
		$retBuilding = "HQ";
	}
	if (ip_in_range( $ip, '192.168.150.0/24' )) {// School
		$retBuilding = "School";
	}
	if (ip_in_range( $ip, '192.168.200.0/22' )) {// FC
		$retBuilding = "FC";
	}
	if (ip_in_range( $ip, '192.168.215.0/24' )) {// Arkansas
		$retBuilding = "Arkansas";
	}
	if (ip_in_range( $ip, '192.168.220.0/24' )) {// Kansas
		$retBuilding = "Kansas";
	}
	if (ip_in_range( $ip, '192.168.225.0/24' )) {// Belleville
		$retBuilding = "Belleville";
	}

	return $retBuilding;
}

function showLocationDropdown($building) {
	$SELECTED_CHICAGO = "";
	$SELECTED_CLEVELAND = "";
	$SELECTED_ARKANSAS = "";
	$SELECTED_KANSAS = "";
	$SELECTED_BELLEVILLE = "";

	switch ($building) {
		case "Chicago":
			$SELECTED_CHICAGO = "SELECTED";
		break;
		case "HQ":
			$SELECTED_CLEVELAND = "SELECTED";
		break;
		case "School":
			$SELECTED_CLEVELAND = "SELECTED";
		break;
		case "FC":
			$SELECTED_CLEVELAND = "SELECTED";
		break;
		case "Arkansas":
			$SELECTED_ARKANSAS = "SELECTED";
		break;
		case "Kansas":
			$SELECTED_KANSAS = "SELECTED";
		break;
		case "Belleville":
			$SELECTED_BELLEVILLE = "SELECTED";
		break;
	}


	print(" 	<tr>\n");
	print(" 		<td>\n");
	print(" 			<LABEL for='sel_Location'>Location: </LABEL>\n");
	print(" 		</td>\n");
	print(" 		<td>\n");
	print("				<select id='sel_Location' onChange=\"showStatusChange()\">\n");
	

	print("					<option value='Cleveland' ".$SELECTED_CLEVELAND.">Cleveland</option>\n");
	print("					<option value='Chicago' ".$SELECTED_CHICAGO.">Chicago</option>\n");
	print("					<option value='Arkansas'".$SELECTED_ARKANSAS.">Arkansas</option>\n");
	print("					<option value='Kansas'".$SELECTED_KANSAS.">Kansas</option>\n");
	print("					<option value='Belleville'".$SELECTED_BELLEVILLE.">Belleville</option>\n");
	print("				</select>\n");
	print(" 		</td>\n");
	print(" 	</tr>\n");


}


?>
