<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$postImages = [];
if (isset($post_images) && !empty($post_images)) {
    array_walk($post_images, function (&$value, &$key) use (&$postImages) {
        $postImages[] = FatUtility::generateFullUrl('image', 'blogPostFront', array($value['afile_record_id'], $value['afile_lang_id'], "LAYOUT1", 0, $value['afile_id']), CONF_WEBROOT_FRONT_URL);
    });
}

if (isset($blogPostData['categoryNames']) && !empty($blogPostData['categoryNames'])) {
    $blogPostData['categoryNames'] = str_replace('~', ' | ', $blogPostData['categoryNames']);
}

$data = array(
    'post_images' => $postImages,
    'blogPostData' => isset($blogPostData) && !empty($blogPostData) ? $blogPostData : (object)array(),
    'socialShareContent' => isset($socialShareContent) && !empty($socialShareContent) ? $socialShareContent : (object)array()
);

if (empty($blogPostData)) {
    $status = applicationConstants::OFF;
}