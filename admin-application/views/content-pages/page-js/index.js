$(document).ready(function () {
    searchPages(document.frmPagesSearch);
});

(function () {

    var currentPage = 1;
    var runningAjaxReq = false;

    pagesLayouts = function () {
        //$.facebox(function() {
        fcom.ajax(fcom.makeUrl('ContentPages', 'layouts'), '', function (t) {
            $.facebox(t, 'faceboxWidth');
        });
        //});
    };

    goToSearchPage = function (page) {
        if (typeof page == undefined || page == null) {
            page = 1;
        }
        var frm = document.frmPagesSearchPaging;
        $(frm.page).val(page);
        searchPages(frm);
    }

    reloadList = function () {
        var frm = document.frmPagesSearchPaging;
        searchPages(frm);
    }

    searchPages = function (form) {
        var dv = '#pageListing';
        var data = '';
        if (form) {
            data = fcom.frmData(form);
        }
        $(dv).html('Loading....');
        fcom.ajax(fcom.makeUrl('ContentPages', 'search'), data, function (res) {
            $(dv).html(res);
        });
    };

    addFormNew = function (id) {

        $.facebox(function () {
            addForm(id)
        });

    }
    addForm = function (id) {
        fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('ContentPages', 'form', [id]), '', function (t) {
            fcom.updateFaceboxContent(t);
            showLayout($("#cpage_layout"));
        });
    };

    setup = function (frm) {
        fcom.resetEditorInstance();
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'setup'), data, function (t) {
            reloadList();
            if (t.langId > 0) {
                addLangForm(t.pageId, t.langId, t.cpage_layout);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    addLangForm = function (pageId, langId, cpage_layout, autoFillLangData = 0) {
        fcom.displayProcessing();
        fcom.resetEditorInstance();
        /*    $.facebox(function() { */
        fcom.ajax(fcom.makeUrl('ContentPages', 'langForm', [pageId, langId, cpage_layout, autoFillLangData]), '', function (t) {
            /*    $.facebox(t); */
            fcom.updateFaceboxContent(t);
            fcom.setEditorLayout(langId);
            var frm = $('#facebox form')[0];

            var validator = $(frm).validation({
                errordisplay: 3
            });

            $(frm).submit(function (e) {
                e.preventDefault();
                validator.validate();
                if (!validator.isValid()) return;
                /* if (validator.validate() == false) {
                    return ;
                } */
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'langSetup'), data, function (t) {
                    fcom.resetEditorInstance();
                    reloadList();
                    if (t.langId > 0) {
                        addLangForm(t.pageId, t.langId, t.cpage_layout);
                        return;
                    }
                    $(document).trigger('close.facebox');
                });
            });
        });
        //});
    };

    setupLang = function (frm) {


        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'langSetup'), data, function (t) {
            reloadList();
            if (t.langId > 0) {
                addLangForm(t.pageId, t.langId, t.cpage_layout);
                return;
            }
            $(document).trigger('close.facebox');
        });
    };

    deleteRecord = function (id) {
        if (!confirm(langLbl.confirmDelete)) {
            return;
        }
        data = 'id=' + id;
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'deleteRecord'), data, function (res) {
            reloadList();
        });
    };

    removeBgImage = function (cpageId, langId, cpageLayout) {
        if (!confirm(langLbl.confirmDeleteImage)) {
            return;
        }
        fcom.updateWithAjax(fcom.makeUrl('ContentPages', 'removeBgImage', [cpageId, langId]), '', function (t) {
            addLangForm(cpageId, langId, cpageLayout);
        });
    };

    clearSearch = function () {
        document.frmPagesSearch.reset();
        searchPages(document.frmPagesSearch);
    };

    showLayout = function (element) {
        if (element.val() != '') {
            $('#viewLayout-js').html('Loading...');
            fcom.ajax(fcom.makeUrl('ContentPages', 'cmsLayout', [element.val()]), '', function (t) {
                $('#viewLayout-js').html(t);
                setTimeout(function () {
                    fcom.resetFaceboxHeight();
                }, 100);
            });
        } else {
            $('#viewLayout-js').html('');
        }
    };

    deleteSelected = function () {
        if (!confirm(langLbl.confirmDelete)) {
            return false;
        }
        $("#frmContentPgListing").attr("action", fcom.makeUrl('ContentPages', 'deleteSelected')).submit();
    };

    popupImage = function (inputBtn) {
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('ContentPages', 'imgCropper'), '', function (t) {
                $('#cropperBox-js').html(t);
                $('#cropperBox-js').css("display", "block");
                $("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
                var minWidth = document.frmBlockLang.min_width.value;
                var minHeight = document.frmBlockLang.min_height.value;
                var options = {
                    aspectRatio: 16 / 5,
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
                return cropImage(file, options, 'uploadBgImage', inputBtn);
            });
        }
    };

    uploadBgImage = function (formData) {
        var lang_id = document.frmBlockLang.lang_id.value;
        var cpage_id = document.frmBlockLang.cpage_id.value;
        var cpage_layout = document.frmBlockLang.cpage_layout.value;
        var file_type = document.frmBlockLang.file_type.value;
        formData.append('lang_id', lang_id);
        formData.append('cpage_id', cpage_id);
        formData.append('cpage_layout', cpage_layout);
        formData.append('file_type', file_type);
        $.ajax({
            url: fcom.makeUrl('ContentPages', 'setUpBgImage'),
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
                fcom.displaySuccessMessage(ans.msg);
                /* addLangForm(ans.cpage_id, ans.lang_id, ans.cpage_layout); */
                /* addForm(cpage_id); */
                /* '<img src=""> <a href="javascript:void(0);" onclick="removeBgImage(1,1,1)" class="remove--img"><i class="ion-close-round"></i></a>';
                fcom.makeUrl('Questionnaires', 'generateLink', [questionnaireId]);
                generateUrl('cart', 'cart_summary');
                */
                $(".temp-hide").show();
                var dt = new Date();
                var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
                $(".uploaded--image").html('<img src="' + fcom.makeUrl('image', 'cpageBackgroundImage', [ans.cpage_id, ans.lang_id, 'THUMB'], SITE_ROOT_URL) + '?' + time + '"> <a href="javascript:void(0);" onclick="removeBgImage(' + [ans.cpage_id, ans.lang_id, ans.cpage_layout] + ')" class="remove--img"><i class="ion-close-round"></i></a>');
                fcom.displaySuccessMessage(ans.msg);
                $('#cropperBox-js').css("display", "none");
                $("#mediaForm-js").css("display", "block");
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

})();

(function () {
    displayImageInFacebox = function (str) {
        $.facebox('<img width="800px;" src="' + str + '">');
    }
})();
