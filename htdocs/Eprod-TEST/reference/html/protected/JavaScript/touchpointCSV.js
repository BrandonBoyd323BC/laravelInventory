function sendLookupOrderShipment(){
	var url = "ajax/TouchpointCSV.php";	
	var id_ord = document.getElementById('tb_ID_ORD').value;
	var action = 'getShip';

	if (!IsInteger(id_ord)) {
		alert("Invalid Order ID!");
		return;
	}
	
	$('input:submit').attr("disabled", true);
	$('#shipDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, id_ord: id_ord },
	function(data){
		$('#shipDiv').html(data.returnValue);
		document.getElementById('tb_ID_ORD').value = "";
		$('input:submit').attr("disabled", false);
	}, "json");
}

function sendGenerateCSV(id_ship){
	var url = "ajax/TouchpointCSV.php";	
	var id_ord = document.getElementById('hid_ID_ORD').value;
	var action = 'generateCSV';

	if (!IsInteger(id_ord)) {
		alert("Invalid Order ID!");
		return;
	}
	
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, id_ord: id_ord, id_ship: id_ship },
	function(data){
		$('#dataDiv').html(data.returnValue);
		document.getElementById('tb_ID_ORD').value = "";
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
