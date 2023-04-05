function showLocationChange(){
	var location = document.getElementById('sel_Location').value;
	var url = "ajax/btLabels.php";
	var action = "location_change";
	//var action = "testLabelFile";

	$('#sel2x2printer').html("<option value='LOADING'>LOADING</option>");

	$.post(url,{ action: action, location: location, printerSize: '2X2' },
	function(data){
		$('#sel2x2printer').html(data.returnValue);
	}, "json");

	$('#sel4x6printer').html("<option value='LOADING'>LOADING</option>");

	$.post(url,{ action: action, location: location, printerSize: '4X6'  },
	function(data){
		$('#sel4x6printer').html(data.returnValue);
	}, "json");

}

function savePrinterChange(id){
	var printer = document.getElementById(id).value;
	var url = "ajax/btLabels.php";
	var action = "savePrinterChange";
	//var printerSize = '';

	//if (id == 'sel_2x2_printer') {
	//	printerSize = '2x2'
	//} else if (id = 'sel_4x6_printer') {
	//	printerSize = '4x6'
	//} 

	
	$('#'+id+'RESPONSE').html("Saving");

	$.post(url,{ action: action, printerSelID: id, printer: printer },
	function(data){
		$('#'+id+'RESPONSE').html(data.returnValue);
	}, "json");

}


function selModeChange() {
	var url = "ajax/btLabels.php";
	var selMode = document.getElementById('selMode').value;
	var focusElement = "selMode";

	if (selMode == "shopOrder") {
		focusElement = "so";
	}
	if (selMode == "itemNumber") {
		focusElement = "id_item";
	}
	if (selMode == "poReceived") {
		focusElement = "id_po";
	}

	$.post(url,{ action: 'selModeChange', selMode: selMode },
	function(data){
		$('#formDiv').html(data.returnValue);
		$('#'+focusElement).focus();
	}, "json");

	$('#dataDiv').html("");
}

function nextOnDash(so,sufx) {
	$('#'+so).keypress(function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#'+sufx).focus();
    	}
	});
}

function checkSufxLength(){
	var url = "ajax/btLabels.php";
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;
	
	if (sufx.length == 3) {
		$('#dw_submit').focus();
	}
}


