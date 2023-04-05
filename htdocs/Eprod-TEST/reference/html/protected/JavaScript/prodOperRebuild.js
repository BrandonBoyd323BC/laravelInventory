

function sendValue(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var zeroHour = document.getElementById('zeroHour').value;
	var url = "ajax/prodOperRebuild.php";	

	if (df > dt) {
		alert('Invalid Date Range');
		return;
	
	}

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ df: df, dt: dt, zeroHour: zeroHour },
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
	var zeroHour = document.getElementById('zeroHour').value;
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
	var zeroHour = document.getElementById('zh').value;	

	
	$.post("ajax/activity.php",{ df: df, dt: dt, zeroHour: zeroHour, team: Team, divclose: 'true' },
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
	var zh = document.getElementById('zh').value;

	//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#dataPopup").fadeOut("slow");  
		$('#dataPopup').html('');
		popupStatus = 0;  
	}  
	
	$.post("ajax/dashrow.php",{ team: team, df: df, dt: dt, zeroHour: zh },
	function(data){
		$('#rowAppStat_' + team).html(data.returnValue);
	}, "json");		

}  



function insertDCApprovalJS(badge,dateapp,div) {
	var earned = document.getElementById('earned').value;
	var actual = document.getElementById('actual').value;
	var unadj = document.getElementById('unadj').value;
	var indir = document.getElementById('indir').value;
	var sample = document.getElementById('sample_mins').value;
	var comments = document.getElementById('cmts_approve').value;
	var code = document.getElementById('select_app').value;


	//VARIABLE FOR EACH PROD OPER	
	var list_ProdOper = document.getElementById('list_ProdOper').value;
	var arrayProdOper = list_ProdOper.split(',');
	aLen = arrayProdOper.length;

	var arrArrProdOper = [];

	for (i = 0; i < aLen; i++) {
		arrArrProdOper[i][0] = arrayProdOper[i];
		arrArrProdOper[i][1] = document.getElementById(arrayProdOper[i]).value;
		
		alert(arrArrProdOper[i][0] + " " + arrArrProdOper[i][1]);
	}


/*	
	if (code == 0) {
		alert("You must select either 'Approve' or 'Review'");
		return;
	}
	if ((code != 0) && (badge != '') && (dateapp != '')){
		$('#sub_approve').attr("disabled", true);
		$.post("ajax/approve.php",{ code: code, badge: badge, dateapp: dateapp, comments: comments, earned: earned, actual: actual, unadj: unadj, indir: indir, sample: sample },
		function(data){
			$('#' + div).html(data.returnValue);
			$('#sub_approve').attr("disabled", false);
		}, "json");
	} else {
		alert('Problem');
	}
*/	 
}

function deleteApproval(rowid,div,adjrowids) {
	//alert(adjrowids);
	
	if (!IsInteger(rowid)) {
		alert("ERROR -- rowid not numeric.");
		return;
	}

	var r=confirm("Are you sure you want to delete this Approval/Review");
	if (r==true) {
		$.post("ajax/delapp.php",{ rowid: rowid },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");
		//alert("Deleted");
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

function checkUncheck() { 
    $('#onlyappvd').click(function() {
        if ( $('#onlyappvd:checked').length > 0) {
            $("#orderByRow").show();
        } else {
            $("#orderByRow").hide();
        }
    }); 
}

