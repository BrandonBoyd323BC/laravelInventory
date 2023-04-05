
function sendValue(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var unit = document.getElementById('unit').value;
	var flag_shift_summary = document.getElementById('shift_summary').value;
	var orderby = document.getElementById('orderby').value;
	var url = "ajax/groupunit.php";	

	if (df > dt) {
		alert('Invalid Date Range');
		return;
	}
	
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ df: df, dt: dt, unit: unit, flag_shift_summary: flag_shift_summary, orderby: orderby },
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

var popupStatus = 0; 

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
