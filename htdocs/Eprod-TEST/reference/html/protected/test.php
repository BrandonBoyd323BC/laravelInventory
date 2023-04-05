<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/tc_calendar.php');

	PrintHeaderJQ('TEST Constructor','default.css','promise.js');

	if (date('I', time())){
		echo 'We.re in DST!';
	} else {
		echo 'We.re not in DST!';
	}



	PrintFooter("emenu.php");


?>
