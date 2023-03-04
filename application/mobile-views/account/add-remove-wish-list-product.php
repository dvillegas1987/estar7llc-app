<?php defined('SYSTEM_INIT') or die('Invalid Usage.');

$data = array(
    'wish_list_id' => $wish_list_id,
    'totalWishListItems' => $totalWishListItems,
);

if (0 < $removeFromCart) {
    require_once(CONF_THEME_PATH . 'cart/price-detail.php');
}