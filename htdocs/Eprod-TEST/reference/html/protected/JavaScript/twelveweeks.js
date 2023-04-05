

function sendValue(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var team = document.getElementById('team').value;
	
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	//$('#dataDiv').html("<img src='images/waterjet.gif' />");
	
	$.post("ajax/twelveweeks.php",{ df: df, dt: dt, team: team },
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
