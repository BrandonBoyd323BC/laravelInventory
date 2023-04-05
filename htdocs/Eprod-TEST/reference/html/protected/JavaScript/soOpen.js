
function submitForm(action){
	var so_num = document.getElementById('so_num').value;
	var sufx = document.getElementById('sufx').value;

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post("ajax/soOpen.php",{ so_num: so_num, sufx: sufx, action: action },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	
}


function showStatusChange_HDR(rowid){
	var so_status = document.getElementById('STAT_REC_SO_' + rowid).value;
	var url = "ajax/soOpen.php";

	if (so_status.substr(0, 1) == '*') {
		$('#'+'HDR_Save_'+rowid).attr("disabled", true);
	} else {
		$('#'+'HDR_Save_'+rowid).attr("disabled", false);
	}


}


function saveStatusChange_HDR(rowid){
	var so_status = document.getElementById('STAT_REC_SO_' + rowid).value;
	var action = "update_hdr";
	var url = "ajax/soOpen.php";

	so_status = so_status.replace("*", "")

	//$('#dataAddDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, so_status: so_status, rowid: rowid },
	function(data){
		$('#'+'HDR_Save_div_' +rowid).html(data.returnValue);
	}, "json");

}


function showStatusChange_OPER(rowid){
	var oper_status = document.getElementById('STAT_REC_OPER_' + rowid).value;
	var url = "ajax/soOpen.php";

	if (oper_status.substr(0, 1) == '*') {
		$('#'+'OPER_Save_'+rowid).attr("disabled", true);
	} else {
		$('#'+'OPER_Save_'+rowid).attr("disabled", false);
	}


}

function saveStatusChange_OPER(rowid){
	var oper_status = document.getElementById('STAT_REC_OPER_' + rowid).value;
	var action = "update_oper";
	var url = "ajax/soOpen.php";

	oper_status = oper_status.replace("*", "")

	//$('#dataAddDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, oper_status: oper_status, rowid: rowid },
	function(data){
		$('#'+'OPER_Save_div_' +rowid).html(data.returnValue);
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

function searchKeyPress(e) {
	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById('submit').click();
	}
}



function closeDiv(div) {
	//alert(div);
	
	var r=confirm("Remove table from view?");
	
	if (r==true) {
	
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
	
}
