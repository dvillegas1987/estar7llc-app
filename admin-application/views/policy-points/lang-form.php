<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--' . $formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLangPolicyPoint(this); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;

$langFld = $langFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "editPolicyPointLangForm(" . $ppointId . ", this.value);");

?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Policy_Point_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">

            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Policy_Point_Setup',$adminLangId);?>
                </h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);"
                                onclick="editPolicyPointForm(<?php echo $ppointId ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
                        <li class="<?php echo (0 == $ppointId) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);" <?php echo (0 < $ppointId) ? "onclick='editPolicyPointLangForm(" . $ppointId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap">
                    <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="editPolicyPointLangForm(<?php echo $ppointId; ?>, <?php echo $lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?> 
                        <div class="tabs_panel">
                            <?php echo $langFrm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>