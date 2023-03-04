<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('id', 'bindProducts');
$frm->setFormTagAttribute('onsubmit', 'setupProductsToBatch(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 2;

$btnFld = $frm->getField('btn_submit');
$btnFld->addFieldTagAttribute('class', 'btn btn-block btn-brand');
$btnFld->developerTags['noCaptionTag'] = true;

$btnFld = $frm->getField('btn_clear');
$btnFld->addFieldTagAttribute('class', 'btn btn-block btn-outline-brand');
$btnFld->addFieldTagAttribute('onClick', 'clearForm();');
$btnFld->developerTags['noCaptionTag'] = true;

$prodName = $frm->getField('product_name');
$prodName->developerTags['col'] = 4;

$prodCatFld = $frm->getField('google_product_category');
$prodCatFld->developerTags['col'] = 5;

$prodName = $frm->getField('abprod_age_group');
$prodName->developerTags['col'] = 3;

echo $frm->getFormHtml();
