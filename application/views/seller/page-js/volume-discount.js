$(document).ready(function(){
    searchVolumeDiscountProducts(document.frmSearch);
        $("select[name='product_name']").select2({
        closeOnSelect: true,
        dir: langLbl.layoutDirection,
        allowClear: true,
        placeholder: $("select[name='product_name']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('Seller', 'autoCompleteProducts'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                return {
                    keyword: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.products,
                    pagination: {
                        more: params.page < data.pageCount
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        templateResult: function (result)
        {
            return result.name;
        },
        templateSelection: function (result)
        {
            return result.name || result.text;
        }
    }).on('select2:selecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        var item = e.params.args.data;
        $("#"+parentForm+" input[name='voldiscount_selprod_id']").val(item.id);      
        $("input[name='voldiscount_min_qty']").removeAttr('disabled');
        $("input[name='voldiscount_percentage']").removeAttr('disabled');

    }).on('select2:unselecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        $("#"+parentForm+" input[name='voldiscount_selprod_id']").val('');
        $("input[name='voldiscount_min_qty']").attr('disabled', 'disabled').val('');
        $("input[name='voldiscount_percentage']").attr('disabled', 'disabled').val('');
    });
    
});

$(document).on('blur', ".js-voldiscount_min_qty", function(){
    var qty = $(this).val();
    var selProdId = $("input[name='voldiscount_selprod_id']").val();
    if(selProdId > 0){
        var data = 'selProdId='+selProdId+"&qty="+qty;
        fcom.ajax(fcom.makeUrl('Seller', 'compareWithInventoryMinPurchase'), data, function(t) {
            var ans = $.parseJSON(t);
            if( ans.status != 1 ){
                $.systemMessage(ans.msg, 'alert--danger');
            }
        });
    }
});

$(document).on('click', 'table.volDiscountList-js tr td .js--editCol', function(){
    $(this).hide();
    var input = $(this).siblings('input[type="text"]');
    var value = input.val();
    input.removeClass('hidden');
    input.val('').focus().val(value);
});

$(document).on('blur', ".js--volDiscountCol", function(){
    var currObj = $(this);
    var value = currObj.val();
    var oldValue = currObj.attr('data-oldval');
    var attribute = currObj.attr('name');
    var id = currObj.data('id');
    var selProdId = currObj.data('selprodid');
    if ('' != value && parseFloat(value) != parseFloat(oldValue)) {
        var data = 'attribute='+attribute+"&voldiscount_id="+id+"&selProdId="+selProdId+"&value="+value;
        fcom.ajax(fcom.makeUrl('Seller', 'updateVolumeDiscountColValue'), data, function(t) {
            var ans = $.parseJSON(t);
            if( ans.status != 1 ){
                $.systemMessage(ans.msg, 'alert--danger', true);
                value = updatedValue = oldValue;
            } else {
                updatedValue = ans.data.value;
                currObj.attr('data-oldval', value);
            }
            currObj.val(value);
            showElement(currObj, updatedValue);
        });
    } else {
        showElement(currObj);
        currObj.val(oldValue);
    }
    return false;
});

(function() {
	var dv = '#listing';
	searchVolumeDiscountProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('Seller','searchVolumeDiscountProducts'),data,function(res){
			$("#listing").html(res);
		});
	};
    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('Seller','volumeDiscount');
        } else {
    		document.frmSearch.reset();
    		searchVolumeDiscountProducts(document.frmSearch);
        }
	};
    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchSpecialPricePaging;
		$(frm.page).val(page);
		searchVolumeDiscountProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmSearch;
		searchVolumeDiscountProducts(frm);
	}
    deleteSellerProductVolumeDiscount = function( voldiscount_id ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteSellerProductVolumeDiscount'), 'voldiscount_id=' + voldiscount_id, function(t) {
            $('form#frmVolDiscountListing table tr#row-'+voldiscount_id).remove();
            if (1 > $('form#frmVolDiscountListing table tbody tr').length) {
                searchVolumeDiscountProducts(document.frmSearch);
            }
		});
	}
    deleteVolumeDiscountRows = function(){
        if (typeof $(".selectItem--js:checked").val() === 'undefined') {
	        $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
	        return false;
	    }
        var agree = confirm(langLbl.confirmDelete);
		if( !agree ){ return false; }
        var data = fcom.frmData(document.getElementById('frmVolDiscountListing'));
        fcom.ajax(fcom.makeUrl('Seller', 'deleteVolumeDiscountArr'), data, function(t) {
            var ans = $.parseJSON(t);
			if( ans.status == 1 ){
				$.systemMessage(ans.msg, 'alert--success');
                $('.formActionBtn-js').addClass('formActions-css');
			} else {
                $.systemMessage(ans.msg, 'alert--danger');
			}
            searchVolumeDiscountProducts(document.frmSearch);
        });
	};
    updateVolumeDiscountRow = function(frm, selProd_id){
        if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'updateVolumeDiscountRow'), data, function(t) {
            if(t.status == true){
                if ((1 > frm.addMultiple.value) || 0 < selProd_id) {
                    if (1 > selProd_id) {
                        frm.elements["voldiscount_selprod_id"].value = '';
                    }
                    frm.reset();
                }
                document.getElementById('frmVolDiscountListing').reset()
                $('table.volDiscountList-js tbody').prepend(t.data);
                if (0 < $('.noResult--js').length) {
                    $('.noResult--js').remove();
                }
                $(frm).find("select[name='product_name']").trigger('change.select2');
            }
			$(document).trigger('close.facebox');
            if (0 < frm.addMultiple.value) {
                var volDisRow = $("#"+frm.id).parent().parent();
                volDisRow.siblings('.divider:first').remove();
                volDisRow.remove();
            }
            searchVolumeDiscountProducts(document.frmSearch);
        });
		return false;
	};
    showElement = function(currObj, value){
        var sibling = currObj.siblings('div');
        if ('' != value){
            sibling.text(value);
        }
        sibling.fadeIn();
        currObj.addClass('hidden');
    };
})();
