function disablePopup(div){  
	//disables popup only if it is enabled  
	if(popupStatus==1){  
		$("#backgroundPopup").fadeOut("slow");  
		$("#dataPopup").fadeOut("slow");  
		$('#dataPopup').html('');
		popupStatus = 0;  
	}  
	
	/*$.post("ajax/rgaform.php",{ action: 'refresh_mainDiv', status: status },
	function(data){
		$('#mainDiv').html(data.returnValue);
	}, "json");*/
	filterRgaStatus();
}  

function goToNewRequestPopUp() {
	$('input:[name=button_NewReqForm]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'form_newreq', divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		$('input:[name=button_NewReqForm]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
	}, "json");

	loadPopup();
	centerPopup();  
}

function check() {
	var choice_value;

	if (document.getElementById('A').checked) {
		choice_value = document.getElementById('A').value;
	} else if (document.getElementById('B').checked) {
		choice_value = document.getElementById('B').value;
	} else if (document.getElementById('C').checked) {
		choice_value = document.getElementById('C').value;
	}

	if (choice_value == 'B'){
		document.getElementById('table_shipping').style.display = 'none';
	} else {
		document.getElementById('table_shipping').style.display = 'table';
	}
}

function rating() {
	var choice_value;

	if (document.getElementById('A').checked) {
		choice_value = document.getElementById('A').value;
	} else if (document.getElementById('B').checked) {
		choice_value = document.getElementById('B').value;
	} else if (document.getElementById('C').checked) {
		choice_value = document.getElementById('C').value;
	}

	if (choice_value == 'C'){
		document.getElementById('tr_RGA_RATING').style.display = 'none';
	} else {
		document.getElementById('tr_RGA_RATING').style.display = 'table-row';
	}
}

function getcustInfo() {
	var custNumber = document.getElementById('txt_customerID').value;
	$.post("ajax/rgaform.php",{ action: 'getcustInfo', field: 'NAME_CUST', custNumber: custNumber },
	function(data){
		$('#div_txt_NAME_CUST').html(data.returnValue);
	}, "json");

	$.post("ajax/rgaform.php",{ action: 'getcustInfo', field: 'CITY', custNumber: custNumber },
	function(data){
		$('#div_txt_CITY').html(data.returnValue);
	}, "json");

	$.post("ajax/rgaform.php",{ action: 'getcustInfo', field: 'ID_ST', custNumber: custNumber },
	function(data){
		$('#div_txt_ID_ST').html(data.returnValue);
	}, "json");

	$.post("ajax/rgaform.php",{ action: 'getcustInfo', field: 'PROV', custNumber: custNumber },
	function(data){
		$('#div_txt_PROV').html(data.returnValue);
	}, "json");

	$.post("ajax/rgaform.php",{ action: 'getcustInfo', field: 'COUNTRY', custNumber: custNumber },
	function(data){
		$('#div_txt_COUNTRY').html(data.returnValue);
	}, "json");

	$.post("ajax/rgaform.php",{ action: 'getcustInfo', field: 'NAME_CONTACT_CUST', custNumber: custNumber },
	function(data){
		$('#div_txt_NAME_CONTACT_CUST').html(data.returnValue);
	}, "json");

	$.post("ajax/rgaform.php",{ action: 'getcustInfo', field: 'PHONE', custNumber: custNumber },
	function(data){
		$('#div_txt_PHONE').html(data.returnValue);
	}, "json");
}

