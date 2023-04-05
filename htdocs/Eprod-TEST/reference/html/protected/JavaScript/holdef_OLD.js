
function submitForm(action){
	var YR = document.getElementById('YR').value;

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post("ajax/holdef.php",{ YR: YR, action: action },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	
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
