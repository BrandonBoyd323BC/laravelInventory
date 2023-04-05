function doOnLoads() {
	numRecsChange();
	focus();tablenum.focus();
}

function showLotInputRow(tr_ln) {
	document.getElementById(tr_ln).style.display = 'table-row';
}



function nextOnEnter(e,thisElement,nextElement) {
	$(thisElement).keypress(
		function(e) {
	    if (e.keyCode == 13) {
	    	e.preventDefault();
	    	$(nextElement).focus();
    	}
	});
}

function selectChangedNextElement(e,nextElement) {
	// look for window.event in case event isn't passed in
	if (window.event) { 
		e = window.event; 
	}

	if (e.keyCode == 13 || e.keyCode == 9) {
		$('#'+nextElement).focus();
		e.preventDefault();
	}
}

function searchKeyPress(e) {
	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById('lookupRowid').click();
	}
}


function clearSeachCriteria() {
	document.getElementById('so_num').value = "";
	document.getElementById('sufx').value = "";
	document.getElementById('lot_num').value = "";
}

function getMarkerInfo(){
	var url = "ajax/lottracking.php";
	var action = "getMarkerInfo";
	var markerrowid = document.getElementById('markerrowid').value;

	//alert("markerrowid: " + markerrowid);
	if (!IsNumeric(markerrowid)) {
		alert("Marker ID Invalid!");
		return;
	}

	//$('#divMarkerInfo').html("<img src='images/loading01.gif' />");
	$('#divMarkerInfo').html("<h4>Loading...</h4>");
	$.post(url,{ action: action, markerrowid: markerrowid },
	function(data){
		$('#divMarkerInfo').html(data.returnValue);
		//$('#so_fab_code').focus();
		$('#lotNumb').focus();
		
		searchLotNumNoEvent();

	}, "json");

	
}

function numRecsChange() {
	var url = "ajax/lottracking.php";
	var action = "numRecsChange";
	var so_num = document.getElementById('so_num').value;
	var sufx = document.getElementById('sufx').value;
	var lot_num = document.getElementById('lot_num').value;
	var num_recs = document.getElementById('num_recs').value;

	$.post(url,{ action: action, num_recs: num_recs, so_num: so_num, sufx: sufx, lot_num: lot_num },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

function nextOnDash(so_num,sufx) {
	$('#'+so_num).keypress(
		function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#'+sufx).focus();
    	}
	});
}

function searchLotNum(e) {
	var url = "ajax/lottracking.php";
	var action = "searchLotNum";
	var markerrowid = document.getElementById('markerrowid').value;
	var lotNumb = document.getElementById('lotNumb').value;

	// look for window.event in case event isn't passed in
	if (window.event) { 
		e = window.event; 
	}

	if (lotNumb.indexOf("10005547221-17525650") !== -1) {
		document.getElementById("lotNumb").value = lotNumb.replace("10005547221-17525650", "");
		e.preventDefault();
		alert("Invalid Lot Number Barcode. Please enter this lot number by hand.");
		return;
	}

	if (e.keyCode == 13 || e.keyCode == 9) {
		$.post(url,{
			action: action,
			markerrowid: markerrowid, 
			lotNumb: lotNumb
		},

		function(data){
			document.getElementById("lotNumb").value = data.returnValue;
		},"json");
	}
}

function searchLotNumNoEvent() {
	var url = "ajax/lottracking.php";
	var action = "searchLotNum";
	var markerrowid = document.getElementById('markerrowid').value;
	var lotNumb = document.getElementById('lotNumb').value;

	// look for window.event in case event isn't passed in

	if (lotNumb.indexOf("10005547221-17525650") !== -1) {
		document.getElementById("lotNumb").value = lotNumb.replace("10005547221-17525650", "");
		alert("Invalid Lot Number Barcode. Please enter this lot number by hand.");
		return;
	}


	$.post(url,{
		action: action,
		markerrowid: markerrowid, 
		lotNumb: lotNumb
	},

	function(data){
		document.getElementById("lotNumb").value = data.returnValue;
	},"json");

}

function sendAddValue(){
	var url = "ajax/lottracking.php";
	var action = "submitAddLotNumber";
	var tablenum = document.getElementById('tablenum').value;
	var idbadge = document.getElementById('idbadge').value;
	var markerrowid = document.getElementById('markerrowid').value;
	var lotNumb = document.getElementById('lotNumb').value;

	if (!IsNumeric(tablenum)) {
		alert("Table # Invalid!");
		return;
	}

	if (!IsNumeric(idbadge)) {
		alert("Badge # Invalid!");
		return;
	}

	$('#lot_submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{ action: action, tablenum: tablenum, idbadge: idbadge, markerrowid: markerrowid, lotNumb: lotNumb	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#lot_submit').attr("disabled", false);
		$('#divMarkerInfo').html("</br>");
		$('#tablenum').focus();
		document.getElementById("tablenum").value = '';
		document.getElementById("idbadge").value = '';
		document.getElementById("markerrowid").value = '';
		numRecsChange();
	}, "json");
}

$(document).ready(function()
{
	$('#marker_fab_code').autocomplete(
	{
		source: "ajax/markerlog_matllookup.php",
		minLength: 3
	});
});

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

function IsNumeric(strString) {
	var strValidChars = "0123456789";
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



