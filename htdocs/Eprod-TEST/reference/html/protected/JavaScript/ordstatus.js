
function showStatusChange(){
	showOnLoad();
}


function sortColumnBy(sort_fieldC){
	
	var url = "ajax/ordstatus.php";
	var show_status = document.getElementById('show_status').value;
	var company_code = document.getElementById('company_code').value;
	var sort_dir_flagC = document.getElementById('sortDirFlag').value;
	//var sort_fieldC = document.getElementById('sortField').value;

	//var sort_fieldC = document.getElementById('sortField').value;


	//alert(sort_fieldC);
	//alert(sort_dir_flagC);

	if (sort_dir_flagC == 'desc') {
		sort_dir_flagC = 'asc';
		document.getElementById('sortDirFlag').value = 'asc';
	} else {
		sort_dir_flagC = 'desc';
		document.getElementById('sortDirFlag').value = 'desc';
	}
	document.getElementById('sortField').value = sort_fieldC;

	//alert(sort_fieldC);
	//$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{ action: "show", show_status: show_status, company_code: company_code, sort_fieldC: sort_fieldC, sort_dir_flagC: sort_dir_flagC},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");



}


function showOnLoad(){
	var url = "ajax/ordstatus.php";
	var show_status = document.getElementById('show_status').value;
	var company_code = document.getElementById('company_code').value;
	var sort_dir_flagC = document.getElementById('sortDirFlag').value;
	var sort_fieldC = document.getElementById('sortField').value;

	if (sort_dir_flagC == 'desc') {
			sort_dir_flagC = 'asc';
			document.getElementById('sortDirFlag').value = 'asc';
		} else {
			sort_dir_flagC = 'desc';
			document.getElementById('sortDirFlag').value = 'desc';
		}
		document.getElementById('sortField').value = sort_fieldC;

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: "show", show_status: show_status, company_code: company_code, sort_fieldC: sort_fieldC, sort_dir_flagC: sort_dir_flagC},
	function(data){
		$('#dataDiv').html(data.returnValue);
		}, "json");
}


function activateSaveButton(rowid){
	document.getElementById('saveButton__'+rowid).disabled = false;

}

function check() {
	var choice_value;

	if (document.getElementById('r1').checked) {
		choice_value = document.getElementById('r1').value;
	} else if (document.getElementById('r2').checked) {
		choice_value = document.getElementById('r2').value;
	} else if (document.getElementById('r3').checked) {
		choice_value = document.getElementById('r3').value;
	}

}

function rating() {
	var choice_value;

	if (document.getElementById('r1').checked) {
		choice_value = document.getElementById('r1').value;
	} else if (document.getElementById('r2').checked) {
		choice_value = document.getElementById('r2').value;
	} else if (document.getElementById('r3').checked) {
		choice_value = document.getElementById('r3').value;
	}
}




function ConfirmChange(rowid){
	var url = "ajax/ordstatus.php";
	var txt;
	var valueSelected;
	var company_code = document.getElementById('company_code').value;
	//var r = confirm("Are you sure you want to change the order status?");


	//if (r == true) {
	    //txt = "You pressed OK!";
	    //alert(txt);
		if (document.getElementById('r1__'+rowid).checked) {
			//alert('A was selected');
			valueSelected = 'A';

		}
		if (document.getElementById('r2__'+rowid).checked) {
			//alert('H was selected');
			valueSelected = 'H';

		}
		if (document.getElementById('r3__'+rowid).checked) {
			//alert('X was selected');
			valueSelected = 'X';
		}
	

		$.post(url,{ action: "saveRecord", company_code: company_code, rowid: rowid, valueSelected: valueSelected },
		function(data){
			$('#retVal__'+rowid).html(data.returnValue);
			document.getElementById('saveButton__'+rowid).disabled = true;	
			}, "json");

	//	reload

}

////////////////////////////////////////
////////Underneath is not needed ///////
////////////////////////////////////////

/*
function showRadioButtons(){
	var url = "ajax/ordstatus.php";
	var show_status = document.getElementById('show_status').value;
	var sort_dir_flagC = document.getElementById('sortDirFlag').value;
	var sort_fieldC = document.getElementById('sortField').value;
	var	radioButtonsAXH = ['<input type="radio" name="ButtonA" value="A">','<input type="radio" name="ButtonX" value="X"> ','<input type="radio" name="ButtonH" value="H">'];
	var RadioButtonA = radioButtonsAXH[0];
	var RadioButtonX = radioButtonsAXH[1];
	var RadioButtonH = radioButtonsAXH[2];

	if( document.getElementById('RadioButtons').value == 0){
			document.getElementById('RadioButtons').innerHTML = RadioButtonA;
			document.getElementById('RadioButtons').innerHTML = RadioButtonX;
			document.getElementById('RadioButtons').innerHTML = RadioButtonH;
	} else {
		alert('Value did not equal zero???');
	}

	$.post(url,{ action: "show", show_status: show_status, sort_fieldC: sort_fieldC, sort_dir_flagC: sort_dir_flagC, RadioButtonA: RadioButtonA, RadioButtonX: RadioButtonX, RadioButtonH: RadioButtonH, radioButtonsAXH: radioButtonsAXH },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

*/


/*

function deleteRecord(rowid) {
	var r = confirm("Are you sure you want to delete this record?");
	var url = "ajax/partsinv.php";	
	var action = "deleteRecord";

	if (r==true) {
		$.post(url,{ action: action, deleteRecord: 1, rowid: rowid },
		function(data){
			$('#delete_' + rowid).html(data.returnValue);
		}, "json");
		
		location.reload();
	}
}


function saveStatus(field_id){
	var url = "ajax/partsinv.php";
	//var show_status = document.getElementById('show_status').value;
	var field_value = document.getElementById(field_id).innerHTML;
	var newStatus = document.getElementById(field_id).value;

	if(newStatus != show_status){//confirm change of status
		if(confirm("Are you sure you want to change the status of this inventory part?")){
			alert("SAVED!");
		}//end if
		else{
			document.getElementById(field_id).selectedIndex = show_status;
			return;
		}//end else
	}//end if

	$.post(url,{ action: "saveStatus", field_id: field_id, field_value: field_value, newStatus: newStatus, show_status: show_status },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json"); 

	function refreshPage(){
    window.location.reload();
} 


} //end saveStatus


function sortBy(sort_field){
	var show_status = document.getElementById('show_status').value;
	var sort_dir_flag = document.getElementById('sortDirFlag').value;
	//var show_team = document.getElementById('filterTeam').value;
	var sort_dir = "";
	var url = "ajax/partsinv.php";
	if (sort_dir_flag == '0') {
		sort_dir = 'asc';
		document.getElementById('sortDirFlag').value = '1';
	} else {
		sort_dir = 'desc';
		document.getElementById('sortDirFlag').value = '0';
	}

	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: "show", sort_field: sort_field, sort_dir: sort_dir },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

*/