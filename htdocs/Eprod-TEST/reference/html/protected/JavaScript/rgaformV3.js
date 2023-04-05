function filterRgaStatus(){

	var customerStatus = document.getElementById("filterCustomerStatus").value;
	var isoStatus = document.getElementById("filterisoStatus").value;
	var filterRGA = document.getElementById("filterRgaNumber").value;
	var filterCustomerNumber = document.getElementById("filterCustomerNumber").value;
	var filterCustomerName = document.getElementById("filterCustomerName").value;
	var filterCreatedBy = document.getElementById("filterCreatedBy").value;
	var filterNumResults = document.getElementById("filterNumResults").value;

	$.post("ajax/rgaformV3.php",{ action: 'RGA_List', customerStatus: customerStatus, isoStatus: isoStatus, filterRGA: filterRGA, filterCustomerNumber: filterCustomerNumber, filterCustomerName: filterCustomerName, filterCreatedBy: filterCreatedBy, filterNumResults: filterNumResults}, 
		function(data){
		$('#mainDiv').html(data.returnValue);
	}, "json");
}

function goToNewRequestPopUp() {
	$('input:[name=button_NewReqForm]').attr("disabled", true);
	$.post("ajax/rgaformV3.php",{ action: 'show_form', divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		$('input:[name=button_NewReqForm]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
	}, "json");

	loadPopup();
	centerPopup();  
}
function getUrlVars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
	vars[key] = value;
});
	return vars;

}

function goToReviewPopUp(rgaNumber) {
	//////////
	///GET///
	/////////
//	var first = getUrlVars()["rgaNumber"];

//	alert(first);

	$.post("ajax/rgaformV3.php",{ action: 'show_form', rgaNumber: rgaNumber, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		//$('input:[name=button_NewReqForm]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
	}, "json");

	loadPopup();
	centerPopup();  
}

function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

var popupStatus = 0; 
function centerPopup(){  
	//request data for centering  
	var windowWidth = document.documentElement.clientWidth;  
	var windowHeight = document.documentElement.clientHeight;  
	var popupHeight = $("#dataPopup").height();  
	var popupWidth = $("#dataPopup").width();  
	//centering  
	$("#dataPopup").css({  
		"position": "absolute",  
		"display": "inline-block",
		//"top": windowHeight/5-popupHeight/5,  
		//"left": windowWidth/5-popupWidth/5,  
		"top": 25,  
		"left": 25,  
		//"overflow": "auto",
		"height": "auto"
	});  
	//only need force for IE6  
	$('#dataPopup').fadeIn('slow');
	$("#backgroundPopup").css({  
		//"height": windowHeight  
		"height": "200%" , 
		"width": "110%"  
	});  
}  

function loadPopup(){  
	//loads popup only if it is disabled  
	if(popupStatus==0){  
		$("#backgroundPopup").css({  
			"opacity": "0.7"  
		});  
		$("#backgroundPopup").fadeIn("slow");  
		//$("#dataPopup").fadeIn("slow");  
		popupStatus = 1;  
	}  
}  

function disablePopup(div){  
	//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#dataPopup").fadeOut("slow");  
		$('#dataPopup').html('');
		popupStatus = 0;  
	}  
	
	/*$.post("ajax/rgaformV2.php",{ action: 'refresh_mainDiv', status: status },
	function(data){
		$('#mainDiv').html(data.returnValue);
	}, "json");*/
	filterRgaStatus();
}  


function showItmInputRow(itm,n,clone) {
	var prevItm = itm-1;

		if (clone > 0) {
		var ordNum = document.getElementById('txt_Quantity_Return'+prevOrd).value;
		var poNum = document.getElementById('txt_Condition'+prevOrd).value;
		var itemNum = document.getElementById('txt_Location'+prevOrd).value;
		var qty = document.getElementById('txt_quant'+prevOrd).value;
		var invoiceNum = document.getElementById('txt_invoiceNumber'+prevOrd).value;
		var dateShipped = document.getElementById('txt_dateShipped'+prevOrd).value;

		document.getElementById('txt_Quantity_Return'+ord).value = ordNum;
		document.getElementById('txt_Condition'+ord).value = poNum;
		document.getElementById('txt_Location'+ord).value = itemNum;
		document.getElementById('txt_quant'+ord).value = qty;
		document.getElementById('txt_invoiceNumber'+ord).value = invoiceNum;
		document.getElementById('txt_dateShipped'+ord).value = dateShipped;
	}

	for(i = 1; i <= n; i++){
		document.getElementById('tr_itm'+itm+'.'+i).style.display = 'table-row';
	}
	document.getElementById('tr_plus_itm'+prevItm).style.display = 'none';
	document.getElementById('tr_plus_itm'+itm).style.display = 'table-row';
}

