<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<div class="tabs_data">
    <div class="tabs_body">
        <div class="row">
            <div class="col-md-6"> 
                 <div class="row">
                     <div class="col-md-12">
                         <div class="field-set">
                             <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_Add_Associated_Product_Option_Groups', $siteLangId); ?></label></div>
                             <div class="field-wraper">
                                 <div class="field_cover">
                                    <?php 
                                    $optionData = array();
                                    foreach($productOptions as $key=>$data){
                                        $optionData[$key]['id'] = $data['option_id'];
                                        $optionData[$key]['value'] = $data['option_name'] .'('.$data['option_identifier'].')';
                                    }
                                    ?>
                                    <input type="text" name="option_groups" value='<?php echo htmlspecialchars(json_encode($optionData), ENT_QUOTES, 'UTF-8'); ?>'>
                                 </div> 
                             </div>
                         </div>
                     </div>
                 </div> 
                 <div class="row">
                     <div class="col-md-12 mb-4" id="upc-listing">
                         
                     </div>
                 </div>
            </div>
            <?php 
                $tagData = array();
                foreach($productTags as $key=>$data){
                    $tagData[$key]['id'] = $data['tag_id'];
                    $tagData[$key]['value'] = $data['tag_identifier'];
                }
            ?>
            <div class="col-md-6">                             
                <div class="row">
                     <div class="col-md-12">
                         <div class="field-set">
                             <div class="caption-wraper"><label class="field_label"><?php echo Labels::getLabel('LBL_Product_Tags', $siteLangId); ?></label></div>
                             <div class="field-wraper">
                                 <div class="field_cover">
                                    <input class="tag_name" type="text" name="tag_name" id="get-tags"  value='<?php echo htmlspecialchars(json_encode($tagData), ENT_QUOTES, 'UTF-8'); ?>'> 
                                 </div> 
                             </div>
                         </div>
                     </div>
                 </div> 
            </div> 
        </div>  
    </div>
    <div class="row tabs_footer">
        <div class="col-6">
             <div class="field-set">
                 <div class="caption-wraper"><label class="field_label"></label></div>
                 <div class="field-wraper">
                     <div class="field_cover">
                        <input type="button" class="btn btn-outline-brand" onClick="productAttributeAndSpecificationsFrm(<?php echo $productId; ?>)" value="<?php echo Labels::getLabel('LBL_Back', $siteLangId); ?>">
                     </div>
                 </div>
             </div>
         </div>
         <div class="col-6 text-right">
             <div class="field-set">
                 <div class="caption-wraper"><label class="field_label"></label></div>
                 <div class="field-wraper">
                     <div class="field_cover">
                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                        <input type="button" class="btn btn-brand" onClick= <?php if($productType == Product::PRODUCT_TYPE_DIGITAL) { ?> "productMedia(<?php echo $productId; ?>)" <?php }else{ ?> "productShipping(<?php echo $productId; ?>)" <?php  } ?> value="<?php echo Labels::getLabel('LBL_Save_And_Next', $siteLangId); ?>">
                     </div>
                 </div>
             </div>
         </div>
     </div>

</div>

<script type="text/javascript">

$("document").ready(function() {   
    var product_id = '<?php echo $productId; ?>';
    
    upcListing(product_id); 
    
    addTagData = function(e){
        var tag_id = e.detail.tag.id; 
        var tag_name = e.detail.tag.title;   
        if(tag_id == ''){
            var data = 'tag_id=0&tag_identifier='+tag_name
            fcom.ajax(fcom.makeUrl('Seller', 'setupTag'), data, function(t) {     
                t = $.parseJSON(t);      
                var dataLang = 'tag_id='+t.tagId+'&tag_name='+tag_name+'&lang_id='+t.langId;
                fcom.ajax(fcom.makeUrl('Seller', 'tagLangSetup'), dataLang, function(t2) { 
                    fcom.ajax(fcom.makeUrl('Seller', 'updateProductTag'), 'product_id='+product_id+'&tag_id='+t.tagId, function(t3) { 
                         var tagifyId = e.detail.tag.__tagifyId;
                         $('[__tagifyid='+tagifyId+']').attr('id', t.tagId);
                     });
                });
            });
        }else{
            fcom.ajax(fcom.makeUrl('Seller', 'updateProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) {});
        }   
        tagifyProducts();
    }

    removeTagData = function(e){ 
        var tag_id = e.detail.tag.id;
        fcom.ajax(fcom.makeUrl('Seller', 'removeProductTag'), 'product_id='+product_id+'&tag_id='+tag_id, function(t) {});
        tagifyProducts();
    }
    
    getTagsAutoComplete = function(e){
        var keyword = e.detail.value;
        tagify.loading(true).dropdown.hide.call(tagify)
        var list = [];
        fcom.ajax(fcom.makeUrl('Seller', 'tagsAutoComplete'), {keyword:keyword}, function(t) {          
            var ans = $.parseJSON(t);
            for (i = 0; i < ans.length; i++) {            
                list.push({
                    "id" : ans[i].id,
                    "value" : ans[i].tag_identifier, 
                });
            } 
            tagify.settings.whitelist = list;
            tagify.loading(false).dropdown.show.call(tagify, keyword);
        });       
    }
    
    tagifyProducts = function() {
        var element = 'input[name=tag_name]';
        $(element).siblings( ".tagify" ).remove();
        tagify = new Tagify(document.querySelector('input[name=tag_name]'), {
           whitelist : [],
           delimiters : "#",
           editTags : false,
        }).on('add', addTagData).on('remove', removeTagData).on('input', getTagsAutoComplete);  
    };
    tagifyProducts();
         
    addOption = function(e){ 
        var option_id = e.detail.tag.id; 
        if(option_id == ''){
            console.log(e.detail.tag);
            var tagifyId = e.detail.tag.__tagifyId;
            console.log('[__tagifyid='+tagifyId+']');
             $('[__tagifyid='+tagifyId+']').remove();
        }else{
            updateProductOption(product_id, option_id, e);
        } 
        /*tagifyTheOptions();*/       
    }

    removeOption = function(e){ 
        var option_id = e.detail.tag.id;
        removeProductOption( product_id,option_id);
        /*tagifyTheOptions();*/
    }
    
    var delayTimer;    
    getOptionsAutoComplete = function(e){     
        clearTimeout(delayTimer);
        delayTimer = setTimeout(function() {
            var keyword = e.detail.value;
            tagifyOption.loading(true).dropdown.hide.call(tagifyOption);
            var listOptions = [];
            fcom.ajax(fcom.makeUrl('Seller', 'autoCompleteOptions'), '', function(t) {           
                var ans = $.parseJSON(t);
                for (i = 0; i < ans.length; i++) {            
                    listOptions.push({
                        "id" : ans[i].id,
                        "value" : ans[i].name+'('+ans[i].option_identifier+')',
                    });
                }            
                tagifyOption.settings.whitelist = listOptions;
                tagifyOption.loading(false).dropdown.show.call(tagifyOption, keyword);
            }); 
        }, 800);
        
    };     

    tagifyTheOptions = function() {
        var element = 'input[name=option_groups]';
        $(element).siblings( ".tagify" ).remove();
        tagifyOption = new Tagify(document.querySelector(element), {
            whitelist : [],
            delimiters : "#",
            editTags : false, 
        }).on('add', addOption).on('remove', removeOption).on('input', getOptionsAutoComplete); 
    };
    tagifyTheOptions();
});
</script>