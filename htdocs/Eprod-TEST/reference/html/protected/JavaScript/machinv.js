function showLocationChange(){
	var show_location = document.getElementById('show_location').value;
	var show_status = document.getElementById('show_status').value;
	var show_team = document.getElementById('filterTeam').value;
	var url = "ajax/machinv.php";

	$('#td_filterTeam').html("<img src='images/loading01.gif' />");

	$.post(url,{ 
		action: "show_location_change", 
		show_location: show_location, 
		show_status: show_status, 
		show_team: show_team 
	},
	function(data){
		$('#td_filterTeam').html(data.returnValue);
		showStatusChange();
	}, "json");


}


function showStatusChange(){
	var show_location = document.getElementById('show_location').value;
	var show_status = document.getElementById('show_status').value;
	var show_team = document.getElementById('filterTeam').value;
	var url = "ajax/machinv.php";

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ 
		action: "show_status", 
		show_location: show_location, 
		show_status: show_status, 
		show_team: show_team 
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}


function getAddTeamDropdownList(id){
	var add_location = document.getElementById(id).value;
	//var add_location = document.getElementById('add_location').value;
	var url = "ajax/machinv.php";

	$('#td_addTeamDropdownList').html("<img src='images/loading01.gif' />");

	$.post(url,{ 
		action: "show_add_team_dropdown_list", 
		add_location: add_location
	},
	function(data){
		$('#td_addTeamDropdownList').html(data.returnValue);
		//showStatusChange();
	}, "json");


}



function checkIfNewTeamSelected(id){
	var val = document.getElementById(id).value;
	var url = "ajax/machinv.php";

	if (val == 'NEW') {
		$.post(url,{ 
			action: "show_add_new_team_textbox", 
		},
		function(data){
			$('#td_addTeamDropdownList').html(data.returnValue);
			//showStatusChange();
		}, "json");
	}
}

