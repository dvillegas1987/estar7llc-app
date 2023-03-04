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
            <?php
            if (!isset($error)) :
                ?>
                <?php echo $frm->getFormTag(); ?>
                <div class="payable-form__body">
                    <div class="row">
                        <div class="col-md-12">
                            <p><?php echo Labels::getLabel('MSG_CONFIRM_TO_PROCEED_FOR_PAYMENT_?', $siteLangId); ?></p>
                            <?php echo $frm->getFieldHtml('razorpay_payment_id'); ?>
                            <?php echo $frm->getFieldHtml('merchant_order_id'); ?>
                        </div>
                        <div class="gap"></div>
                    </div>
                </div>
                <div class="payable-form__footer">
                    <div class="row">
                        <div class="col-md-6">                                    
                            <?php
                            $btn = $frm->getField('btn_submit');
                            $btn->addFieldTagAttribute('onclick', 'razorpaySubmit(this)');
                            $btn->addFieldTagAttribute('class', 'btn btn-secondary');
                            $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
                            echo $frm->getFieldHtml('btn_submit');
                            ?> 
                        </div>
                        <div class="col-md-6 d-md-block d-none">
                            <?php if (FatUtility::isAjaxCall()) { ?>
                                <a href="javascript:void(0);" onclick="loadPaymentSummary()" class="btn btn-outline-brand">
                                    <?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?>
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo $cancelBtnUrl; ?>" class="btn btn-outline-gray"><?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?></a>
                            <?php } ?>                        
                        </div>
                    </div>  
                </div>
                </form>
            <?php else : ?>
                <div class="alert alert--danger"><?php echo $error ?></div>
            <?php endif; ?>
            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
            <?php } ?>
        </div>
    </div>
</section>
<?php if (!FatUtility::isAjaxCall()) { ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<?php } ?>
<script>
    var razorpay_options = {
        key: "<?php echo $paymentSettings['merchant_key_id']; ?>",
        amount: "<?php echo $paymentAmount * 100; ?>",
        name: "<?php echo $orderInfo["site_system_name"]; ?>",
        description: "<?php echo sprintf(Labels::getLabel('MSG_Order_Payment_Gateway_Description', $siteLangId), $orderInfo["site_system_name"], $orderInfo['invoice']) ?>",
        netbanking: true,
        currency: "<?php echo $systemCurrencyCode; ?>",
        prefill: {
            name: "<?php echo $orderInfo["customer_name"]; ?>",
            email: "<?php echo $orderInfo["customer_email"]; ?>",
            contact: "<?php echo $orderInfo["customer_phone"]; ?>"
        },
        notes: {
            system_order_id: "<?php echo $orderInfo["id"];?>"
        },
        handler: function (transaction) {
            document.getElementById('razorpay_payment_id').value = transaction.razorpay_payment_id;
            document.getElementById('razorpay-form').submit();
        }
    };
    var razorpay_submit_btn, razorpay_instance;

    function razorpaySubmit(el) {
        if (typeof Razorpay == 'undefined') {
            setTimeout(razorpaySubmit, 200);
            if (razorpay_submit_btn == 'undefined' && el) {
                razorpay_submit_btn = el;
                el.disabled = true;
                $(el).data('value',$(el).val());
                el.value = $(el).data('processing-text'); 
            
            }
        } else {
            if (!razorpay_instance) {
                razorpay_instance = new Razorpay(razorpay_options);           
                if (razorpay_submit_btn) {                  
                    razorpay_submit_btn.disabled = false;
                    razorpay_submit_btn.value = $(el).data('value')
                }
            }
            razorpay_instance.open();
        }        
    }
</script>