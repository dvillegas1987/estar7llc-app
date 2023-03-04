<div class="tabs  align-items-center">
    <?php require_once(CONF_THEME_PATH.'_partial/seller/customCatalogProductNavigationLinks.php'); ?>
</div>
<div class="card">
    <div class="card-body ">
        <div class="row">
            <div class="col-md-12">
                <div class="">
                <?php
                    $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                    $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                    if (!empty($translatorSubscriptionKey) && $product_lang_id != $siteDefaultLangId) { ?> 
                        <div class="row justify-content-end"> 
                            <div class="col-auto mb-4">
                                <input class="btn btn-brand" 
                                    type="button" 
                                    value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                                    onClick="customCatalogProductLangForm(<?php echo $preqId; ?>, <?php echo $product_lang_id; ?>, 1)">
                            </div>
                        </div>
                    <?php } ?>
                    <?php
                    //$customProductLangFrm->setFormTagAttribute('onsubmit','setUpCustomSellerProductLang(this); return(false);');
                    $customProductLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--' . $formLayout);
                    $customProductLangFrm->developerTags['colClassPrefix'] = 'col-lg-4 col-md-';
                    $customProductLangFrm->developerTags['fld_default_col'] = 4;
    
                    $fld = $customProductLangFrm->getField('product_description');
                    $fld->setWrapperAttribute('class', 'col-lg-8');
                    $fld->developerTags['col'] = 8;
                    
                    $langFld = $customProductLangFrm->getField('lang_id');
                    $langFld->setfieldTagAttribute('onChange', "customCatalogProductLangForm(" . $preqId . ", this.value);");
                    echo $customProductLangFrm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
