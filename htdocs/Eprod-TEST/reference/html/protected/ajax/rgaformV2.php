<?php

	$DEBUG = 1;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}
	
	$DB_TEST_FLAG = "";
	if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
 		$DB_TEST_FLAG = "_TEST";
 	}

	require_once("../procfile.php");
	require_once('../classes/tc_calendar.php');
	require_once('../classes/mail.class.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);
			$ret = '';
			$READONLY = "READONLY style='background-color:#D0D0D0;'";
			if (strpos($UserRow['EMP_ROLE'], ":RGA-ISO-ADMIN:") !== FALSE ){
				$READONLY = "";
			}

			if (isset($_POST["action"])) {
				$Div = "mainDiv";
				$Action = $_POST["action"];

				if (isset($_POST["divclose"])) {
					$ret .= "		<button onClick=\"disablePopup(". $Div .")\">CLOSE</button>\n<br>"; //close popup button
				}

				$sql = "SET ANSI_NULLS ON";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS ON";
				QueryDatabase($sql, $results);

				switch($Action) {

					case "refresh_mainDiv":
						$rgaStatus = $_POST["rgaStatus"];
						$isoStatus = $_POST["isoStatus"];
						$classification = $_POST["classification"];
						$rgaNumber = $_POST["rgaNumber"];
						$custNo = $_POST["custNo"];
						$custName = $_POST["custName"];
						$numResults = $_POST["numResults"];
						$PONo = $_POST["PONo"];
						$OrdNo = $_POST["OrdNo"];
						$itemID = $_POST["itemID"];
						$CreatedBy = $_POST["CreatedBy"];
						$SortBy = $_POST["sortField"];
						$SortDir = $_POST["sortDir"];
						
						if ($rgaStatus == 'ALL') {
							$rgaStatus = '%';
						}

						if ($classification == 'ALL') {
							$classification = '%';
						}

						if ($rgaNumber == 'ALL') {
							$rgaNumber = '%';
						}

						if ($custNo == 'ALL') {
							$custNo = '%';
						}

						if ($custName == 'ALL') {
							$custName = '%';
						}

						if ($isoStatus == 'ALL') {
							$isoStatus = '%';
						}

						if ($PONo == 'ALL') {
							$PONo = '%';
						}

						if ($OrdNo == 'ALL') {
							$OrdNo = '%';
						}

						if ($itemID == 'ALL') {
							$itemID = '%';
						}

						if ($CreatedBy == 'ALL') {
							$CreatedBy = '%';
						}

						if (strpos($UserRow['EMP_ROLE'], ":RGA-CS:") !== FALSE ) {
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 	<th colspan=12>RGA Requests for Review</th>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<th onclick=\"changeSort('RGA_NUMBER')\">RGA #</th>\n";
							$ret .= " 		<th onclick=\"changeSort('CLASSIFICATION')\">Classification</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_CUST')\">Customer #</th>\n";
							$ret .= "		<th onclick=\"changeSort('NAME_CUST')\">Customer Name</th>";
							$ret .= " 		<th onclick=\"changeSort('ID_ORD')\">Order Number</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_PO')\">PO Number</th>\n";
							$ret .= " 		<th onclick=\"changeSort('rbDATE_ADD')\">Date Created</th>\n";
							$ret .= " 		<th onclick=\"changeSort('rbDAYS_OPEN')\">Days Open</th>\n";
							$ret .= " 		<th onclick=\"changeSort('rbDAYS_OPEN')\">TickTock!</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_USER_ADD')\">Created By</th>\n";
							//$ret .= " 		<th onclick=\"changeSort('RGA_ISO_STATUS')\">ISO Status</th>\n";
							$ret .= " 		<th onclick=\"changeSort('RGA_STATUS')\">RGA Status</th>\n";
							$ret .= " 	</tr>\n";

							$sql  = "select distinct";
							if ($numResults <> 'ALL') {
								$sql .= " top " . $numResults . " ";
							}
							$sql .= " convert(varchar(16),b.DATE_ADD,121) as rbDATE_ADD, ";
							$sql .= " DATEDIFF(d, b.DATE_ADD, getDate()) as rbDAYS_OPEN, ";
							$sql .= " b.CLASSIFICATION, ";
							$sql .= " b.RGA_NUMBER, ";
							$sql .= " b.RGA_STATUS, ";
							$sql .= " b.ID_CUST, ";
							$sql .= " b.NAME_CUST, ";
							$sql .= " b.ID_USER_ADD, ";
							$sql .= " cast(substring( ";
							$sql .= " ( ";
							$sql .= "  select '~'+i1.RGA_ISO_STATUS as [text()] ";
							$sql .= "  from nsa.RGA_INVEST" . $DB_TEST_FLAG . " i1 ";
							$sql .= "  where i1.RGA_BASE_rowid = i2.RGA_BASE_rowid ";
							$sql .= "  order by i1.SEQ_INVEST asc ";
							$sql .= "  for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as RGA_ISO_STATUS, ";

							$sql .= " cast(substring( ";
							$sql .= " (  select '~'+l1.ID_PO as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_PO, ";
							$sql .= " cast(substring( ";
							$sql .= " (   select '~'+l1.ID_ORD as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_ORD, ";
							$sql .= " cast(substring( ";
							$sql .= " (   select '~'+l1.ID_ITEM as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_ITEM ";							

							$sql .= " from  nsa.RGA_BASE" . $DB_TEST_FLAG . " b ";
							$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " i2 ";
							$sql .= " on b.rowid = i2.RGA_BASE_rowid ";
							$sql .= " left join nsa.RGA_LINE" . $DB_TEST_FLAG . " l ";
							$sql .= " on b.rowid = l.RGA_BASE_rowid ";
							$sql .= " where b.RGA_STATUS like '".$rgaStatus ."' ";
							if ($isoStatus == '%') {
								$sql .= " and (i2.RGA_ISO_STATUS like '".$isoStatus ."' OR i2.RGA_ISO_STATUS is NULL)";	
							} else {
								$sql .= " and (i2.RGA_ISO_STATUS like '".$isoStatus ."')";	
							}
							$sql .= " and b.RGA_NUMBER like '".$rgaNumber ."' ";
							$sql .= " and b.CLASSIFICATION like '".$classification ."' ";
							$sql .= " and b.ID_CUST like '".$custNo ."' ";
							$sql .= " and b.NAME_CUST like '%".$custName ."%' ";
							//$sql .= " and (l.ID_PO like '".$PONo ."' OR l.ID_PO is NULL)";
							//$sql .= " and (l.ID_ORD like '".$OrdNo ."' OR l.ID_ORD is NULL)";
							//$sql .= " and (l.ID_ITEM like '".$itemID ."' OR l.ID_ITEM is NULL)";
							if ($PONo == '%') {
								$sql .= " and (l.ID_PO like '".$PONo ."' OR l.ID_PO is NULL)";
							} else {
								$sql .= " and (l.ID_PO like '".$PONo ."')";
							}
							if ($OrdNo == '%') {
								$sql .= " and (l.ID_ORD like '".$OrdNo ."' OR l.ID_ORD is NULL)";
							} else {
								$sql .= " and (l.ID_ORD like '".$OrdNo ."')";
							}
							if ($itemID == '%') {
								$sql .= " and (l.ID_ITEM like '".$itemID ."%' OR l.ID_ITEM is NULL)";
							} else {
								$sql .= " and (l.ID_ITEM like '".$itemID ."%')";
							}
							$sql .= " and (b.ID_USER_ADD like '".$CreatedBy ."')";
							//$sql .= " order by rbDATE_ADD desc";
							$sql .= " order by " . $SortBy . " " . $SortDir;
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr class='dbc'>\n";
								$ret .= " 		<td style='cursor: hand' title='Show " . $row['RGA_NUMBER'] . " Details' onclick=\"goToReviewRequestPopUp('" . $row['RGA_NUMBER'] . "')\">" . $row['RGA_NUMBER'] . "</td>\n";
								$ret .= " 		<td>" . $row['CLASSIFICATION'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_CUST'] . "</td>\n";
								$ret .= " 		<td>" . $row['NAME_CUST'] . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['ID_ORD']) . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['ID_PO']) . "</td>\n";
								$ret .= " 		<td>" . $row['rbDATE_ADD'] . "</td>\n";
								$ret .= " 		<td>" . $row['rbDAYS_OPEN'] . "</td>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
								//$ret .= " 		<td>" . str_replace("~", "<br>", $row['RGA_ISO_STATUS']) . "</td>\n";
								$ret .= " 		<td>" . $row['RGA_STATUS'] . "</td>\n";
								$ret .= " 	</tr>\n";
							}
							$ret .= " </table>\n";
							$ret .= "	</br>\n";
						}

						if (strpos($UserRow['EMP_ROLE'], ":RGA-SHIP:") !== FALSE ) {
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th colspan=8>RGA Shipping Requests for Review</th>\n";
							$ret .= " 	</tr>\n";
							$ret .= " 	<tr>\n";
							$ret .= " 		<th onclick=\"changeSort('RGA_NUMBER')\">RGA #</th>\n";
							$ret .= " 		<th onclick=\"changeSort('CLASSIFICATION')\">Classification</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_CUST')\">Customer #</th>\n";
							$ret .= "		<th onclick=\"changeSort('NAME_CUST')\">Customer Name</th>";
							$ret .= "		<th onclick=\"changeSort('ID_ITEM')\">Item Number</th>";
							$ret .= " 		<th onclick=\"changeSort('rbDATE_ADD')\">Date Created</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_USER_ADD')\">Created By</th>\n";
							//$ret .= " 		<th>ISO Status</th>\n";
							$ret .= " 		<th onclick=\"changeSort('RGA_STATUS')\">RGA Status</th>\n";
							$ret .= " 	</tr>\n";

							$sql  = "select distinct";
							if ($numResults <> 'ALL') {
								$sql .= " top " . $numResults . " ";
							}
							$sql .= " convert(varchar(16),b.DATE_ADD,121) as rbDATE_ADD, ";
							$sql .= " DATEDIFF(d, b.DATE_ADD, getDate()) as rbDAYS_OPEN, ";
							$sql .= " b.RGA_NUMBER, ";
							$sql .= " b.RGA_STATUS, ";
							$sql .= " b.CLASSIFICATION, ";
							$sql .= " b.ID_CUST, ";
							$sql .= " b.NAME_CUST, ";
							$sql .= " b.ID_USER_ADD, ";
							$sql .= " cast(substring( ";
							$sql .= " ( ";
							$sql .= "  select '~'+i1.RGA_ISO_STATUS as [text()] ";
							$sql .= "  from nsa.RGA_INVEST" . $DB_TEST_FLAG . " i1 ";
							$sql .= "  where i1.RGA_BASE_rowid = i2.RGA_BASE_rowid ";
							$sql .= "  order by i1.SEQ_INVEST asc ";
							$sql .= "  for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as RGA_ISO_STATUS, ";
							
							$sql .= " cast(substring( ";
							$sql .= " (  select '~'+l1.ID_PO as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_PO, ";
							$sql .= " cast(substring( ";
							$sql .= " (   select '~'+l1.ID_ORD as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_ORD, ";
							$sql .= " cast(substring( ";
							$sql .= " (   select '~'+l1.ID_ITEM as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_ITEM ";

							$sql .= " from  nsa.RGA_BASE" . $DB_TEST_FLAG . " b ";
							$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " i2 ";
							$sql .= " on b.rowid = i2.RGA_BASE_rowid ";
							$sql .= " left join nsa.RGA_LINE" . $DB_TEST_FLAG . " l ";
							$sql .= " on b.rowid = l.RGA_BASE_rowid ";
							$sql .= " where b.RGA_STATUS like '".$rgaStatus ."' ";
							if ($isoStatus == '%') {
								$sql .= " and (i2.RGA_ISO_STATUS like '".$isoStatus ."' OR i2.RGA_ISO_STATUS is NULL)";	
							} else {
								$sql .= " and (i2.RGA_ISO_STATUS like '".$isoStatus ."')";	
							}
							$sql .= " and b.RGA_NUMBER like '".$rgaNumber ."' ";
							$sql .= " and b.CLASSIFICATION like '".$classification ."' ";
							$sql .= " and b.ID_CUST like '".$custNo ."' ";
							$sql .= " and b.NAME_CUST like '%".$custName ."%' ";
							//$sql .= " and (l.ID_PO like '".$PONo ."' OR l.ID_PO is NULL)";
							//$sql .= " and (l.ID_ORD like '".$OrdNo ."' OR l.ID_ORD is NULL)";
							//$sql .= " and (l.ID_ITEM like '".$itemID ."' OR l.ID_ITEM is NULL)";
							if ($PONo == '%') {
								$sql .= " and (l.ID_PO like '".$PONo ."' OR l.ID_PO is NULL)";
							} else {
								$sql .= " and (l.ID_PO like '".$PONo ."')";
							}
							if ($OrdNo == '%') {
								$sql .= " and (l.ID_ORD like '".$OrdNo ."' OR l.ID_ORD is NULL)";
							} else {
								$sql .= " and (l.ID_ORD like '".$OrdNo ."')";
							}
							if ($itemID == '%') {
								$sql .= " and (l.ID_ITEM like '".$itemID ."%' OR l.ID_ITEM is NULL)";
							} else {
								$sql .= " and (l.ID_ITEM like '".$itemID ."%')";
							}
							$sql .= " and (b.ID_USER_ADD like '".$CreatedBy ."')";
							//$sql .= " order by rbDATE_ADD desc";
							$sql .= " order by " . $SortBy . " " . $SortDir;
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr class='dbc'>\n";
								$ret .= " 		<td style='cursor: hand' title='Show " . $row['RGA_NUMBER'] . " Details' onclick=\"goToReviewShippingRequestPopUp('" . $row['RGA_NUMBER'] . "')\">" . $row['RGA_NUMBER'] . "</td>\n";
								$ret .= " 		<td>" . $row['CLASSIFICATION'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_CUST'] . "</td>\n";
								$ret .= "		<td>" . $row['NAME_CUST'] . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['ID_ITEM']) . "</td>\n";
								$ret .= " 		<td>" . $row['rbDATE_ADD'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
								//$ret .= " 		<td>" . str_replace("~", "<br>", $row['RGA_ISO_STATUS']) . "</td>\n";
								$ret .= " 		<td>" . $row['RGA_STATUS'] . "</td>\n";
								$ret .= " 	</tr>\n";
							}
							$ret .= " </table>\n";
							$ret .= "	</br>\n";
						}

						if (strpos($UserRow['EMP_ROLE'], ":RGA-ISO:") !== FALSE || strpos($UserRow['EMP_ROLE'], ":RGA-FINANCE:") !== FALSE) {
							$ret .= " <table class='sample'>\n";
							$ret .= " 	<tr class='blueHeader'>\n";
							$ret .= " 		<th colspan=12>RGA ISO Requests for Review</th>\n";
							$ret .= " 	</tr>\n";							
							$ret .= " 	<tr>\n";
							$ret .= " 		<th onclick=\"changeSort('RGA_NUMBER')\">RGA #</th>\n";
							$ret .= " 		<th onclick=\"changeSort('CLASSIFICATION')\">Classification</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_CUST')\">Customer #</th>\n";
							$ret .= "		<th onclick=\"changeSort('NAME_CUST')\">Customer Name</th>";
							$ret .= " 		<th onclick=\"changeSort('ID_ORD')\">Order Number</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_PO')\">PO Number</th>\n";
							$ret .= " 		<th onclick=\"changeSort('rbDATE_ADD')\">Date Created</th>\n";
							$ret .= " 		<th onclick=\"changeSort('rbDAYS_OPEN')\">Days Open</th>\n";
							$ret .= " 		<th onclick=\"changeSort('rbDAYS_OPEN')\">TickTock!</th>\n";
							$ret .= " 		<th onclick=\"changeSort('ID_USER_ADD')\">Created By</th>\n";
							$ret .= " 		<th onclick=\"changeSort('RGA_ISO_STATUS')\">ISO Status</th>\n";
							$ret .= " 		<th onclick=\"changeSort('RGA_STATUS')\">RGA Status</th>\n";
							$ret .= " 	</tr>\n";

							$sql  = "select distinct";
							if ($numResults <> 'ALL') {
								$sql .= " top " . $numResults . " ";
							}
							$sql .= " convert(varchar(16),b.DATE_ADD,121) as rbDATE_ADD, ";
							$sql .= " DATEDIFF(d, b.DATE_ADD, getDate()) as rbDAYS_OPEN, ";
							$sql .= " b.RGA_NUMBER, ";
							$sql .= " b.RGA_STATUS, ";
							$sql .= " b.CLASSIFICATION, ";
							$sql .= " b.ID_CUST, ";
							$sql .= " b.NAME_CUST, ";
							$sql .= " b.ID_USER_ADD, ";
							$sql .= " cast(substring( ";
							$sql .= " ( ";
							$sql .= "  select '~'+i1.RGA_ISO_STATUS as [text()] ";
							$sql .= "  from nsa.RGA_INVEST" . $DB_TEST_FLAG . " i1 ";
							$sql .= "  where i1.RGA_BASE_rowid = i2.RGA_BASE_rowid ";
							$sql .= "  order by i1.SEQ_INVEST asc ";
							$sql .= "  for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as RGA_ISO_STATUS, ";

							$sql .= " cast(substring( ";
							$sql .= " (  select '~'+l1.ID_PO as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_PO, ";
							$sql .= " cast(substring( ";
							$sql .= " (   select '~'+l1.ID_ORD as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_ORD, ";
							$sql .= " cast(substring( ";
							$sql .= " (   select '~'+l1.ID_ITEM as [text()] ";
							$sql .= "   from nsa.RGA_LINE" . $DB_TEST_FLAG . " l1 ";
							$sql .= "   where l1.RGA_BASE_rowid = b.rowid ";
							$sql .= "   order by l1.SEQ_LINE_RGA asc ";
							$sql .= "   for XML PATH ('') ";
							$sql .= " ),2,1000) as varchar(1000)) as ID_ITEM ";

							$sql .= " from  nsa.RGA_BASE" . $DB_TEST_FLAG . " b ";
							$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " i2 ";
							$sql .= " on b.rowid = i2.RGA_BASE_rowid ";
							$sql .= " left join nsa.RGA_LINE" . $DB_TEST_FLAG . " l ";
							$sql .= " on b.rowid = l.RGA_BASE_rowid ";
							$sql .= " where b.RGA_STATUS like '".$rgaStatus ."' ";
							if ($isoStatus == '%') {
								$sql .= " and (i2.RGA_ISO_STATUS like '".$isoStatus ."' OR i2.RGA_ISO_STATUS is NULL)";	
							} else {
								$sql .= " and (i2.RGA_ISO_STATUS like '".$isoStatus ."')";	
							}
							$sql .= " and b.RGA_NUMBER like '".$rgaNumber ."' ";
							$sql .= " and b.CLASSIFICATION like '".$classification ."' ";
							$sql .= " and b.ID_CUST like '".$custNo ."' ";
							$sql .= " and b.NAME_CUST like '%".$custName ."%' ";
							//$sql .= " and (l.ID_PO like '".$PONo ."' OR l.ID_PO is NULL)";
							//$sql .= " and (l.ID_ORD like '".$OrdNo ."' OR l.ID_ORD is NULL)";
							//$sql .= " and (l.ID_ITEM like '".$itemID ."' OR l.ID_ITEM is NULL)";
							if ($PONo == '%') {
								$sql .= " and (l.ID_PO like '".$PONo ."' OR l.ID_PO is NULL)";
							} else {
								$sql .= " and (l.ID_PO like '".$PONo ."')";
							}
							if ($OrdNo == '%') {
								$sql .= " and (l.ID_ORD like '".$OrdNo ."' OR l.ID_ORD is NULL)";
							} else {
								$sql .= " and (l.ID_ORD like '".$OrdNo ."')";
							}
							if ($itemID == '%') {
								$sql .= " and (l.ID_ITEM like '".$itemID ."%' OR l.ID_ITEM is NULL)";
							} else {
								$sql .= " and (l.ID_ITEM like '".$itemID ."%')";
							}
							$sql .= " and (b.ID_USER_ADD like '".$CreatedBy ."')";
							//$sql .= " order by rbDATE_ADD desc";
							$sql .= " order by " . $SortBy . " " . $SortDir;
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$ret .= " 	<tr class='dbc'>\n";
								$ret .= " 		<td style='cursor: hand' title='Show " . $row['RGA_NUMBER'] . " Details' onclick=\"goToReviewISORequestPopUp('" . $row['RGA_NUMBER'] . "')\">" . $row['RGA_NUMBER'] . "</td>\n";
								$ret .= " 		<td>" . $row['CLASSIFICATION'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_CUST'] . "</td>\n";
								$ret .= "		<td>" . $row['NAME_CUST'] . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['ID_ORD']) . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['ID_PO']) . "</td>\n";
								$ret .= " 		<td>" . $row['rbDATE_ADD'] . "</td>\n";
								$ret .= " 		<td>" . $row['rbDAYS_OPEN'] . "</td>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['RGA_ISO_STATUS']) . "</td>\n";
								$ret .= " 		<td>" . $row['RGA_STATUS'] . "</td>\n";








/*
								$ret .= " 		<td>" . $row['CLASSIFICATION'] . "</td>\n";
								$ret .= " 		<td>" . $row['ID_CUST'] . "</td>\n";
								$ret .= " 		<td>" . $row['NAME_CUST'] . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['ID_ORD']) . "</td>\n";
								$ret .= " 		<td>" . str_replace("~", "<br>", $row['ID_PO']) . "</td>\n";
								$ret .= " 		<td>" . $row['rbDATE_ADD'] . "</td>\n";
								$ret .= " 		<td>" . $row['rbDAYS_OPEN'] . "</td>\n";
								$ret .= " 		<td></td>\n";
								$ret .= " 		<td>" . $row['ID_USER_ADD'] . "</td>\n";
								//$ret .= " 		<td>" . str_replace("~", "<br>", $row['RGA_ISO_STATUS']) . "</td>\n";
								$ret .= " 		<td>" . $row['RGA_STATUS'] . "</td>\n";
*/




								$ret .= " 	</tr>\n";
							}
							$ret .= " </table>\n";
							$ret .= "	</br>\n";
						}
					break;



					case "popErrorType":
						$dept = $_POST["dept"];
						$workcenter = $_POST["workcenter"];

						$sql  = " SELECT ERROR_TYPE";
						$sql .= " from nsa.RGA_WC_ERRORS";
						$sql .= " where DEPT = '".$dept."' ";
						$sql .= " and WC_GROUP = '" .$workcenter. "'";
						QueryDatabase($sql, $results);
						$ret .= "			<option value=''>--Select--</option>";
						while ($row = mssql_fetch_assoc($results)){
							$ret .= "			<option value='" . $row['ERROR_TYPE'] . "'>" . $row['ERROR_TYPE'] . "</option>";
						}
					break;



					case "popWorkCenters":
						$dept = $_POST["dept"];

						$sql  = " SELECT distinct WC_GROUP";
						$sql .= " from nsa.RGA_WC_ERRORS";
						$sql .= " where DEPT = '" .$dept. "'";
						QueryDatabase($sql, $results);
						$ret .= "			<option value=''>--Select--</option>";
						while ($row = mssql_fetch_assoc($results)){
							$ret .= "			<option value='" . $row['WC_GROUP'] . "'>" . $row['WC_GROUP'] . "</option>";
						}
					break;



					case "getcustInfo":
						if (isset($_POST["custNumber"]) && isset($_POST["field"])) {
							$field = $_POST["field"];
							$custNumber = $_POST["custNumber"];

							$sql = " SELECT " . $field . " as retval";
							$sql .= " from nsa.CUSMAS_SOLDTO ";
							$sql .= " where ltrim(ID_CUST) = '" . $custNumber . "'";
							QueryDatabase($sql, $results);
							error_log("SQL: " . $sql);

							if (mssql_num_rows($results) > 0) {
								while ($row = mssql_fetch_assoc($results)) {
									if ($field == 'NAME_CUST') {
										$ret = "<input id='txt_" . $field ."' type=text value='" . trim($row['retval']) . "' size=30>";	
									} else {
										$ret = "<input id='txt_" . $field ."' type=text value='" . trim($row['retval']) . "'>";
									}
								}
							} else {
								$ret = "<input id='txt_" . $field ."' type=text value=''>";
							}
						}
					break;					

					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//BASE RECORD STUFF
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					case "form_newreq":	
						$a ='xx';
						//$ret .= "<form name='form_newreq' action=\"javascript:insertNewBase()\" >";
						$ret .= "<table width='850' >\n";
						$ret .= "	<tr class='blueHeader'>\n";
						$ret .= "		<th colspan='4'><left><img src='/images/ThinkNSA_logo.png' width='70' height='50'></left> <center>RGA & Customer Complaint</center></th>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>RGA Number:*</td>\n";
						$ret .= "		<td><input id='txt_rgaNumber' disabled name='txt_rgaNumber' title='RGA Number Will Automatically Generate Upon Submit' type='text' value='" .date('y-m-') . "-xx' READONLY style='background-color:#D0D0D0;'></input></td>\n";
						$ret .= "		<td>Date Issued:*</td>\n";
						$ret .= "		<td><input id='txt_date' name='txt_date' type='text' value=" . date('Y-m-d') . " ></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Customer #:*</td>\n";
						$ret .= "		<td><input id='txt_customerID' name='txt_customerID' type='text' onblur=\"getcustInfo()\" maxlength='6'></input></td>\n";
						$ret .= "		<td>Customer Name:*</td>\n";
						$ret .= "		<td><div id='div_txt_NAME_CUST'><input id='txt_NAME_CUST' name='txt_NAME_CUST' type='text' maxlength='30' size=30></input></div></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>City:*</td>\n";
						$ret .= "		<td><div id='div_txt_CITY'><input id='txt_CITY' name='txt_CITY' type='text' maxlength='15'></input></div></td>\n";
						$ret .= "		<td>State:*</td>\n";
						$ret .= "		<td><div id='div_txt_ID_ST'><input id='txt_ID_ST' name='txt_ID_ST' type='text' maxlength='2'></input></div></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Prov:</td>\n";
						$ret .= "		<td><div id='div_txt_PROV'><input id='txt_PROV' name='txt_PROV' type='text' maxlength='30'></input></div></td>\n";
						$ret .= "		<td>Country:*</td>\n";
						$ret .= "		<td><div id='div_txt_COUNTRY'><input id='txt_COUNTRY' name='txt_COUNTRY' type='text' maxlength='30'></input></div></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Contact Name:*</td>\n";
						$ret .= "		<td><div id='div_txt_NAME_CONTACT_CUST'><input id='txt_NAME_CONTACT_CUST'name='txt_NAME_CONTACT_CUST' type='text' maxlength='25'></input></div></td>\n";
						$ret .= "		<td>Phone #:</td>\n";
						$ret .= "		<td><div id='div_txt_PHONE'><input id='txt_PHONE' name='txt_PHONE' type='text' maxlength='20'></input></div></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Email:*</td>\n";
						$ret .= "		<td><input id='txt_email' name='txt_email' type='email' size='35' maxlength='100'></input></td>\n";

						$ret .= "		<td>Classification:</td>\n";
						$ret .= "		<td><select id='select_classification' name='select_classification'>\n";
						$ClassificationMDArray = array(
							array("","--Select--"),
							array("NQE","Non-Quality Error"),
							array("PQE","Product Quality Error"),
						);
						for ($rowClassification = 0; $rowClassification < 3; $rowClassification++) {
							$SELECTED = '';
							$CURRENT = '';

							if ("" == trim($ClassificationMDArray[$rowClassification][0])) {
								$SELECTED = 'SELECTED';
							}
							$ret .= "				<option value='" . $ClassificationMDArray[$rowClassification][0] . "' " . $SELECTED . ">" . $ClassificationMDArray[$rowClassification][1] .  "</option>\n";
						}
						$ret .= "		</select></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbd' rowspan=4>\n";
						$ret .= "		<td colspan=4></td>\n";
						$ret .= "	</tr>\n";					

						for ($y = 1; $y <= 10; $y++) {
							if ($y==1) {
								$style = 'table-row';
							} else {
								$style = 'display:none;';
							}
							$z = $y+1;
							$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
							$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
							$ret .= "		<td>Order #:</td>\n";
							$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' maxlength='8'></input></td>\n";
							$ret .= "		<td>PO #:</td>\n";
							$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' size='30' maxlength='25'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
							$ret .= "		<td>Part #:</td>\n";
							$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' size='30' maxlength='30'></input></td>\n";
							$ret .= "		<td>Quantity:</td>\n";
							$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' maxlength='10'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_ord".$y.".4' style='".$style."'>\n";
							$ret .= "		<td>Invoice #:</td>\n";
							$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' maxlength='8'></input></td>\n";//added invoiceNumber to form
							$ret .= "		<td>Date Ship </br> (yyyy-mm-dd):</td>\n";
							$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
							$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','4')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','4','1')\">+Clone</font></td>\n";
							$ret .= "	</tr>\n";
						}

						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Issued by:</td>\n";
						$ret .= "		<td>" . $UserRow['ID_USER'] . "</td>\n";
						$ret .= "		<td>Authorized by:</td>\n";
						$ret .= "		<td><select id='select_auth' >\n";
						$ret .= "           <option value=''>-- Select --</option>\n";
						$ret .= "			<option value='CG2'>Chuck</option>\n";
						$ret .= "			<option value='DS'>Duane</option>\n";
						$ret .= "			<option value='MCF'>Micel</option>\n";
						$ret .= "			<option value='STG'>Sal</option>\n";
						$ret .= "			<option value='JG'>Joe</option>\n";
						$ret .= "			<option value='Stock'>N/A</option>\n";
						$ret .= "		</select></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Sales Manager:</td>\n";
						$ret .= "		<td><select id='selectSalesMgr' onchange=\"slsMgrToTerr()\" >\n";
						$ret .= "        	<option value=''>-- Select --</option>\n";

						//$sql = "SET ANSI_NULLS ON";
						//QueryDatabase($sql, $results);
						//$sql = "SET ANSI_WARNINGS ON";
						//QueryDatabase($sql, $results);

						$sql = " select NAME_SLSREP, ltrim(ID_SLSREP) as ID_SLSREP"; 
						$sql .=	" from nsa.tables_slsrep"; 
						$sql .=	" where ADDR_EMAIL is not NULL";
						$sql .=	" order by NAME_SLSREP asc";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)){
							$ret .= "			<option value='" . $row['ID_SLSREP'] . "_". $row['NAME_SLSREP'] ."'>" . $row['NAME_SLSREP'] . "</option>";
						}

						$ret .= "		</select></td>\n";
						$ret .= "		<td>Territory:</td>\n";
						$ret .= "		<td><input id='txt_territory' name='txt_territory' type='text' maxlength='3'></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Description of <br/>Request/Complaint:</td>\n";
						$ret .= "		<td colspan='3'><textarea id='txt_descr' name='txt_descr' cols='25' rows='7'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Expecting Return?:</td>\n";
						$ret .= "		<td>\n";
						$ret .= "			<select id='selectExpectingReturn' onchange=\"showHideShip()\" >\n";
						$ret .= "   	     	<option value=''>-- Select --</option>\n";
						$ret .= "   	     	<option value='Y'>Yes</option>\n";
						$ret .= "   	     	<option value='N'>No</option>\n";
						$ret .= "			</select>\n";
						$ret .= "		</td>\n";
						$ret .= "		<td colspan=2>\n";
						$ret .= "		</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						/*
						$ret .= "<table width='850'>\n";
						$ret .= "   <tr class='dbc'>\n";
						$ret .= "   <td colspan='3'><strong>Please Select One (Need to Select One)</strong></td>\n";
						$ret .= "   </tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Expecting Return?</td>\n";
						$ret .= "		<td><input type='radio' id='Yes' name='choice' value='Y' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\" >Yes</input></td>\n";
						$ret .= "		<td><input type='radio' id='No' name='choice' value='N' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">No</input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						*/
						/*
						$ret .= "<table width='850'>\n";
						$ret .= "   <tr class='dbc'>\n";
						$ret .= "   <td colspan='6'><strong>Please Select One (Need to Select One)</strong></td>\n";
						$ret .= "   </tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Complaint with Return?</td>\n";
						$ret .= "		<td><input type='radio' id='A' name='choice' value='A' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\" >A</input></td>\n";
						$ret .= "		<td>Complaint without Return?</td>\n";
						$ret .= "		<td><input type='radio' id='B' name='choice' value='B' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">B</input></td>\n";
						$ret .= "		<td>Customer Request?</td>\n";
						$ret .= "		<td><input type='radio' id='C' name='choice' value='C' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">C</input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						*/

						$ret .= "<table width='850'>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Follow-Up that<br /> Requires Action:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_followUp' name='txt_followUp' cols='55' ></textarea></td>\n";
						$ret .= "	</tr>\n";

						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Additional<br /> Information:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_add_info' name='txt_add_info' cols='55' ></textarea></td>\n";
						$ret .= "	</tr>\n";
						//$ret .= "	<tr class='dbc' style='display:none'>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>CC Email:</td>\n";
						$ret .= "		<td><input id='txt_email_notify_base' name='txt_email_notify_base' type='email' style = 'width: 500px;' value='' ></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td colspan=4>\n";
						$ret .= "			<div id='div_submitResp_insertNewBase' name='div_submitResp_insertNewBase'>\n";
						$ret .= " 			<input id='button_insertNewBase' name='button_insertNewBase' type='button' value='Submit' onclick=\"insertNewBase()\" DISABLED></input>\n";
						$ret .= " 			**Send Email?<select id='sel_Email_insertNewBase' name='sel_Email_insertNewBase' onchange=\"checkEnableButton('sel_Email_insertNewBase','button_insertNewBase')\">\n";
						$ret .= "   	        <option value=''>--Select--</option>\n";
						$ret .= "   	        <option value='Send'>Send</option>\n";
						$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
						$ret .= "			</select>\n";
						//$ret .= "			CC Email:<input id='txt_cc_insertNewBase' name='txt_cc_insertNewBase' type='text' DISABLED></input>\n";
						$ret .= "			</div>\n";
						//$ret .= "			<div id='div_submitResp_insertNewBase' name='div_submitResp_insertNewBase'></div>\n";
						$ret .= "		</td>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						//$ret .= "</form>";

						$ret .= "<table width='850' id='table_shipping' style='display:table;'>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<th colspan='4'>For Shipping Only</th>\n";
						$ret .= "	</tr>\n";

						for ($y = 1; $y <= 10; $y++) {
							if ($y==1) {
								$style = 'table-row';
							} else {
								$style = 'display:none;';
							}
							$z = $y +1;
							$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
							$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
							$ret .= "	</tr>\n";							
							$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
							$ret .= "       <td colspan=2></td>\n";
							$ret .= "		<td>Item Recieved:</td>\n";
							$ret .= "		<td><input id='txt_itemReceived".$y."' name='txt_itemReceived".$y."' width='500px' type='text' maxlength='50'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
							$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
							$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text'></input></td>\n";
							$ret .= "		<td>Quantity Received:</td>\n";
							$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."' type='text' maxlength='10'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."' >\n";
							$ret .= "		<td>Carrier:</td>\n";
							$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' maxlength='12'></input></td>\n";
							$ret .= "		<td>Tracking #:</td>\n";
							$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' maxlength='50'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbc' id='tr_itm".$y.".5' style='".$style."'>\n";
							$ret .= "		<td>Condition Received:</td>\n";
							$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' maxlength='50'></input></td>\n";
							$ret .= "		<td>Received by:</td>\n";
							$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' maxlength='50'></input></td>\n";
							$ret .= "	</tr>\n";
							$ret .=" 	<tr class='dbc' id='tr_itm".$y.".6' style='".$style."'>\n";
							$ret .= "		<td>Comments:</td>\n";
							$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' cols='55'></textarea></td>\n";
							$ret .= "	</tr>\n";
							$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
							$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','6','1')\">+Clone</font></td>\n";
							$ret .= "	</tr>\n";	
						}	

						$ret .= "	<tr class='dbc'>\n";
						//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' type='button' value='Submit' onClick=\"insertNewShip()\" ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
						$ret .= "		<td colspan=4>\n";
						$ret .= " 			<input id='button_insertNewShip' name='button_insertNewShip' type='button' value='Submit' onclick=\"insertNewShip()\" DISABLED></input>\n";
						$ret .= " 			**Send Email?<select id='sel_Email_insertNewShip' name='sel_Email_insertNewShip' onchange=\"checkEnableButton('sel_Email_insertNewShip','button_insertNewShip')\">\n";
						$ret .= "   	        <option value=''>--Select--</option>\n";
						$ret .= "   	        <option value='Send'>Send</option>\n";
						$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
						$ret .= "			</select>\n";
						$ret .= "			<div id='div_submitResp_insertNewShip' name='div_submitResp_insertNewShip'></div>\n";
						$ret .= "		</td>\n";						
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						//^only for Shipping when done hit Submit email Sal, Brian, Valentina, Sabrina and initiating CSR (Sabrina to double Check) 
						
						$ret .= "<table width='850' >\n";
						$ret .= "		<th colspan='4'>ISO investigation</th>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc' id='tr_RGA_RATING' style='diplay:table-row;'>\n";
						$ret .= "		<td>RGA Rating (choose one):</td>\n";
						$ret .= "		<td><input type='radio' id='1' name='level' value='1'>Level 1</input></td>\n";
						$ret .= "		<td><input type='radio' id='2' name='level' value='2'>Level 2</input></td>\n";
						$ret .= "		<td><input type='radio' id='3' name='level' value='3'>Level 3</input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Findings:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_findings' name='txt_findings' cols='55'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Cause:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_cause' name='txt_cause' cols='55'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Containment:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_contain' name='txt_contain' cols='55'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Correction:</td>\n";
						$ret .= "		<td colspan=3><textarea id='txt_corr' name='txt_corr' cols='55'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";
						$ret .= "<table width='850' >\n";
						$ret .= "	<th colspan='7'></th>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td><strong>Action Needed:</strong></td>\n";
						$ret .= "		<td style='text-align:left;'>\n";
						$ret .= "			<input type='checkbox' id='action_Rework' name='action_Rework' value=':Rework:'>Rework</input><br />\n";
						$ret .= "			<input type='checkbox' id='action_Replace' name='action_Replace' value=':Replace:'>Replace</input><br />\n";
						$ret .= "			<input type='checkbox' id='action_NonStk-Sample' name='action_NonStk-Sample' value=':NonStk-Sample:'>NonStock/Sample</input><br />\n";
						$ret .= "			<input type='checkbox' id='action_Credit' name='action_Credit' value=':Credit:'>Credit</input><br />\n";
						$ret .= "			<input type='checkbox' id='action_Other' name='action_Other' value=':Other:'>Other</input>\n";
						$ret .= "		</td>\n";
						$ret .= "		<td colspan=5><textarea id='txt_desc' name='txt_desc'></textarea></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td colspan=2>Is a Corrective Action Request (CAR) required? (select one)</td>\n";
						$ret .= "		<td><input type='radio' id='N' name='option' value='N'>No</input></td>\n";
						$ret .= "		<td><input type='radio' id='Y' name='option' value='Y'>Yes</input></td>\n";
						$ret .= "		<td>CAR #</td>\n";
						$ret .= "		<td colspan=2><input id='txt_carNumber' name='txt_carNumber' type='text'></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td colspan=2>Proposed action approved by:</td>\n";
						$ret .= "		<td colspan=2><input id='txt_approve' name='txt_approve' type='text' maxlength='30'></input></td>\n";
						$ret .= "		<td>Date (yyyy-mm-dd):</td>\n";
						$ret .= "		<td colspan=2><input id='txt_dateSubmit' name='txt_dateSubmit' type='text'></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";

						$ret .= "<table width='850' >\n";
						$ret .= " 	<th colspan='4'>For Tracking Only</th>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Department:</td>\n";
						$ret .= "		<td><select id='select_dept' name='select_dept' onchange=\"checkDept()\" >\n";
						$ret .= "           <option value=''>-- Select --</option>\n";

						$sql  = " SELECT distinct DEPT";
						$sql .= " FROM nsa.RGA_WC_ERRORS";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)){
							$ret .= "			<option value='" . $row['DEPT'] . "'>" . $row['DEPT'] . "</option>";
						}
						$ret .= "		 </select></td>\n";

						$ret .= "		<td>Request/Error:</td>\n";
						$ret .= "		<td><select id='select_err'>\n";
						$ret .= "           <option value=''>-- Select --</option>\n";
						$ret .= "			<option value='Error'>Error</option>\n";
						$ret .= "			<option value='Request'>Request</option>\n";
						$ret .= "			<option value='Dispute'>Dispute</option>\n";
						$ret .= "		</select></td>\n";
						$ret .= "	</tr>\n";					
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Workcenter:</td>";
						$ret .= "		<td><select id='select_workcenter' name='select_workcenter' onchange=\"checkWorkcenter()\"\m >";
						$ret .= "        	<option value=''>-- Select --</option>\n";
						/*
						$sql = " select distinct WC_GROUP";
						$sql .= " from nsa.RGA_WC_ERRORS";
						$sql .= " where DEPT = 'PRODUCTION'";
						QueryDatabase($sql, $results);
						while ($row = mssql_fetch_assoc($results)){
							$ret .= "			<option value='" . $row['WC_GROUP'] . "'>" . $row['WC_GROUP'] . "</option>";
						}*/
						$ret .= "		</select></td>\n";

						$ret .= "		<td>Credit Invoice #:</td>\n";
						$ret .= "		<td><input id='txt_invoice' name='txt_invoice' type='text' maxlength='8'></input></td>\n";
						$ret .= "	<tr class='dbc'>\n";	
						$ret .= "		<td>Team/Individual:</td>\n";
						$ret .= "		<td><input id='txt_team' name='txt_team' type='text' maxlength='25'></input></td>\n";					
						$ret .= "		<td>Component Costs:</td>\n";
						$ret .= "		<td><input id='txt_compCost' name='txt_compCost' type='text'></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Error Type:</td>\n";
						$ret .= "		<td><select id='select_errorType' name='select_errorType' >";
						$ret .= "		</select>";
						$ret .= "		<td>Labor Costs:</td>\n";
						$ret .= "		<td><input id='txt_laborCost' name='txt_laborCost' type='text'></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Vendor ID:</td>\n";
						$ret .= "		<td><input id='txt_vendor' name='txt_vendor' type='text' maxlength='6'></input></td>\n";					
						$ret .= "		<td>Shipping Costs:</td>\n";
						$ret .= "		<td><input id='txt_shipCost' name='txt_shipCost' type='text'></input></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>Part #:</td>\n";
						$ret .= "		<td><input id='txt_isoPartNumber' name='txt_isoPartNumber' type='text' maxlength='30'></input></td>\n";
						$ret .= "		<td>Total RGA Costs:</td>\n";
						$ret .= "		<td></td>\n";						
						$ret .= "	<tr class='dbc'>\n";
						$ret .= "		<td>ISO Status:</td>\n";
						$ret .= "		<td><select id='select_iso_status' name='select_iso_status' >\n";
						$ret .= "           <option value=''>-- Select --</option>\n";
						$ret .= "           <option value='Drafted'>Drafted</option>\n";
						$ret .= "           <option value='Pending Extenuating Issues'>Pending Extenuating Issues</option>\n";
						$ret .= "           <option value='Waiting for Approval'>Waiting for Approval</option>\n";
						$ret .= "           <option value='Waiting for Production'>Waiting for Production</option>\n";
						$ret .= "           <option value='Waiting for Customer Service'>Waiting for Customer Service</option>\n";
						$ret .= "           <option value='Waiting for Inventory Audit'>Waiting for Inventory Audit</option>\n";
						$ret .= "           <option value='Waiting for Manufacturer Response'>Waiting for Manufacturer Response</option>\n";
						$ret .= "           <option value='Waiting for Pricing Info'>Waiting for Pricing Info</option>\n";
						$ret .= "           <option value='Waiting for Rework/Replacement Number'>Waiting for Rework/Replacement Number</option>\n";
						$ret .= "           <option value='Working with Production Development'>Working with Production Development</option>\n";
						$ret .= "           <option value='Working with Purchasing'>Working with Purchasing</option>\n";
						$ret .= "           <option value='Closed'>Closed</option>\n";
						$ret .= "		</select></td>\n";
						$ret .= "		<td>RGA Status:</td>\n";
						$ret .= "		<td><select id='select_rga_status' name='select_rga_status' >\n";
						$ret .= "           <option value=''>-- Select --</option>\n";
						$ret .= "			<option selected value='Open, Waiting for Return'>Open, Waiting for Return</option>\n";
						$ret .= "			<option selected value='Open, Response to Customer Required'>Open, Response to Customer Required</option>\n";
						$ret .= "			<option selected value='Open, Inspection Required'>Open, Inspection Required</option>\n";
						$ret .= "			<option selected value='ISO Action Required'>ISO Action Required</option>\n";
						$ret .= "           <option value='Cancelled'>Cancelled</option>\n";
						$ret .= "           <option value='Closed'>Closed</option>\n";
						//$ret .= "           <option value='Open'>Open</option>\n";
						//$ret .= "           <option value='Pending'>Pending Investigation</option>\n";
						//$ret .= "           <option value='Cancelled'>Cancelled</option>\n";
						//$ret .= "           <option value='Closed'>Closed</option>\n";
						$ret .= "		</select></td>\n";
						$ret .= "	</tr>\n";
						$ret .= "	<tr class='dbc'>\n";
						//$ret .= "		<td colspan=4><input id='button_SubmitNew2' name='button_SubmitNew2S' type='button' value='Submit' onClick=\"insertNewInvest()\"></input><div id='div_submitResp2' name='div_submitResp2'></div></td>\n";
						$ret .= "		<td colspan=4>\n";
						$ret .= " 			<input id='button_insertNewInvest' name='button_insertNewInvest' type='button' value='Submit' onclick=\"insertNewInvest()\" DISABLED></input>\n";
						$ret .= " 			**Send Email?<select id='sel_Email_insertNewInvest' name='sel_Email_insertNewInvest' onchange=\"checkEnableButton('sel_Email_insertNewInvest','button_insertNewInvest')\">\n";
						$ret .= "   	        <option value=''>--Select--</option>\n";
						$ret .= "   	        <option value='Send'>Send</option>\n";
						$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
						$ret .= "			</select>\n";
						$ret .= "			<div id='div_submitResp_insertNewInvest' name='div_submitResp_insertNewInvest'></div>\n";
						$ret .= "	</tr>\n";
						$ret .= "</table>\n";

					break;



					case "submit_newreq":						
						if (isset($_POST["rgaNumber"]) && isset($_POST["custNumber"]) && isset($_POST["custName"]) && isset($_POST["city"]) && isset($_POST["state"]) && 
							isset($_POST["country"]) && isset($_POST["contactName"]) && isset($_POST["phoneNumber"]) && isset($_POST["email"])  && isset($_POST["prov"]) &&
							isset($_POST["authorized"]) && isset($_POST["selectSalesMgr"]) && isset($_POST["territory"]) && isset($_POST["descr"]) && isset($_POST["classification"]) &&
							//isset($_POST["rgaClass"]) && 
							isset($_POST["selectExpectingReturn"]) && isset($_POST["followUp"]) && isset($_POST["addInfo"])  && isset($_POST["emailNotify"]) && isset($_POST["sendEmail"]) &&
							isset($_POST["itemNumber1"]) && isset($_POST["orderNumber1"]) && isset($_POST["poNumber1"]) && isset($_POST["invoiceNumber1"]) && isset($_POST["quant1"]) && isset($_POST["dateShipped1"]) &&
							isset($_POST["itemNumber2"]) && isset($_POST["orderNumber2"]) && isset($_POST["poNumber2"]) && isset($_POST["invoiceNumber2"]) && isset($_POST["quant2"]) && isset($_POST["dateShipped2"]) &&
							isset($_POST["itemNumber3"]) && isset($_POST["orderNumber3"]) && isset($_POST["poNumber3"]) && isset($_POST["invoiceNumber3"]) && isset($_POST["quant3"]) && isset($_POST["dateShipped3"]) &&
							isset($_POST["itemNumber4"]) && isset($_POST["orderNumber4"]) && isset($_POST["poNumber4"]) && isset($_POST["invoiceNumber4"]) && isset($_POST["quant4"]) && isset($_POST["dateShipped4"]) &&
							isset($_POST["itemNumber5"]) && isset($_POST["orderNumber5"]) && isset($_POST["poNumber5"]) && isset($_POST["invoiceNumber5"]) && isset($_POST["quant5"]) && isset($_POST["dateShipped5"]) &&
							isset($_POST["itemNumber6"]) && isset($_POST["orderNumber6"]) && isset($_POST["poNumber6"]) && isset($_POST["invoiceNumber6"]) && isset($_POST["quant6"]) && isset($_POST["dateShipped6"]) &&
							isset($_POST["itemNumber7"]) && isset($_POST["orderNumber7"]) && isset($_POST["poNumber7"]) && isset($_POST["invoiceNumber7"]) && isset($_POST["quant7"]) && isset($_POST["dateShipped7"]) &&
							isset($_POST["itemNumber8"]) && isset($_POST["orderNumber8"]) && isset($_POST["poNumber8"]) && isset($_POST["invoiceNumber8"]) && isset($_POST["quant8"]) && isset($_POST["dateShipped8"]) &&
							isset($_POST["itemNumber9"]) && isset($_POST["orderNumber9"]) && isset($_POST["poNumber9"]) && isset($_POST["invoiceNumber9"]) && isset($_POST["quant9"]) && isset($_POST["dateShipped9"]) &&
							isset($_POST["itemNumber10"]) && isset($_POST["orderNumber10"]) && isset($_POST["poNumber10"]) && isset($_POST["invoiceNumber10"]) && isset($_POST["quant10"]) && isset($_POST["dateShipped10"])
						){
							$rgaNumber = $_POST["rgaNumber"];
							$custNumber = $_POST["custNumber"];
							$custName = $_POST["custName"];
							$city = $_POST["city"];
							$state = $_POST["state"];
							$prov = $_POST["prov"];
							$country = $_POST["country"];
							$contactName = $_POST["contactName"];
							$phoneNumber = $_POST["phoneNumber"];
							$email = $_POST["email"];
							$classification = $_POST["classification"];
							$authorized = $_POST["authorized"];
							$sendEmail = $_POST["sendEmail"];
							
							$selectSalesMgr_FULL = $_POST["selectSalesMgr"];
							$pos = strrpos($selectSalesMgr_FULL, '_');
							$selectSalesMgr = substr($selectSalesMgr_FULL, $pos + 1);

							$territory = $_POST["territory"];
							$descr = $_POST["descr"];
							//$rgaClass = $_POST["rgaClass"];
							$expectingReturn = $_POST['selectExpectingReturn'];
							$followUp = $_POST["followUp"];
							$addInfo = $_POST["addInfo"];
							$emailNotify = $_POST["emailNotify"];
							//$rgaStatus = "Open";
							$rgaStatus = "Open, Response to Customer Required";
							if ($expectingReturn == 'Y') {
								$rgaStatus = "Open, Waiting for Return";	
							}

							//if ($rgaClass == "B" && $classification == "PQE") {
							//	//$rgaStatus = "Pending";
							//	$rgaStatus = "Open, Response to Customer Required";
							//}
							


							$itemNumber1 = $_POST["itemNumber1"];
							$itemNumber2 = $_POST["itemNumber2"];
							$itemNumber3 = $_POST["itemNumber3"];
							$itemNumber4 = $_POST["itemNumber4"];
							$itemNumber5 = $_POST["itemNumber5"];
							$itemNumber6 = $_POST["itemNumber6"];
							$itemNumber7 = $_POST["itemNumber7"];
							$itemNumber8 = $_POST["itemNumber8"];
							$itemNumber9 = $_POST["itemNumber9"];
							$itemNumber10 = $_POST["itemNumber10"];

							$orderNumber1 = $_POST["orderNumber1"];
							$orderNumber2 = $_POST["orderNumber2"];
							$orderNumber3 = $_POST["orderNumber3"];
							$orderNumber4 = $_POST["orderNumber4"];
							$orderNumber5 = $_POST["orderNumber5"];
							$orderNumber6 = $_POST["orderNumber6"];
							$orderNumber7 = $_POST["orderNumber7"];
							$orderNumber8 = $_POST["orderNumber8"];
							$orderNumber9 = $_POST["orderNumber9"];
							$orderNumber10 = $_POST["orderNumber10"];

							$poNumber1 = $_POST["poNumber1"];
							$poNumber2 = $_POST["poNumber2"];
							$poNumber3 = $_POST["poNumber3"];
							$poNumber4 = $_POST["poNumber4"];
							$poNumber5 = $_POST["poNumber5"];
							$poNumber6 = $_POST["poNumber6"];
							$poNumber7 = $_POST["poNumber7"];
							$poNumber8 = $_POST["poNumber8"];
							$poNumber9 = $_POST["poNumber9"];
							$poNumber10 = $_POST["poNumber10"];

							$invoiceNumber1 = $_POST["invoiceNumber1"];
							$invoiceNumber2 = $_POST["invoiceNumber2"];
							$invoiceNumber3 = $_POST["invoiceNumber3"];
							$invoiceNumber4 = $_POST["invoiceNumber4"];
							$invoiceNumber5 = $_POST["invoiceNumber5"];
							$invoiceNumber6 = $_POST["invoiceNumber6"];
							$invoiceNumber7 = $_POST["invoiceNumber7"];
							$invoiceNumber8 = $_POST["invoiceNumber8"];
							$invoiceNumber9 = $_POST["invoiceNumber9"];
							$invoiceNumber10 = $_POST["invoiceNumber10"];

							$quant1 = $_POST["quant1"];
							$quant2 = $_POST["quant2"];
							$quant3 = $_POST["quant3"];
							$quant4 = $_POST["quant4"];
							$quant5 = $_POST["quant5"];
							$quant6 = $_POST["quant6"];
							$quant7 = $_POST["quant7"];
							$quant8 = $_POST["quant8"];
							$quant9 = $_POST["quant9"];
							$quant10 = $_POST["quant10"];

							$dateShipped1 = $_POST["dateShipped1"];
							$dateShipped2 = $_POST["dateShipped2"];
							$dateShipped3 = $_POST["dateShipped3"];
							$dateShipped4 = $_POST["dateShipped4"];
							$dateShipped5 = $_POST["dateShipped5"];
							$dateShipped6 = $_POST["dateShipped6"];
							$dateShipped7 = $_POST["dateShipped7"];
							$dateShipped8 = $_POST["dateShipped8"];
							$dateShipped9 = $_POST["dateShipped9"];
							$dateShipped10 = $_POST["dateShipped10"];

							$sql = " insert into nsa.RGA_BASE" . $DB_TEST_FLAG . "( ";
							$sql .= " RGA_NUMBER, ";
							$sql .= " ID_USER_ADD, ";
							$sql .= " DATE_ADD, ";
							$sql .= " DATE_ISSUE, ";
							$sql .= " ID_CUST, ";
							$sql .= " NAME_CUST, ";
							$sql .= " CITY, ";
							$sql .= " ID_ST, ";
							$sql .= " PROV, ";
							$sql .= " COUNTRY, ";
							$sql .= " CONTACT_NAME, ";
							$sql .= " PHONE_NUMBER, ";
							$sql .= " EMAIL, ";
							$sql .= " CLASSIFICATION, ";
							$sql .= " ISSUE_BY, ";
							$sql .= " AUTHORIZED_BY, ";
							$sql .= " SALES_MGR, ";
							$sql .= " ID_TERR, ";
							$sql .= " DESCR, ";
							$sql .= " RETURN_EXPECTED, ";
							$sql .= " RGA_CLASS, ";
							$sql .= " FOLLOW_UP_DESCR, ";
							$sql .= " ADD_INFO, ";
							$sql .= " EMAIL_LIST, ";
							$sql .= " RGA_STATUS, ";
							$sql .= " FLAG_EMAIL_SENT ";
							$sql .= " ) VALUES ( ";
							$sql .= " '" .$UserRow['ID_USER'] . Time()."', "; 
							$sql .= " '" . stripIllegalChars2($UserRow['ID_USER']) . "', ";
							$sql .= " GetDate(), ";
							$sql .= " GetDate(), ";
							$sql .= " upper('" . ms_escape_string($custNumber) . "'), ";
							$sql .= " '" . ms_escape_string($custName) . "', ";
							$sql .= " '" . ms_escape_string($city) . "', ";
							$sql .= " '" . ms_escape_string($state) . "', ";
							$sql .= " '" . ms_escape_string($prov) . "', ";
							$sql .= " '" . ms_escape_string($country) . "', ";
							$sql .= " '" . ms_escape_string($contactName) . "', ";
							$sql .= " '" . ms_escape_string($phoneNumber) . "', ";
							$sql .= " '" . ms_escape_string($email) . "', ";
							$sql .= " '" . ms_escape_string($classification) . "', ";
							$sql .= " '" . $UserRow['ID_USER'] . "', ";
							$sql .= " '" . $authorized . "', ";
							$sql .= " '" . $selectSalesMgr . "', ";
							$sql .= " '" . ms_escape_string($territory) . "', ";
							$sql .= " '" . ms_escape_string($descr) . "', ";
							$sql .= " '" . ms_escape_string($expectingReturn) . "', ";
							//$sql .= " '" . $rgaClass . "', ";
							$sql .= " '', "; //rgaClass does not allow NULL
							$sql .= " '" . ms_escape_string($followUp) . "', ";
							$sql .= " '" . ms_escape_string($addInfo) . "', ";
							$sql .= " '" . ms_escape_string($emailNotify) . "', ";
							$sql .= " '" . $rgaStatus . "', ";
							$sql .= " '' ";
							$sql .= " ) SELECT LAST_INSERT_ID=@@IDENTITY";
							QueryDatabase($sql, $results);
							$row = mssql_fetch_assoc($results);
							$BaseRowID = $row['LAST_INSERT_ID'];

							$sql = "select top 1 RGA_NUMBER, ";
							$sql .= " cast(rtrim(right(RGA_NUMBER, charindex('-', reverse(RGA_NUMBER)) - 1)) as INT) as sufx ";
							$sql .= "from nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
							$sql .= " where RGA_NUMBER like '".date('y-m-') ."%' ";
							$sql .= " order by sufx desc";
							QueryDatabase($sql, $results);

							if (mssql_num_rows($results) == 0) {
								//first RGA of month
								$rgaSufix = '1';//start new months RGA numbering.
							} else {
								$row = mssql_fetch_assoc($results);
								$rgaSufix = $row['sufx'];
								$rgaSufix = intval($rgaSufix) + 1;
							}
							$newRGA = date('y-m') ."-". str_pad($rgaSufix,2,"0",STR_PAD_LEFT);
							$rgaNumber = $newRGA;

							$sql = "update nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
							$sql .= "set RGA_NUMBER = '".$newRGA."' " ;
							$sql .= "where rowid = ".$BaseRowID;
							QueryDatabase($sql, $results);

							for ($y = 1; $y <= 10; $y++) {
								if(${"itemNumber".$y} <> '' || ${"orderNumber".$y} <> '' || ${"poNumber".$y} <> '' || 
									${"invoiceNumber".$y} <> '' || ${"quant".$y} <> '' || ${"dateShipped".$y} <> ''
								){
									error_log("Inserting Seq LINE: " . $y);
									$sql = " insert into nsa.RGA_LINE" . $DB_TEST_FLAG . "( ";//insert into sql RGA_LINE table
									$sql .= " RGA_BASE_rowid, ";
									$sql .= " RGA_NUMBER, ";
									$sql .= " SEQ_LINE_RGA, ";
									$sql .= " ID_ITEM, ";
									$sql .= " QUANTITY, ";
									$sql .= " ID_ORD, ";
									$sql .= " ID_PO, ";
									$sql .= " ID_INVC,";
									if (${"dateShipped".$y} <> '') {
										$sql .= " DATE_SHIPPED, ";
									}
									$sql .= " ID_USER_ADD, ";
									$sql .= " DATE_ADD ";
									$sql .= " ) VALUES ( ";
									$sql .= " '" . $BaseRowID ."', ";
									$sql .= " '" . $newRGA ."', ";
									$sql .= " '" . $y ."', ";
									$sql .= " upper('" . ms_escape_string(${"itemNumber".$y}) . "'), ";
									$sql .= " '" . ms_escape_string(${"quant".$y}) . "', ";
									$sql .= " '" . ms_escape_string(${"orderNumber".$y}) . "', ";
									$sql .= " '" . ms_escape_string(${"poNumber".$y}) . "', ";
									$sql .= " '" . ms_escape_string(${"invoiceNumber".$y}) . "', ";
									if (${"dateShipped".$y} <> '') {
										$sql .= " '" . ${"dateShipped".$y} . "', ";
									}
									$sql .= " '" . $UserRow['ID_USER'] . "', ";
									$sql .= " GetDate() ";
									$sql .= " ) ";
									QueryDatabase($sql, $results);
								}
							}

							if (trim($sendEmail) == 'Send'){
								$sql  = " SELECT rb.NAME_CUST, ";
								$sql .= " rb.SALES_MGR, ";
								$sql .= " sr.ADDR_EMAIL as EMAIL_SLSREP ";
								$sql .= " FROM nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
								$sql .= " left join nsa.tables_slsrep sr ";
								$sql .= " on rb.SALES_MGR = sr.NAME_SLSREP ";
								$sql .= " and sr.ADDR_EMAIL is not null ";
								$sql .= " WHERE rowid = '". $BaseRowID ."'";
								QueryDatabase($sql, $results);

								while ($row = mssql_fetch_assoc($results)) {
									$files = array();
									$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*.{jpg,png,gif,bmp,tif,pdf}", GLOB_BRACE);
									foreach ($a_files as $filename){
										$short_filename = substr($filename, strrpos($filename, '/') + 1);
										$tempFilename = "/tmp/RGA_temp/" . $short_filename;
										shell_exec("cp " . $filename . " " . $tempFilename);
										array_push($files, $tempFilename);
									}

									error_log("emailNotify: " . $emailNotify);
									$a_cc = array();
									$a_emailNotify = explode(';',$emailNotify);
									foreach ($a_emailNotify as $each_emailNotify){
										error_log("each_emailNotify: " . $each_emailNotify);
										array_push($a_cc,$each_emailNotify);
									}


									if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
										//array_push($a_cc,'gvandyne@thinknsa.com'=>$row['SALES_MGR']);
										array_push($a_cc,'gvandyne@thinknsa.com=>gvandyne');

										$head = array(
									    	//'to'      =>array($emailNotify=>$emailNotify),
									    	'to'      =>array('TESTGroup-RGA@thinknsa.com'=>'Group RGA'),
									    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
									    	//'cc'      =>array($emailNotify=>$emailNotify,'gvandyne@thinknsa.com'=>$row['SALES_MGR']),
									    	'cc'      =>$a_cc,
									    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['SALES_MGR']),
									    	//'bcc'     =>array('email4@email.net'=>'Admin'),
								    	);
							    	} else {
							    		//array_push($a_cc,"$row['EMAIL_SLSREP']=>$row['SALES_MGR']")
							    		$head = array(
								    		//'to'      =>array('TESTGroup-RGA@thinknsa.com'=>'Group RGA'),
									    	'to'      =>array('Group-RGA@thinknsa.com'=>'Group RGA'),
									    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
									    	//'cc'      =>array($row['EMAIL_SLSREP']=>$row['SALES_MGR']),
									    	'cc'      =>$a_cc,
									    	//'bcc'     =>array('email4@email.net'=>'Admin'),
								    	);
							    	}
								    
									$subject = "New RGA " . $newRGA . " - " . $custName;
									$body = GenerateHTMLforEmail($rgaNumber);
									//$files = array($file1,$file2);
									if (!empty($files)) {
										mail::send($head,$subject,$body,$files);
									} else {
										mail::send($head,$subject,$body);
									}

									$sql1  = " UPDATE nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
									$sql1 .= " SET FLAG_EMAIL_SENT = 'T' ";
									$sql1 .= " WHERE rowid = '". $BaseRowID ."'";
									QueryDatabase($sql1, $results1);
								}
							}

							$ret .= "<input type='hidden' id='ret_rga_number' name='ret_rga_number' value='".$newRGA."'></input>";
							$ret .= "SAVED </br>" . date('Y-m-d H:i:s');

						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}
					break;

					case "form_review_basereq":
						if (isset($_POST["rgaNumber"])) {
							$rgaNumber = $_POST["rgaNumber"];

							//$sql = "SET ANSI_NULLS ON";
							//QueryDatabase($sql, $results);
							//$sql = "SET ANSI_WARNINGS ON";
							//QueryDatabase($sql, $results);	

							$sql1  = " SELECT * from (select ltrim(t.ID_SLSREP) as ID_SLSREP, t.NAME_SLSREP, t.ADDR_EMAIL from nsa.tables_slsrep t "; 
							$sql1 .= " union all select '', '--SELECT--','9') as U "; 
							$sql1 .= " where U.ADDR_EMAIL is not NULL "; 
							$sql1 .= " order by NAME_SLSREP asc "; 
							QueryDatabase($sql1, $results1);

							$sql =  "SELECT ";
							$sql .= " wa.NAME_EMP, ";
							$sql .= " rb.rowid as BaseRowID, ";
							//$sql .= " ri.rowid as isoRowID, ";
							//$sql .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
							$sql .= " convert(varchar(10),rb.DATE_ISSUE,126) as rbDate_ISSUE,";
							$sql .= " rb.ID_USER_ADD as rb_ID_USER_ADD, ";
							$sql .= " rb.* ";
							//$sql .= " ,ri.* ";
							$sql .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
							//$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
							//$sql .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " where ";
							$sql .= " rb.RGA_NUMBER = '" . $rgaNumber . "' ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								if (strpos($UserRow['EMP_ROLE'], ":RGA-CS:")) {
									$ret .= "<table width='850' >\n";
									$ret .= "	<tr class='blueHeader'>\n";
									$ret .= "		<th colspan='4'><left><img src=''></left> <right>RGA & Customer Complaint</right></th>\n";
									$ret .= "		<input id='BaseRowID' name='BaseRowID' type='hidden' value='" . $row['BaseRowID'] . "'>\n";
									//$ret .= "		<input id='isoRowID' name='isoRowID' type='hidden' value='" . $row['isoRowID'] . "'>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>RGA Number:</td>\n";
									$ret .= "		<td><input id='txt_rgaNumber' disabled name='txt_rgaNumber' type='text' value=" . $rgaNumber . " READONLY style='background-color:#D0D0D0;'></input></td>\n";
									$ret .= "		<td>Date Issued:</td>\n";
									$ret .= "		<td><input id='txt_date' name='txt_date' type='text' value='" . $row['rbDate_ISSUE'] . "'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Customer #:</td>\n";
									$ret .= "		<td><input id='txt_customerID' name='txt_customerID' type='text' value='" . $row['ID_CUST'] . "' maxlength='6'></input></td>\n";
									$ret .= "		<td>Customer Name:</td>\n";
									$ret .= "		<td><input id='txt_NAME_CUST' name='txt_NAME_CUST' type='text' value='".$row['NAME_CUST']."' maxlength='30' size=30></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>City:</td>\n";
									$ret .= "		<td><input id='txt_CITY' name='txt_CITY' type='text' value='".$row['CITY']."' maxlength='15'></input></td>\n";
									$ret .= "		<td>State:</td>\n";
									$ret .= "		<td><input id='txt_ID_ST' name='txt_ID_ST' type='text' value='" . $row['ID_ST'] . "' maxlength='2'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Prov:</td>\n";
									$ret .= "		<td><input id='txt_PROV' name='txt_PROV' type='text' value='" . $row['PROV'] . "' maxlength='30'></input></td>\n";
									$ret .= "		<td>Country:</td>\n";
									$ret .= "		<td><input id='txt_COUNTRY' name='txt_COUNTRY' type='text' value='" . $row['COUNTRY'] . "' maxlength='30'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Contact Name:</td>\n";
									$ret .= "		<td><input id='txt_NAME_CONTACT_CUST' name='txt_NAME_CONTACT_CUST' type='textbox' value='".$row['CONTACT_NAME']."' maxlength='25'></input></td>\n";
									$ret .= "		<td>Phone #:</td>\n";
									$ret .= "		<td><input id='txt_PHONE' name='txt_PHONE' type='text' value='" . $row['PHONE_NUMBER'] . "' maxlength='20'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Email:</td>\n";
									$ret .= "		<td><input id='txt_email' name='txt_email' type='text' size='35' value='" . $row['EMAIL'] . "' maxlength='100'></input></td>\n";
									$ret .= "		<td>Classification:</td>\n";
									$ret .= "		<td><select id='select_classification' name='select_classification'>\n";
									$ClassificationMDArray = array(
										array("","--Select--"),
										array("NQE","Non-Quality Error"),
										array("PQE","Product Quality Error"),
									);
									for ($rowClassification = 0; $rowClassification < 3; $rowClassification++) {
										$SELECTED = '';
										$CURRENT = '';

										if (trim($row['CLASSIFICATION']) == trim($ClassificationMDArray[$rowClassification][0])) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='" . $ClassificationMDArray[$rowClassification][0] . "' " . $SELECTED . ">" . $CURRENT . $ClassificationMDArray[$rowClassification][1] .  "</option>\n";
									}
									$ret .= "		</select></td>\n";
									$ret .= "	</tr>\n";

									$sql2  = "select ";
									$sql2 .= " convert(varchar(10),rl.DATE_SHIPPED,126) as rlDate_SHIPPED, ";
									$sql2 .= " rl.* ";
									$sql2 .= " from nsa.RGA_LINE" . $DB_TEST_FLAG . " rl";
									$sql2 .= " where rl.RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= " ORDER BY rl.SEQ_LINE_RGA asc ";
									QueryDatabase($sql2, $results2);
									$LineCount = mssql_num_rows($results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										$style = 'display:table-row';
										$y = $row2['SEQ_LINE_RGA'];
										$z = $y+1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
										$ret .= "	</tr>\n";

										$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
										$ret .= "		<td>Order #:</td>\n";
										$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' value='".trim($row2['ID_ORD'])."' maxlength='8'></input></td>\n";
										$ret .= "		<td>PO #:</td>\n";
										$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' size='30' value='".trim($row2['ID_PO'])."' maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' size='35' value='".trim($row2['ID_ITEM'])."' maxlength='30'></input></td>\n";
										$ret .= "		<td>Quantity:</td>\n";
										$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' value='".trim($row2['QUANTITY'])."' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' value='".trim($row2['ID_INVC'])."' maxlength='8'></input></td>\n";//added invoiceNumber to form
										$ret .= "		<td>Date Ship (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text' value='".trim($row2['rlDate_SHIPPED'])."' ></input></td>\n";
										$ret .= "	</tr>\n";
										if ($y==$LineCount){
											$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
											$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','4')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','4','1')\">+Clone</font></td>\n";
											$ret .= "	</tr>\n";
										}
									}
									for ($y = $LineCount+1; $y <= 10; $y++) {
										if ($y==1) {
											$style = 'display:table-row';
										} else {
											$style = 'display:none;';
										}
										$z = $y+1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
										$ret .= "		<td>Order #:</td>\n";
										$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' maxlength='8'></input></td>\n";
										$ret .= "		<td>PO #:</td>\n";
										$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' size='30' maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' size='35' maxlength='30'></input></td>\n";
										$ret .= "		<td>Quantity:</td>\n";
										$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' maxlength='8'></input></td>\n";//added invoiceNumber to form
										$ret .= "		<td>Date Ship (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text' ></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','4')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','4','1')\">+Clone</font></td>\n";
										$ret .= "	</tr>\n";
									}

									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Issued by:</td>\n";
									$ret .= "		<td>".$row['rb_ID_USER_ADD']."</td>\n";
									$ret .= "		<td>Authorized by:</td>\n";
									$ret .= "		<td><select id='select_auth' ". $row['AUTHORIZED_BY'] . "' >\n";
									
									$AUTH_BY_MDARRAY = array(
										array("","--Select--"),
										array("CG2","Chuck"),
										array("DS","Duane"),
										array("MCF","Micel"),
										array("STG","Sal"),
										array("JG","Joe"),
										array("Stock","N/A")
									);
									for ($rowAuth = 0; $rowAuth < 5; $rowAuth++) {
										$SELECTED = '';
										$CURRENT = '';

										if (trim($row['AUTHORIZED_BY']) == trim($AUTH_BY_MDARRAY[$rowAuth][0])) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='" . $AUTH_BY_MDARRAY[$rowAuth][0] . "' " . $SELECTED . ">" . $CURRENT . $AUTH_BY_MDARRAY[$rowAuth][1] .  "</option>\n";
									}

									$ret .= "		</select></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Sales Manager:</td>\n";
									$ret .= "		<td><select id='selectSalesMgr' onchange=\"slsMgrToTerr()\" >\n";

									while ($row1 = mssql_fetch_assoc($results1)){
										$SELECTED = '';
										$CURRENT = '';
										$ID = $row1['ID_SLSREP'];
										$NAME = $row1['NAME_SLSREP'];
										$Value = $ID . "_" . $NAME;
										if ($row1['NAME_SLSREP'] == "--Select--") {
											$Value = '';
										}

										if (trim($row['SALES_MGR']) == trim($NAME)) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='". $Value . "' " . $SELECTED . ">" . $CURRENT . $row1['NAME_SLSREP'] .  "</option>\n";
								    }
									$ret .= "		</select></td>\n";								
									$ret .= "		<td>Territory:</td>\n";
									$ret .= "		<td><input id='txt_territory' name='txt_territory' type='text' value='" . trim($row['ID_TERR']) . "' maxlength='3'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Description of <br/>Request/Complaint:</td>\n";
									$ret .= "		<td colspan='3'><textarea id='txt_descr' name='txt_descr' cols='25' rows='7'>".trim($row['DESCR'])."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Expecting Return?:</td>\n";
									$ret .= "		<td>\n";

									$selER_Y = "";
									$selER_N = "";
									$selER_Y_ast = "";
									$selER_N_ast = "";
									if ($row['RETURN_EXPECTED'] == 'Y') {
										$selER_Y = "SELECTED";
										$selER_Y_ast = "*";
									}
									if ($row['RETURN_EXPECTED'] == 'N') {
										$selER_N = "SELECTED";
										$selER_N_ast = "*";
									}
									$ret .= "			<select id='selectExpectingReturn' onchange=\"showHideShip()\" >\n";
									$ret .= "   	     	<option value=''>-- Select --</option>\n";
									$ret .= "   	     	<option value='Y' ".$selER_Y.">".$selER_Y_ast."Yes</option>\n";
									$ret .= "   	     	<option value='N' ".$selER_N.">".$selER_N_ast."No</option>\n";
									$ret .= "			</select>\n";
									$ret .= "		</td>\n";
									$ret .= "		<td colspan=2>\n";
									$ret .= "		</td>\n";
									$ret .= "	</tr>\n";									
									$ret .= "</table>\n";
									/*
									$ret .= "<table width='850'>\n";
									$ret .= "   <tr class='dbc'>\n";
									$ret .= "   <td colspan='6'><strong>Please Select One (Need to Select One)</strong></td>\n";
									$ret .= "   </tr>\n";

									$rgaSelectA = '';
									$rgaSelectB = '';
									$rgaSelectC = '';
									if ($row['RGA_CLASS'] == 'A'){
										$rgaSelectA = "checked = 'checked'";
									}
									if ($row['RGA_CLASS'] == 'B'){
										$rgaSelectB = "checked = 'checked'";
									}
									if ($row['RGA_CLASS'] == 'C'){
										$rgaSelectC = "checked = 'checked'";
									}
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Complaint with Return?</td>\n";
									$ret .= "		<td><input type='radio' id='A' name='choice' ".$rgaSelectA." value='A' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\" >A</input></td>\n";
									$ret .= "		<td>Complaint without Return?</td>\n";
									$ret .= "		<td><input type='radio' id='B' name='choice' ".$rgaSelectB." value='B' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">B</input></td>\n";
									$ret .= "		<td>Customer Request?</td>\n";
									$ret .= "		<td><input type='radio' id='C' name='choice' ".$rgaSelectC." value='C' onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">C</input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									*/
									$ret .= "<table width='850'>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Follow-Up that<br /> Requires Action:</td>\n";
									$ret .= "		<td colspan=3><textarea id='txt_followUp' name='txt_followUp' cols='55' >".trim($row['FOLLOW_UP_DESCR'])."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc' style='display:none'>\n";
									$ret .= "		<td>Email to<br /> Notify:</td>\n";
									$ret .= "		<td><input id='txt_email_notify' name='txt_email_notify' style=' width:500px;' value='" . trim($row['EMAIL_LIST']) . "' ></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Additional<br /> Information:</td>\n";
									$ret .= "		<td colspan=3><textarea id='txt_add_info' name='txt_add_info' cols='55' >".trim($row['ADD_INFO'])."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Attached Files</td>\n";
									$ret .= "		<td colspan=3>\n";

									$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*");
									foreach ($a_files as $filename){
										//error_log($filename);
										$filename = str_replace('..','/protected',$filename);
										$short_filename = substr($filename, strrpos($filename, '/') + 1);
										if (strtoupper(substr($filename,-3)) == "JPG" || strtoupper(substr($filename,-3)) == "PNG") {
											$ret .=	"	<a href='" . $filename . "' target='_blank'><img class='icon' src='" . $filename . "' href='" . $filename . "' target='_blank'></br>".$short_filename."</a></br>\n";
										} else {
											$ret .=	"	<a href='" . $filename . "' target='_blank'>".$short_filename."</a></br>\n";
										}
									}
									$ret .= "		</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td colspan=4><input id='fileToUpload' name='fileToUpload' type='file' value='Choose File' ></input><input type='button' value='Upload' onclick='uploadFile()'></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td colspan=4></input><progress id='progressBar' value='0' max='100'></progress><h5 id='status'><input id='ret_FileName' type='hidden' value=''></input></h5></td>\n";
									$ret .= "	</tr>\n";					
									$ret .= "	<tr class='dbc'>\n";
									//$ret .= "		<td colspan=4><input id='button_SubmitNew' name='button_SubmitNew' type='button' value='Save' onClick=\"updateRgaBase()\"></input><div id='div_submitResp' name='div_submitResp'></div></td>\n";
									$ret .= "		<td colspan=4>\n";
									$ret .= " 			<input id='button_updateRgaBase' name='button_updateRgaBase' type='button' value='Save' onclick=\"updateRgaBase()\" DISABLED></input>\n";
									$ret .= " 			**Send Email?<select id='sel_Email_updateRgaBase' name='sel_Email_updateRgaBase' onchange=\"checkEnableButton('sel_Email_updateRgaBase','button_updateRgaBase')\">\n";
									$ret .= "   	        <option value=''>--Select--</option>\n";
									$ret .= "   	        <option value='Send'>Send</option>\n";
									$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
									$ret .= "			</select>\n";
									$ret .= "			<div id='div_submitResp_updateRgaBase' name='div_submitResp_updateRgaBase'></div>\n";
									$ret .= "		</td>\n";									
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";

									//shipping
									$ret .= "<table width='850' id='table_shipping' style='display:table;'>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<th colspan='4'>For Shipping Only</th>\n";
									$ret .= "		<input id='hidden_RGA_NUMBER' name='hidden_RGA_NUMBER' type='hidden' value='" . $rgaNumber . "'>\n";
									$ret .= "	</tr>\n";

									$sql3  = "select ";
									$sql3 .= " convert(varchar(16),rs.DATE_RECEIVED,121) as rsDATE_RECEIVED,";
									$sql3 .= " rs.* ";
									$sql3 .= " from nsa.RGA_SHIP" . $DB_TEST_FLAG . " rs";
									$sql3 .= " where rs.RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql3 .= " ORDER BY rs.SEQ_LINE_SHIP asc ";
									QueryDatabase($sql3, $results3);
									$LineCount = mssql_num_rows($results3);
									while($row3 = mssql_fetch_assoc($results3)){
										$style= 'table-row';
										$y = $row3['SEQ_LINE_SHIP'];
										$z = $y + 1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
										$ret .= "       <td colspan=2></td>\n";
										$ret .= "		<td>Item Recieved:</td>\n";
										$ret .= "		<td><input id='txt_itemReceived".$y."' name='txt_itemReceived".$y."'  width='500px' type='text' $READONLY value='".$row3['ITEM_RECEIVED']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text'  $READONLY value='".$row3['rsDATE_RECEIVED']."'></input></td>\n";
										$ret .= "		<td>Quantity Received:</td>\n";
										$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."'  type='text' $READONLY value='".$row3['QUANTITY_RECEIVED']."' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Carrier:</td>\n";
										$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' $READONLY value='".$row3['CARRIER']."' maxlength='12'></input></td>\n";
										$ret .= "		<td>Tracking #:</td>\n";
										$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' $READONLY value='".$row3['TRACKING_NO']."' maxlength='3'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".5' style='".$style."'>\n";
										$ret .= "		<td>Condition Received:</td>\n";
										$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' $READONLY value='".$row3['COND_RECEIVED']."' maxlength='50'></input></td>\n";
										$ret .= "		<td>Received by:</td>\n";
										$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' $READONLY value='".$row3['RECEIVED_BY']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .=" 	<tr class='dbc' id='tr_itm".$y.".6' style='".$style."'>\n";
										$ret .= "		<td>Comments:</td>\n";
										$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' cols='55' $READONLY>".$row3['COMMENTS']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										if ($y==$LineCount){
											$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
											$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','6','1')\">+Clone</font></td>\n";
											$ret .= "	</tr>\n";
										}
									}
									for ($y = $LineCount+1; $y <= 10; $y++){
										if($y==1){
											$style = 'table-row';
										}else{
											$style = 'display:none;';
										}
										$z = $y + 1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
										$ret .= "       <td colspan=2></td>\n";
										$ret .= "		<td>Item Recieved:</td>\n";
										$ret .= "		<td><input id='txt_itemReceived". $y."' name='txt_itemReceived".$y."' width='500px' type='text' $READONLY maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text' $READONLY></input></td>\n";
										$ret .= "		<td>Quantity Received:</td>\n";
										$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."' type='text' $READONLY maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."' >\n";
										$ret .= "		<td>Carrier:</td>\n";
										$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' $READONLY maxlength='12'></input></td>\n";
										$ret .= "		<td>Tracking #:</td>\n";
										$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' $READONLY maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".5' style='".$style."'>\n";
										$ret .= "		<td>Condition Received:</td>\n";
										$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' $READONLY maxlength='50'></input></td>\n";
										$ret .= "		<td>Received by:</td>\n";
										$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' $READONLY maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .=" 	<tr class='dbc' id='tr_itm".$y.".6' style='".$style."'>\n";
										$ret .= "		<td>Comments:</td>\n";
										$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' $READONLY cols='55'></textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','6','1')\">+Clone</font></td>\n";
										$ret .= "	</tr>\n";	
									}
									$ret .= "	<tr class='dbc'>\n";
									//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' type='button' disabled $READONLY value='Submit'></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
									$ret .= "		<td colspan=4>\n";
									$ret .= " 			<input id='button_insertNewShip' name='button_insertNewShip' type='button' value='Submit' onclick=\"insertNewShip()\" DISABLED></input>\n";
									$ret .= " 			**Send Email?<select id='sel_Email_insertNewShip' name='sel_Email_insertNewShip' DISABLED onchange=\"checkEnableButton('sel_Email_insertNewShip','button_insertNewShip')\">\n";
									//$ret .= " 			**Send Email?<select id='sel_Email_insertNewShip' name='sel_Email_insertNewShip' DISABLED>\n";
									$ret .= "   	        <option value=''>--Select--</option>\n";
									$ret .= "   	        <option value='Send'>Send</option>\n";
									$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
									$ret .= "			</select>\n";
									$ret .= "			<div id='div_submitResp_insertNewShip' name='div_submitResp_insertNewShip'></div>\n";
									$ret .= "		</td>\n";
									
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									//^only for Shipping when done hit Submit email Sal, Brian, Valentina, Sabrina and initiating CSR (Sabrina to double Check) 




									//////////////
									// INVEST RECORDS
									//////////////
									$sql2 =  "SELECT ";
									$sql2 .= " ri.rowid as isoRowID,";
									$sql2 .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
									$sql2 .= " ri.*, ";
									$sql2 .= " rb.rowid as baseRowID ";
									$sql2 .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
									$sql2 .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
									$sql2 .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
									$sql2 .= " where rb.RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= " order by ri.SEQ_INVEST asc ";
									QueryDatabase($sql2, $results2);
									$y=0;
									while(($row2 = mssql_fetch_assoc($results2)) || ($y<10)){
										$y++;
										$z = $y + 1;
										$style = 'table-row';
										$seqInvest = $row2['SEQ_INVEST'];

										if (empty($seqInvest)) {
											$seqInvest = $y;
											$style = 'display:none;';
										} 

										if ($y==1) {
											$style = 'table-row';
										}

										//selects radio button for RGA Rating
										$rgaRatingSelect1 = '';
										$rgaRatingSelect2 = '';
										$rgaRatingSelect3 = '';
										if($row2['RGA_RATING'] == '1'){
											$rgaRatingSelect1 = "checked = 'checked'";
										}
										if($row2['RGA_RATING'] == '2'){
											$rgaRatingSelect2 = "checked = 'checked'";
										}
										if($row2['RGA_RATING'] == '3'){
											$rgaRatingSelect3 = "checked = 'checked'";
										}
										//selects action needed checkboxes
										$rgaActionNeedRework = '';
										$rgaActionNeedReplace = '';
										$rgaActionNeedNonStock = '';
										$rgaActionNeedCredit = '';
										$rgaActionNeedOther = '';
										if (strpos($row2['ACTION_NEEDED'], ':Rework:') !== false) {
											$rgaActionNeedRework = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':Replace:') !== false) {
											$rgaActionNeedReplace = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':NonStk-Sample:') !== false) {
											$rgaActionNeedNonStock = 'CHECKED';
										}
										
										if (strpos($row2['ACTION_NEEDED'], ':Credit:') !== false) {
											$rgaActionNeedCredit = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':Other:') !== false) {
											$rgaActionNeedOther = 'CHECKED';
										}

										//selects radio button for car
										$carFlagN = '';
										$carFlagY = '';
										if($row2['FLAG_CAR'] == 'N'){
											$carFlagN = "checked = 'checked'";
										}
										if($row2['FLAG_CAR'] == 'Y'){
											$carFlagY = "checked = 'checked";
										}
										$ret .= "<table width='850' id='table_inv_".$y."' style='".$style."'>\n";
										$ret .= "		<th colspan='4'>ISO investigation ".$seqInvest."</th>\n";
										$ret .= "		<input id='hidden_RGA_NUMBER' name='hidden_RGA_NUMBER' type='hidden' value='" . $rgaNumber . "'>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_RGA_RATING_".$seqInvest."' style='diplay:table-row;'>\n";
										$ret .= "		<td>RGA Rating (choose one):</td>\n";
										$ret .= "		<td><input type='radio' id='1_".$seqInvest."' ".$rgaRatingSelect1." name='level' value='1' $READONLY>Level 1</input></td>\n";
										$ret .= "		<td><input type='radio' id='2_".$seqInvest."' ".$rgaRatingSelect2." name='level' value='2' $READONLY>Level 2</input></td>\n";
										$ret .= "		<td><input type='radio' id='3_".$seqInvest."'' ".$rgaRatingSelect3." name='level' value='3' $READONLY>Level 3</input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Findings:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_findings_".$seqInvest."' name='txt_findings_".$seqInvest."' cols='55' $READONLY >".$row2['FINDINGS']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Cause:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_cause_".$seqInvest."' name='txt_cause_".$seqInvest."' cols='55' $READONLY >".$row2['CAUSE']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Containment:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_contain_".$seqInvest."' name='txt_contain_".$seqInvest."' cols='55' $READONLY >".$row2['CONTAINMENT']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Correction:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_corr_".$seqInvest."' name='txt_corr_".$seqInvest."' cols='55' $READONLY >".$row2['CORRECTION']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";

										$ret .= "<table width='850'  id='table_action_".$y."' style='".$style."'>\n";
										$ret .= "	<th colspan='7'></th>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td><strong>Action Needed:</strong></td>\n";
										$ret .= "		<td style='text-align:left;'>\n";
										$ret .= "			<input type='checkbox' id='action_Rework_".$seqInvest."' name='action_Rework_".$seqInvest."' $rgaActionNeedRework value=':Rework:' $READONLY>Rework</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Replace_".$seqInvest."' name='action_Replace_".$seqInvest."' $rgaActionNeedReplace value=':Replace:' $READONLY>Replace</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_NonStk-Sample_".$seqInvest."' name='action_NonStk-Sample_".$seqInvest."' $rgaActionNeedNonStock value=':NonStk-Sample:' $READONLY>NonStock/Sample</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Credit_".$seqInvest."' name='action_Credit_".$seqInvest."' $rgaActionNeedCredit value=':Credit:' $READONLY>Credit</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Other_".$seqInvest."' name='action_Other_".$seqInvest."' $rgaActionNeedOther value=':Other:' $READONLY>Other</input>\n";
										$ret .= "		</td>\n";
										$ret .= "		<td colspan=5><textarea id='txt_desc_".$seqInvest."' name='txt_desc_".$seqInvest."' $READONLY>".$row2['ACTION_DESCR']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td colspan=2>Is a Corrective Action Request (CAR) required? (select one)</td>\n";
										$ret .= "		<td><input type='radio' ".$carFlagN." id='N' name='pick' value='N'>No</input></td>\n";
										$ret .= "		<td><input type='radio' ".$carFlagY." id='Y' name='pick' value='Y'>Yes</input></td>\n";
										$ret .= "		<td>CAR #</td>\n";
										$ret .= "		<td colspan=2><input id='txt_carNumber_".$seqInvest."' name='txt_carNumber_".$seqInvest."' type='text' $READONLY value='".$row2['CAR_NUMBER']."' maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td colspan=2>Proposed action approved by:</td>\n";
										$ret .= "		<td colspan=2><input id='txt_approve_".$seqInvest."' name='txt_approve_".$seqInvest."' type='text' $READONLY value='".$row2['APPROVED_BY']."' maxlength='30'></input></td>\n";
										$ret .= "		<td>Date (yyyy-mm-dd):</td>\n";
										$ret .= "		<td colspan=2><input id='txt_dateSubmit_".$seqInvest."' name='txt_dateSubmit_".$seqInvest."' type='text' $READONLY value='".$row2['riDATE_APPROVED']."'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";

										$ret .= "<table width='850'  id='table_track_".$y."' style='".$style."'>\n";
										$ret .= " 	<th colspan='4'>For Tracking Only</th>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Department:</td>\n";
										$ret .= "		<td><input id='txt_dept_".$seqInvest."' name='txt_dept_".$seqInvest."' type='text' $READONLY value='".$row2['DEPARTMENT']."' maxlength='25'></input></td>\n";
										$ret .= "		<td>Request/Error:</td>\n";
										$ret .= "		<td><select id='select_err_".$seqInvest."' name='select_err_".$seqInvest."' $READONLY>\n";
										$ret .= "			<option value='SELECT'>--Select--</option>\n";

										$REQ_ERR = array("Error","Request", "Dispute");
										foreach($REQ_ERR as $SELECT_ERR) {
											$SELECTED = '';
											$CURRENT = '';

											if (trim($row2['REQ_ERR']) == trim($SELECT_ERR)) {
												$SELECTED = 'SELECTED';
												$CURRENT = '*';
											}
											$ret .= "				<option value='" . $SELECT_ERR . "' " . $SELECTED . ">" . $CURRENT . $SELECT_ERR .  "</option>\n";
										}
										$ret .= "		</select></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Workcenter:</td>\n";
										$ret .= "		<td><input id='select_workcenter_".$seqInvest."' name='select_workcenter_".$seqInvest."' type='text' $READONLY value='".$row2['WORKCENTER']."' maxlength='40'></input></td>\n";
										$ret .= "		<td>Credit Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoice_".$seqInvest."' name='txt_invoice_".$seqInvest."' type='text' $READONLY value='".$row2['ID_INVC_CRED']."' maxlength='8'></input></td>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Team/Individual:</td>\n";
										$ret .= "		<td><input id='txt_team_".$seqInvest."' name='txt_team_".$seqInvest."' type='text' $READONLY value='".$row2['ID_TEAM']."' maxlength='25'></input></td>\n";
										$ret .= "		<td>Component Costs:</td>\n";
										$ret .= "		<td><input id='txt_compCost_".$seqInvest."' name='txt_compCost_".$seqInvest."' type='text' $READONLY value='".$row2['COST_COMP']."'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Error Type:</td>\n";
										$ret .= "		<td><input id='txt_errorType_".$seqInvest."' name='txt_errorType_".$seqInvest."' type='text' $READONLY value='".$row2['ERR_TYPE']."'></input></td>\n";
										$ret .= "		<td>Labor Costs:</td>\n";
										$ret .= "		<td><input id='txt_laborCost_".$seqInvest."' name='txt_laborCost_".$seqInvest."' type='text' $READONLY value='".$row2['COST_LAB']."'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Vendor ID:</td>\n";
										$ret .= "		<td><input id='txt_vendor_".$seqInvest."' name='txt_vendor_".$seqInvest."' type='text' $READONLY value='".$row2['ID_VND']."' maxlength='6'></input></td>\n";
										$ret .= "		<td>Shipping Costs:</td>\n";
										$ret .= "		<td><input id='txt_shipCost_".$seqInvest."' name='txt_shipCost_".$seqInvest."' type='text' $READONLY value='".$row2['COST_SHIP']."'></input></td>\n";
										$ret .= "	</tr>\n";				
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_isoPartNumber_".$seqInvest."' name='txt_isoPartNumber_".$seqInvest."' type='text' $READONLY value='".$row2['ID_ITEM_VND']."' maxlength='30'></input></td>\n";
										$ret .= "		<td>Total RGA Costs:</td>\n";
										$ret .= "		<td><input id='txt_totCost_".$seqInvest."' name='txt_totCost_".$seqInvest."' disabled type='text'  value='".$row2['COST_TOT']."'></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>ISO Status:</td>\n";

										if(empty($row2['isoRowID']) ) {
											$ret .= "		<td><select id='select_iso_status_".$seqInvest."' name='select_iso_status_".$seqInvest."' $READONLY>\n";
											$ret .= "           <option value=''>-- Select --</option>\n";
											$ret .= "           <option value='Drafted'>Drafted</option>\n";
											$ret .= "           <option value='Pending Extenuating Issues'>Pending Extenuating Issues</option>\n";
											$ret .= "           <option value='Waiting for Approval'>Waiting for Approval</option>\n";
											$ret .= "           <option value='Waiting for Production'>Waiting for Production</option>\n";
											$ret .= "           <option value='Waiting for Customer Service'>Waiting for Customer Service</option>\n";
											$ret .= "           <option value='Waiting for Inventory Audit'>Waiting for Inventory Audit</option>\n";
											$ret .= "           <option value='Waiting for Manufacturer Response'>Waiting for Manufacturer Response</option>\n";
											$ret .= "           <option value='Waiting for Pricing Info'>Waiting for Pricing Info</option>\n";
											$ret .= "           <option value='Waiting for Rework/Replacement Number'>Waiting for Rework/Replacement Number</option>\n";
											$ret .= "           <option value='Working with Production Development'>Working with Production Development</option>\n";
											$ret .= "           <option value='Working with Purchasing'>Working with Purchasing</option>\n";
											$ret .= "           <option value='Closed'>Closed</option>\n";
											$ret .= "		</select></td>\n";
										} else {
											$ret .= "		<td><select id='select_iso_status_".$seqInvest."' name='select_iso_status_".$seqInvest."' $READONLY>\n";

											$isoStatus = array("Drafted", 
												"Pending Extenuating Issues", 
												"Waiting for Approval", 
												"Waiting for Production", 
												"Waiting for Customer Service", 
												"Waiting for Inventory Audit", 
												"Waiting for Manufacturer Response", 
												"Waiting for Pricing Info", 
												"Waiting for Rework/Replacement Number", 
												"Working with Production Development", 
												"Working with Purchasing", 
												"Closed");
											foreach ($isoStatus as $SELECT_ISO_STATUS) {
												$SELECTED = '';
												$CURRENT = '';

												if (trim($row2['RGA_ISO_STATUS']) == trim($SELECT_ISO_STATUS)) {
													$SELECTED = 'SELECTED';
													$CURRENT = '*';
												}
												$ret .= "				<option value='" . $SELECT_ISO_STATUS . "' " . $SELECTED . ">" . $CURRENT . $SELECT_ISO_STATUS .  "</option>\n";
											}
											$ret .= "		</select></td>\n";
										}

										if ($seqInvest==1){
											$ret .= "		<td>RGA Status:</td>\n";
											$ret .= "		<td><select id='select_rga_status_".$seqInvest."' name='select_rga_status_".$seqInvest."' onchange=\"checkChangeRgaIsoStatus($seqInvest)\" $READONLY>\n";

											$RGA_STATUS_ARRAY = array(
												array("","--Select--"),
												array("Open, Waiting for Return","Open, Waiting for Return"),
												array("Open, Response to Customer Required","Open, Response to Customer Required"),
												array("Open, Inspection Required","Open, Inspection Required"),
												array("ISO Action Required","ISO Action Required"),
												array("Cancelled","Cancelled"),
												array("Closed","Closed"),
												
												//array("Open","Open"),
												//array("Pending","Pending Investigation"),
												//array("Cancelled","Cancelled"),
												//array("Closed","Closed"),

											);
											for ($rowStat = 0; $rowStat < 5; $rowStat++) {
												$SELECTED = '';
												$CURRENT = '';

												if (trim($row['RGA_STATUS']) == trim($RGA_STATUS_ARRAY[$rowStat][0])) {
													$SELECTED = 'SELECTED';
													$CURRENT = '*';
												}
												$ret .= "				<option value='" . $RGA_STATUS_ARRAY[$rowStat][0] . "' " . $SELECTED . ">" . $CURRENT . $RGA_STATUS_ARRAY[$rowStat][1] .  "</option>\n";
											}

											$ret .= "		</select></td>\n";
										} else {
											$ret .= "		<td colspan=2></td>\n";
										}	

										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										//$ret .= "		<td colspan=7><input id='button_insertNewInvest_".$seqInvest."' DISABLED name='button_insertNewInvest_".$seqInvest."' type='button' $READONLY value='Submit'></input><div id='div_submitResp2' name='div_submitResp2'></div></td>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= " 			<input id='button_insertNewInvest_".$seqInvest."' name='button_insertNewInvest_".$seqInvest."' type='button' value='Submit' onclick=\"insertNewInvest('".$seqInvest."')\" DISABLED></input>\n";
										$ret .= " 				**Send Email?<select id='sel_Email_insertNewInvest_".$seqInvest."' name='sel_Email_insertNewInvest_".$seqInvest."' DISABLED onchange=\"checkEnableButton('sel_Email_insertNewInvest_".$seqInvest."','button_insertNewInvest_".$seqInvest."')\">\n";
										//$ret .= " 				**Send Email?<select id='sel_Email_insertNewInvest_".$seqInvest."' name='sel_Email_insertNewInvest_".$seqInvest."' DISABLED>\n";
										$ret .= "   	        <option value=''>--Select--</option>\n";
										$ret .= "   	        <option value='Send'>Send</option>\n";
										$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
										$ret .= "			</select>\n";
										$ret .= "			<div id='div_submitResp_insertNewInvest_".$seqInvest."' name='div_submitResp_insertNewInvest_".$seqInvest."'></div>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_invest".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= "			<font style='cursor: hand' title='Add Blank Record' onclick=\"showInvestTable('$z')\">+Blank   </font>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";										
										$ret .= "</table>\n";
									}
								}
							}
						}			
					break;

					case "updateBase":	
						if (isset($_POST["rgaNumber"]) && isset($_POST["custNumber"]) && isset($_POST["custName"]) && isset($_POST["city"]) && isset($_POST["state"]) && 
						isset($_POST["country"]) && isset($_POST["contactName"]) && isset($_POST["phoneNumber"]) && isset($_POST["email"]) && isset($_POST["prov"]) &&
						isset($_POST["authorized"]) && isset($_POST["salesMgr"]) && isset($_POST["territory"]) && isset($_POST["descr"]) && isset($_POST["sendEmail"]) &&
						//isset($_POST["rgaClass"]) && 
						isset($_POST["selectExpectingReturn"]) && isset($_POST["followUp"]) && isset($_POST["addInfo"]) && isset($_POST["BaseRowID"]) && isset($_POST["emailNotify"]) && isset($_POST["classification"]) &&
						isset($_POST["itemNumber1"]) && isset($_POST["orderNumber1"]) && isset($_POST["poNumber1"]) && isset($_POST["invoiceNumber1"]) && isset($_POST["quant1"]) && isset($_POST["dateShipped1"]) &&
						isset($_POST["itemNumber2"]) && isset($_POST["orderNumber2"]) && isset($_POST["poNumber2"]) && isset($_POST["invoiceNumber2"]) && isset($_POST["quant2"]) && isset($_POST["dateShipped2"]) &&
						isset($_POST["itemNumber3"]) && isset($_POST["orderNumber3"]) && isset($_POST["poNumber3"]) && isset($_POST["invoiceNumber3"]) && isset($_POST["quant3"]) && isset($_POST["dateShipped3"]) &&
						isset($_POST["itemNumber4"]) && isset($_POST["orderNumber4"]) && isset($_POST["poNumber4"]) && isset($_POST["invoiceNumber4"]) && isset($_POST["quant4"]) && isset($_POST["dateShipped4"]) &&
						isset($_POST["itemNumber5"]) && isset($_POST["orderNumber5"]) && isset($_POST["poNumber5"]) && isset($_POST["invoiceNumber5"]) && isset($_POST["quant5"]) && isset($_POST["dateShipped5"]) &&
						isset($_POST["itemNumber6"]) && isset($_POST["orderNumber6"]) && isset($_POST["poNumber6"]) && isset($_POST["invoiceNumber6"]) && isset($_POST["quant6"]) && isset($_POST["dateShipped6"]) &&
						isset($_POST["itemNumber7"]) && isset($_POST["orderNumber7"]) && isset($_POST["poNumber7"]) && isset($_POST["invoiceNumber7"]) && isset($_POST["quant7"]) && isset($_POST["dateShipped7"]) &&
						isset($_POST["itemNumber8"]) && isset($_POST["orderNumber8"]) && isset($_POST["poNumber8"]) && isset($_POST["invoiceNumber8"]) && isset($_POST["quant8"]) && isset($_POST["dateShipped8"]) &&
						isset($_POST["itemNumber9"]) && isset($_POST["orderNumber9"]) && isset($_POST["poNumber9"]) && isset($_POST["invoiceNumber9"]) && isset($_POST["quant9"]) && isset($_POST["dateShipped9"]) &&
						isset($_POST["itemNumber10"]) && isset($_POST["orderNumber10"]) && isset($_POST["poNumber10"]) && isset($_POST["invoiceNumber10"]) && isset($_POST["quant10"]) && isset($_POST["dateShipped10"])
						){
							$rgaNumber = $_POST["rgaNumber"];
							$BaseRowID = $_POST["BaseRowID"];
							$custNumber = $_POST["custNumber"];
							$custName = $_POST["custName"];
							$city = $_POST["city"];
							$state = $_POST["state"];
							$prov = $_POST["prov"];
							$country = $_POST["country"];
							$contactName = $_POST["contactName"];
							$phoneNumber = $_POST["phoneNumber"];
							$email = $_POST["email"];
							$classification = $_POST["classification"];
							$authorized = $_POST["authorized"];
							$sendEmail = $_POST["sendEmail"];
							
							$salesMgr_FULL = $_POST["salesMgr"];
							$pos = strrpos($salesMgr_FULL, '_');
							$salesMgr = substr($salesMgr_FULL, $pos + 1);

							$territory = $_POST["territory"];
							$descr = $_POST["descr"];
							//$rgaClass = $_POST["rgaClass"];
							$expectingReturn = $_POST["selectExpectingReturn"];
							$followUp = $_POST["followUp"];
							$emailNotify = $_POST["emailNotify"];
							$addInfo = $_POST["addInfo"];
		
							$sql  = "SELECT RGA_STATUS, DESCR ";
							$sql .= " FROM nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
							$sql .= " WHERE rowid = ". $BaseRowID;
							QueryDatabase($sql, $results);
							while ($row = mssql_fetch_assoc($results)) {
								$rgaStatus = $row['RGA_STATUS'];
								$prev_descr = trim($row['DESCR']);
							}

/*							if ($rgaClass == "B" && $rgaStatus == "Open") {
								//$rgaStatus = "Pending";
								$rgaStatus = "Open, Response to Customer Required";
							}
*/

							//if () {
							//}


							$itemNumber1 = $_POST["itemNumber1"];
							$itemNumber2 = $_POST["itemNumber2"];
							$itemNumber3 = $_POST["itemNumber3"];
							$itemNumber4 = $_POST["itemNumber4"];
							$itemNumber5 = $_POST["itemNumber5"];
							$itemNumber6 = $_POST["itemNumber6"];
							$itemNumber7 = $_POST["itemNumber7"];
							$itemNumber8 = $_POST["itemNumber8"];
							$itemNumber9 = $_POST["itemNumber9"];
							$itemNumber10 = $_POST["itemNumber10"];

							$orderNumber1 = $_POST["orderNumber1"];
							$orderNumber2 = $_POST["orderNumber2"];
							$orderNumber3 = $_POST["orderNumber3"];
							$orderNumber4 = $_POST["orderNumber4"];
							$orderNumber5 = $_POST["orderNumber5"];
							$orderNumber6 = $_POST["orderNumber6"];
							$orderNumber7 = $_POST["orderNumber7"];
							$orderNumber8 = $_POST["orderNumber8"];
							$orderNumber9 = $_POST["orderNumber9"];
							$orderNumber10 = $_POST["orderNumber10"];

							$poNumber1 = $_POST["poNumber1"];
							$poNumber2 = $_POST["poNumber2"];
							$poNumber3 = $_POST["poNumber3"];
							$poNumber4 = $_POST["poNumber4"];
							$poNumber5 = $_POST["poNumber5"];
							$poNumber6 = $_POST["poNumber6"];
							$poNumber7 = $_POST["poNumber7"];
							$poNumber8 = $_POST["poNumber8"];
							$poNumber9 = $_POST["poNumber9"];
							$poNumber10 = $_POST["poNumber10"];

							$invoiceNumber1 = $_POST["invoiceNumber1"];
							$invoiceNumber2 = $_POST["invoiceNumber2"];
							$invoiceNumber3 = $_POST["invoiceNumber3"];
							$invoiceNumber4 = $_POST["invoiceNumber4"];
							$invoiceNumber5 = $_POST["invoiceNumber5"];
							$invoiceNumber6 = $_POST["invoiceNumber6"];
							$invoiceNumber7 = $_POST["invoiceNumber7"];
							$invoiceNumber8 = $_POST["invoiceNumber8"];
							$invoiceNumber9 = $_POST["invoiceNumber9"];
							$invoiceNumber10 = $_POST["invoiceNumber10"];

							$quant1 = $_POST["quant1"];
							$quant2 = $_POST["quant2"];
							$quant3 = $_POST["quant3"];
							$quant4 = $_POST["quant4"];
							$quant5 = $_POST["quant5"];
							$quant6 = $_POST["quant6"];
							$quant7 = $_POST["quant7"];
							$quant8 = $_POST["quant8"];
							$quant9 = $_POST["quant9"];
							$quant10 = $_POST["quant10"];

							$dateShipped1 = $_POST["dateShipped1"];
							$dateShipped2 = $_POST["dateShipped2"];
							$dateShipped3 = $_POST["dateShipped3"];
							$dateShipped4 = $_POST["dateShipped4"];
							$dateShipped5 = $_POST["dateShipped5"];
							$dateShipped6 = $_POST["dateShipped6"];
							$dateShipped7 = $_POST["dateShipped7"];
							$dateShipped8 = $_POST["dateShipped8"];
							$dateShipped9 = $_POST["dateShipped9"];
							$dateShipped10 = $_POST["dateShipped10"];

							$sql = " update nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
							$sql .= " SET ";
							$sql .= " ID_USER_CHG = '". $UserRow['ID_USER'] . "', ";
							$sql .= " DATE_CHG = getDate(), ";
							$sql .= " ID_CUST = '" . ms_escape_string($custNumber) . "', ";
							$sql .= " NAME_CUST = '" . ms_escape_string($custName) . "', ";
							$sql .= " CITY = '" . ms_escape_string($city) . "', ";
							$sql .= " ID_ST = '" . ms_escape_string($state) . "', ";
							$sql .= " PROV = '" . ms_escape_string($prov) . "', ";
							$sql .= " COUNTRY = '" . ms_escape_string($country) . "', ";
							$sql .= " CONTACT_NAME = '" . ms_escape_string($contactName) . "', ";
							$sql .= " PHONE_NUMBER = '" . ms_escape_string($phoneNumber) . "', ";
							$sql .= " EMAIL = '" . ms_escape_string($email) . "', ";
							$sql .= " CLASSIFICATION = '" . ms_escape_string($classification) . "', ";
							$sql .= " AUTHORIZED_BY = '" . $authorized . "', ";
							$sql .= " SALES_MGR = '" . $salesMgr . "', ";
							$sql .= " ID_TERR = '" . ms_escape_string($territory) . "', ";
							$sql .= " DESCR = '" . ms_escape_string($descr) . "', ";
							$sql .= " RGA_CLASS = '" . $rgaClass . "', ";
							$sql .= " FOLLOW_UP_DESCR = '" . ms_escape_string($followUp) . "', ";
							$sql .= " EMAIL_LIST = '" . ms_escape_string($emailNotify) . "', ";
							$sql .= " RGA_STATUS = '" . $rgaStatus . "', ";
							$sql .= " ADD_INFO = '" . ms_escape_string($addInfo) . "' ";
							$sql .= " WHERE  rowid = ".$BaseRowID;
							QueryDatabase($sql, $results);

							for ($y = 1; $y <= 10; $y++) {
								if(${"itemNumber".$y} <> '' || ${"orderNumber".$y} <> '' || ${"poNumber".$y} <> '' || 
									${"invoiceNumber".$y} <> '' || ${"quant".$y} <> '' || ${"dateShipped".$y} <> ''
								){
									$sql  = "SELECT * from nsa.RGA_LINE" . $DB_TEST_FLAG . " ";
									$sql .= " WHERE RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql .= " AND SEQ_LINE_RGA = '" . $y . "' ";
									QueryDatabase($sql, $results);

									if (mssql_num_rows($results) <> 0) {
										//error_log("updating nsa.RGA_LINE RGA#: ". $rgaNumber ." SEQ: " . $y);
										$sql1 = " UPDATE nsa.RGA_LINE" . $DB_TEST_FLAG . " ";
										$sql1 .= " set ID_ITEM = '" . ms_escape_string(${"itemNumber".$y}) . "', ";
										$sql1 .= " ID_ORD = '" . ms_escape_string(${"orderNumber".$y}) . "', ";
										$sql1 .= " ID_PO = '" . ms_escape_string(${"poNumber".$y}) . "', ";
										$sql1 .= " ID_INVC = '" . ms_escape_string(${"invoiceNumber".$y}) . "', ";
										$sql1 .= " QUANTITY = '" . ms_escape_string(${"quant".$y}) . "', ";
										if (${"dateShipped".$y} <> '') {
											$sql1 .= " DATE_SHIPPED = '" . ms_escape_string(${"dateShipped".$y}) . "', ";	
										}
										
										$sql1 .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
										$sql1 .= " DATE_CHG = GetDate() ";
										$sql1 .= " WHERE RGA_NUMBER = '" . $rgaNumber . "' ";
										$sql1 .= " AND SEQ_LINE_RGA = '" . $y . "' ";
										QueryDatabase($sql1, $results1);
									} else {
										//error_log("INSERTING nsa.RGA_LINE RGA#: ". $rgaNumber ." SEQ: " . $y);
										$sql1  = " INSERT into nsa.RGA_LINE" . $DB_TEST_FLAG . " ( ";
										$sql1 .= " RGA_BASE_rowid, ";
										$sql1 .= " RGA_NUMBER, ";
										$sql1 .= " SEQ_LINE_RGA, ";
										$sql1 .= " ID_ITEM, ";
										$sql1 .= " QUANTITY, ";
										$sql1 .= " ID_ORD, ";
										$sql1 .= " ID_PO, ";
										$sql1 .= " ID_INVC,";
										if (${"dateShipped".$y} <> '') {
											$sql1 .= " DATE_SHIPPED, ";
										}
										$sql1 .= " ID_USER_ADD, ";
										$sql1 .= " DATE_ADD ";
										$sql1 .= " ) VALUES ( ";
										$sql1 .= " '" . $BaseRowID ."', ";
										$sql1 .= " '" . $rgaNumber ."', ";
										$sql1 .= " '" . $y ."', ";
										$sql1 .= " '" . ms_escape_string(${"itemNumber".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"quant".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"orderNumber".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"poNumber".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"invoiceNumber".$y}) . "', ";
										if (${"dateShipped".$y} <> '') {
											$sql1 .= " '" . ${"dateShipped".$y} . "', ";
										}
										$sql1 .= " '" . $UserRow['ID_USER'] . "', ";
										$sql1 .= " GetDate() ";
										$sql1 .= " ) ";
										QueryDatabase($sql1, $results1);										
									}
								} 
							}

							if (trim($sendEmail) == 'Send') {
								$sql  = " SELECT rb.NAME_CUST, ";
								$sql .= " rb.SALES_MGR, ";
								$sql .= " sr.ADDR_EMAIL as EMAIL_SLSREP, ";
								$sql .= " rb.FLAG_EMAIL_SENT ";
								$sql .= " FROM nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
								$sql .= " left join nsa.tables_slsrep sr ";
								$sql .= " on rb.SALES_MGR = sr.NAME_SLSREP ";
								$sql .= " and sr.ADDR_EMAIL is not null ";
								$sql .= " WHERE rowid = '". $BaseRowID ."'";
								QueryDatabase($sql, $results);
								while ($row = mssql_fetch_assoc($results)) {
									if ($row['FLAG_EMAIL_SENT'] == 'T') {
										// NEW RGA EMAIL HAS ALREADY BEEN SENT, SEND AS UPDATE WITHOUT ATTACHMENTS
										if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
											$head = array(
										    	'to'      =>array('TESTGroup-RGA@thinknsa.com'=>'Group-RGA'),
										    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
										    	//'cc'      =>array($row['EMAIL_SLSREP']=>$row['SALES_MGR']),
										    	//'bcc'     =>array('email4@email.net'=>'Admin'),
										    );
								    	} else {
									    	$head = array(
										    	//'to'      =>array('TESTGroup-RGA@thinknsa.com'=>'Group-RGA'),
										    	'to'      =>array('Group-RGA@thinknsa.com'=>'Group-RGA'),
										    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
										    	//'cc'      =>array($row['EMAIL_SLSREP']=>$row['SALES_MGR']),
										    	//'bcc'     =>array('email4@email.net'=>'Admin'),
								    		);
								    	}

										$files = array();
										$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*.{jpg,png,gif,bmp,tif,pdf}", GLOB_BRACE);
										foreach ($a_files as $filename){
											$short_filename = substr($filename, strrpos($filename, '/') + 1);
											$tempFilename = "/tmp/RGA_temp/" . $short_filename;
											shell_exec("cp " . $filename . " " . $tempFilename);
											array_push($files, $tempFilename);
										}
										$subject = "UPDATED RGA " . $rgaNumber . " - " . $custName;	
										$body = GenerateHTMLforEmail($rgaNumber);

										if (!empty($files)) {
											mail::send($head,$subject,$body,$files);
										} else {
											mail::send($head,$subject,$body);
										}
									} else {
										if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
									    	$head = array(
										    	'to'      =>array('TESTGroup-RGA@thinknsa.com'=>'Group-RGA'),
										    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
										    	'cc'      =>array('gvandyne@thinknsa.com'=>$row['SALES_MGR']),
										    	//'bcc'     =>array('email4@email.net'=>'Admin'),
										    );	
									    } else {
									    	$head = array(
												//'to'      =>array('TESTGroup-RGA@thinknsa.com'=>'Group-RGA'),
										    	'to'      =>array('Group-RGA@thinknsa.com'=>'Group-RGA'),
										    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
									    		'cc'      =>array($row['EMAIL_SLSREP']=>$row['SALES_MGR']),
									    		//'bcc'     =>array('email4@email.net'=>'Admin'),
									    	);
									    }

										$files = array();
										$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*.{jpg,png,gif,bmp,tif,pdf}", GLOB_BRACE);
										foreach ($a_files as $filename){
											$short_filename = substr($filename, strrpos($filename, '/') + 1);
											$tempFilename = "/tmp/RGA_temp/" . $short_filename;
											shell_exec("cp " . $filename . " " . $tempFilename);
											array_push($files, $tempFilename);
										}

										$subject = "NEW RGA " . $rgaNumber . " - " . $custName;
										$body = GenerateHTMLforEmail($rgaNumber);
										
										if (!empty($files)) {
											mail::send($head,$subject,$body,$files);
										} else {
											mail::send($head,$subject,$body);
										}

										$sql1  = " UPDATE nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
										$sql1 .= " SET FLAG_EMAIL_SENT = 'T' ";
										$sql1 .= " WHERE rowid = '". $BaseRowID ."'";
										QueryDatabase($sql1, $results1);
									}
								}
							}
							$ret .= "SAVED </br>" . date('Y-m-d H:i:s');
						}
					break;	
					











					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//SHIPPING RECORD STUFF
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					case "submit_newreq_shipping":
						if (isset($_POST["rgaNumber"]) && isset($_POST["sendEmail"]) && 
							isset($_POST["itemReceived1"]) && isset($_POST["dateReceived1"]) && isset($_POST["quantity1"]) && isset($_POST["condition1"]) && isset($_POST["receivedBy1"]) && isset($_POST["carrier1"]) && isset($_POST["trackingNumber1"]) && isset($_POST["shipComments1"]) &&
							isset($_POST["itemReceived2"]) && isset($_POST["dateReceived2"]) && isset($_POST["quantity2"]) && isset($_POST["condition2"]) && isset($_POST["receivedBy2"]) && isset($_POST["carrier2"]) && isset($_POST["trackingNumber2"]) && isset($_POST["shipComments2"]) &&
							isset($_POST["itemReceived3"]) && isset($_POST["dateReceived3"]) && isset($_POST["quantity3"]) && isset($_POST["condition3"]) && isset($_POST["receivedBy3"]) && isset($_POST["carrier3"]) && isset($_POST["trackingNumber3"]) && isset($_POST["shipComments3"]) &&
							isset($_POST["itemReceived4"]) && isset($_POST["dateReceived4"]) && isset($_POST["quantity4"]) && isset($_POST["condition4"]) && isset($_POST["receivedBy4"]) && isset($_POST["carrier4"]) && isset($_POST["trackingNumber4"]) && isset($_POST["shipComments4"]) &&
							isset($_POST["itemReceived5"]) && isset($_POST["dateReceived5"]) && isset($_POST["quantity5"]) && isset($_POST["condition5"]) && isset($_POST["receivedBy5"]) && isset($_POST["carrier5"]) && isset($_POST["trackingNumber5"]) && isset($_POST["shipComments5"]) &&
							isset($_POST["itemReceived6"]) && isset($_POST["dateReceived6"]) && isset($_POST["quantity6"]) && isset($_POST["condition6"]) && isset($_POST["receivedBy6"]) && isset($_POST["carrier6"]) && isset($_POST["trackingNumber6"]) && isset($_POST["shipComments6"]) &&
							isset($_POST["itemReceived7"]) && isset($_POST["dateReceived7"]) && isset($_POST["quantity7"]) && isset($_POST["condition7"]) && isset($_POST["receivedBy7"]) && isset($_POST["carrier7"]) && isset($_POST["trackingNumber7"]) && isset($_POST["shipComments7"]) &&
							isset($_POST["itemReceived8"]) && isset($_POST["dateReceived8"]) && isset($_POST["quantity8"]) && isset($_POST["condition8"]) && isset($_POST["receivedBy8"]) && isset($_POST["carrier8"]) && isset($_POST["trackingNumber8"]) && isset($_POST["shipComments8"]) &&
							isset($_POST["itemReceived9"]) && isset($_POST["dateReceived9"]) && isset($_POST["quantity9"]) && isset($_POST["condition9"]) && isset($_POST["receivedBy9"]) && isset($_POST["carrier9"]) && isset($_POST["trackingNumber9"]) && isset($_POST["shipComments9"]) &&
							isset($_POST["itemReceived10"]) && isset($_POST["dateReceived10"]) && isset($_POST["quantity10"]) && isset($_POST["condition10"]) && isset($_POST["receivedBy10"]) && isset($_POST["carrier10"]) && isset($_POST["trackingNumber10"]) && isset($_POST["shipComments10"])) 
						{
							$rgaNumber = $_POST["rgaNumber"];
							$sendEmail = $_POST["sendEmail"];
							$itemReceived1 = $_POST["itemReceived1"];
							$dateReceived1 = $_POST["dateReceived1"];
							$quantity1 = $_POST["quantity1"];
							$condition1 = $_POST["condition1"];
							$receivedBy1 = $_POST["receivedBy1"];
							$carrier1 = $_POST["carrier1"];
							$trackingNumber1 = $_POST["trackingNumber1"];
							$shipComments1 = $_POST["shipComments1"];

							$itemReceived2 = $_POST["itemReceived2"];
							$dateReceived2 = $_POST["dateReceived2"];
							$quantity2 = $_POST["quantity2"];
							$condition2 = $_POST["condition2"];
							$receivedBy2 = $_POST["receivedBy2"];
							$carrier2 = $_POST["carrier2"];
							$trackingNumber2 = $_POST["trackingNumber2"];
							$shipComments2 = $_POST["shipComments2"];

							$itemReceived3 = $_POST["itemReceived3"];
							$dateReceived3 = $_POST["dateReceived3"];
							$quantity3 = $_POST["quantity3"];
							$condition3 = $_POST["condition3"];
							$receivedBy3 = $_POST["receivedBy3"];
							$carrier3 = $_POST["carrier3"];
							$trackingNumber3 = $_POST["trackingNumber3"];
							$shipComments3 = $_POST["shipComments3"];

							$itemReceived4 = $_POST["itemReceived4"];
							$dateReceived4 = $_POST["dateReceived4"];
							$quantity4 = $_POST["quantity4"];
							$condition4 = $_POST["condition4"];
							$receivedBy4 = $_POST["receivedBy4"];
							$carrier4 = $_POST["carrier4"];
							$trackingNumber4 = $_POST["trackingNumber4"];
							$shipComments4 = $_POST["shipComments4"];

							$itemReceived5 = $_POST["itemReceived5"];
							$dateReceived5 = $_POST["dateReceived5"];
							$quantity5 = $_POST["quantity5"];
							$condition5 = $_POST["condition5"];
							$receivedBy5 = $_POST["receivedBy5"];
							$carrier5 = $_POST["carrier5"];
							$trackingNumber5 = $_POST["trackingNumber5"];
							$shipComments5 = $_POST["shipComments5"];

							$itemReceived6 = $_POST["itemReceived6"];
							$dateReceived6 = $_POST["dateReceived6"];
							$quantity6 = $_POST["quantity6"];
							$condition6 = $_POST["condition6"];
							$receivedBy6 = $_POST["receivedBy6"];
							$carrier6 = $_POST["carrier6"];
							$trackingNumber6 = $_POST["trackingNumber6"];
							$shipComments6 = $_POST["shipComments6"];

							$itemReceived7 = $_POST["itemReceived7"];
							$dateReceived7 = $_POST["dateReceived7"];
							$quantity7 = $_POST["quantity7"];
							$condition7 = $_POST["condition7"];
							$receivedBy7 = $_POST["receivedBy7"];
							$carrier7 = $_POST["carrier7"];
							$trackingNumber7 = $_POST["trackingNumber7"];
							$shipComments7 = $_POST["shipComments7"];

							$itemReceived8 = $_POST["itemReceived8"];
							$dateReceived8 = $_POST["dateReceived8"];
							$quantity8 = $_POST["quantity8"];
							$condition8 = $_POST["condition8"];
							$receivedBy8 = $_POST["receivedBy8"];
							$carrier8 = $_POST["carrier8"];
							$trackingNumber8 = $_POST["trackingNumber8"];
							$shipComments8 = $_POST["shipComments8"];

							$itemReceived9 = $_POST["itemReceived9"];
							$dateReceived9 = $_POST["dateReceived9"];
							$quantity9 = $_POST["quantity9"];
							$condition9 = $_POST["condition9"];
							$receivedBy9 = $_POST["receivedBy9"];
							$carrier9 = $_POST["carrier9"];
							$trackingNumber9 = $_POST["trackingNumber9"];
							$shipComments9 = $_POST["shipComments9"];

							$itemReceived10 = $_POST["itemReceived10"];
							$dateReceived10 = $_POST["dateReceived10"];
							$quantity10 = $_POST["quantity10"];
							$condition10 = $_POST["condition10"];
							$receivedBy10 = $_POST["receivedBy10"];
							$carrier10 = $_POST["carrier10"];
							$trackingNumber10 = $_POST["trackingNumber10"];
							$shipComments10 = $_POST["shipComments10"];

							$sql  = "SELECT rb.NAME_CUST, wa.EMAIL, wa.NAME_EMP ";
							$sql .= " FROM nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " WHERE rb.RGA_NUMBER = '" . $rgaNumber . "' ";
							QueryDatabase($sql, $results);
							$row = mssql_fetch_assoc($results);
							
							$custName = $row['NAME_CUST'];
							$originatorEmail = $row['EMAIL'];
							$originatorName = $row['NAME_EMP'];
							$LAST_INSERT_ID = '';

							for ($y = 1; $y <= 10; $y++) {
								if(${"itemReceived".$y} <> '' || ${"dateReceived".$y} <> '' || ${"quantity".$y} <> '' || 
									${"condition".$y} <> '' || ${"receivedBy".$y} <> '' || ${"carrier".$y} <> ''|| 
									${"trackingNumber".$y} <> '' || ${"shipComments".$y} <> '' 
								){
									//error_log("Inserting Seq LINESHIP: " . $y);
									$sql = " insert into nsa.RGA_SHIP" . $DB_TEST_FLAG . "( ";
								    $sql .= " RGA_BASE_rowid, ";
								    $sql .= " RGA_NUMBER, ";
									$sql .= " SEQ_LINE_SHIP, ";
									$sql .= " ID_USER_ADD, ";
									$sql .= " DATE_ADD, ";
									$sql .= " ITEM_RECEIVED, ";
									if (${"dateReceived".$y} <> '') {
										$sql .= " DATE_RECEIVED, ";
									}
									$sql .= " QUANTITY_RECEIVED, ";
									$sql .= " COND_RECEIVED, ";
									$sql .= " RECEIVED_BY,";
									$sql .= " CARRIER, ";
									$sql .= " TRACKING_NO ,";
									$sql .= " COMMENTS ";
									$sql .= " ) VALUES ( ";
									$sql .= " (Select rowid from nsa.RGA_BASE" . $DB_TEST_FLAG . " where RGA_NUMBER = '". $rgaNumber ."'), ";
									$sql .= " '" . $rgaNumber . "', ";
									$sql .= " '" . $y ."', ";
								    $sql .= " '" . $UserRow['ID_USER'] . "', ";									
									$sql .= " GetDate(), ";
									$sql .= " '" . strtoupper(ms_escape_string(${"itemReceived".$y})) . "', ";
									if (${"dateReceived".$y} <> '') {
										$sql .= " '" . ms_escape_string(${"dateReceived".$y}) . "', ";
									}
									$sql .= " '" . ms_escape_string(${"quantity".$y}) . "', ";
									$sql .= " '" . ms_escape_string(${"condition".$y}) . "', ";
									$sql .= " '" . strtoupper(ms_escape_string(${"receivedBy".$y})) . "', ";
									$sql .= " '" . strtoupper(ms_escape_string(${"carrier".$y})) . "', ";
									$sql .= " '" . strtoupper(ms_escape_string(${"trackingNumber".$y})) . "', ";
									$sql .= " '" . ms_escape_string(${"shipComments".$y}) . "' ";
									$sql .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
									QueryDatabase($sql, $results);
									$row = mssql_fetch_assoc($results);
									$LAST_INSERT_ID = $row['LAST_INSERT_ID'];

									$sql  = "UPDATE nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
									//$sql .= " set RGA_STATUS = 'Pending', ";
									$sql .= " set RGA_STATUS = 'Open, Inspection Required', ";
									$sql .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
									$sql .= " DATE_CHG = GetDate() ";
									$sql .= " WHERE RGA_NUMBER = '". $rgaNumber ."'";
									QueryDatabase($sql, $results);
								}
							}
							if ($LAST_INSERT_ID <> '') {
								if (trim($sendEmail) == 'Send'){
									if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
										$head = array(
											//'to'      =>array('TESTGroup-RGA-ShipReceived@thinknsa.com'=>'Group-RGA-ShipReceived'),
									    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
									    	'cc'      =>array($originatorEmail => $originatorName),
									    	//'bcc'     =>array('email4@email.net'=>'Admin'),
								    	);
								    } else {
								    	$head = array(
											//'to'      =>array('TESTGroup-RGA-ShipReceived@thinknsa.com'=>'Group-RGA-ShipReceived'),
											'to'      =>array('Group-RGA-ShipReceived@thinknsa.com'=>'Group-RGA-ShipReceived'),
									    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
									    	'cc'      =>array($originatorEmail => $originatorName),
									    	//'bcc'     =>array('email4@email.net'=>'Admin'),
									    );	
								    }

									$subject = "Items Received RGA " . $rgaNumber . " - " . $custName;
									$body = GenerateHTMLforEmail($rgaNumber);
									//$files = array($file1,$file2);
									mail::send($head,$subject,$body);//$files are optional param
								}
							}

							$ret .= "OK </br>" . date('Y-m-d H:i:s');
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}

					break;

					case "form_review_shippingreq":
						if (isset($_POST["rgaNumber"])) {
							$rgaNumber = $_POST["rgaNumber"];

							$sql =  "SELECT ";
							$sql .= " wa.NAME_EMP, ";
							//$sql .= " ri.rowid as isoRowID,";
							//$sql .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
							$sql .= " rb.ID_USER_ADD as rb_ID_USER_ADD, ";
							$sql .= " rb.* ";
							//$sql .= " ,ri.* ";
							$sql .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
							//$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
							//$sql .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " where ";
							$sql .= " rb.RGA_NUMBER = '" . $rgaNumber . "' ";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								 if (strpos($UserRow['EMP_ROLE'], ":RGA-SHIP:")) {
									$ret .= "<table width='850' >\n";
									$ret .= "	<tr class='blueHeader'>\n";
									$ret .= "		<th colspan='4'><left><img src=''></left> <right>RGA & Customer Complaint</right></th>\n";
									//$ret .= "		<input id='shipRowID' name='shipRowID' type='hidden' value='" . $row['shipRowID'] . "'>\n";
									//$ret .= "		<input id='isoRowID' name='isoRowID' type='hidden' value='" . $row['isoRowID'] . "'>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>RGA Number:</td>\n";
									$ret .= "		<td><input id='txt_rgaNumber' name='txt_rgaNumber' type='text' $READONLY value=" . $rgaNumber . " READONLY style='background-color:#D0D0D0;'></input></td>\n";
									$ret .= "		<td>Date Issued:</td>\n";
									$ret .= "		<td><input id='txt_date' name='txt_date' type='text' $READONLY value='" . $row['DATE_ISSUE'] . "'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Customer #:</td>\n";
									$ret .= "		<td><input id='txt_customerID' name='txt_customerID' type='text' $READONLY value='" . $row['ID_CUST'] . "' maxlength='6'></input></td>\n";
									$ret .= "		<td>Customer Name:</td>\n";
									$ret .= "		<td><input id='txt_NAME_CUST' name='txt_NAME_CUST' type='text' $READONLY value='".$row['NAME_CUST']."' maxlength='30' size=30></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>City:</td>\n";
									$ret .= "		<td><input id='txt_CITY' name='txt_CITY' type='text' $READONLY value='".$row['CITY']."' maxlength='15'></input></td>\n";
									$ret .= "		<td>State:</td>\n";
									$ret .= "		<td><input id='txt_ID_ST' name='txt_ID_ST' type='text' $READONLY value='" . $row['ID_ST'] . "' maxlength='2'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Prov:</td>\n";
									$ret .= "		<td><input id='txt_PROV' name='txt_PROV' type='text' $READONLY value='" . $row['PROV'] . "' maxlength='30'></input></td>\n";
									$ret .= "		<td>Country:</td>\n";
									$ret .= "		<td><input id='txt_COUNTRY' name='txt_COUNTRY' type='text' $READONLY value='" . $row['COUNTRY'] . "' maxlength='30'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Contact Name:</td>\n";
									$ret .= "		<td><input id='txt_NAME_CONTACT_CUST' name='txt_NAME_CONTACT_CUST' type='textbox' $READONLY value='".$row['CONTACT_NAME']."' maxlength='25'></input></td>\n";
									$ret .= "		<td>Phone #:</td>\n";
									$ret .= "		<td><input id='txt_PHONE' name='txt_PHONE' type='text' $READONLY value='" . $row['PHONE_NUMBER'] . "' maxlength='20'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Email:</td>\n";
									$ret .= "		<td><input id='txt_email' name='txt_email' type='text' $READONLY value='" . $row['EMAIL'] . "' maxlength='100'></input></td>\n";
									$ret .= "		<td>Classification:</td>\n";
									$ret .= "		<td><select id='select_classification' name='select_classification' >\n";
									$ClassificationMDArray = array(
										array("","--Select--"),
										array("NQE","Non-Quality Error"),
										array("PQE","Product Quality Error"),
									);
									for ($rowClassification = 0; $rowClassification < 3; $rowClassification++) {
										$SELECTED = '';
										$CURRENT = '';

										if (trim($row['CLASSIFICATION']) == trim($ClassificationMDArray[$rowClassification][0])) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='" . $ClassificationMDArray[$rowClassification][0] . "' " . $SELECTED . ">" . $CURRENT . $ClassificationMDArray[$rowClassification][1] .  "</option>\n";
									}
									$ret .= "		</select></td>\n";

									$sql2  = "select ";
									$sql2 .= " convert(varchar(10),rl.DATE_SHIPPED,126) as rlDate_SHIPPED, ";
									$sql2 .= " rl.* ";
									$sql2 .= " from nsa.RGA_LINE" . $DB_TEST_FLAG . " rl";
									$sql2 .= " where rl.RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= " ORDER BY rl.SEQ_LINE_RGA asc ";
									QueryDatabase($sql2, $results2);
									$LineCount = mssql_num_rows($results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										$style = 'table-row';
										$y = $row2['SEQ_LINE_RGA'];
										$z = $y+1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
										$ret .= "		<td>Order #:</td>\n";
										$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' $READONLY value='".$row2['ID_ORD']."' maxlength='8'></input></td>\n";
										$ret .= "		<td>PO #:</td>\n";
										$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' $READONLY value='".$row2['ID_PO']."' maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' $READONLY value='".$row2['ID_ITEM']."' maxlength='30'></input></td>\n";
										$ret .= "		<td>Quantity:</td>\n";
										$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' $READONLY value='".$row2['QUANTITY']."' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' $READONLY value='".$row2['ID_INVC']."' maxlength='8'></input></td>\n";
										$ret .= "		<td>Date Ship (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text' $READONLY value='".$row2['rlDate_SHIPPED']."' ></input></td>\n";
										$ret .= "	</tr>\n";
										if ($y==$LineCount){
											$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
											$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','4')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','4','1')\">+Clone</font></td>\n";
											$ret .= "	</tr>\n";
										}
									}
									for ($y = $LineCount+1; $y <= 10; $y++) {
										if ($y==1) {
											$style = 'table-row';
										} else {
											$style = 'display:none;';
										}
										$z = $y+1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
										$ret .= "		<td>Order #:</td>\n";
										$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' $READONLY maxlength='8'></input></td>\n";
										$ret .= "		<td>PO #:</td>\n";
										$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' $READONLY maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' $READONLY maxlength='30'></input></td>\n";
										$ret .= "		<td>Quantity:</td>\n";
										$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' $READONLY maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' $READONLY maxlength='8'></input></td>\n";//added invoiceNumber to form
										$ret .= "		<td>Date Ship (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text' $READONLY ></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','4')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','4','1')\">+Clone</font></td>\n";
										$ret .= "	</tr>\n";
									}
									
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Issued by:</td>\n";
									$ret .= "		<td>".$row['rb_ID_USER_ADD']."</td>\n";
									$ret .= "		<td>Authorized by:</td>\n";
									$ret .= "		<td><select id='select_auth'". $row['AUTHORIZED_BY'] . "' $READONLY>\n";

									$AUTH_BY = array("CG2","DS","MCF","STG","JG","Stock");
									foreach ($AUTH_BY as $SELECT_AUTH) {
										$SELECTED = '';
										$CURRENT = '';

										if (trim($row['AUTHORIZED_BY']) == trim($SELECT_AUTH)) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='" . $SELECT_AUTH . "' " . $SELECTED . ">" . $CURRENT . $SELECT_AUTH .  "</option>\n";
									}
									$ret .= "		</select></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Sales Manager:</td>\n";
									$ret .= "		<td><input id='txt_salesMgr' name='txt_salesMgr' type='text' $READONLY value='" . $row['SALES_MGR'] . "' maxlength='30'></input></td>\n";
									$ret .= "		<td>Territory:</td>\n";
									$ret .= "		<td><input id='txt_territory' name='txt_territory' type='text' $READONLY value='" . $row['ID_TERR'] . "' maxlength='3'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Description of <br/>Request/Complaint:</td>\n";
									$ret .= "		<td colspan='3'><textarea id='txt_descr' name='txt_descr' cols='25' rows='7' $READONLY >".$row['DESCR']."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									$ret .= "<table width='850'>\n";
									$ret .= "   <tr class='dbc'>\n";
									$ret .= "   <td colspan='6'><strong>Please Select One (Need to Select One)</strong></td>\n";
									$ret .= "   </tr>\n";

									$rgaSelectA = '';
									$rgaSelectB = '';
									$rgaSelectC = '';
									if ($row['RGA_CLASS'] == 'A'){
										$rgaSelectA = "checked = 'checked'";
									}
									if ($row['RGA_CLASS'] == 'B'){
										$rgaSelectB = "checked = 'checked'";
									}
									if ($row['RGA_CLASS'] == 'C'){
										$rgaSelectC = "checked = 'checked'";
									}
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Complaint with Return?</td>\n";
									$ret .= "		<td><input type='radio' id='A' name='choice' ".$rgaSelectA." value='A' $READONLY onClick=\"rating('tr_RGA_RATING');check('table_shipping')\" >A</input></td>\n";
									$ret .= "		<td>Complaint without Return?</td>\n";
									$ret .= "		<td><input type='radio' id='B' name='choice' ".$rgaSelectB." value='B' $READONLY onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">B</input></td>\n";
									$ret .= "		<td>Customer Request?</td>\n";
									$ret .= "		<td><input type='radio' id='C' name='choice' ".$rgaSelectC." value='C' $READONLY onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">C</input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									$ret .= "<table width='850'>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Follow-Up that<br /> Requires Action:</td>\n";
									$ret .= "		<td colspan=3><textarea id='txt_followUp' name='txt_followUp' cols='55' $READONLY >".$row['FOLLOW_UP_DESCR']."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc' style='display:none'>\n";
									$ret .= "		<td>Email to<br /> Notify:</td>\n";
									$ret .= "		<td><input id='txt_email_notify' name='txt_email_notify' type='text' size='65' $READONLY value='" . $row['EMAIL_LIST'] . "' ></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Additional<br /> Information:</td>\n";
									$ret .= "		<td colspan=3><textarea id='txt_add_info' name='txt_add_info' cols='55' $READONLY >".$row['ADD_INFO']."</textarea></td>\n";
									$ret .= "	</tr>\n";

									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Attached Files</td>\n";
									$ret .= "		<td colspan=3>\n";

									$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*");
									foreach ($a_files as $filename){
										//error_log($filename);
										$filename = str_replace('..','/protected',$filename);
										$short_filename = substr($filename, strrpos($filename, '/') + 1);
										$ret .=	"	<a href='" . $filename . "' target='_blank'>".$short_filename."</a></br>\n";
									}
									$ret .= "		</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td colspan=4><input id='fileToUpload' name='fileToUpload' type='file' value='Choose File' ></input><input type='button' value='Upload' onclick='uploadFile()'></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td colspan=4></input><progress id='progressBar' value='0' max='100'></progress><h5 id='status'><input id='ret_FileName' type='hidden' value=''></input></h5></td>\n";
									$ret .= "	</tr>\n";					
									$ret .= "	<tr class='dbc'>\n";
									//$ret .= "		<td colspan=4><input id='button_SubmitNew' name='button_SubmitNew' disabled type='button' $READONLY value='Submit'></input><div id='div_submitResp' name='div_submitResp'></div></td>\n";
									$ret .= "		<td colspan=4>\n";
									$ret .= " 			<input id='button_updateRgaBase' name='button_updateRgaBase' type='button' value='Save' DISABLED onclick=\"updateRgaBase()\" DISABLED></input>\n";
									$ret .= " 			**Send Email?<select id='sel_Email_updateRgaBase' name='sel_Email_updateRgaBase' DISABLED onchange=\"checkEnableButton('sel_Email_updateRgaBase','button_updateRgaBase')\">\n";
									$ret .= "   	        <option value=''>--Select--</option>\n";
									$ret .= "   	        <option value='Send'>Send</option>\n";
									$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
									$ret .= "			</select>\n";
									$ret .= "			<div id='div_submitResp_updateRgaBase' name='div_submitResp_updateRgaBase'></div>\n";
									$ret .= "		</td>\n";										
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";


									//////////
									// SHIPPING
									//////////
									$sql2  = "select ";
									$sql2 .= " convert(varchar(10),DATE_RECEIVED,126) as rsDATE_RECEIVED,";
									$sql2 .= " rowid as shipRowID, ";
									$sql2 .= " * ";
									$sql2 .= "from nsa.RGA_SHIP" . $DB_TEST_FLAG . " ";
									$sql2 .= "where RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= "ORDER BY SEQ_LINE_SHIP asc ";
									QueryDatabase($sql2, $results2);
									$LineCount = mssql_num_rows($results2);
									$ShipLineCount = $LineCount;
									/*
									if($LineCount == 0){
										$ret .= "<form name='insertNewShip' action =\"javascript:insertNewShip()\" >";//start form
									} else {
										$ret .= "<form name='updateShip'  action =\"javascript:updateRgaShip()\" >";//start form
									}
									*/
									$ret .= "<table width='850' id='table_shipping' style='display:table;'>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<th colspan='4'>For Shipping Only</th>\n";
									$ret .= "		<input id='hidden_RGA_NUMBER' name='hidden_RGA_NUMBER' type='hidden' value='" . $rgaNumber . "'>\n";
									$ret .= "	</tr>\n";

									while ($row2 = mssql_fetch_assoc($results2)){
										$style= 'table-row';
										$y = $row2['SEQ_LINE_SHIP'];
										$z = $y + 1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
										$ret .= "	</tr>\n";										
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
										$ret .= "		<input id='shipRowID' name='shipRowID' type='hidden' value='" . $row2['shipRowID'] . "'>\n";
										$ret .= "       <td colspan=2></td>\n";
										$ret .= "		<td>Item Recieved:</td>\n";
										$ret .= "		<td><input id='txt_itemReceived".$y."' name='txt_itemReceived".$y."' width='500px' type='text' value='".$row2['ITEM_RECEIVED']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text' value='".$row2['rsDATE_RECEIVED']."'></input></td>\n";
										$ret .= "		<td>Quantity Received:</td>\n";
										$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."' type='text' value='".$row2['QUANTITY_RECEIVED']."' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Carrier:</td>\n";
										$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' value='".$row2['CARRIER']."' maxlength='12'></input></td>\n";
										$ret .= "		<td>Tracking #:</td>\n";
										$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' value='".$row2['TRACKING_NO']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".5' style='".$style."'>\n";
										$ret .= "		<td>Condition Received:</td>\n";
										$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' value='".$row2['COND_RECEIVED']."' maxlength='50'></input></td>\n";
										$ret .= "		<td>Received by:</td>\n";
										$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' value='".$row2['RECEIVED_BY']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .=" 	<tr class='dbc' id='tr_itm".$y.".6' style='".$style."'>\n";
										$ret .= "		<td>Comments:</td>\n";
										$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' cols='55' >".$row2['COMMENTS']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										if ($y==$LineCount){
											$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
											$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','6','1')\">+Clone</font></td>\n";
											$ret .= "	</tr>\n";
										}
									}
									for ($y = $LineCount+1; $y <= 10; $y++){
										if($y==1){
											$style = 'table-row';
										} else {
											$style = 'display:none;';
										}
										$z = $y + 1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
										$ret .= "	</tr>\n";										
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
										$ret .= "       <td colspan=2></td>\n";
										$ret .= "		<td>Item Recieved:</td>\n";
										$ret .= "		<td><input id='txt_itemReceived". $y."' name='txt_itemReceived".$y."'  width='500px' type='text' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text'></input></td>\n";
										$ret .= "		<td>Quantity Received:</td>\n";
										$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."' type='text' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."' >\n";
										$ret .= "		<td>Carrier:</td>\n";
										$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' maxlength='12'></input></td>\n";
										$ret .= "		<td>Tracking #:</td>\n";
										$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".5' style='".$style."'>\n";
										$ret .= "		<td>Condition Received:</td>\n";
										$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' maxlength='50'></input></td>\n";
										$ret .= "		<td>Received by:</td>\n";
										$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .=" 	<tr class='dbc' id='tr_itm".$y.".6' style='".$style."'>\n";
										$ret .= "		<td>Comments:</td>\n";
										$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' cols='55'></textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','6','1')\">+Clone</font></td>\n";
										$ret .= "	</tr>\n";	
									}
									if ($ShipLineCount==0){ //if not in table show button for new submission, else show button for update
										$ret .= "	<tr class='dbc'>\n";
										//$ret .= "		<td colspan=4><input id='button_insertNewShip' name='button_insertNewShip' type='button' value='Submit' onClick=\"insertNewShip()\" ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= " 			<input id='button_insertNewShip' name='button_insertNewShip' type='button' value='Save' onclick=\"insertNewShip()\" DISABLED></input>\n";
										$ret .= " 			**Send Email?<select id='sel_Email_insertNewShip' name='sel_Email_insertNewShip' onchange=\"checkEnableButton('sel_Email_insertNewShip','button_insertNewShip')\">\n";
										$ret .= "   	        <option value=''>--Select--</option>\n";
										$ret .= "   	        <option value='Send'>Send</option>\n";
										$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
										$ret .= "			</select>\n";
										$ret .= "			<div id='div_submitResp_insertNewShip' name='div_submitResp_insertNewShip'></div>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";
									} else {
										$ret .= "	<tr class='dbc'>\n";
										//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' type='button' value='Save' onClick=\"updateRgaShip()\" ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= " 			<input id='button_updateRgaShip' name='button_updateRgaShip' type='button' value='Save' onclick=\"updateRgaShip()\" DISABLED></input>\n";
										$ret .= " 			**Send Email?<select id='sel_Email_updateRgaShip' name='sel_Email_updateRgaShip' onchange=\"checkEnableButton('sel_Email_updateRgaShip','button_updateRgaShip')\">\n";
										$ret .= "   	        <option value=''>--Select--</option>\n";
										$ret .= "   	        <option value='Send'>Send</option>\n";
										$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
										$ret .= "			</select>\n";
										$ret .= "			<div id='div_submitResp_updateRgaShip' name='div_submitResp_updateRgaShip'></div>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";
									}
									$ret .= "</table>\n";
									//$ret .= "</form>";


									//////////////
									// INVEST RECORDS
									//////////////
									$sql2 =  "SELECT ";
									$sql2 .= " ri.rowid as isoRowID,";
									$sql2 .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
									$sql2 .= " ri.*, ";
									$sql2 .= " rb.rowid as baseRowID ";
									$sql2 .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
									$sql2 .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
									$sql2 .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
									$sql2 .= " where rb.RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= " order by ri.SEQ_INVEST asc ";
									QueryDatabase($sql2, $results2);
									$y=0;
									while(($row2 = mssql_fetch_assoc($results2)) || ($y<10)){
										$y++;
										$z = $y + 1;
										$style = 'table-row';
										$seqInvest = $row2['SEQ_INVEST'];

										if (empty($seqInvest)) {
											$seqInvest = $y;
											$style = 'display:none;';
										} 

										if ($y==1) {
											$style = 'table-row';
										}
										
										$rgaRatingSelect1 = '';
										$rgaRatingSelect2 = '';
										$rgaRatingSelect3 = '';
										if($row2['RGA_RATING'] == '1'){
											$rgaRatingSelect1 = "checked = 'checked'";
										}
										if($row2['RGA_RATING'] == '2'){
											$rgaRatingSelect2 = "checked = 'checked'";
										}
										if($row2['RGA_RATING'] == '3'){
											$rgaRatingSelect3 = "checked = 'checked'";
										}

										//selects action needed checkboxes
										$rgaActionNeedRework = '';
										$rgaActionNeedReplace = '';
										$rgaActionNeedNonStock = '';
										$rgaActionNeedCredit = '';
										$rgaActionNeedOther = '';
										if (strpos($row2['ACTION_NEEDED'], ':Rework:') !== false) {
											$rgaActionNeedRework = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':Replace:') !== false) {
											$rgaActionNeedReplace = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':NonStk-Sample:') !== false) {
											$rgaActionNeedNonStock = 'CHECKED';
										}
										
										if (strpos($row2['ACTION_NEEDED'], ':Credit:') !== false) {
											$rgaActionNeedCredit = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':Other:') !== false) {
											$rgaActionNeedOther = 'CHECKED';
										}

										$carFlagN = '';
										$carFlagY = '';
										if($row2['FLAG_CAR'] == 'N'){
											$carFlagN = "checked = 'checked'";
										}
										if($row2['FLAG_CAR'] == 'Y'){
											$carFlagY = "checked = 'checked";
										}
										$ret .= "<table width='850' id='table_inv_".$y."' style='".$style."'>\n";
										$ret .= "		<th colspan='4'>ISO investigation ".$seqInvest."</th>\n";
										$ret .= "		<input id='hidden_RGA_NUMBER_".$seqInvest."' name='hidden_RGA_NUMBER_".$seqInvest."' type='hidden' value='" . $rgaNumber . "'>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_RGA_RATING_".$seqInvest."' style='diplay:table-row;'>\n";
										$ret .= "		<td>RGA Rating (choose one):</td>\n";
										$ret .= "		<td><input type='radio' ".$rgaRatingSelect1." id='1_".$seqInvest."' name='level' value='1' $READONLY>Level 1</input></td>\n";
										$ret .= "		<td><input type='radio' ".$rgaRatingSelect2." id='2_".$seqInvest."' name='level' value='2' $READONLY>Level 2</input></td>\n";
										$ret .= "		<td><input type='radio' ".$rgaRatingSelect3." id='3_".$seqInvest."' name='level' value='3' $READONLY>Level 3</input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Findings:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_findings_".$seqInvest."' name='txt_findings_".$seqInvest."' cols='55' $READONLY >".$row2['FINDINGS']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Cause:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_cause_".$seqInvest."' name='txt_cause_".$seqInvest."' cols='55' $READONLY>".$row2['CAUSE']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Containment:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_contain_".$seqInvest."' name='txt_contain_".$seqInvest."' cols='55' $READONLY >".$row2['CONTAINMENT']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Correction:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_corr_".$seqInvest."' name='txt_corr_".$seqInvest."' cols='55' $READONLY >".$row2['CORRECTION']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
										$ret .= "<table width='850' id='table_action_".$y."' style='".$style."'>\n";
										$ret .= "	<th colspan='7'></th>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td><strong>Action Needed:</strong></td>\n";
										$ret .= "		<td style='text-align:left;'>\n";
										$ret .= "			<input type='checkbox' id='action_Rework_".$seqInvest."' name='action_Rework_".$seqInvest."' $rgaActionNeedRework value=':Rework:' $READONLY>Rework</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Replace_".$seqInvest."' name='action_Replace_".$seqInvest."' $rgaActionNeedReplace value=':Replace:' $READONLY>Replace</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_NonStk-Sample_".$seqInvest."' name='action_NonStk-Sample_".$seqInvest."' $rgaActionNeedNonStock value=':NonStk-Sample:' $READONLY>NonStock/Sample</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Credit_".$seqInvest."' name='action_Credit_".$seqInvest."' $rgaActionNeedCredit value=':Credit:' $READONLY>Credit</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Other_".$seqInvest."' name='action_Other_".$seqInvest."' $rgaActionNeedOther value=':Other:' $READONLY>Other</input>\n";
										$ret .= "		</td>\n";
										$ret .= "		<td colspan=5><textarea id='txt_desc_".$seqInvest."' name='txt_desc_".$seqInvest."' $READONLY>".$row2['ACTION_DESCR']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td colspan=2>Is a Corrective Action Request (CAR) required? (select one)</td>\n";
										$ret .= "		<td><input type='radio' ".$carFlagN." id='N_".$seqInvest."' name='pick' value='N'>No</input></td>\n";
										$ret .= "		<td><input type='radio' ".$carFlagY." id='Y_".$seqInvest."' name='pick' value='Y'>Yes</input></td>\n";
										$ret .= "		<td>CAR #</td>\n";
										$ret .= "		<td colspan=2><input id='txt_carNumber_".$seqInvest."' name='txt_carNumber_".$seqInvest."' type='text' $READONLY value='".$row2['CAR_NUMBER']."' maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td colspan=2>Proposed action approved by:</td>\n";
										$ret .= "		<td colspan=2><input id='txt_approve_".$seqInvest."' name='txt_approve_".$seqInvest."' type='text' $READONLY value='".$row2['APPROVED_BY']."' maxlength='30'></input></td>\n";
										$ret .= "		<td>Date (yyyy-mm-dd):</td>\n";
										$ret .= "		<td colspan=2><input id='txt_dateSubmit_".$seqInvest."' name='txt_dateSubmit_".$seqInvest."' type='text' $READONLY value='".$row2['riDATE_APPROVED']."'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";

										$ret .= "<table width='850' id='table_track_".$y."' style='".$style."'>\n";
										$ret .= " 	<th colspan='4'>For Tracking Only</th>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Department:</td>\n";
										$ret .= "		<td><input id='txt_dept_".$seqInvest."' name='txt_dept_".$seqInvest."' type='text' $READONLY value='".$row2['DEPARTMENT']."' maxlength='25'></input></td>\n";
										$ret .= "		<td>Request/Error:</td>\n";
										$ret .= "		<td><select id='select_err_".$seqInvest."' name='select_err_".$seqInvest."' $READONLY>\n";
										$ret .= "			<option value='SELECT'>--Select--</option>\n";

										$REQ_ERR = array("Error","Request", "Dispute");
										foreach($REQ_ERR as $SELECT_ERR) {
											$SELECTED = '';
											$CURRENT = '';

											if (trim($row2['REQ_ERR']) == trim($SELECT_ERR)) {
												$SELECTED = 'SELECTED';
												$CURRENT = '*';
											}
											$ret .= "				<option value='" . $SELECT_ERR . "' " . $SELECTED . ">" . $CURRENT . $SELECT_ERR .  "</option>\n";
										}
										$ret .= "		</select></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Workcenter:</td>\n";
										$ret .= "		<td><input id='select_workcenter_".$seqInvest."' name='select_workcenter_".$seqInvest."' type='text' $READONLY value='".$row2['WORKCENTER']."' maxlength='40'></input></td>\n";
										$ret .= "		<td>Credit Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoice_".$seqInvest."' name='txt_invoice_".$seqInvest."' type='text' $READONLY value='".$row2['ID_INVC_CRED']."' maxlength='8'></input></td>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Team/Individual:</td>\n";
										$ret .= "		<td><input id='txt_team_".$seqInvest."' name='txt_team_".$seqInvest."' type='text' $READONLY value='".$row2['ID_TEAM']."' maxlength='25'></input></td>\n";
										$ret .= "		<td>Component Costs:</td>\n";
										$ret .= "		<td><input id='txt_compCost_".$seqInvest."' name='txt_compCost_".$seqInvest."' type='text' $READONLY value='".$row2['COST_COMP']."'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Error Type:</td>\n";
										$ret .= "		<td><input id='txt_errorType_".$seqInvest."' name='txt_errorType_".$seqInvest."' type='text' $READONLY value='".$row2['ERR_TYPE']."' maxlength='40'></input></td>\n";
										$ret .= "		<td>Labor Costs:</td>\n";
										$ret .= "		<td><input id='txt_laborCost_".$seqInvest."' name='txt_laborCost_".$seqInvest."' type='text' $READONLY value='".$row2['COST_LAB']."'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Vendor ID:</td>\n";
										$ret .= "		<td><input id='txt_vendor_".$seqInvest."' name='txt_vendor_".$seqInvest."' type='text' $READONLY value='".$row2['ID_VND']."' maxlength='6'></input></td>\n";
										$ret .= "		<td>Shipping Costs:</td>\n";
										$ret .= "		<td><input id='txt_shipCost_".$seqInvest."' name='txt_shipCost_".$seqInvest."' type='text' $READONLY value='".$row2['COST_SHIP']."'></input></td>\n";
										$ret .= "	</tr>\n";				
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_isoPartNumber_".$seqInvest."' name='txt_isoPartNumber_".$seqInvest."' type='text' $READONLY value='".$row2['ID_ITEM_VND']."' maxlength='30'></input></td>\n";
										$ret .= "		<td>Total RGA Costs:</td>\n";
										$ret .= "		<td><input id='txt_totCost_".$seqInvest."' name='txt_totCost_".$seqInvest."' disabled type='text'  value='".$row2['COST_TOT']."'></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";

										$ret .= "		<td>ISO Status:</td>\n";
										$isoStatus = array("Drafted", 
											"Pending Extenuating Issues", 
											"Waiting for Approval", 
											"Waiting for Production", 
											"Waiting for Customer Service", 
											"Waiting for Inventory Audit", 
											"Waiting for Manufacturer Response", 
											"Waiting for Pricing Info", 
											"Waiting for Rework/Replacement Number", 
											"Working with Production Development", 
											"Working with Purchasing", 
											"Closed");
										if (empty($row2['isoRowID'])) {
											$ret .= "		<td><select id='select_iso_status_".$seqInvest."' name='select_iso_status_".$seqInvest."' $READONLY>\n";
											$ret .= "           <option value=''>-- Select --</option>\n";
											$ret .= "           <option value='Drafted'>Drafted</option>\n";
											$ret .= "           <option value='Pending Extenuating Issues'>Pending Extenuating Issues</option>\n";
											$ret .= "           <option value='Waiting for Approval'>Waiting for Approval</option>\n";
											$ret .= "           <option value='Waiting for Production'>Waiting for Production</option>\n";
											$ret .= "           <option value='Waiting for Customer Service'>Waiting for Customer Service</option>\n";
											$ret .= "           <option value='Waiting for Inventory Audit'>Waiting for Inventory Audit</option>\n";
											$ret .= "           <option value='Waiting for Manufacturer Response'>Waiting for Manufacturer Response</option>\n";
											$ret .= "           <option value='Waiting for Pricing Info'>Waiting for Pricing Info</option>\n";
											$ret .= "           <option value='Waiting for Rework/Replacement Number'>Waiting for Rework/Replacement Number</option>\n";
											$ret .= "           <option value='Working with Production Development'>Working with Production Development</option>\n";
											$ret .= "           <option value='Working with Purchasing'>Working with Purchasing</option>\n";
											$ret .= "           <option value='Closed'>Closed</option>\n";
											$ret .= "		</select></td>\n";
										} else {
											$ret .= "		<td><select id='select_iso_status_".$seqInvest."' name='select_iso_status_".$seqInvest."' $READONLY>\n";
											foreach ($isoStatus as $SELECT_ISO_STATUS) {
												$SELECTED = '';
												$CURRENT = '';

												if (trim($row2['RGA_ISO_STATUS']) == trim($SELECT_ISO_STATUS)) {
													$SELECTED = 'SELECTED';
													$CURRENT = '*';
												}
												$ret .= "				<option value='" . $SELECT_ISO_STATUS . "' " . $SELECTED . ">" . $CURRENT . $SELECT_ISO_STATUS .  "</option>\n";
											}
											$ret .= "		</select></td>\n";
										}

										if ($seqInvest==1){
											$ret .= "		<td>RGA Status:</td>\n";
											$ret .= "		<td><select id='select_rga_status_".$seqInvest."' name='select_rga_status_".$seqInvest."' onchange=\"checkChangeRgaIsoStatus($seqInvest)\" $READONLY>\n";

											$RGA_STATUS_ARRAY = array(
												array("","--Select--"),
												array("Open, Waiting for Return","Open, Waiting for Return"),
												array("Open, Response to Customer Required","Open, Response to Customer Required"),
												array("Open, Inspection Required","Open, Inspection Required"),
												array("ISO Action Required","ISO Action Required"),
												array("Cancelled","Cancelled"),
												array("Closed","Closed"),												

												//array("","--Select--"),
												//array("Open","Open"),
												//array("Pending","Pending Investigation"),
												//array("Cancelled","Cancelled"),
												//array("Closed","Closed"),
											);
											for ($rowStat = 0; $rowStat < 5; $rowStat++) {
												$SELECTED = '';
												$CURRENT = '';

												if (trim($row['RGA_STATUS']) == trim($RGA_STATUS_ARRAY[$rowStat][0])) {
													$SELECTED = 'SELECTED';
													$CURRENT = '*';
												}
												$ret .= "				<option value='" . $RGA_STATUS_ARRAY[$rowStat][0] . "' " . $SELECTED . ">" . $CURRENT . $RGA_STATUS_ARRAY[$rowStat][1] .  "</option>\n";
											}

											$ret .= "		</select></td>\n";
										} else {
											$ret .= "		<td colspan=2></td>\n";
										}

										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										//$ret .= "		<td colspan=7><input id='button_SubmitNew2_".$seqInvest."' name='button_SubmitNew2_".$seqInvest."' DISABLED type='button' $READONLY value='Submit'></input><div id='div_submitResp2' name='div_submitResp2'></div></td>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= " 			<input id='button_updateRgaInvest_".$seqInvest."' name='button_updateRgaInvest_".$seqInvest."' type='button' value='Save' onclick=\"updateRgaInvest(".$seqInvest.")\" DISABLED></input>\n";
										$ret .= " 			**Send Email?<select id='sel_Email_updateRgaInvest_".$seqInvest."' name='sel_Email_updateRgaInvest_".$seqInvest."' DISABLED onchange=\"checkEnableButton('sel_Email_updateRgaInvest_".$seqInvest."','button_updateRgaInvest_".$seqInvest."')\">\n";
										$ret .= "   	        <option value=''>--Select--</option>\n";
										$ret .= "   	        <option value='Send'>Send</option>\n";
										$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
										$ret .= "			</select>\n";
										$ret .= "			<div id='div_submitResp_updateRgaInvest_".$seqInvest."' name='div_submitResp_updateRgaInvest_".$seqInvest."'></div>\n";
										$ret .= "		</td>\n";									
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_invest".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= "			<font style='cursor: hand' title='Add Blank Record' onclick=\"showInvestTable('$z')\">+Blank   </font>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
									}
								}
							}
						}			
					break;

					case "updateShip":
						if(isset($_POST["rgaNumber"]) && isset($_POST["sendEmail"]) &&
							isset($_POST["itemReceived1"]) && isset($_POST["dateReceived1"]) && isset($_POST["quantity1"]) && isset($_POST["condition1"]) && isset($_POST["receivedBy1"]) && isset($_POST["carrier1"]) && isset($_POST["trackingNumber1"]) && isset($_POST["shipComments1"]) &&
							isset($_POST["itemReceived2"]) && isset($_POST["dateReceived2"]) && isset($_POST["quantity2"]) && isset($_POST["condition2"]) && isset($_POST["receivedBy2"]) && isset($_POST["carrier2"]) && isset($_POST["trackingNumber2"]) && isset($_POST["shipComments2"]) &&
							isset($_POST["itemReceived3"]) && isset($_POST["dateReceived3"]) && isset($_POST["quantity3"]) && isset($_POST["condition3"]) && isset($_POST["receivedBy3"]) && isset($_POST["carrier3"]) && isset($_POST["trackingNumber3"]) && isset($_POST["shipComments3"]) &&
							isset($_POST["itemReceived4"]) && isset($_POST["dateReceived4"]) && isset($_POST["quantity4"]) && isset($_POST["condition4"]) && isset($_POST["receivedBy4"]) && isset($_POST["carrier4"]) && isset($_POST["trackingNumber4"]) && isset($_POST["shipComments4"]) &&
							isset($_POST["itemReceived5"]) && isset($_POST["dateReceived5"]) && isset($_POST["quantity5"]) && isset($_POST["condition5"]) && isset($_POST["receivedBy5"]) && isset($_POST["carrier5"]) && isset($_POST["trackingNumber5"]) && isset($_POST["shipComments5"]) &&
							isset($_POST["itemReceived6"]) && isset($_POST["dateReceived6"]) && isset($_POST["quantity6"]) && isset($_POST["condition6"]) && isset($_POST["receivedBy6"]) && isset($_POST["carrier6"]) && isset($_POST["trackingNumber6"]) && isset($_POST["shipComments6"]) &&
							isset($_POST["itemReceived7"]) && isset($_POST["dateReceived7"]) && isset($_POST["quantity7"]) && isset($_POST["condition7"]) && isset($_POST["receivedBy7"]) && isset($_POST["carrier7"]) && isset($_POST["trackingNumber7"]) && isset($_POST["shipComments7"]) &&
							isset($_POST["itemReceived8"]) && isset($_POST["dateReceived8"]) && isset($_POST["quantity8"]) && isset($_POST["condition8"]) && isset($_POST["receivedBy8"]) && isset($_POST["carrier8"]) && isset($_POST["trackingNumber8"]) && isset($_POST["shipComments8"]) &&
							isset($_POST["itemReceived9"]) && isset($_POST["dateReceived9"]) && isset($_POST["quantity9"]) && isset($_POST["condition9"]) && isset($_POST["receivedBy9"]) && isset($_POST["carrier9"]) && isset($_POST["trackingNumber9"]) && isset($_POST["shipComments9"]) &&
							isset($_POST["itemReceived10"]) && isset($_POST["dateReceived10"]) && isset($_POST["quantity10"]) && isset($_POST["condition10"]) && isset($_POST["receivedBy10"]) && isset($_POST["carrier10"]) && isset($_POST["trackingNumber10"]) && isset($_POST["shipComments10"]) 
						){ 
							$shipRowID = $_POST["shipRowID"];
							$rgaNumber = $_POST["rgaNumber"];
							$sendEmail = $_POST["sendEmail"];

							$itemReceived1 = $_POST["itemReceived1"];
							$dateReceived1 = $_POST["dateReceived2"];
							$quantity1 = $_POST["quantity1"];
							$condition1 = $_POST["condition1"];
							$receivedBy1 = $_POST["receivedBy1"];
							$carrier1 = $_POST["carrier1"];
							$trackingNumber1 = $_POST["trackingNumber1"];
							$shipComments1 = $_POST["shipComments1"];

							$itemReceived2 = $_POST["itemReceived2"];
							$dateReceived2 = $_POST["dateReceived2"];
							$quantity2 = $_POST["quantity2"];
							$condition2 = $_POST["condition2"];
							$receivedBy2 = $_POST["receivedBy2"];
							$carrier2 = $_POST["carrier2"];
							$trackingNumber2 = $_POST["trackingNumber2"];
							$shipComments2 = $_POST["shipComments2"];

							$itemReceived3 = $_POST["itemReceived3"];
							$dateReceived3 = $_POST["dateReceived3"];
							$quantity3 = $_POST["quantity3"];
							$condition3 = $_POST["condition3"];
							$receivedBy3 = $_POST["receivedBy3"];
							$carrier3 = $_POST["carrier3"];
							$trackingNumber3 = $_POST["trackingNumber3"];
							$shipComments3 = $_POST["shipComments3"];

							$itemReceived4 = $_POST["itemReceived4"];
							$dateReceived4 = $_POST["dateReceived4"];
							$quantity4 = $_POST["quantity4"];
							$condition4 = $_POST["condition4"];
							$receivedBy4 = $_POST["receivedBy4"];
							$carrier4 = $_POST["carrier4"];
							$trackingNumber4 = $_POST["trackingNumber4"];
							$shipComments4 = $_POST["shipComments4"];

							$itemReceived5 = $_POST["itemReceived5"];
							$dateReceived5 = $_POST["dateReceived5"];
							$quantity5 = $_POST["quantity5"];
							$condition5 = $_POST["condition5"];
							$receivedBy5 = $_POST["receivedBy5"];
							$carrier5 = $_POST["carrier5"];
							$trackingNumber5 = $_POST["trackingNumber5"];
							$shipComments5 = $_POST["shipComments5"];

							$itemReceived6 = $_POST["itemReceived6"];
							$dateReceived6 = $_POST["dateReceived6"];
							$quantity6 = $_POST["quantity6"];
							$condition6 = $_POST["condition6"];
							$receivedBy6 = $_POST["receivedBy6"];
							$carrier6 = $_POST["carrier6"];
							$trackingNumber6 = $_POST["trackingNumber6"];
							$shipComments6 = $_POST["shipComments6"];

							$itemReceived7 = $_POST["itemReceived7"];
							$dateReceived7 = $_POST["dateReceived7"];
							$quantity7 = $_POST["quantity7"];
							$condition7 = $_POST["condition7"];
							$receivedBy7 = $_POST["receivedBy7"];
							$carrier7 = $_POST["carrier7"];
							$trackingNumber7 = $_POST["trackingNumber7"];
							$shipComments7 = $_POST["shipComments7"];

							$itemReceived8 = $_POST["itemReceived8"];
							$dateReceived8 = $_POST["dateReceived8"];
							$quantity8 = $_POST["quantity8"];
							$condition8 = $_POST["condition8"];
							$receivedBy8 = $_POST["receivedBy8"];
							$carrier8 = $_POST["carrier8"];
							$trackingNumber8 = $_POST["trackingNumber8"];
							$shipComments8 = $_POST["shipComments8"];

							$itemReceived9 = $_POST["itemReceived9"];
							$dateReceived9 = $_POST["dateReceived9"];
							$quantity9 = $_POST["quantity9"];
							$condition9 = $_POST["condition9"];
							$receivedBy9 = $_POST["receivedBy9"];
							$carrier9 = $_POST["carrier9"];
							$trackingNumber9 = $_POST["trackingNumber9"];
							$shipComments9 = $_POST["shipComments9"];

							$itemReceived10 = $_POST["itemReceived10"];
							$dateReceived10 = $_POST["dateReceived10"];
							$quantity10 = $_POST["quantity10"];
							$condition10 = $_POST["condition10"];
							$receivedBy10 = $_POST["receivedBy10"];
							$carrier10 = $_POST["carrier10"];
							$trackingNumber10 = $_POST["trackingNumber10"];
							$shipComments10 = $_POST["shipComments10"];

							for ($y = 1; $y <= 10; $y++) {
								if (${"itemReceived".$y} <> '' || ${"dateReceived".$y} <> '' || ${"quantity".$y} <> '' || 
									${"condition".$y} <> '' || ${"receivedBy".$y} <> '' || ${"carrier".$y} <> ''|| 
									${"trackingNumber".$y} <> '' || ${"shipComments".$y} <> '' 
								){
									$sql  = "SELECT * from nsa.RGA_SHIP" . $DB_TEST_FLAG . " ";
									$sql .= " WHERE RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql .= " AND SEQ_LINE_SHIP = '" . $y . "' ";
									QueryDatabase($sql, $results);

									if (mssql_num_rows($results) <> 0) {
										//error_log("updating nsa.RGA_SHIP RGA#: ". $rgaNumber ." SEQ: " . $y);
										$sql1  = " UPDATE nsa.RGA_SHIP" . $DB_TEST_FLAG . " ";
										$sql1 .= " set ITEM_RECEIVED = '" . ms_escape_string(${"itemReceived".$y}) . "', ";
										if (${"dateReceived".$y} <> '') {
											$sql1 .= " DATE_RECEIVED = '" . ms_escape_string(${"dateReceived".$y}) . "', ";
										}
										$sql1 .= " QUANTITY_RECEIVED = '" . ms_escape_string(${"quantity".$y}) . "', ";
										$sql1 .= " COND_RECEIVED = '" . ms_escape_string(${"condition".$y}) . "', ";
										$sql1 .= " RECEIVED_BY = '" . ms_escape_string(${"receivedBy".$y}) . "', ";
										$sql1 .= " CARRIER = '" . ms_escape_string(${"carrier".$y}) . "', ";
										$sql1 .= " TRACKING_NO = '" . ms_escape_string(${"trackingNumber".$y}) . "', ";
										$sql1 .= " COMMENTS = '" . ms_escape_string(${"shipComments".$y}) . "', ";
										$sql1 .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
										$sql1 .= " DATE_CHG = GetDate() ";
										$sql1 .= " WHERE RGA_NUMBER = '" . $rgaNumber . "' ";
										$sql1 .= " AND SEQ_LINE_SHIP = '" . $y . "' ";
										QueryDatabase($sql1, $results1);
									} else {
										//error_log("INSERTING nsa.RGA_LINE RGA#: ". $rgaNumber ." SEQ: " . $y);
										$sql1  = " insert into nsa.RGA_SHIP" . $DB_TEST_FLAG . "( ";//insert into sql RGA_SHIP table
									    $sql1 .= " RGA_BASE_rowid, ";
									    $sql1 .= " RGA_NUMBER, ";
										$sql1 .= " SEQ_LINE_SHIP, ";
										$sql1 .= " ID_USER_ADD, ";
										$sql1 .= " DATE_ADD, ";
										$sql1 .= " ITEM_RECEIVED, ";
										if (${"dateReceived".$y} <> '') {
											$sql1 .= " DATE_RECEIVED, ";
										}
										$sql1 .= " QUANTITY_RECEIVED, ";
										$sql1 .= " COND_RECEIVED, ";
										$sql1 .= " RECEIVED_BY,";
										$sql1 .= " CARRIER, ";
										$sql1 .= " TRACKING_NO ,";
										$sql1 .= " COMMENTS ";
										$sql1 .= " ) VALUES ( ";
										$sql1 .= " (Select rowid from nsa.RGA_BASE" . $DB_TEST_FLAG . " where RGA_NUMBER = '". $rgaNumber ."'), ";
										$sql1 .= " '" . ms_escape_string($rgaNumber) . "', ";
										$sql1 .= " '" . $y ."', ";
									    $sql1 .= " '" . $UserRow['ID_USER'] . "', ";									
										$sql1 .= " 	GetDate(), ";
										$sql1 .= " '" . ms_escape_string(${"itemReceived".$y}) . "', ";
										if (${"dateReceived".$y} <> '') {
											$sql1 .= " '" . ms_escape_string(${"dateReceived".$y}) . "', ";
										}
										$sql1 .= " '" . ms_escape_string(${"quantity".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"condition".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"receivedBy".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"carrier".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"trackingNumber".$y}) . "', ";
										$sql1 .= " '" . ms_escape_string(${"shipComments".$y}) . "' ";
										$sql1 .= " ) ";
										QueryDatabase($sql1, $results1); 
									}
								}		
							}

							if (trim($sendEmail) == 'Send') {
								$sql  = "SELECT rb.NAME_CUST, wa.EMAIL, wa.NAME_EMP ";
								$sql .= " FROM nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
								$sql .= " left join nsa.DCWEB_AUTH wa ";
								$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
								$sql .= " WHERE rb.RGA_NUMBER = '" . $rgaNumber . "' ";
								QueryDatabase($sql, $results);
								$row = mssql_fetch_assoc($results);
								
								$custName = $row['NAME_CUST'];
								$originatorEmail = $row['EMAIL'];
								$originatorName = $row['NAME_EMP'];

								if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
									$head = array(
										'to'      =>array('TESTGroup-RGA-ShipReceived@thinknsa.com'=>'Group-RGA-ShipReceived'),
								    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
								    	'cc'      =>array($originatorEmail => $originatorName),
								    	//'bcc'     =>array('email4@email.net'=>'Admin'),
							    	);
							    } else {
							    	$head = array(
										//'to'      =>array('TESTGroup-RGA-ShipReceived@thinknsa.com'=>'Group-RGA-ShipReceived'),
										'to'      =>array('Group-RGA-ShipReceived@thinknsa.com'=>'Group-RGA-ShipReceived'),
								    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
								    	'cc'      =>array($originatorEmail => $originatorName),
								    	//'bcc'     =>array('email4@email.net'=>'Admin'),
								    );
							    }
								$subject = "Items Received RGA " . $rgaNumber . " - " . $custName;
								$body = GenerateHTMLforEmail($rgaNumber);
								//$files = array($file1,$file2);
								mail::send($head,$subject,$body);//$files are optional param
							}	
							$ret .= "SAVED </br>" . date('Y-m-d H:i:s');	
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}
						
					break;



					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//ISO INVESTIGATION RECORD STUFF
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					case "submit_newreq_iso":
						if (isset($_POST["rgaNumber"]) && isset($_POST["level"]) && isset($_POST["findings"]) && isset($_POST["cause"]) && isset($_POST["contain"]) && isset($_POST["corr"]) && isset($_POST["actionNeed"]) &&
							isset($_POST["desc"]) && isset($_POST["flag_car"]) && isset($_POST["carNumber"]) && isset($_POST["approve"]) && isset($_POST["date"]) && isset($_POST["dept"]) &&  isset($_POST["errReq"]) &&
							isset($_POST["team"])&& isset($_POST["invoice"]) && isset($_POST["errorType"]) && isset($_POST["compCost"]) && isset($_POST["vendor"]) && isset($_POST["laborCost"]) &&
							isset($_POST["itemNumber"]) && isset($_POST["shipCost"]) && isset($_POST["isoStatus"]) && isset($_POST["workcenter"]) && isset($_POST["rgaStatus"]) && isset($_POST["sendEmail"]) && isset($_POST["seqInvest"])) {
							
							$rgaNumber = $_POST["rgaNumber"];
							//$seqInvest = $_POST["seqInvest"] + 1;
							$seqInvest = $_POST["seqInvest"];
							$level = $_POST["level"];
							$findings = $_POST["findings"];
							$cause = $_POST["cause"];
							$contain = $_POST["contain"];
							$corr = $_POST["corr"];
							$actionNeed = $_POST["actionNeed"];
							$desc = $_POST["desc"];
							$flag_car = $_POST["flag_car"];
							$carNumber = $_POST["carNumber"];
							$approve = $_POST["approve"];
							$date = $_POST["date"];
							$dept = $_POST["dept"];
							$errReq = $_POST["errReq"];
							$team = $_POST["team"];
							$invoice = $_POST["invoice"];
							$errorType = $_POST["errorType"];
							$compCost = $_POST["compCost"];
							$vendor = $_POST["vendor"];
							$laborCost = $_POST["laborCost"];
							$itemNumber = $_POST["itemNumber"];
							$shipCost = $_POST["shipCost"];
							$totCost = $laborCost + $compCost + $shipCost;
							$isoStatus = $_POST["isoStatus"];
							$rgaStatus = $_POST["rgaStatus"];
							$workcenter = $_POST["workcenter"];
							$sendEmail = $_POST["sendEmail"];

							if($dept == 'Non-Production') {
								$workcenter = 'NON-PRODUCTION';
							}

							$sql = " insert into nsa.RGA_INVEST" . $DB_TEST_FLAG . "( ";//inserting into RGA_INVEST table
							$sql .= "	RGA_BASE_rowid, ";
							$sql .= " 	RGA_NUMBER, ";
							$sql .= " 	SEQ_INVEST, ";
							$sql .= "	ID_USER_ADD, ";
							$sql .= "	DATE_ADD, ";
							$sql .= " 	RGA_RATING, ";
							$sql .= "	FINDINGS, ";
							$sql .= "	CAUSE, ";
							$sql .= "	CONTAINMENT, ";
							$sql .= "	CORRECTION, ";
							$sql .= "	ACTION_NEEDED, ";
							$sql .= "	ACTION_DESCR, ";
							$sql .= "	ACTION_USER, ";
							$sql .= "	FLAG_CAR, ";
							$sql .= "	CAR_NUMBER, ";
							$sql .= "	APPROVED_BY, ";
							$sql .= "	DEPARTMENT, ";
							$sql .= "	REQ_ERR, ";
							$sql .= "	ID_TEAM, ";
							$sql .= "	ERR_TYPE, ";
							$sql .= "	WORKCENTER, ";
							$sql .= "	ID_VND, ";
							$sql .= "	ID_ITEM_VND, ";
							if ($date <> '') {
								$sql .= "	DATE_APPROVED, ";
							}							
							if ($invoice <> '') {
								$sql .= "	ID_INVC_CRED, ";
							}
							if ($compCost <> '') {
								$sql .= "	COST_COMP, ";
							}
							if ($laborCost <> '') {
								$sql .= "	COST_LAB, ";
							}
							if ($shipCost <> '') {
								$sql .= "	COST_SHIP, ";
							}
							$sql .= "	COST_TOT, ";
							$sql .= "   RGA_ISO_STATUS";
							$sql .= "	) VALUES ( ";
							$sql .= " (Select rowid from nsa.RGA_BASE" . $DB_TEST_FLAG . " where RGA_NUMBER = '". $rgaNumber ."'), ";
							$sql .= " '" . ms_escape_string($rgaNumber) . "', ";
							$sql .= " '" . ms_escape_string($seqInvest) . "', ";
							$sql .= " '" . $UserRow['ID_USER'] . "', ";
							$sql .= " 	GetDate(), ";
							$sql .= " '" . $level . "', ";
							$sql .= " '" . ms_escape_string($findings) . "', ";
							$sql .= " '" . ms_escape_string($cause) . "', ";
							$sql .= " '" . ms_escape_string($contain) . "', ";
							$sql .= " '" . ms_escape_string($corr) . "', ";
							$sql .= " '" . ms_escape_string($actionNeed) . "', ";
							$sql .= " '" . ms_escape_string($desc) . "', ";
							$sql .= " '" . $UserRow['ID_USER'] . "', ";
							$sql .= " '" . $flag_car . "', ";
							$sql .= " '" . ms_escape_string($carNumber) . "', ";
							$sql .= " '" . ms_escape_string($approve) . "', ";
							$sql .= " '" . $dept . "', ";
							$sql .= " '" . $errReq . "', ";
							$sql .= " '" . ms_escape_string($team) . "', ";
							$sql .= " '" . ms_escape_string($errorType) . "', ";
							$sql .= " '" . ms_escape_string($workcenter) . "', ";
							$sql .= " '" . ms_escape_string($vendor) . "', ";
							$sql .= " '" . ms_escape_string($itemNumber) . "', ";
							if ($date <> '') {
								$sql .= " '" . ms_escape_string($date) . "', ";
							}							
							if ($invoice <> '') {
								$sql .= " '" . stripIllegalChars2($invoice) . "', ";
							}
							if ($compCost <> '') {
								$sql .= " '" . stripIllegalChars2($compCost) . "', ";
							}
							
							if ($laborCost <> '') {
								$sql .= " '" . stripIllegalChars2($laborCost) . "', ";
							}
							if ($shipCost <> '') {
								$sql .= " '" . stripIllegalChars2($shipCost) . "', ";
							}
							$sql .= " '" . stripIllegalChars2($totCost) . "', ";
							$sql .= " '" . ms_escape_string($isoStatus) . "' ";
							$sql .= " )  SELECT LAST_INSERT_ID=@@IDENTITY";
							QueryDatabase($sql, $results);

							$sql  = " UPDATE nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
							$sql .= " SET";
							$sql .= " RGA_STATUS = '" . $rgaStatus . "', ";
							$sql .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
							$sql .= " DATE_CHG = GetDate() ";
							$sql .= " WHERE RGA_NUMBER = '". $rgaNumber ."'"; 
							QueryDatabase($sql, $results);

							$sql  = " SELECT rb.NAME_CUST, ";
							$sql .= " rb.SALES_MGR, ";
							$sql .= " sr.ADDR_EMAIL as EMAIL_SLSREP, ";
							$sql .= " wa.ID_USER, ";
							$sql .= " wa.EMAIL as EMAIL_CSR, ";
							$sql .= " wa.NAME_EMP ";
							$sql .= " FROM nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
							$sql .= " left join nsa.tables_slsrep sr ";
							$sql .= " on rb.SALES_MGR = sr.NAME_SLSREP ";
							$sql .= " and sr.ADDR_EMAIL is not null ";
							$sql .= " left join nsa.DCWEB_AUTH wa";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							//$sql .= " WHERE rowid = '". $BaseRowID ."'";
							$sql .= " WHERE rb.RGA_NUMBER = '". $rgaNumber ."'";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								if (trim($sendEmail) == 'Send') {
									if ($rgaStatus == 'Closed') {
										$files = array();
										$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*.{jpg,png,gif,bmp,tif,pdf}", GLOB_BRACE);
										foreach ($a_files as $filename){
											$short_filename = substr($filename, strrpos($filename, '/') + 1);
											$tempFilename = "/tmp/RGA_temp/" . $short_filename;
											shell_exec("cp " . $filename . " " . $tempFilename);
											array_push($files, $tempFilename);
										}

										if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
											$head = array(
												'to'      =>array('TESTGroup-ClosedRGA@thinknsa.com'=>'Group-ClosedRGA'),
												'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
												'cc'      =>array('gvandyne@thinknsa.com'=>$row['SALES_MGR'], $row['EMAIL_CSR']=>$row['NAME_EMP']),
												//'bcc'     =>array('email4@email.net'=>'Admin'),
											);
										} else {
											$head = array(
												//'to'      =>array('TESTGroup-ClosedRGA@thinknsa.com'=>'Group-ClosedRGA'),
												'to'      =>array('Group-ClosedRGA@thinknsa.com'=>'Group-ClosedRGA'),
												'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
												'cc'      =>array($row['EMAIL_SLSREP']=>$row['SALES_MGR'], $row['EMAIL_CSR']=>$row['NAME_EMP']),
												//'bcc'     =>array('email4@email.net'=>'Admin'),
											);
										}

										$subject = "CLOSED RGA " . $rgaNumber . " - " . $row['NAME_CUST'];
										$body = GenerateHTMLforEmail($rgaNumber);
										//$files = array($file1,$file2);
										if (!empty($files)) {
											mail::send($head,$subject,$body,$files);
										} else {
											mail::send($head,$subject,$body);
										}
									}
								}	
							}
							$ret .= "SAVED </br>" . date('Y-m-d H:i:s');
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}
					break;	
					


					case "form_review_isoreq":
						if (isset($_POST["rgaNumber"])) {
							$rgaNumber = $_POST["rgaNumber"];

							//$sql = "SET ANSI_NULLS ON";
							//QueryDatabase($sql, $results);
							//$sql = "SET ANSI_WARNINGS ON";
							//QueryDatabase($sql, $results);
							
							$sql1  = "SELECT NAME_SLSREP, ltrim(ID_SLSREP) as ID_SLSREP "; 
							$sql1 .= " FROM nsa.tables_slsrep "; 
							$sql1 .= " WHERE ADDR_EMAIL is not NULL ";
							$sql1 .= " ORDER BY NAME_SLSREP asc";
							QueryDatabase($sql1, $results1);
/*
							$sql =  "SELECT ";
							$sql .= " wa.NAME_EMP, ";
							$sql .= " ri.rowid as isoRowID,";
							$sql .= " rb.rowid as BaseRowID,";
							$sql .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
							$sql .= " convert(varchar(10),rb.DATE_ISSUE,126) as rbDate_ISSUE,";
							$sql .= " rb.ID_USER_ADD as rb_ID_USER_ADD, ";
							$sql .= " rb.*, ";
							$sql .= " ri.* ";
							$sql .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
							$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
							$sql .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " where ";
							$sql .= " rb.RGA_NUMBER = '" . $rgaNumber . "' ";
							$sql .= " order by ri.SEQ_INVEST asc ";
							QueryDatabase($sql, $results);							
*/
							$sql =  "SELECT ";
							$sql .= " wa.NAME_EMP, ";
							//$sql .= " ri.rowid as isoRowID,";
							$sql .= " rb.rowid as BaseRowID,";
							//$sql .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
							$sql .= " convert(varchar(10),rb.DATE_ISSUE,126) as rbDate_ISSUE,";
							$sql .= " rb.ID_USER_ADD as rb_ID_USER_ADD, ";
							$sql .= " rb.* ";
							//$sql .= " ri.* ";
							$sql .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
							//$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
							//$sql .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
							$sql .= " left join nsa.DCWEB_AUTH wa ";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							$sql .= " where ";
							$sql .= " rb.RGA_NUMBER = '" . $rgaNumber . "' ";
							//$sql .= " order by ri.SEQ_INVEST asc ";
							QueryDatabase($sql, $results);							

							while ($row = mssql_fetch_assoc($results)) {
								 if (strpos($UserRow['EMP_ROLE'], ":RGA-ISO:") || strpos($UserRow['EMP_ROLE'], ":RGA-ISO-ADMIN:") || strpos($UserRow['EMP_ROLE'], ":RGA-FINANCE:")) {
									$ret .= "<table width='850' >\n";
									$ret .= "	<tr class='blueHeader'>\n";
									$ret .= "		<th colspan='4'><left><img src=''></left> <right>RGA & Customer Complaint</right></th>\n";
									
									////////////// WORK NEEDED TO ADDRESS MULTIPLE isoRowIDs
									//$ret .= "		<input id='isoRowID' name='isoRowID' type='hidden' value='" . $row['isoRowID'] . "'>\n";
									$ret .= "		<input id='BaseRowID' name='BaseRowID' type='hidden' value='" . $row['BaseRowID'] . "'>\n";
									//$ret .= "		<input id='shipRowID' name='shipRowID' type='hidden' value='" . $row['shipRowID'] . "'>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>RGA Number:</td>\n";
									$ret .= "		<td><input id='txt_rgaNumber' name='txt_rgaNumber' type='text' $READONLY value=" . $rgaNumber . " READONLY style='background-color:#D0D0D0;'></input></td>\n";
									$ret .= "		<td>Date Issued:</td>\n";
									$ret .= "		<td><input id='txt_date' name='txt_date' type='text' $READONLY value='" . $row['rbDate_ISSUE'] . "'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Customer #:</td>\n";
									$ret .= "		<td><input id='txt_customerID' name='txt_customerID' type='text' $READONLY value='" . $row['ID_CUST'] . "' maxlength='6'></input></td>\n";
									$ret .= "		<td>Customer Name:</td>\n";
									$ret .= "		<td><input id='txt_NAME_CUST' name='txt_NAME_CUST' type='text' $READONLY value='".$row['NAME_CUST']."' maxlength='30' size=30></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>City:</td>\n";
									$ret .= "		<td><input id='txt_CITY' name='txt_CITY' type='text' $READONLY value='".$row['CITY']."' maxlength='15'></input></td>\n";
									$ret .= "		<td>State:</td>\n";
									$ret .= "		<td><input id='txt_ID_ST' name='txt_ID_ST' type='text' $READONLY value='" . $row['ID_ST'] . "' maxlength='2'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Prov:</td>\n";
									$ret .= "		<td><input id='txt_PROV' name='txt_PROV' type='text' $READONLY value='" . $row['PROV'] . "' maxlength='30'></input></td>\n";
									$ret .= "		<td>Country:</td>\n";
									$ret .= "		<td><input id='txt_COUNTRY' name='txt_COUNTRY' type='text' $READONLY value='" . $row['COUNTRY'] . "' maxlength='30'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Contact Name:</td>\n";
									$ret .= "		<td><input id='txt_NAME_CONTACT_CUST' name='txt_NAME_CONTACT_CUST' type='textbox' $READONLY value='".$row['CONTACT_NAME']."' maxlength='25'></input></td>\n";
									$ret .= "		<td>Phone #:</td>\n";
									$ret .= "		<td><input id='txt_PHONE' name='txt_PHONE' type='text' $READONLY value='" . $row['PHONE_NUMBER'] . "' maxlength='20'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Email:</td>\n";
									$ret .= "		<td><input id='txt_email' name='txt_email' type='text' $READONLY value='" . $row['EMAIL'] . "' maxlength='100'></input></td>\n";
									$ret .= "		<td>Classification:</td>\n";
									$ret .= "		<td><select id='select_classification' name='select_classification' $READONLY>\n";
									$ClassificationMDArray = array(
										array("","--Select--"),
										array("NQE","Non-Quality Error"),
										array("PQE","Product Quality Error"),
									);
									for ($rowClassification = 0; $rowClassification < 3; $rowClassification++) {
										$SELECTED = '';
										$CURRENT = '';

										if (trim($row['CLASSIFICATION']) == trim($ClassificationMDArray[$rowClassification][0])) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='" . $ClassificationMDArray[$rowClassification][0] . "' " . $SELECTED . ">" . $CURRENT . $ClassificationMDArray[$rowClassification][1] .  "</option>\n";
									}
									$ret .= "		</select></td>\n";

									$sql2  = "select ";
									$sql2 .= " convert(varchar(10),rl.DATE_SHIPPED,126) as rlDate_SHIPPED, ";
									$sql2 .= " rl.* ";
									$sql2 .= " from nsa.RGA_LINE" . $DB_TEST_FLAG . " rl";
									$sql2 .= " where rl.RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= " ORDER BY rl.SEQ_LINE_RGA asc ";
									QueryDatabase($sql2, $results2);
									$LineCount = mssql_num_rows($results2);
									while ($row2 = mssql_fetch_assoc($results2)) {
										$style = 'table-row';
										$y = $row2['SEQ_LINE_RGA'];
										$z = $y+1;
										
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
										$ret .= "		<td>Order #:</td>\n";
										$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' $READONLY value='".$row2['ID_ORD']."' maxlength='8'></input></td>\n";
										$ret .= "		<td>PO #:</td>\n";
										$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' $READONLY value='".$row2['ID_PO']."' maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' $READONLY value='".$row2['ID_ITEM']."' maxlength='30'></input></td>\n";
										$ret .= "		<td>Quantity:</td>\n";
										$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' $READONLY value='".$row2['QUANTITY']."' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' $READONLY value='".$row2['ID_INVC']."' maxlength='8'></input></td>\n";//added invoiceNumber to form
										$ret .= "		<td>Date Ship (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text' $READONLY value='".$row2['rlDate_SHIPPED']."' ></input></td>\n";
										$ret .= "	</tr>\n";
										
										if ($y==$LineCount){
											$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
											$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','4')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','4','1')\">+Clone</font></td>\n";
											$ret .= "	</tr>\n";
										}
									}
									for ($y = $LineCount+1; $y <= 10; $y++) {
										if ($y==1) {
											$style = 'table-row';
										} else {
											$style = 'display:none;';
										}
										$z = $y+1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".2' style='".$style."'>\n";
										$ret .= "		<td>Order #:</td>\n";
										$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' $READONLY maxlength='8'></input></td>\n";
										$ret .= "		<td>PO #:</td>\n";
										$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' $READONLY maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' $READONLY maxlength='30'></input></td>\n";
										$ret .= "		<td>Quantity:</td>\n";
										$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' $READONLY maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_ord".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' $READONLY maxlength='8'></input></td>\n";//added invoiceNumber to form
										$ret .= "		<td>Date Ship (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text' $READONLY></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showOrdInputRow('$z','4')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showOrdInputRow('$z','4','1')\">+Clone</font></td>\n";
										$ret .= "	</tr>\n";
									}

									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Issued by:</td>\n";
									$ret .= "		<td>".$row['rb_ID_USER_ADD']."</td>\n";
									$ret .= "		<td>Authorized by:</td>\n";
									$ret .= "		<td><select id='select_auth' ". $row['AUTHORIZED_BY'] . "' $READONLY >\n";
									
									$AUTH_BY = array("CG2","DS","MCF","STG","JG","Stock");
									foreach ($AUTH_BY as $SELECT_AUTH) {
										$SELECTED = '';
										$CURRENT = '';

										if (trim($row['AUTHORIZED_BY']) == trim($SELECT_AUTH)) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='" . $SELECT_AUTH . "' " . $SELECTED . ">" . $CURRENT . $SELECT_AUTH .  "</option>\n";
									}
									$ret .= "		</select></td>\n";

									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Sales Manager:</td>\n";
									$ret .= "		<td><select id='selectSalesMgr' onchange=\"slsMgrToTerr()\" $READONLY >\n";
									while ($row1 = mssql_fetch_assoc($results1)){
										$SELECTED = '';
										$CURRENT = '';
										$ID = $row1['ID_SLSREP'];
										$NAME = $row1['NAME_SLSREP'];
										$Value = $ID . "_" . $NAME;
										if ($row1['NAME_SLSREP'] == "--Select--") {
											$Value = '';
										}

										if (trim($row['SALES_MGR']) == trim($NAME)) {
											$SELECTED = 'SELECTED';
											$CURRENT = '*';
										}
										$ret .= "				<option value='". $Value . "' " . $SELECTED . ">" . $CURRENT . $row1['NAME_SLSREP'] .  "</option>\n";
								    }
									$ret .= "		</select></td>\n";
									$ret .= "		<td>Territory:</td>\n";
									$ret .= "		<td><input id='txt_territory' name='txt_territory' type='text' $READONLY value='" . $row['ID_TERR'] . "' maxlength='3'></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Description of <br/>Request/Complaint:</td>\n";
									$ret .= "		<td colspan='3'><textarea id='txt_descr' name='txt_descr' cols='25' rows='7' $READONLY >".$row['DESCR']."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									$ret .= "<table width='850'>\n";
									$ret .= "   <tr class='dbc'>\n";
									$ret .= "   <td colspan='6'><strong>Please Select One (Need to Select One)</strong></td>\n";
									$ret .= "   </tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Complaint with Return?</td>\n";

									$rgaSelectA = '';
									$rgaSelectB = '';
									$rgaSelectC = '';
									if ($row['RGA_CLASS'] == 'A'){
										$rgaSelectA = "checked = 'checked'";
									}
									if ($row['RGA_CLASS'] == 'B'){
										$rgaSelectB = "checked = 'checked'";
									}
									if ($row['RGA_CLASS'] == 'C'){
										$rgaSelectC = "checked = 'checked'";
									}
									$ret .= "		<td><input type='radio' id='A' name='choice' ".$rgaSelectA." value='A' $READONLY onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">A</input></td>\n";
									$ret .= "		<td>Complaint without Return?</td>\n";
									$ret .= "		<td><input type='radio' id='B' name='choice' ".$rgaSelectB." value='B' $READONLY onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">B</input></td>\n";
									$ret .= "		<td>Customer Request?</td>\n";
									$ret .= "		<td><input type='radio' id='C' name='choice' ".$rgaSelectC." value='C' $READONLY onClick=\"rating('tr_RGA_RATING');check('table_shipping')\">C</input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "</table>\n";
									$ret .= "<table width='850'>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Follow-Up that<br /> Requires Action:</td>\n";
									$ret .= "		<td colspan=3><textarea id='txt_followUp' name='txt_followUp' cols='55' $READONLY>".$row['FOLLOW_UP_DESCR']."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc' style='display:none'>\n";
									$ret .= "		<td>Email to<br /> Notify:</td>\n";
									$ret .= "		<td><input id='txt_email_notify' name='txt_email_notify' type='text' size='65' $READONLY value='" . $row['EMAIL_LIST'] . "' ></input></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Additional<br /> Information:</td>\n";
									$ret .= "		<td colspan=3><textarea id='txt_add_info' name='txt_add_info' cols='55' $READONLY >".$row['ADD_INFO']."</textarea></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td>Attached Files</td>\n";
									$ret .= "		<td colspan=3>\n";

									$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*");
									foreach ($a_files as $filename){
										//error_log($filename);
										$filename = str_replace('..','/protected',$filename);
										$short_filename = substr($filename, strrpos($filename, '/') + 1);
										
										$ret .=	"	<a href='" . $filename . "' target='_blank'>".$short_filename."</a></br>\n";
									}
									$ret .= "		</td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td colspan=4><input id='fileToUpload' name='fileToUpload' type='file' value='Choose File' ></input><input type='button' value='Upload' onclick='uploadFile()'></td>\n";
									$ret .= "	</tr>\n";
									$ret .= "	<tr class='dbc'>\n";
									$ret .= "		<td colspan=4></input><progress id='progressBar' value='0' max='100'></progress><h5 id='status'><input id='ret_FileName' type='hidden' value=''></input></h5></td>\n";
									$ret .= "	</tr>\n";					

									if (strpos($UserRow['EMP_ROLE'], ":RGA-ISO-ADMIN:") !== FALSE) {
										$ret .= "	<tr class='dbc'>\n";
										//$ret .= "		<td colspan=4><input id='button_SubmitNew' name='button_SubmitNew' type='button' value='Save' onClick=\"updateRgaBase()\"  \"></input><div id='div_submitResp' name='div_submitResp'></div></td>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= " 			<input id='button_updateRgaBase' name='button_updateRgaBase' type='button' value='Submit' DISABLED onclick=\"updateRgaBase()\" ></input>\n";
										$ret .= " 			**Send Email?<select id='sel_Email_updateRgaBase' name='sel_Email_updateRgaBase' onchange=\"checkEnableButton('sel_Email_updateRgaBase','button_updateRgaBase')\">\n";
										$ret .= "   	        <option value=''>--Select--</option>\n";
										$ret .= "   	        <option value='Send'>Send</option>\n";
										$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
										$ret .= "			</select>\n";
										$ret .= "			<div id='div_submitResp_updateRgaBase' name='div_submitResp_updateRgaBase'></div>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
									} else {
										$ret .= "	<tr class='dbc'>\n";
										//$ret .= "		<td colspan=4><input id='button_SubmitNew' name='button_SubmitNew' type='button' value='Save' DISABLED  \"></input><div id='div_submitResp' name='div_submitResp'></div></td>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= " 			<input id='button_updateRgaBase' name='button_updateRgaBase' type='button' value='Submit' DISABLED onclick=\"updateRgaBase()\" ></input>\n";
										$ret .= " 			**Send Email?<select id='sel_Email_updateRgaBase' name='sel_Email_updateRgaBase' DISABLED onchange=\"checkEnableButton('sel_Email_updateRgaBase','button_updateRgaBase')\">\n";
										$ret .= "   	        <option value=''>--Select--</option>\n";
										$ret .= "   	        <option value='Send'>Send</option>\n";
										$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
										$ret .= "			</select>\n";
										$ret .= "			<div id='div_submitResp_updateRgaBase' name='div_submitResp_updateRgaBase'></div>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
									}
									$ret .= "<table width='850' id='table_shipping' style='display:table;'>\n";
									$ret .= "	<tr class='dbc'>\n";
									//$ret .= "		<th colspan='4'>For Shipping Only</th>\n";
									$ret .= "		<input id='hidden_RGA_NUMBER' name='hidden_RGA_NUMBER' type='hidden' value='" . $rgaNumber . "'>\n";
									$ret .= "	</tr>\n";

									$sql2  = "select ";
									$sql2 .= " convert(varchar(10),DATE_RECEIVED,121) as rsDATE_RECEIVED,";
									$sql2 .= " rowid as shipRowID, ";
									$sql2 .= " * ";
									$sql2 .= "from nsa.RGA_SHIP" . $DB_TEST_FLAG . " ";
									$sql2 .= "where RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= "ORDER BY SEQ_LINE_SHIP asc ";
									QueryDatabase($sql2, $results2);
									$LineCount = mssql_num_rows($results2);
									$ShipLineCount = $LineCount;
									while($row2 = mssql_fetch_assoc($results2)){
										$style= 'table-row';
										$y = $row2['SEQ_LINE_SHIP'];
										$z = $y + 1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
										$ret .= "		<input id='shipRowID' name='shipRowID' type='hidden' value='" . $row2['shipRowID'] . "'>\n";
										$ret .= "       <td colspan=2></td>\n";
										$ret .= "		<td>Item Recieved:</td>\n";
										$ret .= "		<td><input id='txt_itemReceived".$y."' name='txt_itemReceived".$y."' width='500px' type='text' $READONLY value='".$row2['ITEM_RECEIVED']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text' $READONLY value='".$row2['rsDATE_RECEIVED']."'></input></td>\n";
										$ret .= "		<td>Quantity Received:</td>\n";
										$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."' type='text' $READONLY value='".$row2['QUANTITY_RECEIVED']."' maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."'>\n";
										$ret .= "		<td>Carrier:</td>\n";
										$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' $READONLY value='".$row2['CARRIER']."' maxlength='12'></input></td>\n";
										$ret .= "		<td>Tracking #:</td>\n";
										$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' $READONLY value='".$row2['TRACKING_NO']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".5' style='".$style."'>\n";
										$ret .= "		<td>Condition Received:</td>\n";
										$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' $READONLY value='".$row2['COND_RECEIVED']."' maxlength='50'></input></td>\n";
										$ret .= "		<td>Received by:</td>\n";
										$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' $READONLY value='".$row2['RECEIVED_BY']."' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .=" 	<tr class='dbc' id='tr_itm".$y.".6' style='".$style."'>\n";
										$ret .= "		<td>Comments:</td>\n";
										$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' cols='55' $READONLY >".$row2['COMMENTS']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										if ($y==$LineCount){
											$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
											$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','6','1')\">+Clone</font></td>\n";
											$ret .= "	</tr>\n";
										}
									}
									for ($y = $LineCount+1; $y <= 10; $y++){
										if($y==1){
											$style = 'table-row';
										}else{
											$style = 'display:none;';
										}
										$z = $y + 1;
										$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
										$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".2' style='".$style."'>\n";
										$ret .= "       <td colspan=2></td>\n";
										$ret .= "		<td>Item Recieved:</td>\n";
										$ret .= "		<td><input id='txt_itemReceived". $y."' name='txt_itemReceived".$y."' $READONLY  width='500px' type='text' maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".3' style='".$style."'>\n";
										$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
										$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text' $READONLY></input></td>\n";
										$ret .= "		<td>Quantity Received:</td>\n";
										$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."' type='text' $READONLY maxlength='10'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".4' style='".$style."' >\n";
										$ret .= "		<td>Carrier:</td>\n";
										$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' $READONLY maxlength='12'></input></td>\n";
										$ret .= "		<td>Tracking #:</td>\n";
										$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' $READONLY maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_itm".$y.".5' style='".$style."'>\n";
										$ret .= "		<td>Condition Received:</td>\n";
										$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' $READONLY maxlength='50'></input></td>\n";
										$ret .= "		<td>Received by:</td>\n";
										$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' $READONLY maxlength='50'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .=" 	<tr class='dbc' id='tr_itm".$y.".6' style='".$style."'>\n";
										$ret .= "		<td>Comments:</td>\n";
										$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' cols='55' $READONLY></textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_itm".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4><font style='cursor: hand' title='Add Blank Record' onclick=\"showItmInputRow('$z','6')\">+Blank   </font><font style='cursor: hand' title='Copy Previous Record' onclick=\"showItmInputRow('$z','6','1')\">+Clone</font></td>\n";
										$ret .= "	</tr>\n";	
									}
									if(strpos($UserRow['EMP_ROLE'], ":RGA-ISO-ADMIN:") !== FALSE) {
										//if(empty($row['shipRowID'])){ //if not in table show button for new submission, else show button for update
										//error_log("ShipLineCount: " . $ShipLineCount);
										if ($ShipLineCount==0){ //if not in table show button for new submission, else show button for update
											$ret .= "	<tr class='dbc'>\n";
											//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' type='button' value='Submit' onClick=\"insertNewShip()\" ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
											$ret .= "		<td colspan=4>\n";
											$ret .= " 			<input id='button_insertNewShip' name='button_insertNewShip' type='button' value='Save' onclick=\"insertNewShip()\" DISABLED></input>\n";
											$ret .= " 			**Send Email?<select id='sel_Email_insertNewShip' name='sel_Email_insertNewShip' onchange=\"checkEnableButton('sel_Email_insertNewShip','button_insertNewShip')\">\n";
											$ret .= "   	        <option value=''>--Select--</option>\n";
											$ret .= "   	        <option value='Send'>Send</option>\n";
											$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
											$ret .= "			</select>\n";
											$ret .= "			<div id='div_submitResp_insertNewShip' name='div_submitResp_insertNewShip'></div>\n";
											$ret .= "		</td>\n";											
											$ret .= "	</tr>\n";
										} else {
											$ret .= "	<tr class='dbc'>\n";
											//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' onclick=\"updateRgaShip()\" type='button' value='Save' ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
											$ret .= "		<td colspan=4>\n";
											$ret .= " 			<input id='button_updateRgaShip' name='button_updateRgaShip' type='button' value='Save' onclick=\"updateRgaShip()\" DISABLED></input>\n";
											$ret .= " 			**Send Email?<select id='sel_Email_updateRgaShip' name='sel_Email_updateRgaShip' onchange=\"checkEnableButton('sel_Email_updateRgaShip','button_updateRgaShip')\">\n";
											$ret .= "   	        <option value=''>--Select--</option>\n";
											$ret .= "   	        <option value='Send'>Send</option>\n";
											$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
											$ret .= "			</select>\n";
											$ret .= "			<div id='div_submitResp_updateRgaShip' name='div_submitResp_updateRgaShip'></div>\n";
											$ret .= "		</td>\n";											
											$ret .= "	</tr>\n";
										}
									} else { //NON ISO-ADMIN, NOT ALLOWED TO SUBMIT, BUTTON DISABLED WITH ONCLICK
										//$ret .= "	<tr class='dbc'>\n";
										//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' type='button' disabled value='Save' ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
										//$ret .= "	</tr>\n";
										//if(empty($row['shipRowID'])){ //if not in table show button for new submission, else show button for update
										//error_log("ShipLineCount2: " . $ShipLineCount);
										if ($ShipLineCount==0){ //if not in table show button for new submission, else show button for update
											$ret .= "	<tr class='dbc'>\n";
											//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' type='button' value='Submit' onClick=\"insertNewShip()\" ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
											$ret .= "		<td colspan=4>\n";
											$ret .= " 			<input id='button_insertNewShip' name='button_insertNewShip' type='button' value='Save' onclick=\"insertNewShip()\" DISABLED></input>\n";
											$ret .= " 			**Send Email?<select id='sel_Email_insertNewShip' name='sel_Email_insertNewShip' DISABLED onchange=\"checkEnableButton('sel_Email_insertNewShip','button_insertNewShip')\">\n";
											$ret .= "   	        <option value=''>--Select--</option>\n";
											$ret .= "   	        <option value='Send'>Send</option>\n";
											$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
											$ret .= "			</select>\n";
											$ret .= "			<div id='div_submitResp_insertNewShip' name='div_submitResp_insertNewShip'></div>\n";
											$ret .= "		</td>\n";											
											$ret .= "	</tr>\n";
										} else {
											$ret .= "	<tr class='dbc'>\n";
											//$ret .= "		<td colspan=4><input id='button_SubmitNew1' name='button_SubmitNew1' onclick=\"updateRgaShip()\" type='button' value='Save' ></input><div id='div_submitResp1' name='div_submitResp1'></div></td>\n";
											$ret .= "		<td colspan=4>\n";
											$ret .= " 			<input id='button_updateRgaShip' name='button_updateRgaShip' type='button' value='Save' onclick=\"updateRgaShip()\" DISABLED></input>\n";
											$ret .= " 			**Send Email?<select id='sel_Email_updateRgaShip' name='sel_Email_updateRgaShip' DISABLED onchange=\"checkEnableButton('sel_Email_updateRgaShip','button_updateRgaShip')\">\n";
											$ret .= "   	        <option value=''>--Select--</option>\n";
											$ret .= "   	        <option value='Send'>Send</option>\n";
											$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
											$ret .= "			</select>\n";
											$ret .= "			<div id='div_submitResp_updateRgaShip' name='div_submitResp_updateRgaShip'></div>\n";
											$ret .= "		</td>\n";											
											$ret .= "	</tr>\n";
										}										
									}	
									$ret .= "</table>\n";
									
									//////////////
									// INVEST RECORDS
									//////////////
									$sql2 =  "SELECT ";
									$sql2 .= " ri.rowid as isoRowID,";
									$sql2 .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
									$sql2 .= " ri.*, ";
									$sql2 .= " rb.rowid as baseRowID ";
									$sql2 .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
									$sql2 .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
									$sql2 .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
									$sql2 .= " where rb.RGA_NUMBER = '" . $rgaNumber . "' ";
									$sql2 .= " order by ri.SEQ_INVEST asc ";
									QueryDatabase($sql2, $results2);
									$y=0;
									while(($row2 = mssql_fetch_assoc($results2)) || ($y<10)){
										$y++;
										$z = $y + 1;
										$style = 'table-row';
										$seqInvest = $row2['SEQ_INVEST'];

										if (empty($seqInvest)) {
											$seqInvest = $y;
											$style = 'display:none;';
										} 

										if ($y==1) {
											$style = 'table-row';
										}

										$rgaRatingSelect1 = '';
										$rgaRatingSelect2 = '';
										$rgaRatingSelect3 = '';
										if($row2['RGA_RATING'] == '1'){
											$rgaRatingSelect1 = "checked = 'checked'";
										}
										if($row2['RGA_RATING'] == '2'){
											$rgaRatingSelect2 = "checked = 'checked'";
										}
										if($row2['RGA_RATING'] == '3'){
											$rgaRatingSelect3 = "checked = 'checked'";
										}

										//selects action needed checkboxes
										$rgaActionNeedRework = '';
										$rgaActionNeedReplace = '';
										$rgaActionNeedNonStock = '';
										$rgaActionNeedCredit = '';
										$rgaActionNeedOther = '';

										if (strpos($row2['ACTION_NEEDED'], ':Rework:') !== false) {
											$rgaActionNeedRework = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':Replace:') !== false) {
											$rgaActionNeedReplace = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':NonStk-Sample:') !== false) {
											$rgaActionNeedNonStock = 'CHECKED';
										}
										
										if (strpos($row2['ACTION_NEEDED'], ':Credit:') !== false) {
											$rgaActionNeedCredit = 'CHECKED';
										}

										if (strpos($row2['ACTION_NEEDED'], ':Other:') !== false) {
											$rgaActionNeedOther = 'CHECKED';
										}

										$carFlagN = '';
										$carFlagY = '';
										if($row2['FLAG_CAR'] == 'N'){
											$carFlagN = "checked = 'checked'";
										}
										if($row2['FLAG_CAR'] == 'Y'){
											$carFlagY = "checked = 'checked'";
										}
										/*
										if (empty($row2['isoRowID'])){
											$ret .="<form name= 'insertNewISO' action=\"javascript:insertNewInvest()\">";
										} else {
											if(strpos($UserRow['EMP_ROLE'], ":RGA-FINANCE:") !== FALSE) {
												$ret .="<form name= 'updateISO'  action=\"javascript:updateRgaInvestFinOnly()\" >";
											} else {
												$ret .="<form name= 'updateISO'  action=\"javascript:updateRgaInvest()\" >";
											}
										}
										*/
										$READONLY_FIN = "";
										if(strpos($UserRow['EMP_ROLE'], ":RGA-FINANCE:") !== FALSE) {
											$READONLY_FIN = "READONLY style='background-color:#D0D0D0;'";
										}

										$ret .= "<table width='850' id='table_inv_".$y."' style='".$style."'>\n";
										$ret .= "		<th colspan='4'>ISO Investigation " . $seqInvest . "</th>\n";
										$ret .= "		<input id='hidden_RGA_NUMBER' name='hidden_RGA_NUMBER' type='hidden' value='" . $rgaNumber . "'>\n";
										$ret .= "		<input id='isoRowID_".$seqInvest."' name='isoRowID_".$seqInvest."' type='hidden' value='" . $row2['isoRowID'] . "'>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc' id='tr_RGA_RATING' style='diplay:table-row;'>\n";
										$ret .= "		<td>RGA Rating (choose one):</td>\n";
										$ret .= "		<td><input type='radio' ".$rgaRatingSelect1." id='1_".$seqInvest."' name='level' value='1' >Level 1</input></td>\n";
										$ret .= "		<td><input type='radio' ".$rgaRatingSelect2." id='2_".$seqInvest."' name='level' value='2' >Level 2</input></td>\n";
										$ret .= "		<td><input type='radio' ".$rgaRatingSelect3." id='3_".$seqInvest."' name='level' value='3' >Level 3</input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Findings:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_findings_".$seqInvest."' name='txt_findings_".$seqInvest."' cols='55' $READONLY_FIN>".$row2['FINDINGS']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Cause:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_cause_".$seqInvest."' name='txt_cause_".$seqInvest."' cols='55' $READONLY_FIN>".$row2['CAUSE']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Containment:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_contain_".$seqInvest."' name='txt_contain_".$seqInvest."' cols='55' $READONLY_FIN>".$row2['CONTAINMENT']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Correction:</td>\n";
										$ret .= "		<td colspan=3><textarea id='txt_corr_".$seqInvest."' name='txt_corr_".$seqInvest."' cols='55' $READONLY_FIN>".$row2['CORRECTION']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
										$ret .= "<table width='850' id='table_action_".$y."' style='".$style."'>\n";
										$ret .= "	<th colspan='7'></th>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td><strong>Action Needed:</strong></td>\n";
										$ret .= "		<td style='text-align:left;'>\n";
										$ret .= "			<input type='checkbox' id='action_Rework_".$seqInvest."' name='action_Rework_".$seqInvest."' $rgaActionNeedRework value=':Rework:' $READONLY_FIN>Rework</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Replace_".$seqInvest."' name='action_Replace_".$seqInvest."' $rgaActionNeedReplace value=':Replace:' $READONLY_FIN>Replace</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_NonStk-Sample_".$seqInvest."' name='action_NonStk-Sample_".$seqInvest."' $rgaActionNeedNonStock value=':NonStk-Sample:' $READONLY_FIN>NonStock/Sample</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Credit_".$seqInvest."' name='action_Credit_".$seqInvest."' $rgaActionNeedCredit value=':Credit:' $READONLY_FIN>Credit</input><br />\n";
										$ret .= "			<input type='checkbox' id='action_Other_".$seqInvest."' name='action_Other_".$seqInvest."' $rgaActionNeedOther value=':Other:' $READONLY_FIN>Other</input>\n";
										$ret .= "		</td>\n";
										$ret .= "		<td colspan=5><textarea id='txt_desc_".$seqInvest."' name='txt_desc_".$seqInvest."' $READONLY_FIN>".$row2['ACTION_DESCR']."</textarea></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td colspan=2>Is a Corrective Action Request (CAR) required? (select one)</td>\n";
										$ret .= "		<td><input type='radio' ".$carFlagN." id='N_".$seqInvest."' name='option' value='N'>No</input></td>\n";
										$ret .= "		<td><input type='radio' ".$carFlagY." id='Y_".$seqInvest."' name='option' value='Y'>Yes</input></td>\n";
										$ret .= "		<td>CAR #</td>\n";
										$ret .= "		<td colspan=2><input id='txt_carNumber_".$seqInvest."' name='txt_carNumber_".$seqInvest."' type='text'  value='".$row2['CAR_NUMBER']."' $READONLY_FIN maxlength='25'></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td colspan=2>Proposed action approved by:</td>\n";
										$ret .= "		<td colspan=2><input id='txt_approve_".$seqInvest."' name='txt_approve_".$seqInvest."' type='text' value='".$row2['APPROVED_BY']."' $READONLY_FIN maxlength='30'></input></td>\n";
										$ret .= "		<td>Date (yyyy-mm-dd):</td>\n";
										$ret .= "		<td colspan=2><input id='txt_dateSubmit_".$seqInvest."' name='txt_dateSubmit_".$seqInvest."' type='text' value='".$row2['riDATE_APPROVED']."' $READONLY_FIN></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
										$ret .= "<table width='850'  id='table_track_".$y."' style='".$style."'>\n";
										$ret .= " 	<th colspan='4'>For Tracking Only</th>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Department:</td>\n";
										$ret .= "		<td><Select id='select_dept_".$seqInvest."' name='select_dept_".$seqInvest."' onchange=\"checkDept($seqInvest)\" $READONLY_FIN>\n";
										$ret .= "			<option value=''>--Select--</option>\n";

										$DEPT_NAME = array("Production", "Non-Production");
										foreach ($DEPT_NAME as $SELECT_DEPT){
											$SELECTED = '';
											$CURRENT = '';

											if (trim($row2['DEPARTMENT']) == trim($SELECT_DEPT)) {
												$SELECTED = 'SELECTED';
												$CURRENT = '*';
											}
											$ret .= "		<option value='" . $SELECT_DEPT . "' " . $SELECTED . ">" . $CURRENT . $SELECT_DEPT .  "</option>\n";
										}
										$ret .= "		</Select></td>\n";
										$ret .= "		<td>Request/Error:</td>\n";
										$ret .= "		<td><select id='select_err_".$seqInvest."' name='select_err_".$seqInvest."' $READONLY_FIN>\n";
										$ret .= "			<option value=''>--Select--</option>\n";
										
										$REQ_ERR = array("Error","Request", "Dispute");
										foreach($REQ_ERR as $SELECT_ERR) {
											$SELECTED = '';
											$CURRENT = '';

											if (trim($row2['REQ_ERR']) == trim($SELECT_ERR)) {
												$SELECTED = 'SELECTED';
												$CURRENT = '*';
											}
											$ret .= "				<option value='" . $SELECT_ERR . "' " . $SELECTED . ">" . $CURRENT . $SELECT_ERR .  "</option>\n";
										}
										$ret .= "		</select></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Workcenter:</td>\n";
										
										$READONLY_FIN_WC = '';
										if ($READONLY_FIN <> '') {
											$READONLY_FIN_WC = "READONLY";
										}

										$ret .= "		<td><Select id='select_workcenter_".$seqInvest."' name='select_workcenter_".$seqInvest."' $READONLY_FIN_WC onchange=\"checkWorkcenter($seqInvest)\" $READONLY_FIN>\n";
										$ret .= "			<option value=''>--Select--</option>\n";
										
										$sqlwc = " select distinct WC_GROUP";
										$sqlwc .= " from nsa.RGA_WC_ERRORS";
										$sqlwc .= " where WC_GROUP <> 'NON-PRODUCTION'";
										QueryDatabase($sqlwc, $resultswc);
										while ($rowwc = mssql_fetch_assoc($resultswc)){
											$SELECTED = '';
											$CURRENT = '';

											if (trim($row2['WORKCENTER']) == $rowwc['WC_GROUP']) {
												$SELECTED = 'SELECTED';
												$CURRENT = '*';
											}
											$ret .= "		<option value='" . $rowwc['WC_GROUP'] . "' " . $SELECTED . ">" . $CURRENT . $rowwc['WC_GROUP'] .  "</option>\n";
										}
										$ret .= "		</Select></td>\n";
										$ret .= "		<td>Credit Invoice #:</td>\n";
										$ret .= "		<td><input id='txt_invoice_".$seqInvest."' name='txt_invoice_".$seqInvest."' type='text'  value='".$row2['ID_INVC_CRED']."' maxlength='8'></input></td>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Team/Individual:</td>\n";
										$ret .= "		<td><input id='txt_team_".$seqInvest."' name='txt_team_".$seqInvest."' type='text' value='".$row2['ID_TEAM']."' $READONLY_FIN maxlength='25'></input></td>\n";
										$ret .= "		<td>Component Costs:</td>\n";
										$ret .= "		<td><input id='txt_compCost_".$seqInvest."' name='txt_compCost_".$seqInvest."' type='text'  value='".$row2['COST_COMP']."' $READONLY_FIN></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Error Type:</td>\n";
										$ret .= "		<td><select id='select_errorType_".$seqInvest."' name ='select_errorType_".$seqInvest."' $READONLY_FIN>\n";
										$ret .= "			<option value=''>--Select--</option>\n";

										$sqlEr = " select ERROR_TYPE";
										$sqlEr .= " from nsa.RGA_WC_ERRORS";
										$sqlEr .= " where WC_GROUP = '" .$row2['WORKCENTER']. "'";
										QueryDatabase($sqlEr, $resultsEr);
										while ($rowEr = mssql_fetch_assoc($resultsEr)){
											$SELECTED = '';
											$CURRENT = '';

											if (trim($row2['ERR_TYPE']) == $rowEr['ERROR_TYPE']) {
												$SELECTED = 'SELECTED';
												$CURRENT = '*';
											}
											$ret .= "		<option value='" . $rowEr['ERROR_TYPE'] . "' " . $SELECTED . ">" . $CURRENT . $rowEr['ERROR_TYPE'] .  "</option>\n";
										}
										$ret .= "		</select></td>\n";
										$ret .= "		<td>Labor Costs:</td>\n";
										$ret .= "		<td><input id='txt_laborCost_".$seqInvest."' name='txt_laborCost_".$seqInvest."' type='text'  value='".$row2['COST_LAB']."' $READONLY_FIN></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Vendor ID:</td>\n";
										$ret .= "		<td><input id='txt_vendor_".$seqInvest."' name='txt_vendor_".$seqInvest."' type='text' value='".$row2['ID_VND']."' $READONLY_FIN maxlength='6'></input></td>\n";
										$ret .= "		<td>Shipping Costs:</td>\n";
										$ret .= "		<td><input id='txt_shipCost_".$seqInvest."' name='txt_shipCost_".$seqInvest."' type='text'  value='".$row2['COST_SHIP']."' $READONLY_FIN></input></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>Part #:</td>\n";
										$ret .= "		<td><input id='txt_isoPartNumber_".$seqInvest."' name='txt_isoPartNumber_".$seqInvest."' type='text'  value='".$row2['ID_ITEM_VND']."' $READONLY_FIN maxlength='30'></input></td>\n";
										$ret .= "		<td>Total RGA Costs:</td>\n";
										$ret .= "		<td><input id='txt_totCost_".$seqInvest."' name='txt_totCost_".$seqInvest."' disabled type='text'  value='".$row2['COST_TOT']."' $READONLY_FIN></td>\n";
										$ret .= "	</tr>\n";
										$ret .= "	<tr class='dbc'>\n";
										$ret .= "		<td>ISO Status:</td>\n";
										if (empty($row2['isoRowID']) ) {
											$ret .= "		<td><select id='select_iso_status_".$seqInvest."' name='select_iso_status_".$seqInvest."' $READONLY_FIN>\n";
											$ret .= "           <option value='' SELECTED>-- Select --</option>\n";
											$ret .= "           <option value='Drafted'>Drafted</option>\n";
											$ret .= "           <option value='Pending Extenuating Issues'>Pending Extenuating Issues</option>\n";
											$ret .= "           <option value='Waiting for Approval'>Waiting for Approval</option>\n";
											$ret .= "           <option value='Waiting for Production'>Waiting for Production</option>\n";
											$ret .= "           <option value='Waiting for Customer Service'>Waiting for Customer Service</option>\n";
											$ret .= "           <option value='Waiting for Inventory Audit'>Waiting for Inventory Audit</option>\n";
											$ret .= "           <option value='Waiting for Manufacturer Response'>Waiting for Manufacturer Response</option>\n";
											$ret .= "           <option value='Waiting for Pricing Info'>Waiting for Pricing Info</option>\n";
											$ret .= "           <option value='Waiting for Rework/Replacement Number'>Waiting for Rework/Replacement Number</option>\n";
											$ret .= "           <option value='Working with Production Development'>Working with Production Development</option>\n";
											$ret .= "           <option value='Working with Purchasing'>Working with Purchasing</option>\n";
											$ret .= "           <option value='Closed'>Closed</option>\n";
											$ret .= "		</select></td>\n";
										} else {
											$ret .= "		<td><select id='select_iso_status_".$seqInvest."' name='select_iso_status_".$seqInvest."' $READONLY_FIN>\n";
											$ret .= "			<option value=''>--Select--</option>\n";
											$isoStatus = array("Drafted", 
												"Pending Extenuating Issues", 
												"Waiting for Approval", 
												"Waiting for Production", 
												"Waiting for Customer Service", 
												"Waiting for Inventory Audit", 
												"Waiting for Manufacturer Response", 
												"Waiting for Pricing Info", 
												"Waiting for Rework/Replacement Number", 
												"Working with Production Development", 
												"Working with Purchasing", 
												"Closed");
											foreach ($isoStatus as $SELECT_ISO_STATUS) {
												$SELECTED = '';
												$CURRENT = '';

												if (trim($row2['RGA_ISO_STATUS']) == trim($SELECT_ISO_STATUS)) {
													$SELECTED = 'SELECTED';
													$CURRENT = '*';
												}
												$ret .= "				<option value='" . $SELECT_ISO_STATUS . "' " . $SELECTED . ">" . $CURRENT . $SELECT_ISO_STATUS .  "</option>\n";
											}
											$ret .= "		</select></td>\n";
										}

										if ($seqInvest==1){
											$ret .= "		<td>RGA Status:</td>\n";
											$ret .= "		<td><select id='select_rga_status_".$seqInvest."' name='select_rga_status_".$seqInvest."' onchange=\"checkChangeRgaIsoStatus($seqInvest)\" $READONLY_FIN>\n";

											$RGA_STATUS_ARRAY = array(
												array("","--Select--"),
												array("Open, Waiting for Return","Open, Waiting for Return"),
												array("Open, Response to Customer Required","Open, Response to Customer Required"),
												array("Open, Inspection Required","Open, Inspection Required"),
												array("ISO Action Required","ISO Action Required"),
												array("Cancelled","Cancelled"),
												array("Closed","Closed"),												
												//array("","--Select--"),
												//array("Open","Open"),
												//array("Pending","Pending Investigation"),
												//array("Cancelled","Cancelled"),
												//array("Closed","Closed"),
											);
											for ($rowStat = 0; $rowStat < 5; $rowStat++) {
												$SELECTED = '';
												$CURRENT = '';

												if (trim($row['RGA_STATUS']) == trim($RGA_STATUS_ARRAY[$rowStat][0])) {
													$SELECTED = 'SELECTED';
													$CURRENT = '*';
												}
												$ret .= "				<option value='" . $RGA_STATUS_ARRAY[$rowStat][0] . "' " . $SELECTED . ">" . $CURRENT . $RGA_STATUS_ARRAY[$rowStat][1] .  "</option>\n";
											}

											$ret .= "		</select></td>\n";
										} else {
											$ret .= "		<td colspan=2></td>\n";
										}	
										$ret .= "	</tr>\n";
										//////////// WORK NEEDED: NEED TO CHANGE LOGIC TO SHOW IF NO RECORD
										if (empty($row2['isoRowID']) ) {
											$ret .= "	<tr class='dbc'>\n";
											//$ret .= "		<td colspan=4><input id='button_SubmitNew2_".$seqInvest."' name='button_SubmitNew2_".$seqInvest."' type='button' value='Submit' onClick=\"insertNewInvest()\" ></input><div id='div_submitResp2' name='div_submitResp2'></div></td>\n";
											$ret .= "		<td colspan=4>\n";
											$ret .= " 			<input id='button_insertNewInvest_".$seqInvest."' name='button_insertNewInvest_".$seqInvest."' type='button' value='Save' onclick=\"insertNewInvest('".$seqInvest."')\" DISABLED></input>\n";
											$ret .= " 			**Send Email?<select id='sel_Email_insertNewInvest_".$seqInvest."' name='sel_Email_insertNewInvest_".$seqInvest."' onchange=\"checkEnableButton('sel_Email_insertNewInvest_".$seqInvest."','button_insertNewInvest_".$seqInvest."')\">\n";
											$ret .= "   	        <option value=''>--Select--</option>\n";
											$ret .= "   	        <option value='Send'>Send</option>\n";
											$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
											$ret .= "			</select>\n";
											$ret .= "			<div id='div_submitResp_insertNewInvest_".$seqInvest."' name='div_submitResp_insertNewInvest_".$seqInvest."'></div>\n";
											$ret .= "		</td>\n";										
											$ret .= "	</tr>\n";
										} else {
											if(strpos($UserRow['EMP_ROLE'], ":RGA-FINANCE:") !== FALSE) { // CHANGE BUTTON ACTION FOR FINANCE, ONLY ALLOWING THEM TO UPDATE THE CREDIT INVOICE NUMBER
												$ret .= "	<tr class='dbc'>\n";
												//$ret .= "		<td colspan=4><input id='button_SubmitNew2_".$seqInvest."' name='button_SubmitNew2_".$seqInvest."' type='button' value='Save' onClick=\"updateRgaInvestFinOnly()\"></input><div id='div_submitResp2' name='div_submitResp2'></div></td>\n";
												$ret .= "		<td colspan=4>\n";
												$ret .= " 			<input id='button_updateRgaInvestFinOnly_".$seqInvest."' name='button_updateRgaInvestFinOnly_".$seqInvest."' type='button' value='Save' onclick=\"updateRgaInvestFinOnly('".$seqInvest."')\" ></input>\n";
												//$ret .= " 			**Send Email?<select id='sel_Email_updateRgaInvestFinOnly_".$seqInvest."' name='sel_Email_updateRgaInvestFinOnly_".$seqInvest."' onchange=\"checkEnableButton('sel_Email_updateRgaInvestFinOnly','button_updateRgaInvestFinOnly')\">\n";
												//$ret .= "   	        <option value=''>--Select--</option>\n";
												//$ret .= "   	        <option value='Send'>Send</option>\n";
												//$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
												//$ret .= "			</select>\n";
												$ret .= "			<div id='div_submitResp_updateRgaInvestFinOnly_".$seqInvest."' name='div_submitResp_updateRgaInvestFinOnly_".$seqInvest."'></div>\n";
												$ret .= "		</td>\n";													
												$ret .= "	</tr>\n";
											} else {
												$ret .= "	<tr class='dbc'>\n";
												//$ret .= "		<td colspan=4><input id='button_SubmitNew2_".$seqInvest."' name='button_SubmitNew2_".$seqInvest."' type='button' value='Save' onClick=\"updateRgaInvest()\"></input><div id='div_submitResp2_".$seqInvest."' name='div_submitResp2_".$seqInvest."'></div></td>\n";
												$ret .= "		<td colspan=4>\n";
												$ret .= " 			<input id='button_updateRgaInvest_".$seqInvest."' name='button_updateRgaInvest_".$seqInvest."' type='button' value='Save' onclick=\"updateRgaInvest('".$seqInvest."')\" DISABLED></input>\n";
												$ret .= " 			**Send Email?<select id='sel_Email_updateRgaInvest_".$seqInvest."' name='sel_Email_updateRgaInvest_".$seqInvest."' onchange=\"checkEnableButton('sel_Email_updateRgaInvest_".$seqInvest."','button_updateRgaInvest_".$seqInvest."')\">\n";
												$ret .= "   	        <option value=''>--Select--</option>\n";
												$ret .= "   	        <option value='Send'>Send</option>\n";
												$ret .= " 	    	    <option value='NoSend'>Do NOT Send</option>\n";
												$ret .= "			</select>\n";
												$ret .= "			<div id='div_submitResp_updateRgaInvest_".$seqInvest."' name='div_submitResp_updateRgaInvest_".$seqInvest."'></div>\n";
												$ret .= "		</td>\n";											
												$ret .= "	</tr>\n";
											}
										}

										$ret .= "	<tr class='dbdl' rowspan=4 id='tr_plus_invest".$y."' style='".$style."'>\n";
										$ret .= "		<td colspan=4>\n";
										$ret .= "			<font style='cursor: hand' title='Add Blank Record' onclick=\"showInvestTable('$z')\">+Blank   </font>\n";
										$ret .= "		</td>\n";
										$ret .= "	</tr>\n";
										$ret .= "</table>\n";
									}
								}	
							}
						}
					break;	

					case "updateISO":
						if (isset($_POST["rgaNumber"]) && isset($_POST["level"]) && isset($_POST["findings"]) && isset($_POST["cause"]) && isset($_POST["contain"]) && isset($_POST["corr"]) && isset($_POST["actionNeed"]) &&
							isset($_POST["desc"]) && isset($_POST["flag_car"]) && isset($_POST["carNumber"]) && isset($_POST["approve"]) && isset($_POST["date"]) && isset($_POST["dept"]) &&  isset($_POST["errReq"]) &&
							isset($_POST["team"])&& isset($_POST["invoice"]) && isset($_POST["errorType"]) && isset($_POST["compCost"]) && isset($_POST["vendor"]) && isset($_POST["laborCost"]) && isset($_POST["sendEmail"]) &&
							isset($_POST["itemNumber"]) && isset($_POST["shipCost"]) && isset($_POST["isoRowID"]) && isset($_POST["isoStatus"]) && isset($_POST["rgaStatus"]) && isset($_POST["BaseRowID"]) && isset($_POST["workcenter"]) ) {
							
							//$sql = "SET ANSI_NULLS ON";
							//QueryDatabase($sql, $results);
							//$sql = "SET ANSI_WARNINGS ON";
							//QueryDatabase($sql, $results);							

							$rgaNumber = $_POST["rgaNumber"];
							$sendEmail = $_POST["sendEmail"];
							$level = $_POST["level"];
							$findings = $_POST["findings"];
							$cause = $_POST["cause"];
							$contain = $_POST["contain"];
							$corr = $_POST["corr"];
							$actionNeed = $_POST["actionNeed"];
							$desc = $_POST["desc"];
							$flag_car = $_POST["flag_car"];
							$carNumber = $_POST["carNumber"];
							$approve = $_POST["approve"];
							$date = $_POST["date"];
							$dept = $_POST["dept"];
							$errReq = $_POST["errReq"];
							$team = $_POST["team"];
							$invoice = $_POST["invoice"];
							$errorType = $_POST["errorType"];
							$compCost = $_POST["compCost"];
							$vendor = $_POST["vendor"];
							$laborCost = $_POST["laborCost"];
							$itemNumber = $_POST["itemNumber"];
							$shipCost = $_POST["shipCost"];
							$isoRowID = $_POST["isoRowID"];
							$isoStatus = $_POST["isoStatus"];
							$rgaStatus = $_POST["rgaStatus"];
							$BaseRowID =$_POST["BaseRowID"];
							$workcenter = $_POST["workcenter"];
							$totCost = $laborCost + $compCost + $shipCost;

							//if($dept == 'Non-Production') {
							//	$workcenter = 'NON-PRODUCTION';
							//}

							if ($rgaStatus == 'Closed') {
								$isoStatus = 'Closed';
							}

							$sql  = " update nsa.RGA_INVEST" . $DB_TEST_FLAG . " ";
							$sql .= " SET";
							$sql .= " RGA_RATING = '" . $level . "', ";
							$sql .= " FINDINGS = '" . ms_escape_string($findings) . "', ";
							$sql .= " CAUSE = '" . ms_escape_string($cause) . "', ";
							$sql .= " CONTAINMENT = '" . ms_escape_string($contain) . "', ";
							$sql .= " CORRECTION = '" . ms_escape_string($corr) . "', ";
							$sql .= " ACTION_NEEDED = '" . ms_escape_string($actionNeed) . "', ";
							$sql .= " ACTION_DESCR = '" . ms_escape_string($desc) . "', ";
							$sql .= " FLAG_CAR = '" . $flag_car . "', ";
							$sql .= " CAR_NUMBER = '" . ms_escape_string($carNumber) . "', ";
							$sql .= " APPROVED_BY = '" . ms_escape_string($approve) . "', ";
							$sql .= " DEPARTMENT = '" . ms_escape_string($dept) . "', ";
							$sql .= " REQ_ERR = '" . ms_escape_string($errReq) . "', ";
							$sql .= " ID_TEAM = '" . ms_escape_string($team) . "', ";
							$sql .= " ERR_TYPE = '" . ms_escape_string($errorType) . "', ";
							$sql .= " ID_VND = '" . ms_escape_string($vendor) . "', ";
							$sql .= " ID_ITEM_VND = '" . ms_escape_string($itemNumber) . "', ";

							if (stripIllegalChars2($date) == '') {
								$sql .= " DATE_APPROVED = NULL, ";
							} else {
								$sql .= " DATE_APPROVED = '" . ms_escape_string($date) . "', ";
							}

							if (stripIllegalChars2($invoice) == '') {
								$sql .= " ID_INVC_CRED = NULL, ";
							} else {
								$sql .= " ID_INVC_CRED = '" . ms_escape_string($invoice) . "', ";
							}

							if (stripIllegalChars2($compCost) == '') {
								$sql .= " COST_COMP = NULL, ";
							} else {
								$sql .= " COST_COMP = " . stripIllegalChars2($compCost) . ", ";
							}

							if (stripIllegalChars2($laborCost) == '') {
								$sql .= " COST_LAB = NULL, ";
							} else {
								$sql .= " COST_LAB = " . stripIllegalChars2($laborCost) . ", ";
							}

							if (stripIllegalChars2($shipCost) == '') {
								$sql .= " COST_SHIP = NULL, ";
							} else {
								$sql .= " COST_SHIP = " . stripIllegalChars2($shipCost) . ", ";
							}
							
							if (stripIllegalChars2($totCost) == '') {
								$sql .= " COST_TOT = NULL, ";
							} else {
								$sql .= " COST_TOT = " . stripIllegalChars2($totCost) . ", ";
							}

							$sql .= " RGA_ISO_STATUS = '" . ms_escape_string($isoStatus) . "', ";
							$sql .= " WORKCENTER = '" . ms_escape_string($workcenter) . "', ";
							$sql .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
							$sql .= " DATE_CHG = GetDate() ";
							$sql .= " WHERE rowid = " .$isoRowID;
							QueryDatabase($sql, $results);

							$sql = " update nsa.RGA_BASE" . $DB_TEST_FLAG . " ";
							$sql .= " SET";
							$sql .= " RGA_STATUS = '" . ms_escape_string($rgaStatus) . "', ";
							$sql .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
							$sql .= " DATE_CHG = GetDate() ";
							$sql .= " WHERE rowid = " .$BaseRowID;
							QueryDatabase($sql, $results);

							$sql  = " SELECT rb.NAME_CUST, ";
							$sql .= " rb.SALES_MGR, ";
							$sql .= " sr.ADDR_EMAIL as EMAIL_SLSREP, ";
							$sql .= " wa.EMAIL as EMAIL_CSR, ";
							$sql .= " wa.NAME_EMP ";
							$sql .= " FROM nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
							$sql .= " left join nsa.tables_slsrep sr ";
							$sql .= " on rb.SALES_MGR = sr.NAME_SLSREP ";
							$sql .= " and sr.ADDR_EMAIL is not null ";
							$sql .= " left join nsa.DCWEB_AUTH wa";
							$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
							//$sql .= " WHERE rowid = '". $BaseRowID ."'";
							$sql .= " WHERE rb.RGA_NUMBER = '". $rgaNumber ."'";
							QueryDatabase($sql, $results);

							while ($row = mssql_fetch_assoc($results)) {
								if (trim($sendEmail) == 'Send') {
									if ($rgaStatus == 'Closed') {
										$files = array();
										$a_files = glob("../RGA_Attachments/Upload/".$rgaNumber."___*.{jpg,png,gif,bmp,tif,pdf}", GLOB_BRACE);
										foreach ($a_files as $filename){
											$short_filename = substr($filename, strrpos($filename, '/') + 1);
											$tempFilename = "/tmp/RGA_temp/" . $short_filename;
											shell_exec("cp " . $filename . " " . $tempFilename);
											array_push($files, $tempFilename);
										}

										if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
											$head = array(
												'to'      =>array('TESTGroup-ClosedRGA@thinknsa.com'=>'Group-ClosedRGA'),
												'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
												'cc'      =>array('gvandyne@thinknsa.com'=>$row['SALES_MGR'], $row['EMAIL_CSR']=>$row['NAME_EMP']),
												//'bcc'     =>array('email4@email.net'=>'Admin'),
											);
										} else {
											$head = array(
												//'to'      =>array('TESTGroup-ClosedRGA@thinknsa.com'=>'Group-ClosedRGA'),
												'to'      =>array('Group-ClosedRGA@thinknsa.com'=>'Group-ClosedRGA'),
												'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
												'cc'      =>array($row['EMAIL_SLSREP']=>$row['SALES_MGR'], $row['EMAIL_CSR']=>$row['NAME_EMP']),
												//'bcc'     =>array('email4@email.net'=>'Admin'),
											);
										}

										$subject = "CLOSED RGA " . $rgaNumber . " - " . $row['NAME_CUST'];
										$body = GenerateHTMLforEmail($rgaNumber);
										//$files = array($file1,$file2);
										if (!empty($files)) {
											mail::send($head,$subject,$body,$files);
										} else {
											mail::send($head,$subject,$body);
										}
									}
								}
							}
							$ret .= "SAVED </br>" . date('Y-m-d H:i:s');
						} else {
							$ret .= "<font>Missing Fields!</font>\n";
						}
					break;

					case "updateISO_FIN_ONLY":
						if (isset($_POST["rgaNumber"]) && isset($_POST["isoRowID"]) && isset($_POST["invoice"]) && isset($_POST["rgaStatus"])) {
							
							//$sql = "SET ANSI_NULLS ON";
							//QueryDatabase($sql, $results);
							//$sql = "SET ANSI_WARNINGS ON";
							//QueryDatabase($sql, $results);							

							$rgaNumber = $_POST["rgaNumber"];
							$isoRowID = $_POST["isoRowID"];
							$invoice = $_POST["invoice"];
							$rgaStatus = $_POST["rgaStatus"];
							
							$sql  = " update nsa.RGA_INVEST" . $DB_TEST_FLAG . " ";
							$sql .= " SET";
							if (stripIllegalChars2($invoice) == '') {
								$sql .= " ID_INVC_CRED = NULL, ";
							} else {
								$sql .= " ID_INVC_CRED = '" . ms_escape_string($invoice) . "', ";
							}
							$sql .= " ID_USER_CHG = '" . $UserRow['ID_USER'] . "', ";
							$sql .= " DATE_CHG = GetDate() ";
							$sql .= " WHERE rowid = " .$isoRowID;
							QueryDatabase($sql, $results);

							$ret .= "SAVED </br>" . date('Y-m-d H:i:s');
						}
					break;


				}
			}
			$ret .= "<br>";
			if (isset($_POST["divclose"])) {
				$ret .= "		<button onClick=\"disablePopup(". $Div .")\">CLOSE</button>\n";//close popup button
			}
			echo json_encode(array("returnValue"=> $ret));
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			print("					<p class='warning'>Could Not Disconnect From $DBServer!</p>\n");
		}
	}


