<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'payPalFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 4;
$fld = $frm->getField('ub_bank_address');
$fld = $frm->getField('btn_submit');
$fld->setFieldTagAttribute('class', "btn btn-brand");
$frm->setFormTagAttribute('onsubmit', 'setupPluginForm(this); return(false);');
?>
<div class="row">
    <div class="col-md-12">
        <?php echo $frm->getFormHtml();?>
    </div>
</div>
