var selected_products = [];
$(document).ready(function(){ 
    searchRelatedProducts(document.frmSearch);
    $('#related-products').delegate('.remove_related', 'click', function() {
        $(this).parents('li').remove();
    });
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
        $("#"+parentForm+" input[name='selprod_id']").val(item.id);
      
        fcom.ajax(fcom.makeUrl('Seller', 'getRelatedProductsList', [item.id]), '', function(t) {
            var ans = $.parseJSON(t);
            $('#related-products').empty();
            for (var key in ans.relatedProducts) {
                $('#related-products').append(
                    "<li id=productRelated"+ans.relatedProducts[key]['selprod_id']+"><span>"+ans.relatedProducts[key]['selprod_title']+" ["+ans.relatedProducts[key]['product_identifier']+"]<i class=\"remove_related remove_param fas fa-times\"></i><input type=\"hidden\" name=\"selected_products[]\" value="+ans.relatedProducts[key]['selprod_id']+" /></span></li>"
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
        dir: langLbl.layoutDirection,
        allowClear: true,
        placeholder: $("select[name='products_related']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('seller', 'autoCompleteProducts'),
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
        var parentForm = $(this).closest('form').attr('id');
        var item = e.params.args.data;
        $('input[name=\'products_related\']').val('');
        $('#productRelated' + item.id).remove();
        $('#related-products').append('<li id="productRelated' + item.id + '"><span> ' + item.name + '[' + item.product_identifier + ']' + '<i class="remove_related remove_param fas fa-times"></i><input type="hidden" name="selected_products[]" value="' +
                    item.id + '" /></span></li>');
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

		fcom.ajax(fcom.makeUrl('Seller','searchRelatedProducts'),data,function(res){
			$("#listing").html(res);
		});
	};
    clearSearch = function(selProd_id){
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('Seller','relatedProducts');
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
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteSelprodRelatedProduct', [selProdId, relProdId] ), '', function(t) {
            searchRelatedProducts(document.frmRelatedSellerProduct);
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
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupRelatedProduct'), data, function (t) {
            document.frmRelatedSellerProduct.reset();
            $("input[name='selprod_id']").val('');
            $('#related-products').empty();
            $(frm).find("select[name='product_name']").find('option').remove();
            $(frm).find("select[name='product_name']").trigger('change.select2');
            searchRelatedProducts(document.frmRelatedSellerProduct);
        });
    };
})();

$(document).on('click', ".js-product-edit", function(){
    var selProdId = $(this).attr('row-id');
    var prodHtml = $(this).children('.js-prod-name').html();        
    var prodName = prodHtml.replace(/<br>/g, " | ");
    
    fcom.ajax(fcom.makeUrl('Seller', 'getRelatedProductsList', [selProdId]), '', function(t) {       
        
        var ans = $.parseJSON(t);
        $("input[name='selprod_id']").val(selProdId); 
        /*$("input[name='product_name']").val(prodName[0]); */
        var newOption = new Option(prodName, selProdId, true, true);
        $("select[name='product_name']").append(newOption).trigger('change');
        $('#related-products').empty();
        for (var key in ans.relatedProducts) {
            $('#related-products').append(
                "<li id=productRelated"+ans.relatedProducts[key]['selprod_id']+"><span>"+ans.relatedProducts[key]['selprod_title']+" ["+ans.relatedProducts[key]['product_identifier']+"]<i class=\"remove_related remove_param fas fa-times\"></i><input type=\"hidden\" name=\"selected_products[]\" value="+ans.relatedProducts[key]['selprod_id']+" /></span></li>"
            );
        }
    });
});
