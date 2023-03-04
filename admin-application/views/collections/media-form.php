<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$collectionMediaFrm->setFormTagAttribute('class', 'web_form');
$collectionMediaFrm->developerTags['colClassPrefix'] = 'col-sm-';
$collectionMediaFrm->developerTags['fld_default_col'] = 6;

/*$collectionImageHeadingFld = $collectionMediaFrm->getField('collection_image_heading');
$collectionImageHeadingFld->developerTags['col'] = 12;
$collectionImageHeadingFld->value = '<h2>'.Labels::getLabel('LBL_Collection_Image_Management',$adminLangId).'</h2>';*/

$collectionImageDisplayDiv = $collectionMediaFrm->getField('collection_image_display_div');
$collectionImageDisplayDiv->developerTags['col'] = 12;

$languageFld = $collectionMediaFrm->getField('image_lang_id');
$languageFld->setFieldTagAttribute('class', 'language-js');

$displayMediaOnlyObj = $collectionMediaFrm->getField('collection_display_media_only');
$displayMediaOnlyObj->setFieldTagAttribute('class', 'displayMediaOnly--js');
$displayMediaOnlyObj->setFieldTagAttribute('onclick', 'displayMediaOnly('.$collection_id.', this)');
if (0 < $displayMediaOnly) {
    $displayMediaOnlyObj->setFieldTagAttribute('checked', 'checked');
}

$fld = $collectionMediaFrm->getField('collection_image');
$fld->setFieldTagAttribute('data-collection_id', $collection_id);
$fld->addFieldTagAttribute('onChange', 'popupImage(this)');
$preferredDimensionsStr = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s', $adminLangId), '640*480').'</small>';
$fld->htmlAfterField = $preferredDimensionsStr;

$headingArea = $collectionMediaFrm->getField('collection_image_heading');
$str = '<small class="text--small">'.Labels::getLabel('LBL_Used_For_Mobile_Applications', $adminLangId).'</small>';
$headingArea->value = $str;

/*$collectionBgImageHeadingFld = $collectionMediaFrm->getField('collection_bg_image_heading');
$collectionBgImageHeadingFld->developerTags['col'] = 12;
$collectionBgImageHeadingFld->value = '<br/><br/><h2>'.Labels::getLabel('LBL_Collection_Background_Image_Management(If_any)', $adminLangId).'</h2>';

$collectionBgImageDisplayDiv = $collectionMediaFrm->getField('collection_bg_image_display_div');
$collectionBgImageDisplayDiv->developerTags['col'] = 12;

$languageFld = $collectionMediaFrm->getField('bg_image_lang_id');
$languageFld->setFieldTagAttribute('class', 'bgLanguage-js');

$bgFld = $collectionMediaFrm->getField('collection_bg_image');
$bgFld->htmlAfterField = $preferredDimensionsStr;*/

/*$fileTypeArr = [AttachedFile::FILETYPE_COLLECTION_IMAGE, AttachedFile::FILETYPE_COLLECTION_BG_IMAGE];*/

$fileTypeArr = [AttachedFile::FILETYPE_COLLECTION_IMAGE];

foreach ($fileTypeArr as $fileType) {
    $method = 'collectionReal';
    $cType = '';
    $fn = 'removeCollectionImage';
    if ($fileType == AttachedFile::FILETYPE_COLLECTION_BG_IMAGE) {
        $method = 'collectionBgReal';
        $cType = 'bg';
        $fn = 'removeCollectionBGImage';
    }
    $imgUpdatedOn = AttachedFile::setTimeParam($imgUpdatedOn);
    $imgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', $method, array($collection_id, 0, 'THUMB'), CONF_WEBROOT_FRONT_URL).$imgUpdatedOn, CONF_IMG_CACHE_TIME, '.jpg');

    $imagesHtml = '<ul class="grids--onefifth '.$cType.'CollectionImages-js">
        <li id="'.$cType.'Image-0">
            <div class="logoWrap">
                <div class="logothumb">
                    <img src="'.$imgUrl.'">';
    if (AttachedFile::getAttachment($fileType, $collection_id, 0, 0, false)) {
        $imagesHtml .= '<a class="deleteLink white" href="javascript:void(0);" title="Delete '.$collectionImages['afile_name'].'" onclick="'.$fn.'('.$collection_id.',0)" class="delete"><i class="ion-close-round"></i></a>';
    }

    $imagesHtml .= '</div>
                <small><strong> '.Labels::getLabel('LBL_Language', $adminLangId).':</strong> '.Labels::getLabel('LBL_All_Languages', $adminLangId).'</small>
            </div>
        </li>';
    foreach ($languages as $langId => $langName) {
        $langImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', $method, array($collection_id, $langId, 'THUMB'), CONF_WEBROOT_FRONT_URL).$imgUpdatedOn, CONF_IMG_CACHE_TIME, '.jpg');

        $imagesHtml .= '<li class="d-none" id="'.$cType.'Image-'.$langId.'">
                            <div class="logoWrap">
                                <div class="logothumb">
                                    <img src="'.$langImgUrl.'">';
        if (AttachedFile::getAttachment($fileType, $collection_id, 0, $langId, false)) {
            $imagesHtml .= '<a class="deleteLink white" href="javascript:void(0);" title="Delete '.$collectionImages['afile_name'].'" onclick="'.$fn.'('.$collection_id.','.$langId.')" class="delete"><i class="ion-close-round"></i></a>';
        }

                    $imagesHtml .= '</div>
                                <small><strong> '.Labels::getLabel('LBL_Language', $adminLangId).':</strong> '.$langName.'</small>
                            </div>
                        </li>';
    }
    $imagesHtml .= '</ul>';

    if ($fileType == AttachedFile::FILETYPE_COLLECTION_BG_IMAGE) {
        $collectionBgImageDisplayDiv->value = $imagesHtml;
    } else {
        $collectionImageDisplayDiv->value = $imagesHtml;
    }
}


$collectionMediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$collectionMediaFrm->developerTags['fld_default_col'] = 12;

?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionbody space">
        <div class="row">    <div class="col-sm-12">
    <div class="tabs_nav_container responsive flat">
        <ul class="tabs_nav">
            <li><a href="javascript:void(0)" onclick="collectionForm(<?php echo $collection_type ?>, <?php echo $collection_layout_type ?>, <?php echo $collection_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
            <?php if (!in_array($collection_type, Collections::COLLECTION_WITHOUT_RECORDS)) { ?>
            <li><a class=""
                    href="javascript:void(0)"
                    onclick="recordForm(<?php echo $collection_id ?>, <?php echo $collection_type ?>);">
                    <?php echo Labels::getLabel('LBL_Link_Records', $adminLangId);?></a>
            </li>
            <?php } ?>
            <li><a class="active" href="javascript:void(0)" onclick="collectionMediaForm(<?php echo $collection_id ?>);"><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
        </ul>
        <div class="tabs_panel_wrap">
            <div class="tabs_panel">
                <?php echo $collectionMediaFrm->getFormHtml(); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$('input[name=min_width]').val(640);
$('input[name=min_height]').val(480);
var aspectRatio = 4 / 3;
var FILETYPE_COLLECTION_IMAGE = '<?php echo AttachedFile::FILETYPE_COLLECTION_IMAGE ?>';
var FILETYPE_COLLECTION_BG_IMAGE = '<?php echo AttachedFile::FILETYPE_COLLECTION_BG_IMAGE ?>';
</script>
