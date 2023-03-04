$(document).on('change', '.option-js', function () {
    var option_id = $(this).val();
    var product_id = $('#frmCustomCatalogProductImage input[name=preq_id]').val();
    var lang_id = $('.language-js').val();
    productImages(product_id, option_id, lang_id);
});

$(document).on('change', '.language-js', function () {
    var lang_id = $(this).val();
    var product_id = $('#frmCustomCatalogProductImage input[name=preq_id]').val();
    var option_id = $('.option-js').val();
    productImages(product_id, option_id, lang_id);
});

(function () {
    var dv = '#listing';
    var prodCatId = 0;
    var blockCount = 0;

    /* customCatalogProductCategoryForm = function () {
     fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductCategoryForm'), '', function (t) {
     $(dv).html(t);
     customCategoryListing(prodCatId, blockCount);
     });
     }; */

    /* customCatalogProductForm = function( id, prodcat_id ){
     $(dv).html( fcom.getLoader() );
     if((typeof prodcat_id == 'undefined' || prodcat_id == 0)){
     customCatalogProductCategoryForm( );
     return;
     }

     fcom.ajax(fcom.makeUrl('Seller', 'customCatalogGeneralForm', [ id , prodcat_id ]), '', function(t) {
     $(dv).html(t);
     });
     }; */

    /* customCategoryListing = function (prodCatId, section) {
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
     }
     prodCatId = ans.prodcat_id;
     });
     }; */

    searchCategory = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategory'), (data), function (t) {
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

    /*setupCustomProduct = function (frm) {
     if (!$(frm).validate())
     return;
     var data = fcom.frmData(frm);
     fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomCatalogProduct'), (data), function (t) {
     runningAjaxReq = false;
     $.mbsmessage.close();
     var addingNew = ($(frm.preq_id).val() == 0);
     customCatalogSellerProductForm(t.preq_id);
     fcom.scrollToTop(dv);
     });
     }; */

    customCatalogProductImages = function (preqId) {
        //var data = 'displayLinkNavigation=true';
        var data = '';
        fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductImages', [preqId]), (data), function (t) {
            //$(dv).html(t);
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_005").show();
            $("a[rel='tabs_005']").parent().addClass('is-active');
            $("#tabs_005").html(t);
            productImages(preqId);
            displaySubmitApprovalButton(preqId);
           // fcom.scrollToTop(dv);
        });
    };

    productImages = function (preqId, option_id, lang_id) {
        if (typeof option_id == 'undefined') {
            option_id = 0;
        }
        if (typeof lang_id == 'undefined') {
            lang_id = 0;
        }
        fcom.ajax(fcom.makeUrl('Seller', 'customCatalogImages', [preqId, option_id, lang_id]), '', function (t) {
            $('#imageupload_div').html(t);
        });
    };

    customCatalogSellerProductForm = function (preq_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'customCatalogSellerProductForm', [preq_id]), '', function (t) {
            $(dv).html(t);
        });
    };

    setUpCustomSellerProduct = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpCustomSellerProduct'), (data), function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.lang_id > 0) {
                customCatalogSpecifications(t.preq_id);
            }
            return;
        });
    };


    /*............CUSTOM CATALOG SPECIFICATION [............*/

    customCatalogSpecifications = function (preq_id) {
        var buttonClick = 0;
        fcom.ajax(fcom.makeUrl('Seller', 'customCatalogSpecifications', [preq_id]), '', function (t) {
            $(dv).html(t);
            fcom.scrollToTop(dv);
        });
    };

    getCustomCatalogSpecificationForm = function (preqId, prodSpecId) {
        if (typeof prodSpecId == 'undefined') {
            prodSpecId = 0;
        }
        buttonClick++;
        var SpecDiv = "#addSpecFields";
        fcom.ajax(fcom.makeUrl('seller', 'getCustomCatalogSpecificationForm', [preqId, prodSpecId, buttonClick]), '', function (t) {
            $(SpecDiv).append(t);
        });
    };

    setupCustomCatalogSpecification = function (frm, preq_id, prodSpecId) {
        if (typeof prodSpecId == 'undefined') {
            prodSpecId = 0;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomCatalogSpecification', [preq_id, prodSpecId]), data, function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.lang_id > 0) {
                customCatalogProductLangForm(t.preqId, t.lang_id);
                return;
            } else {
                customEanUpcForm(t.preqId);
            }
            fcom.scrollToTop(dv);
            return;
        });
    };

    removeSpecDiv = function (currentDiv) {
        $('#specification' + currentDiv).remove();
        buttonClick--;

        /* $(".specification").each(function() {
         str = $(this).attr('id');
         divNumber = str.replace('specification','');
         });
         */
    };

    /* ] */


    customEanUpcForm = function (preq_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'customEanUpcForm', [preq_id]), '', function (t) {
            $(dv).html(t);
        });
    };

    validateEanUpcCode = function (upccode) {
        var data = {code: upccode};
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'validateUpcCode'), (data), function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            return;
        });
    };

    setupEanUpcCode = function (preq_id, frm) {
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupEanUpcCode', [preq_id]), (data), function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            customCatalogProductImages(preq_id);
            return;
        });
    };

    customCatalogProductLangForm = function (preq_id, lang_id, autoFillLangData = 0) {
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('Seller', 'customCatalogProductLangForm', [preq_id, lang_id, autoFillLangData]), '', function (t) {
            $(dv).html(t);
            var frm = $(dv + ' form')[0];
            var validator = $(frm).validation({errordisplay: 3});
            $(frm).submit(function (e) {
                e.preventDefault();
                if (false === validator.validate() || false == validator.valid) {
                    return false;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomCatalogProductLangForm'), data, function (t) {
                    runningAjaxReq = false;
                    $.mbsmessage.close();
                    fcom.resetEditorInstance();
                    if (t.lang_id > 0) {
                        customCatalogProductLangForm(t.preq_id, t.lang_id);
                    } else if (t.productOptions != null) {
                        customEanUpcForm(t.preq_id);
                    } else {
                        customCatalogProductImages(t.preq_id);
                    }

                    fcom.scrollToTop(dv);
                    return;
                });
            });
        });
    };

    popupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
	        fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function(t) {
				$.facebox(t,'faceboxWidth');
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

    uploadImages = function(formData){
		var preq_id = document.imageFrm.preq_id.value;
        var option_id = document.imageFrm.option_id.value;
        var lang_id = document.imageFrm.lang_id.value;
        formData.append('preq_id', preq_id);
        formData.append('option_id', option_id);
        formData.append('lang_id', lang_id);
        $.ajax({
            url : fcom.makeUrl('Seller', 'setupCustomCatalogProductImages'),
            type: 'post',
            dataType: 'json',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#loader-js').html(fcom.getLoader());
            },
            complete: function() {
                $('#loader-js').html(fcom.getLoader());
            },
			success: function(ans) {
                $.mbsmessage(ans.msg, true, 'alert--success');
                productImages($('#frmCustomCatalogProductImage input[name=preq_id]').val(), $('.option').val(), $('.language').val());
				$('#prod_image').val('');
				$(document).trigger('close.facebox');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
        });
	}
	
	brandPopupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
			fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function(t) {
				$('#cropperBox-js').html(t);
				$("#brandMediaForm-js").css("display", "none");
				var ratioType = document.frmBrandMedia.ratio_type.value;
				var aspectRatio = 1 / 1;
				if(ratioType == ratioTypeRectangular){
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

    uploadBrandLogo = function(formData){
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
            beforeSend: function() {
                $('#loader-js').html(fcom.getLoader());
            },
            complete: function() {
                $('#loader-js').html(fcom.getLoader());
            },
            success: function(ans) {
				$('.text-danger').remove();
				$('#input-field').html(ans.msg);
				if( ans.status == true ){
					$('#input-field').removeClass('text-danger');
					$('#input-field').addClass('text-success');
					brandMediaForm(ans.brandId);
				}else{
					$('#input-field').removeClass('text-success');
					$('#input-field').addClass('text-danger');
				}
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
	}

    /*setupCustomCatalogProductImages = function () {
        var data = new FormData(  );
        $inputs = $('#frmCustomCatalogProductImage input[type=text],#frmCustomCatalogProductImage select,#frmCustomCatalogProductImage input[type=hidden]');
        $inputs.each(function () {
            data.append(this.name, $(this).val());
        });

        $.each($('#prod_image')[0].files, function (i, file) {
            $('#imageupload_div').html(fcom.getLoader());
            data.append('prod_image', file);
            $.ajax({
                url: fcom.makeUrl('Seller', 'setupCustomCatalogProductImages'),
                type: "POST",
                data: data,
                processData: false,
                contentType: false,
                success: function (t) {
                    var ans = $.parseJSON(t);
                    $.mbsmessage(ans.msg, true, 'alert--success');
                    //$.systemMessage( ans.msg );
                    productImages($('#frmCustomCatalogProductImage input[name=preq_id]').val(), $('.option').val(), $('.language').val());
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert("Error Occured.");
                }
            });
        });
    };*/

    deleteCustomProductImage = function (preqId, image_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.ajax(fcom.makeUrl('Seller', 'deleteCustomCatalogProductImage', [preqId, image_id]), '', function (t) {
            var ans = $.parseJSON(t);
            $.mbsmessage(ans.msg, true, 'alert--success');
            if (ans.status == 0) {
                return;
            }
            productImages(preqId, $('.option').val(), $('.language').val());
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
        if (!$(frm).validate())
            return;
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
            fcom.ajax(fcom.makeUrl('sellerRequests', 'brandReqLangForm', [brandReqId, langId, autoFillLangData = 0]), '', function (t) {
                $.facebox(t);
            });
        });
    };

    setupBrandReqLang = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('sellerRequests', 'brandReqLangSetup'), data, function (t) {

            if (t.langId > 0) {
                addBrandReqLangForm(t.brandReqId, t.langId);
                return;
            }
            if (t.openMediaForm)
            {
                brandMediaForm(t.brandReqId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    brandMediaForm = function (brandReqId) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('sellerRequests', 'brandMediaForm', [brandReqId]), '', function (t) {
                $.facebox(t);
            });
        });
    };

    removeBrandLogo = function (brandReqId, langId) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('sellerRequests', 'removeBrandLogo', [brandReqId, langId]), '', function (t) {
            brandMediaForm(brandReqId);
            reloadList();
        });
    }

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
    };
	
	/* ] */
    
    /* Product Category Request [*/
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
    
    /*Product Options*/
    searchOptions = function (form) {
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        /*]*/
        $("#optionListing").html(langLbl.processing);
        fcom.ajax(fcom.makeUrl('seller', 'searchOptions'), data, function (res) {
            $("#optionListing").html(res);
        });
    };

    reloadOptionList = function () {
        var frm = document.frmOptionsSearchPaging;
        searchOptions(frm);
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
                    optionValueListing(optionId, false);
                }
            });
        });
        setTimeout(function () {
            fcom.resetFaceboxHeight();
        }, 700);
    };

    submitOptionForm = function (frm, fn) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupOptions'), data, function (t) {
            reloadOptionList();
            $.mbsmessage.close();
            if (t.optionId > 0) {
                optionForm(t.optionId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addOptionForm = function (optionId) {
        var dv = $('#loadForm');
        fcom.ajax(fcom.makeUrl('Seller', 'addOptionForm', [optionId]), '', function (t) {
            dv.html(t);
        });
    };
    optionValueListing = function (optionId, resetHeight) {
        if (typeof resetHeight == undefined || resetHeight == null) {
            resetHeight = true;
        }
        if (optionId == 0) {
            $('#showHideContainer').addClass('hide');
            return;
        }
        var dv = $('#optionValueListing');
        dv.html('Loading....');
        var data = 'option_id=' + optionId;
        fcom.ajax(fcom.makeUrl('OptionValues', 'search'), data, function (res) {
            dv.html(res);
        });
        if (resetHeight) {
            setTimeout(function () {
                fcom.resetFaceboxHeight();
            }, 500);
        }
    };

    optionValueForm = function (optionId, id) {
        var dv = $('#loadForm');
        fcom.ajax(fcom.makeUrl('OptionValues', 'form', [optionId, id]), '', function (t) {
            dv.html(t);
            jscolor.installByClassName('jscolor');
        });
    };

    setUpOptionValues = function (frm) {
        if (!$(frm).validate())
            return;
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
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id + '&option_id=' + optionId;
        fcom.updateWithAjax(fcom.makeUrl('OptionValues', 'deleteRecord'), data, function (res) {
            $.mbsmessage.close();
            optionValueForm(optionId, 0);
            optionValueListing(optionId);
        });
    };

    /* Product Tag  */
    addTagForm = function (id) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('seller', 'addTagsForm', [id]), '', function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
            });
        });
    };

    setupTag = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupTag'), data, function (t) {
            $.mbsmessage.close();
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
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'tagLangSetup'), data, function (t) {
            $.mbsmessage.close();
            if (t.langId > 0) {
                addTagLangForm(t.tagId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    /* Product shipping  */
    /*addShippingTab = function (id) {
        var ShipDiv = "#tab_shipping";
        var e = document.getElementById("product_type");
        var type = e.options[e.selectedIndex].value;

        if (type == prodTypeDigital) {
            $(ShipDiv).html('');
            $('.not-digital-js').hide();
            return;
        } else {
            $('.not-digital-js').show();
        }

        fcom.ajax(fcom.makeUrl('seller', 'getCustomCatalogShippingTab'), 'preq_id=' + id, function (t) {
            try {
                res = jQuery.parseJSON(t);
                $.facebox(res.msg, 'faceboxWidth');
            } catch (e) {
                $(ShipDiv).html(t);
            }
        });
    }; */

    reloadList = function () {
        var frm = document.frmOptionsSearchPaging;
        searchOptions(frm);
    };

    shippingautocomplete = function (shipping_row) {
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').focusout(function () {
            setTimeout(function () {
                $('.suggestions').hide();
            }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').focusout(function () {
            setTimeout(function () {
                $('.suggestions').hide();
            }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').focusout(function () {
            setTimeout(function () {
                $('.suggestions').hide();
            }, 500);
        });

        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').autocomplete({
            minLength: 0,
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('seller', 'countries_autocomplete'),
                    data: {keyword: request['term'], fIsAjax: 1, includeEverywhere: true},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'],
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][country_id]"]').val(ui.item.id);
            }
        }).focus(function() {
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
                    data: {keyword: request['term'], fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'],
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][company_id]"]').val(ui.item.id);
            }
        }).focus(function() {
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
                    data: {keyword: request['term'], fIsAjax: 1},
                    dataType: 'json',
                    type: 'post',
                    success: function (json) {
                        response($.map(json, function (item) {
                            return {
                                label: item['name'] + '[' + item['duraion'] + ']',
                                value: item['name'],
                                id: item['id']
                            };
                        }));
                    },
                });
            },
            'select': function (event, ui) {
                $('input[name="product_shipping[' + shipping_row + '][processing_time_id]"]').val(ui.item.id);
            }
        }).focus(function() {
            $(this).autocomplete("search", $(this).val());
        });
    }
    /*  End of  Product shipping  */

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

    displaySubmitApprovalButton = function (id) {
        if( id < 1 ){
            return false;
        }
        fcom.ajax(fcom.makeUrl('Seller', 'productRequestApprovalButton', [id]), '', function (rsp) {
            $(".js-approval-btn").html(rsp);
        });
    }

    customCatalogProductForm = function (id) {
        fcom.ajax(fcom.makeUrl('Seller', 'customCatalogGeneralForm', [id]), '', function (t) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_001").show();
            $("a[rel='tabs_001']").parent().addClass('is-active');
            $("#tabs_001").html(t);
            displaySubmitApprovalButton(id);
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

    setupcustomCatalogProduct = function (frm) {
        //if (!$(frm).validate())return;
        var getFrm = $('#tabs_001 form')[0];
        var validator = $(getFrm).validation({errordisplay: 3});
        validator.validate();
        if (!validator.isValid()) return;
        //var data = fcom.frmData(frm);
        var data = fcom.frmData(getFrm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupCustomCatalogProduct'), data, function (t) {
            productAttributeAndSpecificationsFrm(t.preqId);
            hideShippingTab(t.productType, t.productTypeDigital);
        });
    };

    productAttributeAndSpecificationsFrm = function (preqId) {
        var data = '';
        fcom.ajax(fcom.makeUrl('Seller', 'productAttributeAndSpecifications', [preqId]), data, function (res) {
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_002").show();
            $("a[rel='tabs_002']").parent().addClass('is-active');
            $("#tabs_002").html(res);
            displaySubmitApprovalButton(preqId);
        });
    }

    setUpCatalogProductAttributes = function (frm) {
        if (!$(frm).validate())
            return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpCatalogProductAttributes'), data, function (t) {
            productOptionsAndTag(t.preqId);
        });
    };

    prodSpecificationSection = function (langId, $key = - 1) {
        var preqId = $("input[name='preq_id']").val();
        var data = "langId=" + langId + "&key=" + $key;
        
        fcom.ajax(fcom.makeUrl('Seller', 'catalogProdSpecForm', [preqId]), data, function (res) {
            $(".specifications-form-" + langId).html(res);
        });
    }

    prodSpecificationsByLangId = function (langId) {
        var preqId = $("input[name='preq_id']").val();
        var data = 'preq_id=' + preqId + '&langId=' + langId;
        fcom.ajax(fcom.makeUrl('Seller', 'catalogSpecificationsByLangId'), data, function (res) {
            $(".specifications-list-" + langId).html(res);
        });
    }

    saveSpecification = function (langId, key = - 1) {
        var preqId = $("input[name='preq_id']").val();
        var prodspec_name = encodeURIComponent($("input[name='prodspec_name[" + langId + "]']").val());
        var prodspec_value = encodeURIComponent($("input[name='prodspec_value[" + langId + "]']").val());
        var prodspec_group = encodeURIComponent($("input[name='prodspec_group[" + langId + "]']").val());
        if (prodspec_name.trim() == '' || prodspec_value.trim() == '') {
            $(".erlist_specification_"+langId).show();
            return false;
        }
        $(".erlist_specification").hide();
        var data = 'preq_id=' + preqId + '&langId=' + langId + '&key=' + key + '&prodspec_name=' + prodspec_name + '&prodspec_value=' + prodspec_value + '&prodspec_group=' + prodspec_group;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpCustomCatalogSpecifications'), data, function (t) {
            prodSpecificationsByLangId(langId);
            prodSpecificationSection(langId);
        });
    }

    deleteProdSpec = function ($key, langId) {
        var agree = confirm("Do you want to delete record?");
        if (!agree) {
            return false;
        }
        var preqId = $("input[name='preq_id']").val();
        var data = "langId=" + langId + "&key=" + $key;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteCustomCatalogSpecification', [preqId]), data, function (t) {
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

    productShipping = function(preqId){
        var data = '';
		fcom.ajax(fcom.makeUrl('Seller','CustomCatalogShippingFrm', [preqId]),data,function(res){
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_004").show();
            $("a[rel='tabs_004']").parent().addClass('is-active');
            $("#tabs_004").html(res);
           /* addShippingTab(preqId);*/
            displaySubmitApprovalButton(preqId);
		});
    }

    /* addShippingTab = function(preqId){
        var ShipDiv = "#tab_shipping";
        fcom.ajax(fcom.makeUrl('seller','getCustomCatalogShippingTab'),'preq_id='+preqId,function(t){
            $(ShipDiv).html(t);
        });
    }; */

    setUpProductShipping = function(frm){
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpCustomCatalogShipping'), data, function(t) {
            customCatalogProductImages(t.preqId);
        });
    }

    productOptionsAndTag = function(preqId){
        var data = '';
		fcom.ajax(fcom.makeUrl('Seller','customCatalogOptionsAndTag', [preqId]),data,function(res){
            $(".tabs_panel").html('');
            $(".tabs_panel").hide();
            $(".tabs_nav-js  > li").removeClass('is-active');
            $("#tabs_003").show();
            $("a[rel='tabs_003']").parent().addClass('is-active');
            $("#tabs_003").html(res);
            displaySubmitApprovalButton(preqId);
		});
    }

    updateProductOption = function (preq_id, option_id, e){
		fcom.ajax(fcom.makeUrl('Seller', 'updateCustomCatalogOption'), 'preq_id='+preq_id+'&option_id='+option_id, function(t) {
            var ans = $.parseJSON(t);
            if( ans.status == 1 ){
                upcListing(preq_id);
                $.mbsmessage(ans.msg, true, 'alert--success');
            }else{
                var tagifyId = e.detail.tag.__tagifyId;
                $('[__tagifyid='+tagifyId+']').remove();
                $.systemMessage(ans.msg, 'alert--danger');
            }
		});
	}

    removeProductOption = function( preq_id,option_id){
        fcom.ajax(fcom.makeUrl('Seller', 'removeCustomCatalogOption'), 'preq_id='+preq_id+'&option_id='+option_id, function(t) {
            var ans = $.parseJSON(t);
            if( ans.status == 1 ){
                upcListing(preq_id);
                $.mbsmessage(ans.msg, true, 'alert--success');
            }
        });
	};

    removeProductTag = function( preq_id,tag_id){
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCustomCatalogTag'), 'preq_id='+preq_id+'&tag_id='+tag_id, function(t) {
        });
	};

    upcListing = function (preq_id){
        fcom.ajax(fcom.makeUrl('Seller', 'customEanUpcForm', [preq_id]), '', function(t) {
            $("#upc-listing").html(t);
        });
    };

    updateUpc = function(preqId, optionValueId){
        var code = $("input[name='code"+optionValueId+"']").val();
        var data = {'code':code,'optionValueId':optionValueId};
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupEanUpcCode',[preqId]), data, function(t) {
        });
    };

    goToCatalogRequest = function(){
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
	
	goToPackagesSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmPackageSearchPaging;
        $(frm.page).val(page);
        shippingPackages(frm);
    };

})();

