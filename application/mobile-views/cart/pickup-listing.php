<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$productsArr = [
    'notAvailable' => [],
    'available' => [],
    'saveForLater' => [],
];

$productsCount = count($products);
if (0 < $productsCount) {
    uasort($products, function ($a, $b) {
        return  $b['fulfillment_type'] - $a['fulfillment_type'];
    });

    foreach ($products as $key => &$product) {
        $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id']));
        $product['shopUrl'] = UrlHelper::generateFullUrl('Shops', 'View', array($product['shop_id']));
        $product['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');

        $type = '';
        if ($product['fulfillment_type'] == Shipping::FULFILMENT_SHIP) {
            $type = 'notAvailable';
        } else {
            $type = 'available';
        }
        $product['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
        $product['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
        $productsArr[$type][] = $product;
    }
}

foreach ($saveForLaterProducts as &$slProduct) {
    $slProduct['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($slProduct['selprod_id']));
    $slProduct['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($slProduct['product_id'], "THUMB", $slProduct['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
    $productsArr['saveForLater'][] = $slProduct;
}

$cartSummary['orderNetAmount'] = CommonHelper::displayMoneyFormat($cartSummary['orderNetAmount'], false, false, false);

$data = array(
    'products' => $productsArr,
    'cartSummary' => $cartSummary,
    'cartSelectedBillingAddress' => empty($cartSelectedBillingAddress) ? (object)array() : $cartSelectedBillingAddress,
    'cartSelectedShippingAddress' => empty($cartSelectedShippingAddress) ? (object)array() : $cartSelectedShippingAddress,
    'hasPhysicalProduct' => $hasPhysicalProduct,
    'isShippingSameAsBilling' => $isShippingSameAsBilling,
    'selectedBillingAddressId' => $selectedBillingAddressId,
    'selectedShippingAddressId' => $selectedShippingAddressId,
    'cartProductsCount' => $cartProductsCount,
    'shipProductsCount' => $shipProductsCount,
    'pickUpProductsCount' => $pickUpProductsCount,
);

require_once(CONF_THEME_PATH . 'cart/price-detail.php');

if (empty(array_filter($productsArr))) {
    $status = applicationConstants::OFF;
}
