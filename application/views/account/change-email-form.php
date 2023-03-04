<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frm->setFormTagAttribute('id', 'changeEmailFrm');
$frm->setFormTagAttribute('class', 'form');
$frm->developerTags['colClassPrefix'] = 'col-xl-12 col-lg-12 col-md-';
$frm->developerTags['fld_default_col'] = 12;
$frm->setFormTagAttribute('autocomplete', 'off');
$frm->setFormTagAttribute('onsubmit', 'updateEmail(this); return(false);');

$fldSubmit = $frm->getField('btn_submit');
$fldSubmit->developerTags['noCaptionTag'] = true;
$fldSubmit->setFieldTagAttribute('class', "btn btn-brand btn-wide");

$str = '';
if (isset($canSendSms) && true == $canSendSms) {
    $str = '';
}

$fldSubmit->htmlAfterField = '<br/><small>' . Labels::getLabel('MSG_YOUR_EMAIL_WILL_NOT_CHANGE_UNTIL_YOU_VERIFY_YOUR_NEW_EMAIL_ADDRESS', $siteLangId) . '</small>';

echo $frm->getFormHtml();