function showOrdInputRow(ord,n,clone) {
	var prevOrd = ord-1;

	if (clone > 0) {
		var ordNum = document.getElementById('txt_Quantity_Return'+prevOrd).value;
		var poNum = document.getElementById('txt_Condition'+prevOrd).value;
		var itemNum = document.getElementById('txt_Location'+prevOrd).value;
		var qty = document.getElementById('txt_quant'+prevOrd).value;
		var invoiceNum = document.getElementById('txt_invoiceNumber'+prevOrd).value;
		var dateShipped = document.getElementById('txt_dateShipped'+prevOrd).value;

		document.getElementById('txt_Quantity_Return'+ord).value = ordNum;
		document.getElementById('txt_Condition'+ord).value = poNum;
		document.getElementById('txt_Location'+ord).value = itemNum;
		document.getElementById('txt_quant'+ord).value = qty;
		document.getElementById('txt_invoiceNumber'+ord).value = invoiceNum;
		document.getElementById('txt_dateShipped'+ord).value = dateShipped;
	}

	for(i = 1; i <= n; i++){
		document.getElementById('tr_ord'+ord+'.'+i).style.display = 'table-row';
	}
	document.getElementById('tr_plus'+prevOrd).style.display = 'none';
	document.getElementById('tr_plus'+ord).style.display = 'table-row';
}


function goToReviewRequestPopUp(rgaNumber) {
	$.post("ajax/rgaformV3.php",{ action: 'form_review', rgaNumber: rgaNumber, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		//$('#dataPopup').fadeIn('slow');
	}, "json");
	loadPopup();
	centerPopup();  
}

function DisableIt() {
        if (ValidateIt() == true)
          document.getElementById('button_submit_newreq').disabled = true;
    } 

