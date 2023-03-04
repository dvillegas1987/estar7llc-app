<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'frmPlugins');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setupPluginForm(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 3;

$submitBtnFld = $frm->getField('btn_submit');

$frm->addButton("", "btn_cancel", Labels::getLabel("LBL_Cancel", $siteLangId));
$cancelBtnFld = $frm->getField('btn_cancel');
$cancelBtnFld->setFieldTagAttribute('onClick', 'closeForm()');
$cancelBtnFld->setFieldTagAttribute('class', 'btn-outline-brand');
$submitBtnFld->attachField($cancelBtnFld);
?>
<div class="card-header">
    <h5 class="card-title"><?php echo $identifier ?> <?php echo Labels::getLabel('LBL_Form', $siteLangId); ?></h5>
</div>
<div class="card-body ">
    <?php echo $frm->getFormHtml(); ?>
</div>