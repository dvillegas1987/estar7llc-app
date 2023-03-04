$(document).ready(function () {
	sellerProductForm(product_id, selprod_id);
});

$(document).on('change', '.selprodoption_optionvalue_id', function () {
	var frm = document.frmSellerProduct;
	var data = fcom.frmData(frm);
	fcom.ajax(fcom.makeUrl('Seller', 'checkSellProdAvailableForUser'), data, function (t) {
		var ans = $.parseJSON(t);
		if (ans.status == 0) {
			$.mbsmessage(ans.msg, false, 'alert--danger');
			return;
		}
		$.mbsmessage.close();
	});
});

(function () {
	var runningAjaxReq = false;
	var runningAjaxMsg = 'some requests already running or this stucked into runningAjaxReq variable value, so try to relaod the page and update the same to WebMaster. ';
	//var dv = '#sellerProductsForm';
	var dv = '#listing';

	checkRunningAjax = function () {
		if (runningAjaxReq == true) {
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	loadSellerProducts = function (frm) {
		sellerProducts($(frm.product_id).val());
	};


	sellerProductForm = function (product_id, selprod_id) {
		$("#tabs_001").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductGeneralForm', [product_id, selprod_id]), '', function (t) {
			$(".tabs_panel").html('');
			$(".tabs_panel").hide();
			$(".tabs_nav-js  > li").removeClass('is-active');
			$("#tabs_001").show();
			$("a[rel='tabs_001']").parent().addClass('is-active');
			$("#tabs_001").html(t);
		});
	};

	translateData = function (item, defaultLang, toLangId) {
		var autoTranslate = $("input[name='auto_update_other_langs_data']:checked").length;
		var prodName = $("input[name='selprod_title" + defaultLang + "']").val();
		var prodDesc = $("textarea[name='selprod_comments" + defaultLang + "']").val();
		var alreadyOpen = $('.collapse-js-' + toLangId).hasClass('show');
		if (autoTranslate == 0 || prodName == "" || alreadyOpen == true) {
			return false;
		}
		var data = "product_name=" + prodName + '&product_description=' + prodDesc + "&toLangId=" + toLangId;
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'translatedProductData'), data, function (t) {
			if (t.status == 1) {
				$("input[name='selprod_title" + toLangId + "']").val(t.productName);
				$("textarea[name='selprod_comments" + toLangId + "']").val(t.productDesc);
			}
		});
	}

	setUpSellerProduct = function (frm) {
		if (!$(frm).validate()) return;
		events.customizeProduct();
		runningAjaxReq = true;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Seller', 'setUpSellerProduct'), data, function (t) {
			runningAjaxReq = false;
			if (productType === PRODUCT_TYPE_DIGITAL) {
				sellerProductDownloadFrm(t.product_id, t.selprod_id);
				return;
			}
			//window.location.replace(fcom.makeUrl('Seller', 'products'));
			setTimeout(function () { window.location.href = fcom.makeUrl('Seller', 'products'); }, 1000);
		});
	};

	optionsAssocArr = function (formData) {
		var data = {};
		$.each(formData, function (key, obj) {
			if ('' != obj.value) {
				var a = obj.name.match(/(.*?)\[(.*?)\]\[(.*?)\]/);
				if (a !== null) {
					var subName = a[1];
					var subKey = a[2];
					var options = a[3];

					if (!data[subName]) {
						data[subName] = [];
					}

					if (!data[subName][subKey]) {
						data[subName][subKey] = [];
					}

					if (data[subName][subKey][options]) {
						if ($.isArray(data[subName][subKey][options])) {
							data[subName][subKey][options] = obj.value;
						} else {
							data[subName][subKey][options] = obj.value;
						}
					} else {
						data[subName][subKey][options] = obj.value;
					}
				} else {
					if (data[obj.name]) {
						if ($.isArray(data[obj.name])) {
							data[obj.name].push(obj.value);
						} else {
							data[obj.name] = [];
							data[obj.name].push(obj.value);
						}
					} else {
						data[obj.name] = obj.value;
					}
				}
			}
		});
		return data;
	};

	setUpMultipleSellerProducts = function (frm, i = 0, orignalData = []) {
		if (!$(frm).validate()) return;

		if (1 > orignalData.length) {
			orignalData = optionsAssocArr($(frm).serializeArray());
		}
		var data = orignalData;
		var varients = data.varients;
		varients = varients.filter(function () { return true; });

		if (i < varients.length) {
			var chunk = varients[i];
			var final = {};
			$.extend(final, data, chunk);
			final.varients = [];
			var data = jQuery.param(final);

			$('.optionFld-js').each(function () {
				var $this = $(this);
				var errorInRow = false;
				$this.find('input').each(function () {
					if ($(this).parent().hasClass('fldSku') && CONF_PRODUCT_SKU_MANDATORY != 1) {
						return;
					}
					if ($(this).val().length == 0 || $(this).val() == 0) {
						errorInRow = true;
						return false;
					}
				});
				if (errorInRow) {
					$this.parent().addClass('invalid');
				} else {
					$this.parent().removeClass('invalid');
				}
			});
			if ($("#optionsTable-js > tbody > tr.invalid").length == $("#optionsTable-js > tbody > tr").length) {
				$.systemMessage(LBL_MANDATORY_OPTION_FIELDS, 'alert--danger');
				return false;
			}

			fcom.ajax(fcom.makeUrl('Seller', 'setUpMultipleSellerProducts'), data, function (t) {
				i++;
				if (0 == t.status) {
					$.mbsmessage(t.msg, false, 'alert--danger alert');
					setTimeout(function () { location.reload(); }, 3000);
					return;
				}
				if (i < varients.length) {
					setUpMultipleSellerProducts(frm, i, orignalData);
				}
				if (i == varients.length) {
					if (productType === PRODUCT_TYPE_DIGITAL) {
						sellerProductDownloadFrm(t.product_id, 0);
						return;
					}
					setTimeout(function () { location.reload(); }, 1000);
				}
			}, { fOutMode: 'json' });

			var counterString = langLbl.processing_counter.replace("{counter}", (i + 1));
			counterString = counterString.replace("{count}", varients.length);
			counterString = langLbl.processing + " " + counterString;
			$.mbsmessage(counterString, false, 'alert--process alert');
		}
	};

	sellerProductDownloadFrm = function (product_id, selprod_id) {
		$("#tabs_002").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'sellerProductDownloadFrm', [product_id, selprod_id]), '', function (t) {
			$(".tabs_panel").html('');
			$(".tabs_panel").hide();
			$(".tabs_nav-js  > li").removeClass('is-active');
			$("#tabs_002").show();
			$("a[rel='tabs_002']").parent().addClass('is-active');
			$("#tabs_002").html(t);
			$(".downloadType-js").each(function () {
				$(this).trigger("change");
			})
		});
	};

	setUpSellerProductDownloads = function (type, product_id, selprod_id) {
		var data = new FormData();
		$inputs = $('#frmDownload input[type=text],#frmDownload input[type=textarea],#frmDownload select,#frmDownload input[type=hidden]');
		$inputs.each(function () { data.append(this.name, $(this).val()); });
		data.append('selprod_id', selprod_id);
		if (DIGITAL_DOWNLOAD_FILE == type) {
			$.each($('#downloadable_file' + selprod_id)[0].files, function (i, file) {
				$(dv).html(fcom.getLoader());
				data.append('downloadable_file', file);
				$.ajax({
					url: fcom.makeUrl('Seller', 'uploadDigitalFile'),
					type: "POST",
					data: data,
					processData: false,
					contentType: false,
					success: function (t) {
						var ans = $.parseJSON(t);
						if (ans.status == 0) {
							$.mbsmessage(ans.msg, true, 'alert--danger');
							sellerProductDownloadFrm(product_id, selprod_id);
							return;
						}
						sellerProductDownloadFrm(product_id, selprod_id);
					},
					error: function (jqXHR, textStatus, errorThrown) {
						alert("Error Occurred.");
					}
				});
			});
		} else {
			var data = fcom.frmData(document.frmDownload);
			data = data + '&' + 'selprod_id' + "=" + selprod_id;
			/*if (!$('#frmDownload').validate()) return;*/
			$(dv).html(fcom.getLoader());
			fcom.ajax(fcom.makeUrl('Seller', 'uploadDigitalFile'), data, function (t) {
				var ans = $.parseJSON(t);
				if (ans.status == 0) {
					$.mbsmessage(ans.msg, true, 'alert--danger');
					return;
				}
				$.systemMessage(ans.msg, 'alert--success');
				sellerProductDownloadFrm(product_id, selprod_id);
			});
		}
	};

	deleteDigitalFile = function (selprod_id, afile_id) {
		var agree = confirm(langLbl.confirmDelete);
		if (!agree) { return false; }
		fcom.updateWithAjax(fcom.makeUrl('seller', 'deleteDigitalFile', [selprod_id, afile_id]), '', function (t) {
			sellerProductDownloadFrm(product_id, selprod_id, 0);
		});
	}

	updateDiscountString = function () {
		var splprice_display_list_price = 0;
		var splprice_display_dis_val = 0;
		var splprice_display_dis_type = 0;

		splprice_display_list_price = $("input[name='splprice_display_list_price']").val();
		if (splprice_display_list_price == '' || typeof splprice_display_list_price == undefined) {
			splprice_display_list_price = 0;
		}

		splprice_display_dis_val = $("input[name='splprice_display_dis_val']").val();
		if (splprice_display_dis_val == '' || typeof splprice_display_dis_val == undefined) {
			splprice_display_dis_val = 0;
		}

		splprice_display_dis_type = $("select[name='splprice_display_dis_type']").val();
		if (splprice_display_dis_type == 0 || typeof splprice_display_dis_type == undefined || typeof splprice_display_dis_type == '') {
			splprice_display_dis_type = FLAT;
		}
		var data = 'splprice_display_list_price=' + splprice_display_list_price + '&splprice_display_dis_val=' + splprice_display_dis_val + '&splprice_display_dis_type=' + splprice_display_dis_type;
		$("#special-price-discounted-string").html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Seller', 'getSpecialPriceDiscountString'), data, function (res) {
			$("#special-price-discounted-string").html(res);
		});
	}

	gotToProucts = function () {
		window.location.href = fcom.makeUrl('seller', 'Products');
	};

	getUniqueSlugUrl = function (obj, str, recordId) {
		if (str == '') {
			return;
		}
		var data = { url_keyword: str, recordId: recordId }
		fcom.ajax(fcom.makeUrl('Seller', 'isProductRewriteUrlUnique'), data, function (t) {
			var ans = $.parseJSON(t);
			$(obj).next().html(ans.msg);
			if (ans.status == 0) {
				$(obj).next().addClass('text-danger').removeClass('text-muted');
			} else {
				$(obj).next().removeClass('text-danger').addClass('text-muted');
			}
		});
	};

})();

$(document).on('click', '.tabs_001', function () {
	if (selprod_id > 0) {
		sellerProductForm(product_id, selprod_id);
	}
});

$(document).on('click', '.tabs_002', function () {
	if (selprod_id > 0) {
		sellerProductDownloadFrm(product_id, selprod_id);
	}
});
