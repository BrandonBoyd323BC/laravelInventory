

function sendValue(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	
	$.post("ajax/attendance.php",{ df: df, dt: dt },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
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


function insertDCApprovalJS(code,badge,dateapp) {
	var earned = document.getElementById('earned').value;
	var actual = document.getElementById('actual').value;
	var comments = document.getElementById('cmts_approve').value;

	if (!document.getElementById('chk_approve').checked) {
		alert("You must check 'Approve'");
		return;
	}
	
	

	if ((code != '') && (badge != '') && (dateapp != '')){
	
		$.post("ajax/approve.php",{ action: 'insert_approv', code: code, badge: badge, dateapp: dateapp, comments: comments, earned: earned, actual: actual },
		function(data){
			$('#div_sub_approve').html(data.returnValue);
		}, "json");
	} else {
		alert('Problem');
	}
}


function insertDCApprovalHRJS(code,badge) {
	var comments = document.getElementById('cmts_approve').value;
	var dateapp = document.getElementById('da').value;


	if (!document.getElementById('chk_approve').checked) {
		alert("You must check 'Approve'");
		return;
	}
	
	

	if ((code != '') && (badge != '') && (dateapp != '')){
	
		$.post("ajax/approve.php",{ code: code, badge: badge, dateapp: dateapp, comments: comments },
		function(data){
			$('#div_sub_approve').html(data.returnValue);
			$('#chk_approve').attr("checked", false);
		}, "json");
	} else {
		alert('Problem');
	}
}





function showAlert() {
	var date = document.getElementById('da').value;
	
	alert(date);
}