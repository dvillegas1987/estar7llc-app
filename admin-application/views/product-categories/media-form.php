<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$prodCatIconFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$prodCatIconFrm->developerTags['colClassPrefix'] = 'col-md-';
$prodCatIconFrm->developerTags['fld_default_col'] = 12;
$fld = $prodCatIconFrm->getField('cat_icon');
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld->addFieldTagAttribute('onChange', 'iconPopupImage(this)');
$langFld = $prodCatIconFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'icon-language-js');

$preferredDimensionsStr = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_This_will_be_displayed_in_%s_on_your_store', $adminLangId), '60*60').'</small>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="icon-image-listing"></div>';
$fld->htmlAfterField = $htmlAfterField;

$prodCatBannerFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$prodCatBannerFrm->developerTags['colClassPrefix'] = 'col-md-';
$prodCatBannerFrm->developerTags['fld_default_col'] = 12;
$fld2 = $prodCatBannerFrm->getField('cat_banner');
$fld2->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld2->addFieldTagAttribute('onChange', 'bannerPopupImage(this)');
$langFld = $prodCatBannerFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'banner-language-js');
$screenFld = $prodCatBannerFrm->getField('slide_screen');
$screenFld->addFieldTagAttribute('class', 'prefDimensions-js');

$htmlAfterField = '<div style="margin-top:15px;" class="preferredDimensions-js">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s',$adminLangId),'2000 x 500').'</div>';
$htmlAfterField .= '<div id="banner-image-listing"></div>';
$fld2->htmlAfterField = $htmlAfterField;

/*$preferredDimensionsStr = '<small class="text--small">'.sprintf(Labels::getLabel('LBL_Preferred_Dimensions', $adminLangId), '2000*500').'</small>';
$htmlAfterField = $preferredDimensionsStr;
$catBannerImages ='';
$htmlAfterField .= '<div id="banner-image-listing"></div>';
$fld2->htmlAfterField = $htmlAfterField;*/ ?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Product_Category_Media_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li><a href="javascript:void(0);" onclick="categoryForm(<?php echo $prodcat_id ?>);"><?php echo Labels::getLabel('LBL_General', $adminLangId); ?></a></li>
                        <li class="<?php echo (0 == $prodcat_id) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo (0 < $prodcat_id) ? "onclick='categoryLangForm(" . $prodcat_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <li><a class="active" href="javascript:void(0);"
                            <?php if ($prodcat_id>0) { ?>
                                onclick="categoryMediaForm(<?php echo $prodcat_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <section class="">
                                <?php echo $prodCatIconFrm->getFormHtml();?>
                            </section>
                            <section class="">
                                <?php /* echo $prodCatImageFrm->getFormHtml(); */?>
                                <?php echo $prodCatBannerFrm->getFormHtml();?>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
$('input[name=banner_min_width]').val(2000);
$('input[name=banner_min_height]').val(500);
$('input[name=logo_min_width]').val(150);
$('input[name=logo_min_height]').val(150);
var aspectRatio = 4 / 1;
$(document).on('change','.prefDimensions-js',function(){
    var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
    var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;

    if($(this).val() == screenDesktop)
    {
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '2000 x 500'));
        $('input[name=banner_min_width]').val(2000);
        $('input[name=banner_min_height]').val(500);
        aspectRatio = 4 / 1;
    }
    else if($(this).val() == screenIpad)
    {
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '1024 x 360'));
        $('input[name=banner_min_width]').val(1024);
        $('input[name=banner_min_height]').val(360);
        aspectRatio = 128 / 45;
    }
    else{
        $('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '640 x 360'));
        $('input[name=banner_min_width]').val(640);
        $('input[name=banner_min_height]').val(360);
        aspectRatio = 16 / 9;
    }
});
</script>
