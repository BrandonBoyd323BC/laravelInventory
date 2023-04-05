
function idItemChange() {
	$('#id_item').autocomplete(
	{
		source: "ajax/drfToFCBin_itemLookup.php",
		minLength: 1
	});	
}

function clearForm() {
		document.getElementById('id_item').value = '';
		$('#dataDiv').html("");
	

}

function getItemBinOrderInfo(){
	var url = "ajax/drfToFCBin.php";
	var item = document.getElementById('id_item').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	

	$.post(url,{ action: 'getItemBinOrderInfo', item: item},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

function saveNewBinLoc(){
	var url = "ajax/drfToFCBin.php";
	var newBin = document.getElementById('tbNewBin').value;
	var locRowid = document.getElementById('locRowid').value;
	
	$('#div_updateLocButton').html("Working..");
	$.post(url,{ action: 'saveNewBinLoc', newBin: newBin, locRowid: locRowid},
	function(data){
		$('#div_updateLocButton').html(data.returnValue);
	}, "json");
}

function saveNewOrdLinBinLoc(ordLinRowid){
	var url = "ajax/drfToFCBin.php";
	var ordLinNewBin = document.getElementById('tbOrdLinNewBin').value;
	//var ordLinRowid = document.getElementById('ordLinRowid').value;
	
	$('#div_updateOrdLinLocButton_'+ordLinRowid).html("Working..");
	$.post(url,{ action: 'saveNewOrdLinBinLoc', ordLinNewBin: ordLinNewBin, ordLinRowid: ordLinRowid},
	function(data){
		$('#div_updateOrdLinLocButton_'+ordLinRowid).html(data.returnValue);
	}, "json");
}
