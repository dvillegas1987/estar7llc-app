<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$filterLangFrm->setFormTagAttribute('id', 'prodBrand');
$filterLangFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$filterLangFrm->setFormTagAttribute('onsubmit', 'setUpLang(this); return(false);');

$langFld = $filterLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "langForm(" . $filter_id . ", this.value);");
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Product_Brand_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="addForm(<?php echo $filtergroup_id ?>,<?php echo $filter_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo (0 == $filter_id) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);">
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
		</ul>
		<div class="tabs_panel_wrap">
            <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $filter_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="langForm(<?php echo $filter_id; ?>, <?php echo $filter_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?> 
			<div class="tabs_panel">
				<?php echo $filterLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
