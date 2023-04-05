function doOnLoads() {
	//window.resizeTo(600,600);
	//focus();selTeam.focus();
	var id_so = document.getElementById('id_so_TXT').value;	
	if (id_so != '') {
		lookupRecord();
	} else {
		focus();id_so_TXT.focus();
	}
	
	
	
}


function searchKeyPress(e) {
	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById('submit').click();
	}
}

function lookupRecord() {
	var id_so = document.getElementById('id_so_TXT').value;

	$.post("../ajax/GarmentTracking.php",{ id_so: id_so},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");	
	//focus();missingItem.focus();
}
