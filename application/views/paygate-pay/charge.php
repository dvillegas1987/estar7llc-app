<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('class', 'd-none');
if (false === $processRequest) {
    $frm->setFormTagAttribute('onsubmit', 'confirmOrder(this); return(false);');
}
$frm->setFormTagAttribute('id', 'paymentForm-js');
$btn = $frm->getField('btn_submit');
if (null != $btn) {
    $btn->setFieldTagAttribute('class', "d-none");
}
?>
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
                       <h6><?php echo Labels::getLabel('LBL_REDIRECTING_TO_PAYMENT_PAGE...', $siteLangId); ?></h6>
                        <?php echo $frm->getFormHtml(); ?>
                    </div>  
                    <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
                    <?php } ?>
            </div>
        </div>
</section>
<script type="text/javascript">
    $("form#paymentForm-js").submit();
    function confirmOrder(frm) {
        var data = fcom.frmData(frm);
        var action = $(frm).attr('action');
        var submitBtn = $("form#paymentForm-js input[type='submit']");
        $.mbsmessage(langLbl.processing, false, 'alert--process alert');
        submitBtn.attr('disabled', 'disabled');        
        fcom.ajax(action, data, function(res) {
            var json = $.parseJSON(res);
            if (1 > json.status) {
                submitBtn.removeAttr('disabled');
                $.mbsmessage(json.msg, true, 'alert--danger');
                return false;
            }
            $("#paymentFormElement-js").replaceWith(json.html);
            $("#paymentFormElement-js").removeClass('text-center');
            $("form#paymentForm-js").submit();
        });
    }
</script>