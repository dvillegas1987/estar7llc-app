<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('onsubmit', 'updateSettings(this); return(false);');
$frm->setFormTagAttribute('class', 'web_form');

$frm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
$frm->developerTags['fld_default_col'] = 6;
echo $frm->getFormHtml();

