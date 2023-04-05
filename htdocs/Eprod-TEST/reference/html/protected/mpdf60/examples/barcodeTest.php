<?php


$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
	font-size: 9pt;
	background: transparent url(\'bgbarcode.png\') repeat-y scroll left top;
}
h5, p {	margin: 0pt;
}
table.items {
	font-size: 9pt; 
	border-collapse: collapse;
	border: 3px solid #880000; 
}
td { vertical-align: top; 
}
table thead td { background-color: #EEEEEE;
	text-align: center;
}
table tfoot td { background-color: #AAFFEE;
	text-align: center;
}
.barcode {
	padding: 1.5mm;
	margin: 0;
	vertical-align: top;
	color: #000000;
}
.barcodecell {
	text-align: center;
	vertical-align: middle;
	padding: 0;
}
</style>
</head>
<body>



<h1>mPDF</h1>
<h2>Barcodes</h2>
<p>NB <b>Quiet zones</b> - The barcode object includes space to the right/left or top/bottom only when the specification states a \'quiet zone\' or \'light margin\'. All the examples below also have CSS property set on the barcode object i.e. padding: 1.5mm; </p>

<h3>EAN-13 Barcodes (EAN-2 and EAN-5)</h3>
<p>NB EAN-13, UPC-A, UPC-E, and EAN-8 may all include an additional bar code(EAN-2 and EAN-5) to the right of the main bar code (see below).</p>
<p>A nominal height and width for these barcodes is defined by the specification. \'size\' will scale both the height and width. Values between 0.8 and 2 are allowed (i.e. 80% to 200% of the nominal size). \'height\' can also be varied as a factor of 1; this is applied after the scaling factor used for \'size\'.</p>
<table class="items" width="100%" cellpadding="8" border="1">
<thead>
<tr>
<td width="10%">CODE</td>
<td>DESCRIPTION</td>
<td>BARCODE</td>
</tr>
</thead>
<tbody>
<!-- ITEMS HERE -->




<tr>
<td align="center">C39</td>
<td>CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9. Valid characters: [0-9 A-Z \'-\' . Space $/+%]</td>
<td class="barcodecell"><barcode code="99345-2X-6" type="C39" class="barcode" /></td>
</tr>



</tbody>
</table>


</body>
</html>
';
//==============================================================
//==============================================================
include("../mpdf.php");

$mpdf=new mPDF('L','','','',20,15,25,25,10,10); 
$mpdf->WriteHTML($html);
$mpdf->Output(); 

exit;

?>