$(document).ready(function() {
    getForm(1);

    $(document).on("click", "#testMail-js", function() {
        fcom.ajax(fcom.makeUrl('Configurations', 'testEmail'), '', function(t) {
            var ans = $.parseJSON(t);
            if (ans.status == 1) {
                $.systemMessage(ans.msg, 'alert--success');
            } else {
                $.systemMessage(ans.msg, 'alert--danger');
            }
        });
    });

    $(document).on("change", "select[name='CONF_TIMEZONE']", function() {
        var timezone = $("select[name='CONF_TIMEZONE']").val();
        fcom.ajax(fcom.makeUrl('Configurations', 'displayDateTime'), 'time_zone=' + timezone , function(t) {
            var ans = $.parseJSON(t);
            $('#currentDate').html(ans.dateTime);
        });
    });

});

(function() {
    var currentPage = 1;
    var runningAjaxReq = false;
    var dv = '#frmBlock';
    getForm = function(frmType) {        
        fcom.resetEditorInstance();
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Configurations', 'form', [frmType]), '', function(t) {
            $(dv).html(t);
            jscolor.installByClassName('jscolor');
        });
    };

    getLangForm = function(frmType, langId, autoFillLangData = 0) {
        fcom.resetEditorInstance();
        $(dv).html(fcom.getLoader());
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Configurations', 'langForm', [frmType, langId, autoFillLangData]), '', function(t) {
            $(dv).html(t);
            fcom.setEditorLayout(langId);
            if (frmType == FORM_MEDIA) {
                $('input[name=btn_submit]').hide();
            }
            var frm = $(dv + ' form')[0];
            var validator = $(frm).validation({
                errordisplay: 3
            });
            $(frm).submit(function(e) {
                e.preventDefault();
                if (validator.validate() == false) {
                    return;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('Configurations', 'setupLang'), data, function(t) {
                    runningAjaxReq = false;
                    fcom.resetEditorInstance();
                    if (t.langId > 0 && t.shopId > 0) {
                        shopLangForm(t.shopId, t.langId);
                        return;
                    }
                });
            });

        });
        $.systemMessage.close();
    }

    setup = function(frm) {
        if (!$(frm).validate()) {
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'setup'), data, function(t) {
            if (t.langId > 0 && t.frmType > 0) {
                getLangForm(t.frmType, t.langId);
                return;
            }
            if (t.frmType > 0) {
                getForm(t.frmType);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    setupLang = function(frm) {
        if (!$(frm).validate()) {
            return;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'setupLang'), data, function(t) {
            if (t.langId > 0 && t.frmType > 0) {
                getLangForm(t.frmType, t.langId);
                return;
            }
            if (t.frmType > 0) {
                getForm(t.frmType);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    removeSiteAdminLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeSiteAdminLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeDesktopLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeDesktopLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeEmailLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeEmailLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeFavicon = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeFavicon', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeSocialFeedImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeSocialFeedImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removePaymentPageLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removePaymentPageLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeWatermarkImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeWatermarkImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeAppleTouchIcon = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeAppleTouchIcon', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeMobileLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeMobileLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeInvoiceLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeInvoiceLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeCollectionBgImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeCollectionBgImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    removeBrandCollectionBgImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeBrandCollectionBgImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    changedMessageAutoCloseSetting = function(val) {
        if (val == YES) {

        }
        if (val == NO) {
            $("input[name='CONF_TIME_AUTO_CLOSE_SYSTEM_MESSAGES']").val(0);
        }
    };

    generalInstructions = function(frmType) {
        fcom.resetEditorInstance();
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('Configurations', 'generalInstructions', [frmType]), '', function(t) {
            $(dv).html(t);
        });
    };

    removeAppMainScreenImage = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeAppMainScreenImage', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };
    removeAppLogo = function(lang_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Configurations', 'removeAppLogo', [lang_id]), '', function(t) {
            getLangForm(document.frmConfiguration.form_type.value, lang_id);
        });
    };

    popupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
	        fcom.ajax(fcom.makeUrl('Configurations', 'imgCropper'), '', function(t) {
				$.facebox(t,'faceboxWidth medium-fb-width');
                var file = inputBtn.files[0];
                var minWidth = $(inputBtn).attr('data-min_width');
                var minHeight = $(inputBtn).attr('data-min_height');
                /*if(minWidth == minHeight){
					var aspectRatio = 1 / 1
				} else {
	                var aspectRatio = 16 / 9;
	            }*/
	    		var options = {
	                aspectRatio: minWidth / minHeight,
	                data: {
	                    width: minWidth,
	                    height: minHeight,
	                },
	                minCropBoxWidth: minWidth,
	                minCropBoxHeight: minHeight,
					minContainerHeight: 350,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
					imageSmoothingEnabled: true,
		        };
				$(inputBtn).val('');
    	  		return cropImage(file, options, 'uploadConfImages', inputBtn);
	    	});
		}
	};

	uploadConfImages = function(formData){
        var langId = document.frmConfiguration.lang_id.value;
        var formType = document.frmConfiguration.form_type.value;
        var fldName = "ratio_type_"+formData.get('file_type');
        var ratio_type = $('input[name="'+fldName+'"]:checked').val();

        formData.append('lang_id', langId);
        formData.append('form_type', formType);
        formData.append('ratio_type', ratio_type);
        $.ajax({
            url: fcom.makeUrl('Configurations', 'uploadMedia'),
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
                if (!ans.status) {
                    $.systemMessage(ans.msg, 'alert--danger');
                    return;
                }
                $.systemMessage(ans.msg, 'alert--success');
                getLangForm(formType, langId);
                $(document).trigger('close.facebox');
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (xhr.responseText) {
                    $.systemMessage(xhr.responseText, 'alert--danger');
                    return;
                }
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
	}
	
	deleteVerificationFile = function(fileType) {
		if (!confirm(langLbl.confirmDelete)) {
			return;
		}
		fcom.updateWithAjax(fcom.makeUrl('Configurations', 'deleteVerificationFile', [fileType]), '', function(t) {
			getForm(document.frmConfiguration.form_type.value);
		});
	};
	
})();


form = function(form_type) {
    if (typeof form_type == undefined || form_type == null) {
        form_type = 1;
    }
    jQuery.ajax({
        type: "POST",
        data: {
            form: form_type,
            fIsAjax: 1
        },
        url: fcom.makeUrl("configurations", "form"),
        success: function(json) {
            json = $.parseJSON(json);
            if ("1" == json.status) {
                $("#tabs_0" + form_type).html(json.msg);
            } else {
                jsonErrorMessage(json.msg)
            }
        }
    });
}

submitForm = function(form, v) {
    $(form).ajaxSubmit({
        delegation: true,
        beforeSubmit: function() {
            v.validate();
            if (!v.isValid()) {
                return false;
            }
        },
        success: function(json) {
            json = $.parseJSON(json);

            if (json.status == "1") {
                jsonSuccessMessage(json.msg)

            } else {
                jsonErrorMessage(json.msg);
            }
        }
    });
    return false;
}

getCountryStates = function(countryId, stateId, dv) {
    fcom.displayProcessing();
    fcom.ajax(fcom.makeUrl('Configurations', 'getStates', [countryId, stateId]), '', function(res) {
        $(dv).empty();
        $(dv).append(res);
    });
    $.systemMessage.close();
};

updateVerificationFile = function(inputBtn, fileType){
	var formData = new FormData();
	formData.append('fileType', fileType);
	var file = inputBtn.files[0];
	if (inputBtn.files && inputBtn.files[0]) {
		var file = inputBtn.files[0];
		fcom.displayProcessing(langLbl.processing, ' ', true);
		formData.append('verification_file', file);
		$.ajax({
			url: fcom.makeUrl('Configurations', 'updateVerificationFile'),
			type: 'post',
			dataType: 'json',
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			success: function(ans) {
				if (!ans.status) {
					$.systemMessage(ans.msg, 'alert--danger');
					return;
				}
				$.systemMessage(ans.msg, 'alert--success');
				getForm(document.frmConfiguration.form_type.value);
				$(document).trigger('close.facebox');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				if (xhr.responseText) {
					$.systemMessage(xhr.responseText, 'alert--danger');
					return;
				}
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}
