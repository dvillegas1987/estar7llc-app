<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

if (isset($stripe)) {
    if (isset($stripe['secret_key']) && isset($stripe['publishable_key'])) {
        if (!empty($stripe['secret_key']) && !empty($stripe['publishable_key'])) { ?>
            <?php if (!FatUtility::isAjaxCall()) { ?>
                <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
                <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
            <?php } ?>
            <script type="text/javascript">
                var publishable_key = '<?php echo $stripe['publishable_key']; ?>';
            </script>
    <?php }
    }
}
if (isset($client_secret)) { ?>
    <script type="text/javascript">
        var stripe = Stripe(publishable_key);
        var clientSecret = '<?php echo $client_secret; ?>';
        stripe.confirmCardPayment(clientSecret, {
            payment_method: '<?php echo $payment_method_id; ?>'
        }).then(function(result) {
            // console.log(result);
            if (result.error) {
                // PaymentIntent client secret was invalid
                location.href = '<?php echo $cancelBtnUrl; ?>';
            } else {
                if (result.paymentIntent.status === 'succeeded') {

                    var data = 'order_id=<?php echo $order_id ?>&payment_intent_id=<?php echo $payment_intent_id ?>&is_ajax_request=yes';

                    $.ajax({
                        type: "POST",
                        url: '<?php echo UrlHelper::generateUrl('StripePay', 'StripeSuccess') ?>',
                        data: data,
                        success: function(data) {
                            location.href = '<?php echo UrlHelper::generateUrl('custom', 'paymentSuccess', array($order_id), CONF_WEBROOT_URL); ?>';
                        }
                    });

                } else if (result.paymentIntent.status === 'requires_payment_method') {
                    // Authentication failed, prompt the customer to enter another payment method
                    location.href = '<?php echo UrlHelper::generateUrl('custom', 'paymentFailed'); ?>';
                }
            }
        });
    </script>
<?php exit;
} else { ?>
    <script>
        (function($) {
            var _this = false;
            var _subText = false;

            loadStripe = function() {
                try {
                    if (typeof publishable_key != typeof undefined) {
                        // this identifies your website in the createToken call below
                        Stripe.setPublishableKey(publishable_key);
                        // console.log(Stripe);
                        function stripeResponseHandler(status, response) {
                            $('#stripeCharge').find(":submit").attr('disabled', 'disabled');
                            $submit = true;
                            if (_this && _subText) {
                                _this.find('input[type=submit]').val(_subText);
                            }

                            if (response.error) {
                                $("#stripeCharge").prepend('<div class="alert alert--danger">' + response.error.message + '</div>');
                                $("#stripeCharge").find(":submit").removeAttr('disabled');
                            } else {

                                var form = $("#stripeCharge");
                                // token contains id, last4, and card type
                                var token = response['id'];
                                // insert the token into the form so it gets submitted to the server
                                form.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
                                form.attr('onsubmit', 'sendPayment(this, "#tabs-container"); return(false);');
                                form.submit();
                            }

                        }
                        $submit = true;
                        $(document).on("submit", "#stripeCharge", function(event) {
                            event.preventDefault();
                            if (!$(this).validate()) return;
                            var stripeToken = $("input[name='stripeToken']").val();
                            if ('' != stripeToken && 'undefined' != typeof stripeToken) {
                                return;
                            }
                            $.mbsmessage(langLbl.processing, true, 'alert--process');
                            // prop('disabled', true);
                            $('.alert--danger').remove();

                            _this = $(this);
                            var _numberWrap = $('#cc_number');
                            var _cvvWrap = $('#cc_cvv');
                            var _expMonthWrap = $('#cc_expire_date_month');
                            var _expYearWrap = $('#cc_expire_date_year');
                            _subText = _this.find('input[type=submit]').val();


                            if ($submit && _numberWrap.length > 0 && _cvvWrap.length > 0 && _expMonthWrap.length > 0 && _expYearWrap.length > 0) {

                                var _numberValue = _numberWrap.val().trim();
                                var _cvvValue = _cvvWrap.val().trim();
                                var _expMonthValue = _expMonthWrap.val().trim();
                                var _expYearValue = _expYearWrap.val().trim();

                                if (_numberValue != '' && _cvvValue != '' && _expMonthValue != '' && _expYearValue != '') {
                                    $submit = false;
                                    _this.find('input[type=submit]').val(_this.find('input[type=submit]').data('processing-text'));
                                    Stripe.createToken({
                                        number: _numberValue,
                                        cvc: _cvvValue,
                                        exp_month: _expMonthValue,
                                        exp_year: _expYearValue
                                    }, stripeResponseHandler);
                                }

                            }
                            return $submit; // submit from callback
                        });

                    }

                } catch (e) {
                    // console.log(e.message);
                    setTimeout(function() {
                        loadStripe();
                    }, 500);
                }
            }
            loadStripe();
        })(jQuery);
    </script>
<?php }
if (!isset($error)) {
    $frm->setFormTagAttribute('id', 'stripeCharge');
    $fld = $frm->getField('cc_number');
    $fld->addFieldTagAttribute('class', 'p-cards');
    $fld->addFieldTagAttribute('id', 'cc_number');
    $fld = $frm->getField('cc_owner');
    $fld->addFieldTagAttribute('id', 'cc_owner');
    $fld = $frm->getField('cc_cvv');
    $fld->addFieldTagAttribute('id', 'cc_cvv'); ?>

    <?php echo $frm->getFormTag(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('cc_number'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_CARD_HOLDER_NAME', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('cc_owner'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Expiry_Month', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php
                        $fld = $frm->getField('cc_expire_date_month');
                        $fld->addFieldTagAttribute('id', 'cc_expire_date_month');
                        $fld->addFieldTagAttribute('class', 'ccExpMonth  combobox required');
                        echo $fld->getHtml(); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Expiry_year', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php
                        $fld = $frm->getField('cc_expire_date_year');
                        $fld->addFieldTagAttribute('id', 'cc_expire_date_year');
                        $fld->addFieldTagAttribute('class', 'ccExpYear combobox required');
                        echo $fld->getHtml(); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_CVV_SECURITY_CODE', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('cc_cvv'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="total-pay"><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?> <small>(<?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?>)</small> </div>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php
                        $btn = $frm->getField('btn_submit');
                        $btn->addFieldTagAttribute('class', 'btn btn-secondary');
                        $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
                        echo $frm->getFieldHtml('btn_submit'); ?>
                        <a href="<?php echo $cancelBtnUrl; ?>" class="btn btn-outline-brand"><?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
    <?php echo $frm->getExternalJs(); ?>
<?php } else { ?>
    <div class="alert alert--danger"><?php echo $error ?></div>
<?php } ?>
<div id="ajax_message"></div>