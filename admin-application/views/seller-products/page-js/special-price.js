$(document).ready(function () {
    searchSpecialPriceProducts(document.frmSearch);
    $('.date_js').datepicker('option', { minDate: new Date() });
    //$("select[name='product_name']").attr('placeholder')
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
        $("#" + parentForm + " input[name='splprice_selprod_id']").val(e.params.args.data.id);
        //currObj.val((ui.item.label).replace(/<[^>]+>/g, ''));
        $("#" + parentForm + " input[name='splprice_start_date']").removeAttr('disabled');
        $("#" + parentForm + " input[name='splprice_end_date']").removeAttr('disabled');
        $("#" + parentForm + " input[name='splprice_price']").removeAttr('disabled');
        var currentPrice = langLbl.currentPrice + ': ' + e.params.args.data.price;
        $("#" + parentForm + " .js-prod-price").html(currentPrice);
        $("#" + parentForm + " .js-prod-price").attr('data-price', e.params.args.data.price);

    }).on('select2:unselecting', function (e)
    {
        var parentForm = $(this).closest('form').attr('id');
        $("#" + parentForm + " input[name='splprice_selprod_id']").val('');
        $("#" + parentForm + " input[name='splprice_start_date']").attr('disabled', 'disabled').val('');
        $("#" + parentForm + " input[name='splprice_end_date']").attr('disabled', 'disabled').val('');
        $("#" + parentForm + " input[name='splprice_price']").attr('disabled', 'disabled').val('');

    });

});

$(document).on('keyup', ".js-special-price", function () {
    var currObj = $(this);
    var parentForm = currObj.closest('form').attr('id');
    var selProdPrice = $("#" + parentForm + " .js-prod-price").attr('data-price');
    var specialPrice = $("#" + parentForm + " .js-special-price").val();
    if (specialPrice != '') {
        var discountAmt = selProdPrice - specialPrice;
        var percentage = ((discountAmt / selProdPrice) * 100);
        if (percentage > 0) {
            percentage = Number(Number(percentage).toFixed(2));
            var discountPercentage = langLbl.discountPercentage + ': ' + percentage + '%';
            console.log("#" + parentForm + " .js-discount-percentage");
            $("#" + parentForm + " .js-discount-percentage").html(discountPercentage);
        } else {
            $("#" + parentForm + " .js-discount-percentage").html('');
        }
    } else {
        $("#" + parentForm + " .js-discount-percentage").html('');
    }
});

$(document).on('keyup', "input[name='product_seller']", function () {
    var currObj = $(this);
    currObj.siblings('ul.dropdown-menu').remove();
    currObj.autocomplete({
        'source': function (request, response) {
            if ('' != request) {
                $.ajax({
                    url: fcom.makeUrl('Products', 'autoCompleteSellerJson'),
                    data: { keyword: request['term'] },
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            var email = '';
                            if (null !== item['credential_email']) {
                                email = ' (' + item['credential_email'] + ')';
                            }
                            return { label: item['credential_username'] + email, value: item['credential_username'] + email, id: item['credential_user_id'] };
                        }));
                    },
                });
            } else {
                $("input[name='product_seller_id']").val('');
            }
        },
        select: function (event, ui) {
            $("input[name='product_seller_id']").val(ui.item.id);
        }
    });
});

$(document).on('click', 'table.splPriceList-js tr td .js--editCol', function () {
    $(this).hide();
    var input = $(this).siblings('input[type="text"]');
    var value = input.attr('value');
    input.removeClass('hide');
    input.val('').focus().val(value);
});

$(document).on('blur', ".js--splPriceCol.date_js", function () {
    var currObj = $(this);
    var oldValue = currObj.attr('data-oldval');
    showElement(currObj, oldValue);
});
$(document).on('change', ".js--splPriceCol.date_js", function () {
    updateValues($(this));
});

$(document).on('blur', ".js--splPriceCol:not(.date_js)", function () {
    updateValues($(this));
});

