<?php
defined('SYSTEM_INIT') or die('Invalid Usage.');
$layout = Language::getLayoutDirection($langId);
?>
<div class="p-4 mb-4 bg-gray rounded" dir="<?php echo $layout; ?>">
    <div class="row">
        <div class="col-md-4">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Label_Text', $siteLangId); ?></label>
                    <span class="spn_must_field">*</span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_name[<?php echo $langId; ?>]" value="<?php
                        if (!empty($prodSpecData)) {
                            echo $prodSpecData['prod_spec_name'];
                        }
                        ?>">
                        <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo    Labels::getLabel('LBL_Specification_Label_Text_Is_Mandatory', $siteLangId); ?></a></li></ul>  
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Value', $siteLangId); ?></label>
                    <span class="spn_must_field">*</span>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input class="specification-field-js" type="text" name="prodspec_value[<?php echo $langId; ?>]" value="<?php
                        if (!empty($prodSpecData)) {
                            echo $prodSpecData['prod_spec_value'];
                        }
                        ?>">
                         <ul style="display:none;" class="errorlist erlist_specification_<?php echo $langId; ?>"><li><a href="javascript:void(0);"><?php echo Labels::getLabel('LBL_Specification_Value_Is_Mandatory', $siteLangId); ?></a></li></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"><?php echo Labels::getLabel('LBL_Specification_Group', $siteLangId); ?></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <input type="text" class="prodspec_group specification-field-js" name="prodspec_group[<?php echo $langId; ?>]" value="<?php
                        if (!empty($prodSpecData)) {
                            echo $prodSpecData['prod_spec_group'];
                        }
                        ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="field-set">
                <div class="caption-wraper">
                    <label class="field_label"></label>
                </div>
                <div class="field-wraper">
                    <div class="field_cover">
                        <button type="button" class="btn btn-outline-brand btn-block" onClick="saveSpecification(<?php echo $langId; ?>, <?php
                                if (!empty($prodSpecData)) {
                                    echo $prodSpecData['key'];
                                }
                                ?>)"><?php echo Labels::getLabel('LBL_Add', $siteLangId) ?></button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    var langId = '<?php echo $langId; ?>';
    $('input[name="prodspec_group[' + langId + ']"]').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function (request, response) {
            $.ajax({
                url: fcom.makeUrl('Seller', 'prodSpecGroupAutoComplete'),
                data: {keyword: request['term'], langId: langId, fIsAjax: 1},
                dataType: 'json',
                type: 'post',
                success: function (json) {
                    response($.map(json, function (item) {
                        return {
                            label: item['name'],
                            value: item['name'],
                            id: item['name']
                        };
                    }));
                },
            });
        },
        'select': function (event, ui) {
            $('input[name="prodspec_group[' + langId + ']"]').val(ui.item.id);
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
