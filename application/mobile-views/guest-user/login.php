<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$userImage = [
    'token' => $token,
    'user_image' => (!empty($userInfo['user_id']) ? UrlHelper::generateFullUrl('image', 'user', array($userInfo['user_id'],'ORIGINAL')) : '')
];
$data = array_merge($userInfo, $userImage);

if (empty($userInfo)) {
    $status = applicationConstants::OFF;
}
