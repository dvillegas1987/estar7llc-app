<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
foreach ($paymentMethods as $key => $val) {
    if ($cartHasDigitalProduct && in_array(strtolower($val['plugin_code']), ['cashondelivery', 'payatstore'])) {
        unset($paymentMethods[$key]);
        continue;
    }

    if (in_array($val['plugin_code'], $excludePaymentGatewaysArr[applicationConstants::CHECKOUT_PRODUCT])) {
        unset($paymentMethods[$key]);
        continue;
    }
    $fileData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PLUGIN_LOGO, $val['plugin_id']);
    $uploadedTime = AttachedFile::setTimeParam($fileData['afile_updated_at']);
    $paymentMethods[$key]['image'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'plugin', array($val['plugin_id'], 'ICON'), CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
}

$data = array(
    'orderId' => $orderId,
    'orderType' => $orderType,
    'canUseWalletForPayment' => (true == $canUseWalletForPayment ? 1 : 0),
    'paymentMethods' => array_values($paymentMethods),
);

require_once(CONF_THEME_PATH . 'cart/price-detail.php');

if (empty($products)) {
    $status = applicationConstants::OFF;
}
