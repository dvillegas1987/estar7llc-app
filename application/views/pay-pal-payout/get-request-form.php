<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'frmPlugins');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setupPluginForm(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 3;

$amountFld = $frm->getField('amount');
$amountFld->developerTags['col'] = 2;

$submitBtnFld = $frm->getField('btn_submit');
$submitBtnFld->developerTags['col'] = 2;
$submitBtnFld->value = Labels::getLabel('LBL_Save', $siteLangId);
$submitBtnFld->setFieldTagAttribute('class', 'btn btn-brand btn-block');  

$frm->addButton("", "btn_cancel", Labels::getLabel("LBL_Cancel", $siteLangId));
$cancelBtnFld = $frm->getField('btn_cancel');
$cancelBtnFld->setFieldTagAttribute('onClick', 'closeForm()');
$cancelBtnFld->setFieldTagAttribute('class', 'btn btn-outline-brand btn-block');
$cancelBtnFld->developerTags['col'] = 2;
//$submitBtnFld->attachField($cancelBtnFld);
?>
<div class="card-header">
    <h5 class="card-title"><?php echo Labels::getLabel('LBL_PayPal_Payout_Form', $siteLangId); ?></h5>
</div>
<div class="card-body ">
    <?php echo $frm->getFormHtml(); ?>
</div>