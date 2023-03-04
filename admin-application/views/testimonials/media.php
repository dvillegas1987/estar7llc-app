<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$testimonialMediaFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$testimonialMediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$testimonialMediaFrm->developerTags['fld_default_col'] = 12;
$fld2 = $testimonialMediaFrm->getField('testimonial_image');
$fld2->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld2->addFieldTagAttribute('onChange', 'popupImage(this)');
$preferredDimensionsStr = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions', $adminLangId), '80*80').'</small>';
$htmlAfterField = $preferredDimensionsStr;
if (!empty($testimonialImages)) {
    $htmlAfterField .= '<ul class="image-listing grids--onethird">';
    foreach ($testimonialImages as $testimonialImg) {
        $htmlAfterField .= '<li><div class="uploaded--image"><img src="'.UrlHelper::generateFullUrl('Image', 'testimonial', array($testimonialImg['afile_record_id'],$testimonialImg['afile_lang_id'],'THUMB'), CONF_WEBROOT_FRONT_URL).'?t='.time().'"> <a href="javascript:void(0);" onClick="removeTestimonialImage('.$testimonialImg['afile_record_id'].','.$testimonialImg['afile_lang_id'].')" class="remove--img"><i class="ion-close-round"></i></a></div>';
    }
    $htmlAfterField.='</li></ul>';
}
$fld2->htmlAfterField = $htmlAfterField;
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Testimonial_Media_setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Testimonial_Media_setup',$adminLangId);?></h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0)" onclick="editTestimonialForm(<?php echo $testimonialId ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <li class="<?php echo (0 == $testimonialId) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $testimonialId) ? "onclick='editTestimonialLangForm(" . $testimonialId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li><a class="active" href="javascript:void(0)" onclick="testimonialMediaForm(<?php echo $testimonialId ?>);"><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php echo $testimonialMediaFrm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
