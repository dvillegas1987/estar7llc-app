<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

ksort($shippingRates);

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
                    if (count($shippingCharges) > 0) {
                        $name = current($shippingCharges)['code'];
                        $data['rates']['code'] =  $name;
                        foreach ($shippingCharges as $key => $shippingcharge) {
                            if (!empty($orderShippingData)) {
                                foreach ($orderShippingData as $shipdata) {
                                    if ($shipdata['opshipping_code'] == $name && ($key == $shipdata['opshipping_carrier_code'] . "|" . $shipdata['opshipping_label'] || $key == $shipdata['opshipping_rate_id'])) {
                                        $data['rates']['selected'] = $key;
                                        break;
                                    }
                                }
                            }

                            $data['rates']['data'][] = [
                                'title' => $shippingcharge['title'],
                                'cost' => CommonHelper::displayMoneyFormat($shippingcharge['cost'], false, false, false),
                                'id' => $shippingcharge['id'],
                                'carrier_code' => $shippingcharge['carrier_code'],
                                'service_code' => $key,
                            ];
                        }
                    }

                    foreach ($productData as $product) {
                        $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id']));
                        $product['shopUrl'] = UrlHelper::generateFullUrl('Shops', 'View', array($product['shop_id']));
                        $product['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        $data['products'][] = $product;
                    }
                    $data['shipLevel'] = $shipLevel;

                    $productItems[$shippedBy]['data'][] = $data;
                }
                break;
            case Shipping::LEVEL_PRODUCT:
                if (isset($items['products']) && !empty($items['products'])) {
                    foreach ($items['products'] as $selProdid => $product) {
                        if (!isset($productItems[$shippedBy]['title'])) {
                            $productItems[$shippedBy]['title'] = $product['shop_name'];
                        }

                        $priceListCount = isset($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']]) ? count($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']]) : 0;
                        $data['rates']['data'] = [];
                        if ($priceListCount > 0) {
                            $name = current($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']])['code'];
                            $data['rates']['code'] =  $name;
                            foreach ($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']] as $key => $shippingcharge) {
                                if (!empty($orderShippingData)) {
                                    foreach ($orderShippingData as $shipdata) {
                                        if ($shipdata['opshipping_code'] == $name && ($key == $shipdata['opshipping_carrier_code'] . "|" . $shipdata['opshipping_label'] || $key == $shipdata['opshipping_rate_id'])) {
                                            $data['rates']['selected'] = $key;
                                            break;
                                        }
                                    }
                                }
                                $data['rates']['data'][] = [
                                    'title' => $shippingcharge['title'],
                                    'cost' => CommonHelper::displayMoneyFormat($shippingcharge['cost'], false, false, false),
                                    'id' => $shippingcharge['id'],
                                    'carrier_code' => $shippingcharge['carrier_code'],
                                    'service_code' => $key,
                                ];
                            }
                        }

                        $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id']));
                        $product['shopUrl'] = UrlHelper::generateFullUrl('Shops', 'View', array($product['shop_id']));
                        $product['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        $data['products'] = [$product];
                        $data['shipLevel'] = $shipLevel;

                        $productItems[$shippedBy]['data'][] = $data;
                    }
                }

                if (isset($items['digital_products']) && !empty($items['digital_products'])) {
                    foreach ($items['digital_products'] as $selProdid => $product) {
                        if (!isset($productItems[$shippedBy]['title'])) {
                            $productItems[$shippedBy]['title'] = $product['shop_name'];
                        }

                        $priceListCount = isset($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']]) ? count($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']]) : 0;
                        $data['rates']['data'] = [];
                        if ($priceListCount > 0) {
                            $name = current($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']])['code'];
                            $data['rates']['code'] =  $name;
                            foreach ($shippedByItemArr[$shipLevel]['rates'][$product['selprod_id']] as $key => $shippingcharge) {
                                if (!empty($orderShippingData)) {
                                    foreach ($orderShippingData as $shipdata) {
                                        if ($shipdata['opshipping_code'] == $name && ($key == $shipdata['opshipping_carrier_code'] . "|" . $shipdata['opshipping_label'] || $key == $shipdata['opshipping_rate_id'])) {
                                            $data['rates']['selected'] = $key;
                                            break;
                                        }
                                    }
                                }
                                $data['rates']['data'][] = [
                                    'title' => $shippingcharge['title'],
                                    'cost' => CommonHelper::displayMoneyFormat($shippingcharge['cost'], false, false, false),
                                    'id' => $shippingcharge['id'],
                                    'carrier_code' => $shippingcharge['carrier_code'],
                                    'service_code' => $key,
                                ];
                            }
                        }

                        $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id']));
                        $product['shopUrl'] = UrlHelper::generateFullUrl('Shops', 'View', array($product['shop_id']));
                        $product['imageUrl'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "THUMB", $product['selprod_id'], 0, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
                        $data['products'] = [$product];
                        $data['shipLevel'] = $shipLevel;

                        $productItems[$shippedBy]['data'][] = $data;
                    }
                }
                break;
        }
    }
}
