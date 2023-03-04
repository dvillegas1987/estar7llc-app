<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$slideMediaFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$slideMediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$slideMediaFrm->developerTags['fld_default_col'] = 12;

$fld1 = $slideMediaFrm->getField('slide_image');
$fld1->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld1->addFieldTagAttribute('onChange', 'popupImage(this)');
$screenFld = $slideMediaFrm->getField('slide_screen');
$screenFld->addFieldTagAttribute('class', 'prefDimensions-js');
$langFld = $slideMediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');
$htmlAfterField = '<div style="margin-top:15px;" class="preferredDimensions-js">'. sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s', $adminLangId), '1350 x 405').'</div>';
$htmlAfterField .= '<div id="image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField;
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Slide_Image_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <h1><?php //echo Labels::getLabel('LBL_Slide_Image_Setup',$adminLangId);?></h1>
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="slideForm(<?php echo $slide_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
						<li class="<?php echo ($slide_id == 0) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo ($slide_id) ? "onclick='slideLangForm(" . $slide_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li><a class="active" href="javascript:void(0);" <?php if ($slide_id>0) {?> onclick="slideMediaForm(<?php echo $slide_id ?>);" <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php echo $slideMediaFrm->getFormHtml(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
$('input[name=banner_min_width]').val(1350);
$('input[name=banner_min_height]').val(405);
var aspectRatio = 10 / 3;
$(document).on('change', '.prefDimensions-js', function() {
    var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
    var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;

    if ($(this).val() == screenDesktop) {
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '1350 x 405'));
        $('input[name=banner_min_width]').val(1350);
        $('input[name=banner_min_height]').val(405);
        aspectRatio = 10 / 3;
    } else if ($(this).val() == screenIpad) {
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '1024 x 360'));
        $('input[name=banner_min_width]').val(1024);
        $('input[name=banner_min_height]').val(360);
        aspectRatio = 128 / 45;
    } else {
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '640 x 360'));
        $('input[name=banner_min_width]').val(640);
        $('input[name=banner_min_height]').val(360);
        aspectRatio = 16 / 9;
    }
});
</script>