function submit_updatereq() {

	var url = "ajax/rgaformV3.php";

//Request Information Values
	var rgaNum = document.getElementById('txt_rgaNumber').value;
	var shipDate = document.getElementById('ShipDate').value;
	var customerName = document.getElementById('Customer_Name').value;
	var invoice = document.getElementById('Invoice').value;
	var customerNumber = document.getElementById('Customer_Number').value;
	var territoryS = document.getElementById('TerritoryStatus').value
	var date = document.getElementById('Date').value
	var contactName = document.getElementById('Contact_Name').value
	var RoC = document.getElementById('RoC').value
	var FLAG_EMAIL_SENT = document.getElementById('FLAG_EMAIL_SENT').value
	var contactInformation = document.getElementById('Contact_Information').value
	var Order_Number = document.getElementById('Order_Number').value
	var poNumber = document.getElementById('PO').value
	//var receiveReturn = document.getElementById('receiveReturn').value
	//var reworkRequired = document.getElementById('Rework_Required').value
	//var creditRequired = document.getElementById('Credit_Required').value

	//alert('email:' + FLAG_EMAIL_SENT );

	var receiveReturn = '';
	if (document.getElementById('receiveReturnY').checked) {
		receiveReturn = document.getElementById('receiveReturnY').value;
	} else if (document.getElementById('receiveReturnN').checked) {
		receiveReturn = document.getElementById('receiveReturnN').value;
	} 


	var reworkRequired = '';
	if (document.getElementById('Rework_RequiredY').checked) {
		reworkRequired = document.getElementById('Rework_RequiredY').value;
	} else if (document.getElementById('Rework_RequiredN').checked) {
		reworkRequired = document.getElementById('Rework_RequiredN').value;
	} 

	var creditRequired = '';
	if (document.getElementById('Credit_RequiredY').checked) {
		creditRequired = document.getElementById('Credit_RequiredY').value;
	} else if (document.getElementById('Credit_RequiredN').checked) {
		creditRequired = document.getElementById('Credit_RequiredN').value;
	} 

	if(!IsInteger(invoice) && invoice !=''){
		alert("Invoice # must be an integer.");
		return false;
	}

	if(!IsInteger(customerNumber) && customerNumber !=''){
		alert("customerNumber must be an integer.");
		return false;
	}

	if(!IsInteger(Order_Number) && Order_Number !=''){
		alert("Order Number must be an integer.");
		return false;
	}

	if(!IsInteger(poNumber) && poNumber !=''){
		alert("PO# must be an integer.");
		return false;
	}

		//alert('RoC:' + RoC );
	//End of Request Information Values
	//Request Detail Values

	var PartNum1 = document.getElementById('txt_PartNum1').value
	var PartNum2 = document.getElementById('txt_PartNum2').value
	var PartNum3 = document.getElementById('txt_PartNum3').value
	var PartNum4 = document.getElementById('txt_PartNum4').value
	var PartNum5 = document.getElementById('txt_PartNum5').value
	var PartNum6 = document.getElementById('txt_PartNum6').value
	var PartNum7 = document.getElementById('txt_PartNum7').value
	var PartNum8 = document.getElementById('txt_PartNum8').value
	var PartNum9 = document.getElementById('txt_PartNum9').value
	var PartNum10 = document.getElementById('txt_PartNum10').value

	var TotalReceived1 = document.getElementById('txt_TotalReceived1').value
	var TotalReceived2 = document.getElementById('txt_TotalReceived2').value
	var TotalReceived3 = document.getElementById('txt_TotalReceived3').value
	var TotalReceived4 = document.getElementById('txt_TotalReceived4').value
	var TotalReceived5 = document.getElementById('txt_TotalReceived5').value
	var TotalReceived6 = document.getElementById('txt_TotalReceived6').value
	var TotalReceived7 = document.getElementById('txt_TotalReceived7').value
	var TotalReceived8 = document.getElementById('txt_TotalReceived8').value
	var TotalReceived9 = document.getElementById('txt_TotalReceived9').value
	var TotalReceived10 = document.getElementById('txt_TotalReceived10').value

	var Price1 = document.getElementById('txt_Price1').value
	var Price2 = document.getElementById('txt_Price2').value
	var Price3 = document.getElementById('txt_Price3').value
	var Price4 = document.getElementById('txt_Price4').value
	var Price5 = document.getElementById('txt_Price5').value
	var Price6 = document.getElementById('txt_Price6').value
	var Price7 = document.getElementById('txt_Price7').value
	var Price8 = document.getElementById('txt_Price8').value
	var Price9 = document.getElementById('txt_Price9').value
	var Price10 = document.getElementById('txt_Price10').value

	var Total1 = document.getElementById('txt_Total1').value
	var Total2 = document.getElementById('txt_Total2').value
	var Total3 = document.getElementById('txt_Total3').value
	var Total4 = document.getElementById('txt_Total4').value
	var Total5 = document.getElementById('txt_Total5').value
	var Total6 = document.getElementById('txt_Total6').value
	var Total7 = document.getElementById('txt_Total7').value
	var Total8 = document.getElementById('txt_Total8').value
	var Total9 = document.getElementById('txt_Total9').value
	var Total10 = document.getElementById('txt_Total10').value

	var Desc_of_Comp1 = document.getElementById('txt_Desc_of_Comp1').value
	var Desc_of_Comp2 = document.getElementById('txt_Desc_of_Comp2').value
	var Desc_of_Comp3 = document.getElementById('txt_Desc_of_Comp3').value
	var Desc_of_Comp4 = document.getElementById('txt_Desc_of_Comp4').value
	var Desc_of_Comp5 = document.getElementById('txt_Desc_of_Comp5').value
	var Desc_of_Comp6 = document.getElementById('txt_Desc_of_Comp6').value
	var Desc_of_Comp7 = document.getElementById('txt_Desc_of_Comp7').value
	var Desc_of_Comp8 = document.getElementById('txt_Desc_of_Comp8').value
	var Desc_of_Comp9 = document.getElementById('txt_Desc_of_Comp9').value
	var Desc_of_Comp10 = document.getElementById('txt_Desc_of_Comp10').value

	if(!IsInteger(TotalReceived1) && TotalReceived1 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived2) && TotalReceived2 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived3) && TotalReceived3 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived4) && TotalReceived4 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived5) && TotalReceived5 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived6) && TotalReceived6 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived7) && TotalReceived7 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived8) && TotalReceived8 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived9) && TotalReceived9 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
	if(!IsInteger(TotalReceived10) && TotalReceived10 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}

		if(!IsIntegerWithDecimal(Price1) && Price1 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price2) && Price2 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price3) && Price3 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price4) && Price4 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price5) && Price5 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price6) && Price6 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price7) && Price7 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price8) && Price8 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price9) && Price9 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price10) && Price10 !=''){
		alert("Price must be an integer.");
		return false;
	}

	
	if(!IsInteger(Total1) && Total1 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total2) && Total2 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total3) && Total3 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total4) && Total4 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total5) && Total5 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total6) && Total6 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total7) && Total7 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total8) && Total8 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total9) && Total9 !=''){
		alert("Total must be an integer.");
		return false;
	}
	if(!IsInteger(Total10) && Total10 !=''){
		alert("Total must be an integer.");
		return false;
	}
