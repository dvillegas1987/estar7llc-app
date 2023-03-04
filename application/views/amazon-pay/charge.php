<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
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
            <div class="payable-form__body" id="paymentFormElement-js">
                <p id="paymentStatus"></p>
                <?php
                if (isset($error))
                    echo '<div class="alert alert--danger"><p>' . $error . '</p></div>';
                if (isset($success))
                    echo '<div class="alert alert--success" ><p>Your payment has been successfull.</p></div>';
                if (strlen($orderId) > 0 && $orderInfo["order_payment_status"] == Orders::ORDER_PAYMENT_PENDING) echo '<div class="text-center" style="margin-top:40px;" id="AmazonPayButton"></div>';
                ?>
            </div>  
            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
            <?php } ?>
        </div>
    </div>
</section>
<?php
if (isset($amazon) && strlen($orderId) > 0 && $orderInfo["order_payment_status"] == Orders::ORDER_PAYMENT_PENDING) {
    if (strlen($amazon['merchant_id']) > 0 && strlen($amazon['access_key']) > 0 && strlen($amazon['secret_key']) > 0 && strlen($amazon['client_id']) > 0 && strlen(FatApp::getConfig('CONF_TRANSACTION_MODE', FatUtility::VAR_STRING, '0'))) {
?>
        <?php if (!FatUtility::isAjaxCall()) { ?>
            <script type='text/javascript' src='https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js'></script>
        <?php } ?>
        <script type="text/javascript">
            var loginReady = false;
            window.onAmazonLoginReady = function() {
                if (true === loginReady) {
                    return;
                }
                amazon.Login.setClientId('<?php echo $amazon['client_id']; ?>');
                amazon.Login.setUseCookie(true);
                loginReady = true;
            };
            if (false === loginReady) {
                window.onAmazonLoginReady();
            }
        </script>
        <script type="text/javascript">
            var authRequest;
            OffAmazonPayments.Button("AmazonPayButton", '<?php echo $amazon['merchant_id']; ?>', {
                type: "PwA",
                authorization: function() {
                    loginOptions = {
                        scope: "profile postal_code payments:widget payments:shipping_address",
                        popup: true
                    };
                    authRequest = amazon.Login.authorize(loginOptions, "<?php echo UrlHelper::generateUrl('AmazonPay', 'charge', array($orderId), CONF_WEBROOT_URL, false) ?>");
                },
                onError: function(error) {
                    console.log(error);
                    amazon.Login.logout();
                    document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
                    window.location = '<?php echo UrlHelper::generateUrl('AmazonPay', 'charge', array($orderId), CONF_WEBROOT_URL) ?>';
                }
            });
        </script>
<?php
    }
}
