
function doOnLoads() {
	numRecsChange();
}

function numRecsChange(){
	var url = "ajax/maintlog.php";
	var action = "numRecsChange";

	var location = document.getElementById('sel_Location').value;
	var mechanic = document.getElementById('sel_filterMechanic').value;
	var num_recs = document.getElementById('num_recs').value;
	var team = document.getElementById('sel_filterTeam').value;
	var machID = document.getElementById('sel_filterMachID').value;
	var maintCode = document.getElementById('sel_filterMaintCode').value;
	var maintResCode = document.getElementById('sel_filterMaintResCode').value;

	$.post(url,{ action: action, location: location, mechanic: mechanic, num_recs: num_recs, team: team, machID: machID, maintCode: maintCode, maintResCode: maintResCode },
		function(data){
			$('#dataDiv').html(data.returnValue);
		}, "json");
}

function sendValue(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var mechanic = document.getElementById('mechanic').value;
	var url = "ajax/maintlog.php";	

	if (df > dt) {
		alert('Invalid Date Range');
		return;
	}
	
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ df: df, dt: dt, unit: unit, orderby: orderby },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
}

function sendAddValue(){
	var url = "ajax/maintlog.php";	
	var maintReqRowid = document.getElementById('hidden_MaintReqRowid').value;
	var location = document.getElementById('sel_Location').value;
	var mechanic = document.getElementById('mechanic_add').value;
	var dw = document.getElementById('dw').value;
	var team = document.getElementById('team').value;
	//var employee = document.getElementById('employee').value;
	var machine_id = document.getElementById('machine_id').value;
	var maint_code = document.getElementById('maint_code').value
	var maint_res_code = document.getElementById('maint_res_code').value;
	var mins_down = document.getElementById('mins_down').value;
	var comments = document.getElementById('comments').value;
	var action = "insertRecord";


	if (mechanic == "SELECT") {
		alert("Invalid Mechanic selection!");
		return;
	}

	if (!IsInteger(machine_id)) {
		alert("Invalid Machine ID!");
		return;
	}

	if (!IsInteger(mins_down)) {
		alert("Minutes Down must be an integer!");
		return;
		if(mins_down.length > 18){
			alert("Minutes down must be less than 18 digits");
			return;
		}
	}

	if(comments.length > 100){
		alert("Comments must be less than 100 characters total!");
		return;
	}
	
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, maintReqRowid: maintReqRowid, location: location, mechanic: mechanic, dw: dw, team: team, 
		machine_id: machine_id, maint_code: maint_code, maint_res_code: maint_res_code, mins_down: mins_down, comments: comments },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	//reset input form after insert of new record
	document.getElementById('hidden_MaintReqRowid').value = '';
	document.getElementById('mechanic_add').selectedIndex = 0;
	document.getElementById('team').value = '      000';
	document.getElementById('machine_id').value = 'SELECT';
	document.getElementById('maint_code').selectedIndex = 0;
	document.getElementById('maint_res_code').selectedIndex = 0;
	document.getElementById('mins_down').value = '';
	document.getElementById('comments').value = '';

}

function showLocationChange(){
	var location = document.getElementById('sel_Location').value;
	var url = "ajax/maintlog.php";
	var action = "location_change";

	$('#team').html("<option value='LOADING'>LOADING</option>");

	$.post(url,{ action: action, location: location },
	function(data){
		$('#team').html(data.returnValue);
		showTeamChange();
		numRecsChange()
	}, "json");

}

function showTeamChange(){
	var team = document.getElementById('team').value;
	var location = document.getElementById('sel_Location').value;
	var url = "ajax/maintlog.php";
	var action = "team_change";

	$('#machine_id').html("<option value='LOADING'>LOADING</option>");

	$.post(url,{ action: action, location: location, team_change: team },
	function(data){
		$('#machine_id').html(data.returnValue);
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
	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/maintlog.php";
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
	var url = "ajax/maintlog.php";

	if (sel_status.indexOf("*") == 0) {
		alert("Same as in DB");
	} else {
		alert("Show button");
	}

	return;

	$.post(url,{  },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

function cancelEditField(field_id, field_value){
	var url = "ajax/maintlog.php";	
	var action = "canceledit";

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/maintlog.php";	
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
	var url = "ajax/maintlog.php";	
	var action = "deleteRecord";

	if (r==true) {
		$.post(url,{action: action, deleteRecord: 1, rowid: rowid },
		function(data){
			$('#delete_' + rowid).html(data.returnValue);
		}, "json");
	}
}
