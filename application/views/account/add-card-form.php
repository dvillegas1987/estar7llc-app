<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$frm->setFormTagAttribute('onsubmit', 'addNewCard(this); return(false);');
$frm->setFormTagAttribute('class', 'form form--normal');

$fld = $frm->getField('number');
$fld->addFieldTagAttribute('class', 'p-cards');
$fld->addFieldTagAttribute('id', 'cc_number');
$fld = $frm->getField('name');
$fld->addFieldTagAttribute('id', 'cc_owner');
$fld = $frm->getField('cvc');
$fld->addFieldTagAttribute('id', 'cc_cvv');

echo $frm->getFormTag(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_ENTER_CARD_NUMBER', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('number'); ?>
                    </div>
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
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('name'); ?>
                    </div>
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
							$fld = $frm->getField('exp_month');
							$fld->addFieldTagAttribute('id', 'cc_expire_date_month');
							$fld->addFieldTagAttribute('class', 'ccExpMonth  combobox required');
							echo $fld->getHtml(); ?>
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
							$fld = $frm->getField('exp_year');
							$fld->addFieldTagAttribute('id', 'cc_expire_date_year');
							$fld->addFieldTagAttribute('class', 'ccExpYear combobox required');
							echo $fld->getHtml(); ?>
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
                    <div class="field_cover">
                        <?php echo $frm->getFieldHtml('cvc'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="field-set">
                <div class="field-wraper">
                    <div class="field_cover">
                        <?php 
                            $btn = $frm->getField('btn_submit');
                            $btn->addFieldTagAttribute('data-processing-text', Labels::getLabel('L_Please_Wait..', $siteLangId));
                            $btn->addFieldTagAttribute('class', "btn btn-brand");
                            echo $frm->getFieldHtml('btn_submit');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php echo $frm->getExternalJs(); ?>