var selected_products = [];
$(document).ready(function(){
    searchUpsellProducts(document.frmSearch);
    $('#upsell-products').on('click', '.remove_upsell', function() {
        $(this).closest('li').remove();
    });
    
    $("select[name='product_name']").select2({
        closeOnSelect: true,
        dir: layoutDirection,
        allowClear: true,
        placeholder: $("select[name='product_name']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('SellerProducts', 'autoCompleteProducts'),
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
        var item  = e.params.args.data;
        $("#"+parentForm+" input[name='selprod_id']").val(item.id);
            fcom.ajax(fcom.makeUrl('SellerProducts', 'getUpsellProductsList', [item.id]), '', function(t) {
                var ans = $.parseJSON(t);
                $('#upsell-products').empty();
                for (var key in ans.upsellProducts) {
                    $("#upsell-products").append(
                        "<li id=productUpsell"+ans.upsellProducts[key]['selprod_id']+"><span>"+ans.upsellProducts[key]['selprod_title']+" ["+ans.upsellProducts[key]['product_identifier']+"]<i class=\"remove_upsell remove_param fas fa-times\"></i><input type=\"hidden\" name=\"selected_products[]\" value="+ans.upsellProducts[key]['selprod_id']+" /></span></li>"
                    );
                }
        });
  
    }).on('select2:unselecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        $("#" + parentForm + " input[name='selprod_id']").val('');
    });
    
    $("select[name='products_upsell']").select2({
        closeOnSelect: true,
        dir: layoutDirection,
        allowClear: true,
        placeholder: $("select[name='products_upsell']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('SellerProducts', 'autoCompleteProducts'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                var parentForm = $("select[name='products_upsell']").closest('form').attr('id');
                return {
                    keyword: params.term, // search term
                    page: params.page,
                    fIsAjax: 1,
                    selProdId: $("#" + parentForm + " input[name='selprod_id']").val(),
                    selected_products: selected_products
                };
            },
            beforeSend:
                    function (xhr, opts) {
                        var parentForm = $("select[name='products_upsell']").closest('form').attr('id');
                        var selprod_id = $("#" + parentForm + " input[name='selprod_id']").val();
                        if (1 > selprod_id)
                        {
                            xhr.abort();
                        }
                        $('input[name="selected_products[]"]').each(function () {
                            selected_products.push($(this).val());
                        });

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
            return  (typeof result.product_identifier === 'undefined' || typeof result.name === 'undefined') ? result.text : result.name + '[' + result.product_identifier + ']';
        },
        templateSelection: function (result)
        {
            return  (typeof result.product_identifier === 'undefined' || typeof result.name === 'undefined') ? result.text : result.name + '[' + result.product_identifier + ']';
        }
    }).on('select2:selecting', function (e)
    {        
        var item = e.params.args.data;        
        $('input[name=\'products_upsell\']').val('');
        $('#productUpsell' + item.id).remove();
        $('#upsell-products').append('<li id="productUpsell' + item.id + '"><span> ' + item.name + '[' + item.product_identifier + ']' + '<i class="remove_upsell remove_param fas fa-times"></i><input type="hidden" name="selected_products[]" value="' + item.id + '" /></span></li>');
        
        setTimeout(function () {
            $("select[name='products_upsell']").val('').trigger('change');
        }, 200);

    });  
    
});
$(document).on('mouseover', "ul.list-tags li span i", function(){
    $(this).parents('li').addClass("hover");
});
$(document).on('mouseout', "ul.list-tags li span i", function(){
    $(this).parents('li').removeClass("hover");
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
        fcom.ajax(fcom.makeUrl('SellerProducts', 'updateUpsellProductColValue'), data, function(t) {
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
	searchUpsellProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('SellerProducts','searchUpsellProducts'),data,function(res){
			$("#listing").html(res);
		});
	};

    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('SellerProducts','upsellProducts');
        } else {
    		document.frmSearch.reset();
    		searchUpsellProducts(document.frmSearch);
        }
	};

    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchUpsellProductsPaging;
		$(frm.page).val(page);
		searchUpsellProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmUpsellSellerProduct;
		searchUpsellProducts(frm);
	}

    deleteSelprodUpsellProduct = function( selProdId, relProdId ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSelprodUpsellProduct', [selProdId, relProdId] ), '', function(t) {
            /* $('form#frmVolDiscountListing table tr#row-'+voldiscount_id).remove();
            if (1 > $('form#frmVolDiscountListing table tbody tr').length) {
                searchUpsellProducts(document.frmSearch);
            } */
            // $('#upsell-products').empty();
            searchUpsellProducts(document.frmUpsellSellerProduct);
		});
	}

    updateUpsellProductsRow = function(frm, selProd_id){
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'updateUpsellProductsRow'), data, function(t) {
            if(t.status == true){
                if ((1 > frm.addMultiple.value) || 0 < selProd_id) {
                    if (1 > selProd_id) {
                        frm.elements["selprod_id"].value = '';
                    }
                    frm.reset();
                }
                document.getElementById('frmVolDiscountListing').reset()
                $('table.volDiscountList-js tbody').prepend(t.data);
                if (0 < $('.noResult--js').length) {
                    $('.noResult--js').remove();
                }
            }
			$(document).trigger('close.facebox');
            if (0 < frm.addMultiple.value) {
                var volDisRow = $("#"+frm.id).parent().parent();
                volDisRow.siblings('.divider:first').remove();
                volDisRow.remove();
            }
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

    setUpSellerProductLinks = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupUpsellProduct'), data, function(t) {
            document.frmUpsellSellerProduct.reset();
            $("input[name='selprod_id']").val(''); 
            $('#upsell-products').empty();            
            $(frm).find("select[name='product_name']").trigger('change.select2');
            searchUpsellProducts(document.frmUpsellSellerProduct);
		});
	};
})();

$(document).on('click', ".js-product-edit", function(){
    var selProdId = $(this).attr('row-id');
    var prodHtml = $(this).children('.js-prod-name').html(); 
    var prodName = prodHtml.split('<br>');
    var sellerName = $(this).children('.js-seller-name').html(); 
    var selectName = prodName[0]+" | "+sellerName;
    
    fcom.ajax(fcom.makeUrl('SellerProducts', 'getUpsellProductsList', [selProdId]), '', function(t) {
        var ans = $.parseJSON(t);
        $("input[name='selprod_id']").val(selProdId); 
        $("input[name='product_name']").val(selectName); 
        $('#upsell-products').empty();
        for (var key in ans.upsellProducts) {
            $("#upsell-products").append(
                "<li id=productUpsell"+ans.upsellProducts[key]['selprod_id']+"><span>"+ans.upsellProducts[key]['selprod_title']+" ["+ans.upsellProducts[key]['product_identifier']+"]<i class=\"remove_upsell remove_param fas fa-times\"></i><input type=\"hidden\" name=\"selected_products[]\" value="+ans.upsellProducts[key]['selprod_id']+" /></span></li>"
            );
        }
    });
});
