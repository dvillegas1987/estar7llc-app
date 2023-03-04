<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<h5 class="mb-2"><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?> - <?php echo count($products); ?> <?php echo Labels::getLabel('LBL_item(s)', $siteLangId); ?></h5>
<?php /* ?>  <div class="section__action js-editCart" style="display:block;"><a href="javascript:void(0);" onClick="editCart()" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Edit_Cart', $siteLangId);?></a> </div> <?php */ ?>
<?php /*  if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
<div class="applied-coupon">
    <span><?php echo Labels::getLabel("LBL_Coupon", $siteLangId); ?> "<strong><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></strong>" <?php echo Labels::getLabel("LBL_Applied", $siteLangId); ?></span> <a
        href="javascript:void(0)" onClick="removePromoCode()" class="btn btn-brand btn-sm"><?php echo Labels::getLabel("LBL_Remove", $siteLangId); ?></a></div>
<?php } else { ?>
<div class="coupon"> <a class="coupon-input btn btn-brand btn-block" href="javascript:void(0)" onclick="getPromoCode()"><?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></a> </div>

<?php } */ ?>
<div class="order-summary__sections">
    <div class="order-summary__section order-summary__section--product-list">
        <div class="order-summary__section__content scroll" data-simplebar data-simplebar-auto-hide="false">
            <!-- List group -->

            <ul class="list-group list-cart list-cart-checkout">
                <?php foreach ($products as $product) {
                    $productUrl = UrlHelper::generateUrl('Products', 'View', array($product['selprod_id']));
                ?>
                    <li class="list-group-item">
                        <div class="product-profile">
                            <div class="product-profile__thumbnail">
                                <a href="<?php echo $productUrl; ?>">
                                    <img class="img-fluid" data-ratio="3:4" src="<?php echo UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg'); ?>" alt="<?php echo $product['product_name']; ?>" title="<?php echo $product['product_name']; ?>">
                                </a>
                                <span class="product-qty"><?php echo $product['quantity']; ?></span>
                            </div>
                            <div class="product-profile__data">
                                <div class="title"><a class="" href="<?php echo $productUrl; ?>" title="<?php echo $product['product_name'] ?>"><?php echo $product['selprod_title'] ?></a></div>
                                <div class="options">
                                    <p class="">
                                        <?php if (isset($product['options']) && count($product['options'])) {
                                            $optionStr = '';
                                            foreach ($product['options'] as $key => $option) {
                                                $optionStr .= $option['optionvalue_name'] . '|';
                                            }
                                            echo rtrim($optionStr, '|');
                                        } ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="product-price"><?php echo CommonHelper::displayMoneyFormat($product['theprice'] * $product['quantity']); ?></div>

                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="order-summary__section order-summary__section--total-lines">
        <!-- Total -->
        <div class="cart-total my-3">
            <div class="">
                <ul class="list-group list-group-flush list-group-flush-x">
                    <li class="list-group-item">
                        <span class="label"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></span> <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
                    </li>
                    <?php if ($cartSummary['cartVolumeDiscount']) { ?>
                        <li class="list-group-item">
                            <span class="label"><?php echo Labels::getLabel('LBL_Loyalty/Volume_Discount', $siteLangId); ?></span> <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></span>
                        </li>
                    <?php } ?>
                    <?php if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
                        <li class="list-group-item ">
                            <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span> <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
                        </li>
                    <?php } ?>
                    <?php if (/* 0 < $shippingAddress && */isset($cartSummary['taxOptions'])) {
                        foreach ($cartSummary['taxOptions'] as $taxName => $taxVal) { ?>
                            <li class="list-group-item ">
                                <span class="label"><?php echo $taxVal['title']; ?></span>
                                <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($taxVal['value']); ?></span>
                            </li>
                    <?php }
                    } ?>
                    <?php if (!FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
                        <li class="list-group-item ">
                            <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span>
                            <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
                        </li>
                    <?php } ?>
                    <?php if ($cartSummary['originalShipping']) { ?>
                        <li class="list-group-item ">
                            <span class="label"><?php echo Labels::getLabel('LBL_Delivery_Charges', $siteLangId); ?></span>
                            <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['shippingTotal']); ?></span>
                        </li>
                    <?php  } ?>
                    <?php if (!empty($cartSummary['cartRewardPoints'])) {
                        $appliedRewardPointsDiscount = CommonHelper::convertRewardPointToCurrency($cartSummary['cartRewardPoints']);
                    ?>
                        <li class="list-group-item ">
                            <span class="label"><?php echo Labels::getLabel('LBL_Reward_point_discount', $siteLangId); ?></span>
                            <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($appliedRewardPointsDiscount); ?></span>
                        </li>
                    <?php } ?>
                    <?php if (array_key_exists('roundingOff', $cartSummary) && $cartSummary['roundingOff'] != 0) { ?>
                        <li class="list-group-item">
                            <span class="label"><?php echo (0 < $cartSummary['roundingOff']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?></span>
                            <span class="ml-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['roundingOff']); ?></span>
                        </li>
                    <?php } ?>
                    <?php $orderNetAmt = $cartSummary['orderNetAmount'];
                    /* if (0 == $shippingAddress) $orderNetAmt = $orderNetAmt - $cartSummary['cartTaxTotal'];  */ ?>
                    <li class="list-group-item hightlighted">
                        <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
                        <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($orderNetAmt); ?></span>
                    </li>
                    <?php if(false){ ?>
                    <li class="list-group-item hightlighted">
                        <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
                        <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($orderNetAmt); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>
                    <p class="form-text text-muted mt-1"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $cartSummary['orderNetAmount']); ?> </p>
                <?php } ?>
                <?php /*  ?><p class="earn-points"><svg class="svg" width="20px" height="20px">
                        <use xlink:href="../images/retina/sprite.svg#rewards"
                            href="../images/retina/sprite.svg#rewards">
                        </use>
                    </svg> You will earn 575 points </p> <?php */ ?>

            </div>
        </div>
    </div>

    <?php /*?><div class="place-order">
        <p>By placing an order, you agree to Yokart.com's <a href=""> Terms & Conditions</a> and
            <a href=""> Privacy Policy </a></p>
        <button class="btn btn-brand btn-lg btn-block"></span>Place Order</button>
    </div> <?php */ ?>
</div>