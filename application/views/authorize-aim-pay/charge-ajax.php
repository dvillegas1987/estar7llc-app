<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!isset($error)) :
    $frm->setFormTagAttribute('onsubmit', 'sendPayment(this); return(false);');
    $frm->getField('cc_number')->addFieldTagAttribute('class', 'p-cards');
    $frm->getField('cc_number')->addFieldTagAttribute('id', 'cc_number');
?>
    <?php echo $frm->getFormTag(); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_ENTER_CREDIT_CARD_NUMBER', $siteLangId); ?></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover"> <?php echo $frm->getFieldHtml('cc_number'); ?> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_CARD_HOLDER_NAME', $siteLangId); ?></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover"> <?php echo $frm->getFieldHtml('cc_owner'); ?> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="caption-wraper">
                    <label class="field_label"> <?php echo Labels::getLabel('LBL_CREDIT_CARD_EXPIRY', $siteLangId); ?> </label>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php
                                    $fld = $frm->getField('cc_expire_date_month');
                                    $fld->addFieldTagAttribute('id', 'ccExpMonth');
                                    $fld->addFieldTagAttribute('class', 'ccExpMonth  combobox required');
                                    echo $fld->getHtml();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div class="field-set">
                            <div class="field-wraper">
                                <div class="field_cover">
                                    <?php
                                    $fld = $frm->getField('cc_expire_date_year');
                                    $fld->addFieldTagAttribute('id', 'ccExpYear');
                                    $fld->addFieldTagAttribute('class', 'ccExpYear  combobox required');
                                    echo $fld->getHtml();
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"><?php echo Labels::getLabel('LBL_CVV_SECURITY_CODE', $siteLangId); ?></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover"> <?php echo $frm->getFieldHtml('cc_cvv'); ?> </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="total-pay"><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?> <small>(<?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?>)</small> </div>
        <div class="row">
            <div class="col-md-12">
                <div class="field-set">
                    <div class="caption-wraper">
                        <label class="field_label"></label>
                    </div>
                    <div class="field-wraper">
                        <div class="field_cover">
                            <?php
                            $btn = $frm->getField('btn_submit');
                            $btn->addFieldTagAttribute('class', 'btn btn-secondary');
                            $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
                            echo $frm->getFieldHtml('btn_submit'); ?>
                            <a href="<?php echo $cancelBtnUrl; ?>" class="btn btn-outline-brand"><?php echo Labels::getLabel('LBL_Cancel', $siteLangId); ?></a>
                            <span id="load"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php echo $frm->getExternalJs(); ?>
<?php else : ?>
    <div class="alert alert--danger">
        <h5><?php echo $error ?></h5>
    </div>
<?php endif; ?>
<div id="ajax_message"></div>