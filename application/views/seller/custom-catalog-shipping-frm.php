<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$productFrm->setFormTagAttribute('class', 'form form--horizontal');
$productFrm->setFormTagAttribute('onsubmit', 'setUpProductShipping(this); return(false);');
$productFrm->developerTags['colClassPrefix'] = 'col-md-';
$productFrm->developerTags['fld_default_col'] = 12;

if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) && isset($productType) && $productType != Product::PRODUCT_TYPE_DIGITAL) {
    $spPackageFld = $productFrm->getField('product_ship_package');
    $spPackageFld->developerTags['col'] = 6;
    $spPackageFld->htmlAfterField = '<br/><small> <a href="javascript:void(0)" onClick="shippingPackages()">' . Labels::getLabel('LBL_Shipping_Packages', $siteLangId) . '</a></small>';

    $spProfileFld = $productFrm->getField('shipping_profile');
    $spProfileFld->developerTags['col'] = 6;

    /* $psFreeFld = $productFrm->getField('ps_free');
    $psFreeFld->developerTags['col'] = 6; */

    $codFld = $productFrm->getField('product_cod_enabled');
    $codFld->developerTags['col'] = 12;
}

if (isset($productType) && $productType != Product::PRODUCT_TYPE_DIGITAL) {
    $weightUnitFld = $productFrm->getField('product_weight_unit');
    $weightUnitFld->developerTags['col'] = 6;

    $weightFld = $productFrm->getField('product_weight');
    $weightFld->developerTags['col'] = 6;
}

$btnBackFld = $productFrm->getField('btn_back');
$btnBackFld->developerTags['col'] = 6;
$btnBackFld->setFieldTagAttribute('onClick', 'productOptionsAndTag(' . $preqId . ');');
$btnBackFld->value = Labels::getLabel('LBL_Back', $siteLangId);
$btnBackFld->setFieldTagAttribute('class', "btn btn-outline-brand");

$btnSubmitFld = $productFrm->getField('btn_submit');
$btnSubmitFld->developerTags['col'] = 6;
$btnSubmitFld->setWrapperAttribute('class', 'text-right');

$btnSubmitFld->setFieldTagAttribute('class', "btn btn-brand");
?>
<div class="row justify-content-center">
    <div class="col-md-12">
        <?php echo $productFrm->getFormHtml(); ?>
    </div>
</div>

<script type="text/javascript">
    /* $(document).ready(function(){
    $('input[name=\'shipping_country\']').autocomplete({
        'classes': {
            "ui-autocomplete": "custom-ui-autocomplete"
        },
        'source': function(request, response) {
            $.ajax({
                url: fcom.makeUrl('Seller', 'countries_autocomplete'),
                data: {keyword: request['term'],fIsAjax:1},
                dataType: 'json',
                type: 'post',
                success: function(json) {
                    response($.map(json, function(item) {
                        return {
                            label: item['name'] ,
                            value: item['name'],
                            id: item['id']
                            };
                    }));
                },
            });
        },
        'select': function(event, ui) {
                $('input[name=\'ps_from_country_id\']').val(ui.item.id);
        }

    });

    $('input[name=\'shipping_country\']').keyup(function(){
        $('input[name=\'ps_from_country_id\']').val('');
    });
}); */
</script>