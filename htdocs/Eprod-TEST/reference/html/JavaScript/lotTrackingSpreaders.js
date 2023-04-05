function doOnLoads() {
	numRecsChange();
	document.getElementById('txt_markID1').focus();
}

function showMarkerInputRow(mrkr,n) {
	var prevMrkr = mrkr-1;

	for(i = 1; i <= n; i++){
		document.getElementById('tr_marker'+mrkr+'.'+i).style.display = 'table-row';
	}
	document.getElementById('tr_plus'+prevMrkr).style.display = 'none';
	document.getElementById('tr_plus'+mrkr).style.display = 'table-row';
	document.getElementById('txt_markID'+mrkr).focus();
}

function searchKeyPress(e,nextElement) {
	// look for window.event in case event isn't passed in
	if (window.event) { 
		e = window.event; 
	}

	if (e.keyCode == 13 || e.keyCode == 9) {
		$('#'+nextElement).focus();
	}
}

function selectChangedNextElement(e,nextElement) {
	// look for window.event in case event isn't passed in
	if (window.event) { 
		e = window.event; 
	}

	if (e.keyCode == 13 || e.keyCode == 9) {
		$('#'+nextElement).focus();
		e.preventDefault();
	}
}

function searchLotNum(e) {
	var url = "ajax/lotTrackingSpreaders.php";
	var action = "searchLotNum";
	var markID1 = document.getElementById('txt_markID1').value;
	var markID2 = document.getElementById('txt_markID2').value;
	var markID3 = document.getElementById('txt_markID3').value;
	var markID4 = document.getElementById('txt_markID4').value;
	var markID5 = document.getElementById('txt_markID5').value;
	var markID6 = document.getElementById('txt_markID6').value;
	var markID7 = document.getElementById('txt_markID7').value;
	var markID8 = document.getElementById('txt_markID8').value;
	var markID9 = document.getElementById('txt_markID9').value;
	var markID10 = document.getElementById('txt_markID10').value;
	var lotNumb = document.getElementById('lotNumb').value;

	// look for window.event in case event isn't passed in
	if (window.event) { 
		e = window.event; 
	}

	if (lotNumb.indexOf("10005547221-17525650") !== -1) {
		document.getElementById("lotNumb").value = lotNumb.replace("10005547221-17525650", "");
		e.preventDefault();
		alert("Invalid Lot Number Barcode. Please enter this lot number by hand.");
		return;
	}

	if (e.keyCode == 13 || e.keyCode == 9) {
		$.post(url,{
			action: action,
			markID1: markID1, 
			markID2: markID2, 
			markID3: markID3, 
			markID4: markID4, 
			markID5: markID5, 
			markID6: markID6, 
			markID7: markID7, 
			markID8: markID8, 
			markID9: markID9, 
			markID10: markID10, 
			lotNumb: lotNumb
		},

		function(data){
			document.getElementById("lotNumb").value = data.returnValue;
		},"json");
	}
}

function searchAdditionalMarkerIDs(e,nextElement,thisElement) {
	var url = "ajax/lotTrackingSpreaders.php";
	var action = "searchAdditionalMarkerIDs";
	var markID1 = document.getElementById('txt_markID1').value;
	var markIDThis = document.getElementById(thisElement).value;

	// look for window.event in case event isn't passed in
	if (window.event) { 
		e = window.event; 
	}

	if (e.keyCode == 13 || e.keyCode == 9) {
		$.post(url,{
			action: action,
			markID1: markID1, 
			markIDThis: markIDThis
		},

		function(data){
			document.getElementById(thisElement).value = data.returnValue;

			if (document.getElementById(thisElement).value == 'INVALID') {
				alert("Marker Material Does Not Match Marker 1 Material");
				document.getElementById(thisElement).select();
			} else {
				$('#'+nextElement).focus();
			}

		},"json");
		e.preventDefault();
	}
}


function searchIDBadge(e,nextElement) {
	var url = "ajax/lotTrackingSpreaders.php";
	var action = "searchIDBadge";
	var idBadgeSpreader = document.getElementById('idBadgeSpreader').value;

	// look for window.event in case event isn't passed in
	if (window.event) { 
		e = window.event; 
	}

	if (!IsNumeric(idBadgeSpreader)) {
		alert("Numbers only");
		document.getElementById("idBadgeSpreader").value = "";
		return;
	}

	//if (e.code == 'Tab' || e.code == 'Enter') {
	if (idBadgeSpreader.length == 4) {
		$.post(url,{
			action: action,
			idBadgeSpreader: idBadgeSpreader 
		},

		function(data){
			$('#fontNameEmp').html(data.returnValue);
		},"json");

		$('#'+nextElement).focus();
		e.preventDefault();
	}
}


