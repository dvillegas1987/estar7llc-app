$(document).ready(function(){
	searchUsers();
});

(function() {
	var runningAjaxReq = false;
	var dv = '#listing';

	reloadList = function() {
		searchUsers();
	};

	searchUsers = function (form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller','searchUsers'),data,function(res){
			showFormActionsBtns();
			$(dv).html(res);
			$('.hideDiv-js').removeClass('d-none');
		});
	};

	addUserForm = function( id ) {
		fcom.ajax(fcom.makeUrl('Seller', 'addSubUserForm', [id]), '', function(t) {
			$(dv).html(t);
			$('.hideDiv-js').addClass('d-none');
			stylePhoneNumberFld();
		});
	};

	setup = function( frm ) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupSubUser'), data, function(t) {
			$.mbsmessage.close();
			reloadList();
		});
	};

	userPasswordForm = function( id ) {
		fcom.ajax(fcom.makeUrl('Seller', 'subUserPasswordForm', [id]), '', function(t) {
			$(dv).html(t);
			$('.hideDiv-js').addClass('d-none');
		});
	};

	updateUserPassword = function( frm ) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'updateUserPassword'), data, function(t) {
			$.mbsmessage.close();
			reloadList();
		});
	};

	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){ return; }
		data='splatformId='+id;
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteuser'),data,function(res){
			reloadList();
		});
	};

	cancelForm = function(frm){
		reloadList();
		$(dv).html('');
	};

	toggleBulkStatues = function(status){
        if(!confirm(langLbl.confirmUpdateStatus)){
            return false;
        }
        $("#frmSellerUsersListing input[name='status']").val(status);
        $("#frmSellerUsersListing").submit();
    };

	toggleSellerUserStatus = function(e,obj){
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var userId = parseInt(obj.value);
		if( userId < 1 ){
			return false;
		}
		var status = (obj.hasAttribute('checked')) ? 0 : 1;
		data='userId='+userId+'&status='+status;
		fcom.ajax(fcom.makeUrl('Seller','changeUserStatus'),data,function(res){
			var ans = $.parseJSON(res);
			if( ans.status == 1 ){
				$.mbsmessage(ans.msg, true, 'alert--success');
			} else {
				$.mbsmessage(ans.msg, true, 'alert--danger');
			}
		});
	};

})();
