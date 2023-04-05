function saveQtys(id){
	var action = 'saveQtys';
	var qtyMake = document.getElementById('M_'+id).value;
	var qtyPull = document.getElementById('P_'+id).value;
	var qtyTotalOrd = document.getElementById('T_'+id).value;
	
	if (qtyMake == '') {
		qtyMake = 0;
	}

	if (qtyPull == '') {
		qtyPull = 0;
	}

	if (!IsInteger(qtyMake)) {
		alert("Qty Make must be an integer!");
		return;
	}

	if (!IsInteger(qtyPull)) {
		alert("Qty Pull must be an integer!");
		return;
	}

	if ((parseFloat(qtyMake) + parseFloat(qtyPull)) != parseFloat(qtyTotalOrd)) {
		alert('Quantities do not equal Order Qty');
		return;
	}
	
	$(id).attr("disabled", true);

	$.post("ajax/vnt_work.php",{action: action, id: id, qtyMake: qtyMake, qtyPull: qtyPull },
	function(data){
		$('#div_'+id).html(data.returnValue);
		$(id).attr("disabled", false);
	}, "json");
}

function refreshOrd(ord){
	var action = 'refreshOrd';
	
	$('#ref_'+ord).attr("disabled", true);
	$('#div_'+ord).html("<img src='images/loading01.gif' />");

	$.post("ajax/vnt_work.php",{action: action, ord: ord },
	function(data){
		$('#div_'+ord).html(data.returnValue);
		$(id).attr("disabled", false);
	}, "json");
}

function showWOC(rowid,o){
	//alert(rowid);
	var action = 'showWOC';
	
	$.post("ajax/vnt_work.php",{action: action, rowid: rowid },
	function(data){
		$('#wc'+o+'_'+rowid).html(data.returnValue);
	}, "json");
}

function closeDiv(div) {
	//var r=confirm("Remove table from view?");
	
	//if (r==true) {
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	//} 
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
