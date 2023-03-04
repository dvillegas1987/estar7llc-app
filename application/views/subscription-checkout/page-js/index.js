var loginDiv = '#login';
var sCartReviewDiv = '.checkout-content-js';
var paymentDiv = '.checkout-content-js';
var financialSummary = '.summary-listing-js';

$("document").ready(function() {
    //$('.step').removeClass("is-current");
    if (!isUserLogged()) {
        $(loginDiv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'login'), '', function(ans) {
            $(loginDiv).html(ans);

            $(loginDiv).addClass("is-current");
        });
    } else {
        loadSubscriptionCartReviewDiv();
        setTimeout(function(){ loadFinancialSummary(); }, 300);       
    }
});

(function() {
    setUpLogin = function(frm, v) {
        v.validate();
        if (!v.isValid()) return;
        fcom.updateWithAjax(fcom.makeUrl('GuestUser', 'login'), fcom.frmData(frm), function(t) {
            if (t.status == 1) {
                loadAddressDiv();
            }
        });
    };

    loadSubscriptionCartReviewDiv = function() {
        $(loginDiv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'loginDetails'), '', function(ans) {
            $(loginDiv).html(ans);
            //$(loginDiv).show();

        });
        $(sCartReviewDiv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'reviewScart'), '', function(ans) {
            $(sCartReviewDiv).html(ans);
            //$('.step').removeClass("is-current");
            $(sCartReviewDiv).addClass("is-current");
            setCheckoutFlow('BILLING');
        });
    };
    getReviewSCart = function() {
        $(sCartReviewDiv).find('.section-head').attr('onClick', 'loadCartReviewDiv()');
        $(sCartReviewDiv).html(fcom.getLoader());
        $(paymentDiv).html('<div class="selected-panel">4. Make payment</div>');
        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'getReviewScart'), '', function(ans) {
            $(sCartReviewDiv).html(ans);
            setCheckoutFlow('PAYMENT');

        });
    };
    $(document).on('click', ".confirmReview", function() {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        $(sCartReviewDiv).removeClass("is-current");
        loadPaymentSummary();
        loadFinancialSummary();
    });
    $(document).on('click', ".reviewOrder", function() {
        loadSubscriptionCartReviewDiv();
        loadPaymentBlankDiv();
    });
    loadPaymentBlankDiv = function() {
        $(paymentDiv).html(fcom.getLoader());

        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'PaymentBlankDiv'), '', function(ans) {
            $(paymentDiv).html(ans);
            $(paymentDiv).removeClass("is-current");
        });
    }
    loadPaymentSummary = function() {
        $(paymentDiv).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'PaymentSummary'), '', function(ans) {
            $(paymentDiv).html(ans);
            $(paymentDiv).addClass("is-current");
            setCheckoutFlow('PAYMENT');
        });
    };
    loadFinancialSummary = function() {
        $(financialSummary).html(fcom.getLoader());
        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'getFinancialSummary'), '', function(ans) {
            $(financialSummary).html(ans);
        });
    }
    walletSelection = function(el) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        var wallet = ($(el).is(":checked")) ? 1 : 0;
        var data = 'payFromWallet=' + wallet;
        fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'walletSelection'), data, function(ans) {
            loadPaymentSummary();
        });
    };
    useRewardPoints = function(frm) {
        $.systemMessage.close();
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);
        fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'useRewardPoints'), data, function(res) {
            loadPaymentSummary();
            loadFinancialSummary();
        });
    };

    removeRewardPoints = function() {
        $.systemMessage.close();
        fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'removeRewardPoints'), '', function(res) {
            loadPaymentSummary();
            loadFinancialSummary();
        });
    };

    getPromoCode = function(){
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'getCouponForm'), '', function(t) {
                $.facebox(t, 'faceboxWidth');
                $("input[name='coupon_code']").focus();
            });
        });
    };

    applyPromoCode = function(frm) {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        if (!$(frm).validate()) return;
        var data = fcom.frmData(frm);

        fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'applyPromoCode'), data, function(res) {
            $("#facebox .close").trigger('click');
            if ($('.payment-area').length > 0) {
                loadPaymentSummary();
            }       
            loadFinancialSummary();
           /*  if ($('.payment-area').length == 0) {
                console.log($('.payment-area').length);                   
                loadPaymentSummary();
            } else {
                loadPaymentBlankDiv();
                loadSubscriptionCartReviewDiv();
            } */
        });
    };

    triggerApplyCoupon = function(coupon_code) {
        document.frmPromoCoupons.coupon_code.value = coupon_code;
        applyPromoCode(document.frmPromoCoupons);
        return false;
    }

    removePromoCode = function() {
        fcom.updateWithAjax(fcom.makeUrl('SubscriptionCheckout', 'removePromoCode'), '', function(res) {
            $("#facebox .close").trigger('click');
            if ($('.payment-area').length > 0) {
                loadPaymentSummary();
            }    
            loadFinancialSummary();                     
            /* if ($('.payment-area').length == 0) {
                console.log($('.payment-area').length);   
                loadPaymentSummary();
            } else {
                loadPaymentBlankDiv();
                loadSubscriptionCartReviewDiv();
            } */
        });
    };

    setCheckoutFlow = function(type) {
        var obj = $('.checkout-progress');
        obj.find('div').removeClass('is-complete');
        obj.find('div').removeClass('is-active');
        obj.find('div').removeClass('pending');
        switch (type) {
            case 'BILLING':
                obj.find('.billing-js').addClass('is-active');
                obj.find('.shipping-js').addClass('pending');
                obj.find('.payment-js').addClass('pending');
                obj.find('.order-complete-js').addClass('pending');
                break;
            case 'PAYMENT':
                obj.find('.billing-js').addClass('is-complete');
                obj.find('.shipping-js').addClass('is-complete');
                obj.find('.payment-js').addClass('is-active');
                obj.find('.order-complete-js').addClass('pending');
                break;
            case 'COMPLETED':
                obj.find('.billing-js').addClass('is-complete');
                obj.find('.shipping-js').addClass('is-complete');
                obj.find('.payment-js').addClass('is-complete');
                obj.find('.order-complete-js').addClass('pending');
                break;
            default:
                obj.find('li').addClass('pending');
        }
    };

    sendPayment = function(frm, dv = '') {
        var data = fcom.frmData(frm);
        var action = $(frm).attr('action');
        fcom.ajax(action, data, function(t) {
            // debugger;
            try {
                var json = $.parseJSON(t);
                if (typeof json.status != 'undefined' && 1 > json.status) {
                    $.systemMessage(json.msg, 'alert--danger');
                    return false;
                }
                if (typeof json.html != 'undefined') {
                    $(dv).append(json.html);
                }
                if (json['redirect']) {
                    $(location).attr("href", json['redirect']);
                }
            } catch (e) {
                $(dv).append(t);
            }
        });
    };
    /* $(document).on('click', '.coupon-input', function() {
        if (isUserLogged() == 0) {
            loginPopUpBox();
            return false;
        }
        $.facebox(function() {
            fcom.ajax(fcom.makeUrl('SubscriptionCheckout', 'getCouponForm'), '', function(t) {
                $.facebox(t, 'faceboxWidth');
                $("input[name='coupon_code']").focus();
            });
        });
    }); */

})();