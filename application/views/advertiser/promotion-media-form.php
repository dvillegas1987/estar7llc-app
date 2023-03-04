<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$mediaFrm->setFormTagAttribute('class', 'form form--horizontal');
$mediaFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$mediaFrm->developerTags['fld_default_col'] = 12;
$mediaFrm->setFormTagAttribute('onsubmit', 'setupPromotionMedia(this); return(false);');

$uploadfld = $mediaFrm->getField('banner_image');
$uploadfld->addFieldTagAttribute('onChange', 'popupImage(this)');

$langFld = $mediaFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'banner-language-js');

$screenFld = $mediaFrm->getField('banner_screen');
$screenFld->addFieldTagAttribute('class', 'banner-screen-js');

$preferredDimensionsStr = '<span class="form-text text-muted uploadimage--info" > '.sprintf(Labels::getLabel('LBL_Preferred_Dimensions', $siteLangId), $bannerWidth . ' * ' . $bannerHeight).'</span>';

$htmlAfterField = $preferredDimensionsStr;
$htmlAfterField.='<div id="image-listing-js"></div>';
$uploadfld->htmlAfterField = $htmlAfterField;

?>
<div class="tabs tabs--small   tabs--scroll clearfix setactive-js">
    <ul>
        <li><a href="javascript:void(0);" onClick="promotionForm(<?php echo $promotionId;?>)"><?php echo Labels::getLabel('LBL_General', $siteLangId);?></a></li>
		<li class="<?php echo (0 == $promotionId) ? 'fat-inactive' : ''; ?>">
            <a href="javascript:void(0);" <?php echo (0 < $promotionId) ? "onclick='promotionLangForm(" . $promotionId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
            </a>
        </li>
        <?php if ($promotionType == Promotion::TYPE_BANNER || $promotionType == Promotion::TYPE_SLIDES) {?>
        <li class="is-active"><a href="javascript:void(0)"
            <?php if ($promotionId>0) { ?>
                onClick="promotionMediaForm(<?php echo $promotionId;?>)"
            <?php }?>><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        <?php }?>
    </ul>
</div>
<div class="tabs__content">
    <div class="row">
        <div class="col-md-8">
        <?php echo $mediaFrm->getFormHtml(); ?>
        </div>
    </div>
</div>

<script>
$('input[name=banner_min_width]').val(1350);
$('input[name=banner_min_height]').val(405);
var aspectRatio = 10 / 3;
$(document).on('change','.banner-screen-js',function(){
    var promotionType = <?php echo $promotionType ?>;
    var screenDesktop = <?php echo applicationConstants::SCREEN_DESKTOP ?>;
    var screenIpad = <?php echo applicationConstants::SCREEN_IPAD ?>;

    if(promotionType==<?php echo Promotion::TYPE_SLIDES ?>){
        if($(this).val() == screenDesktop)
        {
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, '1350 * 405'));
            $('input[name=banner_min_width]').val(1350);
            $('input[name=banner_min_height]').val(405);
            aspectRatio = 10 / 3;
        }
        else if($(this).val() == screenIpad)
        {
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, '1024 * 360'));
            $('input[name=banner_min_width]').val(1024);
            $('input[name=banner_min_height]').val(360);
            aspectRatio = 128 / 45;
        }
        else{
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, '640 * 360'));
            $('input[name=banner_min_width]').val(640);
            $('input[name=banner_min_height]').val(360);
            aspectRatio = 16 / 9;
        }
    }else if(promotionType==<?php echo Promotion::TYPE_BANNER ?>){
        var deviceType = $(this).val();
        fcom.ajax(fcom.makeUrl('Advertiser', 'getBannerLocationDimensions', [<?php echo $promotionId;?>,deviceType]), '', function(t) {
            var ans = $.parseJSON(t);
            $('.uploadimage--info').html((langLbl.preferredDimensions).replace(/%s/g, ans.bannerWidth +' * '+ ans.bannerHeight));
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
    }
});
</script>
