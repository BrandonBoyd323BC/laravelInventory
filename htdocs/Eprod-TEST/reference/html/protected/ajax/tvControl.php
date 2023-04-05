<?php

	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	$DEBUG = 1;
	include('../phpseclib1.0.11/Net/SSH2.php');
	
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		$ret .= "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			if (isset($_POST["action"])) {
				$action = $_POST["action"];
				switch ($action) {

					case "doOnLoad":
						/////////////////////
						// TABLE OF TV DISPLAYS
						/////////////////////
						$ret .= " <div id='tvTableDiv'>\n";
						$ret .= " 	<table id='tvTable' class='sample'>\n";
						$ret .= " 		<tr>\n";
						$ret .= " 			<th>TVID</th>\n";
						$ret .= " 			<th>TV Desc</th>\n";
						$ret .= " 			<th>IP Address</th>\n";
						$ret .= " 			<th>Power Status</th>\n";
						//$ret .= " 			<th>Current Playlist</th>\n";
						$ret .= " 		</tr>\n";

						$sql  = "SELECT d.TVID, ";
						$sql .= " d.DESCR_TV, ";
						$sql .= " d.IPV4_ADDR, ";
						$sql .= " d.CURR_PLID, ";
						$sql .= " d.rowid as TV_ROWID ";
						//$sql .= " ,h.PL_DESCR ";
						$sql .= " FROM nsa.TV_DISPLAYS d ";
						//$sql .= " LEFT JOIN nsa.TV_PLAYLIST_HDR h ";
						//$sql .= " on d.CURR_PLID = h.PLID ";
						QueryDatabase($sql, $results);

						while ($row = mssql_fetch_assoc($results)) {
							$ret .= " 		<tr>\n";
							$ret .= " 			<td>".$row['TVID']."</td>\n";
							$ret .= " 			<td>".$row['DESCR_TV']."</td>\n";
							$ret .= " 			<td><a href='http://".$row['IPV4_ADDR']."' target='_blank'>".$row['IPV4_ADDR']."</a></td>\n";

							$tv_rowid = $row['TV_ROWID'];
							$tvPowStatus = "UNKNOWN";
							$ssh = new Net_SSH2($row["IPV4_ADDR"]);
							if (!$ssh->login("pi", "NSAmfg123")) {
							   error_log("rbPi: ".$row['IPV4_ADDR']." Login Failed");
							}
							$output = $ssh->exec('echo pow 0 | cec-client -s -d 1');
							$posON = strpos($output,"power status: on");
							$posSTANDBY = strpos($output,"power status: standby");

							if ($posON !== false) {
								$tvPowStatus = "ON";
								$tvPowStatCHECKED = "CHECKED";
							}
							if ($posSTANDBY !== false) {
								$tvPowStatus = "STANDBY";
								$tvPowStatCHECKED = "";
							}

							if ($tvPowStatus <> "UNKNOWN") {
								$ret .= "			<td>\n";
								$ret .= "				<label class='switch'>\n";
								$ret .= "					<input type='checkbox' id='cbTvPow_".$row['TVID']."' onchange=\"tvPowerSliderChanged(this.id,$tv_rowid)\" ".$tvPowStatCHECKED.">\n";
								$ret .= "					<span class='sliderBLUE round'></span>\n";
								$ret .= "				</label>\n";
								$ret .= "			</td>\n";
							} else {
								$ret .= "			<td>UNKNOWN</td>\n";
							}

/*
							$ret .= " 			<td>\n";
							$ret .= "				<select id='selTvPLId_".$tv_rowid."' onChange=\"selTvPLIdChanged($tv_rowid)\">\n";

							$sql1  = "SELECT PLID, PL_DESCR, concat(PLID,' - ', PL_DESCR) as PLID_DESCR ";
							$sql1 .= " FROM nsa.TV_PLAYLIST_HDR h ";
							$sql1 .= " WHERE (h.FLAG_DEL <> 'D' OR h.FLAG_DEL is null) ";
							$sql1 .= " ORDER BY PLID asc ";
							QueryDatabase($sql1, $results1);

							while ($row1 = mssql_fetch_assoc($results1)) {
								$SELECTED = "";
								if ($row['CURR_PLID'] == $row1['PLID']) {
									$SELECTED = "SELECTED";
								}
								$ret .= "					<option value='" . $row1['PLID'] . "' ".$SELECTED.">".$row1['PLID_DESCR']."</option>\n";
							}

							$ret .= "				</select>\n";
							$ret .= "				<input type='button' id= 'saveSelTvPLId_".$tv_rowid."' value='Save' onClick=\"saveSelTvPLId($tv_rowid)\" style='display:none'></input>\n";
							$ret .= " 			</td>\n";
*/							
							$ret .= " 		</tr>\n";
						}
						$ret .= " 	</table>\n";
						$ret .= " </div>\n";
						$ret .= " </br>\n";

/*
						/////////////////////
						// TABLE OF TV PLAYLISTS
						/////////////////////
						$ret .= " <div id='playlistTableDiv'>\n";
						$ret .= " 	<table id='playlistTable' class='sample'>\n";
						$ret .= " 		<tr>\n";
						$ret .= " 			<th>Playlist ID</th>\n";
						$ret .= " 			<th>Playlist Name</th>\n";
						$ret .= " 			<th>Tracks</th>\n";
						$ret .= " 		</tr>\n";
						
						$sql  = "SELECT h.PLID, h.PL_DESCR ";
						$sql .= " FROM nsa.TV_PLAYLIST_HDR h ";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							$ret .= " 		<tr>\n";
							$ret .= " 			<td>".$row['PLID']."</td>\n";
							$ret .= " 			<td>".$row['PL_DESCR']."</td>\n";
							$ret .= " 			<td><table>\n";
							
							$sql1  = "SELECT concat(m.rowid,' - ', m.MEDIA_NAME) as mRowid_NAME, ";
							$sql1 .= " m.MEDIA_DESCR, ";
							$sql1 .= " m.MEDIA_PATH, ";
							$sql1 .= " l.SEQ_TRACK, ";
							$sql1 .= " l.DURATION ";
							$sql1 .= " FROM nsa.TV_PLAYLIST_LINE l ";
							$sql1 .= " JOIN nsa.TV_MEDIA m ";
							$sql1 .= " on l.TV_MEDIA_rowid = m.rowid ";
							$sql1 .= " WHERE l.PLID = ".$row['PLID'];
							QueryDatabase($sql1, $results1);
							while ($row1 = mssql_fetch_assoc($results1)) {
								$cmd = "mediainfo ".$row1['MEDIA_PATH']." | grep -i duration";
								error_log("CMD: ".$cmd);
								$output = shell_exec($cmd);
								error_log("Output: ".$output);
								$ret .= "<tr>\n";
								$ret .= "	<td>" . $row1['SEQ_TRACK']."</td>\n";
								$ret .= "	<td>" . $row1['mRowid_NAME']."</td>\n";
								$ret .= "	<td>" . $row1['DURATION']."</td>\n";
								$ret .= "</tr>\n";
							}
							$ret .= " 			</table></td>\n";
							
							$ret .= " 		</tr>\n";
						}

						$ret .= " 	</table>\n";
						$ret .= " </div>\n";
						$ret .= " </br>\n";

						/////////////////////
						// TABLE OF TV MEDIA
						/////////////////////
						$ret .= " <div id='mediaTableDiv'>\n";
						$ret .= " 	<table id='mediaTable' class='sample'>\n";
						$ret .= " 		<tr>\n";
						$ret .= " 			<th>ID</th>\n";
						$ret .= " 			<th>Media Name</th>\n";
						$ret .= " 			<th>Description</th>\n";
						$ret .= " 			<th>Path</th>\n";
						$ret .= " 			<th>Type</th>\n";
						$ret .= " 			<th>Duration</th>\n";
						$ret .= " 		</tr>\n";
						
						$sql  = "SELECT m.rowid, m.MEDIA_NAME, m.MEDIA_DESCR, m.MEDIA_PATH, m.MEDIA_TYPE ";
						$sql .= " FROM nsa.TV_MEDIA m ";
						$sql .= " WHERE (m.FLAG_DEL <> 'D' OR m.FLAG_DEL is null) ";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)) {
							$ret .= " 		<tr>\n";
							$ret .= " 			<td>".$row['rowid']."</td>\n";
							$ret .= " 			<td>".$row['MEDIA_NAME']."</td>\n";
							$ret .= " 			<td>".$row['MEDIA_DESCR']."</td>\n";
							$ret .= " 			<td>".$row['MEDIA_PATH']."</td>\n";
							$ret .= " 			<td>".$row['MEDIA_TYPE']."</td>\n";
							$ret .= " 			<td></td>\n";
							$ret .= " 		</tr>\n";
						}

						$ret .= " 	</table>\n";
						$ret .= " </div>\n";
						$ret .= " </br>\n";
*/

					break;

/*
					case "saveSelTvPLId":
						if (isset($_POST["tv_rowid"]) && isset($_POST["selectedPLID"])) {
							if (is_numeric($_POST["tv_rowid"]) && is_numeric($_POST["selectedPLID"])) {
								error_log("Updating TVID: " . $_POST["tv_rowid"] . " to PLID: " . $_POST["selectedPLID"]);
								$sql  = "UPDATE nsa.TV_DISPLAYS ";
								$sql .= " SET CURR_PLID = " . $_POST["selectedPLID"];
								$sql .= " WHERE rowid = ".$_POST["tv_rowid"];
								QueryDatabase($sql, $results);
							} else {
								error_log("ERROR!! NON-NUMERIC ATTEMPTED IN UPDATE TO TV_DISPLAYS.CURR_PLID");
							}
						}
					break;
*/



					case "cecTurnOnTV":
						if (isset($_POST["tv_rowid"])) {
							
							$sql  = "SELECT IPV4_ADDR, TVID ";
							$sql .= " FROM nsa.TV_DISPLAYS ";
							$sql .= " WHERE rowid = ".$_POST['tv_rowid'];
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								error_log("Turning ON tvId: " . $row['TVID']);
								$ssh = new Net_SSH2($row['IPV4_ADDR']);
								if (!$ssh->login('pi', 'NSAmfg123')) {
								   error_log('Login Failed');
								}
								$output = $ssh->exec('echo on 0 | cec-client -s -d 1');
								error_log($output);	
							}
						}
					break;

					case "cecTurnOffTV":
						if (isset($_POST["tv_rowid"])) {

							$sql  = "SELECT IPV4_ADDR, TVID ";
							$sql .= " FROM nsa.TV_DISPLAYS ";
							$sql .= " WHERE rowid = ".$_POST["tv_rowid"];
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								error_log("Turning OFF tvId: " . $row['TVID']);
								$ssh = new Net_SSH2($row['IPV4_ADDR']);
								if (!$ssh->login('pi', 'NSAmfg123')) {
								   error_log('Login Failed');
								}
								$output = $ssh->exec('echo standby 0 | cec-client -s -d 1');
								error_log($output);	
							}
						}
					break;


				}
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			$ret .= "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}

?>
