<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$pollingLangFrm->setFormTagAttribute('id', 'polling');
$pollingLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$pollingLangFrm->setFormTagAttribute('onsubmit', 'setupPollingLang(this); return(false);');
if(!empty($polling_type)){
	$polling_type_text ='';
	if($polling_type == Polling::POLLING_TYPE_PRODUCTS){
		$polling_type_text = 'Products';
	} else if($polling_type == Polling::POLLING_TYPE_CATEGORY){
		$polling_type_text = 'Categories';
	}
}
else
{
	die( Labels::getLabel('LBL_Required_variables_not_passed.',$adminLangId));
}

$langFld = $pollingLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "pollingLangForm(" . $polling_id . ", this.value);");
?>
<div class="col-sm-12">
	<h1><?php echo Labels::getLabel('LBL_Polling_Setup',$adminLangId); ?></h1>
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<li><a href="javascript:void(0);" onclick="pollingForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<li class="<?php echo (0 == $polling_id) ? 'fat-inactive' : ''; ?>">
                <a class="active" href="javascript:void(0);">
                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                </a>
            </li>
            <?php 
			if(!empty($polling_type) && !empty($polling_type_text)){ ?>
			<li><a href="javascript:void(0)" onclick="linksForm(<?php echo $polling_id ?>);"><?php echo Labels::getLabel('LBL_Link',$adminLangId); ?> <?php echo $polling_type_text; ?></a></li>
			<?php } ?>
		</ul>
		<div class="tabs_panel_wrap">    <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $polling_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="pollingLangForm(<?php echo $polling_id; ?>, <?php echo $polling_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?> 
			<div class="tabs_panel">
				<?php echo $pollingLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
