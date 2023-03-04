<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<div class="tabs ">
    <?php require_once(CONF_THEME_PATH.'seller/sellerCustomProductTop.php');?>
</div>
<div class="card">
    <div class="card-body ">
        <div class="tabs__content">
            <div class="row">
                <div class="col-md-12">
                    <?php
                    $customProductOptionFrm->setFormTagAttribute('class', 'form form--horizontal');
                    $customProductOptionFrm->developerTags['colClassPrefix'] = 'col-lg-6 col-md-';
                    $customProductOptionFrm->developerTags['fld_default_col'] = 6;
                    $fld1=$customProductOptionFrm->getField('option_name');
                    $fld = $customProductOptionFrm->getField('product_name');
                    $fld->setWrapperAttribute('class', 'col-lg-12');
                    $fld->developerTags['col'] = 12;
                    /* $fld1->fieldWrapper = array('<div class="row">', '</div>'); */
                    echo $customProductOptionFrm->getFormHtml(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('input[name=\'option_name\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('seller', 'autoCompleteOptions'),
                data: {
                    keyword: request['term'],
                    fIsAjax: 1
                },
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return { label: item['name'] + ' (' + item['option_identifier'] + ')', value: item['name'] + ' (' + item['option_identifier'] + ')', id: item['id'] };
                    }));
                },
            });
        },
        'select': function (event, ui) {
            updateProductOption(<?php echo $product_id;?>, ui.item.id);
        }
    });
</script>
