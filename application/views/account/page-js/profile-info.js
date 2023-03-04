$(document).ready(function(){
	profileInfoForm();
});

(function() {
	var runningAjaxReq = false;
	var dv = '#profileInfoFrmBlock';
	var imgdv = '#profileImageFrmBlock';

	profileInfoForm = function(){
		$(dv).html(fcom.getLoader());
		$("#tab-myaccount").parents().children().removeClass("is-active");
		$("#tab-myaccount").addClass("is-active");
		fcom.ajax(fcom.makeUrl('Account', 'profileInfoForm'), '', function(t) {
            $(dv).html(t);
            stylePhoneNumberFld();
		});
	};

	profileImageForm = function(){
		$(imgdv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account', 'profileImageForm'), '', function(t) {
            location.reload();
			/* $(imgdv).html(t); */
		});
	};

	updateProfileInfo = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateProfileInfo'), data, function(t) {
			profileInfoForm();
			$.mbsmessage.close();
		});
	};

	setPreferredDashboad = function (id){
		fcom.updateWithAjax(fcom.makeUrl('Account','setPrefferedDashboard',[id]),'',function(res){
		});
	};

	bankInfoForm = function(){
		$(dv).html(fcom.getLoader());
		$("#tab-bankaccount").parents().children().removeClass("is-active");
		$("#tab-bankaccount").addClass("is-active");
		fcom.ajax(fcom.makeUrl('Account','bankInfoForm'),'',function(t){
			$(dv).html(t);
		});
	};
	settingsForm = function(){
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account','settingsInfo'),'',function(t){
			$(dv).html(t);
		});
	};
	setSettingsInfo = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateSettingsInfo'), data, function(t) {
			settingsForm();
		});
	};
	setBankInfo = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateBankInfo'), data, function(t) {
			bankInfoForm();
		});
	};

	removeProfileImage = function(){
		fcom.ajax(fcom.makeUrl('Account','removeProfileImage'),'',function(t){
			profileImageForm();
		});
	};

	sumbmitProfileImage = function(){
		$("#frmProfile").ajaxSubmit({
			delegation: true,
			success: function(json){
				json = $.parseJSON(json);
				profileImageForm();
				$(document).trigger('close.facebox');
			}
		});
	};

	affiliatePaymentInfoForm = function(){
		$(dv).html(fcom.getLoader());
		$("#tab-paymentinfo").parents().children().removeClass("is-active");
		$("#tab-paymentinfo").addClass("is-active");
		fcom.ajax(fcom.makeUrl('Affiliate','paymentInfoForm'),'',function(t){
			$(dv).html(t);
		});
	}

	setUpAffiliatePaymentInfo = function( frm ){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Affiliate', 'setUpPaymentInfo'), data, function(t) {
			//returnAddressForm();
		});
	}

	popupImage = function(inputBtn){
		if (inputBtn) {
			if(inputBtn.files && inputBtn.files[0]){
				fcom.ajax(fcom.makeUrl('Account', 'imgCropper'), '', function(t) {
					$.facebox(t,'faceboxWidth fbminwidth');
					var file = inputBtn.files[0];
					var options = {
					aspectRatio: 1 / 1,
					preview: '.img-preview',
					imageSmoothingQuality: 'high',
					imageSmoothingEnabled: true,
					crop: function (e) {
					  var data = e.detail;
					}
				  };
				  $(inputBtn).val('');
				  return cropImage(file, options, 'saveProfileImage', inputBtn);
				});
			}
		} else {
			fcom.ajax(fcom.makeUrl('Account', 'imgCropper'), '', function(t) {
				$.facebox(t,'faceboxWidth fbminwidth');
				var container = document.querySelector('.img-container');
				var image = container.getElementsByTagName('img').item(0);
				var options = {
				aspectRatio: 1 / 1,
				preview: '.img-preview',
				imageSmoothingQuality: 'high',
				imageSmoothingEnabled: true,
				crop: function (e) {
				  var data = e.detail;
				}
			  };
			  return cropImage(image, options, 'saveProfileImage');
			});
		}
	};
    
	saveProfileImage = function(formData){
		$.ajax({
			url: fcom.makeUrl('Account', 'uploadProfileImage'),
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
					$('#dispMessage').html(ans.msg);
					profileInfoForm();
					$(document).trigger('close.facebox');
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
		});
	}

	truncateDataRequestPopup = function(){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Account', 'truncateDataRequestPopup'), '', function(t) {
				$.facebox( t,'faceboxWidth');
			});
		});
	};

	sendTruncateRequest = function(){
		/* var agree = confirm( langLbl.confirmDeletePersonalInformation );
		if( !agree ){
			return false;
		} */
		fcom.updateWithAjax(fcom.makeUrl('Account', 'sendTruncateRequest'), '', function(t) {
			profileInfoForm();
			$(document).trigger('close.facebox');
		});
	};

	cancelTruncateRequest = function(){
		$(document).trigger('close.facebox');
	};

	requestData = function(){
		$.facebox(function() {
			fcom.ajax(fcom.makeUrl('Account', 'requestDataForm'), '', function(t) {
				$.facebox( t,'faceboxWidth');
			});
		});
	};

	setupRequestData = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'setupRequestData'), data, function(t) {
			$("#facebox .close").trigger('click');
		});
    };
    
    pluginForm = function(keyName){
		$(dv).html(fcom.getLoader());
		$("ul.tabs-js li").removeClass("is-active");
		$("#tab-" +keyName ).addClass("is-active");
		fcom.ajax(fcom.makeUrl(keyName, 'form'),'',function(t){
			$(dv).html(t);
		});
    };
    
    setupPluginForm = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl(frm.keyName.value, 'setupAccountForm'), data, function(t) {
			pluginForm(frm.keyName.value);
		});
	};
})();
