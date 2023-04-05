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
			print("		<!DOCTYPE html> \n");
			print("<html>\n");
			print("	<head>\n");
			print("		<meta http-equiv='Content-type' content='text/html; charset=utf-8' /> \n");
			print("	</head>\n");
			print("	<body>\n");



			if (isset($_GET['LOC']) && isset($_GET['CAM'])) {
				$LOC = $_GET['LOC'];
				$CAM = $_GET['CAM'];


				switch ($LOC) {
					case "HQ":
						$dvrIP = "192.168.101.200";
					break;

					case "FC":
						$dvrIP = "192.168.201.200";
					break;

					default:
						$dvrIP = "";
					break;
				} // End Switch

				if ($dvrIP <> "") {
					print("<object \n");
					print(" classid='clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921'  \n");
					print(" codebase='http://download.videolan.org/pub/videolan/vlc/last/win32/axvlc.cab' \n");
					print(" id='vlc' \n");
					print(" name='vlc' \n");
					print(" class='vlcPlayer' \n");
					print(" events='True'> \n");
					print("  <param name='Src' value='rtsp://stream:stream18@".$dvrIP.":554/cam/realmonitor?channel=".$CAM."&subtype=0' /> <!-- ie --> \n");
					print("  <param name='ShowDisplay' value='True' /> \n");
					print("  <param name='AutoLoop' value='True' /> \n");
					print("  <param name='AutoPlay' value='True' /> \n");
					print("  <!-- win chrome and firefox--> \n");
					print("  <embed id='vlcEmb'  type='application/x-google-vlc-plugin' version='VideoLAN.VLCPlugin.2' autoplay='yes' loop='no' width='640' height='480' target='rtsp://stream:stream18@".$dvrIP.":554/cam/realmonitor?channel=".$CAM."&subtype=0'>  \n");
					print("</object> \n");
				} else {
					print("<h1>LOC not recognized</h1>\n");
				}
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


