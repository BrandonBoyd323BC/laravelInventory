
function subValue(teamNo) {
	showTeamLights(teamNo);
	//timedRefresh(10000);
}


//function showTeamLights(team, divDisplay){
function showTeamLights(team){
	action = 'showTeamLights';
	//$.post("../ajax/lights.php",{ action: action, team: team, divDisplay: divDisplay },
	$.post("../ajax/lights.php",{ action: action, team: team},
	function(data){
		$('#lightsDiv').html(data.returnValue);
	}, "json");
}


function sliderChanged(sliderID,team){
	var sliderStatus = document.getElementById(sliderID).checked;
	//var divDisplay = "none";
    //document.getElementById("divDisplay").style.display = "none";

	if (sliderStatus == true) {
		action = "insertLightAlert";
		if (sliderID == 'RED') {
			document.getElementById("divMaint").style.display = "inline-block";
		}
		if (sliderID == 'YELLOW') {
			document.getElementById("divQA").style.display = "inline-block";
		}
		if (sliderID == 'BLUE') {
			document.getElementById("divOrdPrep").style.display = "inline-block";
		}
		if (sliderID == 'PURPLE') {
			document.getElementById("divRnD").style.display = "inline-block";
		}		
	} else {
		action = "clearLightAlert";
		if (sliderID == 'RED') {
			document.getElementById("divMaint").style.display = "none";
			//divDisplay = "none";
		}
		if (sliderID == 'YELLOW') {
			document.getElementById("divQA").style.display = "none";
			//divDisplay = "none";
		}
		if (sliderID == 'BLUE') {
			document.getElementById("divOrdPrep").style.display = "none";
			//divDisplay = "none";
		}
		if (sliderID == 'PURPLE') {
			document.getElementById("divRnD").style.display = "none";
			//divDisplay = "none";
		}
	}

	//$.post("../ajax/lights.php",{ action: action, team: team, category: sliderID, divDisplay: divDisplay },
	$.post("../ajax/lights.php",{ action: action, team: team, category: sliderID },
	function(data){
		$('#lightsDiv').html(data.returnValue);
		//showTeamLights(team, divDisplay);
		showTeamLights(team);
	}, "json");
}

/*
function dashsubValue(a_team) {
	//alert(a_team);
	
	var jsSplitResult = a_team.split("~");

	var i = 0;
	for(i=0; i < jsSplitResult.length; i++){
		if (jsSplitResult[i] != '0') {
			//alert(jsSplitResult[i]);
			dashsendValue(jsSplitResult[i]);
		}
		
	}

	
	
	//timedRefresh(10000);
}


function dashsendValue(str){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	
	//alert(df);
	//alert(dt);
	
	$.post("../protected/ajax/realtime.php",{ sendValue: str, from: 'dash', df: df, dt: dt },
	//$.post("../protected/ajax/realtime.php",{ sendValue: str, from: 'dash'},
	function(data){
		$('#div_' + str).html(data.returnValue);
	}, "json");
}

*/

function doOnLoads() {
	window.resizeTo(800,800);
	focus();selTeam.focus();
}


function searchKeyPress(e) {
	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById('submit').click();
	}
}

//function timedRefresh(timeoutPeriod) {
//	setTimeout('subValue(document.getElementById(\"selTeam\").value)',timeoutPeriod);
//}

function goToActivity(Team) {
	document.getElementById("redir_" + Team).submit();
}

function closeDiv(div) {
	//alert(div);
	
	var r=confirm("Remove table from view?");
	
	if (r==true) {
	
		$.post("../ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
	
}

function nextOnDash(so,sufx) {
	$('#op_'+so).keypress(function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#op_'+sufx).focus();
    	}
	});
}

function sufxEntered(){
	var url = "ajax/lights.php";
	var loc = '10';
	var action = 'getCompsBySO';
	var team = document.getElementById('selTeam').value;
	var id_so = document.getElementById('op_so').value;
	var sufx = document.getElementById('op_sufx').value;

	if (sufx.length == 3) {
		$.post("../ajax/lights.php",{ action: action, team: team, id_so: id_so, sufx: sufx, loc: loc},
		function(data){
			$('#op_missingTD').html(data.returnValue);
		}, "json");	
	}
}//end sufxEntered


