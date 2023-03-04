<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
$frm->setFormTagAttribute('class', 'form form--normal');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
?>
<div class="">
    <p><strong><?php echo sprintf(Labels::getLabel('LBL_Pay_using_Payment_Method', $siteLangId), $paymentMethod["plugin_name"]) ?>:</strong></p><br />
    <?php
    if (!isset($error)) {
        echo $frm->getFormHtml();
    }
    ?>
</div>
<script type="text/javascript">
    $("document").ready(function() {
        <?php if (isset($error)) { ?>
            $.systemMessage(<?php echo $error; ?>);
        <?php } ?>
    });

    function confirmOrder(frm) {
        var data = fcom.frmData(frm);
        var action = $(frm).attr('action')
        fcom.updateWithAjax(fcom.makeUrl('WalletPay', 'confirmOrder'), data, function(ans) {
            $(location).attr("href", action);
        });
    }
</script>
<?php
$siteKey = FatApp::getConfig('CONF_RECAPTCHA_SITEKEY', FatUtility::VAR_STRING, '');
$secretKey = FatApp::getConfig('CONF_RECAPTCHA_SECRETKEY', FatUtility::VAR_STRING, '');
if (!empty($siteKey) && !empty($secretKey) && in_array(strtolower($paymentMethod['plugin_code']), ['cashondelivery', 'payatstore'])) { ?>
    <script src='https://www.google.com/recaptcha/api.js?onload=googleCaptcha&render=<?php echo $siteKey; ?>'></script>
<?php } ?>