//End of Request Detail
//Return Detail

	var Quantity_Return1 = document.getElementById('txt_Quantity_Return1').value
	var Quantity_Return2 = document.getElementById('txt_Quantity_Return2').value
	var Quantity_Return3 = document.getElementById('txt_Quantity_Return3').value
	var Quantity_Return4 = document.getElementById('txt_Quantity_Return4').value
	var Quantity_Return5 = document.getElementById('txt_Quantity_Return5').value
	var Quantity_Return6 = document.getElementById('txt_Quantity_Return6').value
	var Quantity_Return7 = document.getElementById('txt_Quantity_Return7').value
	var Quantity_Return8 = document.getElementById('txt_Quantity_Return8').value
	var Quantity_Return9 = document.getElementById('txt_Quantity_Return9').value
	var Quantity_Return10 = document.getElementById('txt_Quantity_Return10').value

	var Condition1 = document.getElementById('txt_Condition1').value
	var Condition2 = document.getElementById('txt_Condition2').value
	var Condition3 = document.getElementById('txt_Condition3').value
	var Condition4 = document.getElementById('txt_Condition4').value
	var Condition5 = document.getElementById('txt_Condition5').value
	var Condition6 = document.getElementById('txt_Condition6').value
	var Condition7 = document.getElementById('txt_Condition7').value
	var Condition8 = document.getElementById('txt_Condition8').value
	var Condition9 = document.getElementById('txt_Condition9').value
	var Condition10 = document.getElementById('txt_Condition10').value

	var Location1 = document.getElementById('txt_Location1').value
	var Location2 = document.getElementById('txt_Location2').value
	var Location3 = document.getElementById('txt_Location3').value
	var Location4 = document.getElementById('txt_Location4').value
	var Location5 = document.getElementById('txt_Location5').value
	var Location6 = document.getElementById('txt_Location6').value
	var Location7 = document.getElementById('txt_Location7').value
	var Location8 = document.getElementById('txt_Location8').value
	var Location9 = document.getElementById('txt_Location9').value
	var Location10 = document.getElementById('txt_Location10').value

	var Comments_Return1 = document.getElementById('txt_Comments_Return1').value
	var Comments_Return2 = document.getElementById('txt_Comments_Return2').value
	var Comments_Return3 = document.getElementById('txt_Comments_Return3').value
	var Comments_Return4 = document.getElementById('txt_Comments_Return4').value
	var Comments_Return5 = document.getElementById('txt_Comments_Return5').value
	var Comments_Return6 = document.getElementById('txt_Comments_Return6').value
	var Comments_Return7 = document.getElementById('txt_Comments_Return7').value
	var Comments_Return8 = document.getElementById('txt_Comments_Return8').value
	var Comments_Return9 = document.getElementById('txt_Comments_Return9').value
	var Comments_Return10 = document.getElementById('txt_Comments_Return10').value

//end of Return detail
//Resolution Info

	var RI1  = document.getElementById('RI1').value
	var RI2  = document.getElementById('RI2').value
	var RI3  = document.getElementById('RI3').value

	if(!IsInteger(RI1) && RI1 !=''){
		alert("A credit issued via invoice # must be an integer.");
		return false;
	}
	if(!IsInteger(RI2) && RI2 !=''){
		alert("A rework was orded on order # must be an integer.");
		return false;
	}
	if(!IsInteger(RI3) && RI3 !=''){
		alert("A replacement was ordered on order # must be an integer.");
		return false;
	}

//end of Resolution Info
// Tracking Information


	var Error1 = '';
	if (document.getElementById('ErrorY').checked) {
		Error1 = document.getElementById('ErrorY').value;
	} else if (document.getElementById('ErrorN').checked) {
		Error1 = document.getElementById('ErrorN').value;
	} 

	var Department  = document.getElementById('Department').value
	//var Error1  = document.getElementById('Error1').value
	var Error_Type  = document.getElementById('Error_Type').value
	var TeamInd  = document.getElementById('TeamInd').value
	var Inspector  = document.getElementById('Inspector').value
	var InvestNotes  = document.getElementById('InvestNotes').value
	var CarNum  = document.getElementById('CarNum').value
	var FinalCost = document.getElementById('FinalCost').value
	var CustomerStatus  = document.getElementById('CustomerStatus').value
	var ISOStatus  = document.getElementById('ISOStatus').value
	var EmailAdd  = document.getElementById('EmailAdd').value
//End of Tracking Information
//End of All inputs 

	var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = shipDate.match(regPattern);
    if (checkArray1 == null && shipDate != ''){
		alert("Please enter a valid Ship date.  (yyyy-mm-dd)");
		return false;
    }

    if(!IsIntegerWithDecimal(FinalCost) && FinalCost !=''){
		alert("Final cost must be an integer.");
		return false;
	}

