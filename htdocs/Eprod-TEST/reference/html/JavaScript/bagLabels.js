function selModeChange() {
	var url = "ajax/bagLabels.php";
	var selMode = document.getElementById('selMode').value;
	//alert(selMode);

	$.post(url,{ action: 'selModeChange', selMode: selMode },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}


function idOrdEntered(evt){
	var url = "ajax/bagLabels.php";
	var id_ord = document.getElementById('id_ord').value;
	var evt = evt || window.event;
	//var code = evt.keyCode || evt.which;
	var code = evt.key || evt.which;
/*
	$('#id_ord').keypress(function(e) {
	    if (e.keyCode == 45) {
	    //if (e.which == 'Enter') {
	    	console.log('keyCode: ' + e.keyCode);
	    	e.preventDefault();
		//$('#'+sufx).focus();
    	}
	});
*/
	if (code == 'Enter'){ //scanner should append CR
		evt.preventDefault();
		return;
	}

	console.log("id_ord: "+id_ord);
	if (id_ord.length == 6) {
		console.log('id_ord.length: ' + id_ord.length);
		$.post(url,{ action: "getOrdLines", id_ord: id_ord},
		function(data){
			//$('#td_id_item').html(data.returnValue);
			$('#table_ret_form').html(data.returnValue);
			$('#selIdItem').focus();
		}, "json");
		
	}
}

function getPTBagLabels(){
	var url = "ajax/bagLabels.php";
	var id_ord = document.getElementById('hid_id_ord').value;
	var seq_line_ord = document.getElementById('selIdItem').value;
	var qty_per_bag = document.getElementById('txt_qty_per_bag').value;
	
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{ action: 'getPTBagLabels', id_ord: id_ord, seq_line_ord: seq_line_ord, qty_per_bag: qty_per_bag },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}


function scanLineItem(evt){
    var i;
    var match = false;
    var barcode = document.getElementById('hid_bc_entered').value;
    var elem = document.getElementById('selIdItem');
    var evt = evt || window.event;
    //var code = evt.keyCode || evt.which;
    var code = evt.key || evt.which;

	//console.log('barcode: ' + barcode)

    if (code == 'Enter'){ //scanner should append CR
    	//set value
        for (i=0; i<elem.options.length; i++) {
        	//console.log('elemValue: ' + elem.options[i].value)
            if (barcode == elem.options[i].value) {
                //console.log('match: ' + barcode)
                match = true;
                break;
            }
        }
        if (match){
            //elem.selectedIndex = i;
             document.getElementById('selIdItem').value = barcode;
        } else {
            elem.selectedIndex = 0;
            alert("Barcode " + barcode + " not found!");
        }

		//var e = jQuery.Event("keypress");
		//e.which = 'Enter'; //choose the one you want
		//e.keyCode = 'Enter';
		//$("#selIdItem").trigger(e);



        console.log('barcode: ' + barcode);
        document.getElementById('hid_bc_entered').value = ''; //Clear out the hidden field

        //$('#txt_qty_per_bag').focus();

    } else {
        document.getElementById('hid_bc_entered').value = barcode+code; //Append the character to hidden field
        //console.log('code: ' + code)
    }
}



//function selIdItemChange() {
//	var url = "ajax/bagLabels.php";
//	var selIdItem = document.getElementById('selIdItem').value;
	//alert(selIdItem);
//}


function nextOnDash(so,sufx) {
	$('#'+so).keypress(function(e) {
	    if (e.keyCode == 45) {
	    	e.preventDefault();
	    	$('#'+sufx).focus();
    	}
	});
}




function clearForm() {
	//document.getElementById('so').value = '';
	//document.getElementById('sufx').value = '';
	//document.getElementById('qty_in_box').value = '';
	$('#dataDiv').html("");
	$('#so').focus();
}

function doOnLoads() {
	focus();so.focus();
}

