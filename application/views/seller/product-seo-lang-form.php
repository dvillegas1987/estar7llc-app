<?php /*require_once('sellerProductSeoTop.php');*/ ?>
<h5 class="card-title mb-2"><?php echo SellerProduct::getProductDisplayTitle($selprodId, $siteLangId, false); ?></h5>
<div class="form__subcontent">
    <?php
    $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
    $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
    if (!empty($translatorSubscriptionKey) && $selprod_lang_id != $siteDefaultLangId) { ?>
        <div class="row justify-content-end">
            <div class="col-auto mb-4">
                <input class="btn btn-brand"
                    type="button"
                    value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>"
                    onClick="editProductMetaTagLangForm(<?php echo $selprodId; ?>, <?php echo $selprod_lang_id; ?>)">
            </div>
        </div>
    <?php } ?>
    <?php
    $productSeoLangForm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
    $productSeoLangForm->setFormTagAttribute('onsubmit', 'setupProductLangMetaTag(this, 0); return(false);');
    $productSeoLangForm->developerTags['colClassPrefix'] = 'col-lg-';
    $productSeoLangForm->developerTags['fld_default_col'] = 12;
    $langFld = $productSeoLangForm->getField('lang_id');
    $langFld->setfieldTagAttribute('onChange', "editProductMetaTagLangForm(" . $selprodId . ", this.value);");
    $mKeywordFld = $productSeoLangForm->getField('meta_keywords');
    $mKeywordFld->setfieldTagAttribute('class', "txtarea-height");
    $mDescFld = $productSeoLangForm->getField('meta_description');
    $mDescFld->setfieldTagAttribute('class', "txtarea-height");
    $mtagsFld = $productSeoLangForm->getField('meta_other_meta_tags');
    $mtagsFld->setfieldTagAttribute('class', "txtarea-height");

    $nextBtn = $productSeoLangForm->getField('btn_next');
    $nextBtn->developerTags['col'] = 4;
    $nextBtn->setfieldTagAttribute('class', "btn btn-brand btn-block");
    $nextBtn->setfieldTagAttribute('onClick', "setupProductLangMetaTag(this.closest('form'), 0)");
    $nextBtn->setWrapperAttribute('class', "col-6");
    $nextBtn->developerTags['noCaptionTag'] = true;

    $exitBtn = $productSeoLangForm->getField('btn_exit');
    $exitBtn->setfieldTagAttribute('class', "btn btn-outline-brand btn-block");
    $exitBtn->setfieldTagAttribute('onClick', "setupProductLangMetaTag(this.closest('form'), 1)");
    $exitBtn->developerTags['col'] = 4;
    $exitBtn->setWrapperAttribute('class', "col-6");
    $exitBtn->developerTags['noCaptionTag'] = true;

    end($languages);
    if (key($languages) == $selprod_lang_id) {
        $nextBtn->value = Labels::getLabel("LBL_Save", $siteLangId);
        $nextBtn->setfieldTagAttribute('class', "btn btn-outline-brand btn-block");
        $exitBtn->setfieldTagAttribute('class', "btn btn-brand btn-block");
    }
    echo $productSeoLangForm->getFormHtml(); ?>
</div>
