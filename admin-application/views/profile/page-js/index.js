$(document).ready(function(){
	profileInfoForm();
});

(function() {
	var runningAjaxReq = false;
	var dv = '#profileInfoFrmBlock';
	var imgdv = '#profileImageFrmBlock';

	profileInfoForm = function(){
		$(dv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Profile', 'profileInfoForm'), '', function(t) {
			$(dv).html(t);

		});
	};

	profileImageForm = function(){
		$(imgdv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Profile', 'profileImageForm'), '', function(t) {
			$(imgdv).html(t);

		});
	};

	updateProfileInfo = function(frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Profile', 'updateProfileInfo'), data, function(t) {
			//$.mbsmessage.close();
		});
	};

	removeProfileImage = function(){
		fcom.ajax(fcom.makeUrl('Profile','removeProfileImage'),'',function(t){
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

	popupImage = function(inputBtn){
		if (inputBtn) {
			if(inputBtn.files && inputBtn.files[0]){
				fcom.ajax(fcom.makeUrl('Profile', 'imgCropper'), '', function(t) {
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
			fcom.ajax(fcom.makeUrl('Profile', 'imgCropper'), '', function(t) {
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
			url: fcom.makeUrl('Profile', 'uploadProfileImage'),
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

})();
