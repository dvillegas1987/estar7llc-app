<?php defined('SYSTEM_INIT') or die('Invalid Usage');
if (!isset($error)) {
    $frm->setFormTagAttribute('onsubmit', 'sendPayment(this); return(false);');

    $frm->getField('cc_number')->addFieldTagAttribute('class', 'p-cards');
    $frm->getField('cc_number')->addFieldTagAttribute('id', 'cc_number');

    echo $frm->getFormTag(); ?>
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
        <div class="col-md-4">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Expiry_Month', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php
                        $fld = $frm->getField('cc_expire_date_month');
                        $fld->addFieldTagAttribute('id', 'cc_expire_date_month');
                        $fld->addFieldTagAttribute('class', 'ccExpMonth  combobox required');
                        echo $fld->getHtml();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Expiry_year', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php
                        $fld = $frm->getField('cc_expire_date_year');
                        $fld->addFieldTagAttribute('id', 'cc_expire_date_year');
                        $fld->addFieldTagAttribute('class', 'ccExpYear combobox required');
                        echo $fld->getHtml();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
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
    <div class="total-pay"><?php echo CommonHelper::displayMoneyFormat($paymentAmount) ?>
        <small>(<?php echo Labels::getLabel('LBL_Total_Payable', $siteLangId); ?>)</small>
    </div>
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
                        $btn->addFieldTagAttribute('class', 'btn btn-brand');
                        $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('LBL_PLEASE_WAIT..', $siteLangId));
                        echo $frm->getFieldHtml('btn_submit'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
    <?php echo $frm->getExternalJs(); ?>
<?php } else { ?>
    <div class="alert alert--danger">
        <?php echo $error ?>
    </div>
<?php } ?>
<div id="ajax_message"></div>