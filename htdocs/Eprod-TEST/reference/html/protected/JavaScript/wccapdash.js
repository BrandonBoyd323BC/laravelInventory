var popupStatus = 0; 

function submitForm(){
	var action = "buildDash";
	var mode = document.getElementById('sel_Mode').value;
	var targetDays = document.getElementById('sel_TargetDays').value;
	var effPct = document.getElementById('sel_EffPct').value;
	var dt = document.getElementById('dt').value;

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post("ajax/wccapdash.php",{ action: action, mode: mode, targetDays: targetDays, dt: dt, effPct: effPct },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
}

function searchKeyPress(e) {
	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById('submit').click();
	}
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




function showWcMembers(ID_WC) {
	var action = "showWcMembers";
	
	$.post("ajax/wccapdash.php",{ action: action, WC: ID_WC, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
	}, "json");	

	loadPopup();
	centerPopup();  
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

function disablePopup(ID_WC){  
	//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#dataPopup").fadeOut("slow");  
		$('#dataPopup').html('');
		popupStatus = 0;  
	}  
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