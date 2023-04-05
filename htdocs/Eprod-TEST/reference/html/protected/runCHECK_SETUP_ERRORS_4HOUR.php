<?php
	$DEBUG = 0;

	if (isset($_POST["debug"])) {
		$DEBUG = $_POST["debug"];
	}

	require_once("procfile.php");
	require_once('classes/mail.class.php');

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		error_log("runCHECK_SETUP_ERRORS_4HOUR cannot connect to database");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			error_log("runCHECK_SETUP_ERRORS_4HOUR cannot select " . $dbName);
		} else {
			error_log("#############################################");
			error_log("### runCHECK_SETUP_ERRORS_4HOUR started at " . date('Y-m-d g:i:s a'));
			error_log("### runCHECK_SETUP_ERRORS_4HOUR CHECKING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));

			$sql  = "SELECT ";
			$sql .= "	* ";
			$sql .= " FROM ";
			$sql .= " 	nsa.RUNNING_PROC ";
			$sql .= " WHERE ";
			$sql .= "	PROC_NAME = 'runCHECK_SETUP_ERRORS_4HOUR' ";
			$sql .= "	and ";
			$sql .= "	FLAG_RUNNING = '1' ";
			$sql .= "	and ";
			$sql .= "	DATE_EXP > getDate()";
			QueryDatabase($sql, $results);

			if (mssql_num_rows($results) == 0) {
				$sql  = "INSERT INTO nsa.RUNNING_PROC( ";
				$sql .= " PROC_NAME, ";
				$sql .= " FLAG_RUNNING, ";
				$sql .= " DATE_ADD, ";
				$sql .= " DATE_EXP ";
				$sql .= ") VALUES ( ";
				$sql .= "'runCHECK_SETUP_ERRORS_4HOUR', ";
				$sql .= "1, ";
				$sql .= " getDate(), ";
				$sql .= " dateadd(minute,5,getDate()) ";
				$sql .= ")  SELECT LAST_INSERT_ID=@@IDENTITY";
				QueryDatabase($sql, $results);
				$row = mssql_fetch_assoc($results);
				$ProcRowID = $row['LAST_INSERT_ID'];
				error_log("### runCHECK_SETUP_ERRORS_4HOUR SETTING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

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













				//////////////////////////////////////////////////////////////////////////////////////
				///////////////CHECK FOR ADVERTISED STOCK ITEMS WITHOUT UPS OR TARIFF CODE
				/////////////////////////////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS_4HOUR CHECKING FOR ADVERTISED STOCK ITEMS WITHOUT UPC OR TARIFF ATTRIBUTES ");

				$sql  = " SELECT ";
				$sql .= "  case when b.id_item like 'DWH%U%' then replace(b.id_item,'U','') else b.id_item end as id_item, ";
				$sql .= "  b.DESCR_1, ";
				$sql .= "  b.DESCR_2, ";
				$sql .= "  sl.[GROUP], ";
				$sql .= "  sl.SORT, ";
				$sql .= "  sl.ADV, ";
				$sql .= "  av.val_string_attr as UPC_CODE, ";
				$sql .= "  avt.val_string_attr as TARIFF_CODE ";
				$sql .= " FROM nsa.itmmas_stk_list sl ";
				$sql .= " LEFT JOIN nsa.ITMMAS_BASE b ";
				$sql .= "  on sl.id_item = b.ID_ITEM ";
				$sql .= " LEFT JOIN nsa.IM_CMCD_ATTR_VALUE av ";
				$sql .= "  on sl.id_item = av.id_item ";
				$sql .= "  and b.code_comm = av.code_comm ";
				$sql .= "  and av.id_attr = 'UPC_CODE' ";
				$sql .= " LEFT JOIN nsa.IM_CMCD_ATTR_VALUE avt ";
				$sql .= "  on sl.id_item = avt.id_item ";
				$sql .= "  and b.code_comm = avt.code_comm ";
				$sql .= "  and avt.id_attr = 'TARIFF_CODE' ";
				$sql .= " WHERE ";
				$sql .= "  (isnull(av.val_string_attr,'') = '' OR isnull(avt.val_string_attr,'') = '') ";
				$sql .= "  and sl.ADV = 'Y' ";
				$sql .= "  and (sl.[GROUP] like 'G%' or sl.[GROUP] like 'R%') ";
				$sql .= " Order by ";
				$sql .= "  sl.[GROUP], ";
				$sql .= "  sl.SORT, ";
				$sql .= "  sl.ID_ITEM ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$subject = "AN ADVERTISED STOCK ITEM IS MISSING UPC OR TARIFF ATTRIBUTES. ITEM: " . $row['id_item'];

					$body  = "<html>";
					$body .= "	<p>An Advertised Stock Item is missing either the UPC or Tariff Code attribute.</p>";
					$body .= "	<p>Item: " . $row['id_item'];
					$body .= "		<br>UPC Code: " . $row['UPC_CODE'];
					$body .= "		<br>Tariff Code: " . $row['TARIFF_CODE'];
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('jmartin@thinknsa.com'=>'Jeff Martin'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	//'bcc'     =>array('sabdelsayed@thinknsa.com'=>'Sabrina Abdelsayed'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}












				//////////////////////////////////////////////////////////////////////////////////////
				///////////////CHECK FOR DUPLICATE ITEMS ON VALUATION REPORTS
				///////////////More than one Primary Vendor OR duplicate items in ITMMAS_STK_LIST
				/////////////////////////////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS_4HOUR CHECKING FOR ITEMS WITH MORE THAN ONE PRIMARY VENDOR ");

				$sql  = " SELECT count(*) as recCount, ID_ITEM ";
				$sql .= " FROM nsa.ITMMAS_VND ";
				$sql .= " WHERE FLAG_VND_PRIM = 'P' ";
				$sql .= " GROUP BY ID_ITEM ";
				$sql .= " HAVING count(*) > 1 ";
				$sql .= " ORDER BY ID_ITEM ASC ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$subject = "AN ITEM WITH MORE THAN ONE PRIMARY VENDOR HAS BEEN IDENTIFIED. ITEM: " . $row['id_item'];

					$body  = "<html>";
					$body .= "	<p>An item with more than one Primary Vendor has been identified.</p>";
					$body .= "	<p>Item: " . $row['id_item'];
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('jmartin@thinknsa.com'=>'Jeff Martin'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	//'bcc'     =>array('sabdelsayed@thinknsa.com'=>'Sabrina Abdelsayed'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}



				error_log("### runCHECK_SETUP_ERRORS_4HOUR CHECKING FOR ITEMS WITH MORE THAN ONE RECORD IN ITMMAS_STK_LIST ");

				$sql  = " SELECT count(*) as recCount, ID_ITEM ";
				$sql .= " FROM nsa.ITMMAS_STK_LIST ";
				$sql .= " GROUP BY ID_ITEM ";
				$sql .= " HAVING count(*) > 1 ";
				$sql .= " ORDER BY ID_ITEM ASC ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$subject = "AN ITEM WITH MORE THAN ONE RECORD IN ITMMAS_STK_LIST HAS BEEN IDENTIFIED. ITEM: " . $row['id_item'];

					$body  = "<html>";
					$body .= "	<p>An item with more than one record in ITMMAS_STK_LIST has been identified.</p>";
					$body .= "	<p>Item: " . $row['id_item'];
					$body .= "	</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('jmartin@thinknsa.com'=>'Jeff Martin'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	//'bcc'     =>array('sabdelsayed@thinknsa.com'=>'Sabrina Abdelsayed'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}













				//////////////////////////////////////////////////////////////////////////////////////
				///////////////CHECK FOR DOCUMENT LINKS ON ROUTING RECORD INSTEAD OF ITEM
				/////////////////////////////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS_4HOUR CHECKING FOR DOCUMENT LINKS ON ROUTING RECORD INSTEAD OF ITEM ");

				$sql  = " SELECT ";
				$sql .= "  sr.TYPE_REC_DOC, ";
				$sql .= "  sr.ID_LOC, ";
				$sql .= "  sr.ID_ITEM, ";
				$sql .= "  sr.ID_RTE, ";
				$sql .= "  sr.ID_OPER, ";
				$sql .= "  sr.NAME_DOC, ";
				$sql .= "  sr.ID_REV_DOC, ";
				$sql .= "  d.STAT_DOC, ";
				$sql .= "  d.DESCR_DOC, ";
				$sql .= "  d.NAME_FILE ";
				$sql .= "  FROM nsa.DOC_XREF_SR_OPER sr ";
				$sql .= "  LEFT JOIN nsa.DOC_XREF_DTL d ";
				$sql .= "  on sr.NAME_DOC = d.NAME_DOC ";
				$sql .= "  WHERE sr.ID_ITEM not in ('LYANATEST9822','LYANATEST9822','LYANA9922','LYANA9922','OWBHNMUXLC2PM')";


				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$subject = "Item ".$row['ID_ITEM']." has a document linked to item routing.";

					$body  = "<html>";
					$body .= "	<p>Item ".$row['ID_ITEM']." has a document linked to item routing, please open document detail ".$row['NAME_DOC']." in document library, and delete this document reference.</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('rd@thinknsa.com'=>'R&D'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('lbrown@thinknsa.com'=>'Lyana Brown'),
					    	//'bcc'     =>array('sabdelsayed@thinknsa.com'=>'Sabrina Abdelsayed'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}


				//////////////////////////////////////////////////////////////////////////////////////
				///////////////CHECK FOR DOCUMENT SHOP ORDERS WITH DOCUMENT LINKS ON ROUTING RECORD INSTEAD OF ITEM
				/////////////////////////////////////////////////////////////////////////////////////
				error_log("### runCHECK_SETUP_ERRORS_4HOUR CHECKING FOR SHOP ORDERS WITH DOCUMENT LINKS ON ROUTING RECORD INSTEAD OF ITEM ");

				$sql  = " SELECT ";
				$sql .= " sr.ID_ITEM, ";
				$sql .= " sr.ID_RTE, ";
				$sql .= " sr.NAME_DOC, ";
				$sql .= " sr.ID_REV_DOC, ";
				$sql .= " sh.ID_SO, ";
				$sql .= " sh.SUFX_SO, ";
				$sql .= " sh.STAT_REC_SO, ";
				$sql .= " sh.DATE_ADD, ";
				$sql .= " sh.DATE_START_PLAN ";
				$sql .= " FROM nsa.DOC_XREF_SR_OPER sr ";
				$sql .= " join nsa.SHPORD_HDR sh ";
				$sql .= " on sr.ID_ITEM = sh.ID_ITEM_PAR ";
				$sql .= " WHERE sh.STAT_REC_SO not in ('E','C') ";
				$sql .= " ORDER BY sr.ID_ITEM asc ";

				QueryDatabase($sql, $results);
				while ($row = mssql_fetch_assoc($results)) {
					$subject = "Shop Order ".$row['ID_SO']." Item ".$row['id_item']." has a document linked to item routing.";

					$body  = "<html>";
					$body .= "	<p>Shop Order ".$row['ID_SO']." Item ".$row['id_item']." has a document linked to item routing, please open document detail ".$row['NAME_DOC']." in document library, and delete this document reference.</p>";
					$body .= "</html>";

					if (strtoupper(substr($_SERVER['HTTP_HOST'],-4)) == "TEST") {
						$head = array(
					    	'to'      =>array('gvandyne@thinknsa.com'=>'Greg VanDyne'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	//'cc'      =>array('gvandyne@thinknsa.com'=>$row['NAME_EMP']),
					    	//'bcc'     =>array('email4@email.net'=>'Admin'),
				    	);
			    	} else {
			    		$head = array(
					    	'to'      =>array('rd@thinknsa.com'=>'R&D'),
					    	'from'    =>array('eProduction@thinkNSA.com' =>'eProduction'),
					    	'cc'      =>array('lbrown@thinknsa.com'=>'Lyana Brown'),
					    	//'bcc'     =>array('sabdelsayed@thinknsa.com'=>'Sabrina Abdelsayed'),
				    	);
			    	}
					mail::send($head,$subject,$body);
					sleep(1);
				}













				$sql = "SET ANSI_NULLS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_WARNINGS OFF";
				QueryDatabase($sql, $results);
				$sql = "SET QUOTED_IDENTIFIER OFF";
				QueryDatabase($sql, $results);
				$sql = "SET ANSI_PADDING OFF";
				QueryDatabase($sql, $results);

				error_log("### runCHECK_SETUP_ERRORS_4HOUR DELETING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
				error_log("### LAST INSERT ID: " . $ProcRowID);

				$sql  = "DELETE FROM nsa.RUNNING_PROC ";
				$sql .= " WHERE ";
				$sql .= " rowid = " . $ProcRowID;
				QueryDatabase($sql, $results);















			} else {
				// FUTURE ENHANCEMENT -- Sleep and reloop
				error_log("### runCHECK_SETUP_ERRORS_4HOUR ALREADY RUNNING flag in RUNNING_PROC " . date('Y-m-d g:i:s a'));
			}

			error_log("### runCHECK_SETUP_ERRORS_4HOUR finished at " . date('Y-m-d g:i:s a'));
			error_log("#############################################");
		}
		$retval = DisconnectFromDatabaseServer($db);
		if ($retval == 0) {
			error_log("runCHECK_SETUP_ERRORS_4HOUR cannot disconnect from database");
		}
	}
?>