function sortBy(sort_field){
	var show_status = document.getElementById('show_status').value;
	var show_location = document.getElementById('show_location').value;
	var sort_dir_flag = document.getElementById('sortDirFlag').value;
	var show_team = document.getElementById('filterTeam').value;
	var sort_dir = "";
	var url = "ajax/machinv.php";

	if (sort_dir_flag == '0') {
		sort_dir = 'asc';
		document.getElementById('sortDirFlag').value = '1';
	} else {
		sort_dir = 'desc';
		document.getElementById('sortDirFlag').value = '0';
	}


	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ 
		action: "show_status", 
		show_status: show_status, 
		show_location: show_location, 
		sort_field: sort_field, 
		sort_dir: sort_dir, 
		show_team: show_team 
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

function showEditSelect(field_id){
	var field_value = document.getElementById(field_id).innerHTML;
	var element_value = document.getElementById(field_id).value;
	//var field_value = document.getElementById(field_id).value;
	var url = "ajax/machinv.php";

	alert("field_id: "+field_id);
	alert("field_value: "+field_value);
	alert("element_value: "+element_value);

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ 
		action: "show_edit_select", 
		field_id: field_id, 
		field_value: field_value 
	},
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function showEditField(field_id){
	var field_value = document.getElementById(field_id).innerHTML;
	var element_value = document.getElementById(field_id).value;
	//var field_value = document.getElementById(field_id).value;
	var url = "ajax/machinv.php";
	var action = "showedit";

	//alert("field_id: "+field_id);
	//alert("field_value: "+field_value);
	//alert("element_value: "+element_value);

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ 
		action: action, 
		field_id: field_id, 
		field_value: field_value 
	},
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function showEditStatusField(id){
	var sel_status = document.getElementById(id).value;
	var url = "ajax/machinv.php";
	//var action = "";

	if (sel_status.indexOf("*") == 0) {
		alert("Same as in DB");
	} else {
		alert("Show button");
	}

	return;

	$.post(url,{ 
		action: action, 
		show_status: show_status 
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

function cancelEditField(field_id, field_value){
	var url = "ajax/machinv.php";	
	var action = "canceledit";

	$.post(url,{ 
		action: action, 
		field_id: field_id, 
		field_value: field_value 
	},
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/machinv.php";	
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

	$.post(url,{ 
		action: action, 
		field_id: field_id, 
		field_value: new_value 
	},
	function(data){
		$('#'+field_id).html(data.returnValue);
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
	var url = "ajax/machinv.php";
	var action = "insert_record";
	var show_status = document.getElementById('show_status').value;
	var show_location = document.getElementById('show_location').value;
	var show_team = document.getElementById('filterTeam').value;
	var machID = document.getElementById('add_ID_MACH').value;
	var location = document.getElementById('add_LOCATION').value;
	var status = document.getElementById('add_STATUS').value;
	var badge = document.getElementById('add_ID_BADGE_TEAM').value;
	var headBrand = document.getElementById('add_HEAD_BRAND').value;
	var headType = document.getElementById('add_HEAD_TYPE').value;
	var headSn = document.getElementById('add_HEAD_SN').value;
	var modelNum = document.getElementById('add_MODEL_NUM').value;
	var priority = document.getElementById('add_PRIORITY').value;
	var comments = document.getElementById('add_COMMENTS').value;

	//Validate Data inputs to match column length and data type
	if(IsInteger(machID) == false){
		alert("Machine ID must be an integer");
		return;
	}//end if
	if(badge.length > 30){
		alert("Team Badge Must be less than 30 characters");
		return;
	}//end if
	if(headBrand.length > 50){
		alert("Head brand must be less than 50 characters");
		return;
	}//end 
	if(headType.length > 50){
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
	}//end if   */

	$('#btnInsertRecord').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		action: action,
		machID: machID,
		location: location,
		status: status,
		badge: badge,
		headBrand: headBrand,
		headType: headType,
		headSn: headSn,
		modelNum: modelNum,
		priority: priority,
		comments: comments,
		show_status: show_status,
		show_location: show_location,
		show_team: show_team
	},

	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnInsertRecord').attr("disabled", false);	
		}, "json");

}//end insertnewrecord

function refreshRecords(){
	var url = "ajax/machinv.php";
	var show_status = document.getElementById('show_status').value;
	var show_location = document.getElementById('show_location').value;
	var filterTeam = document.getElementById('filterTeam').value;
	var action = "refresh_record";

	$.post(url,{
		action: action,
		refreshRec: 1,
		show_status: show_status,
		show_location: show_location,
		filterTeam: filterTeam
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		//$('#btnInsertRecord').attr("disabled", false);	
		}, "json");
}//end refresh records

function saveStatus(field_id){
	var url = "ajax/machinv.php";
	var show_status = document.getElementById('show_status').value;
	var show_location = document.getElementById('show_location').value;
	var show_team = document.getElementById('filterTeam').value;
	var field_value = document.getElementById(field_id).innerHTML;
	var newStatus = document.getElementById(field_id).value;

	if(newStatus != show_status){//confirm change of status
		if(confirm("Are you sure you want to change the status of this machine?")){
			alert("SAVED!");
		}//end if
		else{
			document.getElementById(field_id).selectedIndex = show_status;
			return;
		}//end else
	}//end if

	$.post(url,{ 
		action: "saveStatus", 
		field_id: field_id, 
		field_value: field_value, 
		newStatus: newStatus, 
		show_status: show_status, 
		show_location: show_location, 
		show_team: show_team
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}//end saveStatus


function savePriority(field_id){
	var url = "ajax/machinv.php";
	var show_status = document.getElementById('show_status').value;
	var show_location = document.getElementById('show_location').value;
	var show_team = document.getElementById('filterTeam').value;
	var field_value = document.getElementById(field_id).innerHTML;
	var newPriority = document.getElementById(field_id).value;

	//if(confirm("Are you sure you want to change the priority of this machine?")){
	//	alert("SAVED!");
	//} else {
	//	document.getElementById(field_id).selectedIndex = show_priority;
	//	return;
	//}

	$.post(url,{ 
		action: "savePriority", 
		field_id: field_id, 
		field_value: field_value, 
		newPriority: newPriority, 
		show_status: show_status, 
		show_location: show_location, 
		show_team: show_team
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}//end savePriority



function saveLocation(field_id){
	var url = "ajax/machinv.php";
	var show_status = document.getElementById('show_status').value;
	var show_location = document.getElementById('show_location').value;
	var show_team = document.getElementById('filterTeam').value;
	var field_value = document.getElementById(field_id).innerHTML;
	var newLocation = document.getElementById(field_id).value;

	//if(confirm("Are you sure you want to change the priority of this machine?")){
	//	alert("SAVED!");
	//} else {
	//	document.getElementById(field_id).selectedIndex = show_priority;
	//	return;
	//}

	$.post(url,{ 
		action: "saveLocation", 
		field_id: field_id, 
		field_value: field_value, 
		newLocation: newLocation, 
		show_status: show_status, 
		show_location: show_location, 
		show_team: show_team
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}//end savePriority