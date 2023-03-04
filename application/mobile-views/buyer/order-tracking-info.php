<?php defined('SYSTEM_INIT') or die('Invalid Usage . ');

$data = $trackingInfo;

if (empty($trackingInfo)) {
    $statusArr['status'] = 0;
    $statusArr['msg'] = Labels::getLabel('MSG_TRACKING_DETAIL_NOT_FOUND', $siteLangId);
}