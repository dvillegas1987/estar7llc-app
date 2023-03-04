<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($paymentMethods as $key => $val) {
    if (in_array($val['plugin_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_ADD_MONEY_TO_WALLET])) {
        unset($paymentMethods[$key]);
        continue;
    }
    $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PLUGIN_LOGO, $val['plugin_id']);
    $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
    $paymentMethods[$key]['image'] = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('Image', 'plugin', array($val['plugin_id'], 'ICON'), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
}

$data = array(
    'orderId' => $order_id,
    'orderType' => $orderType,
    'paymentMethods' => array_values($paymentMethods),
);
