<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("../procfile.php");
	
	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			if ($UserRow['PERM_SU'] == '1') {
				$ret = '';

				if (isset($_POST["action"])) {
					$Div = "mainDiv";
					$Action = $_POST["action"];

					switch($Action) {
						case "findBadRecs":
							if (isset($_POST['table']) && $_POST['table'] <> "unselected") {
								$Table = $_POST["table"];
								$ret .= "<div id='div_cusmas_soldto' name='div_cusmas_soldto'>\n";

								$sql  = "select TABLE_NAME, ";
								$sql .= " COLUMN_NAME ";
								$sql .= " FROM INFORMATION_SCHEMA.COLUMNS ";
								$sql .= " WHERE (COLUMN_NAME like '%_FC' OR COLUMN_NAME like '%_FC_%') ";
								$sql .= " and TABLE_SCHEMA = 'nsa' ";
								$sql .= " and TABLE_NAME = '" . $Table . "' ";
								$sql .= " order by TABLE_NAME asc, COLUMN_NAME asc ";
								QueryDatabase($sql, $results);
								/*
								$sql1 = "SELECT * from nsa." . $Table . " WHERE ";
								while ($row = mssql_fetch_assoc($results)) {
									$sql1 .= $row['COLUMN_NAME'] . " not like '001:%' OR ";
								}
								$sql1  = str_lreplace("OR", "", $sql1);
								$sql1 .= " ORDER BY ID_CUST asc ";
								QueryDatabase($sql1, $results1);
								*/

								$sql1_select = " SELECT ";
								$sql1_where = " WHERE ";
								while ($row = mssql_fetch_assoc($results)) {
									$sql1_select .= "(CASE WHEN " . $row['COLUMN_NAME'] . " not like '001:%' THEN 'BAD' ELSE 'GOOD' END) as GB_" . $row['COLUMN_NAME'] . ", ";
									$sql1_where .= $row['COLUMN_NAME'] . " not like '001:%' OR ";
								}
								$sql1_where  = str_lreplace("OR", "", $sql1_where);
								$sql1_select .= " * FROM nsa." . $Table . " ";
								$sql1 = $sql1_select . $sql1_where . " ORDER BY ID_CUST asc";
								QueryDatabase($sql1, $results1);

								switch ($Table) {
									case "CUSMAS_SOLDTO":
										while ($row1 = mssql_fetch_assoc($results1)) {
											$AMT_AGE_1 = number_format((float)$row1['AMT_AGE_1'],2,'.','');
											$AMT_AGE_2 = number_format((float)$row1['AMT_AGE_2'],2,'.','');
											$AMT_AGE_3 = number_format((float)$row1['AMT_AGE_3'],2,'.','');
											$AMT_AGE_4 = number_format((float)$row1['AMT_AGE_4'],2,'.','');
											$AMT_AGE_5 = number_format((float)$row1['AMT_AGE_5'],2,'.','');
											$AMT_CR_LIMIT = number_format((float)$row1['AMT_CR_LIMIT'],2,'.','');
											$AMT_CR_MAX = number_format((float)$row1['AMT_CR_MAX'],2,'.','');
											$AMT_INVC_LAST = number_format((float)$row1['AMT_INVC_LAST'],2,'.','');
											$AMT_INVC_LGST = number_format((float)$row1['AMT_INVC_LGST'],2,'.','');
											$AMT_INVC_OLD = number_format((float)$row1['AMT_INVC_OLD'],2,'.','');
											$AMT_ORD_LAST = number_format((float)$row1['AMT_ORD_LAST'],2,'.','');
											$AMT_ORD_LGST = number_format((float)$row1['AMT_ORD_LGST'],2,'.','');
											$AMT_ORD_OPEN = number_format((float)$row1['AMT_ORD_OPEN'],2,'.','');
											$AMT_PYMT_LAST = number_format((float)$row1['AMT_PYMT_LAST'],2,'.','');
											$AMT_PYMT_LGST = number_format((float)$row1['AMT_PYMT_LGST'],2,'.','');
											$AMT_PYMT_MTD = number_format((float)$row1['AMT_PYMT_MTD'],2,'.','');
											$AMT_PYMT_YTD = number_format((float)$row1['AMT_PYMT_YTD'],2,'.','');
											$BAL_AR = number_format((float)$row1['BAL_AR'],2,'.','');

											$ret .= " <table class='sample'>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<th colspan=5>" . $row1['ID_CUST'] . " - " . $row1['SEQ_SHIPTO'] . " - " . $row1['NAME_CUST'] . "</th>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_AGE_1</td>\n";
											$ret .= " 		<td>".$AMT_AGE_1."</td>\n";
											$ret .= " 		<td>AMT_AGE_FC_1</td>\n";
											$ret .= " 		<td id='AMT_AGE_FC_1_".$row1['rowid']."' name='AMT_AGE_FC_1_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_AGE_FC_1','AMT_AGE_1')\"><font class='".$row1['GB_AMT_AGE_FC_1']."'>".$row1['AMT_AGE_FC_1']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_AGE_2</td>\n";
											$ret .= " 		<td>".$AMT_AGE_2."</td>\n";
											$ret .= " 		<td>AMT_AGE_FC_2</td>\n";
											$ret .= " 		<td id='AMT_AGE_FC_2_".$row1['rowid']."' name='AMT_AGE_FC_2_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_AGE_FC_2','AMT_AGE_2')\"><font class='".$row1['GB_AMT_AGE_FC_2']."'>".$row1['AMT_AGE_FC_2']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_AGE_3</td>\n";
											$ret .= " 		<td>".$AMT_AGE_3."</td>\n";
											$ret .= " 		<td>AMT_AGE_FC_3</td>\n";
											$ret .= " 		<td id='AMT_AGE_FC_3_".$row1['rowid']."' name='AMT_AGE_FC_3_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_AGE_FC_3','AMT_AGE_3')\"><font class='".$row1['GB_AMT_AGE_FC_3']."'>".$row1['AMT_AGE_FC_3']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_AGE_4</td>\n";
											$ret .= " 		<td>".$AMT_AGE_4."</td>\n";
											$ret .= " 		<td>AMT_AGE_FC_4</td>\n";
											$ret .= " 		<td id='AMT_AGE_FC_4_".$row1['rowid']."' name='AMT_AGE_FC_4_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_AGE_FC_4','AMT_AGE_4')\"><font class='".$row1['GB_AMT_AGE_FC_4']."'>".$row1['AMT_AGE_FC_4']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_AGE_5</td>\n";
											$ret .= " 		<td>".$AMT_AGE_5."</td>\n";
											$ret .= " 		<td>AMT_AGE_FC_5</td>\n";
											$ret .= " 		<td id='AMT_AGE_FC_5_".$row1['rowid']."' name='AMT_AGE_FC_5_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_AGE_FC_5','AMT_AGE_5')\"><font class='".$row1['GB_AMT_AGE_FC_5']."'>".$row1['AMT_AGE_FC_5']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_CR_LIMIT</td>\n";
											$ret .= " 		<td>".$AMT_CR_LIMIT."</td>\n";
											$ret .= " 		<td>AMT_CR_LIMIT_FC</td>\n";
											$ret .= " 		<td id='AMT_CR_LIMIT_FC_".$row1['rowid']."' name='AMT_CR_LIMIT_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_CR_LIMIT_FC','AMT_CR_LIMIT')\"><font class='".$row1['GB_AMT_CR_LIMIT_FC']."'>".$row1['AMT_CR_LIMIT_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_CR_MAX</td>\n";
											$ret .= " 		<td>".$AMT_CR_MAX."</td>\n";
											$ret .= " 		<td>AMT_CR_MAX_FC</td>\n";
											$ret .= " 		<td id='AMT_CR_MAX_FC_".$row1['rowid']."' name='AMT_CR_MAX_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_CR_MAX_FC','AMT_CR_MAX')\"><font class='".$row1['GB_AMT_CR_MAX_FC']."'>".$row1['AMT_CR_MAX_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_INVC_LAST</td>\n";
											$ret .= " 		<td>".$AMT_INVC_LAST."</td>\n";
											$ret .= " 		<td>AMT_INVC_LAST_FC</td>\n";
											$ret .= " 		<td id='AMT_INVC_LAST_FC_".$row1['rowid']."' name='AMT_INVC_LAST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_INVC_LAST_FC','AMT_INVC_LAST')\"><font class='".$row1['GB_AMT_INVC_LAST_FC']."'>".$row1['AMT_INVC_LAST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_INVC_LGST</td>\n";
											$ret .= " 		<td>".$AMT_INVC_LGST."</td>\n";
											$ret .= " 		<td>AMT_INVC_LGST_FC</td>\n";
											$ret .= " 		<td id='AMT_INVC_LGST_FC_".$row1['rowid']."' name='AMT_INVC_LGST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_INVC_LGST_FC','AMT_INVC_LGST')\"><font class='".$row1['GB_AMT_INVC_LGST_FC']."'>".$row1['AMT_INVC_LGST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_INVC_OLD</td>\n";
											$ret .= " 		<td>".$AMT_INVC_OLD."</td>\n";
											$ret .= " 		<td>AMT_INVC_OLD_FC</td>\n";
											$ret .= " 		<td id='AMT_INVC_OLD_FC_".$row1['rowid']."' name='AMT_INVC_OLD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_INVC_OLD_FC','AMT_INVC_OLD')\"><font class='".$row1['GB_AMT_INVC_OLD_FC']."'>".$row1['AMT_INVC_OLD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_ORD_LAST</td>\n";
											$ret .= " 		<td>".$AMT_ORD_LAST."</td>\n";
											$ret .= " 		<td>AMT_ORD_LAST_FC</td>\n";
											$ret .= " 		<td id='AMT_ORD_LAST_FC_".$row1['rowid']."' name='AMT_ORD_LAST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_ORD_LAST_FC','AMT_ORD_LAST')\"><font class='".$row1['GB_AMT_ORD_LAST_FC']."'>".$row1['AMT_ORD_LAST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_ORD_LGST</td>\n";
											$ret .= " 		<td>".$AMT_ORD_LGST."</td>\n";
											$ret .= " 		<td>AMT_ORD_LGST_FC</td>\n";
											$ret .= " 		<td id='AMT_ORD_LGST_FC_".$row1['rowid']."' name='AMT_ORD_LGST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_ORD_LGST_FC','AMT_ORD_LGST')\"><font class='".$row1['GB_AMT_ORD_LGST_FC']."'>".$row1['AMT_ORD_LGST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_ORD_OPEN</td>\n";
											$ret .= " 		<td>".$AMT_ORD_OPEN."</td>\n";
											$ret .= " 		<td>AMT_ORD_OPEN_FC</td>\n";
											$ret .= " 		<td id='AMT_ORD_OPEN_FC_".$row1['rowid']."' name='AMT_ORD_OPEN_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_ORD_OPEN_FC','AMT_ORD_OPEN')\"><font class='".$row1['GB_AMT_ORD_OPEN_FC']."'>".$row1['AMT_ORD_OPEN_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_PYMT_LAST</td>\n";
											$ret .= " 		<td>".$AMT_PYMT_LAST."</td>\n";
											$ret .= " 		<td>AMT_PYMT_LAST_FC</td>\n";
											$ret .= " 		<td id='AMT_PYMT_LAST_FC_".$row1['rowid']."' name='AMT_PYMT_LAST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_PYMT_LAST_FC','AMT_PYMT_LAST')\"><font class='".$row1['GB_AMT_PYMT_LAST_FC']."'>".$row1['AMT_PYMT_LAST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_PYMT_LGST</td>\n";
											$ret .= " 		<td>".$AMT_PYMT_LGST."</td>\n";
											$ret .= " 		<td>AMT_PYMT_LGST_FC</td>\n";
											$ret .= " 		<td id='AMT_PYMT_LGST_FC_".$row1['rowid']."' name='AMT_PYMT_LGST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_PYMT_LGST_FC','AMT_PYMT_LGST')\"><font class='".$row1['GB_AMT_PYMT_LGST_FC']."'>".$row1['AMT_PYMT_LGST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_PYMT_MTD</td>\n";
											$ret .= " 		<td>".$AMT_PYMT_MTD."</td>\n";
											$ret .= " 		<td>AMT_PYMT_MTD_FC</td>\n";
											$ret .= " 		<td id='AMT_PYMT_MTD_FC_".$row1['rowid']."' name='AMT_PYMT_MTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_PYMT_MTD_FC','AMT_PYMT_MTD')\"><font class='".$row1['GB_AMT_PYMT_MTD_FC']."'>".$row1['AMT_PYMT_MTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>AMT_PYMT_YTD</td>\n";
											$ret .= " 		<td>".$AMT_PYMT_YTD."</td>\n";
											$ret .= " 		<td>AMT_PYMT_YTD_FC</td>\n";
											$ret .= " 		<td id='AMT_PYMT_YTD_FC_".$row1['rowid']."' name='AMT_PYMT_YTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'AMT_PYMT_YTD_FC','AMT_PYMT_YTD')\"><font class='".$row1['GB_AMT_PYMT_YTD_FC']."'>".$row1['AMT_PYMT_YTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>BAL_AR</td>\n";
											$ret .= " 		<td>".$BAL_AR."</td>\n";
											$ret .= " 		<td>BAL_AR_FC</td>\n";
											$ret .= " 		<td id='BAL_AR_FC_".$row1['rowid']."' name='BAL_AR_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'BAL_AR_FC','BAL_AR')\"><font class='".$row1['GB_BAL_AR_FC']."'>".$row1['BAL_AR_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= "</table>\n";
											$ret .= "	</br>\n";
										}
									break;
									
									case "CUSMAS_SHIPTO":
										while ($row1 = mssql_fetch_assoc($results1)) {
											$COST_MTD = number_format((float)$row1['COST_MTD'],2,'.','');
											$COST_YR_LAST = number_format((float)$row1['COST_YR_LAST'],2,'.','');
											$COST_YTD = number_format((float)$row1['COST_YTD'],2,'.','');
											$SLS_LAST_YR_MTD = number_format((float)$row1['SLS_LAST_YR_MTD'],2,'.','');
											$SLS_LAST_YR_YTD = number_format((float)$row1['SLS_LAST_YR_YTD'],2,'.','');
											$SLS_MTD = number_format((float)$row1['SLS_MTD'],2,'.','');
											$SLS_YR_LAST = number_format((float)$row1['SLS_YR_LAST'],2,'.','');
											$SLS_YTD = number_format((float)$row1['SLS_YTD'],2,'.','');

											$ret .= " <table class='sample'>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<th colspan=5>" . $row1['ID_CUST'] . " - " . $row1['SEQ_SHIPTO'] . " - " . $row1['NAME_CUST'] . "</th>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>COST_MTD</td>\n";
											$ret .= " 		<td>".$COST_MTD."</td>\n";
											$ret .= " 		<td>COST_MTD_FC</td>\n";
											$ret .= " 		<td id='COST_MTD_FC_".$row1['rowid']."' name='COST_MTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'COST_MTD_FC','COST_MTD')\"><font class='".$row1['GB_COST_MTD_FC']."'>".$row1['COST_MTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>COST_YR_LAST</td>\n";
											$ret .= " 		<td>".$COST_YR_LAST."</td>\n";
											$ret .= " 		<td>COST_YR_LAST_FC</td>\n";
											$ret .= " 		<td id='COST_YR_LAST_FC_".$row1['rowid']."' name='COST_YR_LAST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'COST_YR_LAST_FC','COST_YR_LAST')\"><font class='".$row1['GB_COST_YR_LAST_FC']."'>".$row1['COST_YR_LAST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>COST_YTD</td>\n";
											$ret .= " 		<td>".$COST_YTD."</td>\n";
											$ret .= " 		<td>COST_YTD_FC</td>\n";
											$ret .= " 		<td id='COST_YTD_FC_".$row1['rowid']."' name='COST_YTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'COST_YTD_FC','COST_YTD')\"><font class='".$row1['GB_COST_YTD_FC']."'>".$row1['COST_YTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>SLS_LAST_YR_MTD</td>\n";
											$ret .= " 		<td>".$SLS_LAST_YR_MTD."</td>\n";
											$ret .= " 		<td>SLS_LAST_YR_MTD_FC</td>\n";
											$ret .= " 		<td id='SLS_LAST_YR_MTD_FC_".$row1['rowid']."' name='SLS_LAST_YR_MTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'SLS_LAST_YR_MTD_FC','SLS_LAST_YR_MTD')\"><font class='".$row1['GB_SLS_LAST_YR_MTD_FC']."'>".$row1['SLS_LAST_YR_MTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>SLS_LAST_YR_YTD</td>\n";
											$ret .= " 		<td>".$SLS_LAST_YR_YTD."</td>\n";
											$ret .= " 		<td>SLS_LAST_YR_YTD_FC</td>\n";
											$ret .= " 		<td id='SLS_LAST_YR_YTD_FC_".$row1['rowid']."' name='SLS_LAST_YR_YTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'SLS_LAST_YR_YTD_FC','SLS_LAST_YR_YTD')\"><font class='".$row1['GB_SLS_LAST_YR_YTD_FC']."'>".$row1['SLS_LAST_YR_YTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>SLS_MTD</td>\n";
											$ret .= " 		<td>".$SLS_MTD."</td>\n";
											$ret .= " 		<td>SLS_MTD_FC</td>\n";
											$ret .= " 		<td id='SLS_MTD_FC_".$row1['rowid']."' name='SLS_MTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'SLS_MTD_FC','SLS_MTD')\"><font class='".$row1['GB_SLS_MTD_FC']."'>".$row1['SLS_MTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>SLS_YR_LAST</td>\n";
											$ret .= " 		<td>".$SLS_YR_LAST."</td>\n";
											$ret .= " 		<td>SLS_YR_LAST_FC</td>\n";
											$ret .= " 		<td id='SLS_YR_LAST_FC_".$row1['rowid']."' name='SLS_YR_LAST_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'SLS_YR_LAST_FC','SLS_YR_LAST')\"><font class='".$row1['GB_SLS_YR_LAST_FC']."'>".$row1['SLS_YR_LAST_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 	<tr>\n";
											$ret .= " 		<td>SLS_YTD</td>\n";
											$ret .= " 		<td>".$SLS_YTD."</td>\n";
											$ret .= " 		<td>SLS_YTD_FC</td>\n";
											$ret .= " 		<td id='SLS_YTD_FC_".$row1['rowid']."' name='SLS_YTD_FC_".$row1['rowid']."' onDblClick=\"fixBadRecord('".$Table."',".$row1['rowid'].",'SLS_YTD_FC','SLS_YTD')\"><font class='".$row1['GB_SLS_YTD_FC']."'>".$row1['SLS_YTD_FC']."</font></td>\n";
											$ret .= " 	<tr>\n";
											$ret .= "</table>\n";
											$ret .= "	</br>\n";
										}
									break;
								}
								$ret .= "</div>\n";
							} else {
								$ret .= "	<h1>Invalid Selection</h1>\n";
							}
						break;

						case "fixBadRecord":
							if (isset($_POST["table"]) && isset($_POST["rowid"]) && isset($_POST["field_name"]) && isset($_POST["field_readable_name"]))  {

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

								$Field_Name = $_POST["field_name"];
								$Field_Readable_Name = $_POST["field_readable_name"];
								$Table = $_POST["table"];
								$Rowid = $_POST["rowid"];

								$sql  = " SELECT " . $Field_Name . " as RTN_Field_Name," . $Field_Readable_Name . " as RTN_Readable_Field_Name";
								$sql .= " FROM nsa." . $Table;
								$sql .= " WHERE rowid=" . $Rowid;
								QueryDatabase($sql, $results);

								while ($row = mssql_fetch_assoc($results)) {
									$newValue = "001:" . str_pad(($row['RTN_Readable_Field_Name'] * 100),10,'0',STR_PAD_LEFT);
									
									$sql1  = "UPDATE nsa." . $Table;
									$sql1 .= " SET " . $Field_Name . " = '" . $newValue . "'";
									$sql1 .= " WHERE rowid=" . $Rowid;
									//error_log("SQL1: " . $sql1);
									QueryDatabase($sql1, $results1);

									$ret .= $newValue;
								}
				
								$sql = "SET ANSI_NULLS OFF";
								QueryDatabase($sql, $results);
								$sql = "SET ANSI_WARNINGS OFF";
								QueryDatabase($sql, $results);
								$sql = "SET QUOTED_IDENTIFIER OFF";
								QueryDatabase($sql, $results);
								$sql = "SET ANSI_PADDING OFF";
								QueryDatabase($sql, $results);
								$sql = "SET CONCAT_NULL_YIELDS_NULL OFF";
								QueryDatabase($sql, $results);

							}
						break;

					}//End of Switch	
				}

				echo json_encode(array("returnValue"=> $ret));

			} else {
				echo json_encode(array("returnValue"=> '<h1>Invalid Permissions</h1>'));
			}
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}


function str_lreplace($needle, $replace, $haystack) {
    $pos = strrpos($haystack, $needle);

    if($pos !== false) {
        $haystack = substr_replace($haystack, $replace, $pos, strlen($needle));
    }
    return $haystack;
}

?>
