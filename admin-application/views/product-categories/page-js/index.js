$(document).ready(function () {
	searchProductCategories();
	getTotalBlock();
});

(function () {
	var currentPage = 1;
	var runningAjaxReq = false;
	var dv = "#listing";

	searchProductCategories = function () {
		fcom.displayProcessing();
		var data = '';
		fcom.ajax(fcom.makeUrl('productCategories', 'search'), data, function (res) {
			$(dv).html(res);
			$.systemMessage.close();
		});
	};

	getTotalBlock = function () {
		var data = '';
		fcom.ajax(fcom.makeUrl('productCategories', 'getTotalBlock'), data, function (res) {
			$("#total-block").html(res);
		});
	};

	categoryForm = function (prodCatId) {
		var data = '';
		fcom.ajax(fcom.makeUrl('productCategories', 'form', [prodCatId]), data, function (res) {
			$(dv).html(res);
			if (prodCatId > 0) {
				categoryImages(prodCatId, 'icon', 1);
				categoryImages(prodCatId, 'banner', 1);
			}
		});
	}

	setupCategory = function () {
		var frm = $('#frmProdCategory');
		var validator = $(frm).validation({ errordisplay: 3 });
		if (validator.validate() == false) {
			return false;
		}
		if (!$(frm).validate()) {
			return false;
		}
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'setup'), data, function (t) {
			if (t.status == 1) {
				searchProductCategories();
				getTotalBlock();
				setTimeout(function(){
					goToCategory(frm.find('[name="prodcat_id"]').val());
				}, 1000);
			}
		});
	};

	discardForm = function () {
		searchProductCategories();
		getTotalBlock();
	}

	deleteRecord = function (id) {
		if (!confirm(langLbl.confirmDelete)) { return; }
		data = 'id=' + id;
		fcom.ajax(fcom.makeUrl('productCategories', 'deleteRecord'), data, function (res) {
			var ans = $.parseJSON(res);
			if (ans.status == 1) {
				fcom.displaySuccessMessage(ans.msg);
				searchProductCategories();
				getTotalBlock();
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
		});
	};

	toggleStatus = function (e, obj, canEdit, status) {
		if (canEdit == 0) {
			e.preventDefault();
			return;
		}

		var childCount = $(obj).data('childcount');
		if (0 == status && 0 < childCount && !confirm(langLbl.disableChildCategories)) {
			e.preventDefault();
			return;
		}

		var hasParent = $(obj).data('hasparent');
		if (1 == status && 1 == hasParent && !confirm(langLbl.enableParentCategories)) {
			e.preventDefault();
			return;
		}

		var prodCatId = parseInt(obj.value);
		if (prodCatId < 1) {
			fcom.displayErrorMessage(langLbl.invalidRequest);
			return false;
		}
		data = 'prodCatId=' + prodCatId + '&prodcat_active=' + status;
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('productCategories', 'changeStatus'), data, function (res) {
			var ans = $.parseJSON(res);
			if (ans.status == 1) {
				$(obj).toggleClass("active");
				fcom.displaySuccessMessage(ans.msg);
				searchProductCategories();
				getTotalBlock();
				setTimeout(function(){
					goToCategory(prodCatId);
				}, 1000);
			} else {
				fcom.displayErrorMessage(ans.msg);
			}
		});
	};

	goToProduct = function (prodCatId) {
		window.location.href = fcom.makeUrl('Products', 'form', [0, prodCatId]);
	};

	displaySubCategories = function (obj, catId = 0, data, callable = '') {
		$(obj).removeClass('clickable');
		if (catId > 0) {
			var prodCatId = catId;
		} else {
			var prodCatId = $(obj).parent().parent().parent().attr('id');
		}

		if ($("#" + prodCatId + ' ul.append-ul').length) {
			$("#" + prodCatId + ' ul:first').show();
			if (catId == 0) {
				togglePlusMinus(prodCatId);
			}
			if (catId > 0) {
				updateCatOrder(data);
			}
			return false;
		}

		fcom.ajax(fcom.makeUrl('productCategories', 'getSubCategories'), 'prodCatId=' + prodCatId, function (res) {
			if ($("#" + prodCatId).children('ul.append-ul').length) {
				$("#" + prodCatId).children('ul.append-ul').append(res);
			} else {
				$("#" + prodCatId).append('<ul class="append-ul">' + res + '</ul>');
			}
			if (catId == 0) {
				togglePlusMinus(prodCatId);
			}
			if (catId > 0) {
				updateCatOrder(data);
			}

			if ('' != callable) {
				window[callable]();
			}
		});
	}

	togglePlusMinus = function (prodCatId) {
		$("#" + prodCatId).children('div').children('.sortableListsOpener').remove();
		if ($("#" + prodCatId).hasClass('sortableListsClosed')) {
			$("#" + prodCatId).removeClass('sortableListsClosed').addClass('sortableListsOpen');
			$("#" + prodCatId).children('div').append('<span class="sortableListsOpener" ><i class="fa fa-minus clickable sort-icon cat'+prodCatId+'-js" onClick="hideItems(this)"></i></span>');
		} else {
			$("#" + prodCatId).removeClass('sortableListsOpen').addClass('sortableListsClosed');
			$("#" + prodCatId).children('div').append('<span class="sortableListsOpener" ><i class="fa fa-plus c3 clickable sort-icon cat'+prodCatId+'-js" onClick="displaySubCategories(this)"></i></span>');
		}

		$("#" + prodCatId + ' > ul:first > li:has(> ul)').children('div').children('.sortableListsOpener').remove();
		$("#" + prodCatId + ' > ul:first > li:has(> ul)').removeClass('sortableListsOpen').addClass('sortableListsClosed');
		$("#" + prodCatId + ' > ul:first > li:has(> ul)').children('div').append('<span class="sortableListsOpener" ><i class="fa fa-plus c3 clickable sort-icon" onClick="displaySubCategories(this)"></i></span>');
	}

	hideItems = function (obj) {
		var prodCatId = $(obj).parent().parent().parent().attr('id');
		$("#" + prodCatId + ' ul').hide();
		$("#" + prodCatId).removeClass('sortableListsOpen').addClass('sortableListsClosed');
		var icon = $("#" + prodCatId).children('div').children('.sortableListsOpener').remove();
		$("#" + prodCatId).children('div').append('<span class="sortableListsOpener" ><i class="fa fa-plus c3 clickable sort-icon" onClick="displaySubCategories(this)"></i></span>');
	}

	updateCatOrder = function (data) {
		fcom.updateWithAjax(fcom.makeUrl('productCategories', 'updateOrder'), data, function (res) {
			searchProductCategories();
			$("#js-cat-section").removeClass('overlay-blur');
			setTimeout(function(){
				data = queryStringToJSON(data);
				goToCategory(data.catId);
			}, 1000);
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

	translateData = function (item) {
		var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
		var defaultLang = $(item).attr('defaultLang');
		var catName = $("input[name='prodcat_name[" + defaultLang + "]']").val();
		var toLangId = $(item).attr('language');
		var alreadyOpen = $('#collapse_' + toLangId).hasClass('active');
		if (autoTranslate == 0 || catName == "" || alreadyOpen == true) {
			return false;
		}
		var data = "catName=" + catName + "&toLangId=" + toLangId;
		fcom.updateWithAjax(fcom.makeUrl('ProductCategories', 'translatedCategoryData'), data, function (t) {
			if (t.status == 1) {
				$("input[name='prodcat_name[" + toLangId + "]']").val(t.prodCatName);
			}
		});
	}

	bannerPopupImage = function (inputBtn) {
		if (inputBtn.files && inputBtn.files[0]) {
			fcom.ajax(fcom.makeUrl('ProductCategories', 'imgCropper'), '', function (t) {
				$.facebox(t, 'faceboxWidth');
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
				$.facebox(t, 'faceboxWidth');
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
		var prodcatId = $("[name='prodcat_id']").val();
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
					fcom.displaySuccessMessage(ans.msg);
					categoryImages(prodcatId, imageType, slideScreen, langId);
				} else {
					fcom.displayErrorMessage(ans.msg);
				}
				$(document).trigger('close.facebox');
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}

	var parentIds = [];
	var i = 0;
	goToCategory = function (catId = 0) {
		if (0 < parentIds.length) {
			parentIds = i >= parentIds.length ? [] : parentIds;

			if (i < parentIds.length) {
				const element = $('.cat' + parentIds[i] + '-js')[0];
				i = i+1;
				displaySubCategories(element, 0, '', 'goToCategory');
			}
			return;
		} else {
			i = 0;
			fcom.ajax(fcom.makeUrl('ProductCategories', 'getParentIds', [catId]), '', function (t) {
				var ans = $.parseJSON(t);	
				if (0 < ans.status) {					
					parentIds = ans.data;					
					if (i < parentIds.length) {						
						const element = $('.cat' + parentIds[i] + '-js')[0];
						i = i+1;
						displaySubCategories(element, 0, '', 'goToCategory');
					}
				}
			});
		}
	}
})();


/*
$(document).on('click','.catFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var formName = $(node).attr('data-frm');
	var slide_screen = 0;
	if(formName == 'catIcon'){
		var lang_id = $("[name='icon_lang_id']").val();
		var prodcat_id = $("[name='prodcat_id']").val();
		var imageType = 'icon';
		var afile_id = $("#icon-image-listing li").attr('id');
	}else if(formName == 'catBanner'){
		var lang_id = $("[name='banner_lang_id']").val();
		var prodcat_id = $("[name='prodcat_id']").val();
		var slide_screen = $("[name='slide_screen']").val();
		var imageType = 'banner';
		var afile_id = $("#banner-image-listing li").attr('id');
	}

	var fileType = $(node).attr('data-file_type');


	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="file_type" value="' + fileType + '">');
	frm = frm.concat('<input type="hidden" name="prodcat_id" value="' + prodcat_id + '">');
	frm = frm.concat('<input type="hidden" name="lang_id" value="' + lang_id + '">');
	frm = frm.concat('<input type="hidden" name="slide_screen" value="' + slide_screen + '">');
	frm = frm.concat('<input type="hidden" name="afile_id" value="' + afile_id + '">');
	frm = frm.concat('</form>');
	$('body').prepend(frm);
	$('#form-upload input[name=\'file\']').trigger('click');
	if (typeof timer != 'undefined') {
		clearInterval(timer);
	}
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);
			$val = $(node).val();
			$.ajax({
				url: fcom.makeUrl('ProductCategories', 'setUpCatImages'),
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$(node).val('Loading');
				},
				complete: function() {
					$(node).val($val);
					fcom.resetFaceboxHeight();

				},
				success: function(ans) {
						if(ans.status == 1){
							fcom.displaySuccessMessage(ans.msg);
							$('#form-upload').remove();
							categoryImages(prodcat_id,imageType,slide_screen,lang_id);
						}else{
							fcom.displayErrorMessage(ans.msg);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
		}
	}, 500);
});*/

$(document).on('change', '.icon-language-js', function () {
	var lang_id = $(this).val();
	var prodcat_id = $("input[name='prodcat_id']").val();
	var imageId = $("[name='cat_icon_image_id[" + lang_id + "]']").val();
	if (prodcat_id == 0) {
		if (imageId > 0) {
			categoryImages(prodcat_id, 'icon', 0, lang_id);
		} else {
			$("#icon-image-listing").html('');
		}
	} else {
		categoryImages(prodcat_id, 'icon', 0, lang_id);
	}

});

$(document).on('change', '.banner-language-js', function () {
	var lang_id = $(this).val();
	var prodcat_id = $("input[name='prodcat_id']").val();
	var slide_screen = $("input[name='slide_screen']").val();
	var imageId = $("[name='cat_banner_image_id[" + lang_id + "_" + slide_screen + "]']").val();
	if (prodcat_id == 0) {
		if (imageId > 0) {
			categoryImages(prodcat_id, 'banner', slide_screen, lang_id);
		} else {
			$("#banner-image-listing").html('');
		}
	} else {
		categoryImages(prodcat_id, 'banner', slide_screen, lang_id);
	}

});

$(document).on('change', '.prefDimensions-js', function () {
	var slide_screen = $(this).val();
	var prodcat_id = $("input[name='prodcat_id']").val();
	var lang_id = $(".banner-language-js").val();
	var imageId = $("[name='cat_banner_image_id[" + lang_id + "_" + slide_screen + "]']").val();
	if (prodcat_id == 0) {
		if (imageId > 0) {
			categoryImages(prodcat_id, 'banner', slide_screen, lang_id);
		} else {
			$("#banner-image-listing").html('');
		}
	} else {
		categoryImages(prodcat_id, 'banner', slide_screen, lang_id);
	}
});
