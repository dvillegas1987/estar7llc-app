$(document).ready(function () {
    changeEmailForm();
    configurePhoneForm();
});

(function () {
    var runningAjaxReq = false;
    var dv = '#changeEmailFrmBlock';
    var phoneNumberdv = '#changePhoneFrmBlock';

    checkRunningAjax = function () {
        if (runningAjaxReq == true) {
            console.log(runningAjaxMsg);
            return;
        }
        runningAjaxReq = true;
    };

    changeEmailForm = function () {
        if (0 < $(dv).length) {
            $(dv).html(fcom.getLoader());
            fcom.ajax(fcom.makeUrl('GuestUser', 'changeEmailForm'), '', function (t) {
                $(dv).html(t);
            });
        }
    };

    configurePhoneForm = function () {
        if (0 < $(dv).length) {
            $(dv).html(fcom.getLoader());
            fcom.ajax(fcom.makeUrl('GuestUser', 'configurePhoneForm'), '', function (t) {
                $(phoneNumberdv).html(t);
                stylePhoneNumberFld();
            });
        }
    };

    updateEmail = function (frm) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'updateEmail'), data, function (t) {
            changeEmailForm();
        });
    };

    getOtp = function (frm, updateToDbFrm = 0) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        $(frm.btn_submit).attr('disabled', 'disabled');
        $.systemMessage(langLbl.processing, 'alert--process', false);
        fcom.ajax(fcom.makeUrl('Account', 'getOtp', [updateToDbFrm]), data, function (t) {
            $.systemMessage.close();
            t = $.parseJSON(t);
            if (typeof t.status != 'undefined' && 1 > t.status) {
                $.systemMessage(t.msg, 'alert--danger', false);
                $(frm.btn_submit).removeAttr('disabled');
                return false;
            }
            var lastFormElement = phoneNumberdv + ' form:last';
            var resendOtpElement = lastFormElement + " .resendOtp-js";
            $(lastFormElement + ' [name="btn_submit"]').closest("div.row").remove();
            var phoneNumber = $(lastFormElement + " input[name='user_phone']").val();
            var dialCode = $(lastFormElement + " input[name='user_phone_dcode']").val();

            $(lastFormElement).after(t.html);
            $('.formTitle-js').remove();

            var resendFunction = 'resendOtp()';
            if (0 < updateToDbFrm) {
                $(phoneNumberdv + " form").attr('onsubmit', 'return validateOtp(this, 0);');
                var resendOtpElement = lastFormElement + " .resendOtp-js";
                resendFunction = 'resendOtp("' + phoneNumber + '", "' + dialCode + '")';
            }
            $(resendOtpElement).removeAttr('onclick').attr('onclick', resendFunction);
            startOtpInterval();
        });
        return false;
    };

    resendOtp = function (phone = '', dialCode = '') {
        var postparam = (1 == phone) ? '' : "user_phone=" + phone + "&user_phone_dcode=" + dialCode;
        $.systemMessage(langLbl.processing, 'alert--process', false);
        fcom.ajax(fcom.makeUrl('Account', 'resendOtp'), postparam, function (t) {
            t = $.parseJSON(t);
            if (1 > t.status) {
                $.systemMessage(t.msg, 'alert--danger', false);
                return false;
            }
            $.systemMessage(t.msg, 'alert--success', false);
            startOtpInterval();
        });
        return false;
    };

    validateOtp = function (frm, updateToDbFrm = 1) {
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        $.systemMessage(langLbl.processing, 'alert--process', false);
        fcom.ajax(fcom.makeUrl('Account', 'validateOtp', [updateToDbFrm]), data, function (t) {
            t = $.parseJSON(t);
            if (1 > t.status) {
                $.systemMessage(t.msg, 'alert--danger', false);
                invalidOtpField();
                return false;
            }
            $.systemMessage.close();
            location.href = fcom.makeUrl();
        });
        return false;
    };

})();