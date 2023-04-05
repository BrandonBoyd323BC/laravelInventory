


function sendValue1(){
	var company = document.getElementById('company').value;
	var url = "ajax/mgmtdashG2.php";	

	alert("HI");

	$('input:submit').attr("disabled", true);
	//$('#dataDiv').html("<img src='images/loading01.gif' />");
	$('#visualization').html("<img src='images/loading01.gif' />");

	$.post(url,{ company: company },
	function(data){
		$('#visualization').html(data.returnValue);
		$('input:submit').attr("disabled", false);
		drawChart();
	}, "json");
	
	
}

//google.load("visualization", "1", {packages: ['annotatedtimeline']});
function sendValue(){
	var company = document.getElementById('company').value;
	var url = "ajax/mgmtdashG2.php";	

	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	var jsonData = $.ajax({
		url: "ajax/mgmtdashG2.php",
		dataType:"json",
		async: false
		}).responseText;
	
	google.load("visualization", "1", {packages: ['annotatedtimeline']});
	alert("jsonData: " + jsonData);
	var data = new google.visualization.DataTable(jsonData);
	alert("data: " + data);
	var annotatedtimeline = new google.visualization.AnnotatedTimeLine(document.getElementById('visualization'));
	//alert("HI2");
	annotatedtimeline.draw(data, {'displayAnnotations': true});

	$('input:submit').attr("disabled", false);
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

function IsInteger(strString)
	//  check for valid numeric strings	
	{
	var strValidChars = "0123456789-";
	var strChar;
	var blnResult = true;

	if (strString.length == 0) return false;

	//  test strString consists of valid characters listed above
	for (i = 0; i < strString.length && blnResult == true; i++) {
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1) {
         		blnResult = false;
		}
	}
	return blnResult;
}

function drawChart() {
	var data = new google.visualization.DataTable();
	data.addColumn('date', 'Date');
	data.addColumn('number', 'Sold Pencils');
	data.addColumn('string', 'title1');
	data.addColumn('string', 'text1');
	data.addColumn('number', 'Sold Pens');
	data.addColumn('string', 'title2');
	data.addColumn('string', 'text2');
	data.addRows([
		[new Date(2008, 1 ,1), 30000, undefined, undefined, 40645, undefined, undefined],
		[new Date(2008, 1 ,2), 14045, undefined, undefined, 20374, undefined, undefined],
		[new Date(2008, 1 ,3), 55022, undefined, undefined, 50766, undefined, undefined],
		[new Date(2008, 1 ,4), 75284, undefined, undefined, 14334, 'Out of Stock','Ran out of stock on pens at 4pm'],
		[new Date(2008, 1 ,5), 41476, 'Bought Pens','Bought 200k pens', 66467, undefined, undefined],
		[new Date(2008, 1 ,6), 33322, undefined, undefined, 39463, undefined, undefined]
	]);
	alert("HELLOOOO");
	var annotatedtimeline = new google.visualization.AnnotatedTimeLine(document.getElementById('visualization'));
	annotatedtimeline.draw(data, {'displayAnnotations': true, 'zoomStartTime': new Date('".$df."'), 'zoomEndTime': new Date('".$maxDate."')});
	
	
}
