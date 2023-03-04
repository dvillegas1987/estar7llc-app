<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$frmOptionsLang->setFormTagAttribute('class', 'web_form form_horizontal');
$frmOptionsLang->setFormTagAttribute('onsubmit', 'setupOptionsLang(this); return(false);');
$frmOptionsLang->developerTags['colClassPrefix'] = 'col-md-';
$frmOptionsLang->developerTags['fld_default_col'] = 6;

$langFld = $frmOptionsLang->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "addOptionLangForm(" . $option_id . ", this.value);");
?>
<section class="section">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Option_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Options_Setup',$adminLangId);?>
                </h1>
                <ul class="tabs_nav">
                    <li><a href="javascript:void(0);"
                            onclick="addOptionForm(<?php echo $option_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                    </li>
                    <li class="<?php echo (0 == $option_id) ? 'fat-inactive' : ''; ?>">
                        <a class="active" href="javascript:void(0);">
                            <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                        </a>
                    </li>
                </ul>
                <div class="sectionbody space">
                    <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $option_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="addOptionLangForm(<?php echo $option_id; ?>, <?php echo $option_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?> 
                    <div class=" border-box border-box--space">
                        <?php echo $frmOptionsLang->getFormHtml(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>