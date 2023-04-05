function selModeChange() {
	var url = "ajax/prop65Checker.php";
	var selMode = document.getElementById('selMode').value;
	var focusElement = "selMode";

	if (selMode == "shopOrder") {
		focusElement = "so";
	}
	if (selMode == "itemNumber") {
		focusElement = "id_item";
	}
	if (selMode == "order") {
		focusElement = "id_order";
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

function doOnLoads() {
	window.resizeTo(400,600);
	focus();//selTeam.focus();
}



function searchItem(){
	var url = "ajax/prop65Checker.php";

	var id_item = document.getElementById('id_item').value;

	$('#btnSubmit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	
	$.post(url,{
		id_item: id_item 

	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnSubmit').attr("disabled", false);
		}, "json");
}

function sufxEntered(){
	var url = "ajax/prop65Checker.php";
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

function idItemChange() {
	$('#id_item').autocomplete(
	{
		source: "ajax/prop65Checker_itemlookup.php",
		minLength: 1
	});	
}

function clearForm() {
	var selMode = document.getElementById('selMode').value;
	if (selMode == 'shopOrder') {
		document.getElementById('so').value = '';
		document.getElementById('sufx').value = '';
		$('#dataDiv').html("");
	}
	if (selMode == 'itemNumber') {
		document.getElementById('id_item').value = '';
		$('#dataDiv').html("");
	}
	if (selMode == 'order') {
		document.getElementById('id_order').value = '';
		$('#dataDiv').html("");
	}
	selModeChange(); 
}

function clearInputs() {
	var selMode = document.getElementById('selMode').value;
	if (selMode == 'shopOrder') {
		document.getElementById('so').value = '';
		document.getElementById('sufx').value = '';
	}
	if (selMode == 'itemNumber') {
		document.getElementById('id_item').value = '';
	}
	if (selMode == 'order') {
		document.getElementById('id_order').value = '';
	}
}

function checkSO(){
	var url = "ajax/prop65Checker.php";
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'checkSO', so: so, sufx: sufx},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	clearInputs();
}

function checkItem(){
	var url = "ajax/prop65Checker.php";
	var item = document.getElementById('id_item').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'checkItem', item: item},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	clearInputs();
}

function checkOrder(){
	var url = "ajax/prop65Checker.php";
	var order = document.getElementById('id_order').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'checkOrder', order: order},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	clearInputs();
}