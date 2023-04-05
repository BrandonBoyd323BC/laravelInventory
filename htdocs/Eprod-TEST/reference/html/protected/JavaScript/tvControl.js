function doOnLoad() {
	var url = "ajax/tvControl.php";
	var action = "doOnLoad";

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}


function tvPowerSliderChanged(sliderID,tv_rowid){
	var url = "ajax/tvControl.php";
	var sliderStatus = document.getElementById(sliderID).checked;

	if (sliderStatus == true) {
		action = "cecTurnOnTV";
	} else {
		action = "cecTurnOffTV";
	}

	$.post(url,{ action: action, tv_rowid: tv_rowid},
	function(data){
		//$('#dataDiv').html(data.returnValue);
		//showTeamLights(team);
	}, "json");
}

/*
function selTvPLIdChanged(tv_rowid){
	//SHOW THE SAVE BUTTON
	document.getElementById("saveSelTvPLId_"+tv_rowid).style.display = "inline-block";
	//document.getElementById("saveSelTvPLId_"+tv_rowid).style.display = "none";
}

function saveSelTvPLId(tv_rowid){
	var url = "ajax/tvControl.php";
	var action = "saveSelTvPLId";
	var selectedPLID = document.getElementById("selTvPLId_"+tv_rowid).value;

	$.post(url,{ action: action, tv_rowid: tv_rowid, selectedPLID: selectedPLID},
	function(data){
		document.getElementById("saveSelTvPLId_"+tv_rowid).style.display = "none";
	}, "json");
}

*/







function getTvIdContent(tvId='UNDEFINED') {
	var url = "ajax/tvControl.php";
	
	if (tvId == 'UNDEFINED') {
		alert("TVID not set");
		return;
	}

	$('#dataDiv').html("<img src='images/loading01.gif' />");
	alert(tvId);

	$.post(url,{ action: 'getTvIdContent', tvId: tvId },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");

}



function initViz() {
	var containerDiv = document.getElementById("vizContainer"),
	url = "https://AS1/views/WorkDistribution/WorkDistribution";

	var viz = new tableau.Viz(containerDiv, url); 
}


function goFullscreen() {
	var element = document.getElementById('player');
	if (element.mozRequestFullScreen) {
		element.mozRequestFullScreen();
	} else if (element.webkitRequestFullScreen) {
		element.webkitRequestFullScreen();
	}  
}







