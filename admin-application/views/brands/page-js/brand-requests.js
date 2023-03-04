$(document).ready(function(){
	searchProductBrands(document.frmSearch);

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
$(document).on('change','.logo-language-js',function(){
	var lang_id = $(this).val();
	var brand_id = $(this).closest("form").find('input[name="brand_id"]').val();
	brandImages(brand_id, 'logo', 0, lang_id);
});
$(document).on('change','.image-language-js',function(){
	var lang_id = $(this).val();
	var brand_id = $(this).closest("form").find('input[name="brand_id"]').val();
	var slide_screen = $(".prefDimensions-js").val();
	brandImages(brand_id, 'image', slide_screen, lang_id);
});
$(document).on('change','.prefDimensions-js',function(){
	var slide_screen = $(this).val();
	var brand_id = $(this).closest("form").find('input[name="brand_id"]').val();
	var lang_id = $(".image-language-js").val();
	brandImages(brand_id, 'image', slide_screen, lang_id);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	goToSearchPage = function(page) {
		if(typeof page==undefined || page == null){
			page =1;
		}
		var frm = document.frmBrandSearchPaging;
		$(frm.page).val(page);
		searchProductBrands(frm);
	}

	reloadList = function() {
		var frm = document.frmBrandSearchPaging;
		searchProductBrands(frm);
	}



	setupBrand = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('brands', 'setupRequest'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				brandRequestLangForm(t.brandId, t.langId);
				return ;
			}
			if (t.openMediaForm)
			{
				brandRequestMediaForm(t.brandId);
				return;
			}
			/* $(document).trigger('close.facebox'); */
		});
	};

	brandRequestLangForm = function(brandId, langId, autoFillLangData = 0) {
	fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('brands', 'requestLangForm', [brandId, langId, autoFillLangData]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
			};

	setupBrandLang=function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('brands', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				brandRequestLangForm(t.brandId, t.langId);
				return ;
			}
			if (t.openMediaForm)
			{
				brandRequestMediaForm(t.brandId);
				return;
			}
			/* $(document).trigger('close.facebox'); */
		});
	};

	searchProductBrands = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$("#listing").html('Loading....');
		fcom.ajax(fcom.makeUrl('brands','searchBrandRequests'),data,function(res){
			$("#listing").html(res);
		});
	};

    brandRequestMediaForm = function(brandId){
		fcom.displayProcessing();
        fcom.ajax(fcom.makeUrl('Brands', 'requestMedia', [brandId]), '', function(t) {
            brandImages(brandId, 'logo', 1);
            brandImages(brandId, 'image', 1);
            fcom.updateFaceboxContent(t);
        });
	};

	brandImages = function(brandId, fileType, slide_screen, langId){
		fcom.ajax(fcom.makeUrl('Brands', 'images', [brandId, fileType, langId, slide_screen]), '', function(t) {
			if(fileType=='logo') {
				$('#logo-listing').html(t);
			} else {
				$('#image-listing').html(t);
			}
			fcom.resetFaceboxHeight();
		});
	};

	deleteRecord = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('brands','deleteRecord'),data,function(res){
			reloadList();
		});
	};

	clearSearch = function(){
        document.frmSearch.reset();
        $("input[name='user_id']").val("");
		searchProductBrands(document.frmSearch);
	};

	deleteMedia = function( brandId, fileType, afileId ){
		if(!confirm(langLbl.confirmDelete)){return;}
		fcom.updateWithAjax(fcom.makeUrl('brands', 'removeBrandMedia',[brandId, fileType, afileId]), '', function(t) {
			brandImages(brandId,fileType,slide_screen,langId);
			reloadList();
		});
	};

	addBrandRequestForm= function(id){
		$.facebox(function() {
            brandRequestForm(id);
		});
    }
    
	brandRequestForm = function(id) {
		fcom.displayProcessing();
		var frm = document.frmBrandSearchPaging;
			fcom.ajax(fcom.makeUrl('brands', 'requestForm', [id]), '', function(t) {
				fcom.updateFaceboxContent(t);
		});
	};

	showHideCommentBox = function(val){
		if(val == 2){
			$('#div_comments_box').removeClass('hide');
		}else{
			$('#div_comments_box').addClass('hide');
		}
	};

	bannerPopupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
	        fcom.ajax(fcom.makeUrl('Brands', 'imgCropper'), '', function(t) {
				$('#cropperBox-js').html(t);
				$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
	            var minWidth = document.frmBrandImage.banner_min_width.value;
	            var minHeight = document.frmBrandImage.banner_min_height.value;
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
    	  		return cropImage(file, options, 'uploadBrandImages', inputBtn);
	    	});
		}
	};

    logoPopupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
	        fcom.ajax(fcom.makeUrl('Brands', 'imgCropper'), '', function(t) {
				$('#cropperBox-js').html(t);
				$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
	            var minWidth = document.frmBrandLogo.logo_min_width.value;
	            var minHeight = document.frmBrandLogo.logo_min_height.value;
				if(minWidth == minHeight){
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
    	  		return cropImage(file, options, 'uploadBrandImages', inputBtn);
	    	});
		}
	};

	uploadBrandImages = function(formData){
        var frmName = formData.get("frmName");
        if ('frmBrandLogo' == frmName) {
			var brandId = document.frmBrandLogo.brand_id.value;
            var langId = document.frmBrandLogo.lang_id.value;
            var fileType = document.frmBrandLogo.file_type.value;
            var imageType = 'logo';
        } else {
			var brandId = document.frmBrandImage.brand_id.value;
            var langId = document.frmBrandImage.lang_id.value;
            var slideScreen = document.frmBrandImage.slide_screen.value;
            var fileType = document.frmBrandImage.file_type.value;
            var imageType = 'banner';
        }

		formData.append('brand_id', brandId);
        formData.append('slide_screen', slideScreen);
        formData.append('lang_id', langId);
        formData.append('file_type', fileType);
        $.ajax({
            url: fcom.makeUrl('Brands', 'uploadMedia'),
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
				if( ans.status == true ){
					$('#input-field').removeClass('text-danger');
					$('#input-field').addClass('text-success');
					$('#form-upload').remove();
					brandRequestMediaForm(ans.brandId);
					brandImages(ans.brandId,imageType,langId);
				}else{
					$('#input-field').removeClass('text-success');
					$('#input-field').addClass('text-danger');
				}
				reloadList();
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
        });
	}

})();
