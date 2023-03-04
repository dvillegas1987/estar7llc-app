<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$data = array(
    'addresses' => $addresses,
);

if (empty($addresses)) {
    $status = applicationConstants::OFF;
}
