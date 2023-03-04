var selected_products = [];
$(document).ready(function(){
    searchUpsellProducts(document.frmSearch);
    $('#upsell-products').delegate('.remove_upsell', 'click', function() {
        $(this).parent().remove();
    });
 
    $("select[name='product_name']").select2({
        closeOnSelect: true,
        dir: langLbl.layoutDirection,
        allowClear: true,
        placeholder: $("select[name='product_name']").attr('placeholder'),
        language:{
            errorLoading: function(){
        return langLbl.noRecordFound;
            },
            loadingMore: function(){
                return langLbl.processing;
            },
            noResults: function(){
                return langLbl.noRecordFound;
            },
            searching: function(){
                return langLbl.processing;
            }
        },
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
        $("#"+parentForm+" input[name='selprod_id']").val(item.id);       
        fcom.ajax(fcom.makeUrl('Seller', 'getUpsellProductsList', [item.id]), '', function(t) {
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
        dir: langLbl.layoutDirection,
        allowClear: true,
        placeholder: $("select[name='products_upsell']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('Seller', 'autoCompleteProducts'),
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

		fcom.ajax(fcom.makeUrl('Seller','searchUpsellProducts'),data,function(res){
			$("#listing").html(res);
		});
	};

    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('Seller','upsellProducts');
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
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteSelprodUpsellProduct', [selProdId, relProdId] ), '', function(t) {
            searchUpsellProducts(document.frmUpsellSellerProduct);
		});
	}

    showElement = function(currObj, value){
        var sibling = currObj.siblings('div');
        if ('' != value){
            sibling.text(value);
        }
        sibling.fadeIn();
        currObj.addClass('hidden');
    };

    setUpSellerProductLinks = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupUpsellProduct'), data, function (t) {
            document.frmUpsellSellerProduct.reset();
            $("input[name='selprod_id']").val('');
            $('#upsell-products').empty();
            $(frm).find("select[name='product_name']").find('option').remove();
            $(frm).find("select[name='product_name']").trigger('change.select2');
            searchUpsellProducts(document.frmUpsellSellerProduct);
        });
    };
})();


$(document).on('click', ".js-product-edit", function(){
    var selProdId = $(this).attr('row-id');
    var prodHtml = $(this).find('.item__title').html();
    var prodName = prodHtml.split('<br>');
    
    fcom.ajax(fcom.makeUrl('Seller', 'getUpsellProductsList', [selProdId]), '', function(t) {
        var ans = $.parseJSON(t);
        $("input[name='selprod_id']").val(selProdId);       
        $("select[name='product_name']").find('option').remove();
        $("select[name='product_name']").append('<option value="-1">'+prodName[0]+'</option>').val(-1).trigger('change.select2');
        $('#upsell-products').empty();
        for (var key in ans.upsellProducts) {
            $("#upsell-products").append(
                "<li id=productUpsell"+ans.upsellProducts[key]['selprod_id']+"><span>"+ans.upsellProducts[key]['selprod_title']+" ["+ans.upsellProducts[key]['product_identifier']+"]<i class=\"remove_upsell remove_param fas fa-times\"></i><input type=\"hidden\" name=\"selected_products[]\" value="+ans.upsellProducts[key]['selprod_id']+" /></span></li>"
            );
        }
    });
});

