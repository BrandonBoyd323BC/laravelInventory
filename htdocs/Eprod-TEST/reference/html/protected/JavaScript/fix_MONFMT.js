

function findBadRecs(){
	var table = document.getElementById('table').value;
	
	if (table == "unselected") {
		alert("Invalid Selection");
		return;
	}

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	//$('#dataDiv').html("<img src='images/waterjet.gif' />");
	
	$.post("ajax/fix_MONFMT.php",{ action: 'findBadRecs', table: table },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
}

function fixBadRecord(table, rowid, field_name, field_readable_name){
	var url = "ajax/fix_MONFMT.php";
	var field_id = field_name + "_" + rowid;

	$.post(url,{ action: 'fixBadRecord', table: table, rowid: rowid, field_name: field_name, field_readable_name: field_readable_name},
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
	$('#'+field_id).toggleClass('GOOD');
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
