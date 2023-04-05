function selModeChange() {
	var url = "ajax/soLabels.php";
	var selMode = document.getElementById('selMode').value;
	var focusElement = "selMode";

	if (selMode == "shopOrder") {
		focusElement = "so";
	}
	if (selMode == "itemNumber") {
		focusElement = "id_item";
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

function sufxEntered(){
	var url = "ajax/soLabels.php";
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;

	if (sufx.length == 3) {
		$.post(url,{ action: 'getBoxQty', so: so, sufx: sufx},
		function(data){
			$('#td_qty_in_box').html(data.returnValue);
		}, "json");
		$('#qty_in_box').focus();
	}
}

function getSoLabels(){
	var url = "ajax/soLabels.php";
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;
	var qty_in_box = document.getElementById('qty_in_box').value;
	//var qty_in_bag = document.getElementById('qty_in_bag').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'getLabels', so: so, sufx: sufx, qty_in_box: qty_in_box},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	clearInputs();
}

function getItemLabels(){
	var url = "ajax/soLabels.php";
	var item = document.getElementById('id_item').value;
	var qty_in_box = document.getElementById('qty_in_box').value;
	//var qty_in_bag = document.getElementById('qty_in_bag').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'getLabels', item: item, qty_in_box: qty_in_box},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	clearInputs();
}

function idItemChange() {
	$('#id_item').autocomplete(
	{
		source: "ajax/soLabels_itemlookup.php",
		minLength: 1
	});	
}

function clearForm() {
	var selMode = document.getElementById('selMode').value;
	if (selMode == 'shopOrder') {
		document.getElementById('so').value = '';
		document.getElementById('sufx').value = '';
		document.getElementById('qty_in_box').value = '';
		$('#dataDiv').html("");
	}
	if (selMode == 'itemNumber') {
		document.getElementById('id_item').value = '';
		document.getElementById('qty_in_box').value = '';
		$('#dataDiv').html("");
	}
	selModeChange(); 
}

function clearInputs() {
	var selMode = document.getElementById('selMode').value;
	if (selMode == 'shopOrder') {
		document.getElementById('so').value = '';
		document.getElementById('sufx').value = '';
		document.getElementById('qty_in_box').value = '';
	}
	if (selMode == 'itemNumber') {
		document.getElementById('id_item').value = '';
		document.getElementById('qty_in_box').value = '';
	}
}


function doOnLoads() {
	//focus();so.focus();
	selModeChange();
}

