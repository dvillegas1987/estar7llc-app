<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupLangMetaTag(this,"'.$metaType.'"); return(false);');
$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;

$langFld = $langFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "editMetaTagLangForm(" . $metaId . ", this.value, '" . $metaType . "');");
?>
<section class="section">
	<div class="sectionhead">
		<h4><?php echo Labels::getLabel('LBL_Meta_Tag_Setup',$adminLangId); ?></h4>
	</div>
	<div class="sectionbody space">
		<div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Meta_Setup',$adminLangId); ?></h1>
                <div class="tabs_nav_container responsive flat">
                    <?php if($metaType == MetaTag::META_GROUP_ADVANCED) { ?>
                        <ul class="tabs_nav">
                            <li><a href="javascript:void(0);" onclick="editMetaTagForm(<?php echo "$metaId,'$metaType',$recordId" ?>);"><?php echo Labels::getLabel('LBL_General',$adminLangId); ?></a></li>
                            <li class="<?php echo (0 == $metaId) ? 'fat-inactive' : ''; ?>">
                                <a class="active" href="javascript:void(0);">
                                    <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                                </a>
                            </li>
                        </ul>
                    <?php } ?>
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
                                        onClick="editMetaTagLangForm(<?php echo $metaId; ?>, <?php echo $lang_id; ?>, '<?php echo $metaType; ?>', 1)">
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