<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$brandReqMediaFrm->setFormTagAttribute('class', 'form form_horizontal');
$brandReqMediaFrm->developerTags['colClassPrefix'] = 'col-lg-12 col-md-12 col-sm-';
$brandReqMediaFrm->developerTags['fld_default_col'] = 12;
$ratioFld = $brandReqMediaFrm->getField('ratio_type');
$ratioFld->addFieldTagAttribute('class', 'prefRatio-js');
$fld = $brandReqMediaFrm->getField('logo');
$fld->addFieldTagAttribute('class', 'btn btn-brand btn-sm');
$fld->addFieldTagAttribute('onChange', 'brandPopupImage(this)');

$preferredDimensionsStr = ' <small class="form-text text-muted preferredDimensions-js">'. sprintf(Labels::getLabel('LBL_Preferred_Dimensions', $siteLangId), '500 x 500').'</small>';

$htmlAfterField = $preferredDimensionsStr;
if (!empty($brandImages)) {
    $htmlAfterField .= '<div class="gap"></div><div class="row"><div class="col-lg-12 col-md-12"><div id="imageupload_div"><ul class="inline-images">';
    foreach ($brandImages as $bannerImg) {
        $htmlAfterField .= '<li>'.$bannerTypeArr[$bannerImg['afile_lang_id']].'<img src="'.UrlHelper::generateFullUrl('Image', 'brandReal', array($bannerImg['afile_record_id'],$bannerImg['afile_lang_id'],'THUMB'), CONF_WEBROOT_FRONT_URL).'"> <a href="javascript:void(0);" onClick="removeBrandLogo('.$bannerImg['afile_record_id'].','.$bannerImg['afile_lang_id'].')" class="deleteLink"><i class="fa fa-times"></i></a>';
        $lang_name = Labels::getLabel('LBL_All', $siteLangId);
        if ($bannerImg['afile_lang_id'] > 0) {
            $lang_name = $languages[$bannerImg['afile_lang_id']];
        }
        $htmlAfterField .='<p class=""><strong> '.Labels::getLabel('LBL_Language', $siteLangId).':</strong> '.$lang_name.'</p>';
    }
    $htmlAfterField.='</li></ul></div></div></div>';
}
$fld->htmlAfterField = $htmlAfterField;
?>
<div id="cropperBox-js"></div>
<div id="brandMediaForm-js">
    <div class="box__head">
      <h4><?php echo (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) ? Labels::getLabel('LBL_Request_New_Brand', $siteLangId) : Labels::getLabel('LBL_New_Brand', $siteLangId) ?></h4>
    </div>
    <div class="box__body">
      <div class="tabs ">
        <ul>
            <li><a href="javascript:void(0)" onclick="addBrandReqForm(<?php echo $brandReqId ?>);"><?php echo Labels::getLabel('LBL_Basic', $siteLangId);?></a></li>
            <li class="<?php echo (0 == $brandReqId) ? 'fat-inactive' : ''; ?>">
                  <a href="javascript:void(0);" <?php echo (0 < $brandReqId) ? "onclick='addBrandReqLangForm(" . $brandReqId . "," . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1) . ");'" : ""; ?>>
                    <?php echo Labels::getLabel('LBL_Language_Data', $siteLangId); ?>
                  </a>
              </li>
            <li  class="is-active" ><a href="javascript:void(0)" onclick="brandMediaForm(<?php echo $brandReqId ?>);"><?php echo Labels::getLabel('LBL_Media', $siteLangId); ?></a></li>
        </ul>
      </div>
        <?php echo $brandReqMediaFrm->getFormHtml();
        if (!empty($brandImages)) { ?>
          <div class="gap"></div>
          <div class="row">
            <div class="col-lg-12 col-md-12">
              <div id="imageupload_div">
                <ul class="inline-images">
                  <?php
                    foreach ($brandImages as $bannerImg) {
                        $htmlAfterField .= '<li>'.$bannerTypeArr[$bannerImg['afile_lang_id']].'<img src="'.UrlHelper::generateFullUrl('Image', 'brandReal', array($bannerImg['afile_record_id'],$bannerImg['afile_lang_id'],'THUMB'), CONF_WEBROOT_FRONT_URL).'"> <hr><a href="javascript:void(0);" onClick="removeBrandLogo('.$bannerImg['afile_record_id'].','.$bannerImg['afile_lang_id'].')" class="deleteLink white"><i class="fa fa-times"></i></a>';
                        $lang_name = Labels::getLabel('LBL_All', $siteLangId);
                        if ($bannerImg['afile_lang_id'] > 0) {
                            $lang_name = $languages[$bannerImg['afile_lang_id']];
                        }
                        $htmlAfterField .='<p class=""><strong> '.Labels::getLabel('LBL_Language', $siteLangId).':</strong> '.$lang_name.'</p>';
                    } ?>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        <?php } ?>
    </div>
</div>
<script>
var ratioTypeSquare = <?php echo AttachedFile::RATIO_TYPE_SQUARE; ?>;
$(document).on('change','.prefRatio-js',function(){
    if($(this).val() == ratioTypeSquare)
    {
		$('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '500 x 500'));
    } else {
		$('.preferredDimensions-js').html((langLbl.preferredDimensions).replace(/%s/g, '500 x 280'));
    }
});
</script>