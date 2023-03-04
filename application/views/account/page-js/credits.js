$(document).ready(function(){
	creditsInfo();
	searchCredits(document.frmCreditSrch);
});
(function() {
	var dv = '#creditListing';
	var dvForm = '#withdrawalReqForm';

	searchCredits = function(frm){
		/*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
		var data = fcom.frmData(frm);
		/*]*/
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('Account','creditSearch'), data, function(res){
			$(dv).html(res);
		});
	};

	creditsInfo = function(){
		var div = '#credits-info';
		$(div).html(fcom.getLoader());      
		fcom.ajax(fcom.makeUrl('Account','creditsInfo'), '', function(res){
            $(div).html(res);
		});
	};

	goToOrderSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmCreditSrchPaging;
		$(frm.page).val(page);
		searchCredits(frm);
	};

	clearSearch = function(){
		document.frmCreditSrch.reset();
		searchCredits(document.frmCreditSrch);
	};

	withdrawalReqForm = function(){
        $(dvForm).html( fcom.getLoader() );
        $payoutType = $(".payout_type").val();
        if ('-1' == $payoutType) {
            fcom.ajax(fcom.makeUrl('Account','requestWithdrawal'), '', function(res){
                $(dvForm).html(res);
            });
        } else {
            fcom.ajax(fcom.makeUrl($payoutType, 'getRequestForm'), '', function(res){
                $(dvForm).html(res);
            });
        }
        $(".withdrawForm").removeClass('d-none');
        $('html, body').animate({
            scrollTop: $('.withdrawForm').offset().top - 100
        }, 'slow');
	};

	setupWithdrawalReq = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'setupRequestWithdrawal'), data, function(t) {
			$(dvForm).html('');
			creditsInfo();
			searchCredits(document.frmCreditSrch);
		});
	};

	closeForm = function(){
        $(dvForm).html('');
        $(".withdrawForm").addClass('d-none');
        $('html, body').animate({
            scrollTop: $('html, body').offset().top - 100
        }, 'slow');
	};

	setUpWalletRecharge = function( frm ){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'setUpWalletRecharge'), data, function(t) {
			if(t.redirectUrl){
				window.location = t.redirectUrl;
			}
		});
	}
    setupPluginForm = function(frm){
        if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(frm.keyName.value, 'setup'), data, function(t) {
			$(dvForm).html('');
			creditsInfo();
			searchCredits(document.frmCreditSrch);
		});
	};
})();
