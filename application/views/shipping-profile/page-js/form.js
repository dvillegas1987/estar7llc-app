$(document).ready(function () {
    var profileId = $('input[name="profile_id"]').val();
    searchZone(profileId);
    searchProductsSection(profileId);
});
(function () {
    setupProfile = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        var profileId = $('input[name="shipprofile_id"]').val();
        fcom.updateWithAjax(fcom.makeUrl('shippingProfile', 'setup'), data, function (t) {
            if (t.status == 1) {
                if (profileId <= 0) {
                    window.location.replace(fcom.makeUrl('shippingProfile', 'form', [t.profileId]));
                }
            }
        });
    };
    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmProductSearchPaging;
        $(frm.page).val(page);
        var profileId = $('input[name="profile_id"]').val();
        searchProducts(profileId, frm);
    };

    reloadListProduct = function () {
        var frm = document.frmProductSearchPaging;
        var profileId = $('input[name="profile_id"]').val();
        searchProducts(profileId, frm);
    };

    searchProducts = function (profileId, form) {
        var dv = '#product-listing--js';
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }

        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('shippingProfileProducts', 'search', [profileId]), data, function (res) {
            $(dv).html(res);
            document.frmProfileProducts.reset();
        });
    };

    searchProductsSection = function (profileId) {
        var dv = '#product-section--js';
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('shippingProfileProducts', 'index', [profileId]), '', function (res) {
            $(dv).html(res);
            searchProducts(profileId);
        });
    };

    setupProfileProduct = function (frm) {
        if (!$(frm).validate()) return;
        if ($('input[name="shipprofile_id"]').val() <= 0) {
            $.mbsmessage(langLbl.saveProfileFirst, true, 'alert--danger');
            return;
        }
		if ('' == $('input[name="shippro_product_id"]').val()) {
            $.mbsmessage(langLbl.selectProduct, true, 'alert--danger');
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('shippingProfileProducts', 'setup'), data, function (t) {
            var profileId = $('input[name="profile_id"]').val();
            searchProducts(profileId);
            $(document).trigger('close.facebox');
        });
    };

    removeProductFromProfile = function (productId) {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        fcom.updateWithAjax(fcom.makeUrl('shippingProfileProducts', 'removeProduct', [productId]), '', function (t) {
            var profileId = $('input[name="profile_id"]').val();
            searchProducts(profileId);
            $(document).trigger('close.facebox');
        });
    }

    searchZone = function (profileId, scrollToNew = false) {
        var dv = '#listing-zones';
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('ShippingZones', 'search', [profileId]), '', function (res) {
            $(dv).html(res);
            if (true == scrollToNew) {
                setTimeout(function () {
                    $('html, body').animate({
                        scrollTop: $(".zoneRates-js:last").offset().top
                    }, 1000);
                }, 500);
            }
        });
    };

    zoneForm = function (profileId, zoneId) {
        if ($('input[name="shipprofile_id"]').val() <= 0) {
            $.mbsmessage(langLbl.saveProfileFirst, true, 'alert--danger');
            return;
        }       
    
        fcom.ajax(fcom.makeUrl('ShippingZones', 'form', [profileId, zoneId]), '', function (t) {
            $.facebox(t,'faceboxWidth');         
        }); 
    };

    clearForm = function () {
        $('#ship-section--js').html('');
    };
    
    getStates = function (countryId, zoneId, profileId) {
        var shipZoneId = $('input[name="shipzone_id"]').val();
        var isdataLoaded = $('.link_' + countryId).data('loadedstates');
        if (isdataLoaded > 0) {
            return;
        }
        var preSelectedCheckbox = 0;
        if ($(".checkbox_country_" + countryId).is(":checked")) {
            preSelectedCheckbox = 1;
        }
        var dv = '#state_list_' + countryId;
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('ShippingZones', 'searchStates', [countryId, zoneId, shipZoneId, profileId, preSelectedCheckbox]), '', function (res) {
            $(dv).html(res);
            $('.link_' + countryId).data('loadedstates', 1);
            if ($(dv + " .state--js:checked").length) {
                $(dv + " .state--js:checked").prop('checked', false).click();
            }
        });
    }

    setupZone = function (frm) {
        if ($('input[name="rest_of_the_world"]:checked').length < 1 && $('input[name="shiploc_zone_ids[]"]:checked').length < 1 && $('input[name="c_id[]"]:checked').length < 1 && $('input[name="s_id[]"]:checked').length < 1) {
            $.mbsmessage(langLbl.minimumOneLocationRequired, true, 'alert--danger');
            return;
        }

        /* if (!$(frm).validate()) return; */              
        $('.country--js input[type="checkbox"]:checked').each(function(){
            var countryId = $(this).closest('.country--js').data('countryid');
            if($('.country_'+countryId+' .state--js').length == $('.country_'+countryId+' .state--js:not(:disabled)').length){
                $('.country_'+countryId+' .state--js').prop('disabled', true);
            }else{
                $(this).prop('checked', false);
            }
        }); 
        
        /* if (!$(frm).validate()) return; */
        /*var data = fcom.frmData(frm);*/        
        var data = $(frm).serialize();
        fcom.updateWithAjax(fcom.makeUrl('shippingZones', 'setup'), data, function (t) {
            var profileId = $('input[name="profile_id"]').val();
            searchZone(profileId, true);
            clearForm();
            $(document).trigger('close.facebox');
        });
    };

    deleteZone = function (zoneId) {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }

        fcom.updateWithAjax(fcom.makeUrl('shippingZones', 'deleteZone', [zoneId]), '', function (t) {
            var profileId = $('input[name="profile_id"]').val();
            searchZone(profileId);
            clearForm();
        });
    };

    modifyRateFields = function (status) {
        if (status == 1) {
            $('input[name="is_condition"]').val(1);
            $('.add-condition--js').hide();
            $('.remove-condition--js').show();
            $('.condition-field--js').removeClass('d-none');
        } else {
            $('input[name="is_condition"]').val(0);
            $('.remove-condition--js').hide();
            $('.add-condition--js').show();
            $('.condition-field--js').addClass('d-none');
        }
        $('input[name="is_condition"]').trigger('change');
    };

    addEditShipRates = function (zoneId, rateId) {
        /*
        fcom.ajax(fcom.makeUrl('shippingZoneRates', 'form', [zoneId, rateId]), '', function (t) {
            $('#ship-section--js').html(t);
            $('html, body').animate({
                scrollTop: $("#ship-section--js").offset().top
            }, 1000);
        });
        */
        $.facebox(function() {
        	fcom.ajax(fcom.makeUrl('shippingZoneRates', 'form', [zoneId, rateId]), '', function(t) {
        		fcom.updateFaceboxContent(t);        		
        	});
        });
    };

    setupRate = function (frm) {
        if (!$(frm).validate()) return;
        var submitBtn = $("input[name='btn_submit']");
        submitBtn.attr('disabled', 'disabled');
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('shippingZoneRates', 'setup'), data, function (t) {
            submitBtn.removeAttr('disabled');
            var profileId = $('input[name="profile_id"]').val();
            searchZone(profileId);
            if (t.langId > 0) {
                editRateLangForm(t.zoneId, t.rateId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
        setTimeout(function(){
            var attr = submitBtn.attr('disabled');
            if (typeof attr !== typeof undefined && attr !== false) {
                submitBtn.removeAttr('disabled');
            }
        }, 3000);
    };

    editRateLangForm = function (zoneId, rateId, langId) {
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('shippingZoneRates', 'langForm', [zoneId, rateId, langId]), '', function (t) {
                /*$('#ship-section--js').html(t);*/
                fcom.updateFaceboxContent(t);                
            });
        });
    };

    setupLangRate = function (frm) {
        if (!$(frm).validate()) return;
        $("input[name='btn_submit']").attr('disabled', 'disabled');
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('shippingZoneRates', 'langSetup'), data, function (t) {
            $("input[name='btn_submit']").removeAttr('disabled');
            var profileId = $('input[name="profile_id"]').val();
            searchZone(profileId);
            if (t.langId > 0) {
                editRateLangForm(t.zoneId, t.rateId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteRate = function (rateId) {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }

        fcom.updateWithAjax(fcom.makeUrl('shippingZoneRates', 'deleteRate', [rateId]), '', function (t) {
            var profileId = $('input[name="profile_id"]').val();
            searchZone(profileId);
            clearForm();
        });
    }

    getZoneLocation = function (zoneId) {
        $.ajax({
            url: fcom.makeUrl('ShippingZones', 'getLocations', [zoneId, 1]),
            data: { fIsAjax: 1 },
            dataType: 'json',
            type: 'post',
            success: function (res) {
                $('.country--js input[type="checkbox"]').prop('checked', false);
                if (res != '' || res != [] || res != undefined) {
                    $(res).each(function (index, item) {
                        var stateId = item.shiploc_state_id;
                        var countryId = item.shiploc_country_id;
                        var zoneId = item.shiploc_zone_id;
                        if (zoneId == -1) {
                            $('.checkbox_zone_-1').prop('checked', true);
                        }
                        if (stateId == -1) {
                            $('.checkbox_country_' + countryId).prop('checked', true);
                            $('.country_' + countryId + ' input[type="checkbox"]').prop('checked', true);
                        }
                        $('#countries_list_' + zoneId + '.collapse').collapse();
                    });
                }
            },
        });
    }
	
    selectCountryStates = function(countryid) {
        if ($(".checkbox_country_" + countryid).is(":checked")) {			
            var selectedStates = $('.country_' + countryid + ' input[type="checkbox"]:not(:disabled');
            selectedStates.prop('checked', true);
            $('.selectedStateCount--js_' + countryid).html(selectedStates.length);
            $('input[name="rest_of_the_world"]').prop('checked', false);
        } else {
            $('.country_' + countryid + ' input[type="checkbox"]:not(:disabled').prop('checked', false);
            var val = $(".checkbox_country_" + countryid).val();
            var parentIds = val.split("-");
            var zoneId = parentIds[0];                        
            $('.selectedStateCount--js_' + countryid).html(0);
        }
    }
})();

$(document).ready(function () {
    $(document).on('click', 'input[name="rest_of_the_world"]', function () {
        $('.checkbox_container--js input[type="checkbox"]').each(function (index) {
            $(this).prop('checked', false);
        });
        $(this).prop('checked', true);
    });

    $(document).on('click', '.zone--js', function () {
        var zoneid = $(this).data('zoneid');
        if ($(".checkbox_zone_" + zoneid).is(":checked")) {
            $('.zone_' + zoneid + ' .country--js').each(function(){
                    var countryid = $(this).data('countryid');
                    $('.checkbox_country_' + countryid + ':not(:disabled)').prop('checked', true);
                    selectCountryStates(countryid);
            });
        } else {
            $('.zone_' + zoneid + ' input[type="checkbox"]:not(:disabled)').prop('checked', false);
            $(".zone_" + zoneid + " .statecount--js").each(function (index) {
                var statecount = $(this).data('totalcount');
                $(this).html(0);
            });
        }
    });

    $(document).on('click', '.country--js', function () {
        var countryid = $(this).data('countryid');
        selectCountryStates(countryid);        
        if(!$(this).prop("checked")){       
            var zoneId = $(this).find('input').val().split("-")[0];          
            $('.checkbox_zone_' + zoneId).prop('checked', false);
        }        
    });

    $(document).on('click', '.state--js', function () {
        var val = $(this).val();
        var parentIds = val.split("-");
        var zoneId = parentIds[0];
        var countryId = parentIds[1];
        var stateId = parentIds[2];
        if ($(this).is(":checked") == false) {
            $('.checkbox_country_' + countryId).prop('checked', false);
            $('.checkbox_zone_' + zoneId).prop('checked', false);
        }
        var count = $('.country_' + countryId).find('input[type="checkbox"]:checked').length;
        $('.selectedStateCount--js_' + countryId).html(count);
        $('input[name="rest_of_the_world"]').prop('checked', false);
    });
});

$(document).on('keyup', "input[name='product_name']", function () {
    var currObj = $(this);
    var parentForm = currObj.closest('form').attr('id');
    var shipProfileId = $("#" + parentForm + " input[name='shippro_shipprofile_id']").val();
    if ('' != currObj.val()) {
        currObj.siblings('ul.dropdown-menu').remove();
        currObj.autocomplete({
            'source': function (request, response) {
                $.ajax({
                    url: fcom.makeUrl('ShippingProfileProducts', 'autoCompleteProducts'),
                    data: { keyword: request, fIsAjax: 1, keyword: currObj.val(), shipProfileId: shipProfileId },
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
                $("#" + parentForm + " input[name='shippro_product_id']").val(ui.item.id);
            }
        });
    } else {
        $("#" + parentForm + " input[name='shippro_product_id']").val('');
    }
});