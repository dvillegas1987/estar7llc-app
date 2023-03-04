<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<?php
$frm->setFormTagAttribute('action', UrlHelper::generateUrl('TransferBankPay', 'send', array($orderInfo['id'])));
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-12 col-xs-';
$frm->developerTags['fld_default_col'] = 12;
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
            <ul class="transfer-payment-detail mt-4">
                <li>
                    <i class="icn">
                        <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bussiness-name" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bussiness-name"></use>
                        </svg>
                    </i>
                    <div class="lable">
                        <h6><?php echo Labels::getLabel('LBL_BUSSINESS_NAME', $siteLangId); ?></h6>
                        <?php echo $settings['business_name']; ?>
                    </div>

                </li>
                <li>
                    <i class="icn">
                        <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-name" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-name"></use>
                        </svg>
                    </i>
                    <div class="lable">
                        <h6><?php echo Labels::getLabel('LBL_BANK_NAME', $siteLangId); ?></h6>
                        <?php echo $settings['bank_name']; ?>
                    </div>

                </li>
                <li>
                    <i class="icn">
                        <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-branch" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-branch"></use>
                        </svg>
                    </i>
                    <div class="lable">
                        <h6><?php echo Labels::getLabel('LBL_BANK_BRANCH', $siteLangId); ?></h6>
                        <?php echo $settings['bank_branch']; ?>
                    </div>

                </li>
                <li>
                    <i class="icn">
                        <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#account" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#account"></use>
                        </svg>
                    </i>
                    <div class="lable">
                        <h6><?php echo Labels::getLabel('LBL_ACCOUNT_#', $siteLangId); ?></h6>
                        <?php echo $settings['account_number']; ?>
                    </div>

                </li>
                <li>
                    <i class="icn">
                        <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#ifsc" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#ifsc"></use>
                        </svg>
                    </i>
                    <div class="lable">
                        <h6><?php echo Labels::getLabel('LBL_IFSC_/_MICR', $siteLangId); ?></h6>
                        <?php echo $settings['ifsc']; ?>
                    </div>

                </li>
                <li>
                    <i class="icn">
                        <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#routing" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#routing"></use>
                        </svg>
                    </i>
                    <div class="lable">
                        <h6><?php echo Labels::getLabel('LBL_ROUTING_#', $siteLangId); ?></h6>
                        <?php echo $settings['routing']; ?>
                    </div>

                </li>
                <li class="notes">
                    <i class="icn">
                        <svg class="svg">
                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-notes" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/bank.svg#bank-notes"></use>
                        </svg>
                    </i>
                    <div class="lable">
                        <h6><?php echo Labels::getLabel('LBL_OTHER_NOTES', $siteLangId); ?></h6>
                        <?php echo $settings['bank_notes']; ?>
                    </div>
                </li>
            </ul>

            <?php
            if (!isset($error)) :
                $frm->setFormTagAttribute('onsubmit', 'confirmPayment(this); return(false);');
                $frm->setFormTagAttribute('class', 'form form--payment');
                ?>
                <?php echo $frm->getFormTag(); ?>
                <div class="payable-form__body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('opayment_method')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('opayment_method'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('opayment_gateway_txn_id')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('opayment_gateway_txn_id'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('opayment_amount')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('opayment_amount'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="field-set">
                                <div class="caption-wraper">
                                    <label class="field_label"><?php echo $frm->getField('opayment_comments')->getCaption(); ?></label>
                                </div>
                                <div class="field-wraper">
                                    <div class="field_cover">
                                        <?php echo $frm->getFieldHtml('opayment_comments'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php echo $frm->getFieldHtml('opayment_order_id'); ?>
                    <?php echo $frm->getExternalJs(); ?>
                <?php else : ?>
                    <div class="alert alert--danger"><?php echo $error ?></div>
                <?php endif; ?>
                <div id="ajax_message"></div>
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

            </form>
            <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-4"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $paymentAmount); ?> </p>
            <?php } ?>
        </div>
    </div>
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
        $("input[type='submit']").val(langLbl.processing);
        var data = fcom.frmData(frm);
        var action = me.attr('action');
        fcom.ajax(action, data, function (t) {
            try {
                var json = $.parseJSON(t);
                var el = $('#ajax_message');
                if (json['error']) {
                    el.html('<div class="alert alert--danger">' + json['error'] + '<div>');
                }
                if (json['redirect']) {
                    $(location).attr("href", json['redirect']);
                }
            } catch (exc) {
                console.log(t);
            }
        });
    };
</script>