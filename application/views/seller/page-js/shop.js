$(document).ready(function () {
    shopForm();
});

$(document).on('change', '.logo-language-js', function () {
    var lang_id = $(this).val();
    shopImages('logo', 0, lang_id);
});
$(document).on('change', '.banner-language-js', function () {
    var lang_id = $(this).val();
    var slide_screen = $(".prefDimensions-js").val();
    shopImages('banner', slide_screen, lang_id);
});
$(document).on('change', '.prefDimensions-js', function () {
    var slide_screen = $(this).val();
    var lang_id = $(".banner-language-js").val();
    shopImages('banner', slide_screen, lang_id);
});
$(document).on('change', '.bg-language-js', function () {
    var lang_id = $(this).val();
    shopImages('bg', 0, lang_id);
});
$(document).on('change', '.collection-language-js', function () {
    var lang_id = $(this).val();
    var scollection_id = document.frmCollectionMedia.scollection_id.value;
    shopCollectionImages(scollection_id, lang_id);
});

$(document).on("change", "select[name='business_type']", function () {
    requiredFieldsForm();
});

$(document).on("change", ".country", function () {
    if ('' == $(this).val()) {
        return;
    }
    $state = $(this).data("statefield");
    $("." + $state).removeAttr("disabled");
    getStatesByCountryCode($(this).val(), 0, "." + $state, 'state_code');
});

$(document).on("change", ".state", function () {
    $(this).removeAttr("disabled");
});

