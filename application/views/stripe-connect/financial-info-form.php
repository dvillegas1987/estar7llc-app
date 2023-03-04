<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('class', 'form');
$frm->setFormTagAttribute('onsubmit', 'setupFinancialInfo(this); return(false);');
$frm->developerTags['colClassPrefix'] = 'col-md-';
$frm->developerTags['fld_default_col'] = 6;

$btnFld = $frm->getField('btn_submit');
$btnFld->addFieldTagAttribute('class', 'btn btn-brand');

$btnFld = $frm->getField('btn_clear');
$btnFld->addFieldTagAttribute('class', 'btn btn-outline-brand');
$btnFld->addFieldTagAttribute('onClick', 'clearFinancialInfoForm();');

echo $frm->getFormHtml();
