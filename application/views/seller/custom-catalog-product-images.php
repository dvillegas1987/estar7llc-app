<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$imagesFrm->setFormTagAttribute('id', 'frmCustomCatalogProductImage');
$imagesFrm->setFormTagAttribute('class', 'form');
$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
$imagesFrm->developerTags['fld_default_col'] = 6;

$optionFld = $imagesFrm->getField('option_id');
$optionFld->addFieldTagAttribute('class', 'option-js');

$langFld = $imagesFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');

$img_fld = $imagesFrm->getField('prod_image');
$img_fld->addFieldTagAttribute('class', 'btn  btn-sm');
$img_fld->addFieldTagAttribute('onChange', 'popupImage(this)');
?>
<div class="tabs_data">
    <div class="tabs_body">
        <?php echo $imagesFrm->getFormHtml(); ?>
        <div id="imageupload_div"></div>
    </div>
    <div class="row tabs_footer">
        <div class="col-6">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input onclick="
                        <?php if ($productType == Product::PRODUCT_TYPE_PHYSICAL) { ?>
                            productShipping(<?php echo $preqId; ?>);
                        <?php } else { ?>
                            productOptionsAndTag(<?php echo $preqId; ?>);
                        <?php }?>" class="btn btn-outline-brand" type="button" name="btn_back" value="<?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 text-right">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input onclick="goToCatalogRequest();" type="button" class="btn btn-brand" name="btn_Finish" value="<?php echo Labels::getLabel('LBL_Finish', $siteLangId); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
