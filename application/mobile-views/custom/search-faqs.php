<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = [
    'list' => $list
];

if (empty($list)) {
    $status = applicationConstants::OFF;
}
