$(document).ready(function() {
    searchSocialPlatforms();
});
(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    reloadList = function() {
        searchSocialPlatforms();
    };

    searchSocialPlatforms = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SocialPlatform', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    addFormNew = function(id) {
        $.facebox(function() {
            addForm(id);
        });
    };


    addForm = function(id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('SocialPlatform', 'form', [id]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);

        });
    };

    setup = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SocialPlatform', 'setup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                addLangForm(t.splatformId, t.langId);
                return;
            }
            if (t.openMediaForm) {
                mediaForm(t.splatformId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addLangForm = function(splatformId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('SocialPlatform', 'langForm', [splatformId, langId, autoFillLangData]), '', function(t) {
            //$.facebox(t);
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupLang = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SocialPlatform', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                addLangForm(t.splatformId, t.langId);
                return;
            }
            if (t.openMediaForm) {
                mediaForm(t.splatformId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    mediaForm = function(splatform_id) {
        fcom.ajax(fcom.makeUrl('SocialPlatform', 'mediaForm', [splatform_id]), '', function(t) {
            //$.facebox(t);
            fcom.updateFaceboxContent(t);
        });
    };
    removeImg = function(splatform_id) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('SocialPlatform', 'removeImage', [splatform_id]), '', function(t) {
            mediaForm(splatform_id);
            reloadList();
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'splatformId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('SocialPlatform', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    toggleStatus = function(e, obj, canEdit) {
        if (canEdit == 0) {
            e.preventDefault();
            return;
        }
        if (!confirm(langLbl.confirmUpdateStatus)) {
            e.preventDefault();
            return;
        }
        var splatformId = parseInt(obj.value);
        if (splatformId < 1) {
            fcom.displayErrorMessage(langLbl.invalidRequest);
            //$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
            return false;
        }
        data = 'splatformId=' + splatformId;
        fcom.ajax(fcom.makeUrl('SocialPlatform', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                fcom.displaySuccessMessage(ans.msg);
                //$.mbsmessage(ans.msg,true,'alert--success');
                $(obj).toggleClass("active");
            }
        });
    };
    
    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmSocialPlatformListing").attr("action",fcom.makeUrl('SocialPlatform','deleteSelected')).submit();
    };

    popupImage = function(inputBtn){
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('SocialPlatform', 'imgCropper'), '', function(t) {
                $('#cropperBox-js').html(t);
				$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
        		var options = {
                    aspectRatio: 1 / 1,
                    minCropBoxWidth: 30,
                    minCropBoxHeight: 30,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
					imageSmoothingEnabled: true,
    	        };
                $(inputBtn).val('');
    	        return cropImage(file, options, 'uploadImage', inputBtn);
        	});
        }
	};

	uploadImage = function(formData){
        var frmName = formData.get("frmName");
        var splatform_id = document.frmSocialPlatformMedia.splatform_id.value;
        formData.append('splatform_id', splatform_id);
        $.ajax({
            url: fcom.makeUrl('SocialPlatform', 'setUpImage', [splatform_id]),
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
                if (ans.status == 1) {
                    fcom.displaySuccessMessage(ans.msg);
                    mediaForm(ans.splatform_id);
                    reloadList();
                }else{
                    fcom.displayErrorMessage(ans.msg);
                }

            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
	}

})()
