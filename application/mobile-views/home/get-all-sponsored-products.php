<?php defined('SYSTEM_INIT') or die('Invalid Usage.');


foreach ($sponsoredProds as $index => $product) {
    $uploadedTime = AttachedFile::setTimeParam($product['product_updated_on']);
    $sponsoredProds[$index]['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId)).$uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    $sponsoredProds[$index]['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
    $sponsoredProds[$index]['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
}

$data = array(
    'sponsoredProds' => $sponsoredProds,
    'page' => $page,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'postedData' => $postedData
);
if (empty($sponsoredProds)) {
    $status = applicationConstants::OFF;
}
