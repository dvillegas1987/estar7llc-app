<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$rewardPoints = UserRewardBreakup::rewardPointBalance(UserAuthentication::getLoggedUserId(true));
$cartTotal = $cartSummary['cartTotal'];
$couponDiscountTotal = isset($cartSummary['cartDiscounts']['coupon_discount_total']) ? $cartSummary["cartDiscounts"]["coupon_discount_total"] : 0;

$discountTotal = CommonHelper::convertCurrencyToRewardPoint($cartTotal - $couponDiscountTotal);
$canBeUsed = min(min($rewardPoints, $discountTotal), FatApp::getConfig('CONF_MAX_REWARD_POINT', FatUtility::VAR_INT, 0));

$rewardPointsDetail = array(
    'canBeUsed' => $canBeUsed,
    'balance' => $rewardPoints,
    'convertedValue' => CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($canBeUsed)),
);

$data = array('rewardPointsDetail' => $rewardPointsDetail);

require_once(CONF_THEME_PATH . 'cart/price-detail.php');
