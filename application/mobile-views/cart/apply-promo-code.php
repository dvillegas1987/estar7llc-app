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
        if ($product['fulfillment_type'] == Shipping::FULFILMENT_PICKUP) {
            $type = 'notAvailable';
        } else {
            $type = 'available';
        }
        $productsArr[$type][] = $product;
    }
}

$products = $productsArr['available'];

require_once(CONF_THEME_PATH . 'cart/price-detail.php');
