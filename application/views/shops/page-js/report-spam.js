(function() {
	var runningAjaxReq = false;
	setUpShopSpam = function(frm){
		if ( !$(frm).validate() ) return;
		if( runningAjaxReq == true ){
			console.log(langLbl.requestProcessing);
			return;
		}
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Shops', 'setUpShopSpam'), data, function(t) {
            runningAjaxReq = false;
            if (t.status && 'undefined' != typeof t.redirectUri && '' != t.redirectUri) {
                setTimeout(function(){ location.href = t.redirectUri; }, 1000);
            }
        });
        setTimeout(function(){ location.href = $('.backToStore-js').attr('href'); }, 1000);
		return false;
	}
})();
function pageRedirect(shopId) {
	window.location.replace(fcom.makeUrl('Shops', 'reportSpam',[shopId]));
}