(function() {	
    var runningAjaxReq = false;
	var dv = '#listing';

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};
	
    goToCustomCatalogProductSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmSearchCustomCatalogProducts;
		$(frm.page).val(page);
		searchCustomCatalogProducts(frm);
	};
	
	searchCustomCatalogProducts = function(frm){
		checkRunningAjax();
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('SellerRequests','searchCustomCatalogProducts'),data,function(res){
			runningAjaxReq = false;
			$(dv).html(res);
		});
    };
    
    customCatalogInfo = function(prodreq_id) {
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('SellerRequests','customCatalogInfo',[prodreq_id]), '', function(t){
				$.facebox(t,'faceboxWidth catalogInfo');
			});
		});
	}
    
    goToBrandSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmSearchBrandRequest;
		$(frm.page).val(page);
		searchBrandRequests(frm);
    };
    
    searchBrandRequests = function(frm){
		checkRunningAjax();
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('SellerRequests','searchBrandRequests'),data,function(res){
			runningAjaxReq = false;
			$(dv).html(res);
		});
    };
    
    goToProdCategorySearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page = 1;
		}
		var frm = document.frmSrchProdCategoryRequest;
		$(frm.page).val(page);
		searchProdCategoryRequests(frm);
    };
    
    searchProdCategoryRequests = function(frm){
		checkRunningAjax();
		var data = fcom.frmData(frm);
		$(dv).html( fcom.getLoader() );
		fcom.ajax(fcom.makeUrl('SellerRequests','searchProdCategoryRequests'),data,function(res){
			runningAjaxReq = false;
			$(dv).html(res);
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
            searchBrandRequests(frm);
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
	
	brandPopupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
			fcom.ajax(fcom.makeUrl('sellerRequests', 'imgCropper'), '', function(t) {
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
            url: fcom.makeUrl('sellerRequests', 'uploadBrandLogo'),
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
				if (ans.status == true) {
					$('#input-field').removeClass('text-danger');
					$('#input-field').addClass('text-success');
					brandMediaForm(ans.brandId);
				} else {
					$('#input-field').removeClass('text-success');
					$('#input-field').addClass('text-danger');
				}
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
	}
    
	/* ] */
	
    /* Product Category  request [*/
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
            searchProdCategoryRequests(frm);
        });
    };

    /* ] */
	
	translateData = function(item, defaultLang, toLangId){
        var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
        var prodName = $("input[name='product_name["+defaultLang+"]']").val();
        var oEdit = eval(oUtil.arrEditor[0]);
        var prodDesc = oEdit.getTextBody();

        var alreadyOpen = $('.collapse-js-'+toLangId).hasClass('show');
        if(autoTranslate == 0 || prodName == "" || alreadyOpen == true){
            return false;
        }
        var data = "product_name="+prodName+'&product_description='+prodDesc+"&toLangId="+toLangId ;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'translatedProductData'), data, function(t) {
            if(t.status == 1){
                $("input[name='product_name["+toLangId+"]']").val(t.productName);
                var oEdit1 = eval(oUtil.arrEditor[toLangId - 1]);
                oEdit1.putHTML(t.productDesc);
                var layout = langLbl['language' + toLangId];
                $('#idContent' + oUtil.arrEditor[toLangId - 1]).contents().find("body").css('direction', layout);
                $('#idArea' + oUtil.arrEditor[toLangId - 1] + ' td[dir="ltr"]').attr('dir', layout);
            }
        });
    }

    productInstructions = function( type ){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Seller', 'productTooltipInstruction', [type]), '', function(t) {
				$.facebox(t,'medium-fb-width catalog-bg');
			});
		});
	};
	
})(); 