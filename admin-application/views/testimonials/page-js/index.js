$(document).ready(function() {
    searchTestimonial(document.frmTestimonialSearch);
});

(function() {
    var runningAjaxReq = false;
    var dv = '#listing';

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmTestimonialSearchPaging;
        $(frm.page).val(page);
        searchTestimonial(frm);
    }

    reloadList = function() {
        searchTestimonial();
    };

    searchTestimonial = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('Testimonials', 'search'), data, function(res) {
            $(dv).html(res);
        });
    };
    addTestimonialForm = function(id) {

        $.facebox(function() {
            testimonialForm(id);
        });
    };

    testimonialForm = function(id) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Testimonials', 'form', [id]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };
    editTestimonialFormNew = function(testimonialId) {
        $.facebox(function() {
            editTestimonialForm(testimonialId);
        });
    };

    editTestimonialForm = function(testimonialId) {
        fcom.displayProcessing();
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Testimonials', 'form', [testimonialId]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupTestimonial = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'setup'), data, function(t) {
            //$.mbsmessage.close();
            reloadList();
            if (t.langId > 0) {
                editTestimonialLangForm(t.testimonialId, t.langId);
                return;
            }
            if (t.openMediaForm) {
                testimonialMediaForm(t.testimonialId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    }

    editTestimonialLangForm = function(testimonialId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        //	$.facebox(function() {
        fcom.ajax(fcom.makeUrl('Testimonials', 'langForm', [testimonialId, langId, autoFillLangData]), '', function(t) {
            //$.facebox(t,'faceboxWidth');
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    setupLangTestimonial = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'langSetup'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                editTestimonialLangForm(t.testimonialId, t.langId);
                return;
            }
            if (t.openMediaForm) {
                testimonialMediaForm(t.testimonialId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'testimonialId=' + id;
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmTestimonialListing").attr("action",fcom.makeUrl('Testimonials','deleteSelected')).submit();
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
        var testimonialId = parseInt(obj.value);
        if (testimonialId < 1) {

            //$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
            fcom.displayErrorMessage(langLbl.invalidRequest);
            return false;
        }
        data = 'testimonialId=' + testimonialId;
        fcom.ajax(fcom.makeUrl('Testimonials', 'changeStatus'), data, function(res) {
            var ans = $.parseJSON(res);
            if (ans.status == 1) {
                $(obj).toggleClass("active");
                fcom.displaySuccessMessage(ans.msg);
            } else {
                fcom.displayErrorMessage(ans.msg);
            }
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchTestimonial(document.frmSearch);
    };


    testimonialMediaForm = function(testimonialId) {
        //$.facebox(function() {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Testimonials', 'media', [testimonialId]), '', function(t) {
            //$.facebox(t);
            fcom.updateFaceboxContent(t);
        });
        //});
    };

    removeTestimonialImage = function(testimonialId, langId) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('Testimonials', 'removeTestimonialImage', [testimonialId, langId]), '', function(t) {
            testimonialMediaForm(testimonialId);
        });
    }

    popupImage = function(inputBtn){
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Testimonials', 'imgCropper'), '', function(t) {
    			$('#cropperBox-js').html(t);
    			$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
        		var options = {
                    aspectRatio:  1 / 1,
                    data: {
                        width: 80,
                        height: 80,
                    },
                    minCropBoxWidth: 80,
                    minCropBoxHeight: 80,
                    toggleDragModeOnDblclick: false,
                    imageSmoothingQuality: 'high',
					imageSmoothingEnabled: true,
    	        };
                $(inputBtn).val('');
                return cropImage(file, options, 'uploadTestimonialImage', inputBtn);
        	});
        }
	};

	uploadTestimonialImage = function(formData){
		var testimonialId = document.frmTestimonialMedia.testimonial_id.value;
        var langId = 0;

		formData.append('testimonial_id', testimonialId);
        formData.append('lang_id', langId);
        $.ajax({
            url: fcom.makeUrl('Testimonials', 'uploadTestimonialMedia'),
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
                if (!ans.status) {
                    fcom.displayErrorMessage(ans.msg);
                    return;
                }
                fcom.displaySuccessMessage(ans.msg);
                testimonialMediaForm(ans.testimonialId);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
	}

})();
