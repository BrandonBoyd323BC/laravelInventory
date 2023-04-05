var popupStatus = 0; 

function disablePopup(div){  
	//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#dataPopup").fadeOut("slow");  
		$('#dataPopup').html('');
		popupStatus = 0;  
	}  
	
	/*$.post("ajax/rgaform.php",{ action: 'refresh_mainDiv', status: status },
	function(data){
		$('#mainDiv').html(data.returnValue);
	}, "json");*/
	//filterRgaStatus();
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

function goToNewRequestPopUp() {
	$('input:[name=button_NewReqForm]').attr("disabled", true);
	$.post("ajax/RnDInspection.php",{ action: 'form_newreq', divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		$('input:[name=button_NewReqForm]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
	}, "json");

	loadPopup();
	centerPopup();  
}

function refreshMainDiv(){
	//need to pass filter variables here

	$.post("ajax/RnDInspection.php",{ action: 'refresh_mainDiv',  /*and here*/}, 
		function(data){
		$('#mainDiv').html(data.returnValue);
	}, "json");
}//end filterStatus

function goToReviewRequestPopUp(soNumber, soSuffix) {
	$.post("ajax/RnDInspection.php",{ action: 'form_newreq', soNumber: soNumber, soSuffix: soSuffix, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		//$('#dataPopup').fadeIn('slow');
	}, "json");
	loadPopup();
	centerPopup();  
}

function hideElements(){//hides and adds block elems based on inspection type
	var value = "";
    value = document.getElementById("insp_type").value

    document.getElementById("label_100_pass").style.display = "none";
	document.getElementById("label_100_fail").style.display = "none";
	document.getElementById("pass100").style.display = "none";
	document.getElementById("fail100").style.display = "none";
	document.getElementById("passFail_P").style.display = "inline-block";
	document.getElementById("passFail_F").style.display = "inline-block";
	document.getElementById("label_passFail_P").style.display = "inline-block";
	document.getElementById("label_passFail_F").style.display = "inline-block";

	if(value == '100%'){
		document.getElementById("label_100_pass").style.display = "block";
		document.getElementById("label_100_fail").style.display = "block";
		document.getElementById("pass100").style.display = "block";
		document.getElementById("fail100").style.display = "block";
		document.getElementById("passFail_P").style.display = "none";
		document.getElementById("passFail_F").style.display = "none";
		document.getElementById("label_passFail_P").style.display = "none";
		document.getElementById("label_passFail_F").style.display = "none";
	}

}

function insertNewRecord(){//insert a new inspection record into table
	var url = "ajax/RnDInspection.php";	

	var choice_value ='';
	if(document.getElementById('passFail_P').checked){
		choice_value = document.getElementById('passFail_P').value;
	}else if(document.getElementById('passFail_F').checked){
		choice_value = document.getElementById('passFail_F').value;
	}
	else{
		choice_value = " ";
	}

	var insp_type = document.getElementById('insp_type').value;
	var soNumber = document.getElementById('soNumber').value;
	var soNumber_suffix = document.getElementById('soNumber_suffix').value;
	var itemNumber = document.getElementById('idItem').value;
	var orderNumber = document.getElementById('idOrd').value;
	var nameCust = document.getElementById('nameCust').value;
	var passFail = choice_value;
	var notes = document.getElementById('notes').value;
	var teamBadge = document.getElementById('teamBadge').value;
	var inspecInitals = document.getElementById('inspecInitals').value;
	var pass100 = document.getElementById('pass100').value;
	var fail100 = document.getElementById('fail100').value;

	//error/blank field checking
	if(insp_type == '' || inspecInitals == '') {
		alert("Required Field Missing!");
		return;
	}

	if( (insp_type == 'firstItem' || insp_type == 'random') && (itemNumber == '') ) {
		alert("Required Field Missing!");
		return;
	}

	$('#btnInsertRecord').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{ action: 'submit_newrec',
		//sendAddValue: '1',
		insp_type: insp_type, 
		soNumber: soNumber,  
		soNumber_suffix: soNumber_suffix, 
		itemNumber: itemNumber,
		orderNumber: orderNumber,
		nameCust: nameCust,
		passFail: passFail,
		notes: notes,
		teamBadge: teamBadge,
		inspecInitals: inspecInitals,
		pass100: pass100,
		fail100: fail100,
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnInsertRecord').attr("disabled", false);
		//$('#SO_Log_Input_Form')[0].reset();
		//clearing rest of inputs that dont clear automagically
			document.getElementById('soNumber').value = "";
			document.getElementById('soNumber_suffix').value = "";
			document.getElementById('idItem').value = "";
			document.getElementById('idOrd').value = "";
			document.getElementById('nameCust').value = "";
			document.getElementById('insp_type').focus();
		}, "json");

}//end insertNewRecord
