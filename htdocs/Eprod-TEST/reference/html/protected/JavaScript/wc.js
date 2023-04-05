

function sendValue(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	
	if (df > dt) {
		alert('Invalid Date Range');
		return;
	
	}
	

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	//$('#dataDiv').html("<img src='images/waterjet.gif' />");

	$.post("ajax/wc.php",{ df: df, dt: dt },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	
}




function closeDiv(div) {
	//alert(div);
	
	var r=confirm("Remove table from view?");
	
	if (r==true) {
	
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
	
}


function dashsubValue(a_team) {
	//alert(a_team);
	
	//$('input:submit').attr("disabled", true);
	var jsSplitResult = a_team.split("~");
	
	var i = 0;
	for(i=0; i < jsSplitResult.length; i++){
		if (jsSplitResult[i] != '0') {
			//alert(jsSplitResult[i]);
			dashsendValue(jsSplitResult[i]);
		}
		
	}

	//$('input:submit').attr("disabled", false);
	
	//timedRefresh(10000);
}


function dashsendValue(str){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	
	//alert(df);
	//alert(dt);
	
	$.post("ajax/realtime.php",{ sendValue: str, from: 'dash', df: df, dt: dt },
	//$.post("ajax/realtime.php",{ sendValue: str, from: 'dash'},
	function(data){
		$('#div_' + str).html(data.returnValue);
	}, "json");
}


var popupStatus = 0; 

function goToActivity(Team) {
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	
	//loadPopup();
	
	//alert(Team);
	//alert(df);
	//alert(dt);
	window.open("activity.php?df=" + df +"&dt=" + dt + "&team=" + Team,"_newtab");

	//document.getElementById("redir_" + Team).submit();
}

function goToActivityPopUp(Team) {
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	

	
	$.post("ajax/activity.php",{ df: df, dt: dt, team: Team, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		//alert("TEST2");
		//$('input:submit').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
	}, "json");	

	loadPopup();
	centerPopup();  

	//$('#dataPopup').fadeIn('slow');


}


function centerPopup(){  
	//request data for centering  
	var windowWidth = document.documentElement.clientWidth;  
	var windowHeight = document.documentElement.clientHeight;  
	var popupHeight = $("#dataPopup").height();  
	var popupWidth = $("#dataPopup").width();  
	//centering  
	$("#dataPopup").css({  
		"position": "absolute",  
		"display": "inline-block",
		//"top": windowHeight/5-popupHeight/5,  
		//"left": windowWidth/5-popupWidth/5,  
		
		"top": 25,  
		"left": 25,  

		//"overflow": "auto",
		"height": "auto"

	});  
	//only need force for IE6  

	$('#dataPopup').fadeIn('slow');

	$("#backgroundPopup").css({  
		//"height": windowHeight  
		"height": "200%" , 
		"width": "110%"  
	});  
  
	
}  



function closeDiv(div) {
	//alert(div);
	
	var r=confirm("Remove table from view?");
	
	if (r==true) {
	
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
	
}





function loadPopup(){  
	//loads popup only if it is disabled  
	if(popupStatus==0){  
		$("#backgroundPopup").css({  
			"opacity": "0.7"  
		});  
		$("#backgroundPopup").fadeIn("slow");  
		//$("#dataPopup").fadeIn("slow");  
		popupStatus = 1;  
	}  
}  

function disablePopup(team){  
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;

	//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#dataPopup").fadeOut("slow");  
		$('#dataPopup').html('');
		popupStatus = 0;  
	}  
	
	$.post("ajax/dashrow.php",{ team: team, df: df, dt: dt },
	function(data){
		$('#rowAppStat_' + team).html(data.returnValue);
	}, "json");		

}  



function insertDCApprovalJS(badge,dateapp,div) {
	var earned = document.getElementById('earned').value;
	var actual = document.getElementById('actual').value;
	var comments = document.getElementById('cmts_approve').value;
	var code = document.getElementById('select_app').value;
	var txt_min = document.getElementById('txt_min').value;

	if ((!IsInteger(txt_min)) && (txt_min != '')) {
		alert("Adjusted minutes must be numeric.");
		return;
	}
	
	if (code == 0) {
		alert("You must select either 'Approve' or 'Review'");
		return;
	}

	if ((txt_min != '') && (code != 201)){
		alert("To adjust minutes, you must select 'Review'");
		return;
	}
	

	
	if ((code != 0) && (badge != '') && (dateapp != '')){
		$.post("ajax/approve.php",{ code: code, badge: badge, dateapp: dateapp, comments: comments, earned: earned, actual: actual, txt_min : txt_min},
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");
	} else {
		alert('Problem');
	}
	 
}


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
