

function submitForm(){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	var StartProdCat = document.getElementById('StartProdCat').value;
	var EndProdCat = document.getElementById('EndProdCat').value;
	var StartItem = document.getElementById('StartItem').value;
	var EndItem = document.getElementById('EndItem').value;
	var StartCustNum = document.getElementById('StartCustNum').value;
	var EndCustNum = document.getElementById('EndCustNum').value;
	var Order_Num = document.getElementById('Order_Num').value;
	var CapPCT = document.getElementById('CapPCT').value;
	var FlagDetail = document.getElementById('FlagDetail').checked;
	var FD_text = 'false';
	var FlagComments = document.getElementById('FlagComments').checked;
	var FC_text = 'false';

	if (df > dt) {
		alert('Invalid Date Range');
		return;
	}

	if (FlagDetail) {
		FD_text = 'true';
	}
	if (FlagComments) {
		FC_text = 'true';
	}

	
	$('input:submit').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");

	$.post("ajax/promise.php",{ df: df, dt: dt, StartProdCat: StartProdCat, EndProdCat: EndProdCat, StartItem: StartItem, EndItem: EndItem, StartCustNum: StartCustNum, EndCustNum: EndCustNum, Order_Num: Order_Num, CapPCT: CapPCT, FlagDetail: FD_text, FlagComments: FC_text },
	function(data){
		$('#dataDiv').html(data.returnValue);
		$('input:submit').attr("disabled", false);
	}, "json");
	
}



function closeDiv(div) {
	//alert(div);
	
	//var r=confirm("Remove table from view?");
	
	//if (r==true) {
	
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	//} 
	
}

function showAddCommentTextboxRow(Rowid,tb,trClass,DatePromTS){

	$.post("ajax/promise_comment.php",{ action: 'showAddCommentTextboxRow', Rowid: Rowid, tb: tb, trClass: trClass, DatePromTS: DatePromTS },
	function(data){
		$("#" + tb + "_row_add_comment_" + Rowid).html(data.returnValue);
	
	}, "json");		
}

function showAddCommentRow(Rowid,tb,trClass,DatePromTS){
	$.post("ajax/promise_comment.php",{ action: 'showAddCommentRow', Rowid: Rowid, tb: tb, trClass: trClass, DatePromTS: DatePromTS },
	function(data){
		$("#" + tb + "_cmt_row_" + Rowid).html(data.returnValue);
	}, "json");		
}

function addComment(Rowid,tb,trClass,DatePromTS) {
	var elem = tb + "_txt_add_comment_" + Rowid;
	var comment = document.getElementById(elem).value;
	
	if (comment.length > 75) {
		alert("Comment too long. Must be less than 75 characters");
		return;
	}
	
	if (comment.match('[^A-Za-z0-9. -]')) {
		alert("Invalid characters in comment.  Please use A-Z a-z 0-9 dashes, spaces and periods.");
		return;
	}
	

	$.post("ajax/promise_comment.php",{ action: 'addComment', Rowid: Rowid, tb: tb, trClass: trClass, comment: comment, DatePromTS: DatePromTS},
	function(data){
		$("#" + tb + "_cmt_row_" + Rowid).html(data.returnValue);
	}, "json");	

}






function showDetailRow(row){
	//alert(row);
	$("#"+row).css({  
		"visibility": "visible"
	});  	
}

function hideDetailRow(row){
	//alert(row);
	$("#"+row).css({  
		"visibility": "collapse"  
	});  	
}

function toggleDetailRow(row){
	//alert(row);
	
	//var current = document.getElementById(row).style.visiblity;
	//var current = $(document.getElementById(row)).is(":visible")
	
	
	if ($(document.getElementById(row)).is(":visible")) {
		//alert("VISIBLE");
		$("#"+row).css({  
			"visibility": "collapse" 
		}); 
	} else {
		//alert("NOT VISIBLE");
		$("#"+row).css({ 
			"visibility": "visible" 
		}); 
	}
	
	
	
	//if (current=="visible") {
	//	$("#"+row).css({  
	//		"visibility": "collapse"  
	//	});  	
	//}

	//if (current=="collapse") {
	//	$("#"+row).css({  
	//		"visibility": "visible"  
	//	});  			
	//}
}


 
function searchKeyPress(e) {
	var current = document.activeElement.id;
	var currentbutton = current.replace('txt','submit');
	
	//"CH_txt_add_comment_25099"			"CH_submit_add_comment_"
	//"CL_txt_add_comment_83449"			"CL_submit_add_comment_"
	//alert(currentbutton);

	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById(currentbutton).click();
	}
}



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
