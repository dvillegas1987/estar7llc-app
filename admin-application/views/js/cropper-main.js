(function() {
systemImgCropper = function(url, aspectRatio, callback, inputBtn){
	if (inputBtn.files && inputBtn.files[0]) {
		fcom.ajax(url, '', function(t) {
			$.facebox(t,'faceboxWidth fbminwidth');
			var container = document.querySelector('.img-container');
			var file = inputBtn.files[0];
			$('#new-img').attr('src', URL.createObjectURL(file));
			var image = container.getElementsByTagName('img').item(0);
			var options = {
			aspectRatio: aspectRatio,
			preview: '.img-preview',
			imageSmoothingQuality: 'high',
			imageSmoothingEnabled: true,
			crop: function (e) {
			  var data = e.detail;
			}
		  };
		  $(inputBtn).val('');
		  return cropImage(image, options, callback, inputBtn);
		});
	}
};

cropImage = function(file, options, callback, inputBtn){
	var uploadedImageType ='';
  	var container = document.querySelector('.img-container');
  	if (/^image\/\w+/.test(file.type)) {
	    uploadedImageType = file.type;
	    var uploadedImageName = file.name;
		$('#new-img').attr('src', URL.createObjectURL(file));
		var image = container.getElementsByTagName('img').item(0);
 	} else if(typeof file.src != 'undefined') {
		var image = file;
		var uploadedImageType = 'image/png';
	    var uploadedImageName = 'cropped.png';
 	} else {
		window.alert('Please choose an image file.');
 	}

  var actions = document.getElementById('actions');
  var cropper = new Cropper(image, options);
  var originalImageURL = image.src;
  var uploadedImageURL;

  actions.querySelector('.docs-buttons').onclick = function (event) {
	var e = event || window.event;
	var target = e.target || e.srcElement;
	var cropped;
	var result;
	var input;
	var data;
	if (!cropper) {
	  return;
	}

	while (target !== this) {
	  if (target.getAttribute('data-method')) {
		break;
	  }

	  target = target.parentNode;
	}

	if (target === this || target.disabled || target.className.indexOf('disabled') > -1) {
	  return;
	}

	data = {
	  method: target.getAttribute('data-method'),
	  target: target.getAttribute('data-target'),
	  option: target.getAttribute('data-option') || undefined,
	  secondOption: target.getAttribute('data-second-option') || undefined
	};

	cropped = cropper.cropped;

	if (data.method) {
	  if (typeof data.target !== 'undefined') {
		input = document.querySelector(data.target);

		if (!target.hasAttribute('data-option') && data.target && input) {
		  try {
			data.option = JSON.parse(input.value);
		  } catch (e) {
			console.log(e.message);
		  }
		}
	  }

	  switch (data.method) {
		case 'rotate':
		  if (cropped && options.viewMode > 0) {
			cropper.clear();
		  }

		  break;

		case 'getCroppedCanvas':
		  try {
			data.option = JSON.parse(data.option);
		  } catch (e) {
			/*console.log(e.message);*/
		  }

		  if (uploadedImageType === 'image/jpeg') {
			if (!data.option) {
			  data.option = {};
			}

			data.option.fillColor = '#fff';
		  }

		  break;
	  }

		result = cropper[data.method](data.option, data.secondOption);
	  switch (data.method) {
		case 'rotate':
		  if (cropped && options.viewMode > 0) {
			cropper.crop();
		  }

		  break;

		case 'scaleX':
		case 'scaleY':
		  target.setAttribute('data-option', -data.option);
		  break;

		case 'getCroppedCanvas':
		  if (result) {
			var formData = new FormData();
			var canvas;
			canvas = cropper.clear().getCroppedCanvas();
			canvas.toBlob(function (blob) {
				formData.append('org_image', blob, 'org'+uploadedImageName);
				result.toBlob(function (blobs) {
	                formData.append('cropped_image', blobs, uploadedImageName);
	                formData.append("action", "avatar");
					if(inputBtn){
						var frmName = $(inputBtn).attr('data-frm')
						formData.append("frmName", frmName);
					}
                    var fileType = $(inputBtn).attr('data-file_type');
					if (typeof fileType !== typeof undefined && fileType !== false) {
						formData.append("file_type", fileType);
					}
					window[callback](formData);
				}, uploadedImageType);
			}, uploadedImageType);
		  }

		  break;

		case 'destroy':
		  cropper = null;

		  if (uploadedImageURL) {
			URL.revokeObjectURL(uploadedImageURL);
			uploadedImageURL = '';
			image.src = originalImageURL;
		  }

		  break;
	  }

	  if (typeof result === 'object' && result !== cropper && input) {
		try {
		  input.value = JSON.stringify(result);
		} catch (e) {
		  console.log(e.message);
		}
	  }
	}
	};

	// Import image
	  var inputImage = document.getElementById('inputImage');
	  if (URL ) {
          inputImage.onchange = function () {
	      var files = this.files;
	      var file;

	      if (cropper && files && files.length) {
	        file = files[0];

	        if (/^image\/\w+/.test(file.type)) {
	          uploadedImageType = file.type;
	          uploadedImageName = file.name;

	          if (uploadedImageURL) {
	            URL.revokeObjectURL(uploadedImageURL);
	          }

	          image.src = uploadedImageURL = URL.createObjectURL(file);
	          cropper.destroy();
	          cropper = new Cropper(image, options);
	          inputImage.value = null;
	        } else {
	          window.alert('Please choose an image file.');
	        }
	      }
	    };
	  } else {
	    inputImage.disabled = true;
	    inputImage.parentNode.className += ' disabled';
	  }
}
})();
