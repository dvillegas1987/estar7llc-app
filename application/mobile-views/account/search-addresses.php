<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$key = array_search($shippingAddressId, array_column($addresses, 'addr_id'));
if (false === $key) {
    foreach ($addresses as &$value) {
        $value['isShippingAddress'] = (isset($value['add_is_default']) && 1 == $value['add_is_default']) ? 1 : 0;
    }
} else {
    foreach ($addresses as &$value) {
        $value['isShippingAddress'] = (isset($value['addr_id']) && $shippingAddressId == $value['addr_id']) ? 1 : 0;
    }
}

$data = array(
    'addresses' => !empty($addresses) ? $addresses : array(),
);

if (empty($addresses)) {
    $status = applicationConstants::OFF;
}
