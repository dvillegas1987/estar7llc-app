<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$frm->setFormTagAttribute('class', 'web_form');
$frm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-';
$frm->developerTags['fld_default_col'] = 12;
echo $frm->getFormHtml(); 

