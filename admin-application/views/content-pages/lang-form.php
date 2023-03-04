<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$blockLangFrm->setFormTagAttribute('class', 'web_form layout--' . $formLayout);
$blockLangFrm->developerTags['colClassPrefix'] = 'col-md-';
$blockLangFrm->developerTags['fld_default_col'] = 12;

if ($cpage_layout == ContentPage::CONTENT_PAGE_LAYOUT1_TYPE) {
    $fld = $blockLangFrm->getField('cpage_bg_image');
    $fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
    $fld->addFieldTagAttribute('onChange', 'popupImage(this)');
    $preferredDimensionsStr = '<small class="text--small"> ' . Labels::getLabel('LBL_This_will_be_displayed_on_your_cms_Page.', $adminLangId) . ' ' . sprintf(Labels::getLabel('LBL_Preferred_Dimensions', $adminLangId), '1300*400') . '</small>';

    $htmlAfterField = $preferredDimensionsStr;
    if (!empty($bgImages)) {
        $htmlAfterField .= '<ul class="image-listing grids--onethird">';
        foreach ($bgImages as $bgImage) {
            $uploadedTime = AttachedFile::setTimeParam($bgImage['afile_updated_at']);
            $image = UrlHelper::getCachedUrl(UrlHelper::generateFullUrl('image', 'cpageBackgroundImage', array($bgImage['afile_record_id'],$bgImage['afile_lang_id'],'THUMB'), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            $htmlAfterField .= '<li>' . $bannerTypeArr[$bgImage['afile_lang_id']] . '<div class="uploaded--image"><img src="' . $image . '"> <a href="javascript:void(0);" onClick="removeBgImage(' . $bgImage['afile_record_id'] . ',' . $bgImage['afile_lang_id'] . ',' . $cpage_layout.')" class="remove--img"><i class="ion-close-round"></i></a></div>';
        }
        $htmlAfterField .= '</li></ul>';
    } else {
        $htmlAfterField .= '<div class="temp-hide"><ul class="image-listing grids--onethird"><li><div class="uploaded--image"></div></li></ul></div>';
    }
    $fld->htmlAfterField = $htmlAfterField;
}

$langFld = $blockLangFrm->getField('lang_id');
$langFld->setfieldTagAttribute('onChange', "addLangForm(" . $cpage_id . ", this.value, " . $cpage_layout . ");");
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Content_Pages_Setup', $adminLangId); ?>
        </h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Content_Pages_Setup',$adminLangId);?>
                </h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);"
                                onclick="addForm(<?php echo $cpage_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a>
                        </li>
                        <li class="<?php echo (0 == $cpage_id) ? 'fat-inactive' : ''; ?>">
                            <a class="active" href="javascript:void(0);" <?php echo (0 < $cpage_id) ? "onclick='addLangForm(" . $cpage_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ", " . $cpage_layout . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <?php
                        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
                        if (!empty($translatorSubscriptionKey) && $cpage_lang_id != $siteDefaultLangId) { ?>
                            <div class="row justify-content-end">
                                <div class="col-auto mb-4">
                                    <input class="btn btn-brand"
                                        type="button"
                                        value="<?php echo Labels::getLabel('LBL_AUTOFILL_LANGUAGE_DATA', $adminLangId); ?>"
                                        onClick="addLangForm(<?php echo $cpage_id; ?>, <?php echo $cpage_lang_id; ?>, <?php echo $cpage_layout?>, 1)">
                                </div>
                            </div>
                        <?php } ?>
                        <div class="tabs_panel">
                            <?php
                                echo $blockLangFrm->getFormTag();
                                echo $blockLangFrm->getFormHtml(false);
                                echo '</form>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
