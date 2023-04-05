

function sendValue(){
	var action = document.getElementById('action').value;
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post("ajax/fginv.php",{ action: action },
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



var popupStatus = 0; 




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
