<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>

<?php if (!empty($cartSummary['cartDiscounts']['coupon_code'])) { ?>
    <div class="coupons-applied">
        <div class="">
            <h6><?php echo $cartSummary['cartDiscounts']['coupon_code']; ?></h6>
            <p>
                <?php $arr =  ['{AMOUNT}' => CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total'])];
                echo CommonHelper::replaceStringData(Labels::getLabel("LBL_YOU_SAVED_ADDITIONAL_{AMOUNT}", $siteLangId), $arr); ?>
            </p>
        </div>
        <button class="close-layer" onClick="removePromoCode()"></button>

    </div>
<?php } else { ?>
    <div class="coupons">
        <button class="btn btn-outline-brand btn-block" onclick="getPromoCode()"> <?php echo Labels::getLabel('LBL_I_have_a_coupon', $siteLangId); ?></button>

    </div>
<?php } ?>

<ul class="list-group list-group-flush list-group-flush-x">
    <li class="list-group-item border-0">
        <span class="label"><?php echo Labels::getLabel('LBL_Total', $siteLangId); ?></span> <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartTotal']); ?></span>
    </li>
    <?php if ($cartSummary['cartVolumeDiscount']) { ?>
        <li class="list-group-item ">
            <span class="label"><?php echo Labels::getLabel('LBL_Volume_Discount', $siteLangId); ?></span> <span class="mleft-auto txt-success"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartVolumeDiscount']); ?></span>
        </li>
    <?php } ?>

    <?php if (FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
        <li class="list-group-item ">
            <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span> <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
        </li>
    <?php } ?>
    <?php /* if (isset($cartSummary['taxOptions']) && !empty($cartSummary['taxOptions'])) {
        foreach ($cartSummary['taxOptions'] as $taxName => $taxVal) { ?>
            <li class="list-group-item ">
                <span class="label"><?php echo $taxVal['title']; ?></span> <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($taxVal['value']); ?></span>
            </li>
    <?php   }
    } */ ?>
    <?php if (!FatApp::getConfig('CONF_TAX_AFTER_DISOCUNT', FatUtility::VAR_INT, 0) && !empty($cartSummary['cartDiscounts'])) { ?>
        <li class="list-group-item ">
            <span class="label"><?php echo Labels::getLabel('LBL_Discount', $siteLangId); ?></span> <span class="mleft-auto txt-success"><?php echo CommonHelper::displayMoneyFormat($cartSummary['cartDiscounts']['coupon_discount_total']); ?></span>
        </li>
    <?php } ?>
    <?php $netChargeAmt = $cartSummary['cartTotal'] - ((0 < $cartSummary['cartVolumeDiscount']) ? $cartSummary['cartVolumeDiscount'] : 0);
    $netChargeAmt = $netChargeAmt - ((isset($cartSummary['cartDiscounts']['coupon_discount_total']) && 0 < $cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary['cartDiscounts']['coupon_discount_total'] : 0); ?>
    <li class="list-group-item hightlighted">
        <span class="label"><?php echo Labels::getLabel('LBL_Net_Payable', $siteLangId); ?></span>
        <span class="mleft-auto"><?php echo CommonHelper::displayMoneyFormat($netChargeAmt); ?></span>
    </li>


</ul>

<?php if (CommonHelper::getCurrencyId() != FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1)) { ?>

    <p class="included"><?php echo CommonHelper::currencyDisclaimer($siteLangId, $cartSummary['orderNetAmount']); ?> </p>

<?php } ?>