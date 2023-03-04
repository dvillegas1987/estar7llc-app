/* $(document).ready(function(){
	customProductForm(productId, productCatId);
	var productOptions=[] ;
}); */
(function () {
    var runningAjaxReq = false;

    checkRunningAjax = function () {
        if (runningAjaxReq == true) {
            console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    var dv = '#listing';
    var prodCatId = 0;
    var blockCount = 0;

	/* customProductForm = function( productId, prodcat_id ){
		$(dv).html( fcom.getLoader() );
		if( (typeof productId == 'undefined' || productId == 0) && (typeof prodcat_id == 'undefined' || prodcat_id == 0) ){
			customCatalogProductCategoryForm( );
			return;
		}

		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ productId, prodcat_id ]), '', function(t) {
			$(dv).html(t);
		});
	}; */

    customCatalogProductCategoryForm = function () {
        fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductCategoryForm'), '', function (t) {
            $(dv).html(t);
            customCategoryListing(prodCatId, blockCount);
        });
    };

    customCategoryListing = function (prodCatId, section) {
        $(section).parent().find('li').removeClass('is-active');
        $(section).addClass('is-active');
        var bcount = $(section).closest('.categoryblock-js').attr('rel');
        if (typeof bcount != 'undefined') {
            blockCount = bcount;
            blockCount = parseInt(blockCount) + 1;
        }
        $(section).closest('.categoryblock-js').nextAll('div').remove();
        var data = "prodCatId=" + prodCatId + "&blockCount=" + blockCount;
        fcom.ajax(fcom.makeUrl('Seller', 'customCategoryListing'), data, function (t) {
            var ans = $.parseJSON(t);
            $.mbsmessage.close();
            if (ans.structure != '') {
                $('.slick-track').append(ans.structure);
                $('#categories-js .slick-prev').remove();
                $('#categories-js .slick-next').remove();
                $('.select-categories-slider-js').slick('reinit');
                if (blockCount > 2) {
                    $('.select-categories-slider-js').slick("slickNext");
                }
                if ($('.box-categories ul').length == 1) {
                    $('.slick-next').css('pointer-events', 'none');
                    $('.slick-next').addClass('slick-disabled');
                }
            }
            prodCatId = ans.prodcat_id;
        });
    };

    customCatalogProductForm = function (id, prodcat_id) {
        $(dv).html(fcom.getLoader());
        if (typeof id == 'undefined' || id == 0 && (typeof prodcat_id == 'undefined' || prodcat_id == 0)) {
            customCatalogProductCategoryForm();
            return;
        }

        fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [id, prodcat_id]), '', function (t) {
            $(dv).html(t);
        });
    };

    searchCategory = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategory'), (data), function (t) {
            //console.log('called');
            $('#categories-js').hide();
            $('#categorySearchListing').html(t);
        });
    };

    categorySearchByCode = function (prodCatCode) {
        frm = document.frmCustomCatalogProductCategoryForm;
        var data = fcom.frmData(frm);
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategory', [prodCatCode]), (data), function (t) {
            $('#categories-js').hide();
            $('#categorySearchListing').html(t);
        });
    };

    clearCategorySearch = function () {
        window.location.reload();
    };


	/* customProductForm = function( productId ){
		fcom.resetEditorInstance();
		fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [ productId ]), '', function(t) {
			$(dv).html(t);
		});
	}; */

	/* setupCustomProduct = function(frm){
		if (!$(frm).validate()) return;

		addingNew = ($(frm.product_id).val() == 0);
		$(frm.product_options).val(productOptions);
		var data = fcom.frmData(frm);

		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomProduct'), (data), function(t) {

			$.mbsmessage.close();

			if (addingNew) {
				customProductLangForm(t.product_id, t.lang_id);
				return ;
			}
			productId =  t.product_id;
			addShippingTab(t.product_id,t.product_type);
		});
	}; */

    customProductLangForm = function (productId, lang_id, autoFillLangData = 0) {
        $(dv).html(fcom.getLoader());
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('Seller', 'customProductLangForm', [productId, lang_id, autoFillLangData]), '', function (t) {
            $(dv).html(t);
            fcom.setEditorLayout(lang_id);

        });
    };

    productOptionsForm = function (id) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('Seller', 'customProductOptionsForm', [id]), '', function (t) {
                fcom.updateFaceboxContent(t, 'faceboxWidth');
                reloadProductOptions(id);
            });
        });
    };

    optionForm = function (optionId) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('Seller', 'optionForm', [optionId]), '', function (t) {
                try {
                    res = jQuery.parseJSON(t);
                    $.facebox(res.msg, 'faceboxWidth');
                } catch (e) {
                    $.facebox(t, 'faceboxWidth');
                    addOptionForm(optionId);
                    optionValueListing(optionId);
                }
            });
        });
        fcom.resetFaceboxHeight();
    };

    addOptionForm = function (optionId) {
        var dv = $('#loadForm');
        fcom.ajax(fcom.makeUrl('Seller', 'addOptionForm', [optionId]), '', function (t) {
            dv.html(t);
        });
    };

    optionValueListing = function (optionId) {
        if (optionId == 0) { $('#showHideContainer').addClass('hide'); return; }
        var dv = $('#optionValueListing');
        dv.html('Loading....');
        var data = 'option_id=' + optionId;
        fcom.ajax(fcom.makeUrl('OptionValues', 'search'), data, function (res) {
            dv.html(res);
        });
        fcom.resetFaceboxHeight();
    };

    optionValueForm = function (optionId, id) {
        var dv = $('#loadForm');
        fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId, id]), '', function (t) {
            dv.html(t);
            jscolor.installByClassName('jscolor');
        });
    };

    setUpOptionValues = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'setup'), data, function (t) {
            $.mbsmessage.close();
            if (t.optionId > 0) {
                optionValueForm(t.optionId, 0);
                optionValueListing(t.optionId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteOptionValue = function (optionId, id) {
        if (!confirm(langLbl.confirmDelete)) { return; }
        data = 'id=' + id + '&option_id=' + optionId;
        fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'deleteRecord'), data, function (res) {
            $.mbsmessage.close();
            optionValueForm(optionId, 0);
            optionValueListing(optionId);
        });
    }

    optionValueSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchOptionValuePaging;
        $(frm.page).val(page);
        searchOptionValueListing(frm);
    };

    searchOptionValueListing = function (form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#optionValueListing").html('Loading....');
        fcom.ajax(fcom.makeUrl('OptionValues', 'search'), data, function (res) {
            $("#optionValueListing").html(res);
        });
    };

    showHideValues = function (obj) {

        var type = obj.value;
        var data = 'optionType=' + type;
        fcom.ajax(fcom.makeUrl('Options', 'canSetValue'), data, function (t) {
            var res = $.parseJSON(t);
            if (res.hideBox == true) {
                $('#showHideContainer').addClass('hide'); return;
            }
            $('#showHideContainer').removeClass('hide');
        });
    };

    submitOptionForm = function (frm, fn) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupOptions'), data, function (t) {
            reloadList();
            $.mbsmessage.close();
            if (t.optionId > 0) {
                optionForm(t.optionId); return;
            }
            $(document).trigger('close.facebox');
        });
    };

    searchOptions = function (form) {
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        $("#optionListing").html('Loading....');

        fcom.ajax(fcom.makeUrl('seller', 'searchOptions'), data, function (res) {
            $("#optionListing").html(res);
        });
    };

    deleteOptionRecord = function (id) {
        if (!confirm(langLbl.confirmDelete)) { return; }
        data = 'id=' + id;
        fcom.ajax(fcom.makeUrl('seller', 'deleteSellerOption'), data, function (t) {
            var ans = jQuery.parseJSON(t);
            if (ans.status != 1) {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
            $.mbsmessage(ans.msg, true, 'alert--success');
            reloadList();

        });
    };

    reloadList = function () {
        var frm = document.frmOptionsSearchPaging;
        searchOptions(frm);
    };

    /* Product shipping  */
	/* addShippingTab = function(id){
		var ShipDiv = "#tab_shipping";
		var e = document.getElementById("product_type");
		var type = e.options[e.selectedIndex].value;

		if(type == prodTypeDigital){
			$(ShipDiv).html('');
			$('.not-digital-js').hide();
			return;
		}else{
			$('.not-digital-js').show();
		}
		fcom.ajax(fcom.makeUrl('seller','getShippingTab'),'product_id='+id,function(t){
			try{
					res= jQuery.parseJSON(t);
					$.facebox(res.msg,'faceboxWidth');
				}catch (e){

					$(ShipDiv).html(t);
				}

		});
	} */

    addShippingTab = function (productId) {
        var ShipDiv = "#tab_shipping";
        fcom.ajax(fcom.makeUrl('seller', 'getShippingTab'), 'product_id=' + productId, function (t) {
            $(ShipDiv).html(t);
        });
    };

    shippingautocomplete = function (shipping_row) {
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').focusout(function () {
            setTimeout(function () { $('.suggestions').hide(); }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').focusout(function () {
            setTimeout(function () { $('.suggestions').hide(); }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').focusout(function () {
            setTimeout(function () { $('.suggestions').hide(); }, 500);
        });
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('seller', 'countries_autocomplete'),
                    data: { keyword: request['term'], fIsAjax: 1, includeEverywhere: true },
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return { label: item['name'], value: item['name'], id: item['id'] };
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][country_name]"]').val(ui.item.label);
                $('input[name="product_shipping[' + shipping_row + '][country_id]"]').val(ui.item.id);
            }
        }).focus(function () {
            $(this).autocomplete("search", $(this).val());
        });

        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('seller', 'shippingCompanyAutocomplete'),
                    data: { keyword: request['term'], fIsAjax: 1 },
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return { label: item['name'], value: item['name'], id: item['id'] };
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][company_name]"]').val(ui.item.label);
                $('input[name="product_shipping[' + shipping_row + '][company_id]"]').val(ui.item.id);
            }
        }).focus(function () {
            $(this).autocomplete("search", $(this).val());
        });

        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('seller', 'shippingMethodDurationAutocomplete'),
                    data: { keyword: request['term'], fIsAjax: 1 },
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return { label: item['name'] + '[' + item['duraion'] + ']', value: item['duraion'], id: item['id'] };
                        }));
                    },
                });
            },
            select: function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').val(ui.item.label);
                $('input[name="product_shipping[' + shipping_row + '][processing_time_id]"]').val(ui.item.id);
            }
        }).focus(function () {
            $(this).autocomplete("search", $(this).val());
        });
    }

    /*  End of  Product shipping  */
    /* Custom Product Options */
    sellerCustomProductOptions = function (id) {
        var dv = '#listing';
        fcom.ajax(fcom.makeUrl('Seller', 'customProductOptions', [id]), '', function (t) {
            $(dv).html(t);
            reloadProductOptions(id);

        });

    }

    updateProductOption = function (product_id, option_id, e) {
        $.systemMessage(langLbl.processing,'alert--process');
        fcom.ajax(fcom.makeUrl('Seller', 'updateProductOption'), 'product_id=' + product_id + '&option_id=' + option_id, function (t) {
            var ans = $.parseJSON(t);
            if (ans.status == 1) {
                upcListing(product_id);
                // $.systemMessage(ans.msg, 'alert--success');
                $.systemMessage.close();
            } else {
                var tagifyId = e.detail.tag.__tagifyId;
                $('[__tagifyid=' + tagifyId + ']').remove();
                $.systemMessage(ans.msg, 'alert--danger');
            }
        });
    }

    removeProductOption = function (product_id, option_id) {
        $.systemMessage(langLbl.processing,'alert--process');
        fcom.ajax(fcom.makeUrl('Seller', 'checkOptionLinkedToInventory'), 'product_id=' + product_id + '&option_id=' + option_id, function (t) {
            ans = jQuery.parseJSON(t);
            if (ans.status != true) {
                var agree = alert(ans.msg);
                return false;
            }
            fcom.ajax(fcom.makeUrl('Seller', 'removeProductOption'), 'product_id=' + product_id + '&option_id=' + option_id, function (t) {
                var ans = $.parseJSON(t);
                if (ans.status == 1) {
                    upcListing(product_id);
                    $.systemMessage.close();
                    // $.systemMessage(ans.msg, 'alert--success');
                    //reloadProductOptions(product_id);
                }

            });
        });
    };

    reloadProductOptions = function (productId) {

        $("#product_options_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('seller', 'ProductOptions', [productId]), '', function (t) {
            $("#product_options_list").html(t);
        });
    }

    /* Custom Product Options */

    /* Custom product Specifications */

    sellerCustomProductSpecifications = function (id) {
        fcom.ajax(fcom.makeUrl('Seller', 'customProductSpecifications', [id]), '', function (t) {
            var dv = '#listing';
            $(dv).html(t);
            reloadProductSpecifications(id);



        });
    }
    reloadProductSpecifications = function (productId) {

        $("#product_specifications_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('seller', 'ProductSpecifications', [productId]), '', function (t) {
            try {
                res = jQuery.parseJSON(t);

                $("#product_specifications_list").html(res.msg);
            } catch (e) {

                $("#product_specifications_list").html(t);
            }

        });
    }
    addProdSpec = function (productId, prodSpecId) {
        fcom.ajax(fcom.makeUrl('seller', 'prodSpecForm', [productId]), 'prodSpecId=' + prodSpecId, function (t) {
            $.facebox(t, 'faceboxWidth');

        });
    }
    deleteProdSpec = function (productId, prodSpecId) {
        fcom.updateWithAjax(fcom.makeUrl('seller', 'deleteProdSpec', [productId]), 'prodSpecId=' + prodSpecId, function (t) {
            sellerCustomProductSpecifications(productId);
            reloadProductSpecifications(productId);

        });
    }

    submitSpecificationForm = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupProductSpecifications'), data, function (t) {
            $.mbsmessage.close();
            sellerCustomProductSpecifications(t.productId);
            reloadProductSpecifications(t.productId);
            if (t.productId > 0) {
                if (t.prodSpecId < 1) {
                    addProdSpec(t.productId);
                } else {
                    $(document).trigger('close.facebox');
                }
                (t.productId); return;
            }

        });
        return false;
    }
    customProductLinks = function (product_id) {
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('Seller', 'customProductLinks', [product_id]), '', function (t) {
            $(dv).html(t);
            reloadProductLinks(product_id);
        });
    }

    updateProductLink = function (product_id, option_id) {
        //reloadProductLinks(product_id);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'updateProductLink'), 'product_id=' + product_id + '&option_id=' + option_id, function (t) {
            reloadProductLinks(product_id);
        });
    }

    removeProductCategory = function (product_id, option_id) {
        var agree = confirm(langLbl.confirmDeleteOption);
        if (!agree) { return false; }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeProductCategory'), 'product_id=' + product_id + '&option_id=' + option_id, function (t) {
            reloadProductLinks(product_id);
        });
    };

    reloadProductLinks = function (product_id) {
        $("#product_links_list").html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'productLinks', [product_id]), '', function (t) {
            $("#product_links_list").html(t);
        });
    }

    setupProductLinks = function (frm) {
        $('input[name="product_category"]').val($('input[name="list_category"]').val());
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupProductLinks'), data, function (t) {
            $(document).trigger('close.facebox');
        });
    }

    addTagForm = function (id) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('seller', 'addTagsForm', [id]), '', function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
            });
        });
    };

    setupTag = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupTag'), data, function (t) {
            $.mbsmessage.close();
            reloadList();
            if (t.langId > 0) {
                addTagLangForm(t.tagId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addTagLangForm = function (tagId, langId, autoFillLangData = 0) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('seller', 'tagsLangForm', [tagId, langId, autoFillLangData]), '', function (t) {
                $.facebox(t);
            });
        });
    };

    setupTagLang = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'tagLangSetup'), data, function (t) {
            $.mbsmessage.close();
            reloadList();
            if (t.langId > 0) {
                addTagLangForm(t.tagId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    /* Product Brand Request [ */

    addBrandReqForm = function (id) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('sellerRequests', 'addBrandReqForm', [id]), '', function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
            });
        });
    };

    setupBrandReq = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('sellerRequests', 'setupBrandReq'), data, function (t) {
            $.mbsmessage.close();

            if (t.langId > 0) {
                addBrandReqLangForm(t.brandReqId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addBrandReqLangForm = function (brandReqId, langId, autoFillLangData = 0) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('sellerRequests', 'brandReqLangForm', [brandReqId, langId, autoFillLangData]), '', function (t) {
                $.facebox(t);
            });
        });
    };

    setupBrandReqLang = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('sellerRequests', 'brandReqLangSetup'), data, function (t) {

            if (t.langId > 0) {
                addBrandReqLangForm(t.brandReqId, t.langId);
                return;
            }
            if (t.openMediaForm) {
                brandMediaForm(t.brandReqId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    checkUniqueBrandName = function (obj, $langId, $brandId) {

        data = "brandName=" + $(obj).val() + "&langId= " + $langId + "&brandId= " + $brandId;
        fcom.ajax(fcom.makeUrl('Brands', 'checkUniqueBrandName'), data, function (t) {
            $.mbsmessage.close();
            $res = $.parseJSON(t);

            if ($res.status == 0) {
                $(obj).val('');

                $alertType = 'alert--danger';

                $.mbsmessage($res.msg, true, $alertType);
            }

        });
    }

    brandMediaForm = function (brandId) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('sellerRequests', 'brandMediaForm', [brandId]), '', function (t) {
                $.facebox(t);
            });
        });
    };


    removeBrandLogo = function (brandId, langId) {
        if (!confirm(langLbl.confirmDelete)) { return; }
        fcom.updateWithAjax(fcom.makeUrl('sellerRequests', 'removeBrandLogo', [brandId, langId]), '', function (t) {
            brandMediaForm(brandId);
            reloadList();
        });
    }

    /* ] */

    /* Product Category  request [ */

    addCategoryReqForm = function (id) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('sellerRequests', 'categoryReqForm', [id]), '', function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
            });
        });
    };

    setupCategoryReq = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('sellerRequests', 'setupCategoryReq'), data, function (t) {
            $(document).trigger('close.facebox');
        });
    };

    /* ] */

    displayProdInitialTab = function () {
        $(".tabs_panel").hide();
        $(".tabs_nav-js  > li").removeClass('is-active');
        $("#tabs_001").show();
        $("a[rel='tabs_001']").parent().addClass('is-active');
    }


    hideShippingTab = function (product_type, PRODUCT_TYPE_DIGITAL) {
        var tabElement = $(".tabs_004").parent();
        var contentElement = $("#tabs_004");
        tabElement.show();
        contentElement.show();
        if (product_type == PRODUCT_TYPE_DIGITAL) {
            tabElement.hide();
            contentElement.hide();
        }
    }

    customProductForm = function (productId) {
        fcom.ajax(fcom.makeUrl('Seller', 'customProductGeneralForm', [productId]), '', function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_001").show();
            $("a[rel='tabs_001']").parent().addClass('is-active');
            $("#tabs_001").html(t);
            fcom.resetEditorWidth();
            var editors = oUtil.arrEditor;
            for (x in editors) {
                var oEdit1 = eval(editors[x]);
                var layout = langLbl['language' + (parseInt(x) + parseInt(1))];
                $('#idContent' + editors[x]).contents().find("body").css('direction', layout);
                $('#idArea' + oEdit1.oName + ' td[dir="ltr"]').attr('dir', layout);
            }
        });
    };

    setupCustomProduct = function (frm) {
        //if (!$(frm).validate()) return;
        var getFrm = $('#tabs_001 form')[0];
        var validator = $(getFrm).validation({ errordisplay: 3 });
        validator.validate();
        if (!validator.isValid()) return;
        //var data = fcom.frmData(frm);
        var data = fcom.frmData(getFrm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomProduct'), data, function (t) {
            productAttributeAndSpecificationsFrm(t.productId);
            hideShippingTab(t.productType, t.productTypeDigital);
        });
    };

    productAttributeAndSpecificationsFrm = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Seller', 'productAttributeAndSpecificationsFrm', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_002").show();
            $("a[rel='tabs_002']").parent().addClass('is-active');
            $("#tabs_002").html(res);
        });
    }

    setUpProductAttributes = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpProductAttributes'), data, function (t) {
            productOptionsAndTag(t.productId);
        });
    };

    prodSpecificationSection = function (langId, prodSpecId = 0) {
        var productId = $("input[name='product_id']").val();
        var data = "langId=" + langId + "&prodSpecId=" + prodSpecId;
        fcom.ajax(fcom.makeUrl('Seller', 'prodSpecForm', [productId]), data, function (res) {
            $(".specifications-form-" + langId).html(res);
        });
    }

    prodSpecificationsByLangId = function (langId) {
        var productId = $("input[name='product_id']").val();
        var data = 'product_id=' + productId + '&langId=' + langId;
        fcom.ajax(fcom.makeUrl('Seller', 'prodSpecificationsByLangId'), data, function (res) {
            $(".specifications-list-" + langId).html(res);
        });
    }

    saveSpecification = function (langId, prodSpecId) {
        var productId = $("input[name='product_id']").val();
        var prodspec_name = encodeURIComponent($("input[name='prodspec_name[" + langId + "]']").val());
        var prodspec_value = encodeURIComponent($("input[name='prodspec_value[" + langId + "]']").val());
        var prodspec_group = encodeURIComponent($("input[name='prodspec_group[" + langId + "]']").val());
        if (prodspec_name.trim() == '' || prodspec_value.trim() == '') {
            $(".erlist_specification_" + langId).show();
            return false;
        }
        $(".erlist_specification_" + langId).hide();
        var data = 'product_id=' + productId + '&langId=' + langId + '&prodSpecId=' + prodSpecId + '&prodspec_name=' + prodspec_name + '&prodspec_value=' + prodspec_value + '&prodspec_group=' + prodspec_group;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpProductSpecifications'), data, function (t) {
            prodSpecificationsByLangId(langId);
            prodSpecificationSection(langId);
        });
    }

    deleteProdSpec = function (prodSpecId, langId) {
        var agree = confirm("Do you want to delete record?");
        if (!agree) { return false; }
        var productId = $("input[name='product_id']").val();
        var data = 'prodSpecId=' + prodSpecId;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteProdSpec', [productId]), data, function (t) {
            prodSpecificationsByLangId(langId);
        });
    }

    displayOtherLangProdSpec = function (obj, langId) {
        if ($('.collapse-js-' + langId).hasClass('show')) {
            return false;
        }
        prodSpecificationSection(langId);
        prodSpecificationsByLangId(langId);
    }

    productOptionsAndTag = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Seller', 'productOptionsAndTag', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_003").show();
            $("a[rel='tabs_003']").parent().addClass('is-active');
            $("#tabs_003").html(res);
        });
    }

    upcListing = function (product_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'upcListing', [product_id]), '', function (t) {
            $("#upc-listing").html(t);
        });
    };

    productShipping = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Seller', 'productShippingFrm', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_004").show();
            $("a[rel='tabs_004']").parent().addClass('is-active');
            $("#tabs_004").html(res);
            addShippingTab(productId);
        });
    }

    setUpProductShipping = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpProductShipping'), data, function (t) {
            productMedia(t.productId);
        });
    }

    productMedia = function (productId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Seller', 'customProductImages', [productId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_005").show();
            $("a[rel='tabs_005']").parent().addClass('is-active');
            $("#tabs_005").html(res);
            productImages(productId);
            if ($("#tabs_006").length > 0) {
                var dataText = $("input[name='btn_Finish']").attr('data-text');
                $("input[name='btn_Finish']").val(dataText);
                $("input[name='btn_Finish']").attr('onClick', 'checkIfAvailableForInventory(' + productId + ')');
            } else {
                $("input[name='btn_Finish']").attr('onClick', 'goToCatalog()');
            }
        });
    }

    productImages = function (product_id, option_id, lang_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'images', [product_id, option_id, lang_id]), '', function (t) {
            $('#imageupload_div').html(t);
        });
    };

    popupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth');
                var file = inputBtn.files[0];
                var minWidth = document.imageFrm.min_width.value;
                var minHeight = document.imageFrm.min_height.value;
                var options = {
                    aspectRatio: 1 / 1,
                    data: {
                        width: minWidth,
                        height: minHeight,
                    },
                    minCropBoxWidth: minWidth,
                    minCropBoxHeight: minHeight,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
                    imageSmoothingEnabled: true,
                };
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadImages', inputBtn);
            });
        }
    };

    uploadImages = function (formData) {
        var product_id = document.imageFrm.product_id.value;
        var option_id = document.imageFrm.option_id.value;
        var lang_id = document.imageFrm.lang_id.value;
        formData.append('product_id', product_id);
        formData.append('option_id', option_id);
        formData.append('lang_id', lang_id);
        $.ajax({
            url: fcom.makeUrl('Seller', 'setupCustomProductImages'),
            type: 'post',
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loader-js').html(fcom.getLoader());
            },
            complete: function () {
                $('#loader-js').html(fcom.getLoader());
            },
            success: function (ans) {
                if (ans.status == 1) {
                    $.mbsmessage(ans.msg, true, 'alert--success');
                } else {
                    $.mbsmessage(ans.msg, true, 'alert--danger');
                }
                productImages($('#frmCustomProductImage input[name=product_id]').val(), $('.option').val(), $('.language').val());
                $('#prod_image').val('');
                $(document).trigger('close.facebox');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    /*setupCustomProductImages = function ( ){
		var data = new FormData(  );
		$inputs = $('#frmCustomProductImage input[type=text],#frmCustomProductImage select,#frmCustomProductImage input[type=hidden]');
		$inputs.each(function() { data.append( this.name,$(this).val());});

		$.each( $('#prod_image')[0].files, function(i, file) {
				$('#imageupload_div').html(fcom.getLoader());
				data.append('prod_image', file);
				$.ajax({
					url : fcom.makeUrl('Seller', 'setupCustomProductImages'),
					type: "POST",
					data : data,
					processData: false,
					contentType: false,
					success: function(t){
						var ans = $.parseJSON(t);
						if(ans.status == 1){
							$.mbsmessage(ans.msg, true, 'alert--success');
						}else{
							$.mbsmessage(ans.msg, true, 'alert--danger');
						}
						productImages( $('#frmCustomProductImage input[name=product_id]').val(), $('.option').val(), $('.language').val() );
						$('#prod_image').val('');
					},
					error: function(jqXHR, textStatus, errorThrown){
						alert("Error Occured.");
					}
				});
			});
	};*/

    deleteCustomProductImage = function (productId, image_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) { return false; }
        fcom.ajax(fcom.makeUrl('Seller', 'deleteCustomProductImage', [productId, image_id]), '', function (t) {
            var ans = $.parseJSON(t);
            $.mbsmessage(ans.msg, true, 'alert--success');
            if (ans.status == 0) {
                return;
            }
            productImages(productId, $('.option').val(), $('.language').val());
        });
    }

    translateData = function (item, defaultLang, toLangId) {
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var prodName = $("input[name='product_name[" + defaultLang + "]']").val();
        var oEdit = eval(oUtil.arrEditor[0]);
        var prodDesc = oEdit.getTextBody();

        var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
        if (autoTranslate == 0 || prodName == "" || alreadyOpen == true) {
            return false;
        }
        var data = "product_name=" + prodName + '&product_description=' + prodDesc + "&toLangId=" + toLangId;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'translatedProductData'), data, function (t) {
            if (t.status == 1) {
                $("input[name='product_name[" + toLangId + "]']").val(t.productName);
                var oEdit1 = eval(oUtil.arrEditor[toLangId - 1]);
                oEdit1.putHTML(t.productDesc);
                var layout = langLbl['language' + toLangId];
                $('#idContent' + oUtil.arrEditor[toLangId - 1]).contents().find("body").css('direction', layout);
                $('#idArea' + oUtil.arrEditor[toLangId - 1] + ' td[dir="ltr"]').attr('dir', layout);
            }
        });
    }


    brandPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function (t) {
                $('#cropperBox-js').html(t);
                $("#brandMediaForm-js").css("display", "none");
                var ratioType = document.frmBrandMedia.ratio_type.value;
                var aspectRatio = 1 / 1;
                if (ratioType == ratioTypeRectangular) {
                    aspectRatio = 16 / 5
                }
                var options = {
                    aspectRatio: aspectRatio,
                    preview: '.img-preview',
                    imageSmoothingQuality: 'high',
                    imageSmoothingEnabled: true,
                    crop: function (e) {
                        var data = e.detail;
                    }
                };
                var file = inputBtn.files[0];
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadBrandLogo', inputBtn);
            });
        }
    };

    uploadBrandLogo = function (formData) {
        var brandId = document.frmBrandMedia.brand_id.value;
        var langId = document.frmBrandMedia.brand_lang_id.value;
        var ratio_type = $('input[name="ratio_type"]:checked').val();
        formData.append('brand_id', brandId);
        formData.append('lang_id', langId);
        formData.append('ratio_type', ratio_type);
        $.ajax({
            url: fcom.makeUrl('SellerRequests', 'uploadBrandLogo'),
            type: 'post',
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#loader-js').html(fcom.getLoader());
            },
            complete: function () {
                $('#loader-js').html(fcom.getLoader());
            },
            success: function (ans) {
                $('.text-danger').remove();
                $('#input-field').html(ans.msg);
                if (ans.status == true) {
                    $('#input-field').removeClass('text-danger');
                    $('#input-field').addClass('text-success');
                    brandMediaForm(ans.brandId);
                } else {
                    $('#input-field').removeClass('text-success');
                    $('#input-field').addClass('text-danger');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    goToCatalog = function () {
        window.location.href = fcom.makeUrl('seller', 'catalog');
    }

    checkIfAvailableForInventory = function (product_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'checkIfNotAnyInventory', [product_id]), '', function (t) {
            $res = $.parseJSON(t);
            if ($res.status == 0) {
                fcom.ajax(fcom.makeUrl('Seller', 'checkIfAvailableForInventory', [product_id]), '', function (t) {
                    $res = $.parseJSON(t);
                    if ($res.status == 0) {
                        $.mbsmessage($res.msg, true, 'alert--danger');
                        return false;
                    }
                    fcom.ajax(fcom.makeUrl('Seller', 'sellerProductGeneralForm', [product_id]), '', function (res) {
                        $(".tabs_panel").html('');
                        $(".tabs_panel").hide();
                        $(".tabs_nav-js  > li").removeClass('is-active');
                        $("#tabs_006").show();
                        $("a[rel='tabs_006']").parent().addClass('is-active');
                        $("#tabs_006").html(res);
                        $('.js-cancel-inventory').attr('onClick', 'goToCatalog()');
                    });
                });
            }
        });

    }

    setUpSellerProduct = function (frm) {
        if (!$(frm).validate()) return;
        events.customizeProduct();
        runningAjaxReq = true;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerProduct'), data, function (t) {
            runningAjaxReq = false;
            setTimeout(function () { window.location.href = fcom.makeUrl('Seller', 'catalog'); }, 1000);
        });
    };

    optionsAssocArr = function (formData) {
        var data = {};
        $.each(formData, function (key, obj) {
            if ('' != obj.value) {
                var a = obj.name.match(/(.*?)\[(.*?)\]\[(.*?)\]/);
                if (a !== null) {
                    var subName = a[1];
                    var subKey = a[2];
                    var options = a[3];

                    if (!data[subName]) {
                        data[subName] = [];
                    }

                    if (!data[subName][subKey]) {
                        data[subName][subKey] = [];
                    }

                    if (data[subName][subKey][options]) {
                        if ($.isArray(data[subName][subKey][options])) {
                            data[subName][subKey][options] = obj.value;
                        } else {
                            data[subName][subKey][options] = obj.value;
                        }
                    } else {
                        data[subName][subKey][options] = obj.value;
                    }
                } else {
                    if (data[obj.name]) {
                        if ($.isArray(data[obj.name])) {
                            data[obj.name].push(obj.value);
                        } else {
                            data[obj.name] = [];
                            data[obj.name].push(obj.value);
                        }
                    } else {
                        data[obj.name] = obj.value;
                    }
                }
            }
        });
        return data;
    };

    setUpMultipleSellerProducts = function (frm, i = 0, orignalData = []) {
        if (!$(frm).validate()) return;

        if (1 > orignalData.length) {
            orignalData = optionsAssocArr($(frm).serializeArray());
        }
        var data = orignalData;
        var varients = data.varients;
        varients = varients.filter(function () { return true; });

        if (i < varients.length) {
            var chunk = varients[i];
            var final = {};
            $.extend(final, data, chunk);
            final.varients = [];
            var data = jQuery.param(final);

            $('.optionFld-js').each(function () {
                var $this = $(this);
                var errorInRow = false;
                $this.find('input').each(function () {
                    if ($(this).parent().hasClass('fldSku') && CONF_PRODUCT_SKU_MANDATORY != 1) {
                        return;
                    }
                    if ($(this).val().length == 0 || $(this).val() == 0) {
                        errorInRow = true;
                        return false;
                    }
                });
                if (errorInRow) {
                    $this.parent().addClass('invalid');
                } else {
                    $this.parent().removeClass('invalid');
                }
            });
            if ($("#optionsTable-js > tbody > tr.invalid").length == $("#optionsTable-js > tbody > tr").length) {
                $.systemMessage(LBL_MANDATORY_OPTION_FIELDS, 'alert--danger');
                return false;
            }

            fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpMultipleSellerProducts'), data, function (t) {
                i++;
                if (i < varients.length) {
                    setUpMultipleSellerProducts(frm, i, orignalData);
                }
            });
            var counterString = langLbl.processing_counter.replace("{counter}", (i + 1));
            counterString = counterString.replace("{count}", varients.length);
            counterString = langLbl.processing + " " + counterString;
            $.mbsmessage(counterString, false, 'alert--process alert');
        }
        if (i == (varients.length - 1)) {
            setTimeout(function () { window.location.href = fcom.makeUrl('Seller', 'products'); }, 1000);
        }
    };

    goToCatalogRequest = function () {
        window.location.href = fcom.makeUrl('SellerRequests');
    }

    shippingPackages = function (form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('shippingPackages', 'search'), data, function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
            });
        });
    };

})();


$(document).on('change', '.option-js', function () {
    var option_id = $(this).val();
    var product_id = $('#frmCustomProductImage input[name=product_id]').val();
    var lang_id = $('.language-js').val();
    productImages(product_id, option_id, lang_id);
});

$(document).on('change', '.language-js', function () {
    var lang_id = $(this).val();
    var product_id = $('#frmCustomProductImage input[name=product_id]').val();
    var option_id = $('.option-js').val();
    productImages(product_id, option_id, lang_id);
});


$(document).on('click', '.tabs_001', function () {
    var productId = $("input[name='product_id']").val();
    customProductForm(productId);
});

$(document).on('click', '.tabs_002', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productAttributeAndSpecificationsFrm(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_003', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productOptionsAndTag(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_004', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productShipping(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_005', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        productMedia(productId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_006', function () {
    var productId = $("input[name='product_id']").val();
    if (productId > 0) {
        checkIfAvailableForInventory(productId);
    } else {
        displayProdInitialTab();
    }
});
