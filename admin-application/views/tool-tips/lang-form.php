<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
//$tooltipLangFrm->setFormTagAttribute('id', 'prodBrand');
$tooltipLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$tooltipLangFrm->setFormTagAttribute('onsubmit', 'setupTooltipLang(this,"'.$action.'"); return(false);');
$tooltipLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$tooltipLangFrm->developerTags['fld_default_col'] = 12; 	
if($action == 'edit'){
	$fld_tooltip_key = $tooltipLangFrm->getField('tooltip_default_value_new');
	$fld_tooltip_key->setFieldTagAttribute('disabled','disabled');
}

$langFld = $tooltipLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "tooltipLangForm(" . $tooltipId . ", this.value, '" . $action . "');");
?>
<section class="section">
<div class="sectionhead">
   
    <h4><?php echo Labels::getLabel('LBL_Tooltip_Setup',$adminLangId); ?></h4>
</div>
<div class="sectionbody space">
<div class="row">	
<div class="col-sm-12">
	<div class="tabs_nav_container responsive flat">
		<ul class="tabs_nav">
			<?php if($action == 'add'){?>
			<li><a href="javascript:void(0);" onclick="tooltipForm(<?php echo $tooltipId ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
			<?php }?>
            <li class="<?php echo (0 == $tooltipId) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);">
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
		</ul>
		<div class="tabs_panel_wrap">
            <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $tooltip_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="tooltipLangForm(<?php echo $tooltipId; ?>, <?php echo $tooltip_lang_id; ?>, '<?php echo $action; ?>', 1)">
                                </div>
                            </div>
                        <?php } ?> 
			<div class="tabs_panel">
				<?php echo $tooltipLangFrm->getFormHtml(); ?>
			</div>
		</div>
	</div>	
</div>
</div></div></section>