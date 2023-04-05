
///////////////////////////////////////
/////OLD SITE DO NOT MAINTAIN
//////////////////////////////////////
///////////////////////////////////////
/////OLD SITE DO NOT MAINTAIN
//////////////////////////////////////
///////////////////////////////////////
/////OLD SITE DO NOT MAINTAIN
//////////////////////////////////////


function doOnLoads() { //need
	numRecsChange();
}

function nextOnDash(soid,sufxid) { //need
	$('#'+soid).keypress(function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();	    	
	    	$('#'+sufxid).focus();
    	}
	});
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function showSoInputRow(tr_so) { //need
	document.getElementById(tr_so).style.display = 'table-row';
}

function showEditField(field_id) { //need
	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/qa_inspection.php";
	var action = "showedit";

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function cancelEditField(field_id, field_value){//need
	var url = "ajax/qa_inspection.php";	
	var action = "canceledit";

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function saveEditField(field_id){//need
	var new_value = document.getElementById(field_id+'_TXT').value;
	var url = "ajax/qa_inspection.php";	
	var action = "saveedit";
	var compare = field_id;
	String(compare);
	compare = compare.slice(0, compare.indexOf("__"));

	if(compare == 'INSPECTION_TYPE'){
		if (new_value.length > 20){
			alert("Entry too long.");
			return;
		}
		new_value = new_value.toUpperCase();
		if ( (new_value != 'FIRST ITEM') && (new_value != '100%') && (new_value != 'RANDOM') && (new_value != 'SHIPPING') && (new_value != 'STOCK SAMPLE') ){
			alert("Inspection Type needs to be 'First Item', 'Random', '100%', 'Shipping', or 'Stock Sample' only!");
			return;
		}
	}

	if(compare == 'ID_SO'){
		if(IsNumeric(new_value) == false || new_value.length > 9){
			alert("Entry must be Numeric and less than 9 digits.");
			return;
		}
	}

	if(compare == 'ID_SO_SUFFIX'){
		if(IsNumeric(new_value) == false || new_value.length > 3){
			alert("Entry must be Numeric and less than 3 digits.");
			return;
		}
	}

	if(compare == 'ID_ORD'){
		if(IsNumeric(new_value) == false || new_value.length > 9){
			alert("Entry must be Numeric and less than 9 digits.");
			return;
		}
	}

	if(compare == 'FLAG_PASS_FAIL'){
		if(new_value.length > 1 && (new_value != 'P' || new_value != 'F') ){
			alert("Single character.  P = Pass, F = Fail");
			return;
		}
	}

	if(compare == 'NO_LINES'){
		if(new_value == 0 || new_value > 999){
			alert("Must be greater than 0 and less than 999.");
			return;
		}
		if(IsNumeric(new_value) == false){
			alert("Must be a number between 0 and 999.");
			return;
		} 
	}//end compare NO_LINES

	if(compare == 'QTY_PASS'){
		if(IsNumeric(new_value) == false || new_value.length > 4){
			alert("Entry must be Numeric and less than 3 digits.");
			return;
		}
	}

	if(compare == 'QTY_FAIL'){
		if(IsNumeric(new_value) == false || new_value.length > 4){
			alert("Entry must be Numeric and less than 3 digits.");
			return;
		}
	}

	if(compare == 'TEAM_BADGE'){
		if( (IsNumeric(new_value) == false) || (new_value.length > 3)){
			alert("Entry must be Numeric and less than 3 digits.");
			return;
		}
	}

	if(compare == 'INSP_INITIALS'){
		if (new_value.length > 4){
			alert("Entry too long.");
			return;
		}
	}


	if (new_value.length > 250) {
		alert("Value too long. Must be less than 250 characters");
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

function deleteRecord(rowid) {//need
	var r = confirm("Are you sure you want to delete this record?");
	var url = "ajax/qa_inspection.php";	

	if (r==true) {
		$.post(url,{ deleteRecord: 1, rowid: rowid },
		function(data){
			$('#delete_' + rowid).html(data.returnValue);
		}, "json");
	}
}
/*
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
}
*/
function numRecsChange() { //need
	var url = "ajax/qa_inspection.php";
	var num_recs = document.getElementById('num_recs').value;
	var user_recs = document.getElementById('user_recs').value;

	var searchInspType = document.getElementById('searchInspType').value;
	var searchOrd = document.getElementById('searchOrd').value;
	var searchTeam = document.getElementById('searchTeam').value;
	var searchPF = document.getElementById('searchPF').value;
	var searchDate = document.getElementById('searchDate').value;

	var search_so = document.getElementById("searchSO").value;

	if (search_so == '') {
		search_so = 'ALL';
		document.getElementById('searchSO').value = search_so;
	}
	if (searchOrd == '') {
		searchOrd = 'ALL';
		document.getElementById('searchOrd').value = searchOrd;
	}
	if (searchTeam == '') {
		searchTeam = 'ALL';
		document.getElementById('searchTeam').value = searchTeam;
	}

	$.post(url,{ numRecsChange: 1, num_recs: num_recs, user_recs: user_recs, search_so: search_so, searchInspType: searchInspType, searchOrd: searchOrd, searchTeam: searchTeam, searchPF: searchPF, searchDate: searchDate },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

/*
function markerFabCodeChange() {
	$('#marker_fab_code').autocomplete(
	{
		source: "ajax/markerlog_matllookup.php",
		minLength: 1
	});	
}

function recutCheckBoxChange() {
	var flag_recut = document.getElementById('flag_recut').checked;
	if (flag_recut == true){
		document.getElementById('tr_probcode').style.display = 'table-row';
		document.getElementById('tr_badgenum').style.display = 'table-row';
		document.getElementById('so_length').value = '0';
	} else {
		document.getElementById('prob_code').value = '';
		document.getElementById('badge_num').value = '';
		document.getElementById('tr_probcode').style.display = 'none';
		document.getElementById('tr_badgenum').style.display = 'none';
		soFabCodeChange()
	}
}
*/
/*
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
	var flag_recut = document.getElementById('flag_recut').checked;
	var prob_code = document.getElementById('prob_code').value;
	var badge_num = document.getElementById('badge_num').value;
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
		flag_recut: flag_recut,
		prob_code: prob_code,
		ret_FileName: ret_FileName,
		badge_num: badge_num,
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
*/
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

/*
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
*/


function clearInputs(){//clears all text input except inspector initials
	var elements = document.getElementsByTagName("input");
	for (var i=0; i < elements.length; i++) {
    	if (elements[i].type == "text" && elements[i].id != "inspecInitals") {
    		elements[i].value = "";
  		}
	}
	document.getElementById("prodDevNotes").value = "";
	document.getElementById("descText").value = "";
	document.getElementById("insp_type").value = "";
	document.getElementById("trWI").style.display = "none";
	document.getElementById("trLS").style.display = "none";
}

function hideElements(){//hides and adds block elems based on inspection type
	var value = "";
    value = document.getElementById("insp_type").value

	document.getElementById("prodDevSig").style.display = "none";
	document.getElementById("label_prodDevSig").style.display = "none";
	document.getElementById("prodDevNotes").style.display = "none";
	document.getElementById("label_prodDevNotes").style.display = "none";
	document.getElementById("noLines").style.display = "none";
	document.getElementById("label_noLines").style.display = "none";
	document.getElementById("label_100_pass").style.display = "none";
	document.getElementById("label_100_fail").style.display = "none";
	document.getElementById("pass100").style.display = "none";
	document.getElementById("fail100").style.display = "none";
	document.getElementById("passFail_P").style.display = "inline-block";
	document.getElementById("passFail_F").style.display = "inline-block";
	document.getElementById("label_passFail_P").style.display = "inline-block";
	document.getElementById("label_passFail_F").style.display = "inline-block";
	document.getElementById("txt_ID_ORD").value = "";
	document.getElementById("txt_ID_ORD").readOnly = false;
	document.getElementById("txt_NAME_CUST").value = "";
	
	document.getElementById("txt_ID_ITEM_PAR").style.display = "block";
	//document.getElementById("label_soNumber").style.display = "block";
	document.getElementById("label_itemNumber").style.display = "block";
	document.getElementById("soRow").style.display = "table-row";


	if(value == "Stock Sample" ){
		document.getElementById("noLines").style.display = "block";
		document.getElementById("label_noLines").style.display = "block";
		document.getElementById("soRow").style.display = "none";
		//document.getElementById("soNumber").style.display = "none";
		//document.getElementById("soNumber_suffix").style.display = "none";
		document.getElementById("txt_ID_ITEM_PAR").style.display = "none";
		//document.getElementById("label_soNumber").style.display = "none";
		document.getElementById("label_itemNumber").style.display = "none";
		document.getElementById("txt_ID_ORD").value = "Sample";
		document.getElementById("txt_ID_ORD").readOnly = true;
		document.getElementById("txt_NAME_CUST").focus();
	}

	if(value == "Shipping" ){
		document.getElementById("noLines").style.display = "block";
		document.getElementById("label_noLines").style.display = "block";
		document.getElementById("soRow").style.display = "none";
		//document.getElementById("soNumber").style.display = "none";
		//document.getElementById("soNumber_suffix").style.display = "none";
		document.getElementById("txt_ID_ITEM_PAR").style.display = "none";
		//document.getElementById("label_soNumber").style.display = "none";
		document.getElementById("label_itemNumber").style.display = "none";
		document.getElementById("txt_ID_ORD").focus();
	}
	if(value == "100%"){
		document.getElementById("label_100_pass").style.display = "block";
		document.getElementById("label_100_fail").style.display = "block";
		document.getElementById("pass100").style.display = "block";
		document.getElementById("fail100").style.display = "block";
		document.getElementById("passFail_P").style.display = "none";
		document.getElementById("passFail_F").style.display = "none";
		document.getElementById("label_passFail_P").style.display = "none";
		document.getElementById("label_passFail_F").style.display = "none";
		document.getElementById("soNumber").focus();
	}
	//alert(value)
	if(value == "First Item" || value == "Random"){
		document.getElementById("soNumber").focus();
	}
	
}//end hideEmements

function getSoInfo(){//looks up info from SO number
	//alert("inFucntion");
	var soNumber = document.getElementById("soNumber").value;
	var sufx = document.getElementById("soNumber_suffix").value;
	//alert("declared");
	if (sufx.length == 3) {
		document.getElementById("trWI").style.display = "table-row";
		document.getElementById("trLS").style.display = "table-row";
		$('#div_itemNumber').html("<font>LOADING</font>");
		$('#div_orderNumber').html("<font>LOADING</font>");
		$('#div_nameCust').html("<font>LOADING</font>");
		$('#div_WiLink').html("<font>LOADING</font>");
		//$('#div_LabelLink').html("<font>LOADING</font>");

		$.post("ajax/qa_inspection.php",{action: 'getSoInfo', field: 'ID_ITEM_PAR', soNumber:soNumber, sufx:sufx },
		function(data){
			$('#div_itemNumber').html(data.returnValue);
		}, "json");

		$.post("ajax/qa_inspection.php",{action: 'getSoInfo', field: 'ID_ORD', soNumber:soNumber, sufx:sufx },
		function(data){
			$('#div_orderNumber').html(data.returnValue);
		}, "json");

		$.post("ajax/qa_inspection.php",{action: 'getSoInfo', field: 'NAME_CUST', soNumber:soNumber, sufx:sufx },
		function(data){
			$('#div_nameCust').html(data.returnValue);
		}, "json");

		$.post("ajax/qa_inspection.php",{action: 'getSoInfo', field: 'NAME_FILE', soNumber:soNumber, sufx:sufx },
		function(data){
			$('#div_WiLink').html(data.returnValue);
		}, "json");
		
	}//end if
}//end getsoinfo

function getOrdInfo(){//looks up info from ORD number
	var ordNumber = document.getElementById("txt_ID_ORD").value;

	if (ordNumber.length == 6) {
		document.getElementById("trWI").style.display = "table-row";
		document.getElementById("trLS").style.display = "table-row";
		$('#div_itemNumber').html("<font>LOADING</font>");
		//$('#div_orderNumber').html("<font>LOADING</font>");
		$('#div_nameCust').html("<font>LOADING</font>");
		$('#div_WiLink').html("<font>LOADING</font>");
		//$('#div_LabelLink').html("<font>LOADING</font>");

		$.post("ajax/qa_inspection.php",{action: 'getOrdInfo', field: 'ID_ITEM_PAR', ordNumber:ordNumber },
		function(data){
			$('#div_itemNumber').html(data.returnValue);
		}, "json");

		/*$.post("ajax/qa_inspection.php",{action: 'getOrdInfo', field: 'ID_ORD', soNumber:soNumber, sufx:sufx },
		function(data){
			$('#div_orderNumber').html(data.returnValue);
		}, "json");*/

		$.post("ajax/qa_inspection.php",{action: 'getOrdInfo', field: 'NAME_CUST', ordNumber:ordNumber },
		function(data){
			$('#div_nameCust').html(data.returnValue);
		}, "json");

		$.post("ajax/qa_inspection.php",{action: 'getOrdInfo', field: 'NAME_FILE', ordNumber:ordNumber },
		function(data){
			$('#div_WiLink').html(data.returnValue);
		}, "json");
	}//end if
}//end getOrdinfo

function insertNewRecord(){//insert a new inspection record into table
	var url = "ajax/qa_inspection.php";	

	var choice_value ='';
	if(document.getElementById('passFail_P').checked){
		choice_value = document.getElementById('passFail_P').value;
	}else if(document.getElementById('passFail_F').checked){
		choice_value = document.getElementById('passFail_F').value;
	}
	else{
		choice_value = " ";
	}

	var insp_type = document.getElementById('insp_type').value;
	var soNumber = document.getElementById('soNumber').value;
	var soNumber_suffix = document.getElementById('soNumber_suffix').value;
	var itemNumber = document.getElementById('txt_ID_ITEM_PAR').value;
	var orderNumber = document.getElementById('txt_ID_ORD').value;
	var nameCust = document.getElementById('txt_NAME_CUST').value;
	var passFail = choice_value;
	var descText = document.getElementById('descText').value;
	var teamBadge = document.getElementById('teamBadge').value;
	var inspecInitals = document.getElementById('inspecInitals').value;
	var pass100 = document.getElementById('pass100').value;
	var fail100 = document.getElementById('fail100').value;
	var noLines = document.getElementById('noLines').value;
	var num_recs = document.getElementById('num_recs').value;
	var user_recs = document.getElementById('user_recs').value;

	var searchInspType = document.getElementById('searchInspType').value;
	var searchOrd = document.getElementById('searchOrd').value;
	var searchTeam = document.getElementById('searchTeam').value;
	var searchPF = document.getElementById('searchPF').value;
	var search_so = document.getElementById('searchSO').value;

	insp_type = insp_type.toUpperCase();//for uniform case to be used when reporting

	//error/blank field checking
	if(insp_type == '' || inspecInitals == '') {
		alert("Required Field Missing!");
		return;
	}

	if( (insp_type == 'firstItem' || insp_type == 'random') && (itemNumber == '') ) {
		alert("Required Field Missing!");
		return;
	}

	$('#btnInsertRecord').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		//sendAddValue: '1',
		insp_type: insp_type, 
		soNumber: soNumber,  
		soNumber_suffix: soNumber_suffix, 
		itemNumber: itemNumber,
		orderNumber: orderNumber,
		nameCust: nameCust,
		passFail: passFail,
		descText: descText,
		teamBadge: teamBadge,
		inspecInitals: inspecInitals,
		pass100: pass100,
		fail100: fail100,
		noLines: noLines,
		num_recs: num_recs,
		user_recs: user_recs,
		search_so: search_so, 
		searchInspType: searchInspType, 
		searchOrd: searchOrd,
		searchTeam: searchTeam, 
		searchPF: searchPF
	},
	
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnInsertRecord').attr("disabled", false);
		$('#SO_Log_Input_Form')[0].reset();
		//clearing rest of inputs that dont clear automagically
			document.getElementById('soNumber').value = "";
			document.getElementById('soNumber_suffix').value = "";
			document.getElementById('txt_ID_ITEM_PAR').value = "";
			document.getElementById('txt_ID_ORD').value = "";
			document.getElementById('txt_NAME_CUST').value = "";
			document.getElementById('trWI').style.display = "none";
			document.getElementById('insp_type').focus();
		}, "json");

}//end insertNewRecord

function approveDayInspection(){
	var url = "ajax/qa_inspection.php";	
	var dateApproving = document.getElementById('searchDate').value;;

	$('#btnApproveDayInspection').attr("disabled", true);
	$.post(url,{
		dateApproving: dateApproving
	},
	function(data){
		//$('#dataDiv').html(data.returnValue);
		//$('#btnApproveDayInspection').attr("disabled", false);
		}, "json");

}//end approveDayInspection