(function () {
    var dv = '#listing';
    searchSpecialPriceProducts = function (frm) {

        /*[ this block should be before dv.html('... anything here.....') otherwise it will through exception in ie due to form being removed from div 'dv' while putting html*/
        var data = '';
        if (frm) {
            data = fcom.frmData(frm);
        }
        /*]*/
        var dv = $('#listing');
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('SellerProducts', 'searchSpecialPriceProducts'), data, function (res) {
            $("#listing").html(res);
            $('.date_js').datepicker('option', { minDate: new Date() });
        });
    };
    clearSearch = function (selProd_id) {
        if (0 < selProd_id) {
            location.href = fcom.makeUrl('SellerProducts', 'specialPrice');
        } else {
            document.frmSearch.reset();
            document.frmSearch.product_seller_id.value = '';
            searchSpecialPriceProducts(document.frmSearch);
        }
    };
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchSpecialPricePaging;
        $(frm.page).val(page);
        searchSpecialPriceProducts(frm);
    }

    reloadList = function () {
        var frm = document.frmSearch;
        searchSpecialPriceProducts(frm);
    }
    deleteSellerProductSpecialPrice = function (splPrice_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'deleteSellerProductSpecialPrice'), 'splprice_id=' + splPrice_id, function (t) {
            $('form#frmSplPriceListing table tr#row-' + splPrice_id).remove();
            if (1 > $('form#frmSplPriceListing table tbody tr').length) {
                searchSpecialPriceProducts(document.frmSearch);
            }
        });
    }
    deleteSpecialPriceRows = function () {
        if (typeof $(".selectItem--js:checked").val() === 'undefined') {
            $.systemMessage(langLbl.atleastOneRecord, 'alert--danger');
            return false;
        }
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) { return false; }
        var data = fcom.frmData(document.getElementById('frmSplPriceListing'));
        fcom.ajax(fcom.makeUrl('SellerProducts', 'deleteSpecialPriceRows'), data, function (t) {
            var ans = $.parseJSON(t);
            if (ans.status == 1) {
                $.systemMessage(ans.msg, 'alert--success');
                $('.formActionBtn-js').addClass('formActions-css');
            } else {
                $.systemMessage(ans.msg, 'alert--danger');
            }
            searchSpecialPriceProducts(document.frmSearch);
        });
    };
    updateSpecialPriceRow = function (frm, selProd_id) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SellerProducts', 'updateSpecialPriceRow'), data, function (t) {
            if (t.status == true) {
                if (1 > frm.addMultiple.value || 0 < selProd_id) {
                    if (1 > selProd_id) {
                        frm.elements["splprice_selprod_id"].value = '';
                    }
                    frm.reset();
                }
                document.getElementById('frmSplPriceListing').reset()
                $(frm).find("select[name='product_name']").trigger('change.select2');
                $('table.splPriceList-js tbody').prepend(t.data);
                $('.date_js').datepicker('option', { minDate: new Date() });
                if (0 < $('.noResult--js').length) {
                    $('.noResult--js').remove();
                }
                $("#frmAddSpecialPrice-" + selProd_id +" .js-discount-percentage").html('');
                $("#frmAddSpecialPrice-" + selProd_id +" .js-prod-price").html('');
            }
            $(document).trigger('close.facebox');
            if (0 < frm.addMultiple.value) {
                var splPriceRow = $("#" + frm.id).parent().parent();
                splPriceRow.siblings('.divider:first').remove();
                splPriceRow.remove();
            }
        });
        return false;
    };
    updateValues = function (currObj) {
        var value = currObj.val();
        var oldValue = currObj.attr('data-oldval');
        var displayOldValue = currObj.attr('data-displayoldval');
        displayOldValue = typeof displayOldValue == 'undefined' ? oldValue : displayOldValue;
        var attribute = currObj.attr('name');
        var id = currObj.data('id');
        var selProdId = currObj.data('selprodid');
        if ('splprice_price' == attribute) {
            value = parseFloat(value);
            oldValue = parseFloat(oldValue);
        }
        if ('' != value && value != oldValue) {
            var data = 'attribute=' + attribute + "&splprice_id=" + id + "&selProdId=" + selProdId + "&value=" + value;
            fcom.ajax(fcom.makeUrl('SellerProducts', 'updateSpecialPriceColValue'), data, function (t) {
                var ans = $.parseJSON(t);
                if (ans.status != 1) {
                    $.systemMessage(ans.msg, 'alert--danger', true);
                    value = oldValue;
                    updatedValue = displayOldValue;
                } else {
                    updatedValue = ans.data.value;
                    currObj.attr('data-oldval', value);
                }
                currObj.attr('value', value);
                showElement(currObj, updatedValue);
            });
        } else {
            showElement(currObj);
            currObj.val(oldValue);
        }
    };
    showElement = function (currObj, value) {
        var sibling = currObj.siblings('div.js--editCol');
        var percentDiv = currObj.siblings('div.js--percentVal');
        if ('' != value) {
            sibling.text(value);
            var price = currObj.attr('data-price');
            var value = currObj.attr('value');
            var discountPrice = price - value;
            var discountPercentage = ((discountPrice / price) * 100).toFixed(2);
            discountPercentage = discountPercentage + "% off";
            percentDiv.text(discountPercentage);
        }
        sibling.fadeIn();
        currObj.addClass('hide');
    };
})();
