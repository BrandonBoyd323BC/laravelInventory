function doOnLoads() {
	numRecsChange();
}

function numRecsChange(){
	var url = "ajax/rollCountVerify.php";
	var action = "numRecsChange";
	var user_recs = document.getElementById('user_recs').value;
	var num_recs = document.getElementById('num_recs').value;

	$.post(url,{ action: action, user_recs: user_recs, num_recs: num_recs },
		function(data){
			$('#dataAddDiv').html(data.returnValue);
	}, "json");
	document.getElementById('tb_item').focus();
}

function clearForm() {
	document.getElementById('tb_item').value = '';
	document.getElementById('tb_qty').value = '';
	$('#dataDiv').html("");
	//numRecsChange();
}

function itemCodeChange() {
	$('#tb_item').autocomplete(
	{
		source: "ajax/rollCountVerify_matllookup.php",
		minLength: 1
	});	
}

function sendAddValue(){
	var choice_value = '';
	var url = "ajax/rollCountVerify.php";	
	var action = "insertRecord";
	var item = document.getElementById('tb_item').value;
	var findings = document.getElementById('text_findings').value;

	if (document.getElementById('V').checked) {
		choice_value = 'V';
	}  
	if (document.getElementById('D').checked) {
		choice_value = 'D';
	}

	if (item == '')	{
		alert("Item cannot be left blank.");
		return;
	}
	if (choice_value == '')	{
		alert("Please choose either Verified or Discrepancy.");
		return;
	}

	$('#button_addRecord').attr("disabled", true);
	$('#dataAddDiv').html("<img src='images/loading01.gif' />");

	$.post(url,{ action: action, item: item, choice: choice_value, findings: findings },
	function(data){
		$('#dataAddDiv').html(data.returnValue);
		$('#button_addRecord').attr("disabled", false);
		document.getElementById('tb_item').value = '';
		document.getElementById("V").checked = false;
		document.getElementById("D").checked = false;
		document.getElementById('text_findings').value = '';
		document.getElementById("tr_textFindings").style.display = "none";
		numRecsChange();
		document.getElementById('tb_item').focus();
	}, "json");
}

$(document).ready(function()
{
	$('#tb_item').autocomplete(
	{
		source: "ajax/rollCountVerify_matllookup.php",
		minLength: 1
	});

	$('input[type=radio][name=choice]').change(function() {
	    if (this.value == 'V') {
	        document.getElementById("tr_textFindings").style.display = "none";
	    }
	    else if (this.value == 'D') {
	        //alert("D");
	        document.getElementById("tr_textFindings").style.display = "table-row";
	    }
	});
});

