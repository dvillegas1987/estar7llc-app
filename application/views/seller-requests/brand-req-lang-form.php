<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$brandReqLangFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
$brandReqLangFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$brandReqLangFrm->developerTags['fld_default_col'] = 12;
$brandReqLangFrm->setFormTagAttribute('onsubmit', 'setupBrandReqLang(this); return(false);');
$brandFld = $brandReqLangFrm->getField('brand_name');
$brandFld->setFieldTagAttribute('onblur', 'checkUniqueBrandName(this,$("input[name=lang_id]").val(),'.$brandReqId.')');

$langFld = $brandReqLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "addBrandReqLangForm(" . $brandReqId . ", this.value);");

$submitFld = $brandReqLangFrm->getField('btn_submit');
$submitFld->setFieldTagAttribute('class', 'btn btn-brand');
$submitFld->developerTags['noCaptionTag'] = true;
?>
<div class="box__head">
    <h4><?php echo (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) ? Labels::getLabel('LBL_Request_New_Brand', $siteLangId) : Labels::getLabel('LBL_New_Brand', $siteLangId) ?></h4>
</div>

<div class="box__body">
    <div class="tabs">
        <ul>
            <li><a href="javascript:void(0)" onclick="addBrandReqForm(<?php echo $brandReqId ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
            <?php $inactive=($brandReqId==0) ? ' fat-inactive' : ''; ?>
            <li class="<?php echo (0 < $brandReqLangId) ? 'is-active' : ''; echo $inactive; ?>">
                <a href="javascript:void(0);">
                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                </a>
            </li>
            <li  class="<?php echo $inactive;?>" ><a href="javascript:void(0)"
                <?php if ($brandReqId > 0) {?>
                    onclick="brandMediaForm(<?php echo $brandReqId ?>);"
                <?php } ?>><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        </ul>
    </div>
    <div class="tabs__content form">
    <?php
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        if (!empty($translatorSubscriptionKey) && $brandReqLangId != $siteDefaultLangId) { ?> 
            <div class="row justify-content-end"> 
                <div class="col-auto mb-4">
                    <input class="btn btn-brand" 
                        type="button" 
                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                        onClick="addBrandReqLangForm(<?php echo $brandReqId; ?>, <?php echo $brandReqLangId; ?>, 1)">
                </div>
            </div>
        <?php } ?>
        <?php
        echo $brandReqLangFrm->getFormHtml();
        ?>
    </div>
</div>
