<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
if (!empty($translatorSubscriptionKey)) { ?>
    <div class="row justify-content-end">
        <div class="col-auto mb-4">
            <input class="btn btn-brand"
                type="button"
                value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $langId); ?>"
                onClick="autofillLangData($(this), $('form#frmOptionValues'))"
                data-action="<?php echo UrlHelper::generateUrl('OptionValues', 'getTranslatedData'); ?>">
        </div>
    </div>
<?php }
$optionValueFrm->setFormTagAttribute('class', 'form form--horizontal');
$optionValueFrm->setFormTagAttribute('onsubmit', 'setUpOptionValues(this); return(false);');
$optionValueFrm->developerTags['colClassPrefix'] = 'col-md-';
$optionValueFrm->developerTags['fld_default_col'] = 6;

$btnSubmit = $optionValueFrm->getField('btn_submit');
$btnSubmit->developerTags['col'] = '3';
$btnSubmit->addWrapperAttribute('class', 'col-6');
$btnSubmit->setFieldTagAttribute('class', 'btn btn-block btn-brand');

$btnClear = $optionValueFrm->getField('btn_clear');
$btnClear->developerTags['col'] = '3';
$btnClear->addWrapperAttribute('class', 'col-6');
$btnClear->setFieldTagAttribute('class', 'btn btn-block btn-outline-brand');
?><div class="box__head">
<h4><?php echo isset($optionName) ? Labels::getLabel('LBL_CONFIGURE_OPTION_VALUES_FOR', $langId).' '.$optionName : Labels::getLabel('LBL_CONFIGURE_OPTION_VALUES', $langId); ?></h4>
</div>
<div class="box__body">
    <div class="form__subcontent">
        <?php
        echo $optionValueFrm->getFormHtml();
        ?>
    </div>
    <div id="optionValuesListing"></div>
</div>
