
function idItemChange() {
	$('#id_item').autocomplete(
	{
		source: "ajax/itmBinChgr_itemLookup.php",
		minLength: 1
	});	
}

function clearForm() {
		document.getElementById('id_item').value = '';
		$('#dataBinDiv').html("");
		$('#dataOrdLinDiv').html("");
	

}

function getItemBinInfo(){
	var url = "ajax/itmBinChgr.php";
	var item = document.getElementById('id_item').value;
	
	$('#dataBinDiv').html("<img src='images/loading01.gif' />");

	

	$.post(url,{ action: 'getItemBinInfo', item: item},
	function(data){
		$('#dataBinDiv').html(data.returnValue);
		getOrdLinBinInfo(item);
	}, "json");
}

function getOrdLinBinInfo(item){
	var url = "ajax/itmBinChgr.php";
	//var item = document.getElementById('id_item').value;
	
	$('#dataOrdLinDiv').html("<img src='images/loading01.gif' />");

	

	$.post(url,{ action: 'getOrderLinBinInfo', item: item},
	function(data){
		$('#dataOrdLinDiv').html(data.returnValue);
	}, "json");
}

function saveNewBinLoc(){
	var url = "ajax/itmBinChgr.php";
	var newBin = document.getElementById('tbNewBin').value;
	var locRowid = document.getElementById('locRowid').value;
	var hdnItem = document.getElementById('hdnItem').value;
	
	$('#div_updateLocButton').html("Working..");
	$.post(url,{ action: 'saveNewBinLoc', newBin: newBin, locRowid: locRowid},
	function(data){
		$('#div_updateLocButton').html(data.returnValue);
		getOrdLinBinInfo(hdnItem);
	}, "json");
}

function saveNewOrdLinBinLoc(ordLinRowid){
	var url = "ajax/itmBinChgr.php";
	var ordLinNewBin = document.getElementById('tbOrdLinNewBin_'+ordLinRowid).value;
	//var ordLinRowid = document.getElementById('ordLinRowid').value;
	
	$('#div_updateOrdLinLocButton_'+ordLinRowid).html("Working..");
	$.post(url,{ action: 'saveNewOrdLinBinLoc', ordLinNewBin: ordLinNewBin, ordLinRowid: ordLinRowid},
	function(data){
		$('#div_updateOrdLinLocButton_'+ordLinRowid).html(data.returnValue);
	}, "json");
}

/*
function updateALLOrdLinLoc(){
	var url = "ajax/itmBinChgr.php";
	var listOfOrderLineRowids = document.getElementById('listOfOrderLineRowids').value;
	var ordLinNewBin = document.getElementById('tbOrdLinNewBin').value;
}
*/