function GenerateHTMLforEmail($rgaNumber) {
	global $DEBUG, $DB_TEST_FLAG;

	$ret = "";
	$sql = "SET ANSI_NULLS ON";
	QueryDatabase($sql, $results);
	$sql = "SET ANSI_WARNINGS ON";
	QueryDatabase($sql, $results);
	
	$sql =  "SELECT ";
	$sql .= " wa.NAME_EMP, ";
	//$sql .= " ri.rowid as isoRowID, ";
	$sql .= " rb.rowid as BaseRowID, ";
	$sql .= " rb.ID_USER_ADD as ID_USER_ADD_BASE, ";
	//$sql .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED, ";
	$sql .= " rb.* ";
	//$sql .= " ,ri.* ";
	$sql .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
	//$sql .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
	//$sql .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
	$sql .= " left join nsa.DCWEB_AUTH wa ";
	$sql .= " on rb.ID_USER_ADD = wa.ID_USER ";
	$sql .= " where ";
	$sql .= " rb.RGA_NUMBER = '" . $rgaNumber . "' ";
	QueryDatabase($sql, $results);							

	while ($row = mssql_fetch_assoc($results)) {
		$ret .= "<table width='850' >\n";
		$ret .= "	<tr style='background-color: #0060A1; color: #DDE5ED;'>\n";
		$ret .= "		<th colspan='4'><left><img src=''></left> <right>RGA & Customer Complaint</right></th>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>RGA Number:</td>\n";
		$ret .= "		<td><input id='txt_rgaNumber' name='txt_rgaNumber' type='text' READONLY  value=" . $rgaNumber . "></input></td>\n";
		$ret .= "		<td>Date Issued:</td>\n";
		$ret .= "		<td><input id='txt_date' name='txt_date' type='text' READONLY value='" . $row['DATE_ISSUE'] . "'></input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Customer #:</td>\n";
		$ret .= "		<td><input id='txt_customerID' name='txt_customerID' type='text' READONLY value='" . $row['ID_CUST'] . "' ></input></td>\n";
		$ret .= "		<td>Customer Name:</td>\n";
		$ret .= "		<td><input id='txt_NAME_CUST' name='txt_NAME_CUST' type='text' READONLY value='".$row['NAME_CUST']."' size=30></input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>City:</td>\n";
		$ret .= "		<td><input id='txt_CITY' name='txt_CITY' type='text' READONLY value='".$row['CITY']."'></input></td>\n";
		$ret .= "		<td>State:</td>\n";
		$ret .= "		<td><input id='txt_ID_ST' name='txt_ID_ST' type='text' READONLY value='" . $row['ID_ST'] . "'></input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Prov:</td>\n";
		$ret .= "		<td><input id='txt_PROV' name='txt_PROV' type='text' READONLY value='" . $row['PROV'] . "'></input></td>\n";
		$ret .= "		<td>Country:</td>\n";
		$ret .= "		<td><input id='txt_COUNTRY' name='txt_COUNTRY' type='text' READONLY value='" . $row['COUNTRY'] . "'></input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Contact Name:</td>\n";
		$ret .= "		<td><input id='txt_NAME_CONTACT_CUST' name='txt_NAME_CONTACT_CUST' type='textbox' READONLY value='".$row['CONTACT_NAME']."'></input></td>\n";
		$ret .= "		<td>Phone #:</td>\n";
		$ret .= "		<td><input id='txt_PHONE' name='txt_PHONE' type='text' READONLY value='" . $row['PHONE_NUMBER'] . "'></input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Email:</td>\n";
		$ret .= "		<td><input id='txt_email' name='txt_email' type='text' READONLY value='" . $row['EMAIL'] . "' ></input></td>\n";
		$ret .= "		<td>Classification:</td>\n";
		$ret .= "		<td><input id='txt_CLASSIFICATION' name='txt_CLASSIFICATION' type='text' READONLY value='" . $row['CLASSIFICATION'] . "' ></input></td>\n";

		
		$sql2  = "select ";
		$sql2 .= " convert(varchar(10),rl.DATE_SHIPPED,126) as rlDate_SHIPPED, ";
		$sql2 .= " rl.* ";
		$sql2 .= " from nsa.RGA_LINE" . $DB_TEST_FLAG . " rl";
		$sql2 .= " where rl.RGA_NUMBER = '" . $rgaNumber . "' ";
		$sql2 .= " ORDER BY rl.SEQ_LINE_RGA asc ";
		QueryDatabase($sql2, $results2);
		//$LineCount = mssql_num_rows($results2);
		while ($row2 = mssql_fetch_assoc($results2)) {
			$style = 'table-row';
			$y = $row2['SEQ_LINE_RGA'];
			$z = $y+1;
			$ret .= "	<tr class='dbdl' rowspan=4  id='tr_ord".$y.".1' style='".$style."'>\n";
			$ret .= "		<td colspan=4>Order Record ".$y."</td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_ord".$y.".2' style='".$style."'>\n";
			$ret .= "		<td>Order #:</td>\n";
			$ret .= "		<td><input id='txt_orderNumber".$y."' name='txt_orderNumber".$y."' type='text' READONLY value='".$row2['ID_ORD']."' ></input></td>\n";
			$ret .= "		<td>PO #:</td>\n";
			$ret .= "		<td><input id='txt_poNumber".$y."' name='txt_poNumber".$y."' type='text' READONLY value='".$row2['ID_PO']."' ></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_ord".$y.".3' style='".$style."'>\n";
			$ret .= "		<td>Part #:</td>\n";
			$ret .= "		<td><input id='txt_itemNumber".$y."' name='txt_itemNumber".$y."' type='text' READONLY value='".$row2['ID_ITEM']."' ></input></td>\n";
			$ret .= "		<td>Quantity:</td>\n";
			$ret .= "		<td><input id='txt_quant".$y."' name='txt_quant".$y."' type='text' READONLY value='".$row2['QUANTITY']."' ></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_ord".$y.".4' style='".$style."'>\n";
			$ret .= "		<td>Invoice #:</td>\n";
			$ret .= "		<td><input id='txt_invoiceNumber".$y."' name ='txt_invoiceNumber".$y."' type='text' READONLY value='".$row2['ID_INVC']."' ></input></td>\n";//added invoiceNumber to form
			$ret .= "		<td>Date Ship (yyyy-mm-dd):</td>\n";
			$ret .= "		<td><input id='txt_dateShipped".$y."' name='txt_dateShipped".$y."' type='text' READONLY value='".$row2['rlDate_SHIPPED']."' ></input></td>\n";
			$ret .= "	</tr>\n";
		}

		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Issued by:</td>\n";
		$ret .= "		<td>" . $row['ID_USER_ADD_BASE'] . "</td>\n";
		$ret .= "		<td>Authorized by:</td>\n";
		$ret .= "		<td><input id='txt_auth' name='txt_auth' type='text' READONLY value='" . $row['AUTHORIZED_BY'] . "'></input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Sales Manager:</td>\n";
		$ret .= "		<td><input id='txt_salesMgr' name='txt_salesMgr' type='text' READONLY value='" . $row['SALES_MGR'] . "'></input></td>\n";
		$ret .= "		<td>Territory:</td>\n";
		$ret .= "		<td><input id='txt_territory' name='txt_territory' type='text' READONLY value='" . $row['ID_TERR'] . "' maxlength='3'></input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Description of <br/>Request/Complaint:</td>\n";
		$ret .= "		<td colspan='3'><textarea id='txt_descr' name='txt_descr' cols='25' rows='7' READONLY >".$row['DESCR']."</textarea></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "</table>\n";
		$ret .= "<table width='850'>\n";
		$ret .= "   <tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "   	<td colspan='6'>RGA Class</td>\n";
		$ret .= "   </tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Complaint with Return?</td>\n";

		$rgaSelectA = '';
		$rgaSelectB = '';
		$rgaSelectC = '';
		if ($row['RGA_CLASS'] == 'A'){
			$rgaSelectA = "checked = 'checked'";
		}
		if ($row['RGA_CLASS'] == 'B'){
			$rgaSelectB = "checked = 'checked'";
		}
		if ($row['RGA_CLASS'] == 'C'){
			$rgaSelectC = "checked = 'checked'";
		}
		$ret .= "		<td><input type='radio' id='A' name='choice' ".$rgaSelectA." value='A' READONLY >A</input></td>\n";
		$ret .= "		<td>Complaint without Return?</td>\n";
		$ret .= "		<td><input type='radio' id='B' name='choice' ".$rgaSelectB." value='B' READONLY >B</input></td>\n";
		$ret .= "		<td>Customer Request?</td>\n";
		$ret .= "		<td><input type='radio' id='C' name='choice' ".$rgaSelectC." value='C' READONLY >C</input></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "</table>\n";
		$ret .= "<table width='850'>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Follow-Up that<br /> Requires Action:</td>\n";
		$ret .= "		<td colspan=3><textarea id='txt_followUp' name='txt_followUp' cols='55' READONLY>".$row['FOLLOW_UP_DESCR']."</textarea></td>\n";
		$ret .= "	</tr>\n";
		//$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		//$ret .= "		<td>Email to<br /> Notify:</td>\n";
		//$ret .= "		<td><input id='txt_email_notify' name='txt_email_notify' type='text' size='65' READONLY value='" . $row['EMAIL_LIST'] . "'></input></td>\n";
		//$ret .= "	</tr>\n";
		$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
		$ret .= "		<td>Additional<br /> Information:</td>\n";
		$ret .= "		<td colspan=3><textarea id='txt_add_info' name='txt_add_info' cols='55' READONLY >".$row['ADD_INFO']."</textarea></td>\n";
		$ret .= "	</tr>\n";
		$ret .= "</table>\n";

		$ret .= "<table width='850' id='table_shipping' style='display:table;'>\n";
		$ret .= "	<tr>\n";
		$ret .= "		<th colspan='4'>For Shipping Only</th>\n";
		$ret .= "	</tr>\n";

		$sql2  = "select ";
		$sql2 .= " convert(varchar(10),DATE_RECEIVED,121) as rsDATE_RECEIVED,";
		$sql2 .= " * ";
		$sql2 .= "from nsa.RGA_SHIP" . $DB_TEST_FLAG . " ";
		$sql2 .= "where RGA_NUMBER = '" . $rgaNumber . "' ";
		$sql2 .= "ORDER BY SEQ_LINE_SHIP asc ";
		QueryDatabase($sql2, $results2);
		//$LineCount = mssql_num_rows($results2);
		while($row2 = mssql_fetch_assoc($results2)){
			$style= 'table-row';
			$y = $row2['SEQ_LINE_SHIP'];
			$z = $y + 1;
			$ret .= "	<tr class='dbdl' rowspan=4  id='tr_itm".$y.".1' style='".$style."'>\n";
			$ret .= "		<td colspan=4>Receiving Record ".$y."</td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_itm".$y.".2' style='".$style."'>\n";
			$ret .= "       <td colspan=2></td>\n";
			$ret .= "		<td>Item Recieved:</td>\n";
			$ret .= "		<td><input id='txt_itemReceived".$y."' name='txt_itemReceived".$y."' width='500px' type='text' READONLY value='".$row2['ITEM_RECEIVED']."' ></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_itm".$y.".3' style='".$style."'>\n";
			$ret .= "		<td>Date Received (yyyy-mm-dd):</td>\n";
			$ret .= "		<td><input id='txt_dateReceived".$y."' name='txt_dateReceived".$y."' type='text' READONLY value='".$row2['rsDATE_RECEIVED']."'></input></td>\n";
			$ret .= "		<td>Quantity Received:</td>\n";
			$ret .= "		<td><input id='txt_quantity".$y."' name='txt_quantity".$y."' type='text' READONLY value='".$row2['QUANTITY_RECEIVED']."'></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_itm".$y.".4' style='".$style."'>\n";
			$ret .= "		<td>Carrier:</td>\n";
			$ret .= "		<td><input id='txt_carrier".$y."' name='txt_carrier".$y."' type='text' READONLY value='".$row2['CARRIER']."'></input></td>\n";
			$ret .= "		<td>Tracking #:</td>\n";
			$ret .= "		<td><input id='txt_trackingNumber".$y."' name='txt_trackingNumber".$y."' type='text' READONLY value='".$row2['TRACKING_NO']."'></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_itm".$y.".5' style='".$style."'>\n";
			$ret .= "		<td>Condition Received:</td>\n";
			$ret .= "		<td><input id='txt_condition".$y."' name='txt_condition".$y."' type='text' READONLY value='".$row2['COND_RECEIVED']."'></input></td>\n";
			$ret .= "		<td>Received by:</td>\n";
			$ret .= "		<td><input id='txt_receivedBy".$y."' name='txt_receivedBy".$y."' type='text' READONLY value='".$row2['RECEIVED_BY']."'></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .=" 	<tr style='background-color: #DDE5ED; color: #black;' id='tr_itm".$y.".6' style='".$style."'>\n";
			$ret .= "		<td>Comments:</td>\n";
			$ret .= "		<td colspan=4><textarea id='txt_ship_comments".$y."' name='txt_ship_comments".$y."' cols='55' READONLY >".$row2['COMMENTS']."</textarea></td>\n";
			$ret .= "	</tr>\n";
		}
		$ret .= "</table>\n";






		//////////////
		// INVEST RECORDS
		//////////////
		$sql2 =  "SELECT ";
		$sql2 .= " ri.rowid as isoRowID,";
		$sql2 .= " convert(varchar(10),ri.DATE_APPROVED,126) as riDATE_APPROVED,";
		$sql2 .= " ri.*, ";
		$sql2 .= " rb.rowid as baseRowID ";
		$sql2 .= " from nsa.RGA_BASE" . $DB_TEST_FLAG . " rb ";
		$sql2 .= " left join nsa.RGA_INVEST" . $DB_TEST_FLAG . " ri ";
		$sql2 .= " on rb.RGA_NUMBER = ri.RGA_NUMBER ";
		$sql2 .= " where rb.RGA_NUMBER = '" . $rgaNumber . "' ";
		$sql2 .= " order by ri.SEQ_INVEST asc ";
		QueryDatabase($sql2, $results2);
		$y=0;
		//while(($row2 = mssql_fetch_assoc($results2)) || ($y<10)){
		while($row2 = mssql_fetch_assoc($results2)){
			$y++;
			$z = $y + 1;
			$style = 'table-row';
			$seqInvest = $row2['SEQ_INVEST'];

			if (empty($seqInvest)) {
				$seqInvest = $y;
				$style = 'display:none;';
			} 

			if ($y==1) {
				$style = 'table-row';
			}





			$rgaRatingSelect1 = '';
			$rgaRatingSelect2 = '';
			$rgaRatingSelect3 = '';
			if($row2['RGA_RATING'] == '1'){
				$rgaRatingSelect1 = "checked = 'checked'";
			}
			if($row2['RGA_RATING'] == '2'){
				$rgaRatingSelect2 = "checked = 'checked'";
			}
			if($row2['RGA_RATING'] == '3'){
				$rgaRatingSelect3 = "checked = 'checked'";
			}

			//selects action needed checkboxes
			$rgaActionNeedRework = '';
			$rgaActionNeedReplace = '';
			$rgaActionNeedNonStock = '';
			$rgaActionNeedCredit = '';
			$rgaActionNeedOther = '';
			if (strpos($row2['ACTION_NEEDED'], ':Rework:') !== false) {
				$rgaActionNeedRework = 'CHECKED';
			}

			if (strpos($row2['ACTION_NEEDED'], ':Replace:') !== false) {
				$rgaActionNeedReplace = 'CHECKED';
			}

			if (strpos($row2['ACTION_NEEDED'], ':NonStk-Sample:') !== false) {
				$rgaActionNeedNonStock = 'CHECKED';
			}
			
			if (strpos($row2['ACTION_NEEDED'], ':Credit:') !== false) {
				$rgaActionNeedCredit = 'CHECKED';
			}

			if (strpos($row2['ACTION_NEEDED'], ':Other:') !== false) {
				$rgaActionNeedOther = 'CHECKED';
			}

			//selects radio button for car
			$carFlagN = '';
			$carFlagY = '';
			if($row2['FLAG_CAR'] == 'N'){
				$carFlagN = "checked = 'checked'";
			}
			if($row2['FLAG_CAR'] == 'Y'){
				$carFlagY = "checked = 'checked'";
			}

			//$ret .= "<table width='850' id='table_iso_investigation' style='display:table;'>\n";
			$ret .= "<table width='850' id='table_iso_investigation' style='" . $style . ";'>\n";
			$ret .= "	<tr>\n";
			$ret .= "		<th colspan='4'>ISO Investigation " . $seqInvest . "</th>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;' id='tr_RGA_RATING' style='diplay:table-row;'>\n";
			$ret .= "		<td>RGA Rating (choose one):</td>\n";
			$ret .= "		<td><input type='radio' ".$rgaRatingSelect1." id='1' name='level' value='1' READONLY>Level 1</input></td>\n";
			$ret .= "		<td><input type='radio' ".$rgaRatingSelect2." id='2' name='level' value='2' READONLY>Level 2</input></td>\n";
			$ret .= "		<td><input type='radio' ".$rgaRatingSelect3." id='3' name='level' value='3' READONLY>Level 3</input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Findings:</td>\n";
			$ret .= "		<td colspan=3><textarea id='txt_findings' name='txt_findings' cols='55' READONLY>".$row2['FINDINGS']."</textarea></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Cause:</td>\n";
			$ret .= "		<td colspan=3><textarea id='txt_cause' name='txt_cause' cols='55' READONLY>".$row2['CAUSE']."</textarea></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Containment:</td>\n";
			$ret .= "		<td colspan=3><textarea id='txt_contain' name='txt_contain' cols='55' READONLY>".$row2['CONTAINMENT']."</textarea></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Correction:</td>\n";
			$ret .= "		<td colspan=3><textarea id='txt_corr' name='txt_corr' cols='55' READONLY>".$row2['CORRECTION']."</textarea></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "</table>\n";
			$ret .= "<table width='850' style='" . $style . ";'>\n";
			$ret .= "	<th colspan='7'></th>\n";
			$ret .= "	<tr class='dbc'>\n";
			$ret .= "		<td><strong>Action Needed:</strong></td>\n";
			$ret .= "		<td style='text-align:left;'>\n";
			$ret .= "			<input type='checkbox' id='action_Rework' name='action_Rework' $rgaActionNeedRework value=':Rework:' READONLY>Rework</input><br />\n";
			$ret .= "			<input type='checkbox' id='action_Replace' name='action_Replace' $rgaActionNeedReplace value=':Replace:' READONLY>Replace</input><br />\n";
			$ret .= "			<input type='checkbox' id='action_NonStk-Sample' name='action_NonStk-Sample' $rgaActionNeedNonStock value=':NonStk-Sample:' READONLY>NonStock/Sample</input><br />\n";
			$ret .= "			<input type='checkbox' id='action_Credit' name='action_Credit' $rgaActionNeedCredit value=':Credit:' READONLY>Credit</input><br />\n";
			$ret .= "			<input type='checkbox' id='action_Other' name='action_Other' $rgaActionNeedOther value=':Other:' READONLY>Other</input>\n";
			$ret .= "		</td>\n";
			$ret .= "		<td colspan=5><textarea id='txt_desc' name='txt_desc' READONLY>".$row2['ACTION_DESCR']."</textarea></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td colspan=2>Is a Corrective Action Request (CAR) required? (select one)</td>\n";
			$ret .= "		<td><input type='radio' ".$carFlagN." id='N' name='option' value='N' READONLY>No</input></td>\n";
			$ret .= "		<td><input type='radio' ".$carFlagY." id='Y' name='option' value='Y' READONLY>Yes</input></td>\n";
			$ret .= "		<td>CAR #</td>\n";
			$ret .= "		<td colspan=2><input id='txt_carNumber' name='txt_carNumber' type='text' READONLY value='".$row2['CAR_NUMBER']."'></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td colspan=2>Proposed action approved by:</td>\n";
			$ret .= "		<td colspan=2><input id='txt_approve' name='txt_approve' type='text' value='".$row2['APPROVED_BY']."' READONLY></input></td>\n";
			$ret .= "		<td>Date (yyyy-mm-dd):</td>\n";
			$ret .= "		<td colspan=2><input id='txt_dateSubmit' name='txt_dateSubmit' type='text' value='".$row2['riDATE_APPROVED']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "</table>\n";

			$ret .= "<table width='850' style='" . $style . ";'>\n";
			$ret .= " 	<th colspan='4'>For Tracking Only</th>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Department:</td>\n";
			$ret .= "		<td><input id='txt_department' name='txt_department' type='text' value='".$row2['DEPARTMENT']."' READONLY></input></td>\n";
			$ret .= "		<td>Request/Error:</td>\n";
			$ret .= "		<td><input id='txt_req_err' name='txt_req_err' type='text' value='".$row2['REQ_ERR']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Workcenter:</td>\n";
			$ret .= "		<td><input id='txt_workcenter' name='txt_workcenter' type='text' value='".$row2['WORKCENTER']."' READONLY></input></td>\n";
			$ret .= "		<td>Credit Invoice #:</td>\n";
			$ret .= "		<td><input id='txt_invoice' name='txt_invoice' type='text' value='".$row2['ID_INVC_CRED']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td colspan=2></td>\n";
			//$ret .= "		<td>Team/Individual:</td>\n";
			//$ret .= "		<td><input id='txt_team' name='txt_team' type='text' value='".$row2['ID_TEAM']."' READONLY></input></td>\n";
			$ret .= "		<td>Component Costs:</td>\n";
			$ret .= "		<td><input id='txt_compCost' name='txt_compCost' type='text' value='".$row2['COST_COMP']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Error Type:</td>\n";
			$ret .= "		<td><input id='txt_err_type' name='txt_err_type' type='text' value='".$row2['ERR_TYPE']."' READONLY></input></td>\n";
			$ret .= "		<td>Labor Costs:</td>\n";
			$ret .= "		<td><input id='txt_laborCost' name='txt_laborCost' type='text' value='".$row2['COST_LAB']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Vendor ID:</td>\n";
			$ret .= "		<td><input id='txt_vendor' name='txt_vendor' type='text' value='".$row2['ID_VND']."' READONLY></input></td>\n";
			$ret .= "		<td>Shipping Costs:</td>\n";
			$ret .= "		<td><input id='txt_shipCost' name='txt_shipCost' type='text' value='".$row2['COST_SHIP']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>Part #:</td>\n";
			$ret .= "		<td><input id='txt_isoPartNumber' name='txt_isoPartNumber' type='text' value='".$row2['ID_ITEM_VND']."' READONLY></input></td>\n";
			$ret .= "		<td>Total RGA Costs:</td>\n";
			$ret .= "		<td><input id='txt_totCost' name='txt_totCost' type='text' value='".$row2['COST_TOT']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "	<tr style='background-color: #DDE5ED; color: #black;'>\n";
			$ret .= "		<td>ISO Status:</td>\n";
			$ret .= "		<td><input id='txt_rga_status' name='txt_rga_status' type='text'  value='".$row2['RGA_ISO_STATUS']."' READONLY></input></td>\n";
			$ret .= "		<td>RGA Status:</td>\n";
			$ret .= "		<td><input id='txt_rga_status' name='txt_rga_status' type='text'  value='".$row['RGA_STATUS']."' READONLY></input></td>\n";
			$ret .= "	</tr>\n";
			$ret .= "</table>\n";

		}
		return $ret;
	}
}

?>