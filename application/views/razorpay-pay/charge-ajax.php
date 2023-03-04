<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$frm->developerTags['fld_default_col'] = 12;
$btn = $frm->getField('btn_submit');
if (null != $btn) {
    $btn->developerTags['noCaptionTag'] = true;
    $btn->setFieldTagAttribute('class', "btn btn-brand btn-wide");
    $btn->setFieldTagAttribute('onclick', "razorpaySubmit(this);");
}
if (!isset($error)) { ?>
    <div class="text-center">
        <p><?php echo Labels::getLabel('LBL_PROCEED_TO_PAYMENT_?', $siteLangId); ?></p>
        <?php echo $frm->getFormHtml(); ?>
    </div>
<?php
} else { ?>
    <div class="alert alert--danger"><?php echo $error; ?></div>
<?php }

if (!FatUtility::isAjaxCall()) { ?>
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
            system_order_id: "<?php echo $orderInfo["id"]; ?>"
        },
        handler: function(transaction) {
            document.getElementById('razorpay_payment_id').value = transaction.razorpay_payment_id;
            document.getElementById('razorpay-form').submit();
        }
    };
    var razorpay_submit_btn, razorpay_instance;

    function razorpaySubmit(el) {
        if (typeof Razorpay == 'undefined') {
            setTimeout(razorpaySubmit, 200);
            if (!razorpay_submit_btn && el) {
                razorpay_submit_btn = el;
                el.disabled = true;
                el.value = 'Please wait...';
            }
        } else {
            if (!razorpay_instance) {
                razorpay_instance = new Razorpay(razorpay_options);
                if (razorpay_submit_btn) {
                    razorpay_submit_btn.disabled = false;
                    razorpay_submit_btn.value = $(el).data('value');
                }
            }
            razorpay_instance.open();
        }
    }
</script>