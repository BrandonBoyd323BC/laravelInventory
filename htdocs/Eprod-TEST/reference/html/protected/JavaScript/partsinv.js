
function showStatusChange(){
	//var show_status = document.getElementById('show_status').value;
	//var show_status = '';
	var show_mfg = document.getElementById('filterMFG').value;
	var show_model = document.getElementById('filterMODEL').value;
	var show_model_num = document.getElementById('filterMODEL_NUM').value;
	var show_drawer = document.getElementById('filterDRAWER').value;
	var show_row = document.getElementById('filterROW').value;
	var show_bin = document.getElementById('filterBIN').value;
	var show_part_num = document.getElementById('filterPART_NUM').value;
	var show_qty = document.getElementById('filterQTY').value;
	var sort_dir_flagC = document.getElementById('sortDirFlag').value;
	var sort_fieldC = document.getElementById('sortField').value;
	var url = "ajax/partsinv.php";

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: "show", show_mfg: show_mfg, show_model: show_model, show_model_num: show_model_num, show_drawer: show_drawer,
		show_row: show_row, show_bin: show_bin, show_part_num: show_part_num, show_qty: show_qty, sort_fieldC: sort_fieldC, sort_dir_flagC: sort_dir_flagC },
		

	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

function showOnLoad(){
	//var show_status = document.getElementById('show_status').value;
	//var show_status = '';
	var show_mfg = 'ALL';
	var show_model = 'ALL';
	var show_model_num = 'ALL';
	var show_drawer = 'ALL';
	var show_row = 'ALL';
	var show_bin = 'ALL';
	var show_part_num = 'ALL';
	var show_qty = 'ALL';
	var url = "ajax/partsinv.php";
	var sort_fieldC = 'MANUFACTURER';
	var sort_dir_flagC = 'asc';

	$('#dataDiv').html("<img src='images/loading01.gif' />");

$.post(url,{ action: "show", show_mfg: show_mfg, show_model: show_model, show_model_num: show_model_num, show_drawer: show_drawer,
		show_row: show_row, show_bin: show_bin, show_part_num: show_part_num, show_qty: show_qty, sort_fieldC: sort_fieldC, sort_dir_flagC: sort_dir_flagC },
		

	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}


function sortColumnBy(sort_fieldC){
	
	//var sort_dirC = "";
	var url = "ajax/partsinv.php";
	var sort_dir_flagC = document.getElementById('sortDirFlag').value;
	var show_mfg = document.getElementById('filterMFG').value;
	var show_model = document.getElementById('filterMODEL').value;
	var show_model_num = document.getElementById('filterMODEL_NUM').value;
	var show_drawer = document.getElementById('filterDRAWER').value;
	var show_row = document.getElementById('filterROW').value;
	var show_bin = document.getElementById('filterBIN').value;
	var show_part_num = document.getElementById('filterPART_NUM').value;
	var show_qty = document.getElementById('filterQTY').value;

	//alert(sort_fieldC);
	//alert(sort_dir_flagC);

	if (sort_dir_flagC == 'desc') {
		sort_dir_flagC = 'asc';
		document.getElementById('sortDirFlag').value = 'asc';
	} else {
		sort_dir_flagC = 'desc';
		document.getElementById('sortDirFlag').value = 'desc';
	}
	document.getElementById('sortField').value = sort_fieldC;



	//$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{ action: "show", show_mfg: show_mfg, show_model: show_model, show_model_num: show_model_num, show_drawer: show_drawer,
		show_row: show_row, show_bin: show_bin, show_part_num: show_part_num, show_qty: show_qty, sort_fieldC: sort_fieldC, sort_dir_flagC: sort_dir_flagC },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");

}


function showEditField(field_id){
	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/partsinv.php";
	var action = "showedit";

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function showEditStatusField(id){
	var sel_status = document.getElementById(id).value;
	var url = "ajax/partsinv.php";
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

function cancelEditField(field_id, field_value){
	var url = "ajax/partsinv.php";	
	var action = "canceledit";

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/partsinv.php";	
	var action = "saveedit";

	//alert(field_id.substring(0,3));

	//validate new values
	if (new_value.length > 50) {
		alert("Value too long. Must be less than 50 characters");
		return;
	}
	
	if (new_value.match('[^A-Za-z0-9. -]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
		return;
	}

	if (field_id.substring(0,3) == 'QTY' || field_id.substring(0,3) == 'BIN' || field_id.substring(0,3) == 'ROW'|| field_id.substring(0,3) == 'DRA' ) {
		if (!IsInteger(new_value)) {
			alert("Quantity must be an integer!");
			return; 
		}
	}

	if(field_id.substring(0,3) == 'BIN' || field_id.substring(0,3) == 'ROW' || field_id.substring(0,3) == 'DRa' || field_id.substring(0,3) == 'QTY' ) {
		if (new_value.length > 10) {
			alert('No more than 10 characters in Bin, Row, Drawer, or Quantity!');
			return;
		}
	}

	

	$.post(url,{ action: action, field_id: field_id, field_value: new_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}



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
	var url = "ajax/partsinv.php";
	var action = "insert_record";
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
	/*if(headType.length > 50){
		alert("Head type must be less than 50 characters");
		return;
	}//end if
	if(headSn.length >50){
		alert("Head serial number must be less than 50 characters");
		return;
	}//end if
	if(modelNum.length > 50){
		alert("Head model number must be less than 50 characters");
		return;
	}//end if
	/*if(IsInteger(assetNum) == false){
		alert("Asset Number must be an integer!");
		return;
	}//end if  

	*/ 

	$('#btnInsertRecord').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		action: action,
		manf: manf,
		//status: status,
		model: model,
		modelNum: modelNum,
		drawer: drawer,
		row: row,
		bin: bin,
		//assetNum: assetNum,
		quantity: quantity,
		partNum: partNum,
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
	var url = "ajax/partsinv.php";	
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
	var url = "ajax/partsinv.php";
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
}//end refresh records

/*function saveStatus(field_id){
	var url = "ajax/partsinv.php";
	//var show_status = document.getElementById('show_status').value;
	var field_value = document.getElementById(field_id).innerHTML;
	var newStatus = document.getElementById(field_id).value;

	if(newStatus != show_status){//confirm change of status
		if(confirm("Are you sure you want to change the status of this inventory part?")){
			alert("SAVED!");
		}//end if
		else{
			document.getElementById(field_id).selectedIndex = show_status;
			return;
		}//end else
	}//end if

	$.post(url,{ action: "saveStatus", field_id: field_id, field_value: field_value, newStatus: newStatus, show_status: show_status },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json"); 

	function refreshPage(){
    window.location.reload();
} 


} //end saveStatus


function sortBy(sort_field){
	var show_status = document.getElementById('show_status').value;
	var sort_dir_flag = document.getElementById('sortDirFlag').value;
	//var show_team = document.getElementById('filterTeam').value;
	var sort_dir = "";
	var url = "ajax/partsinv.php";
	if (sort_dir_flag == '0') {
		sort_dir = 'asc';
		document.getElementById('sortDirFlag').value = '1';
	} else {
		sort_dir = 'desc';
		document.getElementById('sortDirFlag').value = '0';
	}

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: "show", sort_field: sort_field, sort_dir: sort_dir },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

*/