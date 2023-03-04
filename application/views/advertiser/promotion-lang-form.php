<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$langFrm->setFormTagAttribute('class', 'form form--horizontal layout--'.$formLayout);
$langFrm->setFormTagAttribute('onsubmit', 'setupPromotionLang(this); return(false);');

$langFrm->developerTags['colClassPrefix'] = 'col-md-';
$langFrm->developerTags['fld_default_col'] = 12;

$langFld = $langFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "promotionLangForm(" . $promotionId . ", this.value);");

$btnSubmitFld = $langFrm->getField('btn_submit');
$btnSubmitFld->setFieldTagAttribute('class', 'btn btn-brand btn-wide');
?>
<div class="tabs tabs--small   tabs--scroll clearfix setactive-js rtl">
    <ul>
        <li><a href="javascript:void(0);" onClick="promotionForm(<?php echo $promotionId;?>)"><?php echo Labels::getLabel('LBL_General', $siteLangId);?></a></li>
        <li class="is-active">
            <a href="javascript:void(0);">
                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
            </a>
        </li>
        <?php
        /* foreach ($languages as $langId => $langName) { ?>
            <li class="<?php echo ($promotion_lang_id == $langId)?'is-active':''?>"><a href="javascript:void(0)" <?php if ($promotionId > 0) {
                ?> onClick="promotionLangForm(<?php echo $promotionId;?>, <?php echo $langId;?>)" <?php
                       } ?>>
            <?php echo $langName;?></a></li>
        <?php }  */?>
        <?php if ($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES) { ?>
        <li ><a href="javascript:void(0)" <?php if ($promotionId>0) {
            ?> onClick="promotionMediaForm(<?php echo $promotionId;?>)" <?php
                                          }?>> <?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        <?php }?>
    </ul>
</div>
<div class="tabs__content">
    <div class="row">
        <div class="col-md-6">
            <?php
                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                if (!empty($translatorSubscriptionKey) && $promotion_lang_id != $siteDefaultLangId) { ?> 
                        <div class="col-auto mb-4">
                            <input class="btn btn-brand" 
                                type="button" 
                                value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $siteLangId); ?>" 
                                onClick="promotionLangForm(<?php echo $promotionId; ?>, <?php echo $promotion_lang_id; ?>, 1)">
                        </div>
                <?php } ?>
            <?php echo $langFrm->getFormHtml(); ?>
        </div>
    </div>
</div>
