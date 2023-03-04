<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$productItems = [];
switch ($fulfillmentType) {
    case Shipping::FULFILMENT_PICKUP:
        require_once(CONF_THEME_PATH . 'checkout/fulfillment-pickup-summary.php');
        break;
    case Shipping::FULFILMENT_SHIP:
        require_once(CONF_THEME_PATH . 'checkout/review-ship-summary.php');
        break;
    
    default:
        $msg = Labels::getLabel("MSG_INVALID_FULFILLMENT_TYPE", $siteLangId);
        FatUtility::dieJsonError($msg);
        break;
}

$productItems = array_values($productItems);
$data = [
    'fulfillmentType' => $fulfillmentType,
    'hasPhysicalProd' => $hasPhysicalProd,
    'addresses' => $addresses,
    'cartSummary' => $cartSummary,
    'productItems' => $productItems,
];

require_once(CONF_THEME_PATH . 'cart/price-detail.php');

if (empty($productItems)) {
    $status = applicationConstants::OFF;
}
