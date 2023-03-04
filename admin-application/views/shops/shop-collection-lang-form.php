<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (isset($scollection_id) && $scollection_id > 0) {
    $scollection_id = $scollection_id;
} else {
    $scollection_id = 0;
}
$langFld = $shopColLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "editShopCollectionLangForm(" . $shop_id . ", " . $scollection_id . ", this.value);");
?>
<ul class="tabs_nav tabs_nav--internal">
    <li>
        <a onclick="getShopCollectionGeneralForm(<?php echo $shop_id; ?>, <?php echo $scollection_id; ?>);" href="javascript:void(0)">
            <?php echo Labels::getLabel('TXT_GENERAL', $adminLangId);?>
        </a>
    </li>
    <li class="<?php echo (0 == $scollection_id) ? 'fat-inactive' : ''; ?>">
        <a class="active" href="javascript:void(0);">
            <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
        </a>
    </li>
    <li>
        <a onclick="sellerCollectionProducts(<?php echo $scollection_id ?>,<?php echo $shop_id; ?>)" href="javascript:void(0);">
            <?php echo Labels::getLabel('TXT_LINK', $adminLangId);?>
        </a>
    </li>
    <li> 
        <a onclick="collectionMediaForm(<?php echo $shop_id; ?>,<?php echo $scollection_id ?>)" href="javascript:void(0);"> <?php echo Labels::getLabel('TXT_MEDIA', $adminLangId);?> </a>
    </li>
</ul>
<div class="tabs_panel_wrap">
    <?php
    $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
    $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
    if (!empty($translatorSubscriptionKey) && $langId != $siteDefaultLangId) { ?> 
        <div class="row justify-content-end"> 
            <div class="col-auto mb-4">
                <input class="btn btn-brand" 
                    type="button" 
                    value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                    onClick="editShopCollectionLangForm(<?php echo $shop_id; ?>, <?php echo $scollection_id; ?>, <?php echo $langId; ?>, 1)">
            </div>
        </div>
    <?php } ?> 
    <div class="form__subcontent">
        <?php
            $shopColLangFrm->setFormTagAttribute('class', 'form form_horizontal web_form layout--'.$formLayout);
            $shopColLangFrm->setFormTagAttribute('onsubmit', 'setupShopCollectionlangForm(this); return(false);');
            $shopColLangFrm->developerTags['colClassPrefix'] = 'col-md-';
            $shopColLangFrm->developerTags['fld_default_col'] = 12;
            echo $shopColLangFrm->getFormHtml(); ?>
    </div>
</div>