function insertNewBase() {
	var choice_value = '';
	if (document.getElementById('A').checked) {
		choice_value = document.getElementById('A').value;
	} else if (document.getElementById('B').checked) {
		choice_value = document.getElementById('B').value;
	} else if (document.getElementById('C').checked) {
		choice_value = document.getElementById('C').value;
	}

	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var custNumber = document.getElementById('txt_customerID').value;
	var custName = document.getElementById('txt_NAME_CUST').value;
	var city = document.getElementById('txt_CITY').value;
	var state = document.getElementById('txt_ID_ST').value;
	var prov = document.getElementById('txt_PROV').value;
	var country = document.getElementById('txt_COUNTRY').value;
	var contactName = document.getElementById('txt_NAME_CONTACT_CUST').value;
	var phoneNumber = document.getElementById('txt_PHONE').value;
	var email = document.getElementById('txt_email').value;

	var itemNumber1 = document.getElementById('txt_itemNumber1').value;
	var itemNumber2 = document.getElementById('txt_itemNumber2').value;
	var itemNumber3 = document.getElementById('txt_itemNumber3').value;
	var itemNumber4 = document.getElementById('txt_itemNumber4').value;
	var itemNumber5 = document.getElementById('txt_itemNumber5').value;
	var itemNumber6 = document.getElementById('txt_itemNumber6').value;
	var itemNumber7 = document.getElementById('txt_itemNumber7').value;
	var itemNumber8 = document.getElementById('txt_itemNumber8').value;
	var itemNumber9 = document.getElementById('txt_itemNumber9').value;
	var itemNumber10 = document.getElementById('txt_itemNumber10').value;
	
	var orderNumber1 = document.getElementById('txt_orderNumber1').value;
	var orderNumber2 = document.getElementById('txt_orderNumber2').value;
	var orderNumber3 = document.getElementById('txt_orderNumber3').value;
	var orderNumber4 = document.getElementById('txt_orderNumber4').value;
	var orderNumber5 = document.getElementById('txt_orderNumber5').value;
	var orderNumber6 = document.getElementById('txt_orderNumber6').value;
	var orderNumber7 = document.getElementById('txt_orderNumber7').value;
	var orderNumber8 = document.getElementById('txt_orderNumber8').value;
	var orderNumber9 = document.getElementById('txt_orderNumber9').value;
	var orderNumber10 = document.getElementById('txt_orderNumber10').value;

	var poNumber1 = document.getElementById('txt_poNumber1').value;
	var poNumber2 = document.getElementById('txt_poNumber2').value;
	var poNumber3 = document.getElementById('txt_poNumber3').value;
	var poNumber4 = document.getElementById('txt_poNumber4').value;
	var poNumber5 = document.getElementById('txt_poNumber5').value;
	var poNumber6 = document.getElementById('txt_poNumber6').value;
	var poNumber7 = document.getElementById('txt_poNumber7').value;
	var poNumber8 = document.getElementById('txt_poNumber8').value;
	var poNumber9 = document.getElementById('txt_poNumber9').value;
	var poNumber10 = document.getElementById('txt_poNumber10').value;

	var invoiceNumber1 = document.getElementById('txt_invoiceNumber1').value;
	var invoiceNumber2 = document.getElementById('txt_invoiceNumber2').value;
	var invoiceNumber3 = document.getElementById('txt_invoiceNumber3').value;
	var invoiceNumber4 = document.getElementById('txt_invoiceNumber4').value;
	var invoiceNumber5 = document.getElementById('txt_invoiceNumber5').value;
	var invoiceNumber6 = document.getElementById('txt_invoiceNumber6').value;
	var invoiceNumber7 = document.getElementById('txt_invoiceNumber7').value;
	var invoiceNumber8 = document.getElementById('txt_invoiceNumber8').value;
	var invoiceNumber9 = document.getElementById('txt_invoiceNumber9').value;
	var invoiceNumber10 = document.getElementById('txt_invoiceNumber10').value;
	
	var quant1 = document.getElementById('txt_quant1').value;
	var quant2 = document.getElementById('txt_quant2').value;
	var quant3 = document.getElementById('txt_quant3').value;
	var quant4 = document.getElementById('txt_quant4').value;
	var quant5 = document.getElementById('txt_quant5').value;
	var quant6 = document.getElementById('txt_quant6').value;
	var quant7 = document.getElementById('txt_quant7').value;
	var quant8 = document.getElementById('txt_quant8').value;
	var quant9 = document.getElementById('txt_quant9').value;
	var quant10 = document.getElementById('txt_quant10').value;

	var dateShipped1 = document.getElementById('txt_dateShipped1').value;
	var dateShipped2 = document.getElementById('txt_dateShipped2').value;
	var dateShipped3 = document.getElementById('txt_dateShipped3').value;
	var dateShipped4 = document.getElementById('txt_dateShipped4').value;
	var dateShipped5 = document.getElementById('txt_dateShipped5').value;
	var dateShipped6 = document.getElementById('txt_dateShipped6').value;
	var dateShipped7 = document.getElementById('txt_dateShipped7').value;
	var dateShipped8 = document.getElementById('txt_dateShipped8').value;
	var dateShipped9 = document.getElementById('txt_dateShipped9').value;
	var dateShipped10 = document.getElementById('txt_dateShipped10').value;

	var authorized = document.getElementById('select_auth').value;
	var selectSalesMgr = document.getElementById('selectSalesMgr').value;
	var territory = document.getElementById('txt_territory').value;
	var descr = document.getElementById('txt_descr').value;
	var rgaClass = choice_value;
	var followUp = document.getElementById('txt_followUp').value;
	var emailNotify = document.getElementById('txt_email_notify_base').value;	
	var addInfo = document.getElementById('txt_add_info').value;
	var sendEmail = document.getElementById('sel_Email_insertNewBase').value;

	if (custNumber.trim() == '') {
		alert("Please enter a Customer #");
		return;
	}
	if (custName.trim() == '') {
		alert("Please enter a Customer Name");
		return;
	}
	if (city.trim() == '') {
		alert("Please enter a City");
		return;
	}
	/*if (state.trim() == '') {
		alert("Please enter a State");
		return;
	}*/
	if (country.trim() == '') {
		alert("Please enter a Country");
		return;
	}
	if (contactName.trim() == '') {
		alert("Please enter a Contact Name");
		return;
	}
	//if (phoneNumber.trim() == '') {
	//	alert("Please enter a Phone #");
	//	return;
	//}
	if (!validateEmail(email)) {
		alert("Please enter a valid Email address");
		return;
	}

    var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = dateShipped1.match(regPattern);
    if (checkArray1 == null && dateShipped1 != ''){
		alert("Please enter a valid Ship1 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray2 = dateShipped2.match(regPattern);
    if (checkArray2 == null && dateShipped2 != ''){
		alert("Please enter a valid Ship2 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray3 = dateShipped3.match(regPattern);
    if (checkArray3 == null && dateShipped3 != ''){
		alert("Please enter a valid Ship3 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray4 = dateShipped4.match(regPattern);
    if (checkArray4 == null && dateShipped4 != ''){
		alert("Please enter a valid Ship4 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray5 = dateShipped5.match(regPattern);
    if (checkArray5 == null && dateShipped5 != ''){
		alert("Please enter a valid Ship5 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray6 = dateShipped6.match(regPattern);
    if (checkArray6 == null && dateShipped6 != ''){
		alert("Please enter a valid Ship6 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray7 = dateShipped7.match(regPattern);
    if (checkArray7 == null && dateShipped7 != ''){
		alert("Please enter a valid Ship7 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray8 = dateShipped8.match(regPattern);
    if (checkArray8 == null && dateShipped8 != ''){
		alert("Please enter a valid Ship8 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray9 = dateShipped9.match(regPattern);
    if (checkArray9 == null && dateShipped9 != ''){
		alert("Please enter a valid Ship9 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray10 = dateShipped10.match(regPattern);
    if (checkArray10 == null && dateShipped10 != ''){
		alert("Please enter a valid Ship10 date.  (yyyy-mm-dd)");
		return;
    }

	$('input:[name=button_insertNewBase]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'submit_newreq', rgaNumber: rgaNumber, custNumber: custNumber, custName: custName, 
		city: city, state: state, prov: prov, country: country, contactName: contactName, phoneNumber: phoneNumber, email: email, 
		itemNumber1: itemNumber1, orderNumber1: orderNumber1, poNumber1: poNumber1, invoiceNumber1: invoiceNumber1, quant1: quant1, dateShipped1: dateShipped1,
		itemNumber2: itemNumber2, orderNumber2: orderNumber2, poNumber2: poNumber2, invoiceNumber2: invoiceNumber2, quant2: quant2, dateShipped2: dateShipped2,
		itemNumber3: itemNumber3, orderNumber3: orderNumber3, poNumber3: poNumber3, invoiceNumber3: invoiceNumber3, quant3: quant3, dateShipped3: dateShipped3,
		itemNumber4: itemNumber4, orderNumber4: orderNumber4, poNumber4: poNumber4, invoiceNumber4: invoiceNumber4, quant4: quant4, dateShipped4: dateShipped4,
		itemNumber5: itemNumber5, orderNumber5: orderNumber5, poNumber5: poNumber5, invoiceNumber5: invoiceNumber5, quant5: quant5, dateShipped5: dateShipped5,
		itemNumber6: itemNumber6, orderNumber6: orderNumber6, poNumber6: poNumber6, invoiceNumber6: invoiceNumber6, quant6: quant6, dateShipped6: dateShipped6,
		itemNumber7: itemNumber7, orderNumber7: orderNumber7, poNumber7: poNumber7, invoiceNumber7: invoiceNumber7, quant7: quant7, dateShipped7: dateShipped7,
		itemNumber8: itemNumber8, orderNumber8: orderNumber8, poNumber8: poNumber8, invoiceNumber8: invoiceNumber8, quant8: quant8, dateShipped8: dateShipped8,
		itemNumber9: itemNumber9, orderNumber9: orderNumber9, poNumber9: poNumber9, invoiceNumber9: invoiceNumber9, quant9: quant9, dateShipped9: dateShipped9,
		itemNumber10: itemNumber10, orderNumber10: orderNumber10, poNumber10: poNumber10, invoiceNumber10: invoiceNumber10, quant10: quant10, dateShipped10: dateShipped10,
		authorized: authorized, selectSalesMgr: selectSalesMgr, territory: territory, descr: descr, rgaClass: rgaClass, followUp: followUp, addInfo: addInfo, 
		emailNotify: emailNotify, sendEmail: sendEmail },
	function(data){
		$('#div_submitResp_insertNewBase').html(data.returnValue);
		var ret_rgaNumber = document.getElementById('ret_rga_number').value;
		document.getElementById('txt_rgaNumber').value=ret_rgaNumber;

		//$('input:[name=button_insertNewBase]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
		//disablePopup();
	}, "json");
}

function updateRgaBase(){
	var choice_value = '';
	if (document.getElementById('A').checked) {
		choice_value = document.getElementById('A').value;
	} else if (document.getElementById('B').checked) {
		choice_value = document.getElementById('B').value;
	} else if (document.getElementById('C').checked) {
		choice_value = document.getElementById('C').value;
	}

	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var BaseRowID = document.getElementById('BaseRowID').value;
	var custNumber = document.getElementById('txt_customerID').value;
	var custName = document.getElementById('txt_NAME_CUST').value;
	var city = document.getElementById('txt_CITY').value;
	var state = document.getElementById('txt_ID_ST').value;
	var prov = document.getElementById('txt_PROV').value;
	var country = document.getElementById('txt_COUNTRY').value;
	var contactName = document.getElementById('txt_NAME_CONTACT_CUST').value;
	var phoneNumber = document.getElementById('txt_PHONE').value;
	var email = document.getElementById('txt_email').value;
	var authorized = document.getElementById('select_auth').value;
	var salesMgr = document.getElementById('selectSalesMgr').value;
	var territory = document.getElementById('txt_territory').value;
	var descr = document.getElementById('txt_descr').value;
	var rgaClass = choice_value;
	var followUp = document.getElementById('txt_followUp').value;
	var emailNotify = document.getElementById('txt_email_notify').value;
	var addInfo = document.getElementById('txt_add_info').value;
	var sendEmail = document.getElementById('sel_Email_updateRgaBase').value;

	var itemNumber1 = document.getElementById('txt_itemNumber1').value;
	var itemNumber2 = document.getElementById('txt_itemNumber2').value;
	var itemNumber3 = document.getElementById('txt_itemNumber3').value;
	var itemNumber4 = document.getElementById('txt_itemNumber4').value;
	var itemNumber5 = document.getElementById('txt_itemNumber5').value;
	var itemNumber6 = document.getElementById('txt_itemNumber6').value;
	var itemNumber7 = document.getElementById('txt_itemNumber7').value;
	var itemNumber8 = document.getElementById('txt_itemNumber8').value;
	var itemNumber9 = document.getElementById('txt_itemNumber9').value;
	var itemNumber10 = document.getElementById('txt_itemNumber10').value;
	
	var orderNumber1 = document.getElementById('txt_orderNumber1').value;
	var orderNumber2 = document.getElementById('txt_orderNumber2').value;
	var orderNumber3 = document.getElementById('txt_orderNumber3').value;
	var orderNumber4 = document.getElementById('txt_orderNumber4').value;
	var orderNumber5 = document.getElementById('txt_orderNumber5').value;
	var orderNumber6 = document.getElementById('txt_orderNumber6').value;
	var orderNumber7 = document.getElementById('txt_orderNumber7').value;
	var orderNumber8 = document.getElementById('txt_orderNumber8').value;
	var orderNumber9 = document.getElementById('txt_orderNumber9').value;
	var orderNumber10 = document.getElementById('txt_orderNumber10').value;

	var poNumber1 = document.getElementById('txt_poNumber1').value;
	var poNumber2 = document.getElementById('txt_poNumber2').value;
	var poNumber3 = document.getElementById('txt_poNumber3').value;
	var poNumber4 = document.getElementById('txt_poNumber4').value;
	var poNumber5 = document.getElementById('txt_poNumber5').value;
	var poNumber6 = document.getElementById('txt_poNumber6').value;
	var poNumber7 = document.getElementById('txt_poNumber7').value;
	var poNumber8 = document.getElementById('txt_poNumber8').value;
	var poNumber9 = document.getElementById('txt_poNumber9').value;
	var poNumber10 = document.getElementById('txt_poNumber10').value;

	var invoiceNumber1 = document.getElementById('txt_invoiceNumber1').value;
	var invoiceNumber2 = document.getElementById('txt_invoiceNumber2').value;
	var invoiceNumber3 = document.getElementById('txt_invoiceNumber3').value;
	var invoiceNumber4 = document.getElementById('txt_invoiceNumber4').value;
	var invoiceNumber5 = document.getElementById('txt_invoiceNumber5').value;
	var invoiceNumber6 = document.getElementById('txt_invoiceNumber6').value;
	var invoiceNumber7 = document.getElementById('txt_invoiceNumber7').value;
	var invoiceNumber8 = document.getElementById('txt_invoiceNumber8').value;
	var invoiceNumber9 = document.getElementById('txt_invoiceNumber9').value;
	var invoiceNumber10 = document.getElementById('txt_invoiceNumber10').value;
	
	var quant1 = document.getElementById('txt_quant1').value;
	var quant2 = document.getElementById('txt_quant2').value;
	var quant3 = document.getElementById('txt_quant3').value;
	var quant4 = document.getElementById('txt_quant4').value;
	var quant5 = document.getElementById('txt_quant5').value;
	var quant6 = document.getElementById('txt_quant6').value;
	var quant7 = document.getElementById('txt_quant7').value;
	var quant8 = document.getElementById('txt_quant8').value;
	var quant9 = document.getElementById('txt_quant9').value;
	var quant10 = document.getElementById('txt_quant10').value;

	var dateShipped1 = document.getElementById('txt_dateShipped1').value;
	var dateShipped2 = document.getElementById('txt_dateShipped2').value;
	var dateShipped3 = document.getElementById('txt_dateShipped3').value;
	var dateShipped4 = document.getElementById('txt_dateShipped4').value;
	var dateShipped5 = document.getElementById('txt_dateShipped5').value;
	var dateShipped6 = document.getElementById('txt_dateShipped6').value;
	var dateShipped7 = document.getElementById('txt_dateShipped7').value;
	var dateShipped8 = document.getElementById('txt_dateShipped8').value;
	var dateShipped9 = document.getElementById('txt_dateShipped9').value;
	var dateShipped10 = document.getElementById('txt_dateShipped10').value;

	var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = dateShipped1.match(regPattern);
    if (checkArray1 == null && dateShipped1 != ''){
		alert("Please enter a valid Ship1 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray2 = dateShipped2.match(regPattern);
    if (checkArray2 == null && dateShipped2 != ''){
		alert("Please enter a valid Ship2 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray3 = dateShipped3.match(regPattern);
    if (checkArray3 == null && dateShipped3 != ''){
		alert("Please enter a valid Ship3 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray4 = dateShipped4.match(regPattern);
    if (checkArray4 == null && dateShipped4 != ''){
		alert("Please enter a valid Ship4 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray5 = dateShipped5.match(regPattern);
    if (checkArray5 == null && dateShipped5 != ''){
		alert("Please enter a valid Ship5 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray6 = dateShipped6.match(regPattern);
    if (checkArray6 == null && dateShipped6 != ''){
		alert("Please enter a valid Ship6 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray7 = dateShipped7.match(regPattern);
    if (checkArray7 == null && dateShipped7 != ''){
		alert("Please enter a valid Ship7 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray8 = dateShipped8.match(regPattern);
    if (checkArray8 == null && dateShipped8 != ''){
		alert("Please enter a valid Ship8 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray9 = dateShipped9.match(regPattern);
    if (checkArray9 == null && dateShipped9 != ''){
		alert("Please enter a valid Ship9 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray10 = dateShipped10.match(regPattern);
    if (checkArray10 == null && dateShipped10 != ''){
		alert("Please enter a valid Ship10 date.  (yyyy-mm-dd)");
		return;
    }

	$('input:[name=button_SubmitNew]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'updateBase', BaseRowID: BaseRowID, rgaNumber: rgaNumber, custNumber: custNumber, custName: custName, 
		city: city, state: state, prov: prov, country: country, contactName: contactName, phoneNumber: phoneNumber, email: email, authorized: authorized,
		salesMgr: salesMgr, territory: territory, descr: descr, rgaClass: rgaClass, followUp: followUp, addInfo: addInfo, emailNotify: emailNotify, sendEmail: sendEmail,
		itemNumber1: itemNumber1, orderNumber1: orderNumber1, poNumber1: poNumber1, invoiceNumber1: invoiceNumber1, quant1: quant1, dateShipped1: dateShipped1,
		itemNumber2: itemNumber2, orderNumber2: orderNumber2, poNumber2: poNumber2, invoiceNumber2: invoiceNumber2, quant2: quant2, dateShipped2: dateShipped2,
		itemNumber3: itemNumber3, orderNumber3: orderNumber3, poNumber3: poNumber3, invoiceNumber3: invoiceNumber3, quant3: quant3, dateShipped3: dateShipped3,
		itemNumber4: itemNumber4, orderNumber4: orderNumber4, poNumber4: poNumber4, invoiceNumber4: invoiceNumber4, quant4: quant4, dateShipped4: dateShipped4,
		itemNumber5: itemNumber5, orderNumber5: orderNumber5, poNumber5: poNumber5, invoiceNumber5: invoiceNumber5, quant5: quant5, dateShipped5: dateShipped5,
		itemNumber6: itemNumber6, orderNumber6: orderNumber6, poNumber6: poNumber6, invoiceNumber6: invoiceNumber6, quant6: quant6, dateShipped6: dateShipped6,
		itemNumber7: itemNumber7, orderNumber7: orderNumber7, poNumber7: poNumber7, invoiceNumber7: invoiceNumber7, quant7: quant7, dateShipped7: dateShipped7,
		itemNumber8: itemNumber8, orderNumber8: orderNumber8, poNumber8: poNumber8, invoiceNumber8: invoiceNumber8, quant8: quant8, dateShipped8: dateShipped8,
		itemNumber9: itemNumber9, orderNumber9: orderNumber9, poNumber9: poNumber9, invoiceNumber9: invoiceNumber9, quant9: quant9, dateShipped9: dateShipped9,
		itemNumber10: itemNumber10, orderNumber10: orderNumber10, poNumber10: poNumber10, invoiceNumber10: invoiceNumber10, quant10: quant10, dateShipped10: dateShipped10
	},
	function(data){
		$('#div_submitResp_updateRgaBase').html(data.returnValue);
		$('input:[name=button_updateRgaBase]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
		//disablePopup();
	}, "json"); 
}

//shipping submit
function insertNewShip() {
	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var sendEmail = document.getElementById('sel_Email_insertNewShip').value;
	var itemReceived1 = document.getElementById('txt_itemReceived1').value;
	var itemReceived2 = document.getElementById('txt_itemReceived2').value;
	var itemReceived3 = document.getElementById('txt_itemReceived3').value;
	var itemReceived4 = document.getElementById('txt_itemReceived4').value;
	var itemReceived5 = document.getElementById('txt_itemReceived5').value;
	var itemReceived6 = document.getElementById('txt_itemReceived6').value;
	var itemReceived7 = document.getElementById('txt_itemReceived7').value;
	var itemReceived8 = document.getElementById('txt_itemReceived8').value;
	var itemReceived9 = document.getElementById('txt_itemReceived9').value;
	var itemReceived10 = document.getElementById('txt_itemReceived10').value;

	var dateReceived1 = document.getElementById('txt_dateReceived1').value;
	var dateReceived2 = document.getElementById('txt_dateReceived2').value;
	var dateReceived3 = document.getElementById('txt_dateReceived3').value;
	var dateReceived4 = document.getElementById('txt_dateReceived4').value;
	var dateReceived5 = document.getElementById('txt_dateReceived5').value;
	var dateReceived6 = document.getElementById('txt_dateReceived6').value;
	var dateReceived7 = document.getElementById('txt_dateReceived7').value;
	var dateReceived8 = document.getElementById('txt_dateReceived8').value;
	var dateReceived9 = document.getElementById('txt_dateReceived9').value;
	var dateReceived10 = document.getElementById('txt_dateReceived10').value;	

	var quantity1 = document.getElementById('txt_quantity1').value;
	var quantity2 = document.getElementById('txt_quantity2').value;
	var quantity3 = document.getElementById('txt_quantity3').value;
	var quantity4 = document.getElementById('txt_quantity4').value;
	var quantity5 = document.getElementById('txt_quantity5').value;
	var quantity6 = document.getElementById('txt_quantity6').value;
	var quantity7 = document.getElementById('txt_quantity7').value;
	var quantity8 = document.getElementById('txt_quantity8').value;
	var quantity9 = document.getElementById('txt_quantity9').value;
	var quantity10 = document.getElementById('txt_quantity10').value;

	var condition1 = document.getElementById('txt_condition1').value;
	var condition2 = document.getElementById('txt_condition2').value;
	var condition3 = document.getElementById('txt_condition3').value;
	var condition4 = document.getElementById('txt_condition4').value;
	var condition5 = document.getElementById('txt_condition5').value;
	var condition6 = document.getElementById('txt_condition6').value;
	var condition7 = document.getElementById('txt_condition7').value;
	var condition8 = document.getElementById('txt_condition8').value;
	var condition9 = document.getElementById('txt_condition9').value;
	var condition10 = document.getElementById('txt_condition10').value;

	var receivedBy1 = document.getElementById('txt_receivedBy1').value;
	var receivedBy2 = document.getElementById('txt_receivedBy2').value;
	var receivedBy3 = document.getElementById('txt_receivedBy3').value;
	var receivedBy4 = document.getElementById('txt_receivedBy4').value;
	var receivedBy5 = document.getElementById('txt_receivedBy5').value;
	var receivedBy6 = document.getElementById('txt_receivedBy6').value;
	var receivedBy7 = document.getElementById('txt_receivedBy7').value;
	var receivedBy8 = document.getElementById('txt_receivedBy8').value;
	var receivedBy9 = document.getElementById('txt_receivedBy9').value;
	var receivedBy10 = document.getElementById('txt_receivedBy10').value;

	var carrier1 = document.getElementById('txt_carrier1').value;
	var carrier2 = document.getElementById('txt_carrier2').value;
	var carrier3 = document.getElementById('txt_carrier3').value;
	var carrier4 = document.getElementById('txt_carrier4').value;
	var carrier5 = document.getElementById('txt_carrier5').value;
	var carrier6 = document.getElementById('txt_carrier6').value;
	var carrier7 = document.getElementById('txt_carrier7').value;
	var carrier8 = document.getElementById('txt_carrier8').value;
	var carrier9 = document.getElementById('txt_carrier9').value;
	var carrier10 = document.getElementById('txt_carrier10').value;

	var trackingNumber1 = document.getElementById('txt_trackingNumber1').value;
	var trackingNumber2 = document.getElementById('txt_trackingNumber2').value;
	var trackingNumber3 = document.getElementById('txt_trackingNumber3').value;
	var trackingNumber4 = document.getElementById('txt_trackingNumber4').value;
	var trackingNumber5 = document.getElementById('txt_trackingNumber5').value;
	var trackingNumber6 = document.getElementById('txt_trackingNumber6').value;
	var trackingNumber7 = document.getElementById('txt_trackingNumber7').value;
	var trackingNumber8 = document.getElementById('txt_trackingNumber8').value;
	var trackingNumber9 = document.getElementById('txt_trackingNumber9').value;
	var trackingNumber10 = document.getElementById('txt_trackingNumber10').value;

	var shipComments1 = document.getElementById('txt_ship_comments1').value;
	var shipComments2 = document.getElementById('txt_ship_comments2').value;
	var shipComments3 = document.getElementById('txt_ship_comments3').value;
	var shipComments4 = document.getElementById('txt_ship_comments4').value;
	var shipComments5 = document.getElementById('txt_ship_comments5').value;
	var shipComments6 = document.getElementById('txt_ship_comments6').value;
	var shipComments7 = document.getElementById('txt_ship_comments7').value;
	var shipComments8 = document.getElementById('txt_ship_comments8').value;
	var shipComments9 = document.getElementById('txt_ship_comments9').value;
	var shipComments10 = document.getElementById('txt_ship_comments10').value;

	var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = dateReceived1.match(regPattern);
    if (checkArray1 == null && dateReceived1 != ''){
		alert("Please enter a valid Received1 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray2 = dateReceived2.match(regPattern);
    if (checkArray2 == null && dateReceived2 != ''){
		alert("Please enter a valid Received2 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray3 = dateReceived3.match(regPattern);
    if (checkArray3 == null && dateReceived3 != ''){
		alert("Please enter a valid Received3 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray4 = dateReceived4.match(regPattern);
    if (checkArray4 == null && dateReceived4 != ''){
		alert("Please enter a valid Received4 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray5 = dateReceived5.match(regPattern);
    if (checkArray5 == null && dateReceived5 != ''){
		alert("Please enter a valid Received5 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray6 = dateReceived6.match(regPattern);
    if (checkArray6 == null && dateReceived6 != ''){
		alert("Please enter a valid Received6 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray7 = dateReceived7.match(regPattern);
    if (checkArray7 == null && dateReceived7 != ''){
		alert("Please enter a valid Received7 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray8 = dateReceived8.match(regPattern);
    if (checkArray8 == null && dateReceived8 != ''){
		alert("Please enter a valid Received8 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray9 = dateReceived9.match(regPattern);
    if (checkArray9 == null && dateReceived9 != ''){
		alert("Please enter a valid Received9 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray10 = dateReceived10.match(regPattern);
    if (checkArray10 == null && dateReceived10 != ''){
		alert("Please enter a valid Received10 date.  (yyyy-mm-dd)");
		return;
    }

	$('input:[name=button_insertNewShip]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'submit_newreq_shipping', rgaNumber: rgaNumber, sendEmail: sendEmail,
		itemReceived1: itemReceived1, dateReceived1: dateReceived1, quantity1: quantity1, condition1: condition1, receivedBy1: receivedBy1, carrier1: carrier1, trackingNumber1: trackingNumber1, shipComments1: shipComments1,
		itemReceived2: itemReceived2, dateReceived2: dateReceived2, quantity2: quantity2, condition2: condition2, receivedBy2: receivedBy2, carrier2: carrier2, trackingNumber2: trackingNumber2, shipComments2: shipComments2,
		itemReceived3: itemReceived3, dateReceived3: dateReceived3, quantity3: quantity3, condition3: condition3, receivedBy3: receivedBy3, carrier3: carrier3, trackingNumber3: trackingNumber3, shipComments3: shipComments3,
		itemReceived4: itemReceived4, dateReceived4: dateReceived4, quantity4: quantity4, condition4: condition4, receivedBy4: receivedBy4, carrier4: carrier4, trackingNumber4: trackingNumber4, shipComments4: shipComments4,
		itemReceived5: itemReceived5, dateReceived5: dateReceived5, quantity5: quantity5, condition5: condition5, receivedBy5: receivedBy5, carrier5: carrier5, trackingNumber5: trackingNumber5, shipComments5: shipComments5,
		itemReceived6: itemReceived6, dateReceived6: dateReceived6, quantity6: quantity6, condition6: condition6, receivedBy6: receivedBy6, carrier6: carrier6, trackingNumber6: trackingNumber6, shipComments6: shipComments6,
		itemReceived7: itemReceived7, dateReceived7: dateReceived7, quantity7: quantity7, condition7: condition7, receivedBy7: receivedBy7, carrier7: carrier7, trackingNumber7: trackingNumber7, shipComments7: shipComments7,
		itemReceived8: itemReceived8, dateReceived8: dateReceived8, quantity8: quantity8, condition8: condition8, receivedBy8: receivedBy8, carrier8: carrier8, trackingNumber8: trackingNumber8, shipComments8: shipComments8,
		itemReceived9: itemReceived9, dateReceived9: dateReceived9, quantity9: quantity9, condition9: condition9, receivedBy9: receivedBy9, carrier9: carrier9, trackingNumber9: trackingNumber9, shipComments9: shipComments9,
		itemReceived10: itemReceived10, dateReceived10: dateReceived10, quantity10: quantity10, condition10: condition10, receivedBy10: receivedBy10, carrier10: carrier10, trackingNumber10: trackingNumber10, shipComments10: shipComments10
	 },
	function(data){
		$('#div_submitResp_insertNewShip').html(data.returnValue);
		//$('input:[name=button_insertNewShip]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
		//disablePopup();
	}, "json");
}


function updateRgaShip(){
	var shipRowID = document.getElementById('shipRowID').value;
	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var sendEmail = document.getElementById('sel_Email_updateRgaShip').value;
	var itemReceived1 = document.getElementById('txt_itemReceived1').value;
	var itemReceived2 = document.getElementById('txt_itemReceived2').value;
	var itemReceived3 = document.getElementById('txt_itemReceived3').value;
	var itemReceived4 = document.getElementById('txt_itemReceived4').value;
	var itemReceived5 = document.getElementById('txt_itemReceived5').value;
	var itemReceived6 = document.getElementById('txt_itemReceived6').value;
	var itemReceived7 = document.getElementById('txt_itemReceived7').value;
	var itemReceived8 = document.getElementById('txt_itemReceived8').value;
	var itemReceived9 = document.getElementById('txt_itemReceived9').value;
	var itemReceived10 = document.getElementById('txt_itemReceived10').value;

	var dateReceived1 = document.getElementById('txt_dateReceived1').value;
	var dateReceived2 = document.getElementById('txt_dateReceived2').value;
	var dateReceived3 = document.getElementById('txt_dateReceived3').value;
	var dateReceived4 = document.getElementById('txt_dateReceived4').value;
	var dateReceived5 = document.getElementById('txt_dateReceived5').value;
	var dateReceived6 = document.getElementById('txt_dateReceived6').value;
	var dateReceived7 = document.getElementById('txt_dateReceived7').value;
	var dateReceived8 = document.getElementById('txt_dateReceived8').value;
	var dateReceived9 = document.getElementById('txt_dateReceived9').value;
	var dateReceived10 = document.getElementById('txt_dateReceived10').value;	

	var quantity1 = document.getElementById('txt_quantity1').value;
	var quantity2 = document.getElementById('txt_quantity2').value;
	var quantity3 = document.getElementById('txt_quantity3').value;
	var quantity4 = document.getElementById('txt_quantity4').value;
	var quantity5 = document.getElementById('txt_quantity5').value;
	var quantity6 = document.getElementById('txt_quantity6').value;
	var quantity7 = document.getElementById('txt_quantity7').value;
	var quantity8 = document.getElementById('txt_quantity8').value;
	var quantity9 = document.getElementById('txt_quantity9').value;
	var quantity10 = document.getElementById('txt_quantity10').value;

	var condition1 = document.getElementById('txt_condition1').value;
	var condition2 = document.getElementById('txt_condition2').value;
	var condition3 = document.getElementById('txt_condition3').value;
	var condition4 = document.getElementById('txt_condition4').value;
	var condition5 = document.getElementById('txt_condition5').value;
	var condition6 = document.getElementById('txt_condition6').value;
	var condition7 = document.getElementById('txt_condition7').value;
	var condition8 = document.getElementById('txt_condition8').value;
	var condition9 = document.getElementById('txt_condition9').value;
	var condition10 = document.getElementById('txt_condition10').value;

	var receivedBy1 = document.getElementById('txt_receivedBy1').value;
	var receivedBy2 = document.getElementById('txt_receivedBy2').value;
	var receivedBy3 = document.getElementById('txt_receivedBy3').value;
	var receivedBy4 = document.getElementById('txt_receivedBy4').value;
	var receivedBy5 = document.getElementById('txt_receivedBy5').value;
	var receivedBy6 = document.getElementById('txt_receivedBy6').value;
	var receivedBy7 = document.getElementById('txt_receivedBy7').value;
	var receivedBy8 = document.getElementById('txt_receivedBy8').value;
	var receivedBy9 = document.getElementById('txt_receivedBy9').value;
	var receivedBy10 = document.getElementById('txt_receivedBy10').value;

	var carrier1 = document.getElementById('txt_carrier1').value;
	var carrier2 = document.getElementById('txt_carrier2').value;
	var carrier3 = document.getElementById('txt_carrier3').value;
	var carrier4 = document.getElementById('txt_carrier4').value;
	var carrier5 = document.getElementById('txt_carrier5').value;
	var carrier6 = document.getElementById('txt_carrier6').value;
	var carrier7 = document.getElementById('txt_carrier7').value;
	var carrier8 = document.getElementById('txt_carrier8').value;
	var carrier9 = document.getElementById('txt_carrier9').value;
	var carrier10 = document.getElementById('txt_carrier10').value;

	var trackingNumber1 = document.getElementById('txt_trackingNumber1').value;
	var trackingNumber2 = document.getElementById('txt_trackingNumber2').value;
	var trackingNumber3 = document.getElementById('txt_trackingNumber3').value;
	var trackingNumber4 = document.getElementById('txt_trackingNumber4').value;
	var trackingNumber5 = document.getElementById('txt_trackingNumber5').value;
	var trackingNumber6 = document.getElementById('txt_trackingNumber6').value;
	var trackingNumber7 = document.getElementById('txt_trackingNumber7').value;
	var trackingNumber8 = document.getElementById('txt_trackingNumber8').value;
	var trackingNumber9 = document.getElementById('txt_trackingNumber9').value;
	var trackingNumber10 = document.getElementById('txt_trackingNumber10').value;

	var shipComments1 = document.getElementById('txt_ship_comments1').value;
	var shipComments2 = document.getElementById('txt_ship_comments2').value;
	var shipComments3 = document.getElementById('txt_ship_comments3').value;
	var shipComments4 = document.getElementById('txt_ship_comments4').value;
	var shipComments5 = document.getElementById('txt_ship_comments5').value;
	var shipComments6 = document.getElementById('txt_ship_comments6').value;
	var shipComments7 = document.getElementById('txt_ship_comments7').value;
	var shipComments8 = document.getElementById('txt_ship_comments8').value;
	var shipComments9 = document.getElementById('txt_ship_comments9').value;
	var shipComments10 = document.getElementById('txt_ship_comments10').value;

	var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = dateReceived1.match(regPattern);
    if (checkArray1 == null && dateReceived1 != ''){
		alert("Please enter a valid Received1 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray2 = dateReceived2.match(regPattern);
    if (checkArray2 == null && dateReceived2 != ''){
		alert("Please enter a valid Received2 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray3 = dateReceived3.match(regPattern);
    if (checkArray3 == null && dateReceived3 != ''){
		alert("Please enter a valid Received3 date.  (yyyy-mm-dd)");
		return;
    }
    var checkArray4 = dateReceived4.match(regPattern);
    if (checkArray4 == null && dateReceived4 != ''){
		alert("Please enter a valid Received4 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray5 = dateReceived5.match(regPattern);
    if (checkArray5 == null && dateReceived5 != ''){
		alert("Please enter a valid Received5 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray6 = dateReceived6.match(regPattern);
    if (checkArray6 == null && dateReceived6 != ''){
		alert("Please enter a valid Received6 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray7 = dateReceived7.match(regPattern);
    if (checkArray7 == null && dateReceived7 != ''){
		alert("Please enter a valid Received7 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray8 = dateReceived8.match(regPattern);
    if (checkArray8 == null && dateReceived8 != ''){
		alert("Please enter a valid Received8 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray9 = dateReceived9.match(regPattern);
    if (checkArray9 == null && dateReceived9 != ''){
		alert("Please enter a valid Received9 date.  (yyyy-mm-dd)");
		return;
    }
	var checkArray10 = dateReceived10.match(regPattern);
    if (checkArray10 == null && dateReceived10 != ''){
		alert("Please enter a valid Received10 date.  (yyyy-mm-dd)");
		return;
    }

	$('input:[name=button_updateRgaShip]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'updateShip', shipRowID: shipRowID, rgaNumber: rgaNumber, sendEmail: sendEmail,
		itemReceived1: itemReceived1, dateReceived1: dateReceived1, quantity1: quantity1, condition1: condition1, receivedBy1: receivedBy1, carrier1: carrier1, trackingNumber1: trackingNumber1, shipComments1: shipComments1,
		itemReceived2: itemReceived2, dateReceived2: dateReceived2, quantity2: quantity2, condition2: condition2, receivedBy2: receivedBy2, carrier2: carrier2, trackingNumber2: trackingNumber2, shipComments2: shipComments2,
		itemReceived3: itemReceived3, dateReceived3: dateReceived3, quantity3: quantity3, condition3: condition3, receivedBy3: receivedBy3, carrier3: carrier3, trackingNumber3: trackingNumber3, shipComments3: shipComments3,
		itemReceived4: itemReceived4, dateReceived4: dateReceived4, quantity4: quantity4, condition4: condition4, receivedBy4: receivedBy4, carrier4: carrier4, trackingNumber4: trackingNumber4, shipComments4: shipComments4,
		itemReceived5: itemReceived5, dateReceived5: dateReceived5, quantity5: quantity5, condition5: condition5, receivedBy5: receivedBy5, carrier5: carrier5, trackingNumber5: trackingNumber5, shipComments5: shipComments5,
		itemReceived6: itemReceived6, dateReceived6: dateReceived6, quantity6: quantity6, condition6: condition6, receivedBy6: receivedBy6, carrier6: carrier6, trackingNumber6: trackingNumber6, shipComments6: shipComments6,
		itemReceived7: itemReceived7, dateReceived7: dateReceived7, quantity7: quantity7, condition7: condition7, receivedBy7: receivedBy7, carrier7: carrier7, trackingNumber7: trackingNumber7, shipComments7: shipComments7,
		itemReceived8: itemReceived8, dateReceived8: dateReceived8, quantity8: quantity8, condition8: condition8, receivedBy8: receivedBy8, carrier8: carrier8, trackingNumber8: trackingNumber8, shipComments8: shipComments8,
		itemReceived9: itemReceived9, dateReceived9: dateReceived9, quantity9: quantity9, condition9: condition9, receivedBy9: receivedBy9, carrier9: carrier9, trackingNumber9: trackingNumber9, shipComments9: shipComments9,
		itemReceived10: itemReceived10, dateReceived10: dateReceived10, quantity10: quantity10, condition10: condition10, receivedBy10: receivedBy10, carrier10: carrier10, trackingNumber10: trackingNumber10, shipComments10: shipComments10 },
	function(data){
		$('#div_submitResp_updateRgaShip').html(data.returnValue);
		$('input:[name=button_updateRgaShip]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
		//disablePopup();
	}, "json");	
}

function insertNewInvest() {
	//ISO investigation
	var level = '';
	if (document.getElementById('1').checked) {
		level = document.getElementById('1').value;
	} else if (document.getElementById('2').checked) {
		level = document.getElementById('2').value;
	} else if (document.getElementById('3').checked) {
		level = document.getElementById('3').value;
	}

	var actionNeed = '';
	if (document.getElementById('action_Rework').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Rework').value);
	} 
	if (document.getElementById('action_Replace').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Replace').value);
	}
	if (document.getElementById('action_NonStk-Sample').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_NonStk-Sample').value);
	}
	if (document.getElementById('action_Credit').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Credit').value);
	}
	if (document.getElementById('action_Other').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Other').value);
	}

	var flag_car = '';
	if (document.getElementById('N').checked) {
		flag_car = document.getElementById('N').value;
	} else if (document.getElementById('Y').checked) {
		flag_car = document.getElementById('Y').value;
	}

	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var findings = document.getElementById('txt_findings').value;
	var cause = document.getElementById('txt_cause').value;
	var contain = document.getElementById('txt_contain').value;
	var corr = document.getElementById('txt_corr').value;
	var desc = document.getElementById('txt_desc').value;
	var carNumber = document.getElementById('txt_carNumber').value;
	var approve = document.getElementById('txt_approve').value;
	var date = document.getElementById('txt_dateSubmit').value;
	var dept = document.getElementById('select_dept').value;
	var errReq = document.getElementById('select_err').value;
	var team = document.getElementById('txt_team').value;
	var invoice = document.getElementById('txt_invoice').value;
	var errorType = document.getElementById('select_errorType').value;
	var compCost = document.getElementById('txt_compCost').value;
	var vendor = document.getElementById('txt_vendor').value;
	var laborCost = document.getElementById('txt_laborCost').value;
	var itemNumber = document.getElementById('txt_isoPartNumber').value;
	var shipCost = document.getElementById('txt_shipCost').value;
	var rgaStatus = document.getElementById('select_rga_status').value;
	var isoStatus = document.getElementById('select_iso_status').value;
	var workcenter = document.getElementById('select_workcenter').value;
	var sendEmail = document.getElementById('sel_Email_insertNewInvest').value;

	if(flag_car == 'Y' && carNumber == ''){
		alert("Please Enter a CAR Number");
		return;
	}

	var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = date.match(regPattern);
    if (checkArray1 == null && date != ''){
		alert("Please enter a valid date.  (yyyy-mm-dd)");
		return;
    }
	
	if(!IsInteger(invoice) && invoice !=''){
		alert("Credit Invoice # must be an integer.");
		return;
	}

	if(!IsNumeric(compCost) && compCost !=''){
		alert("Component Costs must be numeric.");
		return;
	}

	if(!IsNumeric(laborCost) && laborCost !=''){
		alert("Labor Costs must be numeric.");
		return;
	}

	if(!IsNumeric(shipCost) && shipCost !=''){
		alert("Shipping Costs must be numeric.");
		return;
	}

	$('input:[name=button_insertNewInvest]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'submit_newreq_iso', rgaNumber: rgaNumber, level: level, findings: findings, cause: cause, contain: contain, corr: corr, actionNeed: actionNeed, desc: desc, flag_car: flag_car,
	carNumber: carNumber, approve: approve, date: date, dept: dept, errReq: errReq,  team: team, invoice: invoice, errorType: errorType, compCost: compCost, vendor: vendor, 
	laborCost: laborCost, itemNumber: itemNumber, shipCost: shipCost, rgaStatus: rgaStatus, isoStatus: isoStatus, workcenter: workcenter, sendEmail: sendEmail },
	function(data){
		$('#div_submitResp_insertNewInvest').html(data.returnValue);
		//$('input:[name=button_insertNewInvest]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
		//disablePopup();
	}, "json");
}

function updateRgaInvest(){
	var level = '';
	if (document.getElementById('1').checked) {
		level = document.getElementById('1').value;
	} else if (document.getElementById('2').checked) {
		level = document.getElementById('2').value;
	} else if (document.getElementById('3').checked) {
		level = document.getElementById('3').value;
	}

	var actionNeed = '';
	if (document.getElementById('action_Rework').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Rework').value);
	} 
	if (document.getElementById('action_Replace').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Replace').value);
	}
	if (document.getElementById('action_NonStk-Sample').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_NonStk-Sample').value);
	}
	if (document.getElementById('action_Credit').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Credit').value);
	}
	if (document.getElementById('action_Other').checked) {
		actionNeed = actionNeed.concat(document.getElementById('action_Other').value);
	}

	var flag_car = '';
	if (document.getElementById('N').checked) {
		flag_car = document.getElementById('N').value;
	} else if (document.getElementById('Y').checked) {
		flag_car = document.getElementById('Y').value;
	}

	var sendEmail = document.getElementById('sel_Email_updateRgaInvest').value;
	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var findings = document.getElementById('txt_findings').value;
	var cause = document.getElementById('txt_cause').value;
	var contain = document.getElementById('txt_contain').value;
	var corr = document.getElementById('txt_corr').value;
	var desc = document.getElementById('txt_desc').value;
	var carNumber = document.getElementById('txt_carNumber').value;
	var approve = document.getElementById('txt_approve').value;
	var date = document.getElementById('txt_dateSubmit').value;
	var dept = document.getElementById('select_dept').value;
	var errReq = document.getElementById('select_err').value;
	var team = document.getElementById('txt_team').value;
	var invoice = document.getElementById('txt_invoice').value;
	var errorType = document.getElementById('select_errorType').value;
	var compCost = document.getElementById('txt_compCost').value;
	var vendor = document.getElementById('txt_vendor').value;
	var laborCost = document.getElementById('txt_laborCost').value;
	var itemNumber = document.getElementById('txt_isoPartNumber').value;
	var shipCost = document.getElementById('txt_shipCost').value;
	var isoRowID = document.getElementById('isoRowID').value;
	var isoStatus = document.getElementById('select_iso_status').value;
	var BaseRowID = document.getElementById('BaseRowID').value;
	var rgaStatus = document.getElementById('select_rga_status').value;
	var workcenter = document.getElementById('select_workcenter').value;

	if (flag_car == 'Y' && carNumber == " " || carNumber=="  " || carNumber==""){
		alert("Please Enter a CAR Number");
		return;
	}
	var regPattern = /^(19|20)\d\d(-)(0[1-9]|1[012])(-)(0[1-9]|[12][0-9]|3[01])$/;
    var checkArray1 = date.match(regPattern);
    if (checkArray1 == null && date != ''){
		alert("Please enter a valid date.  (yyyy-mm-dd)");
		return;
    }
	
	if(!IsInteger(invoice) && invoice !=''){
		alert("Credit Invoice # must be an integer.");
		return;
	}

	if(!IsNumeric(compCost) && compCost !=''){
		alert("Component Costs must be numeric.");
		return;
	}

	if(!IsNumeric(laborCost) && laborCost !=''){
		alert("Labor Costs must be numeric.");
		return;
	}

	if(!IsNumeric(shipCost) && shipCost !=''){
		alert("Shipping Costs must be numeric.");
		return;
	}

	$('input:[name=button_updateRgaInvest]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'updateISO', rgaNumber: rgaNumber, level: level, findings: findings, 
		contain: contain, corr: corr, actionNeed: actionNeed, desc: desc, flag_car: flag_car, cause: cause, 
		carNumber: carNumber, approve: approve, date: date, dept: dept, errReq: errReq,  team: team, 
		errorType: errorType, compCost: compCost, vendor: vendor, sendEmail: sendEmail,invoice: invoice, 
		laborCost: laborCost, itemNumber: itemNumber, shipCost: shipCost, isoRowID: isoRowID, isoStatus: isoStatus, 
		BaseRowID: BaseRowID, rgaStatus: rgaStatus, workcenter: workcenter },
	function(data){
		$('#div_submitResp_updateRgaInvest').html(data.returnValue);
		$('input:[name=button_updateRgaInvest]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
		//disablePopup();
	}, "json");
}

function updateRgaInvestFinOnly(){
	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var isoRowID = document.getElementById('isoRowID').value;
	var invoice = document.getElementById('txt_invoice').value;
	var rgaStatus = document.getElementById('select_rga_status').value;
	
	if(!IsInteger(invoice) && invoice !=''){
		alert("Credit Invoice # must be an integer.");
		return;
	}

	$('input:[name=button_updateRgaInvestFinOnly]').attr("disabled", true);
	$.post("ajax/rgaform.php",{ action: 'updateISO_FIN_ONLY', rgaNumber: rgaNumber, isoRowID: isoRowID, invoice: invoice, rgaStatus: rgaStatus },
	function(data){
		$('#div_submitResp_updateRgaInvestFinOnly').html(data.returnValue);
		$('input:[name=button_updateRgaInvestFinOnly]').attr("disabled", false);
		//$('#dataPopup').fadeIn('slow');
		//disablePopup();
	}, "json");
}

function goToReviewRequestPopUp(rgaNumber) {
	$.post("ajax/rgaform.php",{ action: 'form_review_basereq', rgaNumber: rgaNumber, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		//$('#dataPopup').fadeIn('slow');
	}, "json");
	loadPopup();
	centerPopup();  
}

function goToReviewShippingRequestPopUp(rgaNumber) {
	$.post("ajax/rgaform.php",{ action: 'form_review_shippingreq', rgaNumber: rgaNumber, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		//$('#dataPopup').fadeIn('slow');
	}, "json");
	loadPopup();
	centerPopup();  
}

function goToReviewISORequestPopUp(rgaNumber) {
	$.post("ajax/rgaform.php",{ action: 'form_review_isoreq', rgaNumber: rgaNumber, divclose: 'true' },
	function(data){
		$('#dataPopup').html(data.returnValue);
		//$('#dataPopup').fadeIn('slow');
	}, "json");
	loadPopup();
	centerPopup();  
}

function showOrdInputRow(ord,n,clone) {
	var prevOrd = ord-1;

	if (clone > 0) {
		var ordNum = document.getElementById('txt_orderNumber'+prevOrd).value;
		var poNum = document.getElementById('txt_poNumber'+prevOrd).value;
		var itemNum = document.getElementById('txt_itemNumber'+prevOrd).value;
		var qty = document.getElementById('txt_quant'+prevOrd).value;
		var invoiceNum = document.getElementById('txt_invoiceNumber'+prevOrd).value;
		var dateShipped = document.getElementById('txt_dateShipped'+prevOrd).value;

		document.getElementById('txt_orderNumber'+ord).value = ordNum;
		document.getElementById('txt_poNumber'+ord).value = poNum;
		document.getElementById('txt_itemNumber'+ord).value = itemNum;
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

function showItmInputRow(itm,n,clone) {
	var prevItm = itm-1;
		
	if (clone > 0) {
		var itemReceived = document.getElementById('txt_itemReceived'+prevItm).value;
		var dateReceived = document.getElementById('txt_dateReceived'+prevItm).value;
		var qty = document.getElementById('txt_quantity'+prevItm).value;
		var carrier = document.getElementById('txt_carrier'+prevItm).value;
		var trackingNumber = document.getElementById('txt_trackingNumber'+prevItm).value;
		var condition = document.getElementById('txt_condition'+prevItm).value;
		var receivedBy = document.getElementById('txt_receivedBy'+prevItm).value;
		var shpComments = document.getElementById('txt_ship_comments'+prevItm).value;

		document.getElementById('txt_itemReceived'+itm).value = itemReceived;
		document.getElementById('txt_dateReceived'+itm).value = dateReceived;
		document.getElementById('txt_quantity'+itm).value = qty;
		document.getElementById('txt_carrier'+itm).value = carrier;
		document.getElementById('txt_trackingNumber'+itm).value = trackingNumber;
		document.getElementById('txt_condition'+itm).value = condition;
		document.getElementById('txt_receivedBy'+itm).value = receivedBy;
		document.getElementById('txt_ship_comments'+itm).value = shpComments;
	}

	for(i = 1; i <= n; i++){
		document.getElementById('tr_itm'+itm+'.'+i).style.display = 'table-row';
	}
	document.getElementById('tr_plus_itm'+prevItm).style.display = 'none';
	document.getElementById('tr_plus_itm'+itm).style.display = 'table-row';
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

function dashsubValue(a_team) {
	//$('input:submit').attr("disabled", true);
	var jsSplitResult = a_team.split("~");
	var i = 0;
	for(i=0; i < jsSplitResult.length; i++){
		if (jsSplitResult[i] != '0') {
			//alert(jsSplitResult[i]);
			dashsendValue(jsSplitResult[i]);
		}
	}
	//$('input:submit').attr("disabled", false);
	//timedRefresh(10000);
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

///functions for file upload
function showUploadFile(field_id) {
	var field_value = document.getElementById(field_id).innerHTML;
	var url = "ajax/rgaform.php";
	var action = "showedit";

	if (field_value.indexOf("input id") != -1) {
		return;
	}

	$.post(url,{ action: action, field_id: field_id, field_value: field_value },
	function(data){
		$('#'+field_id).html(data.returnValue);
	}, "json");
}

function uploadFile() {
	var rgaNumber = document.getElementById('txt_rgaNumber').value;
	var file = document.getElementById("fileToUpload").files[0];
	//alert(file.name+" | "+file.size+" | "+file.type);
	if (file) {
		var formdata = new FormData();
		formdata.append("fileToUpload", file);
		formdata.append("rgaNumber", rgaNumber);
		var ajax = new XMLHttpRequest();
		ajax.upload.addEventListener("progress", progressHandler, false);
		ajax.addEventListener("load", completeHandler, false);
		ajax.addEventListener("error", errorHandler, false);
		ajax.addEventListener("abort", abortHandler, false);
		ajax.open("POST", "ajax/file_upload_parser_rga.php");
		ajax.send(formdata);
	} else {
		document.getElementById("status").innerHTML = "No file selected";
		document.getElementById("progressBar").value = 0;
	}
}

function progressHandler(event) {
	var percent = (event.loaded / event.total) * 100;
	document.getElementById("progressBar").value = Math.round(percent);
	document.getElementById("status").innerHTML = Math.round(percent)+"% uploaded... please wait";
	
	if (document.getElementById("status").innerHTML != "Upload is Complete") {
		document.getElementById("progressBar").value = 0;
	}
}

function completeHandler(event) {
	document.getElementById("status").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 100;
}

function errorHandler(event) {
	document.getElementById("status").innerHTML = "Upload Failed";
	document.getElementById("progressBar").value = 0;
}

function abortHandler(event) {
	document.getElementById("status").innerHTML = "Upload Aborted";
	document.getElementById("progressBar").value = 0;
}

function filterRgaStatus(){
	var rgaStatus = document.getElementById("filterRgaStatus").value;
	var isoStatus = document.getElementById("filterIsoStatus").value;
	var rgaNumber = document.getElementById("filterRgaNumber").value;
	var numResults = document.getElementById("filterNumResults").value;
	var custNo = document.getElementById("filterCustNo").value;
	var PONo = document.getElementById("filterPONo").value;
	var OrdNo = document.getElementById("filterOrdNo").value;
	var CreatedBy = document.getElementById("filterCreatedBy").value;

	if (rgaNumber == '') {
		rgaNumber = 'ALL';
		document.getElementById('filterRgaNumber').value = rgaNumber;
	}

	if (custNo == '') {
		custNo = 'ALL';
		document.getElementById('filterCustNo').value = custNo;
	}

	if (PONo == '') {
		PONo = 'ALL';
		document.getElementById('filterPONo').value = PONo;
	}

	if (OrdNo == '') {
		OrdNo = 'ALL';
		document.getElementById('filterOrdNo').value = OrdNo;
	}

	if (CreatedBy == '') {
		CreatedBy = 'ALL';
		document.getElementById('filterCreatedBy').value = CreatedBy;
	}

	$.post("ajax/rgaform.php",{ action: 'refresh_mainDiv', rgaStatus: rgaStatus, isoStatus: isoStatus, rgaNumber: rgaNumber, 
		custNo: custNo, numResults: numResults, PONo: PONo, OrdNo: OrdNo, CreatedBy: CreatedBy }, 
		function(data){
		$('#mainDiv').html(data.returnValue);
	}, "json");
}

function checkDept(){
	var url = "ajax/rgaform.php";
	var dept = document.getElementById('select_dept').value;

	$('#select_workcenter').html("<option value='LOADING'>LOADING</option>");
	$.post(url,{ action: 'popWorkCenters', dept: dept },
		function(data){
			$('#select_workcenter').html(data.returnValue);
		}, "json");
}

function checkWorkcenter(){
	var url = "ajax/rgaform.php";
	var workcenter = document.getElementById('select_workcenter').value;
	var dept = document.getElementById('select_dept').value;

	$('#select_errorType').html("<option value='LOADING'>LOADING</option>");
	$.post(url,{ action: 'popErrorType', dept: dept, workcenter: workcenter },
		function(data){
			$('#select_errorType').html(data.returnValue);
		}, "json");
}

function slsMgrToTerr(){
	var salesMgr = document.getElementById('selectSalesMgr').value;
	var terrID = salesMgr.substr(0,salesMgr.indexOf('_'));
	document.getElementById("txt_territory").value = terrID;
}

function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function checkEnableButton(select,button){
	//'sel_Email_insertNewBase','button_insertNewBase'
	select_value = document.getElementById(select).value;
	if (select_value.trim() !== '') {
		$('input:[name='+button+']').attr("disabled", false);
	} else {
		$('input:[name='+button+']').attr("disabled", true);
	}
}

function checkChangeRgaIsoStatus(){
	var select_rga_status = document.getElementById('select_rga_status').value;
	if (select_rga_status == 'Closed') {
		document.getElementById("select_iso_status").value = select_rga_status;		
	}
}

