function doOnLoads() {
	numRecsChange();
	focus();so1.focus();
}

function nextOnDash(soid,sufxid) {
	$('#'+soid).keypress(function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#'+sufxid).focus();
    	}
	});
}


function numRecsChange() {
	var url = "ajax/OP_Job_Cards_Planned.php";
	var num_recs = document.getElementById('num_recs').value;
	var search_so = document.getElementById("searchSO").value;

	if (search_so == '') {
		search_so = 'ALL';
		document.getElementById('searchSO').value = search_so;
	}

	$.post(url,{ numRecsChange: 1, num_recs: num_recs, search_so: search_so },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}



function checkSufxLength(soid,sufxid){
	var url = "ajax/OP_Job_Cards_Planned.php";
	var so = document.getElementById(soid).value;
	var sufx = document.getElementById(sufxid).value;
	
	if (sufx.length == 3) {

		$('#dw_submit').focus();

	}
}



function sendAddValue(){
	var url = "ajax/OP_Job_Cards_Planned.php";	
	var so1 = document.getElementById('so1').value;
	var sufx_so1 = document.getElementById('sufx_so1').value;	
	var num_recs = document.getElementById('num_recs').value;
	
	if(so1 == '' || sufx_so1 == '') {
		alert("Required Field Missing!");
		return;
	}

	$('#dw_submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		sendAddValue: '1',
		so1: so1, 
		sufx_so1: sufx_so1, 
		num_recs: num_recs
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#dw_submit').attr("disabled", false);
		$('#SO_Form')[0].reset();
		$('#so1').focus();
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

function deleteRecord(rowid) {
	var r = confirm("Are you sure you want to delete this record?");
	var url = "ajax/OP_Job_Cards_Planned.php";	

	if (r==true) {
		$.post(url,{ deleteRecord: 1, rowid: rowid },
		function(data){
			$('#delete_' + rowid).html(data.returnValue);
		}, "json");
	}
}