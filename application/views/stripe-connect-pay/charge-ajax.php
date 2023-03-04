<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$paymentIntendId = isset($paymentIntendId) ? $paymentIntendId : '';

$frm->setFormTagAttribute('onsubmit', 'doPayment(this, "' . $orderInfo["id"] . '"); return(false);');
$frm->setFormTagAttribute('class', 'form form--normal');

echo $frm->getFormTag(); ?>
<div class="m-3 text-right">
        <a class="link-text" href="javascript:void(0);" onclick="addNewCard('<?php echo $orderInfo['id']; ?>')">
            <i class="icn"> 
                <svg class="svg">
                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#add" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#add">
                    </use>
                </svg> 
            </i>
            <?php echo Labels::getLabel('LBL_ADD_NEW_CARD', $siteLangId); ?>
        </a>
    </div>
    <ul class="list-group list-group-flush-x payment-card payment-card-view">
        <?php
        foreach ($savedCards as $cardDetail) { ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-auto">
                        <label class="radio">
                            <input name="card_id" type="radio" value="<?php echo $cardDetail['id']; ?>" <?php echo $defaultSource == $cardDetail['id'] ? "checked" : ""; ?>>
                            <i class="input-helper"></i>
                        </label>
                    </div>
                    <div class="col">
                        <div class="payment-card__photo">
                            <?php 
                                $cardBrand = strtolower(str_replace(" ", "", $cardDetail['brand']));
                            ?>
                            <svg class="svg">
                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#<?php echo $cardBrand; ?>" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#<?php echo $cardBrand; ?>">
                                </use>
                            </svg>
                        </div>
                    </div>
                    <div class="col">
                        <div class="payment-card__number">
                            <?php echo Labels::getLabel('LBL_ENDING_IN', $siteLangId); ?>
                            <strong><?php echo $cardDetail['last4']; ?></strong>
                        </div>
                    </div>
                    <div class="col">
                        <div class="payment-card__name"><?php echo $cardDetail['name']; ?></div>
                    </div>
                    <div class="col">
                        <div class="payment-card__expiry"><?php echo Labels::getLabel('LBL_EXPIRY', $siteLangId); ?>
                            <strong><?php echo $cardDetail['exp_month'] . '/' . $cardDetail['exp_year']; ?></strong></div>
                    </div>
                    <div class="col-auto">
                        <div class="payment-card__actions">
                            <ul class="list-actions">
                                <li>
                                    <a href="javascript:void(0)" onClick="removeCard('<?php echo $cardDetail['id']; ?>');">
                                        <svg class="svg" width="24px" height="24px">
                                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#remove">
                                            </use>
                                        </svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </li>
        <?php } ?>
    </ul>

    <?php if (!empty($savedCards)) { ?>
        <div class="payment-action">
            <a href="<?php echo $cancelBtnUrl; ?>" class="btn btn-outline-brand btn-wide"><?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?></a>
            <?php
                $btn = $frm->getField('btn_submit');
                $btn->addFieldTagAttribute('class', 'btn btn-brand btn-wide');
                $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
                echo $frm->getFieldHtml('btn_submit');
                ?>
        </div>
    
    <?php } ?>
</form>
<?php echo $frm->getExternalJs(); ?>
<?php if (empty($paymentIntendId)) { ?>
    <script type="text/javascript">
        (function() {
            var controller = 'StripeConnectPay';
            var paymentForm = '#tabs-container';
            doPayment = function(frm, orderId) {
                if (!$(frm).validate()) return false;
                var data = fcom.frmData(frm);
                fcom.updateWithAjax(fcom.makeUrl(controller, 'charge', [orderId]), data, function(t) {
                    if ('undefined' != typeof t.redirectUrl) {
                        window.location = t.redirectUrl;
                    } else if (1 > t.status) { 
                        $.systemMessage(t.msg, 'alert--danger', false);
                        return;
                    } else {
                        $(paymentForm).html(t.html);
                        $(".btnFields-js").html(fcom.getLoader());
                        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
                    }
                });
            };

            addNewCard = function(orderId) {
                $(paymentForm).html(fcom.getLoader());
                fcom.ajax(fcom.makeUrl(controller, 'addCardForm', [orderId]), '', function(t) {
                    $(paymentForm).html(t).removeClass('p-0');
                });
            };

            removeCard = function(cardId) {
                if (!confirm(langLbl.confirmDelete)) {
                    return false;
                };
                var data = 'cardId=' + cardId;
                fcom.ajax(fcom.makeUrl(controller, 'removeCard', []), data, function(t) {
                    t = $.parseJSON(t);
                    if (1 > t.status) {
                        $.mbsmessage(t.msg, false, 'alert--danger');
                        return false;
                    }
                    $.mbsmessage(t.msg, false, 'alert--success');
                    loadPaymentSummary();
                });
            };
        })();
        $(document).ready(function() {
            <?php if (empty($savedCards)) { ?>
                addNewCard('<?php echo $orderInfo["id"]; ?>');
            <?php } ?>
            $(document).on("click", ".cancelCardForm-js", function(e){
                e.preventDefault();
                loadPaymentSummary();
            });
        });
    </script>
<?php } else { ?>
    <script type="text/javascript">
        var publishable_key = '<?php echo $settings[$liveMode . 'publishable_key']; ?>';
        var stripe = Stripe(publishable_key);
        var clientSecret = '<?php echo $clientSecret; ?>';
        stripe.confirmCardPayment(clientSecret, {
            payment_method: "<?php echo $sourceId; ?>"
        }).then(function(result) {
            if (result.error) {
                // PaymentIntent client secret was invalid
                location.href = '<?php echo $cancelBtnUrl; ?>';
            } else {
                if (result.paymentIntent.status === 'succeeded') {
                    $.mbsmessage(langLbl.paymentSucceeded, true, 'alert--success');
                    setTimeout(function(){
                        location.href = '<?php echo UrlHelper::generateFullUrl('custom', 'paymentSuccess', array($orderId)); ?>';
                    }, 1000);
                } else if (result.paymentIntent.status === 'payment_failed' || result.paymentIntent.status === 'canceled') {
                    // Authentication failed, prompt the customer to enter another payment method
                    location.href = '<?php echo UrlHelper::generateUrl('custom', 'paymentFailed'); ?>';
                }
            }
        });
    </script>
<?php } ?>