
function suUpdate(row_id) {
	var STATUS = document.getElementById(row_id + '_STATUS').value;
	var PERM_SUPERVISOR = '';
	var PERM_MGMT = '';
	var PERM_SUBSID = '';
	var PERM_HR = '';
	var PERM_PLAN = '';
	var PERM_CS = '';
	var PERM_MAINT = '';
	var PERM_ORD_PREP = '';
	var PERM_QA = '';
	var PERM_RND_REQ = '';
	var PERM_PREPROD = '';
	var PERM_SPREADING = '';
	var PERM_CUTTING = '';
	var PERM_PRODMGT = '';
	var PERM_WH = '';
	var PERM_SU = '';
	
	if (document.getElementById(row_id + '_PERM_SUPERVISOR').checked) {
		PERM_SUPERVISOR = '1';
	}
	
	if (document.getElementById(row_id + '_PERM_MGMT').checked) {
		PERM_MGMT = '1';
	}

	if (document.getElementById(row_id + '_PERM_SUBSID').checked) {
		PERM_SUBSID = '1';
	}
	
	if (document.getElementById(row_id + '_PERM_HR').checked) {
		PERM_HR = '1';
	}

	if (document.getElementById(row_id + '_PERM_PLAN').checked) {
		PERM_PLAN = '1';
	}

	if (document.getElementById(row_id + '_PERM_CS').checked) {
		PERM_CS = '1';
	}	
	
	if (document.getElementById(row_id + '_PERM_MAINT').checked) {
		PERM_MAINT = '1';
	}

	if (document.getElementById(row_id + '_PERM_ORD_PREP').checked) {
		PERM_ORD_PREP = '1';
	}

	if (document.getElementById(row_id + '_PERM_QA').checked) {
		PERM_QA = '1';
	}

	if (document.getElementById(row_id + '_PERM_RND_REQ').checked) {
		PERM_RND_REQ = '1';
	}
	
	if (document.getElementById(row_id + '_PERM_PREPROD').checked) {
		PERM_PREPROD = '1';
	}

	if (document.getElementById(row_id + '_PERM_SPREADING').checked) {
		PERM_SPREADING = '1';
	}

	if (document.getElementById(row_id + '_PERM_CUTTING').checked) {
		PERM_CUTTING = '1';
	}

	if (document.getElementById(row_id + '_PERM_PRODMGT').checked) {
		PERM_PRODMGT = '1';
	}

	if (document.getElementById(row_id + '_PERM_WH').checked) {
		PERM_WH = '1';
	}

	if (document.getElementById(row_id + '_PERM_SU').checked) {
		PERM_SU = '1';
	}	
	
	
	//alert(row_id + " " + PERM_SUPERVISOR);
	//alert(row_id + " " + PERM_MGMT);
	//alert(row_id + " " + PERM_HR);
	//alert(row_id + " " + PERM_SU);

	$.post("ajax/su3.php",{ action: 'update_perm', rowid: row_id, status: STATUS, 
		permSupervisor: PERM_SUPERVISOR, permMgmt: PERM_MGMT, permSubsid: PERM_SUBSID, 
		permHr: PERM_HR, permPlan: PERM_PLAN, permCs: PERM_CS, permMaint: PERM_MAINT, 
		permOrdPrep: PERM_ORD_PREP, permQa: PERM_QA, permRndReq: PERM_RND_REQ, 
		permPreProd: PERM_PREPROD, permSpreading: PERM_SPREADING, permCutting: PERM_CUTTING, 
		permProdMgt: PERM_PRODMGT, permWH: PERM_WH, permSu: PERM_SU 
	},
	function(data){
		$('#div_' + row_id).html(data.returnValue);
	}, "json");

}

function suMyAdd() {
	var MY_USER = document.getElementById('my_user').value;
	var MY_PWD  = document.getElementById('my_pwd').value;
	var MY_PWD2 = document.getElementById('my_pwd2').value;

	if (MY_PWD==MY_PWD2){
		if ((MY_USER != '') && (MY_PWD != '')){
			$.post("ajax/su3.php",{ action: 'add_my', myuser: MY_USER, mypwd: MY_PWD },
			function(data){
				$('#div_myadd').html(data.returnValue);
			}, "json");
		} else {
			alert('Both fields must not be blank');
		}
	} else { 
		alert('Passwords Do not Match!');
	}
		
}

