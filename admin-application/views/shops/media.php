<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$shopLogoFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$shopLogoFrm->developerTags['colClassPrefix'] = 'col-md-';
$shopLogoFrm->developerTags['fld_default_col'] = 12;
$ratioFld = $shopLogoFrm->getField('ratio_type');
$ratioFld->addFieldTagAttribute('class', 'prefRatio-js');
$ratioFld->addOptionListTagAttribute('class', 'list-inline');
$fld = $shopLogoFrm->getField('shop_logo');
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld->addFieldTagAttribute('onChange', 'logoPopupImage(this)');
$langFld = $shopLogoFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'logo-language-js');

$preferredDimensionsStr = '<span class="gap"></span><small class="text--small logoPreferredDimensions-js">' . sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s', $adminLangId), '150 x 150'). '</small>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="logo-image-listing"></div>';
$fld->htmlAfterField = $htmlAfterField;

$shopBannerFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$shopBannerFrm->developerTags['colClassPrefix'] = 'col-md-';
$shopBannerFrm->developerTags['fld_default_col'] = 12;
$fld = $shopBannerFrm->getField('shop_banner');
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld->addFieldTagAttribute('onChange', 'bannerPopupImage(this)');
$langFld = $shopBannerFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'banner-language-js');
$screenFld = $shopBannerFrm->getField('slide_screen');
$screenFld->addFieldTagAttribute('class', 'prefDimensions-js');

$htmlAfterField = '<div style="margin-top:15px;" class="preferredDimensions-js">' . sprintf(Labels::getLabel('LBL_Preferred_Dimensions_%s',$adminLangId),'2000 x 500') . '</div>';
$htmlAfterField .= '<div id="banner-image-listing"></div>';
$fld->htmlAfterField = $htmlAfterField;
/*$bannerSize = applicationConstants::getShopBannerSize();
$shopLayout= ($shopDetails['shop_ltemplate_id'])?$shopDetails['shop_ltemplate_id']:SHOP::TEMPLATE_ONE;
$preferredDimensionsStr = '<span class="gap"></span><small class="text--small">'. sprintf(Labels::getLabel('MSG_Upload_shop_banner_text', $adminLangId), $bannerSize[$shopLayout]). '</small>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="banner-image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField;*/

/*$shopBackgroundImageFrm->setFormTagAttribute('class', 'web_form form_horizontal');
$shopBackgroundImageFrm->developerTags['colClassPrefix'] = 'col-md-';
$shopBackgroundImageFrm->developerTags['fld_default_col'] = 12;
$fld1 = $shopBackgroundImageFrm->getField('shop_background_image');
$fld1->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$langFld = $shopBackgroundImageFrm->getField('lang_id');
$preferredDimensionsStr = '<span class="gap"></span><small class="text--small">'. Labels::getLabel('MSG_Upload_shop_background_text', $adminLangId). '</small>';
$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField .= '<div id="bg-image-listing"></div>';
$fld1->htmlAfterField = $htmlAfterField; */ ?>
<div id="cropperBox-js"></div>
<section class="section" id="mediaForm-js">
    <div class="sectionhead">
        <h4><?php echo Labels::getLabel('LBL_Shop_Media_Setup', $adminLangId); ?></h4>
    </div>
    <div class="sectionbody space">
        <div class="row">
            <div class="col-sm-12">
                <div class="tabs_nav_container responsive flat">
                    <ul class="tabs_nav">
                        <li>
                            <a href="javascript:void(0)" onclick="shopForm(<?php echo $shop_id ?>);">
                                <?php echo Labels::getLabel('LBL_General', $adminLangId); ?>
                            </a>
                        </li>
                        <li class="<?php echo (empty($shop_id)) ? 'fat-inactive' : ''; ?>">
                            <a href="javascript:void(0);" <?php echo ($shop_id) ? "onclick='addShopLangForm(" . $shop_id . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                                <?php echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                            </a>
                        </li>
                        <?php /* <li><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="shopTemplates(<?php echo $shop_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Templates', $adminLangId); ?></a></li> */ ?>
                        <li><a class="active" href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="shopMediaForm(<?php echo $shop_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Media', $adminLangId); ?></a></li>
                        <li><a href="javascript:void(0);"
                            <?php if ($shop_id > 0) { ?>
                                onclick="shopCollections(<?php echo $shop_id ?>);"
                            <?php }?>><?php echo Labels::getLabel('LBL_Collection', $adminLangId); ?></a></li>
                    </ul>
                    <div class="tabs_panel_wrap">
                        <div class="tabs_panel">
                            <?php  echo $shopLogoFrm->getFormHtml();?>
                            <?php echo $shopBannerFrm->getFormHtml();?>
                            <?php /*echo $shopBackgroundImageFrm->getFormHtml();*/ ?>
                        </div>
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
var ratioTypeSquare = <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?>;
var ratioTypeRectangular = <?php echo AttachedFile::RATIO_TYPE_RECTANGULAR; ?>;
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

$(document).on('change','.prefRatio-js',function(){
    if($(this).val() == ratioTypeSquare)
    {
        $('input[name=logo_min_width]').val(150);
        $('input[name=logo_min_height]').val(150);
		$('.logoPreferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '150 x 150'));
    } else {
        $('input[name=logo_min_width]').val(150);
        $('input[name=logo_min_height]').val(85);
		$('.logoPreferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '150 x 85'));
    }
});
</script>
