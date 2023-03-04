<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$statusArr = array(
    'status' => 1,
    'msg' => !empty($msg) ? $msg : Labels::getLabel('MSG_Success', $siteLangId)
);

foreach ($orders as $index => $orderProduct) {
    $orders[$index]['orderstatus_color_code'] = applicationConstants::getClassColor($orderProduct['orderstatus_color_class']);
    $orders[$index]['product_image_url'] = UrlHelper::generateFullUrl('image', 'product', array($orderProduct['selprod_product_id'], "THUMB", $orderProduct['op_selprod_id'], 0, $siteLangId));
}
$data = array(
    'orders' => $orders,
    'page' => $page,
    'pageCount' => $pageCount,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
    'orderStatuses' => $orderStatuses
);
if (1 > count((array)$orders)) {
    $statusArr['status'] = 0;
    $statusArr['msg'] = Labels::getLabel('MSG_No_record_found', $siteLangId);
}