//    alert('Department:' + Department );

	//$('#dataDiv').html("<img src='images/loading01.gif' />");
	//$('input:[name=button_submit_UpdateReq]').attr("disabled", false);

	$.post(url,{ action: "submit_UpdateReq", rgaNum: rgaNum, shipDate: shipDate, customerName: customerName, invoice: invoice, customerNumber: customerNumber, territoryS: territoryS, date: date, 
	contactName: contactName, RoC: RoC, contactInformation: contactInformation, receiveReturn: receiveReturn, Order_Number: Order_Number, reworkRequired: reworkRequired, 
	poNumber: poNumber, creditRequired: creditRequired, PartNum1: PartNum1, PartNum2: PartNum2, PartNum3: PartNum3, PartNum4: PartNum4, PartNum5:PartNum5, PartNum6: PartNum6, PartNum7:  PartNum7,
	PartNum8: PartNum8, PartNum9: PartNum9, PartNum10: PartNum10, TotalReceived1: TotalReceived1, TotalReceived2: TotalReceived2, TotalReceived3: TotalReceived3, TotalReceived4: TotalReceived4,
 	TotalReceived5: TotalReceived5, TotalReceived6: TotalReceived6, TotalReceived7: TotalReceived7, TotalReceived8: TotalReceived8, TotalReceived9: TotalReceived9, TotalReceived10: TotalReceived10,
 	Price1: Price1, Price2: Price2, Price3: Price3, Price4: Price4, Price5: Price5, Price6: Price6,
 	Price7: Price7, Price8: Price8, Price9: Price9, Price10: Price10, Total1: Total1, Total2: Total2, Total3: Total3,
 	Total4: Total4, Total5: Total5, Total6: Total6, Total7: Total7, Total8: Total8, Total9: Total9, Total10: Total10, Desc_of_Comp1: Desc_of_Comp1,
 	Desc_of_Comp2: Desc_of_Comp2, Desc_of_Comp3: Desc_of_Comp3, Desc_of_Comp4: Desc_of_Comp4, Desc_of_Comp5: Desc_of_Comp5, Desc_of_Comp6: Desc_of_Comp6, Desc_of_Comp7: Desc_of_Comp7,
 	Desc_of_Comp8: Desc_of_Comp8, Desc_of_Comp9: Desc_of_Comp9, Desc_of_Comp10: Desc_of_Comp10, Quantity_Return1: Quantity_Return1, Quantity_Return2: Quantity_Return2, Quantity_Return3: Quantity_Return3,
 	Quantity_Return4: Quantity_Return4, Quantity_Return5: Quantity_Return5, Quantity_Return6: Quantity_Return6, Quantity_Return7: Quantity_Return7, Quantity_Return8: Quantity_Return8, Quantity_Return9: Quantity_Return9, Quantity_Return10: Quantity_Return10,
 	Condition1: Condition1,Condition2: Condition2,Condition3: Condition3,Condition4: Condition4,Condition5: Condition5,Condition6: Condition6,Condition7: Condition7,Condition8: Condition8,
 	Condition9: Condition9, Condition10: Condition10, Location1: Location1, Location2: Location2, Location3: Location3, Location4: Location4, Location5: Location5,
 	Location6: Location6, Location7: Location7, Location8: Location8, Location9: Location9, Location10: Location10, Comments_Return1: Comments_Return1, Comments_Return2: Comments_Return2, Comments_Return3: Comments_Return3,
 	Error1: Error1, Comments_Return4: Comments_Return4, Comments_Return5: Comments_Return5, Comments_Return6: Comments_Return6, Comments_Return7: Comments_Return7, Comments_Return8: Comments_Return8, Comments_Return9: Comments_Return9, Comments_Return10: Comments_Return10,
 	RI1: RI1, RI2: RI2, RI3: RI3, Department: Department, Error1: Error1, Error_Type: Error_Type,
 	TeamInd: TeamInd, Inspector: Inspector, CarNum: CarNum, FinalCost: FinalCost, CustomerStatus: CustomerStatus, ISOStatus: ISOStatus, EmailAdd: EmailAdd, InvestNotes: InvestNotes, FLAG_EMAIL_SENT: FLAG_EMAIL_SENT},
	function(data){
		$('#dataPopup').html(data.returnValue);
		$('input:[name=button_submit_UpdateReq]').attr("disabled", true);
		var ret_rgaNumber = document.getElementById('ret_rga_number').value;
		document.getElementById('txt_rgaNumber').value= ret_rgaNumber;
	}, "json");


	//$('input:[name=button_submit_UpdateReq]').attr("disabled", false);


}

