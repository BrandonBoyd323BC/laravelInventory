

function sendValue(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var zeroHour = document.getElementById('zeroHour').value;
	var team = document.getElementById('team').value;

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	//$('#dataDiv').html("<img src='images/waterjet.gif' />");
	
	$.post("ajax/activity.php",{ df: df, dt: dt, zeroHour: zeroHour, team: team },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
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




function insertDCApprovalJS(badge,dateapp,div) {
	var earned = document.getElementById('earned').value;
	var actual = document.getElementById('actual').value;
	var unadj = document.getElementById('unadj').value;
	var indir = document.getElementById('indir').value;	
	var sample = document.getElementById('sample_mins').value;	
	var comments = document.getElementById('cmts_approve').value;
	var code = document.getElementById('select_app').value;


//	//VARIABLE FOR EACH PROD OPER	
//	var list_ProdOper = document.getElementById('list_ProdOper').value;
//	var arrayProdOper = list_ProdOper.split(',');
//
//	aLen = arrayProdOper.length;
//
//	for (i = 0; i < aLen; i++) {
//	    var prodMins_+arrayProdOper[i] = document.getElementById('prodMins_'+arrayProdOper[i]).value;
//		alert(prodMins_+arrayProdOper[i]);
//	}




	if (code == 0) {
		alert("You must select either 'Approve' or 'Review'");
		return;
	}
	
	if ((code != 0) && (badge != '') && (dateapp != '')){
		$.post("ajax/approve.php",{ code: code, badge: badge, dateapp: dateapp, comments: comments, earned: earned, actual: actual, sample: sample },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");
	} else {
		alert('Problem');
	}
 
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





function showAlert() {
	var date = document.getElementById('da').value;
	
	alert(date);
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