/*
function hideElements(){//appers if the blue button is switched on due to the need of order prep/missing pieces

	document.getElementById('selTeam').style.display = "none";
	document.getElementById('so').style.display = "none";
	document.getElementById('missingItem').style.display = "none";
	document.getElementById('qtyMissing').style.display = "none";
	document.getElementById('comments_TXT').style.display = "none";
}
*/
function addMaintRecord(){
	var team = document.getElementById('selTeam').value;
	var id_mach_rowid = document.getElementById('selMachID').value;
	var action = 'addNewMaintReq';
	var comment = document.getElementById('comments_TXT').value;

	if (team == '') {
		alert("No Team Selected. Please select a team.");
		return;
	}

	if (comment.length > 200) {
		alert("Comment too long. Must be less than 200 characters");
		return;
	}
	
	if (comment.match('[^A-Za-z0-9. -]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
		return;
	}

	//if (miss_item.match('[^A-Za-z0-9. -]')) {
	//	alert("Invalid characters in Missing Item Number.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
	//	return;
	//}
	

	$.post("../ajax/lights.php",{ action: action, team: team, id_mach_rowid: id_mach_rowid, comment: comment },
	function(data){
		$('#openMaintReqDiv').html(data.returnValue);
	}, "json");	


	//reset input form after insert of new record
	document.getElementById('selMachID').selectedIndex = 0;
	document.getElementById('comments_TXT').value = '';	

}

function addOrdPrepRecord(){
	var team = document.getElementById('selTeam').value;
	var id_so = document.getElementById('op_so').value;
	var sufx = document.getElementById('op_sufx').value;
	var miss_item = document.getElementById('op_missingItem').value;
	var qty_missing = document.getElementById('op_qtyMissing').value;
	var loc = '10';
	var action = 'addNewOrdPrepReq';
	var comment = document.getElementById('op_comments_TXT').value;
	
	if (team == '') {
		alert("No Team Selected. Please select a team.");
		return;
	}

	if (comment.length > 200) {
		alert("Comment too long. Must be less than 200 characters");
		return;
	}
	
	if (comment.match('[^A-Za-z0-9. -]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
		return;
	}

	//if (miss_item.match('[^A-Za-z0-9. -]')) {
	//	alert("Invalid characters in Missing Item Number.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
	//	return;
	//}
	
	if (qty_missing.match('[^0-9]')) {
		alert("Invalid characters in Qty Missing.");
		return;
	}	

	$.post("../ajax/lights.php",{ action: action, team: team, id_so: id_so, loc: loc, miss_item: miss_item, qty_missing: qty_missing, comment: comment, sufx: sufx },
	function(data){
		$('#openOrdPrepReqDiv').html(data.returnValue);
	}, "json");	

}




function maintCheckboxCompleteChange(rowid){
	var isChecked = document.getElementById("maintCheckboxComplete__"+rowid).checked;
	
	if (isChecked) {
		document.getElementById("buttonMaintSaveCheckboxComplete__"+rowid).style.display = "inline-block";
	} else {
		document.getElementById("buttonMaintSaveCheckboxComplete__"+rowid).style.display = "none";
	}
}

function ordPrepCheckboxCompleteChange(rowid){
	var isChecked = document.getElementById("ordPrepCheckboxComplete__"+rowid).checked;

	if (isChecked) {
		document.getElementById("buttonOrdPrepSaveCheckboxComplete__"+rowid).style.display = "inline-block";
	} else {
		document.getElementById("buttonOrdPrepSaveCheckboxComplete__"+rowid).style.display = "none";
	}
}



function saveCompleteMaint(rowid) {
	var action = 'saveCompleteMaint';
	var team = document.getElementById('selTeam').value;
	var isChecked = document.getElementById("maintCheckboxComplete__"+rowid).checked;
	
	if (isChecked) {
		$.post("../ajax/lights.php",{ action: action, maintReq_rowid: rowid },
		function(data){
			//$('#openReqDiv').html(data.returnValue);
			document.getElementById("buttonMaintSaveCheckboxComplete__"+rowid).style.display = "none";
			showTeamLights(team);
		}, "json");	
	}
}


function saveCompleteOrdPrep(rowid) {
	var action = 'saveCompleteOrdPrep';
	var team = document.getElementById('selTeam').value;
	var isChecked = document.getElementById("ordPrepCheckboxComplete__"+rowid).checked;
	
	if (isChecked) {
		$.post("../ajax/lights.php",{ action: action, opm_rowid: rowid },
		function(data){
			//$('#openReqDiv').html(data.returnValue);
			document.getElementById("buttonOrdPrepSaveCheckboxComplete__"+rowid).style.display = "none";
			showTeamLights(team);
		}, "json");	
	}
}


function refreshOpenMaintReqs(team) {
	var action = 'refreshOpenMaintReqs';

	$.post("../ajax/lights.php",{ action: action, team: team },
	function(data){
		$('#openMaintReqDiv').html(data.returnValue);
	}, "json");		
}
