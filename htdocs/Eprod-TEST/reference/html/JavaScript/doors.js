
function doOnLoads() {
	deptChange();
}

function deptChange(){
	var url = "ajax/doors.php";
	var action = "dept_change";
	var dept = document.getElementById('sel_filterDepartment').value;

	$.post(url,{ action: action, dept: dept },
		function(data){
			$('#dataDiv').html(data.returnValue);
		}, "json");
}



function closeDiv(div) {
	var r=confirm("Remove table from view?");
	
	if (r==true) {
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
}

var popupStatus = 0; 