function IsInteger(strString){
	//  check for valid numeric strings	
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

function IsIntegerWithDecimal(strString){
	//  check for valid numeric strings	
	var strValidChars = "0123456789-.";
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

function submit_newreq() {

	
	var url = "ajax/rgaformV3.php";

	var closeDiv = "no";

//Request Information Values
	var rgaNum = document.getElementById('txt_rgaNumber').value;
	var shipDate = document.getElementById('ShipDate').value;
	var customerName = document.getElementById('Customer_Name').value;
	var invoice = document.getElementById('Invoice').value;
	var customerNumber = document.getElementById('Customer_Number').value;
	var territoryS = document.getElementById('TerritoryStatus').value
	var date = document.getElementById('Date').value
	var contactName = document.getElementById('Contact_Name').value
	var RoC = document.getElementById('RoC').value
	var FLAG_EMAIL_SENT = document.getElementById('FLAG_EMAIL_SENT').value
	var contactInformation = document.getElementById('Contact_Information').value
	var Order_Number = document.getElementById('Order_Number').value
	var poNumber = document.getElementById('PO').value
	//var receiveReturn = document.getElementById('receiveReturn').value
	//var reworkRequired = document.getElementById('Rework_Required').value
	//var creditRequired = document.getElementById('Credit_Required').value

	//alert('email:' + FLAG_EMAIL_SENT );

	var receiveReturn = '';
	if (document.getElementById('receiveReturnY').checked) {
		receiveReturn = document.getElementById('receiveReturnY').value;
	} else if (document.getElementById('receiveReturnN').checked) {
		receiveReturn = document.getElementById('receiveReturnN').value;
	} 


	var reworkRequired = '';
	if (document.getElementById('Rework_RequiredY').checked) {
		reworkRequired = document.getElementById('Rework_RequiredY').value;
	} else if (document.getElementById('Rework_RequiredN').checked) {
		reworkRequired = document.getElementById('Rework_RequiredN').value;
	} 

	var creditRequired = '';
	if (document.getElementById('Credit_RequiredY').checked) {
		creditRequired = document.getElementById('Credit_RequiredY').value;
	} else if (document.getElementById('Credit_RequiredN').checked) {
		creditRequired = document.getElementById('Credit_RequiredN').value;
	} 



	if(!IsInteger(invoice) && invoice !=''){
		alert("Invoice # must be an integer.");
		return false;
	}

	if(!IsInteger(customerNumber) && customerNumber !=''){
		alert("customerNumber must be an integer.");
		return false;
	}

	if(!IsInteger(Order_Number) && Order_Number !=''){
		alert("Order Number must be an integer.");
		return false;
	}

	if(!IsInteger(poNumber) && poNumber !=''){
		alert("PO# must be an integer.");
		return false;
	}

		//alert('RoC:' + RoC );
	//End of Request Information Values
	//Request Detail Values

	var PartNum1 = document.getElementById('txt_PartNum1').value
	var PartNum2 = document.getElementById('txt_PartNum2').value
	var PartNum3 = document.getElementById('txt_PartNum3').value
	var PartNum4 = document.getElementById('txt_PartNum4').value
	var PartNum5 = document.getElementById('txt_PartNum5').value
	var PartNum6 = document.getElementById('txt_PartNum6').value
	var PartNum7 = document.getElementById('txt_PartNum7').value
	var PartNum8 = document.getElementById('txt_PartNum8').value
	var PartNum9 = document.getElementById('txt_PartNum9').value
	var PartNum10 = document.getElementById('txt_PartNum10').value

	var TotalReceived1 = document.getElementById('txt_TotalReceived1').value
	var TotalReceived2 = document.getElementById('txt_TotalReceived2').value
	var TotalReceived3 = document.getElementById('txt_TotalReceived3').value
	var TotalReceived4 = document.getElementById('txt_TotalReceived4').value
	var TotalReceived5 = document.getElementById('txt_TotalReceived5').value
	var TotalReceived6 = document.getElementById('txt_TotalReceived6').value
	var TotalReceived7 = document.getElementById('txt_TotalReceived7').value
	var TotalReceived8 = document.getElementById('txt_TotalReceived8').value
	var TotalReceived9 = document.getElementById('txt_TotalReceived9').value
	var TotalReceived10 = document.getElementById('txt_TotalReceived10').value

	var Price1 = document.getElementById('txt_Price1').value
	var Price2 = document.getElementById('txt_Price2').value
	var Price3 = document.getElementById('txt_Price3').value
	var Price4 = document.getElementById('txt_Price4').value
	var Price5 = document.getElementById('txt_Price5').value
	var Price6 = document.getElementById('txt_Price6').value
	var Price7 = document.getElementById('txt_Price7').value
	var Price8 = document.getElementById('txt_Price8').value
	var Price9 = document.getElementById('txt_Price9').value
	var Price10 = document.getElementById('txt_Price10').value

	var Total1 = document.getElementById('txt_Total1').value
	var Total2 = document.getElementById('txt_Total2').value
	var Total3 = document.getElementById('txt_Total3').value
	var Total4 = document.getElementById('txt_Total4').value
	var Total5 = document.getElementById('txt_Total5').value
	var Total6 = document.getElementById('txt_Total6').value
	var Total7 = document.getElementById('txt_Total7').value
	var Total8 = document.getElementById('txt_Total8').value
	var Total9 = document.getElementById('txt_Total9').value
	var Total10 = document.getElementById('txt_Total10').value

	var Desc_of_Comp1 = document.getElementById('txt_Desc_of_Comp1').value
	var Desc_of_Comp2 = document.getElementById('txt_Desc_of_Comp2').value
	var Desc_of_Comp3 = document.getElementById('txt_Desc_of_Comp3').value
	var Desc_of_Comp4 = document.getElementById('txt_Desc_of_Comp4').value
	var Desc_of_Comp5 = document.getElementById('txt_Desc_of_Comp5').value
	var Desc_of_Comp6 = document.getElementById('txt_Desc_of_Comp6').value
	var Desc_of_Comp7 = document.getElementById('txt_Desc_of_Comp7').value
	var Desc_of_Comp8 = document.getElementById('txt_Desc_of_Comp8').value
	var Desc_of_Comp9 = document.getElementById('txt_Desc_of_Comp9').value
	var Desc_of_Comp10 = document.getElementById('txt_Desc_of_Comp10').value

		if(!IsInteger(TotalReceived1) && TotalReceived1 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived2) && TotalReceived2 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived3) && TotalReceived3 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived4) && TotalReceived4 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived5) && TotalReceived5 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived6) && TotalReceived6 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived7) && TotalReceived7 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived8) && TotalReceived8 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived9) && TotalReceived9 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}
		if(!IsInteger(TotalReceived10) && TotalReceived10 !=''){
		alert("Quantity Received must be an integer.");
		return false;
	}

		if(!IsIntegerWithDecimal(Price1) && Price1 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price2) && Price2 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price3) && Price3 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price4) && Price4 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price5) && Price5 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price6) && Price6 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price7) && Price7 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price8) && Price8 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price9) && Price9 !=''){
		alert("Price must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Price10) && Price10 !=''){
		alert("Price must be an integer.");
		return false;
	}


		if(!IsIntegerWithDecimal(Total1) && Total1 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total2) && Total2 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total3) && Total3 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total4) && Total4 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total5) && Total5 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total6) && Total6 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total7) && Total7 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total8) && Total8 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total9) && Total9 !=''){
		alert("Total must be an integer.");
		return false;
	}
		if(!IsIntegerWithDecimal(Total10) && Total10 !=''){
		alert("Total must be an integer.");
		return false;
	}
