
function selLocationChange() {
	var url = "ajax/comments.php";
	var selLocation = document.getElementById('selLocation').value;
	var focusElement = "selLocation";

	if (selLocation == 'SELECT') {
		$('#formDiv').html("");
		return;
	}

	$.post(url,{ action: 'selLocationChange', selLocation: selLocation },
	function(data){
		$('#formDiv').html(data.returnValue);
		$('#'+focusElement).focus();
	}, "json");

	$('#dataDiv').html("");
}

function nextOnDash(so,sufx) {
	$('#'+so).keypress(function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#'+sufx).focus();
    	}
	});
}

function sufxEntered(){
	var url = "ajax/comments.php";
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;

	if (sufx.length == 3) {
		$.post(url,{ action: 'getBoxQty', so: so, sufx: sufx},
		function(data){
			$('#td_qty_in_box').html(data.returnValue);
		}, "json");
		$('#qty_in_box').focus();
	}
}

function saveOrdHdrComment(id_ord){
	var url = "ajax/comments.php";
	var selLocation = document.getElementById('selLocation').value;
	var est_ship_date = document.getElementById('tb_est_ship_date__'+id_ord).value;
	var old_ship_date = document.getElementById('tb_old_ship_date__'+id_ord).value;
	var late_code = document.getElementById('sel_late_code__'+id_ord).value;
	var comments = document.getElementById('comments_TXT__'+id_ord).value;
	var flag_del = document.getElementById('sel_flag_del__'+id_ord).value;
	

	if (selLocation == 'SELECT') {
		alert("Select Location!");
		return;
	}

	$.post(url,{ action: 'saveOrdHdrComment', 
		selLocation: selLocation, 
		id_ord: id_ord, 
		est_ship_date: est_ship_date, 
		old_ship_date: old_ship_date, 
		late_code: late_code,
		comments: comments, 
		flag_del: flag_del
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");

}


function getSoComments(){
	var url = "ajax/comments.php";
	var so = document.getElementById('so').value;
	var sufx = document.getElementById('sufx').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'getSoComments', so: so, sufx: sufx},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	clearInputs();
}

function getOrderComments(){
	var url = "ajax/comments.php";
	var id_ord = document.getElementById('id_ord').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: 'getOrderComments', id_ord: id_ord},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
	clearInputs();
}

function clearForm() {
	document.getElementById('id_ord').value = '';
	$('#dataDiv').html("");

	selCompanyChange();
}


function doOnLoads() {
	//focus();so.focus();
	selLocationChange();
}




