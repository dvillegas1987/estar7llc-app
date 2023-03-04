$(document).ready(function() {
    searchBlogPosts(document.frmSearch);
});
$(document).on('change', '.language-js', function() {
    /* $(document).delegate('.language-js','change',function(){ */
    var lang_id = $(this).val();
    var post_id = $("input[name='post_id']").val();
    images(post_id, lang_id);
});
(function() {
    var currentPage = 1;
    var runningAjaxReq = false;

    goToSearchPage = function(page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmSearchPaging;
        $(frm.page).val(page);
        searchBlogPosts(frm);
    }

    reloadList = function() {
        var frm = document.frmSearchPaging;
        searchBlogPosts(frm);
    }
    addBlogPostForm = function(id) {
        $.facebox(function() {
            blogPostForm(id);
        });
    };
blogPostForm = function(id) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        var frm = document.frmSearchPaging;

        if (typeof parent == undefined || parent == null) {
            parent = 0;
        }
        fcom.ajax(fcom.makeUrl('BlogPosts', 'form', [id, parent]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    };

    setup = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'setup'), data, function(t) {
            reloadList();
            if (t.openLinksForm) {
                linksForm(t.postId);
                return;
            }
            if (t.langId > 0) {
                langForm(t.postId, t.langId);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    setupPostCategories = function(frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'setupCategories'), data, function(t) {
            reloadList();
            if (t.langId > 0) {
                langForm(t.postId, t.langId);
                return;
            }

            $(document).trigger('close.facebox');
        });
    };

    langForm = function(postId, langId, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('BlogPosts', 'langForm', [postId, langId, autoFillLangData]), '', function(t) {
            fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(langId);
            var frm = $('#facebox form')[0];
            var validator = $(frm).validation({
                errordisplay: 3
            });
            $(frm).submit(function(e) {
                e.preventDefault();
                if (validator.validate() == false) {
                    return;
                }
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'langSetup'), data, function(t) {
                    fcom.resetEditorInstance();
                    reloadList();
                    if (t.langId > 0) {
                        langForm(t.postId, t.langId);
                        return;
                    }
                    if (t.openImagesTab) {
                        postImages(t.postId);
                        return;
                    }

                    $(document).trigger('close.facebox');
                });

            });
        });
    };

    searchBlogPosts = function(form) {
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $("#listing").html('Loading....');
        fcom.ajax(fcom.makeUrl('BlogPosts', 'search'), data, function(res) {
            $("#listing").html(res);
        });
    };

    linksForm = function(id) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('BlogPosts', 'linksForm', [id]), '', function(t) {
            fcom.updateFaceboxContent(t);
        });
    }

    deleteRecord = function(id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('BlogPosts', 'deleteRecord'), data, function(res) {
            reloadList();
        });
    };

    clearSearch = function() {
        document.frmSearch.reset();
        searchBlogPosts(document.frmSearch);
    };

    postImages = function(post_id) {
        fcom.resetEditorInstance();
        fcom.ajax(fcom.makeUrl('BlogPosts', 'imagesForm', [post_id]), '', function(t) {
            images(post_id);
            $.facebox(t, 'faceboxWidth');
        });
    };

    images = function(post_id, lang_id) {
        fcom.ajax(fcom.makeUrl('BlogPosts', 'images', [post_id, lang_id]), '', function(t) {
            $('#image-listing').html(t);
            fcom.resetFaceboxHeight();
        });
    };

    deleteImage = function(post_id, afile_id, lang_id) {
        var agree = confirm(langLbl.confirmDelete);
        if (!agree) {
            return false;
        }
        fcom.ajax(fcom.makeUrl('BlogPosts', 'deleteImage', [post_id, afile_id, lang_id]), '', function(t) {
            var ans = $.parseJSON(t);
            if (ans.status == 0) {
                fcom.displayErrorMessage(ans.msg);
                return;
            } else {
                fcom.displaySuccessMessage(ans.msg);
            }
            images(post_id, lang_id);
        });
    }

	deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmBlogPostListing").attr("action",fcom.makeUrl('BlogPosts','deleteSelected')).submit();
    };

    popupImage = function(inputBtn){
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('Collections', 'imgCropper'), '', function(t) {
    			$('#cropperBox-js').html(t);
    			$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
                var minWidth = document.frmBlogPostImage.min_width.value;
                var minHeight = document.frmBlogPostImage.min_height.value;
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
                return cropImage(file, options, 'uploadImages', inputBtn);
        	});
        }
	};

	uploadImages = function(formData){
        var langId = document.frmBlogPostImage.lang_id.value;
        var postId = document.frmBlogPostImage.post_id.value;
        var fileType = document.frmBlogPostImage.file_type.value;

        formData.append('post_id', postId);
        formData.append('file_type', fileType);
        formData.append('lang_id', langId);
        $.ajax({
            url: fcom.makeUrl('BlogPosts', 'uploadBlogPostImages', [postId, langId]),
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
            success: function(t) {
                if (t.status == 1) {
                    fcom.displaySuccessMessage(t.msg);
                } else {
                    fcom.displayErrorMessage(t.msg);
                }
                $('#form-upload').remove();
                postImages(postId);
                images(postId, langId);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert("Error Occured.");
            }
        });
	}

})();