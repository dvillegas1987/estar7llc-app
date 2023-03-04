$(document).ready(function(){
	changePasswordForm();
	changeEmailForm();
    changePhoneNumberForm();
});

(function() {
	var runningAjaxReq = false;
	var passdv = '#changePassFrmBlock';
	var emaildv = '#changeEmailFrmBlock';
	var phoneNumberdv = '#changePhoneNumberFrmBlock';

	checkRunningAjax = function(){
		if( runningAjaxReq == true ){
			console.log(runningAjaxMsg);
			return;
		}
		runningAjaxReq = true;
	};

	changePasswordForm = function(){
		$(passdv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account', 'changePasswordForm'), '', function(t) {
			$(passdv).html(t);
		});
	};

	changeEmailForm = function(){
		$(emaildv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account', 'changeEmailForm'), '', function(t) {
			$(emaildv).html(t);
		});
    };
    
	updatePassword = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updatePassword'), data, function(t) {
			changePasswordForm();
		});
	};

	updateEmail = function (frm){
		if (!$(frm).validate()) return;
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('Account', 'updateEmail'), data, function(t) {
			changeEmailForm();
		});
    };

    changePhoneNumberForm = function(){
		$(phoneNumberdv).html(fcom.getLoader());
		fcom.ajax(fcom.makeUrl('Account', 'changePhoneForm'), '', function(t) {
            t = $.parseJSON(t);
            $(phoneNumberdv).html(t.html);            
            stylePhoneNumberFld();
		});
	};
    
	getOtp = function (frm, updateToDbFrm = 0){
		if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        $(frm.btn_submit).attr('disabled', 'disabled');
        $.systemMessage(langLbl.processing,'alert--process', false);
		fcom.ajax(fcom.makeUrl( 'Account', 'getOtp', [updateToDbFrm]), data, function(t) {                  
            $.systemMessage.close();
            t = $.parseJSON(t);
            if(1 > t.status){
                $.systemMessage(t.msg,'alert--danger', false);
                $(frm.btn_submit).removeAttr('disabled');
                return false;
            }

            var lastFormElement = phoneNumberdv + ' form:last';
            var resendOtpElement = lastFormElement + " .resendOtp-js";
            $(lastFormElement + ' [name="btn_submit"]').closest("div.row").remove();
            var phoneNumber = $(lastFormElement + " input[name='user_phone']").val();
            var dialCode = $(lastFormElement + " input[name='user_phone_dcode']").val();
            
            if (0 < updateToDbFrm) {
                $(lastFormElement + " input[name='user_phone']").attr('readonly', 'readonly');
            }
            $(lastFormElement).after(t.html);
            $(".otpForm-js .form-side").removeClass('form-side');
            $('.formTitle-js').remove();

            var resendFunction = 'resendOtp()';
            if (0 < updateToDbFrm) {
                $(phoneNumberdv + " form:last").attr('onsubmit', 'return validateOtp(this, 0);');

                var resendOtpElement = lastFormElement + " .resendOtp-js";
                resendFunction = 'resendOtp("' + phoneNumber + '", "' + dialCode + '")';
            }
            $(resendOtpElement).removeAttr('onclick').attr('onclick', resendFunction);
            startOtpInterval();
        });
        return false;
    };
    
    resendOtp = function (phone = '', dialCode = ''){
        clearInterval(otpIntervalObj);
        var postparam = (1 == phone) ? '' : "user_phone=" + phone + "&user_phone_dcode=" + dialCode;
        $.systemMessage(langLbl.processing, 'alert--process', false);
		fcom.ajax(fcom.makeUrl('Account', 'resendOtp'), postparam, function(t) {
            t = $.parseJSON(t);
            if(1 > t.status){
                $.systemMessage(t.msg,'alert--danger', false);
                return false;
            }
            $.systemMessage(t.msg,'alert--success', false);
            startOtpInterval();
        });
        return false;
    };

    validateOtp = function (frm, updateToDbFrm = 1){
		if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        $(frm.btn_submit).attr('disabled', 'disabled');
        $.systemMessage(langLbl.processing,'alert--process', true);
		fcom.ajax(fcom.makeUrl( 'Account', 'validateOtp', [updateToDbFrm]), data, function(t) {
            t = $.parseJSON(t);
            if(1 > t.status){
                $.systemMessage(t.msg,'alert--danger', true);
                invalidOtpField();
                $(frm.btn_submit).removeAttr('disabled');
                return false;
            } else if ('undefined' != typeof t.html) {
                $.systemMessage.close();
                $(phoneNumberdv + " .otpForm-js").remove();
                var lastFormElement = phoneNumberdv + ' form:last';
                $(lastFormElement).html(t.html);
                stylePhoneNumberFld();
            } else {
                $.systemMessage(t.msg,'alert--success', true);
                changePhoneNumberForm();
            }
        });
        return false;
    };

})();
