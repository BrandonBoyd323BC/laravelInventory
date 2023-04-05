<?php

	$DEBUG = 0;
	$SHOW_DEL = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
$DEBUG = 1;
	//PrintHeader('','default.css');
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print "		<p class='warning'>Could Not Connect To $DBServer!\n";
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print "		<p class='warning'>Could Not Select $db!\n";
		} else {

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			if ($UserRow['PERM_SU'] == '1')  {
				if ($_POST["action"] == 'update_perm') {
					if (isset($_POST["rowid"]) && isset($_POST["status"]) && isset($_POST["permSupervisor"]) && isset($_POST["permMgmt"]) && isset($_POST["permSubsid"]) && isset($_POST["permHr"]) && isset($_POST["permPlan"]) && isset($_POST["permCs"]) && isset($_POST["permMaint"]) && isset($_POST["permOrdPrep"]) && isset($_POST["permQa"]) && isset($_POST["permRndReq"]) && isset($_POST["permPreProd"]) && isset($_POST["permSpreading"]) && isset($_POST["permCutting"]) && isset($_POST["permProdMgt"]) && isset($_POST["permWH"]) && isset($_POST["permSu"]))  {
						$strRet = 'ERROR!';
						$today = date('m-d-Y');

						$sql  = " UPDATE nsa.DCWEB_AUTH SET ";
						$sql .= " STATUS='" . $_POST["status"] ."', ";
						$sql .= " PERM_SUPERVISOR='" . $_POST["permSupervisor"] ."', ";
						$sql .= " PERM_MGMT='" . $_POST["permMgmt"] ."', ";
						$sql .= " PERM_SUBSID='" . $_POST["permSubsid"] ."', ";
						$sql .= " PERM_HR='" . $_POST["permHr"] ."', ";
						$sql .= " PERM_PLAN='" . $_POST["permPlan"] ."', ";
						$sql .= " PERM_CS='" . $_POST["permCs"] ."', ";
						$sql .= " PERM_MAINT='" . $_POST["permMaint"] ."', ";
						$sql .= " PERM_ORD_PREP='" . $_POST["permOrdPrep"] ."', ";
						$sql .= " PERM_QA='" . $_POST["permQa"] ."', ";
						$sql .= " PERM_RND_REQ='" . $_POST["permRndReq"] ."', ";
						$sql .= " PERM_PREPROD='" . $_POST["permPreProd"] ."', ";
						$sql .= " PERM_SPREADING='" . $_POST["permSpreading"] ."', ";
						$sql .= " PERM_CUTTING='" . $_POST["permCutting"] ."', ";
						$sql .= " PERM_PRODMGT='" . $_POST["permProdMgt"] ."', ";
						$sql .= " PERM_WH='" . $_POST["permWH"] ."', ";
						$sql .= " PERM_SU='" . $_POST["permSu"] ."', ";
						$sql .= " DATE_CHG=GetDate() ";
						$sql .= " WHERE rowid=" . $_POST['rowid'];
						QueryDatabase($sql, $results);

						if ($results == '1') {
							$strRet = 'OK!';
						}
						$ret = "	<font>" . $strRet . "</font>\n";

						echo json_encode(array("returnValue"=> $ret));
					}
				}

				if ($_POST["action"] == 'perm_add') {
					if (isset($_POST["status"]) && isset($_POST["idbadge"]) && isset($_POST["username"]) && isset($_POST["iduser"]) && isset($_POST["nameemp"]) && isset($_POST["email"]) && isset($_POST["permSupervisor"]) && isset($_POST["permMgmt"]) && isset($_POST["permSubsid"]) && isset($_POST["permHr"]) && isset($_POST["permPlan"]) && isset($_POST["permCs"]) && isset($_POST["permMaint"]) && isset($_POST["permOrdPrep"]) && isset($_POST["permQa"]) && isset($_POST["permRndReq"]) && isset($_POST["permPreProd"]) && isset($_POST["permSpreading"]) && isset($_POST["permCutting"]) && isset($_POST["permProdMgt"]) && isset($_POST["permWH"]) && isset($_POST["permSu"]))  {
						$strRet = 'ERROR!';
						$today = date('m-d-Y');

						$sql  = " INSERT into ";
						$sql .= "	nsa.DCWEB_AUTH ";
						$sql .= " ( ";
						$sql .= " 	STATUS, ";
						$sql .= " 	ID_BADGE, ";
						$sql .= " 	USER_NAME, ";
						$sql .= " 	ID_USER, ";
						$sql .= " 	NAME_EMP, ";
						$sql .= " 	EMAIL, ";
						$sql .= " 	ID_USER_ADD, ";
						$sql .= " 	DATE_ADD, ";
						$sql .= " 	PERM_SUPERVISOR, ";
						$sql .= " 	PERM_MGMT, ";
						$sql .= " 	PERM_SUBSID, ";
						$sql .= " 	PERM_HR, ";
						$sql .= " 	PERM_PLAN, ";
						$sql .= " 	PERM_CS, ";
						$sql .= " 	PERM_MAINT, ";
						$sql .= " 	PERM_ORD_PREP, ";
						$sql .= " 	PERM_QA, ";
						$sql .= " 	PERM_RND_REQ, ";
						$sql .= " 	PERM_PREPROD, ";
						$sql .= " 	PERM_SPREADING, ";
						$sql .= " 	PERM_CUTTING, ";
						$sql .= " 	PERM_PRODMGT, ";
						$sql .= " 	PERM_WH, ";
						$sql .= " 	PERM_SU ";
						$sql .= " ) values ( ";
						$sql .= "	'" . stripIllegalChars($_POST['status']) . "', ";
						$sql .= "	'" . str_pad(stripIllegalChars($_POST['idbadge']),9," ",STR_PAD_LEFT) . "', ";
						$sql .= "	'" . stripIllegalChars($_POST['username']) . "', ";
						$sql .= "	'" . stripIllegalChars($_POST['iduser']) . "', ";
						$sql .= "	'" . stripIllegalChars($_POST['nameemp']) . "', ";
						$sql .= "	'" . $_POST['email'] . "', ";
						$sql .= "	'" . stripIllegalChars($UserRow['ID_USER']) . "', ";
						$sql .= "	GetDate(), ";
						$sql .= "	'" . $_POST['permSupervisor'] . "', ";
						$sql .= "	'" . $_POST['permMgmt'] . "', ";
						$sql .= "	'" . $_POST['permSubsid'] . "', ";
						$sql .= "	'" . $_POST['permHr'] . "', ";
						$sql .= "	'" . $_POST['permPlan'] . "', ";
						$sql .= "	'" . $_POST['permCs'] . "', ";
						$sql .= "	'" . $_POST['permMaint'] . "', ";
						$sql .= "	'" . $_POST['permOrdPrep'] . "', ";
						$sql .= "	'" . $_POST['permQa'] . "', ";
						$sql .= "	'" . $_POST['permRndReq'] . "', ";
						$sql .= "	'" . $_POST['permPreProd'] . "', ";
						$sql .= "	'" . $_POST['permSpreading'] . "', ";
						$sql .= "	'" . $_POST['permCutting'] . "', ";
						$sql .= "	'" . $_POST['permProdMgt'] . "', ";
						$sql .= "	'" . $_POST['permWH'] . "', ";
						$sql .= "	'" . $_POST['permSu'] . "' ";
						$sql .= " ) ";
						QueryDatabase($sql, $results);

						if ($results == '1') {
							$strRet = 'OK!';
						}
						$ret = "	<font>" . $strRet . "</font>\n";

						echo json_encode(array("returnValue"=> $ret));
					}
				}

			} else {
				print "					<p class='warning'>Permission Denied!</p>\n";
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n";
		}
	}


	$my_retval = my_ConnectToDatabaseServer($my_DBServer, $my_db);
	if ($my_retval == 0) {
		print "		<p class='warning'>Could Not Connect To $my_DBServer!\n";
	} else {
		$my_retval = my_SelectDatabase('auth');
		if ($my_retval == 0) {
			print "		<p class='warning'>Could Not Select $my_db!\n";
		} else {

			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);

			if ($UserRow['PERM_SU'] == '1')  {
				if ($_POST["action"] == 'add_my') {
					if (isset($_POST["myuser"]) && isset($_POST["mypwd"]))  {
						if (($_POST["myuser"] != '') && ($_POST["mypwd"] != '')) {
							$strRet = 'ERROR!';
							$today = date('m-d-Y');

							$sql  = " INSERT INTO users ";
							$sql .= " VALUES ( ";
							$sql .= " '" . stripIllegalChars($_POST['myuser']) ."', ";
							$sql .= " ENCRYPT('" . $_POST['mypwd'] ."') ";
							$sql .= " ) ";
							my_QueryDatabase($sql, $results);

							if ($results == '1') {
								$strRet = 'OK!';
							}
							$ret = "	<font>" . $strRet . "</font>\n";

							echo json_encode(array("returnValue"=> $ret));
						}
					}
				}
			} else {
				print "					<p class='warning'>Permission Denied!</p>\n";
			}
		}
		$my_retval = my_DisconnectFromDatabaseServer($my_db);
		if ($my_retval == 0) {
			print "					<p class='warning'>Could Not Disconnect From $my_DBServer!</p>\n";
		}
	}
?>
