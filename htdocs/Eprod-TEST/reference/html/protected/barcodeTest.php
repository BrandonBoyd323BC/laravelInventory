<?php

	//include("mpdf60/mpdf.php");
	//include("procfile.php");

	require_once("mpdf60/mpdf.php");
	require_once("procfile.php");

	$retval = ConnectToDatabaseServer($DBServer, $db);
	if ($retval == 0) {
		print("		<p class='warning'>Could Not Connect To $DBServer!\n");
	} else {
		$retval = SelectDatabase($dbName);
		if ($retval == 0) {
			print( "		<p class='warning'>Could Not Select $db!\n");
		} else {
			$ret = '';
			$UserRow = GetUserPerms($_SERVER['PHP_AUTH_USER']);


			$ID_SO = "31893201";
			$SUFX_SO = 0;
			$DATE_ADD = "2015-07-10";
			$DATE_DUE_ORD = "2015-07-10";
			$ID_ITEM_PAR = "VNT99345XXL";
			$MARKER_NAME = "99345-2X-6";
			//$MARKER_NAME = "C54-RIB-VW-20C85151Y-000";
			$SO_ID_ITEM_COMP = "VNCBX900-SORANGE";
			$MARKER_FAB_CODE = "VNCBX900-SORANGE";
			$MARKER_LENGTH = "183.82";
			$MARKER_LAYERS = "4";
			$DESCR_1 = "BANOX FR3 COTTON PRESHRUNK ORG";
			$DESCR_2 = "60\" WIDE";
			$BIN_PRIM = "A01";
			$BaseRowID = "30829";
			$NUM_LAYERS = "1";

			$html  = "";
			$html .= "<html>";
			$html .= "	<head>";
			$html .= "		<style>";
			$html .= "			body {";
			$html .= "				font-family: sans-serif;";
			$html .= "				font-size: 9pt;";
			$html .= "				background: transparent url('bgbarcode.png') repeat-y scroll left top;";
			$html .= "			}";
			$html .= "			h5, p {	";
			$html .= "				margin: 0pt;";
			$html .= "			}";
			$html .= "			table.items {";
			$html .= "				font-size: 12pt; ";
			$html .= "				border-collapse: collapse;";
			$html .= "				border: 3px solid #880000; ";
			$html .= "			}";
			$html .= "			td { ";
			$html .= "				vertical-align: top; ";
			$html .= "			}";
			$html .= "			table thead td { ";
			$html .= "				background-color: #EEEEEE;";
			$html .= "				text-align: center;";
			$html .= "			}";
			$html .= "			table tfoot td { ";
			$html .= "				background-color: #AAFFEE;";
			$html .= "				text-align: center;";
			$html .= "			}";
			$html .= "			.barcode {";
			$html .= "				padding: 1.0mm;";
			$html .= "				margin: 0;";
			$html .= "				vertical-align: top;";
			$html .= "				color: #000000;";
			$html .= "			}";
			$html .= "			.barcodecell {";
			$html .= "				text-align: center;";
			$html .= "				vertical-align: middle;";
			$html .= "				padding: 0;";
			$html .= "			}";
			$html .= "		</style>";
			$html .= "	</head>";
			$html .= "	<body>";
			$html .= "		<table class='items' width='100%' cellpadding='0' border='1'>";
			$html .= "		<thead>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";
			$html .= "			<tr>";
			$html .= "				<td colspan=2>Part# ". $ID_ITEM_PAR ."</td>";
			$html .= "				<td colspan=2>SO: <b>". $ID_SO ."</b></td>";
			$html .= "				<td>Date Issued: <b>". $DATE_ADD ."</b></td>";
			$html .= "			</tr>";

			$html .= "			<tr>";
			$html .= "				<td>Material: <b>".$MARKER_FAB_CODE."</b></td>";
			$html .= "				<td>Bin: <b>".$BIN_PRIM."</b></td>";
			$html .= "				<td width='10%'>Layers</td>";
			$html .= "				<td width='10%'>Length</td>";
			$html .= "				<td>Marker: ".$MARKER_NAME."</td>";
			$html .= "			</tr>";
			$html .= "		</thead>";
			$html .= "		<tbody>";
			$html .= "			<tr>";
			$html .= "				<td align='center' colspan=2>".$DESCR_1."</td>";
			$html .= "				<td align='center'>".$NUM_LAYERS."</td>";
			$html .= "				<td align='center'>".$MARKER_LENGTH."\"</td>";
			$html .= "				<td class='barcodecell'><barcode code='".$MARKER_NAME."' type='C39' class='barcode' /></td>";
			$html .= "			</tr>";
			$html .= "		</tbody>";
/*
			$html .= "			<tr>";
			$html .= "				<td>Material: <b>".$MARKER_FAB_CODE."</b></td>";
			$html .= "				<td>Bin: <b>".$BIN_PRIM."</b></td>";
			$html .= "				<td width='7%'>Layers: <b>".$NUM_LAYERS."</b></td>";
			$html .= "				<td width='10%'>Length: <b>".$MARKER_LENGTH."\"</b></td>";
			$html .= "				<td>Marker: ".$MARKER_NAME."</td>";
			$html .= "			</tr>";
			$html .= "		</thead>";
			$html .= "		<tbody>";
			$html .= "			<tr>";
			$html .= "				<td align='center' colspan=4>".$DESCR_1."</td>";
			$html .= "				<td class='barcodecell'><barcode code='".$MARKER_NAME."' type='C39' class='barcode' /></td>";
			$html .= "			</tr>";
			$html .= "		</tbody>";
*/
			//$html .= "			<tr>";
			//$html .= "				<td colspan=5>RowID: ".$BaseRowID;
			//$html .= "					<barcode code='".$BaseRowID."' type='C39' class='barcode' />";
			//$html .= "				</td>";
			//$html .= "			</tr>";
			$html .= "		</table>";
			$html .= "		<h7><barcode code='".$BaseRowID."' type='C39' class='barcode' height='0.56' text='".$BaseRowID."'/>RefID: ".$BaseRowID."</h7>";
			$html .= "	</body>";
			$html .= "</html>";







			$outputfile = "/mnt/GerberPDF/DocIncoming/Pending/" . $MARKER_NAME ."___labels.pdf";
			$mpdf=new mPDF('','A4-L','','',10,10,120,10,10,10); 
			//mPDF ([ string $mode [, mixed $format [, float $default_font_size [, string $default_font [, float $margin_left , float $margin_right , float $margin_top , float $margin_bottom , float $margin_header , float $margin_footer [, string $orientation ]]]]]])
			$mpdf->WriteHTML($html);
			//$mpdf->Output($outputfile,'F'); 
			$mpdf->Output(); 
			//exit;
		}
	}	
?>