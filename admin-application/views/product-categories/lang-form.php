<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$prodCatLangFrm->setFormTagAttribute('id', 'prodCate');
$prodCatLangFrm->setFormTagAttribute('class', 'web_form form_horizontal layout--'.$formLayout);

$prodCatLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$prodCatLangFrm->developerTags['fld_default_col'] = 12;
/* [ */
/* $cat_image_fld = $prodCatLangFrm->getField('cat_image');
$cat_image_fld->addFieldTagAttribute('class','btn btn-brand btn-sm');
$htmlAfterField = '<span class="uploadimage--info">This will be displayed in 268x202 on Home Page Collections, while displaying categories.</span>';
if( isset($catImages) && !empty($catImages) ){
    $htmlAfterField .= '
    <div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Category','image',array($prodcat_id, $prodcat_lang_id, 'THUMB'),CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeCatImage('.$prodcat_id.', '.$prodcat_lang_id.')" class="remove--img"><i class="ion-close-round"></i></a></div>';
}
$cat_image_fld->htmlAfterField = $htmlAfterField; */
/* ] */


/* [ */
/* $cat_icon_fld = $prodCatLangFrm->getField('cat_icon');
$cat_icon_fld->addFieldTagAttribute('class','btn btn-brand btn-sm');
$htmlAfterField = '<span class="uploadimage--info">This will be displayed in 60x60 on your store.</span>';

if( isset($catIcons) && !empty($catIcons) ){
    $htmlAfterField .= '
    <div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Category','icon',array($prodcat_id, $prodcat_lang_id, 'THUMB'),CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeCatIcon('.$prodcat_id.', '.$prodcat_lang_id.')" class="remove--img"><i class="ion-close-round"></i></a></div>';
}
$cat_icon_fld->htmlAfterField = $htmlAfterField; */
/* ] */

/* [ */
/* $fld1 = $prodCatLangFrm->getField('cat_banner');
$fld1->addFieldTagAttribute('class','btn btn-brand btn-sm');
$htmlAfterField = '<span class="uploadimage--info">Preferred Dimesnion: Width = 1050PX, Height = 340PX</span>';
if( isset($catBanners) && !empty($catBanners) ){
    $htmlAfterField .= '<div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Category','banner',array($prodcat_id, $prodcat_lang_id,'THUMB'),CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeCatBanner('. $prodcat_id.', '.$prodcat_lang_id.')" class="remove--img"><i class="ion-close-round"></i></a></div>';
}
$fld1->htmlAfterField = $htmlAfterField; */
/* ] */
//$prodCatLangFrm->setFormTagAttribute('onsubmit', 'setupCategoryLang(this); return(false);');

$langFld = $prodCatLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "categoryLangForm(" . $prodcat_id . ", this.value);");
?>
<section class="section">
    <div class="sectionhead">

        <h4><?php echo Labels::getLabel('LBL_Product_Categories_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="col-sm-12">

            <div class="row">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);"
                                onclick="categoryForm(<?php echo $prodcat_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
                        <li class="<?php echo (0 == $prodcat_id) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);">
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li class="<?php echo (!$prodcat_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php if ($prodcat_id > 0) { ?>
                                onclick="categoryMediaForm(<?php echo $prodcat_id ?>);" <?php }?>>
                                <?php echo Labels::getLabel('LBL_Media', $adminLangId); ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <?php 
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $prodcat_lang_id != $siteDefaultLangId) { ?> 
                            <div class="row justify-content-end"> 
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand" 
                                        type="button" 
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>" 
                                        onClick="categoryLangForm(<?php echo $prodcat_id; ?>, <?php echo $prodcat_lang_id; ?>, 1)">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="tabs_panel">
                            <?php echo $prodCatLangFrm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>