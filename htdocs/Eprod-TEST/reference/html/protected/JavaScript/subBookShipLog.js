function doOnLoads() {
	//numRecsChange();
	focus();selSubsidiary.focus();
}



function selSubsidiaryChange() {
	var action = "subsidiaryChanged";
	var url = "ajax/subBookShipLog.php";
	var subsidiary = document.getElementById("selSubsidiary").value;
	var num_recs = document.getElementById('num_recs').value;

	$.post(url,{ action: action, subsidiary: subsidiary, num_recs: num_recs },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}





function numRecsChange() {
	var action = "numRecsChange";
	var url = "ajax/subBookShipLog.php";
	var num_recs = document.getElementById('num_recs').value;
	var subsidiary = document.getElementById("selSubsidiary").value;

	//if (subsidiary == '') {
	//	subsidiary = 'ALL';
	//	document.getElementById('searchSO').value = subsidiary;
	//}

	$.post(url,{ action: action, subsidiary: subsidiary, num_recs: num_recs },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}



function showEditField(field_id){
	var field_value = document.getElementById(field_id).innerHTML;
	var element_value = document.getElementById(field_id).value;
	//var field_value = document.getElementById(field_id).value;
	var url = "ajax/subBookShipLog.php";
	var action = "showedit";

	//alert("field_id: "+field_id);
	//alert("field_value: "+field_value);
	//alert("element_value: "+element_value);

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function cancelEditField(field_id, field_value){
	var url = "ajax/subBookShipLog.php";	
	var action = "canceledit";

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/subBookShipLog.php";	
	var action = "saveedit";

	//validate new values
	if (new_value.length > 50) {
		alert("Value too long. Must be less than 50 characters");
		return;
	}
	
	if (new_value.match('[^A-Za-z0-9. -]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
		return;
	}
	function validate(new_value){
		var str = new_value;
		var res = str.split("__");
		var compare = res[0];

		if(compare == 'ID_MACH'){
			if(IsInteger(compare) == false){
				alert("Machine ID must be an integer!");
				return;
			}//end if
		}//end if

		if(compare == 'ASSET_NUM'){
			if(IsInteger(compare) == false){
				alert("Asset number must be an integer!");
				return;
			}//end if
		}//end if

		if(compare == 'ID_CLUSTER'){
			if(compare.length > 9){
				alert("Cluster ID must be less than 9 characters!");
				return;
			}//end if
		}//end if
		if(compare == 'ID_BADGE_TEAM'){
			if(compare.length > 30){
				alert("Cluster ID must be less than 30 characters!");
				return;
			}//end if
		}//end if

	}//end validate

	$.post(url,{ action: action, field_id: field_id, field_value: new_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}




function sendAddValue(){
	var action = "sendAddValue";
	var url = "ajax/subBookShipLog.php";	
	var subsidiary = document.getElementById('selSubsidiary').value;
	var date_log = document.getElementById('date_log').value;	
	var sales = document.getElementById('sales').value;
	var ship = document.getElementById('ship').value;
	var backlog = document.getElementById('backlog').value;
	var num_recs = document.getElementById('num_recs').value;
	
	if(subsidiary == '') {
		alert("Subsidiary NOT Selected!");
		return;
	}

	$('#dw_submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		action: action,
		subsidiary: subsidiary,
		date_log: date_log, 
		sales: sales, 
		ship: ship, 
		backlog: backlog,
		num_recs: num_recs
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#dw_submit').attr("disabled", false);
		document.getElementById('sales').value = "";
		document.getElementById('ship').value = "";
		document.getElementById('backlog').value = "";
		//$('#Sub_Form')[0].reset();
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

function IsNumeric(strString) {
	var strValidChars = "0123456789.";
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

