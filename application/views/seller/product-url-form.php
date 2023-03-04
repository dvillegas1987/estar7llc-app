<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'prodBrand');
$frm->setFormTagAttribute('class', 'form form--horizontal');
$frm->setFormTagAttribute('onsubmit', 'setupProductUrl(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-lg-';
$frm->developerTags['fld_default_col'] = 12;
$btnSubmit = $frm->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
?>
<h5 class="card-title mb-2"><?php echo SellerProduct::getProductDisplayTitle($selprodId, $siteLangId, false); ?></h5>
<div class="form__subcontent">
    <?php echo $frm->getFormHtml();?>
</div>    