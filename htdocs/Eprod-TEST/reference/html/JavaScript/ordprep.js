
function subValue(teamNo) {
	sendValue(teamNo);
	//timedRefresh(10000);
}





function doOnLoads() {
	window.resizeTo(600,600);
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

function searchTeamOpenReqs(e) {
	var team = document.getElementById('selTeam').value;
	var action = 'getOpenReqs';
	
	// look for window.event in case event isn't passed in
	//if (window.event) { e = window.event; }
	//if (e.keyCode == 13)
	//{
		$.post("../ajax/ordprep.php",{ action: action, team: team},
		function(data){
			$('#openReqDiv').html(data.returnValue);
		}, "json");	
	//}
	//focus();id_so_TXT.focus();
}


function searchCompBySO() {
	var team = document.getElementById('selTeam').value;
	var id_so = document.getElementById('id_so_TXT').value;
	var loc = '10';
	var action = 'getCompsBySO';

	$.post("../ajax/ordprep.php",{ action: action, team: team, id_so: id_so, loc: loc},
	function(data){
		$('#missingTD').html(data.returnValue);
	}, "json");	
	//focus();missingItem.focus();
}


function nextOnDash(so,sufx) {
	$('#'+so).keypress(function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#'+sufx).focus();
    	}
	});
}

function sufxEntered(){
	var url = "ajax/ordprep.php";
	var loc = '10';
	var action = 'getCompsBySO';
	var team = document.getElementById('selTeam').value;
	var id_so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;

	if (sufx.length == 3) {
		$.post("../ajax/ordprep.php",{ action: action, team: team, id_so: id_so, sufx: sufx, loc: loc},
		function(data){
			$('#missingTD').html(data.returnValue);
		}, "json");	
	}
}


function addRecord() {
	var team = document.getElementById('selTeam').value;
	var id_so = document.getElementById('id_so_TXT').value;
	var miss_item = document.getElementById('missingItem').value;
	var qty_missing = document.getElementById('qtyMissing').value;
	var loc = '10';
	var action = 'addNewReq';
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

	if (comment.length > 30) {
		alert("Missing Item Number too long. Must be less than 30 characters");
		return;
	}

	if (miss_item.match('[^A-Za-z0-9. -]')) {
		alert("Invalid characters in Missing Item Number.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
		return;
	}
	
	if (qty_missing.match('[^0-9]')) {
		alert("Invalid characters in Qty Missing.");
		return;
	}	

	$.post("../ajax/ordprep.php",{ action: action, team: team, id_so: id_so, loc: loc, miss_item: miss_item, qty_missing: qty_missing, comment: comment},
	function(data){
		$('#openReqDiv').html(data.returnValue);
	}, "json");	

}




function timedRefresh(timeoutPeriod) {
	setTimeout('subValue(document.getElementById(\"selTeam\").value)',timeoutPeriod);
}

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
