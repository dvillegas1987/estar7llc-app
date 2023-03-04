$(document).ready(function(){
	searchSlides(document.frmSlideSearch);
});
$(document).on('change','.language-js',function(){
	var lang_id = $(this).val();
	var slide_id = $("input[name='slide_id']").val();
	var slide_screen = $(".prefDimensions-js").val();
	images(slide_id,slide_screen,lang_id);
});
$(document).on('change','.prefDimensions-js',function(){
	var slide_screen = $(this).val();
	var slide_id = $("input[name='slide_id']").val();
	var lang_id = $(".language-js").val();
	images(slide_id,slide_screen,lang_id);
});
(function() {
	var currentPage = 1;
	var runningAjaxReq = false;

	reloadList = function() {
		var frm = document.frmSlideSearch;
		searchSlides(frm);
	}

	searchSlides = function(form){
		var dv = '#listing';
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html('Loading....');
		fcom.ajax(fcom.makeUrl('Slides','search'),data,function(res){
			$(dv).html(res);
		});
	};
	addSlideForm = function(id) {
		$.facebox(function() { slideForm(id)
		});
	};


	slideForm = function(id) {
		fcom.displayProcessing();
			fcom.ajax(fcom.makeUrl('Slides', 'form', [id]), '', function(t) {
				//$.facebox(t,'faceboxWidth');
				fcom.updateFaceboxContent(t);
			});
		};

	setup = function(frm) {
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Slides', 'setup'), data, function(t) {
			reloadList();
			if ( t.langId > 0 ) {
				slideLangForm(t.slideId, t.langId);
				return ;
			}
			if(t.openMediaForm){
				slideMediaForm(t.slideId);
				return;
			}
			$(document).trigger('close.facebox');
		});
	};

	slideLangForm = function( slideId, langId, autoFillLangData = 0){
		fcom.displayProcessing();
		//$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Slides', 'langForm', [slideId, langId, autoFillLangData]), '', function(t) {
				//$.facebox(t);
				fcom.updateFaceboxContent(t);
			});
		//});
	};

	setupLang=function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Slides', 'langSetup'), data, function(t) {
			reloadList();
			if ( t.langId > 0 ) {
				slideLangForm(t.slideId, t.langId);
				return ;
			}
			if(t.openMediaForm){
				slideMediaForm(t.slideId);
				return;
			}
			$(document).trigger('close.facebox');
		});
	};

	slideMediaForm = function(slide_id){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('Slides','mediaForm',[slide_id]),'',function(t){
			images(slide_id,1);
			fcom.updateFaceboxContent(t);
		});
	};

	images = function(slide_id,slide_screen,lang_id){
		fcom.ajax(fcom.makeUrl('Slides', 'images', [slide_id,slide_screen,lang_id]), SITE_ROOT_URL  , function(t) {
			$('#image-listing').html(t);
			fcom.resetFaceboxHeight();
		});
	};

	deleteRecord=function(id){
		if(!confirm(langLbl.confirmDelete)){return;}
		data='id='+id;
		fcom.updateWithAjax(fcom.makeUrl('Slides','deleteRecord'),data,function(res){
			reloadList();
		});
	};

	deleteImage = function( slide_id, lang_id, screen ){
		if( !confirm(langLbl.confirmDeleteImage) ){ return; }
		fcom.updateWithAjax(fcom.makeUrl('Slides', 'removeImage',[slide_id, lang_id, screen]), '', function(t) {
			images(slide_id,screen,lang_id);
		});
	};

	/* clearSearch  = function(){
		document.frmSlideSearch.reset();
		searchSlides(document.frmSlideSearch);
	}; */

	toggleStatus = function( e,obj,canEdit ){
		if(canEdit == 0){
			e.preventDefault();
			return;
		}
		if(!confirm(langLbl.confirmUpdateStatus)){
			e.preventDefault();
			return;
		}
		var slideId = parseInt(obj.value);
		if( slideId < 1 ){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			//$.mbsmessage(langLbl.invalidRequest,true,'alert--danger');
			return false;
		}
		data = 'slideId='+slideId;
		fcom.ajax(fcom.makeUrl('Slides','changeStatus'),data,function(res){
			var ans =$.parseJSON(res);
			if(ans.status == 1){
				fcom.displaySuccessMessage(ans.msg);
				//$.mbsmessage(ans.msg,true,'alert--success');
				$(obj).toggleClass("active");
			}else{
				fcom.displayErrorMessage(ans.msg);
				//$.mbsmessage(ans.msg,true,'alert--danger');
			}
		});
	};

    deleteSelected = function(){
        if(!confirm(langLbl.confirmDelete)){
            return false;
        }
        $("#frmSlidesListing").attr("action",fcom.makeUrl('Slides','deleteSelected')).submit();
    };

	popupImage = function(inputBtn){
		if (inputBtn.files && inputBtn.files[0]) {
	        fcom.ajax(fcom.makeUrl('Shops', 'imgCropper'), '', function(t) {
				$('#cropperBox-js').html(t);
				$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
	            var minWidth = document.frmSlideMedia.banner_min_width.value;
	            var minHeight = document.frmSlideMedia.banner_min_height.value;
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
        var frmName = formData.get("frmName");
		var slideId = document.frmSlideMedia.slide_id.value;
		var langId = document.frmSlideMedia.lang_id.value;
		var slideScreen = document.frmSlideMedia.slide_screen.value;
		formData.append('slide_id', slideId);
        formData.append('slide_screen', slideScreen);
        formData.append('lang_id', langId);
        $.ajax({
            url: fcom.makeUrl('Slides', 'setUpImage',[slideId]),
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
				reloadList();
				$('#form-upload').remove();
				slideMediaForm(ans.slideId);
				images(ans.slideId,slideScreen,langId);
				fcom.displaySuccessMessage(ans.msg);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
        });
	}

})();
