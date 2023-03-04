<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<section class="payment-section">
    <div class="payable-amount">
        <div class="payable-amount__head">
            <div class="payable-amount--header">              
                <?php $this->includeTemplate('_partial/paymentPageLogo.php', array('siteLangId' => $siteLangId)); ?>
            </div>
            <div class="payable-amount--decription">
                <h2><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?></h2>
                <p><?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?></p>
                <p><?php echo Labels::getLabel('LBL_Order_Invoice', $siteLangId); ?>: <?php echo $orderInfo["invoice"]; ?></p>
            </div>
        </div>
        <div class="payable-amount__body payment-from">
            <div class="payable-form__body">
                <p class='loading-js'><?php echo Labels::getLabel('MSG_LOADING_PAYMENT_OPTIONS...', $siteLangId); ?></p>
                <div id="paypal-buttons"></div>
            </div>
            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
            <?php } ?>
        </div>
    </div>
</section>
<?php if (!FatUtility::isAjaxCall()) { ?>
    <script type="text/javascript" src="<?php echo $externalLibUrl; ?>"></script>
<?php } ?>
<script type="text/javascript">
    function loadPayPalButtons() {
        //=== Render paypal Buttons
        paypal.Buttons({
            onError: function (err) {
                $.systemMessage(err.message, 'alert--danger', false);
                return;
            },
            style: {
                layout: "vertical"
            },
            //=== Call your server to create an order
            createOrder: function (data, actions) {
                $.mbsmessage(langLbl.requestProcessing, true, 'alert--process');
                return fetch(fcom.makeUrl('PaypalPay', 'createOrder', ['<?php echo $orderInfo['id']; ?>']), {
                    method: "POST",
                }).then(function (res) {
                    return res.json();
                }).then(function (data) {
                    if (!data.success && (data.message || data.msg)) {
                        var msg = typeof data.msg != 'undefined' ? data.msg : data.message;
                        $.mbsmessage(msg, true, 'alert--danger');
                        return;
                    }
                    $.mbsmessage.close();
                    return data.id;
                });
            },
            //=== Call your server to save the transaction
            onApprove: function (data, actions) {
                return fetch(fcom.makeUrl('PaypalPay', 'captureOrder', [data.orderID]), {
                    method: "POST",
                }).then(function (res) {
                    return res.json();
                }).then(function (data) {
                    $.mbsmessage(langLbl.requestProcessing, true, 'alert--process');
                    //=== Redirect to thank you/success page after saving transaction
                    $.ajax({
                        type: "POST",
                        url: fcom.makeUrl('PaypalPay', 'callback', ['<?php echo $orderInfo['id']; ?>']),
                        data: data,
                        dataType: 'json',
                        success: function (resp) {
                            if (1 > resp.status) {
                                $.mbsmessage(resp.msg, true, 'alert--danger');
                            } else {
                                $.mbsmessage(resp.msg, true, 'alert--success');
                                setTimeout(function () {
                                    window.location.href = resp.redirecUrl;
                                }, 100);
                            }
                        }
                    });
                });
            }
        }).render("#paypal-buttons");
    }

    $(document).ready(function () {
        loadPayPalButtons();
        setTimeout(function () {
            if ('' != $("#paypal-buttons").html()) {
                $(".loading-js").hide();
            }
        }, 1000);
    });
</script>