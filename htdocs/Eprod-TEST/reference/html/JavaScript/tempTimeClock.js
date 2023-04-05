


function doOnLoads() {
	window.resizeTo(400,600);
	focus();//selTeam.focus();
}



function searchBadge(){
	var url = "ajax/punchTracker.php";

	var id_badge = document.getElementById('id_badge').value;

	$('#btnSubmit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	
	$.post(url,{
		id_badge: id_badge 

	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnSubmit').attr("disabled", false);
		}, "json");
}