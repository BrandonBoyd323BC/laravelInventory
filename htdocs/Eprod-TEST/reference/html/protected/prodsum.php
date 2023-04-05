<?php

	$filename = "\\FS1\NETSHARE\WORK INSTRUCTIONS\PS PDFS\C88 9oz UltraSoft.PDF";
	$mtime = exec ('stat -c %Y '. escapeshellarg ($filename));
	error_log("mtime: " . $mtime);
	
	//if (file_exists($filename)) {
	//    error_log($filename . " was last modified: " . date ("F d Y H:i:s.", filemtime($filename)));
	    //echo "$filename was last modified: " . date ("F d Y H:i:s.", filemtime($filename));
	//} else {
	//    error_log($filename . " not found");
	//}
	

/*
	print("	<form method='post' enctype='multipart/form-data'>");
	print("	<table width='350' border='0' cellpadding='1' cellspacing='1' class='box'>");
	print("		<tr> ");
	print("			<td width='246'>");
	print("				<input type='hidden' name='MAX_FILE_SIZE' value='2000000'>");
	print("				<input name='userfile' type='file' id='userfile'> ");
	print("			</td>");
	print("			<td width='80'><input name='upload' type='submit' class='box' id='upload' value=' Upload '></td>");
	print("		</tr>");
	print("	</table>");
	print("	</form>");
*/
?>










/*
<?php
	if (isset($_POST['upload']) && $_FILES['userfile']['size'] > 0) {
		$fileName = $_FILES['userfile']['name'];
		$tmpName  = $_FILES['userfile']['tmp_name'];
		$fileSize = $_FILES['userfile']['size'];
		$fileType = $_FILES['userfile']['type'];

		error_log("fileName: " . $fileName);
		error_log("tmpName: " . $tmpName);
		error_log("fileSize: " . $fileSize);
		error_log("fileType: " . $fileType);

//		$fp      = fopen($tmpName, 'r');
//		$content = fread($fp, filesize($tmpName));
//		$content = addslashes($content);
//		fclose($fp);

//		if(!get_magic_quotes_gpc())
//		{
//		    $fileName = addslashes($fileName);
//		}
//		include 'library/config.php';
//		include 'library/opendb.php';

//		$query = "INSERT INTO upload (name, size, type, content ) ".
//		"VALUES ('$fileName', '$fileSize', '$fileType', '$content')";

//		mysql_query($query) or die('Error, query failed'); 
//		include 'library/closedb.php';

		echo "<br>File $fileName uploaded<br>";
	} 
?>
*/