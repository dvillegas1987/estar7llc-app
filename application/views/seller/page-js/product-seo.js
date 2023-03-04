$(document).ready(function(){
    searchSeoProducts(document.frmSearch);
});

/* $(document).on('keyup', "input[name='keyword']", function(){
    var parentForm = $(this).closest('form');
    parentForm.submit();
}); */


(function() {
	var dv = '#listing';
	searchSeoProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('Seller','searchSeoProducts'),data,function(res){
			$("#listing").html(res);
		});
	};
    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('Seller','volumeDiscount');
        } else {
    		document.frmSearch.reset();
    		searchSeoProducts(document.frmSearch);
        }
	};
    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchSeoProductsPaging;
		$(frm.page).val(page);
		searchSeoProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearch;
		searchSeoProducts(frm);
	}

	/* getProductSeoGeneralForm = function (selprod_id){
		fcom.ajax(fcom.makeUrl('Seller', 'productSeoGeneralForm'), 'selprod_id='+selprod_id, function(t) {
			$("#dvForm").html(t);
		});
	} */

    editProductMetaTagLangForm = function(selprod_id, langId){
			fcom.ajax(fcom.makeUrl('seller', 'productSeoLangForm', [selprod_id, langId]), '', function(t) {
				$("#dvForm").html(t).show();
                $("#dvAlert").hide();
			});

	};

	setupProductLangMetaTag = function (frm, exit){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('seller', 'setupProdMetaLang'), data, function(t) {
			if (!exit && t.langId > 0) {
				editProductMetaTagLangForm(t.metaRecordId, t.langId);
				return ;
			} else {
                $("#dvForm").hide();
                $("#dvAlert").show();
            }
		});
	}

})();
