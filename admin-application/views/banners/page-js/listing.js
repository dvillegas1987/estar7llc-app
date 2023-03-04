$(document).ready(function(){
	bannerListing(document.frmListingSearch);
});
$(document).on('change','.language-js',function(){
	var langId = $(this).val();
	var bannerId = $("input[name='banner_id']").val();
	var blocationId = $("input[name='blocation_id']").val();
	var screen = $(".display-js").val();
	images(blocationId,bannerId,langId,screen);
});
$(document).on('change','.display-js',function(){
	var screen = $(this).val();
	var bannerId = $("input[name='banner_id']").val();
	var blocationId = $("input[name='blocation_id']").val();
	var langId = $(".language-js").val();
	images(blocationId,bannerId,langId,screen);
});
(function() {
	var dv = '#listing';

	goToSearchPage = function(page) {
		if(typeof page == undefined || page == null){
			page =1;
		}
		var frm = document.frmListingSearchPaging;
		$(frm.page).val(page);
		bannerListing(frm);
	};
	redirectBack=function(redirecrt){
		var url=	SITE_ROOT_URL +''+redirecrt;
		window.location=url;

	}
	reloadList = function() {
		var frm = document.frmListingSearchPaging;
		bannerListing(frm);
	};

	bannerListing = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Banners','listingSearch'),data,function(res){
			$(dv).html(res);
		});
	};
	addBannerForm = function(blocationId,bannerId){
		$.facebox(function() {
		bannerForm(blocationId,bannerId);
		});
	};

	bannerForm = function(blocationId,bannerId){
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('Banners', 'bannerForm', [blocationId,bannerId]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
	};

	setupBanners = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Banners', 'setupBanner'), data, function(t) {
			reloadList();
			if (t.langId > 0 ) {
				bannerLangForm(t.blocation_id,t.banner_id,t.langId);
				return ;
			}
			if(t.openMediaForm)
			{
				mediaForm(t.blocation_id,t.banner_id);
				return;
			}

			$(document).trigger('close.facebox');
		});
	};

	bannerLangForm = function(blocationId,bannerId,langId, autoFillLangData = 0){
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('Banners', 'bannerLangForm', [blocationId,bannerId,langId, autoFillLangData]), '', function(t) {
				fcom.updateFaceboxContent(t);
			});
	};

	langSetup = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Banners', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				bannerLangForm(t.blocationId,t.bannerId, t.langId);
				return ;
			}
			if(t.openMediaForm)
			{
				mediaForm(t.blocationId,t.bannerId);
				return;
			}
			$(document).trigger('close.facebox');
		});
	};

	mediaForm = function(blocationId,bannerId){
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('Banners', 'mediaForm', [blocationId,bannerId]), '', function(t) {
				images(blocationId,bannerId,0,1);
			fcom.updateFaceboxContent(t);
			});
	};

	images = function(blocationId,bannerId=0,langId=0,screen=0){
		fcom.ajax(fcom.makeUrl('Banners', 'images', [blocationId,bannerId,langId,screen]), '', function(t) {
			$('#image-listing').html(t);
			fcom.resetFaceboxHeight();
		});
	};

	removeBanner = function(blocationId,bannerId,langId,screen){
		if( !confirm(langLbl.confirmDeleteImage) ){ return; }
		fcom.updateWithAjax(fcom.makeUrl('Banners', 'removeBanner',[bannerId,langId,screen]), '', function(t) {
			images(blocationId,bannerId,langId,screen);
			reloadList();
		});
	};

	deleteBanner = function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.ajax(fcom.makeUrl('Banners','deleteRecord'),data,function(res){
			reloadList();
		});
	};

	toggleStatus = function( e,obj,canEdit ){
		if(canEdit == 0){
			e.preventDefault();
			return;
		}
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var bannerId = parseInt(obj.value);
		if( bannerId < 1 ){
			$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data = 'bannerId='+bannerId;
		fcom.ajax(fcom.makeUrl('Banners','changeStatus'),data,function(res){
			var ans =$.parseJSON(res);
			if(ans.status == 1){
				$.mbsmessage(ans.msg,true,'alert--success');
				$(obj).toggleClass("active");
			}else{
				$.mbsmessage(ans.msg,true,'alert--danger');
			}
		});
	};

	popupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
	        fcom.ajax(fcom.makeUrl('Shops', 'imgCropper'), '', function(t) {
				$('#cropperBox-js').html(t);
				$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
	            var minWidth = document.frmBannerMedia.banner_min_width.value;
	            var minHeight = document.frmBannerMedia.banner_min_height.value;
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
		var bannerId = document.frmBannerMedia.banner_id.value;
		var blocationId = document.frmBannerMedia.blocation_id.value;
		var langId = document.frmBannerMedia.lang_id.value;
		var bannerScreen = document.frmBannerMedia.banner_screen.value;
		formData.append('banner_id', bannerId);
		formData.append('blocation_id', blocationId);
        formData.append('banner_screen', bannerScreen);
        formData.append('lang_id', langId);
        $.ajax({
            url: fcom.makeUrl('Banners', 'upload',[bannerId]),
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
				if(ans.status==1)
				{
					fcom.displaySuccessMessage(ans.msg);
					reloadList();
					$('#form-upload').remove();
					mediaForm(blocationId, bannerId);
					images(blocationId, bannerId, langId, bannerScreen);
				} else {
					fcom.displayErrorMessage(ans.msg);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
        });
	}

})();