(function () {
    var runningAjaxReq = false;
    var dv = '#shopFormBlock';
    var dvt = '#shopFormChildBlock';

    checkRunningAjax = function () {
        if (runningAjaxReq == true) {
            //console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    goToCategoryBannerSrchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmCategoryBannerSrchPaging;
        $(frm.page).val(page);
        searchCategoryBanners(frm);
    };

    categoryBanners = function () {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategoryBanners'), '', function (t) {
            $(dv).html(t);
        });
    };

    addCategoryBanner = function (prodCatId) {
        $.facebox(function () {
            fcom.ajax(fcom.makeUrl('Seller', 'addCategoryBanner', [prodCatId]), '', function (t) {
                $.facebox(t, 'faceboxWidth');
            });
        });
    };

    /* categoryBannerLangForm = function( prodCatId, langId ){
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('Seller', 'categoryBannerLangForm',[prodCatId, langId]), '', function(t) {
                $.facebox(t,'faceboxWidth');
            });
        });
    } */

    searchCategoryBanners = function (frm) {
        /*[ this block should be written before overriding html of 'form's parent div/element, otherwise it will through exception in ie due to form being removed from div */
        var data = fcom.frmData(frm);
        /*]*/
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchCategoryBanners'), data, function (res) {
            $(dv).html(res);
        });
    };

    reloadCategoryBannerList = function () {
        searchCategoryBanners(document.frmCategoryBannerSrchPaging);
    };

    removeCategoryBanner = function (prodCatId, lang_id) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCategoryBanner', [prodCatId, lang_id]), '', function (t) {
            reloadCategoryBannerList();
            addCategoryBanner(prodCatId);
        });
    };

    shopForm = function (tab = '') {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopForm', [tab]), '', function (t) {
            $(dv).html(t);
            jscolor.installByClassName("jscolor");
            if ('' != tab) {
                $('.' + tab).click();
                var url = self.location.href;
                url = url.replace(tab, '');
                window.history.pushState("", "", url);
            }
        });
    };

    setupShop = function (frm) {
        if (!$(frm).validate()) return;
        checkRunningAjax();
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShop'), data, function (t) {
            runningAjaxReq = false;
            if (t.langId > 0) {
                shopLangForm(t.shopId, t.langId);
                return;
            }

            shopForm();
            return;
        });
    };

    shopLangForm = function (shopId, langId, autoFillLangData = 0) {
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Seller', 'shopLangForm', [shopId, langId, autoFillLangData]), '', function (t) {
            $(dv).html(t);
            fcom.setEditorLayout(langId);
        });
    };

    setupShopLang = function (frm) {
        if (!$(frm).validate()) return;
        checkRunningAjax();
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setupShopLang'), data, function (t) {
            runningAjaxReq = false;
            $.mbsmessage.close();
            if (t.langId > 0 && t.shopId > 0) {
                shopLangForm(t.shopId, t.langId);
                return;
            }
            returnAddressForm();
        });
    };

    shopMediaForm = function (el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopMediaForm'), '', function (t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            shopImages('logo');
            shopImages('banner', 1);
            shopImages('bg');
        });
    };

    shopImages = function (imageType, slide_screen, lang_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopImages', [imageType, lang_id, slide_screen]), '', function (t) {
            if (imageType == 'logo') {
                $('#logo-image-listing').html(t);
            } else if (imageType == 'banner') {
                $('#banner-image-listing').html(t);
            } else {
                $('#bg-image-listing').html(t);
            }
        });
    };

    shopTemplates = function (el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopTemplate'), '', function (t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
        });
    };
    themeColor = function (el) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopThemeColor'), '', function (t) {
            $(dv).html(t);
            jscolor.installByClassName("jscolor");

        });
    };

    setTemplate = function (ltemplateId) {
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setTemplate', [ltemplateId]), '', function (t) {
            shopTemplates();
        });
    };

    /* getCountryStates = function(countryId,stateId,dv){
        fcom.ajax(fcom.makeUrl('Seller','getStates',[countryId,stateId]),'',function(res){
            $(dv).empty();
            $(dv).append(res);
        });
    }; */

    setUpThemeColor = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupThemeColor'), data, function (t) {
            $.mbsmessage.close();
        });
    };

    removeShopImage = function (BannerId, langId, imageType, slide_screen) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeShopImage', [BannerId, langId, imageType, slide_screen]), '', function (t) {
            shopImages(imageType, slide_screen, langId);
        });
    };

    deleteShopCollection = function (scollection_id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        fcom.ajax(fcom.makeUrl('Seller', 'deleteShopCollection', [scollection_id]), '', function (res) {
            searchShopCollections();
        });
    };

    shopCollections = function (el) {
        $(dv).html(fcom.getLoader());
        // console.log($(el).parent());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollections'), '', function (t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            searchShopCollections();
        });
    };

    searchShopCollections = function (el) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'searchShopCollections'), '', function (t) {
            $(dvt).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
        });
    };

    shopCollectionProducts = function (el) {
        $(dv).html(fcom.getLoader());
        // console.log($(el).parent());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollection'), '', function (t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            getShopCollectionGeneralForm();
        });
    };

    getShopCollectionGeneralForm = function (scollection_id) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionGeneralForm', [scollection_id]), '', function (t) {
            $(dvt).html(t);
        });
    };

    setupShopCollection = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupShopCollection'), data, function (t) {
            $.mbsmessage.close();
            if (t.langId > 0) {
                editShopCollectionLangForm(t.collection_id, t.langId);
                return;
            }

        });
    };

    setupShopCollectionlangForm = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('seller', 'setupShopCollectionLang'), data, function (t) {
            $.mbsmessage.close();
            if (t.langId > 0) {
                editShopCollectionLangForm(t.scollection_id, t.langId);
            }
            if (t.openCollectionLinkForm) {
                sellerCollectionProducts(t.scollection_id);
                return;
            }
        });

    };

    editShopCollectionLangForm = function (scollection_id, langId, autoFillLangData = 0) {
        if (typeof (scollection_id) == "undefined" || scollection_id < 0) {
            return false;
        }
        if (typeof (langId) == "undefined" || langId < 0) {
            return false;
        }
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('seller', 'shopCollectionLangForm', [scollection_id, langId, autoFillLangData]), '', function (t) {
            $(dvt).html(t);
        });
    };

    sellerCollectionProducts = function (scollection_id) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionProductLinkFrm', [scollection_id]), '', function (t) {
            $(dvt).html(t);
            bindAutoComplete();
        });
    };

    setUpSellerCollectionProductLinks = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerCollectionProductLinks'), data, function (t) {
            $.mbsmessage.close();
        });
    };

    socialPlatforms = function (el) {
        $(dv).html(fcom.getLoader());
        // console.log($(el).parent());
        fcom.ajax(fcom.makeUrl('Seller', 'socialPlatforms'), '', function (t) {
            $(dv).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            searchSocialPlatforms();
        });
    };

    searchSocialPlatforms = function (el) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'socialPlatformSearch'), '', function (t) {
            $('.btn-back').addClass('d-none');
            $(dvt).html(t);
        });
    };

    addForm = function (id) {
        fcom.ajax(fcom.makeUrl('Seller', 'socialPlatformForm', [id]), '', function (t) {
            $('.btn-back').removeClass('d-none');
            $(dvt).html(t);
        });
    };

    setup = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'socialPlatformSetup'), data, function (t) {
            $.mbsmessage.close();
            /*reloadSocialPlatformsList();*/
            if (t.langId > 0) {
                addLangForm(t.splatformId, t.langId);
                return;
            }

        });
    };

    addLangForm = function (splatformId, langId, autoFillLangData = 0) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'socialPlatformLangForm', [splatformId, langId, autoFillLangData]), '', function (t) {
            $(dvt).html(t);
        });
    };

    setupLang = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'socialPlatformLangSetup'), data, function (t) {
            $.mbsmessage.close();
            reloadSocialPlatformsList();
            if (t.langId > 0) {
                addLangForm(t.splatformId, t.langId);
                return;
            }
        });
    };

    deleteRecord = function (id) {
        if (!confirm(langLbl.confirmDelete)) { return; }
        data = 'splatformId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'deleteSocialPlatform'), data, function (res) {
            reloadSocialPlatformsList();
        });
    };

    reloadSocialPlatformsList = function () {
        searchSocialPlatforms();
    };

    toggleSocialPlatformStatus = function (e, obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var socialPlatformId = parseInt(obj.value);
        if (socialPlatformId < 1) {
            return false;
        }
        data = 'socialPlatformId=' + socialPlatformId;
        fcom.ajax(fcom.makeUrl('Seller', 'changeSocialPlatformStatus'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    };

    resetDefaultCurrentTemplate = function () {
        var agree = confirm(langLbl.confirmReset);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'resetDefaultThemeColor'), '', function (t) {
            $.mbsmessage.close();
            themeColor();
        });
    };

    returnAddressForm = function () {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'returnAddressForm'), '', function (t) {
            $(dv).html(t);
        });
    };

    setReturnAddress = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setReturnAddress'), data, function (t) {
            returnAddressLangForm(t.langId);
        });
    };

    returnAddressLangForm = function (langId, autoFillLangData = 0) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'returnAddressLangForm', [langId, autoFillLangData]), '', function (t) {
            $(dv).html(t);
        });
    };

    setReturnAddressLang = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setReturnAddressLang'), data, function (t) {
            if (t.langId) {
                returnAddressLangForm(t.langId);
            } else {
                returnAddressForm();
            }
        });
    };

    pickupAddress = function () {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'pickupAddress'), '', function (t) {
            $(dv).html(t);
        });
    };

    pickupAddressForm = function (id) {
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'pickupAddressForm', [id]), '', function (t) {
            $(dv).html(t);
            setTimeout(function () { $('.fromTime-js').change(); }, 500);
        });
    };

    setPickupAddress = function (frm) {
        if (!$(frm).validate()) return;
        if (1 == $(".availabilityType-js:checked").val()) {
            if (1 > $(".slotDays-js:checked").length) {
                $.mbsmessage(langLbl.selectTimeslotDay, true, 'alert--danger');
                return false;
            }
        } else {
            if ('' == $(".selectAllFromTime-js option:selected").val() || '' == $(".selectAllToTime-js option:selected").val()) {
                $.mbsmessage(langLbl.invalidTimeSlot, true, 'alert--danger');
                return false;
            }
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'setPickupAddress'), data, function (t) {
            pickupAddress();
        });
    };

    removeAddress = function (id, type) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        data = 'id=' + id + '&type=' + type;
        fcom.updateWithAjax(fcom.makeUrl('Addresses', 'deleteRecord'), data, function (res) {
            pickupAddress();
        });
    };

    collectionMediaForm = function (el, scollection_id) {
        $(dvt).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionMediaForm', [scollection_id]), '', function (t) {
            $(dvt).html(t);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
            shopCollectionImages(scollection_id);
        });
    };

    shopCollectionImages = function (scollection_id, lang_id) {
        fcom.ajax(fcom.makeUrl('Seller', 'shopCollectionImages', [scollection_id, lang_id]), '', function (t) {
            $('#imageListing').html(t);
        });
    };

    removeCollectionImage = function (scollection_id, langId) {
        var agree = confirm(langLbl.confirmRemove);
        if (!agree) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('Seller', 'removeCollectionImage', [scollection_id, langId]), '', function (t) {
            shopCollectionImages(scollection_id, langId);
        });
    };

    toggleShopCollectionStatus = function (e, obj) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var scollection_id = parseInt(obj.value);
        if (scollection_id < 1) {
            return false;
        }
        data = 'scollection_id=' + scollection_id;
        fcom.ajax(fcom.makeUrl('Seller', 'changeShopCollectionStatus'), data, function (res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $.mbsmessage(ans.msg, true, 'alert--success');
            } else {
                $.mbsmessage(ans.msg, true, 'alert--danger');
            }
        });
    };

    toggleBulkCollectionStatues = function (status) {
        if (!confirm(langLbl.confirmUpdateStatus)) {
            return false;
        }
        $("#frmCollectionsListing input[name='collection_status']").val(status);
        $("#frmCollectionsListing").submit();
    };

    deleteSelectedCollection = function () {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        $("#frmCollectionsListing").attr("action", fcom.makeUrl('Seller', 'deleteSelectedCollections')).submit();
    };

    bannerPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
                var file = inputBtn.files[0];
                var minWidth = document.frmShopBanner.banner_min_width.value;
                var minHeight = document.frmShopBanner.banner_min_height.value;
                var options = {
                    aspectRatio: aspectRatio,
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
                return cropImage(file, options, 'uploadShopImages', inputBtn);
            });
        }
    };

    logoPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
                var file = inputBtn.files[0];
                var minWidth = document.frmShopLogo.logo_min_width.value;
                var minHeight = document.frmShopLogo.logo_min_height.value;
                if (minWidth == minHeight) {
                    var aspectRatio = 1 / 1
                } else {
                    var aspectRatio = 16 / 9;
                }
                var options = {
                    aspectRatio: aspectRatio,
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
                return cropImage(file, options, 'uploadShopImages', inputBtn);
            });
        }
    };

    uploadShopImages = function (formData) {
        var frmName = formData.get("frmName");
        if ('frmShopLogo' == frmName) {
            var langId = document.frmShopLogo.lang_id.value;
            var fileType = document.frmShopLogo.file_type.value;
            var imageType = 'logo';
            var ratio_type = $('input[name="ratio_type"]:checked').val();
        } else {
            var langId = document.frmShopBanner.lang_id.value;
            var slideScreen = document.frmShopBanner.slide_screen.value;
            var fileType = document.frmShopBanner.file_type.value;
            var imageType = 'banner';
            var ratio_type = 0;
        }

        formData.append('slide_screen', slideScreen);
        formData.append('lang_id', langId);
        formData.append('file_type', fileType);
        formData.append('ratio_type', ratio_type);
        formData.append('fIsAjax', 1);
        $.ajax({
            url: fcom.makeUrl('Seller', 'uploadShopImages'),
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
                $.mbsmessage.close();
                $('.text-danger').remove();
                $('#input-field' + fileType).html(ans.msg);
                if (ans.status == true) {
                    $.mbsmessage(ans.msg, true, 'alert--success');
                    $('#input-field' + fileType).removeClass('text-danger');
                    $('#input-field' + fileType).addClass('text-success');
                    $('#form-upload').remove();
                    shopImages(imageType, slideScreen, langId);
                } else {
                    $.mbsmessage(ans.msg, true, 'alert--danger');
                    $('#input-field' + fileType).removeClass('text-success');
                    $('#input-field' + fileType).addClass('text-danger');
                }
                $(document).trigger('close.facebox');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    collectionPopupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Seller', 'imgCropper'), '', function (t) {
                $.facebox(t, 'faceboxWidth medium-fb-width');
                var file = inputBtn.files[0];
                var options = {
                    aspectRatio: 16 / 9,
                    data: {
                        width: collectionMediaWidth,
                        height: collectionMediaHeight,
                    },
                    minCropBoxWidth: collectionMediaWidth,
                    minCropBoxHeight: collectionMediaHeight,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
                    imageSmoothingEnabled: true,
                };
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadCollectionImage', inputBtn);
            });
        }
    };

    uploadCollectionImage = function (formData) {
        var scollection_id = document.frmCollectionMedia.scollection_id.value;
        var lang_id = document.frmCollectionMedia.lang_id.value;

        formData.append('scollection_id', scollection_id);
        formData.append('lang_id', lang_id);
        formData.append('fIsAjax', 1);
        $.ajax({
            url: fcom.makeUrl('Seller', 'uploadCollectionImage'),
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
                $.mbsmessage.close();
                var dv = '#mediaResponse';
                $('.text-danger').remove();
                if (ans.status == true) {
                    $.mbsmessage(ans.msg, true, 'alert--success');
                    $(dv).removeClass('text-danger');
                    $(dv).addClass('text-success');
                    shopCollectionImages(scollection_id, lang_id);
                } else {
                    $.mbsmessage(ans.msg, true, 'alert--danger');
                    $(dv).removeClass('text-success');
                    $(dv).addClass('text-danger');
                }
                $(document).trigger('close.facebox');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    pluginPlatform = function (el) {
        $(dv + " .tabs__content-js").html(fcom.getLoader());
        var platformUrl = $(el).data('platformurl');
        fcom.ajax(platformUrl, '', function (t) {
            t = $.parseJSON(t);
            htm = (1 > t.status) ? t.msg : t.html;

            $(dv + " .tabs__content-js").html(htm);
            $(el).parent().siblings().removeClass('is-active');
            $(el).parent().addClass('is-active');
        });
    };

    requiredFieldsForm = function () {
        var businessType = $("select[name='business_type']").val();
        var contentDv = dv + " .tabs__content-js .row .requiredFieldsForm-js";
        $(contentDv).html(fcom.getLoader());
        var data = 'businessType=' + businessType;
        fcom.ajax(fcom.makeUrl(keyName, 'requiredFieldsForm'), data, function (res) {
            t = $.parseJSON(res);
            if (1 > t.status) {
                $(contentDv).html(t.html);
                bindMcc();
            } else {
                $.mbsmessage(t.msg, false, 'alert--success');
                $('.pluginPlatform-js').click();
            }
            $(".loader-yk").remove();
        });
    };

    bindMcc = function () {
        var selector = $(".mcc--js");
        if (0 < selector.length) {
            var valueFld = selector.data('valfld');

            fcom.ajax(fcom.makeUrl(keyName, 'getMerchantCategory'), '', function (res) {
                selector.select2({
                    closeOnSelect: true,
                    dir: langLbl.layoutDirection,
                    allowClear: true,
                    placeholder: selector.attr('placeholder'),
                    data: $.parseJSON(res),
                    minimumInputLength: 0,
                }).on('select2:selecting', function (e) {
                    var item = e.params.args.data;
                    $("." + valueFld).val(item.id);
                }).on('select2:unselecting', function (e) {
                    $("." + valueFld).val("");
                });
            });
        }
    }

    clearForm = function () {
        requiredFieldsForm();
    };

    setupRequiredFields = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        var attr = $(frm).attr('enctype');
        if (typeof attr !== typeof undefined && attr !== false) {
            $(frm).attr('action', fcom.makeUrl(keyName, 'setupRequiredFields')).removeAttr("onsubmit").submit();
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl(keyName, 'setupRequiredFields'), data, function (t) {
            /* requiredFieldsForm(); */
            $('.' + keyName).click();
        });
    }

    register = function (el) {
        var href = $(el).data('href');
        fcom.updateWithAjax(href, '', function (t) {
            $('.pluginPlatform-js').click();
        });
    }

    completeAccount = function (el) {
        var href = $(el).data('href');
        fcom.updateWithAjax(href, '', function (t) {
            if ('undefined' != typeof t.link) {
                location.href = t.link;
            }
        });
    }

    initialSetup = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl(keyName, 'initialSetup'), data, function (t) {
            $('.' + keyName).click();
        });
    }

    deleteAccount = function (el) {
        if (!confirm(langLbl.deleteAccount)) { return false; };
        var href = $(el).data('href');
        fcom.updateWithAjax(href, '', function (t) {
            $('.pluginPlatform-js').click();
        });
    };
    unlinkAccount = function (el) {
        if (!confirm(langLbl.unlinkAccount)) { return false; };
        var href = $(el).data('href');
        fcom.updateWithAjax(href, '', function (t) {
            $('.pluginPlatform-js').click();
        });
    };
    getUniqueSlugUrl = function (obj, str, recordId) {
        if (str == '') {
            return;
        }
        var data = { url_keyword: str, recordId: recordId }
        fcom.ajax(fcom.makeUrl('Seller', 'isShopRewriteUrlUnique'), data, function (t) {
            var ans = $.parseJSON(t);
            $(obj).next().html(ans.msg);
            if (ans.status == 0) {
                $(obj).next().addClass('text-danger');
            } else {
                $(obj).next().removeClass('text-danger');
            }
        });
    };
})();

