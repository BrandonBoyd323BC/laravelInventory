<?php
	$DEBUG = 0;
	setlocale(LC_MONETARY, 'en_US');

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once("classes/simple_html_dom.php");

/*
	function get_data($url) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	$html = get_data('http://www.sewingmachineparts.com/sewing-parts/brother/page1');
//	error_log($html);
*/


	$URL = 'http://www.sewingmachineparts.com/sewing-parts/brother/page1';
	$html = file_get_html($URL);
	$elem = $html->find('div[class=data-lists]',0);
	//$elem = $html->find('[id^=d]',0);

	error_log($elem);
	echo($elem);
	//echo($html);
/*
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runDAILY_BOOKINGS cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runDAILY_BOOKINGS started at " . date('Y-m-d g:i:s a'));
			$today = date('Y-m-d');

			$sql  = " SELECT ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.BOKHST_LINE l where l.DATE_BOOK_LAST = '".$today."' and ID_CUST NOT like 'V%') as NSA_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.BOKHST_LINE l where l.DATE_BOOK_LAST = '".$today."' and ID_CUST LIKE 'V%') as VINA_COUNT, ";
			$sql .= " (select count(distinct(ID_ORD)) FROM nsa.BOKHST_LINE l where l.DATE_BOOK_LAST = '".$today."') as TOTAL_COUNT, ";
			$sql .= " (select COALESCE(sum(l.SLS),0) FROM nsa.BOKHST_LINE l where l.DATE_BOOK_LAST = '".$today."' and ID_CUST NOT like 'V%') as NSA_SLS, ";
			$sql .= " (select COALESCE(sum(l.SLS),0) FROM nsa.BOKHST_LINE l where l.DATE_BOOK_LAST = '".$today."' and ID_CUST like 'V%') as VINA_SLS, ";
			$sql .= " (select COALESCE(sum(l.SLS),0) FROM nsa.BOKHST_LINE l where l.DATE_BOOK_LAST = '".$today."') as TOTAL_SLS ";
			QueryDatabase($sql, $results);

			while ($row = mssql_fetch_assoc($results)) {
				if ($row['TOTAL_SLS'] <> '0' OR $row['TOTAL_COUNT'] <> '0') {
					$subject = "Bookings Summary for " . $today;
					$body  = "Bookings on " . $today . ".\r\n";
					$body .= "\r\n NSA:		" . $row['NSA_COUNT'] . " orders,	Bookings	" . money_format('%(n',$row['NSA_SLS']);
					$body .= "\r\n Vinatronics:	" . $row['VINA_COUNT'] . " orders,	Bookings	" . money_format('%(n',$row['VINA_SLS']);
					$body .= "\r\n Total:		" . $row['TOTAL_COUNT'] . " orders,	Bookings	" . money_format('%(n',$row['TOTAL_SLS']);

					$headers = "From: eProduction@nsamfg.com" . "\r\n" .
						"X-Mailer: PHP/" . phpversion();

					if ($argv[1] == 'ALL')  {
						error_log("PARAMS: " . $argv[1]);
						$aa_to  = GetEmailSubscribers('BOK');
					} else {
						$aa_to = $argv;
					}
					foreach ($aa_to as $to) {
						if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
							$to = "gvandyne@nsamfg.com";
						}
						error_log("BOK_SUM: " . $to);
						mail($to, $subject, $body, $headers);
					}
				}
			}
			error_log("### runDAILY_BOOKINGS finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runDAILY_BOOKINGS cannot disconnect from database");
		}
	}
*/
?>