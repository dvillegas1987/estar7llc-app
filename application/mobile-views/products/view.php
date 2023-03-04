<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($upsellProducts as $index => $btProduct) {
    $uploadedTime = AttachedFile::setTimeParam($btProduct['product_updated_on']);
    $upsellProducts[$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($btProduct['product_id'], "MEDIUM", $btProduct['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $upsellProducts[$index]['selprod_price'] = CommonHelper::displayMoneyFormat($btProduct['selprod_price'], false, false, false);
    $upsellProducts[$index]['theprice'] = CommonHelper::displayMoneyFormat($btProduct['theprice'], false, false, false);
}

foreach ($relatedProductsRs as $index => $rProduct) {
    $uploadedTime = AttachedFile::setTimeParam($rProduct['product_updated_on']);
    $relatedProductsRs[$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($rProduct['product_id'], "MEDIUM", $rProduct['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $relatedProductsRs[$index]['selprod_price'] = CommonHelper::displayMoneyFormat($rProduct['selprod_price'], false, false, false);
    $relatedProductsRs[$index]['theprice'] = CommonHelper::displayMoneyFormat($rProduct['theprice'], false, false, false);
}

foreach ($recommendedProducts as $index => $recProduct) {
    $uploadedTime = AttachedFile::setTimeParam($recProduct['product_updated_on']);
    $recommendedProducts[$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($recProduct['product_id'], "MEDIUM", $recProduct['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $recommendedProducts[$index]['selprod_price'] = CommonHelper::displayMoneyFormat($recProduct['selprod_price'], false, false, false);
    $recommendedProducts[$index]['theprice'] = CommonHelper::displayMoneyFormat($recProduct['theprice'], false, false, false);
}

foreach ($recentlyViewed as $index => $recViewed) {
    $uploadedTime = AttachedFile::setTimeParam($recViewed['product_updated_on']);
    $recentlyViewed[$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($recViewed['product_id'], "MEDIUM", $recViewed['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $recentlyViewed[$index]['selprod_price'] = CommonHelper::displayMoneyFormat($recViewed['selprod_price'], false, false, false);
    $recentlyViewed[$index]['theprice'] = CommonHelper::displayMoneyFormat($recViewed['theprice'], false, false, false);
}

foreach ($productImagesArr as $afile_id => $image) {
    $uploadedTime = AttachedFile::setTimeParam($image['afile_updated_at']);
    $originalImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'product', array($product['product_id'], 'ORIGINAL', 0, $image['afile_id'])) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $mainImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'product', array($product['product_id'], 'MEDIUM', 0, $image['afile_id'])) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $productImagesArr[$afile_id]['product_image_url'] = $mainImgUrl;
}

$selectedOptionsArr = $product['selectedOptionValues'];
foreach ($optionRows as $key => $option) {
    foreach ($option['values'] as $index => $opVal) {
        $optionRows[$key]['values'][$index]['isAvailable'] = 1;
        $optionRows[$key]['values'][$index]['isSelected'] = 1;
        $optionRows[$key]['values'][$index]['optionUrlValue'] = $product['selprod_id'];
        if (!in_array($opVal['optionvalue_id'], $product['selectedOptionValues'])) {
            $optionRows[$key]['values'][$index]['isSelected'] = 0;
            $optionUrl = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id']);
            $optionUrlArr = explode("::", $optionUrl);
            if (is_array($optionUrlArr) && count($optionUrlArr) == 2) {
                $optionRows[$key]['values'][$index]['isAvailable'] = 0;
            }
            $optionUrl = Product::generateProductOptionsUrl($product['selprod_id'], $selectedOptionsArr, $option['option_id'], $opVal['optionvalue_id'], $product['product_id'], true);
            $optionRows[$key]['values'][$index]['optionUrlValue'] = $optionUrl;
        }
    }
}

$arr_flds = array(
    'country_name' => Labels::getLabel('LBL_Ship_to', $siteLangId),
    'pship_charges' => Labels::getLabel('LBL_Cost', $siteLangId),
    'pship_additional_charges' => Labels::getLabel('LBL_With_Another_item', $siteLangId),
);
$shippingRatesDetail = [];
foreach ($shippingRates as $sn => $row) {
    foreach ($arr_flds as $key => $val) {
        switch ($key) {
            case 'pship_additional_charges':
            case 'pship_charges':
                $shippingRatesDetail[$key]['title'] = $val;
                $shippingRatesDetail[$key]['rate'][] = CommonHelper::displayMoneyFormat($row[$key]);
                break;
            case 'country_name':
                $shippingRatesDetail[$key]['title'] = $val;
                $shippingRatesDetail[$key]['rate'][] = strip_tags(Product::getProductShippingTitle($siteLangId, $row));
                break;
        }
    }
}

if (!empty($product)) {
    $product['productPolicies'] = [];
    $product['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
    $product['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
    $product['inclusiveTax'] = FatUtility::int(FatApp::getConfig("CONF_PRODUCT_INCLUSIVE_TAX", FatUtility::VAR_INT, 0) && 0 == Tax::getActivatedServiceId());
    if (!empty($product['selprod_return_age']) && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) {
        $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_RETURN_BACK_POLICY', $siteLangId);
        $returnAge = !empty($product['selprod_return_age']) ? $product['selprod_return_age'] : $product['shop_return_age'];
        $returnAge = !empty($returnAge) ? $returnAge : 0;
        $returnAge = CommonHelper::replaceStringData($lbl, ['{DAYS}' => $returnAge]);
        $product['productPolicies'][] = array(
            'title' => $returnAge,
            'isSvg' => Plugin::RETURN_FALSE,
            'icon' => CONF_WEBROOT_URL . 'images/easyreturns.png'
        );
    }
    if (!empty($product['selprod_cancellation_age']) && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) {
        $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_CANCELLATION_POLICY', $siteLangId);
        $cancellationAge = !empty($product['selprod_cancellation_age']) ? $product['selprod_cancellation_age'] : $product['shop_cancellation_age'];
        $cancellationAge = !empty($cancellationAge) ? $cancellationAge : 0;
        $cancellationAge = CommonHelper::replaceStringData($lbl, ['{DAYS}' => $cancellationAge]);
        $product['productPolicies'][] = array(
            'title' => $cancellationAge,
            'isSvg' => Plugin::RETURN_FALSE,
            'icon' => CONF_WEBROOT_URL . 'images/easyreturns.png'
        );
    }
    if (!empty($product['product_warranty'])) {
        $lbl = Labels::getLabel('LBL_{DAYS}_DAYS_WARRANTY', $siteLangId);
        $warranty = CommonHelper::replaceStringData($lbl, ['{DAYS}' => $product['product_warranty']]);
        $product['productPolicies'][] = array(
            'title' => $warranty,
            'isSvg' => Plugin::RETURN_FALSE,
            'icon' => CONF_WEBROOT_URL . 'images/yearswarranty.png'
        );
    }

    if (Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) {
        if (isset($shippingDetails['ps_free']) && $shippingDetails['ps_free'] == applicationConstants::YES) {
            $product['productPolicies'][] = array(
                'title' => Labels::getLabel('LBL_Free_Shipping_on_this_Order', $siteLangId),
                'isSvg' => Plugin::RETURN_FALSE,
                'icon' => CONF_WEBROOT_URL . 'images/freeshipping.png'
            );
        } elseif (count($shippingRates) > 0) {
            $product['productPolicies'][] = array(
                'title' => Labels::getLabel('LBL_Shipping_Rates', $siteLangId),
                'isSvg' => Plugin::RETURN_FALSE,
                'icon' => CONF_WEBROOT_URL . 'images/shipping-policies.png',
                'shippingRatesDetail' => $shippingRatesDetail,
            );
        }
    }

    if (0 < $codEnabled && Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) {
        $product['productPolicies'][] = array(
            'title' => Labels::getLabel('LBL_Cash_on_delivery_is_available', $siteLangId),
            'isSvg' => Plugin::RETURN_FALSE,
            'icon' => CONF_WEBROOT_URL . 'images/safepayments.png'
        );
    }

    $product['youtubeUrlThumbnail'] = '';
    if (!empty($product['product_youtube_video'])) {
        $youtubeVideoUrl = $product['product_youtube_video'];
        $videoCode = UrlHelper::parseYouTubeurl($youtubeVideoUrl);
        $product['youtubeUrlThumbnail'] = 'https://img.youtube.com/vi/' . $videoCode . '/hqdefault.jpg';
    }
    $product['productUrl'] = UrlHelper::generateFullUrl('Products', 'View', array($product['selprod_id']));
}

$product['selprod_return_policies'] = !empty($product['selprod_return_policies']) ? $product['selprod_return_policies'] : (object) array();
$product['selprod_warranty_policies'] = !empty($product['selprod_warranty_policies']) ? $product['selprod_warranty_policies'] : (object) array();

if (Product::PRODUCT_TYPE_PHYSICAL == $product['product_type']) {
    $fulfillmentLabel = Labels::getLabel('LBL_INVALID_FULFILLMENT', $siteLangId);
    $icon = CONF_WEBROOT_URL . 'images/';
    switch ($fulfillmentType) {
        case Shipping::FULFILMENT_SHIP:
            $fulfillmentLabel = Labels::getLabel('LBL_SHIPPED_ONLY', $siteLangId);
            $icon .= 'shipping_30x30.png';
            break;
        case Shipping::FULFILMENT_PICKUP:
            $fulfillmentLabel = Labels::getLabel('LBL_PICKUP_ONLY', $siteLangId);
            $icon .= 'item_pickup_30x30.png';
            break;
        case Shipping::FULFILMENT_ALL:
            $fulfillmentLabel = Labels::getLabel('LBL_SHIPPMENT_AND_PICKUP', $siteLangId);
            $icon .= 'shipping_30x30.png';
            break;
        default:
            $fulfillmentLabel = Labels::getLabel('LBL_SHIPPED_ONLY', $siteLangId);
            $icon .= 'shipping_30x30.png';
            break;
    }

    $product['productPolicies'][] = array(
        'title' => $fulfillmentLabel,
        'isSvg' => Plugin::RETURN_TRUE,
        'icon' => $icon
    );
}

$product['product_description'] = html_entity_decode($product['product_description'], ENT_QUOTES, 'utf-8');
$product['product_description'] = str_replace('/editor/editor-image/',FatUtility::generateFullUrl().'editor/editor-image/',$product['product_description']);

if (!empty($product['moreSellersArr']) && 0 < count($product['moreSellersArr'])) {
    foreach ($product['moreSellersArr'] as &$value) {
        $value['selprod_price'] = CommonHelper::displayMoneyFormat($value['selprod_price'], false, false, false);
        $value['theprice'] = CommonHelper::displayMoneyFormat($value['theprice'], false, false, false);
    }
}

$productDetailPageBanner = [];
if (!empty($banners) && $banners['blocation_active'] && count($banners['banners'])) {
    foreach ($banners['banners'] as &$val) {
        $bannerImageUrl = '';
        if (!AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId)) {
            continue;
        } else {
            $slideArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_BANNER, $val['banner_id'], 0, $siteLangId);
            foreach ($slideArr as $slideScreen) {
                switch ($slideScreen['afile_screen']) {
                    case applicationConstants::SCREEN_MOBILE:
                        $bannerImageUrl = UrlHelper::generateFullUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_MOBILE));
                        break;
                    case applicationConstants::SCREEN_IPAD:
                        $bannerImageUrl = UrlHelper::generateFullUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_IPAD));
                        break;
                    case applicationConstants::SCREEN_DESKTOP:
                        $bannerImageUrl = UrlHelper::generateFullUrl('Banner', 'productDetailPageBanner', array($val['banner_id'], $siteLangId, applicationConstants::SCREEN_DESKTOP));
                        break;
                }
            }
            $val['banner_image_url'] = $bannerImageUrl;
            $bannerUrl = UrlHelper::generateFullUrl('Banner', 'url', array($val['banner_id']));
            $urlTypeData = CommonHelper::getUrlTypeData($bannerUrl);

            $val['banner_url'] = ($urlTypeData['urlType'] == applicationConstants::URL_TYPE_EXTERNAL ? $bannerUrl : $urlTypeData['recordId']);
            $val['banner_url_type'] = $urlTypeData['urlType'];
        }
    }
    $productDetailPageBanner = $banners['banners'];
}

$data = array(
    'reviews' => empty($reviews) ? (object) array() : $reviews,
    'codEnabled' => (true === $codEnabled ? 1 : 0),
    // 'shippingRates' => $shippingRates,
    'shippingDetails' => empty($shippingDetails) ? (object) array() : $shippingDetails,
    'optionRows' => $optionRows,
    'productSpecifications' => array(
        'title' => Labels::getLabel('LBL_Specifications', $siteLangId),
        'data' => $productSpecifications,
    ),
    'banners' => $productDetailPageBanner,
    'product' => array(
        'title' => Labels::getLabel('LBL_Detail', $siteLangId),
        'data' => empty($product) ? (object) array() : $product,
    ),
    'shop_rating' => round($shop_rating, 1),
    'shop' => empty($shop) ? (object) array() : $shop,
    'shopTotalReviews' => $shopTotalReviews,
    'productImagesArr' => array_values($productImagesArr),
    'volumeDiscountRows' => $volumeDiscountRows,
    'socialShareContent' => empty($socialShareContent) ? (object) array() : $socialShareContent,
    'buyTogether' => array(
        'title' => Labels::getLabel('LBL_Product_Add-ons', $siteLangId),
        'data' => $upsellProducts,
    ),
    'relatedProducts' => array(
        'title' => Labels::getLabel('LBL_Similar_Products', $siteLangId),
        'data' => array_values($relatedProductsRs)
    ),
    'recommendedProducts' => array(
        'title' => Labels::getLabel('LBL_Recommended_Products', $siteLangId),
        'data' => $recommendedProducts
    ),
    'recentlyViewed' => array(
        'title' => Labels::getLabel('LBL_Recently_Viewed', $siteLangId),
        'data' => array_values($recentlyViewed)
    )
);


if (empty((array) $product)) {
    $status = applicationConstants::OFF;
}
