<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$mediaFrm->developerTags['colClassPrefix'] = 'col-md-';
$mediaFrm->developerTags['fld_default_col'] = 12;

$fld1 = $mediaFrm->getField('banner_image');
$fld1->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld1->addFieldTagAttribute('onChange', 'popupImage(this)');
$langFld = $mediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');

$screenFld = $mediaFrm->getField('banner_screen');
$screenFld->addFieldTagAttribute('class', 'display-js');

if ($blocation_id == BannerLocation::HOME_PAGE_MOBILE_BANNER) {
    $screenFld->setFieldTagAttribute('disabled', 'disabled');
}

$preferredDimensionsStr = '<span class="uploadimage--info" ></span>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField;
?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Banner_Image', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="bannerForm(<?php echo $blocation_id;?>,<?php echo $banner_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <li class="<?php echo (0 == $banner_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $banner_id) ? "onclick='bannerLangForm(" . $blocation_id . ", " . $banner_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li><a class="active" href="javascript:void(0)" onclick="mediaForm(<?php echo $blocation_id ?>,<?php echo $banner_id ?>);"><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel"> <?php echo $mediaFrm->getFormHtml(); ?> </div>
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
    $(document).on('change', '.display-js', function() {
        var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
        var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;
        var deviceType = $(this).val();
        fcom.ajax(fcom.makeUrl('Banners', 'getBannerLocationDimensions', [<?php echo $blocation_id;?>, deviceType]), '', function(t) {
            var ans = $.parseJSON(t);
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, ans.bannerWidth + ' * ' + ans.bannerHeight));
            $('input[name=banner_min_width]').val(ans.bannerWidth);
            $('input[name=banner_min_height]').val(ans.bannerHeight);
            if(deviceType == screenDesktop) {
                aspectRatio = 10 / 3;
            }
            else if(deviceType == screenIpad){
                aspectRatio = 10 / 3;
            }
            else{
                aspectRatio = 16 / 9;
            }
        });
    });
    $("document").ready(function() {
        $(".display-js").trigger('change');
    });
</script>
