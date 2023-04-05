function doOnLoads() {
	numRecsChange();
	focus();so1.focus();
}

function nextOnDash(soid,sufxid) {
	$('#'+soid).keypress(function(e) {
	    if (e.keyCode == 32) {
	    	e.preventDefault();
    	}
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#'+sufxid).focus();
    	}
	});
}

function showSoInputRow(tr_so) {
	document.getElementById(tr_so).style.display = 'table-row';
}

function showTrimInputRow(tr_rc_trim1) {
	document.getElementById(tr_rc_trim1).style.display = 'table-row';
}


function checkMarkerLengthCost() {
	
	var url = "ajax/markerlog.php";
	var marker_fab_code = document.getElementById(marker_fab_code).value;
	var marker_length_y = document.getElementById(marker_length_y).value;
	var marker_length_in = document.getElementById(marker_length_in).value;



}

function showEditField(field_id) {
	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/markerlog.php";
	var action = "showedit";

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function cancelEditField(field_id, field_value){
	var url = "ajax/markerlog.php";	
	var action = "canceledit";

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/markerlog.php";	
	var action = "saveedit";

	if (new_value.length > 75) {
		alert("Value too long. Must be less than 50 characters");
		return;
	}
	
	if (new_value.match('[^A-Za-z0-9. --#*]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, hashes, asterisks, spaces and periods.");
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: new_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function deleteRecord(rowid) {
	var r = confirm("Are you sure you want to delete this record?");
	var url = "ajax/markerlog.php";	

	if (r==true) {
		$.post(url,{ deleteRecord: 1, rowid: rowid },
		function(data){
			$('#delete_' + rowid).html(data.returnValue);
		}, "json");
	}
}

function getTrimCodes(soid,sufxid){
	var url = "ajax/markerlog.php";
	var so = document.getElementById(soid).value;
	var sufx = document.getElementById(sufxid).value;
	
	if (sufx.length == 3) {
		$('#trimComp1').html("<option value='LOADING'>LOADING</option>");
		$('#trimComp2').html("<option value='LOADING'>LOADING</option>");
		$('#trimComp3').html("<option value='LOADING'>LOADING</option>");

		$.post(url,{ getTrimCodes: 1, so: so, sufx: sufx },
		function(data){
			$('#trimComp1').html(data.returnValue);
			$('#trimComp2').html(data.returnValue);
			$('#trimComp3').html(data.returnValue);
			//$('#so_fab_code').focus();
			//$('#marker_name').focus();
		}, "json");
	}
}

function getSoFabCodes(soid,sufxid){
	var url = "ajax/markerlog.php";
	var so = document.getElementById(soid).value;
	var sufx = document.getElementById(sufxid).value;
	
	if (sufx.length == 3) {
		$('#so_fab_code').html("<option value='LOADING'>LOADING</option>");

		$.post(url,{ getSoFabCodes: 1, so: so, sufx: sufx },
		function(data){
			$('#so_fab_code').html(data.returnValue);
			//$('#so_fab_code').focus();
			$('#marker_name').focus();
		}, "json");

		getTrimCodes(soid,sufxid);
	}
}

function soFabCodeChange() {
	var url = "ajax/markerlog.php";
	var so1 = document.getElementById('so1').value;
	var sufx_so1 = document.getElementById('sufx_so1').value;	
	var so2 = document.getElementById('so2').value;
	var sufx_so2 = document.getElementById('sufx_so2').value;	
	var so3 = document.getElementById('so3').value;
	var sufx_so3 = document.getElementById('sufx_so3').value;	
	var so4 = document.getElementById('so4').value;
	var sufx_so4 = document.getElementById('sufx_so4').value;	
	var so5 = document.getElementById('so5').value;
	var sufx_so5 = document.getElementById('sufx_so5').value;
	var so6 = document.getElementById('so6').value;
	var sufx_so6 = document.getElementById('sufx_so6').value;
	var so7 = document.getElementById('so7').value;
	var sufx_so7 = document.getElementById('sufx_so7').value;
	var so8 = document.getElementById('so8').value;
	var sufx_so8 = document.getElementById('sufx_so8').value;
	var so_fab_code = document.getElementById('so_fab_code').value;

	$.post(url,{
		getSoLength: '1',
		so1: so1, 
		so2: so2, 
		so3: so3, 
		so4: so4, 
		so5: so5, 
		so6: so6, 
		so7: so7, 
		so8: so8, 
		sufx_so1: sufx_so1, 
		sufx_so2: sufx_so2, 
		sufx_so3: sufx_so3, 
		sufx_so4: sufx_so4,  
		sufx_so5: sufx_so5,
		sufx_so6: sufx_so6,
		sufx_so7: sufx_so7,
		sufx_so8: sufx_so8,
		so_fab_code: so_fab_code
	},		
	function(data){
		$('#div_so_length').html(data.returnValue);
	}, "json");

	$.post(url,{ soFabCodeChange: 1, so_fab_code: so_fab_code },
	function(data){
		$('#div_marker_fab_code').html(data.returnValue);
		$('#marker_util').focus();
	}, "json");
	
	//var marker_fab_code = document.getElementById('marker_fab_code').value;
	//alert(marker_fab_code);

}

function numRecsChange() {
	var url = "ajax/markerlog.php";
	var num_recs = document.getElementById('num_recs').value;
	var user_recs = document.getElementById('user_recs').value;
	var search_so = document.getElementById("searchSO").value;
	var includeDelSO = document.getElementById("checkboxIncludelDelSO").checked;

	if (search_so == '') {
		search_so = 'ALL';
		document.getElementById('searchSO').value = search_so;
	}


	$.post(url,{ numRecsChange: 1, num_recs: num_recs, user_recs: user_recs, search_so: search_so, includeDelSO: includeDelSO },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");

	focus();so1.focus();	
}

function markerFabCodeChange() {
	$('#marker_fab_code').autocomplete(
	{
		source: "ajax/markerlog_matllookup.php",
		minLength: 1
	});	

	var marker_fab_code = document.getElementById('marker_fab_code').value;
	var url = "ajax/markerlog.php";

	$.post(url,{ markerFabCodeChange: 1, marker_fab_code: marker_fab_code },
	function(data){
		$('#div_hdn_fab_code_cost').html(data.returnValue);
		//$('#marker_util').focus();
	}, "json");

}

function recutCheckBoxChange() {
	var flag_recut = document.getElementById('flag_recut').checked;
	if (flag_recut == true){
		document.getElementById('tr_probcode').style.display = 'table-row';
		document.getElementById('tr_badgenum').style.display = 'table-row';
		document.getElementById('tr_comments').style.display = 'table-row';
		document.getElementById('tr_rc_trim1').style.display = 'table-row';
		document.getElementById('so_length').value = '0';
	} else {
		document.getElementById('prob_code').value = '';
		document.getElementById('badge_num').value = '';
		document.getElementById('comments').value = '';
		document.getElementById('trimComp1').value = '';
		document.getElementById('trimComp2').value = '';
		document.getElementById('trimComp3').value = '';
		document.getElementById('trimLength1').value = '';
		document.getElementById('trimLength2').value = '';
		document.getElementById('trimLength3').value = '';
		document.getElementById('tr_probcode').style.display = 'none';
		document.getElementById('tr_badgenum').style.display = 'none';
		document.getElementById('tr_comments').style.display = 'none';
		document.getElementById('tr_rc_trim1').style.display = 'none';
		document.getElementById('tr_rc_trim2').style.display = 'none';
		document.getElementById('tr_rc_trim3').style.display = 'none';
		soFabCodeChange()
	}
}

function sendAddValue(){
	var url = "ajax/markerlog.php";	
	var dw = document.getElementById('dw').value;
	var so1 = document.getElementById('so1').value;
	var sufx_so1 = document.getElementById('sufx_so1').value;	
	var so2 = document.getElementById('so2').value;
	var sufx_so2 = document.getElementById('sufx_so2').value;	
	var so3 = document.getElementById('so3').value;
	var sufx_so3 = document.getElementById('sufx_so3').value;	
	var so4 = document.getElementById('so4').value;
	var sufx_so4 = document.getElementById('sufx_so4').value;	
	var so5 = document.getElementById('so5').value;
	var sufx_so5 = document.getElementById('sufx_so5').value;
	var so6 = document.getElementById('so6').value;
	var sufx_so6 = document.getElementById('sufx_so6').value;
	var so7 = document.getElementById('so7').value;
	var sufx_so7 = document.getElementById('sufx_so7').value;
	var so8 = document.getElementById('so8').value;
	var sufx_so8 = document.getElementById('sufx_so8').value;	
	var so_fab_code = document.getElementById('so_fab_code').value;
	var so_length = document.getElementById('so_length').value;
	var marker_name = document.getElementById('marker_name').value;
	var marker_fab_code = document.getElementById('marker_fab_code').value;
	var marker_util = document.getElementById('marker_util').value;
	var marker_length_y = document.getElementById('marker_length_y').value;
	var marker_length_in = document.getElementById('marker_length_in').value;
	var num_layers = document.getElementById('num_layers').value;
	var marker_page_from = document.getElementById('marker_page_from').value;
	var marker_page_to = document.getElementById('marker_page_to').value;
	var flag_recut = document.getElementById('flag_recut').checked;
	var prob_code = document.getElementById('prob_code').value;
	var badge_num = document.getElementById('badge_num').value;
	var trim_comp1 = document.getElementById('trimComp1').value;
	var trim_length1 = document.getElementById('trimLength1').value;	
	var trim_comp2 = document.getElementById('trimComp2').value;
	var trim_length2 = document.getElementById('trimLength2').value;	
	var trim_comp3 = document.getElementById('trimComp3').value;
	var trim_length3 = document.getElementById('trimLength3').value;	
	var comments = document.getElementById('comments').value;
	var ret_FileName = document.getElementById('ret_FileName').value;
	var num_recs = document.getElementById('num_recs').value;
	var user_recs = document.getElementById('user_recs').value;
	
	if(so1 == '' || sufx_so1 == '' ||  marker_name == '' || marker_util == '' || (marker_length_y == '' && marker_length_in == '') || num_layers == '') {
		alert("Required Field Missing!");
		return;
	}

	if (so_fab_code == 'SELECT') {
		alert("Shop Fabric Code Invalid!");
		return;		
	}

	if (!IsNumeric(so_length) && so_length != '') {
		alert("Shop Order Length Invalid!");
		return;
	}

	if (!IsNumeric(marker_util)) {
		alert("Marker Utilization Invalid!");
		return;
	}
	
	if (!IsNumeric(marker_length_y) && marker_length_y != '') {
		alert("Marker Length (yd) Invalid!");
		return;
	}

	if (!IsNumeric(marker_length_in) && marker_length_in != '') {
		alert("Marker Length (in) Invalid!");
		return;
	}

	if (!IsNumeric(badge_num) && badge_num != '') {
		alert("Badge Number Invalid!");
		return;
	}
	
	if (!IsNumeric(trim_length1) && trim_length1 != '') {
		alert("Trim Length (in) Invalid!");
		return;
	}

	if (!IsNumeric(trim_length2) && trim_length2 != '') {
		alert("Trim Length 2 (in) Invalid!");
		return;
	}

	if (!IsNumeric(trim_length3) && trim_length3 != '') {
		alert("Trim Length 3 (in) Invalid!");
		return;
	}

	if (marker_page_from == '') {
		alert("Marker Page fields are Required!");
		document.getElementById("marker_page_from").focus();
		return;
	}

	if (marker_page_to == '') {
		alert("Marker Page fields are Required!");
		document.getElementById("marker_page_to").focus();
		return;
	}

	if (!IsNumeric(marker_page_from) && marker_page_from != '') {
		document.getElementById("marker_page_from").focus();
		alert("Marker Page fields are Invalid!");
		return;
	}

	if (!IsNumeric(marker_page_to) && marker_page_to != '') {
		document.getElementById("marker_page_to").focus();
		alert("Marker Page fields are Invalid!");
		return;
	}

	if (marker_page_from > marker_page_to) {
		alert("Marker Page beginning cannot be greater than Marker Page end!");
		document.getElementById("marker_page_from").focus();
		return;
	}



	$('#dw_submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		sendAddValue: '1',
		dw: dw, 
		so1: so1, 
		so2: so2, 
		so3: so3, 
		so4: so4, 
		so5: so5, 
		so6: so6, 
		so7: so7, 
		so8: so8, 
		sufx_so1: sufx_so1, 
		sufx_so2: sufx_so2, 
		sufx_so3: sufx_so3, 
		sufx_so4: sufx_so4,  
		sufx_so5: sufx_so5,
		sufx_so6: sufx_so6,
		sufx_so7: sufx_so7,
		sufx_so8: sufx_so8,
		so_fab_code: so_fab_code,
		so_length: so_length,
		marker_name: marker_name,
		marker_fab_code: marker_fab_code,
		marker_util: marker_util,
		marker_length_y: marker_length_y,
		marker_length_in: marker_length_in,
		num_layers: num_layers,
		marker_page_from: marker_page_from,
		marker_page_to: marker_page_to,
		flag_recut: flag_recut,
		prob_code: prob_code,
		ret_FileName: ret_FileName,
		badge_num: badge_num,
		trim_comp1: trim_comp1,
		trim_length1: trim_length1,
		trim_comp2: trim_comp2,
		trim_length2: trim_length2,
		trim_comp3: trim_comp3,
		trim_length3: trim_length3,
		comments: comments,
		num_recs: num_recs,
		user_recs: user_recs
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#dw_submit').attr("disabled", false);
		$('#MU_Input_Form')[0].reset();
		document.getElementById("progressBar").value = 0;
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


function showUploadFile(field_id) {
	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/markerlog.php";
	var action = "showedit";

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}




function uploadFile() {
	var marker_name = document.getElementById('marker_name').value;
	var file = document.getElementById("fileToUpload").files[0];
	//alert(file.name+" | "+file.size+" | "+file.type);
	if (file) {
		var formdata = new FormData();
		formdata.append("fileToUpload", file);
		formdata.append("marker_name", marker_name);
		var ajax = new XMLHttpRequest();
		ajax.upload.addEventListener("progress", progressHandler, false);
		ajax.addEventListener("load", completeHandler, false);
		ajax.addEventListener("error", errorHandler, false);
		ajax.addEventListener("abort", abortHandler, false);
		ajax.open("POST", "ajax/file_upload_parser.php");
		ajax.send(formdata);
	} else {
		document.getElementById("status").innerHTML = "No file selected";
		document.getElementById("progressBar").value = 0;
	}
}

function progressHandler(event) {
	var percent = (event.loaded / event.total) * 100;
	document.getElementById("progressBar").value = Math.round(percent);
	document.getElementById("status").innerHTML = Math.round(percent)+"% uploaded... please wait";
	
	if (document.getElementById("status").innerHTML != "Upload is Complete") {
		document.getElementById("progressBar").value = 0;	
	}

}

function completeHandler(event) {
	document.getElementById("status").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 100;
}

function errorHandler(event) {
	document.getElementById("status").innerHTML = "Upload Failed";
	document.getElementById("progressBar").value = 0;
}

function abortHandler(event) {
	document.getElementById("status").innerHTML = "Upload Aborted";
	document.getElementById("progressBar").value = 0;
}


