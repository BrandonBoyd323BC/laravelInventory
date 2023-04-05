
function submitForm(action){
	var order_num = document.getElementById('txt_OrderNum').value;
	var po_num = document.getElementById('txt_PONum').value;
	//var ship_num = document.getElementById('txt_ShipNum').value;

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	//$.post("ajax/ediMaint.php",{ order_num: order_num, po_num: po_num, ship_num: ship_num, action: action },
	$.post("ajax/ediMaint.php",{ order_num: order_num, po_num: po_num, action: action },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	
}


function clearForm() {
	document.getElementById('txt_OrderNum').value = '';
	document.getElementById('txt_PONum').value = '';
	//document.getElementById('txt_ShipNum').value = '';
	$('#dataDiv').html("");
	$('#txt_OrderNum').focus();
}



function showStatusChange_HDR(rowid){
	var so_status = document.getElementById('STAT_REC_SO_' + rowid).value;
	var url = "ajax/ediMaint.php";

	if (so_status.substr(0, 1) == '*') {
		$('#'+'HDR_Save_'+rowid).attr("disabled", true);
	} else {
		$('#'+'HDR_Save_'+rowid).attr("disabled", false);
	}


}


function saveStatusChange_HDR(rowid){
	var so_status = document.getElementById('STAT_REC_SO_' + rowid).value;
	var action = "update_hdr";
	var url = "ajax/ediMaint.php";

	so_status = so_status.replace("*", "")

	//$('#dataAddDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, so_status: so_status, rowid: rowid },
	function(data){
		$('#'+'HDR_Save_div_' +rowid).html(data.returnValue);
	}, "json");

}


function showStatusChange_OPER(rowid){
	var oper_status = document.getElementById('STAT_REC_OPER_' + rowid).value;
	var url = "ajax/ediMaint.php";

	if (oper_status.substr(0, 1) == '*') {
		$('#'+'OPER_Save_'+rowid).attr("disabled", true);
	} else {
		$('#'+'OPER_Save_'+rowid).attr("disabled", false);
	}


}

function saveStatusChange_OPER(rowid){
	var oper_status = document.getElementById('STAT_REC_OPER_' + rowid).value;
	var action = "update_oper";
	var url = "ajax/ediMaint.php";

	oper_status = oper_status.replace("*", "")

	//$('#dataAddDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, oper_status: oper_status, rowid: rowid },
	function(data){
		$('#'+'OPER_Save_div_' +rowid).html(data.returnValue);
	}, "json");

}



function closeDiv(div) {
	//alert(div);
	
	var r=confirm("Remove table from view?");
	
	if (r==true) {
	
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
	
}