function clearInputs(){
	document.getElementById("txt_markID1").value = "";
	document.getElementById("txt_markID2").value = "";
	document.getElementById("txt_markID3").value = "";
	document.getElementById("txt_markID4").value = "";
	document.getElementById("txt_markID5").value = "";
	document.getElementById("txt_markID6").value = "";
	document.getElementById("txt_markID7").value = "";
	document.getElementById("txt_markID8").value = "";
	document.getElementById("txt_markID9").value = "";
	document.getElementById("txt_markID10").value = "";

	document.getElementById("txt_spreadL1").value = "";
	document.getElementById("txt_spreadL2").value = "";
	document.getElementById("txt_spreadL3").value = "";
	document.getElementById("txt_spreadL4").value = "";
	document.getElementById("txt_spreadL5").value = "";
	document.getElementById("txt_spreadL6").value = "";
	document.getElementById("txt_spreadL7").value = "";
	document.getElementById("txt_spreadL8").value = "";
	document.getElementById("txt_spreadL9").value = "";
	document.getElementById("txt_spreadL10").value = "";

	document.getElementById('tr_marker2.1').style.display = 'none';
	document.getElementById('tr_marker2.2').style.display = 'none';
	document.getElementById('tr_marker2.3').style.display = 'none';
	document.getElementById('tr_marker3.1').style.display = 'none';
	document.getElementById('tr_marker3.2').style.display = 'none';
	document.getElementById('tr_marker3.3').style.display = 'none';
	document.getElementById('tr_marker4.1').style.display = 'none';
	document.getElementById('tr_marker4.2').style.display = 'none';
	document.getElementById('tr_marker4.3').style.display = 'none';
	document.getElementById('tr_marker5.1').style.display = 'none';
	document.getElementById('tr_marker5.2').style.display = 'none';
	document.getElementById('tr_marker5.3').style.display = 'none';
	document.getElementById('tr_marker6.1').style.display = 'none';
	document.getElementById('tr_marker6.2').style.display = 'none';
	document.getElementById('tr_marker6.3').style.display = 'none';
	document.getElementById('tr_marker7.1').style.display = 'none';
	document.getElementById('tr_marker7.2').style.display = 'none';
	document.getElementById('tr_marker7.3').style.display = 'none';
	document.getElementById('tr_marker8.1').style.display = 'none';
	document.getElementById('tr_marker8.2').style.display = 'none';
	document.getElementById('tr_marker8.3').style.display = 'none';
	document.getElementById('tr_marker9.1').style.display = 'none';
	document.getElementById('tr_marker9.2').style.display = 'none';
	document.getElementById('tr_marker9.3').style.display = 'none';
	document.getElementById('tr_marker10.1').style.display = 'none';
	document.getElementById('tr_marker10.2').style.display = 'none';
	document.getElementById('tr_marker10.3').style.display = 'none';

	document.getElementById('tr_plus1').style.display = 'table-row';
	document.getElementById('tr_plus2').style.display = 'none';
	document.getElementById('tr_plus3').style.display = 'none';
	document.getElementById('tr_plus4').style.display = 'none';
	document.getElementById('tr_plus5').style.display = 'none';
	document.getElementById('tr_plus6').style.display = 'none';
	document.getElementById('tr_plus7').style.display = 'none';
	document.getElementById('tr_plus8').style.display = 'none';
	document.getElementById('tr_plus9').style.display = 'none';

	document.getElementById("mach_numb").value = "";
	document.getElementById("idBadgeSpreader").value = "";
	document.getElementById("fontNameEmp").innerHTML = "";
	document.getElementById("lotNumb").value = "";

	document.getElementById('txt_markID1').focus();
}


