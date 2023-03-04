<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($pnotifications as &$pvalue) {
    $image = '';
    if ($imgData = AttachedFile::getAttachment(AttachedFile::FILETYPE_PUSH_NOTIFICATION_IMAGE, $pvalue['pnotification_id'])) {
        $uploadedTime = AttachedFile::setTimeParam($imgData['afile_updated_at']);
        $image = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('Image', 'pushNotificationImage', [$pvalue['pnotification_id']], CONF_WEBROOT_FRONT_URL) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');
    }
    $pvalue['image'] = $image;
    $pvalue['urlDetail'] = (object)array();
    if (!empty($pvalue['pnotification_url'])) {
        $urlDetail = CommonHelper::getUrlTypeData($pvalue['pnotification_url']);
        $pvalue['urlDetail'] = false !== $urlDetail ? $urlDetail : (object)array();
    }
}

$data = array(
    'pnotifications' => !empty($pnotifications) ? $pnotifications : [],
    'total_pages' => $total_pages,
    'total_records' => $total_records,
);

if (empty($pnotifications)) {
    $status = applicationConstants::OFF;
}
