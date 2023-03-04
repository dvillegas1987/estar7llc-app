<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'web_form form_horizontal');

$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 8;
$paymentMethods = User::getAffiliatePaymentMethodArr($adminLangId);
$payoutPlugins = Plugin::getNamesByType(Plugin::TYPE_PAYOUTS, $adminLangId);

$pluginKeyName = '';
if (!in_array($withdrawal_payment_method, array_keys($paymentMethods)) && in_array($withdrawal_payment_method, array_keys($payoutPlugins))) {
    $pluginKeyName = '"' . Plugin::getAttributesById($withdrawal_payment_method, 'plugin_code') . '"';
}
$frm->setFormTagAttribute('onsubmit', 'setupStatus(this,' . $pluginKeyName . '); return(false);');
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Update_Status_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">	
        <div class="border-box border-box--space">
            <?php echo $frm->getFormHtml(); ?>
        </div>	
    </div>
</section>
<script>
    var transactionApprovedStatus = <?php echo Transactions::WITHDRAWL_STATUS_APPROVED ?>;
</script>