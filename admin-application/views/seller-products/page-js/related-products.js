var selected_products = [];

$(document).ready(function(){
    $('.dvFocus-js').hide();
    searchRelatedProducts(document.frmSearch);
    $('#related-products').delegate('.remove_related', 'click', function() {
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
        $("#" + parentForm + " input[name='selprod_id']").val(e.params.args.data.id);
        fcom.ajax(fcom.makeUrl('SellerProducts', 'getRelatedProductsList', [e.params.args.data.id]), '', function (t) {
            var ans = $.parseJSON(t);
            $('#related-products').empty();
            for (var key in ans.relatedProducts) {
                $('#related-products').append(
                        "<li id=productRelated" + ans.relatedProducts[key]['selprod_id'] + "><span>" + ans.relatedProducts[key]['selprod_title'] + " [" + ans.relatedProducts[key]['product_identifier'] + "]<i class=\"remove_related remove_param fas fa-times\"></i><input type=\"hidden\" name=\"selected_products[]\" value=" + ans.relatedProducts[key]['selprod_id'] + " /></span></li>"
                        );
            }
        });

    }).on('select2:unselecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        $("#" + parentForm + " input[name='selprod_id']").val('');
    });


    $("select[name='products_related']").select2({
        closeOnSelect: true,
        dir: layoutDirection,
        allowClear: true,
        placeholder: $("select[name='products_related']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('SellerProducts', 'autoCompleteProducts'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                var parentForm = $("select[name='products_related']").closest('form').attr('id');
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
                        var parentForm = $("select[name='products_related']").closest('form').attr('id');
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
        $('input[name=\'products_related\']').val('');
        $('#productRelated' + item.id).remove();
        $('#related-products').append('<li id="productRelated' + item.id + '"><span> ' + item.name + '[' + item.product_identifier + ']' + '<i class="remove_related remove_param fas fa-times"></i><input type="hidden" name="selected_products[]" value="' + item.id + '" /></span></li>');

        setTimeout(function () {
            $("select[name='products_related']").val('').trigger('change');
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
        fcom.ajax(fcom.makeUrl('SellerProducts', 'updateRelatedProductColValue'), data, function(t) {
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
	searchRelatedProducts = function(frm){

		/*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
		var data = '';
		if (frm) {
			data = fcom.frmData(frm);
		}
		/*]*/
		var dv = $('#listing');
		$(dv).html( fcom.getLoader() );

		fcom.ajax(fcom.makeUrl('SellerProducts','searchRelatedProducts'),data,function(res){
			$("#listing").html(res);
		});
	};
    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('SellerProducts','relatedProducts');
        } else {
    		document.frmSearch.reset();
    		searchRelatedProducts(document.frmSearch);
        }
	};

    goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmSearchRelatedProductsPaging;
		$(frm.page).val(page);
		searchRelatedProducts(frm);
	}

	reloadList = function() {
		var frm = document.frmRelatedSellerProduct;
		searchRelatedProducts(frm);
	}

    deleteSelprodRelatedProduct = function( selProdId, relProdId ){
		var agree = confirm(langLbl.confirmDelete);
		if( !agree ){
			return false;
		}
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSelprodRelatedProduct', [selProdId, relProdId] ), '', function(t) {
            /* $('form#frmVolDiscountListing table tr#row-'+voldiscount_id).remove();
            if (1 > $('form#frmVolDiscountListing table tbody tr').length) {
                searchRelatedProducts(document.frmSearch);
            } */
            // $('#related-products').empty();
            searchRelatedProducts(document.frmRelatedSellerProduct);
		});
	}

    updateRelatedProductsRow = function(frm, selProd_id){
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'updateRelatedProductsRow'), data, function(t) {
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
		fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'setupRelatedProduct'), data, function(t) {
            document.frmRelatedSellerProduct.reset();
            $("input[name='selprod_id']").val(''); 
            $('#related-products').empty();
            $(".dvFocus-js").trigger('click');
            searchRelatedProducts(document.frmRelatedSellerProduct);
            $(frm).find("select[name='product_name']").trigger('change.select2');
            });
	};
})();

$(document).on('click', ".js-product-edit", function(){
    var selProdId = $(this).attr('row-id');
    var prodHtml = $(this).children('.js-prod-name').html();
    var prodName = prodHtml.replace(/<br>/g, " | ");;  
    fcom.ajax(fcom.makeUrl('SellerProducts', 'getRelatedProductsList', [selProdId]), '', function(t) {
        var ans = $.parseJSON(t);
        var newOption = new Option(prodName, selProdId, true, true);
        $("select[name='product_name']").append(newOption).trigger('change');
        $("input[name='selprod_id']").val(selProdId); 
        /*$("input[name='product_name']").val(selectName);*/
        $('#related-products').empty();
        for (var key in ans.relatedProducts) {
            $('#related-products').append(
                "<li id=productRelated"+ans.relatedProducts[key]['selprod_id']+"><span>"+ans.relatedProducts[key]['selprod_title']+" ["+ans.relatedProducts[key]['product_identifier']+"]<i class=\"remove_related remove_param fas fa-times\"></i><input type=\"hidden\" name=\"selected_products[]\" value="+ans.relatedProducts[key]['selprod_id']+" /></span></li>"
            );
        }
    });
});
