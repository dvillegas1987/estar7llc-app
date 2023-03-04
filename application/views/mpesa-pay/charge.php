<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php $frm->setFormTagAttribute('onsubmit', 'confirmPayment(this); return(false);'); ?>
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
            <?php echo $frm->getFormTag(); ?>
            <div class="payable-form__body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="field-set">
                            <div class="caption-wraper">
                                <label class="field_label"><?php echo Labels::getLabel('LBL_PHONE_NUMBER', $siteLangId); ?></label>
                            </div>
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php echo $frm->getFieldHtml('customerPhone'); ?>
                                    <span class='form-text text-muted'><?php echo Labels::getLabel('LBL_MSISDN_12_DIGITS_MOBILE_NUMBER', $siteLangId); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="payable-form__footer">
                <div class="row">
                    <div class="col-md-6">                                    
                        <?php
                        $btn = $frm->getField('btn_submit');
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
            <?php echo $frm->getExternalJs(); ?>
            </form>
            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
            <?php } ?>
        </div>
    </div>        
</section>
<script>
    var confirmPayment = function (frm) {
        var me = $(frm);
        if (me.data('requestRunning')) {
            return;
        }
        if (!me.validate())
            return;
        var btnEle = $("input[type='submit']");
        var btnText = btnEle.val();
        btnEle.val(langLbl.processing).attr('disabled', 'disabled');
        $.mbsmessage(langLbl.processing, false, 'alert--process');
        var data = fcom.frmData(frm);
        var action = me.attr('action');
        fcom.ajax(action, data, function (t) {
            btnEle.val(btnText).removeAttr('disabled');
            try {
                var json = $.parseJSON(t);
                if (1 > json.status) {
                    $.mbsmessage(json.msg, false, 'alert--danger');
                    return false;
                }
                $.mbsmessage(json.msg, false, 'alert--success');
                if (json['redirect']) {
                    $(location).attr("href", json['redirect']);
                }
            } catch (exc) {
                console.log(t);
            }
        });
    };
</script>