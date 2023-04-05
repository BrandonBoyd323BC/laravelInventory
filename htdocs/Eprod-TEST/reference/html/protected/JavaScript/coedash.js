

function sendValue(){
	var dt = document.getElementById('dt').value;
	var team = document.getElementById('team').value;

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	
	$.post("ajax/coedash.php",{ dt: dt, team: team },
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