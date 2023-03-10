<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$loggedUserId = UserAuthentication::getLoggedUserId(true);
$remainingWalletBalance = $userWalletBalance = User::getUserBalance($loggedUserId, true);

$totalRewardPoints = UserRewardBreakup::rewardPointBalance($loggedUserId);
$discountTotal = isset($cartSummary["cartDiscounts"]["coupon_discount_total"]) ? $cartSummary["cartDiscounts"]["coupon_discount_total"] : 0;
$cartValue = CommonHelper::convertCurrencyToRewardPoint($cartSummary['cartTotal'] - $discountTotal);
$minValue = min($totalRewardPoints, $cartValue);
$canBeUse = min($minValue, FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0));
$canBeUseRPAmt = CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($canBeUse));


$walletCharged = 0;
if ($userWalletBalance > 0 && $cartSummary['orderNetAmount'] > 0 && $cartSummary["cartWalletSelected"]) {
    $remainingWalletBalance = ($userWalletBalance - $cartSummary['orderNetAmount']);
    $remainingWalletBalance = ($remainingWalletBalance < 0) ? 0 : $remainingWalletBalance;

    $walletCharged = $userWalletBalance - $remainingWalletBalance;
}


$priceDetail = array(
    'userWalletBalance' => $userWalletBalance,
    'displayUserWalletBalance' => CommonHelper::displayMoneyFormat($userWalletBalance),
    'rewardPoints' => $totalRewardPoints,
    'canBeUseRP' => trim($canBeUse),
    'canBeUseRPAmt' => trim($canBeUseRPAmt),
    'walletCharged' => CommonHelper::displayMoneyFormat($walletCharged),
    'remainingWalletBalance' => $remainingWalletBalance,
    'displayRemainingWalletBalance' => CommonHelper::displayMoneyFormat($remainingWalletBalance),
    'orderNetAmount' => $cartSummary['orderNetAmount'],
);

$cartTotal = isset($cartSummary['cartTotal']) ? $cartSummary['cartTotal'] : 0;
$shippingTotal = isset($cartSummary['shippingTotal']) ? $cartSummary['shippingTotal'] : 0;
$cartTaxTotal = isset($cartSummary['cartTaxTotal']) ? $cartSummary['cartTaxTotal'] : 0;
$cartVolumeDiscount = isset($cartSummary['cartVolumeDiscount']) ? $cartSummary['cartVolumeDiscount'] : 0;
$coupon_discount_total = isset($cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary['cartDiscounts']['coupon_discount_total'] : 0;
$appliedRewardPointsDiscount = isset($cartSummary['cartRewardPoints']) ? $cartSummary['cartRewardPoints'] : 0;

$priceDetail['priceDetail'] = array(
    array(
        'key' => Labels::getLabel('LBL_Items', $siteLangId),
        'value' => count($products)
    ),
    array(
        'key' => Labels::getLabel('LBL_SUB_TOTAL', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($cartTotal)
    )
);


if (0 < $appliedRewardPointsDiscount) {
    $usedRPAmt = CommonHelper::convertRewardPointToCurrency($appliedRewardPointsDiscount);
    $priceDetail['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Reward_point_discount', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($usedRPAmt)
    );
}
if (0 < $cartVolumeDiscount) {
    $priceDetail['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Volume_Discount', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($cartVolumeDiscount)
    );
}
if (0 < $coupon_discount_total) {
    $priceDetail['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Discount', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($coupon_discount_total)
    );
}

if (0 < $cartTaxTotal) {
    if (isset($cartSummary['taxOptions']) && !empty($cartSummary['taxOptions'])) {
        foreach ($cartSummary['taxOptions'] as $taxName => $taxVal) {
            $priceDetail['priceDetail'][] = array(
                'key' => $taxVal['title'],
                'value' => CommonHelper::displayMoneyFormat($taxVal['value'])
            );
        }
    } else {
        $priceDetail['priceDetail'][] = array(
            'key' => Labels::getLabel('LBL_Tax_Charges', $siteLangId),
            'value' => CommonHelper::displayMoneyFormat($cartTaxTotal)
        );
    }
}

if (0 < $shippingTotal) {
    $priceDetail['priceDetail'][] = array(
        'key' => Labels::getLabel('LBL_Shipping_Charges', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($shippingTotal)
    );
}

if (array_key_exists('roundingOff', $cartSummary) && $cartSummary['roundingOff'] != 0 && !isset($cartPage)) {
    $priceDetail['priceDetail'][] = array(
        'key' => (0 < $cartSummary['roundingOff']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId),
        'value' => CommonHelper::displayMoneyFormat($cartSummary['roundingOff'])
    );
}

$orderNetAmount = $cartSummary['orderNetAmount'];
if (isset($cartPage) && true === $cartPage) {
    $orderNetAmount = $cartSummary['cartTotal'] - ((0 < $cartSummary['cartVolumeDiscount']) ? $cartSummary['cartVolumeDiscount'] : 0);
    $orderNetAmount = $orderNetAmount - ((isset($cartSummary['cartDiscounts']['coupon_discount_total']) && 0 < $cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary['cartDiscounts']['coupon_discount_total'] : 0);
}

$priceDetail['netPayable'] = array(
    'key' => Labels::getLabel('LBL_Net_Payable', $siteLangId),
    'value' => CommonHelper::displayMoneyFormat($orderNetAmount)
);

$data['cartSummary']['cartDiscounts'] = !empty($data['cartSummary']['cartDiscounts']) ? $data['cartSummary']['cartDiscounts'] : (object)array();
$data = !empty($data) ? array_merge($data, $priceDetail) : $priceDetail;
