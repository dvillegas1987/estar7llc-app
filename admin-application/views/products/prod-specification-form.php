<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
?>
<div class="web_form p-4 mb-4 bg-gray rounded">
     <div class="row">
        <div class="col-md-4">
             <div class="field-set">
                 <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Label_Text', $adminLangId); ?></label>
                    <span class="spn_must_field">*</span>
                 </div>
                 <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $langId; ?>]" value="<?php if(!empty($prodSpecData)) { echo $prodSpecData[0]['prodspec_name']; } ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $adminLangId); ?></a></li></ul>
                    </div>
                 </div>
             </div>
         </div>
         <div class="col-md-4">
             <div class="field-set">
                 <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $adminLangId); ?></label>
                    <span class="spn_must_field">*</span>
                 </div>
                 <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $langId; ?>]" value="<?php if(!empty($prodSpecData)) { echo $prodSpecData[0]['prodspec_value']; } ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Value_Is_Mandatory', $adminLangId); ?></a></li></ul>
                    </div>
                </div>
            </div>
         </div>
         <div class="col-md-2">
             <div class="field-set">
                 <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Group', $adminLangId); ?></label>
                 </div>
                 <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" class="prodspec_group" name="prodspec_group[<?php echo $langId; ?>]" value="<?php if(!empty($prodSpecData)) { echo $prodSpecData[0]['prodspec_group']; } ?>">
                    </div>
                </div>
            </div>
         </div>
         <div class="col-md-2">
             <div class="field-set">
                 <div class="caption-wraper"></div>
                 <div class="field-wraper">
                    <div class="field_cover">
                        <?php /* <input type="button" class="btn btn-brand btn-block" onClick="saveSpecification(<?php echo $langId; ?>, <?php if(!empty($prodSpecData)) { echo $prodSpecData[0]['prodspec_id']; } ?>)" value="<?php echo Labels::getLabel('LBL_Add', $adminLangId) ?>"> */ ?>
                        <button type="button" class="btn btn-brand btn-block" onClick="saveSpecification(<?php echo $langId; ?>, <?php if(!empty($prodSpecData)) { echo $prodSpecData[0]['prodspec_id']; } ?>)"><?php echo Labels::getLabel('LBL_Add', $adminLangId) ?></button>
                    </div>
                 </div>
             </div>
         </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    var langId = '<?php echo $langId; ?>';
    $('input[name="prodspec_group['+langId+']"]').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('products', 'prodSpecGroupAutoComplete'),
                data: {keyword: request['term'], langId: langId, fIsAjax:1},
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'],
                            value: item['name']
                            };
                    }));
                },
            });
        },
        'select': function(event, ui) {
            $('input[name="prodspec_group['+langId+']"]').val(ui.item.value);
            return false;
        }

    });
    
    $('.specification-field-js').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) { 
            e.preventDefault();
            return false;
        }
    });
});
</script>