function doOnLoads() {
	$('#div_inputform').html("<img src='images/blank_white.jpg' />");
	refreshFabricSpecRecs();
	focus();tb_id_item.focus();
}

function IdItemChange() {
	$('#tb_id_item').autocomplete(
	{
		source: "ajax/markerlog_matllookup.php",
		minLength: 1
	});	
}

function lookupFabSpec(){
	var url = "ajax/fabspec.php";
	var id_item = document.getElementById('tb_id_item').value;
		
	//$('#div_inputform').html("LOADING");
	$('#div_inputform').html("<img src='images/loading01.gif' />");
	
	$.post(url,{ lookupFabSpec: 1, id_item: id_item },
	function(data){
		$('#div_inputform').html(data.returnValue);
//		//$('#so_fab_code').focus();
//		//$('#marker_name').focus();
	}, "json");
}

function insertFabSpec(){
	var url = "ajax/fabspec.php";
	var id_item = document.getElementById('tb_id_item').value;
	var marker_width = document.getElementById('tb_marker_width').value;
	var max_num_layers = document.getElementById('tb_max_num_layers').value;
	var max_marker_length = document.getElementById('tb_max_marker_length').value;
	var table1 = document.getElementById('cb_table1').checked;
	var table2 = document.getElementById('cb_table2').checked;
	var table3 = document.getElementById('cb_table3').checked;
	var table4 = document.getElementById('cb_table4').checked;
	var direction_length = document.getElementById('sel_direction_length').value;
	var length_add = document.getElementById('tb_length_add').value;
	var knife_type = document.getElementById('sel_knife_type').value;
	var open_tubular = document.getElementById('sel_open_tubular').value;
	var layers_paper = document.getElementById('tb_layers_paper').value;
	var notes = document.getElementById('ta_notes').value;
	flag_table_1 = '';
	flag_table_2 = '';
	flag_table_3 = '';
	flag_table_4 = '';

	if (table1 == true){
		flag_table_1 = 'T';
	}
	if (table2 == true){
		flag_table_2 = 'T';
	}
	if (table3 == true){
		flag_table_3 = 'T';
	}
	if (table4 == true){
		flag_table_4 = 'T';
	}

	if (!IsNumeric(marker_width) && marker_width != '') {
		alert("Marker Width Invalid!");
		return;
	}

	if (!IsNumeric(max_num_layers) && max_num_layers != '') {
		alert("Max # of Layers Invalid!");
		return;
	}

	//if (!StripIllegalChars(max_marker_length) && max_marker_length != '') {
	//	alert("Max Marker Length Invalid!");
	//	return;
	//}

	if (direction_length == 'SELECT') {
		alert("You must select a Direction for Length!");
		return;
	}

	if (!IsNumeric(length_add) && length_add != '') {
		alert("Additional Length Invalid!");
		return;
	}

	if (knife_type == 'SELECT') {
		alert("You must select a Knife Type!");
		return;
	}

	if (open_tubular == 'SELECT') {
		alert("You must select an Open or Tubular Cloth Type!");
		return;
	}

	if (!IsNumeric(layers_paper) && layers_paper != '') {
		alert("# Layers of Paper Invalid!");
		return;
	}


	$.post(url,{ insertFabSpec: 1, 
		id_item: id_item, 
		marker_width: marker_width, 
		max_num_layers: max_num_layers, 
		max_marker_length: max_marker_length,
		flag_table_1: flag_table_1,
		flag_table_2: flag_table_2,
		flag_table_3: flag_table_3,
		flag_table_4: flag_table_4,
		direction_length: direction_length,
		length_add: length_add,
		knife_type: knife_type,
		open_tubular: open_tubular,
		layers_paper: layers_paper,
		notes: notes
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		//$('#so_fab_code').focus();
		//$('#marker_name').focus();
	}, "json");
}

function updateFabSpec(){
	var url = "ajax/fabspec.php";
	var fs_rowid = document.getElementById('h_fs_rowid').value;
	var id_item = document.getElementById('tb_id_item').value;
	var marker_width = document.getElementById('tb_marker_width').value;
	var max_num_layers = document.getElementById('tb_max_num_layers').value;
	var max_marker_length = document.getElementById('tb_max_marker_length').value;
	var table1 = document.getElementById('cb_table1').checked;
	var table2 = document.getElementById('cb_table2').checked;
	var table3 = document.getElementById('cb_table3').checked;
	var table4 = document.getElementById('cb_table4').checked;
	var direction_length = document.getElementById('sel_direction_length').value;
	var length_add = document.getElementById('tb_length_add').value;
	var knife_type = document.getElementById('sel_knife_type').value;
	var open_tubular = document.getElementById('sel_open_tubular').value;
	var layers_paper = document.getElementById('tb_layers_paper').value;
	var notes = document.getElementById('ta_notes').value;
	flag_table_1 = '';
	flag_table_2 = '';
	flag_table_3 = '';
	flag_table_4 = '';

	if (table1 == true){
		flag_table_1 = 'T';
	}
	if (table2 == true){
		flag_table_2 = 'T';
	}
	if (table3 == true){
		flag_table_3 = 'T';
	}
	if (table4 == true){
		flag_table_4 = 'T';
	}

	if (!IsNumeric(marker_width) && marker_width != '') {
		alert("Marker Width Invalid!");
		return;
	}

	if (!IsNumeric(max_num_layers) && max_num_layers != '') {
		alert("Max # of Layers Invalid!");
		return;
	}

	//if (!StripIllegalChars(max_marker_length) && max_marker_length != '') {
	//	alert("Max Marker Length Invalid!");
	//	return;
	//}

	if (direction_length == 'SELECT') {
		alert("You must select a Direction for Length!");
		return;
	}

	if (!IsNumeric(length_add) && length_add != '') {
		alert("Additional Length Invalid!");
		return;
	}

	if (knife_type == 'SELECT') {
		alert("You must select a Knife Type!");
		return;
	}

	if (open_tubular == 'SELECT') {
		alert("You must select an Open or Tubular Cloth Type!");
		return;
	}

	if (!IsNumeric(layers_paper) && layers_paper != '') {
		alert("# Layers of Paper Invalid!");
		return;
	}


	$.post(url,{ updateFabSpec: 1, 
		id_item: id_item, 
		fs_rowid: fs_rowid, 
		marker_width: marker_width, 
		max_num_layers: max_num_layers, 
		max_marker_length: max_marker_length,
		flag_table_1: flag_table_1,
		flag_table_2: flag_table_2,
		flag_table_3: flag_table_3,
		flag_table_4: flag_table_4,
		direction_length: direction_length,
		length_add: length_add,
		knife_type: knife_type,
		open_tubular: open_tubular,
		layers_paper: layers_paper,
		notes: notes
	},
	function(data){
		$('#dataDiv').html(data.returnValue);
		//$('#so_fab_code').focus();
		//$('#marker_name').focus();
	}, "json");
}

function IsNumeric(strString) {
	var strValidChars = "0123456789.";
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


function refreshFabricSpecRecs() {
	var url = "ajax/fabspec.php";

	$.post(url,{ refreshFabricSpecRecs: 1},
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

function sendItemToSearchbox(item) {
	document.getElementById('tb_id_item').value=item;
	$('#tb_id_item').focus();
}
