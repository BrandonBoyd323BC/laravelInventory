
function showTable(){
	var sel_group_filter = document.getElementById('sel_group_filter').value;
	var url = "ajax/stklist.php";
	var action = "show";
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, sel_group_filter: sel_group_filter },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");

}




function showEditField(field_id){
	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/stklist.php";
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
	var url = "ajax/stklist.php";	
	var action = "canceledit";

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/stklist.php";	
	var action = "saveedit";

	//alert(field_id.substring(0,3));

	//validate new values
	if (new_value.length > 50) {
		alert("Value too long. Must be less than 50 characters");
		return;
	}
	
	if (new_value.match('[^A-Za-z0-9. -#]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, hashes, spaces and periods.");
		return;
	}
	

	$.post(url,{ action: action, field_id: field_id, field_value: new_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

/*
function showEditStatusField(id){
	var sel_status = document.getElementById(id).value;
	var url = "ajax/stklist.php";
	//var action = "";

	if (sel_status.indexOf("*") == 0) {
		alert("Same as in DB");
	} else {
		alert("Show button");
	}

	return;

	$.post(url,{ action: action},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}
*/






function IsInteger(strString){
	//  check for valid numeric strings	
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
	
function insertNewRecord(){
	var url = "ajax/stklist.php";
	var action = "insert_record";

	var id_item = document.getElementById('add_ID_ITEM').value;
	var group = document.getElementById('add_GROUP').value;
	var sub_group = document.getElementById('add_SUB_GROUP').value;
	var sort = document.getElementById('add_SORT').value;
	var adv = document.getElementById('add_ADV').value;
	var wo = document.getElementById('add_WO').value;
	var source = document.getElementById('add_SOURCE').value;



/*	
	var quantity = document.getElementById('add_QUANTITY').value;
	var manf = document.getElementById('add_MANUFACTURER').value;
	//var status = document.getElementById('add_Quantity').value;
	var model = document.getElementById('add_MODEL').value;
	var modelNum = document.getElementById('add_MODEL_NUM').value;
	var drawer = document.getElementById('add_DRAWER').value;
	var row = document.getElementById('add_ROW').value;
	var bin = document.getElementById('add_BIN').value;
	//var assetNum = document.getElementById('add_QUANITY').value;
	var partNum = document.getElementById('add_PART_NUM').value;

	if ( manf == null || manf == "" ) {
   	alert('Manufacturer must have input it cannot be blank');
   	  return false;
   	}
   	if ( drawer == null || drawer == "" ) {
   	alert('Drawer must have input it cannot be blank');
      return false;
   	}
	if ( row == null || row == "" ) {
   	alert('Row must have input it cannot be blank');
      return false;
   	}
	if ( bin == null || bin == "" ) {
   	alert('Bin# must have input it cannot be blank');
      return false;
   	}
	if ( partNum == null || partNum	== "" ) {
   	alert('Part Number must have input it cannot be blank');
      return false;
   	}
	if ( quantity == null || quantity == "" ) {
   	alert('Quantity must have input it cannot be blank');
      return false;
   	}
	if(/^[a-zA-Z0-9- ]*$/.test(manf) == false) {
    alert('Manufacturer contains illegal (special) characters.');
    return;
	}
	if(/^[a-zA-Z0-9- ]*$/.test(model) == false) {
    alert('Model contains illegal (special) characters.');
    return;
	}
	if(/^[a-zA-Z0-9- ]*$/.test(modelNum) == false) {
    alert('Model Number contains illegal (special) characters.');
    return;
	}
	if(/^[a-zA-Z0-9- ]*$/.test(partNum) == false) {
    alert('Part Number contains illegal (special) characters.');
    return;
	}
	if(IsInteger(quantity) == false){
		alert("Quantity must be an integer");
		return;
	}//end if
	if(IsInteger(drawer) == false){
		alert("Drawer must be an integer");
		return;
	}//end if
	if(IsInteger(row) == false){
		alert("Row must be an integer");
		return;
	}//end if
	if(IsInteger(bin) == false){
		alert("Bin must be an integer");
		return;
	}//end if
	
	if(quantity.length > 10|| row.length > 10|| bin.length > 10 || drawer.length > 10 ){
		alert(" Quantity, Row, and Bin must be less than 10 characters");
		return;
	}//end if
	if(manf.length > 50 || model.length > 50 || modelNum.length > 50 || partNum.length > 50  ){
		alert("Head brand must be less than 50 characters");
		return;
	}//end 
*/ 

	$('#btnInsertRecord').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		id_item: id_item,
		group: group,
		sub_group: sub_group,
		sort: sort,
		adv: adv,
		wo: wo,
		source: source
	},
	

	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnInsertRecord').attr("disabled", false);	
		}, "json");
		alert("Your record has been recorded");
		location.reload();

}//end insertnewrecord

function deleteRecord(rowid) {
	var r = confirm("Are you sure you want to delete this record?");
	var url = "ajax/stklist.php";	
	var action = "deleteRecord";

	if (r==true) {
		$.post(url,{ action: action, deleteRecord: 1, rowid: rowid },
		function(data){
			$('#delete_' + rowid).html(data.returnValue);
		}, "json");
		
		location.reload();
	}
}

function refreshRecords(){
	var url = "ajax/stklist.php";
	//var show_status = document.getElementById('show_status').value;
	var action = "refresh_record";

	$.post(url,{
		action: action,
		refreshRec: 1
		//show_status: show_status
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnInsertRecord').attr("disabled", false);	
		}, "json");
}
