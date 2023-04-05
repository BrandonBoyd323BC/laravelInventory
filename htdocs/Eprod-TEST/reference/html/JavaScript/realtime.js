
function subValue(teamNo) {
	sendValue(teamNo);
	//timedRefresh(10000);
}


function sendValue(str){
	//var df = document.getElementById('df').value;
	//var dt = document.getElementById('dt').value;
	//var zh = document.getElementById('zh').value;
	//$.post("../ajax/realtime.php",{ sendValue: str, from: 'rt', df: df, dt: dt, zh: zh },
	$.post("../ajax/realtime.php",{ sendValue: str },
	function(data){
		$('#scoreDiv').html(data.returnValue);
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
	window.resizeTo(400,600);
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