function getSoLabelList(){
	var url = "ajax/btLabels.php";
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;
	//var qty_in_box = document.getElementById('qty_in_box').value;
	//var qty_in_bag = document.getElementById('qty_in_bag').value;
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	//$.post(url,{ action: 'getLabels', so: so, sufx: sufx, qty_in_box: qty_in_box},
	$.post(url,{ action: 'getSoLabelList', so: so, sufx: sufx},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}


function printUPCLabels(){
	var url = "ajax/btLabels.php";
	var printer = document.getElementById('sel2x2printer').value;
	var id_item = document.getElementById('hid_ID_ITEM').value;
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;
	var label_qty = document.getElementById('tb_UPC_QTY_LABELS').value;
	//$('#dataDiv').html("<img src='images/loading01.gif' />");

	if (label_qty == '') {
		alert("Qty to Print is Required!");
		$('#tb_UPC_QTY_LABELS').focus();
		return;
	}

	$.post(url,{ action: 'printUPCLabels', id_item: id_item, label_qty: label_qty, id_so: so, sufx_so: sufx, printer: printer},
	function(data){
		//$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}

function printBoxQtyLabels(){
	var url = "ajax/btLabels.php";
	var printer = document.getElementById('sel4x6printer').value;
	var id_item = document.getElementById('hid_ID_ITEM').value;
	//var so = document.getElementById('so').value;
	//var sufx = document.getElementById('sufx').value;
	var qty_in_box = document.getElementById('tb_BoxQty_IN_BOX_QTY').value;
	var id_po_cust = document.getElementById('tb_BoxQty_PO_NUM').value;

	var label_qty = document.getElementById('tb_BoxQty_QTY_LABELS').value;
	//$('#dataDiv').html("<img src='images/loading01.gif' />");

	if (qty_in_box == '') {
		alert("Qty in Box is Required!");
		$('#tb_BoxQty_IN_BOX_QTY').focus();
		return;
	}

	if (label_qty == '') {
		alert("Qty to Print is Required!");
		$('#tb_BoxQty_QTY_LABELS').focus();
		return;
	}

	$.post(url,{ action: 'printBoxQtyLabels', id_item: id_item, label_qty: label_qty, qty_in_box: qty_in_box, id_po_cust: id_po_cust, printer: printer},
	function(data){
		//$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}

function requestUPC(id_so='',sufx_so=''){
	var url = "ajax/btLabels.php";
	var id_item = document.getElementById('hid_ID_ITEM').value;

	$('#button_Request_UPC').attr("disabled", true);
	$.post(url,{ action: 'requestUPC', id_item: id_item, id_so: id_so, sufx_so: sufx_so},
	function(data){
		$('#requestUPC_RESPONSE').html(data.returnValue);
	}, "json");

}


function getItemLabelList(){
	var url = "ajax/btLabels.php";
	var id_item = document.getElementById('id_item').value;

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'getItemLabelList', id_item: id_item},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}

function idItemChange() {
	$('#id_item').autocomplete(
	{
		source: "ajax/btLabels_itemlookup.php",
		minLength: 1
	});	
}

function changeRollTagUOM(){
	var stk_UOM = document.getElementById('hid_RollTag_STK_UOM').value;
	var pur_UOM = document.getElementById('hid_RollTag_PUR_UOM').value;
	var ratio_stk_pur = document.getElementById('hid_RollTag_RATIO_STK_PUR').value;
	var qty_on_tag = document.getElementById('tb_RollTag_QTY_ON_TAG').value;
	var sel_UOM = document.getElementById('sel_RollTag_UOM').value;
	var new_qty = qty_on_tag;

	if (sel_UOM == pur_UOM) {
		new_qty = qty_on_tag/ratio_stk_pur;
	}
	if (sel_UOM == stk_UOM) {
		new_qty = qty_on_tag*ratio_stk_pur;	
	}
	
	document.getElementById('tb_RollTag_QTY_ON_TAG').value = new_qty;

}

function printRollTagLabels(recv_rowid){
	var url = "ajax/btLabels.php";
	var printer = document.getElementById('sel4x6printer').value;
	var id_po = document.getElementById('tb_RollTag_PO_NUM').value;
	var id_item = document.getElementById('hid_ID_ITEM').value;
	//var id_item_vnd = document.getElementById('hid_NonBinTag_ID_ITEM_VND__'+recv_rowid).value;
	var uom = document.getElementById('sel_RollTag_UOM').value;
	//var name_vnd_ordfm = document.getElementById('hid_NonBinTag_NAME_VND_ORDFM__'+recv_rowid).value;
	var date_print = document.getElementById('hid_RollTag_DATE_PRINT').value;
	var item_descr = document.getElementById('hid_RollTag_ITEM_DESCR').value;
	var bin_prim = document.getElementById('tb_RollTag_BIN_PRIM').value;
	var qty_on_tag = document.getElementById('tb_RollTag_QTY_ON_TAG').value;
	var qty_to_print = document.getElementById('tb_RollTag_QTY_LABELS').value;

	if (qty_on_tag == '') {
		alert("Qty on Tag is Required!");
		$('#tb_RollTag_QTY_ON_TAG').focus();
		return;
	}

	if (qty_to_print == '') {
		alert("Qty to Print is Required!");
		$('#tb_RollTag_QTY_LABELS').focus();
		return;
	}

	$.post(url,{ 
		action: 'printRollTagLabels', 
		id_po: id_po, 
		id_item: id_item, 
		//id_item_vnd: id_item_vnd, 
		uom: uom, 
		//name_vnd_ordfm: name_vnd_ordfm, 
		date_print: date_print,
		item_descr: item_descr,
		bin_prim: bin_prim,
		qty_on_tag: qty_on_tag, 
		qty_to_print: qty_to_print, 
		printer: printer
	},
	function(data){
		//$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}



function getPOLabelList(){
	var url = "ajax/btLabels.php";
	var id_po = document.getElementById('id_po').value;

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'getPOLabelList', id_po: id_po},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}

function printNonBinTags(recv_rowid){
	var url = "ajax/btLabels.php";
	var printer = document.getElementById('sel4x6printer').value;
	var id_po = document.getElementById('hid_NonBinTag_ID_PO__'+recv_rowid).value;
	var id_item = document.getElementById('hid_NonBinTag_ID_ITEM__'+recv_rowid).value;
	var id_item_vnd = document.getElementById('hid_NonBinTag_ID_ITEM_VND__'+recv_rowid).value;
	var uom = document.getElementById('sel_NonBinTag_UOM__'+recv_rowid).value;
	var name_vnd_ordfm = document.getElementById('hid_NonBinTag_NAME_VND_ORDFM__'+recv_rowid).value;
	var date_rcv = document.getElementById('hid_NonBinTag_DATE_RCV__'+recv_rowid).value;
	var item_descr = document.getElementById('hid_NonBinTag_ITEM_DESCR__'+recv_rowid).value;
	var bin_prim = document.getElementById('tb_NonBinTag_BIN_PRIM__'+recv_rowid).value;
	var qty_on_tag = document.getElementById('tb_NonBinTag_QTY_ON_TAG__'+recv_rowid).value;
	var qty_to_print = document.getElementById('tb_NonBinTag_QTY_TO_PRINT__'+recv_rowid).value;

	if (qty_on_tag == '') {
		alert("Qty on Tag is Required!");
		$('#tb_NonBinTag_QTY_ON_TAG__'+recv_rowid).focus();
		return;
	}

	if (qty_to_print == '') {
		alert("Qty to Print is Required!");
		$('#tb_NonBinTag_QTY_TO_PRINT__'+recv_rowid).focus();
		return;
	}

	$.post(url,{ 
		action: 'printNonBinTags', 
		id_po: id_po, 
		id_item: id_item, 
		id_item_vnd: id_item_vnd, 
		uom: uom, 
		name_vnd_ordfm: name_vnd_ordfm, 
		date_rcv: date_rcv,
		item_descr: item_descr,
		bin_prim: bin_prim,
		qty_on_tag: qty_on_tag, 
		qty_to_print: qty_to_print, 
		printer: printer
	},
	function(data){
		//$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}

function changeNonBinTagUOM(recv_rowid){
	var stk_UOM = document.getElementById('hid_NonBinTag_STK_UOM__'+recv_rowid).value;
	var pur_UOM = document.getElementById('hid_NonBinTag_PUR_UOM__'+recv_rowid).value;
	var ratio_stk_pur = document.getElementById('hid_NonBinTag_RATIO_STK_PUR__'+recv_rowid).value;
	var qty_on_tag = document.getElementById('tb_NonBinTag_QTY_ON_TAG__'+recv_rowid).value;
	var sel_UOM = document.getElementById('sel_NonBinTag_UOM__'+recv_rowid).value;
	var new_qty = qty_on_tag;

	if (sel_UOM == pur_UOM) {
		new_qty = qty_on_tag/ratio_stk_pur;
	}
	if (sel_UOM == stk_UOM) {
		new_qty = qty_on_tag*ratio_stk_pur;	
	}
	
	document.getElementById('tb_NonBinTag_QTY_ON_TAG__'+recv_rowid).value = new_qty;

}


function getIntransitLabelList(){
	var url = "ajax/btLabels.php";
	var id_intransit = document.getElementById('id_intransit').value;

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'getIntransitLabelList', id_intransit: id_intransit},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}


function printIntransitTags(concat_intr_rev_item){
	var url = "ajax/btLabels.php";
	var printer = document.getElementById('sel4x6printer').value;
	var id_intransit = document.getElementById('hid_intransit_ID_INTRANSIT__'+concat_intr_rev_item).value;
	var id_item = document.getElementById('hid_intransit_ID_ITEM__'+concat_intr_rev_item).value;
	var uom = document.getElementById('sel_intransit_UOM__'+concat_intr_rev_item).value;
	var item_descr = document.getElementById('hid_intransit_ITEM_DESCR__'+concat_intr_rev_item).value;
	var bin_prim = document.getElementById('tb_intransit_BIN_PRIM__'+concat_intr_rev_item).value;
	var qty_on_tag = document.getElementById('tb_intransit_QTY_ON_TAG__'+concat_intr_rev_item).value;
	var qty_to_print = document.getElementById('tb_intransit_QTY_TO_PRINT__'+concat_intr_rev_item).value;

	if (qty_on_tag == '') {
		alert("Qty on Tag is Required!");
		$('#tb_intransit_QTY_ON_TAG__'+concat_intr_rev_item).focus();
		return;
	}

	if (qty_to_print == '') {
		alert("Qty to Print is Required!");
		$('#tb_intransit_QTY_TO_PRINT__'+concat_intr_rev_item).focus();
		return;
	}

	$.post(url,{ 
		action: 'printIntransitTags', 
		id_intransit: id_intransit, 
		id_item: id_item, 
		uom: uom, 
		item_descr: item_descr,
		bin_prim: bin_prim,
		qty_on_tag: qty_on_tag, 
		qty_to_print: qty_to_print, 
		printer: printer
	},
	function(data){
		//$('#dataDiv').html(data.returnValue);
	}, "json");
	//clearInputs();
}


function changeIntransitUOM(concat_intr_rev_item){
	var stk_UOM = document.getElementById('hid_intransit_STK_UOM__'+concat_intr_rev_item).value;
	var pur_UOM = document.getElementById('hid_intransit_PUR_UOM__'+concat_intr_rev_item).value;
	var ratio_stk_pur = document.getElementById('hid_intransit_RATIO_STK_PUR__'+concat_intr_rev_item).value;
	var qty_on_tag = document.getElementById('tb_intransit_QTY_ON_TAG__'+concat_intr_rev_item).value;
	var sel_UOM = document.getElementById('sel_intransit_UOM__'+concat_intr_rev_item).value;
	var new_qty = qty_on_tag;

	if (sel_UOM == pur_UOM) {
		new_qty = qty_on_tag/ratio_stk_pur;
	}
	if (sel_UOM == stk_UOM) {
		new_qty = qty_on_tag*ratio_stk_pur;	
	}
	
	document.getElementById('tb_intransit_QTY_ON_TAG__'+concat_intr_rev_item).value = new_qty;

}


function clearForm() {
	var selMode = document.getElementById('selMode').value;
	if (selMode == 'shopOrder') {
		document.getElementById('so').value = '';
		document.getElementById('sufx').value = '';
		//document.getElementById('qty_in_box').value = '';
		$('#dataDiv').html("");
	}
	if (selMode == 'itemNumber') {
		document.getElementById('id_item').value = '';
		//document.getElementById('qty_in_box').value = '';
		$('#dataDiv').html("");
	}
	selModeChange(); 
}

function clearInputs() {
	var selMode = document.getElementById('selMode').value;
	if (selMode == 'shopOrder') {
		document.getElementById('so').value = '';
		document.getElementById('sufx').value = '';
		//document.getElementById('qty_in_box').value = '';
	}
	if (selMode == 'itemNumber') {
		document.getElementById('id_item').value = '';
		document.getElementById('qty_in_box').value = '';
	}
}


function doOnLoads() {
	//focus();so.focus();
	showLocationChange();
	selModeChange();
}