//End of Request Detail
//Return Detail

	var Quantity_Return1 = document.getElementById('txt_Quantity_Return1').value
	var Quantity_Return2 = document.getElementById('txt_Quantity_Return2').value
	var Quantity_Return3 = document.getElementById('txt_Quantity_Return3').value
	var Quantity_Return4 = document.getElementById('txt_Quantity_Return4').value
	var Quantity_Return5 = document.getElementById('txt_Quantity_Return5').value
	var Quantity_Return6 = document.getElementById('txt_Quantity_Return6').value
	var Quantity_Return7 = document.getElementById('txt_Quantity_Return7').value
	var Quantity_Return8 = document.getElementById('txt_Quantity_Return8').value
	var Quantity_Return9 = document.getElementById('txt_Quantity_Return9').value
	var Quantity_Return10 = document.getElementById('txt_Quantity_Return10').value

	var Condition1 = document.getElementById('txt_Condition1').value
	var Condition2 = document.getElementById('txt_Condition2').value
	var Condition3 = document.getElementById('txt_Condition3').value
	var Condition4 = document.getElementById('txt_Condition4').value
	var Condition5 = document.getElementById('txt_Condition5').value
	var Condition6 = document.getElementById('txt_Condition6').value
	var Condition7 = document.getElementById('txt_Condition7').value
	var Condition8 = document.getElementById('txt_Condition8').value
	var Condition9 = document.getElementById('txt_Condition9').value
	var Condition10 = document.getElementById('txt_Condition10').value

	var Location1 = document.getElementById('txt_Location1').value
	var Location2 = document.getElementById('txt_Location2').value
	var Location3 = document.getElementById('txt_Location3').value
	var Location4 = document.getElementById('txt_Location4').value
	var Location5 = document.getElementById('txt_Location5').value
	var Location6 = document.getElementById('txt_Location6').value
	var Location7 = document.getElementById('txt_Location7').value
	var Location8 = document.getElementById('txt_Location8').value
	var Location9 = document.getElementById('txt_Location9').value
	var Location10 = document.getElementById('txt_Location10').value

	var Comments_Return1 = document.getElementById('txt_Comments_Return1').value
	var Comments_Return2 = document.getElementById('txt_Comments_Return2').value
	var Comments_Return3 = document.getElementById('txt_Comments_Return3').value
	var Comments_Return4 = document.getElementById('txt_Comments_Return4').value
	var Comments_Return5 = document.getElementById('txt_Comments_Return5').value
	var Comments_Return6 = document.getElementById('txt_Comments_Return6').value
	var Comments_Return7 = document.getElementById('txt_Comments_Return7').value
	var Comments_Return8 = document.getElementById('txt_Comments_Return8').value
	var Comments_Return9 = document.getElementById('txt_Comments_Return9').value
	var Comments_Return10 = document.getElementById('txt_Comments_Return10').value

//end of Return detail
//Resolution Info

	var RI1  = document.getElementById('RI1').value
	var RI2  = document.getElementById('RI2').value
	var RI3  = document.getElementById('RI3').value

	if(!IsInteger(RI1) && RI1 !=''){
		alert("A credit issued via invoice # must be an integer.");
		return false;
	}
	if(!IsInteger(RI2) && RI2 !=''){
		alert("A rework was orded on order # must be an integer.");
		return false;
	}
	if(!IsInteger(RI3) && RI3 !=''){
		alert("A replacement was ordered on order # must be an integer.");
		return false;
	}

//end of Resolution Info
// Tracking Information


	var Error1 = '';
	if (document.getElementById('ErrorY').checked) {
		Error1 = document.getElementById('ErrorY').value;
	} else if (document.getElementById('ErrorN').checked) {
		Error1 = document.getElementById('ErrorN').value;
	} 

	var Department  = document.getElementById('Department').value
	//var Error1  = document.getElementById('Error1').value
	var Error_Type  = document.getElementById('Error_Type').value
	var TeamInd  = document.getElementById('TeamInd').value
	var Inspector  = document.getElementById('Inspector').value
	var InvestNotes  = document.getElementById('InvestNotes').value
	var CarNum  = document.getElementById('CarNum').value
	var FinalCost = document.getElementById('FinalCost').value
	var CustomerStatus  = document.getElementById('CustomerStatus').value
	var ISOStatus  = document.getElementById('ISOStatus').value
	var EmailAdd  = document.getElementById('EmailAdd').value
