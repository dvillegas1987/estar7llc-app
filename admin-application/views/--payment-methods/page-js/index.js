$(document).ready(function() {
    searchGateway(document.frmGatewaySearch);
});

(function() {
	var runningAjaxReq = false;
	var dv = '#pMethodListing';

	reloadList = function() {
		var frm = document.frmGatewaySearch;
		searchGateway(frm);
	};

	searchGateway = function(form){
		var data = '';
		if (form) {
			data = fcom.frmData(form);
		}
		$(dv).html(fcom.getLoader());

		fcom.ajax(fcom.makeUrl('PaymentMethods','search'),data,function(res){
			$(dv).html(res);
		});
	};

	editGatewayForm = function(pMethodId){
		$.facebox(function() {
			gatewayForm(pMethodId);
		});
	};

	gatewayForm = function(pMethodId){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('PaymentMethods', 'form', [pMethodId]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	}


	setupGateway = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('PaymentMethods', 'setup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				editGatewayLangForm(t.pMethodId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	}

	editGatewayLangForm = function(pMethodId,langId, autoFillLangData = 0){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl('PaymentMethods', 'langForm', [pMethodId,langId, autoFillLangData]), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupLangGateway = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('PaymentMethods', 'langSetup'), data, function(t) {
			reloadList();
			if (t.langId>0) {
				editGatewayLangForm(t.pMethodId, t.langId);
				return ;
			}
			$(document).trigger('close.facebox');
		});
	};

	settingsForm = function (code){
		$.facebox(function() {
			editSettingForm(code);
		});
	};

	editSettingForm = function (code){
		fcom.displayProcessing();
		fcom.ajax(fcom.makeUrl(code+'-settings'), '', function(t) {
			fcom.updateFaceboxContent(t);
		});
	};

	setupPaymentSettings = function (frm,code){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(code+'-settings', 'setup'), data, function(t) {
			$(document).trigger('close.facebox');
		});
	};

	toggleStatus = function( obj ){
		if( !confirm(langLbl.confirmUpdateStatus) ){ return; }
		var pmethodId = parseInt(obj.id);
		if( pmethodId < 1 ){
			fcom.displayErrorMessage(langLbl.invalidRequest);
			return false;
		}
		data = 'pmethodId='+pmethodId;
		fcom.ajax(fcom.makeUrl('PaymentMethods','changeStatus'),data,function(res){
			var ans =$.parseJSON(res);
			if(ans.status == 1){
				fcom.displaySuccessMessage(ans.msg);
				$(obj).toggleClass("active");
				setTimeout(function(){ reloadList(); }, 1000);
			}else{
				fcom.displayErrorMessage(ans.msg);
			}
		});
    };

    popupImage = function(inputBtn){
        if (inputBtn.files && inputBtn.files[0]) {
            fcom.ajax(fcom.makeUrl('PaymentMethods', 'imgCropper'), '', function(t) {
    			$('#cropperBox-js').html(t);
    			$("#mediaForm-js").css("display", "none");
                var file = inputBtn.files[0];
                var minWidth = document.frmGateway.min_width.value;
                var minHeight = document.frmGateway.min_height.value;
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
                return cropImage(file, options, 'uploadImages', inputBtn);
        	});
        }
	};

	uploadImages = function(formData){
        var plugin_id = document.frmGateway.plugin_id.value;
        var ratio_type = $('input[name="ratio_type"]:checked').val(); 
        formData.append('plugin_id', plugin_id);
        formData.append('ratio_type', ratio_type);
        $.ajax({
            url: fcom.makeUrl('PaymentMethods', 'uploadIcon',[plugin_id]),
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
                $('#gateway_icon').html(ans.msg);
                if(ans.status == true){
                    $('#gateway_icon').removeClass('text-danger');
                    $('#gateway_icon').addClass('text-success');
                    gatewayForm(plugin_id);
                    //editGatewayForm(ans.pmethodId);
                }else{
                    $('#gateway_icon').removeClass('text-success');
                    $('#gateway_icon').addClass('text-danger');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
	}

})();

$(document).on('click','.uploadFile-Js',function(){
	var node = this;
	$('#form-upload').remove();
	var plugin_id = $(node).attr('data-plugin_id');
	var frm = '<form enctype="multipart/form-data" id="form-upload" style="position:absolute; top:-100px;" >';
	frm = frm.concat('<input type="file" name="file" />');
	frm = frm.concat('<input type="hidden" name="plugin_id" value="'+plugin_id+'"/>');
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
				url: fcom.makeUrl('PaymentMethods', 'uploadIcon',[$('#form-upload input[name=\'plugin_id\']').val()]),
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
				},
				success: function(ans) {
						$('.text-danger').remove();
						$('#gateway_icon').html(ans.msg);
						if(ans.status == true){
							$('#gateway_icon').removeClass('text-danger');
							$('#gateway_icon').addClass('text-success');
							//editGatewayForm(ans.pmethodId);
						}else{
							$('#gateway_icon').removeClass('text-success');
							$('#gateway_icon').addClass('text-danger');
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
		}
	}, 500);
});
