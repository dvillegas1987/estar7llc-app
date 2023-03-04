<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$productFrm->setFormTagAttribute('class', 'web_form mt-5');
$productFrm->setFormTagAttribute('onsubmit', 'setUpProductAttributes(this); return(false);');

$fldSeller = $productFrm->getField('selprod_user_shop_name');
$fldSeller->htmlAfterField = '<br/><small>'.Labels::getLabel('LBL_Please_leave_empty_if_you_want_to_add_product_in_system_catalog', $adminLangId).' </small>';
if ($productData['product_added_by_admin_id'] == 1 && $totalProducts >0) {
    $fldSeller->setfieldTagAttribute('readonly', 'readonly');
}
$warrantyFld = $productFrm->getField('product_warranty');
$warrantyFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $adminLangId) . ' </small>';

$fld = $productFrm->getField('product_featured');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';

/* if($productData['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
$fld = $productFrm->getField('ps_free');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';

$fld = $productFrm->getField('product_cod_enabled');
$fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
$fld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
} */

?>
<div class="row justify-content-center">
     <div class="col-md-12">
        <?php echo $productFrm->getFormTag(); ?>
        <div class="row">
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                            <?php $fld = $productFrm->getField('selprod_user_shop_name');
                              echo $fld->getCaption();
                            ?>
                        </label>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('selprod_user_shop_name'); ?>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                        <?php $fld = $productFrm->getField('product_model');
                              echo $fld->getCaption();
                        ?>
                        </label>
                        <?php if (FatApp::getConfig("CONF_PRODUCT_MODEL_MANDATORY", FatUtility::VAR_INT, 1)) { ?>
                        <span class="spn_must_field">*</span>
                        <?php } ?>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_model'); ?>
                         </div>
                     </div>
                 </div>
             </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                            <?php $fld = $productFrm->getField('product_warranty');
                              echo $fld->getCaption();
                            ?>
                        </label>          
                        <?php if($fld->requirements()->isRequired()){ ?>
                            <span class="spn_must_field">*</span> 
                        <?php } ?>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_warranty'); ?>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper"></div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_featured'); ?>
                         </div>
                     </div>
                 </div>
             </div>
          </div>   
        <?php /* if($productData['product_type'] == Product::PRODUCT_TYPE_PHYSICAL) { ?>  
          <div class="row">
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper"></div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('ps_free');  ?>
                         </div>
                     </div>
                 </div>
            </div>
            <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper"></div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_cod_enabled'); ?>
                         </div>
                     </div>
                 </div>
            </div>
         </div>
        <?php } */ ?>
        
         <div class="specifications-form-<?php echo $siteDefaultLangId; ?>"></div>
         <div class="specifications-list-<?php echo $siteDefaultLangId; ?>"></div>
         
         <?php 
         if(!empty($otherLanguages)){ 
            foreach($otherLanguages as $langId=>$data) { 
                $layout = Language::getLayoutDirection($langId);
         ?>
         <div class="accordians_container accordians_container-categories mt-5">
             <div class="accordian_panel">
                 <span class="accordian_title accordianhead" onClick="displayOtherLangProdSpec(this,<?php echo $langId; ?>)">
                 <?php echo $data." "; echo Labels::getLabel('LBL_Language_Specification', $adminLangId); ?>
                 </span>
                 <div class="accordian_body accordiancontent p-0 layout--<?php echo $layout; ?>" style="display: none;">
                     <div class="specifications-form-<?php echo $langId; ?>"></div>
                    <div class="specifications-list-<?php echo $langId; ?>"></div>
                 </div>
             </div>
         </div>
         <?php } 
         }
         ?>

         <div class="row">
            <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper"><label class="field_label"></label></div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('btn_back'); ?>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6 text-right">
                 <div class="field-set">
                     <div class="caption-wraper"><label class="field_label"></label></div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php 
                         echo $productFrm->getFieldHtml('product_id'); 
                         echo $productFrm->getFieldHtml('product_seller_id'); 
                         echo $productFrm->getFieldHtml('btn_submit');  
                         ?>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         </form>
        <?php echo $productFrm->getExternalJS(); ?>
     </div>
</div>

<script type="text/javascript">    
    var product_added_by_admin = <?php echo $productData['product_added_by_admin_id']; ?> ;
    var totalProducts = <?php echo $totalProducts; ?> ;
    
    prodSpecificationSection(<?php echo $siteDefaultLangId; ?>)
    prodSpecificationsByLangId(<?php echo $siteDefaultLangId; ?>)
    
    if (product_added_by_admin == 1 && totalProducts == 0) {
        $('input[name=\'selprod_user_shop_name\']').autocomplete({
            'classes': {
                "ui-autocomplete": "custom-ui-autocomplete"
            },
            'source': function(request, response) {
                $.ajax({
                    url: fcom.makeUrl('sellerProducts', 'autoCompleteUserShopName'),
                    data: {
                        keyword: request['term'],
                        fIsAjax: 1
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(json) {
                        response($.map(json, function(item) {
                            return {
                                label: item['user_name'] + ' - ' + item['shop_identifier'],
                                value: item['user_name'] + ' - ' + item['shop_identifier'],
                                id: item['user_id']
                            };
                        }));
                    },
                });
            },
            select: function(event, ui) {
                $("input[name='product_seller_id']").val(ui.item.id);
            }
        });
    } else {
        $('input[name=\'selprod_user_shop_name\']').addClass('readonly-field');
        $('input[name=\'selprod_user_shop_name\']').attr('readonly', true);
    }

    $('input[name=\'selprod_user_shop_name\']').change(function() {
        if ($(this).val() == '') {
            $("input[name='product_seller_id']").val(0);
        }
    });
        
</script>