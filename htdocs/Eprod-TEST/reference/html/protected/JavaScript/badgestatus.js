
var popupStatus = 0; 

function closeDiv(div) {
	var r=confirm("Remove table from view?");

	if (r==true) {
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
}

function showCrewSizeEdit(this_id,id_badge,curr_val) {
	var field_value = document.getElementById(this_id).innerHTML;
	var url = "ajax/badgestatus.php";
	var action = "showCrewSizeEdit";

	$.post(url,{ action: action, par_id: this_id, id_badge: id_badge, curr_val: curr_val },
	function(data){
		$('#'+this_id).html(data.returnValue);
	}, "json");
}

function saveCrewSizeEdit(par_id, id_badge) {
	var new_val = document.getElementById('textbox_EditCrewSize__'+id_badge).value;
	var url = "ajax/badgestatus.php";
	var action = "saveCrewSizeEdit";

	if (!IsInteger(new_val)) {
		alert("Integer Values Only!");
		return;
	}

	$.post(url,{ action: action, id_badge: id_badge, new_val: new_val },
	function(data){
		$(par_id).html(data.returnValue);
	}, "json");
}


function showJobClassEdit(this_id,id_badge,curr_val) {
	var field_value = document.getElementById(this_id).innerHTML;
	var url = "ajax/badgestatus.php";
	var action = "showJobClassEdit";
	
	$.post(url,{ action: action, par_id: this_id, id_badge: id_badge, curr_val: curr_val },
	function(data){
		$('#'+this_id).html(data.returnValue);
	}, "json");
}

function saveJobClassEdit(par_id, id_badge) {
	var new_val = document.getElementById('selJobClass__'+id_badge).value;
	var url = "ajax/badgestatus.php";
	var action = "saveJobClassEdit";

	$.post(url,{ action: action, id_badge: id_badge, new_val: new_val },
	function(data){
		$(par_id).html(data.returnValue);
	}, "json");
}

function IsInteger(strString) {
	var strValidChars = "0123456789-";
	var strChar;
	var blnResult = true;

	if (strString.length == 0) {
		return false;
	} 

	//  test strString consists of valid characters listed above
	for (i = 0; i < strString.length && blnResult == true; i++) {
		strChar = strString.charAt(i);

		if (strValidChars.indexOf(strChar) == -1) {
     		blnResult = false;
		}
	}
	return blnResult;
}
