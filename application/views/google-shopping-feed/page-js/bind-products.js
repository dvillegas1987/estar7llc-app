var keyName = 'GoogleShoppingFeed';
$(document).ready(function() {
    bindproductform();
    searchproducts();
});

$(document).on('keyup', "input[name='product_name']", function(){
    var currObj = $(this);
    var parentForm = currObj.closest('form').attr('id');
    if('' != currObj.val()){
        currObj.siblings('ul.dropdown-menu').remove();
        currObj.autocomplete({'source': function(request, response) {
        		$.ajax({
        			url: fcom.makeUrl('Seller', 'autoCompleteProducts'),
        			data: {fIsAjax:1,keyword:currObj.val()},
        			dataType: 'json',
        			type: 'post',
        			success: function(json) {
        				response($.map(json.products, function(item) {
        					return { label: item['name'], value: item['name'], id: item['id'] };
        				}));
        			},
        		});
        	},
        	select: function (event, ui) {
                $("#"+parentForm+" input[name='abprod_selprod_id']").val(ui.item.id);
        	}
        });
    }else{
        $("#"+parentForm+" input[name='abprod_selprod_id']").val('');
    }
});

$(document).on('keyup', "input[name='google_product_category']", function(){
    var currObj = $(this);
    var parentForm = currObj.closest('form').attr('id');
    if('' != currObj.val()){
        currObj.siblings('ul.dropdown-menu').remove();
        currObj.autocomplete({
			'classes': {
				"ui-autocomplete": "custom-ui-autocomplete"
			},
			'source': function(request, response) {
        		$.ajax({
        			url: fcom.makeUrl(keyName, 'getProductCategory'),
        			data: {fIsAjax:1,keyword:currObj.val()},
        			dataType: 'json',
        			type: 'post',
        			success: function(json) {
                        response($.map(json, function(value, index) {
                            return { label: value, value: value, id: index };
        				}));
        			},
        		});
        	},
        	select: function (event, ui) {
                $("#"+parentForm+" input[name='abprod_cat_id']").val(ui.item.id);
        	}
        });
    } else {
        $("input[name='abprod_cat_id']").val('');
    }
});

(function() {
    var dv = '#listing';
    var bindProductForm = '#bindProductForm';
    
	bindproductform = function(selProdId = 0){
        $(bindProductForm).html(fcom.getLoader());
        var adsBatchId = $("input[name='adsBatchId']").val();
		fcom.ajax(fcom.makeUrl(keyName,'bindProductForm', [adsBatchId, selProdId]),'',function(res){
			$(bindProductForm).html(res);
		});
    };

    clearForm = function() {
        bindproductform();
    };

	searchproducts = function(){
        $(dv).html(fcom.getLoader());
        var adsBatchId = $("input[name='adsBatchId']").val();
		fcom.ajax(fcom.makeUrl(keyName,'searchProducts', [adsBatchId]),'',function(res){
			$(dv).html(res);
		});
    };
    
	setupProductsToBatch = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(keyName, 'setupProductsToBatch'), data, function(t) {
            bindproductform();
            searchproducts();
		});
    }

	unlinkProduct = function (adsBatchId, selProdId){
        var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl(keyName, 'unlinkProduct', [adsBatchId, selProdId]), '', function(t) {
            searchproducts();
		});
    }

    unlinkproducts = function(adsBatchId){
        if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.mbsmessage(langLbl.atleastOneRecord, 'alert--danger');
	        return false;
	    }
        var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
        var data = fcom.frmData(document.getElementById('frmBatchSelprodListing'));
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        fcom.ajax(fcom.makeUrl(keyName, 'unlinkProducts', [adsBatchId]), data, function(t) {
            var ans = $.parseJSON(t);
			if( ans.status == 1 ){
				$.mbsmessage(ans.msg, true, 'alert--success');
                $('.formActionBtn-js').addClass('formActions-css');
			} else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
			}
            searchproducts();
        });
	};
})();