function bindAutoComplete() {
    $("select[name='scp_selprod_id']").select2({
        closeOnSelect: true,
        dir: langLbl.layoutDirection,
        allowClear: true,
        //placeholder: $("select[name='scp_selprod_id']").attr('placeholder'),
        ajax: {
            url: fcom.makeUrl('seller', 'autoCompleteProducts'),
            dataType: 'json',
            delay: 250,
            method: 'post',
            data: function (params) {
                return {
                    keyword: params.term, // search term
                    page: params.page,
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
        templateResult: function (result) {
            return (typeof result.product_identifier === 'undefined' || typeof result.name === 'undefined') ? result.text : result.name + '[' + result.product_identifier + ']';
        },
        templateSelection: function (result) {
            return (typeof result.product_identifier === 'undefined' || typeof result.name === 'undefined') ? result.text : result.name + '[' + result.product_identifier + ']';
        }
    }).on('select2:selecting', function (e) {
        var item = e.params.args.data;
        $('input[name=\'scp_selprod_id\']').val('');
        $('#selprod-products' + item.id).remove();
        $('#selprod-products ul ').append('<li id="selprod-products' + item.id + '">' + item.name + '[' + item.product_identifier + ']' + '<i class="remove_link remove_param fa fa-times"></i> <input type="hidden" name="product_ids[]" value="' + item.id + '" /></li>');

        setTimeout(function () {
            $('select[name=\'scp_selprod_id\']').val('').trigger('change.select2');
        }, 200);
    });

}

$(document).on('click', '.catFile-Js', function () {
    var node = this;
    $('#form-upload').remove();
    var prodcat_id = document.frmCategoryMedia.prodcat_id.value;
    var lang_id = document.frmCategoryMedia.lang_id.value;
    var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
    frm = frm.concat('<input type="file" name="file" />');
    frm = frm.concat('<input type="hidden" name="prodcat_id" value="' + prodcat_id + '">');
    frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
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
                url: fcom.makeUrl('Seller', 'setupCategoryBanner'),
                type: 'post',
                dataType: 'json',
                data: new FormData($('#form-upload')[0]),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $(node).val('loading..');
                },
                complete: function () {
                    $(node).val($val);
                },
                success: function (ans) {
                    $.mbsmessage.close();
                    //$.mbsmessage(ans.msg, true, 'alert--success');
                    var dv = '#mediaResponse';
                    $('.text-danger').remove();
                    if (ans.status == true) {
                        $.mbsmessage(ans.msg, true, 'alert--success');
                        $(dv).removeClass('text-danger');
                        $(dv).addClass('text-success');
                        reloadCategoryBannerList();
                        addCategoryBanner(prodcat_id);
                    } else {
                        $.mbsmessage(ans.msg, true, 'alert--danger');
                        $(dv).removeClass('text-success');
                        $(dv).addClass('text-danger');
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
    }, 500);

});