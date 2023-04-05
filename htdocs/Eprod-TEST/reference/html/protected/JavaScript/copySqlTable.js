
function copyTableButtonClick(tableName){
	var url = "ajax/copySqlTable.php";

	$('#button_'+tableName).attr("disabled", true);
	//$('#dataAddDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ tableName: tableName },
	function(data){
		//$('#dataAddDiv').html(data.returnValue);
		$('#button_'+tableName).attr("disabled", false);
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
	var machID = document.getElementById('add_ID_MACH').value;
	var status = document.getElementById('add_STATUS').value;
	var badge = document.getElementById('add_ID_BADGE_TEAM').value;
	var headBrand = document.getElementById('add_HEAD_BRAND').value;
	var headType = document.getElementById('add_HEAD_TYPE').value;
	var headSn = document.getElementById('add_HEAD_SN').value;
	var modelNum = document.getElementById('add_MODEL_NUM').value;
	var assetNum = document.getElementById('add_ASSET_NUM').value;
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
	if(IsInteger(assetNum) == false){
		alert("Asset Number must be an integer!");
		return;
	}//end if

	$('#btnInsertRecord').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		action: action,
		machID: machID,
		status: status,
		badge: badge,
		headBrand: headBrand,
		headType: headType,
		headSn: headSn,
		modelNum: modelNum,
		assetNum: assetNum,
		show_status: show_status,
		comments: comments
	},

	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnInsertRecord').attr("disabled", false);	
		}, "json");

}//end insertnewrecord

function refreshRecords(){
	var url = "ajax/machinv.php";
	var show_status = document.getElementById('show_status').value;
	var action = "refresh_record";

	$.post(url,{
		action: action,
		refreshRec: 1,
		show_status: show_status
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		//$('#btnInsertRecord').attr("disabled", false);	
		}, "json");
}//end refresh records

function saveStatus(field_id){
	var url = "ajax/machinv.php";
	var show_status = document.getElementById('show_status').value;
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

	$.post(url,{ action: "saveStatus", field_id: field_id, field_value: field_value, newStatus: newStatus, show_status: show_status },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}//end saveStatus