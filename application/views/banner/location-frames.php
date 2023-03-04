<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php
switch ($frameId) {
    case BannerLocation::HOME_PAGE_BANNER_LAYOUT_1:
        $image =  'images/banner_layouts/layout-1.jpg';
        break;
    case BannerLocation::HOME_PAGE_BANNER_LAYOUT_2:
        $image =  'images/banner_layouts/layout-2.jpg';
        break;
    case BannerLocation::PRODUCT_DETAIL_PAGE_BANNER:
        $image =  'images/banner_layouts/layout-3.jpg';
        break;
}
?>
<img src="<?php echo CONF_WEBROOT_URL . $image; ?>">