
function showRecords(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var sel_ShowRec = document.getElementById('sel_ShowRec').value;
	var action = 'showRecords';

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post("ajax/lateordercodes.php",{ action: action, df: df, dt: dt, sel_ShowRec: sel_ShowRec },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	
}

function saveReasonCode(ilh_ROWID) {
	var newLC = document.getElementById('selReasonCode__'+ilh_ROWID).value;
	var action = 'saveReasonCode';

	$.post("ajax/lateordercodes.php",{ action: action, ilh_ROWID: ilh_ROWID, newLC: newLC },
	function(data){
		$('#resp_'+ilh_ROWID).html(data.returnValue);
	}, "json");

}

function doOnLoads() {
	showRecords();
}

