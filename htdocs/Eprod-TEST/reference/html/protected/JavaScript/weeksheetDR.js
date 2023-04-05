

function sendValue(){
	var weekOf = document.getElementById('weekOf').value;
	var team = document.getElementById('team').value;
	
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	//$('#dataDiv').html("<img src='images/waterjet.gif' />");
	
	$.post("ajax/weeksheetDR.php",{ weekOf: weekOf, team: team },
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
