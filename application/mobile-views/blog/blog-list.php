<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

if (!empty($postList)) {
    array_walk($postList, function (&$value, &$key) use ($siteLangId) {
        $value['post_image'] = UrlHelper::generateFullUrl('Image', 'blogPostFront', array($value['post_id'], $siteLangId, ''));
    });
}

$data = array(
    'page' => $page,
    'pageCount' => $pageCount,
    'postList' => $postList,
    'recordCount' => $recordCount
);

if (empty($postList)) {
    $status = applicationConstants::OFF;
}
