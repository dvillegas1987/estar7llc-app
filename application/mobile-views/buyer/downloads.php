<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

foreach ($downloads as $key => &$op) {
    $uploadedTime = AttachedFile::setTimeParam($op['product_updated_on']);
    $op['product_image_url'] = UrlHelper::getCachedUrl(UrlHelper::generateFullFileUrl('image', 'product', array($op['selprod_product_id'], "CLAYOUT3", $op['op_selprod_id'], 0, $siteLangId)) . $uploadedTime, CONF_IMG_CACHE_TIME, '.jpg');

    $op['files'] = array_values($op['files']);

    foreach ($op['files'] as &$file) {
        $file['downloadUrl'] = UrlHelper::generateFullUrl() . 'public/index.php?url=buyer/download-digital-file/' . $file['afile_id'] . '/' . $file['afile_record_id'];
    }
}

$data = array(
    'downloads' => $downloads,
    'page' => $page,
    'pageCount' => $pageCount,
    'recordCount' => $recordCount,
);

if (1 > $recordCount) {
    $status = applicationConstants::OFF;
}