function suPermAdd() {
	var STATUS = document.getElementById('status').value;
	var ID_BADGE = document.getElementById('id_badge').value;
	var USER_NAME = document.getElementById('user_name').value;
	var ID_USER = document.getElementById('id_user').value;
	var NAME_EMP = document.getElementById('name_emp').value;
	var EMAIL = document.getElementById('email').value;
	var PERM_SUPERVISOR = '';
	var PERM_MGMT = '';
	var PERM_SUBSID = '';
	var PERM_HR = '';
	var PERM_PLAN = '';
	var PERM_CS = '';
	var PERM_MAINT = '';
	var PERM_ORD_PREP = '';
	var PERM_QA = '';
	var PERM_RND_REQ = '';
	var PERM_PREPROD = '';
	var PERM_SPREADING = '';
	var PERM_CUTTING = '';
	var PERM_PRODMGT = '';
	var PERM_WH = '';
	var PERM_SU = '';
	

	//alert("STATUS: " + STATUS);
	//alert("ID_BADGE: " + ID_BADGE);
	//alert("USER_NAME: " + USER_NAME);
	//alert("ID_USER: " + ID_USER);
	//alert("NAME_EMP: " + NAME_EMP);
	//alert("EMAIL: " + EMAIL);

	if (document.getElementById('perm_supervisor').checked) {
		PERM_SUPERVISOR = '1';
	}
	
	if (document.getElementById('perm_mgmt').checked) {
		PERM_MGMT = '1';
	}

	if (document.getElementById('perm_subsid').checked) {
		PERM_SUBSID = '1';
	}
	
	if (document.getElementById('perm_hr').checked) {
		PERM_HR = '1';
	}

	if (document.getElementById('perm_plan').checked) {
		PERM_PLAN = '1';
	}

	if (document.getElementById('perm_cs').checked) {
		PERM_CS = '1';
	}	
	
	if (document.getElementById('perm_maint').checked) {
		PERM_MAINT = '1';
	}

	if (document.getElementById('perm_ord_prep').checked) {
		PERM_ORD_PREP = '1';
	}
	
	if (document.getElementById('perm_qa').checked) {
		PERM_QA = '1';
	}

	if (document.getElementById('perm_rnd_req').checked) {
		PERM_RND_REQ = '1';
	}
	
	if (document.getElementById('perm_preprod').checked) {
		PERM_PREPROD = '1';
	}

	if (document.getElementById('perm_spreading').checked) {
		PERM_SPREADING = '1';
	}

	if (document.getElementById('perm_cutting').checked) {
		PERM_CUTTING = '1';
	}	

	if (document.getElementById('perm_prodmgt').checked) {
		PERM_PRODMGT = '1';
	}

	if (document.getElementById('perm_wh').checked) {
		PERM_WH = '1';
	}	

	if (document.getElementById('perm_su').checked) {
		PERM_SU = '1';
	}	

	if ((USER_NAME != '') && (ID_USER != '') && (NAME_EMP != '')){
		$.post("ajax/su3.php",{ action: 'perm_add', status: STATUS, idbadge: ID_BADGE, username: USER_NAME, iduser: ID_USER, 
			nameemp: NAME_EMP, email: EMAIL, permSupervisor: PERM_SUPERVISOR, permMgmt: PERM_MGMT, permSubsid: PERM_SUBSID, 
			permHr: PERM_HR, permPlan: PERM_PLAN, permCs: PERM_CS, permMaint: PERM_MAINT, permOrdPrep: PERM_ORD_PREP, 
			permQa: PERM_QA, permRndReq: PERM_RND_REQ, permPreProd: PERM_PREPROD, permSpreading: PERM_SPREADING, 
			permCutting: PERM_CUTTING, permProdMgt: PERM_PRODMGT, permWH: PERM_WH, permSu: PERM_SU 
		},
		function(data){
			$('#div_permadd').html(data.returnValue);
		}, "json");
	} else {
		alert('Fields must not be blank');
	}
}


function susendValue(str){
	var df = document.getElementById('df').value;
	var dt = document.getElementById('dt').value;
	
	//alert(df);
	//alert(dt);
	
	$.post("ajax/su3.php",{ sendValue: str, from: 'dash', df: df, dt: dt },
	//$.post("ajax/realtime.php",{ sendValue: str, from: 'dash'},
	function(data){
		$('#div_' + str).html(data.returnValue);
	}, "json");
}



function doOnLoads() {
	window.resizeTo(400,300);
	focus();selTeam.focus();
}


function searchKeyPress(e) {
	// look for window.event in case event isn't passed in
	if (window.event) { e = window.event; }
	if (e.keyCode == 13)
	{
		document.getElementById('submit').click();
	}
}


function timedRefresh(timeoutPeriod) {
	setTimeout('subValue(document.getElementById(\"selTeam\").value)',timeoutPeriod);
}

function goToActivity(Team) {
	document.getElementById("redir_" + Team).submit();
}

function closeDiv(div) {
	//alert(div);
	
	var r=confirm("Remove table from view?");
	
	if (r==true) {
	
		$.post("ajax/dummy.php",{ sendValue: div },
		function(data){
			$('#' + div).html(data.returnValue);
		}, "json");	
	} 
	
}

function UpdateTableHeaders() {
   $(".persist-area").each(function() {
   
       var el             = $(this),
           offset         = el.offset(),
           scrollTop      = $(window).scrollTop(),
           floatingHeader = $(".floatingHeader", this)
       
       if ((scrollTop > offset.top) && (scrollTop < offset.top + el.height())) {
           floatingHeader.css({
            "visibility": "visible"
           });
       } else {
           floatingHeader.css({
            "visibility": "hidden"
           });      
       };
   });
}

// DOM Ready
$(function() {
  var $floatingHeader = $(".persist-header", this).clone();

  $floatingHeader.children().width(function (i, val) {
    return $(".persist-header").children().eq(i).width();    
  });

  $floatingHeader.css("width", $(".persist-header", this).width()).addClass("floatingHeader");
  $(".persist-header", this).before($floatingHeader);   
 
  $(window)
   .scroll(UpdateTableHeaders)
   .trigger("scroll");
});


/*
// DOM Ready      
$(function() {

   var clonedHeaderRow;

   $(".persist-area").each(function() {
       clonedHeaderRow = $(".persist-header", this);
       clonedHeaderRow
         .before(clonedHeaderRow.clone())
         .css("width", clonedHeaderRow.width())
         .addClass("floatingHeader");
         
   });
   
   $(window)
    .scroll(UpdateTableHeaders)
    .trigger("scroll");
   
});
*/
