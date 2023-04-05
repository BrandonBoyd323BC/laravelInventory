function doOnLoads() {
	numRecsChange();
}

function numRecsChange(){
	var url = "ajax/binCount.php";
	var action = "numRecsChange";
	var user_recs = document.getElementById('user_recs').value;
	var num_recs = document.getElementById('num_recs').value;

	$.post(url,{ action: action, user_recs: user_recs, num_recs: num_recs },
		function(data){
			$('#dataAddDiv').html(data.returnValue);
	}, "json");
	document.getElementById('tb_bin').focus();
}

function clearForm() {
	document.getElementById('tb_bin').value = '';
	document.getElementById('tb_item').value = '';
	document.getElementById('tb_qty').value = '';
	$('#dataDiv').html("");
	//numRecsChange();
}

function searchKeyPress(e,nextid) {
	// look for window.event in case event isn't passed in

	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById(nextid).focus();
	}
}

function sendAddValue(){
	var url = "ajax/binCount.php";	
	var bin = document.getElementById('tb_bin').value;
	var item = document.getElementById('tb_item').value;
	var qty = document.getElementById('tb_qty').value;
	var action = "insertRecord";

	if (!IsInteger(qty)) {
		alert("Invalid Quantity!");
		return;
	}

	$('#button_addRecord').attr("disabled", true);
	$('#dataAddDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, bin: bin, item: item, qty: qty },
	function(data){
		$('#dataAddDiv').html(data.returnValue);
		$('#button_addRecord').attr("disabled", false);
		document.getElementById('tb_bin').value = '';
		document.getElementById('tb_item').value = '';
		document.getElementById('tb_qty').value = '';
		numRecsChange();
		document.getElementById('tb_bin').focus();
	}, "json");
	
}

function closeDiv(div) {
	var r=confirm("Remove table from view?");
	
	if (r==true) {
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
}

var popupStatus = 0; 

function IsInteger(strString)
	//  check for valid numeric strings	
	{
	var strValidChars = "0123456789-";
	var strChar;
	var blnResult = true;

	if (strString.length == 0) return false;

	//  test strString consists of valid characters listed above
	for (i = 0; i < strString.length && blnResult == true; i++) {
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1) {
         		blnResult = false;
		}
	}
	return blnResult;
}

function showEditField(field_id){
	return

	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/binCount.php";
	var action = "showedit";

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function cancelEditField(field_id, field_value){
	var url = "ajax/binCount.php";	
	var action = "canceledit";

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/binCount.php";	
	var action = "saveedit";

	if (new_value.length > 75) {
		alert("Value too long. Must be less than 75 characters");
		return;
	}
	
	if (new_value.match('[^A-Za-z0-9. -]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: new_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function deleteRecord(rowid) {//need
	var r = confirm("Are you sure you want to delete this record?");
	var url = "ajax/binCount.php";	
	var action = "deleteRecord";

	if (r==true) {
		$.post(url,{action: action, deleteRecord: 1, rowid: rowid },
		function(data){
			$('#delete_' + rowid).html(data.returnValue);
			numRecsChange();
		}, "json");
	}

}
