


function doOnLoads() {
	window.resizeTo(400,600);
	focus();//selTeam.focus();
}



function searchItem(){
	var url = "ajax/itemInquiry.php";

	var id_loc = document.getElementById('selLoc').value;
	var id_item = document.getElementById('id_item').value;

	$('#btnSubmit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	
	$.post(url,{
		id_item: id_item, id_loc: id_loc 

	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('#btnSubmit').attr("disabled", false);
		}, "json");
}