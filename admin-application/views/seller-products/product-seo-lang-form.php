<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFld = $productSeoLangForm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "editProductMetaTagLangForm(" . $metaId . ", this.value, '" . $metaType . "');");
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_SEO_CONTENT', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="border-box">
                    <div class="tabs_nav_container responsive">
                        <?php require_once('sellerProductSeoTop.php');?>
                    </div>
                    <div class="tabs_nav_container responsive">
                        <div class="tabs_panel_wrap">
                            <?php
                            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                            $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                            if (!empty($translatorSubscriptionKey) && $selprod_lang_id != $siteDefaultLangId) { ?> 
                                <div class="row justify-content-end"> 
                                    <div class="col-auto mb-4">
                                        <input class="btn btn-brand" 
                                            type="button" 
                                            value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                            onClick="editProductMetaTagLangForm(<?php echo $metaId; ?>, <?php echo $selprod_lang_id; ?>, 1)">
                                    </div>
                                </div>
                            <?php } ?> 
                            <div class="tabs_panel">
                                <?php
                                    $productSeoLangForm->setFormTagAttribute('class', 'web_form form--horizontal layout--' . $formLayout);
                                    $productSeoLangForm->setFormTagAttribute('onsubmit', 'setupProductLangMetaTag(this); return(false);');
                                    $productSeoLangForm->developerTags['colClassPrefix'] = 'col-md-';
                                    $productSeoLangForm->developerTags['fld_default_col'] = 8;
                                    //$customProductFrm->getField('option_name')->setFieldTagAttribute('class','mini');
                                    echo $productSeoLangForm->getFormHtml();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>