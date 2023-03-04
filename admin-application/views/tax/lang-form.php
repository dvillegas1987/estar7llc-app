<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$taxLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$taxLangFrm->setFormTagAttribute('onsubmit', 'setupTaxLang(this); return(false);');
$taxLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$taxLangFrm->developerTags['fld_default_col'] = 12;

$langFld = $taxLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "addTaxLangForm(" . $taxcat_id . ", this.value);");
?>

<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Sales_Tax_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">      
		<div class="tabs_nav_container responsive flat">
			<ul class="tabs_nav">
				<li><a href="javascript:void(0);" onclick="taxForm(<?php echo $taxcat_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
                <li class="<?php echo (0 == $taxcat_id) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);">
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
			</ul>
			<div class="tabs_panel_wrap">
                <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $taxcat_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="addTaxLangForm(<?php echo $taxcat_id; ?>, <?php echo $taxcat_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?> 
				<div class="tabs_panel">
					<?php echo $taxLangFrm->getFormHtml(); ?>
				</div>
			</div>						
		</div>
	</div>						
</section>

