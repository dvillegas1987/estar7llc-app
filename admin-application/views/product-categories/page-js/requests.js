$(document).ready(function(){
	searchProductCategories(document.frmSearch);

	$('input[name=\'user_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('Users', 'autoCompleteJson'),
				data: {keyword: request['term'], fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'] +'(' + item['username'] + ')', value: item['name'] +'(' + item['username'] + ')', id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ul) {
			$("input[name='user_id']").val( ul.item.id );
		}
	});
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmCategorySearchPaging;
		$(frm.page).val(page);
		searchProductCategories(frm);
	}

	reloadList = function() {
		var frm = document.frmCategorySearchPaging;
		searchProductCategories(frm);
	}

	searchProductCategories = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('ProductCategories', 'searchRequests'),data,function(res){
			$("#listing").html(res);
		});
	};

	clearSearch = function(){
		document.frmSearch.reset();
		searchProductCategories(document.frmSearch);
	};
    
    setupCategory = function() {
        var frm = $('#frmProdCategory');
        var validator = $(frm).validation({errordisplay: 3});
        if (validator.validate() == false) {
            return false;
        }
        if (!$(frm).validate()) {
            return false;
        }
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'setup', [1]), data, function(t) {
            if(t.status == 1){
                $(document).trigger('close.facebox');
                reloadList();
            }
        });
	};
    
    editProdCatRequestForm = function(id){
		$.facebox(function() {
            prodCatRequestForm(id);
		});
    }

    prodCatRequestForm = function(id) {
		
		fcom.displayProcessing();
		var frm = document.frmBrandSearchPaging;
		fcom.ajax(fcom.makeUrl('ProductCategories', 'form', [id, 1]), '', function(t) {
			
			fcom.updateFaceboxContent(t);
			if (id > 0) {
				categoryImages(id, 'icon');
				categoryImages(id, 'banner', 1);
			}
			
		});
	};
	
	
	bannerPopupImage = function (inputBtn) {
		if (inputBtn.files && inputBtn.files[0]) {
			fcom.ajax(fcom.makeUrl('ProductCategories', 'imgCropper'), '', function (t) {
				$('#cropperBox-js').html(t);
				$("#mediaForm-js").css("display", "none");	
				$('.fbminwidth').animate({
					scrollTop: $("#cropperBox-js").offset().top
				}, 2000);
				/* $.facebox(t, 'faceboxWidth'); */
				var file = inputBtn.files[0];
				var minWidth = document.frmProdCategory.banner_min_width.value;
				var minHeight = document.frmProdCategory.banner_min_height.value;
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
				return cropImage(file, options, 'uploadCatImages', inputBtn);
			});
		}
	};

	iconPopupImage = function (inputBtn) {
		if (inputBtn.files && inputBtn.files[0]) {
			fcom.ajax(fcom.makeUrl('Shops', 'imgCropper'), '', function (t) {
				$('#cropperBox-js').html(t);
				$("#mediaForm-js").css("display", "none");
				$('.fbminwidth').animate({
					scrollTop: $("#cropperBox-js").offset().top
				}, 2000);	
				var file = inputBtn.files[0];
				var minWidth = document.frmProdCategory.logo_min_width.value;
				var minHeight = document.frmProdCategory.logo_min_height.value;
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
				return cropImage(file, options, 'uploadCatImages', inputBtn);
			});
		}
	};

	uploadCatImages = function (formData) {
		var frmName = formData.get("frmName");
		var slideScreen = 0;
		var frmProdCategory = $('#frmProdCategory')[0]; 
		var frmProdCategoryData = new FormData(frmProdCategory);
		var prodcatId = frmProdCategoryData.get('prodcat_id');
		
		if (frmName == 'frmCategoryIcon') {
			var afileId = $("#icon-image-listing li").attr('id');
			var langId = $("[name='icon_lang_id']").val();
			var fileType = $("[name='icon_file_type']").val();
			var imageType = 'icon';
		} else {
			var afileId = $("#banner-image-listing li").attr('id');
			var langId = $("[name='banner_lang_id']").val();
			var fileType = $("[name='banner_file_type']").val();
			slideScreen = $("[name='slide_screen']").val();
			var imageType = 'banner';
		}
		formData.append('prodcat_id', prodcatId);
		formData.append('slide_screen', slideScreen);
		formData.append('lang_id', langId);
		formData.append('file_type', fileType);
		formData.append('afile_id', afileId);
		$.ajax({
			url: fcom.makeUrl('ProductCategories', 'setUpCatImages'),
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
					editProdCatRequestForm(prodcatId);	
					fcom.displaySuccessMessage(ans.msg);
				} else {
					fcom.displayErrorMessage(ans.msg);
				}
							
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
	
	categoryImages = function (prodCatId, imageType, slide_screen, lang_id) {
		fcom.ajax(fcom.makeUrl('ProductCategories', 'images', [prodCatId, imageType, lang_id, slide_screen]), '', function (t) {
			if (imageType == 'icon') {
				$('#icon-image-listing').html(t);
				var prodCatId = $("[name='prodcat_id']").val();
				if (prodCatId == 0) {
					var iconImageId = $("#icon-image-listing li").attr('id');
					var selectedLangId = $(".icon-language-js").val();
					$("[name='cat_icon_image_id[" + selectedLangId + "]']").val(iconImageId);
				}
			} else if (imageType == 'banner') {
				$('#banner-image-listing').html(t);
				var bannerImageId = $("#banner-image-listing li").attr('id');
				var selectedLangId = $(".banner-language-js").val();
				var screen = $(".prefDimensions-js").val();
				$("[name='cat_banner_image_id[" + selectedLangId + "_" + screen + "]']").val(bannerImageId);
			}
		});
	};

	deleteImage = function (fileId, prodcatId, imageType, langId, slide_screen) {
		if (!confirm(langLbl.confirmDeleteImage)) { return; }
		fcom.updateWithAjax(fcom.makeUrl('productCategories', 'removeImage', [fileId, prodcatId, imageType, langId, slide_screen]), '', function (t) {
			//categoryImages( prodcatId, imageType, slide_screen, langId );
			if (imageType == 'icon') {
				$("#icon-image-listing").html('');
				$("[name='cat_icon_image_id[" + langId + "]']").val('');
			} else if (imageType == 'banner') {
				$("#banner-image-listing").html('');
				$("[name='cat_banner_image_id[" + langId + "_" + slide_screen + "]']").val('');
			}
		});
	};
	
	
	
	
})();
