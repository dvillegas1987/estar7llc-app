<section class="section certified-bar shippingBar-js">
    <ul>
        <?php
        if (!empty($product['product_warranty'])) { ?>
            <li>
                <div class="certified-box">
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#yearswarranty" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#yearswarranty">
                            </use>
                        </svg>
                    </i>
                    <?php /* <p><?php echo $product['selprod_warranty_policies']['ppoint_title']; ?></p> */ ?>
                    <p>
                        <?php
                        $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_WARRANTY', $siteLangId);
                        echo CommonHelper::replaceStringData($lbl, ['{DAYS}' => $product['product_warranty']]);
                        ?>
                    </p>
                </div>
            </li>
        <?php } ?>
        <?php if ((!empty($product['shop_return_age']) || !empty($product['selprod_return_age'])) && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
            <li>
                <div class="certified-box">
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns">
                            </use>
                        </svg>
                    </i>
                    <?php /* <p><?php echo $product['selprod_return_policies']['ppoint_title']; ?></p> */ ?>
                    <p>
                        <?php
                        $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_RETURN_BACK_POLICY', $siteLangId);
                        $returnAge = !empty($product['selprod_return_age']) ? $product['selprod_return_age'] : $product['shop_return_age'];
                        $returnAge = !empty($returnAge) ? $returnAge : 0;
                        echo CommonHelper::replaceStringData($lbl, ['{DAYS}' => $returnAge]);
                        ?>
                    </p>
                </div>
            </li>
        <?php } ?>
        <?php if ((!empty($product['shop_cancellation_age']) || !empty($product['selprod_cancellation_age'])) && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
            <li>
                <div class="certified-box">
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#easyreturns">
                            </use>
                        </svg>
                    </i>
                    <p>
                        <?php
                        $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_CANCELLATION_POLICY', $siteLangId);
                        $cancellationAge = !empty($product['selprod_cancellation_age']) ? $product['selprod_cancellation_age'] : $product['shop_cancellation_age'];
                        $cancellationAge = !empty($cancellationAge) ? $cancellationAge : 0;
                        echo CommonHelper::replaceStringData($lbl, ['{DAYS}' => $cancellationAge]);
                        ?>
                    </p>
                </div>
            </li>
        <?php } ?>
        <?php if ($codEnabled && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
            <li>
                <div class="certified-box">
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#safepayments" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#safepayments">
                            </use>
                        </svg>
                    </i>
                    <p><?php echo Labels::getLabel('LBL_Cash_on_delivery_is_available', $siteLangId); ?>
                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="right" data-container="body" title="<?php echo Labels::getLabel('MSG_Cash_on_delivery_available._Choose_from_payment_options', $siteLangId); ?>
                    "></i></p>
                </div>
            </li>
        <?php } ?>
        <?php if (Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) { ?>
            <li>
                <div class="certified-box">
                    <?php $icon = $fulfillmentType == Shipping::FULFILMENT_PICKUP ? 'item_pickup' : 'freeshipping'; ?>
                    <i class="icn">
                        <svg class="svg">
                            <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#<?php echo $icon; ?>" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#<?php echo $icon; ?>">
                            </use>
                        </svg>
                    </i>
                    <p>
                        <?php
                        switch ($fulfillmentType) {
                            case Shipping::FULFILMENT_SHIP:
                                echo Labels::getLabel('LBL_SHIPPED_ONLY', $siteLangId);
                                break;
                            case Shipping::FULFILMENT_PICKUP:
                                echo Labels::getLabel('LBL_PICKUP_ONLY', $siteLangId);
                                break;
                            default:
                                echo Labels::getLabel('LBL_SHIPPMENT_AND_PICKUP', $siteLangId);
                                break;
                        }
                        ?>
                    </p>
                </div>
            </li>
        <?php } ?>
    </ul>
</section>

<!-- Below code used to remove above bar if empty. -->
<script>
    $(document).ready(function(){
        if (1 > $('.shippingBar-js ul li').length) {
            $('.shippingBar-js').remove();
        }
    });
</script>