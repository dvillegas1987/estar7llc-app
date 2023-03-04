<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($paymentMethods as $key => $val) {
    if (in_array($val['plugin_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_ADD_MONEY_TO_WALLET])) {
        unset($paymentMethods[$key]);
        continue;
    }
    $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PLUGIN_LOGO, $val['plugin_id']);
    $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
    $paymentMethods[$key]['image'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'plugin', array($val['plugin_id'], 'ICON'), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
}

$orderNetAmount = (!empty($orderInfo['order_net_amount']) && 0 < $orderInfo['order_net_amount'] ? $orderInfo['order_net_amount'] : 0);
$data = array(
    'paymentMethods' => array_values($paymentMethods),
    // 'orderInfo' => $orderInfo,
    'order_type' => $orderInfo['order_type'],
    'orderNetAmount' => $orderNetAmount
);
$data['netPayable'] = array(
    'key' => Labels::getLabel('LBL_Net_Payable', $siteLangId),
    'value' => CommonHelper::displayMoneyFormat($orderNetAmount)
);

if (empty(array_filter($paymentMethods)) || empty(array_filter($orderInfo))) {
    $status = applicationConstants::OFF;
    $msg = Labels::getLabel("LBL_Payment_method_is_not_available._Please_contact_your_administrator.", $siteLangId);
}
