<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$bannerLocLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$bannerLocLangFrm->setFormTagAttribute('onsubmit', 'langSetup(this); return(false);');
$bannerLocLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$bannerLocLangFrm->developerTags['fld_default_col'] = 12;

$langFld = $bannerLocLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "bannerLocationLangForm(" . $blocationId . ", this.value);");

?>

<section class="section">
	<div class="sectionhead">

		<h4><?php echo Labels::getLabel('LBL_Banner_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">		
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="bannerLocation(<?php echo $blocationId;?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
            <li class="<?php echo (0 == $blocationId) ? 'fat-inactive' : ''; ?>">
                <a class="active" href="javascript:void(0);">
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
		</ul>
		<div class="tabs_panel_wrap">
        <?php
            $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
            $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
            if (!empty($translatorSubscriptionKey) && $bannerLocaLangId != $siteDefaultLangId) { ?> 
                <div class="row justify-content-end"> 
                    <div class="col-auto mb-4">
                        <input class="btn btn-brand" 
                            type="button" 
                            value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                            onClick="bannerLocationLangForm(<?php echo $blocationId; ?>, <?php echo $bannerLocaLangId; ?>, 1)">
                    </div>
                </div>
            <?php } ?> 
			<div class="tabs_panel">
				<?php echo $bannerLocLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>

</div>
</div>
</section>
