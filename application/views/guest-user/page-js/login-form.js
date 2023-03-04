(function() {
	signUpWithPhone = function() {
        fcom.ajax(fcom.makeUrl( 'GuestUser', 'signUpWithPhone'), '', function(t) {
            $('#sign-up').html(t);
		});
    };
    
	signUpWithEmail = function() {
        fcom.ajax(fcom.makeUrl( 'GuestUser', 'signUpWithEmail'), '', function(t) {
            $('#sign-up').html(t);
		});
    };

    registerWithPhone = function (frm){
		if (!$(frm).validate()) return;	
		var data = fcom.frmData(frm);
		fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'register'), data, function(t) {
            if (1 == t.status) {
                fcom.ajax(fcom.makeUrl( 'GuestUser', 'otpForm'), '', function(t) {
                    t = $.parseJSON(t);
                    if(1 > t.status){
                        $.systemMessage(t.msg,'alert--danger', false);
                        return false;
                    }
                    $('#sign-up').html(t.html);
                    startOtpInterval();
                });
            }
        });	
        return false;
    };

    validateOtp = function (frm){
		if (!$(frm).validate()) return;	
        var data = fcom.frmData(frm);
		fcom.ajax(fcom.makeUrl('GuestUser', 'validateOtp'), data, function(t) {
            t = $.parseJSON(t);						
            if (1 == t.status) {
                window.location.href = t.redirectUrl;
            } else {
                $.systemMessage(t.msg, 'alert--danger', true);
                invalidOtpField();
            }
        });	
        return false;
    };
    
    resendOtp = function (userId, getOtpOnly = 0){
        $.systemMessage(langLbl.processing, 'alert--process', false);
		fcom.ajax(fcom.makeUrl( 'GuestUser', 'resendOtp', [userId, getOtpOnly]), '', function(t) {
            t = $.parseJSON(t);
            if(typeof t.status != 'undefined' &&  1 > t.status){
                $.systemMessage(t.msg, 'alert--danger', false);
                return false
            }
            $.systemMessage(t.msg, 'alert--success', true);
            var parent = 0 < $('#facebox .loginpopup').length ? '.loginpopup' : '';
            $('#sign-up').html(t.html);
            startOtpInterval(parent);
        });
        return false;
	};
})();