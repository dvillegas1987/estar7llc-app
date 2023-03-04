<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$appScreenType = CommonHelper::getAppScreenType();
$resType = $appScreenType == applicationConstants::SCREEN_IPAD ? 'TABLET' : 'MOBILE';

foreach ($slides as $index => $slideDetail) {
    $uploadedTime = AttachedFile::setTimeParam($slideDetail['slide_img_updated_on']);
    $slides[$index]['slide_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'slide', array($slideDetail['slide_id'], $appScreenType, $siteLangId, $resType)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $urlTypeData = CommonHelper::getUrlTypeData($slideDetail['slide_url']);

    $slides[$index]['slide_url'] = $slides[$index]['slide_url_type'] = $slides[$index]['slide_url_title'] = "";
    if (false != $urlTypeData) {
        $slides[$index]['slide_url'] = ($urlTypeData['urlType'] == applicationConstants::URL_TYPE_EXTERNAL ? $slideDetail['slide_url'] : $urlTypeData['recordId']);
        $slides[$index]['slide_url_type'] = $urlTypeData['urlType'];

        switch ($urlTypeData['urlType']) {
            case applicationConstants::URL_TYPE_SHOP:
                $slides[$index]['slide_url_title'] = Shop::getName($urlTypeData['recordId'], $siteLangId);
                break;
            case applicationConstants::URL_TYPE_PRODUCT:
                $slides[$index]['slide_url_title'] = SellerProduct::getProductDisplayTitle($urlTypeData['recordId'], $siteLangId);
                break;
            case applicationConstants::URL_TYPE_CATEGORY:
                $slides[$index]['slide_url_title'] = ProductCategory::getProductCategoryName($urlTypeData['recordId'], $siteLangId);
                break;
            case applicationConstants::URL_TYPE_BRAND:
                $slides[$index]['slide_url_title'] = Brand::getBrandName($urlTypeData['recordId'], $siteLangId);
                break;
        }
    }
}
/* 
// Moved to collections.
foreach ($sponsoredProds as $index => $product) {
    $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']);
    $sponsoredProds[$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $sponsoredProds[$index]['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
    $sponsoredProds[$index]['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
}
foreach ($sponsoredShops as $shopIndex => $shopData) {
    foreach ($shopData["products"] as $index => $shopProduct) {
        $uploadedTime = AttachedFile::setTimeParam($shopProduct['product_updated_on']);
        $sponsoredShops[$shopIndex]['products'][$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($shopProduct['product_id'], "CLAYOUT3", $shopProduct['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
        $sponsoredShops[$shopIndex]['products'][$index]['selprod_price'] = CommonHelper::displayMoneyFormat($shopProduct['selprod_price'], false, false, false);
        $sponsoredShops[$shopIndex]['products'][$index]['theprice'] = CommonHelper::displayMoneyFormat($shopProduct['theprice'], false, false, false);
    }
} */

foreach ($collections as $collectionIndex => $collectionData) {
    if (array_key_exists('products', $collectionData)) {
        foreach ($collectionData['products'] as $index => $product) {
            $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']);
            $collections[$collectionIndex]['products'][$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
            $collections[$collectionIndex]['products'][$index]['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
            $collections[$collectionIndex]['products'][$index]['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
        }
    } elseif (array_key_exists('categories', $collectionData)) {
        foreach ($collectionData['categories'] as $index => $category) {
            $imgUpdatedOn = ProductCategory::getAttributesById($category['prodcat_id'], 'prodcat_updated_on');
            $uploadedTime = AttachedFile::setTimeParam($imgUpdatedOn);
            $collections[$collectionIndex]['categories'][$index]['prodcat_name'] = html_entity_decode($category['prodcat_name'], ENT_QUOTES, 'utf-8');
            $collections[$collectionIndex]['categories'][$index]['prodcat_description'] = strip_tags(html_entity_decode($category['prodcat_description'], ENT_QUOTES, 'utf-8'));

            $collections[$collectionIndex]['categories'][$index]['category_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Category', 'banner', array($category['prodcat_id'], $siteLangId, 'MOBILE', applicationConstants::SCREEN_MOBILE)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
        }
    } elseif (array_key_exists('shops', $collectionData)) {
        foreach ($collectionData['shops'] as $index => $shop) {
            $shopId = isset($shop['shopData']['shop_id']) ? $shop['shopData']['shop_id'] : $shop['shop_id'];
            $collections[$collectionIndex]['shops'][$index]['shop_id'] = $shopId;
            $collections[$collectionIndex]['shops'][$index]['shop_logo'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'shopLogo', array($shopId, $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
            $collections[$collectionIndex]['shops'][$index]['shop_banner'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'shopBanner', array($shopId, $siteLangId, 'MOBILE', 0, applicationConstants::SCREEN_MOBILE)), CONF_IMG_CACHE_TIME, '.jpg');
        }
    } elseif (array_key_exists('brands', $collectionData)) {
        foreach ($collectionData['brands'] as $index => $shop) {
            $collections[$collectionIndex]['brands'][$index]['brand_image'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'brand', array($shop['brand_id'], $siteLangId)), CONF_IMG_CACHE_TIME, '.jpg');
        }
    } elseif (array_key_exists('testimonials', $collectionData)) {
        foreach ($collectionData['testimonials'] as $index => $testimonial) {
            $collections[$collectionIndex]['testimonials'][$index]['user_image'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'testimonial', array($testimonial['testimonial_id'], $siteLangId, 'THUMB')), CONF_IMG_CACHE_TIME, '.jpg');
        }
    } elseif (array_key_exists('banners', $collectionData) && 0 < count((array)$collectionData['banners']) && array_key_exists('banners', $collectionData['banners'])) {
        foreach ($collectionData['banners']['banners'] as $index => $banner) {
            $uploadedTime = AttachedFile::setTimeParam($banner['banner_updated_on']);
            $urlTypeData = CommonHelper::getUrlTypeData($banner['banner_url']);
            if (false === $urlTypeData) {
                $urlTypeData = array(
                    'url' => $banner['banner_url'],
                    'recordId' => 0,
                    'urlType' => applicationConstants::URL_TYPE_EXTERNAL
                );
            }

            $collections[$collectionIndex]['banners']['banners'][$index]['banner_image'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Banner', 'HomePageBannerTopLayout', array($banner['banner_id'], $siteLangId, CommonHelper::getAppScreenType())) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');

            $collections[$collectionIndex]['banners']['banners'][$index]['banner_url'] = ($urlTypeData['urlType'] == applicationConstants::URL_TYPE_EXTERNAL ? $banner['banner_url'] : $urlTypeData['recordId']);
            $collections[$collectionIndex]['banners']['banners'][$index]['banner_url_type'] = $urlTypeData['urlType'];

            switch ($urlTypeData['urlType']) {
                case applicationConstants::URL_TYPE_SHOP:
                    $collections[$collectionIndex]['banners']['banners'][$index]['banner_url_title'] = Shop::getName($urlTypeData['recordId'], $siteLangId);
                    break;
                case applicationConstants::URL_TYPE_PRODUCT:
                    $collections[$collectionIndex]['banners']['banners'][$index]['banner_url_title'] = SellerProduct::getProductDisplayTitle($urlTypeData['recordId'], $siteLangId);
                    break;
                case applicationConstants::URL_TYPE_CATEGORY:
                    $collections[$collectionIndex]['banners']['banners'][$index]['banner_url_title'] = ProductCategory::getProductCategoryName($urlTypeData['recordId'], $siteLangId);
                    break;
                case applicationConstants::URL_TYPE_BRAND:
                    $collections[$collectionIndex]['banners']['banners'][$index]['banner_url_title'] = Brand::getBrandName($urlTypeData['recordId'], $siteLangId);
                    break;
            }
        }
    }
}

$data = array(
    'isWishlistEnable' => $isWishlistEnable,
    'slides' => $slides,
    'collections' => array_values($collections),
);

if (empty($sponsoredProds) && empty($sponsoredShops) && empty($slides) && empty($collections)) {
    $status = applicationConstants::OFF;
}
