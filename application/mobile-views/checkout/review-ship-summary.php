<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

ksort($shippingRates);
$selectedShippingProducts = (object)[];
if (isset($cartOrderData['shopping_cart']['product_shipping_methods']['product'])) {
    $selectedShippingProducts = $cartOrderData['shopping_cart']['product_shipping_methods']['product'];
}
foreach ($shippingRates as $shippedBy => $shippedByItemArr) {
    ksort($shippedByItemArr);
    foreach ($shippedByItemArr as $shipLevel => $items) {
        $data = [];
        switch ($shipLevel) {
            case Shipping::LEVEL_ORDER:
            case Shipping::LEVEL_SHOP:
                if (isset($items['products']) && !empty($items['products'])) {
                    $productData = $items['products'];
                    $productInfo = current($productData);

                    if (!isset($productItems[$shippedBy]['title'])) {
                        $productItems[$shippedBy]['title'] = ($shipLevel == Shipping::LEVEL_SHOP) ? $productInfo['shop_name'] : FatApp::getConfig('CONF_WEBSITE_NAME_' . $siteLangId, null, '');
                    }

                    $shippingCharges = [];
                    if (isset($shippedByItemArr[$shipLevel]['rates'])) {
                        $shippingCharges = $shippedByItemArr[$shipLevel]['rates'];
                    }

                    $data['rates']['data'] = [];
                    foreach ($productData as $product) {
                        $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', [$product['selprod_id']]);
                        $product['shopUrl'] = UrlHelper::generateFullUrl('Shops', 'View', array($product['shop_id']));
                        $product['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');

                        $selectedRates = isset($selectedShippingProducts[$product['selprod_id']]) ? $selectedShippingProducts[$product['selprod_id']] : [];
                        if (array_key_exists('mshipapi_cost', $selectedRates)) {
                            $selectedRates['mshipapi_cost'] = CommonHelper::displayMoneyFormat($selectedRates['mshipapi_cost'], false, false, false);
                        }
                        
                        $data['rates']['data'][] = (object)$selectedRates;

                        $product['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
                        $product['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
                        $data['products'][] = $product;
                    }
                    $data['shipLevel'] = $shipLevel;

                    $productItems[$shippedBy]['data'][] = $data;
                }
                break;
            case Shipping::LEVEL_PRODUCT:
                if (isset($items['products']) && !empty($items['products'])) {
                    $data['rates']['data'] = [];
                    foreach ($items['products'] as $selProdid => $product) {
                        if (!isset($productItems[$shippedBy]['title'])) {
                            $productItems[$shippedBy]['title'] = $product['shop_name'];
                        }

                        $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id']));
                        $product['shopUrl'] = UrlHelper::generateFullUrl('Shops', 'View', array($product['shop_id']));
                        $product['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        $product['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
                        $product['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
                        $data['products'] = [$product];
                        $data['shipLevel'] = $shipLevel;
                        $selectedRates = isset($selectedShippingProducts[$product['selprod_id']]) ? $selectedShippingProducts[$product['selprod_id']] : (object)[];
                        if (array_key_exists('mshipapi_cost', $selectedRates)) {
                            $selectedRates['mshipapi_cost'] = CommonHelper::displayMoneyFormat($selectedRates['mshipapi_cost'], false, false, false);
                        }
                        $data['rates']['data'] = [$selectedRates];
                        $productItems[$shippedBy]['data'][] = $data;
                    }
                }

                if (isset($items['digital_products']) && !empty($items['digital_products'])) {
                    $data['rates']['data'] = [];
                    foreach ($items['digital_products'] as $selProdid => $product) {
                        if (!isset($productItems[$shippedBy]['title'])) {
                            $productItems[$shippedBy]['title'] = $product['shop_name'];
                        }

                        $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id']));
                        $product['shopUrl'] = UrlHelper::generateFullUrl('Shops', 'View', array($product['shop_id']));
                        $product['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        $product['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
                        $product['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
                        $data['products'] = [$product];
                        $data['shipLevel'] = $shipLevel;
                        $data['rates']['data'][] = is_array($selectedShippingProducts) && isset($selectedShippingProducts[$product['selprod_id']]) ? $selectedShippingProducts[$product['selprod_id']] : (object)[];
                        $productItems[$shippedBy]['data'][] = $data;
                    }
                }
                break;
        }
    }
}
