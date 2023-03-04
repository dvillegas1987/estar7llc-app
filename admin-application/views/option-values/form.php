<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
$optionValueFrm->setFormTagAttribute('class', 'web_form form_horizontal');

$optionValueFrm->setFormTagAttribute('onsubmit', 'setUpOptionValues(this); return(false);');
$optionValueFrm->developerTags['colClassPrefix'] = 'col-md-';
$optionValueFrm->developerTags['colClassPrefix'] = 'col-md-';
$optionValueFrm->developerTags['fld_default_col'] = 12;
?>
<div class="row mb-4 justify-content-between">
    <div class="col-md-8">
        <h3>
            <?php echo isset($optionName) ? Labels::getLabel('LBL_CONFIGURE_OPTION_VALUES_FOR', $adminLangId) . ' ' . $optionName : Labels::getLabel('LBL_CONFIGURE_OPTION_VALUES', $adminLangId); ?>
        </h3>
    </div>
    <?php if (!empty($translatorSubscriptionKey)) { ?>
        <div class="col-auto">
            <input class="btn btn-brand" type="button" value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" onClick="autofillLangData($(this), $('form#frmOptionValues'))" data-action="<?php echo UrlHelper::generateUrl('OptionValues', 'getTranslatedData'); ?>">
        </div>
    <?php } ?>
</div>
<div class="row">
    <div class="col-md-12">
        <?php echo $optionValueFrm->getFormHtml(); ?>
    </div>
</div>
<div id="optionValuesListing"></div>