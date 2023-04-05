<?php
	$file = file_get_contents('PS_PDFS/C88 9oz UltraSoft.pdf');
	//$file = shell_exec('ls PS_PDFS');
	if (!$file) {
		//error_log($file);
		echo "<p>Unable to open remote file.\n";
		//exit;
	} else {
		//error_log($file);
		echo "<p>ABLE to open remote file.\n";
		//exit;
	}
	echo $file;
	
?>
