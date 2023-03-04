<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$productFrm->setFormTagAttribute('class', 'web_form mt-5');
$productFrm->setFormTagAttribute('onsubmit', 'setUpProduct(this); return(false);');

$fld = $productFrm->getField('auto_update_other_langs_data');
if (null != $fld){
    $fld->developerTags['cbLabelAttributes'] = array('class' => 'checkbox');
    $fld->developerTags['cbHtmlAfterCheckbox'] = '<i class="input-helper"></i>';
}

$btnDiscardFld = $productFrm->getField('btn_discard');
if($prodCatId > 0){
   $catNameFld = $productFrm->getField('category_name'); 
   $catNameFld->addFieldTagAttribute('disabled', true);
   $btnDiscardFld->addFieldTagAttribute('onClick', 'goToProductCategory()');   
}else{
   $btnDiscardFld->addFieldTagAttribute('onClick', 'goToProduct()');
}   
?>
<div class="row justify-content-center">
     <div class="col-md-12">
         <?php echo $productFrm->getFormTag(); ?>
         <div class="row">
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                            <?php $fld = $productFrm->getField('product_identifier');
                              echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_identifier'); ?>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                        <?php $fld = $productFrm->getField('product_type');
                              echo $fld->getCaption();
                        ?>
                        </label>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_type'); ?>
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
                            <?php $fld = $productFrm->getField('brand_name');
                              echo $fld->getCaption();
                            ?>
                        </label>
                        <?php if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) { ?>
                        <span class="spn_must_field">*</span>
                        <?php } ?>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('brand_name'); ?>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                            <?php $fld = $productFrm->getField('category_name');
                              echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('category_name'); ?>
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
                            <?php $fld = $productFrm->getField('taxcat_name');
                              echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('taxcat_name'); ?>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                            <?php $fld = $productFrm->getField('product_min_selling_price');
                              echo $fld->getCaption();
                            ?>
                        </label>
                        <span class="spn_must_field">*</span>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_min_selling_price'); ?>
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
                            <?php $fld = $productFrm->getField('product_approved');
                              echo $fld->getCaption();
                            ?>
                        </label>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_approved'); ?>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="col-md-6">
                 <div class="field-set">
                     <div class="caption-wraper">
                        <label class="field_label">
                            <?php $fld = $productFrm->getField('product_active');
                              echo $fld->getCaption();
                            ?>
                        </label>
                     </div>
                     <div class="field-wraper">
                         <div class="field_cover">
                         <?php echo $productFrm->getFieldHtml('product_active'); ?>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
         <?php $divLayout = Language::getLayoutDirection($siteDefaultLangId); ?>
         <div class="p-4 mb-4 bg-gray rounded layout--<?php echo $divLayout;?>">
             <div class="row">
                <div class="col-md-6">
                     <div class="field-set">
                         <div class="caption-wraper">
                            <label class="field_label">
                                <?php $fld = $productFrm->getField('product_name['.$siteDefaultLangId.']');
                                      echo $fld->getCaption();
                                ?>
                                <span class="spn_must_field">*</span>
                            </label>
                         </div>
                         <div class="field-wraper">
                             <div class="field_cover">
                               <?php echo $productFrm->getFieldHtml('product_name['.$siteDefaultLangId.']'); ?>
                             </div>
                         </div>
                     </div>
                 </div>
                 <div class="col-md-6">
                     <div class="field-set">
                         <div class="caption-wraper">
                            <label class="field_label">
                                <?php $fld = $productFrm->getField('product_youtube_video['.$siteDefaultLangId.']');
                                      echo $fld->getCaption();
                                ?>
                            </label>
                         </div>
                         <div class="field-wraper">
                             <div class="field_cover">
                               <?php echo $productFrm->getFieldHtml('product_youtube_video['.$siteDefaultLangId.']'); ?>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="row">
                 <div class="col-md-12">
                     <div class="field-set mb-0">
                         <div class="caption-wraper">
                            <label class="field_label">
                            <?php $fld = $productFrm->getField('product_description_'.$siteDefaultLangId);
                                  echo $fld->getCaption();
                            ?>
                            </label>
                         </div>
                         <div class="field-wraper">
                             <div class="field_cover"> 
                             <?php echo $productFrm->getFieldHtml('product_description_'.$siteDefaultLangId); ?>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
             
             <?php 
                $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
                if(!empty($translatorSubscriptionKey) && count($otherLanguages) > 0){
             ?>
             <div class="row">
                 <div class="col-md-12">
                     <div class="field-set mb-0">
                         <div class="caption-wraper"></div>
                         <div class="field-wraper">
                             <div class="field_cover"> 
                             <?php echo $productFrm->getFieldHtml('auto_update_other_langs_data'); ?>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
            <?php } ?>
                            
         </div>
         
         <?php 
         if(!empty($otherLanguages)){ 
            foreach($otherLanguages as $langId=>$data) { 
                $layout = Language::getLayoutDirection($langId);
         ?>
         <div class="accordians_container accordians_container-categories my-3">
             <div class="accordian_panel">
                 <span class="accordian_title accordianhead" id="collapse_<?php echo $langId; ?>" onclick="translateData(this, '<?php echo $siteDefaultLangId; ?>', '<?php echo $langId; ?>')">
                 <?php echo $data." "; echo Labels::getLabel('LBL_Language_Data', $adminLangId); ?>
                 </span>
                 <div class="accordian_body accordiancontent layout--<?php echo $layout; ?>" style="display: none;">
                    <div class="p-4 mb-4 bg-gray rounded">
                         <div class="row">
                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">
                                        <?php  $fld = $productFrm->getField('product_name['.$langId.']');
                                            echo $fld->getCaption(); ?>
                                        </label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                        <?php echo $productFrm->getFieldHtml('product_name['.$langId.']'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">
                                        <?php  $fld = $productFrm->getField('product_youtube_video['.$langId.']');
                                            echo $fld->getCaption(); ?>
                                        </label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                        <?php echo $productFrm->getFieldHtml('product_youtube_video['.$langId.']'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="field-set">
                                    <div class="caption-wraper">
                                        <label class="field_label">
                                        <?php  $fld = $productFrm->getField('product_description_'.$langId);
                                            echo $fld->getCaption(); ?>
                                        </label>
                                    </div>
                                    <div class="field-wraper">
                                        <div class="field_cover">
                                        <?php echo $productFrm->getFieldHtml('product_description_'.$langId); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                         <?php echo $productFrm->getFieldHtml('btn_discard'); ?>
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
                         echo $productFrm->getFieldHtml('product_brand_id'); 
                         echo $productFrm->getFieldHtml('ptc_prodcat_id'); 
                         echo $productFrm->getFieldHtml('ptt_taxcat_id');                         
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
$(document).ready(function(){
	$('input[name=\'brand_name\']').autocomplete({
        minLength : 0, 
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
		'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('brands', 'autoComplete'),
				data: {keyword: request['term'],fIsAjax:1, 'fetchAllRecords' : 1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ui) {
			$('input[name=\'product_brand_id\']').val(ui.item.id);
		}
	}).focus(function() {
        $('input[name=\'brand_name\']').autocomplete('search');
    });
	
    $('input[name=\'brand_name\']').change(function() {
        if ($(this).val() == '') {
            $("input[name='product_brand_id']").val(0);
        }
    });

	$('input[name=\'category_name\']').autocomplete({
        minLength: 2,
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
			$.ajax({
				url: fcom.makeUrl('productCategories', 'links_autocomplete'),
				data: {keyword: request['term'],fIsAjax:1},
				dataType: 'json',
				type: 'post',
				success: function(json) {
					response($.map(json, function(item) {
						return { label: item['name'], value: item['name'], id: item['id'] };
					}));
				},
			});
		},
		select: function(event, ui) {
			$('input[name=\'ptc_prodcat_id\']').val(ui.item.id);
		}
	}).focus(function(){            
            $(this).autocomplete('search', $(this).val())
        });	
    
    $('input[name=\'category_name\']').change(function() {
        if ($(this).val() == '') {
            $("input[name='ptc_prodcat_id']").val(0);
        }
    });
    
    $('input[name=\'taxcat_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
                $.ajax({
                        url: fcom.makeUrl('tax', 'autoCompleteTaxCategories'),
                        data: {keyword: request['term'],fIsAjax:1},
                        dataType: 'json',
                        type: 'post',
                        success: function(json) {
                                response($.map(json, function(item) {
                                        return { label: item['name'], value: item['name'], id: item['id'] };
                                }));
                        },
                });
        },
        select: function(event, ui) {
                $('input[name=\'ptt_taxcat_id\']').val(ui.item.id);
        }
    });
});
</script>