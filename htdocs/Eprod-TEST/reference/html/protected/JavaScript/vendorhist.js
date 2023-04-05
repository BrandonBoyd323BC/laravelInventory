

function submitForm(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var StartItem = document.getElementById('StartItem').value;
	var EndItem = document.getElementById('EndItem').value;
	var StartVendNum = document.getElementById('StartVendNum').value;
	var EndVendNum = document.getElementById('EndVendNum').value;

	if (df > dt) {
		alert('Invalid Date Range');
		return;
	}

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post("ajax/vendorhist.php",{ df: df, dt: dt, StartItem: StartItem, EndItem: EndItem, StartVendNum: StartVendNum, EndVendNum: EndVendNum },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	
}



function closeDiv(div) {
	$.post("ajax/dummy.php",{ sendValue: div },
	function(data){
		$('#' + div).html(data.returnValue);
	}, "json");	
}


 
function searchKeyPress(e) {
	var current = document.activeElement.id;
	var currentbutton = current.replace('txt','submit');
	
	//"CH_txt_add_comment_25099"			"CH_submit_add_comment_"
	//"CL_txt_add_comment_83449"			"CL_submit_add_comment_"
	//alert(currentbutton);

	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById(currentbutton).click();
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
