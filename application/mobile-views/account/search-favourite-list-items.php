<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($products as $key => $product) {
    $products[$key]['product_image_url'] = UrlHelper::generateFullUrl('image', 'product', array($product['product_id'], "CLAYOUT3", $product['selprod_id'], 0, $siteLangId));
    $products[$key]['selprod_price'] = CommonHelper::displayMoneyFormat($product['selprod_price'], false, false, false);
    $products[$key]['theprice'] = CommonHelper::displayMoneyFormat($product['theprice'], false, false, false);
}

$data = array(
    'products' => $products,
    'showProductShortDescription' => $showProductShortDescription,
    'showProductReturnPolicy' => $showProductReturnPolicy,
    'page' => $page,
    'recordCount' => $recordCount,
    'pageCount' => $pageCount,
    'postedData' => $postedData,
    'startRecord' => $startRecord,
    'endRecord' => $endRecord,
);

if (1 > $recordCount) {
    $status = applicationConstants::OFF;
}
