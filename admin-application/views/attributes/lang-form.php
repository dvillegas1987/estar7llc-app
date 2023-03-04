<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$attrLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$attrLangFrm->setFormTagAttribute('onsubmit', 'setupAttrLang(this); return(false);');
$langFld = $attrLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "langForm(" . $attr_id . ", this.value);");
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Attribute_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
            <li class="active" class="<?php echo (0 == $attr_id) ? 'fat-inactive' : ''; ?>">
                <a href="javascript:void(0);" <?php echo (0 < $attr_id) ? "onclick='langForm(" . $attr_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
		</ul>
		<div class="tabs_panel_wrap">
        <?php
            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
            $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            if (!empty($translatorSubscriptionKey) && $attr_lang_id != $siteDefaultLangId) { ?> 
                <div class="row justify-content-end"> 
                    <div class="col-auto mb-4">
                        <input class="btn btn-brand" 
                            type="button" 
                            value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                            onClick="langForm(<?php echo $attr_id; ?>, <?php echo $attr_lang_id; ?>, 1)">
                    </div>
                </div>
            <?php } ?> 
			<div class="tabs_panel">
				<?php echo $attrLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
