<?php defined('SYSTEM_INIT') or die('Invalid Usage.');  ?>
<span class="shippingTabListing--js">
    <?php
    if (!empty($shipping_rates) && count($shipping_rates) > 0) {
        $shipping_row = 0;
        foreach ($shipping_rates as $key=>$shipping) { ?>
            <div class="row align-items-center shippingRow--js" id="shipping-row<?php echo $shipping_row; ?>">
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][pship_id]" value="<?php echo $shipping['pship_id']; ?>">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][country_id]" value="<?php echo $shipping["pship_country"]?>">
                        <input type="text"
                            name="product_shipping[<?php echo $shipping_row; ?>][country_name]"
                            value="<?php echo $shipping["pship_country"] != "1" ? $shipping["country_name"] : "&#8594;".Labels::getLabel('LBL_EveryWhere_Else', $siteLangId);?>"
                            placeholder="<?php echo Labels::getLabel('LBL_Destination_Country', $siteLangId)?>" class="pship_country" data-row="<?php echo $shipping_row; ?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][company_id]" value="<?php echo $shipping["pship_company"]?>">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][company_name]" value="<?php echo isset($shipping["scompany_name"]) ? $shipping["scompany_name"] : ''?>" placeholder="<?php echo Labels::getLabel('LBL_Shipping_Company', $siteLangId); ?>" class="pship_company" data-row="<?php echo $shipping_row; ?>">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="field-set">
                        <input type="hidden" name="product_shipping[<?php echo $shipping_row; ?>][processing_time_id]" value="<?php echo isset($shipping['pship_duration']) ? $shipping['pship_duration']: ''?>">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][processing_time]" value="<?php echo isset($shipping['sduration_days_or_weeks']) ? ShippingDurations::getShippingDurationTitle($shipping, $siteLangId) : ''?>"
                        placeholder="<?php echo Labels::getLabel('LBL_Shipping_Service_Type', $siteLangId)?>" class="pship_duration" data-row="<?php echo $shipping_row; ?>">
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="field-set">
                        <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][cost]" value="<?php echo isset($shipping["pship_charges"]) ? $shipping["pship_charges"] : '';?>" placeholder="<?php echo Labels::getLabel('LBL_RATE', $siteLangId) .' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="field-set">
                       <div class="icon-group">
                            <input type="text" name="product_shipping[<?php echo $shipping_row; ?>][additional_cost]" value="<?php echo isset($shipping["pship_additional_charges"]) ? $shipping["pship_additional_charges"] : '';?>" placeholder="<?php echo Labels::getLabel('LBL_Additional_Per_Item', $siteLangId).' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                            <button type="button" onclick="removeShippingRow('<?php echo $shipping_row; ?>');" class="btn btn-secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId)?>" style="<?php if($shipping_row == 0) { ?> display:none;<?php }else{ ?> display:block; <?php } ?>">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php $shipping_row++;
        }
    } else { ?>
        <div class="row align-items-center shippingRow--js" id="shipping-row0">
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="hidden" name="product_shipping[0][pship_id]" value="" />
                    <input type="hidden" name="product_shipping[0][country_id]">
                    <input type="text" name="product_shipping[0][country_name]" placeholder="<?php echo Labels::getLabel('LBL_Destination_Country', $siteLangId)?>" />
                </div>
            </div>
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="hidden" name="product_shipping[0][company_id]">
                    <input type="text" name="product_shipping[0][company_name]" placeholder="<?php echo Labels::getLabel('LBL_Shipping_Company', $siteLangId)?>">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="field-set">
                    <input type="hidden" name="product_shipping[0][processing_time_id]">
                    <input type="text" name="product_shipping[0][processing_time]" placeholder="<?php echo Labels::getLabel('LBL_Shipping_Service_Type', $siteLangId)?>">
                </div>
            </div>
            <div class="col-lg-2">
                <div class="field-set">
                    <input type="text" name="product_shipping[0][cost]" placeholder="<?php echo Labels::getLabel('LBL_RATE', $siteLangId).' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="field-set">
                   <div class="icon-group">
                        <input type="text" name="product_shipping[0][additional_cost]" placeholder="<?php echo Labels::getLabel('LBL_Additional_Per_Item', $siteLangId).' ['.commonHelper::getDefaultCurrencySymbol().']';?>">
                        <button type="button" onclick="removeShippingRow('0')" class="btn btn-secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Remove', $siteLangId)?>" style="display:none;">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</span>
<div class="row align-items-center justify-content-end">
    <div class="col-auto">
        <button type="button" class="btn btn-secondary ripplelink" title="<?php echo Labels::getLabel('LBL_Shipping', $siteLangId)?>" onclick="addShipping();">
            <i class="fa fa-plus"></i>
        </button>
    </div>
</div>

<script>
    addShipping = function() {
        var shipping_row = parseInt($("span.shippingTabListing--js div.shippingRow--js").length);
        //$("span.shippingTabListing--js div.shippingRow--js:last").clone().appendTo('span.shippingTabListing--js');
        var new_row = $("span.shippingTabListing--js div.shippingRow--js:last").clone();
        new_row.appendTo('span.shippingTabListing--js');
        new_row.attr("id", "shipping-row"+shipping_row);
        
        $("span.shippingTabListing--js div.shippingRow--js:last input").each(function(){
            $(this).val("");
            var name = $(this).attr("name");
            var newName = name.replace("["+(shipping_row-1)+"]", "["+(shipping_row)+"]");
            $(this).attr("name", newName);
            $(this).next('button').attr("onclick", 'removeShippingRow('+shipping_row+')');
        });
        
        $('input[name="product_shipping[' + shipping_row + '][country_name]"]').attr('data-row',shipping_row);
        $('input[name="product_shipping[' + shipping_row + '][company_name]"]').attr('data-row',shipping_row);
        $('input[name="product_shipping[' + shipping_row + '][processing_time]"]').attr('data-row',shipping_row);
        if(shipping_row > 0) {
            $('input[name="product_shipping[' + shipping_row + '][additional_cost]"]').next('button').css('display','block');
        }
        
        shippingautocomplete(shipping_row);
    }
    
    removeShippingRow = function(shipping_row) {
        var rowLen = parseInt($("span.shippingTabListing--js div.shippingRow--js").length);
         if(rowLen == 1){
            return false;
        }
        $("#shipping-row" + shipping_row).remove();
    }

    $('span.shippingTabListing--js div.shippingRow--js').each(function(index, element) {
        shippingautocomplete(index);
    });
    
</script>
