function doOnLoads() {
	numRecsChange();
	focus();marker_id.focus();
}

function numRecsChange() {
	var url = "ajax/markerscut.php";
	var num_recs = document.getElementById('num_recs').value;
	var search_marker_id = document.getElementById("searchMarkerID").value;

	if (search_marker_id == '') {
		search_marker_id = 'ALL';
		document.getElementById('searchMarkerID').value = search_marker_id;
	}

	$.post(url,{ numRecsChange: 1, num_recs: num_recs, search_marker_id: search_marker_id },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}


function searchKeyPress(e) {
	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		sendAddValue();
		//document.getElementById('dw_submit').click();
		//return;
	}
}



function sendAddValue(){
	var url = "ajax/markerscut.php";	
	var marker_id = document.getElementById('marker_id').value;
	var num_recs = document.getElementById('num_recs').value;
	
	if(marker_id == '') {
		alert("Required Field Missing!");
		return;
	}

	$('#dw_submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		sendAddValue: '1',
		marker_id: marker_id, 
		num_recs: num_recs
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#dw_submit').attr("disabled", false);
		//$('#Marker_Form')[0].reset();
		document.getElementById('marker_id').value = '';
		$('#marker_id').focus();
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

function IsNumeric(strString) {
	var strValidChars = "0123456789.";
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

