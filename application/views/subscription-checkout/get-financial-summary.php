<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); 
$cartTotal = isset($cartSummary['cartTotal']) ? $cartSummary['cartTotal'] : 0;
$cartAdjustableAmount = isset($cartSummary['cartAdjustableAmount']) ? $cartSummary['cartAdjustableAmount'] : 0;
$discountTotal = isset($cartSummary['cartDiscounts']) && isset($cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary['cartDiscounts']['coupon_discount_total'] : 0;
$amount = CommonHelper::displayMoneyFormat($cartTotal - $cartAdjustableAmount - $discountTotal, true, false, true, false, true);
?>
<h5 class="mb-2"><?php echo Labels::getLabel('LBL_Order_Summary', $siteLangId); ?></h5>
<div class="box box--white box--radius order-summary">
    <?php if ($spackage_type != SellerPackages::FREE_TYPE) { ?>
        <?php if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
            <div class="coupons-applied">
                <div class="">
                    <h6><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></h6>
                    <p>
                        <?php $arr =  ['{AMOUNT}' => CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total'])];
                        echo CommonHelper::replaceStringData(Labels::getLabel("LBL_YOU_SAVED_ADDITIONAL_{AMOUNT}", $siteLangId), $arr); ?>
                    </p>
                </div>
                <button class="close-layer" onClick="removePromoCode()"> </button>

            </div>
        <?php } else { ?>
            <div class="coupons">
                <button class="btn btn-outline-brand btn-block" onclick="getPromoCode()"> <?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></button>

            </div>
        <?php } ?>
    <?php } ?>
    <div class="order-summary__sections">
        <div class="order-summary__section order-summary__section--total-lines">
            <div class="cart-total my-3">
                <div class="">
                    <ul class="list-group list-group-flush list-group-flush-x">
                        <li class="list-group-item">
                            <span class="label"><?php echo Labels::getLabel('LBL_Sub_Total', $siteLangId); ?></span>
                            <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartTotal, true, false, true, false, true); ?></span>
                        </li>
                        <?php if ($cartAdjustableAmount > 0) { ?>
                            <li class="list-group-item ">
                                <span class="label"><?php echo Labels::getLabel('LBL_Adjusted_Amount', $siteLangId); ?></span>
                                <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartAdjustableAmount, true, false, true, false, true); ?></span>
                            </li>
                        <?php } ?>
                        <?php if ($discountTotal > 0) { ?>
                            <li class="list-group-item ">
                                <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span>
                                <span class="mleft-auto">
                                    <?php echo CommonHelper::displayMoneyFormat($discountTotal, true, false, true, false, true); ?></span>
                            </li>
                        <?php } ?>
                        <li class="list-group-item hightlighted">
                            <span class="label"><?php echo Labels::getLabel('LBL_You_Pay', $siteLangId); ?></span>
                            <span class="mleft-auto">
                                <?php echo $amount; ?></span>
                        </li>
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>
<div class="gap"></div>