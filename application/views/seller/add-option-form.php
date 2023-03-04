<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
if (!empty($translatorSubscriptionKey)) { ?> 
    <div class="row justify-content-end"> 
        <div class="col-auto mb-4">
            <input class="btn btn-brand" 
                type="button" 
                value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                onClick="autofillLangData($(this), $('form#frmOptions'))"
                data-action="<?php echo UrlHelper::generateUrl('Seller', 'getTranslatedOptionData'); ?>">
        </div>
    </div>
<?php }
$frmOptions->setFormTagAttribute('class', 'form form_horizontal');
$frmOptions->developerTags['colClassPrefix'] = 'col-md-';
$frmOptions->developerTags['fld_default_col'] = 6;
$frmOptions->setFormTagAttribute('onsubmit', 'submitOptionForm(this); return(false);');

$btnSubmit = $frmOptions->getField('btn_submit');
$btnSubmit->setFieldTagAttribute('class', "btn btn-brand");
echo $frmOptions->getFormHtml();
?>
<script type="text/javascript">
$(document).ready(function(){
	fcom.resetFaceboxHeight();
});
</script>
