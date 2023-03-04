<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$imagesFrm->setFormTagAttribute('class', 'web_form mt-5');
$imagesFrm->setFormTagAttribute('id', 'imageFrm');
$imagesFrm->developerTags['colClassPrefix'] = 'col-md-';
$imagesFrm->developerTags['fld_default_col'] = 6;

$optionFld = $imagesFrm->getField('option_id');
$optionFld->addFieldTagAttribute('class', 'option-js');

$langFld = $imagesFrm->getField('lang_id');
$langFld->addFieldTagAttribute('class', 'language-js');

$imgFld = $imagesFrm->getField('prod_image');
$imgFld->addFieldTagAttribute('onChange', 'popupImage(this)');
$imgFld->htmlBeforeField='<span class="filename"></span>';
$imgFld->htmlAfterField='<br/><small>'.Labels::getLabel('LBL_Please_keep_image_dimensions_greater_than_500_x_500', $adminLangId).'</small>';

/*$btnbackFld = $imagesFrm->getField('btn_back');
$btnbackFld->developerTags['col'] = 6;

$btnFinishFld = $imagesFrm->getField('btn_finish');
$btnFinishFld->developerTags['col'] = 6;
$btnFinishFld->addWrapperAttribute('class', 'text-right'); */

?>


<div class="tabs_data">
     <div class="tabs_body">
         <?php echo $imagesFrm->getFormHtml(); ?>
         <div id="imageupload_div" class="padd15">
            <?php if (!empty($product_images)) { ?>
            <ul class="grids--onefifth ui-sortable" id="<?php if ($canEdit) { ?>sortable<?php } ?>">
                <?php
                $count=1;
                foreach ($product_images as $afile_id => $row) { ?>
                <li id="<?php echo $row['afile_id']; ?>">
                    <div class="logoWrap">
                        <div class="logothumb"> <img src="<?php echo UrlHelper::generateUrl('image', 'product', array($row['afile_record_id'], "THUMB",$row['afile_id']), CONF_WEBROOT_URL); ?>" title="<?php echo $row['afile_name'];?>"
                                alt="<?php echo $row['afile_name'];?>"> <?php echo ($count == 1) ? '<small><strong>'.Labels::getLabel('LBL_Default_Image', $adminLangId).'</strong></small>' : '&nbsp;'; if ($canEdit) { ?> <a
                                class="deleteLink white" href="javascript:void(0);" title="Delete <?php echo $row['afile_name'];?>" onclick="deleteProductImage(<?php echo $row['afile_record_id']; ?>, <?php echo $row['afile_id']; ?>);"
                                class="delete"><i class="ion-close-round"></i></a>
                            <?php } ?>
                        </div>
                        <?php if (!empty($imgTypesArr[$row['afile_record_subid']])) {
                            echo '<small class=""><strong>'.Labels::getLabel('LBL_Type', $adminLangId).': </strong> '.$imgTypesArr[$row['afile_record_subid']].'</small><br/>';
                                $lang_name = Labels::getLabel('LBL_All', $adminLangId);
                                if ($row['afile_lang_id'] > 0) {
                                    $lang_name = $languages[$row['afile_lang_id']]; ?>
                                            <?php
                                } ?>
                        <small class=""><strong> <?php echo Labels::getLabel('LBL_Language', $adminLangId); ?>:</strong> <?php echo $lang_name; ?></small>
                    </div>
                </li>
                <?php $count++;} ?>
                <?php } ?>
            </ul>
            <?php } ?>
        </div>
    </div>
        
    <div class="row web_form tabs_footer">
        <div class="col-md-6">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input onclick="<?php if($productType == Product::PRODUCT_TYPE_PHYSICAL) { ?>productShipping(<?php echo $productId; ?>); <?php }else{ ?> productOptionsAndTag(<?php echo $productId; ?>); <?php }?>" type="button" name="btn_back" value="<?php echo Labels::getLabel('LBL_Back', $adminLangId); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 text-right">
            <div class="field-set">
                <div class="caption-wraper"><label class="field_label"></label></div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input onclick="goToProduct();" type="submit" name="btn_Finish" value="<?php echo Labels::getLabel('LBL_Finish', $adminLangId); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
var aspectRatio = 1 / 1;
$(function() {
    $("#sortable").sortable({
        stop: function() {
            var mysortarr = new Array();
            $(this).find('li').each(function() {
                mysortarr.push($(this).attr("id"));
            });
            var product_id = $('#imageFrm input[name=product_id]').val();
            var sort = mysortarr.join('-');
            var lang_id = $('.language-js').val();
            var option_id = $('.option-js').val();
            data = '&product_id=' + product_id + '&ids=' + sort;
            fcom.updateWithAjax(fcom.makeUrl('products', 'setImageOrder'), data, function(t) {
                productImages(product_id, option_id, lang_id);
            });
        }
    }).disableSelection();
});
</script>