function insertNewRecord(){//insert a new inspection record into table
	var url = "ajax/lotTrackingSpreaders.php";
	var action = "insertNewRecord";

	var markID1 = document.getElementById('txt_markID1').value;
	var markID2 = document.getElementById('txt_markID2').value;
	var markID3 = document.getElementById('txt_markID3').value;
	var markID4 = document.getElementById('txt_markID4').value;
	var markID5 = document.getElementById('txt_markID5').value;
	var markID6 = document.getElementById('txt_markID6').value;
	var markID7 = document.getElementById('txt_markID7').value;
	var markID8 = document.getElementById('txt_markID8').value;
	var markID9 = document.getElementById('txt_markID9').value;
	var markID10 = document.getElementById('txt_markID10').value;

	var spreadL1 = document.getElementById('txt_spreadL1').value;
	var spreadL2 = document.getElementById('txt_spreadL2').value;
	var spreadL3 = document.getElementById('txt_spreadL3').value;
	var spreadL4 = document.getElementById('txt_spreadL4').value;
	var spreadL5 = document.getElementById('txt_spreadL5').value;
	var spreadL6 = document.getElementById('txt_spreadL6').value;
	var spreadL7 = document.getElementById('txt_spreadL7').value;
	var spreadL8 = document.getElementById('txt_spreadL8').value;
	var spreadL9 = document.getElementById('txt_spreadL9').value;
	var spreadL10 = document.getElementById('txt_spreadL10').value;

	var machNumb = document.getElementById('mach_numb').value;
	var idBadgeSpreader = document.getElementById('idBadgeSpreader').value;
	var lotNumb = document.getElementById('lotNumb').value;
	var num_recs = document.getElementById('num_recs').value;


	if (markID1 == '') {
		alert("Marker Record 1 cannot be blank!");
		return;
	}

	if (spreadL1 == '') {
		alert("Spread Length 1 cannot be blank!");
		return;
	}

	if (machNumb == '') {
		alert("Machine Number is Required!");
		return;
	}

	if (idBadgeSpreader == '') {
		alert("Spreader's Badge Number is Required!");
		return;
	}

	if (lotNumb == '') {
		alert("Lot Number is Required!");
		return;
	}

	if (lotNumb.indexOf('10005547221-17525650') !== -1 ) {
		alert("Invalid Lot Number Barcode. Please enter this lot number by hand.");
		return;
	}

	var x;
	for (x = 1; x <= 10; x++) {
		if (eval('markID'+[x]) == '' && eval('spreadL'+[x]) != '') {
			alert("Marker Record "+x+" cannot be blank!");
			document.getElementById('txt_markID'+[x]).focus();
			document.getElementById('txt_markID'+[x]).select();
			return;
		}
		if (eval('markID'+[x]) != '' && eval('spreadL'+[x]) == '') {
			alert("Spread Length "+x+" cannot be blank!");
			document.getElementById('txt_spreadL'+[x]).focus();
			document.getElementById('txt_spreadL'+[x]).select();
			return;
		}

		if (eval('markID'+[x]) != '') {
			if (IsNumeric(eval('markID'+[x])) == false){
				alert("Marker ID Must Be Numeric Only");
				document.getElementById('txt_markID'+[x]).focus();
				document.getElementById('txt_markID'+[x]).select();
				return;
			}
		}
	}

	$('#btnInsertRecord').attr("disabled", true);
	$('#dataDiv').html("<img src='images/loading01.gif' />");
	$.post(url,{
		action: action,
		markID1: markID1, 
		markID2: markID2, 
		markID3: markID3, 
		markID4: markID4, 
		markID5: markID5, 
		markID6: markID6, 
		markID7: markID7, 
		markID8: markID8, 
		markID9: markID9, 
		markID10: markID10, 
		spreadL1: spreadL1,
		spreadL2: spreadL2,
		spreadL3: spreadL3,
		spreadL4: spreadL4,
		spreadL5: spreadL5,
		spreadL6: spreadL6,
		spreadL7: spreadL7,
		spreadL8: spreadL8,
		spreadL9: spreadL9,
		spreadL10: spreadL10,
		idBadgeSpreader: idBadgeSpreader,
		machNumb: machNumb, 
		lotNumb: lotNumb,
		num_recs: num_recs
	},

	function(data){
		$('#dataDiv').html(data.returnValue);
		clearInputs();
		$('#btnInsertRecord').attr("disabled", false);
	},"json");

}

function IsNumeric(strString) {
	var strValidChars = "0123456789.";
	var strChar;
	var blnResult = true;

	//  test strString consists of valid characters listed above
	for (i = 0; i < strString.length && blnResult == true; i++) {
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1) {
         		blnResult = false;
		}
	}
	return blnResult;
}

function numRecsChange() { 
	var url = "../ajax/lotTrackingSpreaders.php";
	var action = "numRecsChange";
	var num_recs = document.getElementById('num_recs').value;

	$.post(url,{ action: action, numRecsChange: 1, num_recs: num_recs },
	function(data){
		$('#dataDiv').html(data.returnValue);
	}, "json");
}

