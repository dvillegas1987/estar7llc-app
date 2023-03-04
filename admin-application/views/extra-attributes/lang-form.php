<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$extraAttributeLangFrm->setFormTagAttribute('id', 'frmExtraAttributeLang');
$extraAttributeLangFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$extraAttributeLangFrm->setFormTagAttribute('onsubmit', 'setUpLang(this); return(false);');

$langFld = $extraAttributeLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "langForm(" . $eattribute_id . ", this.value);");
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Attribute_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addForm(<?php echo $eattribute_eattrgroup_id ?>,<?php echo $eattribute_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo (0 == $eattribute_id) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);">
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
		</ul>
		<div class="tabs_panel_wrap">
            <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $attribute_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="langForm(<?php echo $eattribute_id; ?>, <?php echo $attribute_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?> 
			<div class="tabs_panel">
				<?php echo $extraAttributeLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
