$(document).ready(function(){
    searchCatalogProducts(document.frmSearchCatalogProduct);
});

/* $(document).on('keyup', "input[name='keyword']", function(){
    var parentForm = $(this).closest('form');
    parentForm.submit();
}); */

(function() {
	var dv = '#listing';
	searchCatalogProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('Seller','searchProductTags'), data, function(res){
			$("#listing").html(res);
		});
	};
    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('Seller','volumeDiscount');
        } else {
    		document.frmSearchCatalogProduct.reset();
    		searchCatalogProducts(document.frmSearchCatalogProduct);
        }
	};
    goToCatalogProductSearchPage = function(page){
		if(typeof page==undefined || page == null){
			page = 1;
		}
		var frm = document.frmCatalogProductSearchPaging;
		$(frm.page).val(page);
		searchCatalogProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearchCatalogProduct;
		searchCatalogProducts(frm);
	}

    /*editTagsLangForm = function(product_id){
        $('input[name=\'product_id\']').val(product_id);
		$.facebox({ div: '#productTagForm' }, '');
	};*/

    addTagData = function(e){
        var product_id = $(e.detail.tagify.DOM.originalInput).attr('data-product_id');
        var tag_id = e.detail.tag.id;
        var tag_name = e.detail.tag.title;
        if(tag_id == ''){
            var data = 'tag_id=0&tag_identifier='+tag_name
            fcom.updateWithAjax(fcom.makeUrl('Seller', 'tagSetup'), data, function(t) {
                var dataLang = 'tag_id='+t.tagId+'&tag_name='+tag_name+'&lang_id=0';
                fcom.updateWithAjax(fcom.makeUrl('Seller', 'tagLangSetup'), dataLang, function(t2) {
                    fcom.updateWithAjax(fcom.makeUrl('Seller', 'updateProductTag'), 'product_id='+product_id+'&tag_id='+t.tagId, function(t3) {
                         var tagifyId = e.detail.tag.__tagifyId;
                         $('[__tagifyid='+tagifyId+']').attr('id', t.tagId);
                     });
                });
            });
        }else{
            fcom.updateWithAjax(fcom.makeUrl('Seller', 'updateProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) { });
        }
    }

    removeTagData = function(e){
        var tag_id = e.detail.tag.id;
        var product_id = $(e.detail.tagify.DOM.originalInput).attr('data-product_id');
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) {
        });
    }

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