//End of Tracking Information
//End of All inputs 

	var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = shipDate.match(regPattern);
    if (checkArray1 == null && shipDate != ''){
		alert("Please enter a valid Ship date.  (yyyy-mm-dd)");
		return false;
    }

    if(!IsIntegerWithDecimal(FinalCost) && FinalCost !=''){
		alert("Final cost must be an integer.");
		return false;
	}
	//$('#dataDiv').html("<img src='images/loading01.gif' />");
	$('input:[name=button_submit_newreq]').attr("disabled", true);

	$.post(url,{ action: "submit_newreq", rgaNum: rgaNum, shipDate: shipDate, customerName: customerName, invoice: invoice, customerNumber: customerNumber, territoryS: territoryS, date: date, 
	contactName: contactName, RoC: RoC, contactInformation: contactInformation, receiveReturn: receiveReturn, Order_Number: Order_Number, reworkRequired: reworkRequired, 
	poNumber: poNumber, creditRequired: creditRequired, PartNum1: PartNum1, PartNum2: PartNum2, PartNum3: PartNum3, PartNum4: PartNum4, PartNum5:PartNum5, PartNum6: PartNum6, PartNum7:  PartNum7,
	PartNum8: PartNum8, PartNum9: PartNum9, PartNum10: PartNum10, TotalReceived1: TotalReceived1, TotalReceived2: TotalReceived2, TotalReceived3: TotalReceived3, TotalReceived4: TotalReceived4,
 	TotalReceived5: TotalReceived5, TotalReceived6: TotalReceived6, TotalReceived7: TotalReceived7, TotalReceived8: TotalReceived8, TotalReceived9: TotalReceived9, TotalReceived10: TotalReceived10,
 	Price1: Price1, Price2: Price2, Price3: Price3, Price4: Price4, Price5: Price5, Price6: Price6,
 	Price7: Price7, Price8: Price8, Price9: Price9, Price10: Price10, Total1: Total1, Total2: Total2, Total3: Total3,
 	Total4: Total4, Total5: Total5, Total6: Total6, Total7: Total7, Total8: Total8, Total9: Total9, Total10: Total10, Desc_of_Comp1: Desc_of_Comp1,
 	Desc_of_Comp2: Desc_of_Comp2, Desc_of_Comp3: Desc_of_Comp3, Desc_of_Comp4: Desc_of_Comp4, Desc_of_Comp5: Desc_of_Comp5, Desc_of_Comp6: Desc_of_Comp6, Desc_of_Comp7: Desc_of_Comp7,
 	Desc_of_Comp8: Desc_of_Comp8, Desc_of_Comp9: Desc_of_Comp9, Desc_of_Comp10: Desc_of_Comp10, Quantity_Return1: Quantity_Return1, Quantity_Return2: Quantity_Return2, Quantity_Return3: Quantity_Return3,
 	Quantity_Return4: Quantity_Return4, Quantity_Return5: Quantity_Return5, Quantity_Return6: Quantity_Return6, Quantity_Return7: Quantity_Return7, Quantity_Return8: Quantity_Return8, Quantity_Return9: Quantity_Return9, Quantity_Return10: Quantity_Return10,
 	Condition1: Condition1,Condition2: Condition2,Condition3: Condition3,Condition4: Condition4,Condition5: Condition5,Condition6: Condition6,Condition7: Condition7,Condition8: Condition8,
 	Condition9: Condition9, Condition10: Condition10, Location1: Location1, Location2: Location2, Location3: Location3, Location4: Location4, Location5: Location5,
 	Location6: Location6, Location7: Location7, Location8: Location8, Location9: Location9, Location10: Location10, Comments_Return1: Comments_Return1, Comments_Return2: Comments_Return2, Comments_Return3: Comments_Return3,
 	Error1: Error1, Comments_Return4: Comments_Return4, Comments_Return5: Comments_Return5, Comments_Return6: Comments_Return6, Comments_Return7: Comments_Return7, Comments_Return8: Comments_Return8, Comments_Return9: Comments_Return9, Comments_Return10: Comments_Return10,
 	RI1: RI1, RI2: RI2, RI3: RI3, Department: Department, Error1: Error1, Error_Type: Error_Type,
 	TeamInd: TeamInd, Inspector: Inspector, CarNum: CarNum, FinalCost: FinalCost, CustomerStatus: CustomerStatus, ISOStatus: ISOStatus, EmailAdd: EmailAdd, InvestNotes: InvestNotes, FLAG_EMAIL_SENT: FLAG_EMAIL_SENT },
	function(data){
		$('#dataDiv').html(data.returnValue);
		//var ret_rgaNumber = document.getElementById('ret_rga_number').value;
		//document.getElementById('txt_rgaNumber').value=ret_rgaNumber;
	}, "json");


	//$('input:[name=button_submit_newreq]').attr("disabled", false);

}