$(document).on('click', '.uploadFile-Js', function () {
    var node = this;
    $('#form-upload').remove();
    /* var brandId = document.frmProdBrandLang.brand_id.value;
     var langId = document.frmProdBrandLang.lang_id.value; */

    var brandId = $(node).attr('data-brand_id');
    var langId = document.frmBrandMedia.brand_lang_id.value;

    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="brand_id" value="' + brandId + '"/>');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + langId + '"/>');
    frm = frm.concat('</form>');
    $('body').prepend(frm);
    $('#form-upload input[name=\'file\']').trigger('click');
    if (typeof timer != 'undefined') {
        clearInterval(timer);
    }
    timer = setInterval(function () {
        if ($('#form-upload input[name=\'file\']').val() != '') {
            clearInterval(timer);
            $val = $(node).val();
            $.ajax({
                url: fcom.makeUrl('SellerRequests', 'uploadBrandLogo'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(node).val('Loading');
                },
                complete: function () {
                    $(node).val($val);
                },
                success: function (ans) {
                    //$.mbsmessage(ans.msg);
                    $('.text-danger').remove();
                    $('#input-field').html(ans.msg);
                    if (ans.status == true) {
                        $('#input-field').removeClass('text-danger');
                        $('#input-field').addClass('text-success');
                        //brandLangForm( brandId, langId );
                        brandMediaForm(ans.brandId);
                    } else {
                        $('#input-field').removeClass('text-success');
                        $('#input-field').addClass('text-danger');
                    }
                    reloadList();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);
});


$(document).on('click', '.tabs_001', function () {
    var preqId = $("input[name='preq_id']").val();
    customCatalogProductForm(preqId);
});

$(document).on('click', '.tabs_002', function () {
    var preqId = $("input[name='preq_id']").val();
    if (preqId > 0) {
        productAttributeAndSpecificationsFrm(preqId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_003', function () {
    var preqId = $("input[name='preq_id']").val();
    if (preqId > 0) {
        productOptionsAndTag(preqId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_004', function () {
    var preqId = $("input[name='preq_id']").val();
    if (preqId > 0) {
        productShipping(preqId);
    } else {
        displayProdInitialTab();
    }
});

$(document).on('click', '.tabs_005', function () {
    var preqId = $("input[name='preq_id']").val();
    if (preqId > 0) {
        customCatalogProductImages(preqId);
    } else {
        displayProdInitialTab();
    }
});
